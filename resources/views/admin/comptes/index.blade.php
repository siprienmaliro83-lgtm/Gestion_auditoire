@extends('layouts.app')

@section('title', 'Comptes')
@section('page-title', 'Comptes Décanat & Administrateur')

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-hourglass-split"></i> Demandes de comptes en attente
            @if($pending->isNotEmpty())
                <span class="badge bg-warning text-dark ms-auto">{{ $pending->count() }}</span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($pending->isEmpty())
                <p class="text-muted p-4 mb-0">Aucune demande de compte en attente.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>E-mail</th>
                                <th>Rôle demandé</th>
                                <th>Domaine / Filière / Mention</th>
                                <th>Demandé le</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role?->nom }}</td>
                                    <td>
                                        @if($user->domaine)
                                            {{ $user->domaine->nom }}<br>
                                            <small class="text-muted">{{ $user->filiere?->nom ?? '—' }} / {{ $user->mention?->nom ?? '—' }}</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end text-nowrap">
                                        <form class="d-inline" method="POST" action="{{ route('admin.comptes.approuver', $user->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success" title="Approuver" type="submit"><i class="bi bi-check-lg"></i> Approuver</button>
                                        </form>
                                        <form class="d-inline" method="POST" action="{{ route('admin.comptes.refuser', $user->id) }}"
                                              onsubmit="return confirm('Refuser la demande de « {{ addslashes($user->name) }} » ?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" title="Refuser" type="submit"><i class="bi bi-x-lg"></i> Refuser</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-people me-1"></i> Tous les comptes
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>E-mail</th>
                                <th>Rôle</th>
                                <th>Domaine / Filière / Mention</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role?->nom }}</td>
                                    <td>
                                        @if($user->domaine)
                                            {{ $user->domaine->nom }}<br>
                                            <small class="text-muted">{{ $user->filiere?->nom ?? '—' }} / {{ $user->mention?->nom ?? '—' }}</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                <td>
                                    @if($user->confirme)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Désactivé</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($user->id !== auth()->id())
                                        @if($user->confirme)
                                            <form class="d-inline" method="POST" action="{{ route('admin.comptes.desactiver', $user->id) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-secondary" title="Désactiver" type="submit"><i class="bi bi-pause-circle"></i></button>
                                            </form>
                                        @else
                                            <form class="d-inline" method="POST" action="{{ route('admin.comptes.activer', $user->id) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" title="Activer" type="submit"><i class="bi bi-play-circle"></i></button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
