import { Head } from '@inertiajs/react';

/**
 * Halaman error bergaya website (spec §10).
 *
 * 404 dipakai untuk dua hal sekaligus: slug yang tidak ada, DAN anak usaha
 * yang belum diterbitkan. Keduanya tampil sama persis — supaya keberadaan
 * profil yang belum terbit tidak terungkap.
 *
 * 500 tidak pernah menampilkan detail teknis; detailnya masuk log server.
 */

type Props = {
    status: number;
};

const MESSAGES: Record<number, { title: string; body: string }> = {
    404: {
        title: 'Halaman tidak ditemukan',
        body: 'Alamat yang Anda buka tidak ada. Mungkin salah ketik, atau halamannya sudah dipindah.',
    },
    500: {
        title: 'Ada yang bermasalah',
        body: 'Terjadi kesalahan di sisi kami. Coba beberapa saat lagi.',
    },
    503: {
        title: 'Sedang dalam perbaikan',
        body: 'Website sedang diperbarui sebentar. Silakan kembali lagi nanti.',
    },
};

export default function ErrorPage({ status }: Props) {
    const message = MESSAGES[status] ?? MESSAGES[500];

    return (
        <>
            <Head title={message.title} />

            <div className="flex min-h-svh items-center justify-center px-4 font-sans">
                <div className="glass-panel relative w-full max-w-[460px] rounded-brand-lg p-8 text-center">
                    <p className="mb-3 text-[11px] font-medium tracking-[0.22em] text-brass uppercase">
                        Error {status}
                    </p>

                    <h1 className="font-display text-[28px] leading-tight font-semibold text-ink md:text-[32px]">
                        {message.title}
                    </h1>

                    <hr className="mx-auto my-4 h-px w-[52px] border-0 bg-brass-line" />

                    <p className="mb-6 text-[15px] leading-relaxed text-ink-soft">
                        {message.body}
                    </p>

                    <a
                        href="/"
                        className="inline-flex min-h-11 items-center justify-center rounded-[2px] bg-brass px-7 py-3 text-[14px] font-medium text-white transition-opacity hover:opacity-90"
                    >
                        Kembali ke halaman utama
                    </a>
                </div>
            </div>
        </>
    );
}
