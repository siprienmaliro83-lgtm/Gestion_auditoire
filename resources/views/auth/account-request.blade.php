@extends('layouts.app')

@section('title', 'Demander un compte')
@section('page-title', 'Demander un compte')

@php($decanatRoleId = $roles->firstWhere('nom', 'Décanat')?->id)

@section('content')
    <div class="card auth-card w-100">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus fs-1 text-primary"></i>
                <h1 class="h4 mt-2">Demander un compte</h1>
                <p class="text-muted mb-0">Compte Décanat ou Administrateur — validation par le Super Administrateur</p>
            </div>

            <form method="POST" action="{{ route('account.request.store') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Nom complet</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="role_id">Type de compte</label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->nom }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div id="decanat-fields" class="{{ old('role_id') == $decanatRoleId ? '' : 'd-none' }} border rounded p-3 mb-3 bg-light">
                    <div class="small text-muted mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-diagram-3"></i> Rattaché au Domaine <i class="bi bi-arrow-down"></i> Filière <i class="bi bi-arrow-down"></i> Mention
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="domaine_id">Domaine</label>
                        <select class="form-select @error('domaine_id') is-invalid @enderror" id="domaine_id" name="domaine_id">
                            <option value="">Sélectionner un Domaine</option>
                            @foreach($domaines as $domaine)
                                <option value="{{ $domaine->id }}" @selected(old('domaine_id') == $domaine->id)>{{ $domaine->nom }}</option>
                            @endforeach
                        </select>
                        @error('domaine_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="filiere_id">Filière</label>
                        <select class="form-select @error('filiere_id') is-invalid @enderror" id="filiere_id" name="filiere_id" disabled>
                            <option value="">Sélectionner une Filière</option>
                            @foreach($filieres as $filiere)
                                <option value="{{ $filiere->id }}" @selected(old('filiere_id') == $filiere->id)>{{ trim($filiere->code.' '.$filiere->nom) }}</option>
                            @endforeach
                        </select>
                        @error('filiere_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="mention_id">Mention</label>
                        <select class="form-select @error('mention_id') is-invalid @enderror" id="mention_id" name="mention_id" disabled>
                            <option value="">Sélectionner une Mention</option>
                            @foreach($mentions as $mention)
                                <option value="{{ $mention->id }}" @selected(old('mention_id') == $mention->id)>{{ trim($mention->code.' '.$mention->nom) }}</option>
                            @endforeach
                        </select>
                        @error('mention_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <i class="bi bi-send me-1"></i>Envoyer la demande
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-muted">J'ai déjà un compte</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const decanatRoleId = @json($decanatRoleId);
        const roleSelect = document.getElementById('role_id');
        const fields = document.getElementById('decanat-fields');
        const domaineSelect = document.getElementById('domaine_id');
        const filiereSelect = document.getElementById('filiere_id');
        const mentionSelect = document.getElementById('mention_id');

        const emptyOption = (label) => { const o = document.createElement('option'); o.value = ''; o.textContent = label; return o; };

        function setRequired(select, required) {
            select.required = required;
            if (!required) select.value = '';
        }

        function toggleDecanatFields() {
            const isDecanat = Number(roleSelect.value) === Number(decanatRoleId);
            fields.classList.toggle('d-none', !isDecanat);
            filiereSelect.disabled = !isDecanat;
            mentionSelect.disabled = !isDecanat;
            setRequired(domaineSelect, isDecanat);
            setRequired(filiereSelect, isDecanat);
            setRequired(mentionSelect, isDecanat);
            if (!isDecanat) {
                domaineSelect.value = '';
                filiereSelect.innerHTML = '';
                filiereSelect.appendChild(emptyOption('Sélectionner une Filière'));
                mentionSelect.innerHTML = '';
                mentionSelect.appendChild(emptyOption('Sélectionner une Mention'));
            }
        }

        async function loadFilieres() {
            filiereSelect.innerHTML = '';
            filiereSelect.appendChild(emptyOption('Sélectionner une Filière'));
            mentionSelect.innerHTML = '';
            mentionSelect.appendChild(emptyOption('Sélectionner une Mention'));
            mentionSelect.disabled = true;
            if (!domaineSelect.value) {
                filiereSelect.disabled = true;
                return;
            }
            filiereSelect.disabled = true;
            try {
                const res = await fetch('/api/filieres?domaine_id=' + encodeURIComponent(domaineSelect.value));
                const filieres = await res.json();
                filiereSelect.disabled = false;
                filieres.forEach(f => {
                    const o = document.createElement('option');
                    o.value = f.id;
                    o.textContent = (f.code ? f.code + ' ' : '') + f.nom;
                    filiereSelect.appendChild(o);
                });
            } catch (e) {
                filiereSelect.disabled = false;
            }
        }

        async function loadMentions() {
            mentionSelect.innerHTML = '';
            mentionSelect.appendChild(emptyOption('Sélectionner une Mention'));
            if (!filiereSelect.value) {
                mentionSelect.disabled = true;
                return;
            }
            mentionSelect.disabled = true;
            try {
                const res = await fetch('/api/mentions?filiere_id=' + encodeURIComponent(filiereSelect.value));
                const mentions = await res.json();
                mentionSelect.disabled = false;
                mentions.forEach(m => {
                    const o = document.createElement('option');
                    o.value = m.id;
                    o.textContent = (m.code ? m.code + ' ' : '') + m.nom;
                    mentionSelect.appendChild(o);
                });
            } catch (e) {
                mentionSelect.disabled = false;
            }
        }

        roleSelect.addEventListener('change', toggleDecanatFields);
        domaineSelect.addEventListener('change', loadFilieres);
        filiereSelect.addEventListener('change', loadMentions);

        toggleDecanatFields();
    })();
</script>
@endpush
