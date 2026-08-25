import { ArrowUpRight } from 'lucide-react';
import { cn } from '@/lib/utils';
import { whatsappUrl } from '@/lib/whatsapp';

type Props = {
    number: string;
    businessName: string;
    itemName?: string;
    className?: string;
    locale?: 'id' | 'en';
    children: React.ReactNode;
};

/** CTA kapsul bersama keluarga signature; data nomor tetap dari backend. */
export default function SignatureWhatsappButton({
    number,
    businessName,
    itemName,
    className,
    locale = 'id',
    children,
}: Props) {
    return (
        <a
            href={whatsappUrl(number, businessName, itemName, locale)}
            target="_blank"
            rel="noopener noreferrer"
            className={cn('signature-button', className)}
        >
            <span>{children}</span>
            <ArrowUpRight aria-hidden="true" />
        </a>
    );
}
