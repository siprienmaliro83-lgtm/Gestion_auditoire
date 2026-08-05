@extends('layouts.app')

@section('title', 'Mon horaire - Enseignant')
@section('page-title', 'Mon horaire de cours')

@section('content')
@if(!$enseignant)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Votre profil enseignant n'est pas encore configuré. Veuillez contacter l'administrateur.
    </div>
@elseif($programmations->isEmpty())
    <div class="alert alert-info">Aucune programmation ne vous est assignée pour le moment.</div>
@else
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Vos cours programmés</h5>
        <span class="badge bg-primary fs-6">{{ $programmations->total() }} programmation(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Heure début</th>
                    <th>Heure fin</th>
                    <th>EC</th>
                    <th>UE</th>
                    <th>Promotion(s)</th>
                    <th>Auditoire</th>
                    <th>Bâtiment</th>
                    <th>Volume horaire</th>
                </tr>
            </thead>
            <tbody>
                @foreach($programmations as $prog)
                <tr>
                    <td>{{ $prog->date_debut->format('d/m/Y') }}</td>
                    <td>{{ substr($prog->heure_debut, 0, 5) }}</td>
                    <td>{{ substr($prog->heure_fin, 0, 5) }}</td>
                    <td>
                        <div class="fw-semibold">{{ $prog->ec->nom }}</div>
                        <small class="text-muted">{{ $prog->ec->code }}</small>
                    </td>
                    <td>{{ $prog->ec->ue->code }} - {{ $prog->ec->ue->nom }}</td>
                    <td>{{ $prog->promotionsNom }}</td>
                    <td>{{ $prog->auditoire->nom }}</td>
                    <td>{{ $prog->auditoire->batiment->nom }}</td>
                    <td>{{ $prog->ec->volume_horaire }} h</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $programmations->links() }}
@endif
@endsection
