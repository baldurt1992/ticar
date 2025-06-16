<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class PersonCheckPerUserSheet implements FromView, WithTitle
{
    protected $token;
    protected $list;
    protected $columns;


    public function __construct(string $token, $list, $columns)
    {
        $this->token = $token;
        $this->list = $list;
        $this->columns = $columns;
    }

    public function view(): View
    {
        $totalMin = 0;

        foreach ($this->list as $item) {
            if ($item['moment_enter'] && $item['moment_exit']) {
                $start = \Carbon\Carbon::parse($item['moment_enter']);
                $end = \Carbon\Carbon::parse($item['moment_exit']);
                $totalMin += $start->diffInSeconds($end);
            }
        }

        $horas_int = floor($totalMin / 3600);
        $minutos_int = floor(($totalMin % 3600) / 60);
        $segundos_int = $totalMin % 60;

        $total_horas = sprintf('%02d:%02d:%02d', $horas_int, $minutos_int, $segundos_int);

        return view('reports.xls', [
            'list' => $this->list,
            'total_horas' => $total_horas,
            'columns' => $this->columns
        ]);
    }

    public function title(): string
    {
        return 'Usuario ' . $this->token;
    }
}
