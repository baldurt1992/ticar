<?php

namespace App\Http\Controllers;

use App\Division;
use App\Motive;
use App\Person;
use App\PersonCheck;
use Illuminate\Http\Request;

class PersonDivController extends Controller
{

    public function index($id=null) {

        return view('divisions.persons', ['id' => $id]);

    }

    public function getList(Request $request) {

        $skip = $request->input('start') * $request->input('take');

        $filters = $request->input('filters', true);

        $orders = $request->input('orders', true);

        $datos = Person::leftjoin('persons_divisions', 'persons_divisions.person_id', 'persons.id');

        if ( $filters['value'] !== '') $datos->where( $filters['field'], 'LIKE', '%'.$filters['value'].'%');

        $datos->where('persons_divisions.division_id', $filters['division_id']);

        $datos = $datos->orderby($orders['field'], $orders['type']);

        $total = $datos->select('persons.*')->count();

        $list =  $datos->skip($skip)->take($request['take'])->get();

        $result = [

            'total' => $total,

            'list' =>  $list,

            'division' => Division::find($filters['division_id']),

        ];

        return response()->json($result, 200);
    }

    public function destroy($id)
    {

        PersonCheck::destroy($id);

        return response()->json('Datos eliminados con exito!', 200);

    }
}
