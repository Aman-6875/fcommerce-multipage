<?php

namespace App\Http\Controllers\Client\ChatBot;

use App\Http\Controllers\Controller;
use App\Models\BusinessInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    public function index()
    {
        $client = Auth::user();
        $pageId = $client->getSelectedPageId();
        
        if (!$pageId) {
            return redirect()->route('client.facebook.index')->with('warning', 'Please connect a Facebook page first.');
        }
        
        $inquiries = BusinessInquiry::forPage($pageId)
            ->with(['customer'])
            ->latest()
            ->paginate(20);
        
        $stats = [
            'total' => BusinessInquiry::forPage($pageId)->count(),
            'pending' => BusinessInquiry::forPage($pageId)->byStatus('pending')->count(),
            'completed' => BusinessInquiry::forPage($pageId)->byStatus('completed')->count(),
            'today' => BusinessInquiry::forPage($pageId)->whereDate('created_at', today())->count(),
        ];
        
        return view('client.chat-bot.inquiries.index', compact('inquiries', 'stats'));
    }
    
    public function show($id)
    {
        $client = Auth::user();
        $pageId = $client->getSelectedPageId();
        
        if (!$pageId) {
            return response()->json(['error' => 'No page selected'], 400);
        }
        
        $inquiry = BusinessInquiry::forPage($pageId)->with(['customer'])->findOrFail($id);
        
        return view('client.chat-bot.inquiries.show', compact('inquiry'))->render();
    }
    
    public function updateStatus(Request $request, $id)
    {
        $client = Auth::user();
        $pageId = $client->getSelectedPageId();
        
        if (!$pageId) {
            return response()->json(['error' => 'No page selected'], 400);
        }
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);
        
        $inquiry = BusinessInquiry::forPage($pageId)->findOrFail($id);
        $inquiry->update(['status' => $request->status]);
        
        return response()->json(['success' => true]);
    }
    
    public function destroy($id)
    {
        $client = Auth::user();
        $pageId = $client->getSelectedPageId();
        
        if (!$pageId) {
            return redirect()->route('client.facebook.index')->with('warning', 'Please connect a Facebook page first.');
        }
        
        $inquiry = BusinessInquiry::forPage($pageId)->findOrFail($id);
        $inquiry->delete();
        
        return redirect()->route('client.chat-bot.inquiries.index')
                        ->with('success', __('client.inquiry_deleted_successfully'));
    }
}