@extends('layouts.app')

@section('title', 'Attribution des auditoires')
@section('page-title', 'Attribution des auditoires')

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

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">Demandes en attente</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>EC</th>
                            <th>Enseignant</th>
                            <th>Décanat</th>
                            <th>Date</th>
                            <th>Horaire</th>
                            <th>Effectif</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demandes as $demande)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $demande->ec->nom }}</div>
                                    <small class="text-muted">{{ $demande->ec->ue->code ?? '' }}</small>
                                </td>
                                <td>{{ $demande->enseignant->prenom }} {{ $demande->enseignant->nom }}</td>
                                <td>{{ $demande->user->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}</td>
                                <td>{{ substr($demande->heure_debut, 0, 5) }} - {{ substr($demande->heure_fin, 0, 5) }}</td>
                                <td>{{ $demande->effectif_total }}</td>
                                <td>
                                    @if($demande->statut === 'En attente')
                                        <span class="badge bg-warning text-dark">{{ $demande->statut }}</span>
                                    @elseif($demande->statut === 'Acceptée')
                                        <span class="badge bg-info">{{ $demande->statut }}</span>
                                    @elseif($demande->statut === 'Attribuée')
                                        <span class="badge bg-success">{{ $demande->statut }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $demande->statut }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($demande->statut === 'En attente')
                                            <form method="POST" action="{{ route('admin.attributions.accepter', $demande) }}" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button class="btn btn-outline-success btn-sm" title="Accepter"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                            <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#refusModal{{ $demande->id }}" title="Refuser">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <!-- Modal Refus -->
                                            <div class="modal fade" id="refusModal{{ $demande->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('admin.attributions.refuser', $demande) }}">
                                                        @csrf @method('PATCH')
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Refuser la demande</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <label class="form-label">Motif du refus</label>
                                                                <textarea class="form-control" name="motif_refus" required rows="3" placeholder="Entrez le motif du refus..."></textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-danger">Refuser</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                        @if($demande->statut === 'Acceptée')
                                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#attrModal{{ $demande->id }}">
                                                <i class="bi bi-door-open"></i> Attribuer
                                            </button>
                                            <!-- Modal Attribution -->
                                            <div class="modal fade" id="attrModal{{ $demande->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('admin.attributions.store') }}">
                                                        @csrf
                                                        <input type="hidden" name="demande_auditoire_id" value="{{ $demande->id }}">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Attribuer un auditoire</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <strong>{{ $demande->ec->nom }}</strong> — {{ $demande->enseignant->prenom }} {{ $demande->enseignant->nom }}<br>
                                                                    Effectif: {{ $demande->effectif_total }}
                                                                </div>
                                                                <label class="form-label">Auditoire</label>
                                                                <select class="form-select" name="auditoire_id" required>
                                                                    <option value="">Sélectionner un auditoire</option>
                                                                    @foreach($auditoires as $aud)
                                                                        <option value="{{ $aud->id }}" @if($aud->capacite < $demande->effectif_total) disabled @endif>
                                                                            {{ $aud->nom }} ({{ $aud->batiment->nom }}) — Capacité: {{ $aud->capacite }} {{ $aud->capacite < $demande->effectif_total ? '⚠ insuffisant' : '' }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-primary">Attribuer</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-muted py-4" colspan="8">Aucune demande en attente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">Auditoires disponibles</div>
            <div class="list-group list-group-flush">
                @forelse($auditoires as $aud)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $aud->nom }}</div>
                            <small class="text-muted">{{ $aud->batiment->nom }}</small>
                        </div>
                        <span class="badge bg-primary">{{ $aud->capacite }}</span>
                    </div>
                @empty
                    <div class="list-group-item text-muted">Aucun auditoire disponible.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
