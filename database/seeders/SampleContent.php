<?php

namespace Database\Seeders;

/**
 * Satu sumber kebenaran untuk seluruh teks CONTOH.
 *
 * Dipakai dua tempat: seeder yang memasukkannya, dan `jcorp:clear-samples`
 * yang membersihkannya. Karena keduanya membaca daftar yang sama, perintah
 * pembersih bisa membandingkan nilai di database dengan teks contoh dan
 * HANYA mengosongkan yang masih persis sama.
 *
 * Konsekuensinya yang penting: kalau admin sudah mengubah tagline lewat
 * panel, teksnya tidak lagi cocok dan tidak akan ikut terhapus. Tanpa
 * perbandingan ini, perintah pembersih berisiko menghapus tulisan sungguhan.
 *
 * SEMUA TEKS DI SINI KARANGAN. Diganti begitu materi dari client masuk.
 *
 * JANGAN MENGHAPUS entri anak usaha yang materi aslinya sudah datang.
 * ClientContentSeeder membandingkan isi database dengan daftar di sini untuk
 * mengetahui mana yang masih teks contoh dan boleh ditimpa. Kalau entrinya
 * dihapus, perbandingan itu gagal dan teks contoh lama tertinggal di halaman
 * berdampingan dengan materi asli.
 *
 * Yang sudah TIDAK dipakai lagi untuk mengisi: SampleContentSeeder melewati
 * anak usaha yang ada di ClientContent.
 */
class SampleContent
{
    /** Jalur logo Sweetness — aset tetap di public/, bukan unggahan admin. */
    public const SWEETNESS_LOGO = 'images/brand/sweetness-logo-600.webp';

