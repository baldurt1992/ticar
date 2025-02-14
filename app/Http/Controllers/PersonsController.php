<?php

namespace App\Http\Controllers;

use App\Rol;
use App\Person;
use App\Division;
use App\PersonRol;
use Carbon\Carbon;
use Nexmo\Response;
use App\PersonCheck;
use GuzzleHttp\Client;
use App\PersonDivision;
use Illuminate\Http\Request;
use App\Exports\PersonCheckXls;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Cache\Store;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class PersonsController extends Controller
{
    public function index()
    {
        return view('persons.person');
    }

    public function getList(Request $request) {

            $skip = $request->input('start') * $request->input('take');

            $filters = $request->input('filters', true);

            $orders = $request->input('orders', true);

            $datos = Person::with('Status', 'Rols', 'Divisions');

            if ( $filters['value'] !== '') $datos->where( $filters['field'], 'LIKE', '%'.$filters['value'].'%');

            $datos = $datos->orderby($orders['field'], $orders['type']);

            $total = $datos->select('*')->count();

            $list =  $datos->skip($skip)->take($request['take'])->get();

            $result = [

                'total' => $total,

                'list' =>  $list,

                'rols' => Rol::all(),

                'divisions' => Division::select('id', 'names')->get()

            ];

            return response()->json($result, 200);
    }

    public function store(Request $request)
    {

        $person_id = Person::create($request->input('person'))->id;

        $divisions = $request->input('data.divisions');

        $rols = $request->input('data.rols');

       // ROLES
        foreach ( $rols as $rl) {

            PersonRol::create([

                'person_id' =>  $person_id,

                'rol_id' => $rl
            ]);
        }

        // Divisiones
        foreach ( $divisions as $dl) {

            PersonDivision::create([

                'person_id' =>  $person_id,

                'division_id' => $dl
            ]);
        }

        return response()->json('Trabajador añadido con exito!', 200);
    }


    public function update(Request $request, $id)
    {

        Person::where('id', $id)->update($request->input('person'));


        $divisions = $request->input('data.divisions');

        $rols = $request->input('data.rols');

        // ROLES

        PersonRol::where('person_id', $id)->delete();
        foreach ( $rols as $rl) {

            PersonRol::create([

                'person_id' =>  $id,

                'rol_id' => $rl
            ]);
        }

        // Divisiones
        PersonDivision::where('person_id', $id)->delete();
        foreach ( $divisions as $dl) {

            PersonDivision::create([

                'person_id' =>  $id,

                'division_id' => $dl
            ]);
        }

        return response()->json('Datos actualizados con exito!', 200);
    }


    public function destroy($id)
    {

        Person::destroy($id);

        PersonDivision::where('person_id', $id)->delete();

        PersonRol::where('person_id', $id)->delete();

        return response()->json('Datos eliminados con exito!', 200);

    }

    public function check(Request $request) {

       $data = $request->all();

       $person = Person::where('token', $data['token'])->first();

       if (empty($person)) { return response()->json('No esta registrado!', 500);}

      // $imagen64 = str_replace("data:image/png;base64,", "", $data['screen']);

      // $imagen = base64_decode($imagen64);

      // $img = Image::make($imagen)->resize(400, 300);

       $moment = Carbon::now();

       $nombre = $moment->toIso8601String(). ".png";
       
       // Sacar IP

       $client = new Client();
       $response = $client->get('http://ipinfo.io/json');
   
       $data_location = json_decode($response->getBody()->getContents(), true);
       $location_ip = $data_location['ip']; // "IP Address"
   
       $co = PersonCheck::where('person_id', $person->id)->where(DB::raw('DAY(moment)'), $moment->day)->count();

       $message = $co % 2 == 0 ? 'Gracias por registar su entrada ' . $person->names : 'Gracias por registar su salida '. $person->names ;

       PersonCheck::create([

           'person_id' => $person->id,

           'moment' => $moment,

           'motive_id' => $data['motive_id'],

           'division_id' => $data['division_id'],

           'note' => $data['note'],

           'url_screen' => 'storage/' . $person->token . '/' .$nombre,

           'check_ip' => $location_ip

       ]);

       return response()->json($message, 200);
    }
}
