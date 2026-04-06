# Search Logic Explanation

## Smart Search Detection

Sistem search sekarang menggunakan deteksi cerdas untuk menentukan jenis pencarian berdasarkan input user.

### 1. Date Search
**Pattern:** `YYYY-MM-DD` (e.g., "2026-01-22")
**Fields:** `invoice_date`
**Method:** Exact match

### 2. Phone Number Search
**Criteria:**
- Contains phone-specific characters: `+`, `-`, spaces, `(`, `)`
- OR pure digits with 8+ characters (e.g., "98209850")

**Examples:**
- "+62 812 3456 7890" → Phone search
- "0812-3456-7890" → Phone search
- "98209850" (8 digits) → Phone search
- "081234567890" (12 digits) → Phone search

**Fields:** `phone_number_1`, `phone_number_2`, `phone_number_3`, `phone_number_4`
**Method:** FULLTEXT ngram index
**Performance:** Fast, indexed

### 3. Pure Digits Search (Short Numbers)
**Criteria:**
- Pure digits (no special characters)
- Less than 8 digits
- Could be invoice_no, wip_no, or partial phone number

**Examples:**
- "3200707" (7 digits) → Invoice/WIP/Phone search
- "22657" (5 digits) → Invoice/WIP/Phone search
- "1234567" (7 digits) → Invoice/WIP/Phone search

**Fields:** 
- `invoice_no` (prefix LIKE)
- `wip_no` (prefix LIKE)
- `chassis` (prefix LIKE)
- `phone_number_1-4` (FULLTEXT ngram)

**Method:** Hybrid - LIKE for invoice/wip + FULLTEXT for phone
**Performance:** Good, uses B-TREE index for LIKE prefix + FULLTEXT for phone

### 4. Text Search
**Criteria:**
- Contains letters or mixed alphanumeric
- Not matching date or phone patterns

**Examples:**
- "dewi purnamasari" → Customer name search
- "ms dhivi" → Customer name search
- "ABC123" → Registration/chassis search

**Fields:**
- `customer_name` (FULLTEXT ngram with AND logic)
- `registration_no` (FULLTEXT ngram with AND logic)
- `chassis` (prefix LIKE)
- `invoice_no` (prefix LIKE)
- `wip_no` (prefix LIKE)

**Method:** FULLTEXT ngram for names + LIKE prefix for codes
**Performance:** Fast, indexed

## Search Flow Diagram

```
User Input
    |
    v
Is Date? (YYYY-MM-DD)
    |-- YES --> Search invoice_date (exact match)
    |
    v NO
Has phone chars? (+, -, space, ()) OR 8+ digits?
    |-- YES --> Search phone_number_1-4 (FULLTEXT ngram)
    |
    v NO
Is pure digits? (1-7 digits)
    |-- YES --> Search invoice_no, wip_no, chassis (LIKE prefix)
    |           + phone_number_1-4 (FULLTEXT ngram)
    |
    v NO
Text search
    --> Search customer_name, registration_no (FULLTEXT ngram + AND logic)
        + chassis, invoice_no, wip_no (LIKE prefix)
```

## Performance Characteristics

| Search Type | Index Used | Performance | Notes |
|-------------|-----------|-------------|-------|
| Date | B-TREE | Very Fast | Exact match |
| Phone (11+ digits) | FULLTEXT ngram | Fast | Indexed |
| Pure digits (1-10) | B-TREE + FULLTEXT | Good | Hybrid approach |
| Text | FULLTEXT ngram | Fast | AND logic for accuracy |

## Examples with Expected Results

### Example 1: Invoice Number
**Input:** "3200707"
**Detection:** Pure digits (7 chars)
**Search in:** invoice_no, wip_no, chassis, phone_numbers
**Result:** Finds invoice "3200707" + any phone containing "3200707"

### Example 2: WIP Number
**Input:** "22657"
**Detection:** Pure digits (5 chars)
**Search in:** invoice_no, wip_no, chassis, phone_numbers
**Result:** Finds WIP "22657" + any phone containing "22657"

### Example 3: Phone Number (8 digits)
**Input:** "98209850"
**Detection:** Pure digits (8 chars) = Phone
**Search in:** phone_numbers only
**Result:** Finds phone "98209850"

### Example 4: Phone Number (Long)
**Input:** "081234567890"
**Detection:** Pure digits (12 chars) = Phone
**Search in:** phone_numbers only
**Result:** Finds phone "081234567890"

### Example 5: Phone Number (Formatted)
**Input:** "+62 812 3456"
**Detection:** Has phone chars (+, space)
**Search in:** phone_numbers only
**Result:** Finds phones matching the pattern

### Example 6: Customer Name
**Input:** "ms dhivi ramadhani"
**Detection:** Text with spaces
**Search in:** customer_name, registration_no, chassis, invoice_no, wip_no
**Result:** Finds customers with ALL words (ms AND dhivi AND ramadhani)

### Example 7: Registration Number
**Input:** "ABC123"
**Detection:** Alphanumeric
**Search in:** customer_name, registration_no, chassis, invoice_no, wip_no
**Result:** Finds registration/chassis starting with "ABC123"

## Why This Approach?

### Problem with Old Approach
- All pure digits were treated as phone numbers
- Invoice "3200707" couldn't be found when searching "3200707"
- WIP "22657" couldn't be found when searching "22657"

### Solution
1. **Smart Detection:** Distinguish between short numbers (invoice/wip) and long numbers (phone)
2. **Hybrid Search:** For short numbers, search in BOTH invoice/wip AND phone
3. **Performance:** Still use indexes (B-TREE for LIKE prefix, FULLTEXT for phone)

### Trade-offs
- **Pros:** More accurate results, finds invoice/wip numbers correctly
- **Cons:** Slightly more complex logic, but performance is still good
- **Result:** Better user experience with minimal performance impact

## Configuration

### Phone Number Threshold
Currently set to 8 digits. Can be adjusted based on your data:

```php
$isLongNumber = preg_match('/^\d{8,}$/', $search); // 8+ digits
```

Adjust the `{8,}` to change the threshold:
- `{7,}` = 7+ digits treated as phone
- `{9,}` = 9+ digits treated as phone
- `{10,}` = 10+ digits treated as phone

### Recommendation
- Indonesia phone numbers: 8-13 digits (including area code/country code)
- Invoice/WIP numbers: Usually 5-7 digits
- **Current setting (8+)** is optimal for Indonesian context
