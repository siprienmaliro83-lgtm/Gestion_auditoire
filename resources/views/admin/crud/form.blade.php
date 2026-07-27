@extends('layouts.app')

@section('title', ($item->exists ? 'Modifier ' : 'Ajouter ').$config['singular'])
@section('page-title', ($item->exists ? 'Modifier ' : 'Ajouter ').$config['singular'])

@section('content')
    <div class="card stat-card">
        <div class="card-body">
            <form method="POST" action="{{ $item->exists ? route('admin.crud.update', [$resource, $item->id]) : route('admin.crud.store', $resource) }}" novalidate>
                @csrf
                @if($item->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    @foreach($config['fields'] as $name => $definition)
                        @php
                            $type = str_contains($definition, ':') ? str($definition)->before(':')->toString() : $definition;
                            $meta = str_contains($definition, ':') ? str($definition)->after(':')->toString() : null;
                            $value = old($name, $item->{$name} ?? null);
                            if ($value instanceof \Carbon\CarbonInterface) {
                                $value = $value->format('Y-m-d');
                            }
                            $selectedValues = is_array($value) ? array_map('strval', $value) : [];
                        @endphp
                        <div class="{{ $type === 'textarea' || $type === 'select-multiple' ? 'col-12' : 'col-md-6' }}">
                            @if($type === 'checkbox')
                                <div class="form-check mt-4">
                                    <input class="form-check-input @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" type="checkbox" value="1" @checked(old($name, $item->{$name}))>
                                    <label class="form-check-label" for="{{ $name }}">{{ ucfirst(str_replace('_', ' ', $name)) }}</label>
                                    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            @else
                                <label class="form-label" for="{{ $name }}">{{ ucfirst(str_replace('_', ' ', $name)) }}</label>

                                @if($type === 'textarea')
                                    <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" rows="3">{{ $value }}</textarea>
                                @elseif($type === 'select')
                                    <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}">
                                        <option value="">Sélectionner</option>
                                        @foreach($options[$meta] ?? [] as $option)
                                            <option value="{{ $option['id'] }}" @selected((string) $value === (string) $option['id'])>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif($type === 'select-multiple')
                                    <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}[]" multiple size="6">
                                        @foreach($options[$meta] ?? [] as $option)
                                            <option value="{{ $option['id'] }}" @selected(in_array((string) $option['id'], $selectedValues, true))>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif($type === 'enum')
                                    <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}">
                                        <option value="">Sélectionner</option>
                                        @foreach(explode(',', $meta) as $option)
                                            <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : $value }}">
                                @endif

                                @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('admin.crud.index', $resource) }}">Annuler</a>
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-save me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
