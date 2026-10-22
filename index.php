<?php

// Jalankan dari PowerShell: & "C:\xampp\php\php.exe" index.php
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

date_default_timezone_set('Asia/Jakarta');

interface BisaAbsen
{
    public function getId(): string;
    public function getNama(): string;
    public function absen(Rapat $rapat): string;
    public function simpanRiwayatHadir(Absensi $absensi): void;
}

abstract class Pengguna
{
    protected string $id;
    protected string $nama;

    public function __construct(string $id, string $nama)
    {
        $this->id = $id;
        $this->nama = $nama;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function tampilkanProfil(): string
    {
        return "{$this->nama} ({$this->id})";
    }

    abstract public function aksesUtama(): string;
}

class Anggota extends Pengguna implements BisaAbsen
{
    private string $nim;
    private bool $statusAktif;
    private bool $sudahLogin = false;
    /** @var Absensi[] */
    private array $riwayatHadir = [];

    public function __construct(string $nim, string $nama, bool $statusAktif = true)
    {
        parent::__construct($nim, $nama);
        $this->nim = $nim;
        $this->statusAktif = $statusAktif;
    }

    public function getNim(): string
    {
        return $this->nim;
    }

    public function getStatusAktif(): bool
    {
        return $this->statusAktif;
    }

    public function login(): string
    {
        if (!$this->statusAktif) {
            $this->sudahLogin = false;
            return "GAGAL: akun {$this->nama} tidak aktif.";
        }

        $this->sudahLogin = true;
        return "BERHASIL: {$this->nama} login.";
    }

    public function aksesUtama(): string
    {
        return 'Login, melihat jadwal rapat, mengisi absensi, dan melihat riwayat hadir.';
    }

    public function tampilkanProfil(): string
    {
        $status = $this->statusAktif ? 'Aktif' : 'Tidak aktif';
        return "{$this->nama} (NIM {$this->nim}) - Anggota HIMATIF - {$status}";
    }

    public function absen(Rapat $rapat): string
    {
        if (!$this->statusAktif) {
            return "GAGAL: akun {$this->nama} tidak aktif.";
        }
        if (!$this->sudahLogin) {
            return "GAGAL: {$this->nama} harus login sebelum mengisi absensi.";
        }

        return $rapat->catatKehadiran($this);
    }

    public function simpanRiwayatHadir(Absensi $absensi): void
    {
        $this->riwayatHadir[] = $absensi;
    }

    public function lihatRiwayatHadir(): string
    {
        if ($this->riwayatHadir === []) {
            return "{$this->nama} belum memiliki riwayat kehadiran.";
        }

        $baris = [];
        foreach ($this->riwayatHadir as $absensi) {
            $baris[] = "{$absensi->getJudulRapat()} ({$absensi->getTanggalRapat()}) - {$absensi->getWaktuHadir()}";
        }
        return implode("\n  ", $baris);
    }
}

class PengurusHimatif extends Pengguna
{
    private string $jabatan;

    public function __construct(string $id, string $nama, string $jabatan)
    {
        parent::__construct($id, $nama);
        $this->jabatan = $jabatan;
    }

    public function aksesUtama(): string
    {
        return 'Mengelola informasi HIMATIF, rapat, dan rekap absensi.';
    }

    public function tampilkanProfil(): string
    {
        return parent::tampilkanProfil() . " - Pengurus ({$this->jabatan})";
    }

    public function bukaRapat(Rapat $rapat): string
    {
        return $rapat->buka();
    }

    public function tutupRapat(Rapat $rapat): string
    {
        return $rapat->tutup();
    }
}

class Absensi
{
    private string $idAnggota;
    private string $namaAnggota;
    private string $judulRapat;
    private string $tanggalRapat;
    private string $waktuHadir;

    public function __construct(string $idAnggota, string $namaAnggota, string $judulRapat, string $tanggalRapat, string $waktuHadir)
    {
        $this->idAnggota = $idAnggota;
        $this->namaAnggota = $namaAnggota;
        $this->judulRapat = $judulRapat;
        $this->tanggalRapat = $tanggalRapat;
        $this->waktuHadir = $waktuHadir;
    }

