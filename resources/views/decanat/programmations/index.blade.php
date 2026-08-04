@extends('layouts.app')

@section('title', 'Horaires - Décanat')
@section('page-title', 'Horaires - Gestion des programmations')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Programmations de mon domaine</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('decanat.export.pdf') }}?date={{ $selectedDate }}&promotion_id={{ $selectedPromotion }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="{{ route('decanat.export.excel') }}?date={{ $selectedDate }}&promotion_id={{ $selectedPromotion }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer me-1"></i>Imprimer
        </button>
    </div>
</div>

<!-- Filtres -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end" id="filterForm">
            <div class="col-md-4">
                <label class="form-label small" for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control form-control-sm" value="{{ $selectedDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small" for="promotion_id">Promotion</label>
                <select name="promotion_id" id="promotion_id" class="form-select form-select-sm">
                    <option value="">Toutes les promotions</option>
                    @foreach($promotions as $promo)
                        <option value="{{ $promo->id }}" @selected($selectedPromotion == $promo->id)>{{ $promo->nom }} ({{ $promo->mention->nom ?? '' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('decanat.programmations.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

@if($programmations->isEmpty())
    <div class="alert alert-info">Aucune programmation pour la période sélectionnée.</div>
@else
    <!-- Tableau des horaires -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                <i class="bi bi-calendar-week me-2"></i>
                Semaine du {{ $weekDays[0]->format('d/m/Y') }} au {{ $weekDays[6]->format('d/m/Y') }}
            </span>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('decanat.programmations.index', ['date' => \Carbon\Carbon::parse($selectedDate)->subWeek()->format('Y-m-d'), 'promotion_id' => $selectedPromotion]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <a href="{{ route('decanat.programmations.index', ['date' => now()->format('Y-m-d'), 'promotion_id' => $selectedPromotion]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-calendar2-week"></i> Aujourd'hui
                </a>
                <a href="{{ route('decanat.programmations.index', ['date' => \Carbon\Carbon::parse($selectedDate)->addWeek()->format('Y-m-d'), 'promotion_id' => $selectedPromotion]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 timetable-grid">
                <thead class="table-dark">
                    <tr>
                        <th style="width:60px; min-width:60px;">Heure</th>
                        @foreach($weekDays as $day)
                            <th class="text-center {{ $day->format('Y-m-d') === now()->format('Y-m-d') ? 'table-primary' : '' }}">
                                <div>{{ $day->isoFormat('dddd') }}</div>
                                <small>{{ $day->format('d/m') }}</small>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $slot)
                        <tr>
                            <td class="text-center fw-semibold small text-muted">{{ $slot }}</td>
                            @foreach($weekDays as $day)
                                @php
                                    $dateKey = $day->format('Y-m-d');
                                    $cellProgrammations = $grid[$slot][$dateKey] ?? [];
                                @endphp
                                <td class="p-1 {{ $dateKey === $selectedDate ? 'table-info' : '' }}" style="height:60px; min-height:60px;">
                                    @foreach($cellProgrammations as $prog)
                                        <div class="timetable-event" title="{{ $prog->ec->nom }} - {{ $prog->enseignant->nom }} {{ $prog->enseignant->prenom }}">
                                            <div class="fw-semibold small">{{ \Illuminate\Support\Str::limit($prog->ec->nom, 20) }}</div>
                                            <small class="text-muted">
                                                {{ substr($prog->heure_debut, 0, 5) }}-{{ substr($prog->heure_fin, 0, 5) }}
                                                {{ $prog->auditoire->nom ?? '' }}
                                            </small>
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Liste détaillée -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-list-ul me-2"></i>Liste détaillée
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>EC</th>
                        <th>UE</th>
                        <th>Enseignant</th>
                        <th>Auditoire</th>
                        <th>Bâtiment</th>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Effectif</th>
                        <th>Promotions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programmations as $prog)
                    <tr>
                        <td class="fw-semibold">{{ $prog->ec->nom }}</td>
                        <td><small>{{ $prog->ec->ue->code }} - {{ $prog->ec->ue->nom }}</small></td>
                        <td>{{ $prog->enseignant->prenom }} {{ $prog->enseignant->nom }}</td>
                        <td>{{ $prog->auditoire->nom }}</td>
                        <td>{{ $prog->auditoire->batiment->nom }}</td>
                        <td>{{ $prog->date_debut->format('d/m/Y') }}</td>
                        <td>{{ substr($prog->heure_debut, 0, 5) }} - {{ substr($prog->heure_fin, 0, 5) }}</td>
                        <td>{{ $prog->effectif_total }}</td>
                        <td>
                            @foreach($prog->promotions_concernees as $pid)
                                @php($p = $promotions->firstWhere('id', $pid))
                                @if($p)
                                    <span class="badge bg-secondary">{{ $p->nom }}</span>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<style>
    .timetable-grid { font-size: 12px; }
    .timetable-grid td { vertical-align: top; }
    .timetable-event {
        background: #e8f4fd;
        border-left: 3px solid #0d6efd;
        border-radius: 4px;
        padding: 2px 4px;
        margin-bottom: 2px;
        cursor: default;
    }
    .timetable-event:hover { background: #d0e9fc; }
    @media print {
        .sidebar, .navbar, .btn, .card-header .btn-group, #filterForm { display: none !important; }
        .content { margin: 0 !important; padding: 0 !important; }
        .container-fluid { padding: 10px !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        .timetable-grid { page-break-after: auto; }
    }
</style>
@endsection
