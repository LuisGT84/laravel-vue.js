<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model {
  use HasFactory;

  protected $fillable = ['usuario_id','titulo','descripcion','estado','fecha_vencimiento'];
  protected $casts = ['fecha_vencimiento' => 'date'];

  public function usuario() { return $this->belongsTo(Usuario::class); }
}

