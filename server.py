"""
Server Proxy ASGI (Uvicorn + FastAPI) untuk Sistem Presensi PWA PT. CAK.
Menghubungkan ASGI Uvicorn Server ke Backend PHP Laravel 11.
"""

import os
import sys
import time
import socket
import atexit
import subprocess
from contextlib import asynccontextmanager
from typing import Optional

from fastapi import FastAPI, Request, Response
import httpx
import uvicorn

PHP_HOST = "127.0.0.1"
PHP_PORT = 8080
LARAVEL_DIR = os.path.dirname(os.path.abspath(__file__))
php_process: Optional[subprocess.Popen] = None


def is_port_in_use(port: int) -> bool:
    """Cek apakah port sedang digunakan."""
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        return s.connect_ex((PHP_HOST, port)) == 0


def start_php_backend():
    """Menjalankan PHP Built-in Server untuk Laravel di background."""
    global php_process
    if is_port_in_use(PHP_PORT):
        return

    cmd = [
        "php",
        "artisan",
        "serve",
        f"--host={PHP_HOST}",
        f"--port={PHP_PORT}",
    ]

    try:
        php_process = subprocess.Popen(
            cmd,
            cwd=LARAVEL_DIR,
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
        # Tunggu hingga server siap menerima koneksi
        for _ in range(30):
            if is_port_in_use(PHP_PORT):
                return
            time.sleep(0.2)
    except Exception as e:
        print(f"[!] Gagal menjalankan PHP backend: {e}")


def stop_php_backend():
    """Menghentikan proses PHP backend saat Uvicorn di-shutdown."""
    global php_process
    if php_process and php_process.poll() is None:
        php_process.terminate()
        try:
            php_process.wait(timeout=3)
        except subprocess.TimeoutExpired:
            php_process.kill()


atexit.register(stop_php_backend)


@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup
    start_php_backend()
    yield
    # Shutdown
    stop_php_backend()


app = FastAPI(
    title="Presensi PWA PT. CAK - Uvicorn ASGI Runner",
    version="1.0.0",
    lifespan=lifespan,
    docs_url=None,
    redoc_url=None,
)


@app.api_route("/{path:path}", methods=["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS", "HEAD"])
async def proxy_all(request: Request, path: str):
    """
    Reverse proxy dari Uvicorn ASGI ke Backend PHP Laravel.
    Meneruskan method, raw headers (termasuk session & cookie), query params, dan payload request secara transparan.
    """
    target_url = f"http://{PHP_HOST}:{PHP_PORT}/{path}"
    if request.url.query:
        target_url = f"{target_url}?{request.url.query}"

    # Salin raw headers dari request klien
    req_headers = []
    client_host = request.headers.get("host", "127.0.0.1:8000")
    for k, v in request.headers.raw:
        k_str = k.decode("latin-1").lower()
        if k_str == "host":
            req_headers.append((b"host", f"{PHP_HOST}:{PHP_PORT}".encode("latin-1")))
        else:
            req_headers.append((k, v))

    # Tambahkan header X-Forwarded untuk identitas klien dan host asli
    client_ip = request.client.host if request.client else "127.0.0.1"
    req_headers.append((b"x-forwarded-for", client_ip.encode("latin-1")))
    req_headers.append((b"x-forwarded-proto", request.url.scheme.encode("latin-1")))
    req_headers.append((b"x-forwarded-host", client_host.encode("latin-1")))

    body = await request.body()

    try:
        async with httpx.AsyncClient(timeout=60.0, follow_redirects=False) as client:
            resp = await client.request(
                method=request.method,
                url=target_url,
                headers=req_headers,
                content=body,
            )

            excluded_headers = {b"content-encoding", b"content-length", b"transfer-encoding", b"connection"}

            response = Response(
                content=resp.content,
                status_code=resp.status_code,
                media_type=resp.headers.get("content-type"),
            )

            # Salin seluruh raw headers dari backend PHP (termasuk multiple Set-Cookie headers) secara utuh
            response.raw_headers.clear()
            for k, v in resp.headers.raw:
                if k.lower() in excluded_headers:
                    continue
                # Jika ada redirect Location, pastikan tetap mengarah ke host/port Uvicorn klien (port 8000)
                if k.lower() == b"location":
                    loc_str = v.decode("latin-1")
                    loc_str = loc_str.replace(f"{PHP_HOST}:{PHP_PORT}", client_host)
                    loc_str = loc_str.replace(f"localhost:{PHP_PORT}", client_host)
                    response.raw_headers.append((b"location", loc_str.encode("latin-1")))
                else:
                    response.raw_headers.append((k, v))

            return response
    except httpx.ConnectError:
        return Response(
            content="<b>502 Bad Gateway</b>: Gagal terhubung ke backend PHP Laravel. Pastikan PHP terinstal.",
            status_code=502,
            media_type="text/html",
        )
    except Exception as err:
        return Response(
            content=f"<b>500 Internal Server Error</b>: {str(err)}",
            status_code=500,
            media_type="text/html",
        )


if __name__ == "__main__":
    port = int(os.environ.get("PORT", 8000))
    host = os.environ.get("HOST", "127.0.0.1")
    print(f"============================================================")
    print(f" Presensi PWA PT. Cahaya Anugrah Kalimantan (Uvicorn ASGI)  ")
    print(f" URL Akses Peramban: http://{host}:{port}                   ")
    print(f" Tekan Ctrl + C untuk menghentikan server                   ")
    print(f"============================================================")
    uvicorn.run("server:app", host=host, port=port, log_level="info", reload=False)
