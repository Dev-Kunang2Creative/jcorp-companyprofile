import { cn } from '@/lib/utils';
import { whatsappUrl } from '@/lib/whatsapp';

/**
 * Tombol WhatsApp — komponen terpisah karena client kemungkinan meminta form
 * kontak tersimpan di kemudian hari. Menambahkannya nanti berarti menambah
 * komponen di sebelah ini, bukan mengganti yang sudah ada (spec §8).
 *
 * WARNANYA MENGIKUTI AKSEN UNIT USAHA, bukan emas tetap. Sebelum arah A+B,
 * tombol di halaman PT. Ayodya Utama Logistic berwarna emas dessert —
 * warna yang dirancang untuk butik kue. Sekarang ia merah, sesuai logonya
 * sendiri (DESIGN_SYSTEM §2.3).
 *
 * `--unit-accent` disetel di pembungkus halaman; kalau tidak ada, CSS
 * menjatuhkannya ke kuningan induk.
 */

type Props = {
    number: string;
    businessName: string;
    /** Menyebut item yang sedang dilihat di pesan pembuka. */
    itemName?: string;
    variant?: 'solid' | 'outline';
    className?: string;
    children: React.ReactNode;
};

export default function WhatsappButton({
    number,
    businessName,
    itemName,
    variant = 'solid',
    className,
    children,
}: Props) {
    return (
        <a
            href={whatsappUrl(number, businessName, itemName)}
            target="_blank"
            rel="noopener noreferrer"
            className={cn(
                'inline-flex items-center justify-center gap-2 font-medium transition-[background-color,color,border-color] duration-300',
                // Target sentuh minimal 44px di mobile (DESIGN_SYSTEM §6)
                'min-h-11',
                // Sudut nyaris siku, bukan kapsul: arah A+B memakai bentuk
                // yang tegas di seluruh halaman.
                'rounded-[2px]',
                variant === 'solid' &&
                    'bg-[var(--unit-accent)] px-7 py-3.5 text-[14px] tracking-[0.02em] text-white hover:opacity-90',
                variant === 'outline' &&
                    'border border-[var(--unit-accent)] px-5 py-2 text-[12.5px] text-[var(--unit-accent)] hover:bg-[var(--unit-accent)] hover:text-white',
                className,
            )}
        >
            {children}
        </a>
    );
}
