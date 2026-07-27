@extends('layouts.app')

@section('title', 'Détail de la demande')
@section('page-title', 'Demande #'.$demande->id)

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span>Détails de la demande</span>
                @if($demande->statut === 'En attente')
                    <span class="badge bg-warning text-dark">{{ $demande->statut }}</span>
                @elseif($demande->statut === 'Acceptée')
                    <span class="badge bg-info">{{ $demande->statut }}</span>
                @elseif($demande->statut === 'Attribuée')
                    <span class="badge bg-success">{{ $demande->statut }}</span>
                @else
                    <span class="badge bg-danger">{{ $demande->statut }}</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">EC</h6>
                        <p class="fw-semibold mb-0">{{ $demande->ec->nom }}</p>
                        <small class="text-muted">{{ $demande->ec->code }} — UE: {{ $demande->ec->ue?->nom ?? '-' }}</small>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Enseignant</h6>
                        <p class="fw-semibold mb-0">{{ $demande->enseignant->prenom }} {{ $demande->enseignant->nom }}</p>
                        <small class="text-muted">{{ $demande->enseignant->email }}</small>
                    </div>
                    <div class="col-md-12">
                        <h6 class="text-muted mb-1">Promotions concernées</h6>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($demande->promotions_concernees as $pid)
                                @php($promo = \App\Models\Promotion::with('mention')->find($pid))
                                @if($promo)
                                    <span class="badge bg-primary">{{ $promo->nom }} <small>({{ $promo->mention->nom ?? '' }})</small></span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Date début</h6>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Date fin</h6>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Horaire</h6>
                        <p class="mb-0">{{ substr($demande->heure_debut, 0, 5) }} - {{ substr($demande->heure_fin, 0, 5) }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Effectif total</h6>
                        <p class="mb-0 fw-semibold">{{ $demande->effectif_total }}</p>
                    </div>
                    @if($demande->motif_refus)
                        <div class="col-md-12">
                            <h6 class="text-danger mb-1">Motif du refus</h6>
                            <div class="alert alert-danger mb-0">{{ $demande->motif_refus }}</div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Demandé par</h6>
                        <p class="mb-0">{{ $demande->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Envoyée le</h6>
                        <p class="mb-0">{{ $demande->envoyee_a ? $demande->envoyee_a->format('d/m/Y H:i') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('decanat.demandes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
