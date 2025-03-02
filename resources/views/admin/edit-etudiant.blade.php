<!DOCTYPE html>
<html>
<head>
    <title>Modifier les informations de l'étudiant</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header with navigation -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Modifier les informations de l'étudiant
                </h1>
                <a href="{{ route('admin.etudiantDetails', $etudiant->id) }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Retour
                </a>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Edit Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form action="{{ route('admin.updateEtudiant', $etudiant->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div>
                            <h2 class="text-xl font-semibold mb-4">Informations personnelles</h2>
                            
                            <div class="mb-4">
                                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                                <input type="text" name="nom" id="nom" value="{{ old('nom', $etudiant->nom) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                                <input type="text" name="prenom" id="prenom" value="{{ old('prenom', $etudiant->prenom) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $etudiant->email) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                                <input type="date" name="date_naissance" id="date_naissance" value="{{ old('date_naissance', $etudiant->date_naissance) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="est_actif" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                <select name="est_actif" id="est_actif" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="1" {{ old('est_actif', $etudiant->est_actif) ? 'selected' : '' }}>Actif</option>
                                    <option value="0" {{ !old('est_actif', $etudiant->est_actif) ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Password and Photo -->
                        <div>
                            <h2 class="text-xl font-semibold mb-4">Mot de passe et photo</h2>
                            
                            <div class="mb-4">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                                <input type="password" name="password" id="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Laissez vide pour conserver le mot de passe actuel</p>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="url_photo_profil" class="block text-sm font-medium text-gray-700 mb-1">URL de la photo de profil</label>
                                <input type="text" name="url_photo_profil" id="url_photo_profil" value="{{ old('url_photo_profil', $etudiant->url_photo_profil) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            @if($etudiant->url_photo_profil)
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Photo actuelle:</p>
                                    <img src="{{ $etudiant->url_photo_profil }}" alt="Photo de profil" class="w-32 h-32 object-cover rounded-full">
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4 border-t mt-6">
                        <a href="{{ route('admin.etudiantDetails', $etudiant->id) }}" 
                           class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                            Annuler
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 