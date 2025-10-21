import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/react';
import { useI18n } from '@/i18n';

export function NavMain({ items = [] }) {
    const page = usePage();
    const { __ } = useI18n();

    // Helper to determine active state. Prefer matching by route name(s) when provided.
    const isItemActive = (item) => {
        try {
            if (item.match) {
                // Support both a single pattern string and an array of patterns, e.g. 'incomes.*'
                if (Array.isArray(item.match)) {
                    return item.match.some((pattern) => route().current(pattern));
                }
                return route().current(item.match);
            }
        } catch (e) {
            // If Ziggy's route() is not available for some reason, fall back to URL matching.
        }
        // Fallback: compare the current path with item's href prefix
        return page.url?.startsWith?.(item.href);
    };

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>{__('nav.platform')}</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild isActive={isItemActive(item)} tooltip={{ children: item.title }}>
                            <Link href={item.href} prefetch>
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
