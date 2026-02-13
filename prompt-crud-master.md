1. Buatkan CRUD (Model, Migration, Controller, Request, Middleware (jika ada), View) untuk module Dealer dengan schema di bawah ini : 



Table ms_dealers {

  dealer_id "bigint unsigned" [not null, pk, increment]

  dealer_code varchar(50) [not null, unique]

  dealer_name varchar(150) [not null]

  city varchar(100) [null]

  created_by "bigint unsigned" [not null]

  created_date datetime [null, default: "CURRENT_TIMESTAMP"]

  updated_by "bigint unsigned" [null]

  updated_date datetime [null]

  unique_id char(36) [not null, unique, note: "UUIDV4, di gunakan untuk Get Data dari URL"]

  is_active enum_is_active [null, default: '1']

  Indexes {

    created_by

    updated_by

    is_active

  }

}

2. jangan lupa tambahkan role menu, role permission seperti modul lainnya

3. untuk di form add dan edit di masing2 field tambahan html maxlength (sesuai jumlah data pada schema di masing2 field), tambahkan juga validasi di Request

4. Untuk input field search dan button search letakkan di kanan dari table

5. tidak perlu ada pilihan status active / inactive karena sudah di kasih default

6. Jika Modul ini ada Relasi ke Model / Table lain gunakan Eager Loading