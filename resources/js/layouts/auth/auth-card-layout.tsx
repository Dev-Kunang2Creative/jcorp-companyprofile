import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

export default function AuthCardLayout({
    children,
    title,
    description,
}: PropsWithChildren<{
    name?: string;
    title?: string;
    description?: string;
}>) {
    return (
        <div className="relative flex min-h-svh flex-col items-center justify-center gap-6 p-6 font-sans md:p-10">
            <div className="relative flex w-full max-w-md flex-col gap-6">
                <Link
                    href={home()}
                    className="flex items-center gap-2 self-center font-medium transition-transform duration-200 hover:scale-105"
                >
                    <img
                        src="/images/brand/jcorp-logo-300.webp"
                        alt="J-Corporate Group"
                        className="h-14 w-auto object-contain"
                        width={300}
                        height={293}
                    />
                </Link>

                <div className="flex flex-col gap-6">
                    <Card className="rounded-brand-lg">
                        <CardHeader className="px-10 pt-8 pb-0 text-center">
                            <CardTitle className="font-legacy-display text-xl text-ink">
                                {title}
                            </CardTitle>
                            <CardDescription>{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-10 py-8">
                            {children}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
