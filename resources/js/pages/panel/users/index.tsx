import { Head, router, usePage } from '@inertiajs/react';
import { Check, Copy, UserPlus } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import FormModal from '@/components/panel/form-modal';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { active, resend, store } from '@/routes/panel/users';

/**
 * Kelola akun panel — super-admin saja.
 *
 * Password tidak pernah diketik di sini. Super-admin membuat undangan, lalu
 * menyalin tautannya untuk dikirim lewat WhatsApp — yang diundang membuat
 * passwordnya sendiri.
 */

type AccountStatus =
    'aktif' | 'menunggu_aktivasi' | 'undangan_kedaluwarsa' | 'nonaktif';

type UserRow = {
    id: number;
    name: string;
    email: string;
    role: string;
    role_label: string;
    business_name: string | null;
    status: AccountStatus;
    is_self: boolean;
};

type Props = {
    users: UserRow[];
    availableBusinesses: Array<{ slug: string; name: string }>;
};

const STATUS_LABEL: Record<AccountStatus, string> = {
    aktif: 'Aktif',
    menunggu_aktivasi: 'Menunggu aktivasi',
    undangan_kedaluwarsa: 'Undangan kedaluwarsa',
    nonaktif: 'Nonaktif',
};

const STATUS_CLASS: Record<AccountStatus, string> = {
    aktif: 'text-gold-deep',
    menunggu_aktivasi: 'text-ink-soft',
    undangan_kedaluwarsa: 'text-destructive',
    nonaktif: 'text-muted-foreground',
};

