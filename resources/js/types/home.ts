import type { PublicContact } from '@/types/public';

/**
 * Bentuk data halaman induk J Corp.
 *
 * Berbeda dari profil anak usaha: induk tidak punya katalog maupun portfolio
 * sendiri, tapi punya daftar kartu anak usaha (spec §3).
 */

export type ParentBusiness = {
    name: string;
    tagline: string | null;
    description: string | null;
    logo_url: string | null;
    /** Foto pembuka halaman induk. */
    cover_image_url: string | null;
    /** Sudah dipastikan #RRGGBB di server — lihat types/public.ts. */
    accent_color: string;
    /** Ikon tab browser. */
    favicon_url: string | null;
    /** Satu kalimat, ditampilkan sebagai kutipan. */
    vision: string | null;
    /** Daftar kalimat, ditampilkan bernomor. */
    mission: string[] | null;
};

export type SubsidiaryCard = {
    slug: string;
    name: string;
    tagline: string | null;
    logo_url: string | null;
    /** Satu foto portfolio yang dipilih admin untuk kartu halaman induk. */
    featured_image_url: string | null;
    /** Aksen unit ini — mewarnai pita dan tautan di kartunya. */
    accent_color: string;
    /** Lencana untuk anak usaha yang belum punya logo. */
    initials: string;
    /** Label bidang yang ditampilkan pada orbit homepage induk. */
    sector_label: string | null;
};

export type HomeProps = {
    parent: ParentBusiness;
    subsidiaries: SubsidiaryCard[];
    contact: PublicContact | null;
};
