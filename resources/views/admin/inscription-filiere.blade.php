<!DOCTYPE html>
<html>
<head>
    <title>Inscription à une filière</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-2xl mx-auto">
            <!-- Header with navigation -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Inscription à une filière
                </h1>
                <a href="{{ route('admin.etudiantDetails', $etudiant->id) }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Retour
                </a>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Student Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Informations de l'étudiant</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="font-medium">ID:</span>
                        <span>{{ $etudiant->id }}</span>
                    </div>
                    <div>
                        <span class="font-medium">Nom complet:</span>
                        <span>{{ $etudiant->prenom }} {{ $etudiant->nom }}</span>
                    </div>
                    <div>
                        <span class="font-medium">Email:</span>
                        <span>{{ $etudiant->email }}</span>
                    </div>
                    <div>
                        <span class="font-medium">Statut:</span>
                        <span class="{{ $etudiant->est_actif ? 'text-green-600' : 'text-red-600' }}">
                            {{ $etudiant->est_actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Enrollment Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Formulaire d'inscription</h2>
                
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <p class="text-sm text-blue-800">
                        <span class="font-medium">Note:</span> Cette inscription enregistre l'étudiant dans la filière.
                        Les paiements mensuels devront être ajoutés séparément dans la page de détails de l'étudiant.
                    </p>
                </div>
                
                <form action="{{ route('admin.storeInscriptionFiliere', $etudiant->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="etudiant_id" class="block text-sm font-medium text-gray-700">ID Étudiant</label>
                        <input type="text" id="etudiant_id" name="etudiant_id" value="{{ $etudiant->id }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm" readonly>
                    </div>
                    
                    <div class="mb-4">
                        <label for="filiere_id" class="block text-sm font-medium text-gray-700">Filière</label>
                        <select id="filiere_id" name="filiere_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Sélectionner une filière</option>
                            @foreach($filieres as $filiere)
                                <option value="{{ $filiere->id }}" {{ old('filiere_id') == $filiere->id ? 'selected' : '' }}>
                                    {{ $filiere->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('filiere_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="date_inscription" class="block text-sm font-medium text-gray-700">Date d'inscription</label>
                        <input type="date" id="date_inscription" name="date_inscription" value="{{ old('date_inscription', date('Y-m-d')) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        @error('date_inscription')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="mb-6">
                        <label for="date_completion" class="block text-sm font-medium text-gray-700">Date de fin (optionnelle)</label>
                        <input type="date" id="date_completion" name="date_completion" value="{{ old('date_completion') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('date_completion')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Inscrire l'étudiant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 