<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscriptionEtudiantFiliere extends Model
{
    protected $table = 'inscriptions_etudiant_filiere';
    public $timestamps = false;
    
    protected $fillable = [
        'etudiant_id',
        'filiere_id',
        'statut',
        'date_inscription',
        'date_completion',
    ];
    
    protected $casts = [
        'date_inscription' => 'datetime',
        'date_completion' => 'datetime',
    ];
    
    /**
     * Get the student that owns the enrollment
     */
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id', 'id');
    }
    
    /**
     * Get the filiere that the student is enrolled in
     */
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }
    
    /**
     * Get the monthly payments for this enrollment
     */
    public function paiements()
    {
        return $this->hasMany(PaiementMensuel::class, 'inscription_etudiant_filiere_id');
    }
} 