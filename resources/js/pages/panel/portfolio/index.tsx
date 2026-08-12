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

            <div className="space-y-6 px-4 py-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title={business.portfolio_label}
                        description={`Foto hasil kerja ${business.name}`}
                    />

                    <Button onClick={() => setCreating(true)}>
                        <Plus className="size-4" />
                        Tambah foto
                    </Button>
                </div>

                <section>
                    {items.length === 0 ? (
                        <div className="rounded-brand-md border border-dashed border-line-strong p-8 text-center">
                            <p className="text-sm text-muted-foreground">
                                Belum ada foto. Selama kosong, section portfolio
                                tidak muncul di halaman publik.
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
                    ) : (
                        <>
                            <p className="mb-3 text-sm text-muted-foreground">
                                {items.length} foto tersimpan
                            </p>

                            <ul className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                {items.map((item) => (
                                    <li
                                        key={item.id}
                                        className="glass-card flex flex-col overflow-hidden rounded-brand-md"
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
                                            </p>

                                            <div className="mt-auto flex gap-2">
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
            </div>

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
