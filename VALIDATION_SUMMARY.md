# Validasi Import - Summary

## TransactionHeaderImport

### Validasi Length String (Maksimal Karakter):
1. **service_code** - Max 3 karakter
2. **account_code** - Max 20 karakter
3. **customer_name** - Max 150 karakter
4. **department** - Max 50 karakter
5. **registration_no** - Max 20 karakter
6. **chassis** - Max 25 karakter
7. **currency_code** - Max 3 karakter (required)
8. **customer_discount** - Max 10 karakter
9. **description** - Max 250 karakter
10. **engine_no** - Max 20 karakter
11. **account_company** - Max 50 karakter

### Validasi Integer:
- **wip_no** - Harus integer, tidak boleh text seperti "WIP000001"
- **invoice_no** - Harus integer
- **magic_id (MAGICH)** - Harus integer
- **mileage** - Harus integer (0 diperbolehkan)

### Validasi Enum:
- **document_type** - Harus 'I' atau 'C'

### Validasi Required:
- **wip_no** - Required
- **invoice_date** - Required
- **magic_id** - Required
- **invoice_no** - Required
- **mileage** - Required
- **currency_code** - Required

---

## TransactionBodyImport

### Validasi Length String (Maksimal Karakter):
1. **part_no** - Max 100 karakter
2. **description** - Max 250 karakter
3. **unit (UOI)** - Max 10 karakter
4. **account_code** - Max 20 karakter
5. **department** - Max 50 karakter
6. **franchise_code** - Max 3 karakter
7. **warranty_code** - Max 3 karakter
8. **supplier_code** - Max 20 karakter

### Validasi Integer:
- **invoice_no** - Harus integer
- **wip_no** - Harus integer, tidak boleh text seperti "WIP000001"
- **line** - Harus integer

### Validasi Char (1 karakter):
- **analysis_code** - Harus 1 karakter
- **sales_type** - Harus 1 karakter
- **vat** - 1 karakter (optional)
- **menu_vat** - 1 karakter (optional)
- **menu_flag** - 1 karakter (optional)
- **labour_rates** - 1 karakter (optional)

### Validasi Enum:
- **invoice_status** - Harus 'X' atau 'C'
- **part_or_labour** - Harus 'P' atau 'L'

### Validasi Required:
- **part_no** - Required
- **invoice_no** - Required
- **wip_no** - Required
- **line** - Required
- **analysis_code** - Required
- **invoice_status** - Required
- **sales_type** - Required
- **part_or_labour** - Required

---

## Error Handling

### Cache Management:
- Cache di-flush otomatis saat terjadi error import (apapun jenisnya)
- Cache di-flush saat import sukses
- Cache di-flush saat partial success (ada error tapi beberapa data berhasil)

### SQL Error Handling:
- Error "Incorrect integer value" ditangkap dan ditampilkan dengan pesan yang jelas
- Error "Data too long" ditangkap dan ditampilkan dengan field yang bermasalah
- Error "Incorrect date value" ditangkap dan ditampilkan dengan format yang benar
- Error "Column cannot be null" ditangkap dan ditampilkan field yang required

### User-Friendly Messages:
- Semua error menampilkan nomor baris, field, nilai, dan pesan error yang jelas
- Nilai yang terlalu panjang dipotong dengan "..." untuk readability
- Pesan error dalam bahasa yang mudah dipahami user
