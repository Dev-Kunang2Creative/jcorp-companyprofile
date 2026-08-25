<?php

/**
 * Pemrosesan logo PT. Ayodya Utama Logistic — sekali jalan, hasilnya masuk git.
 *
 *     php docs/scripts/proses-logo-ayodya.php
 *
 * TIDAK dijalankan otomatis. Berkas keluarannya sudah ada di
 * `public/images/brand/`, jadi skrip ini disimpan sebagai catatan CARA
 * hasilnya dibuat — bukan bagian dari alur aplikasi.
 *
 * ---
 *
 * BERKAS SUMBERNYA KOP SURAT 746x171, BUKAN LOGO BERSIH.
 *
 * Isinya satu kesatuan yang membentang penuh:
 *
 *   x   0..137  lambang bulat merah, latar putih
 *   x 138..710  tulisan "PT. AYODYA UTAMA LOGISTIC" — JUGA MERAH,
 *               di atas latar biru muda bergradasi
 *
 * KESALAHAN YANG SEMPAT TERJADI: percobaan pertama memotong di x=138 karena
 * cacah piksel biru per kolom melonjak di situ. Itu salah baca — birunya
 * memang mulai di sana, tapi itu LATAR di belakang tulisan, bukan penanda
 * batas logo. Hasilnya lambangnya terpotong: hanya lingkaran merah tanpa
 * nama perusahaan, dan di halaman terlihat seperti gambar rusak.
 *
 * Yang benar: seluruh lebar diambil, dan latar birunya yang dibuang.
 * Keduanya terpisah jelas — merah rata-rata rgb(228,68,68), biru rata-rata
 * rgb(147,197,240) — jadi penyaringan warna bisa membuang latar tanpa
 * menyentuh tulisannya.
 *
 * PEMISAHANNYA MEMAKAI WARNA, BUKAN LUMINANSI. Merah tua punya luminansi
 * rendah, dan ambang luminansi memperlakukan semua warna sama sehingga
 * merahnya ikut pudar di tepi.
 */
$path = __DIR__.'/../../public/images/ayodya-logo.png';

if (! is_file($path)) {
    fwrite(STDERR, <<<'TEXT'
        Berkas sumber tidak ada: public/images/ayodya-logo.png

        Ini normal. Sumbernya sengaja dipindah keluar dari public/ setelah
        diproses — berkas di sana bisa dibuka siapa pun, dan itu kop surat
        utuh berlatar biru.

        Hasil olahannya sudah ada dan ikut git:
          public/images/brand/ayodya-logo-600.webp  (dan 300, badge 96/48)

        Skrip ini hanya perlu dijalankan lagi kalau logonya diganti. Taruh
        berkas barunya di jalur di atas, lalu jalankan ulang.

        TEXT);

    exit(1);
}

$src = imagecreatefrompng($path);
$w = imagesx($src);
$h = imagesy($src);

/*
 * Sebuah piksel dianggap ISI bila jelas kemerahan: kanal merah cukup
 * terang DAN cukup unggul atas hijau maupun biru.
 *
 * Angkanya diambil dari pengukuran: merah rata-rata rgb(228,68,68) —
 * selisih R terhadap G dan B sekitar 160, jauh di atas ambang 45. Latar
 * biru rgb(147,197,240) tidak pernah lolos karena R-nya justru terkecil.
 */
const RED_MIN = 100;
const RED_LEAD = 45;

$out = imagecreatetruecolor($w, $h);
imagealphablending($out, false);
imagesavealpha($out, true);
imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));

$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $c = imagecolorat($src, $x, $y);
        $r = ($c >> 16) & 255;
        $g = ($c >> 8) & 255;
        $b = $c & 255;

        // Seberapa unggul merah atas kanal lain. Nilai ini yang membedakan
        // tulisan dari latar putih MAUPUN latar biru sekaligus.
        $lead = $r - max($g, $b);

        if ($r < RED_MIN || $lead < RED_LEAD) {
            continue; // latar — putih atau biru, dua-duanya dibuang
        }

        // Alpha naik bertahap di dekat ambang supaya tepi hurufnya tidak
        // bergerigi. Warna aslinya dipakai apa adanya — merah itu identitas
        // mereka.
        $alpha = $lead >= RED_LEAD + 55
            ? 0
            : (int) round((1 - ($lead - RED_LEAD) / 55) * 127);

        imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $alpha));

        if ($alpha < 110) {
            $minX = min($minX, $x);
            $maxX = max($maxX, $x);
            $minY = min($minY, $y);
            $maxY = max($maxY, $y);
        }
    }
}

// Dipotong ke isi + margin 3%. Kop suratnya punya ruang kosong di sekeliling
// tulisan; tanpa pemotongan, logo terlihat melayang di dalam kotaknya.
$pad = (int) round(max($maxX - $minX, $maxY - $minY) * 0.03);
$minX = max(0, $minX - $pad);
$minY = max(0, $minY - $pad);
$maxX = min($w - 1, $maxX + $pad);
$maxY = min($h - 1, $maxY + $pad);

