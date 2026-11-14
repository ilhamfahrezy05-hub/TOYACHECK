<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\InspectionItem;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Riwayat seluruh checklist dengan filter dan export CSV.
 * Admin & Mekanik melihat semua data; Driver / HSE hanya melihat miliknya sendiri.
 */
class ChecklistHistoryController extends Controller
{
    public function index(Request $request)
    {
        $checklists = $this->filteredQuery($request)
            ->paginate(auth()->user()->perPage())
            ->withQueryString();

        $vehicles = Vehicle::orderBy('kode_armada')->get();

        // Ringkasan dihitung dari hasil filter yang sama (tanpa pagination)
        $summaryQuery = $this->filteredQuery($request);
        $stats = [
            'total'   => (clone $summaryQuery)->count(),
            'aman'    => (clone $summaryQuery)->where('status', 'aman')->count(),
            'kendala' => (clone $summaryQuery)->where('status', 'kendala')->count(),
        ];

        $filters = $request->only(['dari', 'sampai', 'vehicle_id', 'type', 'status']);

        return view('history.index', compact('checklists', 'vehicles', 'stats', 'filters'));
    }

    public function exportPdf(Request $request)
    {
        $filtered = $this->filteredQuery($request)->get();
        $bulan = $this->resolveMonth($request, $filtered->first()?->tanggal);

        if ($request->filled('vehicle_id')) {
            $vehicles = Vehicle::where('id', $request->vehicle_id)->get();
        } else {
            $vehicleIds = $filtered->pluck('vehicle_id')->filter()->unique()->values();
            $vehicles = $vehicleIds->isNotEmpty()
                ? Vehicle::whereIn('id', $vehicleIds)->orderBy('kode_armada')->get()
                : Vehicle::orderBy('kode_armada')->get();
        }

        $sheets = $this->buildMonthlyVehicleSheets($vehicles, $bulan);
        $filters = $request->only(['dari', 'sampai', 'vehicle_id', 'type', 'status']);
        $singleVehicle = null;

        return view('history.pdf', compact('sheets', 'bulan', 'filters', 'singleVehicle'));
    }

    /**
     * Cetak PDF 2 Lembar (Fisik & K3) khusus untuk satu unit armada per bulan.
     */
    public function vehiclePdf(Request $request, Vehicle $vehicle)
    {
        $bulan = $this->resolveMonth($request);
        $sheets = $this->buildMonthlyVehicleSheets(collect([$vehicle]), $bulan);
        $filters = $request->only(['dari', 'sampai', 'vehicle_id', 'type', 'status']);
        $singleVehicle = $vehicle;

        return view('history.pdf', compact('sheets', 'bulan', 'filters', 'singleVehicle'));
    }

    /**
     * Tentukan bulan target (YYYY-MM) dari parameter `bulan`, `dari`, `sampai`, atau fallback tanggal.
     */
    private function resolveMonth(Request $request, ?Carbon $fallback = null): string
    {
        foreach ([$request->bulan, $request->dari, $request->sampai] as $candidate) {
            if (! $candidate) {
                continue;
            }
            try {
                if (preg_match('/^\d{4}-\d{2}$/', (string) $candidate)) {
                    return Carbon::createFromFormat('Y-m', (string) $candidate)->format('Y-m');
                }
                return Carbon::parse((string) $candidate)->format('Y-m');
            } catch (\Throwable $e) {
                // Abaikan format tanggal yang tidak valid dan lanjut ke fallback berikutnya
            }
        }

        return ($fallback ?? now())->format('Y-m');
    }

    /**
     * Bangun struktur data laporan bulanan 2 lembar (Sheet 1: Fisik, Sheet 2: K3) per armada.
     */
    private function buildMonthlyVehicleSheets(Collection $vehicles, string $bulan): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulanNama = $bulanIndo[(int) $monthStart->format('n')] ?? $monthStart->format('F');
        $bulanSingkat = substr($bulanNama, 0, 3);
        $tahunNama = $monthStart->format('Y');

