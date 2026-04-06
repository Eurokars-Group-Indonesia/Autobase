# Phone Number Search Optimization

## Problem
Pencarian phone number dengan wildcard (`%search%`) tidak efisien menggunakan LIKE karena tidak bisa menggunakan B-TREE index.

## Solution
Menggunakan FULLTEXT index dengan ngram parser untuk mendukung partial matching yang lebih cepat.

## Steps to Implement

### 1. Run Migration
Jalankan migration untuk membuat FULLTEXT index dengan ngram parser:

```bash
php artisan migrate
```

Migration ini akan:
- Drop existing B-TREE indexes untuk phone_number_1, phone_number_2, phone_number_3, phone_number_4
- Create FULLTEXT index dengan ngram parser untuk keempat kolom phone number

### 2. Update Controller Code

Ganti kode pencarian phone number di `TransactionHeaderController.php`:

**Lokasi 1: Method `index()` - Line ~104-111**
**Lokasi 2: Method `search()` - Line ~787-794**

**DARI:**
```php
if ($isPhoneNumber) {
    // Use LIKE with wildcard for phone number search
    $searchWhere->where(function($phoneWhere) use ($search) {
        $phoneWhere->where('tx_header.phone_number_1', 'like', '%' . $search . '%')
                   ->orWhere('tx_header.phone_number_2', 'like', '%' . $search . '%')
                   ->orWhere('tx_header.phone_number_3', 'like', '%' . $search . '%')
                   ->orWhere('tx_header.phone_number_4', 'like', '%' . $search . '%');
    });
}
```

**MENJADI:**
```php
if ($isPhoneNumber) {
    // Use FULLTEXT search with ngram parser for phone numbers
    // This supports partial matching and is much faster than LIKE %search%
    // The ngram index will handle wildcard searches efficiently
    $searchWhere->whereRaw(
        'MATCH(phone_number_1, phone_number_2, phone_number_3, phone_number_4) AGAINST(? IN BOOLEAN MODE)',
        [$search]
    );
}
```

## Benefits

1. **Performance**: FULLTEXT search dengan ngram jauh lebih cepat daripada LIKE %search%
2. **Partial Matching**: Mendukung pencarian sebagian dari phone number
3. **Index Usage**: Menggunakan FULLTEXT index untuk optimasi query
4. **Scalability**: Performa tetap baik meskipun data bertambah banyak

## How ngram Works

- ngram parser membuat token dari setiap N karakter berurutan
- Default token_size = 2 (bigram)
- Contoh: "9111500598209850" akan di-index sebagai: "91", "11", "11", "15", "50", "00", "05", "59", "98", "82", "20", "09", "98", "85", "50"
- Pencarian "911150" akan match karena token-tokennya ada di index

## Testing

Setelah implementasi, test dengan:
1. Full phone number: "9111500598209850" ✓
2. Partial awal: "9111" ✓
3. Partial tengah: "1500" ✓
4. Partial akhir: "9850" ✓

## Rollback

Jika perlu rollback:
```bash
php artisan migrate:rollback
```

Ini akan mengembalikan ke B-TREE indexes.
