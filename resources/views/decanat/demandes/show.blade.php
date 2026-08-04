@extends('layouts.app')

@section('title', 'Détail de la demande')
@section('page-title', 'Détail de la demande')

@section('content')
    <div class="card stat-card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">EC</dt>
                <dd class="col-sm-9">{{ $demande->ec?->nom ?? '-' }}</dd>

                <dt class="col-sm-3">Enseignant</dt>
                <dd class="col-sm-9">{{ $demande->enseignant?->nom ?? '-' }}</dd>

                <dt class="col-sm-3">Promotions</dt>
                <dd class="col-sm-9">{{ implode(', ', array_map(fn ($id) => \App\Models\Promotion::find($id)?->nom ?? $id, $demande->promotions_concernees ?? [])) }}</dd>

                <dt class="col-sm-3">Période</dt>
                <dd class="col-sm-9">{{ $demande->date_debut->format('d/m/Y') }} à {{ $demande->date_fin->format('d/m/Y') }}</dd>

                <dt class="col-sm-3">Horaire</dt>
                <dd class="col-sm-9">{{ $demande->heure_debut }} → {{ $demande->heure_fin }}</dd>

                <dt class="col-sm-3">Effectif total</dt>
                <dd class="col-sm-9">{{ $demande->effectif_total }}</dd>

                <dt class="col-sm-3">Statut</dt>
                <dd class="col-sm-9">{{ $demande->statut }}</dd>
            </dl>
        </div>
    </div>
@endsection
