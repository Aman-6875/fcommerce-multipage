<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConversationState;
use App\Models\Workflow;
use App\Services\WorkflowEngine;
use App\Services\ProductSelectorService;
use App\Services\WorkflowMessageService;
use App\Services\FacebookGraphAPIService;

class DebugWorkflow extends Command
{
    protected $signature = 'workflow:debug {action=status} {conversation_id?}';
    protected $description = 'Debug workflow system step by step';

    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'status':
                $this->showWorkflowStatus();
                break;
            case 'steps':
                $this->showWorkflowSteps();
                break;
            case 'conversation':
                $conversationId = $this->argument('conversation_id');
                if ($conversationId) {
                    $this->showConversationDetails($conversationId);
                } else {
                    $this->showAllConversations();
                }
                break;
            case 'simulate':
                $this->simulateMessage();
                break;
            default:
                $this->info('Available actions: status, steps, conversation, simulate');
        }
    }

    protected function showWorkflowStatus()
    {
        $this->info('=== WORKFLOW SYSTEM STATUS ===');
        
        // Active workflows
        $workflows = Workflow::where('is_active', true)->get();
        $this->info("Active Workflows: " . $workflows->count());
        foreach ($workflows as $workflow) {
            $this->line("- ID: {$workflow->id}, Name: {$workflow->name}, Steps: " . $workflow->getTotalSteps());
        }
        
        // Active conversations
        $conversations = ConversationState::where('status', 'active')->get();
        $this->info("\nActive Conversations: " . $conversations->count());
        foreach ($conversations as $conversation) {
            $currentStep = $conversation->getCurrentStep();
            $this->line("- ID: {$conversation->id}, Customer: {$conversation->customer_id}, Step: {$conversation->current_step_index} ({$currentStep['id']})");
        }
    }

    protected function showWorkflowSteps()
    {
        $workflow = Workflow::first();
        if (!$workflow) {
            $this->error('No workflow found');
            return;
        }

        $this->info("=== WORKFLOW STEPS: {$workflow->name} ===");
        $steps = $workflow->getSteps();
        
        foreach ($steps as $index => $step) {
            $this->line("{$index}: {$step['id']} ({$step['type']})");
            if (isset($step['labels']['en']['title'])) {
                $this->line("   Title: {$step['labels']['en']['title']}");
            }
        }
    }

    protected function showConversationDetails($conversationId)
    {
        $conversation = ConversationState::find($conversationId);
        if (!$conversation) {
            $this->error("Conversation {$conversationId} not found");
            return;
        }

        $this->info("=== CONVERSATION DETAILS ===");
        $this->line("ID: {$conversation->id}");
        $this->line("Customer: {$conversation->customer_id}");
        $this->line("Status: {$conversation->status}");
        $this->line("Current Step: {$conversation->current_step_index}");
        
        $currentStep = $conversation->getCurrentStep();
        if ($currentStep) {
            $this->line("Step ID: {$currentStep['id']}");
            $this->line("Step Type: {$currentStep['type']}");
        }

        $this->info("\nStep Responses:");
        $responses = $conversation->getAllResponses();
        foreach ($responses as $stepId => $response) {
            $this->line("- {$stepId}: " . json_encode($response));
        }

        $this->info("\nTemp Data:");
        $tempData = $conversation->temp_data;
        if ($tempData) {
            foreach ($tempData as $key => $value) {
                $this->line("- {$key}: " . json_encode($value));
            }
        }
    }

    protected function showAllConversations()
    {
        $conversations = ConversationState::with('customer')->get();
        $this->info('=== ALL CONVERSATIONS ===');
        
        foreach ($conversations as $conversation) {
            $currentStep = $conversation->getCurrentStep();
            $this->line("ID: {$conversation->id} | Customer: {$conversation->customer_id} | Status: {$conversation->status} | Step: {$conversation->current_step_index} ({$currentStep['id']})");
        }
    }

    protected function simulateMessage()
    {
        $conversations = ConversationState::where('status', 'active')->get();
        if ($conversations->isEmpty()) {
            $this->error('No active conversations to simulate');
            return;
        }

        $conversation = $conversations->first();
        $message = $this->ask('Enter message to simulate');
        
        $this->info("Simulating message '{$message}' for conversation {$conversation->id}");
        
        $workflowEngine = new WorkflowEngine(
            new ProductSelectorService(),
            new WorkflowMessageService(),
            new FacebookGraphAPIService()
        );
        
        $result = $workflowEngine->processStepInput($conversation, $message);
        
        $this->info('=== SIMULATION RESULT ===');
        $this->line('Success: ' . ($result['success'] ? 'YES' : 'NO'));
        $this->line('Message: ' . ($result['message'] ?? 'No message'));
        $this->line('Show Next: ' . ($result['show_next'] ?? false ? 'YES' : 'NO'));
        $this->line('Completed: ' . ($result['completed'] ?? false ? 'YES' : 'NO'));
        
        if (isset($result['next_step'])) {
            $this->line('Next Step: ' . $result['next_step']['id']);
        }
    }
}