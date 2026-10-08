@extends('layouts.app')

@section('title', 'Odontogram Comparison - ' . $patient->full_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/odontogram_v2.css') }}">
<style>
.compare-nav-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 14px 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.compare-stat-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.compare-view-selector {
    background: #f1f5f9;
    border-radius: 8px;
    padding: 3px;
    display: inline-flex;
    gap: 2px;
}

.compare-view-btn {
    border: none;
    background: transparent;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.compare-view-btn.active {
    background: #ffffff;
    color: #0f172a;
    font-weight: 700;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.column-v1-wrapper, .column-v2-wrapper {
    transition: all 0.3s ease;
}
</style>
@endpush

@section('content')
<div class="compare-container">
    <!-- Hero Banner with Patient Summary -->
    <div class="compare-hero-card">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff; font-weight: 700;">Clinical Visualization Lab</span>
                <span style="font-size: 13px; color: #94a3b8;">{{ $patient->patient_number }}</span>
            </div>
            <h1 class="compare-hero-title">
                <i class="fa-solid fa-code-compare" style="color: #38bdf8;"></i>
                Odontogram v1 vs Odontogram v2 Comparison
            </h1>
            <p class="compare-hero-subtitle">
                Comparing <strong>Odontogram v1 (Traditional Geometric 5-Surface Diagram)</strong> with <strong>Odontogram v2 (Realistic Anatomical Real Teeth Visualization)</strong> for patient <strong>{{ $patient->full_name }}</strong>.
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'odontogram']) }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff;">
                <i class="fa-solid fa-tooth"></i> Tab v1
            </a>
            <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'odontogram_v2']) }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff;">
                <i class="fa-solid fa-teeth"></i> Tab v2
            </a>
            <a href="{{ route('patients.show', $patient) }}" class="btn btn-light btn-sm" style="font-weight: 700;">
                <i class="fa-solid fa-arrow-left"></i> Return to Patient
            </a>
        </div>
    </div>

    <!-- Toolbar & Mode Switcher -->
    <div class="compare-nav-bar">
        <!-- View layout switcher -->
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="font-weight: 700; font-size: 13px; color: #334155;">Comparison Mode:</span>
            <div class="compare-view-selector">
                <button type="button" class="compare-view-btn active" id="btn-mode-split" onclick="switchCompareMode('split')">
                    <i class="fa-solid fa-table-columns"></i> Side-by-Side Dual View
                </button>
                <button type="button" class="compare-view-btn" id="btn-mode-v1" onclick="switchCompareMode('v1')">
                    <i class="fa-solid fa-square-full"></i> Focus Odontogram v1
                </button>
                <button type="button" class="compare-view-btn" id="btn-mode-v2" onclick="switchCompareMode('v2')">
                    <i class="fa-solid fa-sparkles"></i> Focus Odontogram v2
                </button>
            </div>
        </div>

        <!-- Live Sync Status & Charted Count -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            @php
                $cariesCount = $patient->dentalCharts->where('condition', 'caries')->count();
                $filledCount = $patient->dentalCharts->where('condition', 'filled')->count();
                $crownCount = $patient->dentalCharts->where('condition', 'crown')->count();
                $rctCount = $patient->dentalCharts->where('condition', 'root_canal')->count();
                $missingCount = $patient->dentalCharts->whereIn('condition', ['missing', 'extracted'])->count();
            @endphp
            <div class="compare-stat-chip">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
                <span>Decay: <strong>{{ $cariesCount }}</strong></span>
            </div>
            <div class="compare-stat-chip">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6;"></span>
                <span>Filled: <strong>{{ $filledCount }}</strong></span>
            </div>
            <div class="compare-stat-chip">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
                <span>Crowns: <strong>{{ $crownCount }}</strong></span>
            </div>
            <div class="compare-stat-chip">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #8b5cf6;"></span>
                <span>RCT: <strong>{{ $rctCount }}</strong></span>
            </div>
            <div class="sync-active-badge">
                <span class="sync-dot"></span> Live Bi-Directional Sync
            </div>
        </div>
    </div>

    <!-- Side-by-Side Split Grid -->
    <div class="compare-split-grid" id="compare-grid">
        <!-- COLUMN 1: Odontogram v1 (Traditional Geometric Surface Diagram) -->
        <div class="compare-column-card column-v1-wrapper" id="col-v1">
            <div class="compare-column-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="compare-col-tag tag-v1">Odontogram v1</span>
                    <strong style="font-size: 14px; color: #0f172a;">Geometric 5-Surface Chart</strong>
                </div>
                <span style="font-size: 11.5px; color: #64748b;">Classical Polygon Model</span>
            </div>

            <div class="compare-column-body">
                @php
                    $chartMapV1 = [];
                    foreach($patient->dentalCharts as $c) {
                        $chartMapV1["{$c->tooth_number}_{$c->surface}"] = [
                            'tooth_number' => $c->tooth_number,
                            'surface' => $c->surface,
                            'condition' => $c->condition,
                            'notes' => $c->notes,
                        ];
                    }
                @endphp

                <div class="odontogram-container" 
                     id="odontogram-app" 
                     data-patient-id="{{ $patient->id }}"
                     data-update-url="{{ route('dental-chart.update', $patient) }}"
                     data-history-url="{{ route('dental-chart.history', $patient) }}"
                     data-chart-data="{{ json_encode($chartMapV1) }}"
                     style="border: none; padding: 0;">

                    <!-- Controls for v1 -->
                    <div class="odontogram-controls" style="margin-bottom: 14px;">
                        <div>
                            <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">Palette:</div>
                            <div class="condition-palette">
                                <button type="button" class="palette-btn active" data-condition="caries">
                                    <span class="swatch" style="background:#ef4444;"></span> Caries
                                </button>
                                <button type="button" class="palette-btn" data-condition="filled">
                                    <span class="swatch" style="background:#3b82f6;"></span> Filled
                                </button>
                                <button type="button" class="palette-btn" data-condition="crown">
                                    <span class="swatch" style="background:#f59e0b;"></span> Crown
                                </button>
                                <button type="button" class="palette-btn" data-condition="root_canal">
                                    <span class="swatch" style="background:#8b5cf6;"></span> RCT
                                </button>
                                <button type="button" class="palette-btn" data-condition="missing">
                                    <span class="swatch" style="background:#cbd5e1;"></span> Missing
                                </button>
                                <button type="button" class="palette-btn" data-condition="implant">
                                    <span class="swatch" style="background:#06b6d4;"></span> Implant
                                </button>
                                <button type="button" class="palette-btn" data-condition="healthy">
                                    <span class="swatch" style="background:#ffffff; border:1px solid #cbd5e1;"></span> Sound
                                </button>
                            </div>
                        </div>

                        <div style="display: flex; gap: 4px;">
                            <button type="button" class="numbering-toggle-btn active btn btn-sm" data-system="fdi" style="padding: 4px 8px; font-size: 11px;">FDI</button>
                            <button type="button" class="numbering-toggle-btn btn btn-sm" data-system="universal" style="padding: 4px 8px; font-size: 11px;">Univ</button>
                        </div>
                    </div>

                    <!-- Upper Arch v1 -->
                    <div class="teeth-arch" style="margin-bottom: 16px;">
                        <div class="arch-title" style="font-size: 11px;">Upper Maxillary Arch (Right to Left)</div>
                        <div class="teeth-row" id="teeth-upper-row" style="gap: 3px;">
                            <!-- Rendered by odontogram.js -->
                        </div>
                    </div>

                    <!-- Lower Arch v1 -->
                    <div class="teeth-arch">
                        <div class="teeth-row" id="teeth-lower-row" style="gap: 3px;">
                            <!-- Rendered by odontogram.js -->
                        </div>
                        <div class="arch-title" style="margin-top: 8px; margin-bottom: 0; font-size: 11px;">Lower Mandibular Arch (Right to Left)</div>
                    </div>

                    <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border); font-size: 11px; color: var(--text-muted);">
                        <i class="fa-solid fa-circle-info"></i> Abstract polygon surfaces. Does not display anatomical roots, crowns, or visual tooth geometry.
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: Odontogram v2 (Realistic Teeth Visualization) -->
        <div class="compare-column-card column-v2-wrapper" id="col-v2">
            <div class="compare-column-header" style="background: #f0f9ff; border-color: #bae6fd;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="compare-col-tag tag-v2">Odontogram v2</span>
                    <strong style="font-size: 14px; color: #0369a1;">Realistic Anatomical Visual Chart</strong>
                </div>
                <span class="v2-badge-pill" style="font-size: 10px; padding: 2px 8px;">
                    <i class="fa-solid fa-check"></i> Recommended
                </span>
            </div>

            <div class="compare-column-body" style="padding: 16px;">
                @include('patients.partials.odontogram_v2_tab', ['appId' => 'odontogram-app-v2', 'hideCompareBtn' => true])
            </div>
        </div>
    </div>

    <!-- Feature Comparison Matrix -->
    <div class="card" style="margin-top: 10px;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-scale-balanced" style="color: var(--primary);"></i>
                <span>Comprehensive Architectural & Visual Comparison Matrix</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="feature-compare-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Feature Capability</th>
                        <th style="width: 35%;">Odontogram v1 (Traditional)</th>
                        <th style="width: 40%;">Odontogram v2 (Realistic Visual)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Teeth Visual Representation</strong></td>
                        <td><span style="color: #64748b;">Pure geometric 5-polygon square box abstraction</span></td>
                        <td><strong style="color: #0284c7;"><i class="fa-solid fa-check-circle text-success"></i> High-resolution real tooth anatomical photo/illustrations</strong> showing realistic enamel, cusps, and roots on top</td>
                    </tr>
                    <tr>
                        <td><strong>Root & Crown Anatomy</strong></td>
                        <td><span style="color: #94a3b8;">Not rendered (roots completely hidden)</span></td>
                        <td><strong style="color: #0284c7;"><i class="fa-solid fa-check-circle text-success"></i> Full anatomical roots & crowns</strong> (3-root maxillary molars, 2-root mandibular molars, single-root canines/incisors)</td>
                    </tr>
                    <tr>
                        <td><strong>Whole Tooth Charting</strong></td>
                        <td>Manual multi-surface selection required</td>
                        <td><strong style="color: #0284c7;"><i class="fa-solid fa-check-circle text-success"></i> 1-Click Whole Tooth Action:</strong> Click the real tooth image directly to apply Crown, RCT, Extraction, or Implant</td>
                    </tr>
                    <tr>
                        <td><strong>Endodontic (RCT) & Crown Visualization</strong></td>
                        <td>Color fill inside small square polygon</td>
                        <td><strong style="color: #0284c7;"><i class="fa-solid fa-check-circle text-success"></i> Specialized overlays:</strong> Gold crown highlight, purple canal indicator, titanium implant screw fixture, extraction cross</td>
                    </tr>
                    <tr>
                        <td><strong>Panoramic Dental Arch Strip</strong></td>
                        <td>None</td>
                        <td><strong style="color: #0284c7;"><i class="fa-solid fa-check-circle text-success"></i> Integrated Panoramic Arch Reference Strip</strong> showing full adult dentition alignment</td>
                    </tr>
                    <tr>
                        <td><strong>Display Modes</strong></td>
                        <td>Fixed 5-surface boxes only</td>
                        <td><strong style="color: #0284c7;"><i class="fa-solid fa-check-circle text-success"></i> 3 Flexible Modes:</strong> Combined (Real Teeth + Surfaces), Real Teeth Only, Surface Only</td>
                    </tr>
                    <tr>
                        <td><strong>Patient Communication Clarity</strong></td>
                        <td>Low (patients often cannot read abstract squares)</td>
                        <td><strong style="color: #059669;"><i class="fa-solid fa-star text-warning"></i> Exceptional:</strong> Patients immediately recognize their actual front and back teeth during treatment consultation</td>
                    </tr>
                    <tr>
                        <td><strong>Database Compatibility</strong></td>
                        <td>Standard DentalChart model</td>
                        <td><strong style="color: #0284c7;"><i class="fa-solid fa-check-circle text-success"></i> 100% Shared & Interoperable:</strong> Data charted in v1 is instantly visible in v2 and vice-versa</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/odontogram.js') }}"></script>
<script src="{{ asset('js/odontogram_v2.js') }}"></script>
<script>
function switchCompareMode(mode) {
    const grid = document.getElementById('compare-grid');
    const colV1 = document.getElementById('col-v1');
    const colV2 = document.getElementById('col-v2');
    
    document.querySelectorAll('.compare-view-btn').forEach(b => b.classList.remove('active'));
    
    if (mode === 'split') {
        document.getElementById('btn-mode-split').classList.add('active');
        grid.style.gridTemplateColumns = '1fr 1fr';
        colV1.style.display = 'block';
        colV2.style.display = 'block';
    } else if (mode === 'v1') {
        document.getElementById('btn-mode-v1').classList.add('active');
        grid.style.gridTemplateColumns = '1fr';
        colV1.style.display = 'block';
        colV2.style.display = 'none';
    } else if (mode === 'v2') {
        document.getElementById('btn-mode-v2').classList.add('active');
        grid.style.gridTemplateColumns = '1fr';
        colV1.style.display = 'none';
        colV2.style.display = 'block';
    }
}
</script>
@endpush
