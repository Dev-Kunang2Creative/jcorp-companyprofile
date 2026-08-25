import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/panel/invitation';

/**
 * Halaman aktivasi undangan.
 *
 * Dibuka admin baru lewat tautan yang dikirim super-admin. Di sinilah
 * passwordnya dibuat — bukan diketikkan super-admin, jadi tidak ada yang
 * tahu password orang lain.
 */

type Props = {
    token: string;
    name: string;
    businessName: string | null;
    passwordRules: string;
};

export default function Invitation({
    token,
    name,
    businessName,
    passwordRules,
}: Props) {
    return (
        <>
            <Head title="Aktifkan Akun" />

            <div className="mb-6 rounded-sm border border-hair bg-wash p-4 text-sm">
                <p className="font-medium text-ink">Halo, {name}.</p>
                <p className="mt-1 text-ink-soft">
                    {businessName
                        ? `Anda diundang sebagai admin ${businessName}.`
                        : 'Anda diundang sebagai super admin J-Corporate Group.'}{' '}
                    Buat password untuk mengaktifkan akun.
                </p>
            </div>

            <Form
                action={store.url({ token })}
                method="post"
                resetOnError={['password', 'password_confirmation']}
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="password">Password baru</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                autoComplete="new-password"
                                autoFocus
                                required
                            />
                            <p className="text-xs text-muted-foreground">
                                {passwordRules}
                            </p>
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">
                                Ulangi password
                            </Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                autoComplete="new-password"
                                required
                            />
                            <InputError
                                message={errors.password_confirmation}
                            />
                        </div>

                        <Button className="w-full" disabled={processing}>
                            {processing && <Spinner />}
                            Aktifkan akun
                        </Button>
                    </div>
                )}
            </Form>
        </>
    );
}

Invitation.layout = {
    title: 'Aktifkan Akun',
    description: 'Buat password untuk akun panel J-Corporate Group Anda',
};
