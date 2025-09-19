import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { useI18n } from '@/i18n';

export default function SettingsLayout({ children }) {
    const { __ } = useI18n();

    const sidebarNavItems = [
        {
            title: __('settings.nav.profile'),
            href: route('profile.edit'),
            match: ['profile.*', 'settings.security', 'settings.sessions', 'settings.sessions.*'],
            icon: null,
        },
        {
            title: __('settings.nav.password'),
            href: route('password.edit'),
            match: 'password.*',
            icon: null,
        },
        {
            title: __('settings.nav.appearance'),
            href: route('appearance'),
            match: 'appearance',
            icon: null,
        },
    ];
    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const isItemActive = (item) => {
        try {
            if (item.match) {
                // Support both a single pattern string and an array of patterns
                if (Array.isArray(item.match)) {
                    return item.match.some((pattern) => route().current(pattern));
                }
                return route().current(item.match);
            }
        } catch (e) {
            // Ignore if Ziggy route() is not available for some reason
        }
        const currentPath = window.location.pathname;
        return (item.matchPrefixes || [item.href]).some((prefix) => currentPath.startsWith(prefix));
    };

    return (
        <div className="px-4 py-6">
            <Heading title={__('settings.title')} description={__('settings.description')} />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                        {sidebarNavItems.map((item, index) => {
                            return (
                                <Button
                                    key={`${item.href}-${index}`}
                                    size="sm"
                                    variant="ghost"
                                    asChild
                                    className={cn('w-full justify-start', {
                                        'bg-muted': isItemActive(item),
                                    })}
                                >
                                    <Link href={item.href} prefetch>
                                        {item.title}
                                    </Link>
                                </Button>
                            );
                        })}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
