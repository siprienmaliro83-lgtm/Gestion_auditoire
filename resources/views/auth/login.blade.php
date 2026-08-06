@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
    <div class="card auth-card w-100">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-building-check fs-1 text-primary"></i>
                <h1 class="h4 mt-2">Connexion</h1>
                <p class="text-muted mb-0">Gestion d'attribution des auditoires universitaires</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Identifiant</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="text" value="{{ old('email') }}" placeholder="Email, matricule ou nom" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('account.request') }}">Demander un compte</a>
            </div>
        </div>
    </div>
@endsection
