<?php

namespace App\Http\Controllers;

use App\Motive;
use App\Person;
use App\PersonCheck;
use Illuminate\Http\Request;

class CheckController extends Controller
{

    public function index($id=null) {

        return view('checks.check', ['id' => $id]);

    }

    public function getList(Request $request) {

        $skip = $request->input('start') * $request->input('take');

        $filters = $request->input('filters', true);

        $orders = $request->input('orders', true);

        $datos = PersonCheck::leftjoin('motives', 'persons_checks.motive_id', 'motives.id')

            ->leftjoin('divisions', 'persons_checks.division_id', 'divisions.id');

        if ( $filters['value'] !== '') $datos->where( $filters['field'], 'LIKE', '%'.$filters['value'].'%');

        $datos->where('person_id', $filters['person_id']);

        $datos = $datos->orderby($orders['field'], $orders['type']);

        $total = $datos->select('persons_checks.*', 'motives.motive', 'divisions.names as division')->count();

        $list =  $datos->skip($skip)->take($request['take'])->get();

        $result = [

            'total' => $total,

            'list' =>  $list,

            'person' => Person::find($filters['person_id']),

            'motives' => Motive::all()
        ];

        return response()->json($result, 200);
    }

    public function destroy($id)
    {

        PersonCheck::destroy($id);

        return response()->json('Datos eliminados con exito!', 200);

    }
}
