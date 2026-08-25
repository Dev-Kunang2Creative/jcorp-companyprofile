import WhatsappButton from '@/components/public/whatsapp-button';
import type { PublicCatalogItem } from '@/types/public';

/**
 * Katalog dalam bentuk daftar harga, bukan grid kartu.
 *
 * Dipakai saat TIDAK ADA SATU PUN item yang punya gambar — lihat
 * `catalog-section.tsx`. Kartu katalog punya area gambar 4:3; lima belas
 * layanan tanpa foto berarti lima belas kotak inisial memenuhi layar, dan
 * yang paling dicari pengunjung (harganya) justru tenggelam.
 *
 * Bentuk ini juga yang dipakai client sendiri di dokumennya, dan cara salon
 * biasa menampilkan tarif: dikelompokkan, nama di kiri, harga di kanan.
 *
 * Begitu foto diunggah lewat panel, katalognya kembali jadi grid kartu tanpa
 * perlu menyentuh kode.
 */

type Props = {
    items: PublicCatalogItem[];
    businessName: string;
    whatsapp?: string;
    /** Keterangan yang berlaku untuk seluruh item, di bawah daftar. */
    note?: string | null;
};

export default function PriceList({
    items,
    businessName,
    whatsapp,
    note,
}: Props) {
    // Dikelompokkan menurut kategori, urutannya mengikuti urutan item —
    // bukan diurutkan ulang, karena urutan di dokumen client sudah punya
    // maksud sendiri (yang paling umum lebih dulu).
    const groups: Array<{
        category: string | null;
        items: PublicCatalogItem[];
    }> = [];

    for (const item of items) {
        const last = groups.at(-1);

        if (last && last.category === item.category) {
            last.items.push(item);
        } else {
            groups.push({ category: item.category, items: [item] });
        }
    }

    return (
        <div className="border border-hair">
            {groups.map((group, groupIndex) => (
                <section key={groupIndex}>
                    {group.category && (
                        <h3 className="border-b border-hair bg-wash px-5 py-3 text-[10.5px] font-medium tracking-[0.14em] text-[var(--unit-accent)] uppercase md:px-7">
                            {group.category}
                        </h3>
                    )}

                    <ul>
                        {group.items.map((item) => (
                            <li
                                key={item.id}
                                // Garis pemisah antar baris, tapi tidak di
                                // baris terakhir tiap kelompok — di situ
                                // sudah ada garis judul kelompok berikutnya.
                                className="flex items-baseline justify-between gap-4 border-b border-hair-soft px-5 py-3.5 last:border-b-0 md:px-7"
                            >
                                <div className="min-w-0">
                                    <p className="text-[15px] text-ink">
                                        {item.name}
                                    </p>

                                    {item.description && (
                                        <p className="mt-0.5 text-[13px] leading-relaxed text-ink-soft">
                                            {item.description}
                                        </p>
                                    )}
                                </div>

                                {/* Titik-titik penghubung: membantu mata
                                    menyusuri dari nama ke harganya pada baris
                                    yang panjang. Dekoratif, jadi tidak dibaca
                                    pembaca layar. */}
                                <span
                                    aria-hidden="true"
                                    className="min-w-6 flex-1 translate-y-[-3px] border-b border-dotted border-hair"
                                />

                                <p className="shrink-0 text-[15px] font-medium whitespace-nowrap text-[var(--unit-accent)] tabular-nums">
                                    {item.formatted_price ??
                                        item.price_note ??
                                        'hubungi kami'}
                                </p>
                            </li>
                        ))}
                    </ul>
                </section>
            ))}

            {(note || whatsapp) && (
                // Garis pemisah hanya bila ada daftar di atasnya. Saat
                // daftarnya kosong — usaha yang baru punya keterangan harga
                // tanpa rincian per layanan — garis itu menggantung di tepi
                // atas panel tanpa memisahkan apa pun.
                <div
                    className={`px-5 py-4 md:px-7 md:py-5 ${
                        groups.length > 0 ? 'border-t border-hair' : ''
                    }`}
                >
                    {note && (
                        <p
                            className={
                                // Saat berdiri sendiri, catatan ini satu-satunya
                                // keterangan harga yang dimiliki pengunjung —
                                // jadi dibaca sebagai kalimat biasa, bukan
                                // catatan kaki bercetak miring.
                                groups.length > 0
                                    ? 'text-[13px] leading-relaxed text-ink-soft italic'
                                    : 'text-[15px] leading-relaxed text-ink-soft'
                            }
                        >
                            {note}
                        </p>
                    )}

                    {whatsapp && (
                        <WhatsappButton
                            number={whatsapp}
                            businessName={businessName}
                            className={`w-full md:w-auto ${note ? 'mt-4' : ''}`}
                        >
                            Tanya &amp; buat janji
                        </WhatsappButton>
                    )}
                </div>
            )}
        </div>
    );
}
