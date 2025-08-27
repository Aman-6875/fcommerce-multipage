<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerMessage;
use App\Models\FacebookPage;
use App\Services\FacebookGraphAPIService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    protected FacebookGraphAPIService $facebookService;

    public function __construct(FacebookGraphAPIService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    public function verify(Request $request)
    {
        Log::info('Webhook verification request received', ['params' => $request->all()]);
        $verifyToken = config('services.facebook.webhook_verify_token');

        if ($request->get('hub_mode') === 'subscribe' && $request->get('hub_verify_token') === $verifyToken) {
            Log::info('Webhook verified successfully');
            return response($request->get('hub_challenge'));
        }

        Log::warning('Webhook verification failed', ['request' => $request->all()]);
        return response('Verification failed', 403);
    }

    public function handle(Request $request)
    {
        Log::info('Facebook webhook POST request reached handle method.');
        Log::info('Facebook webhook received', ['payload' => $request->getContent()]);

        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature || !$this->facebookService->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Webhook signature verification failed');
            return response('Signature verification failed', 403);
        }

        $data = $request->json()->all();
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                $this->processEntry($entry);
            }
        }

        return response('OK', 200);
    }

    protected function processEntry(array $entry): void
    {
        $pageId = $entry['id'];
        $facebookPage = FacebookPage::where('page_id', $pageId)->where('is_connected', true)->first();

        if (!$facebookPage) {
            Log::warning('Webhook for unknown or disconnected page', ['page_id' => $pageId]);
            return;
        }

        if (isset($entry['messaging'])) {
            foreach ($entry['messaging'] as $messagingEvent) {
                $this->processMessagingEvent($facebookPage, $messagingEvent);
            }
        }
    }

    protected function processMessagingEvent(FacebookPage $facebookPage, array $event): void
    {
        if (isset($event['sender']['id']) && $event['sender']['id'] !== $facebookPage->page_id) {
            $senderId = $event['sender']['id'];
            $customer = $this->findOrCreateCustomer($facebookPage, $senderId);

            if (isset($event['message'])) {
                $this->handleIncomingMessage($customer, $event['message'], $event);
            } elseif (isset($event['postback'])) {
                $this->handlePostback($customer, $event['postback'], $event);
            }
        }
    }

    protected function findOrCreateCustomer(FacebookPage $facebookPage, string $facebookUserId): Customer
    {
        return Customer::firstOrCreate(
            [
                'client_id' => $facebookPage->client_id,
                'facebook_user_id' => $facebookUserId
            ],
            [
                'name' => 'Facebook User', // Default name, can be updated later
                'profile_data' => ['source_page_id' => $facebookPage->page_id],
                'status' => 'active'
            ]
        );
    }

    protected function handleIncomingMessage(Customer $customer, array $message, array $event): void
    {
        if (!isset($message['mid'])) {
            Log::warning('Received message event without a message ID (mid)', ['event' => $event]);
            return;
        }

        // Avoid processing duplicate messages
        if (CustomerMessage::whereJsonContains('message_data->facebook_message_id', $message['mid'])->exists()) {
            Log::info('Skipping duplicate message', ['mid' => $message['mid']]);
            return;
        }

        CustomerMessage::create([
            'customer_id' => $customer->id,
            'client_id' => $customer->client_id,
            'message_type' => 'incoming',
            'message_content' => $message['text'] ?? '[Attachment]',
            'attachments' => $message['attachments'] ?? [],
            'message_data' => [
                'facebook_message_id' => $message['mid'],
                'timestamp' => $event['timestamp']
            ],
            'is_read' => false
        ]);

        Log::info('Incoming message processed', ['customer_id' => $customer->id, 'mid' => $message['mid']]);
    }

    protected function handlePostback(Customer $customer, array $postback, array $event): void
    {
        CustomerMessage::create([
            'customer_id' => $customer->id,
            'client_id' => $customer->client_id,
            'message_type' => 'incoming',
            'message_content' => $postback['title'] ?? 'Postback',
            'message_data' => [
                'type' => 'postback',
                'payload' => $postback['payload'],
                'timestamp' => $event['timestamp']
            ],
            'is_read' => false
        ]);

        Log::info('Postback processed', ['customer_id' => $customer->id, 'payload' => $postback['payload']]);
    }
}
