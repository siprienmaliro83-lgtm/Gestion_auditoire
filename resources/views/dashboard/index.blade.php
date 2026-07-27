@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard - '.$role)

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($role === 'Administrateur')
    <div class="row g-3 mb-4">
        @foreach([
            ['Auditoires', $stats['auditoires'] ?? 0, 'bi-door-open', 'primary'],
            ['Bâtiments', $stats['batiments'] ?? 0, 'bi-buildings', 'primary'],
            ['Enseignants', $stats['enseignants'] ?? 0, 'bi-person-workspace', 'info'],
            ['EC', $stats['ecs'] ?? 0, 'bi-journal-bookmark', 'info'],
            ['Programmations', $stats['programmations'] ?? 0, 'bi-calendar-check', 'success'],
            ['Demandes en attente', $stats['demandes_en_attente'] ?? 0, 'bi-hourglass-split', 'warning'],
            ['Demandes validées', $stats['demandes_validees'] ?? 0, 'bi-check2-circle', 'success'],
            ['Comptes non confirmés', $stats['utilisateurs_non_confirme'] ?? 0, 'bi-person-exclamation', 'danger'],
        ] as [$label, $value, $icon, $color])
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fs-4 fw-semibold">{{ $value }}</div>
                        </div>
                        <i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card stat-card">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    <span>Demandes récentes</span>
                    <a href="{{ route('admin.attributions.index') }}" class="text-primary small">Voir tout</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @forelse($recentDemandes as $demande)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $demande->ec?->nom }}</div>
                                        <small class="text-muted">{{ $demande->enseignant?->prenom }} {{ $demande->enseignant?->nom }}</small>
                                    </td>
                                    <td class="text-end">
                                        @if($demande->statut === 'En attente')
                                            <span class="badge bg-warning text-dark">{{ $demande->statut }}</span>
                                        @elseif($demande->statut === 'Acceptée')
                                            <span class="badge bg-info">{{ $demande->statut }}</span>
                                        @elseif($demande->statut === 'Attribuée')
                                            <span class="badge bg-success">{{ $demande->statut }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $demande->statut }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-muted text-center">Aucune demande.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card stat-card">
                <div class="card-header bg-white fw-semibold">Programmations récentes</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @forelse($recentProgrammations as $prog)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $prog->ec?->nom }}</div>
                                        <small class="text-muted">{{ $prog->enseignant?->prenom }} {{ $prog->enseignant?->nom }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $prog->auditoire?->nom }}</small>
                                    </td>
                                    <td class="text-end">
                                        <small>{{ \Carbon\Carbon::parse($prog->date_debut)->format('d/m/Y') }} {{ substr($prog->heure_debut, 0, 5) }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-muted text-center">Aucune programmation.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@elseif($role === 'Décanat')
    <div class="row g-3 mb-4">
        @foreach([
            ['Total demandes', $stats['mes_demandes'] ?? 0, 'bi-file-earmark-text', 'primary'],
            ['En attente', $stats['en_attente'] ?? 0, 'bi-hourglass-split', 'warning'],
            ['Acceptées', $stats['acceptees'] ?? 0, 'bi-check-circle', 'info'],
            ['Attribuées', $stats['attribuees'] ?? 0, 'bi-door-open', 'success'],
            ['Refusées', $stats['refusees'] ?? 0, 'bi-x-circle', 'danger'],
        ] as [$label, $value, $icon, $color])
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fs-4 fw-semibold">{{ $value }}</div>
                        </div>
                        <i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card stat-card">
                <div class="card-header bg-white fw-semibold">Domaine: {{ $user->domaine?->nom ?? 'Non défini' }}</div>
                <div class="card-body">
                    <p>Vous pouvez créer des demandes d'auditoire pour les EC de votre domaine.</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('decanat.demandes.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Nouvelle demande
                        </a>
                        <a href="{{ route('decanat.programmations.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-printer me-1"></i>Horaires
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card stat-card">
                <div class="card-header bg-white fw-semibold">Mes demandes récentes</div>
                <div class="list-group list-group-flush">
                    @forelse($recentDemandes as $demande)
                        <a href="{{ route('decanat.demandes.show', $demande) }}" class="list-group-item list-group-item-action">
                            <div class="fw-semibold">{{ $demande->ec?->nom }}</div>
                            <small class="text-muted">{{ $demande->enseignant?->prenom }} {{ $demande->enseignant?->nom }} — {{ $demande->statut }}</small>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Aucune demande.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@elseif($role === 'Enseignant')
    <div class="row g-3 mb-4">
        @foreach([
            ['Mes EC', $stats['mes_ecs'] ?? 0, 'bi-journal-text', 'primary'],
            ['Mes programmations', $stats['mes_programmations'] ?? 0, 'bi-calendar-check', 'success'],
        ] as [$label, $value, $icon, $color])
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fs-4 fw-semibold">{{ $value }}</div>
                        </div>
                        <i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card stat-card">
        <div class="card-header bg-white fw-semibold">Mes programmations récentes</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>EC</th><th>Auditoire</th><th>Date</th><th>Horaire</th></tr>
                </thead>
                <tbody>
                    @forelse($recentProgrammations as $prog)
                        <tr>
                            <td>{{ $prog->ec?->nom }}</td>
                            <td>{{ $prog->auditoire?->nom }} ({{ $prog->auditoire?->batiment?->nom }})</td>
                            <td>{{ \Carbon\Carbon::parse($prog->date_debut)->format('d/m/Y') }}</td>
                            <td>{{ substr($prog->heure_debut, 0, 5) }} - {{ substr($prog->heure_fin, 0, 5) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">Aucune programmation.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($role === 'Étudiant')
    <div class="row g-3 mb-4">
        @foreach([
            ['Programmations', $stats['mes_programmations'] ?? 0, 'bi-calendar-week', 'primary'],
        ] as [$label, $value, $icon, $color])
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fs-4 fw-semibold">{{ $value }}</div>
                        </div>
                        <i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="text-muted">Ma Promotion</h6>
                    <h5>{{ $user->promotion?->nom ?? '-' }}</h5>
                    <p class="text-muted mb-0">{{ $user->promotion?->mention?->filiere?->domaine?->nom ?? '' }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card stat-card">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    <span>Mon emploi du temps</span>
                    <a href="{{ route('etudiant.programmations.index') }}" class="text-primary small">Voir tout</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @forelse($recentProgrammations as $prog)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $prog->ec?->nom }}</div>
                                        <small class="text-muted">{{ $prog->enseignant?->prenom }} {{ $prog->enseignant?->nom }}</small>
                                    </td>
                                    <td>{{ $prog->auditoire?->nom }}</td>
                                    <td class="text-end"><small>{{ substr($prog->heure_debut, 0, 5) }} - {{ substr($prog->heure_fin, 0, 5) }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted text-center">Aucune programmation.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
