<?php

namespace App\Http\Controllers;

use App\Models\TipoDenuncia;
use Illuminate\Http\Request;

class TipoDenunciaController extends Controller
{
    public function index()
    {
        $tiposDenuncia = TipoDenuncia::orderBy('id', 'desc')->get();

        return view('tipo_denuncia.index', compact('tiposDenuncia'));
    }

    public function create()
    {
        return view('tipo_denuncia.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tipo_denuncia,name',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        TipoDenuncia::create([
            'name' => $request->name,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('tipo_denuncia.index')->with('guardar', 'ok-guardar');
    }

    public function edit(TipoDenuncia $tipo_denuncia)
    {
        return view('tipo_denuncia.edit', compact('tipo_denuncia'));
    }

    public function update(Request $request, TipoDenuncia $tipo_denuncia)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tipo_denuncia,name,' . $tipo_denuncia->id,
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $tipo_denuncia->update([
            'name' => $request->name,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('tipo_denuncia.index')->with('modificar', 'ok-modificar');
    }

    public function destroy(TipoDenuncia $tipo_denuncia)
    {
        $tipo_denuncia->delete();

        return redirect()->route('tipo_denuncia.index')->with('eliminar', 'ok-eliminar');
    }

    public function papelera()
    {
        $tiposDenuncia = TipoDenuncia::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('tipo_denuncia.papelera', compact('tiposDenuncia'));
    }

    public function restore($id)
    {
        $tipoDenuncia = TipoDenuncia::onlyTrashed()->findOrFail($id);
        $tipoDenuncia->restore();

        return redirect()->route('tipo_denuncia.papelera')->with('restaurar', 'ok-restaurar');
    }

    public function restoreAll()
    {
        TipoDenuncia::onlyTrashed()->restore();

        return redirect()->route('tipo_denuncia.papelera')->with('restaurar', 'ok-restaurar-todo');
    }
}