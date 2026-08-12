import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

/**
 * Konfirmasi hapus yang menyebut NAMA item, bukan sekadar "yakin?" (spec §6).
 *
 * Memakai dialog, bukan confirm() bawaan browser: dialog bawaan tidak bisa
 * diberi gaya, tampil berbeda di tiap browser, dan di HP muncul sebagai
 * potongan sistem yang mudah ditekan tanpa dibaca.
 */

type Props = {
    /** Alamat penghapusan. */
    url: string;
    /** Nama item, ditampilkan di dalam pertanyaan. */
    itemName: string;
    /** Penjelasan tambahan, misalnya bahwa data masih bisa dipulihkan. */
    note?: string;
};

export default function ConfirmDelete({ url, itemName, note }: Props) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    function handleDelete() {
        setProcessing(true);

        router.delete(url, {
            preserveScroll: true,
            onFinish: () => {
                setProcessing(false);
                setOpen(false);
            },
        });
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm">
                    Hapus
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus &ldquo;{itemName}&rdquo;?</DialogTitle>
                    <DialogDescription>
                        {note ??
                            'Item ini akan hilang dari halaman publik. Datanya masih tersimpan dan bisa dipulihkan lewat database bila diperlukan.'}
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline" disabled={processing}>
                            Batal
                        </Button>
                    </DialogClose>

                    <Button
                        variant="destructive"
                        onClick={handleDelete}
                        disabled={processing}
                    >
                        {processing ? 'Menghapus…' : 'Ya, hapus'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
