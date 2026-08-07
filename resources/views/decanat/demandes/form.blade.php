@extends('layouts.app')

@section('title', 'Nouvelle demande d\'auditoire')
@section('page-title', 'Nouvelle demande d\'auditoire')

@section('content')
    @unless($anneeActive)
        <div class="alert alert-warning">
            <strong>Aucune année académique active.</strong>
            Créez et activez une année académique avant de créer une demande d'auditoire.
        </div>
    @endunless

    <div class="card stat-card">
        <div class="card-body">
            <form method="POST" action="{{ route('decanat.demandes.store') }}" novalidate>
                @csrf

                @error('annee_academique')<div class="alert alert-danger">{{ $message }}</div>@enderror

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="ec_id">EC</label>
                        <select class="form-select @error('ec_id') is-invalid @enderror" id="ec_id" name="ec_id">
                            <option value="">Sélectionner</option>
                            @foreach($ecs as $ec)
                                <option value="{{ $ec->id }}" @selected(old('ec_id') == $ec->id)>{{ $ec->nom }} ({{ $ec->ue?->nom ?? '' }})</option>
                            @endforeach
                        </select>
                        @error('ec_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="enseignant_id">Enseignant</label>
                        <select class="form-select @error('enseignant_id') is-invalid @enderror" id="enseignant_id" name="enseignant_id">
                            <option value="">Sélectionner</option>
                            @foreach($enseignants as $enseignant)
                                <option value="{{ $enseignant->id }}" @selected(old('enseignant_id') == $enseignant->id)>
                                    {{ trim($enseignant->prenom.' '.$enseignant->nom) }}@if($enseignant->grade) ({{ $enseignant->grade }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('enseignant_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Promotions concernées</label>
                        <div class="row g-2">
                            @foreach($promotions as $promotion)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="promotions_concernees[]" value="{{ $promotion->id }}" id="promotion_{{ $promotion->id }}" @checked(in_array((string)$promotion->id, old('promotions_concernees', [])))>
                                        <label class="form-check-label" for="promotion_{{ $promotion->id }}">{{ $promotion->nom }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('promotions_concernees')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="date_debut">Date début</label>
                        <input class="form-control @error('date_debut') is-invalid @enderror" type="date" id="date_debut" name="date_debut" value="{{ old('date_debut') }}">
                        @error('date_debut')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="date_fin">Date fin</label>
                        <input class="form-control @error('date_fin') is-invalid @enderror" type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}">
                        @error('date_fin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="heure_debut">Heure début</label>
                        <input class="form-control @error('heure_debut') is-invalid @enderror" type="time" id="heure_debut" name="heure_debut" value="{{ old('heure_debut') }}">
                        @error('heure_debut')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="heure_fin">Heure fin</label>
                        <input class="form-control @error('heure_fin') is-invalid @enderror" type="time" id="heure_fin" name="heure_fin" value="{{ old('heure_fin') }}">
                        @error('heure_fin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="effectif_total">Effectif total</label>
                        <input class="form-control @error('effectif_total') is-invalid @enderror" type="number" id="effectif_total" name="effectif_total" value="{{ old('effectif_total') }}">
                        @error('effectif_total')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('decanat.demandes.index') }}">Annuler</a>
                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
@endsection
