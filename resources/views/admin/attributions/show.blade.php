@extends('layouts.app')

@section('title', 'Détail de la demande')
@section('page-title', 'Détail de la demande')

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card stat-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Demande #{{ $demande->id }}</span>
                    @if($demande->statut === 'Attribuée')
                        <span class="badge text-bg-success">{{ $demande->statut }}</span>
                    @elseif($demande->statut === 'Refusée')
                        <span class="badge text-bg-danger">{{ $demande->statut }}</span>
                    @elseif($demande->statut === 'Acceptée')
                        <span class="badge text-bg-info">{{ $demande->statut }}</span>
                    @else
                        <span class="badge text-bg-warning text-dark">{{ $demande->statut }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">EC</dt>
                        <dd class="col-sm-8">{{ $demande->ec?->nom ?? '-' }} <small class="text-muted">{{ $demande->ec?->code ?? '' }}</small></dd>

                        <dt class="col-sm-4">UE</dt>
                        <dd class="col-sm-8">{{ $demande->ec?->ue?->code ?? '' }} {{ $demande->ec?->ue?->nom ?? '-' }}</dd>

                        <dt class="col-sm-4">Enseignant</dt>
                        <dd class="col-sm-8">{{ $demande->enseignant?->nom ?? '-' }}</dd>

                        <dt class="col-sm-4">Demandeur</dt>
                        <dd class="col-sm-8">{{ $demande->user?->name ?? '-' }} <small class="text-muted">{{ $demande->user?->email ?? '' }}</small></dd>

                        <dt class="col-sm-4">Promotions</dt>
                        <dd class="col-sm-8">{{ $demande->promotionsNom }}</dd>

                        <dt class="col-sm-4">Effectif total</dt>
                        <dd class="col-sm-8">{{ $demande->effectif_total }}</dd>

                        <dt class="col-sm-4">Période</dt>
                        <dd class="col-sm-8">{{ $demande->date_debut->format('d/m/Y') }} → {{ $demande->date_fin->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Horaire</dt>
                        <dd class="col-sm-8">{{ $demande->heure_debut }} → {{ $demande->heure_fin }}</dd>

                        <dt class="col-sm-4">Envoyée le</dt>
                        <dd class="col-sm-8">{{ $demande->envoyee_a?->format('d/m/Y H:i') ?? '-' }}</dd>

                        @if($demande->statut === 'Refusée')
                            <dt class="col-sm-4">Motif de rejet</dt>
                            <dd class="col-sm-8">{{ $demande->motif_refus ?? '-' }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if($demande->statut === 'Attribuée')
                <div class="card stat-card">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-check2-circle text-success me-1"></i>Attribution enregistrée
                    </div>
                    <div class="card-body">
                        @if($demande->programmation)
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Auditoire</dt>
                                <dd class="col-sm-7">{{ $demande->programmation->auditoire?->nom ?? '-' }}</dd>
                                <dt class="col-sm-5">Bâtiment</dt>
                                <dd class="col-sm-7">{{ $demande->programmation->auditoire?->batiment?->nom ?? '-' }}</dd>
                                <dt class="col-sm-5">Horaire</dt>
                                <dd class="col-sm-7">{{ $demande->programmation->heure_debut }} → {{ $demande->programmation->heure_fin }}</dd>
                                <dt class="col-sm-5">Statut</dt>
                                <dd class="col-sm-7">{{ $demande->programmation->statut }}</dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">Demande attribuée mais programmation introuvable.</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="card stat-card mb-4">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-door-open me-1"></i>Attribuer un auditoire</div>
                    <div class="card-body">
                        @php($disponibles = $auditoiresDisponibles)
                        @if($disponibles->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Aucun auditoire disponible pour cette demande (capacité insuffisante ou déjà réservé sur la plage horaire).
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.attributions.store') }}">
                                @csrf
                                <input type="hidden" name="demande_auditoire_id" value="{{ $demande->id }}">
                                <label class="form-label" for="auditoire-select">Auditoires disponibles</label>
                                <select class="form-select mb-3" id="auditoire-select" name="auditoire_id" required>
                                    <option value="">Sélectionner…</option>
                                    @foreach($disponibles as $auditoire)
                                        <option value="{{ $auditoire->id }}">
                                            {{ $auditoire->nom }} · {{ $auditoire->batiment?->nom }} · Capacité {{ $auditoire->capacite }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check2-circle me-1"></i>Attribuer cet auditoire
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card stat-card">
                    <div class="card-header bg-white fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i>Rejeter la demande</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.attributions.rejeter', $demande) }}">
                            @csrf
                            <label class="form-label" for="motif">Motif de rejet <span class="text-danger">*</span></label>
                            <textarea class="form-control mb-3" id="motif" name="motif_refus" rows="3" required placeholder="Précisez le motif du rejet…"></textarea>
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-x-circle me-1"></i>Rejeter la demande
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.attributions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour à la liste
        </a>
    </div>
@endsection
