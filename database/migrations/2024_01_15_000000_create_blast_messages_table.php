<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlastMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blast_messages', function (Blueprint $table) {
            $table->id();
            $table->string('no_telp', 20);
            $table->text('message');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->json('template_variables')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'sent', 'delivered', 'read', 'failed', 'retry'])->default('pending');
            $table->enum('tipe_template', ['immediate', 'scheduled', 'broadcast'])->default('immediate');
            $table->text('error_message')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('max_retry')->default(3);
            $table->string('created_by', 255)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('no_telp');
            $table->index('status');
            $table->index('created_at');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blast_messages');
    }
}