    public function getIdAnggota(): string { return $this->idAnggota; }
    public function getNamaAnggota(): string { return $this->namaAnggota; }
    public function getJudulRapat(): string { return $this->judulRapat; }
    public function getTanggalRapat(): string { return $this->tanggalRapat; }
    public function getWaktuHadir(): string { return $this->waktuHadir; }
}

class Rapat
{
    private string $judul;
    private string $tanggal;
    private bool $dibuka = false;
    /** @var array<string, Absensi> */
    private array $daftarAbsensi = [];

    public function __construct(string $judul, string $tanggal)
    {
        $this->judul = $judul;
        $this->tanggal = $tanggal;
    }

    public function getJudul(): string { return $this->judul; }
    public function getTanggal(): string { return $this->tanggal; }
    public function getStatus(): string { return $this->dibuka ? 'Dibuka' : 'Ditutup'; }

    public function buka(): string
    {
        if ($this->dibuka) {
            return 'Rapat sudah dalam keadaan dibuka.';
        }
        $this->dibuka = true;
        return 'Sesi absensi rapat berhasil dibuka.';
    }

    public function tutup(): string
    {
        if (!$this->dibuka) {
            return 'Rapat sudah dalam keadaan ditutup.';
        }
        $this->dibuka = false;
        return 'Sesi absensi rapat berhasil ditutup.';
    }

    public function catatKehadiran(BisaAbsen $anggota): string
    {
        if (!$this->dibuka) {
            return "GAGAL: sesi absensi '{$this->judul}' sedang ditutup.";
        }
        if (isset($this->daftarAbsensi[$anggota->getId()])) {
            return "GAGAL: {$anggota->getNama()} sudah mengisi absensi rapat ini.";
        }

        $absensi = new Absensi(
            $anggota->getId(),
            $anggota->getNama(),
            $this->judul,
            $this->tanggal,
            date('H:i:s')
        );
        $this->daftarAbsensi[$anggota->getId()] = $absensi;
        $anggota->simpanRiwayatHadir($absensi);

        return "BERHASIL: {$anggota->getNama()} tercatat hadir.";
    }

    /** @return Absensi[] */
    public function getDaftarAbsensi(): array
    {
        return array_values($this->daftarAbsensi);
    }
}

class Foto
{
    private string $file;
    private string $keterangan;

    public function __construct(string $file, string $keterangan)
    {
        $this->file = $file;
        $this->keterangan = $keterangan;
    }

    public function tampilkan(): string
    {
        return "{$this->file} - {$this->keterangan}";
    }
}

class Kegiatan
{
    private string $judul;
    private string $tanggal;
    private string $deskripsi;
    /** @var Foto[] */
    private array $galeri = [];

    public function __construct(string $judul, string $tanggal, string $deskripsi)
    {
        $this->judul = $judul;
        $this->tanggal = $tanggal;
        $this->deskripsi = $deskripsi;
    }

    public function tambahFoto(Foto $foto): void
    {
        $this->galeri[] = $foto;
    }

    public function tampilkanDetail(): string
    {
        return "{$this->judul} ({$this->tanggal})\n  {$this->deskripsi}";
    }

    /** @return Foto[] */
    public function getGaleri(): array
    {
        return $this->galeri;
    }
}

class KontakPerson
{
    private string $nama;
    private string $jabatan;
    private string $kontak;

    public function __construct(string $nama, string $jabatan, string $kontak)
    {
        $this->nama = $nama;
        $this->jabatan = $jabatan;
        $this->kontak = $kontak;
    }

