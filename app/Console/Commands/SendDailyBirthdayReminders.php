<?php

namespace App\Console\Commands;

use App\Models\BirthdayReminder;
use App\Jobs\SendBirthdayReminderJob;
use Illuminate\Console\Command;

class SendDailyBirthdayReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday-reminder:send-daily';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Send birthday reminder messages for patients with birthdays today';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Ambil semua pengingat untuk hari ini yang belum dikirim
        $reminders = BirthdayReminder::where('status', 'pending')
            ->whereDate('birthday_date', now()->toDateString())
            ->get();

        if ($reminders->isEmpty()) {
            $this->info('Tidak ada pengingat ulang tahun untuk hari ini');
            return 0;
        }

        $count = 0;
        foreach ($reminders as $reminder) {
            // Dispatch job untuk pengiriman
            SendBirthdayReminderJob::dispatch($reminder);
            $count++;
        }

        $this->info("Berhasil mengirim/menjadwalkan {$count} pengingat ulang tahun");

        return 0;
    }
}
