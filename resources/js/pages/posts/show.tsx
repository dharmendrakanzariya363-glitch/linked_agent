import { Form, router } from '@inertiajs/react';
import { useEffect } from 'react';
import { AppLayout } from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TextAreaField, TextField } from '@/components/form-field';
import type { Post } from '@/types/models';

export default function PostShow({ post }: { post: Post }) {
    useEffect(() => {
        if (!['generating', 'publishing'].includes(post.status)) {
            return;
        }

        const interval = window.setInterval(() => {
            router.reload({ only: ['post'] });
        }, 4000);

        return () => window.clearInterval(interval);
    }, [post.status, post.id]);

    const busy = ['generating', 'publishing'].includes(post.status);

    return (
        <AppLayout title={post.campaign?.name ?? 'Post'}>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-semibold">{post.campaign?.name}</h1>
                    <p className="text-sm text-muted-foreground">{post.topic?.title} · {post.scheduled_for}</p>
                </div>
                <Badge variant={post.status === 'published' ? 'success' : post.status === 'failed' ? 'danger' : 'default'}>
                    {post.status_label}
                </Badge>
            </div>
            {post.last_error ? <p className="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{post.last_error}</p> : null}
            <div className="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
                <div className="grid gap-6">
                    <Card>
                        <CardHeader><CardTitle>Preview</CardTitle></CardHeader>
                        <CardContent>
                            <p className="whitespace-pre-wrap text-sm leading-6">{post.current_version?.description}</p>
                            {post.current_version?.image ? (
                                <img src={post.current_version.image.url} alt="" className="mt-4 rounded-xl border border-border" />
                            ) : null}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle>Manual edit</CardTitle></CardHeader>
                        <CardContent>
                            <Form action={`/posts/${post.id}`} method="put" className="grid gap-4">
                                {({ processing }) => (
                                    <>
                                        <TextAreaField label="Description" name="description" defaultValue={post.current_version?.description} disabled={busy} />
                                        <Button disabled={processing || busy}>Save version</Button>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle>Ask AI to edit</CardTitle></CardHeader>
                        <CardContent className="grid gap-4">
                            <Form action={`/posts/${post.id}/ai-edit`} method="post" className="grid gap-4">
                                {({ processing }) => (
                                    <>
                                        <TextAreaField label="Instruction" name="instruction" placeholder="Make this more practical for senior Laravel developers." disabled={busy} />
                                        <Button disabled={processing || busy} variant="secondary">Rewrite with AI</Button>
                                    </>
                                )}
                            </Form>
                            {post.campaign?.requires_image ? (
                                <Form action={`/posts/${post.id}/ai-edit-image`} method="post" className="grid gap-4">
                                    {({ processing }) => (
                                        <>
                                            <TextField label="Image instruction" name="instruction" placeholder="Make it darker and more cinematic." disabled={busy} />
                                            <Button disabled={processing || busy} variant="outline">Edit image with AI</Button>
                                        </>
                                    )}
                                </Form>
                            ) : null}
                        </CardContent>
                    </Card>
                </div>
                <div className="grid gap-6">
                    <Card>
                        <CardHeader><CardTitle>Actions</CardTitle></CardHeader>
                        <CardContent className="grid gap-2">
                            <Button variant="outline" disabled={busy} onClick={() => router.post(`/posts/${post.id}/regenerate`)}>Regenerate description</Button>
                            {post.campaign?.requires_image ? (
                                <Button variant="outline" disabled={busy} onClick={() => router.post(`/posts/${post.id}/regenerate-image`)}>Regenerate image</Button>
                            ) : null}
                            {post.status === 'ready' ? (
                                <Button onClick={() => router.post(`/posts/${post.id}/approve`)}>Approve and publish</Button>
                            ) : null}
                            {post.status === 'failed' ? (
                                <Button onClick={() => router.post(`/posts/${post.id}/retry`)}>Retry publish</Button>
                            ) : null}
                            {post.published_url ? (
                                <a href={post.published_url} className="text-sm text-primary" target="_blank" rel="noreferrer">View on LinkedIn</a>
                            ) : null}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle>Versions</CardTitle></CardHeader>
                        <CardContent className="grid gap-3">
                            {(post.versions ?? []).map((version) => (
                                <div key={version.id} className={`rounded-lg border p-3 ${version.id === post.current_version_id ? 'border-primary' : 'border-border'}`}>
                                    <p className="text-sm font-medium">v{version.version_number} · {version.type_label}</p>
                                    <p className="mt-1 line-clamp-4 text-xs text-muted-foreground">{version.description}</p>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
