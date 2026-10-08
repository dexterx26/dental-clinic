/**
 * Odontogram Engine V2
 * Realistic Anatomical Teeth Visualization with Real Tooth Graphics,
 * 5-Surface Diagram Overlay, FDI/Universal Numbering, and Live Sync
 */

(function () {
    function initOdontogramV2(containerId = 'odontogram-app-v2') {
        const container = document.getElementById(containerId);
        if (!container) return;

        const patientId = container.dataset.patientId;
        const updateUrl = container.dataset.updateUrl;
        const historyUrl = container.dataset.historyUrl;
        const teethBaseUrl = container.dataset.teethBaseUrl || '/images/dental/teeth';
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        // Existing Chart Data from server
        let chartData = JSON.parse(container.dataset.chartData || '{}');

        // State
        let selectedCondition = 'caries';
        let currentNumberingSystem = 'fdi'; // 'fdi' or 'universal'
        let currentViewMode = 'combined'; // 'combined', 'teeth-only', 'surface-only'
        let currentArchFilter = 'all'; // 'all', 'upper', 'lower'

        // Adult Permanent Dentition
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

        const conditionIcons = {
            caries: { icon: 'fa-triangle-exclamation', color: '#ef4444', label: 'Caries' },
            filled: { icon: 'fa-fill-drip', color: '#3b82f6', label: 'Filled' },
            crown: { icon: 'fa-crown', color: '#f59e0b', label: 'Crown' },
            root_canal: { icon: 'fa-wave-square', color: '#8b5cf6', label: 'RCT' },
            missing: { icon: 'fa-xmark', color: '#64748b', label: 'Missing' },
            extracted: { icon: 'fa-trash-can', color: '#ef4444', label: 'Extracted' },
            implant: { icon: 'fa-screwdriver', color: '#06b6d4', label: 'Implant' },
            fractured: { icon: 'fa-bolt', color: '#ec4899', label: 'Fractured' },
            impacted: { icon: 'fa-down-left-and-up-right-to-center', color: '#6366f1', label: 'Impacted' },
            healthy: { icon: 'fa-check', color: '#10b981', label: 'Sound' }
        };

        function getToothName(toothNumber) {
            const quadrantNames = { 1: 'Upper Right (Maxillary)', 2: 'Upper Left (Maxillary)', 3: 'Lower Left (Mandibular)', 4: 'Lower Right (Mandibular)' };
            const toothNames = {
                1: 'Central Incisor', 2: 'Lateral Incisor', 3: 'Canine', 4: 'First Premolar',
                5: 'Second Premolar', 6: 'First Molar', 7: 'Second Molar', 8: 'Third Molar (Wisdom)'
            };
            const quad = Math.floor(toothNumber / 10);
            const tooth = toothNumber % 10;
            return `${quadrantNames[quad] || ''} ${toothNames[tooth] || ''} (#${toothNumber})`;
        }

        function getDisplayNumber(toothNumber) {
            if (currentNumberingSystem === 'universal') {
                return fdiToUniversal[toothNumber] || toothNumber;
            }
            return toothNumber;
        }

        function getToothPrimaryCondition(toothNumber) {
            // Check whole tooth condition first
            const whole = chartData[`${toothNumber}_whole`]?.condition;
            if (whole && whole !== 'healthy') return whole;

            // Otherwise check surfaces
            const surfaces = ['occlusal', 'buccal', 'lingual', 'mesial', 'distal'];
            for (const s of surfaces) {
                const cond = chartData[`${toothNumber}_${s}`]?.condition;
                if (cond && cond !== 'healthy') {
                    return cond;
                }
            }
            return 'healthy';
        }

        // Palette Buttons in this container
        const paletteButtons = container.querySelectorAll('.palette-btn');
        paletteButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                paletteButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedCondition = this.dataset.condition;
            });
        });

        // Numbering Toggles
        const numToggleBtns = container.querySelectorAll('.numbering-toggle-btn');
        numToggleBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                numToggleBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentNumberingSystem = this.dataset.system;
                renderAllTeethNumbers();
            });
        });

        // View Mode Toggles
        const viewModeBtns = container.querySelectorAll('[data-view-mode]');
        viewModeBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                viewModeBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentViewMode = this.dataset.viewMode;
                applyViewMode();
            });
        });

        function applyViewMode() {
            container.querySelectorAll('.tooth-v2-image-wrap').forEach(el => {
                el.style.display = (currentViewMode === 'surface-only') ? 'none' : 'flex';
            });
            container.querySelectorAll('.tooth-v2-surface-svg').forEach(el => {
                el.style.display = (currentViewMode === 'teeth-only') ? 'none' : 'block';
            });
        }

        function renderAllTeethNumbers() {
            container.querySelectorAll('.tooth-v2-unit').forEach(unit => {
                const fdiNumber = parseInt(unit.dataset.toothNumber, 10);
                const numBadge = unit.querySelector('.tooth-v2-number-badge');
                if (numBadge) {
                    numBadge.textContent = `#${getDisplayNumber(fdiNumber)}`;
                }
            });
        }

        // Render 5-surface SVG diagram
        function renderSurfaceSVG(toothNumber, isUpper) {
            const wholeCond = chartData[`${toothNumber}_whole`]?.condition || 'healthy';
            const occlusalCond = chartData[`${toothNumber}_occlusal`]?.condition || wholeCond;
            const buccalCond = chartData[`${toothNumber}_buccal`]?.condition || wholeCond;
            const lingualCond = chartData[`${toothNumber}_lingual`]?.condition || wholeCond;
            const mesialCond = chartData[`${toothNumber}_mesial`]?.condition || wholeCond;
            const distalCond = chartData[`${toothNumber}_distal`]?.condition || wholeCond;

            const quad = Math.floor(toothNumber / 10);
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
            svg.setAttribute('class', 'tooth-svg tooth-v2-surface-svg');

            svg.innerHTML = `
                <rect x="2" y="2" width="96" height="96" rx="8" fill="transparent" stroke="#cbd5e1" stroke-width="1.5" class="surface-part" data-surface="whole"/>
                <polygon points="10,10 90,10 70,30 30,30" class="surface-part cond-${topCond}" data-surface="${topSurfaceName}" title="${topSurfaceName}"/>
                <polygon points="30,70 70,70 90,90 10,90" class="surface-part cond-${bottomCond}" data-surface="${bottomSurfaceName}" title="${bottomSurfaceName}"/>
                <polygon points="10,10 30,30 30,70 10,90" class="surface-part cond-${leftCond}" data-surface="${leftSurfaceName}" title="${leftSurfaceName}"/>
                <polygon points="70,30 90,10 90,90 70,70" class="surface-part cond-${rightCond}" data-surface="${rightSurfaceName}" title="${rightSurfaceName}"/>
                <rect x="30" y="30" width="40" height="40" class="surface-part cond-${occlusalCond}" data-surface="occlusal" title="occlusal"/>
            `;

            return svg;
        }

        // Create Individual Tooth V2 Unit
        function createToothV2Unit(toothNumber, isUpper) {
            const primaryCond = getToothPrimaryCondition(toothNumber);
            const unit = document.createElement('div');
            unit.className = `tooth-v2-unit status-${primaryCond} ${isUpper ? 'tooth-v2-upper' : 'tooth-v2-lower'}`;
            unit.dataset.toothNumber = toothNumber;
            unit.title = `${getToothName(toothNumber)} - Status: ${primaryCond.replace('_', ' ')}`;

            // 1. Real Tooth Image Wrapper
            const imgWrap = document.createElement('div');
            imgWrap.className = 'tooth-v2-image-wrap';
            imgWrap.title = `Click to chart whole tooth #${getDisplayNumber(toothNumber)} as ${selectedCondition.replace('_', ' ')}`;

            const img = document.createElement('img');
            img.src = `${teethBaseUrl}/tooth_${toothNumber}.png`;
            img.className = 'tooth-v2-real-img';
            img.alt = `Tooth ${toothNumber}`;
            imgWrap.appendChild(img);

            // Condition badge icon on real tooth image
            const badgeOverlay = document.createElement('div');
            badgeOverlay.className = 'tooth-v2-cond-overlay';
            if (primaryCond !== 'healthy' && conditionIcons[primaryCond]) {
                const conf = conditionIcons[primaryCond];
                badgeOverlay.innerHTML = `
                    <span class="tooth-v2-badge-icon" style="background:${conf.color};" title="${conf.label}">
                        <i class="fa-solid ${conf.icon}"></i>
                    </span>
                `;
            }
            imgWrap.appendChild(badgeOverlay);

            // Clicking real tooth image charts the WHOLE tooth!
            imgWrap.addEventListener('click', function (e) {
                e.stopPropagation();
                applyToothCondition(toothNumber, 'whole', selectedCondition, unit);
            });

            unit.appendChild(imgWrap);

            // 2. 5-Surface SVG diagram
            const svg = renderSurfaceSVG(toothNumber, isUpper);
            unit.appendChild(svg);

            // 3. Tooth Number Badge
            const numBadge = document.createElement('div');
            numBadge.className = 'tooth-v2-number-badge';
            numBadge.textContent = `#${getDisplayNumber(toothNumber)}`;
            unit.appendChild(numBadge);

            // 4. Status Pill
            const pill = document.createElement('div');
            pill.className = `tooth-v2-status-pill pill-${primaryCond}`;
            pill.textContent = primaryCond === 'healthy' ? 'Sound' : primaryCond.replace('_', ' ');
            unit.appendChild(pill);

            // Surface click handlers
            svg.querySelectorAll('.surface-part').forEach(part => {
                part.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const surface = this.dataset.surface;
                    applyToothCondition(toothNumber, surface, selectedCondition, unit, this);
                });
            });

            // Context menu / right click
            unit.addEventListener('contextmenu', function (e) {
                e.preventDefault();
                openToothModal(toothNumber);
            });

            return unit;
        }

        // Apply condition via AJAX
        function applyToothCondition(toothNumber, surface, condition, unitElement, surfaceElement = null) {
            // Update local state
            chartData[`${toothNumber}_${surface}`] = {
                tooth_number: toothNumber,
                surface: surface,
                condition: condition
            };

            // Optimistic UI updates
            if (surfaceElement) {
                surfaceElement.setAttribute('class', `surface-part cond-${condition}`);
            }

            // Refresh tooth unit visual indicators
            const primary = getToothPrimaryCondition(toothNumber);
            unitElement.className = `tooth-v2-unit status-${primary} ${unitElement.classList.contains('tooth-v2-upper') ? 'tooth-v2-upper' : 'tooth-v2-lower'} selected`;

            const pill = unitElement.querySelector('.tooth-v2-status-pill');
            if (pill) {
                pill.className = `tooth-v2-status-pill pill-${primary}`;
                pill.textContent = primary === 'healthy' ? 'Sound' : primary.replace('_', ' ');
            }

            const badgeOverlay = unitElement.querySelector('.tooth-v2-cond-overlay');
            if (badgeOverlay) {
                if (primary !== 'healthy' && conditionIcons[primary]) {
                    const conf = conditionIcons[primary];
                    badgeOverlay.innerHTML = `
                        <span class="tooth-v2-badge-icon" style="background:${conf.color};" title="${conf.label}">
                            <i class="fa-solid ${conf.icon}"></i>
                        </span>
                    `;
                } else {
                    badgeOverlay.innerHTML = '';
                }
            }

            // If whole tooth changed to healthy, reset surface colors
            if (surface === 'whole' && condition === 'healthy') {
                unitElement.querySelectorAll('.surface-part').forEach(p => {
                    p.setAttribute('class', 'surface-part cond-healthy');
                });
            }

            // AJAX call
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
                    notes: `V2: Charted ${condition} on ${surface} surface.`
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(`Tooth #${getDisplayNumber(toothNumber)} (${surface}) set to ${condition.replace('_', ' ')}`);
                    // Broadcast custom event so comparison page or v1 updates automatically!
                    window.dispatchEvent(new CustomEvent('dental-chart-updated', {
                        detail: { toothNumber, surface, condition, source: 'v2' }
                    }));
                }
            })
            .catch(err => {
                console.error('Failed to update tooth:', err);
                showToast('Failed to save tooth update', 'error');
            });
        }

        function openToothModal(toothNumber) {
            const modal = document.getElementById('tooth-edit-modal');
            if (!modal) return;
            const title = document.getElementById('modal-tooth-title');
            const numInput = document.getElementById('modal-tooth-number');
            if (title) title.textContent = getToothName(toothNumber);
            if (numInput) numInput.value = toothNumber;
            modal.style.display = 'flex';
        }

        function showToast(message, type = 'success') {
            let toast = document.getElementById('odontogram-v2-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'odontogram-v2-toast';
                toast.style.position = 'fixed';
                toast.style.bottom = '24px';
                toast.style.right = '24px';
                toast.style.padding = '12px 20px';
                toast.style.borderRadius = '10px';
                toast.style.fontSize = '13px';
                toast.style.fontWeight = '600';
                toast.style.color = '#ffffff';
                toast.style.zIndex = '99999';
                toast.style.boxShadow = '0 10px 25px -5px rgba(0,0,0,0.25)';
                toast.style.transition = 'opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                document.body.appendChild(toast);
            }

            toast.style.background = type === 'error' ? '#ef4444' : '#0284c7';
            toast.innerHTML = `<i class="fa-solid fa-tooth" style="margin-right:8px;"></i> ${message}`;
            toast.style.opacity = '1';

            setTimeout(() => {
                toast.style.opacity = '0';
            }, 2500);
        }

        // Render Arches
        function renderArches() {
            const upperRow = container.querySelector('#teeth-upper-row-v2');
            const lowerRow = container.querySelector('#teeth-lower-row-v2');

            if (upperRow) {
                upperRow.innerHTML = '';
                // Quadrant 1 (18 to 11)
                upperRight.forEach(t => upperRow.appendChild(createToothV2Unit(t, true)));

                // Midline Divider
                const mid = document.createElement('div');
                mid.className = 'v2-arch-midline';
                mid.innerHTML = '<span class="v2-midline-badge">R | L</span>';
                upperRow.appendChild(mid);

                // Quadrant 2 (21 to 28)
                upperLeft.forEach(t => upperRow.appendChild(createToothV2Unit(t, true)));
            }

            if (lowerRow) {
                lowerRow.innerHTML = '';
                // Quadrant 4 (48 to 41)
                lowerRight.forEach(t => lowerRow.appendChild(createToothV2Unit(t, false)));

                // Midline Divider
                const mid = document.createElement('div');
                mid.className = 'v2-arch-midline';
                mid.innerHTML = '<span class="v2-midline-badge">R | L</span>';
                lowerRow.appendChild(mid);

                // Quadrant 3 (31 to 38)
                lowerLeft.forEach(t => lowerRow.appendChild(createToothV2Unit(t, false)));
            }

            applyViewMode();
        }

        renderArches();

        // Listen for sync events from external updates
        window.addEventListener('dental-chart-updated', function (e) {
            if (e.detail && e.detail.source !== 'v2') {
                const { toothNumber, surface, condition } = e.detail;
                chartData[`${toothNumber}_${surface}`] = {
                    tooth_number: toothNumber,
                    surface: surface,
                    condition: condition
                };
                // Re-render unit
                const unit = container.querySelector(`.tooth-v2-unit[data-tooth-number="${toothNumber}"]`);
                if (unit) {
                    const isUpper = unit.classList.contains('tooth-v2-upper');
                    const newUnit = createToothV2Unit(toothNumber, isUpper);
                    unit.replaceWith(newUnit);
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initOdontogramV2('odontogram-app-v2');
    });

    // Export globally for comparison page
    window.initOdontogramV2 = initOdontogramV2;
})();
