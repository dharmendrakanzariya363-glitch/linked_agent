import { router } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Card, CardContent } from '@/components/ui/card';
import type { AppNotification, Paginated } from '@/types/models';

export default function NotificationsIndex({ notifications }: { notifications: Paginated<AppNotification> }) {
    return (
        <AppLayout title="Notifications">
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Notifications</h1>
                <button className="text-sm text-primary" onClick={() => router.post('/notifications/read-all')}>Mark all read</button>
            </div>
            <div className="grid gap-3">
                {notifications.data.length === 0 ? (
                    <Card><CardContent className="p-8 text-sm text-muted-foreground">No notifications yet.</CardContent></Card>
                ) : notifications.data.map((notification) => (
                    <button
                        key={notification.id}
                        className="rounded-xl border border-border bg-card p-4 text-left"
                        onClick={() => router.post(`/notifications/${notification.id}/read`)}
                    >
                        <p className="font-medium">{notification.title}</p>
                        <p className="text-sm text-muted-foreground">{notification.message}</p>
                    </button>
                ))}
            </div>
        </AppLayout>
    );
}
