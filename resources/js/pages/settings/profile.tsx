import { Form } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { TextField } from '@/components/form-field';
import { usePage } from '@inertiajs/react';

export default function ProfileSettings() {
    const user = usePage().props.auth.user;

    return (
        <AppLayout title="Profile">
            <h1 className="mb-6 text-2xl font-semibold">Profile</h1>
            <Form action="/user/profile-information" method="put" className="grid max-w-xl gap-4">
                {({ errors, processing }) => (
                    <>
                        <TextField label="Name" name="name" defaultValue={user?.name} error={errors.name} />
                        <TextField label="Email" type="email" name="email" defaultValue={user?.email} required error={errors.email} />
                        <Button disabled={processing}>Save profile</Button>
                    </>
                )}
            </Form>
        </AppLayout>
    );
}
