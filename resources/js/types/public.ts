/**
 * Bentuk data halaman publik.
 *
 * Section yang bernilai null tidak dirender sama sekali — bukan ditampilkan
 * kosong, bukan diisi "coming soon" (spec §3). Penyaringannya terjadi di
 * PublicProfileController, jadi datanya memang tidak sampai ke browser.
 */

export type PublicBusiness = {
    slug: string;
    name: string;
    tagline: string | null;
    description: string | null;
    logo_url: string | null;
    catalog_label: string;
    portfolio_label: string;
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

export type PublicContact = {
    whatsapp?: string;
    instagram?: string;
    tiktok?: string;
    address?: string;
    business_hours?: string;
};

export type PublicProfileProps = {
    business: PublicBusiness;
    catalog: PublicCatalogItem[] | null;
    portfolio: PublicPortfolioItem[] | null;
    contact: PublicContact | null;
};
