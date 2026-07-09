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

    protected $fillable = ['fecha', 'url','titular','contenido','observacion',
                           'emisor_id','emisorredsocial_id','user_id','estatus'];


    //Relación (MUCHOS) EmisorRedSocial-noticia 
    public function emisor_red_social(){
        return $this->belongsTo(EmisorRedSocial::class,'emisorredsocial_id');
    }

    //Relación (MUCHOS) user
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
