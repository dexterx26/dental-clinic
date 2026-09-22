@extends('layouts.app')

@section('title', 'System Settings & Backups')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Clinic Settings & Database Backups</h1>
        <div class="page-subtitle">Configure practice branding, operating parameters, and disaster recovery backups</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Left: Settings Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-sliders" style="color: var(--primary);"></i>
                <span>Practice Information & Configuration</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="clinic_name">Dental Practice / Clinic Name</label>
                            <input type="text" name="clinic_name" id="clinic_name" class="form-control" required value="{{ $settings['clinic_name'] ?? 'BrightSmile Dental & Oral Health Center' }}">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="clinic_tagline">Tagline / Motto</label>
                            <input type="text" name="clinic_tagline" id="clinic_tagline" class="form-control" value="{{ $settings['clinic_tagline'] ?? 'State-of-the-Art Dental Care' }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="clinic_address">Clinic Physical Address</label>
                    <input type="text" name="clinic_address" id="clinic_address" class="form-control" required value="{{ $settings['clinic_address'] ?? 'Unit 402 Medical Arts Tower, Bonifacio Global City, Taguig, Philippines' }}">
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="clinic_phone">Official Telephone / Mobile</label>
                            <input type="text" name="clinic_phone" id="clinic_phone" class="form-control" required value="{{ $settings['clinic_phone'] ?? '+63 917 123 4567 / (02) 8888-9999' }}">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="clinic_email">Contact Email Address</label>
                            <input type="email" name="clinic_email" id="clinic_email" class="form-control" required value="{{ $settings['clinic_email'] ?? 'contact@brightsmiledental.ph' }}">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="currency_symbol">Currency Symbol</label>
                            <input type="text" name="currency_symbol" id="currency_symbol" class="form-control" required value="{{ $settings['currency_symbol'] ?? '₱' }}">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="invoice_prefix">Invoice Prefix</label>
                            <input type="text" name="invoice_prefix" id="invoice_prefix" class="form-control" required value="{{ $settings['invoice_prefix'] ?? 'INV-2026-' }}">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="receipt_prefix">Receipt Prefix (OR)</label>
                            <input type="text" name="receipt_prefix" id="receipt_prefix" class="form-control" required value="{{ $settings['receipt_prefix'] ?? 'OR-2026-' }}">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="opening_time">Daily Clinic Opening Time</label>
                            <input type="time" name="opening_time" id="opening_time" class="form-control" value="{{ $settings['opening_time'] ?? '08:00' }}">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="closing_time">Daily Clinic Closing Time</label>
                            <input type="time" name="closing_time" id="closing_time" class="form-control" value="{{ $settings['closing_time'] ?? '18:00' }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="dpa_compliance_notice">Philippine RA 10173 Compliance Statement</label>
                    <textarea name="dpa_compliance_notice" id="dpa_compliance_notice" class="form-control" rows="2">{{ $settings['dpa_compliance_notice'] ?? 'Patient records are protected under Republic Act No. 10173 (Philippine Data Privacy Act of 2012).' }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save System Settings
                </button>
            </form>
        </div>
    </div>

    <!-- Right: Disaster Recovery & Database Backups -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-database" style="color: var(--primary);"></i>
                    <span>Database Backup & Recovery</span>
                </div>
            </div>
            <div class="card-body">
                <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                    Generate an immediate, full snapshot of the entire MySQL database including patients, dental charts, examinations, procedures, invoices, and audit logs.
                </p>

                <form action="{{ route('settings.backup') }}" method="POST" style="margin-bottom: 24px;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">
                        <i class="fa-solid fa-download"></i> Generate Full Database Backup Now
                    </button>
                </form>

                <h4 style="font-size: 13px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); margin-bottom: 10px;">
                    Saved Backup Archives
                </h4>

                @forelse($backupFiles as $backup)
                <div style="padding: 10px 12px; background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 8px; font-size: 12.5px;">
                    <div style="font-weight: 700; color: var(--text-main); word-break: break-all;">{{ $backup['name'] }}</div>
                    <div style="display: flex; justify-content: space-between; color: var(--text-light); font-size: 11px; margin-top: 4px;">
                        <span>{{ $backup['size'] }}</span>
                        <span>{{ $backup['date'] }}</span>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 12.5px;">
                    No manual backups generated yet. Click above to create one.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
