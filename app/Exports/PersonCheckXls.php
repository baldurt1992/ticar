<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PersonCheckXls implements FromView
{
    protected $list;

    public function __construct(array $list)
    {
        $this->list = $list;
    }

    public function view(): View
    {
        return view('reports.xls', ['list' => $this->list]);
    }
}
