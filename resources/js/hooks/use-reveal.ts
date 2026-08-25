import { useEffect } from 'react';

/**
 * Menyalakan animasi masuk untuk seluruh elemen ber-`.reveal` di halaman.
 *
 * SATU pengamat untuk seluruh halaman, bukan satu per komponen. Halaman
 * profil lengkap punya sekitar 40 elemen yang bergerak; empat puluh
 * IntersectionObserver terpisah membuat setiap guliran memanggil empat
 * puluh callback, dan itu terasa di HP kelas bawah.
 *
 * Cara pakai: panggil sekali di komponen halaman, lalu beri `.reveal`
 * pada elemen yang ingin bergerak. Jeda berurutan diatur lewat
 * `style={{ transitionDelay }}` di komponennya masing-masing — bukan
 * lewat kelas, karena jumlah elemennya berbeda-beda tiap halaman.
 */
export function useReveal(): void {
    useEffect(() => {
        const nodes = Array.from(
            document.querySelectorAll<HTMLElement>('.reveal'),
        );

        if (nodes.length === 0) {
            return;
        }

        // Yang mematikan animasi di tingkat sistem langsung mendapat
        // halaman utuh. CSS sudah menanganinya juga, tapi tanpa cabang
        // ini pengamatnya tetap berjalan tanpa guna.
        const reduced = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;

        if (reduced || typeof IntersectionObserver === 'undefined') {
            nodes.forEach((node) => node.classList.add('is-in'));

            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-in');

                    // Sekali masuk, berhenti diamati: animasinya tidak
                    // berulang saat pengunjung menggulir naik-turun.
                    observer.unobserve(entry.target);
                });
            },
            {
                // Mulai bergerak saat 12% elemen terlihat, dan sedikit
                // sebelum tepi bawah layar — supaya gerakannya SELESAI
                // ketika mata sampai ke situ, bukan tertinggal di belakang.
                threshold: 0.12,
                rootMargin: '0px 0px -6% 0px',
            },
        );

        nodes.forEach((node) => observer.observe(node));

        return () => observer.disconnect();
    }, []);
}

/**
 * Jeda berurutan untuk elemen dalam satu kelompok.
 *
 * 60ms — cukup terasa berurutan tanpa membuat orang menunggu. Dibatasi
 * di 360ms: lewat dari itu, elemen terakhir terasa tertinggal.
 */
export function stagger(index: number): { transitionDelay: string } {
    return { transitionDelay: `${Math.min(index, 6) * 60}ms` };
}
