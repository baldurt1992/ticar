<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomReport;
use Carbon\Carbon;
use App\Jobs\SendCustomReport;
use Cron\CronExpression;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:send-scheduled';
    protected $description = 'Envía reportes programados si coincide el cron actual';

    public function handle()
    {
        $now = now()->setTimezone('UTC');
        $this->info("⏰ Hora actual UTC: $now");

        $reports = CustomReport::all();

        foreach ($reports as $report) {
            $cron = $report->cron;
            $this->info("📋 Reporte #{$report->id} => CRON: {$cron} | Último envío: {$report->last_sent_at}");

            if (
                CronExpression::factory($cron)->isDue($now) &&
                (!$report->last_sent_at || $report->last_sent_at->lt($now->subMinutes(1)))
            ) {
                dispatch(new SendCustomReport($report->id));
                $this->info("✅ Reporte #{$report->id} encolado para envío");
            }
        }
    }
}
