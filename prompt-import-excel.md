1. Buatkan fungsi import dari excel transaction body, pakai createOrReplace / updateOrCreate

dan tambahkan button Import di Datatable Transaction Body dengan permission "transaction-body.import"



2. Buat input field nya pakai drag n drop, dan validasi mime type csv, xls, xlsx

kalau ada yang gagal simpan ke dalam Array, lalu tampilkan ke sisi user berupa list / daftar pakai loop dan gagal nya di line berapa, dan error nya apa



untuk contoh nya bisa di lihat dari halaman import transaction header

untuk template import excel nya : 

1. Part

2. Desc

3. Qty 

4. SellPrice 

5. Disc% 

6. ExtPrice 

7. MP 

8. VAT 

9. MV 

10. CostPr

11. AnalCode 

12. InvStat

13. UOI 

14. MpU

15. WIPNo

16. Line 

17. Acct 

18. Dept

19. InvNo

20. FC

21. SaleType

22. Wcode 

23. MenuFlag 

24. Contrib 

25. DateDecard

26. HMagic1

27. HMagic2

28. PO

29. GRN

30. Menu

31. LR

32. Supp

33. MenuLink

34. CurPrice

35. Parts/Labour



diatas adalah nama body di excel nya, nah nanti dari body di atas insert ke table : tx_body di field2 berikut:

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

22. Wcode -> sales_type

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