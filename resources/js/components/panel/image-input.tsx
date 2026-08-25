import { useEffect, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

/**
 * Input gambar dengan pratinjau SEBELUM disimpan (spec §6).
 *
 * Admin mengunggah foto langsung dari HP tanpa memeriksanya dulu; pratinjau
 * mencegah foto salah atau miring baru ketahuan setelah tersimpan.
 */

type Props = {
    name?: string;
    label?: string;
    /** Gambar yang sudah tersimpan, ditampilkan sampai admin memilih yang baru. */
    currentUrl?: string | null;
    error?: string;
    required?: boolean;
    hint?: string;
    /**
     * Menyalakan tombol hapus foto tersimpan.
     *
     * Hanya untuk gambar yang boleh kosong. Foto portfolio TIDAK memakai
     * ini — foto portfolio tanpa gambar tidak ada isinya sama sekali,
     * jadi menghapusnya berarti menghapus itemnya.
     */
    removable?: boolean;
};

export default function ImageInput({
    name = 'image',
    label = 'Foto',
    currentUrl,
    error,
    required = false,
    hint,
    removable = false,
}: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const [fileName, setFileName] = useState<string | null>(null);

    // Menandai foto tersimpan untuk dihapus. Baru benar-benar terhapus
    // setelah form disimpan — jadi admin masih bisa membatalkannya dengan
    // menutup dialog.
    const [markedForRemoval, setMarkedForRemoval] = useState(false);

    // URL objek harus dilepas saat tidak dipakai lagi, kalau tidak memori
    // browser terus bertambah setiap kali admin mengganti pilihan foto.
    useEffect(() => {
        return () => {
            if (preview) {
                URL.revokeObjectURL(preview);
            }
        };
    }, [preview]);

    function handleChange(event: React.ChangeEvent<HTMLInputElement>) {
        const file = event.target.files?.[0];

        if (preview) {
            URL.revokeObjectURL(preview);
        }

        // Memilih foto baru membatalkan niat menghapus — yang dimaksud
        // admin jelas mengganti, bukan mengosongkan.
        setMarkedForRemoval(false);

        if (!file) {
            setPreview(null);
            setFileName(null);

            return;
        }

        setPreview(URL.createObjectURL(file));
        setFileName(file.name);
    }

    function clear() {
        if (preview) {
            URL.revokeObjectURL(preview);
        }

        setPreview(null);
        setFileName(null);

        if (inputRef.current) {
            inputRef.current.value = '';
        }
    }

    // Foto tersimpan disembunyikan begitu ditandai hapus, supaya admin
    // melihat akibat pilihannya sebelum menyimpan — bukan setelahnya.
    const savedImage = markedForRemoval ? null : currentUrl;
    const shownImage = preview ?? savedImage ?? null;

    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>
                {label}
                {!required && (
                    <span className="ml-1 font-normal text-muted-foreground">
                        (opsional)
                    </span>
                )}
            </Label>

            {shownImage && (
                <div className="flex items-start gap-3">
                    <img
                        src={shownImage}
                        alt={preview ? 'Pratinjau foto baru' : 'Foto tersimpan'}
                        className="h-24 w-24 rounded-lg border border-glass-edge object-cover"
                    />

                    <div className="text-sm">
                        <p className="font-medium">
                            {preview ? 'Foto baru' : 'Foto tersimpan'}
                        </p>
                        {fileName && (
                            <p className="text-muted-foreground">{fileName}</p>
                        )}
                        {preview && (
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={clear}
                                className="mt-2"
                            >
                                Batalkan pilihan
                            </Button>
                        )}

                        {/* Hanya untuk foto TERSIMPAN, bukan pratinjau —
                            yang belum disimpan cukup dibatalkan. */}
                        {!preview && removable && (
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => setMarkedForRemoval(true)}
                                className="mt-2"
                            >
                                Hapus foto
                            </Button>
                        )}
                    </div>
                </div>
            )}

            {/* Keadaan setelah foto ditandai hapus. Tanpa umpan balik ini,
                admin tidak punya cara tahu apakah tombolnya bekerja —
                fotonya cuma hilang begitu saja. */}
            {markedForRemoval && (
                <div className="flex items-start justify-between gap-3 rounded-sm border border-dashed border-hair bg-wash p-3">
                    <p className="text-sm text-muted-foreground">
                        Foto akan dihapus saat disimpan.
                    </p>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => setMarkedForRemoval(false)}
                    >
                        Batalkan
                    </Button>
                </div>
            )}

            {/* Dikirim ke server hanya saat foto ditandai hapus. Server
                membedakannya dari "tidak mengunggah apa pun": yang pertama
                mengosongkan, yang kedua mempertahankan. */}
            {markedForRemoval && (
                <input type="hidden" name="remove_image" value="1" />
            )}

            <input
                ref={inputRef}
                id={name}
                name={name}
                type="file"
                accept="image/jpeg,image/png,image/webp"
                required={required}
                onChange={handleChange}
                className="block w-full text-sm file:mr-3 file:rounded-full file:border file:border-hair file:bg-wash file:px-4 file:py-2 file:text-sm file:font-medium file:text-ink"
            />

            <p className="text-xs text-muted-foreground">
                {hint ??
                    'JPEG, PNG, atau WebP. Maksimal 4 MB. Foto langsung dari HP boleh — ukurannya dikecilkan otomatis di server.'}
            </p>

            <InputError message={error} />
        </div>
    );
}
