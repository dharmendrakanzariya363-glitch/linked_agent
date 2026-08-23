import { Link, router } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Campaign, Paginated, Post } from '@/types/models';

export default function CampaignShow({ campaign, posts }: { campaign: Campaign; posts: Paginated<Post> }) {
    return (
        <AppLayout title={campaign.name}>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-semibold">{campaign.name}</h1>
                    <p className="text-sm text-muted-foreground">{campaign.daily_post_time} · {campaign.timezone}</p>
                </div>
                <div className="flex gap-2">
                    <Button asChild variant="secondary"><Link href={`/campaigns/${campaign.id}/edit`}>Edit</Link></Button>
                    {campaign.status === 'active' ? (
                        <Button variant="outline" onClick={() => router.post(`/campaigns/${campaign.id}/pause`)}>Pause</Button>
                    ) : (
                        <Button onClick={() => router.post(`/campaigns/${campaign.id}/activate`)}>Activate</Button>
                    )}
                </div>
            </div>
            <div className="grid gap-4 lg:grid-cols-3">
                <Card>
                    <CardHeader><CardTitle>Status</CardTitle></CardHeader>
                    <CardContent><Badge variant={campaign.status === 'active' ? 'success' : 'default'}>{campaign.status_label}</Badge></CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle>Content</CardTitle></CardHeader>
                    <CardContent>{campaign.content_type_label}</CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle>LinkedIn</CardTitle></CardHeader>
                    <CardContent>{campaign.linkedin_account?.name}</CardContent>
                </Card>
            </div>
            <Card className="mt-6">
                <CardHeader><CardTitle>Topics</CardTitle></CardHeader>
                <CardContent className="flex flex-wrap gap-2">
                    {campaign.topics?.map((topic) => (
                        <Badge key={topic.id} variant={topic.is_active ? 'default' : 'outline'}>{topic.title}</Badge>
                    ))}
                </CardContent>
            </Card>
            <Card className="mt-6">
                <CardHeader><CardTitle>Posts</CardTitle></CardHeader>
                <CardContent className="grid gap-3">
                    {posts.data.length === 0 ? <p className="text-sm text-muted-foreground">No posts yet.</p> : posts.data.map((post) => (
                        <Link key={post.id} href={`/posts/${post.id}`} className="flex items-center justify-between rounded-lg border border-border p-3">
                            <span>{post.scheduled_for} · {post.topic?.title}</span>
                            <Badge>{post.status_label}</Badge>
                        </Link>
                    ))}
                </CardContent>
            </Card>
        </AppLayout>
    );
}
