<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpgradeRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UpgradeRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = UpgradeRequest::with(['client', 'processedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $upgradeRequests = $query->paginate(20);
        
        $stats = [
            'total' => UpgradeRequest::count(),
            'pending' => UpgradeRequest::where('status', 'pending')->count(),
            'approved' => UpgradeRequest::where('status', 'approved')->count(),
            'rejected' => UpgradeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.upgrade-requests.index', compact('upgradeRequests', 'stats'));
    }

    public function show(UpgradeRequest $upgradeRequest)
    {
        $upgradeRequest->load(['client', 'processedBy']);
        return view('admin.upgrade-requests.show', compact('upgradeRequest'));
    }

    public function approve(Request $request, UpgradeRequest $upgradeRequest)
    {
        \Log::info('Upgrade approval process started', [
            'upgrade_request_id' => $upgradeRequest->id,
            'client_id' => $upgradeRequest->client_id,
            'current_status' => $upgradeRequest->status,
            'requested_plan' => $upgradeRequest->requested_plan,
            'admin_id' => auth('admin')->id()
        ]);

        if (!$upgradeRequest->isPending()) {
            \Log::warning('Upgrade request not pending', ['status' => $upgradeRequest->status]);
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
            'subscription_months' => 'required|integer|min:1|max:12',
        ]);

        \Log::info('Validation passed', $request->only(['admin_notes', 'subscription_months']));

        try {
            // Update the upgrade request
            $upgradeRequest->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'processed_by' => auth('admin')->id(),
                'processed_at' => now(),
            ]);

            // Upgrade the client
            $client = $upgradeRequest->client;
            $planType = explode('_', $upgradeRequest->requested_plan)[0]; // Extract plan type (premium/enterprise)
            
            // Validate plan type
            if (!in_array($planType, ['free', 'premium', 'enterprise'])) {
                throw new \Exception('Invalid plan type: ' . $planType);
            }
            
            $expiresAt = null;
            if ($planType !== 'free') {
                $expiresAt = Carbon::now()->addMonths((int) $request->subscription_months);
            }

            // Update client with detailed logging
            $updateResult = $client->update([
                'plan_type' => $planType,
                'subscription_expires_at' => $expiresAt,
            ]);

            if (!$updateResult) {
                throw new \Exception('Failed to update client plan');
            }

            // Verify the update was successful
            $client->refresh();
            if ($client->plan_type !== $planType) {
                throw new \Exception('Client plan update verification failed');
            }

            \Log::info('Upgrade request approved successfully', [
                'upgrade_request_id' => $upgradeRequest->id,
                'client_id' => $client->id,
                'old_plan' => $upgradeRequest->current_plan,
                'new_plan' => $planType,
                'expires_at' => $expiresAt,
                'processed_by' => auth('admin')->id()
            ]);

            return redirect()->route('admin.upgrade-requests.show', $upgradeRequest)
                ->with('success', 'Upgrade request approved and client account updated successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Upgrade request approval failed', [
                'upgrade_request_id' => $upgradeRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to approve upgrade request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, UpgradeRequest $upgradeRequest)
    {
        if (!$upgradeRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $upgradeRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'processed_by' => auth('admin')->id(),
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.upgrade-requests.show', $upgradeRequest)
            ->with('success', 'Upgrade request has been rejected.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'requests' => 'required|array|min:1',
            'requests.*' => 'exists:upgrade_requests,id',
            'admin_notes' => 'nullable|string|max:500',
            'subscription_months' => 'required_if:action,approve|integer|min:1|max:12',
        ]);

        $requests = UpgradeRequest::whereIn('id', $request->requests)
            ->where('status', 'pending')
            ->get();

        $processed = 0;

        foreach ($requests as $upgradeRequest) {
            if ($request->action === 'approve') {
                // Update the upgrade request
                $upgradeRequest->update([
                    'status' => 'approved',
                    'admin_notes' => $request->admin_notes,
                    'processed_by' => auth('admin')->id(),
                    'processed_at' => now(),
                ]);

                // Upgrade the client
                $client = $upgradeRequest->client;
                $planType = explode('_', $upgradeRequest->requested_plan)[0];
                
                $expiresAt = null;
                if ($planType !== 'free') {
                    $expiresAt = Carbon::now()->addMonths((int) $request->subscription_months);
                }

                $client->update([
                    'plan_type' => $planType,
                    'subscription_expires_at' => $expiresAt,
                ]);
            } else {
                $upgradeRequest->update([
                    'status' => 'rejected',
                    'admin_notes' => $request->admin_notes,
                    'processed_by' => auth('admin')->id(),
                    'processed_at' => now(),
                ]);
            }

            $processed++;
        }

        return redirect()->route('admin.upgrade-requests.index')
            ->with('success', "Successfully {$request->action}d {$processed} upgrade request(s).");
    }
}