    public function tampilkanKontak(): string
    {
        return "{$this->nama} - {$this->jabatan} - {$this->kontak}";
    }
}

function garis(string $judul): void
{
    echo "\n--- {$judul} ---\n";
}

$anggota1 = new Anggota('2310001', 'Fifi Nabilah Zulfa');
$anggota2 = new Anggota('2310002', 'Maulana Ibrahim Zulfa');
$anggotaNonaktif = new Anggota('2310003', 'Anggota Nonaktif', false);
$pengurus = new PengurusHimatif('P001', 'Dedi Prasetyo', 'Ketua Umum');
$daftarPengguna = [$anggota1, $pengurus, $anggota2, $anggotaNonaktif];

$rapat = new Rapat('Rapat Koordinasi Mingguan', '22 Oktober 2026');
$kegiatan = new Kegiatan('Workshop Pemrograman', '10 Oktober 2026', 'Pelatihan pemrograman untuk anggota HIMATIF.');
$kegiatan->tambahFoto(new Foto('foto-workshop-1.jpg', 'Dokumentasi kegiatan (nama file contoh)'));
$kegiatan->tambahFoto(new Foto('foto-workshop-2.jpg', 'Dokumentasi kegiatan (nama file contoh)'));
$kontak = new KontakPerson('Pengurus HIMATIF', 'Kontak organisasi', 'Isi dengan kontak resmi HIMATIF');

echo "=== SISTEM INFORMASI & ABSENSI HIMATIF ===\n";

garis('1. INFORMASI HIMATIF');
echo "Profil: Himpunan Mahasiswa Teknik Informatika.\n";
echo "Visi, misi, dan struktur: isi menggunakan informasi resmi HIMATIF.\n";

garis('2. KEGIATAN DAN GALERI FOTO');
echo $kegiatan->tampilkanDetail() . "\n";
foreach ($kegiatan->getGaleri() as $nomor => $foto) {
    echo '  ' . ($nomor + 1) . '. ' . $foto->tampilkan() . "\n";
}

garis('3. KONTAK PERSON');
echo $kontak->tampilkanKontak() . "\n";

garis('4. DAFTAR PENGGUNA (POLYMORPHISM)');
foreach ($daftarPengguna as $pengguna) {
    echo '[' . $pengguna->getId() . '] ' . $pengguna->tampilkanProfil() . "\n";
    echo '  Akses: ' . $pengguna->aksesUtama() . "\n";
}

garis('5. LOGIN ANGGOTA');
echo $anggota1->login() . "\n";
echo $anggota2->login() . "\n";
echo $anggotaNonaktif->login() . "\n";

garis('6. JADWAL DAN PROSES ABSENSI RAPAT');
echo 'Rapat: ' . $rapat->getJudul() . ' - ' . $rapat->getTanggal() . "\n";
echo 'Status awal: ' . $rapat->getStatus() . "\n";
echo 'Pengurus: ' . $pengurus->bukaRapat($rapat) . "\n";
echo 'Status sesi: ' . $rapat->getStatus() . "\n";
echo 'Anggota 1: ' . $anggota1->absen($rapat) . "\n";
echo 'Anggota 2: ' . $anggota2->absen($rapat) . "\n";
echo 'Anggota nonaktif: ' . $anggotaNonaktif->absen($rapat) . "\n";
echo 'Uji absen dua kali: ' . $anggota1->absen($rapat) . "\n";
echo 'Pengurus: ' . $pengurus->tutupRapat($rapat) . "\n";
echo 'Uji setelah sesi ditutup: ' . $anggota2->absen($rapat) . "\n";

garis('7. REKAP DAN RIWAYAT KEHADIRAN');
$daftarAbsensi = $rapat->getDaftarAbsensi();
foreach ($daftarAbsensi as $nomor => $absensi) {
    echo ($nomor + 1) . '. ' . $absensi->getNamaAnggota() . ' (NIM ' . $absensi->getIdAnggota() . ') - Hadir pukul ' . $absensi->getWaktuHadir() . "\n";
}
echo 'Total anggota hadir: ' . count($daftarAbsensi) . " orang\n";
echo "Riwayat {$anggota1->getNama()}:\n  {$anggota1->lihatRiwayatHadir()}\n";
echo "Riwayat {$anggota2->getNama()}:\n  {$anggota2->lihatRiwayatHadir()}\n";
echo "Riwayat {$anggotaNonaktif->getNama()}:\n  {$anggotaNonaktif->lihatRiwayatHadir()}\n";

garis('SELESAI');
