# Login SSO Microsoft Azure - AutoBase

## Fitur Baru

Aplikasi AutoBase sekarang sudah mendukung login menggunakan akun Microsoft Azure AD (Single Sign-On) dengan fitur **auto-registration**.

## Cara Kerja Auto-Registration

Ketika user login dengan Microsoft Azure SSO:

1. **User Baru (Belum Terdaftar)**
   - Sistem mengambil UUID dari Azure AD sebagai user_id
   - Sistem akan otomatis membuat akun baru
   - User ID menggunakan UUID dari Azure AD (contoh: `a1b2c3d4-e5f6-7890-abcd-ef1234567890`)
   - Email dan nama diambil dari Azure AD
   - Password di-generate secara random (user tidak perlu tahu)
   - Status user: Aktif (is_active = 1)
   - User langsung bisa login

2. **User Sudah Terdaftar**
   - Sistem cek berdasarkan user_id (Azure UUID)
   - Sistem akan update informasi user (nama, email, last_login)
   - User langsung bisa login

## Cara Menggunakan

### Untuk User

1. Buka halaman login AutoBase
2. Klik tombol **"Sign in with Microsoft"**
3. Login menggunakan akun Microsoft Anda
4. Jika pertama kali login, akun Anda akan otomatis dibuat
5. Anda akan langsung masuk ke dashboard

### Untuk Administrator

#### Setup Azure AD Application

1. Login ke [Azure Portal](https://portal.azure.com)
2. Navigasi ke **Azure Active Directory** > **App registrations**
3. Klik **New registration**
4. Isi form:
   - Name: `AutoBase SSO`
   - Supported account types: `Accounts in this organizational directory only`
   - Redirect URI: `Web` - `http://127.0.0.1:8000/auth/azure/callback`
5. Klik **Register**
6. Catat **Application (client) ID** dan **Directory (tenant) ID**
7. Buka menu **Certificates & secrets**
8. Klik **New client secret**
9. Isi description dan pilih expiry
10. Catat **Value** (hanya muncul sekali!)
11. Buka menu **API permissions**
12. Pastikan permission berikut sudah ada:
    - Microsoft Graph > User.Read (Delegated)
    - Microsoft Graph > openid (Delegated)
    - Microsoft Graph > profile (Delegated)
    - Microsoft Graph > email (Delegated)

#### Konfigurasi Environment

Edit file `.env` dan tambahkan/update:

```env
TENANT_ID=your-tenant-id-from-azure
CLIENT_ID=your-client-id-from-azure
CLIENT_SECRET=your-client-secret-from-azure
APP_URL=http://127.0.0.1:8000
```

Untuk production, ganti `APP_URL` dengan domain production Anda dan tambahkan redirect URI di Azure Portal.

#### Manajemen User Auto-Registered

User yang auto-register melalui SSO akan memiliki:
- User ID: UUID dari Azure AD (contoh: `a1b2c3d4-e5f6-7890-abcd-ef1234567890`)
- Email: Dari Azure AD
- Nama: Dari Azure AD
- Password: Random (tidak perlu diketahui user)
- Status: Aktif
- Dealer: NULL (bisa di-assign manual oleh admin)
- Role: Belum ada (harus di-assign manual oleh admin)

**Penting:** Setelah user auto-register, admin perlu:
1. Assign dealer (jika diperlukan)
2. Assign role dan permissions
3. Assign brand access (jika diperlukan)

## Keamanan

- User baru otomatis dibuat dengan status aktif
- Email harus valid dari Azure AD
- Password di-generate random dan di-hash
- Session regeneration setelah login
- Logging semua aktivitas SSO
- Error handling yang aman

## Troubleshooting

### "Your account has been deactivated"
- Akun sudah di-nonaktifkan oleh admin
- Hubungi administrator untuk aktivasi

### "Failed to authenticate with Microsoft Azure"
- Cek konfigurasi TENANT_ID, CLIENT_ID, CLIENT_SECRET
- Cek Redirect URI di Azure Portal
- Cek log di `storage/logs/laravel.log`

### User baru tidak punya akses ke menu
- User auto-register belum punya role
- Admin perlu assign role melalui User Management

## File yang Dimodifikasi

- `app/Http/Controllers/AuthController.php` - Tambah method SSO dengan auto-register
- `routes/web.php` - Tambah route SSO
- `config/services.php` - Konfigurasi Azure
- `app/Providers/AppServiceProvider.php` - Register Azure provider
- `resources/views/auth/login.blade.php` - Tambah tombol SSO
- `composer.json` - Tambah dependencies

## Dependencies

- `laravel/socialite` - OAuth provider
- `socialiteproviders/microsoft` - Microsoft Azure provider

## Support

Untuk bantuan lebih lanjut, hubungi IT Team Eurokars Group Indonesia.
