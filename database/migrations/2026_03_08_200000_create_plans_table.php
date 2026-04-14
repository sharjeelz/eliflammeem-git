<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->string('key', 30)->primary(); // starter | growth | pro | enterprise
            $table->string('label', 60);
            $table->unsignedSmallInteger('max_branches')->nullable(); // null = unlimited
            $table->unsignedSmallInteger('max_users')->nullable();
            $table->unsignedSmallInteger('max_contacts')->nullable();
            // Feature flags
            $table->boolean('feat_ai_analysis')->default(false);
            $table->boolean('feat_ai_trends')->default(false);
            $table->boolean('feat_chatbot')->default(false);
            $table->unsignedSmallInteger('feat_chatbot_daily')->nullable(); // null = unlimited, 0 = off
            $table->boolean('feat_broadcasting')->default(false);
            $table->boolean('feat_whatsapp')->default(false);
            $table->boolean('feat_document_library')->default(false);
            $table->boolean('feat_custom_smtp')->default(false);
            $table->boolean('feat_reports_full')->default(false);
            $table->boolean('feat_csv_export')->default(false);
            $table->boolean('feat_csat')->default(false);
            $table->boolean('feat_two_factor')->default(false);
            $table->timestamps();
        });

        // Seed defaults
        $plans = [
            [
                'key' => 'starter', 'label' => 'Starter',
                'max_branches' => 1, 'max_users' => 5, 'max_contacts' => 100,
                'feat_ai_analysis' => false, 'feat_ai_trends' => false,
                'feat_chatbot' => false, 'feat_chatbot_daily' => 0,
                'feat_broadcasting' => false, 'feat_whatsapp' => false,
                'feat_document_library' => false, 'feat_custom_smtp' => false,
                'feat_reports_full' => false, 'feat_csv_export' => false,
                'feat_csat' => false, 'feat_two_factor' => false,
            ],
            [
                'key' => 'growth', 'label' => 'Growth',
                'max_branches' => 3, 'max_users' => 15, 'max_contacts' => 500,
                'feat_ai_analysis' => true, 'feat_ai_trends' => true,
                'feat_chatbot' => true, 'feat_chatbot_daily' => 50,
                'feat_broadcasting' => true, 'feat_whatsapp' => false,
                'feat_document_library' => true, 'feat_custom_smtp' => true,
                'feat_reports_full' => true, 'feat_csv_export' => true,
                'feat_csat' => true, 'feat_two_factor' => true,
            ],
            [
                'key' => 'pro', 'label' => 'Pro',
                'max_branches' => 10, 'max_users' => 50, 'max_contacts' => 2000,
                'feat_ai_analysis' => true, 'feat_ai_trends' => true,
                'feat_chatbot' => true, 'feat_chatbot_daily' => 200,
                'feat_broadcasting' => true, 'feat_whatsapp' => true,
                'feat_document_library' => true, 'feat_custom_smtp' => true,
                'feat_reports_full' => true, 'feat_csv_export' => true,
                'feat_csat' => true, 'feat_two_factor' => true,
            ],
            [
                'key' => 'enterprise', 'label' => 'Enterprise',
                'max_branches' => null, 'max_users' => null, 'max_contacts' => null,
                'feat_ai_analysis' => true, 'feat_ai_trends' => true,
                'feat_chatbot' => true, 'feat_chatbot_daily' => null,
                'feat_broadcasting' => true, 'feat_whatsapp' => true,
                'feat_document_library' => true, 'feat_custom_smtp' => true,
                'feat_reports_full' => true, 'feat_csv_export' => true,
                'feat_csat' => true, 'feat_two_factor' => true,
            ],
        ];

        foreach ($plans as $plan) {
            \Illuminate\Support\Facades\DB::table('plans')->insert(array_merge($plan, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
