import HeadingSmall from '@/components/heading-small';
import { useI18n } from '@/i18n';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Head, Link } from '@inertiajs/react';
import { useLocaleRefreshOnly } from '@/components/language-switcher.jsx';

export default function ListsIndex({ cards }) {
    const { __ } = useI18n();
    const breadcrumbs = [{ title: __('settings.lists.title'), href: route('settings.lists') }];

    // After a user changes language using a LanguageSwitcher, refresh additional props
    useLocaleRefreshOnly(['cards']);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('settings.lists.title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={__('settings.lists.title')} description={__('settings.lists.description')} />

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {cards.map((card, key) => (
                            <Link
                                key={key}
                                href={card.href}
                                prefetch
                                className="block rounded-lg border p-4 transition-colors duration-200 hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            >
                                <div className="space-y-1">
                                    <p className="font-medium">{card.title}</p>
                                    <p className="text-sm text-muted-foreground">{card.description}</p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
