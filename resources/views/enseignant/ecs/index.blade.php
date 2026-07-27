@extends('layouts.app')

@section('title', 'Mes EC - Enseignant')
@section('page-title', 'Mes unités d\'enseignement')

@section('content')
@if(!$enseignant)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Votre profil enseignant n'est pas encore configuré. Veuillez contacter l'administrateur.
    </div>
@elseif($ecs->isEmpty())
    <div class="alert alert-info">Aucun EC ne vous est attribué pour le moment.</div>
@else
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">EC qui vous sont attribués</h5>
        <span class="badge bg-primary fs-6">{{ $ecs->count() }} EC</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>UE</th>
                    <th>Volume horaire (h)</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ecs as $ec)
                <tr>
                    <td>{{ $ec->code }}</td>
                    <td>{{ $ec->nom }}</td>
                    <td>{{ $ec->ue->code }} - {{ $ec->ue->nom }}</td>
                    <td>{{ $ec->volume_horaire }}</td>
                    <td>
                        @if($ec->statut === 'Entièrement dispensé')
                            <span class="badge bg-success">{{ $ec->statut }}</span>
                        @elseif($ec->statut === 'En cours')
                            <span class="badge bg-warning text-dark">{{ $ec->statut }}</span>
                        @else
                            <span class="badge bg-secondary">{{ $ec->statut }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
