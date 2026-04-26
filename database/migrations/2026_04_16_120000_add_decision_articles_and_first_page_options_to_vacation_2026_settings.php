<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacation_2026_settings', function (Blueprint $table) {
            $table->text('decision_article_1')->nullable()->after('decision_reference');
            $table->text('decision_article_2')->nullable()->after('decision_article_1');
        });
    }

    public function down(): void
    {
        Schema::table('vacation_2026_settings', function (Blueprint $table) {
            $table->dropColumn(['decision_article_1', 'decision_article_2']);
        });
    }
};
