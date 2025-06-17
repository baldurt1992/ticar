<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:5120',
        ]);

        $company = \App\Company::first();

        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        if ($company->logo && Storage::disk('public')->exists('logos/' . $company->logo)) {
            Storage::disk('public')->delete('logos/' . $company->logo);
        }

        $file = $request->file('logo');
        $filename = 'logo_' . $company->id . '.' . $file->getClientOriginalExtension();
        $file->storeAs('logos', $filename, 'public');

        $company->logo = $filename;
        $company->save();

        return response()->json([
            'logo_url' => asset('storage/logos/' . $filename)
        ]);
    }

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
