import { Link } from '@inertiajs/react';
import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

type Props = ComponentProps<typeof Link>;

export default function TextLink({
    className = '',
    children,
    ...props
}: Props) {
    return (
        <Link
            className={cn(
                // Garis bawah emas samar, bukan abu-abu netral — tautan ikut
                // palet merek (DESIGN_SYSTEM §2). Warna teksnya --gold-deep,
                // tidak pernah --gold (§2.1).
                'text-gold-deep underline decoration-gold/50 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current!',
                className,
            )}
            {...props}
        >
            {children}
        </Link>
    );
}
