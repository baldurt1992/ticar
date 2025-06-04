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
    public function index() {

        return view('reports.rep') ;

    }

    public function getList(Request $request) {

        $skip = $request->input('start') * $request->input('take');

        $filters = $request->input('filters', true);

        $person_id = $filters['person'];

        $dstar = $filters['dstar'];

        $dend =  $filters['dend'];

        $division = $filters['division'];

        $rol = $filters['rol'];

        $datos = Person::leftjoin('persons_divisions','persons_divisions.person_id', 'persons.id')

            ->leftjoin('divisions','persons_divisions.division_id', 'divisions.id')

             ->leftjoin('persons_rols','persons_rols.person_id', 'persons.id')

             ->leftjoin('rols','persons_rols.rol_id', 'rols.id')

             ->leftjoin('persons_checks','persons_checks.person_id', 'persons.id');

        if ($person_id > 0) $datos->where('persons.id', $person_id);

        if ($division > 0) $datos->where('divisions.id', $division);

        $datos->whereBetween('moment', [$dstar, $dend]);

        $datos->orderby('moment', 'asc');

        if ($rol > 0) $datos->where( 'rols.id', $rol);

        $total = $datos->select('persons.names', 'persons.token', 'persons.id', 'divisions.names as div', 'rols.rol', DB::raw('DATE_FORMAT(persons_checks.moment, "%Y-%m-%d %H:%i") as moment'))->count();

        $data =  $datos->skip($skip)->take($request['take'])->get();

       // return response()->json($data, 200);
        $list = [];

        $times = [];

        for ($i = 0; $i <= count($data) - 1; $i++) {

            $start = Carbon::parse($data[$i]['moment']);

            if ($i + 1 <= count($data) - 1) {

                $end = Carbon::parse($data[$i+1]['moment']);

            } else {

                $data[$i]['dend'] = '-';

                $data[$i]['dstar'] = $data[$i]['moment'];

                $data[$i]['hours'] = 0;

                $list[] = is_array($data[$i]) ? $data[$i] : $data[$i]->toArray();


                break;
            }

            if ( $start->day == $end->day) {

                $hours = (Carbon::parse($data[$i+1]['moment'])->diffInMinutes(Carbon::parse($data[$i]['moment'])));

                $times[] = $hours ;

                $data[$i]['dend'] = $data[$i+1]['moment'];

                $data[$i]['dstar'] = $data[$i]['moment'];

                if (!is_int( $hours / 60)) {

                    $aux = (string) $hours /60;

                    $h = explode('.', $aux);

                    $h[1] = round(((int) substr( $h[1], 0, 2) / 100) * 60);

                    $h[1] = $h[1] < 10 ? '0'.$h[1] : $h[1];
                }  else {

                    $h[0] = $hours / 60;  $h[1] = 0;
                }

                $data[$i]['hours'] = $h[0] . ':' . $h[1]; // number_format($hours / 60, 2, ':', '');

                $list[] = is_array($data[$i]) ? $data[$i] : $data[$i]->toArray();


                if (($i + 2) <= count($data) - 1) {

                    if ($end->day < Carbon::parse($data[$i+2]['moment'])->day) {

                        $totalminut = collect($times)->reduce(function ($carry, $item) {
                            return $carry + $item;
                        });

                        $aux = (string) $totalminut  /60;

                        $h = explode('.', $aux);

                        $h[1] = round(((int) substr( $h[1], 0, 2) / 100) * 60);

                        $h[1] = $h[1] < 10 ? '0'.$h[1] : $h[1];

                        $list[] = ['-', '-', '-', '-', '-', 'dend' => 'Total', 'hours' => $h[0] . ':' . $h[1]];

                        $times= [];
                    }

                }

                $i = $i + 1;

                if (($i + 2) > count($data) - 1) { $i = count($data) - 1;}


            } else {

                $data[$i]['dend'] = '-';

                $data[$i]['dstar'] = $data[$i]['moment'];

                $data[$i]['hours'] = '0';

                $list[] = is_array($data[$i]) ? $data[$i] : $data[$i]->toArray();


                $totalminut = collect($times)->reduce(function ($carry, $item) {
                    return $carry + $item;
                });

                $hours = $totalminut * (1/60);

                $list[] = ['-', '-', '-', '-', '-', 'dend' => 'Total', 'hours' => number_format($hours, 2, '.', '')];

                $times= [];
            }

        }

        $result = [

            'total' => $total,

            'list' =>  $list,

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
        $dstar     = $filters['dstar'] ?? now()->startOfDay();
        $dend      = $filters['dend'] ?? now()->endOfDay();
        $division  = $filters['division'] ?? 0;
        $rol       = $filters['rol'] ?? 0;

        $datos = Person::leftjoin('persons_divisions','persons_divisions.person_id', 'persons.id')
            ->leftjoin('divisions','persons_divisions.division_id', 'divisions.id')
            ->leftjoin('persons_rols','persons_rols.person_id', 'persons.id')
            ->leftjoin('rols','persons_rols.rol_id', 'rols.id')
            ->leftjoin('persons_checks','persons_checks.person_id', 'persons.id');

        if ($person_id > 0) $datos->where('persons.id', $person_id);
        if ($division > 0) $datos->where('divisions.id', $division);
        if ($rol > 0) $datos->where('rols.id', $rol);

        $datos->whereBetween('moment', [$dstar, $dend]);
        $datos->orderBy('moment', 'asc');

        $data = $datos->select(
            'persons.names', 'persons.token', 'persons.id',
            'divisions.names as div', 'rols.rol',
            DB::raw('DATE_FORMAT(persons_checks.moment, "%Y-%m-%d %H:%i") as moment')
        )->get();

        $list = [];
        $times = [];

        for ($i = 0; $i <= count($data) - 1; $i++) {
            $start = Carbon::parse($data[$i]['moment']);

            if ($i + 1 <= count($data) - 1) {
                $end = Carbon::parse($data[$i + 1]['moment']);
            } else {
                $list[] = [
                    'names' => $data[$i]['names'],
                    'token' => $data[$i]['token'],
                    'div'   => $data[$i]['div'],
                    'rol'   => $data[$i]['rol'],
                    'moment'=> $data[$i]['moment'],
                    'dstar' => $data[$i]['moment'],
                    'dend'  => '-',
                    'hours' => 0
                ];
                break;
            }

            if ($start->day == $end->day) {
                $minutes = $end->diffInMinutes($start);
                $times[] = $minutes;

                $h = floor($minutes / 60);
                $m = str_pad($minutes % 60, 2, '0', STR_PAD_LEFT);

                $list[] = [
                    'names' => $data[$i]['names'],
                    'token' => $data[$i]['token'],
                    'div'   => $data[$i]['div'],
                    'rol'   => $data[$i]['rol'],
                    'moment'=> $data[$i]['moment'],
                    'dstar' => $data[$i]['moment'],
                    'dend'  => $data[$i + 1]['moment'],
                    'hours' => "$h:$m"
                ];

                if (($i + 2) <= count($data) - 1 && $end->day < Carbon::parse($data[$i + 2]['moment'])->day) {
                    $totalMin = collect($times)->sum();
                    $th = floor($totalMin / 60);
                    $tm = str_pad($totalMin % 60, 2, '0', STR_PAD_LEFT);

                    $list[] = ['names'=> '', 'token'=>'', 'div'=>'', 'rol'=>'', 'moment'=>'-', 'dstar'=>'', 'dend'=>'Total', 'hours'=>"$th:$tm"];
                    $times = [];
                }

                $i++;
                if ($i + 2 > count($data) - 1) $i = count($data) - 1;

            } else {
                $list[] = [
                    'names' => $data[$i]['names'],
                    'token' => $data[$i]['token'],
                    'div'   => $data[$i]['div'],
                    'rol'   => $data[$i]['rol'],
                    'moment'=> $data[$i]['moment'],
                    'dstar' => $data[$i]['moment'],
                    'dend'  => '-',
                    'hours' => '0'
                ];

                $totalMin = collect($times)->sum();
                $th = floor($totalMin / 60);
                $tm = str_pad($totalMin % 60, 2, '0', STR_PAD_LEFT);

                $list[] = ['names'=> '', 'token'=>'', 'div'=>'', 'rol'=>'', 'moment'=>'-', 'dstar'=>'', 'dend'=>'Total', 'hours'=>"$th:$tm"];
                $times = [];
            }
        }

        return $list;
    }

}
