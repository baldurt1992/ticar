<?php

namespace App\Http\Controllers;

use App\Company;
use App\Division;
use App\Exports\PersonCheckXls;
use App\Motive;
use App\Person;
use App\PersonCheck;
use App\Rol;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use Nexmo\Call\Collection;
use phpDocumentor\Reflection\Types\Object_;

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

                $list[] = $data[$i];

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

                $list[] = $data[$i];

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

                $list[] = $data[$i];

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

    public function pdf(Request $request) {

        $pdf = \App::make('snappy.pdf.wrapper');

       // $skip = $request->input('start') * $request->input('take');

        $filters = $request->input('filters');

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

        $data =  $datos->get();

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

                $list[] = $data[$i];

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

                $list[] = $data[$i]->toArray();

                if (($i + 2) <= count($data) - 1) {

                    if ($end->day < Carbon::parse($data[$i+2]['moment'])->day) {

                        $totalminut = collect($times)->reduce(function ($carry, $item) {
                            return $carry + $item;
                        });

                        $aux = (string) $totalminut  /60;

                        $h = explode('.', $aux);

                        $h[1] = round(((int) substr( $h[1], 0, 2) / 100) * 60);

                        $h[1] = $h[1] < 10 ? '0'.$h[1] : $h[1];

                        $list[] =['names'=> '', 'token' =>'', 'div' => '', 'rol' => '',  'moment' => '-', 'dstar' => '', 'dend' => 'Total', 'hours' => $h[0] . ':' . $h[1]];

                        $times= [];
                    }

                }

                $i = $i + 1;

                if (($i + 2) > count($data) - 1) { $i = count($data) - 1;}


            } else {

                $data[$i]['dend'] = '-';

                $data[$i]['dstar'] = $data[$i]['moment'];

                $data[$i]['hours'] = '0';

                $list[] = $data[$i]->toArray();

                $totalminut = collect($times)->reduce(function ($carry, $item) {
                    return $carry + $item;
                });

                $hours = $totalminut * (1/60);

                $list[] =  ['names'=> '', 'token' =>'', 'div' => '', 'rol' => '',  'moment' => '-', 'dstar' => '', 'dend' => 'Total', 'hours' => number_format($hours, 2, '.', '')];

                $times= [];
            }

        }
      //  return response()->json($list, 200);

        $result = [

            'list' =>  $list,

            'filters' =>  $filters,

            'company' => Company::first()

        ];

        if (count($list) <= 0) { return response()->json('No exiten datos!', 500);}
        if (count($list) <= 0) { return response()->json('No exiten datos!', 500);}

        $footer = \View::make('reports.footer_simple')->render();

        $html = \View::make('reports.pdf',  $result)->render();

        $pdf->loadHTML($html)->setOption('footer-html', $footer);

        $pdfBase64 = base64_encode($pdf->inline());

        return 'data:application/pdf;base64,' . $pdfBase64;
    }

    public function export (Request $request) {


        $filters = $request->all();

        return \Excel::download(new PersonCheckXls($filters), 'personal.xlsx');
    }
}
