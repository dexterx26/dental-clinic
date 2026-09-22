@extends('layouts.app')

@section('title', 'Dental Services & Pricing')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Dental Services & Fee Schedule</h1>
        <div class="page-subtitle">Configure dental procedures, categories, standard pricing, and estimated durations</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Left: Existing Services Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-notes-medical" style="color: var(--primary);"></i>
                <span>Services Catalog</span>
            </div>
            <span class="badge badge-primary">{{ $services->count() }} Active Services</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Service Name</th>
                            <th>Category</th>
                            <th>Duration</th>
                            <th>Standard Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $srv)
                        <tr>
                            <td><strong style="color: var(--primary);">{{ $srv->service_code }}</strong></td>
                            <td>
                                <div style="font-weight: 600;">{{ $srv->name }}</div>
                                @if($srv->description)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $srv->description }}</div>
                                @endif
                            </td>
                            <td><span class="badge badge-secondary">{{ $srv->category }}</span></td>
                            <td>{{ $srv->duration_minutes }} mins</td>
                            <td><strong style="color: #0f172a;">₱{{ number_format($srv->standard_price, 2) }}</strong></td>
                            <td>
                                <span class="badge {{ $srv->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $srv->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                No services created yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Add New Service Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-plus" style="color: var(--primary);"></i>
                <span>Add New Dental Service</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('services.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label required" for="service_code">Service Code</label>
                    <input type="text" name="service_code" id="service_code" class="form-control" required placeholder="e.g. SRV-013" value="{{ old('service_code') }}">
                </div>

                <div class="form-group">
                    <label class="form-label required" for="name">Service Name</label>
                    <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Pit and Fissure Sealant" value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label class="form-label required" for="category">Category</label>
                    <select name="category" id="category" class="form-control" required>
                        <option value="Diagnostic">Diagnostic</option>
                        <option value="Preventive">Preventive</option>
                        <option value="Restorative">Restorative</option>
                        <option value="Endodontics">Endodontics</option>
                        <option value="Periodontics">Periodontics</option>
                        <option value="Oral Surgery">Oral Surgery</option>
                        <option value="Prosthodontics">Prosthodontics</option>
                        <option value="Orthodontics">Orthodontics</option>
                        <option value="Cosmetic">Cosmetic</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="standard_price">Standard Price (₱)</label>
                            <input type="number" step="0.01" name="standard_price" id="standard_price" class="form-control" required min="0" value="1000.00">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="duration_minutes">Duration (Mins)</label>
                            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" required min="5" value="30">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Service Description</label>
                    <textarea name="description" id="description" class="form-control" rows="2" placeholder="Clinical notes on this procedure..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fa-solid fa-plus"></i> Add Service to Catalog
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
