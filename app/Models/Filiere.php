<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    protected $table = 'filieres';
    public $timestamps = false;
    
    protected $fillable = [
        'nom',
    ];
    
    /**
     * Get the students enrolled in this filiere
     */
    public function etudiants()
    {
        return $this->belongsToMany(Etudiant::class, 'inscriptions_etudiant_filiere', 'filiere_id', 'etudiant_id')
                    ->withPivot('id', 'statut', 'date_inscription', 'date_completion');
    }
    
    /**
     * Get the enrollments for this filiere
     */
    public function inscriptions()
    {
        return $this->hasMany(InscriptionEtudiantFiliere::class);
    }
} 