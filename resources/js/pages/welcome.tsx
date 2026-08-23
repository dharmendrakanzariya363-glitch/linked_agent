import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <div className="min-h-screen bg-background">
            <Head title="AI LinkedIn content agent" />
            <header className="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
                <span className="text-lg font-semibold">Linkd</span>
                <div className="flex gap-3">
                    {auth.user ? (
                        <Link href="/dashboard" className="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground">
                            Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link href="/login" className="rounded-md px-4 py-2 text-sm">
                                Log in
                            </Link>
                            <Link href="/register" className="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground">
                                Start free
                            </Link>
                        </>
                    )}
                </div>
            </header>
            <main className="mx-auto grid max-w-6xl gap-12 px-6 py-16 lg:grid-cols-2 lg:items-center">
                <div>
                    <p className="text-sm font-medium text-primary">Laravel 13 · React · OpenAI</p>
                    <h1 className="mt-3 text-4xl font-semibold tracking-tight lg:text-5xl">
                        Generate LinkedIn posts every day. Publish only when you approve.
                    </h1>
                    <p className="mt-4 max-w-xl text-muted-foreground">
                        Connect LinkedIn, create a campaign, and let the agent draft on-brand posts with optional images. Review, edit, and publish from one queue.
                    </p>
                    <div className="mt-8 flex gap-3">
                        <Link href="/register" className="rounded-md bg-primary px-5 py-3 text-sm font-medium text-primary-foreground">
                            Create an account
                        </Link>
                        <Link href="/login" className="rounded-md border border-border px-5 py-3 text-sm">
                            I already have one
                        </Link>
                    </div>
                </div>
                <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                    <p className="text-sm font-medium">Today’s post</p>
                    <p className="mt-4 text-sm leading-6 text-muted-foreground">
                        Most teams don’t fail at LinkedIn because they lack ideas. They fail because publishing is inconsistent. Linkd generates a draft at your scheduled time, then waits for you.
                    </p>
                    <div className="mt-6 flex gap-2 text-xs">
                        <span className="rounded-full bg-secondary px-2 py-1">Ready for review</span>
                        <span className="rounded-full bg-muted px-2 py-1">Human approval required</span>
                    </div>
                </div>
            </main>
        </div>
    );
}
