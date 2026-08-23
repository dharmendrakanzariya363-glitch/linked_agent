import { Form } from '@inertiajs/react';
import { useState } from 'react';
import { AppLayout } from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { TextField } from '@/components/form-field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Campaign, LinkedInAccount } from '@/types/models';

export default function CampaignForm({
    campaign,
    accounts,
    timezones,
}: {
    campaign?: Campaign;
    accounts: { data: LinkedInAccount[] } | LinkedInAccount[];
    timezones: string[];
}) {
    const accountList = Array.isArray(accounts) ? accounts : accounts.data;
    const [topics, setTopics] = useState<string[]>(campaign?.topics?.map((topic) => topic.title) ?? ['Laravel tips']);

    return (
        <AppLayout title={campaign ? 'Edit campaign' : 'New campaign'}>
            <h1 className="mb-6 text-2xl font-semibold">{campaign ? 'Edit campaign' : 'New campaign'}</h1>
            <Form
                action={campaign ? `/campaigns/${campaign.id}` : '/campaigns'}
                method={campaign ? 'put' : 'post'}
                className="grid max-w-2xl gap-4"
            >
                {({ errors, processing }) => (
                    <>
                        <TextField label="Name" name="name" defaultValue={campaign?.name} required error={errors.name} />
                        <div className="grid gap-2">
                            <Label>LinkedIn account</Label>
                            <select name="linkedin_account_id" defaultValue={campaign?.linkedin_account_id} className="h-10 rounded-md border border-input bg-card px-3 text-sm" required>
                                {accountList.map((account) => (
                                    <option key={account.id} value={account.id}>{account.name}</option>
                                ))}
                            </select>
                            {errors.linkedin_account_id ? <p className="text-sm text-destructive">{errors.linkedin_account_id}</p> : null}
                        </div>
                        <div className="grid gap-2">
                            <Label>Timezone</Label>
                            <select name="timezone" defaultValue={campaign?.timezone ?? 'UTC'} className="h-10 rounded-md border border-input bg-card px-3 text-sm">
                                {timezones.map((timezone) => <option key={timezone}>{timezone}</option>)}
                            </select>
                        </div>
                        <TextField label="Daily post time" type="time" name="daily_post_time" defaultValue={campaign?.daily_post_time ?? '10:00'} required />
                        <TextField label="Start date" type="date" name="start_date" defaultValue={campaign?.start_date} required />
                        <TextField label="End date" type="date" name="end_date" defaultValue={campaign?.end_date ?? ''} />
                        <div className="grid gap-2">
                            <Label>Content type</Label>
                            <select name="content_type" defaultValue={campaign?.content_type ?? 'description'} className="h-10 rounded-md border border-input bg-card px-3 text-sm">
                                <option value="description">Description only</option>
                                <option value="description_image">Description + image</option>
                            </select>
                        </div>
                        <div className="grid gap-2">
                            <Label>Topics</Label>
                            {topics.map((topic, index) => (
                                <div key={index} className="flex gap-2">
                                    <Input name="topics[]" value={topic} onChange={(event) => setTopics((items) => items.map((item, i) => (i === index ? event.target.value : item)))} />
                                    <Button type="button" variant="outline" onClick={() => setTopics((items) => items.filter((_, i) => i !== index))}>Remove</Button>
                                </div>
                            ))}
                            <Button type="button" variant="secondary" onClick={() => setTopics((items) => [...items, ''])}>Add topic</Button>
                        </div>
                        <Button disabled={processing}>{processing ? 'Saving...' : 'Save campaign'}</Button>
                    </>
                )}
            </Form>
        </AppLayout>
    );
}
