<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoRedSocial extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = "tipo_red_social";
    
    protected $fillable = ['name','activo'];

    //Relacion (UNO) EmisorRedSocial-RedSocial
    public function emisor_red_social(){
        return $this->hasMany( EmisorRedSocial::class);
    }

}
