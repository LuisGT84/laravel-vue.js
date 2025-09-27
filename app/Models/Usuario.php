<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Para login con Auth
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Tarea; //  modelo Tarea

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios'; // Nombre exacto de la tabla

    // Campos que se pueden asignar en masa (create/update)
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
    ];

    // Ocultar el password al devolver el modelo en JSON
    protected $hidden = [
        'password',
    ];

    // Relación uno a muchos: un usuario tiene muchas tareas
    public function tareas(): HasMany   // alias importado
    {
        return $this->hasMany(Tarea::class, 'usuario_id');
    }

}

