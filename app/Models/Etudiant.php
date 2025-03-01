<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $table = 'etudiants';
    public $timestamps = false;
    
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
    
    /**
     * Get the filiere enrollments for the student
     */
    public function filiereEnrollments()
    {
        return $this->hasMany(InscriptionEtudiantFiliere::class, 'etudiant_id', 'id');
    }
    
    /**
     * Get the filieres that the student is enrolled in
     */
    public function filieres()
    {
        return $this->belongsToMany(Filiere::class, 'inscriptions_etudiant_filiere', 'etudiant_id', 'filiere_id')
                    ->withPivot('id', 'statut', 'date_inscription', 'date_completion')
                    ->distinct();
    }
}
