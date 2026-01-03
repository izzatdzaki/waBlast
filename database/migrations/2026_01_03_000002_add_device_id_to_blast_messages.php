<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeviceIdToBlastMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blast_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('blast_messages', 'device_id')) {
                $table->unsignedBigInteger('device_id')->nullable()->after('template_id');
                $table->foreign('device_id')
                    ->references('id')
                    ->on('whatsapp_devices')
                    ->onDelete('set null');
                $table->index('device_id');
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
            if (Schema::hasColumn('blast_messages', 'device_id')) {
                $table->dropForeign(['device_id']);
                $table->dropIndex(['device_id']);
                $table->dropColumn('device_id');
            }
        });
    }
}
