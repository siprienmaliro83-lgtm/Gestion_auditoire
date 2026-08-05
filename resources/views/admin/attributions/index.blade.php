@extends('layouts.app')

@section('title', 'Attribution des auditoires')
@section('page-title', 'Gestion des demandes d\'auditoire')

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card stat-card">
        <div class="card-header bg-white fw-semibold">Liste des demandes</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>EC</th>
                        <th>Enseignant</th>
                        <th>Promotions</th>
                        <th>Effectif</th>
                        <th>Période</th>
                        <th>Horaire</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $demande->ec?->nom ?? '-' }}</div>
                                <small class="text-muted">{{ $demande->ec?->code ?? '' }}</small>
                            </td>
                            <td>{{ $demande->enseignant?->nom ?? '-' }}</td>
                            <td><small>{{ $demande->promotionsNom }}</small></td>
                            <td>{{ $demande->effectif_total }}</td>
                            <td>{{ $demande->date_debut->format('d/m/Y') }} → {{ $demande->date_fin->format('d/m/Y') }}</td>
                            <td>{{ $demande->heure_debut }} → {{ $demande->heure_fin }}</td>
                            <td>
                                @if($demande->statut === 'Attribuée')
                                    <span class="badge text-bg-success">{{ $demande->statut }}</span>
                                @elseif($demande->statut === 'Acceptée')
                                    <span class="badge text-bg-info">{{ $demande->statut }}</span>
                                @else
                                    <span class="badge text-bg-warning text-dark">{{ $demande->statut }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.attributions.show', $demande) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Voir
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal" data-bs-target="#attribuer-{{ $demande->id }}"
                                            @if($demande->statut === 'Attribuée') disabled @endif>
                                        <i class="bi bi-door-open me-1"></i>Attribuer
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#rejeter-{{ $demande->id }}"
                                            @if(in_array($demande->statut, ['Attribuée', 'Refusée'], true)) disabled @endif>
                                        <i class="bi bi-x-circle me-1"></i>Rejeter
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal : attribuer un auditoire --}}
                        <div class="modal fade" id="attribuer-{{ $demande->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.attributions.store') }}">
                                        @csrf
                                        <input type="hidden" name="demande_auditoire_id" value="{{ $demande->id }}">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Attribuer un auditoire</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted mb-2">
                                                {{ $demande->ec?->nom }} — {{ $demande->enseignant?->nom }}<br>
                                                {{ $demande->date_debut->format('d/m/Y') }} · {{ $demande->heure_debut }} → {{ $demande->heure_fin }}
                                            </p>
                                            @php($disponibles = $auditoiresDisponibles[$demande->id] ?? collect())
                                            @if($disponibles->isEmpty())
                                                <div class="alert alert-warning mb-0">
                                                    Aucun auditoire disponible pour cette demande (capacité insuffisante ou déjà réservé sur la plage horaire).
                                                </div>
                                            @else
                                                <label class="form-label" for="auditoire-select-{{ $demande->id }}">Auditoires disponibles</label>
                                                <select class="form-select" id="auditoire-select-{{ $demande->id }}" name="auditoire_id" required>
                                                    <option value="">Sélectionner…</option>
                                                    @foreach($disponibles as $auditoire)
                                                        <option value="{{ $auditoire->id }}">
                                                            {{ $auditoire->nom }} · {{ $auditoire->batiment?->nom }} · Capacité {{ $auditoire->capacite }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary" @if($disponibles->isEmpty()) disabled @endif>
                                                <i class="bi bi-check2-circle me-1"></i>Attribuer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Modal : rejeter la demande --}}
                        <div class="modal fade" id="rejeter-{{ $demande->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.attributions.rejeter', $demande) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Rejeter la demande</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted mb-2">
                                                {{ $demande->ec?->nom }} — {{ $demande->enseignant?->nom }}
                                            </p>
                                            <label class="form-label" for="motif-{{ $demande->id }}">Motif de rejet <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="motif-{{ $demande->id }}" name="motif_refus" rows="3" required placeholder="Précisez le motif du rejet…"></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i>Rejeter</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucune demande à traiter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $demandes->links() }}
        </div>
    </div>
@endsection
