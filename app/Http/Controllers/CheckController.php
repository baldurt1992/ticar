<?php

namespace App\Http\Controllers;

use App\Motive;
use App\Person;
use App\PersonCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckController extends Controller
{

    public function index($id = null)
    {

        return view('checks.check', ['id' => $id]);

    }

    public function getList(Request $request)
    {
        $skip = $request->input('start') * $request->input('take');
        $filters = $request->input('filters', true);
        $orders = $request->input('orders', true);

        $datos = PersonCheck::leftJoin('divisions', 'persons_checks.division_id', '=', 'divisions.id');

        if ($filters['value'] !== '') {
            $datos->where($filters['field'], 'LIKE', '%' . $filters['value'] . '%');
        }

        $datos->where('person_id', $filters['person_id']);
        $datos = $datos->orderby($orders['field'], $orders['type']);

        $datos->select(
            'persons_checks.id',
            'persons_checks.check_ip',
            'persons_checks.moment',
            'persons_checks.moment_enter',
            'persons_checks.moment_exit',
            'persons_checks.note',
            'persons_checks.person_id',
            'persons_checks.motive_id',
            'divisions.names as division'
        );

        $total = (clone $datos)->count();
        $list = $datos->skip($skip)->take($request['take'])->get()->load('motive');
        $list = $list->map(function ($item) {
            return [
                'id' => $item->id,
                'check_ip' => $item->check_ip,
                'moment' => $item->moment,
                'moment_enter' => $item->moment_enter,
                'moment_exit' => $item->moment_exit,
                'note' => $item->note,
                'person_id' => $item->person_id,
                'motive_id' => $item->motive_id,
                'motive' => $item->motive_id > 0 && $item->motive ? $item->motive->motive : '-',
                'division' => $item->division,
            ];
        });

        $totalHoras = PersonCheck::where('person_id', $filters['person_id'])
            ->when($filters['value'] !== '', function ($query) use ($filters) {
                return $query->where($filters['field'], 'LIKE', '%' . $filters['value'] . '%');
            })
            ->selectRaw("SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(moment_exit, moment_enter)))) as total")
            ->value('total');

        $result = [
            'total' => $total,
            'list' => $list,
            'person' => Person::find($filters['person_id']),
            'motives' => Motive::all(),
            'total_hours' => $totalHoras ?? '00:00:00' // ✅ Agrega al JSON
        ];

        return response()->json($result, 200);
    }


    public function destroy($id)
    {

        PersonCheck::destroy($id);

        return response()->json('Datos eliminados con exito!', 200);

    }
}
