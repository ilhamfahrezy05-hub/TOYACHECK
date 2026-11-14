<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['employee_id', 'name', 'username', 'password', 'avatar', 'assigned_vehicle_id', 'vehicle_assigned_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasRoles, HasFactory, Notifiable;

    /**
     * URL Avatar Pengguna (jika belum upload foto, otomatis fallback ke inisial nama).
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0F1E36&color=ffffff&bold=true&format=svg';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password'            => 'hashed',
            'vehicle_assigned_at' => 'datetime',
        ];
    }

    public function assignedVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    }

    /**
     * Cek apakah driver saat ini sudah terikat dengan satu armada aktif (periode 1 bulan / 30 hari).
     */
    public function hasActiveAssignedVehicle(): bool
    {
        if (!$this->assigned_vehicle_id || !$this->vehicle_assigned_at) {
            return false;
        }

        // Cek apakah penugasan masih dalam kurun waktu 30 hari (1 bulan)
        return $this->vehicle_assigned_at->diffInDays(now()) < 30;
    }

    /**
     * Sisa hari sebelum driver dapat memilih/mengganti armada baru lagi.
     */
    public function daysUntilNextVehicleChange(): int
    {
        if (!$this->hasActiveAssignedVehicle()) {
            return 0;
        }

        $daysPassed = $this->vehicle_assigned_at->diffInDays(now());
        return max(1, 30 - (int) $daysPassed);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * Ambil satu nilai preferensi, dengan nilai bawaan jika belum diatur.
     */
    public function preference(string $key, mixed $default = null): mixed
    {
        return $this->settings?->{$key} ?? $default;
    }

    /**
     * Jumlah baris per halaman sesuai preferensi pengguna.
     */
    public function perPage(): int
    {
        return (int) $this->preference('per_page', 15);
    }

    /**
     * Apakah pengguna ini punya riwayat data (checklist, perbaikan, permintaan)?
     * Dipakai untuk mencegah penghapusan akun yang menjadi bagian audit.
     */
    public function hasActivityHistory(): bool
    {
        return Checklist::where('user_id', $this->id)->orWhere('driver_id', $this->id)->exists()
            || Maintenance::where('mekanik_id', $this->id)->exists()
            || SparepartRequest::where('requested_by', $this->id)->exists();
    }

    /**
     * Jumlah notifikasi untuk lonceng di topbar, mengikuti preferensi pengguna.
     *
     * @return array{kendala:int, permintaan:int, total:int}
     */
    public function alertCounts(): array
    {
        $kendala = $this->preference('notify_kendala', true) && $this->hasAnyRole(['admin', 'mekanik'])
            ? Vehicle::where('status', Vehicle::STATUS_RUSAK)->count()
            : 0;

        $permintaan = $this->preference('notify_permintaan', true) && $this->hasAnyRole(['admin', 'pengadaan'])
            ? SparepartRequest::where('status', SparepartRequest::STATUS_PENDING)->count()
            : 0;

        return [
            'kendala'    => $kendala,
            'permintaan' => $permintaan,
            'total'      => $kendala + $permintaan,
        ];
    }

    /**
     * Mengambil daftar notifikasi terperinci (activity alerts) untuk dropdown topbar.
     *
     * @return \Illuminate\Support\Collection<int, array{id:string, type:string, title:string, message:string, time:string, url:string, icon:string, color:string, is_unread:bool}>
     */
    public function getNotificationsList(): \Illuminate\Support\Collection
    {
        $notifications = collect();

        // 1. Notifikasi Armada Rusak / Terkendala (Untuk Admin & Mekanik)
        if ($this->hasAnyRole(['admin', 'mekanik']) && $this->preference('notify_kendala', true)) {
            $brokenVehicles = Vehicle::where('status', Vehicle::STATUS_RUSAK)
                ->with(['checklists' => fn ($q) => $q->latest('tanggal')->limit(1)])
                ->latest('updated_at')
                ->take(5)
                ->get();

            foreach ($brokenVehicles as $v) {
                $lastChecklist = $v->checklists->first();
                $driverName = $lastChecklist?->driver?->name ?? 'Petugas';
                $timeAgo = $v->updated_at ? $v->updated_at->diffForHumans() : 'Baru saja';

                $notifications->push([
                    'id'        => 'vehicle-' . $v->id,
                    'type'      => 'kendala',
                    'title'     => 'Kendala Armada: ' . strtoupper($v->plat_nomor),
                    'message'   => "Unit dilaporkan mengalami kerusakan ({$v->merk}). Diperiksa oleh {$driverName}.",
                    'time'      => $timeAgo,
                    'raw_time'  => $v->updated_at,
                    'url'       => $this->hasRole('mekanik') ? route('mekanik.repairs') : route('maintenances.index'),
                    'icon'      => 'fa-solid fa-triangle-exclamation',
                    'color'     => '#DC2626',
                    'bg'        => '#FEE2E2',
                    'is_unread' => true,
                ]);
            }
        }

        // 2. Notifikasi Permintaan Suku Cadang Pending (Untuk Admin & Pengadaan)
        if ($this->hasAnyRole(['admin', 'pengadaan']) && $this->preference('notify_permintaan', true)) {
            $pendingRequests = SparepartRequest::where('status', SparepartRequest::STATUS_PENDING)
                ->with('requester')
                ->latest('created_at')
                ->take(5)
                ->get();

            foreach ($pendingRequests as $req) {
                $reqName = $req->requester?->name ?? 'Mekanik';
                $timeAgo = $req->created_at ? $req->created_at->diffForHumans() : 'Baru saja';

                $notifications->push([
                    'id'        => 'request-' . $req->id,
                    'type'      => 'permintaan',
                    'title'     => 'Permintaan Suku Cadang Baru',
                    'message'   => "{$reqName} mengajukan pengadaan {$req->qty}x {$req->nama_sparepart}.",
                    'time'      => $timeAgo,
                    'raw_time'  => $req->created_at,
                    'url'       => route('sparepart-requests.index'),
                    'icon'      => 'fa-solid fa-box-open',
                    'color'     => '#D97706',
                    'bg'        => '#FEF3C7',
                    'is_unread' => true,
                ]);
            }
        }

        // 3. Notifikasi Checklist Terbaru Hari Ini (Untuk Admin Distribusi)
        if ($this->hasRole('admin')) {
            $recentChecklists = Checklist::with(['vehicle', 'driver'])
                ->whereDate('tanggal', now()->toDateString())
                ->latest('tanggal')
                ->take(4)
                ->get();

            foreach ($recentChecklists as $chk) {
                $plat = $chk->vehicle->plat_nomor ?? '-';
                $driverName = $chk->driver->name ?? 'Sopir';
                $typeLabel = $chk->checklist_type === 'k3' ? 'Cek K3' : 'Cek Fisik';
                $timeAgo = $chk->tanggal ? $chk->tanggal->diffForHumans() : 'Hari ini';

                $notifications->push([
                    'id'        => 'checklist-' . $chk->id,
                    'type'      => 'checklist',
                    'title'     => "Laporan {$typeLabel}: " . strtoupper($plat),
                    'message'   => "Status: " . ($chk->status === 'aman' ? 'Aman' : 'Ditemukan Kendala') . " oleh {$driverName}.",
                    'time'      => $timeAgo,
                    'raw_time'  => $chk->tanggal,
                    'url'       => route('riwayat.index', ['vehicle_id' => $chk->vehicle_id]),
                    'icon'      => $chk->status === 'aman' ? 'fa-solid fa-clipboard-check' : 'fa-solid fa-circle-exclamation',
                    'color'     => $chk->status === 'aman' ? '#16A34A' : '#DC2626',
                    'bg'        => $chk->status === 'aman' ? '#DCFCE7' : '#FEE2E2',
                    'is_unread' => $chk->status !== 'aman',
                ]);
            }
        }

        // Urutkan notifikasi berdasarkan waktu terbaru
        return $notifications->sortByDesc('raw_time')->values();
    }
}
