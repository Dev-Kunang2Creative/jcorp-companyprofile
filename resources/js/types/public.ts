/**
 * Bentuk data halaman publik.
 *
 * Section yang bernilai null tidak dirender sama sekali — bukan ditampilkan
 * kosong, bukan diisi "coming soon" (spec §3). Penyaringannya terjadi di
 * PublicProfileController, jadi datanya memang tidak sampai ke browser.
 */

/** Butir berjudul: dipakai untuk layanan dan keunggulan. */
export type PublicDetail = {
    title: string;
    description: string;
};

export type PublicBusiness = {
    slug: string;
    name: string;
    tagline: string | null;
    description: string | null;
    logo_url: string | null;
    /** Foto pembuka yang dikelola dari halaman Profil di panel. */
    cover_image_url: string | null;
    /**
     * Warna aksen halaman, sudah dipastikan berbentuk #RRGGBB di server
     * (Business::safeAccentColor). Dipasang sebagai CSS custom property
     * --unit-accent di pembungkus halaman.
     */
    accent_color: string;
    /** Ikon tab browser. null berarti memakai ikon bawaan J-Corporate. */
    favicon_url: string | null;
    catalog_label: string;
    /** Keterangan di bawah daftar harga ("sudah termasuk …"). */
    catalog_note: string | null;
    portfolio_label: string;
    /** Satu kalimat, ditampilkan sebagai kutipan. */
    vision: string | null;
    /** Daftar kalimat, ditampilkan bernomor. */
    mission: string[] | null;
    /** APA yang dijual — "Layanan Unggulan". */
    featured_services: PublicDetail[] | null;
    /** BAGAIMANA prosesnya — survei, sewa, akad. */
    services: PublicDetail[] | null;
    /** Judul section layanan. null → "Cara Pemesanan". */
    services_label: string | null;
    highlights: PublicDetail[] | null;
};

export type PublicCatalogItem = {
    id: number;
    name: string;
    description: string | null;
    category: string | null;
    /**
     * Keterangan harga. Ditampilkan di baris kecil di atas angka, atau
     * menggantikan baris harga sepenuhnya bila item memang tanpa harga
     * ("hubungi kami").
     */
    price_note: string | null;
    /** Angka harga siap tampil ("Rp 150.000"), tanpa keterangan. */
    formatted_price: string | null;
    image_url: string | null;
    /** Isi kotak cadangan saat gambar belum ada atau gagal dimuat. */
    initials: string;
};

export type PublicPortfolioItem = {
    id: number;
    caption: string | null;
    image_url: string | null;
    full_url: string | null;
};

/** Akun unit usaha di halaman induk. */
export type UnitSocial = {
    /** Nama unit usaha, ditulis apa adanya menurut client. */
    label: string;
    /** Username Instagram tanpa @. */
    username: string;
};

export type PublicContact = {
    whatsapp?: string;
    /** Nama pemilik nomor ("Sekar") — hanya ada bila nomornya ada. */
    whatsapp_label?: string;
    /** Nomor kedua — sebagian usaha memberi dua. */
    whatsapp_alt?: string;
    whatsapp_alt_label?: string;
    instagram?: string;
    tiktok?: string;
    address?: string;
    business_hours?: string;
    /**
     * Kalimat pembuka section kontak, menurut usahanya sendiri. Isinya
     * berbeda menurut peran: anak usaha menerangkan cara memesan, induk
     * menerangkan peluang kerja sama.
     */
    contact_note?: string;
    /** Hanya di halaman induk. */
    unit_socials?: UnitSocial[];
};

export type PublicProfileProps = {
    business: PublicBusiness;
    /** Nama induk untuk footer — dari database, bukan ditulis di komponen. */
    parentName: string | null;
    catalog: PublicCatalogItem[] | null;
    portfolio: PublicPortfolioItem[] | null;
    contact: PublicContact | null;
};
