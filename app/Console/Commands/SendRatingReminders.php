<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendRatingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sipala:send-rating-reminders
                            {--days=7 : Interval hari pengingat setelah tiket selesai atau pengingat terakhir}
                            {--force : Kirim pengingat ke semua tiket selesai yang belum dinilai tanpa menunggu batas hari}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email pengingat penilaian (rating) otomatis untuk tiket berstatus SELESAI yang belum diberi rating';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $days = max(1, (int) $this->option('days'));
        $force = (bool) $this->option('force');

        $sentCount = $notificationService->sendDueRatingReminders($days, $force);

        $this->info("Pengingat rating selesai diproses. Total email terkirim: {$sentCount}.");

        return self::SUCCESS;
    }
}

