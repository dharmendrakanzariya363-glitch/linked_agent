import { router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

function applyAppearance(appearance: Appearance): void {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark);
    document.documentElement.classList.toggle('dark', isDark);
}

export function initializeTheme(): void {
    const saved = (document.cookie.match(/(?:^|; )appearance=([^;]+)/)?.[1] ?? 'system') as Appearance;
    applyAppearance(saved);
}

export function useAppearance() {
    const pageAppearance = usePage().props.appearance as Appearance;
    const [appearance, setAppearanceState] = useState<Appearance>(pageAppearance ?? 'system');

    useEffect(() => {
        applyAppearance(appearance);
    }, [appearance]);

    const setAppearance = useCallback((value: Appearance) => {
        setAppearanceState(value);
        applyAppearance(value);
        router.post('/settings/appearance', { appearance: value }, { preserveScroll: true, preserveState: true });
    }, []);

    return { appearance, setAppearance };
}
