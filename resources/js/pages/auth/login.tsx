import { Form, Link } from '@inertiajs/react';
import { GuestLayout } from '@/layouts/guest-layout';
import { Button } from '@/components/ui/button';
import { TextField } from '@/components/form-field';

export default function Login({ status }: { status?: string }) {
    return (
        <GuestLayout title="Log in">
            <div className="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-sm">
                <h1 className="text-2xl font-semibold">Welcome back</h1>
                <p className="mt-1 text-sm text-muted-foreground">Log in to review today’s drafts.</p>
                {status ? <p className="mt-4 text-sm text-emerald-600">{status}</p> : null}
                <Form action="/login" method="post" className="mt-6 grid gap-4">
                    {({ errors, processing }) => (
                        <>
                            <TextField label="Email" type="email" name="email" required error={errors.email} />
                            <TextField label="Password" type="password" name="password" required error={errors.password} />
                            <label className="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="remember" />
                                Remember me
                            </label>
                            <Button disabled={processing}>{processing ? 'Logging in...' : 'Log in'}</Button>
                            <Link href="/forgot-password" className="text-center text-sm text-primary">
                                Forgot password?
                            </Link>
                        </>
                    )}
                </Form>
            </div>
        </GuestLayout>
    );
}
