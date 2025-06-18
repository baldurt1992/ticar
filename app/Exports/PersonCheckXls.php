<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PersonCheckXls implements WithMultipleSheets
{
    protected $list;
    protected $columns;
    protected $isCustom;

    public function __construct(array $list, ?array $columns = null, bool $isCustom = false)
    {
        $this->list = $list;
        $this->isCustom = $isCustom;
        $this->columns = $columns ?? [
            'division',
            'role',
            'token',
            'name',
            'moment_enter',
            'moment_exit',
            'hours'
        ];
    }

    protected function mapColumns($columns)
    {
        return $columns;
    }

    public function sheets(): array
    {
        $agrupados = collect($this->list)->groupBy('token');
        $columns = $this->isCustom ? $this->mapColumns($this->columns) : $this->columns;

        $sheets = [];

        foreach ($agrupados as $token => $registros) {
            $sheets[] = new PersonCheckPerUserSheet($token, $registros, $columns);
        }

        return $sheets;
    }
}

