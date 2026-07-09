<?php

namespace App\Http\Controllers;

use App\Models\TipoRedSocial;
use Illuminate\Http\Request;

class TipoRedSocialController extends Controller
{
    public function index()
    {
        $tiposRedSocial = TipoRedSocial::orderBy('id', 'desc')->get();

        return view('tipo_red_social.index', compact('tiposRedSocial'));
    }

    public function create()
    {
        return view('tipo_red_social.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tipo_red_social,name',
        ]);

        TipoRedSocial::create([
            'name' => $request->name,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('tipo_red_social.index')->with('guardar', 'ok-guardar');
    }

    public function edit(TipoRedSocial $tipo_red_social)
    {
        return view('tipo_red_social.edit', compact('tipo_red_social'));
    }

    public function update(Request $request, TipoRedSocial $tipo_red_social)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tipo_red_social,name,' . $tipo_red_social->id,
        ]);

        $tipo_red_social->update([
            'name' => $request->name,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('tipo_red_social.index')->with('modificar', 'ok-modificar');
    }

    public function destroy(TipoRedSocial $tipo_red_social)
    {
        if ($tipo_red_social->emisor_red_social()->exists()) {
            return redirect()->route('tipo_red_social.index')->with('eliminar', 'no-eliminar');
        }

        $tipo_red_social->delete();

        return redirect()->route('tipo_red_social.index')->with('eliminar', 'ok-eliminar');
    }

    public function papelera()
    {
        $tiposRedSocial = TipoRedSocial::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('tipo_red_social.papelera', compact('tiposRedSocial'));
    }

    public function restore($id)
    {
        $tipoRedSocial = TipoRedSocial::onlyTrashed()->findOrFail($id);
        $tipoRedSocial->restore();

        return redirect()->route('tipo_red_social.papelera')->with('restaurar', 'ok-restaurar');
    }

    public function restoreAll()
    {
        TipoRedSocial::onlyTrashed()->restore();

        return redirect()->route('tipo_red_social.papelera')->with('restaurar', 'ok-restaurar-todo');
    }
}