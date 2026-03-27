import os
import sys
from deepface import DeepFace

folder = 'wajah_karyawan'

# 1. Cek foto terdaftar
files = [f for f in os.listdir(folder) if f.endswith('.jpg') and f != 'temp_scan.jpg']
print('=== Foto Terdaftar ===')
print(files)
print()

# 2. Cek apakah wajah terdeteksi di setiap foto
print('=== Cek Deteksi Wajah di Foto Referensi ===')
valid_files = []
for f in files:
    path = os.path.join(folder, f)
    try:
        faces = DeepFace.extract_faces(img_path=path, detector_backend='opencv', enforce_detection=True)
        print(f'OK: {f} -> {len(faces)} wajah terdeteksi')
        valid_files.append(f)
    except Exception as e:
        print(f'ERROR: {f} -> {e}')

print()

# 3. Test self-verify (foto dengan dirinya sendiri harus match)
if valid_files:
    print('=== Test Self-Verify (foto vs dirinya sendiri) ===')
    for f in valid_files[:2]:
        path = os.path.join(folder, f)
        try:
            result = DeepFace.verify(
                img1_path=path,
                img2_path=path,
                model_name='VGG-Face',
                detector_backend='opencv',
                enforce_detection=False
            )
            print(f'{f}: verified={result["verified"]}, distance={result["distance"]:.4f}')
        except Exception as e:
            print(f'{f}: ERROR verify -> {e}')
