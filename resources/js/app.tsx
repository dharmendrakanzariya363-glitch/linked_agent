import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from 'sonner';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from '@/hooks/use-appearance';

initializeTheme();

const appName = import.meta.env.VITE_APP_NAME || 'Linkd';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#0A66C2',
    },
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx', { eager: true });
        return pages[`./pages/${name}.tsx`];
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <>
                <App {...props} />
                <Toaster richColors position="top-right" />
            </>,
        );
    },
});
