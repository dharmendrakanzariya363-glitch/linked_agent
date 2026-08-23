export type LinkedInAccount = {
    id: number;
    name: string;
    email: string | null;
    headline: string | null;
    profile_url: string | null;
    avatar_url: string | null;
    connected: boolean;
    token_expires_at: string | null;
};

export type CampaignTopic = {
    id: number;
    title: string;
    is_active: boolean;
    sort_order: number;
    last_used_at: string | null;
};

export type Campaign = {
    id: number;
    name: string;
    timezone: string;
    daily_post_time: string;
    start_date: string;
    end_date: string | null;
    content_type: 'description' | 'description_image';
    content_type_label: string;
    status: 'draft' | 'active' | 'paused' | 'completed';
    status_label: string;
    linkedin_account_id: number;
    linkedin_account?: LinkedInAccount;
    topics?: CampaignTopic[];
    posts_count?: number;
    created_at: string | null;
};

export type PostImage = {
    id: number;
    url: string;
    prompt: string | null;
    generation_type: string;
};

export type PostVersion = {
    id: number;
    version_number: number;
    type: string;
    type_label: string;
    prompt: string | null;
    description: string;
    image?: PostImage | null;
    created_at: string | null;
};

export type Post = {
    id: number;
    status: 'generating' | 'ready' | 'approved' | 'publishing' | 'published' | 'failed';
    status_label: string;
    scheduled_for: string;
    generated_at: string | null;
    approved_at: string | null;
    published_at: string | null;
    published_url: string | null;
    last_error: string | null;
    current_version_id: number | null;
    campaign?: {
        id: number;
        name: string;
        content_type: string;
        requires_image: boolean;
    };
    topic?: { id: number; title: string } | null;
    current_version?: PostVersion | null;
    versions?: PostVersion[];
    created_at: string | null;
};

export type AppNotification = {
    id: string;
    title: string;
    message: string;
    url: string;
    post_id: number | null;
    read_at: string | null;
    created_at: string | null;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};
