@extends('layouts.app')

@section('title', 'System Audit Trail (RA 10173)')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">System Audit Trail & Compliance Log</h1>
        <div class="page-subtitle">Immutable access and activity log compliant with Philippine Republic Act No. 10173 (Data Privacy Act of 2012)</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px;">
        <form action="{{ route('audit-logs.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Search activity description..." value="{{ $search }}">
            </div>
            <div style="flex: 1; min-width: 180px;">
                <select name="action" class="form-control">
                    <option value="">All Action Types</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $act)) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 180px;">
                <select name="user_id" class="form-control">
                    <option value="">All Staff Members</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Audit Log Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i>
            <span>Activity Records</span>
        </div>
        <span class="badge badge-primary">{{ $logs->total() }} Log Entries</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 16%;">Timestamp</th>
                        <th style="width: 15%;">User / Actor</th>
                        <th style="width: 14%;">Action</th>
                        <th style="width: 40%;">Description</th>
                        <th style="width: 15%;">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td style="font-size: 12px;">
                            <strong>{{ $log->created_at->format('M d, Y') }}</strong>
                            <div style="color: var(--text-light);">{{ $log->created_at->format('h:i:s A') }}</div>
                        </td>
                        <td>
                            @if($log->user)
                                <div style="font-weight: 600;">{{ $log->user->name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ ucwords(str_replace('_', ' ', $log->user->role)) }}</div>
                            @else
                                <span style="color: var(--text-light);">System / Guest</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 11px;">
                                {{ ucwords(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td style="font-size: 13px; color: var(--text-main);">
                            {{ $log->description }}
                        </td>
                        <td style="font-size: 11.5px; color: var(--text-muted);">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No audit logs found matching criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 16px;">
    {{ $logs->links() }}
</div>
@endsection
