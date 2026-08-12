import { Head } from '@inertiajs/react';
import ContactSection from '@/components/public/contact-section';
import SubsidiaryCard from '@/components/public/subsidiary-card';
import type { HomeProps } from '@/types/home';

/**
 * Halaman induk J Corp — etalase (spec §3).
 *
 * Perannya etalase + profil singkat: memperkenalkan induk, lalu mengantar
 * pengunjung ke profil anak usaha. Tidak punya katalog sendiri.
 *
 * Aturan section kosong berlaku sama seperti profil anak usaha: yang tidak
 * ada isinya tidak dirender.
 */
export default function Home({ parent, subsidiaries, contact }: HomeProps) {
    return (
        <>
            <Head title={`${parent.name} — Induk Usaha`}>
                {parent.tagline && (
                    <meta name="description" content={parent.tagline} />
                )}
            </Head>

            {/* Tanpa bg: aurora di <body> membentang di seluruh halaman, dan
                itulah yang dibiaskan setiap panel kaca di atasnya. */}
            <div className="min-h-svh font-sans">
                {/* HERO — gradasinya lebih pekat dari aurora halaman, supaya
                    tetap jadi titik fokus */}
                <header
                    className="relative px-4 py-16 md:px-8 md:py-24"
                    style={{ background: 'var(--hero-gradient)' }}
                >
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute inset-0 opacity-50"
                        style={{ backgroundImage: 'var(--star-motif)' }}
                    />

                    <div className="relative mx-auto max-w-6xl">
                        <div className="glass-panel max-w-[680px] rounded-brand-lg p-6 md:p-9">
                            {parent.logo_url && (
                                <img
                                    src={parent.logo_url}
                                    alt={`Logo ${parent.name}`}
                                    className="mb-5 h-14 w-auto md:h-[74px]"
                                />
                            )}

                            <p className="mb-3 text-[11px] font-medium tracking-[0.22em] text-gold-deep uppercase">
                                Induk Usaha
                            </p>

                            <h1 className="font-display text-[34px] leading-[1.06] font-semibold tracking-[-0.015em] text-ink md:text-[52px]">
                                {parent.name}
                            </h1>

                            <hr className="my-4 h-0.5 w-[52px] border-0 bg-gold" />

                            {parent.tagline && (
                                <p className="max-w-[44ch] text-[15px] leading-relaxed text-ink-soft md:text-[17px]">
                                    {parent.tagline}
                                </p>
                            )}

                            {subsidiaries.length > 0 && (
                                <a
                                    href="#usaha"
                                    className="mt-6 inline-flex min-h-11 items-center justify-center rounded-full bg-gold-deep px-7 py-3.5 text-[15px] font-medium text-white transition-colors hover:bg-gold-hover"
                                >
                                    Lihat bidang usaha
                                </a>
                            )}
                        </div>
                    </div>
                </header>

                <main>
                    {parent.description && (
                        <section className="px-4 py-12 md:px-8 md:py-16">
                            <div className="mx-auto max-w-6xl">
                                <div className="glass-panel rounded-brand-lg p-6 md:p-9">
                                    <h2 className="mb-6 font-display text-2xl leading-tight font-semibold text-ink md:text-[30px]">
                                        Tentang J Corp
                                    </h2>

                                    <div className="max-w-[58ch] space-y-4">
                                        {parent.description
                                            .split(/\n\s*\n/)
                                            .map((p) => p.trim())
                                            .filter(Boolean)
                                            .map((paragraph, index) => (
                                                <p
                                                    key={index}
                                                    className="text-[15px] leading-[1.75] text-ink-soft md:text-[15.5px]"
                                                >
                                                    {paragraph}
                                                </p>
                                            ))}
                                    </div>
                                </div>
                            </div>
                        </section>
                    )}

                    {/* ETALASE — hilang bila belum ada anak usaha yang terbit */}
                    {subsidiaries.length > 0 && (
                        <section
                            id="usaha"
                            className="px-4 py-12 md:px-8 md:py-16"
                        >
                            <div className="mx-auto max-w-6xl">
                                <h2 className="mb-2 font-display text-2xl leading-tight font-semibold text-ink md:text-[30px]">
                                    Bidang Usaha
                                </h2>
                                <p className="mb-6 max-w-[52ch] text-[15px] leading-relaxed text-ink-soft">
                                    Setiap unit berjalan sendiri dengan
                                    kekhasannya masing-masing.
                                </p>

                                <div className="grid gap-3 sm:grid-cols-2 md:gap-4 lg:grid-cols-3">
                                    {subsidiaries.map((item) => (
                                        <SubsidiaryCard
                                            key={item.slug}
                                            slug={item.slug}
                                            name={item.name}
                                            tagline={item.tagline}
                                            logoUrl={item.logo_url}
                                            initials={item.initials}
                                        />
                                    ))}
                                </div>
                            </div>
                        </section>
                    )}

                    {contact && (
                        <ContactSection
                            contact={contact}
                            businessName={parent.name}
                        />
                    )}
                </main>

                {/* Footer ikut terang. Balok gelap di dasar halaman memotong
                    aurora dan membuat seluruh temanya terbaca setengah jadi. */}
                <footer className="glass-veil border-t px-4 py-8 text-center text-[13px] text-ink-soft md:px-8">
                    © {new Date().getFullYear()} {parent.name}
                </footer>
            </div>
        </>
    );
}
