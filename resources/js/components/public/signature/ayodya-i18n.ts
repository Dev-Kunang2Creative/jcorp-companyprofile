import type {
    PublicBusiness,
    PublicCatalogItem,
    PublicContact,
} from '@/types/public';

export type BrandLocale = 'id' | 'en';

export type BrandUiCopy = {
    profile: string;
    about: string;
    aboutNav: string;
    vision: string;
    mission: string;
    visionMissionNav: string;
    featuredServices: string;
    defaultServices: string;
    gallery: string;
    contact: string;
    heroWhatsapp: string;
    ask: string;
    askWhatsapp: string;
    chatNow: string;
    open: string;
    partOf: string;
    whyChoose: string;
};

export const brandUiCopy: Record<BrandLocale, BrandUiCopy> = {
    id: {
        profile: 'Profil Usaha',
        about: 'Tentang Kami',
        aboutNav: 'Tentang',
        vision: 'Visi',
        mission: 'Misi',
        visionMissionNav: 'Visi & Misi',
        featuredServices: 'Layanan Unggulan',
        defaultServices: 'Cara Pemesanan',
        gallery: 'Galeri',
        contact: 'Kontak',
        heroWhatsapp: 'Hubungi lewat WhatsApp',
        ask: 'Tanya',
        askWhatsapp: 'Tanya lewat WhatsApp',
        chatNow: 'Chat sekarang',
        open: 'Buka',
        partOf: 'bagian dari',
        whyChoose: 'Mengapa memilih',
    },
    en: {
        profile: 'Business Profile',
        about: 'About Us',
        aboutNav: 'About',
        vision: 'Vision',
        mission: 'Mission',
        visionMissionNav: 'Vision & Mission',
        featuredServices: 'Featured Services',
        defaultServices: 'How to Order',
        gallery: 'Gallery',
        contact: 'Contact',
        heroWhatsapp: 'Contact Us on WhatsApp',
        ask: 'Ask',
        askWhatsapp: 'Ask on WhatsApp',
        chatNow: 'Chat Now',
        open: 'Open',
        partOf: 'part of',
        whyChoose: 'Why choose',
    },
};

/**
 * Terjemahan presentasi khusus Ayodya.
 *
 * Bahasa Indonesia tetap berasal dari props/database dan menjadi sumber
 * utama. Salinan Inggris ini menerjemahkan isi yang sama tanpa menambah
 * layanan, mitra, cakupan, atau data kontak baru.
 */
const ayodyaEnglishContent = {
    description: [
        "The rapid growth of the business world requires the support of facilities and infrastructure to ensure a company's success. Speed and accuracy in goods distribution have become key benchmarks for companies seeking to stay competitive in this era of globalization.",
        "PT Ayudya Utama Logistic is a domestic courier, cargo, and logistic trucking service provider operating in Semarang, Central Java. We offer shipping services for your company's goods, both domestic and international (export-import), through several modes of transportation.",
        'We are supported by professional and reliable personnel in their respective fields, as well as partners committed to providing dependable, high-quality service.',
    ].join('\n\n'),
    vision: 'To become a trusted national-level service company oriented toward customer and shareholder satisfaction.',
    mission: [
        'Generate returns for shareholders.',
        'Provide after-sales service to stakeholders.',
        'Improve the welfare of employees and their families.',
    ],
    featuredServices: [
        {
            title: 'Land Transportation',
            description:
                'Land delivery using our own fleet, supported by transportation partners. Available fleet: trailer trucks, CDD boxes, fuso boxes, wing boxes, and pickup boxes. Insurance is available for cargo security.',
        },
        {
            title: 'Sea Transportation',
            description:
                'We act as a forwarding provider for domestic and international container shipments at competitive rates. We work with international shipping lines (Maersk Line, NYK Line, Mitsui Line) and national shipping lines (Meratus, Samudera). Services include EMKL and customs clearance.',
        },
        {
            title: 'Air Transportation',
            description:
                'For time-sensitive goods. Domestic shipments depart from Semarang, Solo, and Yogyakarta airports to airports throughout Indonesia; international shipments depart from Semarang. Supported by Garuda, Citilink, Lion, AirAsia, and Trigana, with Garuda serving international routes.',
        },
        {
            title: 'Biz Service',
            description:
                'We support your company’s field operations, from merchant barcode installation to market-condition surveys. Previous projects include OVO and Danaku throughout Central Java. Services also include domestic and international home and office relocation.',
        },
    ],
    services: [
        {
            title: 'Domestic Courier & Cargo',
            description: 'Domestic courier and cargo delivery services.',
        },
        {
            title: 'Trucking',
            description: 'Logistics trucking services.',
        },
        {
            title: 'Operational Coverage',
            description:
                'The primary operational area covers Semarang and Central Java.',
        },
    ],
    contactNote:
        'PIC: Johan Setiadi Agung Nugroho — Mobile/WhatsApp 0811276265. Office phone 024-3511609, fax 024-3511610. Email johansetiady@yahoo.com or ayudyalogistic@gmail.com, website www.ayudyalogistic.com.',
} as const;

export function localizeAyodyaBusiness(
    business: PublicBusiness,
    locale: BrandLocale,
): PublicBusiness {
    if (business.slug !== 'ayodya-logistic' || locale === 'id') {
        return business;
    }

    return {
        ...business,
        description: ayodyaEnglishContent.description,
        vision: ayodyaEnglishContent.vision,
        mission: [...ayodyaEnglishContent.mission],
        featured_services: ayodyaEnglishContent.featuredServices.map(
            (item) => ({ ...item }),
        ),
        services: ayodyaEnglishContent.services.map((item) => ({ ...item })),
        services_label: 'Service Profile',
        catalog_label: 'Our Services',
    };
}

export function localizeAyodyaCatalog(
    catalog: PublicCatalogItem[] | null,
    slug: string,
    locale: BrandLocale,
): PublicCatalogItem[] | null {
    if (!catalog || slug !== 'ayodya-logistic' || locale === 'id') {
        return catalog;
    }

    return catalog.map((item) => ({
        ...item,
        description:
            item.description === 'Melayani Jasa Pickup Box'
                ? 'Pickup box service.'
                : item.description,
    }));
}

export function localizeAyodyaContact(
    contact: PublicContact | null,
    slug: string,
    locale: BrandLocale,
): PublicContact | null {
    if (!contact || slug !== 'ayodya-logistic' || locale === 'id') {
        return contact;
    }

    return {
        ...contact,
        contact_note: ayodyaEnglishContent.contactNote,
    };
}
