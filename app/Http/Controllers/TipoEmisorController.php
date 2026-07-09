<?php

namespace App\Http\Controllers;

use App\Models\TipoEmisor;
use Illuminate\Http\Request;

class TipoEmisorController extends Controller
{
    public function index()
    {
        $tiposEmisor = TipoEmisor::orderBy('id', 'desc')->get();

        return view('tipo_emisor.index', compact('tiposEmisor'));
    }

    public function create()
    {
        return view('tipo_emisor.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tipo_emisor,name',
        ]);

        TipoEmisor::create([
            'name' => $request->name,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('tipo_emisor.index')->with('guardar', 'ok-guardar');
    }

    public function edit(TipoEmisor $tipo_emisor)
    {
        return view('tipo_emisor.edit', compact('tipo_emisor'));
    }

    public function update(Request $request, TipoEmisor $tipo_emisor)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tipo_emisor,name,' . $tipo_emisor->id,
        ]);

        $tipo_emisor->update([
            'name' => $request->name,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('tipo_emisor.index')->with('modificar', 'ok-modificar');
    }

    public function destroy(TipoEmisor $tipo_emisor)
    {
        if ($tipo_emisor->emisor()->exists()) {
            return redirect()->route('tipo_emisor.index')->with('eliminar', 'no-eliminar');
        }

        $tipo_emisor->delete();

        return redirect()->route('tipo_emisor.index')->with('eliminar', 'ok-eliminar');
    }

    public function papelera()
    {
        $tiposEmisor = TipoEmisor::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('tipo_emisor.papelera', compact('tiposEmisor'));
    }

    public function restore($id)
    {
        $tipoEmisor = TipoEmisor::onlyTrashed()->findOrFail($id);
        $tipoEmisor->restore();

        return redirect()->route('tipo_emisor.papelera')->with('restaurar', 'ok-restaurar');
    }

    public function restoreAll()
    {
        TipoEmisor::onlyTrashed()->restore();

        return redirect()->route('tipo_emisor.papelera')->with('restaurar', 'ok-restaurar-todo');
    }
}