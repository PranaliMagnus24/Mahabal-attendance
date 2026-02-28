<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCheckoutUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-checkout-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically check out users who haven\'t checked out after 9 hours from check-in';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-checkout process...');

        $hours = config('attendance.auto_checkout_hours');

        $attendances = Attendance::whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->where('check_in_time', '<=', now()->subHours($hours))
            ->get();

        $count = 0;

        foreach ($attendances as $attendance) {
            $attendance->update([
                'check_out_time' => Carbon::parse($attendance->check_in_time)->addHours($hours),
                'auto_checkout' => true,
            ]);

            $this->info("Auto-checked out user ID: {$attendance->user_id} (Attendance ID: {$attendance->id})");
            $count++;
        }

        $this->info("Auto-checkout completed. Processed $count records.");

        return 0;
    }
}
