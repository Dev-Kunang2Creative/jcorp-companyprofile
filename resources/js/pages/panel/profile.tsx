import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update } from '@/routes/panel/profile';

/**
 * Info kontak & label section.
 *
 * Yang BISA diubah admin di sini sengaja terbatas (spec §6): nama usaha,
 * tagline, dan cerita perusahaan tidak ada di form ini karena jarang berubah
 * dan salah ketik di sana langsung terlihat publik.
 */

type Props = {
    business: {
        slug: string;
        name: string;
        whatsapp: string | null;
        instagram: string | null;
        tiktok: string | null;
        address: string | null;
        business_hours: string | null;
        catalog_label: string;
        portfolio_label: string;
    };
};

export default function PanelBusinessProfile({ business }: Props) {
    return (
        <>
            <Head title="Info Kontak" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Info Kontak"
                    description={`Kontak dan label section ${business.name}`}
                />

                <Form
                    {...update.form()}
                    options={{ preserveScroll: true }}
                    className="grid max-w-xl gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <section className="glass-card grid gap-4 rounded-brand-md p-4">
                                <h2 className="text-sm font-medium">
                                    Cara dihubungi
                                </h2>

                                <div className="grid gap-2">
                                    <Label htmlFor="whatsapp">
                                        Nomor WhatsApp
                                    </Label>
                                    <Input
                                        id="whatsapp"
                                        name="whatsapp"
                                        defaultValue={business.whatsapp ?? ''}
                                        placeholder="6281234567890"
                                        inputMode="numeric"
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Diawali <strong>62</strong>, tanpa spasi
                                        atau tanda plus. Nomor ini yang dipakai
                                        semua tombol WhatsApp di halaman publik.
                                    </p>
                                    <InputError message={errors.whatsapp} />
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="instagram">
                                            Username Instagram
                                        </Label>
                                        <Input
                                            id="instagram"
                                            name="instagram"
                                            defaultValue={
                                                business.instagram ?? ''
                                            }
                                            placeholder="sweetnessthings"
                                            maxLength={100}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Tanpa @ dan tanpa alamat lengkap.
                                        </p>
                                        <InputError
                                            message={errors.instagram}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="tiktok">
                                            Username TikTok
                                        </Label>
                                        <Input
                                            id="tiktok"
                                            name="tiktok"
                                            defaultValue={business.tiktok ?? ''}
                                            maxLength={100}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Kosongkan kalau tidak dipakai.
                                        </p>
                                        <InputError message={errors.tiktok} />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="address">Alamat</Label>
                                    <Input
                                        id="address"
                                        name="address"
                                        defaultValue={business.address ?? ''}
                                        maxLength={255}
                                    />
                                    <InputError message={errors.address} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="business_hours">
                                        Jam buka
                                    </Label>
                                    <Input
                                        id="business_hours"
                                        name="business_hours"
                                        defaultValue={
                                            business.business_hours ?? ''
                                        }
                                        placeholder="Senin–Sabtu, 09.00–18.00"
                                        maxLength={255}
                                    />
                                    <InputError
                                        message={errors.business_hours}
                                    />
                                </div>

                                <p className="text-xs text-muted-foreground">
                                    Kalau semua kolom di atas dikosongkan,
                                    section Kontak tidak muncul sama sekali di
                                    halaman publik.
                                </p>
                            </section>

                            <section className="glass-card grid gap-4 rounded-brand-md p-4">
                                <h2 className="text-sm font-medium">
                                    Sebutan section
                                </h2>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="catalog_label">
                                            Label katalog
                                        </Label>
                                        <Input
                                            id="catalog_label"
                                            name="catalog_label"
                                            defaultValue={
                                                business.catalog_label
                                            }
                                            required
                                            maxLength={50}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Contoh: Menu Kami, Layanan &amp;
                                            Harga, Unit Tersedia.
                                        </p>
                                        <InputError
                                            message={errors.catalog_label}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="portfolio_label">
                                            Label portfolio
                                        </Label>
                                        <Input
                                            id="portfolio_label"
                                            name="portfolio_label"
                                            defaultValue={
                                                business.portfolio_label
                                            }
                                            required
                                            maxLength={50}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Contoh: Hasil Kerja, Portfolio.
                                        </p>
                                        <InputError
                                            message={errors.portfolio_label}
                                        />
                                    </div>
                                </div>
                            </section>

                            <div>
                                <Button disabled={processing}>
                                    {processing ? 'Menyimpan…' : 'Simpan'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
