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

    public function getList(Request $request)
    {
        $skip = $request->input('start') * $request->input('take');
        $filters = $request->input('filters', true);

        $person_id = $filters['person'];
        $dstar = $filters['dstar'];
        $dend = $filters['dend'];
        $division = $filters['division'];
        $rol = $filters['rol'];

        $datos = PersonCheck::join('persons', 'persons_checks.person_id', '=', 'persons.id')
            ->leftJoin('divisions', 'persons_checks.division_id', '=', 'divisions.id')
            ->leftJoin('persons_rols', 'persons_rols.person_id', '=', 'persons.id')
            ->leftJoin('rols', 'persons_rols.rol_id', '=', 'rols.id')
            ->whereBetween('persons_checks.moment', [$dstar, $dend]);

        if ($person_id > 0)
            $datos->where('persons.id', $person_id);
        if ($division > 0)
            $datos->where('persons_checks.division_id', $division);
        if ($rol > 0)
            $datos->where('rols.id', $rol);

        $total = (clone $datos)->count();

        $data = $datos->select(
            'persons_checks.id as check_id',
            'persons.names',
            'persons.token',
            'divisions.names as div',
            'rols.rol',
            'persons_checks.moment_enter',
            'persons_checks.moment_exit'
        )->orderBy('persons.token')
            ->orderBy('persons_checks.id', 'desc')
            ->skip($skip)->take($request['take'])->get();

        $list = [];

        foreach ($data as $registro) {
            $entrada = $registro->moment_enter ? Carbon::parse($registro->moment_enter) : null;
            $salida = $registro->moment_exit ? Carbon::parse($registro->moment_exit) : null;
            $horas = ($entrada && $salida) ? $entrada->diff($salida)->format('%H:%I:%S') : '0:00';

            $list[] = [
                'id' => $registro->check_id,
                'names' => $registro->names,
                'token' => $registro->token,
                'div' => $registro->div,
                'rol' => $registro->rol,
                'moment_enter' => $registro->moment_enter ? Carbon::parse($registro->moment_enter)->toIso8601String() : null,
                'moment_exit' => $registro->moment_exit ? Carbon::parse($registro->moment_exit)->toIso8601String() : null,
                'hours' => $horas
            ];
        }

        $result = [
            'total' => $total,
            'list' => $list,
            'persons' => Person::select('id', 'names', 'email')->get(),
            'motives' => Motive::all(),
            'rols' => Rol::all(),
            'divisions' => Division::select('id', 'names')->get(),
        ];

        // Totales
        $totalesPorToken = PersonCheck::join('persons', 'persons_checks.person_id', '=', 'persons.id')
            ->leftJoin('divisions', 'persons_checks.division_id', '=', 'divisions.id')
            ->leftJoin('persons_rols', 'persons_rols.person_id', '=', 'persons.id')
            ->leftJoin('rols', 'persons_rols.rol_id', '=', 'rols.id')
            ->whereBetween('persons_checks.moment', [$dstar, $dend]);

        if ($person_id > 0)
            $totalesPorToken->where('persons.id', $person_id);
        if ($division > 0)
            $totalesPorToken->where('persons_checks.division_id', $division);
        if ($rol > 0)
            $totalesPorToken->where('rols.id', $rol);

        $totalesPorToken = $totalesPorToken
            ->select('persons.token', DB::raw('SUM(TIMESTAMPDIFF(SECOND, moment_enter, moment_exit)) as total_seconds'))
            ->groupBy('persons.token')
            ->pluck('total_seconds', 'persons.token')
            ->toArray();

        $result['totales_tokens'] = collect($totalesPorToken)->map(function ($segundos) {
            $h = floor($segundos / 3600);
            $m = floor(($segundos % 3600) / 60);
            $s = $segundos % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        });

        // Tokens finalizados
        $idsPorToken = PersonCheck::join('persons', 'persons_checks.person_id', '=', 'persons.id')
            ->whereBetween('persons_checks.moment', [$dstar, $dend]);

        if ($person_id > 0)
            $idsPorToken->where('persons.id', $person_id);
        if ($division > 0)
            $idsPorToken->where('persons_checks.division_id', $division);
        if ($rol > 0)
            $idsPorToken->where('rols.id', $rol);

        $result['tokens_finalizados'] = $idsPorToken
            ->select('persons.token', DB::raw('MAX(persons_checks.id) as max_id'))
            ->groupBy('persons.token')
            ->pluck('max_id', 'persons.token')
            ->keys()
            ->toArray();

        return response()->json($result, 200);
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
            'persons_checks.moment_exit'
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
            ];
        }

        return $list;
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
        ];

        // Generar el PDF
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
            'persons_checks.moment_exit'
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
                'hours' => $horas
            ];
        }

        return $list;
    }

}
