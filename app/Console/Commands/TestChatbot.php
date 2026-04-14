<?php

namespace App\Console\Commands;

use App\Models\AgentConversation;
use App\Models\Tenant;
use App\Services\ChatbotService;
use Illuminate\Console\Command;

class TestChatbot extends Command
{
    protected $signature = 'test:chatbot {tenant_id} {--query=What is the fee for Class 3?}';

    protected $description = 'Test the AI chatbot with reasoning queries';

    public function handle(ChatbotService $chatbot)
    {
        $tenantId = $this->argument('tenant_id');
        $query = $this->option('query');

        // Initialize tenancy
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            $this->error("Tenant not found: {$tenantId}");
            return 1;
        }

        tenancy()->initialize($tenant);

        $this->info("Testing chatbot for tenant: {$tenant->id}");
        $this->info("Query: {$query}");
        $this->newLine();

        try {
            // Create a test conversation
            $conversation = $chatbot->getOrCreateConversation(
                userId: null,
                title: 'Test Conversation - ' . now()->toDateTimeString()
            );

            $this->info("Created conversation: {$conversation->id}");
            $this->newLine();

            // Process the query
            $this->info("Processing query...");
            $response = $chatbot->processUserMessage($conversation->id, $query);

            // Display results
            $this->line("<fg=green>═══════════════════════════════════════════════════════════</>");
            $this->line("<fg=cyan;options=bold>ANSWER:</>");
            $this->line($response['answer']);
            $this->newLine();

            $this->line("<fg=cyan;options=bold>REASONING:</>");
            $this->line($response['reasoning']);
            $this->newLine();

            $this->line("<fg=cyan;options=bold>CONFIDENCE:</> " . ($response['confidence'] * 100) . "%");
            $this->newLine();

            $this->line("<fg=cyan;options=bold>SOURCES:</>");
            foreach ($response['sources'] as $source) {
                $this->line("  • {$source['document_title']} ({$source['category']}) - Similarity: {$source['similarity']}");
            }
            $this->line("<fg=green>═══════════════════════════════════════════════════════════</>");

            $this->newLine();
            $this->info("✓ Test completed successfully!");

            // Suggest more test queries
            $this->newLine();
            $this->comment("Try these reasoning-based queries:");
            $this->line("  php artisan test:chatbot {$tenantId} --query=\"What is the fee for Class 3?\"");
            $this->line("  php artisan test:chatbot {$tenantId} --query=\"My child is 8 years old, what is the tuition?\"");
            $this->line("  php artisan test:chatbot {$tenantId} --query=\"When is the payment deadline?\"");
            $this->line("  php artisan test:chatbot {$tenantId} --query=\"What is the PhD fee?\"");

            return 0;

        } catch (\Exception $e) {
            $this->error("Test failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
