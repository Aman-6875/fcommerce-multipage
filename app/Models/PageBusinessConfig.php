<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBusinessConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'facebook_page_id',
        'business_type',
        'welcome_message_en',
        'welcome_message_bn',
        'company_description_en', 
        'company_description_bn',
        'is_welcome_enabled',
        'default_language',
        'inquiry_name_en',
        'inquiry_name_bn',
        'service_name_en',
        'service_name_bn',
        'collect_date',
        'collect_time',
        'collect_budget',
        'collect_quantity',
        'collect_email',
        'budget_options',
        'custom_fields',
        'available_time_slots'
    ];

    protected $casts = [
        'is_welcome_enabled' => 'boolean',
        'collect_date' => 'boolean',
        'collect_time' => 'boolean', 
        'collect_budget' => 'boolean',
        'collect_quantity' => 'boolean',
        'collect_email' => 'boolean',
        'budget_options' => 'array',
        'custom_fields' => 'array',
        'available_time_slots' => 'array'
    ];

    // Relationships
    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    // Helper methods
    public function getWelcomeMessage(string $language = 'en'): string
    {
        $message = match($language) {
            'bn' => $this->welcome_message_bn ?: $this->welcome_message_en,
            default => $this->welcome_message_en
        };

        return $message ?: $this->getDefaultWelcomeMessage($language);
    }

    public function getCompanyDescription(string $language = 'en'): string
    {
        return match($language) {
            'bn' => $this->company_description_bn ?: $this->company_description_en,
            default => $this->company_description_en ?: ''
        };
    }

    public function getInquiryName(string $language = 'en'): string
    {
        return match($language) {
            'bn' => $this->inquiry_name_bn ?: 'অনুরোধ',
            default => $this->inquiry_name_en ?: 'inquiry'
        };
    }

    public function getServiceName(string $language = 'en'): string
    {
        return match($language) {
            'bn' => $this->service_name_bn ?: 'সেবা',
            default => $this->service_name_en ?: 'service'
        };
    }

    public function getBudgetOptions(string $language = 'en'): array
    {
        $options = $this->budget_options ?: $this->getDefaultBudgetOptions();
        
        $result = [];
        foreach ($options as $option) {
            if (is_array($option)) {
                $result[] = $option[$language] ?? $option['en'] ?? $option;
            } else {
                $result[] = $option;
            }
        }
        
        return $result;
    }

    public function getCollectionSteps(): array
    {
        $steps = ['name', 'phone'];
        
        if ($this->collect_email) {
            $steps[] = 'email';
        }
        
        $steps[] = 'service';
        $steps[] = 'description';
        
        if ($this->collect_date) {
            $steps[] = 'date';
        }
        
        if ($this->collect_time) {
            $steps[] = 'time';
        }
        
        if ($this->collect_quantity) {
            $steps[] = 'quantity';
        }
        
        if ($this->collect_budget) {
            $steps[] = 'budget';
        }
        
        if (!empty($this->custom_fields)) {
            $steps[] = 'custom_fields';
        }
        
        return $steps;
    }

    private function getDefaultWelcomeMessage(string $language = 'en'): string
    {
        $businessName = $this->facebookPage->page_name ?? 'Our Business';
        
        return match($language) {
            'bn' => "👋 {$businessName} এ স্বাগতম!\nআমরা কীভাবে আপনাকে সাহায্য করতে পারি?",
            default => "👋 Welcome to {$businessName}!\nHow can we help you today?"
        };
    }

    private function getDefaultBudgetOptions(): array
    {
        return match($this->business_type) {
            'restaurant' => [
                ['en' => 'Under $50', 'bn' => '৫০ ডলারের নিচে'],
                ['en' => '$50 - $100', 'bn' => '৫০-১০০ ডলার'],
                ['en' => '$100+', 'bn' => '১০০+ ডলার'],
                ['en' => "Let's discuss", 'bn' => 'আলোচনা করি']
            ],
            'salon' => [
                ['en' => 'Under $100', 'bn' => '১০০ ডলারের নিচে'],
                ['en' => '$100 - $300', 'bn' => '১০০-৩০০ ডলার'],
                ['en' => '$300+', 'bn' => '৩০০+ ডলার'],
                ['en' => "Let's discuss", 'bn' => 'আলোচনা করি']
            ],
            'software' => [
                ['en' => '$1,000 - $5,000', 'bn' => '১০০০-৫০০০ ডলার'],
                ['en' => '$5,000 - $15,000', 'bn' => '৫০০০-১৫০০০ ডলার'],
                ['en' => '$15,000+', 'bn' => '১৫০০০+ ডলার'],
                ['en' => "Let's discuss", 'bn' => 'আলোচনা করি']
            ],
            default => [
                ['en' => 'Budget friendly', 'bn' => 'সাশ্রয়ী'],
                ['en' => 'Mid-range', 'bn' => 'মধ্যম রেঞ্জ'],
                ['en' => 'Premium', 'bn' => 'প্রিমিয়াম'],
                ['en' => "Let's discuss", 'bn' => 'আলোচনা করি']
            ]
        };
    }

    // Static methods
    public static function getBusinessTypes(): array
    {
        return [
            'restaurant' => ['en' => 'Restaurant', 'bn' => 'রেস্তোরাঁ'],
            'salon' => ['en' => 'Beauty Salon', 'bn' => 'বিউটি সেলুন'],
            'software' => ['en' => 'Software Company', 'bn' => 'সফটওয়্যার কোম্পানি'],
            'retail' => ['en' => 'Retail Store', 'bn' => 'খুচরা দোকান'],
            'service' => ['en' => 'Service Business', 'bn' => 'সেবা ব্যবসা'],
            'consulting' => ['en' => 'Consulting', 'bn' => 'পরামর্শ'],
            'real_estate' => ['en' => 'Real Estate', 'bn' => 'রিয়েল এস্টেট'],
            'healthcare' => ['en' => 'Healthcare', 'bn' => 'স্বাস্থ্যসেবা'],
            'education' => ['en' => 'Education', 'bn' => 'শিক্ষা'],
            'other' => ['en' => 'Other', 'bn' => 'অন্যান্য']
        ];
    }

    public static function getDefaultTimeSlots(): array
    {
        return [
            '09:00', '10:00', '11:00', '12:00', 
            '13:00', '14:00', '15:00', '16:00', '17:00'
        ];
    }
}