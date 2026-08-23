import { AppLayout } from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { useAppearance, type Appearance } from '@/hooks/use-appearance';

const options: Appearance[] = ['light', 'dark', 'system'];

export default function AppearanceSettings() {
    const { appearance, setAppearance } = useAppearance();

    return (
        <AppLayout title="Appearance">
            <h1 className="mb-6 text-2xl font-semibold">Appearance</h1>
            <div className="flex gap-3">
                {options.map((option) => (
                    <Button key={option} variant={appearance === option ? 'default' : 'outline'} onClick={() => setAppearance(option)}>
                        {option}
                    </Button>
                ))}
            </div>
        </AppLayout>
    );
}
