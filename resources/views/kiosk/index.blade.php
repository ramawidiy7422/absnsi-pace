<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Digital — {{ $namaPerusahaan }}</title>
    <link href="/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="/fonts/inter-local.css" rel="stylesheet">
    <script src="{{ asset('js/jsQR.min.js') }}"></script>
<style>
:root {
    --accent:   #06b6d4;
    --accent2:  #818cf8;
    --green:    #10b981;
    --amber:    #f59e0b;
    --red:      #ef4444;
    --bg:       #050c1a;
    --surface:  rgba(255,255,255,0.035);
    --border:   rgba(255,255,255,0.07);
    --text:     rgba(255,255,255,0.94);
    --muted:    rgba(255,255,255,0.58);
    --radius:   20px;
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

html { height:100%; overflow:hidden; }
body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    color: var(--text);
    height: 100%;
    overflow: hidden;
    position: relative;
}

/* ── ANIMATED BACKGROUND ─────────────────────────────────────── */
.bg-orbs {
    position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden;
}
.orb {
    position: absolute; border-radius: 50%;
    filter: blur(80px); opacity: 0.18;
    animation: orbFloat 12s ease-in-out infinite;
}
.orb1 { width:500px; height:500px; background:#0891b2; top:-100px; left:-100px; animation-delay:0s; }
.orb2 { width:400px; height:400px; background:#4f46e5; bottom:-80px; right:-80px; animation-delay:-4s; }
.orb3 { width:300px; height:300px; background:#059669; top:50%; left:50%; transform:translate(-50%,-50%); animation-delay:-8s; }
@keyframes orbFloat {
    0%,100% { transform: translate(0,0) scale(1); }
    33%      { transform: translate(30px,-20px) scale(1.05); }
    66%      { transform: translate(-20px,25px) scale(0.95); }
}

/* Grid noise texture */
.bg-grid {
    position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background-image:
        linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 40px 40px;
}

/* ── ROOT LAYOUT ──────────────────────────────────────────────── */
.kiosk-root {
    position: relative; z-index: 1;
    width: 100vw; height: 100vh;
    display: grid;
    /* Desktop: camera left 55% | info right 45% */
    grid-template-columns: 55fr 45fr;
    grid-template-rows: 1fr;
    grid-template-areas: "cam info";
}

/* ── CAM PANEL ────────────────────────────────────────────────── */
.cam-panel {
    grid-area: cam;
    display: flex; flex-direction: column;
    padding: 20px 16px 20px 20px;
    gap: 14px;
    border-right: 1px solid var(--border);
    background: rgba(0,0,0,0.12);
    min-height: 0;
}

/* Brand */
.brand {
    display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
.brand-logo {
    width: 46px; height: 46px; border-radius: 12px;
    background: #fff; display: flex; align-items: center; justify-content: center;
    padding: 5px; flex-shrink: 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.1);
}
.brand-logo img { width: 34px; height: 34px; object-fit: contain; }
.brand-text { flex: 1; min-width: 0; }
.brand-name {
    font-size: 12px; font-weight: 700;
    color: rgba(255,255,255,0.82); white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.brand-sub { font-size: 10px; color: var(--muted); margin-top: 1px; }
.live-chip {
    display: flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 20px;
    background: rgba(6,182,212,0.1); border: 1px solid rgba(6,182,212,0.3);
    font-size: 10px; font-weight: 700; color: #22d3ee;
    letter-spacing: 1px; text-transform: uppercase; flex-shrink: 0;
}
.live-chip .pulse {
    width: 6px; height: 6px; border-radius: 50%; background: #22d3ee;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%,100% { opacity:1; box-shadow: 0 0 0 0 rgba(34,211,238,0.4); }
    50%      { opacity:0.6; box-shadow: 0 0 0 5px rgba(34,211,238,0); }
}

/* Mode pills */
.mode-pills {
    display: flex; gap: 8px; flex-shrink: 0;
}
.mpill {
    flex: 1; padding: 8px 12px;
    border-radius: 10px; border: 1px solid var(--border);
    background: var(--surface);
    display: flex; align-items: center; gap: 7px;
    font-size: 11px; font-weight: 600; color: var(--muted);
    transition: all 0.35s cubic-bezier(.4,0,.2,1);
}
.mpill i { font-size: 13px; transition: transform 0.3s; }
.mpill.active-face {
    background: rgba(16,185,129,0.15);
    border-color: rgba(16,185,129,0.5);
    color: #34d399;
    box-shadow: 0 0 20px rgba(16,185,129,0.15);
}
.mpill.active-qr {
    background: rgba(239,68,68,0.15);
    border-color: rgba(239,68,68,0.5);
    color: #fb7185;
    box-shadow: 0 0 20px rgba(239,68,68,0.15);
}
.mpill.active-face i, .mpill.active-qr i { transform: scale(1.2); }

/* Camera viewport */
.cam-viewport {
    flex: 1; min-height: 0; position: relative;
    border-radius: 18px; overflow: hidden;
    background: #000;
    box-shadow: 0 8px 40px rgba(0,0,0,0.5), 0 0 0 1px var(--border);
}
#video {
    width: 100%; height: 100%; display: block;
    object-fit: cover;
}
#cvs, #cvs-qr { display: none; }

/* Scan overlay corner brackets */
.scan-corners {
    position: absolute; inset: 0; pointer-events: none;
}
.corner {
    position: absolute; width: 28px; height: 28px;
    border-color: rgba(255,255,255,0.15); border-style: solid;
}
.corner.tl { top:16px; left:16px; border-width:3px 0 0 3px; border-radius:6px 0 0 0; }
.corner.tr { top:16px; right:16px; border-width:3px 3px 0 0; border-radius:0 6px 0 0; }
.corner.bl { bottom:16px; left:16px; border-width:0 0 3px 3px; border-radius:0 0 0 6px; }
.corner.br { bottom:16px; right:16px; border-width:0 3px 3px 0; border-radius:0 0 6px 0; }

/* QR area indicator */
#qr-zone {
    position: absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    width:42%; aspect-ratio:1; pointer-events:none;
    opacity:0; transition: opacity 0.3s;
}
#qr-zone.show { opacity:1; }
#qr-zone::before,#qr-zone::after,
#qr-zone span::before,#qr-zone span::after {
    content:''; position:absolute; width:24px; height:24px;
    border-color:#fb7185; border-style:solid;
    transition: border-color 0.3s;
}
#qr-zone::before  {top:0;left:0;border-width:3px 0 0 3px;border-radius:5px 0 0 0;}
#qr-zone::after   {top:0;right:0;border-width:3px 3px 0 0;border-radius:0 5px 0 0;}
#qr-zone span::before{bottom:0;left:0;border-width:0 0 3px 3px;border-radius:0 0 0 5px;}
#qr-zone span::after {bottom:0;right:0;border-width:0 3px 3px 0;border-radius:0 0 5px 0;}
.qr-scanline {
    position:absolute; left:4%; right:4%; height:2px;
    background:linear-gradient(90deg,transparent,#fb7185,transparent);
    animation:scan 2s ease-in-out infinite;
    display:none;
}
#qr-zone.show .qr-scanline { display:block; }
@keyframes scan { 0%{top:5%}50%{top:90%}100%{top:5%} }

/* Face ring */
#face-ring {
    position:absolute; top:50%; left:50%;
    transform:translate(-50%,-50%);
    width:40%; aspect-ratio:1; border-radius:50%;
    border:2px solid transparent;
    pointer-events:none;
    transition: border-color 0.4s, box-shadow 0.4s;
}
#face-ring.lit {
    border-color: #34d399;
    box-shadow: 0 0 30px rgba(52,211,153,0.4), inset 0 0 30px rgba(52,211,153,0.06);
}

