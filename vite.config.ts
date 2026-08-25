import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    /**
     * Laragon tidak mendaftarkan PHP ke PATH sistem (perilaku normalnya), jadi
     * plugin Wayfinder — yang memanggil `php artisan` sendiri saat build —
     * gagal dengan "'php' is not recognized".
     *
     * Isi PHP_BINARY di `.env` dengan path lengkap php.exe untuk mengatasinya.
     * Kalau `php` memang sudah ada di PATH (dijalankan lewat Laragon Terminal,
     * atau di mesin lain), biarkan kosong — nilai bawaannya sudah benar.
     */
    const env = loadEnv(mode, process.cwd(), 'PHP_BINARY');
    const phpBinary = env.PHP_BINARY || 'php';

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.tsx'],
                refresh: true,
                /**
                 * Pasangan huruf dari DESIGN_SYSTEM.md §3.
                 *
                 * HALAMAN PUBLIK (arah A+B):
                 * Instrument Serif — serif editorial bertekanan tinggi.
                 *                    Menggantikan Fraunces yang terlalu
                 *                    hangat untuk induk yang menaungi
                 *                    logistik dan properti.
                 * Inter Tight      — grotesk padat. Bukan Inter biasa;
                 *                    versi rapatnya punya karakter lebih.
                 *
                 * PANEL ADMIN: Fraunces + Jost, belum diubah.
                 *
                 * Semuanya diunduh ke server sendiri saat build — tidak ada
                 * permintaan ke pihak ketiga saat pengunjung membuka halaman.
                 */
                fonts: [
                    // Halaman publik (arah A+B, 23 Agustus 2026)
                    bunny('Instrument Serif', { weights: [400] }),
                    bunny('Inter Tight', { weights: [400, 500, 600] }),

                    // Panel admin & halaman auth — masih memakai pasangan
                    // lama. Dipertahankan sampai panel ikut dikerjakan;
                    // menghapusnya sekarang merusak 25 berkas.
                    bunny('Fraunces', { weights: [400, 600, 700] }),
                    bunny('Jost', { weights: [400, 500, 600] }),
                ],
            }),
            inertia(),
            react({
                babel: {
                    plugins: ['babel-plugin-react-compiler'],
                },
            }),
            tailwindcss(),
            wayfinder({
                formVariants: true,
                command: `"${phpBinary}" artisan wayfinder:generate`,
            }),
        ],
    };
});
