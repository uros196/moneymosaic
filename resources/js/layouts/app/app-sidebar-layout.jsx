import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { useEffect, useRef } from 'react';
import { router, usePage } from '@inertiajs/react';

export default function AppSidebarLayout({ children, breadcrumbs = [] }) {
    const { auth } = usePage().props;
    const minutes = auth.user.password_confirm_minutes ?? null;

    const ticking = useRef(false);
    const timeoutId = useRef(null);

    useEffect(() => {
        if (!minutes || minutes <= 0) {
            return;
        }

        const check = () => {
            if (ticking.current) return;
            ticking.current = true;
            fetch(route('password.needs-confirmation'), { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then((r) => r.json())
                .then((data) => {
                    if (data?.required && data?.redirect) {
                        router.visit(data.redirect, { preserveState: true });
                    }
                })
                .finally(() => {
                    ticking.current = false;
                });
        };

        const onFocus = () => {
            // small debounce to avoid double firing
            if (timeoutId.current) {
                window.clearTimeout(timeoutId.current);
                timeoutId.current = null;
            }
            timeoutId.current = window.setTimeout(() => check(), 150);
        };

        window.addEventListener('focus', onFocus);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') onFocus();
        });

        return () => {
            window.removeEventListener('focus', onFocus);
        };
    }, [minutes]);

    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
