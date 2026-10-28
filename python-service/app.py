import sys, io, os, time, base64
import numpy as np
import cv2
from PIL import Image
from flask import Flask, request, jsonify
from flask_cors import CORS
from insightface.app import FaceAnalysis

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

# ── Thread tuning untuk CPU i3-4170 (4 threads) ──────────────────────────────
os.environ.setdefault('OMP_NUM_THREADS', '4')
os.environ.setdefault('OPENBLAS_NUM_THREADS', '4')
os.environ.setdefault('MKL_NUM_THREADS', '4')

app = Flask(__name__)
CORS(app)

WAJAH_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'wajah_karyawan')
os.makedirs(WAJAH_DIR, exist_ok=True)

# ── Model: buffalo_sc (det_500m + w600k_mbf ArcFace) ─────────────────────────
# det_size=320 → ~20ms/frame pada i3, cukup untuk webcam 640x480
face_app = FaceAnalysis(name='buffalo_sc', providers=['CPUExecutionProvider'])
face_app.prepare(ctx_id=0, det_size=(320, 320))

MODEL_NAME = 'InsightFace-ArcFace (buffalo_sc)'

# Threshold cosine distance:
# - 0.40 = ketat (hanya wajah sangat mirip lolos, cocok foto bagus)
# - 0.50 = normal
# - 0.55 = longgar (berguna saat foto referensi 1 sudut, scan beda sudut/pencahayaan)
THRESHOLD = 0.50

# Cooldown produksi: cegah scan ganda. Cukup di atas interval auto-scan wajah
# di frontend (~1.8 detik) supaya tetap efektif mencegah spam-request, tapi
# jauh lebih singkat dari sebelumnya (5 detik) supaya jarang terlihat karyawan
# saat mereka masih berdiri di depan kios setelah absen sukses.
COOLDOWN_DETIK = 3

_emb_cache = {}   # { nik: np.array }
_last_scan  = {}  # { nik: timestamp }


# ── Helpers ──────────────────────────────────────────────────────────────────

def _preprocess(b64_string: str) -> np.ndarray:
    """Decode base64 → BGR numpy. Terapkan CLAHE untuk normalisasi cahaya."""
    if ',' in b64_string:
        b64_string = b64_string.split(',')[1]
    data = base64.b64decode(b64_string)
    buf  = np.frombuffer(data, dtype=np.uint8)
    img_bgr = cv2.imdecode(buf, cv2.IMREAD_COLOR)
    if img_bgr is None:
        raise ValueError("Gambar tidak bisa didecode")

    # CLAHE: normalisasi pencahayaan tanpa mengubah warna
    # Berguna saat wajah kurang cahaya atau terlalu terang
    lab = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2LAB)
    l, a, b = cv2.split(lab)
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    l = clahe.apply(l)
    lab = cv2.merge([l, a, b])
    img_bgr = cv2.cvtColor(lab, cv2.COLOR_LAB2BGR)

    # InsightFace butuh RGB
    return cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)


def _get_embedding(img_rgb: np.ndarray):
    """Detect wajah terbesar, return (embedding, error_msg)."""
    faces = face_app.get(img_rgb)
    if not faces:
        return None, 'Wajah tidak terdeteksi. Pastikan wajah menghadap kamera dan cahaya cukup.'

    # Ambil wajah dengan det_score tertinggi (bukan hanya terbesar)
    face = max(faces, key=lambda f: f.det_score)

    # Tolak wajah dengan det_score rendah (blur/jauh/tidak jelas)
    if face.det_score < 0.60:
        return None, f'Kualitas wajah terlalu rendah (score {face.det_score:.2f}). Hadapkan wajah langsung ke kamera.'

    return face.normed_embedding, None  # sudah L2-normalized


def _cosine_dist(a, b) -> float:
    return float(1 - np.dot(a, b))


