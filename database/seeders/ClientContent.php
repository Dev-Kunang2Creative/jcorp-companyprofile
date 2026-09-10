<?php

namespace Database\Seeders;

/**
 * MATERI ASLI DARI CLIENT — bukan contoh, bukan karangan.
 *
 * Sengaja terpisah dari SampleContent. Perintah `php artisan jcorp:clear-samples`
 * membaca SampleContent untuk menentukan apa yang harus dihapus; berkas ini
 * tidak pernah disentuhnya. Kalau materi asli ditaruh di sana, suatu hari
 * perintah pembersih akan ikut menghapusnya.
 *
 * Aturan yang dipegang saat menyalin: HANYA yang benar-benar diberikan client.
 * Kolom yang tidak ada di materinya dikembalikan sebagai null (lihat
 * `emptyFor()`) — bukan diisi kalimat karangan supaya halamannya terlihat
 * penuh. Section yang datanya null memang tidak dirender (spec §3).
 *
 * Sumber: dua tangkapan layar dari client, 21 Agustus 2026 — profil (visi,
 * misi, about us, profil layanan) dan informasi kontak.
 */
class ClientContent
{
    /** Logo Sweetness — aset tetap di public/, hasil pemrosesan Fase 2. */
    public const SWEETNESS_LOGO = 'images/brand/sweetness-logo-600.webp';

    /** Logo induk — diproses 21 Agustus 2026 dari foto yang dikirim client. */
    public const JCORP_LOGO = 'images/brand/jcorp-logo-600.webp';

    /** Logo ngelash — warnanya dibalik dari sumber berlatar gelap (DESIGN_SYSTEM §8). */
    public const NGELASH_LOGO = 'images/brand/ngelash-logo-600.webp';

    /** Logo Nail's by Me — hijau tua aslinya dipertahankan, kontras 9,88:1. */
    public const NAILS_LOGO = 'images/brand/nails-logo-600.webp';

    /** Lambang Ayodya — dipisah dari kop surat berlatar biru (DESIGN_SYSTEM §14). */
    public const AYODYA_LOGO = 'images/brand/ayodya-logo-600.webp';

    /** Logo J-Land — sumbernya JPEG bersih, tinta rgb(36,36,36). */
    public const JLAND_LOGO = 'images/brand/jland-logo-600.webp';

