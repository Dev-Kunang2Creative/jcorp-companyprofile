<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom profil tambahan, mengikuti bentuk materi yang dikirim client.
 *
 * Template yang dipakai client sama untuk kelima anak usaha: visi, misi,
 * about us, profil layanan, keunggulan, dan kontak. Empat di antaranya belum
 * punya tempat di skema — sebelumnya hanya ada `tagline` dan `description`.
 *
 * Misi dan keunggulan bentuknya DAFTAR. Dijejalkan ke satu kolom teks,
 * keduanya kehilangan bentuknya dan terbaca sebagai satu blok panjang —
 * terutama sulit di layar HP.
 *
 * Kenapa `json` dan bukan tabel terpisah: isinya daftar teks pendek yang
 * selalu dibaca sekaligus dengan profilnya. Tidak pernah dicari, disaring,
 * atau diurutkan sendiri. Tabel terpisah berarti tiga tabel, tiga model, dan
 * tiga policy tambahan tanpa manfaat yang sepadan.
 *
 * Semuanya nullable: anak usaha yang materinya belum masuk tidak terpengaruh,
 * dan section-nya tidak dirender sama sekali (spec §3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Satu kalimat, ditampilkan sebagai kutipan.
            $table->text('vision')->nullable()->after('description');

            // Daftar kalimat: ["Menciptakan…", "Menyediakan…"]
            $table->json('mission')->nullable()->after('vision');

            // Daftar objek: [{"title": "…", "description": "…"}]
            //
            // `featured_services` = APA yang dijual (Sewa Kost, Sewa Rumah).
            // `services`          = BAGAIMANA prosesnya (survei → akad).
            // `highlights`        = KENAPA memilih usaha ini.
            //
            // Ketiganya bentuknya sama tapi menjawab pertanyaan berbeda, dan
            // sebagian usaha memang memberi ketiganya sekaligus.
            $table->json('featured_services')->nullable()->after('mission');
            $table->json('services')->nullable()->after('featured_services');
            $table->json('highlights')->nullable()->after('services');

            // Judul section layanan. Bawaannya "Cara Pemesanan" — tepat untuk
            // usaha yang menerangkan alur memesan, tapi sebagian menyebutnya
            // "Layanan Kami" karena isinya daftar layanan, bukan prosedur.
            $table->string('services_label', 50)->nullable()->after('highlights');

            // Sebagian usaha memberi dua nomor. Divalidasi sama ketatnya
            // dengan `whatsapp` karena sama-sama dipakai membentuk link wa.me.
            $table->string('whatsapp_alt')->nullable()->after('whatsapp');

            // Nama pemilik nomor ("Sekar", "Bu Agung"). Kalau diisi, baris
            // kontaknya jadi "WhatsApp (Sekar)" — pengunjung tahu siapa yang
            // dihubungi sebelum mengirim pesan.
            $table->string('whatsapp_label', 50)->nullable()->after('whatsapp');
            $table->string('whatsapp_alt_label', 50)->nullable()->after('whatsapp_alt');

            // Daftar akun Instagram unit usaha, ditampilkan di halaman induk.
            //
            // Diambil dari materi induk, BUKAN dikumpulkan otomatis dari
            // kolom `instagram` tiap anak usaha — sebagian anak usaha masih
            // berisi username contoh, dan mengumpulkannya otomatis membuat
            // akun karangan itu tampil sebagai tautan yang bisa diklik.
            //
            // Bentuknya [{"label": "...", "username": "..."}] supaya nama
            // unit usaha bisa ditulis apa adanya menurut client.
            $table->json('unit_socials')->nullable()->after('tiktok');

            // Catatan di bawah daftar harga — apa yang sudah termasuk,
            // syarat, atau keterangan lain yang berlaku untuk SELURUH item
            // ("Biaya sudah termasuk Primer + Lash Bound + Free Spoolie").
            //
            // Ditaruh dekat harganya, bukan di bagian lain: pengunjung yang
            // sedang membaca tarif perlu langsung tahu apa yang termasuk.
            $table->string('catalog_note', 500)->nullable()->after('catalog_label');

            // Kalimat pembuka section kontak, menurut usahanya sendiri.
            // Isinya berbeda menurut peran: anak usaha menerangkan cara
            // memesan ("hanya lewat chat WhatsApp atau DM Instagram"),
            // induk menerangkan peluang kerja sama. Karena itu namanya
            // netral — bukan `order_note`, yang keliru untuk induk.
            $table->string('contact_note', 500)->nullable()->after('business_hours');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'vision',
                'mission',
                'featured_services',
                'services',
                'services_label',
                'highlights',
                'whatsapp_label',
                'whatsapp_alt',
                'whatsapp_alt_label',
                'unit_socials',
                'catalog_note',
                'contact_note',
            ]);
        });
    }
};