def _load_all_embeddings():
    global _emb_cache
    _emb_cache = {}
    for fname in os.listdir(WAJAH_DIR):
        if not fname.endswith('.npy'):
            continue
        nik = fname[:-4]
        try:
            _emb_cache[nik] = np.load(os.path.join(WAJAH_DIR, fname))
        except Exception as e:
            print(f"[WARN] Gagal load {nik}: {e}")


_load_all_embeddings()
print(f"Model    : {MODEL_NAME}")
print(f"Threshold: {THRESHOLD}  Cooldown: {COOLDOWN_DETIK}s")
print(f"Terdaftar: {len(_emb_cache)} wajah")
print(f"Folder   : {WAJAH_DIR}")

# Auto-sync dari storage Laravel jika cache kosong (misal setelah fresh install)
if len(_emb_cache) == 0:
    print("[INFO] Cache kosong — mencoba auto-sync dari storage Laravel...")
    try:
        import subprocess
        sync_script = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'sync_from_db.py')
        if os.path.exists(sync_script):
            subprocess.Popen([sys.executable, sync_script])
            print("[INFO] sync_from_db.py dijalankan di background")
    except Exception as e:
        print(f"[WARN] Auto-sync gagal: {e}")


# ── Routes ────────────────────────────────────────────────────────────────────

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status'    : 'ok',
        'message'   : 'Face Recognition Service aktif',
        'model'     : MODEL_NAME,
        'threshold' : THRESHOLD,
        'terdaftar' : len(_emb_cache),
    })


@app.route('/reload', methods=['POST'])
def reload_embeddings():
    before = len(_emb_cache)
    _load_all_embeddings()
    return jsonify({
        'success'   : True,
        'message'   : f'Cache diperbarui: {before} → {len(_emb_cache)} wajah',
        'terdaftar' : len(_emb_cache),
    })


@app.route('/daftarkan', methods=['POST'])
def daftarkan_wajah():
    data     = request.get_json()
    nik      = (data.get('nik') or '').strip()
    foto_b64 = data.get('foto')

    if not nik or not foto_b64:
        return jsonify({'success': False, 'message': 'NIK dan foto wajib diisi'}), 400

    try:
        img_rgb     = _preprocess(foto_b64)
        emb, errmsg = _get_embedding(img_rgb)

        if emb is None:
            return jsonify({'success': False, 'message': errmsg}), 400

        npy_path = os.path.join(WAJAH_DIR, f"{nik}.npy")
        jpg_path = os.path.join(WAJAH_DIR, f"{nik}.jpg")

        np.save(npy_path, emb)
        Image.fromarray(img_rgb).save(jpg_path)
        _emb_cache[nik] = emb

        return jsonify({'success': True, 'message': f'Wajah NIK {nik} berhasil didaftarkan'})

    except Exception as e:
        return jsonify({'success': False, 'message': f'Gagal daftarkan: {str(e)}'}), 400


