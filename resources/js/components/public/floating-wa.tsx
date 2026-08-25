import { whatsappUrl } from '@/lib/whatsapp';

/**
 * Tombol WhatsApp melayang — HANYA di mobile (DESIGN_SYSTEM §7).
 *
 * Mayoritas pengunjung datang dari tautan Instagram di HP, dan tujuan
 * utamanya menghubungi lewat WA. Tombol itu tidak boleh hilang saat halaman
 * digulir. Di desktop tidak perlu, karena navigasi sudah menempel di atas.
 *
 * Warnanya mengikuti aksen unit usaha, sama seperti tombol lain sejak
 * arah A+B — jadi di halaman Ayodya ia merah, bukan emas dessert.
 */

type Props = {
    number: string;
    businessName: string;
};

export default function FloatingWa({ number, businessName }: Props) {
    return (
        <div className="sticky bottom-0 z-20 px-4 pb-4 md:hidden">
            <a
                href={whatsappUrl(number, businessName)}
                target="_blank"
                rel="noopener noreferrer"
                className="flex min-h-12 w-full items-center justify-center rounded-[2px] bg-[var(--unit-accent)] px-5 py-4 text-[14px] font-medium tracking-[0.02em] text-white shadow-[0_6px_20px_rgb(26_26_26/0.22)] transition-opacity hover:opacity-90"
            >
                Pesan lewat WhatsApp
            </a>
        </div>
    );
}
