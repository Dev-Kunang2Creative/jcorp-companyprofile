import { cn } from '@/lib/utils';
import { whatsappUrl } from '@/lib/whatsapp';

/**
 * Tombol WhatsApp — komponen terpisah karena client kemungkinan meminta form
 * kontak tersimpan di kemudian hari. Menambahkannya nanti berarti menambah
 * komponen di sebelah ini, bukan mengganti yang sudah ada (spec §8).
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
                'inline-flex items-center justify-center gap-2 rounded-full font-medium transition-colors',
                // Target sentuh minimal 44px di mobile (DESIGN_SYSTEM §6)
                'min-h-11',
                variant === 'solid' &&
                    'bg-gold-deep px-7 py-3.5 text-[15px] text-white hover:bg-gold-hover',
                variant === 'outline' &&
                    'border border-gold px-5 py-2 text-[13px] text-gold-deep hover:border-gold-deep hover:bg-gold-deep hover:text-white',
                className,
            )}
        >
            {children}
        </a>
    );
}
