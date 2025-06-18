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


    public function __construct(string $token, $list, $columns, )
    {
        $this->token = $token;
        $this->list = $list;
        $this->columns = $columns;
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

        $transformed = collect($this->list);

        return view('reports.xls', [
            'list' => $transformed,
            'total_horas' => $total_horas,
            'columns' => $this->columns
        ]);

    }

    public function title(): string
    {
        return 'Usuario ' . $this->token;
    }
}
