import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import type { User } from '@/types';

export function UserInfo({
    user,
    showEmail = false,
}: {
    user: User;
    showEmail?: boolean;
}) {
    const getInitials = useInitials();

    return (
        <>
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                <AvatarImage src={user.avatar} alt={user.name} />
                {/* Lencana inisial memakai krem + emas merek, bukan abu-abu
                    netral — sama seperti kotak inisial di kartu publik. */}
                <AvatarFallback className="rounded-full border border-glass-edge-soft bg-glass-tint font-display font-semibold text-gold-deep">
                    {getInitials(user.name)}
                </AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium text-ink">
                    {user.name}
                </span>
                {showEmail && (
                    <span className="truncate text-xs text-ink-soft">
                        {user.email}
                    </span>
                )}
            </div>
        </>
    );
}
