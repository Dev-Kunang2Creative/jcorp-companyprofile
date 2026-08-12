import { useFlashToast } from '@/hooks/use-flash-toast';
import { Toaster as Sonner, type ToasterProps } from 'sonner';

function Toaster({ ...props }: ToasterProps) {
    useFlashToast();

    return (
        <Sonner
            theme="light"
            className="toaster group"
            position="bottom-right"
            style={
                {
                    // --popover sudah tembus pandang; blur-nya ditempel lewat
                    // toastOptions di bawah, karena Sonner merender toast di
                    // portalnya sendiri dan tidak memakai data-slot.
                    '--normal-bg': 'var(--popover)',
                    '--normal-text': 'var(--popover-foreground)',
                    '--normal-border': 'var(--glass-border)',
                } as React.CSSProperties
            }
            toastOptions={{
                className:
                    'backdrop-blur-xl backdrop-saturate-150 shadow-glass rounded-xl',
            }}
            {...props}
        />
    );
}

export { Toaster };
