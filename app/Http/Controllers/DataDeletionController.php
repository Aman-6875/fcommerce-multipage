<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DataDeletionController extends Controller
{
    /**
     * Show data deletion request form
     */
    public function show()
    {
        return view('legal.data-deletion');
    }

    /**
     * Process data deletion request
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facebook_user_id' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'business_pages' => 'nullable|string',
            'deletion_reason' => 'nullable|string',
            'confirm_deletion' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Log the deletion request
            Log::info('Data deletion request submitted', [
                'facebook_user_id' => $request->facebook_user_id,
                'email' => $request->email,
                'phone' => $request->phone,
                'business_pages' => $request->business_pages,
                'deletion_reason' => $request->deletion_reason,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);

            // Find and mark customer(s) for deletion
            $customers = Customer::where('facebook_user_id', $request->facebook_user_id)
                ->orWhere('email', $request->email)
                ->get();

            if ($customers->isEmpty()) {
                return back()->with('warning', 'No data found matching your request. If you believe this is an error, please contact us directly.');
            }

            foreach ($customers as $customer) {
                // Mark customer for deletion (you can implement actual deletion or flag for review)
                $customer->update([
                    'status' => 'deletion_requested',
                    'custom_fields' => array_merge($customer->custom_fields ?? [], [
                        'deletion_requested_at' => now()->toISOString(),
                        'deletion_reason' => $request->deletion_reason,
                        'deletion_email' => $request->email,
                        'deletion_ip' => $request->ip()
                    ])
                ]);

                Log::info('Customer marked for deletion', [
                    'customer_id' => $customer->id,
                    'facebook_user_id' => $customer->facebook_user_id,
                    'client_id' => $customer->client_id
                ]);
            }

            // Send notification to admin
            $this->notifyAdminOfDeletionRequest($request->all(), $customers->count());

            // Send confirmation to user
            $this->sendDeletionConfirmation($request->email, $customers->count());

            return redirect()->route('data-deletion')
                ->with('success', 'Your data deletion request has been submitted successfully. You will receive a confirmation email shortly. Processing may take up to 30 days.');

        } catch (\Exception $e) {
            Log::error('Data deletion request failed', [
                'error' => $e->getMessage(),
                'facebook_user_id' => $request->facebook_user_id,
                'email' => $request->email
            ]);

            return back()->with('error', 'There was an error processing your request. Please try again or contact us directly.');
        }
    }

    /**
     * Process data deletion (admin function)
     */
    public function processDelete($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            
            if ($customer->status !== 'deletion_requested') {
                return response()->json(['error' => 'Customer not marked for deletion'], 400);
            }

            // Delete customer messages
            CustomerMessage::where('customer_id', $customer->id)->delete();
            
            // Log the deletion
            Log::info('Customer data deleted', [
                'customer_id' => $customer->id,
                'facebook_user_id' => $customer->facebook_user_id,
                'client_id' => $customer->client_id,
                'deletion_processed_at' => now()
            ]);
            
            // Delete customer record
            $customer->delete();

            return response()->json(['success' => 'Customer data deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Data deletion processing failed', [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to process deletion'], 500);
        }
    }

    /**
     * Notify admin of deletion request
     */
    private function notifyAdminOfDeletionRequest($requestData, $customerCount)
    {
        try {
            // You can implement email notification here
            Log::info('Admin notification - Data deletion request', [
                'facebook_user_id' => $requestData['facebook_user_id'],
                'email' => $requestData['email'],
                'customers_affected' => $customerCount,
                'reason' => $requestData['deletion_reason'] ?? 'Not specified',
                'business_pages' => $requestData['business_pages'] ?? 'Not specified'
            ]);
            
            // TODO: Implement actual email notification to admin
            // Mail::to('admin@softstation.xyz')->send(new DeletionRequestNotification($requestData, $customerCount));
            
        } catch (\Exception $e) {
            Log::error('Failed to notify admin of deletion request', [
                'error' => $e->getMessage(),
                'request_data' => $requestData
            ]);
        }
    }

    /**
     * Send confirmation to user
     */
    private function sendDeletionConfirmation($email, $customerCount)
    {
        try {
            Log::info('User confirmation - Data deletion request received', [
                'email' => $email,
                'customers_affected' => $customerCount,
                'confirmation_sent_at' => now()
            ]);

            // TODO: Implement actual email confirmation
            // Mail::to($email)->send(new DeletionConfirmationEmail($customerCount));
            
        } catch (\Exception $e) {
            Log::error('Failed to send deletion confirmation', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
        }
    }
}