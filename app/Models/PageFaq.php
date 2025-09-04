<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageFaq extends Model
{
    use HasFactory;

    protected $fillable = [
        'facebook_page_id',
        'question_en',
        'question_bn',
        'answer_en', 
        'answer_bn',
        'display_order',
        'is_active',
        'quick_reply_text',
        'action_type',
        'inquiry_type'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    // Helper methods
    public function getQuestion(string $language = 'en'): string
    {
        return match($language) {
            'bn' => $this->question_bn ?: $this->question_en,
            default => $this->question_en
        };
    }

    public function getAnswer(string $language = 'en'): string
    {
        return match($language) {
            'bn' => $this->answer_bn ?: $this->answer_en,
            default => $this->answer_en
        };
    }

    public function shouldStartInquiry(): bool
    {
        return $this->action_type === 'start_inquiry';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPage($query, int $facebookPageId)
    {
        return $query->where('facebook_page_id', $facebookPageId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    // Static methods
    public static function getNextQuickReplyText(int $facebookPageId): string
    {
        $lastText = static::where('facebook_page_id', $facebookPageId)
            ->orderByDesc('display_order')
            ->value('quick_reply_text');

        if (!$lastText) {
            return '1️⃣';
        }

        // Extract number and increment
        if (preg_match('/(\d+)/', $lastText, $matches)) {
            $nextNum = (int)$matches[1] + 1;
            return $nextNum . '️⃣';
        }

        return '1️⃣';
    }

    public static function getAvailableQuickReplies(): array
    {
        return [
            '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣',
            '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'
        ];
    }
}