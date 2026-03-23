<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $organization->organization_name ?? 'Queue Kiosk' }} - Queue Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:   {{ $settings->primary_color   ?? '#3b82f6' }};
            --secondary: {{ $settings->secondary_color ?? '#8b5cf6' }};
            --accent:    {{ $settings->accent_color    ?? '#10b981' }};
            --text-color:{{ $settings->text_color      ?? '#ffffff' }};
            --primary-color:   {{ $settings->primary_color   ?? '#3b82f6' }};
            --secondary-color: {{ $settings->secondary_color ?? '#8b5cf6' }};
            --accent-color:    {{ $settings->accent_color    ?? '#10b981' }};
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
            background-attachment: fixed;
        }

        /* ─── Layout ────────────────────────────────────────────────────── */
        #app {
            position: fixed; inset: 0;
            display: flex; flex-direction: column; align-items: center;
            overflow-y: auto; overflow-x: hidden;
            padding: 1rem .75rem 1.5rem;
            gap: .75rem;
        }

        /* ─── Cards ─────────────────────────────────────────────────────── */
        .card {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px -8px rgba(0,0,0,.22), 0 0 0 1px rgba(255,255,255,.25);
        }

        /* ─── Step wizard bar ───────────────────────────────────────────── */
        .wizard-bar {
            display: flex; align-items: center; gap: .5rem;
            padding: .5rem 1rem;
        }
        .step-pill {
            display: flex; flex-direction: column; align-items: center;
            padding: .35rem .9rem; border-radius: .65rem;
            font-weight: 700; min-width: 56px;
            transition: background .25s, color .25s;
            background: #f1f5f9; color: #64748b;
        }
        .step-pill .num  { font-size: .95rem; line-height: 1; }
        .step-pill .lbl  { font-size: .65rem; margin-top: 1px; }
        .step-pill.active   { background: var(--primary-color);   color: #fff; }
        .step-pill.done     { background: var(--accent-color);    color: #fff; }
        .step-sep { color: #cbd5e1; font-size: .9rem; }

        /* ─── Step content panel ────────────────────────────────────────── */
        .step-panel {
            width: 100%; max-width: 860px;
            flex: 1; min-height: 0;
            display: flex; flex-direction: column;
        }
        .step-panel.hidden { display: none; }

        /* ─── Counter grid ──────────────────────────────────────────────── */
        .counters-scroll {
            overflow-y: auto;
            flex: 1; min-height: 120px;
            padding: .25rem;
        }
        .counters-scroll::-webkit-scrollbar { width: 5px; }
        .counters-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        .counters-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .grid-counters {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .6rem;
        }
        @media (min-width: 480px)  { .grid-counters { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 768px)  { .grid-counters { grid-template-columns: repeat(4, 1fr); } }

        .counter-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: .35rem; padding: .8rem .5rem;
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: .85rem;
            cursor: pointer;
            transition: all .22s cubic-bezier(.4,0,.2,1);
            min-height: 100px;
            text-align: center;
        }
        .counter-btn:hover  { border-color: var(--primary-color); transform: translateY(-3px); box-shadow: 0 8px 18px rgba(0,0,0,.12); }
        .counter-btn:active { transform: scale(.97); }
        .counter-btn:disabled { opacity: .45; pointer-events: none; }
        .counter-badge {
            width: 42px; height: 42px; border-radius: .5rem; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: .95rem; color: #fff;
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
        }
        .counter-name  { font-size: .85rem; font-weight: 700; color: #0f172a; line-height: 1.2; word-break: break-word; }
        .counter-desc  { font-size: .7rem;  color: #64748b; line-height: 1.2; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .counter-avail { display: flex; align-items: center; gap: 4px; }
        .dot-green     { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; flex-shrink: 0; }
        .avail-text    { font-size: .65rem; font-weight: 600; color: #374151; }

        /* ─── Animations ────────────────────────────────────────────────── */
        @keyframes fadeUp   { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scalePop { from { opacity: 0; transform: scale(.88);       } to { opacity: 1; transform: scale(1);     } }
        @keyframes spin     { to   { transform: rotate(360deg); } }
        @keyframes pulse-num {
            0%, 100% { text-shadow: 0 0 0 rgba(0,0,0,0); }
            50%       { text-shadow: 0 0 28px rgba(255,255,255,.45); }
        }
        .anim-fade-up   { animation: fadeUp   .45s ease-out both; }
        .anim-scale-pop { animation: scalePop .4s  ease-out both; }
        .spin-icon      { animation: spin 1.2s linear infinite; display: inline-block; }
        .pulse-num      { animation: pulse-num 2.2s ease-in-out infinite; }

        /* ─── Progress bar ──────────────────────────────────────────────── */
        @keyframes progress-fill { from { width: 0; } to { width: 100%; } }
        .progress-track { background: #e2e8f0; border-radius: 9999px; overflow: hidden; height: 6px; }
        .progress-fill  {
            height: 100%; border-radius: 9999px; width: 0;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            animation: progress-fill 2.2s ease-in-out forwards;
        }

        /* ─── Queue number card ─────────────────────────────────────────── */
        .queue-num-card {
            border-radius: 1rem; overflow: hidden; position: relative;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1.5rem 1rem 1.25rem;
        }
        .queue-num-card .dots-bg {
            position: absolute; inset: 0; opacity: .08;
            background-image: radial-gradient(circle, rgba(255,255,255,.5) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }
        .queue-num-card > * { position: relative; z-index: 1; }
        .queue-number {
            font-size: clamp(4rem, 16vw, 7rem);
            font-weight: 900; line-height: 1;
            color: #fff; letter-spacing: .04em;
        }

        /* ─── Inputs inside modal ───────────────────────────────────────── */
        input[type="text"], input[type="number"], input[type="email"], select, textarea {
            border: 1.5px solid #d1d5db;
            border-radius: .5rem;
            padding: .5rem .75rem;
            outline: none;
            font-size: .9rem;
            transition: border-color .2s;
            width: 100%;
            background: #fff;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--primary-color); }
        input[type="radio"] { width: auto; }

        /* ─── Settings gear ─────────────────────────────────────────────── */
        #settingsBtn {
            position: fixed; top: 14px; right: 14px; z-index: 60;
            width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff; font-size: 1.1rem;
            cursor: pointer; transition: background .2s;
            backdrop-filter: blur(8px);
        }
        #settingsBtn:hover { background: rgba(255,255,255,.3); }

        /* ─── Modal backdrop ────────────────────────────────────────────── */
        .modal-backdrop {
            position: fixed; inset: 0; z-index: 70;
            background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }
        .modal-backdrop.hidden { display: none; }
        .modal-box {
            background: #fff; border-radius: 1.25rem;
            box-shadow: 0 32px 64px rgba(0,0,0,.3);
            width: 100%; max-width: 480px;
            max-height: 90vh; overflow-y: auto;
        }
    </style>
</head>
<body>

<!-- Settings Button -->
<button id="settingsBtn" onclick="showSettings()" aria-label="Settings">
    <i class="fas fa-cog"></i>
</button>

<!-- ═══ Main App ═══════════════════════════════════════════════════════════ -->
<div id="app">

    <!-- ── Wizard Stepper ──────────────────────────────────────────────── -->
    <div class="card" style="flex-shrink:0;">
        <div class="wizard-bar">
            <div id="pill1" class="step-pill active"><span class="num">1</span><span class="lbl">Select</span></div>
            <i class="fas fa-chevron-right step-sep"></i>
            <div id="pill2" class="step-pill"><span class="num">2</span><span class="lbl">Process</span></div>
            <i class="fas fa-chevron-right step-sep"></i>
            <div id="pill3" class="step-pill"><span class="num">3</span><span class="lbl">Done</span></div>
        </div>
    </div>

    <!-- ══ STEP 1 — Select Counter ════════════════════════════════════════ -->
    <div id="step1" class="step-panel anim-fade-up">

        <!-- Org Header Card -->
        <div class="card" style="flex-shrink:0; padding: .85rem 1.25rem; margin-bottom:.65rem;">
            <div style="display:flex; align-items:center; gap:.75rem;">
                @if(!empty($settings->logo_url))
                    <img src="{{ $settings->logo_url }}" alt="Logo"
                         style="height:44px; width:auto; border-radius:.5rem; object-fit:contain; flex-shrink:0;">
                @else
                    <div style="width:44px; height:44px; border-radius:.5rem; flex-shrink:0;
                                background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));
                                display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-building" style="color:#fff; font-size:1.2rem;"></i>
                    </div>
                @endif
                <div style="flex:1; min-width:0;">
                    <div style="font-size:clamp(.95rem,3.5vw,1.35rem); font-weight:900; color:#0f172a; line-height:1.15;
                                overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" data-org-name>
                        {{ $organization->organization_name }}
                    </div>
                    <div style="font-size:.8rem; color:#64748b; font-weight:600; margin-top:1px;">
                        <i class="fas fa-hand-pointer" style="color:var(--primary-color); margin-right:.3rem;"></i>
                        Tap a counter to get your queue number
                    </div>
                </div>
            </div>
        </div>

        <!-- Counters Card -->
        <div class="card" style="flex:1; min-height:0; display:flex; flex-direction:column; padding:1rem;">
            <div style="font-size:.9rem; font-weight:800; color:#1e293b; margin-bottom:.65rem; flex-shrink:0;">
                <i class="fas fa-desktop" style="color:var(--primary-color); margin-right:.4rem;"></i>
                Available Service Counters
            </div>

            <div class="counters-scroll">
                <div id="countersGrid" class="grid-counters"></div>
                <div id="noCounters" class="hidden" style="text-align:center; padding:3rem 1rem;">
                    <i class="fas fa-clock" style="font-size:3rem; color:#cbd5e1; display:block; margin-bottom:.75rem;"></i>
                    <div style="font-weight:700; color:#475569; margin-bottom:.35rem;">No Counters Available</div>
                    <div style="font-size:.85rem; color:#94a3b8; margin-bottom:1rem;">All service counters are currently offline</div>
                    <i class="fas fa-spinner spin-icon" style="color:var(--primary-color); font-size:1.5rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ STEP 2 — Processing ════════════════════════════════════════════ -->
    <div id="step2" class="step-panel hidden">
        <div style="flex:1; display:flex; align-items:center; justify-content:center;">
            <div class="card anim-scale-pop" style="width:100%; max-width:420px; padding:2.5rem 2rem; text-align:center;">
                <div style="width:80px; height:80px; border-radius:50%; margin:0 auto 1.25rem;
                            background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));
                            display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-spinner spin-icon" style="color:#fff; font-size:2rem;"></i>
                </div>
                <div style="font-size:1.3rem; font-weight:800; color:#0f172a; margin-bottom:.4rem;">
                    Processing Your Request
                </div>
                <div style="font-size:.9rem; color:#64748b; margin-bottom:1.5rem;">
                    Generating your queue number…
                </div>
                <div class="progress-track"><div class="progress-fill"></div></div>
            </div>
        </div>
    </div>

    <!-- ══ STEP 3 — Queue Number ══════════════════════════════════════════ -->
    <div id="step3" class="step-panel hidden">
        <div style="flex:1; display:flex; align-items:flex-start; justify-content:center; overflow-y:auto; padding:.25rem 0;">
            <div class="card anim-scale-pop" id="queueContent"
                 style="width:100%; max-width:440px; padding:1.5rem 1.25rem; text-align:center;">

                <!-- Success icon -->
                <div style="width:58px; height:58px; border-radius:50%; margin:0 auto .75rem;
                            background:linear-gradient(135deg,var(--accent-color),var(--primary-color));
                            display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-check" style="color:#fff; font-size:1.5rem;"></i>
                </div>
                <div style="font-size:1.4rem; font-weight:900; color:var(--accent-color); margin-bottom:.2rem;">Success!</div>
                <div style="font-size:.875rem; color:#64748b; margin-bottom:1rem;">Your queue number is ready</div>

                <!-- Queue Number Banner -->
                <div class="queue-num-card" style="margin-bottom:1rem; text-align:center;">
                    <div class="dots-bg"></div>
                    <div style="font-size:.65rem; letter-spacing:.15em; font-weight:700; color:rgba(255,255,255,.75); margin-bottom:.35rem; text-transform:uppercase;">
                        Your Queue Number
                    </div>
                    <div class="queue-number pulse-num" id="queueNumber">—</div>
                    <div style="font-size:.9rem; font-weight:700; color:rgba(255,255,255,.9); margin-top:.5rem;" id="counterInfo"></div>
                    <div style="font-size:.78rem; color:rgba(255,255,255,.7); margin-top:.2rem;" id="queueTime"></div>
                    <div style="font-size:.72rem; color:rgba(255,255,255,.6); margin-top:.2rem; font-family:monospace;" id="ticketSignature">Sig: N/A</div>
                </div>

                <!-- Notice -->
                <div style="border-left:3px solid var(--accent-color); background:#f0fdf4; border-radius:.5rem;
                            padding:.7rem .9rem; text-align:left; margin-bottom:1rem;">
                    <div style="font-size:.8rem; color:#166534; display:flex; gap:.5rem; align-items:flex-start;">
                        <i class="fas fa-info-circle" style="color:var(--accent-color); margin-top:2px; flex-shrink:0;"></i>
                        <span><strong>Important:</strong> Please wait for your number to be called on the display monitor.</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-bottom:.6rem;">
                    <button onclick="printQueue()"
                            style="padding:.7rem; border-radius:.65rem; font-weight:700; font-size:.85rem; cursor:pointer;
                                   border:none; color:#fff;
                                   background:linear-gradient(135deg,var(--accent-color),var(--primary-color));
                                   transition:opacity .2s;"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        <i class="fas fa-print" style="margin-right:.4rem;"></i>Print
                    </button>
                    <button onclick="capturePhoto()"
                            style="padding:.7rem; border-radius:.65rem; font-weight:700; font-size:.85rem; cursor:pointer;
                                   border:none; color:#fff;
                                   background:linear-gradient(135deg,var(--secondary-color),var(--accent-color));
                                   transition:opacity .2s;"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        <i class="fas fa-camera" style="margin-right:.4rem;"></i>Save
                    </button>
                </div>
                <button onclick="finishAndReset()"
                        style="width:100%; padding:.75rem; border-radius:.75rem; font-weight:700; font-size:.9rem;
                               cursor:pointer; border:none; color:#fff;
                               background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));
                               transition:opacity .2s;"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    <i class="fas fa-redo" style="margin-right:.4rem;"></i>Get Another Number
                </button>

            </div>
        </div>
    </div>

</div><!-- #app -->

<!-- ═══ Settings Modal ════════════════════════════════════════════════════ -->
<div id="settingsModal" class="modal-backdrop hidden">
    <div class="modal-box">
        <!-- Header -->
        <div style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));
                    padding:1rem 1.25rem; border-radius:1.25rem 1.25rem 0 0;
                    display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:.65rem;">
                <div style="width:38px; height:38px; border-radius:.5rem; background:rgba(255,255,255,.18);
                            display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-cog" style="color:#fff; font-size:1.1rem;"></i>
                </div>
                <span style="font-size:1.1rem; font-weight:700; color:#fff;">Printer Settings</span>
            </div>
            <button onclick="closeSettings()"
                    style="width:36px; height:36px; border-radius:.5rem; background:rgba(255,255,255,.15);
                           border:none; color:#fff; cursor:pointer; font-size:1rem; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <div style="padding:1.25rem; display:flex; flex-direction:column; gap:1rem;">

            <!-- Printer Type -->
            <div style="background:#f8fafc; border-radius:.75rem; padding:1rem;">
                <div style="font-size:.85rem; font-weight:700; color:#374151; margin-bottom:.65rem; display:flex; align-items:center; gap:.4rem;">
                    <i class="fas fa-print" style="color:var(--primary-color);"></i> Printer Type
                </div>
                <div style="display:flex; flex-direction:column; gap:.45rem;">
                    <label style="display:flex; align-items:center; gap:.65rem; padding:.75rem 1rem;
                                  background:#fff; border-radius:.6rem; border:2px solid #e2e8f0;
                                  cursor:pointer; min-height:52px; transition:border-color .2s;"
                           onmouseover="this.style.borderColor='var(--primary-color)'" onmouseout="this.style.borderColor='#e2e8f0'">
                        <input type="radio" name="printerType" value="thermal" checked onchange="updatePrinterSettings()">
                        <div style="flex:1;">
                            <div style="font-weight:700; font-size:.875rem; color:#0f172a;">USB Thermal Printer (80mm)</div>
                            <div style="font-size:.75rem; color:#94a3b8;">Direct thermal printing via USB</div>
                        </div>
                        <i class="fas fa-receipt" style="color:#cbd5e1;"></i>
                    </label>
                    <label style="display:flex; align-items:center; gap:.65rem; padding:.75rem 1rem;
                                  background:#fff; border-radius:.6rem; border:2px solid #e2e8f0;
                                  cursor:pointer; min-height:52px; transition:border-color .2s;"
                           onmouseover="this.style.borderColor='var(--primary-color)'" onmouseout="this.style.borderColor='#e2e8f0'">
                        <input type="radio" name="printerType" value="browser" onchange="updatePrinterSettings()">
                        <div style="flex:1;">
                            <div style="font-weight:700; font-size:.875rem; color:#0f172a;">Browser Print</div>
                            <div style="font-size:.75rem; color:#94a3b8;">Standard browser print dialog</div>
                        </div>
                        <i class="fas fa-print" style="color:#cbd5e1;"></i>
                    </label>
                    <label style="display:flex; align-items:center; gap:.65rem; padding:.75rem 1rem;
                                  background:#fff; border-radius:.6rem; border:2px solid #e2e8f0;
                                  cursor:pointer; min-height:52px; transition:border-color .2s;"
                           onmouseover="this.style.borderColor='var(--primary-color)'" onmouseout="this.style.borderColor='#e2e8f0'">
                        <input type="radio" name="printerType" value="none" onchange="updatePrinterSettings()">
                        <div style="flex:1;">
                            <div style="font-weight:700; font-size:.875rem; color:#0f172a;">Screenshot Only</div>
                            <div style="font-size:.75rem; color:#94a3b8;">Save as image file</div>
                        </div>
                        <i class="fas fa-camera" style="color:#cbd5e1;"></i>
                    </label>
                </div>
            </div>

            <!-- Vendor ID -->
            <div id="thermalSettings" style="background:#eff6ff; border-radius:.75rem; padding:1rem;">
                <label style="display:block; font-size:.8rem; font-weight:700; color:#374151; margin-bottom:.45rem;">
                    <i class="fas fa-usb" style="color:var(--primary-color); margin-right:.3rem;"></i> Vendor ID (Optional)
                </label>
                <input type="text" id="vendorId" placeholder="0x0fe6">
                <div style="font-size:.72rem; color:#64748b; margin-top:.35rem;">
                    <i class="fas fa-info-circle" style="margin-right:.25rem;"></i>
                    Leave empty for default (0x0fe6 — Bixolon)
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="background:#f8fafc; padding:.85rem 1.25rem; border-radius:0 0 1.25rem 1.25rem;
                    display:flex; gap:.65rem; border-top:1px solid #e2e8f0;">
            <button onclick="testPrint()"
                    style="flex:1; padding:.7rem; border-radius:.65rem; font-weight:700; font-size:.875rem;
                           border:none; cursor:pointer; color:#fff;
                           background:linear-gradient(135deg,var(--primary-color),#6366f1);">
                <i class="fas fa-print" style="margin-right:.35rem;"></i>Test Print
            </button>
            <button onclick="saveSettings()"
                    style="flex:1; padding:.7rem; border-radius:.65rem; font-weight:700; font-size:.875rem;
                           border:none; cursor:pointer; color:#fff;
                           background:linear-gradient(135deg,#16a34a,#059669);">
                <i class="fas fa-save" style="margin-right:.35rem;"></i>Save
            </button>
        </div>
    </div>
</div>

<script>
let currentQueue  = null;
let connectedPrinter = null;
let isGenerating  = false;
let printerSettings = { type: 'thermal', vendorId: '0x0fe6' };

const countersEndpoint = '{{ route('kiosk.counters', ['organization_code' => $companyCode]) }}';
const initialCounters  = @json($onlineCounters);

/* ── settings ─────────────────────────────────────────────── */
function loadSettings() {
    try {
        const s = localStorage.getItem('kioskPrinterSettings');
        if (s) printerSettings = JSON.parse(s);
    } catch(e) {}
}
function saveSettings() {
    printerSettings.type     = document.querySelector('input[name="printerType"]:checked').value;
    printerSettings.vendorId = document.getElementById('vendorId').value || '0x0fe6';
    localStorage.setItem('kioskPrinterSettings', JSON.stringify(printerSettings));
    alert('Settings saved!');
    closeSettings();
}
function showSettings() {
    document.getElementById('settingsModal').classList.remove('hidden');
    document.querySelectorAll('input[name="printerType"]').forEach(r => r.checked = (r.value === printerSettings.type));
    document.getElementById('vendorId').value = printerSettings.vendorId || '';
    updatePrinterSettings();
}
function closeSettings() { document.getElementById('settingsModal').classList.add('hidden'); }
function updatePrinterSettings() {
    const v = document.querySelector('input[name="printerType"]:checked').value;
    document.getElementById('thermalSettings').style.display = (v === 'thermal') ? '' : 'none';
}

/* ── counter rendering ────────────────────────────────────── */
function renderCounters(counters) {
    const grid = document.getElementById('countersGrid');
    const empty = document.getElementById('noCounters');
    grid.innerHTML = '';
    if (!counters || counters.length === 0) { empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    counters.forEach(c => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'counter-btn';
        btn.onclick = () => selectCounter(c.id, c.counter_number, c.display_name);

        const badge = document.createElement('div');
        badge.className = 'counter-badge';
        badge.textContent = c.counter_number;

        const name = document.createElement('div');
        name.className = 'counter-name';
        name.textContent = c.display_name;

        const desc = document.createElement('div');
        desc.className = 'counter-desc';
        desc.textContent = c.short_description || 'Ready to serve';

        const avail = document.createElement('div');
        avail.className = 'counter-avail';
        const dot = document.createElement('span');
        dot.className = 'dot-green';
        const txt = document.createElement('span');
        txt.className = 'avail-text';
        txt.textContent = 'Available';
        avail.appendChild(dot); avail.appendChild(txt);

        btn.appendChild(badge); btn.appendChild(name); btn.appendChild(desc); btn.appendChild(avail);
        grid.appendChild(btn);
    });
}

/* ── counter polling ──────────────────────────────────────── */
let refreshInFlight = false;
let refreshController = null;
function refreshCounters() {
    if (refreshInFlight) return;
    refreshInFlight = true;
    if (refreshController) try { refreshController.abort(); } catch(e) {}
    refreshController = new AbortController();
    fetch(countersEndpoint, {
        credentials: 'same-origin', cache: 'no-store',
        headers: { 'Accept': 'application/json' },
        signal: refreshController.signal,
    })
    .then(r => r.ok ? r.json() : Promise.reject(r))
    .then(data => {
        initialCounters.splice(0, initialCounters.length, ...(data.counters || []));
        if (!document.getElementById('step1').classList.contains('hidden')) {
            renderCounters(data.counters || []);
        }
    })
    .catch(e => { if (e && e.name !== 'AbortError') console.warn('Counter refresh failed', e); })
    .finally(() => { refreshInFlight = false; });
}

/* ── wizard step management ───────────────────────────────── */
function moveToStep(n) {
    ['step1','step2','step3'].forEach((id, i) => {
        document.getElementById(id).classList.toggle('hidden', i + 1 !== n);
    });
    ['pill1','pill2','pill3'].forEach((id, i) => {
        const el = document.getElementById(id);
        el.classList.remove('active','done');
        if      (i + 1 <  n) el.classList.add('done');
        else if (i + 1 === n) el.classList.add('active');
    });
}

/* ── counter selection / queue generation ─────────────────── */
function selectCounter(counterId, counterNumber, counterName) {
    if (isGenerating) return;
    if (!counterId) { showError('Invalid counter selection. Please try again.'); return; }
    isGenerating = true;
    moveToStep(2);
    document.querySelectorAll('.counter-btn').forEach(b => b.disabled = true);

    const controller = new AbortController();
    const tid = setTimeout(() => controller.abort(), 15000);
    const url = `{{ route('kiosk.generate', ['organization_code' => $companyCode]) }}?counter_id=${encodeURIComponent(counterId)}`;

    fetch(url, {
        method: 'GET', credentials: 'same-origin',
        signal: controller.signal,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
    .then(({ ok, data }) => {
        if (!ok) throw new Error(data.message || 'Request failed');
        if (data.success && data.queue) {
            currentQueue = data.queue;
            showQueueDisplay(data.queue);
        } else {
            throw new Error(data.message || 'Failed to generate queue number');
        }
    })
    .catch(e => {
        const msg = (e && e.name === 'AbortError') ? 'Request timed out. Please try again.' : (e.message || 'Error generating queue number.');
        showError(msg);
    })
    .finally(() => clearTimeout(tid));
}

function showQueueDisplay(queue) {
    const now = new Date().toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', hour12:true });
    const parts = String(queue.queue_number).split('-');
    const display = parts[parts.length - 1];

    document.getElementById('queueNumber').textContent = display;
    document.getElementById('counterInfo').textContent = `Counter ${queue.counter.counter_number} – ${queue.counter.display_name}`;
    document.getElementById('queueTime').textContent   = `Generated at ${now}`;
    const sigEl = document.getElementById('ticketSignature');
    if (sigEl) sigEl.textContent = 'Sig: ' + (queue.signature ? String(queue.signature).slice(0,10) : 'N/A');

    moveToStep(3);
    isGenerating = false;
}

function showError(msg) {
    alert(msg);
    document.querySelectorAll('.counter-btn').forEach(b => b.disabled = false);
    moveToStep(1);
    isGenerating = false;
}

function finishAndReset() {
    currentQueue = null;
    moveToStep(1);
    document.querySelectorAll('.counter-btn').forEach(b => b.disabled = false);
    isGenerating = false;
}

/* ── printing ─────────────────────────────────────────────── */
function printQueue() {
    if (!currentQueue) { alert('No queue number to print'); return; }
    switch (printerSettings.type) {
        case 'thermal': printToThermalPrinter(); break;
        case 'browser': printToBrowser(); break;
        default: capturePhoto(); break;
    }
}

function printToThermalPrinter() {
    if (!navigator.usb) { alert('USB not supported. Using browser print.'); printToBrowser(); return; }
    if (connectedPrinter) { sendToPrinter(connectedPrinter); return; }
    let vendorId = parseInt(printerSettings.vendorId);
    if (isNaN(vendorId)) vendorId = 0x0fe6;
    navigator.usb.requestDevice({ filters: [{ vendorId }] })
        .then(d => { connectedPrinter = d; return d.open(); })
        .then(() => sendToPrinter(connectedPrinter))
        .catch(e => { console.warn('Thermal error', e); connectedPrinter = null; printToBrowser(); });
}

async function sendToPrinter(device) {
    try {
        if (!device.opened) await device.open();
        if (device.configuration === null) await device.selectConfiguration(1);
        await device.claimInterface(0);
        const enc = new TextEncoder();
        const now = new Date().toLocaleString('en-US', { month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit', hour12:true });
        const cmds = [
            '\x1B\x40','\x1B\x61\x01','\x1B\x45\x01','\x1D\x21\x11',
            '{{ $organization->organization_name }}\n',
            '\x1B\x45\x00','\x1D\x21\x00','\n',
            'QUEUE MANAGEMENT SYSTEM\n','================================\n','\n',
            '\x1B\x45\x01','\x1D\x21\x11','Priority Number\n',
            '\x1D\x21\x22', currentQueue.queue_number.split('-').pop()+'\n',
            '\x1D\x21\x00','\x1B\x45\x00','\n',
            '================================\n',
            '\x1B\x45\x01','Counter '+currentQueue.counter.counter_number+'\n',
            '\x1B\x45\x00', currentQueue.counter.display_name+'\n','\n',
            '================================\n',
            'INSTRUCTIONS:\n',
            '1. Watch the monitor display\n',
            '2. Listen for your number\n',
            '3. Proceed to Counter '+currentQueue.counter.counter_number+'\n',
            '================================\n','\n',
            'Generated: '+now+'\n',
            '\x1B\x61\x01','Thank you!\n','\n\n\n','\x1D\x56\x00',
        ];
        await device.transferOut(1, enc.encode(cmds.join('')));
    } catch(e) {
        console.error('Print error', e);
        alert('Failed to print: '+(e.message||e));
        connectedPrinter = null;
    }
}

function printToBrowser() {
    if (!currentQueue) return;
    const win = window.open('','_blank','width=380,height=560');
    const now = new Date().toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit',hour12:true});
    const num = currentQueue.queue_number.split('-').pop();
    win.document.write(`<!DOCTYPE html><html><head><title>Queue ${num}</title>
    <style>
        @media print { @page { margin:0; size:80mm auto; } body { margin:0; padding:8mm; } }
        body { font-family:Arial,sans-serif; text-align:center; padding:20px; max-width:300px; margin:0 auto; }
        .org  { font-size:18px; font-weight:bold; margin-bottom:8px; }
        .hdr  { font-size:16px; font-weight:bold; padding-bottom:12px; border-bottom:2px double #000; margin-bottom:12px; }
        .lbl  { font-size:14px; text-transform:uppercase; margin-bottom:6px; }
        .num  { font-size:80px; font-weight:900; border:4px solid #000; padding:18px; letter-spacing:4px; margin:16px 0; }
        .ctr  { font-size:20px; font-weight:bold; margin:10px 0 4px; }
        .cname{ font-size:16px; color:#333; margin-bottom:16px; }
        .inst { font-size:13px; border-top:2px double #000; border-bottom:2px double #000; padding:12px; text-align:left; line-height:1.7; margin-bottom:12px; }
        .foot { font-size:12px; color:#555; border-top:1px dashed #999; padding-top:10px; }
    </style></head><body>
        <div class="org" data-org-name>{{ $organization->organization_name }}</div>
        <div class="hdr">QUEUE MANAGEMENT SYSTEM</div>
        <div class="lbl">Priority Number</div>
        <div class="num">${num}</div>
        <div class="ctr">Counter ${currentQueue.counter.counter_number}</div>
        <div class="cname">${currentQueue.counter.display_name}</div>
        <div class="inst"><strong style="display:block;text-align:center;margin-bottom:6px;">📋 INSTRUCTIONS</strong>
            1. Watch the monitor display<br>2. Listen for your number<br>
            3. Proceed to Counter ${currentQueue.counter.counter_number}<br>4. Keep this ticket visible</div>
        <div class="foot">Generated: ${now}<br><strong>Thank you for your patience!</strong></div>
    </body></html>`);
    win.document.close();
    win.onload = () => { setTimeout(() => { win.print(); setTimeout(() => win.close(), 500); }, 250); };
}

/* ── photo capture ────────────────────────────────────────── */
function capturePhoto() {
    if (!currentQueue) { alert('No queue number to capture'); return; }
    generateTicketImage(currentQueue)
        .then(() => alert('Queue ticket saved.'))
        .catch(e => { console.error(e); alert('Could not save ticket image. Please try printing instead.'); });
}

async function generateTicketImage(queue) {
    return new Promise((resolve, reject) => {
        try {
            const orgName = `{{ $organization->organization_name }}` || 'Queue System';
            const nowStr  = new Date().toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit',hour12:true});
            const display = String(queue.queue_number).split('-').pop();
            const S=2, W=600, H=1100;
            const canvas = document.createElement('canvas');
            canvas.width = W*S; canvas.height = H*S;
            const ctx = canvas.getContext('2d');
            ctx.scale(S,S);
            ctx.fillStyle='#fff'; ctx.fillRect(0,0,W,H);

            function cx(text, y, font, color) {
                ctx.font = font; ctx.fillStyle = color||'#111';
                const m = ctx.measureText(text);
                ctx.fillText(text, (W - m.width)/2, y);
            }
            ctx.textBaseline='top';
            cx(orgName,       20, '800 20px Arial', '#111');
            cx('QUEUE MANAGEMENT SYSTEM', 50, '700 16px Arial','#111');
            cx('PRIORITY NUMBER',         78, '700 14px Arial','#444');

            const bY=110, bH=220;
            ctx.fillStyle='#fff'; ctx.strokeStyle='#111'; ctx.lineWidth=3;
            ctx.fillRect(40,bY,W-80,bH); ctx.strokeRect(40,bY,W-80,bH);
            ctx.textBaseline='middle'; ctx.fillStyle='#000'; ctx.font='900 130px Arial';
            const nm = ctx.measureText(display);
            ctx.fillText(display,(W-nm.width)/2, bY+bH/2);

            const barcodeText = '* '+display.split('').join(' ')+' *';
            cx(barcodeText, bY+bH+30, '700 24px Courier New', '#111');
            cx('Counter '+queue.counter.counter_number, bY+bH+70, '700 18px Arial','#111');
            cx(queue.counter.display_name, bY+bH+95, '600 16px Arial','#444');

            const iY=bY+bH+130;
            ctx.textBaseline='top';
            ctx.font='700 13px Arial'; ctx.fillStyle='#111';
            ctx.fillText('📋 INSTRUCTIONS', 60, iY);
            ctx.font='13px Arial'; let ly=iY+24;
            ['1. Watch the monitor display','2. Listen for your number',
             `3. Proceed to Counter ${queue.counter.counter_number}`,
             '4. Keep this ticket visible'].forEach(l=>{ ctx.fillText(l,60,ly); ly+=22; });
            cx('Generated: '+nowStr, ly+14, '12px Arial','#555');
            cx('Thank you for your patience!', ly+34, '700 13px Arial','#111');

            // QR code
            try {
                const verifyUrl = `${location.origin}/{{ $companyCode }}/kiosk/verify-ticket?queue_number=${encodeURIComponent(queue.queue_number)}&signature=${encodeURIComponent(queue.signature||'')}`;
                const qrSz=160;
                const img = new Image(); img.crossOrigin='Anonymous';
                img.onload = () => {
                    try { ctx.drawImage(img, W-40-qrSz, ly+55, qrSz, qrSz); } catch(e) {}
                    canvas.toBlob(blob => blobDownload(blob, queue, resolve, reject), 'image/png');
                };
                img.onerror = () => canvas.toBlob(blob => blobDownload(blob, queue, resolve, reject), 'image/png');
                img.src = `https://chart.googleapis.com/chart?cht=qr&chs=${qrSz}x${qrSz}&chl=${encodeURIComponent(verifyUrl)}`;
                return;
            } catch(e) {}

            canvas.toBlob(blob => blobDownload(blob, queue, resolve, reject), 'image/png');
        } catch(e) { reject(e); }
    });
}

function blobDownload(blob, queue, resolve, reject) {
    if (!blob) { return reject(new Error('Blob generation failed')); }
    try {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = `ticket-${queue.queue_number}.png`;
        document.body.appendChild(a); a.click();
        document.body.removeChild(a); URL.revokeObjectURL(url);
        resolve();
    } catch(e) { reject(e); }
}

function testPrint() {
    if (!currentQueue) currentQueue = { queue_number:'TEST-0001', counter:{ counter_number:'1', display_name:'Test Counter' } };
    printQueue();
}

/* ── bootstrap ────────────────────────────────────────────── */
loadSettings();
renderCounters(initialCounters);
setInterval(refreshCounters, 5000);
</script>
</body>
</html>