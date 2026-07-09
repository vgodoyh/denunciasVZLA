<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    public function index()
    {
        $estados = Estado::orderBy('id', 'desc')->get();

        return view('estado.index', compact('estados'));
    }

    public function create()
    {
        return view('estado.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:estado,name',
        ]);

        Estado::create([
            'name' => $request->name,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('estado.index')->with('guardar', 'ok-guardar');
    }

    public function edit(Estado $estado)
    {
        return view('estado.edit', compact('estado'));
    }

    public function update(Request $request, Estado $estado)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:estado,name,' . $estado->id,
        ]);

        $estado->update([
            'name' => $request->name,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('estado.index')->with('modificar', 'ok-modificar');
    }

    public function destroy(Estado $estado)
    {
        $estado->delete();

        return redirect()->route('estado.index')->with('eliminar', 'ok-eliminar');
    }

    public function papelera()
    {
        $estados = Estado::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('estado.papelera', compact('estados'));
    }

    public function restore($id)
    {
        $estado = Estado::onlyTrashed()->findOrFail($id);
        $estado->restore();

        return redirect()->route('estado.papelera')->with('restaurar', 'ok-restaurar');
    }

    public function restoreAll()
    {
        Estado::onlyTrashed()->restore();

        return redirect()->route('estado.papelera')->with('restaurar', 'ok-restaurar-todo');
    }
}