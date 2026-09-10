import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import ImageInput from '@/components/panel/image-input';
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
        cover_image_url: string | null;
        whatsapp: string | null;
        whatsapp_alt: string | null;
        instagram: string | null;
        tiktok: string | null;
        address: string | null;
        business_hours: string | null;
        contact_note: string | null;
        catalog_label: string;
        catalog_note: string | null;
        portfolio_label: string;
    };
};

export default function PanelBusinessProfile({ business }: Props) {
    return (
        <>
            <Head title="Profil & Kontak" />

            <main className="panel-page space-y-6">
                <Heading
                    title="Profil & Kontak"
                    description={`Foto pembuka, kontak, dan sebutan bagian ${business.name}`}
                />

                <Form
                    {...update.form()}
                    options={{ preserveScroll: true }}
                    setDefaultsOnSuccess
                    className="grid gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(22rem,0.85fr)]"
                >
                    {({ processing, errors, isDirty }) => (
                        <>
                            <section className="panel-surface grid gap-5 p-5 sm:p-6">
                                <h2 className="font-legacy-display text-xl font-semibold text-ink">
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

                                <div className="grid gap-2">
                                    <Label htmlFor="whatsapp_alt">
                                        Nomor WhatsApp kedua
                                    </Label>
                                    <Input
                                        id="whatsapp_alt"
                                        name="whatsapp_alt"
                                        defaultValue={
                                            business.whatsapp_alt ?? ''
                                        }
                                        placeholder="6281234567890"
                                        inputMode="numeric"
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Opsional. Ditampilkan di section Kontak,
                                        tapi tombol WhatsApp tetap memakai nomor
                                        pertama.
                                    </p>
                                    <InputError message={errors.whatsapp_alt} />
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

                                <div className="grid gap-2">
                                    <Label htmlFor="contact_note">
                                        Catatan pemesanan
                                    </Label>
                                    <Input
                                        id="contact_note"
                                        name="contact_note"
                                        defaultValue={
                                            business.contact_note ?? ''
                                        }
                                        placeholder="Pemesanan hanya lewat chat WhatsApp atau DM Instagram."
                                        maxLength={255}
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Muncul sebagai kalimat pembuka di
                                        section Kontak. Kalau dikosongkan,
                                        dipakai kalimat bawaan.
                                    </p>
                                    <InputError message={errors.contact_note} />
                                </div>

                                <p className="text-xs text-muted-foreground">
                                    Kalau semua kolom di atas dikosongkan,
                                    section Kontak tidak muncul sama sekali di
                                    halaman publik.
                                </p>
                            </section>

                            <section className="panel-surface grid content-start gap-5 p-5 sm:p-6">
                                <h2 className="font-legacy-display text-xl font-semibold text-ink">
                                    Foto pembuka
                                </h2>

                                <ImageInput
                                    name="cover_image"
                                    label="Foto bagian atas halaman"
                                    currentUrl={business.cover_image_url}
                                    error={errors.cover_image}
                                    removable
                                    hint="JPEG, PNG, atau WebP. Maksimal 4 MB. Gunakan foto asli yang paling mewakili usaha."
                                />

                                <p className="text-xs text-muted-foreground">
                                    Jika dikosongkan, halaman tetap memakai
                                    susunan logo yang sekarang tanpa kotak foto
                                    kosong.
                                </p>

                                <div className="border-t border-hair pt-5">
                                    <h2 className="font-legacy-display text-xl font-semibold text-ink">
                                        Sebutan bagian
                                    </h2>
                                </div>

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

                                <div className="grid gap-2">
                                    <Label htmlFor="catalog_note">
                                        Catatan harga
                                    </Label>
                                    <Input
                                        id="catalog_note"
                                        name="catalog_note"
                                        defaultValue={
                                            business.catalog_note ?? ''
                                        }
                                        placeholder="Biaya sudah termasuk Primer + Lash Bound + Free Spoolie."
                                        maxLength={500}
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Muncul di bawah daftar harga. Untuk
                                        keterangan yang berlaku ke semua item —
                                        apa yang sudah termasuk, syarat, atau
                                        catatan lain.
                                    </p>
                                    <InputError message={errors.catalog_note} />
                                </div>
                            </section>

                            <div
                                className={
                                    isDirty || processing
                                        ? 'panel-save-bar xl:col-span-2'
                                        : 'hidden'
                                }
                            >
                                <p className="text-sm text-muted-foreground">
                                    Perubahan belum disimpan.
                                </p>
                                <Button disabled={processing}>
                                    {processing ? 'Menyimpan…' : 'Simpan'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </main>
        </>
    );
}
