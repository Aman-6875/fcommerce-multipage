<?php

namespace App\Http\Controllers\Client\ChatBot;

use App\Http\Controllers\Controller;
use App\Models\PageFaq;
use App\Models\PageBusinessConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $client = auth('client')->user();
        $selectedPageId = $request->get('page_id') ?: $client->getSelectedPageId();
        $facebookPages = $client->facebookPages;
        
        if (!$selectedPageId && $facebookPages->count() > 0) {
            $selectedPageId = $facebookPages->first()->id;
        }

        $faqs = collect();
        if ($selectedPageId) {
            $faqs = PageFaq::forPage($selectedPageId)
                ->active()
                ->ordered()
                ->get();
        }

        return view('client.chat-bot.faqs.index', compact('faqs', 'facebookPages', 'selectedPageId'));
    }

    public function create(Request $request)
    {
        $client = auth('client')->user();
        $selectedPageId = $request->get('page_id') ?: $client->getSelectedPageId();
        $facebookPages = $client->facebookPages;
        
        if (!$selectedPageId) {
            return redirect()->route('client.chat-bot.faqs.index')
                ->with('error', 'Please select a Facebook page first.');
        }

        return view('client.chat-bot.faqs.create', compact('facebookPages', 'selectedPageId'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'question_en' => 'required|string|max:500',
            'question_bn' => 'nullable|string|max:500',
            'answer_en' => 'required|string|max:2000',
            'answer_bn' => 'nullable|string|max:2000',
            'action_type' => 'required|in:answer_only,start_inquiry,show_menu,custom',
            'inquiry_type' => 'nullable|string|in:order,booking,quote,consultation,appointment,reservation,purchase,inquiry',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verify page ownership
        $client = auth('client')->user();
        $facebookPage = $client->facebookPages()->find($request->facebook_page_id);
        
        if (!$facebookPage) {
            return back()->with('error', 'Invalid Facebook page selected.')->withInput();
        }

        // Get next display order and quick reply text
        $nextOrder = PageFaq::forPage($request->facebook_page_id)->max('display_order') + 1;
        $quickReplyText = PageFaq::getNextQuickReplyText($request->facebook_page_id);

        PageFaq::create([
            'facebook_page_id' => $request->facebook_page_id,
            'question_en' => $request->question_en,
            'question_bn' => $request->question_bn,
            'answer_en' => $request->answer_en,
            'answer_bn' => $request->answer_bn,
            'display_order' => $nextOrder,
            'is_active' => $request->boolean('is_active', true),
            'quick_reply_text' => $quickReplyText,
            'action_type' => $request->action_type,
            'inquiry_type' => $request->inquiry_type
        ]);

        return redirect()->route('client.chat-bot.faqs.index', ['page_id' => $request->facebook_page_id])
            ->with('success', 'FAQ created successfully!');
    }

    public function edit(PageFaq $faq)
    {
        // Verify ownership
        $client = auth('client')->user();
        if (!$client->facebookPages()->where('id', $faq->facebook_page_id)->exists()) {
            abort(404);
        }

        $facebookPages = $client->facebookPages;
        
        return view('client.chat-bot.faqs.edit', compact('faq', 'facebookPages'));
    }

    public function update(Request $request, PageFaq $faq)
    {
        // Verify ownership
        $client = auth('client')->user();
        if (!$client->facebookPages()->where('id', $faq->facebook_page_id)->exists()) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'question_en' => 'required|string|max:500',
            'question_bn' => 'nullable|string|max:500',
            'answer_en' => 'required|string|max:2000',
            'answer_bn' => 'nullable|string|max:2000',
            'action_type' => 'required|in:answer_only,start_inquiry,show_menu,custom',
            'inquiry_type' => 'nullable|string|in:order,booking,quote,consultation,appointment,reservation,purchase,inquiry',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $faq->update([
            'question_en' => $request->question_en,
            'question_bn' => $request->question_bn,
            'answer_en' => $request->answer_en,
            'answer_bn' => $request->answer_bn,
            'action_type' => $request->action_type,
            'inquiry_type' => $request->inquiry_type,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('client.chat-bot.faqs.index', ['page_id' => $faq->facebook_page_id])
            ->with('success', 'FAQ updated successfully!');
    }

    public function destroy(PageFaq $faq)
    {
        // Verify ownership
        $client = auth('client')->user();
        if (!$client->facebookPages()->where('id', $faq->facebook_page_id)->exists()) {
            abort(404);
        }

        $pageId = $faq->facebook_page_id;
        $faq->delete();

        return redirect()->route('client.chat-bot.faqs.index', ['page_id' => $pageId])
            ->with('success', 'FAQ deleted successfully!');
    }

    public function toggleStatus(Request $request, PageFaq $faq)
    {
        // Verify ownership
        $client = auth('client')->user();
        if (!$client->facebookPages()->where('id', $faq->facebook_page_id)->exists()) {
            abort(404);
        }

        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $faq->update(['is_active' => $request->is_active]);

        $status = $faq->is_active ? 'activated' : 'deactivated';
        return response()->json([
            'success' => true,
            'message' => "FAQ {$status} successfully!",
            'is_active' => $faq->is_active
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'faq_ids' => 'required|array',
            'faq_ids.*' => 'exists:page_faqs,id'
        ]);

        $client = auth('client')->user();
        
        foreach ($request->faq_ids as $index => $faqId) {
            $faq = PageFaq::find($faqId);
            
            // Verify ownership
            if ($faq && $client->facebookPages()->where('id', $faq->facebook_page_id)->exists()) {
                $faq->update(['display_order' => $index + 1]);
            }
        }

        return response()->json(['success' => true, 'message' => 'FAQ order updated successfully!']);
    }

    public function quickSetup(Request $request)
    {
        $request->validate([
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'business_type' => 'required|string'
        ]);

        $client = auth('client')->user();
        $facebookPage = $client->facebookPages()->find($request->facebook_page_id);
        
        if (!$facebookPage) {
            return response()->json(['success' => false, 'message' => 'Invalid Facebook page.']);
        }

        // Clear existing FAQs
        PageFaq::forPage($request->facebook_page_id)->delete();

        // Create default FAQs based on business type
        $defaultFaqs = $this->getDefaultFaqsForBusinessType($request->business_type);
        
        foreach ($defaultFaqs as $index => $faqData) {
            PageFaq::create([
                'facebook_page_id' => $request->facebook_page_id,
                'question_en' => $faqData['question_en'],
                'question_bn' => $faqData['question_bn'],
                'answer_en' => $faqData['answer_en'],
                'answer_bn' => $faqData['answer_bn'],
                'display_order' => $index + 1,
                'is_active' => true,
                'quick_reply_text' => ($index + 1) . '️⃣',
                'action_type' => $faqData['action_type'],
                'inquiry_type' => $faqData['inquiry_type'] ?? null
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Default FAQs created successfully!',
            'count' => count($defaultFaqs)
        ]);
    }

    private function getDefaultFaqsForBusinessType(string $businessType): array
    {
        $defaults = [
            'software' => [
                [
                    'question_en' => 'What services do you provide?',
                    'question_bn' => 'আপনারা কী সেবা প্রদান করেন?',
                    'answer_en' => '💻 **Our Software Development Services:**\n\n• Custom Web Development\n• Mobile App Development (iOS & Android)\n• Cloud Solutions & DevOps\n• AI & Automation\n• UI/UX Design\n• E-commerce Development',
                    'answer_bn' => '💻 **আমাদের সফটওয়্যার ডেভেলপমেন্ট সেবা:**\n\n• কাস্টম ওয়েব ডেভেলপমেন্ট\n• মোবাইল অ্যাপ ডেভেলপমেন্ট\n• ক্লাউড সমাধান\n• এআই ও অটোমেশন\n• UI/UX ডিজাইন\n• ই-কমার্স ডেভেলপমেন্ট',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'What are your rates?',
                    'question_bn' => 'আপনাদের রেট কত?',
                    'answer_en' => '💰 **Our Development Rates:**\n\n• Simple Website: $1,000 - $3,000\n• Complex Web App: $5,000 - $15,000\n• Mobile App: $3,000 - $10,000\n• Enterprise Solution: $15,000+\n\n*Final price depends on requirements. Contact us for a detailed quote!*',
                    'answer_bn' => '💰 **আমাদের ডেভেলপমেন্ট রেট:**\n\n• সাধারণ ওয়েবসাইট: ১,০০০ - ৩,০০০ ডলার\n• জটিল ওয়েব অ্যাপ: ৫,০০০ - ১৫,০০০ ডলার\n• মোবাইল অ্যাপ: ৩,০০০ - ১০,০০০ ডলার\n• এন্টারপ্রাইজ সমাধান: ১৫,০০০+ ডলার',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'How long does development take?',
                    'question_bn' => 'ডেভেলপমেন্ট কত সময় নেয়?',
                    'answer_en' => '⏰ **Development Timeline:**\n\n• Simple Website: 2-4 weeks\n• Web Application: 2-4 months\n• Mobile App: 3-6 months\n• Complex System: 6-12 months\n\n*Timeline varies based on complexity and requirements.*',
                    'answer_bn' => '⏰ **ডেভেলপমেন্ট সময়সূচি:**\n\n• সাধারণ ওয়েবসাইট: ২-৪ সপ্তাহ\n• ওয়েব অ্যাপ্লিকেশন: ২-৪ মাস\n• মোবাইল অ্যাপ: ৩-৬ মাস\n• জটিল সিস্টেম: ৬-১২ মাস',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'Can I see your portfolio?',
                    'question_bn' => 'আপনাদের পোর্টফোলিও দেখতে পারি?',
                    'answer_en' => '🎨 **Our Recent Work:**\n\n• E-commerce Platform for Fashion Brand\n• Restaurant Management System\n• Mobile Banking App\n• Healthcare Management Portal\n• Educational Platform\n\nVisit our website to see detailed case studies and live demos!',
                    'answer_bn' => '🎨 **আমাদের সাম্প্রতিক কাজ:**\n\n• ফ্যাশন ব্র্যান্ডের জন্য ই-কমার্স প্ল্যাটফর্ম\n• রেস্তোরাঁ ব্যবস্থাপনা সিস্টেম\n• মোবাইল ব্যাংকিং অ্যাপ\n• স্বাস্থ্যসেবা পোর্টাল\n• শিক্ষামূলক প্ল্যাটফর্ম',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'How do I get started?',
                    'question_bn' => 'কীভাবে শুরু করব?',
                    'answer_en' => '🚀 **Getting Started is Easy:**\n\n1. Share your project requirements\n2. We\'ll schedule a free consultation\n3. Receive detailed proposal & timeline\n4. Project kickoff & development begins!\n\nReady to start your project?',
                    'answer_bn' => '🚀 **শুরু করা সহজ:**\n\n১. আপনার প্রজেক্টের প্রয়োজনীয়তা শেয়ার করুন\n২. আমরা ফ্রি পরামর্শের সময় নির্ধারণ করব\n৩. বিস্তারিত প্রস্তাব ও সময়সূচি পাবেন\n৪. প্রজেক্ট শুরু ও ডেভেলপমেন্ট!',
                    'action_type' => 'start_inquiry',
                    'inquiry_type' => 'consultation'
                ]
            ],
            'restaurant' => [
                [
                    'question_en' => 'What\'s on the menu today?',
                    'question_bn' => 'আজকের মেনুতে কী আছে?',
                    'answer_en' => '🍽️ **Today\'s Special Menu:**\n\n• Biryani (Chicken/Beef/Mutton)\n• Traditional Curry\n• Chinese Dishes\n• Burger & Pizza\n• Fresh Juice & Beverages\n\nAll items freshly prepared with premium ingredients!',
                    'answer_bn' => '🍽️ **আজকের বিশেষ মেনু:**\n\n• বিরিয়ানি (চিকেন/গরু/খাসি)\n• ঐতিহ্যবাহী তরকারি\n• চাইনিজ খাবার\n• বার্গার ও পিৎজা\n• তাজা জুস ও পানীয়\n\nসব খাবার তাজা উপাদান দিয়ে তৈরি!',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'What are your prices?',
                    'question_bn' => 'দাম কত?',
                    'answer_en' => '💰 **Our Prices:**\n\n• Biryani: ৳200-350\n• Curry: ৳150-250\n• Chinese: ৳180-300\n• Burger: ৳120-200\n• Beverages: ৳30-80\n\n*Prices may vary. Contact us for latest menu with prices.*',
                    'answer_bn' => '💰 **আমাদের দাম:**\n\n• বিরিয়ানি: ৳২০০-৩৫০\n• তরকারি: ৳১৫০-২৫০\n• চাইনিজ: ৳১৮০-৩০০\n• বার্গার: ৳১২০-২০০\n• পানীয়: ৳৩০-৮০',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'What are your hours?',
                    'question_bn' => 'খোলা থাকার সময় কী?',
                    'answer_en' => '🕐 **Opening Hours:**\n\n• Monday - Sunday: 11:00 AM - 11:00 PM\n• Friday: 2:00 PM - 11:00 PM (after Jumma)\n• Delivery available during all hours\n• Takeaway & Dine-in available',
                    'answer_bn' => '🕐 **খোলার সময়:**\n\n• সোমবার - রবিবার: সকাল ১১টা - রাত ১১টা\n• শুক্রবার: দুপুর ২টা - রাত ১১টা (জুম্মার পর)\n• সব সময় ডেলিভারি\n• টেকঅ্যাওয়ে ও ডাইন-ইন',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'How do I make a reservation?',
                    'question_bn' => 'টেবিল বুক করব কীভাবে?',
                    'answer_en' => '📅 **Table Reservation:**\n\n• Call us directly: +88019XXXXXXXX\n• Walk-in (subject to availability)\n• Book through our chat system\n• Advance booking recommended for groups\n\nWould you like to book a table now?',
                    'answer_bn' => '📅 **টেবিল রিজার্ভেশন:**\n\n• সরাসরি কল করুন: +৮৮০১৯XXXXXXXX\n• সরাসরি এসে বসুন (জায়গা থাকলে)\n• আমাদের চ্যাট সিস্টেমে বুক করুন\n• গ্রুপের জন্য আগে বুকিং দিন\n\nএখনি টেবিল বুক করতে চান?',
                    'action_type' => 'start_inquiry',
                    'inquiry_type' => 'reservation'
                ],
                [
                    'question_en' => 'Do you deliver food?',
                    'question_bn' => 'খাবার ডেলিভারি দেন?',
                    'answer_en' => '🛵 **Food Delivery:**\n\n• Yes! We deliver hot & fresh food\n• Delivery area: Within 5km radius\n• Delivery charge: ৳30-60 (based on distance)\n• Minimum order: ৳200\n• Delivery time: 30-45 minutes\n\nReady to place an order?',
                    'answer_bn' => '🛵 **খাবার ডেলিভারি:**\n\n• হ্যাঁ! আমরা গরম ও তাজা খাবার ডেলিভারি দেই\n• ডেলিভারি এলাকা: ৫ কিমি এর মধ্যে\n• ডেলিভারি চার্জ: ৳৩০-৬০ (দূরত্ব অনুযায়ী)\n• মিনিমাম অর্ডার: ৳২০০\n• ডেলিভারি সময়: ৩০-৪৫ মিনিট\n\nঅর্ডার দিতে প্রস্তুত?',
                    'action_type' => 'start_inquiry',
                    'inquiry_type' => 'order'
                ]
            ],
            'salon' => [
                [
                    'question_en' => 'What services do you offer?',
                    'question_bn' => 'আপনারা কী সেবা দেন?',
                    'answer_en' => '💇‍♀️ **Our Beauty Services:**\n\n• Haircut & Styling\n• Hair Coloring & Highlights\n• Facial Treatment\n• Manicure & Pedicure\n• Eyebrow Threading\n• Bridal Makeup\n• Hair Treatment & Spa',
                    'answer_bn' => '💇‍♀️ **আমাদের বিউটি সেবা:**\n\n• চুল কাটা ও স্টাইলিং\n• চুলে রং ও হাইলাইট\n• ফেসিয়াল ট্রিটমেন্ট\n• ম্যানিকিউর ও পেডিকিউর\n• ভ্রু প্লাক\n• দুলহার মেকআপ\n• চুলের চিকিৎসা ও স্পা',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'What are your prices?',
                    'question_bn' => 'দাম কত?',
                    'answer_en' => '💰 **Service Prices:**\n\n• Haircut: ৳300-800\n• Hair Color: ৳1,500-3,000\n• Facial: ৳800-1,500\n• Manicure: ৳400-600\n• Pedicure: ৳500-800\n• Bridal Package: ৳5,000-15,000\n\n*Prices vary by stylist and treatment type.*',
                    'answer_bn' => '💰 **সেবার দাম:**\n\n• চুল কাটা: ৳৩০০-৮০০\n• চুলে রং: ৳১,৫০০-৩,০০০\n• ফেসিয়াল: ৳৮০০-১,৫০০\n• ম্যানিকিউর: ৳৪০০-৬০০\n• পেডিকিউর: ৳৫০০-৮০০\n• দুলহার প্যাকেজ: ৳৫,০০০-১৫,০০০',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'Can I see your work?',
                    'question_bn' => 'আপনাদের কাজ দেখতে পারি?',
                    'answer_en' => '🎨 **Our Portfolio:**\n\n• Check our Facebook photo albums\n• Instagram: @salonname\n• Before/After transformations\n• Bridal makeup gallery\n• Customer testimonials\n\nVisit our page to see amazing transformations!',
                    'answer_bn' => '🎨 **আমাদের পোর্টফোলিও:**\n\n• আমাদের ফেসবুক ফটো অ্যালবাম দেখুন\n• ইনস্টাগ্রাম: @salonname\n• আগে/পরে রূপান্তর\n• দুলহার মেকআপ গ্যালারি\n• গ্রাহকদের মন্তব্য',
                    'action_type' => 'answer_only'
                ],
                [
                    'question_en' => 'How do I book an appointment?',
                    'question_bn' => 'অ্যাপয়েন্টমেন্ট কীভাবে নেব?',
                    'answer_en' => '📅 **Book Your Appointment:**\n\n• Call us: +88019XXXXXXXX\n• Message us here\n• Walk-in (subject to availability)\n• Online booking available\n• Advance booking recommended\n\nWould you like to book now?',
                    'answer_bn' => '📅 **আপনার অ্যাপয়েন্টমেন্ট নিন:**\n\n• কল করুন: +৮৮০১৯XXXXXXXX\n• এখানে মেসেজ করুন\n• সরাসরি চলে আসুন\n• অনলাইন বুকিং আছে\n• আগে বুকিং দেওয়া ভাল\n\nএখনি বুক করতে চান?',
                    'action_type' => 'start_inquiry',
                    'inquiry_type' => 'appointment'
                ],
                [
                    'question_en' => 'What are your hours?',
                    'question_bn' => 'খোলার সময় কী?',
                    'answer_en' => '🕐 **Salon Hours:**\n\n• Monday - Saturday: 10:00 AM - 8:00 PM\n• Sunday: 2:00 PM - 7:00 PM\n• Friday: 3:00 PM - 8:00 PM (after prayers)\n• Appointments preferred\n• Bridal services by appointment only',
                    'answer_bn' => '🕐 **সেলুনের সময়:**\n\n• সোমবার - শনিবার: সকাল ১০টা - রাত ৮টা\n• রবিবার: দুপুর ২টা - সন্ধ্যা ৭টা\n• শুক্রবার: দুপুর ৩টা - রাত ৮টা (নামাজের পর)\n• অ্যাপয়েন্টমেন্ট পছন্দনীয়\n• দুলহার সেবা শুধু অ্যাপয়েন্টমেন্টে',
                    'action_type' => 'answer_only'
                ]
            ]
        ];

        return $defaults[$businessType] ?? $defaults['software'];
    }
}