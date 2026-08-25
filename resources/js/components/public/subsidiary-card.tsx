import { useState } from 'react';

/**
 * Kartu anak usaha di etalase induk.
 *
 * Inti arah A+B ada di sini: induk sengaja paling tenang — putih,
 * kuningan, tanpa warna kuat — dan KARTU INILAH yang berwarna. Tiap
 * unit memakai aksennya sendiri, diambil dari logonya.
 *
 * Itu yang menyelesaikan masalah lama: sebelum ini, PT. Ayodya Utama
 * Logistic dan J-Land Property tampil dengan palet emas yang dirancang
 * untuk butik dessert (DESIGN_SYSTEM §2.2).
 *
 * MENAMPUNG LOGO YANG RASIONYA SANGAT BERBEDA
 * -------------------------------------------
 * Kelima logo diukur dan hasilnya berjauhan:
 *
 *   Ayodya      5,50:1  (memanjang — tulisan penuh)
 *   ngelash     3,41:1
 *   Nail's      1,49:1
 *   Sweetness   1,10:1
 *   J-Corporate 0,99:1 (nyaris bujur sangkar)
 *
 * Ditaruh apa adanya, tinggi kartunya jadi berbeda-beda dan kisinya
 * terlihat berantakan. Diselesaikan dengan AREA BERTINGGI TETAP: logo
 * dipusatkan di dalamnya dengan `object-contain`, jadi yang memanjang
 * menyusut lebarnya dan yang persegi menyusut tingginya — tapi baris
 * teks di bawahnya selalu mulai di ketinggian yang sama.
 *
 * Pola yang sama dipakai halaman company profile pada umumnya, dan
 * itulah yang membuat deretan logo klien terlihat rapi walau bentuknya
 * berbeda-beda.
 *
 * HANYA TOMBOLNYA YANG BISA DIKLIK, BUKAN SELURUH KARTU
 * -----------------------------------------------------
 * Sebelumnya seluruh kartu dibungkus <Link>. Itu praktik yang umum tapi
 * punya tiga akibat yang merugikan:
 *
 *   1. Teks di dalamnya tidak bisa diseleksi — menyorot nama usaha untuk
 *      disalin malah membuka halamannya
 *   2. Pembaca layar membacakan seluruh isi kartu sebagai SATU tautan
 *      panjang: nomor, nama, tagline, dan kata "lihat profil" beruntun
 *      tanpa jeda
 *   3. Tidak ada cara membuka di tab baru dengan klik tengah pada bagian
 *      kartu yang terasa seperti teks biasa
 *
 * Sekarang pembungkusnya <article> biasa, dan hanya tombol di dasar
 * kartu yang jadi tautan. Efek hover tetap berlaku ke seluruh kartu
 * lewat `group-hover`, jadi kartunya tetap terasa satu kesatuan.
 */

type Props = {
    slug: string;
    name: string;
    tagline: string | null;
    logoUrl: string | null;
    accentColor: string;
    /** Nomor urut dua digit: "01", "02". */
    index: string;
    /** Lencana cadangan untuk unit yang belum punya logo. */
    initials: string;
};

