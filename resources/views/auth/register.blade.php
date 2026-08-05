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

                <div class="d-none" id="etudiant-wrap">
                    <div class="mb-3">
                        <label class="form-label" for="etudiant_domaine_id">Domaine</label>
                        <select class="form-select" id="etudiant_domaine_id">
                            <option value="">Sélectionner</option>
                            @foreach($domaines as $domaine)
                                <option value="{{ $domaine->id }}">{{ $domaine->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="etudiant_filiere_id">Filière</label>
                        <select class="form-select" id="etudiant_filiere_id" disabled>
                            <option value="">Choisissez d'abord le domaine</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="etudiant_mention_id">Mention</label>
                        <select class="form-select" id="etudiant_mention_id" disabled>
                            <option value="">Choisissez d'abord la filière</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="promotion_id">Promotion</label>
                        <select class="form-select @error('promotion_id') is-invalid @enderror" id="promotion_id" name="promotion_id" disabled>
                            <option value="">Choisissez d'abord la mention</option>
                        </select>
                        @error('promotion_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
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
        const etudiantWrap = document.getElementById('etudiant-wrap');

        function syncRoleFields() {
            const selected = roleSelect.options[roleSelect.selectedIndex];
            const role = selected ? selected.dataset.role : '';
            domaineWrap.classList.toggle('d-none', role !== 'Décanat');
            etudiantWrap.classList.toggle('d-none', role !== 'Étudiant');
        }

        const etudiantDomaine = document.getElementById('etudiant_domaine_id');
        const etudiantFiliere = document.getElementById('etudiant_filiere_id');
        const etudiantMention = document.getElementById('etudiant_mention_id');
        const etudiantPromotion = document.getElementById('promotion_id');

        function resetSelect(select, placeholder) {
            select.innerHTML = '<option value="">' + placeholder + '</option>';
            select.disabled = true;
        }

        async function loadOptions(url, target, placeholder) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                resetSelect(target, placeholder);
                if (data.length) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.text = (item.code ? item.code + ' - ' : '') + item.nom;
                        target.appendChild(option);
                    });
                    target.disabled = false;
                }
            } catch (e) {
                resetSelect(target, placeholder);
            }
        }

        etudiantDomaine.addEventListener('change', function () {
            resetSelect(etudiantFiliere, 'Aucune filière');
            resetSelect(etudiantMention, 'Choisissez d\'abord la filière');
            resetSelect(etudiantPromotion, 'Choisissez d\'abord la mention');
            const domaineId = this.value;
            if (!domaineId) return;
            loadOptions('{{ route('api.filieres') }}?domaine_id=' + domaineId, etudiantFiliere, 'Aucune filière');
        });

        etudiantFiliere.addEventListener('change', function () {
            resetSelect(etudiantMention, 'Aucune mention');
            resetSelect(etudiantPromotion, 'Choisissez d\'abord la mention');
            const filiereId = this.value;
            if (!filiereId) return;
            loadOptions('{{ route('api.mentions') }}?filiere_id=' + filiereId, etudiantMention, 'Aucune mention');
        });

        etudiantMention.addEventListener('change', function () {
            resetSelect(etudiantPromotion, 'Aucune promotion');
            const mentionId = this.value;
            if (!mentionId) return;
            loadOptions('{{ route('api.promotions') }}?mention_id=' + mentionId, etudiantPromotion, 'Aucune promotion');
        });

        roleSelect.addEventListener('change', syncRoleFields);
        syncRoleFields();
    </script>
@endpush
