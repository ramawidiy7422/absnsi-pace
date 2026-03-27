@echo off
set TF_ENABLE_ONEDNN_OPTS=0
set TF_CPP_MIN_LOG_LEVEL=3
echo ========================================
echo   Face Recognition Service - Farmasi
echo ========================================
echo.
echo Memulai service pada http://127.0.0.1:5000
echo Tekan Ctrl+C untuk menghentikan
echo.
python app.py
pause
