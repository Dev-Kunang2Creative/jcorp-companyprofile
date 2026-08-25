/**
 * Tautan WhatsApp dengan pesan pembuka terisi otomatis (spec §8).
 *
 * Nomor diambil dari database, bukan ditanam di kode — supaya admin bisa
 * menggantinya lewat panel tanpa menyentuh kode.
 */

/**
 * Membersihkan nomor ke bentuk yang diterima wa.me: hanya angka, diawali 62.
 *
 * Admin kadang mengetik "0812-3456-7890" atau "+62 812 3456 7890" walau form
 * memvalidasi formatnya — data lama dari database juga bisa bentuknya lain.
 * Jadi dinormalkan di sini alih-alih menghasilkan tautan mati.
 */
export function normalizeWhatsappNumber(raw: string): string {
    const digits = raw.replace(/\D/g, '');

    if (digits.startsWith('62')) {
        return digits;
    }

    // "08123..." -> "628123..."
    if (digits.startsWith('0')) {
        return `62${digits.slice(1)}`;
    }

    return digits;
}

/**
 * Tautan wa.me lengkap dengan pesan pembuka.
 *
 * @param itemName Kalau diisi, pesannya menyebut item yang sedang dilihat —
 *                 "Halo Sweetness Things, saya mau tanya soal Dessert Box Coklat"
 */
export function whatsappUrl(
    number: string,
    businessName: string,
    itemName?: string,
    locale: 'id' | 'en' = 'id',
): string {
    const message =
        locale === 'en'
            ? itemName
                ? `Hello ${businessName}, I would like to ask about ${itemName}`
                : `Hello ${businessName}, I would like to ask for more information`
            : itemName
              ? `Halo ${businessName}, saya mau tanya soal ${itemName}`
              : `Halo ${businessName}, saya mau tanya-tanya dulu`;

    return `https://wa.me/${normalizeWhatsappNumber(number)}?text=${encodeURIComponent(message)}`;
}

/**
 * Nomor dalam bentuk yang enak dibaca: 0812-3456-7890.
 * Yang ditampilkan ke pengunjung, bukan yang dipakai di tautan.
 */
export function formatWhatsappDisplay(raw: string): string {
    const digits = normalizeWhatsappNumber(raw);

    if (!digits.startsWith('62')) {
        return raw;
    }

    const local = `0${digits.slice(2)}`;

    return local.replace(/^(\d{4})(\d{4})(\d+)$/, '$1-$2-$3');
}
