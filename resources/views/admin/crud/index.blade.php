@extends('layouts.app')

@section('title', $config['title'])
@section('page-title', $config['title'])

@section('content')
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <form class="d-flex gap-2" method="GET">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Recherche">
            <button class="btn btn-outline-primary" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </form>
            @unless($resource === 'demandes')
                <a class="btn btn-primary" href="{{ route('admin.crud.create', $resource) }}">
                    <i class="bi bi-plus-circle me-1"></i>Ajouter
                </a>
            @endunless
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

    <div class="card stat-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    @foreach($config['columns'] as $column)
                        <th>{{ str_replace(['_', '.'], [' ', ' / '], $column) }}</th>
                    @endforeach
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        @foreach($config['columns'] as $column)
                            @php($value = data_get($item, $column))
                            <td>
                                @if(is_bool($value))
                                    <span class="badge text-bg-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'Oui' : 'Non' }}</span>
                                @elseif($value instanceof \Carbon\CarbonInterface)
                                    {{ $value->format('d/m/Y') }}
                                @else
                                    {{ $value ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @if($resource === 'users')
                                    <form method="POST" action="{{ route('admin.users.confirmer', $item->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $item->confirme ? 'btn-outline-warning' : 'btn-outline-success' }}" type="submit" title="{{ $item->confirme ? 'Déconfirmer' : 'Confirmer' }}">
                                            <i class="bi {{ $item->confirme ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                                        </button>
                                    </form>
                                @endif
                                <a class="btn btn-outline-primary" href="{{ route('admin.crud.edit', [$resource, $item->id]) }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.crud.destroy', [$resource, $item->id]) }}" onsubmit="return confirm('Confirmer la suppression ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-muted py-4" colspan="{{ count($config['columns']) + 1 }}">Aucune donnée.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $items->links() }}
        </div>
    </div>
@endsection
