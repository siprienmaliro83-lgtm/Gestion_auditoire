@extends('layouts.app')

@section('title', 'Horaires - Décanat')
@section('page-title', 'Horaires des programmations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Programmations de mon domaine</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('decanat.export.pdf') }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="{{ route('decanat.export.excel') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer me-1"></i>Imprimer
        </button>
    </div>
</div>

@if($programmations->isEmpty())
    <div class="alert alert-info">Aucune programmation pour votre domaine.</div>
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
                    <th>Effectif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($programmations as $prog)
                <tr>
                    <td>{{ $prog->ec->nom }}</td>
                    <td>{{ $prog->ec->ue->code }} - {{ $prog->ec->ue->nom }}</td>
                    <td>{{ $prog->enseignant->prenom }} {{ $prog->enseignant->nom }}</td>
                    <td>{{ $prog->auditoire->nom }}</td>
                    <td>{{ $prog->auditoire->batiment->nom }}</td>
                    <td>{{ $prog->date_debut->format('d/m/Y') }}</td>
                    <td>{{ substr($prog->heure_debut, 0, 5) }} - {{ substr($prog->heure_fin, 0, 5) }}</td>
                    <td>{{ $prog->effectif_total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $programmations->links() }}
@endif

<style>
    @media print {
        .sidebar, .navbar, .btn, .d-flex.gap-2 { display: none !important; }
        .content { margin: 0 !important; padding: 0 !important; }
        .container-fluid { padding: 10px !important; }
    }
</style>
@endsection
