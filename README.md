# EvalSSN Helper

**EvalSSN Helper** adalah aplikasi web sederhana berbasis **CodeIgniter 4** untuk menjalankan kumpulan query **SQL Server Susenas** yang disimpan dalam file **Excel**.

Aplikasi ini dirancang untuk digunakan dalam **jaringan intranet kantor**, sehingga pegawai dapat menjalankan query melalui browser tanpa perlu menggunakan SQL client seperti **HeidiSQL**, serta tanpa bergantung pada server provinsi.

---

## Tujuan

* Menyediakan platform untuk menjalankan query anomali **Susenas** secara mandiri di tingkat kabupaten
* Menghilangkan ketergantungan pada koneksi ke server provinsi
* Menghindari penggunaan jaringan terenkripsi yang lambat seperti **Tailscale** atau **Ngrok**
* Memungkinkan eksekusi query dan penampilan hasil secara **real-time melalui browser**

---

## Teknologi yang Digunakan

* **PHP 8.5**
* **CodeIgniter 4**
* **Microsoft SQL Server**
* **PhpSpreadsheet**
* **Composer**

> - Instalasi memerlukan pengetahuan **web programming tingkat menengah**.
> - Sebaiknya diinstal di PC server lokal Susenas.

---

## Struktur File Query Excel

Aplikasi membaca query dari file Excel dengan format berikut.

| Kolom | Isi         |
| ----- | ----------- |
| A     | Judul Query |
| B     | SQL Query   |

Ketentuan:

* Baris pertama dianggap sebagai **header**
* Isi header bebas dan tidak mempengaruhi sistem

---

## Cara Kerja Aplikasi

1. Sistem membaca file Excel yang berisi kumpulan query
2. Isi **kolom A** ditampilkan sebagai dropdown pilihan query
3. User memilih query yang ingin dijalankan
4. Sistem mengirim request **POST ke `/runquery`**
5. Query dijalankan ke **SQL Server**
6. Hasil query ditampilkan dalam bentuk **tabel di halaman web**

Jika query tidak menghasilkan data maka akan muncul pesan:

```
Tidak ada data
```

---

## Instalasi

### 1. Install PHP

Buka **PowerShell sebagai Administrator**, kemudian jalankan:

```powershell
powershell -c "& ([ScriptBlock]::Create((irm 'https://www.php.net/include/download-instructions/windows.ps1'))) -Version 8.5"
```

Saat instalasi berlangsung pilih:

```
x64
False
Asia/Makassar
2 (All users)
```

Pada saat dokumentasi ini dibuat, versi yang terinstal adalah **PHP 8.5.4**.
Jika versi berbeda, sesuaikan direktori pada langkah berikutnya.

---

### 2. Copy File Konfigurasi PHP

Copy file `php.ini` dari folder **tools** ke:

```
C:\Program Files\php\current\
```

Kemudian **replace** file `php.ini` yang sudah ada.

> File `php.ini` pada folder tools dikonfigurasi khusus untuk proyek ini.
> Anda dapat melakukan **merge konfigurasi** apabila diperlukan.

---

### 3. Install Driver SQL Server untuk PHP

Copy file berikut dari folder **tools**:

```
php_sqlsrv_85_nts_x64
php_pdo_sqlsrv_85_nts_x64
```

ke direktori:

```
C:\Program Files\PHP\8.5.4\nts\x64\ext
```

File tersebut merupakan **driver SQL Server untuk PHP**.

Pastikan menyesuaikan:

* versi PHP
* arsitektur sistem (x64)

---

### 4. Install Composer

Download installer:

```
https://getcomposer.org/Composer-Setup.exe
```

---

### 5. Instalasi CodeIgniter 4

Install proyek template **CodeIgniter 4**:

```bash
composer create-project codeigniter4/appstarter evalssn-helper
cd evalssn-helper
```

Kemudian clone repository **EvalSSN Helper**:

```bash
git init
git remote remove origin 2>nul
git remote add origin https://github.com/tiomultazem/evalssn-helper.git
git fetch origin
git checkout origin/main -- .
```

Rename file:

```
env -> .env
```

---

### 6. Install ODBC Driver SQL Server

Download dan install:

**Microsoft ODBC Driver for SQL Server (x64)**

```
https://go.microsoft.com/fwlink/?linkid=2345415
```

---

### 7. Install PhpSpreadsheet

Library ini digunakan untuk membaca file Excel.

Jalankan:

```bash
composer require phpoffice/phpspreadsheet
```

---

## Menjalankan Server

Di dalam folder proyek jalankan:

```bash
serve
```

Script tersebut akan menjalankan:

```bash
php spark serve --host 0.0.0.0 --port 8080
```

Akses aplikasi melalui browser:

```
http://localhost:8080
```

---

## Akses Melalui Jaringan Intranet

1. Cari **IP Address** komputer server (misal `192.168.x.x`)
2. Dari komputer lain dalam jaringan, buka:

```
http://192.168.x.x:8080
```

---

## Konfigurasi Awal

1. Buka halaman:

```
/settings
```

atau klik tombol **Settings** di pojok kanan atas.

2. Isi **Hostname / Server** dengan format:

```
IP_ADDRESS\SQL_INSTANCE
```

Contoh:

```
127.0.0.1\new_sqlipds
```

3. Masukkan **username dan password database**
4. Klik **Muat Database**
5. Tunggu hingga dropdown database terisi, lalu pilih database yang dibutuhkan.
6. Upload file Excel query
7. Klik simpan. Bila semua terisi dengan benar, anda akan diarahkan otomatis kembali ke Home.

Untuk pengujian, gunakan file:

```
contoh query.xlsx
```

yang tersedia pada folder **tools**.

---

## Routing

| Route       | Fungsi            |
| ----------- | ----------------- |
| `/`         | Halaman utama     |
| `/settings` | Konfigurasi database |

Route yang tidak valid akan diarahkan kembali ke halaman utama.

---

## Catatan Keamanan

Aplikasi ini **tidak memiliki sistem autentikasi**.

Disarankan hanya digunakan pada **jaringan intranet internal kantor**.

Dipersilakan bertanya via wa/ig seputar instalasi.

Jangan lupa surat resmi, bukti penggunaan, dll tetek bengek ZI kalo mau replikasi ya.

---

## License (MIT)

```
Copyright (c) 2026
Gilang Wahyu Prasetyo, S.Tr.Stat
BPS Kabupaten Tabalong

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files to deal in the Software
without restriction including without limitation the rights to use copy
modify merge publish distribute sublicense and or sell copies of the Software.

THE SOFTWARE IS PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND.
```