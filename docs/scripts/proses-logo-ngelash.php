<?php

/**
 * Pemrosesan logo ngelash.id — sekali jalan, hasilnya masuk git.
 *
 *     php docs/scripts/proses-logo-ngelash.php
 *
 * TIDAK dijalankan otomatis. Berkas keluarannya sudah ada di
 * `public/images/brand/`, jadi skrip ini disimpan sebagai catatan CARA
 * hasilnya dibuat — bukan bagian dari alur aplikasi.
 *
 * ---
 *
 * Berbeda dari dua logo sebelumnya. Sumbernya bukan foto di atas kertas,
 * melainkan berkas desain bersih 1310x389: latar gelap rata rgb(38,38,38),
 * tulisan putih, aksen emas rgb(208,160,0).
 *
 * Karena itu tidak perlu penyaringan komponen seperti logo J-Corporate —
 * tidak ada bayangan tepi maupun noda yang perlu disingkirkan.
 *
 * YANG PERLU DIPUTUSKAN, dan sudah disetujui pemilik project:
 *
 * Halaman website berlatar TERANG (krem #FDFBF6 dengan aurora). Logo putih
 * di atasnya tidak akan terbaca sama sekali. Jadi tulisan putihnya dibalik
 * jadi tinta gelap, sementara AKSEN EMASNYA DIPERTAHANKAN — itu warna khas
 * logonya, dan kebetulan sudah dekat dengan emas design system.
 *
 * Bentuk hurufnya tidak berubah sedikit pun; yang berubah hanya warnanya.
 */
$path = __DIR__.'/../../public/images/ngelash-logo.png';

if (! is_file($path)) {
    fwrite(STDERR, <<<'TEXT'
        Berkas sumber tidak ada: public/images/ngelash-logo.png

        Ini normal. Sumbernya sengaja dihapus setelah diproses — berkas di
        public/ bisa dibuka siapa pun, dan itu versi berlatar gelap yang
        tidak dipakai di halaman.

        Hasil olahannya sudah ada dan ikut git:
          public/images/brand/ngelash-logo-600.webp  (dan 300, badge 96/48)

        Skrip ini hanya perlu dijalankan lagi kalau logonya diganti. Taruh
        berkas barunya di jalur di atas, lalu jalankan ulang.

        TEXT);

    exit(1);
}

$src = imagecreatefrompng($path);
$w = imagesx($src);
$h = imagesy($src);

// Latar gelap rata. Ambang longgar supaya bayangan halus di sekitar huruf
// ikut terbuang, tapi tidak sampai memakan bagian huruf yang paling redup.
const BACKGROUND = 70;   // di bawah ini: latar, dibuang
const SOLID = 150;       // di atas ini: bagian huruf yang pekat

// Warna tinta untuk menggantikan putih. Sama dengan yang dipakai logo
// J-Corporate, supaya ketiga logo terbaca sebagai satu keluarga.
$ink = [38, 36, 42];

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

        if ($lum <= BACKGROUND) {
            continue; // latar — biarkan transparan
        }

        // Piksel emas dikenali dari merah tinggi dan biru rendah. Warnanya
        // dipertahankan apa adanya; hanya yang keputihan yang dibalik.
        $isGold = $r > 140 && $b < 120 && ($r - $b) > 60;

        // Semakin terang piksel aslinya, semakin pekat hasilnya — hubungan
        // yang terbalik dari logo berlatar terang.
        $alpha = $lum >= SOLID
            ? 0
            : (int) round((1 - ($lum - BACKGROUND) / (SOLID - BACKGROUND)) * 127);

        $color = $isGold
            ? [$r, $g, $b]
            : $ink;

        imagesetpixel($out, $x, $y, imagecolorallocatealpha(
            $out, $color[0], $color[1], $color[2], $alpha,
        ));

        if ($alpha < 110) {
            $minX = min($minX, $x);
            $maxX = max($maxX, $x);
            $minY = min($minY, $y);
            $maxY = max($maxY, $y);
        }
    }
}

// Dipotong ke isi + margin 2% supaya hurufnya tidak menyentuh tepi.
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

    imagewebp($img, "{$dir}/ngelash-logo-{$width}.webp", 88);
    imagepng($img, "{$dir}/ngelash-logo-{$width}.png", 9);

    printf(
        "  %4dpx  webp %5.1f KB   png %5.1f KB\n",
        $width,
        filesize("{$dir}/ngelash-logo-{$width}.webp") / 1024,
        filesize("{$dir}/ngelash-logo-{$width}.png") / 1024,
    );
};

$save(600);
$save(300);

/**
 * Lencana persegi untuk navigasi dan favicon.
 *
 * Logonya sangat memanjang (rasio kira-kira 3:1), jadi di kanvas persegi ia
 * hanya mengisi sepertiga tingginya. Itu wajar — memotongnya jadi persegi
 * akan memenggal hurufnya.
 */
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

    imagepng($img, "{$dir}/ngelash-badge-{$size}.png", 9);

    printf("  badge %3dpx  png %5.1f KB\n", $size, filesize("{$dir}/ngelash-badge-{$size}.png") / 1024);
};

$badge(96);
$badge(48);

echo "selesai\n";
