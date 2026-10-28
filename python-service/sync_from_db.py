#!/usr/bin/env python3
"""
Script sinkronisasi wajah dari storage Laravel ke face service.
Jalankan setelah service restart atau tambah foto baru via admin.
Usage: python3 sync_from_db.py
"""
import os, sys, subprocess, base64, requests, json

STORAGE_PATH = '/var/www/absnsi-pace/storage/app/public/'
LARAVEL_PATH = '/var/www/absnsi-pace'
FACE_URL     = os.environ.get('FACE_API_URL', 'http://127.0.0.1:5000')

def get_karyawan_foto():
    result = subprocess.run(
        ['php', 'artisan', 'tinker', '--execute',
         'foreach(\\App\\Models\\Karyawan::whereNotNull("foto_wajah")->whereIn("status",["kontrak","tetap","magang","training","outsourcing","harian"])->get(["nik","nama","foto_wajah"]) as $k) echo $k->nik."|".$k->nama."|".$k->foto_wajah.PHP_EOL;'],
        capture_output=True, text=True, cwd=LARAVEL_PATH
    )
    rows = []
    for line in result.stdout.split('\n'):
        if '|' in line and 'INFO' not in line and 'ERROR' not in line:
            parts = line.strip().split('|')
            if len(parts) >= 3:
                rows.append({'nik': parts[0], 'nama': parts[1], 'foto': parts[2]})
    return rows

def main():
    # Cek service aktif
    try:
        r = requests.get(f'{FACE_URL}/health', timeout=3)
        h = r.json()
        print(f"Service: {h['status']} | Terdaftar sebelum: {h['terdaftar']}")
    except Exception as e:
        print(f"GAGAL: Service tidak aktif di {FACE_URL}: {e}")
        sys.exit(1)

    karyawan = get_karyawan_foto()
    print(f"Karyawan dengan foto di DB: {len(karyawan)}")

    berhasil, gagal = 0, []
    for k in karyawan:
        full_path = STORAGE_PATH + k['foto']
        if not os.path.exists(full_path):
            gagal.append(f"{k['nama']}: file tidak ada")
            continue
        try:
            with open(full_path, 'rb') as f:
                b64 = 'data:image/jpeg;base64,' + base64.b64encode(f.read()).decode()
            r = requests.post(f'{FACE_URL}/daftarkan',
                              json={'nik': k['nik'], 'foto': b64}, timeout=15)
            d = r.json()
            if d.get('success'):
                print(f"  ✓ {k['nama']} (NIK={k['nik']})")
                berhasil += 1
            else:
                gagal.append(f"{k['nama']}: {d.get('message')}")
                print(f"  ✗ {k['nama']}: {d.get('message')}")
        except Exception as e:
            gagal.append(f"{k['nama']}: {e}")
            print(f"  ✗ {k['nama']}: {e}")

    h2 = requests.get(f'{FACE_URL}/health').json()
    print(f"\nSelesai: {berhasil} berhasil, {len(gagal)} gagal")
    print(f"Service: {h2['terdaftar']} wajah terdaftar")
    if gagal:
        print("Gagal:")
        for g in gagal: print(f"  - {g}")

if __name__ == '__main__':
    main()
