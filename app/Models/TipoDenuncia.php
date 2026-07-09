<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoDenuncia extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "tipo_denuncia";
    
    protected $fillable = ['name','descripcion','activo'];

    //Relacion (UNO) denuncia
    public function denuncia(){
        return $this->hasMany( Denuncia::class);
    }

}
