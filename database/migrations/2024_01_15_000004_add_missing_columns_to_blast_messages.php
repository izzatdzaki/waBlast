<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMissingColumnsToBlastMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blast_messages', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('blast_messages', 'response')) {
                $table->json('response')->nullable()->after('error_message');
            }
            if (!Schema::hasColumn('blast_messages', 'external_message_id')) {
                $table->string('external_message_id')->nullable()->after('response');
            }
            if (!Schema::hasColumn('blast_messages', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->change();
            }
            // Also ensure no_telp was renamed to no_tlp
            if (Schema::hasColumn('blast_messages', 'no_telp')) {
                DB::statement('ALTER TABLE blast_messages CHANGE COLUMN no_telp no_tlp VARCHAR(20)');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blast_messages', function (Blueprint $table) {
            if (Schema::hasColumn('blast_messages', 'response')) {
                $table->dropColumn('response');
            }
            if (Schema::hasColumn('blast_messages', 'external_message_id')) {
                $table->dropColumn('external_message_id');
            }
        });
    }
}
