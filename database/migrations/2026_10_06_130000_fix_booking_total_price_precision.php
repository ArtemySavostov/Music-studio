<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('total_price', 12, 2)->change();
        });
    }

    public function down(): void
    {
        // Keep the wider precision so rolling back cannot truncate existing prices.
    }
};
