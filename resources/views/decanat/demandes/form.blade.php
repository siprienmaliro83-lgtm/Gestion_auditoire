@extends('layouts.app')

@section('title', 'Nouvelle demande d\'auditoire')
@section('page-title', 'Nouvelle demande d\'auditoire')

@section('content')
    <div class="card stat-card">
        <div class="card-body">
            <form method="POST" action="{{ route('decanat.demandes.store') }}" novalidate>
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="ec_id">EC <span class="text-danger">*</span></label>
                        <select class="form-select @error('ec_id') is-invalid @enderror" id="ec_id" name="ec_id" required>
                            <option value="">Sélectionner un EC</option>
                            @php($grouped = $ecs->groupBy(fn($ec) => $ec->ue?->nom ?? 'Sans UE'))
                            @foreach($grouped as $ueNom => $ecsUe)
                                <optgroup label="{{ $ueNom }}">
                                    @foreach($ecsUe as $ec)
                                        <option value="{{ $ec->id }}" data-enseignants="{{ json_encode($ec->enseignants->pluck('id')->toArray()) }}" @selected(old('ec_id') == $ec->id)>
                                            {{ $ec->code }} - {{ $ec->nom }} ({{ $ec->volume_horaire }}h)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('ec_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="enseignant_id">Enseignant <span class="text-danger">*</span></label>
                        <select class="form-select @error('enseignant_id') is-invalid @enderror" id="enseignant_id" name="enseignant_id" required>
                            <option value="">Sélectionner un enseignant</option>
                            @foreach($enseignants as $ens)
                                <option value="{{ $ens->id }}" data-ecs="{{ json_encode($ens->ecs->pluck('id')->toArray()) }}" @selected(old('enseignant_id') == $ens->id)>
                                    {{ $ens->nom }} {{ $ens->prenom }} ({{ $ens->grade }})
                                </option>
                            @endforeach
                        </select>
                        @error('enseignant_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Promotions concernées <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            @foreach($promotions as $promotion)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="promotions_concernees[]" value="{{ $promotion->id }}" id="promotion_{{ $promotion->id }}" @checked(in_array((string)$promotion->id, old('promotions_concernees', [])))>
                                        <label class="form-check-label" for="promotion_{{ $promotion->id }}">
                                            {{ $promotion->nom }}<br><small class="text-muted">{{ $promotion->mention->nom ?? '' }}</small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('promotions_concernees')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="date_debut">Date début <span class="text-danger">*</span></label>
                        <input class="form-control @error('date_debut') is-invalid @enderror" type="date" id="date_debut" name="date_debut" value="{{ old('date_debut') }}" required>
                        @error('date_debut')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="date_fin">Date fin <span class="text-danger">*</span></label>
                        <input class="form-control @error('date_fin') is-invalid @enderror" type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}" required>
                        @error('date_fin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="heure_debut">Heure début <span class="text-danger">*</span></label>
                        <input class="form-control @error('heure_debut') is-invalid @enderror" type="time" id="heure_debut" name="heure_debut" value="{{ old('heure_debut') }}" required>
                        @error('heure_debut')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="heure_fin">Heure fin <span class="text-danger">*</span></label>
                        <input class="form-control @error('heure_fin') is-invalid @enderror" type="time" id="heure_fin" name="heure_fin" value="{{ old('heure_fin') }}" required>
                        @error('heure_fin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="effectif_total">Effectif total <span class="text-danger">*</span></label>
                        <input class="form-control @error('effectif_total') is-invalid @enderror" type="number" id="effectif_total" name="effectif_total" value="{{ old('effectif_total') }}" min="1" required>
                        @error('effectif_total')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('decanat.demandes.index') }}">Annuler</a>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-send me-1"></i>Envoyer la demande</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ecSelect = document.getElementById('ec_id');
    const ensSelect = document.getElementById('enseignant_id');

    ecSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const ecEnseignantIds = JSON.parse(selected.dataset.enseignants || '[]');

        for (const opt of ensSelect.options) {
            if (opt.value === '') continue;
            const ensEcs = JSON.parse(opt.dataset.ecs || '[]');
            opt.hidden = ecEnseignantIds.length > 0 && !ecEnseignantIds.includes(parseInt(opt.value));
            opt.disabled = opt.hidden;
        }
        ensSelect.value = '';
    });
});
</script>
@endpush
