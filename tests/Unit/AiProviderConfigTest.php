<?php

namespace Tests\Unit;

use App\Agents\ContentAgent;
use Tests\TestCase;

class AiProviderConfigTest extends TestCase
{
    public function test_content_agent_follows_configured_provider(): void
    {
        config(['ai.default' => 'gemini']);
        $this->assertSame('gemini', (new ContentAgent)->provider());

        config(['ai.default' => 'openai']);
        $this->assertSame('openai', (new ContentAgent)->provider());
    }
}
