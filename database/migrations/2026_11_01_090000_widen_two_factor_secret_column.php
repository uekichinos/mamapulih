<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * pragmarx/google2fa 9.x doubled generateSecretKey()'s default length
     * (16 -> 32 chars), and the column stores it Laravel-encrypted — the
     * resulting ciphertext no longer reliably fits the original
     * varchar(255). Widened to an unbounded text column rather than a
     * larger fixed size, since an encrypted blob has no natural cap.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret')->nullable()->change();
        });
    }
};
