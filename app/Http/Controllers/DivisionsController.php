<?php

namespace App\Http\Controllers;

use App\Division;
use App\Person;
use App\PersonCheck;
use App\Rol;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Nexmo\Response;

class DivisionsController extends Controller
{
    public function index()
    {
        return view('divisions.div');
    }

    public function data($token)
    {

        $division = Person::with('divisions')->where('token', $token)->first();

        if (empty($division))  return response()->json('No existes en nuestra base de datos!!',  500);

        $ids = $division->divisions->pluck('division_id');

        $division = Division::wherein('id', $ids)->get();

        return response()->json($division, 200);
    }

    public function getList(Request $request) {

            $skip = $request->input('start') * $request->input('take');

            $filters = $request->input('filters', true);

            $orders = $request->input('orders', true);

            $datos = Division::with('Status');

            if ( $filters['value'] !== '') $datos->where( $filters['field'], 'LIKE', '%'.$filters['value'].'%');

            $datos = $datos->orderby($orders['field'], $orders['type']);

            $total = $datos->select('*')->count();

            $list =  $datos->skip($skip)->take($request['take'])->get();

            $result = [

                'total' => $total,

                'list' =>  $list,

            ];

            return response()->json($result, 200);
    }

    public function store(Request $request)
    {

        Division::create($request->input('divisions'));

        return response()->json('Division añadido con exito!', 200);
    }


    public function update(Request $request, $id)
    {

        Division::where('id', $id)->update($request->input('divisions'));

        return response()->json('Datos actualizados con exito!', 200);
    }


    public function destroy($id)
    {

        Division::destroy($id);

        return response()->json('Datos eliminados con exito!', 200);

    }

}
