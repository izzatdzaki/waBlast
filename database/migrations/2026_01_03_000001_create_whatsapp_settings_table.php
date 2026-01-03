<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsAppSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            
            // Connection settings
            $table->string('baileys_url')->default('http://localhost:3000');
            $table->boolean('baileys_status')->default(false);
            
            // Device settings
            $table->string('default_device_id')->nullable();
            $table->integer('device_check_interval')->default(30);
            
            // Webhook settings
            $table->text('webhook_url')->nullable();
            $table->boolean('webhook_enabled')->default(true);
            $table->string('webhook_secret')->nullable();
            
            // Message settings
            $table->boolean('enable_auto_reply')->default(false);
            $table->longText('auto_reply_message')->nullable();
            $table->integer('message_retention_days')->default(30);
            $table->integer('max_message_length')->default(4096);
            
            // API settings
            $table->integer('api_rate_limit')->default(20);
            $table->integer('api_timeout')->default(30);
            $table->integer('api_retry_attempts')->default(3);
            $table->integer('api_retry_delay')->default(5);
            
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_settings');
    }
}
