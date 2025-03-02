<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Etudiant;
use App\Models\InscriptionEtudiantFiliere;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = DB::table('administrateurs')
            ->where('email', $credentials['email'])
            ->where('password', $credentials['password'])  // Changed from mot_de_passe_hash
            ->where('est_actif', true)
            ->first();

        if ($admin) {
            Session::put('admin_id', $admin->id);
            Session::put('admin_name', $admin->prenom . ' ' . $admin->nom);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Ces informations ne correspondent pas à nos enregistrements.',
        ]);
    }

    public function dashboard(Request $request)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('admin.login');
        }
        
        $query = Etudiant::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            
            // Check if search is numeric (potential ID)
            if (is_numeric($search)) {
                // For ID, use exact match only
                $query->where('id', $search);
            } else {
                // For non-numeric search, use LIKE on text fields
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'LIKE', "%{$search}%")
                      ->orWhere('prenom', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }
        }
        
        $etudiants = $query->get();
        
        return view('admin.dashboard', compact('etudiants'));
    }

    public function etudiantDetails($id)
    {
        // Load the student with their unique filieres
        $etudiant = Etudiant::with(['filieres' => function($query) {
            $query->distinct();
        }])->find($id);
        
        if (!$etudiant) {
            return redirect()->route('admin.dashboard')->with('error', 'Étudiant non trouvé');
        }
        
        return view('admin.etudiant-details', compact('etudiant'));
    }
    
    public function updateEtudiantPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);
        
        $etudiant = Etudiant::find($id);
        
        if (!$etudiant) {
            return redirect()->route('admin.dashboard')->with('error', 'Étudiant non trouvé');
        }
        
        $etudiant->mot_de_passe_hash = $request->password; // In a real app, this should be hashed
        $etudiant->save();
        
        return redirect()->route('admin.etudiantDetails', $id)->with('success', 'Mot de passe mis à jour avec succès');
    }
    
    public function getMonthlyPayments($etudiantId, $filiereId, $year)
    {
        $etudiant = Etudiant::find($etudiantId);
        $months = [];
        
        if (!$etudiant) {
            return response()->json(['error' => 'Étudiant non trouvé'], 404);
        }
        
        // Get the enrollment record
        $enrollment = InscriptionEtudiantFiliere::where('etudiant_id', $etudiantId)
            ->where('filiere_id', $filiereId)
            ->first();
            
        if (!$enrollment) {
            return response()->json(['error' => 'Inscription non trouvée'], 404);
        }
        
        // Initialize all months as unpaid
        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = [
                'month' => $month,
                'paid' => false,
                'payment_id' => null,
                'date_paiement' => null,
                'date_echeance' => null
            ];
        }
        
        // Get all payments for this enrollment and year
        $payments = \App\Models\PaiementMensuel::where('inscription_etudiant_filiere_id', $enrollment->id)
            ->whereYear('date_paiement', $year)
            ->get();
        
        // Mark months that have payments as paid
        foreach ($payments as $payment) {
            $month = date('n', strtotime($payment->date_paiement));
            $months[$month] = [
                'month' => $month,
                'paid' => true,
                'payment_id' => $payment->id,
                'date_paiement' => $payment->date_paiement,
                'date_echeance' => $payment->date_echeance
            ];
        }
        
        return response()->json([
            'etudiant_id' => $etudiantId,
            'filiere_id' => $filiereId,
            'year' => $year,
            'months' => array_values($months)
        ]);
    }
    
    public function addMonthlyEnrollment(Request $request)
    {
        $request->validate([
            'etudiant_id' => 'required',
            'filiere_id' => 'required|numeric',
            'year' => 'required|numeric',
            'month' => 'required|numeric|min:1|max:12',
        ]);
        
        $etudiantId = $request->etudiant_id;
        $filiereId = $request->filiere_id;
        $year = $request->year;
        $month = $request->month;
        
        // Get the enrollment record
        $enrollment = InscriptionEtudiantFiliere::where('etudiant_id', $etudiantId)
            ->where('filiere_id', $filiereId)
            ->first();
            
        if (!$enrollment) {
            return response()->json(['error' => 'Inscription non trouvée'], 404);
        }
        
        // Check if payment already exists for this month
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate)); // Last day of the month
        
        $existingPayment = \App\Models\PaiementMensuel::where('inscription_etudiant_filiere_id', $enrollment->id)
            ->whereYear('date_paiement', $year)
            ->whereMonth('date_paiement', $month)
            ->first();
            
        if ($existingPayment) {
            return response()->json(['error' => 'Paiement déjà existant pour ce mois'], 400);
        }
        
        // Create new payment
        $payment = new \App\Models\PaiementMensuel();
        $payment->inscription_etudiant_filiere_id = $enrollment->id;
        $payment->date_paiement = $startDate;
        $payment->date_echeance = $endDate;
        $payment->statut = 'payé';
        $payment->verifie_par_admin_id = Session::get('admin_id');
        $payment->save();
        
        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }
    
    public function deleteMonthlyEnrollment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|numeric',
        ]);
        
        $paymentId = $request->payment_id;
        
        $payment = \App\Models\PaiementMensuel::find($paymentId);
        
        if (!$payment) {
            return response()->json(['error' => 'Paiement non trouvé'], 404);
        }
        
        $payment->delete();
        
        return response()->json([
            'success' => true
        ]);
    }

    public function logout()
    {
        Session::forget(['admin_id', 'admin_name']);
        return redirect()->route('admin.login');
    }

    public function showInscriptionFiliereForm($id)
    {
        $etudiant = Etudiant::find($id);
        
        if (!$etudiant) {
            return redirect()->route('admin.dashboard')->with('error', 'Étudiant non trouvé');
        }
        
        $filieres = \App\Models\Filiere::all();
        
        return view('admin.inscription-filiere', compact('etudiant', 'filieres'));
    }
    
    public function storeInscriptionFiliere(Request $request, $id)
    {
        $request->validate([
            'filiere_id' => 'required|exists:filieres,id',
            'date_inscription' => 'required|date',
            'date_completion' => 'nullable|date|after_or_equal:date_inscription',
        ]);
        
        $etudiant = Etudiant::find($id);
        
        if (!$etudiant) {
            return redirect()->route('admin.dashboard')->with('error', 'Étudiant non trouvé');
        }
        
        // Check if student is already enrolled in this filiere
        $existingEnrollment = InscriptionEtudiantFiliere::where('etudiant_id', $id)
            ->where('filiere_id', $request->filiere_id)
            ->where('statut', 'actif')
            ->first();
            
        if ($existingEnrollment) {
            return redirect()->route('admin.inscriptionFiliere', $id)
                ->with('error', 'L\'étudiant est déjà inscrit à cette filière')
                ->withInput();
        }
        
        // Create new enrollment
        $enrollment = new InscriptionEtudiantFiliere();
        $enrollment->etudiant_id = $id;
        $enrollment->filiere_id = $request->filiere_id;
        $enrollment->statut = 'actif';
        $enrollment->date_inscription = $request->date_inscription;
        $enrollment->date_completion = $request->date_completion;
        $enrollment->save();
        
        return redirect()->route('admin.etudiantDetails', $id)
            ->with('success', 'Étudiant inscrit à la filière avec succès');
    }
    
    /**
     * Update the status of a student's filiere enrollment
     */
    public function updateFiliereStatus(Request $request)
    {
        $request->validate([
            'etudiant_id' => 'required',
            'filiere_id' => 'required|numeric',
            'status' => 'required|in:actif,terminé,abandonné',
        ]);
        
        // Find the enrollment record
        $enrollment = InscriptionEtudiantFiliere::where('etudiant_id', $request->etudiant_id)
            ->where('filiere_id', $request->filiere_id)
            ->first();
            
        if (!$enrollment) {
            return response()->json(['error' => 'Inscription non trouvée'], 404);
        }
        
        // Update the status
        $enrollment->statut = $request->status;
        
        // If status is "terminé" or "abandonné", set the completion date to now
        if ($request->status === 'terminé' || $request->status === 'abandonné') {
            $enrollment->date_completion = now();
        }
        
        $enrollment->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'enrollment' => $enrollment
        ]);
    }
    
    /**
     * Update student information
     */
    public function updateEtudiant(Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:etudiants,email,'.$id,
            'date_naissance' => 'nullable|date',
            'est_actif' => 'required|boolean',
            'url_photo_profil' => 'nullable|string|max:255',
            'password' => 'nullable|min:6|confirmed',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $etudiant = Etudiant::find($id);
        
        if (!$etudiant) {
            return redirect()->route('admin.dashboard')->with('error', 'Étudiant non trouvé');
        }
        
        // Update student information
        $etudiant->nom = $request->nom;
        $etudiant->prenom = $request->prenom;
        $etudiant->email = $request->email;
        $etudiant->date_naissance = $request->date_naissance;
        $etudiant->est_actif = $request->est_actif;
        $etudiant->url_photo_profil = $request->url_photo_profil;
        
        // Update password if provided
        if ($request->filled('password')) {
            $etudiant->mot_de_passe_hash = $request->password; // In a real app, this should be hashed
        }
        
        $etudiant->save();
        
        return redirect()->route('admin.etudiantDetails', $id)
            ->with('success', 'Informations de l\'étudiant mises à jour avec succès');
    }
    
    /**
     * Show the form for editing student information
     */
    public function editEtudiant($id)
    {
        $etudiant = Etudiant::find($id);
        
        if (!$etudiant) {
            return redirect()->route('admin.dashboard')->with('error', 'Étudiant non trouvé');
        }
        
        return view('admin.edit-etudiant', compact('etudiant'));
    }
}
