<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closure_days', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('scope')->default('all');
            $table->string('reason')->nullable();
            $table->string('source')->default('manual');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['date', 'scope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closure_days');
    }
};