export default function SubsidiaryCard({
    slug,
    name,
    tagline,
    logoUrl,
    accentColor,
    index,
    initials,
}: Props) {
    const [logoFailed, setLogoFailed] = useState(false);

    const showFallback = !logoUrl || logoFailed;

    return (
        <article
            className="hairline-card group flex h-full flex-col p-7 md:p-8"
            style={{ '--unit-accent': accentColor } as React.CSSProperties}
        >
            <span
                aria-hidden="true"
                className="mb-5 block font-display text-[13px] tracking-[0.08em] text-[var(--unit-accent)] tabular-nums"
            >
                {index}
            </span>

            {/* Tinggi tetap 68px. Bukan angka bulat yang dikira-kira:
                pada 68px, logo Ayodya yang 5,50:1 masih muat penuh di
                kolom tersempit (sekitar 300px di layar 1024), sementara
                logo bujur sangkar masih cukup besar untuk terbaca. */}
            <div className="mb-6 flex h-[68px] items-center">
                {showFallback ? (
                    // J-Land Property belum mengirim logo. Lencana inisial,
                    // bukan kotak kosong (spec §10).
                    <div
                        aria-hidden="true"
                        className="flex h-14 w-14 items-center justify-center border border-hair font-display text-[19px] text-[var(--unit-accent)]"
                    >
                        {initials}
                    </div>
                ) : (
                    <img
                        src={logoUrl ?? undefined}
                        alt={`Logo ${name}`}
                        loading="lazy"
                        onError={() => setLogoFailed(true)}
                        // max-w-[200px] menahan logo memanjang supaya tidak
                        // membentang selebar kartu dan menenggelamkan
                        // namanya sendiri di bawah.
                        className="max-h-full max-w-[200px] object-contain object-left transition-transform duration-500 group-hover:scale-[1.03]"
                    />
                )}
            </div>

            <h3 className="mb-2 font-display text-[21px] leading-snug font-normal tracking-[-0.015em] text-ink transition-transform duration-500 group-hover:translate-x-1.5">
                {name}
            </h3>

            {tagline && (
                <p className="mb-6 line-clamp-3 text-[13.5px] leading-[1.6] text-ink-soft">
                    {tagline}
                </p>
            )}

            {/* SATU-SATUNYA yang bisa diklik di kartu ini.
                Referensinya punya tiga tombol (Visit us / More /
                Download); dua sisanya tidak punya tujuan di sini, dan
                tombol yang tidak menuju ke mana-mana membuat pengunjung
                ragu mana yang benar-benar bisa ditekan.

                Garis bawah, bukan latar berwarna: warna aksen sudah
                dipakai nomor urut dan pita di tepi atas kartu. Tombol
                pekat di tiap kartu membuat lima warna kuat berjajar dan
                saling berebut perhatian. */}
            {/* Target sentuh 44px dipenuhi lewat padding VERTIKAL di
                pembungkusnya (py-3 = 12px atas-bawah + tinggi teks),
                bukan `min-h-11` pada elemen bergaris bawah — yang
                terakhir membuat garisnya melayang jauh di bawah teks.

                <a> BIASA, BUKAN <Link> INERTIA. Inertia mengambil alih
                klik untuk berpindah halaman tanpa memuat ulang, dan itu
                membuat `target="_blank"` diabaikan — halamannya tetap
                terbuka di tab yang sama.

                Konsekuensinya halaman anak usaha dimuat penuh dari nol,
                bukan lewat perpindahan Inertia. Di sini itu justru yang
                diinginkan: tab baru memang mulai dari keadaan bersih.

                `rel="noopener"` wajib menyertai target="_blank" —
                tanpa itu halaman yang dibuka bisa mengakses
                `window.opener` dan mengarahkan tab induk ke mana pun. */}
            <a
                href={`/${slug}`}
                target="_blank"
                rel="noopener"
                className="mt-auto -mb-3 w-fit py-3 text-[11px] font-medium tracking-[0.11em] uppercase"
            >
                <span className="inline-flex items-center gap-2 border-b border-ink pb-1 text-ink transition-colors duration-300 group-hover:border-[var(--unit-accent)] group-hover:text-[var(--unit-accent)]">
                    {/* Nama usaha ikut dibacakan pembaca layar, supaya
                        tautan ini tidak terdengar sebagai "lihat profil"
                        kelima kalinya tanpa keterangan apa pun.

                        "membuka tab baru" juga dibacakan: orang yang
                        tidak melihat layar tidak punya cara lain untuk
                        tahu bahwa fokusnya akan berpindah tab — dan
                        kehilangan konteks di situ jauh lebih
                        membingungkan daripada bagi yang melihat. */}
                    Lihat profil
                    <span className="sr-only"> {name} (membuka tab baru)</span>
                    {/* Panah digambar SVG, bukan karakter ↗ (U+2197).
                        Karakter itu punya penyajian emoji bawaan di
                        sebagian sistem — Windows merendernya berwarna
                        biru-ungu, dan bentuknya ikut berubah antar
                        perangkat. SVG selalu tampil sama, mewarisi warna
                        teks lewat `currentColor`, dan ketebalannya bisa
                        disamakan dengan garis rambut di sekitarnya. */}
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 12 12"
                        className="h-2.5 w-2.5 transition-transform duration-500 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="1.4"
                        strokeLinecap="square"
                    >
                        <path d="M2.5 9.5 9.5 2.5" />
                        <path d="M4 2.5h5.5V8" />
                    </svg>
                </span>
            </a>
        </article>
    );
}
