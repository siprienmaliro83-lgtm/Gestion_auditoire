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
        .sidebar { width: 280px; background: #14213d; color: #fff; overflow-y: auto; }
        .sidebar a { color: rgba(255, 255, 255, .78); text-decoration: none; }
        .sidebar a:hover, .sidebar .active { color: #fff; background: rgba(255, 255, 255, .1); }
        .sidebar .sidebar-section { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: rgba(255,255,255,.4); padding: .5rem .75rem; margin-top: .5rem; }
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

                @if(auth()->user()->hasRole('Administrateur'))
                    <div class="sidebar-section">Gestion</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'users') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'users') }}"><i class="bi bi-people me-2"></i>Utilisateurs</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'enseignants') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'enseignants') }}"><i class="bi bi-person-workspace me-2"></i>Enseignants</a>
                    <div class="sidebar-section">Infrastructure</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'batiments') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'batiments') }}"><i class="bi bi-building me-2"></i>Bâtiments</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'auditoires') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'auditoires') }}"><i class="bi bi-door-open me-2"></i>Auditoires</a>
                    <div class="sidebar-section">Académique</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'domaines') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'domaines') }}"><i class="bi bi-diagram-3 me-2"></i>Domaines / Filières</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'ecs') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'ecs') }}"><i class="bi bi-journal-bookmark me-2"></i>UE / EC</a>
                    <div class="sidebar-section">Programmation</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.attributions.*') ? 'active' : '' }}" href="{{ route('admin.attributions.index') }}"><i class="bi bi-calendar-check me-2"></i>Attributions</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'programmations') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'programmations') }}"><i class="bi bi-calendar-week me-2"></i>Programmations</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'demandes') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'demandes') }}"><i class="bi bi-inbox me-2"></i>Demandes</a>
                    <div class="sidebar-section">Autres</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.programmations.export') ? 'active' : '' }}" href="{{ route('admin.programmations.export') }}"><i class="bi bi-download me-2"></i>Exporter CSV</a>
                @endif

                @if(auth()->user()->hasRole('Décanat'))
                    <div class="sidebar-section">Demandes</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.demandes.create') ? 'active' : '' }}" href="{{ route('decanat.demandes.create') }}"><i class="bi bi-plus-circle me-2"></i>Nouvelle demande</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.demandes.index') ? 'active' : '' }}" href="{{ route('decanat.demandes.index') }}"><i class="bi bi-calendar-plus me-2"></i>Mes demandes</a>
                    <div class="sidebar-section">Consultation</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.programmations.*') ? 'active' : '' }}" href="{{ route('decanat.programmations.index') }}"><i class="bi bi-printer me-2"></i>Horaires</a>
                @endif

                @if(auth()->user()->hasRole('Enseignant'))
                    <div class="sidebar-section">Enseignement</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('enseignant.ecs.*') ? 'active' : '' }}" href="{{ route('enseignant.ecs.index') }}"><i class="bi bi-journal-text me-2"></i>Mes EC</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('enseignant.programmations.*') ? 'active' : '' }}" href="{{ route('enseignant.programmations.index') }}"><i class="bi bi-clock me-2"></i>Mes horaires</a>
                @endif

                @if(auth()->user()->hasRole('Étudiant'))
                    <div class="sidebar-section">Études</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('etudiant.programmations.*') ? 'active' : '' }}" href="{{ route('etudiant.programmations.index') }}"><i class="bi bi-calendar-week me-2"></i>Mon emploi du temps</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('etudiant.promotions.*') ? 'active' : '' }}" href="{{ route('etudiant.promotions.show') }}"><i class="bi bi-mortarboard me-2"></i>Ma promotion</a>
                @endif
            </nav>
        </aside>
        <main class="content flex-grow-1">
            <nav class="navbar navbar-expand bg-white border-bottom px-4">
                <div class="container-fluid px-0">
                    <span class="navbar-brand mb-0 h6">@yield('page-title', 'Dashboard')</span>
                    <div class="d-flex align-items-center gap-3">
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