/* Status bar */
.cam-statusbar {
    position: absolute; bottom:0; left:0; right:0;
    padding: 9px 16px;
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 600;
    border-radius: 0 0 18px 18px;
    backdrop-filter: blur(12px);
    transition: background 0.3s;
}
.sb-idle { background: rgba(0,0,0,0.7); color: var(--muted); }
.sb-face { background: rgba(16,185,129,0.8); color:#fff; }
.sb-qr   { background: rgba(239,68,68,0.8); color:#fff; }
.sb-proc { background: rgba(99,102,241,0.85); color:#fff; }
.sb-ok   { background: rgba(52,211,153,0.9); color:#022c22; }
.sb-err  { background: rgba(239,68,68,0.85); color:#fff; }
.sb-dot  { width:8px; height:8px; border-radius:50%; flex-shrink:0; transition:background 0.3s; }

/* Cooldown ring */
#cd-ring {
    position:absolute; top:12px; right:12px;
    width:44px; height:44px; display:none;
    filter: drop-shadow(0 0 8px rgba(52,211,153,0.6));
}
#cd-ring svg { transform:rotate(-90deg); }
#cd-ring circle { fill:none; stroke-width:4; stroke-linecap:round; }
.cd-bg  { stroke:rgba(255,255,255,0.1); }
#cd-fg  { stroke:#34d399; stroke-dasharray:113; stroke-dashoffset:113; transition:stroke-dashoffset 1s linear; }
#cd-num {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    font-size:14px; font-weight:900; color:#34d399;
}

/* Cam bottom controls */
.cam-controls { flex-shrink:0; display:flex; gap:8px; }
.btn-manual {
    flex:1; padding:10px 14px;
    background: rgba(6,182,212,0.08);
    border: 1px solid rgba(6,182,212,0.25);
    border-radius: 12px;
    color: #22d3ee; font-size: 12px; font-weight: 600;
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;
    transition:all 0.2s;
}
.btn-manual:hover:not(:disabled) {
    background:rgba(6,182,212,0.18); border-color:#22d3ee;
    box-shadow: 0 0 20px rgba(6,182,212,0.2);
    transform: translateY(-1px);
}
.btn-manual:disabled { opacity:0.25; cursor:not-allowed; }

.cam-err-msg {
    display:none; text-align:center; flex-shrink:0;
    padding:10px 14px; border-radius:10px;
    background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3);
    color:#fb7185; font-size:12px; font-weight:500;
}

/* ── INFO PANEL ───────────────────────────────────────────────── */
.info-panel {
    grid-area: info;
    display: flex; flex-direction: column;
    padding: 20px 20px 20px 16px;
    gap: 0; min-height: 0;
    overflow: hidden;
}

/* Clock area */
.clock-area { flex-shrink:0; margin-bottom:18px; }
.sys-label {
    font-size: 10px; font-weight: 600; letter-spacing: 2px;
    text-transform: uppercase; color: var(--muted); margin-bottom:8px;
    display: flex; align-items: center; gap: 8px;
}
.sys-label::after {
    content:''; flex:1; height:1px; background:var(--border);
}
.clock-time {
    font-size: clamp(42px, 6vw, 72px);
    font-weight: 900; line-height:1; letter-spacing:1px;
    font-variant-numeric: tabular-nums;
    background: linear-gradient(135deg, #fff 0%, #22d3ee 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    background-clip:text;
}
.clock-date {
    font-size:12px; color:var(--muted); margin-top:6px; letter-spacing:0.3px;
}
.company-label {
    margin-top:10px; font-size:14px; font-weight:700;
    color:rgba(255,255,255,0.75); letter-spacing:-0.2px;
}
.tagline-label {
    font-size:11px; color:var(--muted); margin-top:3px;
}

/* Separator */
.sep { height:1px; background:var(--border); flex-shrink:0; margin-bottom:16px; }

/* Result card */
.result-card {
    flex:1; min-height:0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    padding:20px; position:relative; overflow:hidden;
}
.result-card::before {
    content:''; position:absolute; inset:0; border-radius:var(--radius);
    background:radial-gradient(ellipse 90% 50% at 50% 0%, rgba(6,182,212,0.05) 0%, transparent 60%);
    pointer-events:none;
}

/* ── IDLE STATE ───────────────────────────────────────────────── */
#s-idle { text-align:center; position:relative; z-index:1; }
.idle-ring {
    width:88px; height:88px; border-radius:50%; margin:0 auto 16px;
    border:1.5px solid rgba(255,255,255,0.1);
    display:flex; align-items:center; justify-content:center;
    position:relative;
}
.idle-ring::before {
    content:''; position:absolute; inset:-10px; border-radius:50%;
    border:1px dashed rgba(255,255,255,0.06);
    animation:spin 15s linear infinite;
}
.idle-ring::after {
    content:''; position:absolute; inset:-20px; border-radius:50%;
    border:1px dashed rgba(255,255,255,0.03);
    animation:spin 25s linear infinite reverse;
}
@keyframes spin { to{transform:rotate(360deg);} }
.idle-ring i { font-size:32px; color:rgba(255,255,255,0.45); }
.idle-title { font-size:16px; font-weight:700; color:rgba(255,255,255,0.85); margin-bottom:10px; }
.idle-steps {
    list-style:none; display:flex; flex-direction:column; gap:8px;
    text-align:left; display:inline-block;
}
.idle-steps li {
    font-size:12px; color:var(--muted); display:flex; align-items:center; gap:10px;
}
.idle-step-icon {
    width:26px; height:26px; border-radius:8px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:11px;
}
.step-face { background:rgba(16,185,129,0.12); color:#34d399; border:1px solid rgba(16,185,129,0.2); }
.step-qr   { background:rgba(239,68,68,0.12);  color:#fb7185; border:1px solid rgba(239,68,68,0.2); }

/* ── LOADING STATE ────────────────────────────────────────────── */
#s-load { display:none; text-align:center; position:relative; z-index:1; }
.loader {
    width:52px; height:52px; border-radius:50%; margin:0 auto 16px;
    border:3px solid rgba(6,182,212,0.1); border-top:3px solid #22d3ee;
    animation:spin 0.7s linear infinite;
}
#load-txt { font-size:13px; color:var(--muted); font-weight:500; }

/* ── SUCCESS STATE ────────────────────────────────────────────── */
#s-ok {
    display:none; text-align:center; width:100%; cursor:pointer;
    user-select:none; position:relative; z-index:1;
    animation:fadeInUp 0.4s cubic-bezier(.4,0,.2,1);
}
@keyframes fadeInUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
.ok-photo-wrap { position:relative; display:inline-block; margin-bottom:12px; }
#ok-foto {
    width:88px; height:88px; border-radius:50%; object-fit:cover;
    border:3px solid #34d399;
    box-shadow:0 0 0 6px rgba(52,211,153,0.1), 0 0 40px rgba(52,211,153,0.35);
    display:block;
}
.ok-check {
    position:absolute; bottom:2px; right:2px;
    width:22px; height:22px; border-radius:50%;
    background:#34d399; border:2px solid var(--bg);
    display:flex; align-items:center; justify-content:center; font-size:9px;
}
.ok-check.out { background:#f59e0b; }
#ok-nama {
    font-size:clamp(16px,2.5vw,22px); font-weight:800; color:#fff;
    margin-bottom:2px; letter-spacing:-0.3px;
}
#ok-sub { font-size:11px; color:var(--muted); margin-bottom:12px; }
#ok-badge { margin-bottom:4px; }
.badge-in, .badge-out {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 22px; border-radius:22px;
    font-size:13px; font-weight:700; letter-spacing:0.3px;
}
.badge-in      { background:linear-gradient(135deg,#059669,#34d399); color:#fff; box-shadow:0 6px 20px rgba(52,211,153,0.4); }
.badge-out     { background:linear-gradient(135deg,#d97706,#fbbf24); color:#fff; box-shadow:0 6px 20px rgba(251,191,36,0.4); }
.badge-late    { background:linear-gradient(135deg,#dc2626,#f87171); color:#fff; box-shadow:0 6px 20px rgba(220,38,38,0.4); }
.badge-early   { background:linear-gradient(135deg,#d97706,#fbbf24); color:#fff; box-shadow:0 6px 20px rgba(217,119,6,0.4); }
#ok-metode { font-size:10px; color:var(--muted); margin:5px 0 10px; }

/* Duration box */
#ok-dur-box {
    display:none; width:100%; margin:4px 0 6px;
    background:rgba(52,211,153,0.05); border:1px solid rgba(52,211,153,0.15);
    border-radius:14px; padding:12px 16px;
}
#ok-dur-val {
    font-size:clamp(22px,3.5vw,30px); font-weight:900;
    color:#34d399; letter-spacing:-0.5px;
}
.dur-lbl { font-size:10px; color:var(--muted); margin-top:2px; text-transform:uppercase; letter-spacing:1px; }
#ok-early { display:none; font-size:11px; color:#fb7185; margin:4px 0; }
#ok-early i { margin-right:4px; }

/* Confidence bar */
.conf-row { width:100%; margin-top:10px; }
.conf-head { display:flex; justify-content:space-between; font-size:10px; color:var(--muted); margin-bottom:5px; }
.conf-track { background:rgba(255,255,255,0.06); border-radius:6px; height:4px; overflow:hidden; }
.conf-fill  { height:100%; border-radius:6px; background:linear-gradient(90deg,#059669,#34d399); transition:width 0.6s; }
#ok-time { font-size:16px; font-weight:700; color:rgba(255,255,255,0.85); margin-top:10px; }
#ok-msg  { font-size:11px; color:var(--muted); margin-top:3px; }
.tap-hint {
    display:none; margin-top:12px; font-size:11px; color:var(--muted);
    animation:blink2 1.5s infinite;
}
@keyframes blink2 { 0%,100%{opacity:0.4}50%{opacity:0.9} }

/* ── ERROR STATE ──────────────────────────────────────────────── */
#s-err { display:none; text-align:center; position:relative; z-index:1; }
.err-ring {
    width:76px; height:76px; border-radius:50%; margin:0 auto 14px;
    background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2);
    display:flex; align-items:center; justify-content:center;
    font-size:28px; color:#fb7185;
    animation:shake 0.4s cubic-bezier(.4,0,.2,1);
}
@keyframes shake {
    0%,100%{transform:translateX(0)}
    20%,60%{transform:translateX(-6px)}
    40%,80%{transform:translateX(6px)}
}
#err-msg  { font-size:14px; font-weight:700; color:rgba(255,255,255,0.85); margin-bottom:6px; }
#err-hint { font-size:11px; color:var(--muted); }

/* ── FOOTER ───────────────────────────────────────────────────── */
.kiosk-footer {
    flex-shrink:0; margin-top:12px;
    font-size:9.5px; color:rgba(255,255,255,0.12); text-align:center;
    letter-spacing:0.5px;
}

/* ══ RESPONSIVE ════════════════════════════════════════════════ */

/* Laptop kecil 1024–1280px */
@media (max-width:1280px) {
    .kiosk-root { grid-template-columns: 58fr 42fr; }
    .clock-time { font-size: clamp(38px,5.5vw,60px); }
    .cam-panel  { padding:16px 12px 16px 16px; }
    .info-panel { padding:16px 16px 16px 12px; }
}

/* Tablet landscape 768–1023px — tetap split tapi proporsional */
@media (max-width:1023px) and (min-width:769px) {
    .kiosk-root { grid-template-columns: 60fr 40fr; }
    .clock-time { font-size:38px; }
    .company-label { font-size:13px; }
    .cam-panel { padding:14px 10px 14px 14px; gap:10px; }
    .info-panel { padding:14px 14px 14px 10px; }
    #ok-foto { width:74px; height:74px; }
    #ok-nama  { font-size:17px; }
}

/* Tablet portrait & HP landscape ≤768px — STACK vertikal */
@media (max-width:768px) {
    html, body { overflow: auto; }
    .kiosk-root {
        grid-template-columns: 1fr;
        grid-template-rows: 55vh 1fr;
        grid-template-areas:
            "cam"
            "info";
        height: auto;
        min-height: 100vh;
    }
    .cam-panel {
        border-right: none;
        border-bottom: 1px solid var(--border);
        padding: 14px; gap: 10px;
        height: 55vh;
    }
    .info-panel { padding: 14px; height: auto; overflow: auto; }
    .clock-area { margin-bottom: 12px; }
    .clock-time { font-size: 40px; }
    .company-label { font-size: 13px; }
    .tagline-label { display: none; }
    .result-card { min-height: 240px; }
    #ok-foto { width:72px; height:72px; }
}

/* HP kecil portrait ≤480px */
@media (max-width:480px) {
    .cam-panel { height: 50vh; min-height:240px; padding:10px; gap:8px; }
    .info-panel { padding:10px; }
    .brand-name { font-size:11px; }
    .brand-sub, .live-chip span { display:none; }
    .live-chip { padding:4px 8px; }
    .clock-time { font-size:36px; }
    .clock-date { font-size:11px; }
    .company-label { font-size:12px; }
    .result-card { padding:16px; min-height:200px; }
    #ok-foto { width:64px; height:64px; }
    #ok-nama  { font-size:16px; }
    .badge-in, .badge-out { padding:7px 16px; font-size:12px; }
    .btn-manual { font-size:11px; padding:9px; }
    .mpill { font-size:10px; padding:7px 10px; }
    .idle-ring { width:70px; height:70px; }
    .idle-ring i { font-size:26px; }
}

/* Landscape HP kecil (tinggi ≤420px) — compact */
@media (max-height:420px) and (orientation:landscape) {
    .kiosk-root {
        grid-template-columns: 55fr 45fr;
        grid-template-rows: 1fr;
        grid-template-areas: "cam info";
        height: 100vh; overflow: hidden;
    }
    html, body { overflow: hidden; }
    .cam-panel { height: 100vh; padding:10px; border-right:1px solid var(--border); border-bottom:none; }
    .info-panel { height: 100vh; padding:10px; overflow:auto; }
    .clock-time { font-size:28px; }
    .company-label { display:none; }
    .tagline-label { display:none; }
    .clock-area { margin-bottom:8px; }
    .sep { margin-bottom:10px; }
    .brand-logo { width:36px; height:36px; }
    .brand-logo img { width:26px; height:26px; }
    .result-card { padding:12px; }
    #ok-foto { width:56px; height:56px; }
    #ok-nama { font-size:15px; }
}
</style>
</head>
<body>
<div class="bg-orbs">
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>
    <div class="orb orb3"></div>
</div>
<div class="bg-grid"></div>

<div class="kiosk-root">

    <!-- ═══════════ CAM PANEL ═══════════ -->
    <div class="cam-panel">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-logo">
                <img src="{{ $logoPerusahaan ?? '/images/logokecil.png' }}" alt="{{ $namaPerusahaan ?? 'Logo' }}">
            </div>
            <div class="brand-text">
                <div class="brand-name">{{ $namaPerusahaan }}</div>
                <div class="brand-sub">Sistem Kehadiran Digital</div>
            </div>
            <div class="live-chip">
                <div class="pulse"></div>
                <span>Live</span>
            </div>
        </div>

        <!-- Mode pills -->
        <div class="mode-pills">
            <div class="mpill" id="pill-face">
                <i class="fas fa-user-circle"></i> Face Recognition
            </div>
            <div class="mpill" id="pill-qr">
                <i class="fas fa-qrcode"></i> QR Code
            </div>
        </div>

        <!-- Camera viewport -->
        <div class="cam-viewport">
            <video id="video" autoplay playsinline muted></video>
            <canvas id="cvs"></canvas>
            <canvas id="cvs-qr"></canvas>

            <!-- Corner decorations -->
            <div class="scan-corners">
                <div class="corner tl"></div>
                <div class="corner tr"></div>
                <div class="corner bl"></div>
                <div class="corner br"></div>
            </div>

            <!-- QR scan area -->
            <div id="qr-zone"><span></span><div class="qr-scanline"></div></div>

            <!-- Face ring -->
            <div id="face-ring"></div>

            <!-- Status bar -->
            <div class="cam-statusbar sb-idle" id="sbar">
                <div class="sb-dot" id="sb-dot" style="background:rgba(255,255,255,.3);"></div>
                <span id="sb-txt">Memulai kamera...</span>
            </div>

            <!-- Cooldown ring -->
            <div id="cd-ring">
                <svg width="44" height="44" viewBox="0 0 44 44">
                    <circle class="cd-bg" cx="22" cy="22" r="18"/>
                    <circle id="cd-fg" cx="22" cy="22" r="18"/>
                </svg>
                <div id="cd-num">5</div>
            </div>
        </div>

        <!-- Controls -->
        <div class="cam-controls">
            <button class="btn-manual" id="btn-m" onclick="manualFace()">
                <i class="fas fa-camera"></i> Deteksi Wajah Manual
            </button>
            <button class="btn-manual" id="btn-fmk" onclick="manualFmk()">
                <i class="fas fa-walking"></i> Absen Pergi/Kembali (FMK)
            </button>
        </div>
        <div class="cam-err-msg" id="cam-err">
            <i class="fas fa-video-slash"></i>&nbsp; Kamera tidak tersedia — izinkan akses kamera di browser
        </div>

    </div><!-- /cam-panel -->

    <!-- ═══════════ INFO PANEL ═══════════ -->
    <div class="info-panel">

        <!-- Clock -->
        <div class="clock-area">
            <div class="sys-label">Check In &amp; Check Out</div>
            <div class="clock-time" id="jam">00:00:00</div>
            <div class="clock-date" id="tgl"></div>
            <div class="company-label">{{ $namaPerusahaan }}</div>
            <div class="tagline-label">Face Recognition &amp; QR Code</div>
        </div>

        <div class="sep"></div>

        <!-- Result card -->
        <div class="result-card">

            <!-- IDLE -->
            <div id="s-idle">
                <div class="idle-ring"><i class="fas fa-fingerprint"></i></div>
                <div class="idle-title">Siap Mendeteksi</div>
                <ul class="idle-steps">
                    <li>
                        <div class="idle-step-icon step-face"><i class="fas fa-user"></i></div>
                        Hadapkan wajah ke kamera
                    </li>
                    <li>
                        <div class="idle-step-icon step-qr"><i class="fas fa-qrcode"></i></div>
                        Atau scan QR Code karyawan
                    </li>
                </ul>
            </div>

            <!-- LOADING -->
            <div id="s-load">
                <div class="loader"></div>
                <p id="load-txt">Memproses...</p>
            </div>

            <!-- SUCCESS -->
            <div id="s-ok" onclick="if(STATE==='cooldown')resetToIdle()">
                <div class="ok-photo-wrap">
                    <img id="ok-foto" src="" alt="" onerror="this.style.display='none'">
                    <div class="ok-check" id="ok-check"><i class="fas fa-check" style="font-size:8px;"></i></div>
                </div>
                <div id="ok-nama"></div>
                <div id="ok-nik" style="font-size:11px;color:rgba(255,255,255,0.55);margin:-4px 0 4px;"></div>
                <div id="ok-sub"></div>
                <div id="ok-badge"></div>
                <div id="ok-status-info" style="display:none;font-size:11px;color:#fca5a5;margin:3px 0 0;font-weight:600;"></div>
                <div id="ok-metode"></div>
                <div id="ok-dur-box">
                    <div id="ok-dur-val"></div>
                    <div class="dur-lbl">Durasi Kerja</div>
                </div>
                <div id="ok-early"><i class="fas fa-exclamation-triangle"></i> Pulang lebih awal</div>
                <div id="ok-lembur" style="display:none;font-size:11px;color:#fbbf24;margin:4px 0;"><i class="fas fa-business-time" style="margin-right:4px;"></i> Potensi lembur terdeteksi — cek portal</div>
                <div class="conf-row">
                    <div class="conf-head">
                        <span id="conf-lbl">Akurasi</span>
                        <span id="conf-val">—</span>
                    </div>
                    <div class="conf-track"><div class="conf-fill" id="conf-fill" style="width:0%"></div></div>
                </div>
                <div id="ok-time"></div>
                <div id="ok-msg"></div>
                <div class="tap-hint" id="tap-hint"><i class="fas fa-hand-pointer"></i> Tap untuk lanjut</div>
            </div>

            <!-- ERROR -->
            <div id="s-err">
                <div class="err-ring"><i class="fas fa-user-slash" id="err-ico"></i></div>
                <p id="err-msg">Tidak dikenali</p>
                <small id="err-hint">Pastikan pencahayaan cukup</small>
            </div>

            {{-- State: Sudah Absen Lengkap --}}
            <div id="s-sudah" style="display:none;text-align:center;position:relative;z-index:1;animation:fadeInUp .3s ease;">
                <div style="width:80px;height:80px;border-radius:50%;background:rgba(16,185,129,0.15);border:3px solid var(--green);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:32px;">
                    <i class="fas fa-calendar-check" id="sudah-ico" style="color:var(--green);"></i>
                </div>
                <div id="sudah-judul" style="font-size:15px;font-weight:800;color:var(--green);margin-bottom:6px;">Sudah Absen Hari Ini</div>
                <div id="sudah-nama" style="font-size:18px;font-weight:900;color:rgba(255,255,255,0.9);margin-bottom:6px;"></div>
                <div id="sudah-jam" style="display:none;font-size:13px;color:rgba(255,255,255,0.7);margin-bottom:6px;"></div>
                <div id="sudah-ket" style="font-size:12px;color:rgba(255,255,255,0.55);">Absen masuk &amp; keluar sudah tercatat</div>
                <div style="margin-top:12px;display:flex;justify-content:center;gap:6px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:var(--green);animation:pulse 0.8s infinite;"></span>
                    <span style="font-size:11px;color:var(--green);font-weight:600;">Silakan orang berikutnya...</span>
                </div>
            </div>

            {{-- State: Sedang Diproses (cooldown wajah — bukan kegagalan, cuma jeda antar-scan) --}}
            <div id="s-proses" style="display:none;text-align:center;position:relative;z-index:1;animation:fadeInUp .3s ease;">
                <div style="width:80px;height:80px;border-radius:50%;background:rgba(56,189,248,0.15);border:3px solid #38bdf8;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:32px;">
                    <i class="fas fa-hourglass-half" style="color:#38bdf8;"></i>
                </div>
                <div style="font-size:15px;font-weight:800;color:#38bdf8;margin-bottom:6px;">Sedang Diproses</div>
                <div id="proses-nama" style="font-size:18px;font-weight:900;color:rgba(255,255,255,0.9);margin-bottom:6px;"></div>
                <div style="font-size:12px;color:rgba(255,255,255,0.55);">Wajah sudah terdeteksi, mohon tunggu sebentar</div>
            </div>

        </div><!-- /result-card -->

        <div class="kiosk-footer">&copy; {{ date('Y') }} {{ $namaPerusahaan }} &mdash; Sistem Kehadiran Digital</div>

    </div><!-- /info-panel -->

</div><!-- /kiosk-root -->

<script>
// ── AUDIO SYSTEM (optimized untuk ruangan terbuka / pabrik) ──────────────────
var _actx = null;
function actx(){
    if(!_actx) _actx = new(window.AudioContext||window.webkitAudioContext)();
    if(_actx.state==='suspended') _actx.resume();
    return _actx;
}

/**
 * Buat satu nada dengan oscillator + distorsi untuk suara lebih keras & tajam
 * Pakai 2 oscillator (detuned) = suara lebih tebal, lebih terdengar di kebisingan
 */
function playTone(freq, startTime, duration, volume, type){
    var c = actx();
    type = type || 'square'; // square/sawtooth lebih tajam dari sine di kebisingan

    // Oscillator utama
    var o1 = c.createOscillator();
    var o2 = c.createOscillator(); // detune sedikit = efek chorus, suara lebih tebal
    var g  = c.createGain();
    var compressor = c.createDynamicsCompressor(); // cegah clipping saat volume tinggi
    compressor.threshold.setValueAtTime(-6, c.currentTime);
    compressor.knee.setValueAtTime(3, c.currentTime);
    compressor.ratio.setValueAtTime(4, c.currentTime);

    o1.connect(g); o2.connect(g);
    g.connect(compressor); compressor.connect(c.destination);

    o1.type = type; o1.frequency.setValueAtTime(freq, c.currentTime + startTime);
    o2.type = type; o2.frequency.setValueAtTime(freq * 1.005, c.currentTime + startTime); // +0.5% detune

    // Volume envelope: attack cepat, sustain, decay
    g.gain.setValueAtTime(0, c.currentTime + startTime);
    g.gain.linearRampToValueAtTime(volume, c.currentTime + startTime + 0.01); // attack 10ms
    g.gain.setValueAtTime(volume, c.currentTime + startTime + duration * 0.7);
    g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + startTime + duration);

    o1.start(c.currentTime + startTime); o1.stop(c.currentTime + startTime + duration + 0.05);
    o2.start(c.currentTime + startTime); o2.stop(c.currentTime + startTime + duration + 0.05);
}

function beep(type){
    try {
        if(type === 'ok'){
            // ✅ SUKSES: 2 nada naik — do-mi (C5→E5)
            // Nada tinggi, tajam, jelas terdengar di kebisingan pabrik
            playTone(523, 0.00, 0.18, 1.0, 'square');  // C5
            playTone(659, 0.20, 0.25, 1.0, 'square');  // E5 — lebih panjang & keras
        } else if(type === 'err'){
            // ❌ GAGAL: 2 nada turun — A4→F4 (berbeda dari sukses, mudah dibedakan)
            playTone(440, 0.00, 0.20, 1.0, 'sawtooth'); // A4 — sawtooth lebih kasar/khas error
            playTone(349, 0.22, 0.30, 1.0, 'sawtooth'); // F4
        } else if(type === 'info'){
            // ℹ️ INFO (sudah absen): 1 nada pendek netral
            playTone(523, 0.00, 0.15, 0.8, 'sine');     // C5 pendek
        }
    } catch(e){}
}

// ── CLOCK ─────────────────────────────────────────────────────────────────────
(function tick(){
    var n=new Date();
    document.getElementById('jam').textContent = n.toLocaleTimeString('id-ID');
    var H=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var B=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    document.getElementById('tgl').textContent = H[n.getDay()]+', '+n.getDate()+' '+B[n.getMonth()]+' '+n.getFullYear();
    setTimeout(tick,1000);
})();

// ── REFS ──────────────────────────────────────────────────────────────────────
var video   = document.getElementById('video');
var cvs     = document.getElementById('cvs');
var cvsQr   = document.getElementById('cvs-qr');
var CSRF    = document.querySelector('meta[name=csrf-token]').content;
var URL_FACE= '{{ route("kiosk.scan") }}';
var URL_QR  = '{{ route("kiosk.scan-qr") }}';
var URL_FMK = '{{ route("kiosk.fmk") }}';

var STATE      = 'idle';
var lastQrNik  = '';
var lastQrTime = 0;
var QR_INTERVAL   = 350;
var FACE_INTERVAL = 1800;
var COOLDOWN_SEC      = {{ (int) \App\Models\Pengaturan::get('kiosk_cooldown_detik', 5) }};
var WAJIB_SELFIE_KELUAR = {{ \App\Models\Pengaturan::get('wajib_selfie_keluar','0') === '1' ? 'true' : 'false' }};

// ── CAMERA ────────────────────────────────────────────────────────────────────
navigator.mediaDevices.getUserMedia({
    video:{ width:{ideal:640}, height:{ideal:480}, facingMode:'user' }
}).then(function(stream){
    video.srcObject = stream;
    video.onloadedmetadata = function(){
        video.play(); setSbar('idle','Siap — hadapkan wajah atau scan QR...'); startLoops();
    };
}).catch(function(){
    document.getElementById('cam-err').style.display='block';
    document.getElementById('btn-m').disabled=true;
    setSbar('err','Kamera tidak tersedia');
});

// ── LOOPS ─────────────────────────────────────────────────────────────────────
function startLoops(){
    setInterval(function(){
        if(STATE!=='idle'||video.readyState<2) return;
        var nik=readQR();
        if(nik){
            var now=Date.now();
            if(nik===lastQrNik&&(now-lastQrTime)<4000) return;
            lastQrNik=nik; lastQrTime=now;
            setQrViz(true); sendQR(nik);
        } else { setQrViz(false); }
    }, QR_INTERVAL);

    setInterval(function(){
        if(STATE!=='idle'||video.readyState<2) return;
        if(!frameBright()) return;
        sendFace(false);
    }, FACE_INTERVAL);
    // sbarCls sudah include 'err', STATE='err' akan block loop
}

// ── QR ────────────────────────────────────────────────────────────────────────
function readQR(){
    if(typeof jsQR==='undefined') return null;
    var w=video.videoWidth||640, h=video.videoHeight||480;
    cvsQr.width=w; cvsQr.height=h;
    var ctx=cvsQr.getContext('2d');
    ctx.drawImage(video,0,0,w,h);
    var img=ctx.getImageData(0,0,w,h);
    var res=jsQR(img.data,img.width,img.height,{inversionAttempts:'attemptBoth'});
    return res?res.data.trim():null;
}

function sendQR(nik){
    STATE='busy'; setSbar('qr','QR terdeteksi — memproses...'); setPill('qr'); showLoad('Membaca QR Code...');
    fetch(URL_QR,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify({nik:nik})})
    .then(r=>r.json()).then(d=>{
        if(d.success){
            // Jika wajib selfie keluar dan ini absen keluar via QR — tolak
            if(WAJIB_SELFIE_KELUAR && d.tipe==='keluar'){
                showErr('Absen keluar wajib menggunakan scan wajah (face recognition). Hadapkan wajah ke kamera.', 'Kebijakan perusahaan');
                resetAfter(4000); return;
            }
            showOk(d,'qr'); startCooldown();
        } else if(d.message && (d.message.indexOf('sudah absen masuk dan keluar')!==-1 || d.message.indexOf('sudah absen masuk hari ini')!==-1)){
            showSudahAbsen(d);
        } else if(d.belum_waktu_keluar){
            // Sudah absen masuk, belum waktunya absen keluar — bukan kegagalan, info jadwal saja.
            showSudahAbsen(d, 'Absen keluar mulai '+(d.waktu_boleh_keluar||'—'));
        } else if(d.wajib_izin_pulang_cepat){
            // Kebijakan: wajib izin pulang cepat disetujui HRD sebelum jam shift selesai.
            showSudahAbsen(d, 'Absen keluar mulai '+(d.jam_shift_keluar||'—')+' (atau ajukan izin pulang cepat ke HRD)');
        } else if(d.belum_dibuka){
            // Datang terlalu awal untuk jadwal shift-nya — bukan kegagalan, info jadwal saja.
            showSudahAbsen(d, 'Absen masuk mulai '+(d.waktu_boleh_masuk||'—'));
        } else if(d.message && d.message.indexOf('Absen masuk ditutup')!==-1){
            showErr(d.message, 'Hubungi HRD untuk pencatatan manual');
            resetAfter(4000);
        } else {
            showErr(d.message||'QR tidak valid.'); resetAfter(2500);
        }
    }).catch(()=>{ showErr('Koneksi gagal.'); resetAfter(2000); });
}

// ── FACE ──────────────────────────────────────────────────────────────────────
function captureFrame(){
    var w=video.videoWidth||640, h=video.videoHeight||480;
    cvs.width=w; cvs.height=h;
    cvs.getContext('2d').drawImage(video,0,0,w,h);
    return cvs.toDataURL('image/jpeg',0.8);
}

function frameBright(){
    var w=video.videoWidth||640, h=video.videoHeight||480;
    var ctx=cvsQr.getContext('2d');
    ctx.drawImage(video,0,0,w,h);
    var step=Math.floor(w/8), sum=0, cnt=0;
    for(var x=step;x<w;x+=step)
        for(var y=Math.floor(h/8);y<h;y+=Math.floor(h/8)){
            var p=ctx.getImageData(x,y,1,1).data;
            sum+=(p[0]*299+p[1]*587+p[2]*114)/1000; cnt++;
        }
    return cnt>0&&(sum/cnt)>30;
}

function sendFace(manual){
    if(STATE!=='idle') return;
    STATE='busy'; setSbar('face','Mengenali wajah...'); setPill('face'); setFaceViz(true);
    if(manual) showLoad('Memproses wajah...');
    fetch(URL_FACE,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify({foto:captureFrame()})})
    .then(r=>r.json()).then(d=>{
        setFaceViz(false);
        if(d.success){
            showOk(d,'face'); startCooldown();
        } else if(d.cooldown){
            // Cooldown Python — wajah yang sama baru saja ke-scan (auto-scan berjalan tiap ~1.8dtk).
            // Ini BUKAN kegagalan, cuma jeda teknis sebelum hasil scan sebelumnya selesai diproses.
            showProses(d.nik, d.sisa_detik);
        } else if(d.message&&(d.message.indexOf('sudah absen masuk dan keluar')!==-1||d.message.indexOf('sudah absen lengkap')!==-1||d.message.indexOf('sudah absen masuk hari ini')!==-1)){
            // Sudah absen masuk (dan mungkin sudah keluar juga) hari ini
            showSudahAbsen(d);
        } else if(d.belum_waktu_keluar){
            // Sudah absen masuk, belum waktunya absen keluar — BUKAN kegagalan,
            // cuma info jadwal. Tampilkan netral seperti kondisi "sudah absen".
            showSudahAbsen(d, 'Absen keluar mulai '+(d.waktu_boleh_keluar||'—'));
        } else if(d.wajib_izin_pulang_cepat){
            // Kebijakan: wajib izin pulang cepat disetujui HRD sebelum jam shift selesai.
            showSudahAbsen(d, 'Absen keluar mulai '+(d.jam_shift_keluar||'—')+' (atau ajukan izin pulang cepat ke HRD)');
        } else if(d.belum_dibuka){
            // Datang terlalu awal untuk jadwal shift-nya (mis. dijadwalkan shift siang
            // tapi scan pagi) — BUKAN kegagalan, cuma info jadwal.
            showSudahAbsen(d, 'Absen masuk mulai '+(d.waktu_boleh_masuk||'—'));
        } else if(d.message&&d.message.indexOf('Absen masuk ditutup')!==-1){
            showErr(d.message,'Hubungi HRD untuk pencatatan manual');
            setSbar('err','Waktu absen habis'); resetAfter(4000);
        } else if(d.message&&d.message.indexOf('terlalu cepat')!==-1){
            showErr(d.message,'Baru saja absen masuk');
            setSbar('err','Terlalu cepat'); resetAfter(2500);
        } else {
            if(manual){
                var hint = 'Hadap langsung ke kamera, pastikan pencahayaan cukup';
                showErr(d.message||'Wajah tidak dikenali.', hint);
                resetAfter(3000);
            } else{ toIdle(); }
        }
    }).catch(function(){
        setFaceViz(false);
        if(manual){ showErr('Python service tidak aktif.'); resetAfter(2500); }
        else{ toIdle(); }
    });
}
function manualFace(){ if(STATE!=='idle')return; sendFace(true); }

// ── FMK: Absen Pergi/Kembali ─────────────────────────────────────────────────
function manualFmk(){
    if(STATE!=='idle') return;
    STATE='busy'; showLoad('Memproses Absen Pergi/Kembali FMK...');
    fetch(URL_FMK,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify({foto:captureFrame()})})
    .then(r=>r.json()).then(d=>{
        if(d.success){
            beep('ok');
            showErr(d.message, d.tipe==='fmk_pergi' ? 'Absen Pergi FMK berhasil' : 'Absen Kembali FMK berhasil');
            document.getElementById('err-msg').style.color = '#48bb78';
        } else {
            showErr(d.message||'FMK tidak dapat diproses.', 'Absen Pergi/Kembali FMK');
        }
        resetAfter(3500);
    }).catch(()=>{ showErr('Koneksi gagal.'); resetAfter(2000); });
}

// ── INFO NETRAL (sudah absen, belum waktunya keluar, atau belum waktunya masuk) ──
// Dipakai untuk semua kondisi yang BUKAN kegagalan sistem, cuma info jadwal/status
// absensi — supaya bunyi & tampilannya konsisten netral, bukan seolah error.
function showSudahAbsen(d, infoTambahan){
    beep('info'); hideAll();
    var nama = d.karyawan||d||'';
    var sudahLengkap = !!d.jam_keluar;

    var judul = 'Sudah Absen Hari Ini';
    var keterangan = sudahLengkap ? 'Absen masuk & keluar sudah tercatat' : 'Absen masuk sudah tercatat';
    var sbarInfo = sudahLengkap ? 'sudah absen lengkap hari ini' : 'sudah absen masuk hari ini';

    if(d.belum_dibuka){
        judul = 'Belum Waktunya Absen';
        keterangan = 'Sesuai jadwal shift Anda hari ini';
        sbarInfo = infoTambahan || 'belum waktunya absen masuk';
    } else if(infoTambahan){
        keterangan = 'Sudah absen masuk, menunggu jadwal absen keluar';
        sbarInfo = infoTambahan;
    }

    document.getElementById('sudah-judul').textContent = judul;
    document.getElementById('sudah-ico').className = d.belum_dibuka ? 'fas fa-clock' : 'fas fa-calendar-check';
    document.getElementById('s-sudah').style.display='block';
    document.getElementById('sudah-nama').textContent = nama;
    // Tampil jam jika ada
    var jamEl = document.getElementById('sudah-jam');
    if(jamEl && d.jam_masuk){
        jamEl.textContent = 'Masuk: '+d.jam_masuk + (sudahLengkap?' · Keluar: '+d.jam_keluar:'') + (infoTambahan?' · '+infoTambahan:'');
        jamEl.style.display='block';
    } else if(jamEl && d.belum_dibuka){
        jamEl.textContent = infoTambahan||'';
        jamEl.style.display = infoTambahan ? 'block' : 'none';
    }
    setSbar('ok', nama+' — '+sbarInfo);
    var ketEl = document.getElementById('sudah-ket');
    if(ketEl){ ketEl.textContent = keterangan; }
    // Auto reset 2 detik — cepat, orang berikutnya bisa langsung scan
    setTimeout(function(){ lastQrNik=''; toIdle(); }, 2000);
}

// ── SEDANG DIPROSES (cooldown wajah — bukan kegagalan, cuma jeda antar-scan wajah yang sama) ──
function showProses(nik, sisaDetik){
    beep('info'); hideAll();
    document.getElementById('s-proses').style.display='block';
    document.getElementById('proses-nama').textContent = nik ? 'NIK '+nik : '';
    setSbar('ok','Wajah terdeteksi — sedang diproses...');
    resetAfter(Math.max(1, sisaDetik||2) * 1000);
}

// ── COOLDOWN ──────────────────────────────────────────────────────────────────
var cdTimer=null;

function toIdle(){
    STATE='idle'; setPill(null); setQrViz(false); setFaceViz(false);
    setSbar('idle','Siap — hadapkan wajah atau scan QR...'); showIdle();
}

function resetToIdle(){
    if(cdTimer){clearInterval(cdTimer);cdTimer=null;}
    document.getElementById('cd-ring').style.display='none';
    document.getElementById('tap-hint').style.display='none';
    document.getElementById('btn-m').disabled=false;
    lastQrNik=''; toIdle();
}

function startCooldown(){
    STATE='cooldown'; document.getElementById('btn-m').disabled=true;
    var el=document.getElementById('cd-ring'), fg=document.getElementById('cd-fg'),
        num=document.getElementById('cd-num'), circ=113;
    el.style.display='block'; num.textContent=COOLDOWN_SEC; fg.style.strokeDashoffset=circ;
    document.getElementById('tap-hint').style.display='block';
    setSbar('ok','Berhasil! Tap atau tunggu '+COOLDOWN_SEC+'dtk...');
    var rem=COOLDOWN_SEC;
    cdTimer=setInterval(function(){
        rem--; num.textContent=rem; fg.style.strokeDashoffset=circ*(rem/COOLDOWN_SEC);
        setSbar('ok','Berhasil! Tunggu '+rem+'dtk...');
        if(rem<=0) resetToIdle();
    },1000);
}

function resetAfter(ms){
    setTimeout(function(){ lastQrNik=''; toIdle(); }, ms);
}

// ── DISPLAY ───────────────────────────────────────────────────────────────────
function hideAll(){
    ['s-idle','s-load','s-ok','s-err','s-sudah','s-proses'].forEach(function(id){ document.getElementById(id).style.display='none'; });
}
function showIdle(){
    hideAll(); document.getElementById('s-idle').style.display='block';
}
function showLoad(txt){
    hideAll(); document.getElementById('load-txt').textContent=txt||'Memproses...';
    document.getElementById('s-load').style.display='block';
}
function showErr(msg,hint){
    beep('err'); hideAll(); STATE='err';
    document.getElementById('err-msg').textContent  = msg||'Terjadi kesalahan';
    document.getElementById('err-hint').textContent = hint||'Silakan coba kembali';
    document.getElementById('s-err').style.display  = 'block';
}
function showOk(d, metode){
    beep('ok'); hideAll();

    // Foto
    var f=document.getElementById('ok-foto');
    if(d.foto){ f.src=d.foto; f.style.display='block'; } else { f.style.display='none'; }

    // Check icon warna
    var chk=document.getElementById('ok-check');
    chk.className = 'ok-check'+(d.tipe==='keluar'?' out':'');

    document.getElementById('ok-nama').textContent = d.karyawan||'';
    var nikEl = document.getElementById('ok-nik');
    if(nikEl) nikEl.textContent = d.nik ? 'NIK: '+d.nik : '';
    document.getElementById('ok-sub').textContent  = (d.jabatan||'')+(d.departemen?' — '+d.departemen:'');

    // Badge — merah terlambat, kuning pulang cepat, hijau hadir, biru keluar normal
    var statusInfo = document.getElementById('ok-status-info');
    if(d.tipe==='masuk'){
        if(d.status==='terlambat'){
            document.getElementById('ok-badge').innerHTML='<span class="badge-late"><i class="fas fa-clock"></i> TERLAMBAT</span>';
            if(statusInfo){ statusInfo.innerHTML='<i class="fas fa-exclamation-triangle"></i> Anda terlambat dari jadwal masuk. Isi alasan di portal.'; statusInfo.style.display='block'; }
        } else {
            document.getElementById('ok-badge').innerHTML='<span class="badge-in"><i class="fas fa-sign-in-alt"></i> ABSEN MASUK</span>';
            if(statusInfo){ statusInfo.style.display='none'; }
        }
    } else {
        if(d.pulang_cepat){
            document.getElementById('ok-badge').innerHTML='<span class="badge-early"><i class="fas fa-sign-out-alt"></i> PULANG LEBIH AWAL</span>';
            if(statusInfo){ statusInfo.innerHTML='<i class="fas fa-exclamation-circle"></i> Anda pulang sebelum jam shift selesai. Hubungi supervisor jika ada keperluan.'; statusInfo.style.display='block'; }
        } else {
            document.getElementById('ok-badge').innerHTML='<span class="badge-out"><i class="fas fa-sign-out-alt"></i> ABSEN KELUAR</span>';
            if(statusInfo){ statusInfo.style.display='none'; }
        }
    }

    // Metode
    document.getElementById('ok-metode').innerHTML = metode==='qr'
        ? '<i class="fas fa-qrcode"></i> via QR Code'
        : '<i class="fas fa-user-circle"></i> via Face Recognition';

    // Durasi (absen keluar)
    var dbox=document.getElementById('ok-dur-box'), ebox=document.getElementById('ok-early'),
        lbox=document.getElementById('ok-lembur');
    if(d.tipe==='keluar'&&d.durasi){
        document.getElementById('ok-dur-val').textContent=d.durasi;
        dbox.style.display='block';
        ebox.style.display=d.pulang_cepat?'block':'none';
        if(lbox) lbox.style.display=d.potensi_lembur?'block':'none';
    } else {
        dbox.style.display='none'; ebox.style.display='none';
        if(lbox) lbox.style.display='none';
    }

    // Confidence
    var conf=metode==='qr'?100:(parseFloat(d.confidence)||0);
    document.getElementById('conf-lbl').textContent = metode==='qr'?'Kecocokan QR':'Akurasi Wajah';
    document.getElementById('conf-val').textContent = conf+'%';
    document.getElementById('conf-fill').style.width = conf+'%';

    document.getElementById('ok-time').textContent = d.jam||'';
    document.getElementById('ok-msg').textContent  = d.message||'';
    document.getElementById('s-ok').style.display  = 'block';
}

// ── VISUAL INDICATORS ─────────────────────────────────────────────────────────
function setQrViz(on){
    document.getElementById('qr-zone').className = on?'qr-zone show':'qr-zone';
}
function setFaceViz(on){
    var e=document.getElementById('face-ring');
    on?e.classList.add('lit'):e.classList.remove('lit');
}
function setPill(mode){
    document.getElementById('pill-face').className='mpill'+(mode==='face'?' active-face':'');
    document.getElementById('pill-qr').className  ='mpill'+(mode==='qr'  ?' active-qr':'');
}

var sbarCls={
    idle:'sb-idle', face:'sb-face', qr:'sb-qr', proc:'sb-proc', ok:'sb-ok', err:'sb-err'
};
var dotClr={
    idle:'rgba(255,255,255,.3)', face:'#34d399', qr:'#fb7185',
    proc:'#a5b4fc', ok:'#022c22', err:'#fca5a5'
};
function setSbar(st,txt){
    var bar=document.getElementById('sbar'), dot=document.getElementById('sb-dot');
    bar.className='cam-statusbar '+(sbarCls[st]||'sb-idle');
    dot.style.background=dotClr[st]||dotClr.idle;
    document.getElementById('sb-txt').textContent=txt||'';
}

// ── INIT ──────────────────────────────────────────────────────────────────────
showIdle();

// Unlock AudioContext — setiap interaksi (bukan hanya pertama kali)
// Penting di tempat terbuka: browser sering suspend audio setelah idle lama
['click','touchstart','keydown','pointerdown'].forEach(function(ev){
    document.addEventListener(ev, function(){
        var c = actx();
        if(c.state === 'suspended') c.resume();
    });
});

// Test beep saat halaman pertama kali siap (senyap/volume rendah)
// agar AudioContext langsung aktif dan siap untuk beep keras berikutnya
setTimeout(function(){
    try {
        var c = actx();
        var o = c.createOscillator();
        var g = c.createGain();
        o.connect(g); g.connect(c.destination);
        g.gain.setValueAtTime(0.001, c.currentTime); // hampir tidak terdengar
        o.start(); o.stop(c.currentTime + 0.01);
    } catch(e){}
}, 500);
</script>
</body>
</html>
