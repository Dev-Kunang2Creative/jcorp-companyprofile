import { whatsappUrl } from '@/lib/whatsapp';

/**
 * Tombol WhatsApp melayang — HANYA di mobile (DESIGN_SYSTEM §7).
 *
 * Mayoritas pengunjung datang dari tautan Instagram di HP, dan tujuan
 * utamanya menghubungi lewat WA. Tombol itu tidak boleh hilang saat halaman
 * digulir. Di desktop tidak perlu, karena navigasi sudah menempel di atas.
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
                className="flex min-h-12 w-full items-center justify-center rounded-full bg-gold-deep px-5 py-4 text-[15px] font-medium text-white shadow-[0_6px_20px_rgb(107_79_15/0.32)] transition-colors hover:bg-gold-hover"
            >
                Pesan lewat WhatsApp
            </a>
        </div>
    );
}
