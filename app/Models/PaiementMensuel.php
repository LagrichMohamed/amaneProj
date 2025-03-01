<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementMensuel extends Model
{
    protected $table = 'paiements_mensuels';
    public $timestamps = false;
    
    protected $fillable = [
        'inscription_etudiant_filiere_id',
        'date_paiement',
        'date_echeance',
        'statut',
        'verifie_par_admin_id',
    ];
    
    protected $casts = [
        'date_paiement' => 'date',
        'date_echeance' => 'date',
    ];
    
    /**
     * Get the enrollment that owns the payment
     */
    public function inscription()
    {
        return $this->belongsTo(InscriptionEtudiantFiliere::class, 'inscription_etudiant_filiere_id');
    }
    
    /**
     * Get the admin that verified the payment
     */
    public function admin()
    {
        return $this->belongsTo(Administrateur::class, 'verifie_par_admin_id');
    }
} 