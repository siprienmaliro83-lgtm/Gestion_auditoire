@extends('layouts.app')

@section('title', 'Ma promotion - Étudiant')
@section('page-title', 'Ma promotion')

@section('content')
@if($promotion)
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Promotion</h6>
                    <h4>{{ $promotion->nom }}</h4>
                    <p class="text-muted mb-0">Code : {{ $promotion->code }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Niveau</h6>
                    <h4>{{ $promotion->niveau }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Effectif</h6>
                    <h4>{{ $promotion->effectif }} étudiants</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Mention</h6>
                    <h4>{{ $promotion->mention->nom ?? '-' }}</h4>
                    <p class="text-muted mb-0">Filière : {{ $promotion->mention->filiere->nom ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Domaine</h6>
                    <h4>{{ $promotion->mention->filiere->domaine->nom ?? '-' }}</h4>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Aucune promotion associée à votre compte. Veuillez contacter l'administrateur.
    </div>
@endif
@endsection
