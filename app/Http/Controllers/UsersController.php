<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        return view('users.user');
    }

    public function getList(Request $request) {

        $skip = $request->input('start') * $request->input('take');

        $filters = $request->input('filters', true);

        $orders = $request->input('orders', true);

        $datos = User::with('Status');

        if ( $filters['value'] !== '') $datos->where( $filters['field'], 'LIKE', '%'.$filters['value'].'%');

        $datos = $datos->orderby($orders['field'], $orders['type']);

        $total = $datos->select('*')->count();

        $list =  $datos->skip($skip)->take($request['take'])->get();

        $result = [

            'total' => $total,

            'list' =>  $list

        ];

        return response()->json($result, 200);
    }

    public function store(Request $request)
    {

        $data = $request->input('user');

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) return response()->json('Ya existe un usuario con ese email!', 500);

        User::create([

            'name' => $data['name'],

            'email' => $data['email'],

            'password' => Hash::make($data['password']),

            'status_id' => $data['status_id']
        ]);

        return response()->json('Usuario añadido con exito!', 200);
    }


    public function update(Request $request, $id)
    {

        $data = $request->input('user');

        $user = User::find($id);

        $user->name = $data['name'];

        $user->email = $data['email'];

        if (isset($data['password'])) { $user->password = Hash::make($data['password']); }

        $user->status_id = $data['status_id'];

        $user->save();

        return response()->json('Datos actualizados con exito!', 200);
    }


    public function destroy($id)
    {

        User::destroy($id);

        return response()->json('Datos eliminados con exito!', 200);

        /*   if ($client->dependencies()) {

               Client::destroy($id);

               ClientAddressInvoice::where('client_id', $id)->delete();

               ClientAddressShipping::where('client_id', $id)->delete();

               return response()->json('Datos eliminados con exito!', 200);

           } else {

               return response()->json('No se puede eliminar esta en uso!', 200);
           } */

    }
}
