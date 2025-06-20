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
            ->leftJoin('motives', 'persons_checks.motive_id', '=', 'motives.id') // 👈 este estaba después (mal)
            ->when(isset($filters['division']), fn($q) => $q->where('persons_checks.division_id', $filters['division']))
            ->when(isset($filters['person']), fn($q) => $q->where('persons_checks.person_id', $filters['person']))
            ->orderBy('persons_checks.moment_enter', 'asc')
            ->get([
                'persons.names as names',
                'persons.token',
                'divisions.names as div',
                'rols.rol',
                'persons_checks.moment_enter',
                'persons_checks.moment_exit',
                'persons_checks.motive_id',
                'persons_checks.note',
                'motives.motive as motive_name',
            ])
            ->map(function ($row) {
                $entrada = $row->moment_enter ? \Carbon\Carbon::parse($row->moment_enter) : null;
                $salida = $row->moment_exit ? \Carbon\Carbon::parse($row->moment_exit) : null;
                $diff = ($entrada && $salida && $salida->gte($entrada)) ? $entrada->diffInSeconds($salida) : 0;

                return [
                    'names' => $row->names,
                    'token' => $row->token,
                    'div' => $row->div,
                    'rol' => $row->rol,
                    'moment_enter' => $row->moment_enter,
                    'moment_exit' => $row->moment_exit,
                    'hours' => sprintf('%02d:%02d:%02d', floor($diff / 3600), floor(($diff % 3600) / 60), $diff % 60),
                    'note' => $row->note,
                    'motive_id' => $row->motive_id,
                    'motive_name' => $row->motive_name ?? '',
                ];
            })
            ->toArray();
    }


    private function generatePdf(array $list, CustomReport $report): string
    {
        $columns = $report->columns;

        $isSoloOtros = count($columns) === 1 && in_array('is_otros', $columns);
        $columnsOriginales = $report->columns;

        if ($isSoloOtros) {
            $columns = ['names', 'token', 'moment_enter', 'moment_exit', 'hours', 'note', 'motive_name'];
        }

        $columnMap = [
            'division' => 'div',
            'role' => 'rol',
            'name' => 'names',
            'token' => 'token',
            'moment_enter' => 'moment_enter',
            'moment_exit' => 'moment_exit',
            'hours' => 'hours',
            'is_otros' => 'is_otros',
            'note' => 'note',
            'motive_name' => 'motive_name',
        ];

        $mappedList = collect($list)->map(function ($item) {
            $row = [
                'div' => $item['div'] ?? '',
                'rol' => $item['rol'] ?? '',
                'token' => $item['token'] ?? '',
                'names' => $item['names'] ?? '',
                'moment_enter' => $item['moment_enter'] ?? '',
                'moment_exit' => $item['moment_exit'] ?? '',
                'hours' => '',
                'is_otros' => ($item['motive_id'] ?? 0) > 0 ? 'Sí' : 'No',
                'note' => $item['note'] ?? '',
                'motive_name' => $item['motive_name'] ?? '',
                'motive_id' => $item['motive_id'] ?? 0,
            ];

            if (!empty($item['moment_enter']) && !empty($item['moment_exit'])) {
                $start = \Carbon\Carbon::parse($item['moment_enter']);
                $end = \Carbon\Carbon::parse($item['moment_exit']);
                $seconds = $start->diffInSeconds($end);
                $row['hours'] = sprintf('%02d:%02d:%02d', floor($seconds / 3600), floor(($seconds % 3600) / 60), $seconds % 60);
            } else {
                $row['hours'] = '0:00:00';
            }

            return $row;
        });

        if ($isSoloOtros) {
            $mappedList = $mappedList->filter(fn($item) => ($item['motive_id'] ?? 0) > 0);
        }

        $agrupados = collect($mappedList)->groupBy('token');

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

        $totales_otros = collect($agrupados)->map(function ($items) {
            $segundos = collect($items)->reduce(function ($acc, $item) {
                if (($item['motive_id'] ?? 0) > 0 && !empty($item['moment_enter']) && !empty($item['moment_exit'])) {
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
            'columns' => $columns,
            'total_horas' => $totalHoras,
            'company' => \App\Company::first(),
            'filters' => $report->filters ?? [],
            'agrupados' => $agrupados,
            'totales' => $totales,
            'totales_otros' => $totales_otros,
            'is_solo_otros' => $isSoloOtros,
            'columns_originales' => $columnsOriginales,
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
