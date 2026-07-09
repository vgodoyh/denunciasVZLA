<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DenunciaEstado extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "denuncia_estado";
    
    protected $fillable = ['denuncia_id','estado_id','activo'];

    //Relación (MUCHOS) estado
    public function estado(){
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    //Relación (MUCHOS) denuncia 
    public function denuncia(){
        return $this->belongsTo(Denuncia::class, 'denuncia_id' );
    }

}
