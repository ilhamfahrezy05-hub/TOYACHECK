<div class="page-header">
                <div>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                    @hasSection('page-subtitle')
                        <p class="page-subtitle">@yield('page-subtitle')</p>
                    @endif
                </div>
                <div class="d-flex gap-8 align-center">
                    @yield('page-actions')
                </div>
            </div>