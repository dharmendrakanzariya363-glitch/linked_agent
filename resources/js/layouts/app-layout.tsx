import { Head, Link, router, usePage } from '@inertiajs/react';
import { Bell, LayoutDashboard, Megaphone, Menu, Newspaper, Settings, Share2, SunMoon, X } from 'lucide-react';
import { useState, type ReactNode } from 'react';
import { FlashToasts } from '@/components/flash-toasts';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import type { AppNotification } from '@/types/models';

const nav = [
    { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { href: '/posts/today', label: "Today's post", icon: Newspaper },
    { href: '/campaigns', label: 'Campaigns', icon: Megaphone },
    { href: '/posts', label: 'History', icon: Newspaper },
    { href: '/linkedin', label: 'LinkedIn', icon: Share2 },
    { href: '/settings/profile', label: 'Settings', icon: Settings },
];

export function AppLayout({ title, children }: { title: string; children: ReactNode }) {
    const page = usePage();
    const auth = page.props.auth;
    const unread = Number(page.props.unreadNotificationsCount ?? 0);
    const recent = (page.props.recentNotifications ?? []) as AppNotification[];
    const linkedinConnected = Boolean(page.props.linkedinConnected);
    const [open, setOpen] = useState(false);
    const [notesOpen, setNotesOpen] = useState(false);
    const { appearance, setAppearance } = useAppearance();

    return (
        <div className="min-h-screen bg-background lg:grid lg:grid-cols-[16rem_1fr]">
            <Head title={title} />
            <FlashToasts />
            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-40 w-64 border-r border-sidebar-border bg-sidebar p-4 text-sidebar-foreground lg:static',
                    open ? 'block' : 'hidden lg:block',
                )}
            >
                <div className="mb-8 flex items-center justify-between">
                    <Link href="/dashboard" className="text-lg font-semibold">
                        Linkd
                    </Link>
                    <Button variant="ghost" size="icon" className="lg:hidden" onClick={() => setOpen(false)}>
                        <X className="h-4 w-4" />
                    </Button>
                </div>
                <nav className="grid gap-1">
                    {nav.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-white/10"
                        >
                            <item.icon className="h-4 w-4" />
                            {item.label}
                        </Link>
                    ))}
                </nav>
                <div className="mt-8 rounded-lg bg-white/5 p-3 text-xs">
                    <p className="font-medium">LinkedIn</p>
                    <p className="mt-1 opacity-70">{linkedinConnected ? 'Connected' : 'Not connected'}</p>
                </div>
            </aside>
            <div>
                <header className="flex items-center justify-between border-b border-border px-4 py-3 lg:px-8">
                    <Button variant="ghost" size="icon" className="lg:hidden" onClick={() => setOpen(true)}>
                        <Menu className="h-4 w-4" />
                    </Button>
                    <div className="ml-auto flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setAppearance(appearance === 'dark' ? 'light' : 'dark')}
                            aria-label="Toggle theme"
                        >
                            <SunMoon className="h-4 w-4" />
                        </Button>
                        <div className="relative">
                            <Button variant="ghost" size="icon" onClick={() => setNotesOpen((value) => !value)} aria-label="Notifications">
                                <Bell className="h-4 w-4" />
                            </Button>
                            {unread > 0 ? (
                                <span className="absolute -top-1 -right-1 rounded-full bg-primary px-1.5 text-[10px] text-primary-foreground">
                                    {unread}
                                </span>
                            ) : null}
                            {notesOpen ? (
                                <div className="absolute right-0 z-20 mt-2 w-80 rounded-xl border border-border bg-card p-3 shadow-xl">
                                    <div className="mb-2 flex items-center justify-between">
                                        <p className="text-sm font-medium">Notifications</p>
                                        <button className="text-xs text-primary" onClick={() => router.post('/notifications/read-all')}>
                                            Mark all read
                                        </button>
                                    </div>
                                    <div className="grid max-h-80 gap-2 overflow-auto">
                                        {recent.length === 0 ? (
                                            <p className="text-sm text-muted-foreground">You are all caught up.</p>
                                        ) : (
                                            recent.map((notification) => (
                                                <button
                                                    key={notification.id}
                                                    className="rounded-lg p-2 text-left hover:bg-muted"
                                                    onClick={() => router.post(`/notifications/${notification.id}/read`)}
                                                >
                                                    <p className="text-sm font-medium">{notification.title}</p>
                                                    <p className="text-xs text-muted-foreground">{notification.message}</p>
                                                </button>
                                            ))
                                        )}
                                    </div>
                                    <Link href="/notifications" className="mt-2 block text-center text-xs text-primary">
                                        View all
                                    </Link>
                                </div>
                            ) : null}
                        </div>
                        <Link href="/settings/profile" className="text-sm font-medium">
                            {auth.user?.name}
                        </Link>
                        <Button variant="ghost" size="sm" onClick={() => router.post('/logout')}>
                            Log out
                        </Button>
                    </div>
                </header>
                <main className="px-4 py-6 lg:px-8">{children}</main>
            </div>
        </div>
    );
}
