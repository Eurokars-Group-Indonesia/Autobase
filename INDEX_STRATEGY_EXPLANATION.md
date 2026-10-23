# Index Strategy untuk Fields dengan FULLTEXT + Sorting

## Pertanyaan
Apakah perlu 2 index untuk field yang bisa di-search (FULLTEXT) dan di-sort (B-TREE)?

## Jawaban: **YA, perlu 2 index yang berbeda**

### Alasan Teknis

#### 1. FULLTEXT Index (untuk Search)
- **Tujuan**: Pencarian text dengan partial matching
- **Cara Kerja**: Menggunakan inverted index untuk full-text search
- **Keuntungan**: 
  - Cepat untuk pencarian text kompleks
  - Support wildcard matching (*word*)
  - Support boolean operators (AND, OR)
- **Tidak bisa untuk**: Sorting/ORDER BY

#### 2. B-TREE Index (untuk Sorting)
- **Tujuan**: Sorting dan range queries
- **Cara Kerja**: Menggunakan balanced tree structure
- **Keuntungan**:
  - Optimal untuk ORDER BY
  - Optimal untuk range queries (>, <, BETWEEN)
  - Optimal untuk equality (=)
- **Tidak bisa untuk**: Full-text search

### Mengapa Tidak Bisa Pakai 1 Index?

```
FULLTEXT Index:
- Tidak support ORDER BY
- Tidak support range queries
- Hanya untuk text search

B-TREE Index:
- Tidak support FULLTEXT search
- Tidak support wildcard matching
- Hanya untuk sorting/filtering
```

## Current Implementation

### Fields dengan 2 Indexes:

| Field | B-TREE Index | FULLTEXT Index | Fungsi |
|-------|--------------|----------------|--------|
| `customer_name` | idx_customer_name | idx_customer_name_ngram | Search + Sort |
| `account_name` | tx_header_account_name_index | idx_account_name_ngram | Search + Sort |
| `registration_no` | idx_registration_no | idx_registration_no_ngram | Search + Sort |

### Query Examples

#### Search (menggunakan FULLTEXT)
```php
// Search dengan wildcard matching
MATCH(customer_name) AGAINST('*dimple* *sameer*' IN BOOLEAN MODE)
// Menggunakan: idx_customer_name_ngram (FULLTEXT)
```

#### Sort (menggunakan B-TREE)
```php
// Sort ascending
ORDER BY customer_name ASC
// Menggunakan: idx_customer_name (B-TREE)

// Sort descending
ORDER BY customer_name DESC
// Menggunakan: idx_customer_name (B-TREE)
```

## Performance Impact

### Storage
- **Overhead**: ~2x storage untuk field dengan 2 indexes
- **Acceptable**: Karena field ini adalah text (tidak terlalu besar)
- **Trade-off**: Worth it untuk performance

### Query Performance

#### Search Query
```sql
SELECT * FROM tx_header 
WHERE MATCH(customer_name) AGAINST('*dimple*' IN BOOLEAN MODE)
-- Uses: idx_customer_name_ngram (FULLTEXT)
-- Speed: Very fast (< 1ms untuk 1M rows)
```

#### Sort Query
```sql
SELECT * FROM tx_header 
ORDER BY customer_name ASC
-- Uses: idx_customer_name (B-TREE)
-- Speed: Very fast (< 1ms untuk 1M rows)
```

#### Combined (Search + Sort)
```sql
SELECT * FROM tx_header 
WHERE MATCH(customer_name) AGAINST('*dimple*' IN BOOLEAN MODE)
ORDER BY customer_name ASC
-- Uses: idx_customer_name_ngram (FULLTEXT) + idx_customer_name (B-TREE)
-- Speed: Very fast (< 10ms untuk 1M rows)
```

## Best Practices

### ✅ DO:
1. **Gunakan 2 indexes** untuk field yang perlu search + sort
2. **FULLTEXT untuk search** dengan wildcard/partial matching
3. **B-TREE untuk sort** dan range queries
4. **Monitor index size** - jika terlalu besar, pertimbangkan partitioning

### ❌ DON'T:
1. **Jangan gunakan FULLTEXT untuk sorting** - tidak support
2. **Jangan gunakan B-TREE untuk full-text search** - tidak optimal
3. **Jangan buat 3+ indexes** untuk 1 field - overhead terlalu besar
4. **Jangan lupa maintain indexes** - rebuild jika fragmented

## Maintenance

### Check Index Size
```sql
SELECT 
    OBJECT_NAME(i.object_id) AS TableName,
    i.name AS IndexName,
    ps.used_page_count * 8 / 1024 AS SizeMB
FROM sys.indexes i
JOIN sys.dm_db_index_physical_stats(DB_ID(), NULL, NULL, NULL, 'LIMITED') ps
    ON i.object_id = ps.object_id 
    AND i.index_id = ps.index_id
WHERE OBJECT_NAME(i.object_id) = 'tx_header'
ORDER BY ps.used_page_count DESC;
```

### Rebuild Fragmented Indexes
```sql
ALTER INDEX idx_customer_name ON tx_header REBUILD;
ALTER INDEX idx_customer_name_ngram ON tx_header REBUILD;
```

## Summary

| Aspek | FULLTEXT | B-TREE |
|-------|----------|--------|
| Search | ✅ Optimal | ❌ Not optimal |
| Sort | ❌ Not supported | ✅ Optimal |
| Wildcard | ✅ Yes | ❌ No |
| Range Query | ❌ No | ✅ Yes |
| Storage | Medium | Small |
| Maintenance | Medium | Low |

**Kesimpulan**: Untuk field yang perlu search + sort, **2 indexes adalah solusi optimal** dan tidak ada alternatif yang lebih baik.
