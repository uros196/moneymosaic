import { Head } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useI18n } from '@/i18n';

export default function Appearance() {
    const { __ } = useI18n();
    const breadcrumbs = [
        {
            title: __('appearance.title'),
            href: route('appearance'),
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('appearance.title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={__('appearance.title')} description={__('appearance.description')} />
                    <AppearanceTabs />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
