<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return view('company.comp');
    }

    public function getList(Request $request)
    {
        $data = empty(Company::first()) ? null : Company::all()[0];

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        if ($request->input('company.id') > 0) {
            Company::where('id', $request->input('company.id'))->update($request->input('company'));
        } else {
            Company::create($request->input('company'));
        }

        return response()->json('Datos añadido con exito!', 200);
    }


    public function update(Request $request, $id)
    {

        Company::where('id', $id)->update($request->input('company'));

        return response()->json('Datos actualizados con exito!', 200);
    }


    public function destroy($id)
    {

        Company::destroy($id);

        return response()->json('Datos eliminados con exito!', 200);

    }
}
