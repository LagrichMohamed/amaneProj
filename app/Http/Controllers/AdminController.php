<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Etudiant;

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

    public function dashboard()
    {

        if (!Session::has('admin_id')) {
            return redirect()->route('admin.login');
        }
        $etudiants = Etudiant::all();
        return view('admin.dashboard', compact('etudiants'));
    }
    public function etudiantDetails($id)
    {
        $etudiant = Etudiant::find($id);
        return view('admin.etudiant-details', compact('etudiant')); // Update the view path
    }

    public function logout()
    {
        Session::forget(['admin_id', 'admin_name']);
        return redirect()->route('admin.login');
    }
}
