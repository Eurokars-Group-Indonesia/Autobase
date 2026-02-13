# SSL Certificate Configuration

Letakkan file SSL certificate Anda di folder ini.

## Required Files

1. **certificate.crt** - SSL Certificate file
2. **private.key** - Private key file

## File Structure

```
docker/nginx/ssl/
├── certificate.crt    (Your SSL certificate)
├── private.key        (Your private key)
└── README.md          (This file)
```

## Cara Setup SSL Certificate

### Option 1: Menggunakan SSL Certificate yang Sudah Ada

Jika Anda sudah memiliki SSL certificate dari Certificate Authority (CA):

```bash
# Copy certificate file
copy path\to\your\certificate.crt docker\nginx\ssl\certificate.crt

# Copy private key file
copy path\to\your\private.key docker\nginx\ssl\private.key
```

### Option 2: Generate Self-Signed Certificate (Development Only)

Untuk development/testing, Anda bisa generate self-signed certificate:

#### Menggunakan OpenSSL (Windows)

1. Install OpenSSL jika belum ada (bisa via Git Bash atau download dari https://slproweb.com/products/Win32OpenSSL.html)

2. Generate certificate:

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/private.key \
  -out docker/nginx/ssl/certificate.crt \
  -subj "/C=ID/ST=Jakarta/L=Jakarta/O=YourCompany/CN=localhost"
```

#### Menggunakan PowerShell (Windows 10+)

```powershell
# Generate self-signed certificate
$cert = New-SelfSignedCertificate -DnsName "localhost" -CertStoreLocation "cert:\LocalMachine\My" -NotAfter (Get-Date).AddYears(1)

# Export certificate
$certPath = "docker\nginx\ssl\certificate.crt"
$keyPath = "docker\nginx\ssl\private.key"

Export-Certificate -Cert $cert -FilePath $certPath
$mypwd = ConvertTo-SecureString -String "password" -Force -AsPlainText
Export-PfxCertificate -Cert $cert -FilePath "temp.pfx" -Password $mypwd

# Convert PFX to PEM (requires OpenSSL)
openssl pkcs12 -in temp.pfx -nocerts -out $keyPath -nodes -password pass:password
openssl pkcs12 -in temp.pfx -clcerts -nokeys -out $certPath -password pass:password

# Clean up
Remove-Item temp.pfx
```

### Option 3: Generate dengan Docker

Jika Anda sudah punya Docker running:

```bash
docker run --rm -v ${PWD}/docker/nginx/ssl:/certs alpine/openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /certs/private.key -out /certs/certificate.crt -subj "/C=ID/ST=Jakarta/L=Jakarta/O=YourCompany/CN=localhost"
```

## Verifikasi Certificate

Setelah file SSL tersedia, verifikasi dengan:

```bash
# Check certificate
openssl x509 -in docker/nginx/ssl/certificate.crt -text -noout

# Check private key
openssl rsa -in docker/nginx/ssl/private.key -check
```

## Production SSL Certificate

Untuk production, gunakan SSL certificate dari Certificate Authority terpercaya seperti:

- **Let's Encrypt** (Free) - https://letsencrypt.org/
- **Cloudflare SSL** (Free untuk Cloudflare users)
- **DigiCert**
- **Sectigo**
- **GoDaddy**

### Let's Encrypt dengan Certbot

```bash
# Install certbot
# Kemudian generate certificate
certbot certonly --standalone -d yourdomain.com

# Certificate akan tersimpan di:
# /etc/letsencrypt/live/yourdomain.com/fullchain.pem
# /etc/letsencrypt/live/yourdomain.com/privkey.pem

# Copy ke folder SSL
copy C:\Certbot\live\yourdomain.com\fullchain.pem docker\nginx\ssl\certificate.crt
copy C:\Certbot\live\yourdomain.com\privkey.pem docker\nginx\ssl\private.key
```

## Security Notes

⚠️ **PENTING:**

1. **JANGAN commit private key ke Git repository!**
2. File `private.key` harus dijaga kerahasiaannya
3. Set permission yang tepat untuk private key (hanya owner yang bisa read)
4. Untuk production, gunakan certificate dari CA terpercaya
5. Self-signed certificate akan menampilkan warning di browser

## Restart Nginx Setelah Update Certificate

```bash
docker-compose restart nginx
```

## Troubleshooting

### Error: "SSL certificate not found"

Pastikan file certificate dan private key ada di folder `docker/nginx/ssl/`:

```bash
dir docker\nginx\ssl\
```

Harus ada:
- certificate.crt
- private.key

### Browser Warning "Not Secure"

Jika menggunakan self-signed certificate, browser akan menampilkan warning. Ini normal untuk development. Klik "Advanced" dan "Proceed to localhost" untuk melanjutkan.

### Permission Denied

Jika ada error permission di Linux/Mac:

```bash
chmod 600 docker/nginx/ssl/private.key
chmod 644 docker/nginx/ssl/certificate.crt
```

## Custom Domain

Jika menggunakan custom domain (bukan localhost), update:

1. File `docker/nginx/default.conf`:
   ```nginx
   server_name yourdomain.com www.yourdomain.com;
   ```

2. File `.env`:
   ```
   APP_URL=https://yourdomain.com:8443
   ```

3. Generate certificate dengan domain yang sesuai
