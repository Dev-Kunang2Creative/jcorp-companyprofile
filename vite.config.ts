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
                 * Fraunces (display) — serif berkarakter untuk judul.
                 * Jost (antarmuka)   — geometris bersih; logo sudah sangat
                 *                      ornamen, jadi huruf antarmuka menahan diri.
                 *
                 * Diunduh ke server sendiri saat build — tidak ada permintaan
                 * ke pihak ketiga saat pengunjung membuka halaman.
                 */
                fonts: [
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
