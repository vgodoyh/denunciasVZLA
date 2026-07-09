<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmisorRedSocial extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "emisor_red_social";
    
    protected $fillable = ['emisor_id','tiporedsocial_id','name','activo'];

    protected $dates = ['deleted_at'];

    //Relación (MUCHOS) redsocial-emisor_redsocial
    public function tipo_red_social(){
        return $this->belongsTo(TipoRedSocial::class,'tiporedsocial_id');
    }

    //Relación (MUCHOS) emisor-emisor_redsocial
    public function emisor(){
        return $this->belongsTo(Emisor::class,'emisor_id');
    }

    //Relacion (UNO) denuncia-emisor_redsocial
    public function denuncia()
    {
        return $this->hasMany(Denuncia::class, 'emisorredsocial_id');
    }
}
