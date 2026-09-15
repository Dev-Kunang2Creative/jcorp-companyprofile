import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(
    props: ImgHTMLAttributes<HTMLImageElement>,
) {
    const { className, alt = 'J-Corporate Group', ...rest } = props;

    return (
        <img
            src="/images/brand/jcorp-badge-96.png"
            alt={alt}
            className={`object-contain ${className ?? ''}`.trim()}
            {...rest}
        />
    );
}
