<?php

namespace App\Http\Controllers;

use App\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolsController extends Controller
{
    public function index()
    {
        return view('rols.rol');
    }

    public function getList(Request $request) {

        $skip = $request->input('start') * $request->input('take');

        $filters = $request->input('filters', true);

        $orders = $request->input('orders', true);

        $datos = DB::table('rols');

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

        Rol::create($request->input('rol'));

        return response()->json('Rol añadido con exito!', 200);
    }


    public function update(Request $request, $id)
    {

        Rol::where('id', $id)->update($request->input('rol'));

        return response()->json('Datos actualizados con exito!', 200);
    }


    public function destroy($id)
    {

        Rol::destroy($id);

        return response()->json('Datos eliminados con exito!', 200);

    }
}
