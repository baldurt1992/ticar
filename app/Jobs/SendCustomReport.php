<?php

namespace App\Jobs;

use App\Mail\CustomReportMail;
use App\Models\CustomReport;
use App\PersonCheck;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PersonCheckXls;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
class SendCustomReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $reportId;

    public function __construct(int $reportId)
    {
        $this->reportId = $reportId;
    }

    public function handle()
    {
        \Log::info("📤 Ejecutando envío de reporte ID: {$this->reportId}");

        $report = CustomReport::find($this->reportId);
        if (!$report)
            return;

        // Cast seguros
        $report->filters = is_array($report->filters) ? $report->filters : json_decode($report->filters, true) ?? [];
        $report->emails = is_array($report->emails) ? $report->emails : json_decode($report->emails, true) ?? [];
        $report->columns = is_array($report->columns) ? $report->columns : json_decode($report->columns, true) ?? [];

        if (!$report)
            return;

        $filters = $report->filters ?? [];

        $list = PersonCheck::leftJoin('persons', 'persons.id', 'persons_checks.person_id')
            ->leftJoin('divisions', 'divisions.id', 'persons_checks.division_id')
            ->select(
                'persons_checks.*',
                'persons.names as name',
                'divisions.names as div'
            )
            ->when(isset($filters['division']), fn($q) => $q->where('persons_checks.division_id', $filters['division']))
            ->when(isset($filters['person']), fn($q) => $q->where('persons_checks.person_id', $filters['person']))
            ->orderBy('persons_checks.moment_enter', 'asc')
            ->get()
            ->toArray();

        $attachments = [];

        if (in_array($report->format, ['pdf', 'both'])) {
            $totalSeconds = 0;
            foreach ($list as $item) {
                if (!empty($item['moment_enter']) && !empty($item['moment_exit'])) {
                    $start = \Carbon\Carbon::parse($item['moment_enter']);
                    $end = \Carbon\Carbon::parse($item['moment_exit']);
                    $totalSeconds += $start->diffInSeconds($end);
                }
            }

            $horas = floor($totalSeconds / 3600);
            $min = floor(($totalSeconds % 3600) / 60);
            $seg = $totalSeconds % 60;
            $totalHoras = sprintf('%02d:%02d:%02d', $horas, $min, $seg);

            $pdf = Pdf::loadView('reports.pdf', [
                'list' => $list,
                'columns' => $report->columns,
                'total_horas' => $totalHoras,
                'company' => \App\Company::first(),
                'filters' => $report->filters ?? [],
                'agrupados' => []
            ]);
            $pdfPath = 'reports/report_' . $report->id . '.pdf';
            Storage::put($pdfPath, $pdf->output());
            $attachments[] = storage_path("app/{$pdfPath}");
        }

        if (in_array($report->format, ['excel', 'both'])) {
            $xlsPath = 'reports/report_' . $report->id . '.xlsx';
            Excel::store(new PersonCheckXls($list), $xlsPath);
            $attachments[] = storage_path("app/{$xlsPath}");
        }
        \Log::info("✅ Enviando correo a: ", $report->emails);

        foreach ($report->emails as $email) {
            Mail::to($email)->send(new CustomReportMail($report, $attachments));
        }

        $report->update(['last_sent_at' => now()]);
    }
}
