@extends('layouts.app')

@section('title', 'Mon programme - Étudiant')
@section('page-title', 'Mon programme')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Programme de ma promotion</h5>
    @if($promotion)
        <span class="badge bg-primary fs-6">{{ $promotion->nom }} — {{ $promotion->mention->nom ?? '' }}</span>
    @endif
</div>

@if($programmations->isEmpty())
    <div class="alert alert-info">Aucune programmation pour votre promotion pour le moment.</div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>EC</th>
                    <th>UE</th>
                    <th>Enseignant</th>
                    <th>Auditoire</th>
                    <th>Bâtiment</th>
                    <th>Date</th>
                    <th>Horaire</th>
                </tr>
            </thead>
            <tbody>
                @foreach($programmations as $prog)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $prog->ec->nom }}</div>
                        <small class="text-muted">{{ $prog->ec->code }}</small>
                    </td>
                    <td>{{ $prog->ec->ue->code }} - {{ $prog->ec->ue->nom }}</td>
                    <td>{{ $prog->enseignant->prenom }} {{ $prog->enseignant->nom }}</td>
                    <td>{{ $prog->auditoire->nom }}</td>
                    <td>{{ $prog->auditoire->batiment->nom }}</td>
                    <td>{{ $prog->date_debut->format('d/m/Y') }}</td>
                    <td>{{ substr($prog->heure_debut, 0, 5) }} - {{ substr($prog->heure_fin, 0, 5) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $programmations->links() }}
@endif
@endsection
