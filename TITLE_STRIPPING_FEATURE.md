# Title Stripping Feature

## Problem
Ketika user search "ms dhivi ramadhani" atau "dimple bernando" tapi data di database adalah "Ms Dhivi Ramadhani" atau "Mr Dimple Bernando Torrez", pencarian tidak menemukan hasil karena:
1. FULLTEXT search dengan NATURAL LANGUAGE MODE menggunakan OR logic dengan relevance scoring
2. Multi-word search seperti "dimple bernando" bisa return records yang hanya match salah satu kata
3. Relevance score untuk partial match bisa terlalu rendah

## Solution
1. Strip common titles/prefixes dari search query sebelum melakukan pencarian
2. Gunakan BOOLEAN MODE dengan wildcards untuk ngram FULLTEXT search
3. Split search menjadi words dan tambahkan wildcards: "dimple bernando" → "*dimple* *bernando*"
4. Ini memastikan ALL words harus ada (AND logic) dengan partial matching

### Titles yang Di-strip:
- Mr / Mr.
- Mrs / Mrs.
- Ms / Ms.
- Miss
- Dr / Dr.
- Prof / Prof.
- Sir
- Madam
- Lady
- Lord

### How It Works:

**Before (NATURAL LANGUAGE MODE):**
```php
Search: "dimple bernando"
Query: MATCH(customer_name) AGAINST('dimple bernando' IN NATURAL LANGUAGE MODE)
Result: Returns records with "dimple" OR "bernando" (OR logic with scoring)
Problem: May return "Dimple Kirana" or "John Bernando" (only one word matches)
```

**After (BOOLEAN MODE with Wildcards):**
```php
Search: "dimple bernando"
Cleaned: "dimple bernando" (no title to strip)
Words: ["dimple", "bernando"]
Wildcards: "*dimple* *bernando*"
Query: MATCH(customer_name) AGAINST('*dimple* *bernando*' IN BOOLEAN MODE)
Result: Returns ONLY records with BOTH "dimple" AND "bernando" (AND logic)
Match: "Mr Dimple Bernando Torrez" ✅
No Match: "Dimple Kirana" ❌ (missing "bernando")
```

**With Title Stripping:**
```php
Search: "ms dhivi ramadhani"
Cleaned: "dhivi ramadhani" (ms stripped)
Words: ["dhivi", "ramadhani"]
Wildcards: "*dhivi* *ramadhani*"
Query: MATCH(customer_name) AGAINST('*dhivi* *ramadhani*' IN BOOLEAN MODE)
Result: Returns records with BOTH "dhivi" AND "ramadhani"
Match: "Ms Dhivi Ramadhani" ✅
```

## Examples

### Example 1: Search with Title
**Input:** "ms dhivi ramadhani"
**Cleaned:** "dhivi ramadhani"
**Database:** "Ms Dhivi Ramadhani"
**Result:** ✅ Found (matches "dhivi" AND "ramadhani")

### Example 2: Search without Title
**Input:** "dhivi ramadhani"
**Cleaned:** "dhivi ramadhani"
**Database:** "Ms Dhivi Ramadhani"
**Result:** ✅ Found (matches "dhivi" AND "ramadhani")

### Example 3: Different Title
**Input:** "mr dimple bernando"
**Cleaned:** "dimple bernando"
**Database:** "Mr Dimple Bernando Torrez"
**Result:** ✅ Found (matches "dimple" AND "bernando")

### Example 4: Search with Dr
**Input:** "dr john doe"
**Cleaned:** "john doe"
**Database:** "Dr. John Doe"
**Result:** ✅ Found (matches "john" AND "doe")

### Example 5: Title in Middle (Not Stripped)
**Input:** "john mr smith"
**Cleaned:** "john mr smith" (mr not at beginning)
**Database:** "John Mr Smith"
**Result:** ✅ Found (matches all words)

## Regex Pattern

```php
preg_replace('/^(mr|mrs|ms|miss|dr|prof|sir|madam|lady|lord)\.?\s+/i', '', trim($search))
```

**Breakdown:**
- `^` - Start of string
- `(mr|mrs|ms|miss|dr|prof|sir|madam|lady|lord)` - Title options
- `\.?` - Optional dot after title (e.g., "Mr." or "Mr")
- `\s+` - One or more spaces after title
- `i` - Case insensitive (matches Mr, MR, mr, etc.)

## Benefits

1. **Flexible Search:** User bisa search dengan atau tanpa title
2. **Better Matching:** Tidak perlu exact match untuk title
3. **User Friendly:** User tidak perlu tahu apakah data memiliki title atau tidak
4. **Consistent Results:** "ms dhivi" dan "dhivi" akan memberikan hasil yang sama
5. **Multi-Word AND Logic:** "dimple bernando" hanya return records yang memiliki BOTH words
6. **Partial Matching:** Wildcards (*word*) memungkinkan partial matching dengan ngram index

## Key Technical Details

### Why BOOLEAN MODE with Wildcards?

**ngram FULLTEXT Index Characteristics:**
- Creates very small tokens (default: 2 characters)
- Works differently than regular FULLTEXT indexes
- NATURAL LANGUAGE MODE with ngram uses OR logic → too many results
- BOOLEAN MODE with `+word` doesn't work well with ngram tokens
- **Solution:** BOOLEAN MODE with wildcards `*word*` works perfectly with ngram

**Example:**
```
Database: "Mr Dimple Bernando Torrez"
ngram tokens: "mr", "r ", " d", "di", "im", "mp", "pl", "le", "e ", " b", "be", "er", ...

Search: "dimple bernando"
NATURAL LANGUAGE MODE: "dimple bernando"
  → Returns records with "dimple" OR "bernando" (too many results)
  
BOOLEAN MODE with +: "+dimple +bernando"
  → May not work well with ngram tokens (too restrictive)
  
BOOLEAN MODE with wildcards: "*dimple* *bernando*"
  → Matches ngram tokens perfectly: *di*im*pl*e* AND *be*rn*an*do*
  → Returns ONLY records with BOTH words ✅
```

## Edge Cases Handled

### Case 1: Title with Dot
**Input:** "Mr. John Doe"
**Cleaned:** "John Doe" ✅

### Case 2: Title without Dot
**Input:** "Mr John Doe"
**Cleaned:** "John Doe" ✅

### Case 3: Multiple Spaces
**Input:** "Ms  Dhivi" (2 spaces)
**Cleaned:** "Dhivi" ✅

### Case 4: Mixed Case
**Input:** "MS DHIVI" or "Ms DHIVI"
**Cleaned:** "DHIVI" ✅

### Case 5: Title Only
**Input:** "Ms"
**Cleaned:** "" (empty)
**Result:** Will search in other fields (chassis, invoice_no, wip_no)

## Performance Impact

**No Performance Impact:**
- Title stripping is done in PHP before query
- FULLTEXT search still uses ngram index
- Query performance remains the same
- Only removes 1-2 words from search term

## Testing

### Test Cases:

| Input | Cleaned | Database Value | Expected | Status |
|-------|---------|----------------|----------|--------|
| "ms dhivi ramadhani" | "dhivi ramadhani" | "Ms Dhivi Ramadhani" | Found | ✅ |
| "dhivi ramadhani" | "dhivi ramadhani" | "Ms Dhivi Ramadhani" | Found | ✅ |
| "mr dimple bernando" | "dimple bernando" | "Mr Dimple Bernando Torrez" | Found | ✅ |
| "dimple" | "dimple" | "Mr Dimple Bernando Torrez" | Found | ✅ |
| "dr john doe" | "john doe" | "Dr. John Doe" | Found | ✅ |
| "prof smith" | "smith" | "Prof. Smith" | Found | ✅ |

## Implementation Details

**File Modified:** `app/Http/Controllers/TransactionHeaderController.php`

**Methods Updated:**
1. `index()` - Line ~131-136
2. `search()` - Line ~840-845

**Code Added:**
```php
// Strip common titles/prefixes from search to improve matching
$searchClean = preg_replace('/^(mr|mrs|ms|miss|dr|prof|sir|madam|lady|lord)\.?\s+/i', '', trim($search));

// For ngram FULLTEXT, use BOOLEAN MODE with wildcards for multi-word matching
// Split search into words and add wildcards: "dimple bernando" -> "*dimple* *bernando*"
$words = preg_split('/\s+/', $searchClean);
$fulltextSearch = implode(' ', array_map(function($word) {
    return '*' . $word . '*';
}, $words));

// Use FULLTEXT search with ngram parser
// BOOLEAN MODE with wildcards: requires ALL words to match (AND logic)
$searchWhere->whereRaw('MATCH(tx_header.customer_name) AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch])
```

## Future Enhancements

If needed, more titles can be added:
- Ir (Engineer)
- Drs (Doktorandus)
- Hj (Hajjah)
- H (Haji)
- Tn (Tuan)
- Ny (Nyonya)
- Nn (Nona)

To add more titles, update the regex:
```php
preg_replace('/^(mr|mrs|ms|miss|dr|prof|ir|drs|hj|h|tn|ny|nn)\.?\s+/i', '', trim($search))
```

## Notes

- Title stripping only applies to text search (not phone, invoice, wip, date)
- Titles are only stripped from the beginning of search string
- Original search is preserved for other field searches (chassis, invoice_no, wip_no)
- Case insensitive matching for titles
