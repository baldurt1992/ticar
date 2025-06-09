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
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;


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

        $datos = Person::leftjoin('persons_divisions', 'persons_divisions.person_id', 'persons.id')
            ->leftjoin('divisions', 'persons_divisions.division_id', 'divisions.id')
            ->leftjoin('persons_rols', 'persons_rols.person_id', 'persons.id')
            ->leftjoin('rols', 'persons_rols.rol_id', 'rols.id')
            ->leftjoin('persons_checks', 'persons_checks.person_id', 'persons.id');

        if ($person_id > 0)
            $datos->where('persons.id', $person_id);
        if ($division > 0)
            $datos->where('divisions.id', $division);
        if ($rol > 0)
            $datos->where('rols.id', $rol);

        $datos->whereBetween('moment', [$dstar, $dend]);
        $datos->orderBy('moment', 'asc');

        $total = (clone $datos)->select('persons_checks.id')->count();

        $data = $datos->select(
            'persons.names',
            'persons.token',
            'persons.id',
            'divisions.names as div',
            'rols.rol',
            'persons_checks.moment_enter',
            'persons_checks.moment_exit'
        )->skip($skip)->take($request['take'])->get();

        $list = [];

        foreach ($data as $registro) {
            $entrada = $registro->moment_enter ? Carbon::parse($registro->moment_enter) : null;
            $salida = $registro->moment_exit ? Carbon::parse($registro->moment_exit) : null;

            if ($entrada && $salida) {
                $diff = $entrada->diffInMinutes($salida);
                $h = floor($diff / 60);
                $m = str_pad($diff % 60, 2, '0', STR_PAD_LEFT);
                $horas = "$h:$m";
            } else {
                $horas = '0:00';
            }

            $list[] = [
                'names' => $registro->names,
                'token' => $registro->token,
                'div' => $registro->div,
                'rol' => $registro->rol,
                'moment_enter' => $registro->moment_enter,
                'moment_exit' => $registro->moment_exit,
                'hours' => $horas
            ];
        }

        $result = [
            'total' => $total,
            'list' => $list,
            'persons' => Person::select('id', 'names')->get(),
            'motives' => Motive::all(),
            'rols' => Rol::all(),
            'divisions' => Division::select('id', 'names')->get(),
        ];

        return response()->json($result, 200);
    }

    public function pdf(Request $request)
    {
        $filters = $request->input('filters');
        $list = $this->generateList($filters);

        if (count($list) <= 0) {
            return response()->json('No existen datos!', 500);
        }

        $result = [
            'list' => $list,
            'filters' => $filters,
            'company' => Company::first()
        ];

        $html = \View::make('reports.pdf', $result)->render();

        $pdf = App::make('snappy.pdf.wrapper');
        $pdf->setBinary(env('WKHTMLTOPDF_PATH'));

        $pdf->loadHTML($html);

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="reporte.pdf"');
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

        $datos = Person::leftjoin('persons_divisions', 'persons_divisions.person_id', 'persons.id')
            ->leftjoin('divisions', 'persons_divisions.division_id', 'divisions.id')
            ->leftjoin('persons_rols', 'persons_rols.person_id', 'persons.id')
            ->leftjoin('rols', 'persons_rols.rol_id', 'rols.id')
            ->leftjoin('persons_checks', 'persons_checks.person_id', 'persons.id');

        if ($person_id > 0)
            $datos->where('persons.id', $person_id);
        if ($division > 0)
            $datos->where('divisions.id', $division);
        if ($rol > 0)
            $datos->where('rols.id', $rol);

        $datos->whereBetween('moment', [$dstar, $dend]);
        $datos->orderBy('moment', 'asc');

        // ⚠️ Elimina skip/take completamente
        $data = $datos->select(
            'persons.names',
            'persons.token',
            'persons.id',
            'divisions.names as div',
            'rols.rol',
            'persons_checks.moment_enter',
            'persons_checks.moment_exit'
        )->get();

        $list = [];

        foreach ($data as $registro) {
            $entrada = $registro->moment_enter ? Carbon::parse($registro->moment_enter) : null;
            $salida = $registro->moment_exit ? Carbon::parse($registro->moment_exit) : null;

            if ($entrada && $salida) {
                $diff = $entrada->diffInMinutes($salida);
                $h = floor($diff / 60);
                $m = str_pad($diff % 60, 2, '0', STR_PAD_LEFT);
                $horas = "$h:$m";
            } else {
                $horas = '0:00';
            }

            $list[] = [
                'names' => $registro->names,
                'token' => $registro->token,
                'div' => $registro->div,
                'rol' => $registro->rol,
                'moment_enter' => $registro->moment_enter,
                'moment_exit' => $registro->moment_exit,
                'hours' => $horas
            ];
        }

        return $list;
    }

}