    /**
     * Profil per anak usaha: teks, kontak, dan label section.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function businesses(): array
    {
        return [
            'jcorp' => [
                'tagline' => 'Beberapa usaha yang tumbuh dari hal-hal kecil, dijalankan orang-orang yang benar-benar mengerjakannya sendiri.',
                'description' => implode("\n\n", [
                    'J Corp menaungi beberapa usaha yang bidangnya berbeda-beda — dari dessert rumahan sampai logistik dan properti.',
                    'Yang menyatukannya bukan jenis usahanya, melainkan cara kerjanya: dikerjakan langsung oleh orangnya, bukan diserahkan ke sistem yang tidak dikenal siapa pun.',
                    'Setiap unit berjalan sendiri dengan kekhasannya masing-masing. Halaman ini pintu masuk ke masing-masing dari mereka.',
                ]),
                'whatsapp' => '6281200000000',
                'instagram' => 'jcorp.id',
                'address' => 'Jl. Contoh Utama No. 1, Bekasi',
                'business_hours' => 'Senin–Jumat, 09.00–17.00',
            ],

            'sweetness-things' => [
                'tagline' => 'Dibuat sedikit-sedikit tiap hari, supaya yang sampai ke meja Anda masih sehangat waktu keluar oven.',
                'description' => implode("\n\n", [
                    'Sweetness Things berawal dari dapur rumah — dari kebiasaan membuat kue untuk keluarga, lalu untuk tetangga, lalu untuk siapa saja yang menanyakan resepnya.',
                    'Sampai sekarang cara kerjanya belum berubah. Setiap pesanan dibuat setelah dipesan, bukan diambil dari stok. Bahan yang dipakai sama dengan yang dipakai di rumah sendiri.',
                    'Manisnya sengaja ditahan. Yang kami cari bukan yang paling manis, tapi yang masih enak dimakan sampai potongan terakhir.',
                ]),
                'whatsapp' => '6281234567890',
                'instagram' => 'sweetnessthings',
                'address' => 'Jl. Contoh Raya No. 12, Bekasi',
                'business_hours' => 'Senin–Sabtu, 09.00–18.00',
                'catalog_label' => 'Menu Kami',
            ],

            'nails-by-me' => [
                'tagline' => 'Nail art yang dikerjakan pelan-pelan, karena tangan yang rapi tidak bisa diburu-buru.',
                'description' => implode("\n\n", [
                    'Nail\'s by Me melayani perawatan dan nail art dengan janji temu, bukan antre di tempat. Satu tamu satu waktu, supaya tidak ada yang dikejar-kejar.',
                    'Alat disterilkan setiap selesai dipakai. Kutek dan gel yang dipakai merek yang aman untuk kuku alami — bukan yang paling murah.',
                    'Desain bisa dibawa sendiri dari referensi, atau dibicarakan di tempat sesuai bentuk kuku dan warna kulit.',
                ]),
                'whatsapp' => '6281211112222',
                'instagram' => 'nailsbyme.id',
                'address' => 'Ruko Contoh Blok B No. 5, Bekasi',
                'business_hours' => 'Selasa–Minggu, 10.00–19.00 (dengan janji temu)',
                'catalog_label' => 'Layanan & Harga',
                'portfolio_label' => 'Hasil Kerja',
            ],

            'ngelash' => [
                'tagline' => 'Eyelash extension yang menyesuaikan bentuk mata, bukan menempel begitu saja.',
                'description' => implode("\n\n", [
                    'ngelash.id mengerjakan eyelash extension dengan pendekatan sederhana: bentuk mata setiap orang berbeda, jadi lengkung dan panjang bulunya tidak bisa disamaratakan.',
                    'Konsultasi singkat dulu sebelum mulai — supaya hasilnya sesuai keseharian, bukan cuma bagus di foto.',
                    'Lem yang dipakai jenis rendah iritasi. Kalau mata pernah bermasalah dengan extension sebelumnya, kabari lebih dulu.',
                ]),
                'whatsapp' => '6281233334444',
                'instagram' => 'ngelash.id',
                'tiktok' => 'ngelash.id',
                'address' => 'Jl. Contoh Melati No. 8, Bekasi',
                'business_hours' => 'Setiap hari, 10.00–20.00',
                'catalog_label' => 'Layanan & Harga',
                'portfolio_label' => 'Hasil Kerja',
            ],

            'ayodya-logistic' => [
                'tagline' => 'Pengiriman barang yang bisa ditanya kabarnya, bukan yang hilang di tengah jalan.',
                'description' => implode("\n\n", [
                    'PT. Ayodya Utama Logistic melayani pengiriman barang antar kota, sewa armada, dan penyimpanan sementara.',
                    'Setiap pengiriman punya satu orang yang bisa dihubungi — bukan nomor pusat yang menjawab dengan template.',
                    'Untuk barang pecah belah atau berdimensi besar, pengemasan dibicarakan dulu sebelum diangkat.',
                ]),
                'whatsapp' => '6281255556666',
                'instagram' => 'ayodyalogistic',
                'address' => 'Jl. Contoh Industri No. 21, Bekasi',
                'business_hours' => 'Senin–Sabtu, 08.00–17.00',
                'catalog_label' => 'Layanan Kami',
            ],

            // SLUG INI SUDAH TIDAK ADA di database — Lumintu Property
            // berganti jadi J-Land Property beserta alamat halamannya
            // (lihat migrasi 2026_08_22_000001).
            //
            // Entrinya SENGAJA DIPERTAHANKAN: ClientContentSeeder memakai
            // daftar ini untuk mengenali teks karangan yang masih tersimpan
            // di database. Dihapus, nomor `6281277778888` dan alamat
            // "Jl. Contoh Damai No. 3" tidak lagi dikenali sebagai contoh —
            // dan bertahan di halaman J-Land tanpa satu pun error.
            'lumintu-property' => [
                'tagline' => 'Kos dan properti sewa yang pemiliknya masih bisa dihubungi langsung.',
                'description' => implode("\n\n", [
                    'Lumintu Property mengelola kos dan unit sewa di beberapa titik di Bekasi.',
                    'Tidak ada perantara: yang menjawab pertanyaan adalah orang yang benar-benar mengurus bangunannya, jadi soal air, listrik, atau perbaikan bisa langsung ditanyakan.',
                    'Sewa bulanan maupun tahunan, dengan perjanjian tertulis yang isinya dibaca bersama sebelum ditandatangani.',
                ]),
                'whatsapp' => '6281277778888',
                'instagram' => 'lumintuproperty',
                'address' => 'Jl. Contoh Damai No. 3, Bekasi',
                'business_hours' => 'Senin–Sabtu, 09.00–18.00',
                'catalog_label' => 'Unit Tersedia',
            ],
        ];
    }

    /**
     * Item katalog contoh per anak usaha.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function catalogItems(): array
    {
        return [
            'sweetness-things' => [
                ['name' => 'Dessert Box Coklat', 'description' => 'Lapisan brownies dan krim coklat, ditutup remah biskuit.', 'price' => 65000, 'price_note' => null],
                ['name' => 'Cookies Almond', 'description' => 'Renyah di luar, lembut di tengah. Isi 12 potong.', 'price' => 45000, 'price_note' => null],
                ['name' => 'Tiramisu Jar', 'description' => 'Kopi tubruk asli, tanpa perasa tambahan.', 'price' => 38000, 'price_note' => null],
                ['name' => 'Nastar Premium', 'description' => 'Selai nanas dimasak sendiri, bukan selai jadi.', 'price' => 85000, 'price_note' => null],
                ['name' => 'Dessert Box Red Velvet', 'description' => 'Krim keju yang tidak terlalu manis.', 'price' => 68000, 'price_note' => null],
                ['name' => 'Choco Chip Cookies', 'description' => 'Coklat batang dipotong kasar, bukan chip instan.', 'price' => 42000, 'price_note' => null],
            ],

            'nails-by-me' => [
                ['name' => 'Manicure Dasar', 'description' => 'Rapikan kuku, kutikula, dan pijat tangan singkat.', 'price' => 60000, 'price_note' => null],
                ['name' => 'Pedicure Dasar', 'description' => 'Perawatan kuku kaki dan penghalusan tumit.', 'price' => 75000, 'price_note' => null],
                ['name' => 'Gel Polish Polos', 'description' => 'Tahan 2–3 minggu. Pilihan warna lebih dari 40.', 'price' => 90000, 'price_note' => null],
                ['name' => 'Nail Art Sederhana', 'description' => 'Aksen garis, titik, atau french tip.', 'price' => 120000, 'price_note' => null],
                ['name' => 'Nail Art Detail', 'description' => 'Motif bebas sesuai referensi. Waktu pengerjaan lebih lama.', 'price' => 180000, 'price_note' => null],
                ['name' => 'Lepas Gel & Perawatan', 'description' => 'Pelepasan aman tanpa mengikis lapisan kuku.', 'price' => 40000, 'price_note' => null],
            ],

            'ngelash' => [
                ['name' => 'Classic Eyelash', 'description' => 'Satu helai per bulu mata asli. Hasil paling alami.', 'price' => 150000, 'price_note' => null],
                ['name' => 'Volume Eyelash', 'description' => 'Beberapa helai tipis per bulu mata. Lebih tebal.', 'price' => 220000, 'price_note' => null],
                ['name' => 'Hybrid Eyelash', 'description' => 'Campuran classic dan volume.', 'price' => 190000, 'price_note' => null],
                ['name' => 'Refill (maks. 3 minggu)', 'description' => 'Untuk yang extension-nya masih tersisa separuh.', 'price' => 100000, 'price_note' => null],
                ['name' => 'Lepas Extension', 'description' => 'Pelepasan dengan cairan khusus, tidak dicabut.', 'price' => 50000, 'price_note' => null],
                ['name' => 'Lash Lift', 'description' => 'Menaikkan lengkung bulu mata asli tanpa sambungan.', 'price' => 175000, 'price_note' => null],
            ],

            'ayodya-logistic' => [
                ['name' => 'Pengiriman Reguler', 'description' => 'Antar kota dalam Jawa. Estimasi 2–4 hari kerja.', 'price' => null, 'price_note' => null, 'category' => 'Pengiriman'],
                ['name' => 'Pengiriman Kargo', 'description' => 'Untuk barang di atas 100 kg atau berdimensi besar.', 'price' => null, 'price_note' => null, 'category' => 'Pengiriman'],
                ['name' => 'Sewa Armada Harian', 'description' => 'Truk engkel dan CDD, dengan sopir.', 'price' => 850000, 'price_note' => null, 'category' => 'Armada'],
                ['name' => 'Sewa Armada Bulanan', 'description' => 'Kontrak minimal satu bulan, tarif lebih ringan.', 'price' => null, 'price_note' => 'hubungi kami', 'category' => 'Armada'],
                ['name' => 'Penyimpanan Sementara', 'description' => 'Gudang tertutup, dihitung per kubik per minggu.', 'price' => null, 'price_note' => 'hubungi kami', 'category' => 'Gudang'],
            ],

            'lumintu-property' => [
                ['name' => 'Kos Putri — Kamar Standar', 'description' => 'Kamar mandi dalam, kasur, lemari, meja. Listrik terpisah.', 'price' => 950000, 'price_note' => 'per bulan', 'category' => 'Kos Putri'],
                ['name' => 'Kos Putri — Kamar AC', 'description' => 'Sama dengan standar, ditambah AC dan water heater.', 'price' => 1350000, 'price_note' => 'per bulan', 'category' => 'Kos Putri'],
                ['name' => 'Kos Putra — Kamar Standar', 'description' => 'Kamar mandi dalam, akses 24 jam.', 'price' => 900000, 'price_note' => 'per bulan', 'category' => 'Kos Putra'],
                ['name' => 'Rumah Petak 2 Kamar', 'description' => 'Ruang tamu, dapur, halaman kecil. Cocok untuk keluarga muda.', 'price' => 2200000, 'price_note' => 'per bulan', 'category' => 'Rumah Sewa'],
                ['name' => 'Ruko 2 Lantai', 'description' => 'Pinggir jalan, cocok untuk usaha. Sewa tahunan.', 'price' => 45000000, 'price_note' => 'per tahun', 'category' => 'Ruko'],
            ],
        ];
    }

    /**
     * Foto portfolio contoh. Hanya untuk anak usaha yang memang memamerkan
     * hasil kerja (spec §3).
     *
     * @return array<string, list<array{image: string, caption: string}>>
     */
    public static function portfolioItems(): array
    {
        return [
            'nails-by-me' => [
                ['image' => 'images/placeholder/nail-1.webp', 'caption' => 'Nail art motif bunga, gel polish'],
                ['image' => 'images/placeholder/nail-2.webp', 'caption' => 'French tip dengan aksen emas'],
                ['image' => 'images/placeholder/nail-3.webp', 'caption' => 'Warna polos nude, kuku pendek'],
                ['image' => 'images/placeholder/nail-4.webp', 'caption' => 'Motif marmer, pengerjaan 2 jam'],
            ],
            'ngelash' => [
                ['image' => 'images/placeholder/lash-1.webp', 'caption' => 'Classic eyelash, hasil alami'],
                ['image' => 'images/placeholder/lash-2.webp', 'caption' => 'Volume eyelash untuk mata sipit'],
                ['image' => 'images/placeholder/lash-3.webp', 'caption' => 'Hybrid, lengkung C'],
            ],
        ];
    }
}
