import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
import { Moon, Sun } from 'lucide-react';
import { useI18n } from '@/i18n';

export default function AppearanceToggleDropdown({ className = '', ...props }) {
    const { appearance, updateAppearance } = useAppearance();
    const { __ } = useI18n();

    const isSystemDark = typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDarkEffective = appearance === 'dark' || (appearance === 'system' && isSystemDark);

    const toggleAppearance = () => {
        if (appearance === 'dark') {
            updateAppearance('light');
            return;
        }
        if (appearance === 'light') {
            updateAppearance('dark');
            return;
        }
        // If current setting is "system", toggle to the opposite of the effective system theme for a deterministic flip.
        updateAppearance(isSystemDark ? 'light' : 'dark');
    };

    return (
        <div className={className} {...props}>
            <Button variant="ghost" size="icon" className="h-9 w-9 rounded-md" onClick={toggleAppearance}>
                {isDarkEffective ? <Moon className="h-5 w-5" /> : <Sun className="h-5 w-5" />}
                <span className="sr-only">{__('common.appearance.toggle_theme')}</span>
            </Button>
        </div>
    );
}
