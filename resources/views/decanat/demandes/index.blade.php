@extends('layouts.app')

@section('title', 'Mes demandes d\'auditoire')
@section('page-title', 'Mes demandes d\'auditoire')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Mes demandes</h5>
    <a href="{{ route('decanat.demandes.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Nouvelle demande
    </a>
</div>

@if($demandes->isEmpty())
    <div class="alert alert-info">Aucune demande créée pour le moment.</div>
@else
    <div class="card stat-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>EC</th>
                        <th>Enseignant</th>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Effectif</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demandes as $demande)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $demande->ec->nom }}</div>
                            <small class="text-muted">{{ $demande->ec->ue?->code ?? '' }}</small>
                        </td>
                        <td>{{ $demande->enseignant->prenom }} {{ $demande->enseignant->nom }}</td>
                        <td>{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}</td>
                        <td>{{ substr($demande->heure_debut, 0, 5) }} - {{ substr($demande->heure_fin, 0, 5) }}</td>
                        <td>{{ $demande->effectif_total }}</td>
                        <td>
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
                        <td>
                            <a href="{{ route('decanat.demandes.show', $demande) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $demandes->links() }}
        </div>
    </div>
@endif
@endsection
