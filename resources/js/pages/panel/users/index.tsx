import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';

/**
 * Daftar akun panel — super-admin saja.
 *
 * Hanya menampilkan. Akun dibuat lewat `php artisan jcorp:make-admin` supaya
 * password tidak pernah melintas lewat form web, dan tidak ada jalur di
 * aplikasi yang bisa menaikkan peran seseorang (spec §6).
 */

type UserRow = {
    id: number;
    name: string;
    email: string;
    role: string;
    role_label: string;
    business_name: string | null;
};

type Props = {
    users: UserRow[];
};

export default function UsersIndex({ users }: Props) {
    return (
        <>
            <Head title="Kelola Akun" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Kelola Akun"
                    description={`${users.length} akun terdaftar`}
                />

                <ul className="grid gap-2">
                    {users.map((user) => (
                        <li
                            key={user.id}
                            className="flex flex-col gap-1 rounded-md border border-border p-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div className="min-w-0">
                                <p className="truncate font-medium">
                                    {user.name}
                                </p>
                                <p className="truncate text-sm text-muted-foreground">
                                    {user.email}
                                </p>
                            </div>

                            <div className="shrink-0 text-sm sm:text-right">
                                <p className="font-medium">{user.role_label}</p>
                                <p className="text-muted-foreground">
                                    {user.business_name ?? 'Seluruh anak usaha'}
                                </p>
                            </div>
                        </li>
                    ))}
                </ul>

                <section className="rounded-md border border-border bg-muted/40 p-4 text-sm">
                    <h2 className="mb-2 font-medium">Menambah akun</h2>
                    <p className="mb-3 text-muted-foreground">
                        Akun dibuat lewat terminal, bukan dari halaman ini —
                        supaya password tidak pernah melewati form web:
                    </p>
                    <code className="block rounded bg-background px-3 py-2 font-mono text-xs">
                        php artisan jcorp:make-admin
                    </code>
                    <p className="mt-3 text-muted-foreground">
                        Satu anak usaha hanya boleh dipegang satu admin.
                    </p>
                </section>
            </div>
        </>
    );
}
