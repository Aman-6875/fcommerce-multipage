<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('page_business_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facebook_page_id')->constrained()->onDelete('cascade');
            
            // Business type and settings
            $table->enum('business_type', ['restaurant', 'salon', 'software', 'retail', 'service', 'consulting', 'real_estate', 'healthcare', 'education', 'other'])->default('service');
            
            // Welcome message settings
            $table->text('welcome_message_en')->nullable();
            $table->text('welcome_message_bn')->nullable();
            $table->text('company_description_en')->nullable();
            $table->text('company_description_bn')->nullable();
            $table->boolean('is_welcome_enabled')->default(true);
            $table->enum('default_language', ['en', 'bn'])->default('en');
            
            // What they call their inquiries (multi-language)
            $table->string('inquiry_name_en', 50)->default('inquiry'); // "order", "booking", "appointment"
            $table->string('inquiry_name_bn', 50)->default('অনুরোধ');
            
            // What they call their service/product (multi-language)
            $table->string('service_name_en', 50)->default('service'); // "product", "appointment", "consultation"
            $table->string('service_name_bn', 50)->default('সেবা');
            
            // Collection preferences
            $table->boolean('collect_date')->default(false); // Need appointment date?
            $table->boolean('collect_time')->default(false); // Need appointment time?
            $table->boolean('collect_budget')->default(true); // Ask for budget?
            $table->boolean('collect_quantity')->default(false); // Ask for quantity?
            $table->boolean('collect_email')->default(true); // Collect email?
            
            // Budget options (JSON array)
            $table->json('budget_options')->nullable(); // [{"en": "$100-500", "bn": "১০০-৫০০ ডলার"}, ...]
            
            // Custom fields they want to collect
            $table->json('custom_fields')->nullable(); // [{"name": "dietary_restrictions", "label_en": "Any food allergies?", "label_bn": "খাবারে অ্যালার্জি?", "required": false}]
            
            // Time slots for appointments (JSON)
            $table->json('available_time_slots')->nullable(); // ["09:00", "10:00", "11:00", ...]
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique('facebook_page_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_business_config');
    }
};