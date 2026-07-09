<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "estado";
    
    protected $fillable = ['name','activo'];

    //Relacion (UNO) localidad-estado
    public function denuncia_estado(){
        return $this->hasMany(DenunciaEstado::class);
    }
    
}
