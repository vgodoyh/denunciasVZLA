<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PalabrasClaves extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "palabras_claves";

    protected $fillable = ['palabra','activo'];

}
