import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { FlashToasts } from '@/components/flash-toasts';

export function GuestLayout({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="flex min-h-screen flex-col bg-background">
            <Head title={title} />
            <FlashToasts />
            <header className="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6">
                <Link href="/" className="text-lg font-semibold tracking-tight">
                    Linkd
                </Link>
                <div className="flex gap-3 text-sm">
                    <Link href="/login" className="text-muted-foreground hover:text-foreground">
                        Log in
                    </Link>
                    <Link href="/register" className="font-medium text-primary">
                        Get started
                    </Link>
                </div>
            </header>
            <main className="flex flex-1 items-center justify-center px-6 py-10">{children}</main>
        </div>
    );
}
