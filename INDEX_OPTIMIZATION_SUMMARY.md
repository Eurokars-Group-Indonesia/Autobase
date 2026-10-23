# Database Index Optimization for Sorting

## Overview
Added database indexes to all sortable columns in `tx_header` and `tx_body` tables to optimize sorting performance.

## Migration Created
- **File**: `2026_03_20_094404_add_indexes_for_sorting_optimization.php`
- **Status**: ✅ Successfully migrated

---

## Transaction Header (tx_header) Indexes

### Sortable Columns (UI)
| Column | Index Name | Status | Purpose |
|--------|-----------|--------|---------|
| `invoice_no` | idx_invoice_no | ✅ Already existed | Sorting, searching |
| `wip_no` | idx_wip_no | ✅ Already existed | Sorting, searching |
| `invoice_date` | idx_invoice_date | ✅ Already existed | Sorting, filtering |
| `account_code` | idx_account_code | ✅ **NEW** | Sorting |
| `customer_name` | idx_customer_name | ✅ **NEW** | Sorting (B-TREE) |
| `customer_name` | idx_customer_name_fulltext | ✅ Already existed | FULLTEXT search |
| `registration_no` | idx_registration_no | ✅ **NEW** | Sorting (B-TREE) |
| `registration_no` | idx_registration_no_fulltext | ✅ Already existed | FULLTEXT search |
| `chassis` | idx_chassis | ✅ Already existed | Sorting, searching |
| `document_type` | idx_document_type | ✅ **NEW** | Sorting |
| `pos_code` | idx_pos_code | ✅ Already existed | Filtering |
| `gross_value` | idx_gross_value | ✅ **NEW** | Sorting |
| `net_value` | idx_net_value | ✅ **NEW** | Sorting |

### Other Indexes
| Column | Index Name | Status | Purpose |
|--------|-----------|--------|---------|
| `created_by` | idx_created_by | ✅ Already existed | Audit trail |
| `updated_by` | idx_updated_by | ✅ Already existed | Audit trail |
| `is_active` | idx_is_active | ✅ Already existed | Soft delete filter |
| `unique_id` | tx_header_unique_id_unique | ✅ Already existed | UUID lookup |

---

## Transaction Body (tx_body) Indexes

### Sortable Columns (UI)
| Column | Index Name | Status | Purpose |
|--------|-----------|--------|---------|
| `part_no` | idx_part_no | ✅ Already existed | Sorting, searching |
| `invoice_no` | idx_invoice_no | ✅ Already existed | Sorting, searching |
| `wip_no` | idx_wip_no | ✅ Already existed | Sorting, searching |
| `pos_code` | *(uses tx_header filter)* | N/A | Filtering via join |
| `description` | idx_description | ✅ **NEW** | Sorting |
| `date_decard` | idx_date_decard | ✅ **NEW** | Sorting, filtering |
| `qty` | idx_qty | ✅ **NEW** | Sorting |
| `unit` | idx_unit | ✅ **NEW** | Sorting |
| `cost_price` | idx_cost_price | ✅ **NEW** | Sorting |
| `selling_price` | idx_selling_price | ✅ **NEW** | Sorting |
| `discount` | idx_discount | ✅ **NEW** | Sorting |
| `extended_price` | idx_extended_price | ✅ **NEW** | Sorting |
| `part_or_labour` | idx_part_or_labour | ✅ **NEW** | Sorting |
| `invoice_status` | idx_invoice_status | ✅ **NEW** | Sorting, filtering |

### Other Indexes
| Column | Index Name | Status | Purpose |
|--------|-----------|--------|---------|
| `analysis_code` | idx_analysis_code | ✅ **NEW** | Common filter |
| `account_code` | idx_account_code | ✅ **NEW** | Common filter |
| `department` | idx_department | ✅ **NEW** | Common filter |
| `created_by` | idx_created_by | ✅ Already existed | Audit trail |
| `updated_by` | idx_updated_by | ✅ Already existed | Audit trail |
| `is_active` | idx_is_active | ✅ Already existed | Soft delete filter |
| `unique_id` | tx_body_unique_id_unique | ✅ Already existed | UUID lookup |

---

## Performance Benefits

### Before Optimization
- Sorting on non-indexed columns required **full table scans**
- Large datasets would cause **slow sorting operations**
- Filesort operations in MySQL execution plan

### After Optimization
- ✅ **B-TREE indexes** enable fast sorting using index order
- ✅ **Reduced query execution time** for sorted results
- ✅ **Better performance** on pagination with ORDER BY
- ✅ **Optimized filtering** on indexed columns

---

## Usage

### Transaction Header Sorting
All sortable columns in the UI now have corresponding indexes:
```php
// Controller allowed sort columns
$allowedSortColumns = [
    'invoice_no', 'wip_no', 'invoice_date', 'account_code',
    'customer_name', 'registration_no', 'chassis', 'document_type',
    'pos_code', 'gross_value', 'net_value'
];
```

### Transaction Body Sorting
All sortable columns in the UI now have corresponding indexes:
```php
// Controller allowed sort columns
$allowedSortColumns = [
    'part_no', 'invoice_no', 'wip_no', 'description', 'date_decard',
    'qty', 'unit', 'cost_price', 'selling_price', 'discount',
    'extended_price', 'part_or_labour', 'invoice_status', 'pos_code'
];
```

---

## Rollback

To rollback these changes:
```bash
php artisan migrate:rollback --step=1
```

This will drop all the newly created indexes while preserving the original indexes.

---

## Notes

1. **FULLTEXT vs B-TREE**: 
   - `customer_name` and `registration_no` have BOTH FULLTEXT (for search) and B-TREE (for sorting) indexes
   - FULLTEXT indexes are used for `MATCH() AGAINST()` queries
   - B-TREE indexes are used for `ORDER BY` clauses

2. **Index Naming Convention**:
   - All indexes follow the pattern: `idx_<column_name>`
   - Unique indexes use table prefix: `tx_header_<column>_unique`

3. **Safe Migration**:
   - Migration checks if index exists before creating
   - Prevents duplicate index errors
   - Can be run multiple times safely

---

## Testing

To verify indexes are working:
```sql
-- Check tx_header indexes
SHOW INDEX FROM tx_header;

-- Check tx_body indexes
SHOW INDEX FROM tx_body;

-- Analyze query execution plan
EXPLAIN SELECT * FROM tx_header ORDER BY customer_name LIMIT 10;
EXPLAIN SELECT * FROM tx_body ORDER BY unit LIMIT 10;
```

Look for:
- ✅ `type: index` or `type: range` in execution plan
- ✅ `key: idx_<column_name>` showing which index is used
- ❌ Avoid `type: ALL` (full table scan)

---

**Created**: 2026-03-20  
**Migration Status**: ✅ Completed Successfully
