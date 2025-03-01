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
        
        // Initialize all months as unpaid
        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = [
                'month' => $month,
                'paid' => false,
                'start_date' => null,
                'end_date' => null
            ];
        }
        
        // Get all enrollments for this student, filiere, and year
        $enrollments = InscriptionEtudiantFiliere::where('etudiant_id', $etudiantId)
            ->where('filiere_id', $filiereId)
            ->whereYear('date_inscription', $year)
            ->get();
        
        // Mark months that have enrollments as paid
        foreach ($enrollments as $enrollment) {
            $month = date('n', strtotime($enrollment->date_inscription));
            $months[$month] = [
                'month' => $month,
                'paid' => true,
                'enrollment_id' => $enrollment->id,
                'start_date' => $enrollment->date_inscription,
                'end_date' => $enrollment->date_completion
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
        
        // Check if enrollment already exists for this month
        $existingEnrollment = InscriptionEtudiantFiliere::where('etudiant_id', $etudiantId)
            ->where('filiere_id', $filiereId)
            ->whereYear('date_inscription', $year)
            ->whereMonth('date_inscription', $month)
            ->first();
            
        if ($existingEnrollment) {
            return response()->json(['error' => 'Inscription déjà existante pour ce mois'], 400);
        }
        
        // Calculate start and end dates for the month
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate)); // Last day of the month
        
        // Create new enrollment
        $enrollment = new InscriptionEtudiantFiliere();
        $enrollment->etudiant_id = $etudiantId;
        $enrollment->filiere_id = $filiereId;
        $enrollment->statut = 'actif';
        $enrollment->date_inscription = $startDate;
        $enrollment->date_completion = $endDate;
        $enrollment->save();
        
        return response()->json([
            'success' => true,
            'enrollment' => $enrollment
        ]);
    }
    
    public function deleteMonthlyEnrollment(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|numeric',
        ]);
        
        $enrollmentId = $request->enrollment_id;
        
        $enrollment = InscriptionEtudiantFiliere::find($enrollmentId);
        
        if (!$enrollment) {
            return response()->json(['error' => 'Inscription non trouvée'], 404);
        }
        
        $enrollment->delete();
        
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
}
