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
        Schema::table('users', function (Blueprint $table) {
            $table->string('github_username')->nullable()->after('email');
            $table->string('github_token')->nullable()->after('github_username');
            $table->integer('points')->default(0)->after('github_token');
            $table->text('bio')->nullable()->after('points');
            $table->string('avatar')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['github_username', 'github_token', 'points', 'bio', 'avatar']);
        });
    }
};
