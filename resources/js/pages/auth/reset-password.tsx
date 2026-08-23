import { Form } from '@inertiajs/react';
import { GuestLayout } from '@/layouts/guest-layout';
import { Button } from '@/components/ui/button';
import { TextField } from '@/components/form-field';

export default function ResetPassword({ email, token }: { email: string; token: string }) {
    return (
        <GuestLayout title="Reset password">
            <div className="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-sm">
                <h1 className="text-2xl font-semibold">Choose a new password</h1>
                <Form action="/reset-password" method="post" className="mt-6 grid gap-4">
                    {({ errors, processing }) => (
                        <>
                            <input type="hidden" name="token" value={token} />
                            <TextField label="Email" type="email" name="email" defaultValue={email} required error={errors.email} />
                            <TextField label="Password" type="password" name="password" required error={errors.password} />
                            <TextField label="Confirm password" type="password" name="password_confirmation" required />
                            <Button disabled={processing}>Reset password</Button>
                        </>
                    )}
                </Form>
            </div>
        </GuestLayout>
    );
}
