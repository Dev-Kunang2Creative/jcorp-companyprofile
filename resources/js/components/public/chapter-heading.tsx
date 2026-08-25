/**
 * Judul bab bernomor — penanda khas arah A+B.
 *
 * Garis PADAT di atas (bukan garis rambut) dan nomor kecil di kiri.
 * Dua hal itu yang membuat halaman terbaca seperti terbitan yang
 * disusun, bukan tumpukan section yang kebetulan berurutan.
 *
 * Nomornya dihitung di halaman pemanggil, bukan otomatis: urutan bab
 * berbeda-beda antar anak usaha karena section yang datanya kosong
 * tidak dirender sama sekali (spec §3). Menghitungnya di sini akan
 * menghasilkan lompatan nomor.
 */

type Props = {
    /** Nomor bab, ditulis dua digit: "01", "02". */
    number: string;
    title: string;
    /** Sasaran tautan navigasi. */
    id?: string;
};

export default function ChapterHeading({ number, title, id }: Props) {
    return (
        <div
            id={id}
            className="reveal mb-7 flex items-baseline gap-4 border-t border-ink pt-5 md:mb-8"
        >
            <span
                aria-hidden="true"
                className="font-display text-[13px] tracking-[0.1em] text-brass"
            >
                {number}
            </span>

            <h2 className="font-display text-[24px] leading-tight font-normal tracking-[-0.02em] text-ink md:text-[34px]">
                {title}
            </h2>
        </div>
    );
}