$cw = $maxX - $minX + 1;
$ch = $maxY - $minY + 1;

$crop = imagecreatetruecolor($cw, $ch);
imagealphablending($crop, false);
imagesavealpha($crop, true);
imagefill($crop, 0, 0, imagecolorallocatealpha($crop, 0, 0, 0, 127));
imagecopy($crop, $out, 0, 0, $minX, $minY, $cw, $ch);

echo "logo dipotong ke {$cw}x{$ch} (dari {$w}x{$h}, latar putih & biru dibuang)\n";

$dir = __DIR__.'/../../public/images/brand';

/** Menyimpan versi berskala, menjaga rasio dan transparansi. */
$save = function (int $width) use ($crop, $cw, $ch, $dir): void {
    $height = (int) round($ch * ($width / $cw));

    $img = imagecreatetruecolor($width, $height);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, true);
    imagecopyresampled($img, $crop, 0, 0, 0, 0, $width, $height, $cw, $ch);
    imagesavealpha($img, true);

    imagewebp($img, "{$dir}/ayodya-logo-{$width}.webp", 88);
    imagepng($img, "{$dir}/ayodya-logo-{$width}.png", 9);

    printf(
        "  %4dpx  webp %5.1f KB   png %5.1f KB\n",
        $width,
        filesize("{$dir}/ayodya-logo-{$width}.webp") / 1024,
        filesize("{$dir}/ayodya-logo-{$width}.png") / 1024,
    );
};

$save(600);
$save(300);

/*
 * Lencana persegi — HANYA LAMBANG BULATNYA, bukan logo utuh.
 *
 * Logo lengkapnya berasio 5,5:1. Dimampatkan ke kotak 96x96, tulisannya
 * menyusut jadi pita setinggi dua piksel yang tidak terbaca sama sekali —
 * di favicon 48px lebih parah lagi.
 *
 * Lambang bulat di ujung kiri berasio nyaris 1:1, jadi ia yang dipakai.
 * Ini juga yang lazim: logo panjang untuk hero, lambangnya saja untuk ikon.
 */
$markWidth = (int) round($ch * 1.35);

$mark = imagecreatetruecolor($markWidth, $ch);
imagealphablending($mark, false);
imagesavealpha($mark, true);
imagefill($mark, 0, 0, imagecolorallocatealpha($mark, 0, 0, 0, 127));
imagecopy($mark, $crop, 0, 0, 0, 0, $markWidth, $ch);

$badge = function (int $size) use ($mark, $markWidth, $ch, $dir): void {
    $scale = $size / max($markWidth, $ch);
    $bw = (int) round($markWidth * $scale);
    $bh = (int) round($ch * $scale);

    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, true);
    imagecopyresampled(
        $img, $mark,
        (int) (($size - $bw) / 2), (int) (($size - $bh) / 2), 0, 0,
        $bw, $bh, $markWidth, $ch,
    );
    imagesavealpha($img, true);

    imagepng($img, "{$dir}/ayodya-badge-{$size}.png", 9);

    printf("  badge %3dpx  png %5.1f KB (lambang saja)\n", $size, filesize("{$dir}/ayodya-badge-{$size}.png") / 1024);
};

$badge(96);
$badge(48);

/*
 * Pemeriksaan kontras — DESIGN_SYSTEM §5.3 mewajibkan warna baru diuji
 * terhadap titik gradasi TERGELAP yang akan memuatnya, bukan terhadap putih.
 */
$luminance = function (int $r, int $g, int $b): float {
    $channel = function (int $v): float {
        $v /= 255;

        return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    };

    return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
};

// Merah rata-rata lambang, diukur dari piksel pekatnya.
$sr = $sg = $sb = $n = 0;

for ($y = 0; $y < $ch; $y++) {
    for ($x = 0; $x < $cw; $x++) {
        $c = imagecolorat($crop, $x, $y);

        if ((($c >> 24) & 0x7F) > 20) {
            continue;
        }

        $sr += ($c >> 16) & 255;
        $sg += ($c >> 8) & 255;
        $sb += $c & 255;
        $n++;
    }
}

$mr = (int) round($sr / $n);
$mg = (int) round($sg / $n);
$mb = (int) round($sb / $n);

$ratio = ($luminance(245, 239, 227) + 0.05) / ($luminance($mr, $mg, $mb) + 0.05);

printf(
    "\nmerah lambang rgb(%d,%d,%d) di atas latar terpucat (#F5EFE3): %.2f:1 — %s\n",
    $mr, $mg, $mb,
    $ratio,
    $ratio >= 4.5 ? 'lolos AA' : ($ratio >= 3.0 ? 'lolos AA untuk grafis besar (bukan teks)' : 'GAGAL'),
);

echo "selesai\n";
