@php
    $chartMap = [];
    foreach($patient->dentalCharts as $c) {
        $chartMap["{$c->tooth_number}_{$c->surface}"] = [
            'tooth_number' => $c->tooth_number,
            'surface' => $c->surface,
            'condition' => $c->condition,
            'notes' => $c->notes,
        ];
    }
@endphp

<div class="odontogram-v2-container" 
     id="{{ $appId ?? 'odontogram-app-v2' }}" 
     data-patient-id="{{ $patient->id }}"
     data-update-url="{{ route('dental-chart.update', $patient) }}"
     data-history-url="{{ route('dental-chart.history', $patient) }}"
     data-teeth-base-url="{{ asset('images/dental/teeth') }}"
     data-chart-data="{{ json_encode($chartMap) }}">

    <!-- Odontogram v2 Header -->
    <div class="odontogram-v2-header">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span class="v2-badge-pill">
                <i class="fa-solid fa-sparkles"></i> Odontogram v2
            </span>
            <span style="font-weight: 700; font-size: 16px; color: #0f172a;">
                Realistic Anatomical Visual Dental Chart
            </span>
            <span class="sync-active-badge">
                <span class="sync-dot"></span> Live Sync Active
            </span>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @if(!isset($hideCompareBtn) || !$hideCompareBtn)
            <a href="{{ route('patients.odontogram.compare', $patient) }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);">
                <i class="fa-solid fa-code-compare"></i> Compare v1 vs v2 Side-by-Side
            </a>
            @endif

            <button type="button" class="btn btn-secondary btn-sm" id="view-chart-history-btn-v2" onclick="document.getElementById('chart-history-modal') ? document.getElementById('chart-history-modal').style.display='flex' : null">
                <i class="fa-solid fa-clock-rotate-left"></i> History Audit
            </button>
        </div>
    </div>

    <!-- Controls Toolbar -->
    <div class="odontogram-controls" style="margin-bottom: 18px;">
        <div>
            <div style="font-weight: 700; font-size: 13px; margin-bottom: 8px; color: #334155;">
                <i class="fa-solid fa-paintbrush" style="color: #0284c7; margin-right: 4px;"></i> Select Clinical Condition to Chart:
            </div>
            <div class="condition-palette">
                <button type="button" class="palette-btn active" data-condition="caries">
                    <span class="swatch" style="background:#ef4444;"></span> Caries / Decay
                </button>
                <button type="button" class="palette-btn" data-condition="filled">
                    <span class="swatch" style="background:#3b82f6;"></span> Filled (Composite)
                </button>
                <button type="button" class="palette-btn" data-condition="crown">
                    <span class="swatch" style="background:#f59e0b;"></span> Crown (PFM/Zirconia)
                </button>
                <button type="button" class="palette-btn" data-condition="root_canal">
                    <span class="swatch" style="background:#8b5cf6;"></span> Root Canal (RCT)
                </button>
                <button type="button" class="palette-btn" data-condition="missing">
                    <span class="swatch" style="background:#cbd5e1;"></span> Missing
                </button>
                <button type="button" class="palette-btn" data-condition="extracted">
                    <span class="swatch" style="background:#64748b;"></span> Extracted
                </button>
                <button type="button" class="palette-btn" data-condition="implant">
                    <span class="swatch" style="background:#06b6d4;"></span> Implant
                </button>
                <button type="button" class="palette-btn" data-condition="fractured">
                    <span class="swatch" style="background:#ec4899;"></span> Fractured
                </button>
                <button type="button" class="palette-btn" data-condition="impacted">
                    <span class="swatch" style="background:#6366f1;"></span> Impacted
                </button>
                <button type="button" class="palette-btn" data-condition="healthy">
                    <span class="swatch" style="background:#ffffff; border:1px solid #cbd5e1;"></span> Sound / Healthy
                </button>
            </div>
        </div>

        <div class="v2-control-group">
            <!-- View Mode Switcher -->
            <div class="v2-segmented-control" title="Switch Visualization Display Mode">
                <button type="button" class="v2-seg-btn active" data-view-mode="combined">
                    <i class="fa-solid fa-layer-group"></i> Combined
                </button>
                <button type="button" class="v2-seg-btn" data-view-mode="teeth-only">
                    <i class="fa-solid fa-image"></i> Real Teeth Only
                </button>
                <button type="button" class="v2-seg-btn" data-view-mode="surface-only">
                    <i class="fa-solid fa-chart-pie"></i> Surface Only
                </button>
            </div>

            <!-- Numbering Switcher -->
            <div class="v2-segmented-control" title="Tooth Numbering Nomenclature">
                <button type="button" class="numbering-toggle-btn active v2-seg-btn" data-system="fdi">FDI (11-48)</button>
                <button type="button" class="numbering-toggle-btn v2-seg-btn" data-system="universal">Universal (1-32)</button>
            </div>
        </div>
    </div>

    <!-- Panoramic Dental Reference Strip -->
    <div class="v2-panoramic-card">
        <div class="v2-panoramic-header">
            <span class="v2-panoramic-title">
                <i class="fa-solid fa-eye" style="color: #0284c7;"></i>
                Panoramic Dental Arch Anatomy Reference
            </span>
            <span style="font-size: 11px; color: #64748b;">
                Adult Permanent Dentition (32 Teeth) &bull; Maxillary (Top) &bull; Mandibular (Bottom)
            </span>
        </div>
        <div class="v2-panoramic-img-container">
            <img src="{{ asset('images/dental/realistic_teeth_arch.jpg') }}" alt="Realistic Dental Arch Reference" title="Full Permanent Dental Arch Medical Illustration">
        </div>
    </div>

    <!-- UPPER MAXILLARY ARCH (Quadrants 1 & 2) -->
    <div class="v2-arch-section">
        <div class="v2-arch-header">
            <div class="v2-arch-title">
                <i class="fa-solid fa-circle-chevron-up" style="color: #0284c7;"></i>
                Upper Maxillary Arch
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Roots Apical &bull; Crowns Incisal/Occlusal &bull; Right (Q1) to Left (Q2)
            </div>
        </div>

        <div class="v2-arch-label-quadrants">
            <span>Quadrant 1: Upper Right (18 to 11)</span>
            <span>Quadrant 2: Upper Left (21 to 28)</span>
        </div>

        <div class="v2-teeth-row" id="teeth-upper-row-v2">
            <!-- Rendered with Real Teeth Images on Top by odontogram_v2.js -->
        </div>
    </div>

    <!-- LOWER MANDIBULAR ARCH (Quadrants 4 & 3) -->
    <div class="v2-arch-section" style="margin-bottom: 12px;">
        <div class="v2-arch-header">
            <div class="v2-arch-title">
                <i class="fa-solid fa-circle-chevron-down" style="color: #0284c7;"></i>
                Lower Mandibular Arch
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Crowns Occlusal &bull; Roots Apical &bull; Right (Q4) to Left (Q3)
            </div>
        </div>

        <div class="v2-arch-label-quadrants">
            <span>Quadrant 4: Lower Right (48 to 41)</span>
            <span>Quadrant 3: Lower Left (31 to 38)</span>
        </div>

        <div class="v2-teeth-row" id="teeth-lower-row-v2">
            <!-- Rendered with Real Teeth Images by odontogram_v2.js -->
        </div>
    </div>

    <!-- Instructions / Footer Tips -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #64748b; flex-wrap: wrap; gap: 8px;">
        <span>
            <i class="fa-solid fa-circle-info" style="color: #0284c7;"></i>
            <strong>Quick Charting:</strong> Click any <strong>real tooth image</strong> to chart the entire tooth (Crown, Root Canal, Implant, Extraction). Click individual <strong>surfaces</strong> (Buccal, Lingual, Mesial, Distal, Occlusal) for precise cavity fillings.
        </span>
        <span style="font-weight: 600;">
            <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> Changes automatically saved & audit logged.
        </span>
    </div>
