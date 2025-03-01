<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrateur extends Model
{
    protected $table = 'administrateurs';
    public $timestamps = false;
    
    protected $fillable = [
        'email',
        'password', // Changed from mot_de_passe_hash as per the SQL comment
        'prenom',
        'nom',
        'est_actif',
        'est_proprietaire',
    ];
    
    protected $casts = [
        'est_actif' => 'boolean',
        'est_proprietaire' => 'boolean',
        'cree_le' => 'datetime',
    ];
    
    /**
     * Get the activity logs for the admin
     */
    public function activityLogs()
    {
        return $this->hasMany(JournalActiviteAdmin::class, 'admin_id');
    }
    
    /**
     * Get the payments verified by the admin
     */
    public function paiementsVerifies()
    {
        return $this->hasMany(PaiementMensuel::class, 'verifie_par_admin_id');
    }
} 