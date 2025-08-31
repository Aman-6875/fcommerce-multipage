<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\UpgradeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpgradeController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();
        $currentRequests = $client->upgradeRequests()->orderBy('created_at', 'desc')->get();
        $plans = UpgradeRequest::getPlanPrices();
        $paymentMethods = UpgradeRequest::getPaymentMethods();

        return view('client.upgrade.index', compact('currentRequests', 'plans', 'paymentMethods'));
    }

    public function create()
    {
        $client = auth('client')->user();
        
        // Check if user already has a pending request
        $pendingRequest = $client->upgradeRequests()->where('status', 'pending')->first();
        if ($pendingRequest) {
            return redirect()->route('client.upgrade.index')
                ->with('error', __('client.pending_upgrade_request_exists'));
        }

        $plans = UpgradeRequest::getPlanPrices();
        $paymentMethods = UpgradeRequest::getPaymentMethods();

        return view('client.upgrade.create', compact('plans', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $client = auth('client')->user();

        // Check if user already has a pending request
        $pendingRequest = $client->upgradeRequests()->where('status', 'pending')->first();
        if ($pendingRequest) {
            return redirect()->route('client.upgrade.index')
                ->with('error', __('client.pending_upgrade_request_exists'));
        }

        $request->validate([
            'requested_plan' => 'required|in:premium,enterprise',
            'billing_cycle' => 'required|in:monthly,yearly',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string|max:255',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string|max:500',
        ]);

        $plans = UpgradeRequest::getPlanPrices();
        $planDetails = $plans[$request->requested_plan];
        $amount = $planDetails[$request->billing_cycle];

        $data = [
            'client_id' => $client->id,
            'current_plan' => $client->plan_type,
            'requested_plan' => $request->requested_plan . '_' . $request->billing_cycle,
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'notes' => $request->notes,
        ];

        // Handle payment proof upload
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = 'upgrade_proof_' . $client->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('upgrade_proofs', $filename, 'public');
            $data['payment_proof'] = $path;
        }

        UpgradeRequest::create($data);

        return redirect()->route('client.upgrade.index')
            ->with('success', __('client.upgrade_request_submitted'));
    }

    public function show(UpgradeRequest $upgradeRequest)
    {
        // Ensure client can only view their own requests
        if ($upgradeRequest->client_id !== auth('client')->id()) {
            abort(403);
        }

        return view('client.upgrade.show', compact('upgradeRequest'));
    }
}