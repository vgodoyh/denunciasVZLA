<?php

namespace App\Livewire\Admin;

use App\Models\Denuncia;
use App\Models\Emisor;
use App\Models\Estado;
use App\Models\TipoDenuncia;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $pendientes = Denuncia::where('estatus', 'pendiente')->count();
        $aceptadas = Denuncia::where('estatus', 'aceptada')->count();
        $descartadas = Denuncia::where('estatus', 'descartada')->count();
        $emisoresActivos = Emisor::where('activo', true)->count();

        $porTipo = TipoDenuncia::withCount(['denuncia' => function ($query) {
                $query->where('estatus', 'aceptada');
            }])
            ->having('denuncia_count', '>', 0)
            ->orderByDesc('denuncia_count')
            ->get();

        $porEstado = Estado::withCount('denuncias')
            ->having('denuncias_count', '>', 0)
            ->orderByDesc('denuncias_count')
            ->limit(5)
            ->get();

        $tendencia = Denuncia::selectRaw('YEARWEEK(fecha, 1) as semana, count(*) as total')
            ->groupBy('semana')
            ->orderBy('semana')
            ->limit(8)
            ->get();

        $pendientesRecientes = Denuncia::with('emisor_red_social.emisor')
            ->where('estatus', 'pendiente')
            ->orderBy('fecha', 'desc')
            ->limit(8)
            ->get();

        return view('livewire.admin.dashboard', compact(
            'pendientes', 'aceptadas', 'descartadas', 'emisoresActivos',
            'porTipo', 'porEstado', 'tendencia', 'pendientesRecientes'
        ))->layout('layouts.admin');
    }
}