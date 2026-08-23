import { Form } from '@inertiajs/react';
import { GuestLayout } from '@/layouts/guest-layout';
import { Button } from '@/components/ui/button';
import { TextField } from '@/components/form-field';

export default function Register() {
    return (
        <GuestLayout title="Create account">
            <div className="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-sm">
                <h1 className="text-2xl font-semibold">Create your Linkd account</h1>
                <Form action="/register" method="post" className="mt-6 grid gap-4">
                    {({ errors, processing }) => (
                        <>
                            <TextField label="Name" name="name" required error={errors.name} />
                            <TextField label="Email" type="email" name="email" required error={errors.email} />
                            <TextField label="Password" type="password" name="password" required error={errors.password} />
                            <TextField label="Confirm password" type="password" name="password_confirmation" required />
                            <Button disabled={processing}>{processing ? 'Creating account...' : 'Create account'}</Button>
                        </>
                    )}
                </Form>
            </div>
        </GuestLayout>
    );
}
