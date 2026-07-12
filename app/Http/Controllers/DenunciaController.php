<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\Estado;
use App\Models\EmisorRedSocial;
use App\Models\PalabrasClaves;
use App\Models\TipoDenuncia;
use Illuminate\Http\Request;

class DenunciaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', '10');
        $estatus = $request->get('estatus', 'pendiente'); // default: pendientes

        $query = Denuncia::with(['emisor_red_social.emisor', 'tipoDenuncia'])
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('titular', 'like', '%' . $request->buscar . '%')
                        ->orWhere('url', 'like', '%' . $request->buscar . '%');
                });
            })
            ->when($estatus !== 'todos', function ($q) use ($estatus) {
                $q->where('estatus', $estatus);
            })
            ->orderBy('id', 'desc');

        $denuncias = $perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $perPage)->withQueryString();

        return view('denuncia.index', compact('denuncias', 'perPage', 'estatus'));
    }
    
    public function create()
    {
        $emisoresRedSocial = EmisorRedSocial::with('emisor')
            ->where('activo', true)
            ->get();

        return view('denuncia.create', compact('emisoresRedSocial'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'url' => 'required|string|max:500',
            'titular' => 'required|string|max:500',
            'contenido' => 'required|string',
            'emisorredsocial_id' => 'required|exists:emisor_red_social,id',
        ]);

        Denuncia::create([
            'fecha' => $request->fecha,
            'url' => $request->url,
            'titular' => $request->titular,
            'contenido' => $request->contenido,
            'emisorredsocial_id' => $request->emisorredsocial_id,
            'estatus' => 'pendiente',
        ]);

        return redirect()->route('denuncia.index')->with('guardar', 'ok-guardar');
    }

    public function show(Denuncia $denuncia)
    {
        $denuncia->load(['emisor_red_social.emisor', 'tipoDenuncia', 'user', 'estados', 'palabrasClaves']);

        return view('denuncia.show', compact('denuncia'));
    }

    public function edit(Denuncia $denuncia)
    {
        $emisoresRedSocial = EmisorRedSocial::with('emisor')->where('activo', true)->get();
        $tiposDenuncia = TipoDenuncia::where('activo', true)->orderBy('name')->get();
        $estados = Estado::where('activo', true)->orderBy('name')->get();
        $palabrasClaves = PalabrasClaves::where('activo', true)->orderBy('palabra')->get();

        $denuncia->load(['estados', 'palabrasClaves']);
        $estadosSeleccionados = $denuncia->estados->pluck('id')->toArray();
        $palabrasSeleccionadas = $denuncia->palabrasClaves->pluck('id')->toArray();

        return view('denuncia.edit', compact(
            'denuncia', 'emisoresRedSocial', 'tiposDenuncia', 'estados', 'palabrasClaves',
            'estadosSeleccionados', 'palabrasSeleccionadas'
        ));
    }

    public function update(Request $request, Denuncia $denuncia)
    {
        $request->validate([
            'fecha' => 'required|date',
            'url' => 'required|string|max:500',
            'titular' => 'required|string|max:500',
            'contenido' => 'required|string',
            'emisorredsocial_id' => 'required|exists:emisor_red_social,id',
            'estatus' => 'required|in:pendiente,aceptada,descartada',
            'observacion' => 'nullable|string|max:1000',
            'tipo_denuncia_id' => 'required_if:estatus,aceptada|nullable|exists:tipo_denuncia,id',
            'estados' => 'nullable|array',
            'estados.*' => 'exists:estado,id',
            'palabras_claves' => 'nullable|array',
            'palabras_claves.*' => 'exists:palabras_claves,id',
        ]);

        $denuncia->update([
            'fecha' => $request->fecha,
            'url' => $request->url,
            'titular' => $request->titular,
            'contenido' => $request->contenido,
            'emisorredsocial_id' => $request->emisorredsocial_id,
            'estatus' => $request->estatus,
            'observacion' => $request->observacion,
            'tipo_denuncia_id' => $request->estatus === 'aceptada' ? $request->tipo_denuncia_id : null,
            'user_id' => auth()->id(),
        ]);

        if ($request->estatus === 'aceptada') {
            $denuncia->estados()->sync($request->input('estados', []));
            $denuncia->palabrasClaves()->sync($request->input('palabras_claves', []));
        } else {
            $denuncia->estados()->sync([]);
            $denuncia->palabrasClaves()->sync([]);
        }

        return redirect()->route('denuncia.index')->with('modificar', 'ok-modificar');
    }

    public function destroy(Denuncia $denuncia)
    {
        $denuncia->delete();

        return redirect()->route('denuncia.index')->with('eliminar', 'ok-eliminar');
    }
}