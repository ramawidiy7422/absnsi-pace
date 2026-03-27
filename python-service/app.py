import sys
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

from flask import Flask, request, jsonify
from flask_cors import CORS
import os
import base64
from deepface import DeepFace
from PIL import Image

app = Flask(__name__)
CORS(app)

WAJAH_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'wajah_karyawan')
os.makedirs(WAJAH_DIR, exist_ok=True)
try:
    import stat as _stat
    os.chmod(WAJAH_DIR, _stat.S_IRWXU | _stat.S_IRWXG | _stat.S_IRWXO)
except Exception:
    pass

# Gunakan Facenet - lebih ringan, akurat, dan model lebih kecil (~90MB)
MODEL_NAME = 'Facenet'
DETECTOR  = 'opencv'
THRESHOLD = 50  # confidence minimum (%)


def base64_to_image(b64_string):
    if ',' in b64_string:
        b64_string = b64_string.split(',')[1]
    img_bytes = base64.b64decode(b64_string)
    img = Image.open(io.BytesIO(img_bytes)).convert('RGB')
    return img


@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'message': 'Face Recognition Service aktif', 'model': MODEL_NAME})


@app.route('/daftarkan', methods=['POST'])
def daftarkan_wajah():
    data = request.get_json()
    nik  = data.get('nik')
    foto_b64 = data.get('foto')

    if not nik or not foto_b64:
        return jsonify({'success': False, 'message': 'NIK dan foto wajib diisi'}), 400

    try:
        img  = base64_to_image(foto_b64)
        path = os.path.join(WAJAH_DIR, f"{nik}.jpg")
        img.save(path)

        # Validasi ada wajah dan hanya 1 wajah
        faces = DeepFace.extract_faces(img_path=path, detector_backend=DETECTOR, enforce_detection=True)

        if len(faces) == 0:
            os.remove(path)
            return jsonify({'success': False, 'message': 'Wajah tidak terdeteksi di foto. Pastikan wajah terlihat jelas.'}), 400

        if len(faces) > 1:
            os.remove(path)
            return jsonify({'success': False, 'message': f'Terdeteksi {len(faces)} wajah. Foto harus hanya 1 orang.'}), 400

        return jsonify({'success': True, 'message': f'Wajah karyawan NIK {nik} berhasil didaftarkan'})

    except Exception as e:
        if os.path.exists(os.path.join(WAJAH_DIR, f"{nik}.jpg")):
            os.remove(os.path.join(WAJAH_DIR, f"{nik}.jpg"))
        return jsonify({'success': False, 'message': f'Gagal daftarkan: {str(e)}'}), 400


@app.route('/kenali', methods=['POST'])
def kenali_wajah():
    data = request.get_json()
    foto_b64 = data.get('foto')

    if not foto_b64:
        return jsonify({'success': False, 'message': 'Foto wajib diisi'}), 400

    import tempfile, uuid
    temp_path = os.path.join(tempfile.gettempdir(), f'scan_{uuid.uuid4().hex}.jpg')

    try:
        img = base64_to_image(foto_b64)
        img.save(temp_path)

        # Cek ada wajah di foto scan
        try:
            faces = DeepFace.extract_faces(img_path=temp_path, detector_backend=DETECTOR, enforce_detection=True)
            if not faces:
                return jsonify({'success': False, 'message': 'Wajah tidak terdeteksi. Pastikan wajah menghadap kamera.'}), 400
        except Exception:
            return jsonify({'success': False, 'message': 'Wajah tidak terdeteksi. Pastikan pencahayaan cukup.'}), 400

        # Ambil semua foto referensi (skip temp)
        foto_files = [f for f in os.listdir(WAJAH_DIR)
                      if f.endswith('.jpg') and f != 'temp_scan.jpg']

        if not foto_files:
            return jsonify({'success': False, 'message': 'Belum ada karyawan terdaftar'}), 404

        hasil_terbaik   = None
        confidence_terbaik = 0

        for foto_file in foto_files:
            nik      = foto_file.replace('.jpg', '')
            foto_ref = os.path.join(WAJAH_DIR, foto_file)

            try:
                result = DeepFace.verify(
                    img1_path=temp_path,
                    img2_path=foto_ref,
                    model_name=MODEL_NAME,
                    detector_backend=DETECTOR,
                    enforce_detection=False,
                    silent=True
                )

                # Hitung confidence dari distance
                distance   = result['distance']
                threshold  = result['threshold']
                # Normalisasi: distance 0 = 100%, distance = threshold = 50%
                confidence = round(max(0, (1 - distance / threshold) * 50 + 50), 2)

                print(f"  {nik}: verified={result['verified']}, distance={distance:.4f}, threshold={threshold:.4f}, confidence={confidence}%")

                if result['verified'] and confidence > confidence_terbaik:
                    confidence_terbaik = confidence
                    hasil_terbaik = {'nik': nik, 'confidence': confidence}

            except Exception as ex:
                print(f"  {nik}: skip - {ex}")
                continue

        if os.path.exists(temp_path):
            os.remove(temp_path)

        if hasil_terbaik and confidence_terbaik >= THRESHOLD:
            return jsonify({
                'success'   : True,
                'nik'       : hasil_terbaik['nik'],
                'confidence': hasil_terbaik['confidence'],
                'message'   : f"Wajah dikenali: {hasil_terbaik['nik']} ({hasil_terbaik['confidence']}%)"
            })
        else:
            best_info = f" (terbaik: {confidence_terbaik:.1f}%)" if confidence_terbaik > 0 else ""
            return jsonify({'success': False, 'message': f'Wajah tidak dikenali{best_info}. Coba lagi dengan posisi wajah lebih jelas.'}), 404

    except Exception as e:
        if os.path.exists(temp_path):
            os.remove(temp_path)
        return jsonify({'success': False, 'message': f'Error sistem: {str(e)}'}), 500


@app.route('/hapus/<nik>', methods=['DELETE'])
def hapus_wajah(nik):
    path = os.path.join(WAJAH_DIR, f"{nik}.jpg")
    if os.path.exists(path):
        os.remove(path)
        return jsonify({'success': True, 'message': f'Wajah NIK {nik} dihapus'})
    return jsonify({'success': False, 'message': 'Data wajah tidak ditemukan'}), 404


@app.route('/list', methods=['GET'])
def list_wajah():
    files = [f.replace('.jpg', '') for f in os.listdir(WAJAH_DIR)
             if f.endswith('.jpg') and f != 'temp_scan.jpg']
    return jsonify({'success': True, 'terdaftar': files, 'total': len(files)})


if __name__ == '__main__':
    print(f"Model: {MODEL_NAME} | Detector: {DETECTOR} | Threshold confidence: {THRESHOLD}%")
    print(f"Folder wajah: {WAJAH_DIR}")
    app.run(host='0.0.0.0', port=5000, debug=False)
