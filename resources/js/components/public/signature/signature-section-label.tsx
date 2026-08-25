type Props = {
    children: React.ReactNode;
    className?: string;
};

/**
 * Label section bersama untuk arah One Family, Five Signatures.
 *
 * Garisnya dekoratif lewat CSS; teksnya tetap berasal dari label section
 * atau props yang sudah ada. Komponen ini sengaja tidak menerima nomor bab
 * karena arah signature mengikuti hierarki homepage induk yang baru.
 */
export default function SignatureSectionLabel({
    children,
    className = '',
}: Props) {
    return (
        <p className={`signature-section-label reveal ${className}`.trim()}>
            {children}
        </p>
    );
}
