<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('sensitive_flag')->default('none')->after('status');
            $table->text('sensitive_reason')->nullable()->after('sensitive_flag');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['sensitive_flag', 'sensitive_reason']);
        });
    }
};