</div>

<!-- Charted Conditions Summary Table -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
            <span>Currently Charted Conditions ({{ $patient->dentalCharts->where('condition', '!=', 'healthy')->count() }} surfaces/teeth)</span>
        </div>
        <span class="badge badge-primary">Synchronized Real-time</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 100px;">Tooth Visual</th>
                        <th>Tooth #</th>
                        <th>Surface</th>
                        <th>Condition</th>
                        <th>Clinical Notes</th>
                        <th>Charted By</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->dentalCharts->where('condition', '!=', 'healthy') as $chart)
                    <tr>
                        <td>
                            <div style="width: 38px; height: 50px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; padding: 2px;">
                                <img src="{{ asset('images/dental/teeth/tooth_' . $chart->tooth_number . '.png') }}" 
                                     alt="Tooth #{{ $chart->tooth_number }}" 
                                     style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        </td>
                        <td><strong>Tooth #{{ $chart->tooth_number }}</strong></td>
                        <td><span class="badge badge-secondary">{{ ucfirst($chart->surface) }}</span></td>
                        <td>
                            @php
                                $badgeStyle = match($chart->condition) {
                                    'caries' => 'background:#fee2e2; color:#dc2626; border:1px solid #fca5a5;',
                                    'filled' => 'background:#dbeafe; color:#2563eb; border:1px solid #bfdbfe;',
                                    'crown' => 'background:#fef3c7; color:#d97706; border:1px solid #fde68a;',
                                    'root_canal' => 'background:#ede9fe; color:#7c3aed; border:1px solid #ddd6fe;',
                                    'missing', 'extracted' => 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;',
                                    'implant' => 'background:#cffafe; color:#0891b2; border:1px solid #a5f3fc;',
                                    default => 'background:#f1f5f9; color:#334155;'
                                };
                            @endphp
                            <span class="badge" style="{{ $badgeStyle }} font-weight:700;">
                                {{ ucwords(str_replace('_', ' ', $chart->condition)) }}
                            </span>
                        </td>
                        <td>{{ $chart->notes ?? 'No notes recorded.' }}</td>
                        <td>{{ $chart->updatedBy->name ?? 'Clinical Staff' }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $chart->updated_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            <i class="fa-solid fa-tooth" style="font-size: 24px; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                            All teeth sound and healthy. No pathological conditions currently charted.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tooth Clinical Detail & Notes Modal -->
<div id="tooth-edit-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.6); backdrop-filter:blur(3px); z-index:99999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#ffffff; border-radius:14px; width:100%; max-width:520px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid #e2e8f0;">
        <div style="padding:16px 22px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="v2-badge-pill"><i class="fa-solid fa-tooth"></i> Tooth Detail</span>
                <h3 id="modal-tooth-title" style="font-size:15px; font-weight:700; color:#0f172a; margin:0;">Tooth Detail</h3>
            </div>
            <button type="button" onclick="document.getElementById('tooth-edit-modal').style.display='none'" style="border:none; background:transparent; font-size:20px; color:#64748b; cursor:pointer;">&times;</button>
        </div>

        <form id="tooth-modal-form" action="{{ route('dental-chart.update', $patient) }}" method="POST" style="padding:22px;">
            @csrf
            <input type="hidden" name="tooth_number" id="modal-tooth-number" value="">

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label" style="font-weight:700; font-size:13px;">Target Surface / Area:</label>
                <select name="surface" id="modal-surface" class="form-control" style="width:100%;">
                    <option value="whole">Whole Tooth (Crown & Roots)</option>
                    <option value="occlusal">Occlusal (Biting Surface)</option>
                    <option value="buccal">Buccal / Facial (Cheek side)</option>
                    <option value="lingual">Lingual / Palatal (Tongue side)</option>
                    <option value="mesial">Mesial (Front contact)</option>
                    <option value="distal">Distal (Rear contact)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label" style="font-weight:700; font-size:13px;">Clinical Condition:</label>
                <select name="condition" id="modal-condition" class="form-control" style="width:100%;">
                    <option value="caries">Caries / Cavity Decay</option>
                    <option value="filled">Filled (Composite / Amalgam)</option>
                    <option value="crown">Crown (PFM / Zirconia / Ceramic)</option>
                    <option value="root_canal">Root Canal Treatment (RCT)</option>
                    <option value="missing">Missing</option>
                    <option value="extracted">Extracted</option>
                    <option value="implant">Dental Implant</option>
                    <option value="fractured">Fractured / Chipped</option>
                    <option value="impacted">Impacted</option>
                    <option value="healthy">Sound / Healthy</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label class="form-label" style="font-weight:700; font-size:13px;">Clinical Examination Notes & Observations:</label>
                <textarea name="notes" id="modal-notes" rows="3" class="form-control" placeholder="e.g. Deep occlusal fissure decay extending to dentin. Advised indirect restoration."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('tooth-edit-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#0284c7; border-color:#0284c7;">
                    <i class="fa-solid fa-save"></i> Save Clinical Charting
                </button>
            </div>
        </form>
    </div>
</div>
