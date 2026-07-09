<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoEmisor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "tipo_emisor";
    
    protected $fillable = ['name','activo'];

    //Relacion (UNO) tipoemisor-emisor
    public function emisor(){
        return $this->hasMany(Emisor::class);
    }
}
