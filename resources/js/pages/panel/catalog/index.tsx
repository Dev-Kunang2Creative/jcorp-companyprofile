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

            <main className="panel-page space-y-6">
                <div className="panel-page-header">
                    <Heading
                        title={business.catalog_label}
                        description={`Mengelola katalog ${business.name}`}
                    />

                    <div className="panel-page-actions">
                        <Button onClick={() => setCreating(true)}>
                            <Plus className="size-4" />
                            Tambah item
                        </Button>
                    </div>
                </div>

                <section>
                    {items.length === 0 ? (
                        <div className="panel-empty-state">
                            <div>
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
                        </div>
                    ) : (
                        <>
                            <p className="mb-3 text-sm text-muted-foreground">
                                {items.length} item tersimpan
                            </p>

                            <div className="panel-table-shell">
                                <table className="panel-table">
                                    <thead>
                                        <tr>
                                            <th>Foto</th>
                                            <th>Nama</th>
                                            <th>Kategori</th>
                                            <th>Harga</th>
                                            <th>Status</th>
                                            <th>Urutan</th>
                                            <th className="text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {items.map((item) => (
                                            <tr key={item.id}>
                                                <td>
                                                    <ItemThumbnail
                                                        item={item}
                                                    />
                                                </td>
                                                <td>
                                                    <p className="max-w-64 font-medium text-ink">
                                                        {item.name}
                                                    </p>
                                                </td>
                                                <td className="text-muted-foreground">
                                                    {item.category ?? '—'}
                                                </td>
                                                <td>
                                                    {item.formatted_price ??
                                                        'Tanpa harga'}
                                                </td>
                                                <td>
                                                    <span
                                                        className={
                                                            item.is_available
                                                                ? 'panel-status panel-status--published'
                                                                : 'panel-status panel-status--draft'
                                                        }
                                                    >
                                                        {item.is_available
                                                            ? 'Tampil'
                                                            : 'Disembunyikan'}
                                                    </span>
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
                                                            itemName={item.name}
                                                        />
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <ul className="panel-mobile-list">
                                {items.map((item) => (
                                    <li
                                        key={item.id}
                                        className="panel-mobile-card"
                                    >
                                        <div className="flex items-start gap-3">
                                            <ItemThumbnail item={item} />
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-start justify-between gap-2">
                                                    <p className="font-medium text-ink">
                                                        {item.name}
                                                    </p>
                                                    <span
                                                        className={
                                                            item.is_available
                                                                ? 'panel-status panel-status--published'
                                                                : 'panel-status panel-status--draft'
                                                        }
                                                    >
                                                        {item.is_available
                                                            ? 'Tampil'
                                                            : 'Tersembunyi'}
                                                    </span>
                                                </div>
                                                <p className="mt-1 text-sm text-muted-foreground">
                                                    {item.formatted_price ??
                                                        'Tanpa harga'}
                                                    {item.category &&
                                                        ` · ${item.category}`}
                                                </p>
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    Urutan {item.sort_order}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="mt-4 flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="flex-1"
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
            </main>

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

function ItemThumbnail({ item }: { item: CatalogItem }) {
    return item.thumb_url ? (
        <img
            src={item.thumb_url}
            alt=""
            className="h-14 w-14 shrink-0 rounded-sm object-cover"
        />
    ) : (
        <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-sm bg-muted text-center text-[0.65rem] leading-tight text-muted-foreground">
            tanpa
            <br />
            foto
        </div>
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

            <ImageInput
                currentUrl={item?.image_url}
                error={errors.image}
                // Foto katalog boleh kosong — kartunya menampilkan kotak
                // inisial. Portfolio tidak: foto portfolio tanpa gambar
                // tidak ada isinya.
                removable
            />

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
