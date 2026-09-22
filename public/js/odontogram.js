/**
 * Interactive Odontogram (Dental Chart) Engine
 * FDI & Universal Numbering, Multi-Surface Mapping, History Preservation
 */

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('odontogram-app');
    if (!container) return;

    const patientId = container.dataset.patientId;
    const updateUrl = container.dataset.updateUrl;
    const historyUrl = container.dataset.historyUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Existing Chart Data from server
    let chartData = JSON.parse(container.dataset.chartData || '{}');

    // Current selected condition
    let selectedCondition = 'caries';
    let currentNumberingSystem = 'fdi'; // 'fdi' or 'universal'

    // Teeth layout: Adult
    const upperRight = [18, 17, 16, 15, 14, 13, 12, 11];
    const upperLeft = [21, 22, 23, 24, 25, 26, 27, 28];
    const lowerRight = [48, 47, 46, 45, 44, 43, 42, 41];
    const lowerLeft = [31, 32, 33, 34, 35, 36, 37, 38];

    // FDI to Universal mapping
    const fdiToUniversal = {
        18: 1, 17: 2, 16: 3, 15: 4, 14: 5, 13: 6, 12: 7, 11: 8,
        21: 9, 22: 10, 23: 11, 24: 12, 25: 13, 26: 14, 27: 15, 28: 16,
        38: 17, 37: 18, 36: 19, 35: 20, 34: 21, 33: 22, 32: 23, 31: 24,
        41: 25, 42: 26, 43: 27, 44: 28, 45: 29, 46: 30, 47: 31, 48: 32
    };

    // Tooth naming
    function getToothName(toothNumber) {
        const quadrantNames = { 1: 'Upper Right', 2: 'Upper Left', 3: 'Lower Left', 4: 'Lower Right' };
        const toothNames = {
            1: 'Central Incisor', 2: 'Lateral Incisor', 3: 'Canine', 4: 'First Premolar',
            5: 'Second Premolar', 6: 'First Molar', 7: 'Second Molar', 8: 'Third Molar (Wisdom)'
        };
        const quad = Math.floor(toothNumber / 10);
        const tooth = toothNumber % 10;
        return `${quadrantNames[quad] || ''} ${toothNames[tooth] || ''} (#${toothNumber})`;
    }

    // Condition palette buttons
    const paletteButtons = document.querySelectorAll('.palette-btn');
    paletteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            paletteButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedCondition = this.dataset.condition;
        });
    });

    // Numbering toggle
    const toggleBtns = document.querySelectorAll('.numbering-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentNumberingSystem = this.dataset.system;
            renderAllTeethNumbers();
        });
    });

    function getDisplayNumber(toothNumber) {
        if (currentNumberingSystem === 'universal') {
            return fdiToUniversal[toothNumber] || toothNumber;
        }
        return toothNumber;
    }

    function renderAllTeethNumbers() {
        document.querySelectorAll('.tooth-unit').forEach(unit => {
            const fdiNumber = parseInt(unit.dataset.toothNumber, 10);
            const numLabel = unit.querySelector('.tooth-number');
            if (numLabel) {
                numLabel.textContent = getDisplayNumber(fdiNumber);
            }
        });
    }

    // Render individual SVG tooth unit with 5 surfaces (Buccal, Lingual, Mesial, Distal, Occlusal)
    function renderToothSVG(toothNumber, isUpper) {
        // Tooth condition checks
        const wholeCond = chartData[`${toothNumber}_whole`]?.condition || 'healthy';
        const occlusalCond = chartData[`${toothNumber}_occlusal`]?.condition || wholeCond;
        const buccalCond = chartData[`${toothNumber}_buccal`]?.condition || wholeCond;
        const lingualCond = chartData[`${toothNumber}_lingual`]?.condition || wholeCond;
        const mesialCond = chartData[`${toothNumber}_mesial`]?.condition || wholeCond;
        const distalCond = chartData[`${toothNumber}_distal`]?.condition || wholeCond;

        const quad = Math.floor(toothNumber / 10);
        // For right quadrants (1 and 4), Mesial is toward midline (inner), Distal is toward outer
        // For left quadrants (2 and 3), Mesial is toward midline (inner), Distal is toward outer
        const isRightQuad = (quad === 1 || quad === 4);
        const leftSurfaceName = isRightQuad ? 'distal' : 'mesial';
        const rightSurfaceName = isRightQuad ? 'mesial' : 'distal';
        const leftCond = isRightQuad ? distalCond : mesialCond;
        const rightCond = isRightQuad ? mesialCond : distalCond;

        const topSurfaceName = isUpper ? 'buccal' : 'lingual';
        const bottomSurfaceName = isUpper ? 'lingual' : 'buccal';
        const topCond = isUpper ? buccalCond : lingualCond;
        const bottomCond = isUpper ? lingualCond : buccalCond;

        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 100 100');
        svg.setAttribute('class', 'tooth-svg');

        svg.innerHTML = `
            <!-- Whole tooth backdrop / missing cross if missing -->
            <rect x="2" y="2" width="96" height="96" rx="10" fill="transparent" stroke="#94a3b8" stroke-width="1.5" class="surface-part" data-surface="whole"/>
            
            <!-- Top surface (Buccal / Lingual) -->
            <polygon points="10,10 90,10 70,30 30,30" class="surface-part cond-${topCond}" data-surface="${topSurfaceName}" title="${topSurfaceName}"/>
            
            <!-- Bottom surface (Lingual / Buccal) -->
            <polygon points="30,70 70,70 90,90 10,90" class="surface-part cond-${bottomCond}" data-surface="${bottomSurfaceName}" title="${bottomSurfaceName}"/>
            
            <!-- Left surface -->
            <polygon points="10,10 30,30 30,70 10,90" class="surface-part cond-${leftCond}" data-surface="${leftSurfaceName}" title="${leftSurfaceName}"/>
            
            <!-- Right surface -->
            <polygon points="70,30 90,10 90,90 70,70" class="surface-part cond-${rightCond}" data-surface="${rightSurfaceName}" title="${rightSurfaceName}"/>
            
            <!-- Center surface (Occlusal / Incisal) -->
            <rect x="30" y="30" width="40" height="40" class="surface-part cond-${occlusalCond}" data-surface="occlusal" title="occlusal"/>
        `;

        return svg;
    }

    function createToothElement(toothNumber, isUpper) {
        const unit = document.createElement('div');
        unit.className = 'tooth-unit';
        unit.dataset.toothNumber = toothNumber;
        unit.title = getToothName(toothNumber);

        const svg = renderToothSVG(toothNumber, isUpper);
        unit.appendChild(svg);

        const label = document.createElement('div');
        label.className = 'tooth-number';
        label.textContent = getDisplayNumber(toothNumber);
        unit.appendChild(label);

        // Click on individual surface
        svg.querySelectorAll('.surface-part').forEach(part => {
            part.addEventListener('click', function (e) {
                e.stopPropagation();
                const surface = this.dataset.surface;
                applyToothCondition(toothNumber, surface, selectedCondition, this);
            });
        });

        // Double click tooth unit opens detail modal
        unit.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            openToothModal(toothNumber);
        });

        return unit;
    }

    function applyToothCondition(toothNumber, surface, condition, element) {
        // Optimistic UI update
        element.setAttribute('class', `surface-part cond-${condition}`);

        // Update local chart state
        chartData[`${toothNumber}_${surface}`] = {
            tooth_number: toothNumber,
            surface: surface,
            condition: condition
        };

        // AJAX update to server
        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                tooth_number: toothNumber,
                surface: surface,
                condition: condition,
                notes: `Charted ${condition} on ${surface} surface.`
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(`Tooth #${getDisplayNumber(toothNumber)} (${surface}) set to ${condition}`);
            }
        })
        .catch(err => {
            console.error('Failed to update tooth:', err);
            showToast('Failed to save tooth update', 'error');
        });
    }

    // Modal dialog for detailed tooth entry
    function openToothModal(toothNumber) {
        const modal = document.getElementById('tooth-edit-modal');
        if (!modal) return;

        document.getElementById('modal-tooth-title').textContent = getToothName(toothNumber);
        document.getElementById('modal-tooth-number').value = toothNumber;
        modal.style.display = 'flex';
    }

    // Toast notification helper
    function showToast(message, type = 'success') {
        let toast = document.getElementById('odontogram-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'odontogram-toast';
            toast.style.position = 'fixed';
            toast.style.bottom = '24px';
            toast.style.right = '24px';
            toast.style.padding = '10px 18px';
            toast.style.borderRadius = '8px';
            toast.style.fontSize = '13px';
            toast.style.fontWeight = '600';
            toast.style.color = '#ffffff';
            toast.style.zIndex = '9999';
            toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            toast.style.transition = 'opacity 0.3s ease';
            document.body.appendChild(toast);
        }

        toast.style.background = type === 'error' ? '#ef4444' : '#0f766e';
        toast.textContent = message;
        toast.style.opacity = '1';

        setTimeout(() => {
            toast.style.opacity = '0';
        }, 2500);
    }

    // Render Upper and Lower Teeth Rows
    function renderArch() {
        const upperArchContainer = document.getElementById('teeth-upper-row');
        const lowerArchContainer = document.getElementById('teeth-lower-row');

        if (upperArchContainer) {
            upperArchContainer.innerHTML = '';
            // Upper Right (18 to 11)
            upperRight.forEach(t => upperArchContainer.appendChild(createToothElement(t, true)));
            
            // Midline divider
            const div = document.createElement('div');
            div.className = 'arch-divider';
            upperArchContainer.appendChild(div);

            // Upper Left (21 to 28)
            upperLeft.forEach(t => upperArchContainer.appendChild(createToothElement(t, true)));
        }

        if (lowerArchContainer) {
            lowerArchContainer.innerHTML = '';
            // Lower Right (48 to 41)
            lowerRight.forEach(t => lowerArchContainer.appendChild(createToothElement(t, false)));

            // Midline divider
            const div = document.createElement('div');
            div.className = 'arch-divider';
            lowerArchContainer.appendChild(div);

            // Lower Left (31 to 38)
            lowerLeft.forEach(t => lowerArchContainer.appendChild(createToothElement(t, false)));
        }
    }

    // Initialize Chart
    renderArch();

    // History Timeline trigger
    const historyBtn = document.getElementById('view-chart-history-btn');
    if (historyBtn) {
        historyBtn.addEventListener('click', function () {
            fetch(historyUrl, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('chart-history-list');
                    if (!list) return;
                    list.innerHTML = '';

                    if (!data.histories || data.histories.length === 0) {
                        list.innerHTML = '<p class="text-muted">No history changes recorded yet.</p>';
                    } else {
                        data.histories.forEach(h => {
                            const item = document.createElement('div');
                            item.className = 'history-item';
                            item.style.padding = '10px 0';
                            item.style.borderBottom = '1px solid #e2e8f0';
                            item.innerHTML = `
                                <div style="display:flex; justify-content:space-between; font-size:12.5px;">
                                    <strong>Tooth #${h.tooth_number} (${h.surface})</strong>
                                    <span style="color:#64748b;">${new Date(h.created_at).toLocaleString()}</span>
                                </div>
                                <div style="font-size:12px; margin-top:2px;">
                                    Changed from <span class="badge badge-secondary">${h.previous_condition || 'none'}</span> to <span class="badge badge-primary">${h.new_condition}</span>
                                    by <em>${h.changed_by ? h.changed_by.name : 'System'}</em>
                                </div>
                                ${h.notes ? `<div style="font-size:11.5px; color:#64748b; margin-top:2px;">"${h.notes}"</div>` : ''}
                            `;
                            list.appendChild(item);
                        });
                    }

                    const historyModal = document.getElementById('chart-history-modal');
                    if (historyModal) historyModal.style.display = 'flex';
                });
        });
    }
});
