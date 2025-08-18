import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { toast } from 'react-hot-toast';

export default function ToastManager() {
    const { props } = usePage();
    const flash = props?.flash ?? {};

    useEffect(() => {
        const messages = [];
        if (flash?.success || flash?.status) {
            messages.push({ type: 'success', text: flash?.success ?? flash?.status });
        }
        if (flash?.error) {
            messages.push({ type: 'error', text: flash?.error });
        }
        if (flash?.warning) {
            messages.push({ type: 'warning', text: flash?.warning });
        }
        if (flash?.info) {
            messages.push({ type: 'info', text: flash?.info });
        }

        messages.forEach((m) => {
            if (!m.text) {
                return;
            }
            switch (m.type) {
                case 'error':
                    toast.error(String(m.text));
                    break;
                case 'success':
                    toast.success(String(m.text));
                    break;
                default:
                    toast(String(m.text));
                    break;
            }
        });
        // Re-run for each new flash event by watching the unique flash key
    }, [flash?.key]);

    return null;
}
