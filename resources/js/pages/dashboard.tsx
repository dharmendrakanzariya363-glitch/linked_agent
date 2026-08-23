import { Link } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Campaign, LinkedInAccount, Post } from '@/types/models';

export default function Dashboard({
    linkedinConnected,
    linkedinAccount,
    campaigns,
    todayPosts,
    recentPosts,
    stats,
}: {
    linkedinConnected: boolean;
    linkedinAccount: LinkedInAccount | null;
    campaigns: { data: Campaign[] };
    todayPosts: { data: Post[] };
    recentPosts: { data: Post[] };
    stats: { active_campaigns: number; ready_posts: number; published_posts: number };
}) {
    return (
        <AppLayout title="Dashboard">
            <div className="grid gap-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">Dashboard</h1>
                        <p className="text-sm text-muted-foreground">Review today’s drafts and keep campaigns moving.</p>
                    </div>
                    <Button asChild>
                        <Link href="/campaigns/create">New campaign</Link>
                    </Button>
                </div>
                <div className="grid gap-4 md:grid-cols-3">
                    <Card><CardHeader><CardTitle>Active campaigns</CardTitle></CardHeader><CardContent className="text-2xl font-semibold">{stats.active_campaigns}</CardContent></Card>
                    <Card><CardHeader><CardTitle>Ready today</CardTitle></CardHeader><CardContent className="text-2xl font-semibold">{stats.ready_posts}</CardContent></Card>
                    <Card><CardHeader><CardTitle>Published</CardTitle></CardHeader><CardContent className="text-2xl font-semibold">{stats.published_posts}</CardContent></Card>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>LinkedIn</CardTitle>
                    </CardHeader>
                    <CardContent className="flex items-center justify-between gap-4">
                        <p className="text-sm text-muted-foreground">
                            {linkedinConnected ? `Connected as ${linkedinAccount?.name}` : 'Connect LinkedIn before activating a campaign.'}
                        </p>
                        <Button asChild variant={linkedinConnected ? 'secondary' : 'default'}>
                            <Link href="/linkedin">{linkedinConnected ? 'Manage' : 'Connect'}</Link>
                        </Button>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle>Today’s posts</CardTitle></CardHeader>
                    <CardContent className="grid gap-3">
                        {todayPosts.data.length === 0 ? (
                            <p className="text-sm text-muted-foreground">No post generated for today yet.</p>
                        ) : todayPosts.data.map((post) => (
                            <Link key={post.id} href={`/posts/${post.id}`} className="flex items-center justify-between rounded-lg border border-border p-3">
                                <div>
                                    <p className="font-medium">{post.campaign?.name}</p>
                                    <p className="text-sm text-muted-foreground">{post.topic?.title}</p>
                                </div>
                                <Badge>{post.status_label}</Badge>
                            </Link>
                        ))}
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle>Campaigns</CardTitle></CardHeader>
                    <CardContent className="grid gap-3">
                        {campaigns.data.slice(0, 5).map((campaign) => (
                            <Link key={campaign.id} href={`/campaigns/${campaign.id}`} className="flex items-center justify-between rounded-lg border border-border p-3">
                                <span>{campaign.name}</span>
                                <Badge variant={campaign.status === 'active' ? 'success' : 'default'}>{campaign.status_label}</Badge>
                            </Link>
                        ))}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
