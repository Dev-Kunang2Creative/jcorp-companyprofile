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
};

export default function ImageInput({
    name = 'image',
    label = 'Foto',
    currentUrl,
    error,
    required = false,
    hint,
}: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const [fileName, setFileName] = useState<string | null>(null);

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

    const shownImage = preview ?? currentUrl ?? null;

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
                        className="h-24 w-24 rounded-md border border-border object-cover"
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
                    </div>
                </div>
            )}

            <input
                ref={inputRef}
                id={name}
                name={name}
                type="file"
                accept="image/jpeg,image/png,image/webp"
                required={required}
                onChange={handleChange}
                className="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-2 file:text-sm file:font-medium"
            />

            <p className="text-xs text-muted-foreground">
                {hint ??
                    'JPEG, PNG, atau WebP. Maksimal 4 MB. Foto langsung dari HP boleh — ukurannya dikecilkan otomatis di server.'}
            </p>

            <InputError message={error} />
        </div>
    );
}
