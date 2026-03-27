<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal Absensi - PT Global Multi Pharmalab</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="{{ asset('js/jsQR.min.js') }}"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
            min-height:100vh;display:flex;align-items:center;justify-content:center;color:#fff;
        }
        .wrap{width:100%;max-width:960px;padding:16px}

        /* Header */
        .hdr{text-align:center;margin-bottom:10px}
        .hdr h1{font-size:20px;font-weight:800;letter-spacing:1px}
        .hdr p{font-size:12px;opacity:.6;margin-top:2px}
        .jam{font-size:50px;font-weight:800;text-align:center;letter-spacing:4px;margin:6px 0 2px}
        .tgl{text-align:center;font-size:12px;opacity:.6;margin-bottom:16px}

        /* Status pill */
        .status-pill{
            display:flex;justify-content:center;align-items:center;gap:8px;
            margin-bottom:14px;font-size:12px;font-weight:600;
        }
        .pill{
            padding:5px 16px;border-radius:20px;display:flex;align-items:center;gap:6px;
            border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);
            transition:all .3s;
        }
        .pill.on-face{background:rgba(79,172,254,.25);border-color:#4facfe;color:#4facfe}
        .pill.on-qr  {background:rgba(245,87,108,.25);border-color:#f5576c;color:#f5576c}
        .pill .dot{width:7px;height:7px;border-radius:50%;background:currentColor}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
        .blink{animation:blink 1s infinite}

        /* Body grid */
        .body{display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start}

        /* Kamera */
        .cam-box{
            background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
            border-radius:18px;padding:16px;
        }
        .cam-box h3{font-size:13px;opacity:.7;margin-bottom:10px}
        .cam-wrap{position:relative;border-radius:12px;overflow:hidden}
        #video{width:100%;display:block;background:#000;border-radius:12px;aspect-ratio:4/3;object-fit:cover}
        #cvs,#cvs-qr{display:none}

        /* QR corners */
        .qr-corners{
            position:absolute;top:50%;left:50%;
            transform:translate(-50%,-50%);
            width:48%;aspect-ratio:1;pointer-events:none;
            opacity:.25;transition:opacity .3s,border-color .3s;
        }
        .qr-corners.lit{opacity:1}
        .qr-corners::before,.qr-corners::after,
        .qr-corners span::before,.qr-corners span::after{
            content:'';position:absolute;width:20px;height:20px;
            border-color:#f5576c;border-style:solid;
        }
        .qr-corners::before {top:0;left:0;  border-width:3px 0 0 3px;border-radius:4px 0 0 0}
        .qr-corners::after  {top:0;right:0; border-width:3px 3px 0 0;border-radius:0 4px 0 0}
        .qr-corners span::before{bottom:0;left:0; border-width:0 0 3px 3px;border-radius:0 0 0 4px}
        .qr-corners span::after {bottom:0;right:0;border-width:0 3px 3px 0;border-radius:0 0 4px 0}
        .qr-line{
            position:absolute;left:5%;right:5%;height:2px;
            background:linear-gradient(90deg,transparent,#f5576c,transparent);
            top:10%;display:none;
        }
        .qr-corners.lit .qr-line{display:block;animation:scanln 1.8s ease-in-out infinite}
        @keyframes scanln{0%{top:10%}50%{top:85%}100%{top:10%}}

        /* Face ring */
        .face-ring{
            position:absolute;top:50%;left:50%;
            transform:translate(-50%,-50%);
            width:46%;aspect-ratio:1;border-radius:50%;
            border:2px solid transparent;pointer-events:none;
            transition:border-color .3s;
        }
        .face-ring.lit{border-color:rgba(79,172,254,.7)}

        /* Status bar */
        .sbar{
            position:absolute;bottom:0;left:0;right:0;
            padding:8px 12px;display:flex;align-items:center;gap:7px;
            font-size:12px;font-weight:600;border-radius:0 0 12px 12px;
            transition:background .3s;
        }
        .sbar-idle{background:rgba(0,0,0,.55)}
        .sbar-qr  {background:rgba(245,87,108,.75)}
        .sbar-face{background:rgba(79,172,254,.75)}
        .sbar-ok  {background:rgba(72,187,120,.85)}
        .sbar-err {background:rgba(229,62,62,.8)}
        .sbar-proc{background:rgba(160,100,255,.75)}
        .sdot{width:8px;height:8px;border-radius:50%;flex-shrink:0}

        /* Cooldown ring */
        .cd{position:absolute;top:8px;right:8px;width:42px;height:42px;display:none}
        .cd svg{transform:rotate(-90deg)}
        .cd circle{fill:none;stroke-width:4;stroke-linecap:round}
        .cd .bg{stroke:rgba(255,255,255,.15)}
        .cd .fg{stroke:#48bb78;stroke-dasharray:113;stroke-dashoffset:113;transition:stroke-dashoffset 1s linear}
        .cd .num{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:13px;font-weight:800}

        /* Tombol manual */
        .btn-manual{
            width:100%;margin-top:10px;padding:10px;
            background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);
            border:1px solid rgba(255,255,255,.18);border-radius:10px;
            font-size:13px;font-weight:600;cursor:pointer;
            display:flex;align-items:center;justify-content:center;gap:7px;
            transition:all .2s;
        }
        .btn-manual:hover:not(:disabled){background:rgba(79,172,254,.25);border-color:#4facfe;color:#fff}
        .btn-manual:disabled{opacity:.3;cursor:not-allowed}
        #cam-err{display:none;margin-top:8px;text-align:center;color:#fc8181;font-size:11px}

        /* Result card */
        .res-card{
            background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
            border-radius:18px;padding:20px;min-height:380px;
            display:flex;flex-direction:column;align-items:center;justify-content:center;
        }

        /* Idle */
        .r-idle{text-align:center;opacity:.35}
        .r-idle i{font-size:52px;margin-bottom:12px;display:block}
        .r-idle p{font-size:13px;line-height:1.7}

        /* Loading */
        .r-load{display:none;text-align:center}
        .spinner{border:3px solid rgba(255,255,255,.1);border-top:3px solid #4facfe;
            border-radius:50%;width:46px;height:46px;animation:spin .9s linear infinite;margin:0 auto 12px}
        @keyframes spin{to{transform:rotate(360deg)}}
        .r-load p{font-size:13px;opacity:.7}

        /* Sukses */
        .r-ok{display:none;text-align:center;width:100%}
        .r-ok .ava{width:88px;height:88px;border-radius:50%;object-fit:cover;
            border:3px solid #48bb78;margin:0 auto 10px;display:block}
        .r-ok .nama{font-size:21px;font-weight:800;margin-bottom:3px}
        .r-ok .info{font-size:12px;opacity:.6;margin-bottom:8px}
        .b-masuk {background:#48bb78;color:#fff;padding:6px 18px;border-radius:20px;font-weight:700;font-size:13px;display:inline-block}
        .b-keluar{background:#ed8936;color:#fff;padding:6px 18px;border-radius:20px;font-weight:700;font-size:13px;display:inline-block}
        .metode-tag{font-size:11px;opacity:.55;margin-top:5px}
        .durasi-box{background:rgba(255,255,255,.07);border-radius:9px;padding:9px 14px;margin-top:10px;width:100%;text-align:center}
        .durasi-val{font-size:19px;font-weight:800;color:#4facfe}
        .durasi-lbl{font-size:10px;opacity:.5;margin-top:1px}
        .pulang-tag{font-size:11px;color:#fc8181;margin-top:4px}
        .conf-wrap{width:100%;margin-top:11px}
        .conf-lbl{display:flex;justify-content:space-between;font-size:11px;opacity:.6;margin-bottom:3px}
        .conf-bar{background:rgba(255,255,255,.1);border-radius:6px;height:6px;overflow:hidden}
        .conf-fill{height:100%;background:linear-gradient(90deg,#48bb78,#38a169);border-radius:6px;transition:width .5s}
        .r-ok .jam{font-size:17px;font-weight:700;margin-top:10px}
        .r-ok .msg{font-size:11px;opacity:.55;margin-top:3px}

        /* Error */
        .r-err{display:none;text-align:center}
        .r-err i{font-size:50px;color:#fc8181;margin-bottom:10px;display:block}
        .r-err p{font-size:13px;opacity:.85}
        .r-err small{font-size:11px;opacity:.45;margin-top:6px;display:block}
    </style>
</head>
<body>
<div class="wrap">
    <div class="hdr">
        <h1><i class="fas fa-capsules"></i> PT GLOBAL MULTI PHARMALAB</h1>
        <p>Terminal Absensi &mdash; Face Recognition &amp; QR Code Otomatis</p>
    </div>
    <div class="jam" id="jam">00:00:00</div>
    <div class="tgl" id="tgl"></div>

    <div class="status-pill">
        <div class="pill" id="pill-face"><span class="dot blink"></span> Face Recognition</div>
        <div class="pill" id="pill-qr"><span class="dot blink"></span> QR Code</div>
    </div>

    <div class="body">
        <!-- KAMERA -->
        <div class="cam-box">
            <h3><i class="fas fa-camera"></i> Kamera — Wajah &amp; QR Otomatis</h3>
            <div class="cam-wrap">
                <video id="video" autoplay playsinline muted></video>
                <canvas id="cvs"></canvas>
                <canvas id="cvs-qr"></canvas>

                <div class="qr-corners" id="qr-corners"><span></span><div class="qr-line"></div></div>
                <div class="face-ring" id="face-ring"></div>

                <div class="sbar sbar-idle" id="sbar">
                    <div class="sdot" id="sdot" style="background:rgba(255,255,255,.5)"></div>
                    <span id="stxt">Memulai kamera...</span>
                </div>

                <div class="cd" id="cd">
                    <svg width="42" height="42" viewBox="0 0 42 42">
                        <circle class="bg" cx="21" cy="21" r="18"/>
                        <circle class="fg" id="cd-fg" cx="21" cy="21" r="18"/>
                    </svg>
                    <div class="num" id="cd-num">5</div>
                </div>
            </div>

            <button class="btn-manual" id="btn-m" onclick="manualFace()">
                <i class="fas fa-camera"></i> Scan Wajah Manual
            </button>
            <div id="cam-err">
                <i class="fas fa-exclamation-triangle"></i> Kamera tidak tersedia — izinkan akses kamera di browser
            </div>
        </div>

        <!-- HASIL -->
        <div class="res-card">
            <div class="r-idle" id="r-idle">
                <i class="fas fa-user-circle"></i>
                <p>Hadapkan wajah ke kamera<br>atau tunjukkan QR Code karyawan<br><br><strong>Deteksi berjalan otomatis</strong></p>
            </div>
            <div class="r-load" id="r-load">
                <div class="spinner"></div>
                <p id="load-txt">Memproses...</p>
            </div>
            <div class="r-ok" id="r-ok">
                <img id="ok-foto" class="ava" src="" alt="" onerror="this.style.display='none'">
                <div class="nama" id="ok-nama"></div>
                <div class="info" id="ok-info"></div>
                <div id="ok-badge"></div>
                <div class="metode-tag" id="ok-metode"></div>
                <div class="durasi-box" id="ok-durasi" style="display:none">
                    <div class="durasi-val" id="ok-durasi-val"></div>
                    <div class="durasi-lbl">Durasi Kerja</div>
                </div>
                <div class="pulang-tag" id="ok-pulang" style="display:none">
                    <i class="fas fa-exclamation-triangle"></i> Pulang lebih awal
                </div>
                <div class="conf-wrap">
                    <div class="conf-lbl">
                        <span id="ok-conf-lbl">Akurasi</span>
                        <span id="ok-conf-val">-</span>
                    </div>
                    <div class="conf-bar"><div class="conf-fill" id="ok-conf-fill" style="width:0%"></div></div>
                </div>
                <div class="jam" id="ok-jam"></div>
                <div class="msg" id="ok-msg"></div>
            </div>
            <div class="r-err" id="r-err">
                <i class="fas fa-user-slash" id="err-ico"></i>
                <p id="err-msg">Tidak dikenali</p>
                <small id="err-hint">Pastikan pencahayaan cukup</small>
            </div>
        </div>
    </div>
</div>

<script>
// ─── CSRF & URL ───────────────────────────────────────────────
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const URL_FACE  = '{{ route("kiosk.scan") }}';
const URL_QR    = '{{ route("kiosk.scan-qr") }}';

// ─── JAM ─────────────────────────────────────────────────────
(function tick(){
    const n=new Date();
    document.getElementById('jam').textContent=n.toLocaleTimeString('id-ID');
    const hr=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const bl=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    document.getElementById('tgl').textContent=`${hr[n.getDay()]}, ${n.getDate()} ${bl[n.getMonth()]} ${n.getFullYear()}`;
    setTimeout(tick,1000);
})();

// ─── ELEMEN ──────────────────────────────────────────────────
const video   = document.getElementById('video');
const cvs     = document.getElementById('cvs');
const cvsQr   = document.getElementById('cvs-qr');

// ─── STATE ───────────────────────────────────────────────────
// 'idle' | 'busy' | 'cooldown'
let STATE      = 'idle';
let lastQrNik  = '';     // anti double-fire QR
let lastQrTime = 0;
let faceTimer  = null;
let qrTimer    = null;

const QR_INTERVAL   = 350;   // ms scan QR
const FACE_INTERVAL = 2500;  // ms scan face
const COOLDOWN_SEC  = 7;

// ─── KAMERA ──────────────────────────────────────────────────
navigator.mediaDevices.getUserMedia({
    video: { width:{ideal:640}, height:{ideal:480}, facingMode:'user' }
}).then(function(stream){
    video.srcObject = stream;
    video.onloadedmetadata = function(){
        video.play();
        setSbar('idle','Siap — menunggu wajah atau QR...');
        startLoops();
    };
}).catch(function(e){
    console.error('Kamera error:', e);
    document.getElementById('cam-err').style.display = 'block';
    document.getElementById('btn-m').disabled = true;
    setSbar('err','Kamera tidak tersedia');
});

// ─── LOOP ────────────────────────────────────────────────────
function startLoops(){
    // Loop QR — cepat
    qrTimer = setInterval(function(){
        if(STATE !== 'idle') return;
        if(video.readyState < 2) return;
        var nik = readQR();
        if(nik){
            // Anti double: skip jika NIK sama dalam 3 detik
            var now = Date.now();
            if(nik === lastQrNik && (now - lastQrTime) < 3000) return;
            lastQrNik  = nik;
            lastQrTime = now;
            litQR(true);
            kirimQR(nik);
        } else {
            litQR(false);
        }
    }, QR_INTERVAL);

    // Loop Face — lebih lambat
    faceTimer = setInterval(function(){
        if(STATE !== 'idle') return;
        if(video.readyState < 2) return;
        kirimFace(false);
    }, FACE_INTERVAL);
}

// ─── BACA QR ─────────────────────────────────────────────────
function readQR(){
    if(typeof jsQR === 'undefined') return null;
    var w = video.videoWidth  || 640;
    var h = video.videoHeight || 480;
    cvsQr.width  = w;
    cvsQr.height = h;
    var ctx = cvsQr.getContext('2d');
    ctx.drawImage(video, 0, 0, w, h);
    var img = ctx.getImageData(0, 0, w, h);
    var res = jsQR(img.data, img.width, img.height, {inversionAttempts:'attemptBoth'});
    return res ? res.data.trim() : null;
}

// ─── KIRIM QR ────────────────────────────────────────────────
function kirimQR(nik){
    STATE = 'busy';
    setSbar('proc','QR terdeteksi — memproses...');
    setPill('qr');
    showLoad('Membaca QR Code...');

    fetch(URL_QR, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({nik: nik})
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            showOk(d, 'qr');
            startCooldown();
        } else {
            showErr(d.message || 'QR tidak valid.');
            resetAfter(3500);
        }
    })
    .catch(function(e){
        console.error('QR fetch error:', e);
        showErr('Koneksi gagal. Periksa jaringan.');
        resetAfter(3000);
    });
}

// ─── CAPTURE FRAME ───────────────────────────────────────────
function captureFrame(){
    var w = video.videoWidth  || 640;
    var h = video.videoHeight || 480;
    cvs.width  = w;
    cvs.height = h;
    cvs.getContext('2d').drawImage(video, 0, 0, w, h);
    return cvs.toDataURL('image/jpeg', 0.8);
}

// ─── KIRIM FACE ──────────────────────────────────────────────
function kirimFace(isManual){
    if(STATE !== 'idle') return;
    STATE = 'busy';
    setSbar('face','Mengenali wajah...');
    setPill('face');
    litFace(true);
    if(isManual) showLoad('Memproses wajah...');

    var foto = captureFrame();

    fetch(URL_FACE, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({foto: foto})
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        litFace(false);
        if(d.success){
            showOk(d, 'face');
            startCooldown();
        } else {
            // Gagal kenali → diam, reset diam
            if(isManual){
                showErr(d.message || 'Wajah tidak dikenali.', 'Pastikan pencahayaan cukup');
                resetAfter(3500);
            } else {
                STATE = 'idle';
                setPill(null);
                setSbar('idle','Siap — menunggu wajah atau QR...');
                showIdle();
            }
        }
    })
    .catch(function(e){
        litFace(false);
        console.error('Face fetch error:', e);
        if(isManual){
            showErr('Python service tidak aktif atau koneksi gagal.');
            resetAfter(3500);
        } else {
            STATE = 'idle';
            setPill(null);
            setSbar('idle','Siap — menunggu wajah atau QR...');
            showIdle();
        }
    });
}

function manualFace(){
    if(STATE !== 'idle') return;
    kirimFace(true);
}

// ─── COOLDOWN ────────────────────────────────────────────────
function startCooldown(){
    STATE = 'cooldown';
    document.getElementById('btn-m').disabled = true;

    var sec = COOLDOWN_SEC;
    var cdEl  = document.getElementById('cd');
    var cdFg  = document.getElementById('cd-fg');
    var cdNum = document.getElementById('cd-num');
    var circ  = 113;

    cdEl.style.display = 'block';
    cdNum.textContent  = sec;
    cdFg.style.strokeDashoffset = circ;
    setSbar('ok','Berhasil! Siap dalam ' + sec + 'dtk...');

    var rem = sec;
    var t = setInterval(function(){
        rem--;
        cdNum.textContent = rem;
        cdFg.style.strokeDashoffset = circ * (rem / sec);
        setSbar('ok','Berhasil! Siap dalam ' + rem + 'dtk...');
        if(rem <= 0){
            clearInterval(t);
            cdEl.style.display = 'none';
            STATE     = 'idle';
            lastQrNik = '';
            litQR(false);
            litFace(false);
            setPill(null);
            document.getElementById('btn-m').disabled = false;
            showIdle();
            setSbar('idle','Siap — menunggu wajah atau QR...');
        }
    }, 1000);
}

// ─── TAMPIL ──────────────────────────────────────────────────
function hideAll(){
    ['r-idle','r-load','r-ok','r-err'].forEach(function(id){
        document.getElementById(id).style.display='none';
    });
}
function showIdle(){
    hideAll();
    document.getElementById('r-idle').style.display='block';
}
function showLoad(txt){
    hideAll();
    document.getElementById('load-txt').textContent = txt||'Memproses...';
    document.getElementById('r-load').style.display = 'block';
}
function showErr(msg, hint){
    hideAll();
    document.getElementById('err-ico').className = 'fas fa-user-slash';
    document.getElementById('err-msg').textContent  = msg  || 'Terjadi kesalahan';
    document.getElementById('err-hint').textContent = hint || 'Silakan coba kembali';
    document.getElementById('r-err').style.display = 'block';
}
function showOk(d, metode){
    hideAll();

    // Foto
    var f = document.getElementById('ok-foto');
    if(d.foto){ f.src=d.foto; f.style.display='block'; }
    else { f.style.display='none'; }

    document.getElementById('ok-nama').textContent = d.karyawan || '';
    document.getElementById('ok-info').textContent = (d.jabatan||'') + ' — ' + (d.departemen||'');
    document.getElementById('ok-jam').textContent  = d.jam || '';
    document.getElementById('ok-msg').textContent  = d.message || '';

    // Badge
    var badge = document.getElementById('ok-badge');
    if(d.tipe === 'masuk'){
        badge.innerHTML = '<span class="b-masuk"><i class="fas fa-sign-in-alt"></i> ABSEN MASUK</span>';
    } else {
        badge.innerHTML = '<span class="b-keluar"><i class="fas fa-sign-out-alt"></i> ABSEN KELUAR</span>';
    }

    // Metode tag
    var mEl = document.getElementById('ok-metode');
    mEl.innerHTML = metode === 'qr'
        ? '<i class="fas fa-qrcode"></i> via QR Code'
        : '<i class="fas fa-user-circle"></i> via Face Recognition';

    // Durasi (hanya saat keluar)
    var dBox = document.getElementById('ok-durasi');
    var pTag = document.getElementById('ok-pulang');
    if(d.tipe === 'keluar' && d.durasi){
        document.getElementById('ok-durasi-val').textContent = d.durasi;
        dBox.style.display = 'block';
        pTag.style.display = d.pulang_cepat ? 'block' : 'none';
    } else {
        dBox.style.display = 'none';
        pTag.style.display = 'none';
    }

    // Confidence
    var conf = (metode === 'qr') ? 100 : (parseFloat(d.confidence)||0);
    document.getElementById('ok-conf-lbl').textContent  = metode === 'qr' ? 'Kecocokan QR' : 'Akurasi Wajah';
    document.getElementById('ok-conf-val').textContent  = conf + '%';
    document.getElementById('ok-conf-fill').style.width = conf + '%';

    document.getElementById('r-ok').style.display = 'block';
}
function resetAfter(ms){
    setTimeout(function(){
        STATE     = 'idle';
        lastQrNik = '';
        litQR(false);
        litFace(false);
        setPill(null);
        showIdle();
        setSbar('idle','Siap — menunggu wajah atau QR...');
    }, ms);
}

// ─── VISUAL ──────────────────────────────────────────────────
function litQR(on){
    var el = document.getElementById('qr-corners');
    if(on) el.classList.add('lit'); else el.classList.remove('lit');
}
function litFace(on){
    var el = document.getElementById('face-ring');
    if(on) el.classList.add('lit'); else el.classList.remove('lit');
}
function setPill(mode){
    var pf = document.getElementById('pill-face');
    var pq = document.getElementById('pill-qr');
    pf.className = 'pill' + (mode==='face' ? ' on-face' : '');
    pq.className = 'pill' + (mode==='qr'   ? ' on-qr'   : '');
}

var sbarMap = {
    idle: {cls:'sbar-idle', dot:'rgba(255,255,255,.5)'},
    qr:   {cls:'sbar-qr',   dot:'#f5576c'},
    face: {cls:'sbar-face', dot:'#4facfe'},
    ok:   {cls:'sbar-ok',   dot:'#48bb78'},
    err:  {cls:'sbar-err',  dot:'#fc8181'},
    proc: {cls:'sbar-proc', dot:'#fff'},
};
function setSbar(state, txt){
    var el   = document.getElementById('sbar');
    var dot  = document.getElementById('sdot');
    var span = document.getElementById('stxt');
    var m    = sbarMap[state] || sbarMap.idle;
    el.className  = 'sbar ' + m.cls;
    dot.style.background = m.dot;
    span.textContent = txt || '';
}

// init
showIdle();
</script>
</body>
</html>
