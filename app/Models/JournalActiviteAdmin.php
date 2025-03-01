<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalActiviteAdmin extends Model
{
    protected $table = 'journal_activites_admin';
    public $timestamps = false;
    
    protected $fillable = [
        'admin_id',
        'type_action',
        'details_action',
        'adresse_ip',
    ];
    
    protected $casts = [
        'cree_le' => 'datetime',
    ];
    
    /**
     * Get the admin that performed the action
     */
    public function admin()
    {
        return $this->belongsTo(Administrateur::class, 'admin_id');
    }
} 