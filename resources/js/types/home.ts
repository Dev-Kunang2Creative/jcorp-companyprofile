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
};

export type SubsidiaryCard = {
    slug: string;
    name: string;
    tagline: string | null;
    logo_url: string | null;
    /** Lencana untuk anak usaha yang belum punya logo. */
    initials: string;
};

export type HomeProps = {
    parent: ParentBusiness;
    subsidiaries: SubsidiaryCard[];
    contact: PublicContact | null;
};
