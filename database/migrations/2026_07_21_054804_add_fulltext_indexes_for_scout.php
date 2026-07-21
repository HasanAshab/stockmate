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
        // Only add fulltext indexes for MySQL and PostgreSQL
        // SQLite uses FTS5 which requires different setup
        if (Schema::connection($this->getConnection())->getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Products: search on name and sku
        Schema::table('products', function (Blueprint $table) {
            $table->fullText(['name', 'sku']);
        });

        // Users: search on name and email
        Schema::table('users', function (Blueprint $table) {
            $table->fullText(['name', 'email']);
        });

        // Sales Orders: search on customer fields and transaction reference
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->fullText(['customer_name', 'customer_email', 'customer_phone', 'transaction_reference']);
        });

        // Warehouses: search on name and location
        Schema::table('warehouses', function (Blueprint $table) {
            $table->fullText(['name', 'location']);
        });

        // Purchase Orders: search on note
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->fullText('note');
        });

        // Stock Logs: search on note
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->fullText('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop fulltext indexes for MySQL and PostgreSQL
        if (Schema::connection($this->getConnection())->getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropFullText(['name', 'sku']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropFullText(['name', 'email']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropFullText(['customer_name', 'customer_email', 'customer_phone']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropFullText(['name', 'location']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropFullText('note');
        });

        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropFullText('note');
        });
    }
};
