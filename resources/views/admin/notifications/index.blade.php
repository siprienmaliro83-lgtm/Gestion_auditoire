@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
    <div class="card stat-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Destinataire</th>
                        <th>Contenu</th>
                        <th>Lu</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        <tr>
                            <td>{{ $notification->type }}</td>
                            <td>{{ $notification->notifiable_type }} #{{ $notification->notifiable_id }}</td>
                            <td>{{ data_get($notification->data, 'message', 'Aucun message') }}</td>
                            <td>{{ $notification->read_at ? 'Oui' : 'Non' }}</td>
                            <td class="text-end">
                                @if(!$notification->read_at)
                                    <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Marquer lu</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucune notification.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection
