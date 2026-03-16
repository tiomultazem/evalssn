# EvalSSN Helper

Basic web app berbasis CodeIgniter 4 untuk menjalankan kumpulan query SQL Server Susenas yang disimpan dalam file Excel. Aplikasi ini digunakan dalam jaringan intranet agar pegawai dapat menjalankan query melalui browser tanpa SQL client seperti HeidiSQL dan tidak bergantung kepada server provinsi.

## Tujuan Utama
- Menyediakan platform untuk menjalankan query anomali Susenas tanpa bergantung pada server provinsi.
- Tidak memerlukan jaringan lelet terenkripsi Tailscale atau Ngrok. Query dijalankan dan ditampilkan secara real-time.

## Teknologi
- PHP 8.5 (pengetahuan web programming tingkat menengah dibutuhkan untuk instalasi)
- CodeIgniter 4
- Microsoft SQL Server (default terinstal di server lokal Susenas)
- PhpSpreadsheet
- Composer

## Struktur Query Excel

Format:
Kolom A : Judul query  
Kolom B : SQL query  

Baris pertama adalah header dengan isian terserah.

## Cara Kerja

1. Sistem membaca Excel
2. Kolom A menjadi dropdown judul query
3. User memilih query
4. Sistem POST ke /runquery
5. Query dijalankan ke SQL Server
6. Hasil ditampilkan dalam tabel ke web

Jika query kosong maka muncul:

Tidak ada data

## Instalasi

### Install PHP

Buka Powershell as administrator dan jalankan
```
powershell -c "& ([ScriptBlock]::Create((irm 'https://www.php.net/include/download-instructions/windows.ps1'))) -Version 8.5"
x64
False
Asia/Makassar
2 (All users)
```
Saat dokumentasi ini dibuat, yang terinstal adalah PHP 8.5.4. Silakan disesuaikan karena kedepannya ada peletakan file berdasar direktori versi.

### Copy file konfigurasi PHP

Copy php.ini di folder tools ke ```C:\Program Files\php\current\``` dan replace php.ini yang sudah ada.
Perhatikan bahwa php.ini dari folder tools dikhususkan hanya untuk proyek ini, sehingga anda bisa mempertimbangkan untuk merge isinya,
alih-alih menghapus yang lama.

### Copy PHP SQLServer

Copy ```php_sqlsrv_85_nts_x64``` dan ```php_pdo_sqlsrv_85_nts_x64``` di folder tools ke ```C:\Program Files\PHP\8.5.4\nts\x64\ext```.
Ini adalah ekstensi driver SQL Server untuk PHP. Sesuaikan versinya (asumsi anda penggunakan arsitektur x64 dan PHP 8.5.4)

### Install Composer (opsional, hanya untuk menginstal Codeigniter 4)

Download di https://getcomposer.org/Composer-Setup.exe

### Instalasi CodeIgniter

Instal Codeigniter 4 dengan nama proyek evalssn-helper. Ini masih berupa proyek template CI4.
```
composer create-project codeigniter4/appstarter evalssn-helper
cd evalssn-helper
```
Clone proyek EvalSSN Helper di Github ke proyek CI4 yang baru diinstal.
```
git init
git remote remove origin 2>nul
git remote add origin https://github.com/tiomultazem/evalssn-helper.git
git fetch origin
git checkout origin/main -- .
```

### Install ODBC SQL Server Driver

Download dan Install Microsoft ODBC Driver for SQL Server (x64). Link berikut untuk arsitektur x64

https://go.microsoft.com/fwlink/?linkid=2345415 

## Menjalankan Server

ketikkan di dalam folder proyek
```
serve
```
Dia akan menjalankan file serve.bat yang berisi
```
php spark serve --host 0.0.0.0 --port 8080
```
Akses di

http://localhost:8080

Selanjutnya untuk intranet:
Cari IP Adress PC Server (misal 192.168.x.x) dan akses dari PC lain di

http://192.168.x.x:8080

## Konfigurasi Pertama Kali Akses

1. Buka /settings atau klik tombol settings di pojok kanan atas
2. Isi kolom Hostname/Server dengan "IP Address\Instance SQL Server". Misal "127.0.0.1\new_sqlipds"
3. Isi username dan password yang didapat dari pusat/provinsi
4. Klik "Muat Database". Tunggu hingga dropdown database menampilkan isi yang dapat dipilih.
5. 

## Routing

/ → halaman utama  
/runquery → menjalankan query  

Route tidak valid akan kembali ke halaman utama.

## Catatan

Aplikasi ini tidak memiliki autentikasi. Disarankan hanya digunakan dalam jaringan intranet.

## License (MIT)

Copyright (c) 2026  
Gilang Wahyu Prasetyo, S.Tr.Stat  
BPS Kabupaten Tabalong

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files to deal in the Software without restriction including without limitation the rights to use copy modify merge publish distribute sublicense and or sell copies of the Software.

THE SOFTWARE IS PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND.