import { Link } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import type { Paginated, Post } from '@/types/models';

export default function PostsIndex({ posts }: { posts: Paginated<Post> }) {
    return (
        <AppLayout title="Post history">
            <h1 className="mb-6 text-2xl font-semibold">Post history</h1>
            <div className="grid gap-3">
                {posts.data.length === 0 ? (
                    <Card><CardContent className="p-8 text-sm text-muted-foreground">No posts yet.</CardContent></Card>
                ) : posts.data.map((post) => (
                    <Link key={post.id} href={`/posts/${post.id}`}>
                        <Card>
                            <CardContent className="flex items-center justify-between p-5">
                                <div>
                                    <p className="font-medium">{post.campaign?.name}</p>
                                    <p className="text-sm text-muted-foreground">{post.scheduled_for} · {post.topic?.title}</p>
                                </div>
                                <Badge>{post.status_label}</Badge>
                            </CardContent>
                        </Card>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}
