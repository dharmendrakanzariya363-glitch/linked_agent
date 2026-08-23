import { Form } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { TextField } from '@/components/form-field';

export default function PasswordSettings() {
    return (
        <AppLayout title="Password">
            <h1 className="mb-6 text-2xl font-semibold">Password</h1>
            <Form action="/user/password" method="put" className="grid max-w-xl gap-4">
                {({ errors, processing }) => (
                    <>
                        <TextField label="Current password" type="password" name="current_password" required error={errors.current_password} />
                        <TextField label="New password" type="password" name="password" required error={errors.password} />
                        <TextField label="Confirm password" type="password" name="password_confirmation" required />
                        <Button disabled={processing}>Update password</Button>
                    </>
                )}
            </Form>
        </AppLayout>
    );
}
