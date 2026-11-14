<header class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <span class="breadcrumb-item">TOYACHECK</span>
                    <span class="breadcrumb-sep"><i class="fa-solid fa-chevron-right" style="font-size:9px"></i></span>
                    <span class="breadcrumb-current">@yield('page-title', 'Dashboard')</span>
                </div>
            </div>

            <div class="topbar-right">
                @php
                    $alerts = auth()->user()->alertCounts();
                    $notifList = auth()->user()->getNotificationsList();
                @endphp

                {{-- Notification Dropdown Bell --}}
                <div class="topbar-notif-wrap" style="position:relative">
                    <button type="button" class="topbar-notif" id="topbarNotifBtn" aria-label="Notifikasi" title="Notifikasi Sistem">
                        <i class="fa-regular fa-bell" style="font-size:16px"></i>
                        @if($alerts['total'] > 0)
                            <span class="notif-dot"></span>
                        @endif
                    </button>

                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-dropdown-header">
                            <div style="display:flex;align-items:center;gap:8px">
                                <span style="font-size:14px;font-weight:700;color:var(--text-primary)">Notifikasi</span>
                                @if($alerts['total'] > 0)
                                    <span style="background:#FEE2E2;color:#DC2626;font-size:10px;font-weight:800;padding:2px 7px;border-radius:999px">
                                        {{ $alerts['total'] }} Baru
                                    </span>
                                @endif
                            </div>
                            <span style="font-size:11px;color:var(--text-muted)">Real-time Alert</span>
                        </div>

                        <div class="notif-dropdown-body">
                            @forelse($notifList as $n)
                                <a href="{{ $n['url'] }}" class="notif-item {{ $n['is_unread'] ? 'unread' : '' }}">
                                    <div class="notif-item-icon" style="background:{{ $n['bg'] }};color:{{ $n['color'] }}">
                                        <i class="{{ $n['icon'] }}"></i>
                                    </div>
                                    <div class="notif-item-content">
                                        <div class="notif-item-title">{{ $n['title'] }}</div>
                                        <div class="notif-item-msg">{{ $n['message'] }}</div>
                                        <div class="notif-item-time">
                                            <i class="fa-regular fa-clock" style="font-size:9.5px;margin-right:3px"></i>
                                            {{ $n['time'] }}
                                        </div>
                                    </div>
                                    @if($n['is_unread'])
                                        <span class="notif-unread-dot"></span>
                                    @endif
                                </a>
                            @empty
                                <div style="text-align:center;padding:32px 16px;color:var(--text-muted)">
                                    <i class="fa-regular fa-bell-slash" style="font-size:28px;margin-bottom:8px;opacity:0.4;display:block"></i>
                                    <div style="font-size:12.5px;font-weight:600;color:var(--text-secondary)">Tidak ada notifikasi</div>
                                    <div style="font-size:11px;margin-top:2px">Semua armada dan permintaan dalam kondisi normal.</div>
                                </div>
                            @endforelse
                        </div>

                        @if($notifList->count() > 0)
                            <div class="notif-dropdown-footer">
                                @role('admin')
                                <a href="{{ route('maintenances.index') }}" class="notif-footer-link">
                                    Catatan Perbaikan <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>
                                </a>
                                @elserole('mekanik')
                                <a href="{{ route('mekanik.repairs') }}" class="notif-footer-link">
                                    Log Perbaikan <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>
                                </a>
                                @endrole
                            </div>
                        @endif
                    </div>
                </div>

                <div class="topbar-user" id="userMenuTrigger">
                    <div class="user-avatar" style="overflow:hidden">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                        <div class="user-role">
                            {{ ucfirst(auth()->user()->getRoleNames()->first() ?? 'Admin Distribusi') }}
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down user-dropdown-icon" id="userDropdownIcon"></i>

                    <div class="user-dropdown" id="userDropdown">
                        <a href="{{ route('settings.index') }}" class="dropdown-item">
                            <i class="fa-solid fa-gear"></i>
                            Pengaturan
                        </a>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item text-danger" onclick="openModal('modalConfirmLogout')">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>