import { Link } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { Campaign, Paginated } from '@/types/models';

export default function CampaignsIndex({ campaigns }: { campaigns: Paginated<Campaign> }) {
    return (
        <AppLayout title="Campaigns">
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Campaigns</h1>
                <Button asChild><Link href="/campaigns/create">New campaign</Link></Button>
            </div>
            <div className="grid gap-4">
                {campaigns.data.length === 0 ? (
                    <Card><CardContent className="p-8 text-sm text-muted-foreground">Create a campaign to start generating daily posts.</CardContent></Card>
                ) : campaigns.data.map((campaign) => (
                    <Link key={campaign.id} href={`/campaigns/${campaign.id}`}>
                        <Card>
                            <CardContent className="flex items-center justify-between p-5">
                                <div>
                                    <p className="font-medium">{campaign.name}</p>
                                    <p className="text-sm text-muted-foreground">{campaign.daily_post_time} · {campaign.timezone}</p>
                                </div>
                                <Badge variant={campaign.status === 'active' ? 'success' : 'default'}>{campaign.status_label}</Badge>
                            </CardContent>
                        </Card>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}
