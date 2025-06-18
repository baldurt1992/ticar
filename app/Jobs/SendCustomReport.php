<?php

namespace App\Jobs;

use App\Mail\CustomReportMail;
use App\Models\CustomReport;
use App\PersonCheck;
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
        $report = CustomReport::find($this->reportId);
        if (!$report)
            return;

        $report->filters = is_array($report->filters) ? $report->filters : json_decode($report->filters, true) ?? [];
        $report->emails = is_array($report->emails) ? $report->emails : json_decode($report->emails, true) ?? [];
        $report->columns = is_array($report->columns) ? $report->columns : json_decode($report->columns, true) ?? [];

        $list = $this->getData($report->filters);
        $attachments = [];

        if (in_array($report->format, ['pdf', 'both'])) {
            $attachments[] = $this->generatePdf($list, $report);
        }

        if (in_array($report->format, ['excel', 'both'])) {
            $attachments[] = $this->generateExcel($list, $report);
        }

        foreach ($report->emails as $email) {
            Mail::to($email)->send(new CustomReportMail($report, $attachments));
        }

        $report->update(['last_sent_at' => now()]);
    }

    private function getData(array $filters): array
    {
        return PersonCheck::join('persons', 'persons_checks.person_id', '=', 'persons.id')
            ->leftJoin('divisions', 'persons_checks.division_id', '=', 'divisions.id')
            ->leftJoin('persons_rols', 'persons_rols.person_id', '=', 'persons.id')
            ->leftJoin('rols', 'persons_rols.rol_id', '=', 'rols.id')
            ->select(
                'persons_checks.*',
                'persons.names AS name',
                'persons.token AS token',
                'divisions.names AS division',
                'rols.rol AS role'
            )
            ->when(isset($filters['division']), fn($q) => $q->where('persons_checks.division_id', $filters['division']))
            ->when(isset($filters['person']), fn($q) => $q->where('persons_checks.person_id', $filters['person']))
            ->orderBy('persons_checks.moment_enter', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'moment_enter' => $row->moment_enter,
                    'moment_exit' => $row->moment_exit,
                    'token' => $row->token,
                    'name' => $row->name,
                    'division' => $row->division,
                    'role' => $row->role,
                ];
            })
            ->toArray();

    }

    private function generatePdf(array $list, CustomReport $report): string
    {
        $columns = $report->columns;

        $columnMap = [
            'division' => 'div',
            'role' => 'rol',
            'name' => 'names',
            'token' => 'token',
            'moment_enter' => 'moment_enter',
            'moment_exit' => 'moment_exit',
            'hours' => 'hours',
        ];

        $mappedList = array_map(function ($item) use ($columnMap) {
            $row = [];
            foreach ($item as $key => $value) {
                $mappedKey = $columnMap[$key] ?? $key;
                $row[$mappedKey] = $value;
            }
            return $row;
        }, $list);

        $agrupados = collect($mappedList)->groupBy('token')->toArray();

        $totalSeconds = collect($list)->reduce(function ($carry, $item) {
            if (!empty($item['moment_enter']) && !empty($item['moment_exit'])) {
                $start = \Carbon\Carbon::parse($item['moment_enter']);
                $end = \Carbon\Carbon::parse($item['moment_exit']);
                return $carry + $start->diffInSeconds($end);
            }
            return $carry;
        }, 0);

        $totalHoras = gmdate('H:i:s', $totalSeconds);

        $totales = collect($agrupados)->map(function ($items) {
            $segundos = collect($items)->reduce(function ($acc, $item) {
                if (!empty($item['moment_enter']) && !empty($item['moment_exit'])) {
                    $start = \Carbon\Carbon::parse($item['moment_enter']);
                    $end = \Carbon\Carbon::parse($item['moment_exit']);
                    return $acc + $start->diffInSeconds($end);
                }
                return $acc;
            }, 0);
            $horas = floor($segundos / 3600);
            $minutos = floor(($segundos % 3600) / 60);
            $segundosRestantes = $segundos % 60;
            return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundosRestantes);
        })->toArray();

        $pdf = Pdf::loadView('reports.pdf', [
            'columns' => array_map(fn($c) => $columnMap[$c] ?? $c, $columns),
            'total_horas' => $totalHoras,
            'company' => \App\Company::first(),
            'filters' => $report->filters ?? [],
            'agrupados' => $agrupados,
            'totales' => $totales,
        ]);

        $pdfPath = 'reports/report_' . $report->id . '.pdf';
        Storage::put($pdfPath, $pdf->output());

        return storage_path("app/{$pdfPath}");
    }

    private function generateExcel(array $list, CustomReport $report): string
    {
        $columns = $report->columns;
        $totalSeconds = 0;

        $agrupados = collect($list)->groupBy('token');
        $finalList = [];

        foreach ($agrupados as $token => $registros) {
            $registros = collect($registros)->map(function ($item) use (&$totalSeconds) {
                if (!empty($item['moment_enter']) && !empty($item['moment_exit'])) {
                    $start = \Carbon\Carbon::parse($item['moment_enter']);
                    $end = \Carbon\Carbon::parse($item['moment_exit']);
                    $seconds = $start->diffInSeconds($end);
                    $item['hours'] = sprintf('%02d:%02d', floor($seconds / 3600), floor(($seconds % 3600) / 60));
                    $totalSeconds += $seconds;
                } else {
                    $item['hours'] = '0:00';
                }
                return $item;
            })->toArray();

            $finalList = array_merge($finalList, $registros);
        }



        $path = 'reports/report_' . $report->id . '.xlsx';

        Excel::store(new PersonCheckXls($finalList, $columns, true), $path);

        return storage_path("app/{$path}");
    }
}
