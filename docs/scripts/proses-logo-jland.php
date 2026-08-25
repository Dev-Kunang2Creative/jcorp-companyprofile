<?php

/**
 * Pemrosesan logo J-Land Property — sekali jalan, hasilnya masuk git.
 *
 *     php docs/scripts/proses-logo-jland.php
 *
 * TIDAK dijalankan otomatis. Berkas keluarannya sudah ada di
 * `public/images/brand/`, jadi skrip ini disimpan sebagai catatan CARA
 * hasilnya dibuat — bukan bagian dari alur aplikasi.
 *
 * ---
 *
 * Yang paling bersih dari kelima logo: 1254x1254, latar putih rata
 * (87,5% piksel di lum 240+), tinta gelap rgb(36,36,36) (10,6% di lum
 * 32-47). Tidak ada wilayah abu-abu di antaranya, jadi pemisahan ambang
 * saja sudah cukup — tanpa penyaringan komponen seperti logo Ayodya,
 * dan tanpa pembalikan warna seperti ngelash.
 *
 * SUMBERNYA JPEG, bukan PNG. Bedanya penting di sini: kompresi JPEG
 * meninggalkan riak halus di sekitar tepi tinta (ringing), yang pada
 * ambang ketat terbaca sebagai bintik. Itu alasan PAPER dipasang agak
 * tinggi (225) dan tepi semi-transparannya dibuat lebar — riaknya larut
 * jadi gradasi alih-alih jadi bintik.
 */
$path = __DIR__.'/../../public/images/jland-logo.jpeg';

if (! is_file($path)) {
    fwrite(STDERR, <<<'TEXT'
        Berkas sumber tidak ada: public/images/jland-logo.jpeg

        Ini normal. Sumbernya sengaja dipindah keluar dari public/ setelah
        diproses — berkas di sana bisa dibuka siapa pun, dan itu versi
        mentah berlatar putih.

        Hasil olahannya sudah ada dan ikut git:
          public/images/brand/jland-logo-600.webp  (dan 300, badge 96/48)

        Skrip ini hanya perlu dijalankan lagi kalau logonya diganti. Taruh
        berkas barunya di jalur di atas, lalu jalankan ulang.

        TEXT);

    exit(1);
}

$src = imagecreatefromjpeg($path);
$w = imagesx($src);
$h = imagesy($src);

// Di bawah INK murni tinta, di atas PAPER murni latar, di antaranya
// semi-transparan supaya tepi hurufnya tidak bergerigi.
const INK = 110;
const PAPER = 225;

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
            continue; // latar putih — biarkan transparan
        }

        $alpha = $lum <= INK
            ? 0
            : (int) round((($lum - INK) / (PAPER - INK)) * 127);

        // Warna aslinya dipakai apa adanya. Yang bergradasi hanya
        // alpha-nya, dan itu yang menghilangkan halo putih di tepi.
        imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $alpha));

        if ($alpha < 110) {
            $minX = min($minX, $x);
            $maxX = max($maxX, $x);
            $minY = min($minY, $y);
            $maxY = max($maxY, $y);
        }
    }
}

// Dipotong ke isi + margin 3%. Sumbernya punya ruang kosong lebar di
// bawah tulisan; tanpa pemotongan, logo terlihat melayang ke atas saat
// disejajarkan dengan logo lain di kartu anak usaha.
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

    imagewebp($img, "{$dir}/jland-logo-{$width}.webp", 88);
    imagepng($img, "{$dir}/jland-logo-{$width}.png", 9);

    printf(
        "  %4dpx  webp %5.1f KB   png %5.1f KB\n",
        $width,
        filesize("{$dir}/jland-logo-{$width}.webp") / 1024,
        filesize("{$dir}/jland-logo-{$width}.png") / 1024,
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

    imagepng($img, "{$dir}/jland-badge-{$size}.png", 9);

    printf("  badge %3dpx  png %5.1f KB\n", $size, filesize("{$dir}/jland-badge-{$size}.png") / 1024);
};

$badge(96);
$badge(48);

/*
 * Pemeriksaan kontras — DESIGN_SYSTEM §2.1 mewajibkan warna baru diuji
 * terhadap permukaan yang akan memuatnya, bukan terhadap putih.
 */
$luminance = function (int $r, int $g, int $b): float {
    $channel = function (int $v): float {
        $v /= 255;

        return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    };

    return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
};

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

// --color-wash, permukaan kartu anak usaha.
$ratio = ($luminance(250, 249, 247) + 0.05) / ($luminance($mr, $mg, $mb) + 0.05);

printf(
    "\ntinta rgb(%d,%d,%d) di atas permukaan kartu (#FAF9F7): %.2f:1 — %s\n",
    $mr, $mg, $mb,
    $ratio,
    $ratio >= 4.5 ? 'lolos AA' : ($ratio >= 3.0 ? 'grafis besar saja' : 'GAGAL'),
);

echo "selesai\n";
