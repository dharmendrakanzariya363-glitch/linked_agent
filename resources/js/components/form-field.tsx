import type { ReactNode } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

export function Field({
    label,
    error,
    hint,
    children,
}: {
    label: string;
    error?: string;
    hint?: string;
    children: ReactNode;
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            {children}
            {hint ? <p className="text-xs text-muted-foreground">{hint}</p> : null}
            {error ? <p className="text-sm text-destructive">{error}</p> : null}
        </div>
    );
}

export function TextField({
    label,
    error,
    className,
    ...props
}: React.InputHTMLAttributes<HTMLInputElement> & { label: string; error?: string }) {
    return (
        <Field label={label} error={error}>
            <Input className={cn(error && 'border-destructive', className)} {...props} />
        </Field>
    );
}

export function TextAreaField({
    label,
    error,
    className,
    ...props
}: React.TextareaHTMLAttributes<HTMLTextAreaElement> & { label: string; error?: string }) {
    return (
        <Field label={label} error={error}>
            <Textarea className={cn(error && 'border-destructive', className)} {...props} />
        </Field>
    );
}
