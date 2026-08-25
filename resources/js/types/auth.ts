export type UserRole = 'super_admin' | 'business_admin';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role: UserRole;
    /** null untuk super_admin — hanya business_admin yang terikat satu anak usaha. */
    business_id: number | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    /** Nama anak usaha yang sedang dikelola, untuk ditampilkan di panel. */
    businessName: string | null;
    isSuperAdmin: boolean;
    /** Menentukan apakah menu Portfolio muncul di sidebar. */
    hasPortfolio: boolean;
    /** Konteks usaha aktif yang tampil di topbar panel. */
    business: {
        slug: string;
        name: string;
        isParent: boolean;
        isPublished: boolean;
        hasPortfolio: boolean;
        accentColor: string;
        publicUrl: string;
    } | null;
    /** Hanya tersedia untuk super-admin. */
    switchableBusinesses: Array<{
        slug: string;
        name: string;
        accentColor: string;
    }> | null;
};
