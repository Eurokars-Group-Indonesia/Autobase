# Azure AD User Sync - UI Implementation

## Fitur yang Diimplementasikan

### 1. Button Sync Azure AD
- Lokasi: Di header halaman Users Management, sebelah kiri button "Add User"
- Icon: Arrow repeat (bi-arrow-repeat)
- Warna: Info (btn-info)
- Permission: Hanya muncul jika user memiliki permission `users.edit`

### 2. Loading Overlay
- Tampil saat proses sync sedang berjalan
- Full screen overlay dengan background gelap (rgba(0, 0, 0, 0.7))
- Menampilkan spinner dan text "Syncing users from Azure AD..."
- Menggunakan z-index 9999 agar berada di atas semua elemen
- Button sync akan disabled selama proses berlangsung

### 3. Modal Hasil Sync
Modal akan muncul setelah proses sync selesai dengan informasi:

#### Jika Sukses:
- Alert hijau dengan icon check-circle
- Statistik dalam 3 card:
  - **Total Synced** (biru): Total user yang berhasil di-sync
  - **Created** (hijau): Jumlah user baru yang dibuat
  - **Updated** (info): Jumlah user yang di-update

#### Jika Ada Error:
- Tabel error yang menampilkan:
  - Email user yang gagal
  - Pesan error
- Counter jumlah error

#### Jika Gagal Total:
- Alert merah dengan icon exclamation-triangle
- Pesan error detail

### 4. Action Buttons di Modal
- **Close**: Menutup modal
- **Refresh Page**: Reload halaman untuk melihat data terbaru

## Endpoint API
- **Route**: `POST /users/sync-azure`
- **Name**: `users.sync.azure`
- **Controller**: `UserController@syncFromAzure`
- **Permission**: `users.edit`

## Response Format
```json
{
  "success": true,
  "message": "Successfully synced X users from Azure AD.",
  "data": {
    "total_synced": 10,
    "created": 5,
    "updated": 5,
    "errors": [
      {
        "email": "user@example.com",
        "error": "Error message"
      }
    ]
  }
}
```

## Teknologi yang Digunakan
- Bootstrap 5 Modal
- Fetch API untuk AJAX request
- CSS custom untuk loading overlay
- Bootstrap Icons

## Catatan
- Loading overlay menggunakan style yang sama dengan implementasi di transaction body/header
- Modal menggunakan Bootstrap 5 modal component
- CSRF token otomatis disertakan dalam request
- Error handling mencakup network error dan server error
