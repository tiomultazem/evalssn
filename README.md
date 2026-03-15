# EvalSSN Helper

Aplikasi web sederhana berbasis CodeIgniter 4 untuk menjalankan kumpulan query SQL Server Susenas yang disimpan dalam file Excel. Aplikasi ini digunakan dalam jaringan intranet agar pegawai dapat menjalankan query melalui browser tanpa SQL client seperti HeidiSQL.

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

Lokasi file:
C:\Users\ACER\Downloads\listquery.xlsx

Format:
Kolom A : Judul query  
Kolom B : SQL query  

Baris pertama adalah header dengan isian terserah.

## Cara Kerja

1. Sistem membaca Excel menggunakan PhpSpreadsheet
2. Kolom A menjadi dropdown
3. User memilih query
4. Sistem POST ke /runquery
5. Query dijalankan ke SQL Server
6. Hasil ditampilkan dalam tabel

Jika query kosong maka muncul:

Tidak ada data

## Instalasi

### Install PHP

Gunakan PHP 8.5 dan tambahkan ke PATH.

### Install Composer

Cek dengan:

composer -V

### Install CodeIgniter

composer create-project codeigniter4/appstarter queryssn26

Masuk folder project:

cd queryssn26

### Install PhpSpreadsheet

composer require phpoffice/phpspreadsheet

### Install ODBC SQL Server Driver

Install Microsoft ODBC Driver for SQL Server (x64)

### Install PHP SQL Server Driver

Tambahkan:

php_sqlsrv.dll  
php_pdo_sqlsrv.dll  

## PHP Extension

Edit php.ini lalu enable:

extension=gd
extension=mbstring
extension=zip
extension=intl
extension=openssl
extension=pdo_sqlsrv
extension=sqlsrv

## Konfigurasi Database

File:

app/Config/Database.php

Contoh:

hostname : 192.168.x.x  
username : username  
password : password  
database : Susenas2026  
DBDriver : SQLSRV  
port : 1433  

## Menjalankan Server

Masuk folder project:

cd C:\Users\ACER\queryssn26

Jalankan server:

php spark serve --host 0.0.0.0 --port 8080

Akses:

http://localhost:8080

Untuk intranet:

http://IP-PC:8080

## Struktur Proyek

app/
Controllers/Home.php  
Views/welcome_message.php  
Config/Routes.php  

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