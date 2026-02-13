# Import Body - Field Mapping

## Excel Column Headers to Database Fields

1. Part -> part_no
2. Desc -> description
3. Qty -> qty
4. SellPrice -> selling_price
5. Disc% -> discount
6. ExtPrice -> extended_price
7. MP -> menu_price
8. VAT -> vat
9. MV -> menu_vat
10. CostPr -> cost_price
11. AnalCode -> analysis_code
12. InvStat -> invoice_status
13. UOI -> unit
14. MpU -> mins_per_unit
15. WIPNo -> wip_no
16. Line -> line
17. Acct -> account_code
18. Dept -> department
19. InvNo -> invoice_no
20. FC -> franchise_code
21. SaleType -> sales_type
22. Wcode -> warranty_code
23. MenuFlag -> menu_flag
24. Contrib -> contribution
25. DateDecard -> date_decard
26. HMagic1 -> magic_1
27. HMagic2 -> magic_2
28. PO -> po_no
29. GRN -> grn_no
30. Menu -> menu_code
31. LR -> labour_rates
32. Supp -> supplier_code
33. MenuLink -> menu_link
34. CurPrice -> currency_price
35. Parts/Labour -> part_or_labour
36. COper -> operator_code
37. COperName -> operator_name
38. PosCo -> pos_code

## Field Validations

### Required Fields
- **Part** (part_no): Required, max 100 characters
- **InvNo** (invoice_no): Required, numeric
- **WIPNo** (wip_no): Required, integer
- **Line** (line): Required, numeric
- **AnalCode** (analysis_code): Required, 1 character
- **InvStat** (invoice_status): Required, must be 'X' (Closed) or 'C' (Completed)
- **SaleType** (sales_type): Required, 1 character
- **Parts/Labour** (part_or_labour): Required, must be 'P' (Part) or 'L' (Labour)
- **HMagic2** (magic_2): Required, numeric
- **PosCo** (pos_code): Required, alphanumeric, max 20 characters, must match user's assigned brands

### Optional Fields
- **Desc** (description): Optional, max 250 characters
- **Qty** (qty): Optional, numeric (decimal 2 places)
- **SellPrice** (selling_price): Optional, numeric (decimal 2 places)
- **Disc%** (discount): Optional, numeric (decimal 2 places)
- **ExtPrice** (extended_price): Optional, numeric (decimal 2 places)
- **MP** (menu_price): Optional, numeric (decimal 2 places)
- **VAT** (vat): Optional, 1 character
- **MV** (menu_vat): Optional, 1 character
- **CostPr** (cost_price): Optional, numeric (decimal 2 places)
- **UOI** (unit): Optional, max 10 characters
- **MpU** (mins_per_unit): Optional, numeric (decimal 2 places)
- **Acct** (account_code): Optional, max 20 characters
- **Dept** (department): Optional, max 50 characters
- **FC** (franchise_code): Optional, max 3 characters
- **Wcode** (warranty_code): Optional, max 3 characters
- **MenuFlag** (menu_flag): Optional, 1 character
- **Contrib** (contribution): Optional, numeric (decimal 2 places)
- **DateDecard** (date_decard): Optional, date format (YYYY-MM-DD)
- **HMagic1** (magic_1): Optional, numeric
- **PO** (po_no): Optional, numeric
- **GRN** (grn_no): Optional, numeric
- **Menu** (menu_code): Optional, numeric
- **LR** (labour_rates): Optional, 1 character
- **Supp** (supplier_code): Optional, max 20 characters
- **MenuLink** (menu_link): Optional, numeric
- **CurPrice** (currency_price): Optional, numeric (decimal 2 places)
- **COper** (operator_code): Optional, alphanumeric, max 20 characters
- **COperName** (operator_name): Optional, alphanumeric, max 150 characters

## Import Logic

The import uses **updateOrCreate** based on the following unique key combination:
- Part (part_no)
- InvNo (invoice_no)
- WIPNo (wip_no)
- Line (line)
- PosCo (pos_code)
- HMagic2 (magic_2)

If a record with this combination exists, it will be **updated**.
If not, a new record will be **created**.

## Notes

1. **PosCo (POS Code)** must match one of the brand codes assigned to your user account
2. **InvStat** values: X = Closed, C = Completed
3. **Parts/Labour** values: P = Part, L = Labour
4. Date format should be YYYY-MM-DD or Excel date format
5. All numeric fields accept decimal values with 2 decimal places
6. **COper** and **COperName** are optional fields for operator information
