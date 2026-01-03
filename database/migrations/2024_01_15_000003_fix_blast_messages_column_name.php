<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixBlastMessagesColumnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to rename column
        DB::statement('ALTER TABLE blast_messages CHANGE COLUMN no_telp no_tlp VARCHAR(20)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Use raw SQL to rename column back
        DB::statement('ALTER TABLE blast_messages CHANGE COLUMN no_tlp no_telp VARCHAR(20)');
    }
}
