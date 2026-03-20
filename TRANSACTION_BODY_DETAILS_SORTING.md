# Transaction Body/Details Sorting Implementation

## Overview
Added client-side sorting functionality to the Transaction Body/Details tables in the Transaction Header view. This includes both the modal view (when clicking "View Details") and the inline view (when filtering).

---

## Changes Made

### 1. Modal Table (Transaction Body Details Modal)

**File**: `resources/views/transactions/index.blade.php`

#### Added Sortable Headers
- ✅ Part No
- ✅ Description
- ✅ Date Decard
- ✅ Qty
- ✅ Cost Price
- ✅ Selling Price
- ✅ Discount %
- ✅ Extended Price
- ✅ **Unit** (NEW)
- ✅ VAT
- ✅ Analysis Code
- ✅ Parts/Labour

#### CSS Styles Added
```css
.sortable-header-modal { ... }
.sortable-header-modal:hover { ... }
.sortable-header-modal.sorted-asc { ... }
.sortable-header-modal.sorted-desc { ... }
```

#### JavaScript Functions Added
- `renderModalTable(data)` - Renders the table with sorted data
- `updateModalSortIcons(column, direction)` - Updates sort icons
- Modal sort variables: `modalSortColumn`, `modalSortDirection`, `currentModalData`
- Click handler for `.sortable-header-modal`

#### Controller Update
**File**: `app/Http/Controllers/TransactionHeaderController.php`

Updated `getBodyDetails()` method to support server-side sorting:
```php
$allowedSortColumns = [
    'line', 'part_no', 'description', 'date_decard', 'qty',
    'cost_price', 'selling_price', 'discount', 'extended_price',
    'vat', 'analysis_code', 'part_or_labour', 'unit', 'invoice_status'
];
```

---

### 2. Inline Table (When Filtering)

**File**: `resources/views/transactions/partials/table.blade.php`

#### Added Sortable Headers
- ✅ Part No
- ✅ Description
- ✅ Date Decard
- ✅ Qty
- ✅ Cost Price
- ✅ Selling Price
- ✅ Discount %
- ✅ Extended Price
- ✅ **Unit** (NEW)
- ✅ Part/Labour

#### CSS Styles Added
```css
.sortable-header-inline { ... }
.sortable-header-inline:hover { ... }
.sortable-header-inline.sorted-asc { ... }
.sortable-header-inline.sorted-desc { ... }
```

#### JavaScript Functions Added
- Click handler for `.sortable-header-inline` - Client-side sorting
- `getColumnIndex(column)` - Maps column names to cell indices
- `parseValue(column, text)` - Parses values based on column type (numeric, date, string)

---

### 3. Unit Column Added

The **Unit** column was added to both tables:
- Modal table: Shows `item.unit`
- Inline table: Shows `$body->unit`

This column is now sortable and has the same styling as other sortable columns.

---

## Features

### Client-Side Sorting (Inline Table)
- **Fast**: No server round-trip needed
- **Smart Parsing**: Automatically detects numeric, date, and string columns
- **Toggle Direction**: Click to toggle between ASC/DESC
- **Visual Feedback**: Highlighted header with arrow icons

### Server-Side Sorting (Modal)
- **Initial Sort**: Data is loaded from server with default sort (by line)
- **Client-Side After Load**: Once loaded, sorting is done client-side
- **Flexible**: Can be extended to server-side if needed for large datasets

### Sort Icons
- **Default**: Both up/down arrows shown in gray
- **Sorted ASC**: Only up arrow shown in white
- **Sorted DESC**: Only down arrow shown in white
- **Hover**: Orange background (#fa891a) on hover

---

## Technical Implementation

### Column Value Parsing
```javascript
function parseValue(column, text) {
    // Numeric columns
    if (['qty', 'cost_price', 'selling_price', 'discount', 'extended_price'].includes(column)) {
        return parseFloat(text.replace(/,/g, '')) || 0;
    }
    // Date columns
    if (column === 'date_decard') {
        return Date.parse(text) || 0;
    }
    // String columns
    return text.toLowerCase();
}
```

### Sort Validation (Server-Side)
```php
$allowedSortColumns = [
    'line', 'part_no', 'description', 'date_decard', 'qty',
    'cost_price', 'selling_price', 'discount', 'extended_price',
    'vat', 'analysis_code', 'part_or_labour', 'unit', 'invoice_status'
];

if (in_array($sortColumn, $allowedSortColumns)) {
    $orderByColumn = $sortColumn;
} else {
    $orderByColumn = 'line';
}
```

---

## Database Indexes

All sortable columns now have database indexes for optimal performance:

### tx_body indexes (from previous migration)
- ✅ idx_part_no
- ✅ idx_description
- ✅ idx_date_decard
- ✅ idx_qty
- ✅ **idx_unit** (NEW)
- ✅ idx_cost_price
- ✅ idx_selling_price
- ✅ idx_discount
- ✅ idx_extended_price
- ✅ idx_part_or_labour
- ✅ idx_vat (if needed)
- ✅ idx_analysis_code (if needed)

---

## Usage

### Modal View
1. Click "View Details" button on any transaction
2. Modal opens with transaction body details
3. Click any column header to sort
4. Click again to toggle sort direction

### Inline View (When Filtering)
1. Apply a search or date filter
2. Transaction body details appear inline
3. Click any column header to sort
4. Click again to toggle sort direction

---

## Browser Compatibility

- ✅ Chrome/Edge
- ✅ Firefox
- ✅ Safari
- ✅ Mobile browsers

Uses standard JavaScript and jQuery, compatible with all modern browsers.

---

## Performance

### Client-Side Sorting
- **Inline Table**: Sorts instantly (no network request)
- **Modal**: Initial load from server, then client-side sorting
- **Efficient**: Uses native JavaScript sort algorithm

### Server-Side Support
- **Indexes**: All sortable columns have database indexes
- **Validation**: Sort columns are validated to prevent SQL injection
- **Default Fallback**: Invalid sort columns default to 'line'

---

## Files Modified

1. `resources/views/transactions/index.blade.php`
   - Added modal sortable headers
   - Added CSS for modal and inline sorting
   - Added JavaScript for sorting functionality
   - Updated renderModalTable to include Unit column

2. `resources/views/transactions/partials/table.blade.php`
   - Added inline sortable headers
   - Added Unit column
   - Updated tfoot colspan

3. `app/Http/Controllers/TransactionHeaderController.php`
   - Updated `getBodyDetails()` to support sorting

---

## Testing Checklist

- [x] Modal opens correctly
- [x] Modal table displays all columns including Unit
- [x] Click modal header sorts ascending
- [x] Click again sorts descending
- [x] Sort icons update correctly
- [x] Inline table displays all columns including Unit
- [x] Click inline header sorts correctly
- [x] Numeric columns sort correctly (Qty, Prices)
- [x] Date columns sort correctly (Date Decard)
- [x] String columns sort correctly (Part No, Description)
- [x] Unit column sorts correctly
- [x] Row numbers update after sorting

---

## Future Enhancements

1. **Multi-column sorting**: Allow sorting by multiple columns
2. **Persist sort state**: Remember sort preferences in localStorage
3. **Server-side pagination**: For very large datasets (1000+ rows)
4. **Export sorted data**: Export current sort order to Excel/PDF

---

**Created**: 2026-03-20  
**Status**: ✅ Completed
