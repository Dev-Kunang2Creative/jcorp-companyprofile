import { Head } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import ConfirmDelete from '@/components/panel/confirm-delete';
import FormModal from '@/components/panel/form-modal';
import ImageInput from '@/components/panel/image-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy, store, update } from '@/routes/panel/portfolio';

type PortfolioItem = {
    id: number;
    caption: string | null;
    is_featured_on_home: boolean;
    sort_order: number;
    image_url: string | null;
    thumb_url: string | null;
};

type Props = {
    business: { slug: string; name: string; portfolio_label: string };
    items: PortfolioItem[];
};

export default function PortfolioIndex({ business, items }: Props) {
    const [creating, setCreating] = useState(false);
    const [editing, setEditing] = useState<PortfolioItem | null>(null);

    return (
        <>
            <Head title={business.portfolio_label} />

            <main className="panel-page space-y-6">
                <div className="panel-page-header">
                    <Heading
                        title={business.portfolio_label}
                        description={`Foto hasil kerja ${business.name}`}
                    />

                    <div className="panel-page-actions">
                        <Button onClick={() => setCreating(true)}>
                            <Plus className="size-4" />
                            Tambah foto
                        </Button>
                    </div>
                </div>

                <section>
                    {items.length === 0 ? (
                        <div className="panel-empty-state">
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Belum ada foto. Selama kosong, section
                                    portfolio tidak muncul di halaman publik.
                                </p>
                                <Button
                                    variant="outline"
                                    className="mt-4"
                                    onClick={() => setCreating(true)}
                                >
                                    <Plus className="size-4" />
                                    Unggah foto pertama
                                </Button>
                            </div>
                        </div>
                    ) : (
                        <>
                            <p className="mb-3 text-sm text-muted-foreground">
                                {items.length} foto tersimpan
                            </p>

                            <div className="panel-table-shell">
                                <table className="panel-table">
                                    <thead>
                                        <tr>
                                            <th>Foto</th>
                                            <th>Keterangan</th>
                                            <th>Halaman induk</th>
                                            <th>Urutan</th>
                                            <th className="text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {items.map((item) => (
                                            <tr key={item.id}>
                                                <td>
                                                    {item.thumb_url && (
                                                        <img
                                                            src={item.thumb_url}
                                                            alt=""
                                                            className="h-16 w-16 rounded-sm object-cover"
                                                        />
                                                    )}
                                                </td>
                                                <td className="font-medium text-ink">
                                                    {item.caption ??
                                                        'Tanpa keterangan'}
                                                </td>
                                                <td>
                                                    {item.is_featured_on_home ? (
                                                        <span className="inline-flex rounded-full bg-[color-mix(in_srgb,var(--unit-accent)_12%,white)] px-2.5 py-1 text-xs font-semibold text-[var(--unit-accent)]">
                                                            Ditampilkan
                                                        </span>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">
                                                            —
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="tabular-nums">
                                                    {item.sort_order}
                                                </td>
                                                <td>
                                                    <div className="flex justify-end gap-2">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() =>
                                                                setEditing(item)
                                                            }
                                                        >
                                                            Ubah
                                                        </Button>
                                                        <ConfirmDelete
                                                            url={
                                                                destroy(item.id)
                                                                    .url
                                                            }
                                                            itemName={
                                                                item.caption ??
                                                                'foto ini'
                                                            }
                                                            note="Foto akan hilang dari halaman publik. Berkasnya tetap tersimpan di server dan bisa dipulihkan lewat database."
                                                        />
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <ul className="panel-mobile-list grid-cols-2">
                                {items.map((item) => (
                                    <li
                                        key={item.id}
                                        className="panel-mobile-card flex flex-col overflow-hidden p-0"
                                    >
                                        {item.thumb_url && (
                                            <img
                                                src={item.thumb_url}
                                                alt={item.caption ?? ''}
                                                className="aspect-square w-full object-cover"
                                            />
                                        )}

                                        <div className="flex flex-1 flex-col p-3">
                                            <p className="truncate text-sm font-medium">
                                                {item.caption ??
                                                    'Tanpa keterangan'}
                                            </p>
                                            <p className="mb-2 text-xs text-muted-foreground">
                                                urutan {item.sort_order}
                                                {item.is_featured_on_home &&
                                                    ' · tampil di halaman induk'}
                                            </p>

                                            <div className="mt-auto grid gap-2 sm:grid-cols-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() =>
                                                        setEditing(item)
                                                    }
                                                >
                                                    Ubah
                                                </Button>

                                                <ConfirmDelete
                                                    url={destroy(item.id).url}
                                                    itemName={
                                                        item.caption ??
                                                        'foto ini'
                                                    }
                                                    note="Foto akan hilang dari halaman publik. Berkasnya tetap tersimpan di server dan bisa dipulihkan lewat database."
                                                />
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}
                </section>
            </main>

            <FormModal
                open={creating}
                onOpenChange={setCreating}
                title="Tambah foto portfolio"
                description={`Foto hasil kerja ${business.name}.`}
                action={store.url()}
                submitLabel="Unggah foto"
                onSuccess={() => setCreating(false)}
            >
                {(errors) => <Fields errors={errors} imageRequired />}
            </FormModal>

            {editing && (
                <FormModal
                    key={editing.id}
                    open
                    onOpenChange={(open) => !open && setEditing(null)}
                    title="Ubah foto"
                    action={update.url(editing.id)}
                    isUpdate
                    submitLabel="Simpan perubahan"
                    onSuccess={() => setEditing(null)}
                >
                    {(errors) => (
                        <Fields
                            item={editing}
                            errors={errors}
                            imageRequired={false}
                        />
                    )}
                </FormModal>
            )}
        </>
    );
}

function Fields({
    item,
    errors,
    imageRequired,
}: {
    item?: PortfolioItem;
    errors: Record<string, string>;
    imageRequired: boolean;
}) {
    return (
        <>
            <ImageInput
                currentUrl={item?.image_url}
                error={errors.image}
                required={imageRequired}
                hint={
                    imageRequired
                        ? 'JPEG, PNG, atau WebP. Maksimal 4 MB. Dipotong jadi kotak untuk grid.'
                        : 'Kosongkan kalau hanya mau mengubah keterangan — foto lama dipertahankan.'
                }
            />

            <div className="grid gap-2">
                <Label htmlFor="caption">Keterangan</Label>
                <Input
                    id="caption"
                    name="caption"
                    defaultValue={item?.caption ?? ''}
                    maxLength={255}
                    placeholder="Nail art bunga, Maret 2026"
                />
                <p className="text-xs text-muted-foreground">
                    Dipakai juga sebagai teks alternatif gambar — tulis yang
                    menggambarkan isinya.
                </p>
                <InputError message={errors.caption} />
            </div>

            <div className="rounded-sm border border-hair bg-wash p-4">
                <input type="hidden" name="is_featured_on_home" value="0" />
                <label className="flex cursor-pointer items-start gap-3">
                    <input
                        type="checkbox"
                        name="is_featured_on_home"
                        value="1"
                        defaultChecked={item?.is_featured_on_home ?? false}
                        className="mt-0.5 size-4 accent-[var(--unit-accent)]"
                    />
                    <span>
                        <strong className="block text-sm font-medium text-ink">
                            Tampilkan di halaman induk
                        </strong>
                        <span className="mt-1 block text-xs leading-relaxed text-muted-foreground">
                            Foto ini dipakai pada kartu unit usaha. Jika
                            dipilih, pilihan pada foto lain di unit ini akan
                            dilepas.
                        </span>
                    </span>
                </label>
                <InputError message={errors.is_featured_on_home} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="sort_order">Urutan tampil</Label>
                <Input
                    id="sort_order"
                    name="sort_order"
                    type="number"
                    min="0"
                    max="9999"
                    defaultValue={item?.sort_order ?? 0}
                    required
                />
                <InputError message={errors.sort_order} />
            </div>
        </>
    );
}
