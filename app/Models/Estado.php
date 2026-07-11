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

    public function denuncias(){
        return $this->belongsToMany(Denuncia::class, 'denuncia_estado', 'estado_id', 'denuncia_id')
                    ->withTimestamps();
    }
    
}
