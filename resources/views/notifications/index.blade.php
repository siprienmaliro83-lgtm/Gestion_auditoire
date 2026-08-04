@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Mes notifications')

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card stat-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Message</th>
                        <th>Reçu</th>
                        <th>Lu</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        <tr>
                            <td>{{ data_get($notification->data, 'message', 'Aucun message') }}</td>
                            <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $notification->read_at ? 'Oui' : 'Non' }}</td>
                            <td class="text-end">
                                @if(!$notification->read_at)
                                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Marquer lu</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucune notification.</td>
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
