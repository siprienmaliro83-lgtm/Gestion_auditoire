@extends('layouts.app')

@section('title', 'Attribution des auditoires')
@section('page-title', 'Attribution des auditoires')

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
        @forelse($demandes as $demande)
            <div class="col-lg-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="fw-semibold">{{ $demande->ec?->nom }}</div>
                                <div class="small text-muted">{{ $demande->enseignant?->nom }} · {{ $demande->date_debut->format('d/m/Y') }} · {{ $demande->heure_debut }} - {{ $demande->heure_fin }}</div>
                            </div>
                            <span class="badge text-bg-{{ $demande->statut === 'Attribuée' ? 'success' : ($demande->statut === 'Acceptée' ? 'info' : 'warning') }}">{{ $demande->statut }}</span>
                        </div>

                        <form method="POST" action="{{ route('admin.attributions.store') }}">
                            @csrf
                            <input type="hidden" name="demande_auditoire_id" value="{{ $demande->id }}">

                            <div class="mb-3">
                                <label class="form-label" for="auditoire_{{ $demande->id }}">Auditoire</label>
                                <select class="form-select" id="auditoire_{{ $demande->id }}" name="auditoire_id" required>
                                    <option value="">Sélectionner</option>
                                    @foreach($auditoires as $auditoire)
                                        <option value="{{ $auditoire->id }}">{{ $auditoire->nom }} · {{ $auditoire->batiment?->nom }} · Capacité {{ $auditoire->capacite }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <input type="hidden" name="statut" value="Validée">

                            @if($demande->statut !== 'Attribuée')
                                <button type="submit" class="btn btn-primary w-100">Attribuer</button>
                            @else
                                <button type="button" class="btn btn-outline-success w-100" disabled>Déjà attribuée</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">Aucune demande à traiter.</div>
            </div>
        @endforelse
    </div>
@endsection
