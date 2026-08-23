import { Form } from '@inertiajs/react';
import { GuestLayout } from '@/layouts/guest-layout';
import { Button } from '@/components/ui/button';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <GuestLayout title="Verify email">
            <div className="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-sm">
                <h1 className="text-2xl font-semibold">Verify your email</h1>
                <p className="mt-2 text-sm text-muted-foreground">
                    We sent a verification link to your inbox. Click it to continue.
                </p>
                {status ? <p className="mt-4 text-sm text-emerald-600">{status}</p> : null}
                <Form action="/email/verification-notification" method="post" className="mt-6">
                    {({ processing }) => (
                        <Button disabled={processing} variant="secondary">
                            Resend verification email
                        </Button>
                    )}
                </Form>
            </div>
        </GuestLayout>
    );
}