export default function UsersIndex({ users, availableBusinesses }: Props) {
    const [inviting, setInviting] = useState(false);
    const [pendingToggle, setPendingToggle] = useState<UserRow | null>(null);
    const [processing, setProcessing] = useState(false);

    // Tautan undangan datang lewat flash setelah form berhasil. Ini
    // satu-satunya kesempatan menyalinnya — tokennya tidak disimpan mentah.
    //
    // Dibaca langsung dari props, bukan disalin ke state lewat useEffect:
    // flash Inertia sudah hilang sendiri pada navigasi berikutnya, jadi
    // menyalinnya cuma menambah render tanpa guna. `dismissed` menangani
    // penutupan dialog tanpa perlu menyentuh datanya.
    const flash = usePage().props.invitation as
        { name: string; url: string; expires_in_days: number } | undefined;

    const [dismissed, setDismissed] = useState(false);

    const invitation = dismissed ? undefined : flash;

    function confirmToggle() {
        if (!pendingToggle) {
            return;
        }

        setProcessing(true);

        router.put(
            active(pendingToggle.id).url,
            { is_active: pendingToggle.status === 'nonaktif' },
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessing(false);
                    setPendingToggle(null);
                },
            },
        );
    }

    return (
        <>
            <Head title="Kelola Akun" />

            <div className="space-y-6 px-4 py-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title="Kelola Akun"
                        description={`${users.length} akun terdaftar`}
                    />

                    <Button onClick={() => setInviting(true)}>
                        <UserPlus className="size-4" />
                        Undang admin
                    </Button>
                </div>

                <ul className="grid gap-2">
                    {users.map((user) => (
                        <li
                            key={user.id}
                            className="flex flex-col gap-3 rounded-md border border-border p-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div className="min-w-0">
                                <p className="truncate font-medium">
                                    {user.name}
                                    {user.is_self && (
                                        <span className="ml-2 rounded bg-muted px-1.5 py-0.5 text-xs font-normal">
                                            Anda
                                        </span>
                                    )}
                                </p>
                                <p className="truncate text-sm text-muted-foreground">
                                    {user.email} · {user.role_label}
                                    {user.business_name &&
                                        ` · ${user.business_name}`}
                                </p>
                            </div>

                            <div className="flex shrink-0 flex-wrap items-center gap-2">
                                <span
                                    className={`text-sm font-medium ${STATUS_CLASS[user.status]}`}
                                >
                                    {STATUS_LABEL[user.status]}
                                </span>

                                {(user.status === 'menunggu_aktivasi' ||
                                    user.status === 'undangan_kedaluwarsa') && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => {
                                            setDismissed(false);
                                            router.post(
                                                resend(user.id).url,
                                                {},
                                                { preserveScroll: true },
                                            );
                                        }}
                                    >
                                        Buat tautan baru
                                    </Button>
                                )}

                                {/* Akun sendiri tidak bisa dinonaktifkan —
                                    penjagaan sungguhannya di server. */}
                                {!user.is_self && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setPendingToggle(user)}
                                    >
                                        {user.status === 'nonaktif'
                                            ? 'Nyalakan akses'
                                            : 'Matikan akses'}
                                    </Button>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>

                <section className="rounded-md border border-border bg-muted/40 p-4 text-sm">
                    <h2 className="mb-2 font-medium">Cara kerja undangan</h2>
                    <p className="mb-2 text-muted-foreground">
                        Anda membuat akun tanpa password, lalu mengirim
                        tautannya lewat WhatsApp. Admin baru membuat passwordnya
                        sendiri — jadi tidak ada yang tahu password orang lain.
                    </p>
                    <p className="text-muted-foreground">
                        Tautan berlaku 7 hari dan hanya bisa dipakai sekali.
                        Kalau kedaluwarsa, buat tautan baru lewat tombol di
                        baris akunnya.
                    </p>
                </section>
            </div>

            {/* ---------- Modal undang ---------- */}

            <FormModal
                open={inviting}
                onOpenChange={setInviting}
                title="Undang admin baru"
                description="Akun dibuat tanpa password. Anda akan mendapat tautan untuk dikirim ke orangnya."
                action={store.url()}
                submitLabel="Buat undangan"
                onSuccess={() => {
                    setInviting(false);
                    // Dialog tautan yang muncul berikutnya adalah undangan
                    // baru, jadi penutupan yang lama tidak boleh menempel.
                    setDismissed(false);
                }}
            >
                {(errors) => (
                    <InviteFields
                        errors={errors}
                        availableBusinesses={availableBusinesses}
                    />
                )}
            </FormModal>

            {/* ---------- Tautan undangan ---------- */}

            <Dialog
                open={invitation !== undefined}
                onOpenChange={(open) => !open && setDismissed(true)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            Undangan untuk {invitation?.name}
                        </DialogTitle>
                        <DialogDescription>
                            Salin tautan ini dan kirim lewat WhatsApp. Berlaku{' '}
                            {invitation?.expires_in_days} hari dan hanya bisa
                            dipakai sekali.
                        </DialogDescription>
                    </DialogHeader>

                    {invitation && <CopyableLink url={invitation.url} />}

                    <p className="text-sm text-destructive">
                        Tautan ini tidak bisa dilihat lagi setelah ditutup.
                        Salin sekarang — kalau terlewat, buat tautan baru lewat
                        tombol di baris akunnya.
                    </p>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline">Tutup</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* ---------- Konfirmasi nyalakan/matikan ---------- */}

            <Dialog
                open={pendingToggle !== null}
                onOpenChange={(open) => !open && setPendingToggle(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {pendingToggle?.status === 'nonaktif'
                                ? `Nyalakan akses "${pendingToggle?.name}"?`
                                : `Matikan akses "${pendingToggle?.name}"?`}
                        </DialogTitle>
                        <DialogDescription>
                            {pendingToggle?.status === 'nonaktif'
                                ? 'Akun ini bisa masuk panel lagi dengan password yang sama seperti sebelumnya.'
                                : 'Akun ini langsung terlempar keluar, bahkan kalau sedang login. Datanya tidak dihapus — bisa dinyalakan lagi kapan saja.'}
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline" disabled={processing}>
                                Batal
                            </Button>
                        </DialogClose>

                        <Button onClick={confirmToggle} disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Ya, lanjutkan'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

function CopyableLink({ url }: { url: string }) {
    const [copied, setCopied] = useState(false);

    async function copy() {
        try {
            await navigator.clipboard.writeText(url);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch {
            // Clipboard ditolak browser (biasanya karena bukan HTTPS).
            // Tautannya tetap terlihat dan bisa disalin manual.
            setCopied(false);
        }
    }

    return (
        <div className="flex items-center gap-2">
            <code className="min-w-0 flex-1 truncate rounded border border-border bg-background px-3 py-2 font-mono text-xs">
                {url}
            </code>

            <Button variant="outline" size="sm" onClick={copy}>
                {copied ? (
                    <>
                        <Check className="size-4" />
                        Tersalin
                    </>
                ) : (
                    <>
                        <Copy className="size-4" />
                        Salin
                    </>
                )}
            </Button>
        </div>
    );
}

function InviteFields({
    errors,
    availableBusinesses,
}: {
    errors: Record<string, string>;
    availableBusinesses: Array<{ slug: string; name: string }>;
}) {
    const [role, setRole] = useState('business_admin');

    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="name">Nama lengkap</Label>
                <Input
                    id="name"
                    name="name"
                    required
                    maxLength={255}
                    autoFocus
                />
                <InputError message={errors.name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="email">Email</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    required
                    maxLength={255}
                />
                <p className="text-xs text-muted-foreground">
                    Dipakai untuk masuk panel. Tautan undangan tidak dikirim ke
                    email ini — Anda yang mengirimnya lewat WhatsApp.
                </p>
                <InputError message={errors.email} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="role">Peran</Label>
                <select
                    id="role"
                    name="role"
                    value={role}
                    onChange={(e) => setRole(e.target.value)}
                    className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                >
                    <option value="business_admin">
                        Admin Anak Usaha — mengelola satu anak usaha
                    </option>
                    <option value="super_admin">
                        Super Admin — mengelola semuanya
                    </option>
                </select>
                <InputError message={errors.role} />
            </div>

            {role === 'business_admin' && (
                <div className="grid gap-2">
                    <Label htmlFor="business">Anak usaha yang dikelola</Label>

                    {availableBusinesses.length === 0 ? (
                        <p className="rounded-md border border-dashed border-border p-3 text-sm text-muted-foreground">
                            Semua anak usaha sudah punya admin aktif. Matikan
                            akses admin lama dulu, atau undang sebagai super
                            admin.
                        </p>
                    ) : (
                        <select
                            id="business"
                            name="business"
                            required
                            className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                        >
                            <option value="">Pilih anak usaha</option>
                            {availableBusinesses.map((b) => (
                                <option key={b.slug} value={b.slug}>
                                    {b.name}
                                </option>
                            ))}
                        </select>
                    )}

                    <p className="text-xs text-muted-foreground">
                        Hanya yang belum punya admin aktif yang bisa dipilih —
                        satu anak usaha satu admin.
                    </p>
                    <InputError message={errors.business} />
                </div>
            )}
        </>
    );
}
