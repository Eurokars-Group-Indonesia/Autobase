Buatkan fungsi import dari excel transaction header, pakai createOrReplace

untuk template import excel nya : 

1. WIPNO
2. Account	
3. CustName	
4. Add1	
5. Add2	
6. Add3	
7. Add4	
8. Add5	
9. Dept	
10. InvNo
11. InvDate	
12. MAGICH	
13. DocType	
14. ExchangeRate	
15. RegNo	
16. Chassis	
17. Mileage	
18. CurrCode	
19. GrossValue	
20. NetValue	
21. CustDisc	
22. SvcCode	
23. RegDate	
24. Description	
25. EngineNo	
26. AccCo

diatas adalah nama header di excel nya, nah nanti dari header di atas insert ke table : tx_header di field2 berikut:
1. WIPNO -> wip_no
2. Account -> account_code
3. CustName	-> customer_name
4. Add1	-> address_1
5. Add2	-> address_2
6. Add3	-> address_3
7. Add4	-> address_4
8. Add5	-> address_5
9. Dept	-> department
10. InvNo -> invoice_no
11. InvDate	-> invoice_date
12. MAGICH	-> magic_id
13. DocType	-> document_type
14. ExchangeRate -> exchange_rate
15. RegNo -> registration_no
16. Chassis	-> chassis
17. Mileage	-> mileage
18. CurrCode -> currency_code
19. GrossValue -> gross_value	
20. NetValue -> net_value
21. CustDisc -> customer_discount
22. SvcCode	-> service_code
23. RegDate	-> registration_date
24. Description	-> description
25. EngineNo -> engineer_no
26. AccCo -> account_company