<?php

/**
 * Pemrosesan logo J-Corporate Group — sekali jalan, hasilnya masuk git.
 *
 *     php docs/scripts/proses-logo-jcorp.php
 *
 * TIDAK dijalankan otomatis. Berkas keluarannya sudah ada di
 * `public/images/brand/`, jadi skrip ini disimpan sebagai catatan CARA
 * hasilnya dibuat — bukan bagian dari alur aplikasi.
 *
 * ---
 *
 * LOGO KEDUA. Client mengirim berkas baru 24 Agustus 2026, menggantikan
 * yang dipakai sejak 21 Agustus.
 *
 * Yang lama: foto logo di atas kertas bertekstur, 659x659. Perlu
 * penyaringan komponen tersambung untuk membuang bayangan pinggir foto
 * dan noda — rumit, dan hasilnya tetap terbatas oleh mutu fotonya.
 *
 * Yang sekarang: JPEG bersih 1064x1006, latar putih rata (87,7% piksel
 * di lum 240+), tinta rgb(49,49,48). Pemisahan ambang saja sudah cukup.
 *
 * NAMA BERKAS KELUARANNYA SENGAJA SAMA PERSIS dengan yang lama —
 * `jcorp-logo-600.webp` dan seterusnya. Dengan begitu tidak ada satu
 * baris kode pun yang perlu diubah: `ClientContent::JCORP_LOGO` tetap
 * menunjuk ke jalur yang sama, dan lencana favicon tetap diturunkan
 * dengan pola yang sama.
 *
 * SUMBERNYA JPEG, dan itu berpengaruh dua kali:
 *
 * 1. Kompresi JPEG meninggalkan riak halus di sekitar tepi tinta yang
 *    pada ambang ketat terbaca sebagai bintik — sama seperti logo
 *    J-Land. Karena itu PAPER dipasang agak tinggi dengan tepi
 *    semi-transparan yang lebar, supaya riaknya larut jadi gradasi.
 *
 * 2. Warna tintanya jadi tidak rata. Sampel dari sumbernya menunjukkan
 *    rgb(15,15,15) sampai rgb(61,61,61) — semuanya abu-abu netral, jadi
 *    variasinya NOISE, bukan gradasi yang disengaja perancangnya.
 *
 *    Dibiarkan apa adanya, hasilnya 1.202 warna berbeda dan berkas WebP
 *    56 KB — tiga kali lipat logo lama. Karena itu warnanya
 *    DISERAGAMKAN ke satu nilai; yang tetap bergradasi hanya alpha-nya,
 *    dan itu yang menjaga tepi kurvanya tetap halus.
 */
$path = __DIR__.'/../../public/images/jcorp-logo.jpeg';

if (! is_file($path)) {
    fwrite(STDERR, <<<'TEXT'
        Berkas sumber tidak ada: public/images/jcorp-logo.jpeg

        Ini normal. Sumbernya sengaja dipindah keluar dari public/ setelah
        diproses — berkas di sana bisa dibuka siapa pun, dan itu versi
        mentah berlatar putih.

        Hasil olahannya sudah ada dan ikut git:
          public/images/brand/jcorp-logo-600.webp  (dan 300, badge 96/48)

        Skrip ini hanya perlu dijalankan lagi kalau logonya diganti. Taruh
        berkas barunya di jalur di atas, lalu jalankan ulang.

        TEXT);

    exit(1);
}

$src = imagecreatefromjpeg($path);
$w = imagesx($src);
$h = imagesy($src);

// Di bawah INK murni tinta, di atas PAPER murni latar, di antaranya
// semi-transparan supaya tepi kurvanya tidak bergerigi.
const INK = 110;
const PAPER = 225;

/**
 * Warna tinta seragam.
 *
 * Diambil dari rata-rata piksel pekat di sumbernya, dibulatkan sedikit
 * ke arah gelap. Memakai satu nilai alih-alih warna asli tiap piksel
 * memangkas jumlah warna dari 1.202 jadi satu — dan ukuran berkasnya
 * ikut turun drastis tanpa perbedaan yang terlihat mata.
 */
const INK_COLOR = [34, 34, 34];

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

        // Warna tinta seragam, BUKAN warna asli piksel. Yang bergradasi
        // hanya alpha-nya — itu yang menghilangkan halo putih di tepi
        // sekaligus menjaga kurvanya tetap halus.
        imagesetpixel($out, $x, $y, imagecolorallocatealpha(
            $out, INK_COLOR[0], INK_COLOR[1], INK_COLOR[2], $alpha,
        ));

        if ($alpha < 110) {
            $minX = min($minX, $x);
            $maxX = max($maxX, $x);
            $minY = min($minY, $y);
            $maxY = max($maxY, $y);
        }
    }
}

/*
 * Membuang bintik nyasar sisa riak JPEG.
 *
 * Piksel pekat yang SELURUH tetangganya transparan bukan bagian dari
 * bentuk apa pun — di gambar sebersih ini, garis logo selalu punya
 * tetangga. Tanpa langkah ini tersisa 17 bintik yang terlihat seperti
 * kotoran pada tampilan besar.
 */
$dibuang = 0;

for ($y = 1; $y < $h - 1; $y++) {
    for ($x = 1; $x < $w - 1; $x++) {
        $c = imagecolorat($out, $x, $y);

        if ((($c >> 24) & 0x7F) > 60) {
            continue;
        }

        $tetangga = 0;

        foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
            if (((imagecolorat($out, $x + $dx, $y + $dy) >> 24) & 0x7F) < 60) {
                $tetangga++;
            }
        }

        if ($tetangga === 0) {
            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, 0, 0, 0, 127));
            $dibuang++;
        }
    }
}

if ($dibuang > 0) {
    echo "bintik nyasar dibuang: {$dibuang} piksel
";
}

// Dipotong ke isi + margin 3% supaya lingkarannya tidak menyentuh tepi.
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

    imagewebp($img, "{$dir}/jcorp-logo-{$width}.webp", 88);
    imagepng($img, "{$dir}/jcorp-logo-{$width}.png", 9);

    printf(
        "  %4dpx  webp %5.1f KB   png %5.1f KB\n",
        $width,
        filesize("{$dir}/jcorp-logo-{$width}.webp") / 1024,
        filesize("{$dir}/jcorp-logo-{$width}.png") / 1024,
    );
};

$save(600);
$save(300);

/**
 * Lencana persegi untuk navigasi dan favicon.
 *
 * Logonya nyaris bujur sangkar, jadi cukup diberi kanvas persegi dan
 * dipusatkan — tidak perlu diambil sebagiannya seperti logo Ayodya yang
 * memanjang 5,5:1.
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

    imagepng($img, "{$dir}/jcorp-badge-{$size}.png", 9);

    printf("  badge %3dpx  png %5.1f KB\n", $size, filesize("{$dir}/jcorp-badge-{$size}.png") / 1024);
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