    /**
     * Profil per anak usaha yang materinya sudah masuk.
     *
     * Anak usaha yang belum ada di sini belum mengirim materi; profilnya
     * tetap seperti sebelumnya dan tidak tersentuh seeder ini.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function businesses(): array
    {
        return [
            'jcorp' => [
                'name' => 'J-Corporate Group',

                'tagline' => 'Growing Together, Serving All',

                // "Tentang Kami" — dua paragraf sesuai pemisahan di materi.
                'description' => implode("\n\n", [
                    'J-Corporate Group adalah induk usaha yang menaungi lima unit bisnis dengan bidang yang berbeda-beda, mulai dari kuliner, kecantikan, hingga jasa logistik. Berangkat dari semangat membangun usaha yang saling melengkapi dalam satu payung manajemen, J-Corporate Group hadir untuk memberikan produk dan layanan yang berkualitas, konsisten, dan dekat dengan kebutuhan pelanggan sehari-hari.',
                    'Setiap unit usaha di bawah J-Corporate Group dikelola secara mandiri oleh tim yang fokus pada bidangnya masing-masing, namun tetap berpegang pada nilai dan standar yang sama sebagai satu keluarga besar J-Corporate.',
                ]),

                'vision' => 'Menjadi grup usaha keluarga yang tumbuh berkelanjutan dan terpercaya di berbagai bidang, serta memberi nilai tambah bagi pelanggan dan masyarakat di setiap lini bisnisnya.',

                'mission' => [
                    'Menghadirkan produk dan layanan berkualitas di setiap unit usaha.',
                    'Mengedepankan pelayanan yang ramah, cepat, dan dapat diandalkan.',
                    'Mengembangkan setiap unit bisnis secara profesional dan berkelanjutan.',
                    'Membuka peluang kerja sama dan kolaborasi yang saling menguntungkan.',
                ],

                'contact_note' => 'Kami membuka peluang kerja sama, kemitraan, maupun pertanyaan seputar layanan dari masing-masing unit usaha J-Corporate Group. Silakan hubungi kami melalui kontak berikut.',

                // Akun unit usaha, disalin dari materi induk.
                //
                // Materi induk menulis "@j-landproperty" — tanda hubung tidak
                // diterima Instagram, jadi sempat dilewati. Materi J-Land
                // sendiri kemudian memastikan akunnya bernama lain:
                // magerpindah.id, dan itu valid.
                //
                // PT. Ayodya Utama Logistic tetap belum ada — memang tidak
                // disebut di daftar yang dikirim client.
                'unit_socials' => [
                    ['label' => 'Sweetness Things', 'username' => 'sweetnessthings._'],
                    ['label' => "Nail's by Me", 'username' => 'nails.bymeeeeeee'],
                    ['label' => 'ngelash.id', 'username' => 'ngelash.id'],
                    ['label' => 'J-Land Property', 'username' => 'magerpindah.id'],
                ],

                // Kontak resmi induk BELUM dipasang. Di materinya:
                //
                //   Email   : [isi alamat email]   <- placeholder
                //   Alamat  : [isi alamat]         <- placeholder
                //   WhatsApp: 081176265            <- sembilan digit
                //
                // Nomor Indonesia paling pendek pun sebelas digit, jadi hampir
                // pasti ada yang terpotong saat mengetik. Dipasang sebagai
                // link wa.me, nomor keliru mengarah ke akun orang lain dan
                // tidak ada cara pengunjung tahu itu salah.
                //
                // Section kontaknya tetap tampil — kalimat pembuka dan daftar
                // Instagram unit usaha di atas sudah memberi pengunjung cara
                // menghubungi. Baris email/WA/alamat menyusul.
            ],

            'sweetness-things' => [
                // "ABOUT US" dari tangkapan layar. Dipecah dua paragraf di
                // batas yang ditulis client sendiri — di sumbernya tertulis
                // "…riuhnya hari.Setiap produk…" tanpa spasi, yang justru
                // menandai di situ pergantian paragrafnya.
                'description' => implode("\n\n", [
                    'Sweetnessthings adalah usaha kuliner lokal yang bergerak di bidang pembuatan dan penjualan aneka hidangan penutup (dessert). Berdiri dengan kehangatan dan kecintaan pada rasa manis, kami percaya bahwa dessert bukan sekadar makanan penutup, melainkan momen selebrasi kecil di tengah riuhnya hari.',
                    'Setiap produk Sweetnessthings dibuat dengan cara homemade menggunakan bahan-bahan berkualitas tinggi, tanpa pengawet buatan, dan diproses secara higienis untuk menghadirkan cita rasa yang manisnya pas, nikmat, serta memberikan kebahagiaan di setiap gigitan.',
                ]),

                'vision' => 'Menjadi brand dessert pilihan utama yang menghadirkan kebahagiaan lewat rasa manis berkualitas, terjangkau, dan bisa dinikmati oleh semua kalangan.',

                'mission' => [
                    'Menciptakan aneka makanan manis yang higienis, lezat, dan menggunakan bahan berkualitas.',
                    'Menyediakan dessert dengan harga terjangkau tanpa mengorbankan cita rasa.',
                    'Memberikan pelayanan yang ramah dan pengalaman menyenangkan bagi setiap pelanggan.',
                    'Terus berinovasi menciptakan varian rasa baru yang sesuai selera pasar.',
                    'Menjaga konsistensi rasa dan kualitas di setiap produk yang dijual.',
                ],

                // "PROFIL LAYANAN"
                'services' => [
                    [
                        'title' => 'Open pre-order',
                        'description' => 'Pembelian satuan atau paket untuk konsumsi pribadi. Memesan dan membayar lebih dulu, lalu menunggu proses produksi atau pengiriman selesai dalam jangka waktu tertentu.',
                    ],
                    [
                        'title' => 'Custom Hampers',
                        'description' => 'Paket bingkisan manis yang bisa disesuaikan dengan kebutuhan acara.',
                    ],
                ],

                // "Mengapa memilih sweetnessthings?"
                'highlights' => [
                    [
                        'title' => '100% Premium Ingredients',
                        'description' => 'Kami menggunakan bahan-bahan pilihan.',
                    ],
                    [
                        'title' => 'Manis yang Pas (Not Too Sweet)',
                        'description' => 'Resep kami disesuaikan agar tidak terlalu manis membuat eneg, sehingga cocok dinikmati semua kalangan.',
                    ],
                    [
                        'title' => 'Estetis & Gift-Ready',
                        'description' => 'Kemasan dipikirkan secara matang, cantik, dan siap dijadikan hadiah tanpa perlu dibungkus ulang.',
                    ],
                    [
                        'title' => 'Freshly Made',
                        'description' => 'Dibuat secara berkala untuk menjaga kesegaran dan cita rasa terbaik.',
                    ],
                ],

                // Nomor ditulis client sebagai 0819-3802-0815 dan
                // 0881-3742-352. Dinormalkan ke bentuk yang diterima wa.me
                // (62 + angka, tanpa pemisah) karena dipakai membentuk link,
                // bukan sekadar ditampilkan — yang tampil di layar tetap
                // bentuk 0819-… lewat formatWhatsappDisplay().
                'whatsapp' => '6281938020815',
                'whatsapp_alt' => '628813742352',

                // Tanpa @ — ContactSection yang menambahkannya saat menampilkan.
                'instagram' => 'sweetnessthings._',
                'tiktok' => 'sweetnessthings',

                // Tangkapan layar hanya menyebut kota, di bawah nama usaha.
                'address' => 'Semarang',

                // "Pre-order only by chat whatsapp / Direct message (DM) Instagram"
                'contact_note' => 'Pemesanan hanya lewat chat WhatsApp atau DM Instagram.',

                'catalog_label' => 'Menu Kami',
            ],

            'ngelash' => [
                // Kalimat penutup materi dipakai sebagai tagline hero —
                // memang ditulis client sebagai slogan, lengkap dengan
                // tanda kutipnya.
                'tagline' => 'Enhance Your Beauty, Elevate Your Confidence.',

                // "Tentang Kami" — empat paragraf, sesuai pemisahan di
                // materi. Kalimat slogan di akhir tidak diulang di sini
                // karena sudah jadi tagline.
                'description' => implode("\n\n", [
                    'Ngelash.id adalah beauty service yang hadir untuk membantu setiap perempuan tampil lebih cantik, percaya diri, dan merasa nyaman dengan dirinya sendiri melalui layanan eyelash yang berkualitas.',
                    'Kami percaya bahwa kecantikan tidak selalu harus berlebihan. Dengan teknik eyelash yang rapi, ringan, dan disesuaikan dengan karakter serta kebutuhan setiap pelanggan, Ngelash.id menghadirkan tampilan mata yang lebih lentik, indah, dan tetap terlihat natural.',
                    'Kami mengutamakan kualitas, kebersihan, kenyamanan, dan kepuasan pelanggan dalam setiap treatment. Setiap proses dikerjakan dengan penuh ketelitian agar pelanggan mendapatkan hasil yang tidak hanya cantik, tetapi juga nyaman digunakan.',
                    'Bagi kami, eyelash bukan sekadar mempercantik tampilan, tetapi juga menjadi bagian dari perjalanan untuk meningkatkan rasa percaya diri dan self-love.',
                ]),

                'vision' => 'Menjadi usaha eyelash yang terpercaya dan unggul dalam memberikan layanan kecantikan berkualitas, dengan mengutamakan hasil yang natural, elegan, nyaman, dan sesuai dengan karakter setiap pelanggan.',

                'mission' => [
                    'Memberikan pelayanan terbaik dengan mengutamakan keramahan, kenyamanan, dan kepuasan pelanggan.',
                    'Menghasilkan eyelash yang berkualitas dengan teknik pemasangan yang rapi, aman, tahan lama, dan sesuai dengan kebutuhan pelanggan.',
                    'Mengutamakan kecantikan yang natural dan personal, sehingga setiap pelanggan dapat memiliki tampilan mata yang lebih menarik tanpa kehilangan karakter alaminya.',
                    'Menjaga standar kebersihan dan keamanan dalam setiap proses treatment untuk memberikan pengalaman yang higienis dan nyaman.',
                    'Terus meningkatkan keterampilan dan inovasi dalam mengikuti tren serta perkembangan teknik eyelash dan industri beauty.',
                    'Membangun kepercayaan dan hubungan jangka panjang dengan pelanggan melalui pelayanan yang konsisten dan profesional.',
                    'Menciptakan pengalaman beauty yang menyenangkan, sehingga pelanggan tidak hanya mendapatkan hasil yang cantik, tetapi juga merasa lebih percaya diri setelah melakukan treatment.',
                ],

                // "Layanan Kami" — satu butir, karena materinya memang
                // hanya memuat kalimat pengantar tanpa rincian layanan.
                //
                // Daftar layanan beserta harganya BELUM ADA dari client.
                // Enam layanan karangan yang sempat tayang di halaman ini
                // (Classic Rp150rb, Volume Rp220rb, dst) dihapus — lihat
                // catatan di ClientContentSeeder. Keunggulan ke-7 justru
                // menjanjikan "Harga Transparan", jadi harga karangan di
                // bawah janji itu merusak kepercayaan begitu pelanggan
                // menanyakannya dan angkanya ternyata berbeda.
                'services' => [
                    [
                        'title' => 'Eyelash Extension',
                        'description' => 'Berbagai pilihan layanan eyelash extension, mulai dari tampilan natural hingga volume dramatis, yang dapat disesuaikan dengan kebutuhan dan gaya setiap pelanggan.',
                    ],
                ],

                // "Mengapa Memilih Kami" — tujuh butir.
                'highlights' => [
                    [
                        'title' => 'Beauticians Berpengalaman',
                        'description' => 'Tenaga profesional yang berpengalaman dan selalu mengikuti perkembangan teknik eyelash terbaru.',
                    ],
                    [
                        'title' => 'Material & Alat Berkualitas',
                        'description' => 'Menggunakan primer, lash bound, dan bahan berkualitas dalam setiap treatment untuk hasil yang rapi, tahan lama, dan aman untuk mata.',
                    ],
                    [
                        'title' => 'Hasil Natural dan Personal',
                        'description' => 'Desain eyelash disesuaikan dengan bentuk mata dan karakter wajah setiap pelanggan, dari tampilan natural hingga dramatis.',
                    ],
                    [
                        'title' => 'Higienis dan Aman',
                        'description' => 'Ruang dan alat treatment dijaga kebersihannya di setiap sesi demi kenyamanan dan keamanan pelanggan.',
                    ],
                    [
                        'title' => 'Home Service Tersedia',
                        'description' => 'Tersedia layanan datang ke rumah pelanggan untuk kemudahan dan fleksibilitas waktu.',
                    ],
                    [
                        'title' => 'Booking Praktis via WhatsApp',
                        'description' => 'Reservasi mudah melalui WhatsApp dengan sistem janji temu, tanpa harus menunggu lama.',
                    ],
                    [
                        'title' => 'Harga Transparan',
                        'description' => 'Setiap harga treatment sudah termasuk primer, lash bound, dan free spoolie — tidak ada biaya tersembunyi.',
                    ],
                ],

                // "WhatsApp: 085156886646 (WhatsApp only)" — dinormalkan
                // ke bentuk wa.me. Dua belas digit, formatnya benar.
                'whatsapp' => '6285156886646',

                // Materi ngelash tidak menyebut media sosial sama sekali,
                // tapi materi INDUK mencantumkan @ngelash.id di daftar
                // akun unit usaha — jadi akun itu memang ada.
                //
                // TikTok tidak dipasang: tidak ada di materi mana pun.
                'instagram' => 'ngelash.id',

                'address' => 'Kios G-09, Apartment Candiland',

                'contact_note' => 'Reservasi lewat WhatsApp dengan sistem janji temu (by appointment). Tersedia juga home service.',

                'catalog_label' => 'Layanan & Harga',
                'catalog_note' => 'Biaya sudah termasuk Primer + Lash Bound + Free Spoolie.',
                'portfolio_label' => 'Hasil Kerja',
            ],

            'nails-by-me' => [
                'description' => "Nail's By Me merupakan usaha yang bergerak di bidang jasa nail art dengan komitmen menghadirkan hasil yang indah, rapi, dan tahan lama dengan harga yang terjangkau. Sebelum proses pengaplikasian nail art, setiap pelanggan akan mendapatkan perawatan manicure kutikula serta vitamin kutikula guna menjaga kesehatan kuku dan memastikan hasil akhir bertahan lebih dari 2-3 minggu. Melalui pelayanan yang profesional dan penggunaan bahan berkualitas, Nail's By Me berupaya menjadi mitra kepercayaan pelanggan dalam merawat keindahan kuku.",

                'vision' => 'Menjadi penyedia jasa nail art terpercaya yang menghadirkan hasil berkualitas tinggi dan tahan lama dengan harga yang terjangkau bagi masyarakat luas.',

                // Materi client menomori 1, 3, 4 — nomor 2 memang tidak ada.
                // Disalin apa adanya sebagai tiga poin; penomorannya dibuat
                // ulang saat dirender, jadi lompatan itu tidak ikut tampil.
                'mission' => [
                    'Menyediakan layanan nail art dengan harga kompetitif tanpa mengurangi kualitas hasil akhir.',
                    'Memberikan pelayanan yang profesional, higienis, dan nyaman bagi setiap pelanggan.',
                    'Mengikuti perkembangan tren nail art terkini guna memenuhi kebutuhan dan preferensi pelanggan.',
                ],

                // "Profil Layanan" — apa yang dikerjakan.
                'services_label' => 'Profil Layanan',
                'services' => [
                    [
                        'title' => 'Nail Art Custom',
                        'description' => 'Layanan nail art sesuai preferensi dan kebutuhan pelanggan, dari desain simpel hingga detail rumit.',
                    ],
                    [
                        'title' => 'Manicure Kutikula & Vitamin',
                        'description' => 'Perawatan dasar sebelum pengaplikasian nail art, serta pemberian vitamin kutikula untuk menjaga kesehatan kuku dan daya tahan hasil akhir.',
                    ],
                    [
                        'title' => 'Free Garansi Klaim 7 Hari',
                        'description' => 'Garansi gratis berlaku 7 hari terhitung sejak tanggal pengerjaan. Jika ada chip atau kerusakan dalam periode tersebut, pelanggan bisa klaim perbaikan tanpa dikenakan biaya.',
                    ],
                ],

                // "Mengapa memilih Nail's By Me?"
                'highlights' => [
                    [
                        'title' => '100% Perawatan Higienis',
                        'description' => 'Setiap pelanggan mendapat perawatan manicure kutikula serta vitamin kutikula.',
                    ],
                    [
                        'title' => 'Tahan Lama (2–4 Minggu)',
                        'description' => 'Teknik pengerjaan dan produk terbaik memastikan hasil nail art bertahan lebih dari 2-4 minggu.',
                    ],
                    [
                        'title' => 'Harga Terjangkau, Kualitas Premium',
                        'description' => 'Layanan kompetitif tanpa mengurangi kualitas hasil akhir.',
                    ],
                    [
                        'title' => 'Free Garansi Klaim 7 Hari',
                        'description' => 'Jika terjadi kerusakan dalam 7 hari setelah pengerjaan, klaim garansi tanpa biaya tambahan.',
                    ],
                    [
                        'title' => 'Mengikuti Tren Terkini',
                        'description' => 'Desain selalu update mengikuti perkembangan tren nail art.',
                    ],
                ],

                // NOMOR INI SAMA PERSIS dengan nomor Sekar di J-Land
                // Property (0812-2553-9182). Bisa jadi memang satu orang
                // yang mengurus dua unit usaha — tapi BELUM DIKONFIRMASI ke
                // client, jadi jangan dianggap sudah beres.
                //
                // Dipasang apa adanya karena formatnya benar dan itu yang
                // tertulis di materi. Kalau ternyata salah salin, cukup
                // ganti di sini.
                //
                // "(chat only)" jadi bagian catatan kontak di bawah.
                'whatsapp' => '6281225539182',

                'instagram' => 'nails.bymeeeeeee',
                'tiktok' => 'nails.bymeeeeeee',

                'contact_note' => 'Pemesanan lewat chat WhatsApp dengan sistem janji temu (appointment only), dan tersedia home service.',

                'catalog_label' => 'Layanan & Harga',
                'catalog_note' => 'Harga di atas berlaku untuk tangan atau kaki, serta bebas pilih warna sesuka hati.',
                'portfolio_label' => 'Hasil Kerja',
            ],

            /*
             * PT. Ayodya Utama Logistic.
             *
             * CATATAN EJAAN: materi client memakai DUA ejaan — "AYODYA" di
             * judul dan nama perusahaan, "Ayudya" di kalimat penutup serta
             * di alamat web dan email (ayudyalogistic.com,
             * ayudyalogistic@gmail.com).
             *
             * Keduanya disalin APA ADANYA atas keputusan pemilik project:
             * itu data asli dari client, dan alamat web maupun email tidak
             * boleh "dibetulkan" — kalau ejaannya diubah, tautannya mengarah
             * ke domain yang tidak ada dan email tidak sampai.
             */
            'ayodya-logistic' => [
                'tagline' => 'International Freight Forwarding & Global Logistic',

                'description' => implode("\n\n", [
                    'Perkembangan dunia usaha yang semakin pesat memerlukan dukungan sarana dan prasarana guna mendukung keberhasilan perusahaan. Kecepatan dan ketepatan dalam pendistribusian barang menjadi salah satu tolak ukur perusahaan agar dapat memenangkan persaingan dalam kompetisi di era globalisasi ini.',
                    'PT Ayudya Utama Logistic adalah perusahaan penyedia jasa layanan kurir domestik, kargo, dan angkutan truk (logistic trucking provider) yang beroperasi di wilayah Semarang, Jawa Tengah. Kami menawarkan jasa pengiriman barang perusahaan Anda, baik domestik maupun luar negeri (export-import), dengan beberapa mode transportasi.',
                    'Kami didukung oleh tenaga-tenaga yang profesional dan handal di bidangnya, serta para partner yang mempunyai komitmen untuk memberikan pelayanan yang bermutu dan handal.',
                ]),

                'vision' => 'Menjadi perusahaan jasa tingkat nasional yang terpercaya yang berorientasi pada kepuasan pelanggan dan pemegang saham.',

                // Materi menulis misi sebagai satu kalimat berkoma; dipecah
                // jadi tiga poin sesuai jumlah sasarannya, tanpa menambah
                // atau mengubah kata.
                'mission' => [
                    'Menghasilkan keuntungan bagi pemegang saham.',
                    'Memberikan layanan purna pada stake holder.',
                    'Meningkatkan kesejahteraan pada karyawan dan keluarga.',
                ],

                // Empat mode transportasi — inti penawaran mereka. Uraian
                // panjang di materi dipadatkan tanpa membuang informasi yang
                // menentukan keputusan pembeli: jenis armada, nama pelayaran
                // dan penerbangan yang bekerja sama, serta cakupan rutenya.
                'featured_services' => [
                    [
                        'title' => 'Land Transportation',
                        'description' => 'Pengiriman darat dengan armada sendiri, didukung rekan-rekan transportasi. Armada tersedia: truck trailer, CDD box, fuso box, wing box, dan pickup box. Tersedia asuransi untuk keamanan barang.',
                    ],
                    [
                        'title' => 'Sea Transportation',
                        'description' => 'Bertindak sebagai forwarding untuk pengiriman domestik dan luar negeri memakai container, dengan harga kompetitif. Bekerja sama dengan pelayaran internasional (Maersk Line, NYK Line, Mitsui Line) dan nasional (Meratus, Samudera). Termasuk EMKL dan jasa pengurusan kepabeanan.',
                    ],
                    [
                        'title' => 'Air Transportation',
                        'description' => 'Untuk barang yang dibutuhkan cepat. Domestik dari bandara Semarang, Solo, dan Yogyakarta ke seluruh bandara di Indonesia; luar negeri dari Semarang. Didukung Garuda, Citilink, Lion, AirAsia, dan Trigana — Garuda untuk rute internasional.',
                    ],
                    [
                        'title' => 'Biz Service',
                        'description' => 'Menjadi partner kegiatan operasional lapangan perusahaan Anda, dari pemasangan barcode merchant sampai survei kondisi pasar. Pernah dikerjakan untuk OVO dan Danaku di seluruh Jawa Tengah. Termasuk jasa pindahan rumah dan kantor, dalam maupun luar negeri.',
                    ],
                ],

                // "PROFIL LAYANAN"
                'services_label' => 'Profil Layanan',
                'services' => [
                    [
                        'title' => 'Kurir Domestik & Kargo',
                        'description' => 'Layanan kurir domestik dan pengiriman kargo.',
                    ],
                    [
                        'title' => 'Angkutan Truk (Trucking)',
                        'description' => 'Penyedia angkutan truk logistik.',
                    ],
                    [
                        'title' => 'Cakupan Operasional',
                        'description' => 'Area operasional utama di sekitar Semarang dan Jawa Tengah.',
                    ],
                ],

                // "HP : 0811276265" — dinormalkan ke bentuk wa.me. Nomor
                // ini juga yang tercantum sebagai kontak induk di materi
                // J-Corporate (081176265, sembilan digit) — kemungkinan
                // besar nomor yang sama, dan versi induk yang terpotong.
                // Belum dikonfirmasi, jadi keduanya dibiarkan apa adanya.
                'whatsapp' => '62811276265',
                'whatsapp_label' => 'Johan Setiadi',

                'address' => 'Ruko Kuala Mas III No. 1A, Tanah Mas, Semarang',

                // Telepon kantor, fax, email, dan web tidak punya kolom
                // sendiri di skema ini — semuanya digabung ke catatan kontak
                // supaya tidak ada informasi client yang hilang.
                //
                // Ejaan "ayudyalogistic" pada web dan email DIPERTAHANKAN
                // apa adanya: itu alamat sesungguhnya, bukan salah tulis
                // yang boleh dibetulkan.
                'contact_note' => 'PIC: Johan Setiadi Agung Nugroho — HP/WhatsApp 0811276265. Telepon kantor 024-3511609, fax 024-3511610. Email johansetiady@yahoo.com atau ayudyalogistic@gmail.com, situs www.ayudyalogistic.com.',

                'catalog_label' => 'Layanan Kami',
            ],

