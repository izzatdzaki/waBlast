<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsAppDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name', 255);
            $table->string('phone_number', 20)->unique();
            $table->text('session_data')->nullable();
            $table->enum('status', ['inactive', 'connecting', 'active', 'disconnected', 'error'])->default('inactive');
            $table->text('error_message')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->json('device_info')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('created_by', 255)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('is_primary');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_devices');
    }
}
