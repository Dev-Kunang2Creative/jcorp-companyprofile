import { Form } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

/**
 * Modal berisi form untuk panel admin.
 *
 * Dipakai bersama oleh form tambah dan form ubah — keduanya memakai field
 * yang sama, hanya beda alamat tujuan dan judulnya.
 *
 * DUA HAL YANG DITANGANI DI SINI
 * ------------------------------
 * 1. Isi form bisa digulir. Form katalog cukup panjang (nama, deskripsi,
 *    harga, kategori, urutan, foto, sakelar tampil) — di laptop berlayar
 *    pendek atau HP, tombol Simpan bisa berada di luar layar dan tidak
 *    terjangkau. Judul dan tombol dibuat menempel, hanya bagian tengah yang
 *    bergulir.
 *
 * 2. Method spoofing untuk form ubah. PHP tidak mengurai multipart/form-data
 *    pada request PUT, jadi unggahan berkas dikirim sebagai POST dengan
 *    `_method=put`.
 */

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description?: string;
    /** Alamat tujuan form. */
    action: string;
    /** true untuk form ubah — mengirim POST + _method=put. */
    isUpdate?: boolean;
    submitLabel: string;
    /** Dijalankan setelah simpan berhasil. */
    onSuccess?: () => void;
    /** Field form; menerima error validasi dari server. */
    children: (errors: Record<string, string>) => ReactNode;
};

export default function FormModal({
    open,
    onOpenChange,
    title,
    description,
    action,
    isUpdate = false,
    submitLabel,
    onSuccess,
    children,
}: Props) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                // p-0 dan flex: padding dipindah ke tiap bagian, supaya
                // hanya bagian tengah yang bergulir sementara judul dan
                // tombol tetap terlihat.
                className="flex max-h-[calc(100svh-2rem)] flex-col gap-0 p-0 sm:max-w-xl"
            >
                <DialogHeader className="border-b px-6 py-4 text-left">
                    <DialogTitle>{title}</DialogTitle>
                    {description && (
                        <DialogDescription>{description}</DialogDescription>
                    )}
                </DialogHeader>

                <Form
                    action={action}
                    method="post"
                    options={{ preserveScroll: true }}
                    resetOnSuccess={!isUpdate}
                    onSuccess={onSuccess}
                    className="flex min-h-0 flex-1 flex-col"
                >
                    {({ processing, errors }) => (
                        <>
                            {/* min-h-0 wajib: tanpa itu, flex child menolak
                                menyusut dan area gulirnya tidak pernah aktif. */}
                            <div className="grid min-h-0 flex-1 gap-4 overflow-y-auto px-6 py-5">
                                {isUpdate && (
                                    <input
                                        type="hidden"
                                        name="_method"
                                        value="put"
                                    />
                                )}

                                {children(errors)}
                            </div>

                            <div className="flex justify-end gap-2 border-t px-6 py-4">
                                <DialogClose asChild>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        disabled={processing}
                                    >
                                        Batal
                                    </Button>
                                </DialogClose>

                                <Button disabled={processing}>
                                    {processing ? 'Menyimpan…' : submitLabel}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
