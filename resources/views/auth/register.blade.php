@extends('layouts.app')

@section('title', 'Créer un compte')

@section('content')
    <div class="card auth-card w-100">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus fs-1 text-primary"></i>
                <h1 class="h4 mt-2">Créer un compte</h1>
                <p class="text-muted mb-0">Choisissez votre rôle pour accéder à l'espace adapté.</p>
            </div>

            <form method="POST" action="{{ route('register.store') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Nom complet</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="role_id">Rôle</label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" data-role="{{ $role->nom }}" @selected(old('role_id') == $role->id)>{{ $role->nom }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 d-none" id="domaine-wrap">
                    <label class="form-label" for="domaine_id">Domaine du Décanat</label>
                    <select class="form-select @error('domaine_id') is-invalid @enderror" id="domaine_id" name="domaine_id">
                        <option value="">Sélectionner</option>
                        @foreach($domaines as $domaine)
                            <option value="{{ $domaine->id }}" @selected(old('domaine_id') == $domaine->id)>{{ $domaine->nom }}</option>
                        @endforeach
                    </select>
                    @error('domaine_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 d-none" id="promotion-wrap">
                    <label class="form-label" for="promotion_id">Promotion</label>
                    <select class="form-select @error('promotion_id') is-invalid @enderror" id="promotion_id" name="promotion_id">
                        <option value="">Sélectionner</option>
                        @foreach($promotions as $promotion)
                            <option value="{{ $promotion->id }}" @selected(old('promotion_id') == $promotion->id)>
                                {{ $promotion->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('promotion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-check2-circle me-1"></i>Créer le compte
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}">J'ai déjà un compte</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const roleSelect = document.getElementById('role_id');
        const domaineWrap = document.getElementById('domaine-wrap');
        const promotionWrap = document.getElementById('promotion-wrap');

        function syncRoleFields() {
            const selected = roleSelect.options[roleSelect.selectedIndex];
            const role = selected ? selected.dataset.role : '';
            domaineWrap.classList.toggle('d-none', role !== 'Décanat');
            promotionWrap.classList.toggle('d-none', role !== 'Étudiant');
        }

        roleSelect.addEventListener('change', syncRoleFields);
        syncRoleFields();
    </script>
@endpush
