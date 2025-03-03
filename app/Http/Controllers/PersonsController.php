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
use Illuminate\Support\Str;
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

    public function getList(Request $request)
    {

        $skip = $request->input('start') * $request->input('take');

        $filters = $request->input('filters', true);

        $orders = $request->input('orders', true);

        $datos = Person::with('Status', 'Rols', 'Divisions');

        if ($filters['value'] !== '') $datos->where($filters['field'], 'LIKE', '%' . $filters['value'] . '%');

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
        foreach ($rols as $rl) {

            PersonRol::create([

                'person_id' =>  $person_id,

                'rol_id' => $rl
            ]);
        }

        // Divisiones
        foreach ($divisions as $dl) {

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
        foreach ($rols as $rl) {

            PersonRol::create([

                'person_id' =>  $id,

                'rol_id' => $rl
            ]);
        }

        // Divisiones
        PersonDivision::where('person_id', $id)->delete();
        foreach ($divisions as $dl) {

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

    public function check(Request $request)
    {

        $data = $request->all();
        $person = Person::where('token', $data['token'])->first();

        if (empty($person)) {
            return response()->json('No esta registrado!', 500);
        }

        //$entradasalida = PersonCheck::where('person_id', $person->id)->where(DB::raw('DAY(moment)'), Carbon::now()->day)->update(['moment_exit' => Carbon::now()->format('H:i:s')]);

        // Comprobar si existe un registro de hoy con moment_enter relleno y moment_exit vacío
        $registroHoy = PersonCheck::where('person_id', $person->id)
            ->whereNotNull('moment_enter')
            ->whereNull('moment_exit')
            ->where('motive_id', 0) //aseguramos que es el registro de entrada
            ->first();

        if ($registroHoy) {
            // Verificar si la fecha del registro es de hoy o de ayer
            $fechaRegistro = Carbon::createFromFormat('d/m/Y H:i', $registroHoy->moment)->startOfDay();

            //Si el registro de entrada sin salida es de hoy se actualiza la salida con la hora actual
            if ($fechaRegistro->isToday()) {
                if (!empty($data['motive_id'])) {
                    return $this->checkMotive($data, $registroHoy, $person);
                } else {
                    $registroHoy->update(['moment_exit' => Carbon::now()->format('Y-m-d H:i:s')]);
                    return response()->json('Se ha realizado la salida correctamente. Gracias ' . $person->names, 200);
                }
            }
            //Si el registro de entrada sin salida es de ayer se informa al usuario
            elseif ($fechaRegistro->isYesterday()) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Tiene una entrada activa sin una salida con fecha de ayer ' . $fechaRegistro->toDateString(),
                    'action_required' => true // Indica que el frontend debe mostrar un modal
                ], 200);
            }
            // Si el registro de entrada sin salida no es de hoy ni de ayer se informa al usuario
            else {
                return response()->json('El registro con fecha' . $fechaRegistro, 200);
            }
        } else {
            if (!empty($data['motive_id'])) {
                return response()->json('Todavía no has hecho registro de entrada ' . $person->names, 200);
            }
            return $this->checkin(new Request($data));
        }
    }

    public function checkin(Request $request)
    {

        $data = $request->all();

        //** tratamiento de las imagenes capturadas */
        //$person = Person::where('token', $data['token'])->first();

        // $imagen64 = str_replace("data:image/png;base64,", "", $data['screen']);

        // $imagen = base64_decode($imagen64);

        // $img = Image::make($imagen)->resize(400, 300);
        //** fin de tratamiento de las inmagenes capturadas */

        $person = Person::where('token', $data['token'])->first();

        $moment = Carbon::now();

        $moment_enter = Carbon::now()->format('Y-m-d H:i:s');


        $nombre = $moment->toIso8601String() . ".png";

        // Sacar IP

        $client = new Client();
        $response = $client->get('http://ipinfo.io/json');

        $data_location = json_decode($response->getBody()->getContents(), true);
        $location_ip = $data_location['ip']; // "IP Address"

        $co = PersonCheck::where('person_id', $person->id)->where(DB::raw('DAY(moment)'), $moment->day)->count();

        if (!empty($data['motive_id'])) {
            $message = $co % 2 == 0 ? 'Gracias por registar su pausa ' . $person->names : 'Gracias por registar su regreso de paso ' . $person->names;
        } else {
            $message = $co % 2 == 0 ? 'Gracias por registar su entrada ' . $person->names : 'Gracias por registar su salida ' . $person->names;
        }


        PersonCheck::create([

            'person_id' => $person->id,

            'moment' => $moment,

            'moment_enter' => $moment_enter,

            'motive_id' => $data['motive_id'],

            'division_id' => $data['division_id'],

            'note' => $data['note'],

            'url_screen' => 'storage/' . $person->token . '/' . $nombre,

            'check_ip' => $location_ip

        ]);

        return response()->json($message, 200);
    }

    protected function checkMotive($data, $registroHoy, $person)
    {
        $registroHoyMotivo = PersonCheck::where('person_id', $registroHoy->person_id)
            ->whereNotNull('moment_enter')
            ->where('motive_id', '>', 0)
            ->whereNull('moment_exit')
            ->first();

        if ($registroHoyMotivo) {
            // Verificar si la fecha del registro es de hoy o de ayer
            $fechaRegistro = Carbon::createFromFormat('d/m/Y H:i', $registroHoyMotivo->moment)->startOfDay();

            //Si el registro de entrada sin salida es de hoy se actualiza la salida con la hora actual
            if ($fechaRegistro->isToday()) {

                $registroHoyMotivo->update(['moment_exit' => Carbon::now()->format('Y-m-d H:i:s')]);
                return response()->json('Se ha registrado regreso de pausa correctamente. Gracias ' . $person->names, 200);
            }
            //
            elseif ($fechaRegistro->isYesterday()) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Tiene una entrada activa sin una salida con fecha de ayer ' . $fechaRegistro->toDateString(),
                    'action_required' => true // Indica que el frontend debe mostrar un modal
                ], 200);
            }
            // Si el registro de entrada sin salida no es de hoy ni de ayer se informa al usuario
            else {
                return response()->json('El registro con fecha' . $fechaRegistro, 200);
            }
        } else {
            return $this->checkin(new Request($data));
        }
    }
}
