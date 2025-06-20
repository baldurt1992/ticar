<?php

namespace App\Http\Controllers;

use App;
use App\Rol;
use App\Motive;
use App\Person;
use App\Company;
use App\Division;
use Carbon\Carbon;
use App\PersonCheck;
use Nexmo\Call\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Exports\PersonCheckXls;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Object_;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        $now = now();
        $start = $now->copy()->startOfMonth()->format('Y-m-d H:i');
        $end = $now->format('Y-m-d H:i');

        return view('reports.rep', [
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }

    private function getBaseQuery($filters)
    {
        $query = PersonCheck::join('persons', 'persons_checks.person_id', '=', 'persons.id')
            ->leftJoin('divisions', 'persons_checks.division_id', '=', 'divisions.id')
            ->leftJoin('persons_rols', 'persons_rols.person_id', '=', 'persons.id')
            ->leftJoin('rols', 'persons_rols.rol_id', '=', 'rols.id')
            ->leftJoin('motives', 'persons_checks.motive_id', '=', 'motives.id')
            ->whereBetween('persons_checks.moment', [$filters['dstar'], $filters['dend']]);

        if ($filters['person'] > 0)
            $query->where('persons.id', $filters['person']);
        if ($filters['division'] > 0)
            $query->where('persons_checks.division_id', $filters['division']);
        if ($filters['rol'] > 0)
            $query->where('rols.id', $filters['rol']);

        return $query;
    }

    private function parseList($data, &$otros_tokens)
    {
        $list = [];

        foreach ($data as $registro) {
            $entrada = $registro->moment_enter ? Carbon::parse($registro->moment_enter) : null;
            $salida = $registro->moment_exit ? Carbon::parse($registro->moment_exit) : null;
            $horas = ($entrada && $salida) ? $entrada->diff($salida)->format('%H:%I:%S') : '0:00';

            if ($registro->motive_id > 0 && $entrada && $salida) {
                $seconds = $entrada->diffInSeconds($salida);
                if (!isset($otros_tokens[$registro->token]))
                    $otros_tokens[$registro->token] = 0;
                $otros_tokens[$registro->token] += $seconds;
            }

            $list[] = [
                'id' => $registro->check_id,
                'names' => $registro->names,
                'token' => $registro->token,
                'div' => $registro->div,
                'rol' => $registro->rol,
                'moment_enter' => $registro->moment_enter ? $entrada->toIso8601String() : null,
                'moment_exit' => $registro->moment_exit ? $salida->toIso8601String() : null,
                'hours' => $horas,
                'motive_id' => $registro->motive_id,
                'note' => $registro->note,
            ];
        }

        return $list;
    }

    public function getList(Request $request)
    {
        $page = (int) $request->input('start', 0);
        $take = (int) $request->input('take', 12);
        $filters = $request->input('filters', true);
        $priorizar_otros = $request->has('priorizar_otros') ? filter_var($request->input('priorizar_otros'), FILTER_VALIDATE_BOOLEAN) : null;

        $otros_tokens = [];
        $baseQuery = $this->getBaseQuery($filters);

        $allData = $baseQuery->select(
            'persons_checks.id as check_id',
            'persons.names',
            'persons.token',
            'divisions.names as div',
            'rols.rol',
            'persons_checks.moment_enter',
            'persons_checks.moment_exit',
            'persons_checks.motive_id',
            'persons_checks.note'
        )->orderBy('persons.token')->orderBy('persons_checks.id')->get();

        $grupos = $allData->groupBy(fn($r) => $r->token ?? $r->person_id);

        $ordenados = collect();

        foreach ($grupos as $token => $grupo) {
            $otros = $grupo->filter(fn($r) => $r->motive_id > 0)->values();
            $normales = $grupo->filter(fn($r) => $r->motive_id == 0)->values();

            $merged = match ($priorizar_otros) {
                true => $otros->concat($normales),
                false => $normales->concat($otros),
                default => $grupo->sortByDesc('check_id')->values()
            };

            $ordenados = $ordenados->concat($merged);
        }

        $total = $ordenados->count();

        $paginado = $ordenados->slice($page * $take, $take)->values();

        $list = $this->parseList($paginado, $otros_tokens);

        $totalesPorToken = $this->getBaseQuery($filters)
            ->select('persons.token', DB::raw('SUM(TIMESTAMPDIFF(SECOND, moment_enter, moment_exit)) as total_seconds'))
            ->groupBy('persons.token')
            ->pluck('total_seconds', 'persons.token')
            ->toArray();

        $otros_tokens_completos = [];
        $this->parseList($ordenados, $otros_tokens_completos);

        return response()->json([
            'total' => $total,
            'list' => $list,
            'persons' => Person::select('id', 'names', 'email')->get(),
            'motives' => Motive::all(),
            'rols' => Rol::all(),
            'divisions' => Division::select('id', 'names')->get(),
            'totales_tokens' => collect($totalesPorToken)->map($this->segundosAHoras()),
            'totales_tokens_otros' => collect($otros_tokens_completos)->map($this->segundosAHoras()),
            'tokens_finalizados' => $ordenados->pluck('token')->unique()->values()
        ], 200);
    }

    private function segundosAHoras()
    {
        return function ($segundos) {
            $h = floor($segundos / 3600);
            $m = floor(($segundos % 3600) / 60);
            $s = $segundos % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        };
    }

    private function getRawList($filters): array
    {
        $person_id = $filters['person'] ?? 0;
        $dstar = $filters['dstar'] ?? now()->startOfDay();
        $dend = $filters['dend'] ?? now()->endOfDay();
        $division = $filters['division'] ?? 0;
        $rol = $filters['rol'] ?? 0;

        $datos = PersonCheck::join('persons', 'persons_checks.person_id', '=', 'persons.id')
            ->leftJoin('divisions', 'persons_checks.division_id', '=', 'divisions.id')
            ->leftJoin('persons_rols', 'persons_rols.person_id', '=', 'persons.id')
            ->leftJoin('rols', 'persons_rols.rol_id', '=', 'rols.id')
            ->leftJoin('motives', 'persons_checks.motive_id', '=', 'motives.id')
            ->whereBetween('persons_checks.moment', [$dstar, $dend]);

        if ($person_id > 0)
            $datos->where('persons.id', $person_id);
        if ($division > 0)
            $datos->where('persons_checks.division_id', $division);
        if ($rol > 0)
            $datos->where('rols.id', $rol);

        $data = $datos->select(
            'persons.names',
            'persons.token',
            'divisions.names as div',
            'rols.rol',
            'persons_checks.moment_enter',
            'persons_checks.moment_exit',
            'persons_checks.motive_id',
            'persons_checks.note',
            'motives.motive as motive_name'
        )->orderBy('persons.token')
            ->orderBy('persons_checks.id', 'desc')
            ->get();

        $list = [];

        foreach ($data as $registro) {
            $entrada = $registro->moment_enter ? Carbon::parse($registro->moment_enter) : null;
            $salida = $registro->moment_exit ? Carbon::parse($registro->moment_exit) : null;

            $diff = ($entrada && $salida && $salida->gte($entrada)) ? $entrada->diffInSeconds($salida) : 0;

            $list[] = [
                'names' => $registro->names,
                'token' => $registro->token,
                'div' => $registro->div,
                'rol' => $registro->rol,
                'moment_enter' => $registro->moment_enter,
                'moment_exit' => $registro->moment_exit,
                'hours' => sprintf('%02d:%02d:%02d', floor($diff / 3600), floor(($diff % 3600) / 60), $diff % 60),
                'seconds' => $diff,
                'note' => $registro->note,
                'motive_id' => $registro->motive_id,
                'motive_name' => $registro->motive_name ?? '',
            ];
        }

        return $list;
    }

    private function getTotalesOtros($agrupados)
    {
        return collect($agrupados)->map(function ($items) {
            $segundos = collect($items)->reduce(function ($acc, $item) {
                if (($item['motive_id'] ?? 0) > 0 && !empty($item['moment_enter']) && !empty($item['moment_exit'])) {
                    $start = \Carbon\Carbon::parse($item['moment_enter']);
                    $end = \Carbon\Carbon::parse($item['moment_exit']);
                    return $acc + $start->diffInSeconds($end);
                }
                return $acc;
            }, 0);
            return sprintf('%02d:%02d:%02d', floor($segundos / 3600), floor(($segundos % 3600) / 60), $segundos % 60);
        })->toArray();
    }

    public function pdf(Request $request)
    {
        $filters = $request->input('filters');
        $list = $this->getRawList($filters);

        if (count($list) <= 0) {
            return response()->json('No existen datos!', 500);
        }

        $agrupados = collect($list)->groupBy('token');

        $resumen = [];

        foreach ($agrupados as $token => $registros) {
            $totalSec = $registros->sum('seconds');
            $horas_int = floor($totalSec / 3600);
            $minutos_int = floor(($totalSec % 3600) / 60);
            $segundos_int = $totalSec % 60;
            $horas = sprintf('%02d:%02d:%02d', $horas_int, $minutos_int, $segundos_int);

            $resumen[$token] = $horas;
        }

        $data = [
            'filters' => $filters,
            'company' => Company::first(),
            'agrupados' => $agrupados,
            'totales' => $resumen,
            'totales_otros' => $this->getTotalesOtros($agrupados),
            'columns' => ['div', 'rol', 'token', 'names', 'moment_enter', 'moment_exit', 'hours', 'note', 'motive_name', 'is_otros'],
            'columns_originales' => ['div', 'rol', 'token', 'names', 'moment_enter', 'moment_exit', 'hours', 'note', 'motive_name', 'is_otros'],
            'is_solo_otros' => false,
        ];

        $pdf = Pdf::loadView('reports.pdf', $data);
        return $pdf->download('reporte.pdf');
    }

    public function export(Request $request)
    {
        $filters = $request->input('filters', []);
        $list = $this->generateList($filters);

        if (count($list) <= 0) {
            return response()->json('No existen datos para exportar', 500);
        }

        $list = $this->generateList($filters);
        return \Excel::download(new PersonCheckXls($list), 'reporte.xlsx');
    }

    private function generateList($filters)
    {
        $person_id = $filters['person'] ?? 0;
        $dstar = $filters['dstar'] ?? now()->startOfDay();
        $dend = $filters['dend'] ?? now()->endOfDay();
        $division = $filters['division'] ?? 0;
        $rol = $filters['rol'] ?? 0;

        $datos = PersonCheck::join('persons', 'persons_checks.person_id', '=', 'persons.id')
            ->leftJoin('divisions', 'persons_checks.division_id', '=', 'divisions.id')
            ->leftJoin('persons_rols', 'persons_rols.person_id', '=', 'persons.id')
            ->leftJoin('rols', 'persons_rols.rol_id', '=', 'rols.id')
            ->leftJoin('motives', 'persons_checks.motive_id', '=', 'motives.id')
            ->whereBetween('persons_checks.moment', [$dstar, $dend]);

        if ($person_id > 0)
            $datos->where('persons.id', $person_id);
        if ($division > 0)
            $datos->where('persons_checks.division_id', $division);
        if ($rol > 0)
            $datos->where('rols.id', $rol);

        $data = $datos->select(
            'persons.names',
            'persons.token',
            'divisions.names as div',
            'rols.rol',
            'persons_checks.moment_enter',
            'persons_checks.moment_exit',
            'persons_checks.motive_id',
            'persons_checks.note',
            'motives.motive as motive_name'
        )->orderBy('persons.token')
            ->orderBy('persons_checks.id', 'desc')
            ->get();

        $list = [];

        foreach ($data as $registro) {
            $entrada = $registro->moment_enter ? Carbon::parse($registro->moment_enter) : null;
            $salida = $registro->moment_exit ? Carbon::parse($registro->moment_exit) : null;

            if ($entrada && $salida) {
                $diff = $entrada->diffInSeconds($salida);
                $horas = sprintf('%02d:%02d:%02d', floor($diff / 3600), floor(($diff % 3600) / 60), $diff % 60);
            } else {
                $horas = '00:00:00';
            }

            $list[] = [
                'id' => $registro->check_id,
                'names' => $registro->names,
                'token' => $registro->token,
                'div' => $registro->div,
                'rol' => $registro->rol,
                'moment_enter' => $registro->moment_enter ? Carbon::parse($registro->moment_enter)->toIso8601String() : null,
                'moment_exit' => $registro->moment_exit ? Carbon::parse($registro->moment_exit)->toIso8601String() : null,
                'hours' => $horas,
                'note' => $registro->note,
                'motive_id' => $registro->motive_id,
                'is_otros' => ($registro->motive_id ?? 0) > 0 ? 'Sí' : 'No',
                'motive_name' => $registro->motive_name ?? '',
            ];
        }

        return $list;
    }

}
