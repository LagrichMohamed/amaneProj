<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $fillable = [
        'id',
        'email',
        'mot_de_passe_hash',
        'prenom',
        'nom',
        'date_naissance',
        'url_photo_profil',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
    ];
}
