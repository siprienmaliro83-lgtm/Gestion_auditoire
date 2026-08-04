@extends('layouts.app')

@section('title', 'Demandes d\'auditoire')
@section('page-title', 'Demandes d\'auditoire')

@section('content')
    <div class="card stat-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Liste des demandes</span>
            <a href="{{ route('decanat.demandes.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Nouvelle demande
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>EC</th>
                        <th>Enseignant</th>
                        <th>Effectif</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                        <tr>
                            <td>{{ $demande->ec?->nom ?? '-' }}</td>
                            <td>{{ $demande->enseignant?->nom ?? '-' }}</td>
                            <td>{{ $demande->effectif_total }}</td>
                            <td>{{ $demande->date_debut->format('d/m/Y') }} → {{ $demande->date_fin->format('d/m/Y') }}</td>
                            <td>{{ $demande->heure_debut }} → {{ $demande->heure_fin }}</td>
                            <td><span class="badge text-bg-primary">{{ $demande->statut }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('decanat.demandes.show', $demande) }}" class="btn btn-outline-primary btn-sm">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucune demande d’auditoire.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $demandes->links() }}
        </div>
    </div>
@endsection
