import type { Auth, Flash } from '@/types';
import type { AppNotification } from '@/types/models';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            appearance: string;
            flash: Flash;
            linkedinConnected: boolean;
            unreadNotificationsCount: number;
            recentNotifications: AppNotification[];
            [key: string]: unknown;
        };
    }
}
