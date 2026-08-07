<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gestion des auditoires')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .app-shell { min-height: 100vh; }
        .sidebar { width: 280px; background: #14213d; color: #fff; }
        .sidebar a { color: rgba(255, 255, 255, .78); text-decoration: none; }
        .sidebar a:hover, .sidebar .active { color: #fff; background: rgba(255, 255, 255, .1); }
        .content { min-width: 0; }
        .stat-card { border: 0; border-radius: 8px; box-shadow: 0 8px 24px rgba(20, 33, 61, .06); }
        .auth-card { max-width: 460px; border: 0; border-radius: 8px; box-shadow: 0 10px 28px rgba(20, 33, 61, .08); }
        @media (max-width: 991.98px) { .sidebar { width: 100%; } .app-shell { flex-direction: column; } }
    </style>
</head>
<body>
@auth
    <div class="d-flex app-shell">
        <aside class="sidebar p-3">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-building-check fs-4"></i>
                <span class="fw-semibold">Gestion Auditoires</span>
            </div>
            <nav class="d-grid gap-1">
                <a class="px-3 py-2 rounded {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                @if(auth()->user()->hasRole(['Administrateur', 'Super Administrateur']))
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.comptes.*') ? 'active' : '' }}" href="{{ route('admin.comptes.index') }}"><i class="bi bi-person-check me-2"></i>Validation des comptes</a>
                    <a class="px-3 py-2 rounded" href="{{ route('admin.crud.index', 'users') }}"><i class="bi bi-people me-2"></i>Utilisateurs</a>
                    <a class="px-3 py-2 rounded" href="{{ route('admin.crud.index', 'domaines') }}"><i class="bi bi-diagram-3 me-2"></i>Domaines</a>
                    <a class="px-3 py-2 rounded" href="{{ route('admin.crud.index', 'batiments') }}"><i class="bi bi-building me-2"></i>Bâtiments</a>
                    <a class="px-3 py-2 rounded" href="{{ route('admin.crud.index', 'auditoires') }}"><i class="bi bi-door-open me-2"></i>Auditoires</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.attributions.*') ? 'active' : '' }}" href="{{ route('admin.attributions.index') }}"><i class="bi bi-calendar-check me-2"></i>Demandes</a>
                    <a class="px-3 py-2 rounded" href="{{ route('admin.crud.index', 'programmations') }}"><i class="bi bi-calendar-week me-2"></i>Programmations</a>
                    <a class="px-3 py-2 rounded" href="{{ route('admin.notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a>
                    <a class="px-3 py-2 rounded" href="{{ route('admin.programmations.export') }}"><i class="bi bi-download me-2"></i>Exporter CSV</a>
                @endif
                @if(auth()->user()->hasRole('Décanat'))
                    <div class="px-3 pt-3 pb-1 small text-uppercase" style="color: rgba(255,255,255,.45);">Auditoires</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.demandes.*') ? 'active' : '' }}" href="{{ route('decanat.demandes.index') }}"><i class="bi bi-calendar-plus me-2"></i>Demandes</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.programmations.*') ? 'active' : '' }}" href="{{ route('decanat.programmations.index') }}"><i class="bi bi-printer me-2"></i>Horaires</a>
                    <div class="px-3 pt-3 pb-1 small text-uppercase" style="color: rgba(255,255,255,.45);">Structure académique</div>
                    @php($decanatResource = request()->route('resource'))
                    <a class="px-3 py-2 rounded {{ $decanatResource === 'enseignants' ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'enseignants') }}"><i class="bi bi-person-badge me-2"></i>Enseignants</a>
                    <a class="px-3 py-2 rounded {{ $decanatResource === 'etudiants' ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'etudiants') }}"><i class="bi bi-mortarboard me-2"></i>Étudiants</a>
                    <a class="px-3 py-2 rounded {{ $decanatResource === 'promotions' ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'promotions') }}"><i class="bi bi-people me-2"></i>Promotions</a>
                    <a class="px-3 py-2 rounded {{ $decanatResource === 'programmes-academiques' ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'programmes-academiques') }}"><i class="bi bi-journal-bookmark me-2"></i>Programmes</a>
                    <a class="px-3 py-2 rounded {{ $decanatResource === 'ues' ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'ues') }}"><i class="bi bi-journal me-2"></i>UE</a>
                    <a class="px-3 py-2 rounded {{ $decanatResource === 'ecs' ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'ecs') }}"><i class="bi bi-journal-text me-2"></i>EC</a>
                    <a class="px-3 py-2 rounded {{ $decanatResource === 'annees-academiques' ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'annees-academiques') }}"><i class="bi bi-calendar-range me-2"></i>Années académiques</a>
                @endif
                @if(auth()->user()->hasRole('Enseignant'))
                    <a class="px-3 py-2 rounded {{ request()->routeIs('enseignant.ecs.*') ? 'active' : '' }}" href="{{ route('enseignant.ecs.index') }}"><i class="bi bi-journal-text me-2"></i>Mes EC</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('enseignant.programmations.*') ? 'active' : '' }}" href="{{ route('enseignant.programmations.index') }}"><i class="bi bi-clock me-2"></i>Mes horaires</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a>
                @endif
                @if(auth()->user()->hasRole('Étudiant'))
                    <a class="px-3 py-2 rounded {{ request()->routeIs('etudiant.programmations.*') ? 'active' : '' }}" href="{{ route('etudiant.programmations.index') }}"><i class="bi bi-calendar-week me-2"></i>Mon programme</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('etudiant.promotions.*') ? 'active' : '' }}" href="{{ route('etudiant.promotions.show') }}"><i class="bi bi-mortarboard me-2"></i>Promotion</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a>
                @endif
            </nav>
        </aside>
        <main class="content flex-grow-1">
            <nav class="navbar navbar-expand bg-white border-bottom px-4">
                <div class="container-fluid px-0">
                    <span class="navbar-brand mb-0 h6">@yield('page-title', 'Dashboard')</span>
                    <div class="d-flex align-items-center gap-3">
                        <a class="btn btn-outline-primary btn-sm position-relative" href="{{ route('notifications.index') }}">
                            <i class="bi bi-bell"></i>
                            @php($unreadCount = auth()->user()->unreadNotifications()->count())
                            @if($unreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <span class="text-muted small">{{ auth()->user()->name }} · {{ auth()->user()->role?->nom }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm" type="submit">
                                <i class="bi bi-box-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </nav>
            <div class="container-fluid p-4">
                @yield('content')
            </div>
        </main>
    </div>
@else
    <main class="min-vh-100 d-flex align-items-center justify-content-center px-3">
        @yield('content')
    </main>
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
