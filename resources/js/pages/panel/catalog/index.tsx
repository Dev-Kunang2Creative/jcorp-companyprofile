import { Head } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import ConfirmDelete from '@/components/panel/confirm-delete';
import FormModal from '@/components/panel/form-modal';
import ImageInput from '@/components/panel/image-input';
import PriceInput from '@/components/panel/price-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy, store, update } from '@/routes/panel/catalog';

/**
 * Kelola katalog.
 *
 * Sejak revisi 12 Agustus, panel ikut berpermukaan kaca seperti halaman
 * publik (DESIGN_SYSTEM §5). Yang TIDAK ikut: ruang kosong leganya. Admin
 * membuka halaman ini berjam-jam untuk mengisi data, dan jarak selega
 * etalase memperlambat kerja — barisnya tetap rapat, hanya permukaannya
 * yang berubah.
 *
 * Form tambah dan ubah keduanya modal — daftar item tetap terlihat sebagai
 * isi utama halaman, dan tidak ada form panjang yang mendorong daftarnya ke
 * bawah layar.
 */

type CatalogItem = {
    id: number;
    name: string;
    description: string | null;
    price: string | null;
    price_note: string | null;
    formatted_price: string | null;
    category: string | null;
    is_available: boolean;
    sort_order: number;
    image_url: string | null;
    thumb_url: string | null;
};

type Props = {
    business: { slug: string; name: string; catalog_label: string };
    items: CatalogItem[];
};

export default function CatalogIndex({ business, items }: Props) {
    const [creating, setCreating] = useState(false);
    const [editing, setEditing] = useState<CatalogItem | null>(null);

    return (
        <>
            <Head title={business.catalog_label} />

            <div className="space-y-6 px-4 py-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title={business.catalog_label}
                        description={`Mengelola katalog ${business.name}`}
                    />

                    <Button onClick={() => setCreating(true)}>
                        <Plus className="size-4" />
                        Tambah item
                    </Button>
                </div>

                <section>
                    {items.length === 0 ? (
                        <div className="rounded-brand-md border border-dashed border-line-strong p-8 text-center">
                            <p className="text-sm text-muted-foreground">
                                Belum ada item. Selama katalog kosong,
                                sectionnya tidak muncul di halaman publik.
                            </p>
                            <Button
                                variant="outline"
                                className="mt-4"
                                onClick={() => setCreating(true)}
                            >
                                <Plus className="size-4" />
                                Tambah item pertama
                            </Button>
                        </div>
                    ) : (
                        <>
                            <p className="mb-3 text-sm text-muted-foreground">
                                {items.length} item tersimpan
                            </p>

                            <ul className="grid gap-2">
                                {items.map((item) => (
                                    <li
                                        key={item.id}
                                        className="glass-card flex items-center gap-4 rounded-brand-md p-3"
                                    >
                                        {item.thumb_url ? (
                                            <img
                                                src={item.thumb_url}
                                                alt=""
                                                className="h-14 w-14 shrink-0 rounded object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded bg-muted text-center text-xs leading-tight text-muted-foreground">
                                                tanpa
                                                <br />
                                                foto
                                            </div>
                                        )}

                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-medium">
                                                {item.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {item.formatted_price ??
                                                    'Tanpa harga'}
                                                {' · urutan '}
                                                {item.sort_order}
                                                {!item.is_available && (
                                                    <span className="ml-2 rounded bg-muted px-1.5 py-0.5 text-xs">
                                                        disembunyikan
                                                    </span>
                                                )}
                                            </p>
                                        </div>

                                        <div className="flex shrink-0 gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => setEditing(item)}
                                            >
                                                Ubah
                                            </Button>

                                            <ConfirmDelete
                                                url={destroy(item.id).url}
                                                itemName={item.name}
                                            />
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
                title="Tambah item katalog"
                description={`Item baru untuk ${business.name}.`}
                action={store.url()}
                submitLabel="Tambah item"
                onSuccess={() => setCreating(false)}
            >
                {(errors) => <Fields errors={errors} />}
            </FormModal>

            {/* key memaksa React membuat ulang isinya saat berpindah item —
                tanpa itu, nilai defaultValue dari item sebelumnya tertinggal. */}
            {editing && (
                <FormModal
                    key={editing.id}
                    open
                    onOpenChange={(open) => !open && setEditing(null)}
                    title={`Ubah "${editing.name}"`}
                    action={update.url(editing.id)}
                    isUpdate
                    submitLabel="Simpan perubahan"
                    onSuccess={() => setEditing(null)}
                >
                    {(errors) => <Fields item={editing} errors={errors} />}
                </FormModal>
            )}
        </>
    );
}

/** Field yang sama dipakai form tambah dan form ubah. */
function Fields({
    item,
    errors,
}: {
    item?: CatalogItem;
    errors: Record<string, string>;
}) {
    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="name">Nama item</Label>
                <Input
                    id="name"
                    name="name"
                    defaultValue={item?.name ?? ''}
                    required
                    maxLength={255}
                    autoFocus
                />
                <InputError message={errors.name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="description">Deskripsi singkat</Label>
                <textarea
                    id="description"
                    name="description"
                    defaultValue={item?.description ?? ''}
                    rows={2}
                    maxLength={2000}
                    className="glass-field rounded-lg px-3 py-2 text-sm"
                />
                <InputError message={errors.description} />
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <PriceInput defaultValue={item?.price} error={errors.price} />

                <div className="grid gap-2">
                    <Label htmlFor="price_note">
                        Satuan harga
                        <span className="ml-1 font-normal text-muted-foreground">
                            (opsional)
                        </span>
                    </Label>
                    <Input
                        id="price_note"
                        name="price_note"
                        defaultValue={item?.price_note ?? ''}
                        placeholder="per bulan"
                        maxLength={50}
                    />
                    <p className="text-xs text-muted-foreground">
                        Kosongkan untuk harga pas. Isi kalau harganya bersatuan
                        — &ldquo;per bulan&rdquo;, &ldquo;per tahun&rdquo; —
                        atau tulis &ldquo;hubungi kami&rdquo; untuk item tanpa
                        harga.
                    </p>
                    <InputError message={errors.price_note} />
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="category">Kategori</Label>
                    <Input
                        id="category"
                        name="category"
                        defaultValue={item?.category ?? ''}
                        placeholder="Dessert Box"
                        maxLength={100}
                    />
                    <InputError message={errors.category} />
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
                    <p className="text-xs text-muted-foreground">
                        Angka kecil tampil lebih dulu.
                    </p>
                    <InputError message={errors.sort_order} />
                </div>
            </div>

            <ImageInput currentUrl={item?.image_url} error={errors.image} />

            <div className="flex items-center gap-2">
                <input type="hidden" name="is_available" value="0" />
                <input
                    id="is_available"
                    name="is_available"
                    type="checkbox"
                    value="1"
                    defaultChecked={item?.is_available ?? true}
                    className="h-4 w-4 rounded border-input"
                />
                <Label htmlFor="is_available" className="font-normal">
                    Tampilkan di halaman publik
                </Label>
            </div>
            <p className="-mt-2 text-xs text-muted-foreground">
                Kalau dimatikan, item tetap tersimpan di sini tapi tidak muncul
                di halaman publik — berguna saat stok habis.
            </p>
        </>
    );
}
