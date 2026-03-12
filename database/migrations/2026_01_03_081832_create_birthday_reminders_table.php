<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBirthdayRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('birthday_reminders', function (Blueprint $table) {
            $table->id();
            // Menggunakan varchar untuk compatibility dengan pasien.no_rkm_medis
            $table->string('no_rkm_medis', 15)->charset('latin1')->collation('latin1_swedish_ci')->comment('FK ke tabel pasien');
            $table->foreign('no_rkm_medis')->references('no_rkm_medis')->on('pasien')->onDelete('cascade');
            $table->longText('message')->comment('Pesan pengingat ulang tahun');
            $table->string('sender_phone')->comment('Nomor WA yang mengirim');
            $table->string('receiver_phone')->comment('Nomor WA penerima pasien');
            $table->date('birthday_date')->comment('Tanggal ulang tahun pasien');
            $table->dateTime('scheduled_date')->nullable()->comment('Tanggal jadwal pengiriman');
            $table->enum('status', ['pending', 'sent', 'failed', 'scheduled'])->default('pending')->comment('Status pengiriman');
            $table->text('response')->nullable()->comment('Response dari WhatsApp API');
            $table->dateTime('sent_at')->nullable()->comment('Waktu pesan terkirim');
            $table->timestamps();
            
            $table->index('status');
            $table->index('birthday_date');
            $table->index('no_rkm_medis');
        });
        
        // Set charset untuk entire table
        DB::statement('ALTER TABLE birthday_reminders CONVERT TO CHARACTER SET latin1 COLLATE latin1_swedish_ci');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('birthday_reminders');
    }
}
