import { router } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { LinkedInAccount } from '@/types/models';

export default function LinkedInIndex({ accounts }: { accounts: { data: LinkedInAccount[] } | LinkedInAccount[] }) {
    const list = Array.isArray(accounts) ? accounts : accounts.data;

    return (
        <AppLayout title="LinkedIn">
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">LinkedIn accounts</h1>
                <Button onClick={() => router.get('/linkedin/connect')}>Connect LinkedIn</Button>
            </div>
            <div className="grid gap-4">
                {list.length === 0 ? (
                    <Card><CardContent className="p-8 text-sm text-muted-foreground">No LinkedIn account connected yet.</CardContent></Card>
                ) : list.map((account) => (
                    <Card key={account.id}>
                        <CardHeader>
                            <CardTitle>{account.name}</CardTitle>
                        </CardHeader>
                        <CardContent className="flex items-center justify-between">
                            <div className="text-sm text-muted-foreground">
                                <p>{account.headline}</p>
                                <p>{account.connected ? 'Connected' : 'Disconnected'}</p>
                            </div>
                            {account.connected ? (
                                <Button variant="outline" onClick={() => router.delete(`/linkedin/${account.id}`)}>Disconnect</Button>
                            ) : null}
                        </CardContent>
                    </Card>
                ))}
            </div>
        </AppLayout>
    );
}
