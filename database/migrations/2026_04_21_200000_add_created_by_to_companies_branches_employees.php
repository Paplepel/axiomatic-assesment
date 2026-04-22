<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('registration_number');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('address');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
