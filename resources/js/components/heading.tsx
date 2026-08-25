export default function Heading({
    title,
    description,
    variant = 'default',
}: {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
}) {
    return (
        <header className={variant === 'small' ? '' : 'space-y-1.5'}>
            <h2
                className={
                    variant === 'small'
                        ? 'mb-0.5 font-display text-base font-semibold text-ink'
                        : 'font-legacy-display text-3xl leading-[1.08] font-semibold tracking-[-0.025em] text-ink sm:text-4xl'
                }
            >
                {title}
            </h2>
            {description && (
                <p className="max-w-2xl text-sm leading-6 text-ink-soft">
                    {description}
                </p>
            )}
        </header>
    );
}
