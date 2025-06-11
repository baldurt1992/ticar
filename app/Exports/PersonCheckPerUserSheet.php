<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class PersonCheckPerUserSheet implements FromView, WithTitle
{
    protected $token;
    protected $list;

    public function __construct(string $token, $list)
    {
        $this->token = $token;
        $this->list = $list;
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

        $total_horas = gmdate('H:i:s', $totalMin);


        return view('reports.xls', [
            'list' => $this->list,
            'total_horas' => $total_horas
        ]);
    }

    public function title(): string
    {
        return 'Usuario ' . $this->token;
    }
}
