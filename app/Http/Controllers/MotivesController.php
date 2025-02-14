<?php

namespace App\Http\Controllers;

use App\Motive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MotivesController extends Controller
{
    public function index()
    {
        return view('motives.mot');
    }

    public function getMotives()
    {
        return response()->json(Motive::all());
    }


    public function getList(Request $request) {

        $skip = $request->input('start') * $request->input('take');

        $filters = $request->input('filters', true);

        $orders = $request->input('orders', true);

        $datos = DB::table('motives');

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

        Motive::create($request->input('motive'));

        return response()->json('Motivo añadido con exito!', 200);
    }


    public function update(Request $request, $id)
    {

        Motive::where('id', $id)->update($request->input('motive'));

        return response()->json('Datos actualizados con exito!', 200);
    }


    public function destroy($id)
    {

        Motive::destroy($id);

        return response()->json('Datos eliminados con exito!', 200);

    }
}
