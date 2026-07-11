<?php

namespace App\Http\Controllers;

use App\Models\PalabrasClaves;
use Illuminate\Http\Request;

class PalabrasClavesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', '10');

        $query = PalabrasClaves::when($request->filled('buscar'), function ($q) use ($request) {
                $q->where('palabra', 'like', '%' . $request->buscar . '%');
            })
            ->orderBy('id', 'desc');

        $palabrasClaves = $perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $perPage)->withQueryString();

        return view('palabras_claves.index', compact('palabrasClaves', 'perPage'));
    }

    public function create()
    {
        return view('palabras_claves.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'palabra' => 'required|string|max:255|unique:palabras_claves,palabra',
        ]);

        PalabrasClaves::create([
            'palabra' => $request->palabra,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('palabras_clave.index')->with('guardar', 'ok-guardar');
    }

    public function show(PalabrasClaves $palabras_clave)
    {
        return view('palabras_claves.show', ['palabraClave' => $palabras_clave]);
    }

    public function edit(PalabrasClaves $palabras_clave)
    {
        return view('palabras_claves.edit', ['palabraClave' => $palabras_clave]);
    }

    public function update(Request $request, PalabrasClaves $palabras_clave)
    {
        $request->validate([
            'palabra' => 'required|string|max:255|unique:palabras_claves,palabra,' . $palabras_clave->id,
        ]);

        $palabras_clave->update([
            'palabra' => $request->palabra,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('palabras_clave.index')->with('modificar', 'ok-modificar');
    }

    public function destroy(PalabrasClaves $palabras_clave)
    {
        $palabras_clave->delete();

        return redirect()->route('palabras_clave.index')->with('eliminar', 'ok-eliminar');
    }

    public function papelera()
    {
        $palabrasClaves = PalabrasClaves::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('palabras_claves.papelera', compact('palabrasClaves'));
    }

    public function restore($id)
    {
        $palabraClave = PalabrasClaves::onlyTrashed()->findOrFail($id);
        $palabraClave->restore();

        return redirect()->route('palabras_clave.papelera')->with('restaurar', 'ok-restaurar');
    }

    public function restoreAll()
    {
        PalabrasClaves::onlyTrashed()->restore();

        return redirect()->route('palabras_clave.papelera')->with('restaurar', 'ok-restaurar-todo');
    }
}