import { Link } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import PostShow from './show';
import type { Post } from '@/types/models';

export default function TodayPost({ post }: { post: Post | null }) {
    if (post) {
        return <PostShow post={post} />;
    }

    return (
        <AppLayout title="Today’s post">
            <Card>
                <CardContent className="p-8">
                    <h1 className="text-xl font-semibold">No post for today</h1>
                    <p className="mt-2 text-sm text-muted-foreground">Activate a campaign and wait for the scheduled generation, or create a campaign first.</p>
                    <Button asChild className="mt-4"><Link href="/campaigns">View campaigns</Link></Button>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
