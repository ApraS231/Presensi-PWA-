"""
Script Peluncur Lokal Aplikasi Presensi PWA PT. CAK dengan Uvicorn ASGI Server.
Penggunaan:
    python serve.py
    atau
    uvicorn server:app --host 127.0.0.1 --port 8000
"""

import os
import sys
import uvicorn

if __name__ == "__main__":
    host = os.environ.get("HOST", "127.0.0.1")
    port = int(os.environ.get("PORT", 8000))
    
    print("-" * 65)
    print(" SISTEM PRESENSI PWA PT. CAHAYA ANUGRAH KALIMANTAN")
    print(f" Menjalankan server lokal via Uvicorn ASGI di http://{host}:{port}")
    print("-" * 65)
    
    uvicorn.run("server:app", host=host, port=port, log_level="info", reload=False)
