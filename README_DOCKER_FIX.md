# 🚀 Docker Permission Fix - Quick Guide

## Masalah yang Diperbaiki

- ❌ "unknown uid 1000" error
- ❌ "Operation not permitted" saat chmod
- ❌ Permission error saat import Excel
- ❌ Permission hilang setelah restart container

## ✅ Solusi

Saya sudah fix 4 file Docker:
1. `Dockerfile` - Container run sebagai www-data, bukan UID 1000
2. `docker-compose.yml` - Hapus user mapping yang bermasalah
3. `docker/entrypoint.sh` - Auto-fix permission saat container start
4. `docker/supervisord.conf` - Fix supervisord user issue

## 📦 Cara Deploy ke Server

### Option 1: Upload File Manual (Recommended)

**1. Upload 4 file ini ke server:**
```bash
# Dari local machine ke server
scp Dockerfile root@egi-dockerdev:/home/itteam/service-history-new-old/
scp docker-compose.yml root@egi-dockerdev:/home/itteam/service-history-new-old/
scp docker/entrypoint.sh root@egi-dockerdev:/home/itteam/service-history-new-old/docker/
scp docker/supervisord.conf root@egi-dockerdev:/home/itteam/service-history-new-old/docker/
```

**2. SSH ke server dan rebuild:**
```bash
ssh root@egi-dockerdev
cd /home/itteam/service-history-new-old

# Rebuild container
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Cek logs
docker-compose logs -f app
```

### Option 2: Via Git (Jika sudah commit)

```bash
# Di server
cd /home/itteam/service-history-new-old
git pull origin main

# Rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Option 3: Gunakan Script Otomatis

```bash
# Upload script ke server
scp deploy-docker-fix.sh root@egi-dockerdev:/home/itteam/service-history-new-old/

# SSH ke server
ssh root@egi-dockerdev
cd /home/itteam/service-history-new-old

# Jalankan script
chmod +x deploy-docker-fix.sh
./deploy-docker-fix.sh
```

## ✅ Verifikasi

Setelah rebuild, cek:

```bash
# Cek user (harus www-data, bukan unknown uid 1000)
docker exec -it laravel_app whoami

# Cek permission
docker exec -it laravel_app ls -la /var/www/html/storage

# Test write
docker exec -it laravel_app touch /var/www/html/storage/test.txt
```

## 🎯 Hasil Akhir

Setelah deploy:
- ✅ Container berjalan sebagai `www-data` (bukan UID 1000)
- ✅ Permission storage otomatis 775
- ✅ Import Excel langsung bisa tanpa error
- ✅ Tidak perlu chmod manual lagi selamanya!
- ✅ Permission persist setelah restart

## 📚 Dokumentasi Lengkap

- `DOCKER_PERMISSION_FIX_PERMANENT.md` - Penjelasan lengkap semua perubahan
- `FIX_UID_1000_ISSUE.md` - Troubleshooting UID 1000 issue
- `deploy-docker-fix.sh` - Script otomatis untuk deploy

## 🆘 Troubleshooting

### Jika masih ada error permission setelah rebuild:

```bash
# Clear cache laravel-excel
docker exec -it laravel_app rm -rf /var/www/html/storage/framework/cache/laravel-excel

# Restart container
docker-compose restart app queue
```

### Jika "unknown uid" masih muncul:

Berarti rebuild belum berhasil. Pastikan file sudah terupload, lalu:
```bash
docker-compose down
docker rmi service-history-new-old_app service-history-new-old_queue
docker-compose build --no-cache
docker-compose up -d
```

## 📞 Support

Jika ada masalah, cek logs:
```bash
docker-compose logs app
docker-compose logs queue
```

---

**TL;DR:** Upload 4 file yang sudah difix ke server, lalu jalankan:
```bash
cd /home/itteam/service-history-new-old
docker-compose down && docker-compose build --no-cache && docker-compose up -d
```

**Done!** 🎉 Tidak perlu chmod manual lagi!