            'j-land-property' => [
                'name' => 'J-Land Property',

                'description' => 'J-Land Property adalah perusahaan yang bergerak di bidang properti, melayani jasa persewaan kost, persewaan rumah, dan jual beli rumah. Kami hadir untuk menjadi solusi hunian yang aman, nyaman, dan terpercaya bagi masyarakat, baik untuk kebutuhan tempat tinggal sementara maupun investasi properti jangka panjang. Dengan mengedepankan pelayanan yang ramah dan transparan, J-Land Property berkomitmen membantu setiap klien menemukan properti yang sesuai dengan kebutuhan dan anggaran mereka.',

                'vision' => 'Menjadi perusahaan properti terpercaya dan terdepan dalam menyediakan layanan persewaan kost, rumah, serta jual beli rumah yang berkualitas, dengan mengutamakan kepuasan dan kepercayaan pelanggan.',

                'mission' => [
                    'Menyediakan pilihan kost dan rumah sewa yang nyaman, bersih, dan sesuai standar kelayakan huni.',
                    'Memberikan layanan jual beli rumah yang jujur, transparan, dan menguntungkan kedua belah pihak.',
                    'Membangun hubungan jangka panjang dengan pelanggan melalui pelayanan yang profesional dan responsif.',
                    'Terus berinovasi dalam pengelolaan properti agar sesuai dengan perkembangan kebutuhan pasar.',
                    'Menjadi mitra terpercaya bagi pemilik properti dalam mengelola dan memasarkan aset mereka secara optimal.',
                ],

                // "Layanan Unggulan (Our Property Services)" — APA yang
                // dijual. Ditempatkan sebelum "Mengapa Memilih" sesuai
                // urutan di dokumen client.
                'featured_services' => [
                    [
                        'title' => 'Sewa Kost',
                        'description' => 'Pilihan kamar kost nyaman dengan fasilitas lengkap, cocok untuk pelajar, mahasiswa, maupun karyawan. Tersedia tipe kost khusus putra/putri dengan berbagai pilihan harga.',
                    ],
                    [
                        'title' => 'Sewa Rumah',
                        'description' => 'Rumah sewa siap huni dengan berbagai pilihan lokasi strategis, ukuran, dan harga sesuai kebutuhan keluarga maupun individu.',
                    ],
                    [
                        'title' => 'Jual Rumah',
                        'description' => 'Layanan jual beli rumah dengan proses transparan, harga kompetitif, dan bantuan legalitas dari awal hingga akad.',
                    ],
                ],

                // "Mengapa Memilih J-Land Property?"
                'highlights' => [
                    [
                        'title' => 'Lokasi Strategis',
                        'description' => 'Properti kami tersebar di lokasi-lokasi strategis yang dekat dengan kampus, perkantoran, dan pusat kota.',
                    ],
                    [
                        'title' => 'Harga Transparan (No Hidden Cost)',
                        'description' => 'Semua biaya disampaikan secara jelas di awal tanpa biaya tersembunyi.',
                    ],
                    [
                        'title' => 'Legalitas Terjamin',
                        'description' => 'Setiap transaksi jual beli dan sewa didukung dokumen yang lengkap dan sah secara hukum.',
                    ],
                    [
                        'title' => 'Pelayanan Responsif',
                        'description' => 'Tim kami siap membantu survei, konsultasi, hingga proses akad dengan cepat dan ramah.',
                    ],
                ],

                // "Layanan Kami" — BAGAIMANA prosesnya, dari survei sampai
                // serah terima kunci. Judulnya mengikuti dokumen client,
                // bukan bawaan "Cara Pemesanan".
                'services_label' => 'Layanan Kami',
                'services' => [
                    [
                        'title' => 'Survei & Konsultasi Properti',
                        'description' => 'Calon penyewa atau pembeli dapat melakukan survei langsung ke lokasi didampingi tim kami untuk memastikan properti sesuai kebutuhan, lalu berkonsultasi mengenai harga dan proses selanjutnya.',
                    ],
                    [
                        'title' => 'Sewa Tahunan/Bulanan',
                        'description' => 'Layanan sewa kost dan rumah dengan pilihan durasi bulanan maupun tahunan, disesuaikan dengan kebutuhan penyewa.',
                    ],
                    [
                        'title' => 'Jual Beli Rumah',
                        'description' => 'Proses jual beli rumah dari negosiasi harga, pengecekan legalitas, hingga serah terima kunci, didampingi penuh oleh tim J-Land Property.',
                    ],
                ],

                // Dua nomor, masing-masing dengan nama pemiliknya — supaya
                // pengunjung tahu siapa yang dihubungi sebelum mengirim
                // pesan. Dinormalkan ke bentuk wa.me.
                'whatsapp' => '6281225539182',
                'whatsapp_label' => 'Sekar',
                'whatsapp_alt' => '6282226256525',
                'whatsapp_alt_label' => 'Bu Agung',

                // Bukan "j-landproperty" seperti yang tertulis di materi
                // induk — akun sesungguhnya bernama lain, dan yang ini
                // valid sebagai username Instagram.
                'instagram' => 'magerpindah.id',

                'address' => 'Jalan Rorojonggrang VIII Nomor 08',
                'business_hours' => '24 jam',

                'catalog_label' => 'Unit Tersedia',
            ],
        ];
    }

    /**
     * Kolom yang HARUS kosong karena client tidak memberikannya.
     *
     * Diperlukan karena database sudah lebih dulu terisi teks contoh: tagline
     * dan jam buka karangan sudah ada di sana. Tanpa daftar ini keduanya
     * tertinggal dan ikut tayang berdampingan dengan materi asli.
     *
     * Mengosongkannya membuat section-nya hilang dari halaman — itu memang
     * yang benar: lebih baik tidak ada daripada mengarang jam buka yang bisa
     * membuat pembeli datang di waktu yang salah.
     *
     * @return array<string, list<string>>
     */
    public static function emptyFor(): array
    {
        return [
            // Induk sempat terisi kontak karangan lengkap dari data contoh —
            // nomor 6281200000000, alamat "Jl. Contoh Utama No. 1, Bekasi",
            // Instagram jcorp.id. Semuanya harus hilang: tidak satu pun ada
            // di materi client.
            'jcorp' => [
                'whatsapp',
                'instagram',
                'address',
                'business_hours',
            ],

            'sweetness-things' => [
                'tagline',
                'business_hours',
            ],

            // TikTok ngelash murni karangan — tidak ada di materi mana pun,
            // dan tautan ke akun yang belum tentu ada terlihat tidak
            // profesional. Jam buka juga tidak disebutkan: layanannya by
            // appointment, jadi jam buka tetap malah menyesatkan.
            'ngelash' => [
                'tiktok',
                'business_hours',
            ],

            // Tagline dan jam buka karangan harus hilang: materi Nail's by Me
            // tidak menyebut keduanya, dan layanannya by appointment — jam
            // buka tetap justru menyesatkan.
            'nails-by-me' => [
                'tagline',
                'address',
                'business_hours',
            ],

            // Instagram karangan ("ayodyalogistic") harus hilang: materi
            // client tidak menyebut media sosial sama sekali, dan daftar
            // akun di halaman induk pun tidak memuat Ayodya. Jam buka juga
            // tidak disebutkan.
            'ayodya-logistic' => [
                'instagram',
                'tiktok',
                'business_hours',
            ],

            // Tagline dan Instagram karangan ("lumintuproperty") harus
            // hilang — Instagram sesungguhnya diisi dari materi client.
            'j-land-property' => [
                'tagline',
                'tiktok',
            ],
        ];
    }

    /**
     * Produk asli per anak usaha.
     *
     * Harga sengaja null: client belum mengirimkan daftar harga. Kartunya
     * menampilkan "hubungi kami" tanpa angka — bukan "Rp 0", dan bukan angka
     * karangan yang bisa membuat pembeli merasa ditipu saat menanyakannya.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function catalogItems(): array
    {
        return [
            'sweetness-things' => [
                [
                    'name' => 'Dessert Box Series',
                    // Ejaan dua varian dibetulkan dari sumber atas persetujuan
                    // pemilik project: "kunnaffa" → Kunafa, "chesse" → Cheese.
                    'description' => 'Lapisan fudgy brownies yang lembut, krim lumer, dan topping berlimpah yang disajikan dalam box praktis. Tersedia varian Pistachio Kunafa, Chocolate, Oreo, dan Strawberry Cheese.',
                    'price' => null,
                    'price_note' => 'hubungi kami',
                ],
                [
                    'name' => 'Soft Cookies',
                    'description' => 'Soft cookies dengan isian melt yang berlimpah.',
                    'price' => null,
                    'price_note' => 'hubungi kami',
                ],
            ],

            /*
             * Daftar harga Nail's by Me dari materi client 10 September 2026.
             *
             * Foto sumber hanya menjadi acuan data dan tidak dipublikasikan.
             * Halaman Nail's menampilkannya sebagai tabel dua kolom. Ejaan
             * "Frech" pada materi dinormalkan menjadi istilah layanan yang
             * umum, "French"; angka serta satuan harga tetap disalin apa
             * adanya.
             */
            'nails-by-me' => [
                [
                    'name' => 'Nail Art Polos',
                    'description' => 'Manicure kutikula, vitamin kutikula, dan hand body.',
                    'price' => 50000,
                ],

                ['name' => 'Nail Art Ombre', 'price' => 5000, 'price_note' => 'per kuku', 'category' => 'Add ++'],
                ['name' => 'Nail Art Glitter', 'price' => 3000, 'price_note' => 'per kuku', 'category' => 'Add ++'],
                ['name' => 'Nail Art Marble', 'price' => 5000, 'price_note' => 'per kuku', 'category' => 'Add ++'],
                ['name' => 'Nail Art Cat Eye', 'price' => 6000, 'price_note' => 'per kuku', 'category' => 'Add ++'],
                ['name' => 'Nail Art French', 'price' => 5000, 'price_note' => 'per kuku', 'category' => 'Add ++'],
                ['name' => 'Nail Art Motif', 'price' => null, 'price_note' => 'Rp 5.000–25.000 per kuku', 'category' => 'Add ++'],

                ['name' => 'Fake Nails', 'price' => 3500, 'price_note' => 'per kuku', 'category' => 'Lainnya'],
                ['name' => 'Accessories', 'price' => null, 'price_note' => 'Rp 1.000–15.000 per kuku', 'category' => 'Lainnya'],
                ['name' => 'Remove Nail Art', 'price' => 30000, 'category' => 'Lainnya'],
            ],

            /*
             * Daftar harga ngelash — 15 layanan dalam 6 kategori.
             *
             * PERHATIAN: "Natural", "Medium", dan "Volume" muncul DUA KALI,
             * di 2D LASH dan 3D LASH, dengan harga berbeda. Karena itu
             * ClientContentSeeder mencocokkan item dari nama DAN kategori —
             * dengan nama saja, tarif 2D akan tertimpa tarif 3D.
             *
             * Deskripsi sengaja kosong: dokumen client hanya memuat nama dan
             * harga. Mengarang keterangan layanan kecantikan berisiko keliru
             * secara teknis.
             */
            'ngelash' => [
                ['name' => 'Single', 'price' => 75000, 'category' => 'Single / Double'],
                ['name' => 'Double', 'price' => 85000, 'category' => 'Single / Double'],

                ['name' => 'Natural', 'price' => 80000, 'category' => '2D Lash'],
                ['name' => 'Medium', 'price' => 90000, 'category' => '2D Lash'],
                ['name' => 'Volume', 'price' => 100000, 'category' => '2D Lash'],

                ['name' => 'Natural', 'price' => 85000, 'category' => '3D Lash'],
                ['name' => 'Medium', 'price' => 95000, 'category' => '3D Lash'],
                ['name' => 'Volume', 'price' => 105000, 'category' => '3D Lash'],

                ['name' => 'Anime', 'price' => 125000, 'category' => 'Special Lash'],
                ['name' => 'Wishpy', 'price' => 130000, 'category' => 'Special Lash'],
                ['name' => 'Mega Volume', 'price' => 140000, 'category' => 'Special Lash'],

                ['name' => 'Dari Ngelash.id', 'price' => 20000, 'category' => 'Remove'],
                ['name' => 'Dari tempat lain', 'price' => 25000, 'category' => 'Remove'],

                ['name' => 'Single – 3D', 'price' => 60000, 'category' => 'Retouch'],
                ['name' => 'Special Lash', 'price' => 65000, 'category' => 'Retouch'],
            ],
        ];
    }

    /**
     * Anak usaha yang memamerkan hasil kerjanya (spec §3).
     *
     * Sakelarnya dinyalakan walau fotonya belum ada: section-nya sudah
     * hilang sendiri selama kosong, dan admin bisa langsung mengunggah
     * tanpa perlu menyalakan apa pun lebih dulu.
     *
     * Dulu diurus SampleContentSeeder, tapi seeder itu kini melewati anak
     * usaha yang materi aslinya sudah masuk — jadi keputusannya ikut pindah
     * ke sini, ke berkas yang sama dengan materinya.
     *
     * @return list<string>
     */
    public static function withPortfolio(): array
    {
        return ['ngelash', 'nails-by-me'];
    }

    /**
     * Warna aksen per anak usaha (DESIGN_SYSTEM §2.3, arah A+B).
     *
     * Diambil dari logo masing-masing, lalu DITUAKAN sampai lolos WCAG AA
     * sebagai teks di atas permukaan kartu #FAF9F7. Warna logo apa adanya
     * hampir selalu terlalu terang untuk itu — merah Ayodya rgb(227,43,43)
     * hanya 3,95, di bawah syarat 4,5.
     *
     * Rasio terukur di permukaan kartu:
     *
     *   jcorp             #7C5F33   5,64
     *   sweetness-things  #6B4F0F   7,26
     *   ngelash           #7A5C12   5,93
     *   nails-by-me       #2A4104  10,75
     *   ayodya-logistic   #9E1B21   7,58
     *   j-land-property   #14488C   8,55
     *
     * Kalau menambah warna baru: UKUR dulu, jangan dikira. Ada test yang
     * memeriksa bentuknya, tapi tidak ada yang memeriksa kontrasnya.
     *
     * @return array<string, string>
     */
    public static function accentColors(): array
    {
        return [
            'jcorp' => '#7C5F33',            // kuningan — netral, induk
            'sweetness-things' => '#6B4F0F', // emas
            'ngelash' => '#7A5C12',          // emas tua
            'nails-by-me' => '#2A4104',      // hijau tua
            'ayodya-logistic' => '#9E1B21',  // merah
            'j-land-property' => '#14488C',  // biru
        ];
    }

    /**
     * Logo yang berkasnya sudah ada di public/images/brand/.
     *
     * @return array<string, string>
     */
    public static function logos(): array
    {
        return [
            'jcorp' => self::JCORP_LOGO,
            'sweetness-things' => self::SWEETNESS_LOGO,
            'ngelash' => self::NGELASH_LOGO,
            'nails-by-me' => self::NAILS_LOGO,
            'ayodya-logistic' => self::AYODYA_LOGO,
            'j-land-property' => self::JLAND_LOGO,
        ];
    }
}
