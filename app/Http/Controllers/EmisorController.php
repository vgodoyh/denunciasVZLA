<?php

namespace App\Http\Controllers;

use App\Models\Emisor;
use App\Models\EmisorRedSocial;
use App\Models\TipoEmisor;
use App\Models\TipoRedSocial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmisorController extends Controller
{
    public function index()
    {
        $emisores = Emisor::with('tipoemisor')->orderBy('id', 'desc')->get();

        return view('emisor.index', compact('emisores'));
    }

    public function create()
    {
        $tiposEmisor = TipoEmisor::where('activo', true)->orderBy('name')->get();
        $tiposRedSocial = TipoRedSocial::where('activo', true)->orderBy('name')->get();

        return view('emisor.create', compact('tiposEmisor', 'tiposRedSocial'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tipoemisor_id' => 'required|exists:tipo_emisor,id',
            'redes' => 'nullable|array',
            'redes.*.tiporedsocial_id' => 'required_with:redes|exists:tipo_red_social,id',
            'redes.*.name' => 'required_with:redes|string|max:255',
        ]);

        if (Emisor::existeEmisor($request->name, $request->tipoemisor_id)) {
            return back()->withInput()->withErrors([
                'name' => 'Ya existe un emisor con ese nombre para el tipo de emisor seleccionado.',
            ]);
        }

        DB::transaction(function () use ($request) {
            $emisor = Emisor::create([
                'name' => $request->name,
                'tipoemisor_id' => $request->tipoemisor_id,
                'activo' => $request->has('activo'),
            ]);

            foreach ($request->input('redes', []) as $red) {
                EmisorRedSocial::create([
                    'emisor_id' => $emisor->id,
                    'tiporedsocial_id' => $red['tiporedsocial_id'],
                    'name' => $red['name'],
                    'activo' => true,
                ]);
            }
        });

        return redirect()->route('emisor.index')->with('guardar', 'ok-guardar');
    }

    public function show(Emisor $emisor)
    {
        $emisor->load(['tipoemisor', 'emisor_red_social.tipo_red_social']);

        return view('emisor.show', compact('emisor'));
    }

    public function edit(Emisor $emisor)
    {
        $tiposEmisor = TipoEmisor::where('activo', true)->orderBy('name')->get();
        $tiposRedSocial = TipoRedSocial::where('activo', true)->orderBy('name')->get();
        $emisor->load('emisor_red_social');

        return view('emisor.edit', compact('emisor', 'tiposEmisor', 'tiposRedSocial'));
    }

    public function update(Request $request, Emisor $emisor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tipoemisor_id' => 'required|exists:tipo_emisor,id',
            'redes' => 'nullable|array',
            'redes.*.tiporedsocial_id' => 'required_with:redes|exists:tipo_red_social,id',
            'redes.*.name' => 'required_with:redes|string|max:255',
        ]);

        $duplicado = Emisor::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->name))])
            ->where('tipoemisor_id', $request->tipoemisor_id)
            ->where('id', '!=', $emisor->id)
            ->exists();

        if ($duplicado) {
            return back()->withInput()->withErrors([
                'name' => 'Ya existe un emisor con ese nombre para el tipo de emisor seleccionado.',
            ]);
        }

        DB::transaction(function () use ($request, $emisor) {
            $emisor->update([
                'name' => $request->name,
                'tipoemisor_id' => $request->tipoemisor_id,
                'activo' => $request->has('activo'),
            ]);

            $emisor->emisor_red_social()->delete();

            foreach ($request->input('redes', []) as $red) {
                EmisorRedSocial::create([
                    'emisor_id' => $emisor->id,
                    'tiporedsocial_id' => $red['tiporedsocial_id'],
                    'name' => $red['name'],
                    'activo' => true,
                ]);
            }
        });

        return redirect()->route('emisor.index')->with('modificar', 'ok-modificar');
    }

    public function destroy(Emisor $emisor)
    {
        $emisor->delete();

        return redirect()->route('emisor.index')->with('eliminar', 'ok-eliminar');
    }

    public function papelera()
    {
        $emisores = Emisor::onlyTrashed()->with('tipoemisor')->orderBy('deleted_at', 'desc')->get();

        return view('emisor.papelera', compact('emisores'));
    }

    public function restore($id)
    {
        $emisor = Emisor::onlyTrashed()->findOrFail($id);
        $emisor->restore();

        return redirect()->route('emisor.papelera')->with('restaurar', 'ok-restaurar');
    }

    public function restoreAll()
    {
        Emisor::onlyTrashed()->restore();

        return redirect()->route('emisor.papelera')->with('restaurar', 'ok-restaurar-todo');
    }
}