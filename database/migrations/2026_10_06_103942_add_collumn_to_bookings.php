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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('studio_id')->constrained('studios');

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status')->default('confirmed');
            
            $table->index(['studio_id', 'starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['studio_id', 'starts_at', 'ends_at']);

            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('studio_id');

            $table->dropColumn([
                'starts_at',
                'ends_at',
                'total_price',
                'status',
            ]);

        });
    }
};
