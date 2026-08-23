import { Form } from '@inertiajs/react';
import { GuestLayout } from '@/layouts/guest-layout';
import { Button } from '@/components/ui/button';
import { TextField } from '@/components/form-field';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <GuestLayout title="Forgot password">
            <div className="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-sm">
                <h1 className="text-2xl font-semibold">Reset your password</h1>
                {status ? <p className="mt-4 text-sm text-emerald-600">{status}</p> : null}
                <Form action="/forgot-password" method="post" className="mt-6 grid gap-4">
                    {({ errors, processing }) => (
                        <>
                            <TextField label="Email" type="email" name="email" required error={errors.email} />
                            <Button disabled={processing}>Email reset link</Button>
                        </>
                    )}
                </Form>
            </div>
        </GuestLayout>
    );
}
