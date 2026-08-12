import type { HTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export default function InputError({
    message,
    className = '',
    ...props
}: HTMLAttributes<HTMLParagraphElement> & { message?: string }) {
    return message ? (
        <p
            {...props}
            // --destructive #9B2C1F: merah bata yang senada dengan palet
            // hangat, dan rasionya 6.5 di atas krem — lolos AA.
            className={cn('text-sm text-destructive', className)}
        >
            {message}
        </p>
    ) : null;
}