@app.route('/kenali', methods=['POST'])
def kenali_wajah():
    data     = request.get_json()
    foto_b64 = data.get('foto')

    if not foto_b64:
        return jsonify({'success': False, 'message': 'Foto tidak diberikan'}), 400

    if not _emb_cache:
        return jsonify({'success': False, 'message': 'Belum ada karyawan terdaftar di sistem Face Recognition.'}), 404

    try:
        t_start     = time.time()
        img_rgb     = _preprocess(foto_b64)
        emb, errmsg = _get_embedding(img_rgb)

        if emb is None:
            return jsonify({'success': False, 'message': errmsg}), 400

        # ── Cari kandidat terbaik ─────────────────────────────────────────
        now_ts   = time.time()
        best_nik  = None
        best_dist = 1.0
        second_dist = 1.0  # untuk mendeteksi ambiguitas

        for nik, ref_emb in _emb_cache.items():
            dist = _cosine_dist(emb, ref_emb)
            if dist < best_dist:
                second_dist = best_dist
                best_dist   = dist
                best_nik    = nik
            elif dist < second_dist:
                second_dist = dist

        elapsed_ms = (time.time() - t_start) * 1000

        # Wajah tidak cocok dengan siapapun
        if best_dist > THRESHOLD:
            return jsonify({
                'success'   : False,
                'message'   : f'Wajah tidak dikenali (jarak {best_dist:.3f} > threshold {THRESHOLD}). Pastikan foto wajah sudah terdaftar.',
                'dist'      : round(best_dist, 4),
                'threshold' : THRESHOLD,
                'ms'        : round(elapsed_ms, 1),
            })

        # Cek cooldown
        sisa_cooldown = COOLDOWN_DETIK - (now_ts - _last_scan.get(best_nik, 0))
        if sisa_cooldown > 0:
            return jsonify({
                'success'    : False,
                'cooldown'   : True,
                'sisa_detik' : max(1, int(sisa_cooldown)),
                'nik'        : best_nik,
                'message'    : f'Harap tunggu {max(1, int(sisa_cooldown))} detik sebelum scan ulang.',
            }), 429

        # ── Confidence score ──────────────────────────────────────────────
        # Formula: makin kecil jarak → makin tinggi confidence
        # Range output: 50-99.9%
        # Gap antara best dan second: makin besar gap → makin yakin
        raw_conf   = (1 - best_dist / THRESHOLD) * 50 + 50
        gap_bonus  = min(10, (second_dist - best_dist) * 100)  # max +10%
        confidence = round(min(99.9, max(50.0, raw_conf + gap_bonus)), 1)

        _last_scan[best_nik] = now_ts

        print(f"[OK] {best_nik} dist={best_dist:.3f} conf={confidence}% gap={second_dist-best_dist:.3f} {elapsed_ms:.0f}ms")

        return jsonify({
            'success'    : True,
            'nik'        : best_nik,
            'confidence' : confidence,
            'dist'       : round(best_dist, 4),
            'ms'         : round(elapsed_ms, 1),
            'message'    : f'Wajah dikenali: {best_nik} ({confidence}%)',
        })

    except Exception as e:
        return jsonify({'success': False, 'message': f'Error sistem: {str(e)}'}), 500


@app.route('/migrasi', methods=['POST'])
def migrasi_embeddings():
    """Re-generate .npy embedding dari .jpg referensi (migrasi atau re-enroll)."""
    berhasil, gagal = [], []
    for fname in os.listdir(WAJAH_DIR):
        if not fname.endswith('.jpg'):
            continue
        nik      = fname[:-4]
        npy_path = os.path.join(WAJAH_DIR, f"{nik}.npy")
        if os.path.exists(npy_path):
            berhasil.append(nik)
            continue
        try:
            img_rgb     = np.array(Image.open(os.path.join(WAJAH_DIR, fname)).convert('RGB'))
            emb, errmsg = _get_embedding(img_rgb)
            if emb is None:
                gagal.append({'nik': nik, 'alasan': errmsg})
                continue
            np.save(npy_path, emb)
            _emb_cache[nik] = emb
            berhasil.append(nik)
        except Exception as e:
            gagal.append({'nik': nik, 'alasan': str(e)})

    _load_all_embeddings()
    return jsonify({'success': True, 'berhasil': berhasil, 'gagal': gagal, 'total': len(_emb_cache)})


@app.route('/hapus/<nik>', methods=['DELETE'])
def hapus_wajah(nik):
    dihapus = False
    for ext in ['.jpg', '.npy']:
        path = os.path.join(WAJAH_DIR, f"{nik}{ext}")
        if os.path.exists(path):
            os.remove(path)
            dihapus = True
    if nik in _emb_cache:
        del _emb_cache[nik]
        dihapus = True
    if dihapus:
        return jsonify({'success': True, 'message': f'Wajah NIK {nik} dihapus'})
    return jsonify({'success': False, 'message': 'Data wajah tidak ditemukan'}), 404


@app.route('/list', methods=['GET'])
def list_wajah():
    niks = sorted(_emb_cache.keys())
    return jsonify({'success': True, 'terdaftar': niks, 'total': len(niks)})


if __name__ == '__main__':
    print(f"Folder wajah: {WAJAH_DIR}")
    app.run(host='0.0.0.0', port=5000, debug=False)
