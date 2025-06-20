<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PersonCheckPerUserSheet implements FromView, WithTitle, WithStyles
{
    protected $token;
    protected $list;
    protected $columns;
    protected $normales;
    protected $otros;

    public function __construct(string $token, $list, $columns)
    {
        $this->token = $token;
        $this->list = $list;
        $this->columns = $columns;

        $this->normales = collect($list)->filter(fn($r) => ($r['motive_id'] ?? 0) == 0)->values();
        $this->otros = collect($list)->filter(fn($r) => ($r['motive_id'] ?? 0) > 0)->values();
    }

    public function styles(Worksheet $sheet)
    {
        $rowStart = count($this->normales) + 8;
        $rowEnd = $rowStart + count($this->otros) - 1;

        return [
            "A{$rowStart}:Z{$rowEnd}" => [
                'font' => ['color' => ['rgb' => 'FF0000']]
            ]
        ];
    }

    public function view(): View
    {
        $totalMin = 0;

        foreach ($this->list as $item) {
            $enter = $item['moment_enter'] ?? null;
            $exit = $item['moment_exit'] ?? null;

            if (!empty($enter) && !empty($exit)) {
                $start = \Carbon\Carbon::parse($enter);
                $end = \Carbon\Carbon::parse($exit);
                $totalMin += $start->diffInSeconds($end);
            }
        }

        $horas_int = floor($totalMin / 3600);
        $minutos_int = floor(($totalMin % 3600) / 60);
        $segundos_int = $totalMin % 60;
        $total_horas = sprintf('%02d:%02d:%02d', $horas_int, $minutos_int, $segundos_int);

        return view('reports.xls', [
            'list' => $this->list,
            'normales' => $this->normales,
            'otros' => $this->otros,
            'total_horas' => $total_horas,
            'columns' => $this->columns,
        ]);
    }

    public function title(): string
    {
        return 'Usuario ' . $this->token;
    }
}
