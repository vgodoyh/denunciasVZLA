<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Emisor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "emisor";
    
    protected $fillable = ['name', 'tipoemisor_id','activo'];

    //Relación (MUCHOS) tipoemisor-emisor 
    public function tipoemisor(){
        return $this->belongsTo(TipoEmisor::class, 'tipoemisor_id');
    }

    //Relacion (UNO) emisor_redsocial
    public function emisor_red_social(){
        return $this->hasMany(EmisorRedSocial::class);
    }

    //Relacion (UNO) denuncia
    public function denuncia(){
        return $this->hasMany(Denuncia::class);
    }

    /** FUNCTION QUE VERIFICA SI EXISTE UN EMISOR */
    public static function existeEmisor(string $name, int $tipoEmisorId): bool
    {
        return Emisor::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
                    ->where('tipoemisor_id', $tipoEmisorId)
                    ->exists();
    }
}
