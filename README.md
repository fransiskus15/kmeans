<div align="center">
                    <img src="public/img/logo.jpg" alt="pos omah">
</div>

## SISTEM INFORMASI PEMINJAMAN ASET DENGAN ANALISIS PERILAKU PEMINJAM MENGGUNAKAN _K-MEANS CLUSTERING_    

Sistem ini adalah aplikasi web yang dibuat untuk Batam Pos supaya proses peminjaman aset - seperti kamera, laptop, dan kendaraan - tidak lagi dicatat manual di Excel. Yang membuat sistem ini berbeda dari sistem peminjaman biasa adalah adanya fitur analisis perilaku. Setiap kali HR mau menyetujui permintaan peminjaman, sistem otomatis menampilkan profil si peminjam: apakah dia termasuk tipe yang disiplin, kasual, atau berisiko - lengkap dengan data riwayat peminjaman historisnya. Informasi ini dihasilkan oleh algoritma K-Means Clustering yang berjalan di balik layar secara otomatis. Dengan begitu, keputusan setuju atau tolak tidak lagi subjektif, tapi berbasis data nyata.

## Fitur Utama:

Manajemen pengguna dan akses berbasis peran
Analisis Perilaku Peminjam
Manajemen inventaris
Laporan Peminjaman

## INSTALASI

1. Clone repositori ini:
   git clone https://github.com/fransiskus15/kmeans.git

2. Jalankan perintah berikut:

    cd kmeans

    composer install

    cp .env.example .env

    Lalu buka file .env dan atur koneksi database:

    DB_DATABASE=pos

    DB_USERNAME=root

    DB_PASSWORD=

    php artisan key:generate

    php artisan migrate


## Kotributor

    FRANSISKUS ZEDEKIA SITUMORANG -> Pengembang 
