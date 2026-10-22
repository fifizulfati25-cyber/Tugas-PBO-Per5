# Mini Application OOP HIMATIF

**Nama:** Fifi Nabilah Zulfa  
**Mata kuliah:** Pemrograman Berorientasi Objek (TI033015)  
**Tugas:** Mini Application OOP — Pertemuan 5

## 1. Latar Belakang

Informasi HIMATIF seperti profil, kegiatan, galeri, dan kontak perlu tersedia di satu tempat. Absensi rapat juga akan lebih mudah direkap jika dicatat secara digital. Program ini menampilkan contoh informasi organisasi dan simulasi absensi anggota.

## 2. Class yang Digunakan

Semua class ada di satu file, `index.php`:

- `Pengguna` adalah abstract class untuk data dasar pengguna.
- `Anggota` menyimpan NIM, nama, dan status aktif. Anggota bisa login dan melihat riwayat kehadiran.
- `PengurusHimatif` mewarisi `Pengguna` dan dapat membuka atau menutup rapat.
- `Rapat` mengatur sesi absensi dan menyimpan catatan kehadiran.
- `Absensi` menyimpan data kehadiran anggota pada rapat.
- `Kegiatan` menyimpan informasi kegiatan dan galeri `Foto`.
- `KontakPerson` menyimpan kontak organisasi.
- `BisaAbsen` adalah interface untuk pengguna yang dapat mengisi absensi.

## 3. Relasi dan Perilaku

`Anggota` dan `PengurusHimatif` mewarisi `Pengguna`. Keduanya meng-override `tampilkanProfil()` dan `aksesUtama()` sesuai peran. Daftar pengguna memanggil method yang sama pada tipe objek berbeda, sehingga menunjukkan polymorphism.

`Anggota` menerapkan interface `BisaAbsen`. Anggota harus aktif dan login sebelum mengisi absensi. `Rapat` hanya mencatat kehadiran saat sesi dibuka dan menolak absensi kedua dari anggota yang sama. Catatan `Absensi` dibuat oleh rapat, lalu dapat dilihat kembali melalui riwayat anggota. `Kegiatan` menyimpan beberapa objek `Foto`.

## 4. Data Contoh

Program memakai data contoh untuk kegiatan dan kontak. Ganti keterangan atau nama file foto dengan data dan dokumentasi resmi HIMATIF sebelum digunakan sebagai informasi organisasi.

## 5. Cara Menjalankan

Pastikan XAMPP sudah terpasang. Buka PowerShell, lalu pindah ke folder project:

```powershell
Set-Location "C:\Users\Vivi\Downloads\Semester 3\himatif-mini-oop\himatif-mini-oop"
```

Untuk melihat hasil sebagai output terminal, jalankan:

```powershell
& "C:\xampp\php\php.exe" index.php
```

Untuk melihatnya di browser, jalankan server PHP:

```powershell
& "C:\xampp\php\php.exe" -S localhost:8000
```

Biarkan terminal tetap terbuka, lalu buka `http://localhost:8000`. Tekan `Ctrl+C` untuk menghentikan server.

Screenshot yang ada mungkin masih menampilkan versi sebelumnya. Ambil screenshot baru setelah menjalankan program.
