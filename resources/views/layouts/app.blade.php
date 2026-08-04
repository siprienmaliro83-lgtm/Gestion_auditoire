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
        .sidebar { width: 280px; background: #14213d; color: #fff; overflow-y: auto; flex-shrink: 0; }
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
                    <div class="sidebar-section">Gestion des comptes</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'users') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'users') }}"><i class="bi bi-people me-2"></i>Utilisateurs</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'roles') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'roles') }}"><i class="bi bi-shield-lock me-2"></i>Rôles</a>
                    <div class="sidebar-section">Infrastructure</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'batiments') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'batiments') }}"><i class="bi bi-building me-2"></i>Bâtiments</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.crud.index', 'auditoires') ? 'active' : '' }}" href="{{ route('admin.crud.index', 'auditoires') }}"><i class="bi bi-door-open me-2"></i>Auditoires</a>
                    <div class="sidebar-section">Traitement des demandes</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.attributions.*') ? 'active' : '' }}" href="{{ route('admin.attributions.index') }}"><i class="bi bi-calendar-check me-2"></i>Attributions</a>
                    <div class="sidebar-section">Autres</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a>
                @endif

                @if(auth()->user()->hasRole('Decanat'))
                    <div class="sidebar-section">Structure académique</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'domaines') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'domaines') }}"><i class="bi bi-diagram-3 me-2"></i>Domaines</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'filieres') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'filieres') }}"><i class="bi bi-diagram-3 me-2"></i>Filières</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'mentions') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'mentions') }}"><i class="bi bi-tag me-2"></i>Mentions</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'promotions') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'promotions') }}"><i class="bi bi-calendar2-range me-2"></i>Promotions</a>
                    <div class="sidebar-section">Programmes</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'annees-academiques') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'annees-academiques') }}"><i class="bi bi-calendar3 me-2"></i>Années académiques</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'programmes-academiques') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'programmes-academiques') }}"><i class="bi bi-journal-richtext me-2"></i>Programmes</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'ues') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'ues') }}"><i class="bi bi-book me-2"></i>UE</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'ecs') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'ecs') }}"><i class="bi bi-journal-bookmark me-2"></i>EC</a>
                    <div class="sidebar-section">Enseignement</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.crud.index', 'enseignants') ? 'active' : '' }}" href="{{ route('decanat.crud.index', 'enseignants') }}"><i class="bi bi-person-workspace me-2"></i>Enseignants</a>
                    <div class="sidebar-section">Demandes & Horaires</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.demandes.create') ? 'active' : '' }}" href="{{ route('decanat.demandes.create') }}"><i class="bi bi-plus-circle me-2"></i>Nouvelle demande</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.demandes.index') ? 'active' : '' }}" href="{{ route('decanat.demandes.index') }}"><i class="bi bi-calendar-plus me-2"></i>Mes demandes</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('decanat.programmations.*') ? 'active' : '' }}" href="{{ route('decanat.programmations.index') }}"><i class="bi bi-printer me-2"></i>Horaires</a>
                    <div class="sidebar-section">Export</div>
                    <a class="px-3 py-2 rounded" href="{{ route('decanat.export.pdf') }}"><i class="bi bi-file-earmark-pdf me-2"></i>Export PDF</a>
                    <a class="px-3 py-2 rounded" href="{{ route('decanat.export.excel') }}"><i class="bi bi-file-earmark-excel me-2"></i>Export Excel</a>
                @endif

                @if(auth()->user()->hasRole('Enseignant'))
                    <div class="sidebar-section">Enseignement</div>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('enseignant.ecs.*') ? 'active' : '' }}" href="{{ route('enseignant.ecs.index') }}"><i class="bi bi-journal-text me-2"></i>Mes EC</a>
                    <a class="px-3 py-2 rounded {{ request()->routeIs('enseignant.programmations.*') ? 'active' : '' }}" href="{{ route('enseignant.programmations.index') }}"><i class="bi bi-clock me-2"></i>Mes horaires</a>
                @endif

                @if(auth()->user()->hasRole('Etudiant'))
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