        $fisikMasterGroups = InspectionItem::groups()
            ->active()
            ->where('category', 'fisik')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('urutan')->orderBy('id')])
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $k3MasterItems = InspectionItem::answerable('k3')->get();

        $user = auth()->user();
        $canViewAll = $user->hasAnyRole(['admin', 'mekanik']);

        $allChecklists = Checklist::with(['driver', 'items.inspectionItem'])
            ->whereIn('vehicle_id', $vehicles->pluck('id'))
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->when(! $canViewAll, fn (Builder $q) => $q->where('driver_id', $user->id))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->groupBy('vehicle_id');

        $sheets = [];

        foreach ($vehicles as $vehicle) {
            $vehicleChecklists = $allChecklists->get($vehicle->id, collect());
            $fisikChecklists = $vehicleChecklists->where('checklist_type', 'fisik')->values();
            $k3Checklists = $vehicleChecklists->where('checklist_type', 'k3')->values();

            $fisikByDay = $this->indexChecklistByDay($fisikChecklists);
            $k3ByDay = $this->indexChecklistByDay($k3Checklists);

            $fisikGroupsData = [];
            $matchedFisikItemIds = [];
            $matchedFisikNames = [];

            foreach ($fisikMasterGroups as $groupIndex => $group) {
                $letter = chr(65 + $groupIndex); // A, B, C, D...
                $rows = [];
                $rowNo = 1;

                foreach ($group->children as $child) {
                    $matchedFisikItemIds[] = $child->id;
                    $matchedFisikNames[] = mb_strtolower(trim($child->nama));

                    $rows[] = [
                        'no' => $rowNo++,
                        'nama' => $child->nama,
                        'criteria' => $child->criteria ?: 'Sesuai standar',
                        'days' => $this->mapDailyItemMarks($fisikByDay, $child->id, $child->nama),
                    ];
                }

                if (! empty($rows)) {
                    $fisikGroupsData[] = [
                        'letter' => $letter,
                        'groupName' => $group->nama,
                        'rows' => $rows,
                    ];
                }
            }

            // Tambahkan item historis fisik yang tidak lagi berada di master aktif agar data tetap terbaca
            $extraFisikRows = $this->collectExtraHistoricalRows($fisikChecklists, $fisikByDay, $matchedFisikItemIds, $matchedFisikNames);
            if (! empty($extraFisikRows)) {
                $fisikGroupsData[] = [
                    'letter' => chr(65 + count($fisikGroupsData)),
                    'groupName' => 'Pemeriksaan Tambahan',
                    'rows' => $extraFisikRows,
                ];
            }

            // Sheet 2: K3 Matrix
            $k3Rows = [];
            $matchedK3Ids = [];
            $matchedK3Names = [];
            $k3No = 1;

            foreach ($k3MasterItems as $item) {
                $matchedK3Ids[] = $item->id;
                $matchedK3Names[] = mb_strtolower(trim($item->nama));

                $k3Rows[] = [
                    'no' => $k3No++,
                    'nama' => $item->criteria ? "{$item->nama} ({$item->criteria})" : $item->nama,
                    'days' => $this->mapDailyItemMarks($k3ByDay, $item->id, $item->nama),
                ];
            }

            $extraK3Rows = $this->collectExtraHistoricalRows($k3Checklists, $k3ByDay, $matchedK3Ids, $matchedK3Names, $k3No);
            $k3Rows = array_merge($k3Rows, $extraK3Rows);

            $k3Decisions = [];
            $k3Initials = [];
            for ($d = 1; $d <= 31; $d++) {
                $dayChecklist = $k3ByDay[$d] ?? null;
                if ($dayChecklist) {
                    $k3Decisions[$d] = $dayChecklist->status === 'aman' ? 'Y' : 'T';
                    $k3Initials[$d] = $this->makeInitials($dayChecklist->driver->name ?? '');
                } else {
                    $k3Decisions[$d] = null;
                    $k3Initials[$d] = null;
                }
            }

            $driverNames = $fisikChecklists->pluck('driver.name')->filter()->unique()->values();
            if ($driverNames->isEmpty()) {
                $driverNames = $k3Checklists->pluck('driver.name')->filter()->unique()->values();
            }

            $k3Examiners = $k3Checklists->pluck('driver.name')->filter()->unique()->values();

            $sheets[] = [
                'vehicle' => $vehicle,
                'bulan' => $bulan,
                'bulanNama' => $bulanNama,
                'bulanSingkat' => $bulanSingkat,
                'tahunNama' => $tahunNama,
                'driverName' => $driverNames->isNotEmpty() ? $driverNames->join(', ') : '-',
                'k3ExaminerName' => $k3Examiners->isNotEmpty() ? $k3Examiners->join(', ') : '',
                'fisikGroups' => $fisikGroupsData,
                'fisikNotes' => $this->collectMonthlyNotes($fisikChecklists),
                'fisikCount' => $fisikChecklists->count(),
                'k3Rows' => $k3Rows,
                'k3Decisions' => $k3Decisions,
                'k3Initials' => $k3Initials,
                'k3Notes' => $this->collectMonthlyNotes($k3Checklists),
                'k3Count' => $k3Checklists->count(),
            ];
        }

        return $sheets;
    }

    /**
     * Kelompokkan checklist per hari (1..31), ambil pemeriksaan terbaru jika ada >1 di tanggal yang sama.
     */
    private function indexChecklistByDay(Collection $checklists): array
    {
        $byDay = [];
        foreach ($checklists as $checklist) {
            $day = (int) $checklist->tanggal->format('j');
            $byDay[$day] = $checklist;
        }
        return $byDay;
    }

    /**
     * Petakan status harian ('✓' / 'X' / null) untuk satu item pemeriksaan sepanjang tanggal 1..31.
     */
    private function mapDailyItemMarks(array $checklistsByDay, ?int $inspectionItemId, string $itemName): array
    {
        $normalizedTarget = mb_strtolower(trim($itemName));
        $marks = [];

        for ($d = 1; $d <= 31; $d++) {
            $checklist = $checklistsByDay[$d] ?? null;
            if (! $checklist) {
                $marks[$d] = null;
                continue;
            }

            $found = $checklist->items->first(function ($ci) use ($inspectionItemId, $normalizedTarget) {
                if ($inspectionItemId && (int) $ci->inspection_item_id === $inspectionItemId) {
                    return true;
                }
                return mb_strtolower(trim((string) $ci->item)) === $normalizedTarget;
            });

            $marks[$d] = $found ? ($found->kondisi ? '✓' : 'X') : null;
        }

        return $marks;
    }

    /**
     * Kumpulkan item historis pada checklist yang tidak masuk daftar master aktif.
     */
    private function collectExtraHistoricalRows(
        Collection $checklists,
        array $checklistsByDay,
        array $matchedIds,
        array $matchedNames,
        int $startNo = 1
    ): array {
        $extras = [];
        $seenNames = [];

        foreach ($checklists as $checklist) {
            foreach ($checklist->items as $ci) {
                $norm = mb_strtolower(trim((string) $ci->item));
                if ($ci->inspection_item_id && in_array($ci->inspection_item_id, $matchedIds, true)) {
                    continue;
                }
                if (in_array($norm, $matchedNames, true) || isset($seenNames[$norm])) {
                    continue;
                }
                $seenNames[$norm] = true;
                $extras[] = [
                    'no' => $startNo++,
                    'nama' => $ci->item,
                    'criteria' => 'Sesuai standar',
                    'days' => $this->mapDailyItemMarks($checklistsByDay, $ci->inspection_item_id, $ci->item),
                ];
            }
        }

        return $extras;
    }

    /**
     * Kumpulkan catatan checklist & temuan kendala item sepanjang bulan.
     */
    private function collectMonthlyNotes(Collection $checklists): array
    {
        $notes = [];
        foreach ($checklists as $checklist) {
            $tgl = $checklist->tanggal->format('d/m');
            if (filled($checklist->catatan)) {
                $notes[] = "[Tgl {$tgl}] " . trim($checklist->catatan);
            }
            foreach ($checklist->items->where('kondisi', false) as $badItem) {
                if (filled($badItem->catatan)) {
                    $notes[] = "[Tgl {$tgl}] {$badItem->item}: " . trim($badItem->catatan);
                }
            }
        }
        return array_values(array_unique($notes));
    }

    /**
     * Buat inisial singkat dari nama pemeriksa (maksimal 3 huruf).
     */
    private function makeInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($parts)) {
            return '-';
        }
        $initials = '';
        foreach ($parts as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            if (mb_strlen($initials) >= 3) {
                break;
            }
        }
        return $initials;
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->filteredQuery($request)->get();

        $filename = 'riwayat-checklist-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // BOM supaya Excel membaca UTF-8 dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Tanggal', 'Kode Armada', 'Plat Nomor', 'Tipe Kendaraan',
                'Petugas', 'Jenis Checklist', 'Status', 'Item Bermasalah', 'Kelayakan (%)',
            ]);

            foreach ($rows as $row) {
                $total = $row->items->count();
                $bad   = $row->items->where('kondisi', false);

                fputcsv($out, [
                    $row->tanggal->format('Y-m-d H:i'),
                    $row->vehicle->kode_armada ?? '-',
                    $row->vehicle->plat_nomor ?? '-',
                    $row->vehicle->jenis_kendaraan ?? '-',
                    $row->driver->name ?? '-',
                    $row->checklist_type === 'k3' ? 'K3 & Kebersihan' : 'Fisik',
                    $row->status === 'aman' ? 'Aman' : 'Kendala',
                    $bad->pluck('item')->join('; '),
                    $total > 0 ? round((($total - $bad->count()) / $total) * 100) : 100,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Query dasar + filter dari request.
     */
    private function filteredQuery(Request $request): Builder
    {
        $user = auth()->user();

        return Checklist::with(['vehicle', 'driver', 'items'])
            ->when(
                ! $user->hasAnyRole(['admin', 'mekanik']),
                fn (Builder $q) => $q->where('driver_id', $user->id)
            )
            ->when($request->dari, fn (Builder $q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($request->sampai, fn (Builder $q, $v) => $q->whereDate('tanggal', '<=', $v))
            ->when($request->vehicle_id, fn (Builder $q, $v) => $q->where('vehicle_id', $v))
            ->when($request->type, fn (Builder $q, $v) => $q->where('checklist_type', $v))
            ->when($request->status, fn (Builder $q, $v) => $q->where('status', $v))
            ->latest('tanggal')
            ->latest('id');
    }
}
