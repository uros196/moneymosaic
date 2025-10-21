import { AppContent } from '@/components/app-content';
import { AppHeader } from '@/components/app-header';
import { AppShell } from '@/components/app-shell';
import { ConfirmDeleteProvider } from '@/components/ui/confirm-delete-provider';

export default function AppHeaderLayout({ children, breadcrumbs }) {
    return (
        <AppShell>
            <AppHeader breadcrumbs={breadcrumbs} />
            <ConfirmDeleteProvider>
                <AppContent>{children}</AppContent>
            </ConfirmDeleteProvider>
        </AppShell>
    );
}
