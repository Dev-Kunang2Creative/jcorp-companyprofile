import { useLayoutEffect, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';

/**
 * Input harga yang menampilkan "Rp" dan titik ribuan sambil diketik.
 *
 * Admin melihat "Rp 150.000", server tetap menerima angka mentah 150000 —
 * lewat input tersembunyi. Tanpa pemisahan itu, angka berformat "150.000"
 * akan ditolak validasi `numeric`.
 *
 * Memakai type="text" dengan inputMode="numeric", bukan type="number":
 * input angka bawaan browser tidak bisa menampilkan titik ribuan, dan panah
 * naik-turunnya tidak ada gunanya untuk harga.
 *
 * DUA JEBAKAN YANG SUDAH DITANGANI
 * --------------------------------
 * 1. Backspace menghapus tiga digit sekaligus. Terjadi kalau pemotongan
 *    desimal (".00" dari database) dijalankan di setiap ketikan: saat
 *    mengetik, "1.000.00" ikut tersambar aturan itu dan langsung jadi
 *    "1.000". Sekarang desimal hanya dipotong sekali saat memuat nilai awal.
 *
 * 2. Kursor melompat ke akhir. Setiap penyisipan titik mengubah panjang
 *    teks, dan React memulihkan posisi kursor ke ujung. Diperbaiki dengan
 *    menghitung ulang posisinya berdasarkan JUMLAH DIGIT di kiri kursor,
 *    bukan jumlah karakter.
 */

type Props = {
    /** Nilai tersimpan, apa adanya dari database ("150000.00"). */
    defaultValue?: string | null;
    error?: string;
};

/** "150000" -> "150.000" */
function withThousandSeparator(digits: string): string {
    if (digits === '') {
        return '';
    }

    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/** Membuang segala yang bukan angka. Dipakai di setiap ketikan. */
function toDigits(value: string): string {
    return value.replace(/\D/g, '');
}

/**
 * Membaca nilai dari database, yang selalu berbentuk "150000.00".
 *
 * Hanya dipanggil sekali saat komponen dimuat — tidak boleh dipakai di
 * setiap ketikan, karena "1.000.00" yang muncul di tengah pengetikan akan
 * ikut terpotong (lihat jebakan nomor 1 di atas).
 */
function digitsFromStored(value: string): string {
    return toDigits(value.replace(/\.\d{2}$/, ''));
}

export default function PriceInput({ defaultValue, error }: Props) {
    const inputRef = useRef<HTMLInputElement>(null);

    const [display, setDisplay] = useState(() =>
        withThousandSeparator(digitsFromStored(defaultValue ?? '')),
    );

    // Berapa digit yang berada di kiri kursor setelah pengetikan terakhir.
    // null berarti tidak perlu memindahkan kursor (misalnya saat memuat).
    const digitsBeforeCaret = useRef<number | null>(null);

    useLayoutEffect(() => {
        const target = digitsBeforeCaret.current;
        const input = inputRef.current;

        if (target === null || !input) {
            return;
        }

        digitsBeforeCaret.current = null;

        // Cari posisi TEPAT SESUDAH digit ke-`target`.
        //
        // Kalau target 0, kursor di paling depan. Selain itu, telusuri sampai
        // digit ke-target ditemukan lalu berhenti setelahnya — bukan sebelum
        // digit berikutnya, karena di antara keduanya bisa ada titik dan
        // kursor akan berhenti sebelum titik itu.
        let position = 0;

        if (target > 0) {
            let seen = 0;
            position = display.length;

            for (let i = 0; i < display.length; i++) {
                if (/\d/.test(display[i])) {
                    seen++;

                    if (seen === target) {
                        position = i + 1;
                        break;
                    }
                }
            }
        }

        input.setSelectionRange(position, position);
    }, [display]);

    function handleChange(event: React.ChangeEvent<HTMLInputElement>) {
        const raw = event.target.value;
        const caret = event.target.selectionStart ?? raw.length;

        // Hitung digit di kiri kursor SEBELUM diformat ulang — itu yang
        // dipakai menempatkan kursor kembali setelah titik disisipkan.
        digitsBeforeCaret.current = toDigits(raw.slice(0, caret)).length;

        setDisplay(withThousandSeparator(toDigits(raw)));
    }

    const rawValue = display.replace(/\./g, '');

    return (
        <div className="grid gap-2">
            <Label htmlFor="price-display">Harga</Label>

            <div className="relative">
                <span
                    aria-hidden="true"
                    className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground"
                >
                    Rp
                </span>

                <input
                    ref={inputRef}
                    id="price-display"
                    type="text"
                    inputMode="numeric"
                    autoComplete="off"
                    value={display}
                    onChange={handleChange}
                    placeholder="150.000"
                    className="h-9 w-full rounded-md border border-input bg-transparent py-1 pr-3 pl-9 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                />
            </div>

            {/* Yang benar-benar dikirim ke server: angka mentah tanpa titik. */}
            <input type="hidden" name="price" value={rawValue} />

            <p className="text-xs text-muted-foreground">
                Kosongkan kalau harganya menyesuaikan atau ingin ditanyakan
                langsung.
            </p>

            <InputError message={error} />
        </div>
    );
}
