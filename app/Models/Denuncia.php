<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Denuncia extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "denuncia";

    protected $fillable = [
        'fecha', 'url', 'titular', 'contenido', 'observacion',
        'emisorredsocial_id', 'tipodenuncia_id', 'user_id', 'estatus',
    ];

    public function emisor_red_social(){
        return $this->belongsTo(EmisorRedSocial::class, 'emisorredsocial_id');
    }

    public function tipoDenuncia(){
        return $this->belongsTo(TipoDenuncia::class, 'tipodenuncia_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estados(){
        return $this->belongsToMany(Estado::class, 'denuncia_estado', 'denuncia_id', 'estado_id')
                    ->withTimestamps();
    }

    public function palabrasClaves(){
        return $this->belongsToMany(PalabrasClaves::class, 'denuncia_palabra_clave', 'denuncia_id', 'palabras_claves_id')
                    ->withTimestamps();
    }
}