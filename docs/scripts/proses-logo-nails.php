<?php

/**
 * Pemrosesan logo Nail's by Me — sekali jalan, hasilnya masuk git.
 *
 *     php docs/scripts/proses-logo-nails.php
 *
 * TIDAK dijalankan otomatis. Berkas keluarannya sudah ada di
 * `public/images/brand/`, jadi skrip ini disimpan sebagai catatan CARA
 * hasilnya dibuat — bukan bagian dari alur aplikasi.
 *
 * ---
 *
 * Berkas desain bersih 404x272: latar putih rata, tulisan hijau tua
 * rgb(42,65,4). Tidak perlu penyaringan komponen seperti logo J-Corporate
 * — tidak ada bayangan tepi maupun noda.
 *
 * WARNA TINTANYA DIPERTAHANKAN, tidak diseragamkan ke tinta gelap seperti
 * logo ngelash. Bedanya: ngelash berlatar gelap sehingga tulisan putihnya
 * tidak akan terbaca di atas krem halaman — pembalikan warnanya terpaksa.
 * Yang ini sudah gelap di atas terang, jadi kontrasnya aman apa adanya, dan
 * hijau itu warna khas mereka.
 *
 * Kontras hijau ini terhadap latar terpucat halaman (#F5EFE3, titik gradasi
 * paling gelap) diperiksa di bagian bawah berkas — bukan diasumsikan.
 */
// Nama berkasnya diseragamkan saat dipindah: aslinya "nail's by me-logo.png",
// yang memuat apostrof dan spasi — dua hal yang menyulitkan di baris perintah
// dan mudah rusak saat disalin antar sistem.
$path = __DIR__.'/../../public/images/nails-logo.png';

if (! is_file($path)) {
    fwrite(STDERR, <<<'TEXT'
        Berkas sumber tidak ada: public/images/nails-logo.png

        Ini normal. Sumbernya sengaja dipindah keluar dari public/ setelah
        diproses — berkas di sana bisa dibuka siapa pun, dan itu versi
        mentah berlatar putih.

        Hasil olahannya sudah ada dan ikut git:
          public/images/brand/nails-logo-600.webp  (dan 300, badge 96/48)

        Skrip ini hanya perlu dijalankan lagi kalau logonya diganti. Taruh
        berkas barunya di jalur di atas, lalu jalankan ulang.

        TEXT);

    exit(1);
}

$src = imagecreatefrompng($path);
$w = imagesx($src);
$h = imagesy($src);

// Ambang: di bawah INK murni tinta, di atas PAPER murni latar, di antaranya
// dibuat semi-transparan supaya tepi hurufnya tidak bergerigi.
//
// PAPER sengaja tinggi (235, bukan 210). Di bawah tulisan utama ada baris
// teks kecil yang jauh lebih pucat — piksel terpucatnya sekitar 160-185,
// dan sebagian tepinya di atas 210. Dengan ambang ketat, baris itu terpotong
// separuh: yang tersisa hanya pecahan huruf yang terlihat seperti noda.
const INK = 110;
const PAPER = 235;

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

        $lum = 0.299 * $r + 0.587 * $g + 0.114 * $b;

        if ($lum >= PAPER) {
            continue; // latar — biarkan transparan
        }

        $alpha = $lum <= INK
            ? 0
            : (int) round((($lum - INK) / (PAPER - INK)) * 127);

        // Warna aslinya dipakai apa adanya — hijau itu identitas mereka.
        // Yang bergradasi hanya alpha-nya, dan itu yang menghilangkan halo
        // putih di tepi huruf.
        imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $alpha));

        if ($alpha < 110) {
            $minX = min($minX, $x);
            $maxX = max($maxX, $x);
            $minY = min($minY, $y);
            $maxY = max($maxY, $y);
        }
    }
}

// Dipotong ke isi + margin 2%. Sumbernya punya ruang kosong cukup lebar di
// bawah tulisan; tanpa pemotongan, logo terlihat melayang ke atas saat
// disejajarkan dengan teks di sebelahnya.
$pad = (int) round(max($maxX - $minX, $maxY - $minY) * 0.02);
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

echo "dipotong ke {$cw}x{$ch} (dari {$w}x{$h})\n";

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

    imagewebp($img, "{$dir}/nails-logo-{$width}.webp", 88);
    imagepng($img, "{$dir}/nails-logo-{$width}.png", 9);

    printf(
        "  %4dpx  webp %5.1f KB   png %5.1f KB\n",
        $width,
        filesize("{$dir}/nails-logo-{$width}.webp") / 1024,
        filesize("{$dir}/nails-logo-{$width}.png") / 1024,
    );
};

$save(600);
$save(300);

/** Lencana persegi untuk navigasi dan favicon. */
$badge = function (int $size) use ($crop, $cw, $ch, $dir): void {
    $scale = $size / max($cw, $ch);
    $bw = (int) round($cw * $scale);
    $bh = (int) round($ch * $scale);

    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, true);
    imagecopyresampled(
        $img, $crop,
        (int) (($size - $bw) / 2), (int) (($size - $bh) / 2), 0, 0,
        $bw, $bh, $cw, $ch,
    );
    imagesavealpha($img, true);

    imagepng($img, "{$dir}/nails-badge-{$size}.png", 9);

    printf("  badge %3dpx  png %5.1f KB\n", $size, filesize("{$dir}/nails-badge-{$size}.png") / 1024);
};

$badge(96);
$badge(48);

/*
 * Pemeriksaan kontras — DESIGN_SYSTEM §5.3 mewajibkan warna baru diuji
 * terhadap titik gradasi TERGELAP yang akan memuatnya, bukan terhadap putih.
 * Menguji di atas putih saja akan meloloskan warna yang gagal di lapangan.
 */
$luminance = function (int $r, int $g, int $b): float {
    $channel = function (int $v): float {
        $v /= 255;

        return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    };

    return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
};

// Titik gradasi paling gelap di aurora halaman (lihat app.css).
$backdrop = $luminance(245, 239, 227);
$ink = $luminance(42, 65, 4);

$ratio = ($backdrop + 0.05) / ($ink + 0.05);

printf(
    "\nkontras hijau logo di atas latar terpucat (#F5EFE3): %.2f:1 — %s\n",
    $ratio,
    $ratio >= 4.5 ? 'lolos AA' : 'GAGAL AA',
);

echo "selesai\n";
