<!DOCTYPE html>
<html>
<head>
    <title>Détails de l'étudiant</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Détails de l'étudiant
                </h1>
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Retour
                </a>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="font-semibold">ID:</div>
                    <div>{{ $etudiant->id }}</div>

                    <div class="font-semibold">Nom:</div>
                    <div>{{ $etudiant->nom }}</div>

                    <div class="font-semibold">Prénom:</div>
                    <div>{{ $etudiant->prenom }}</div>

                    <div class="font-semibold">Email:</div>
                    <div>{{ $etudiant->email }}</div>

                    <div class="font-semibold">Date de naissance:</div>
                    <div>{{ $etudiant->date_naissance ?? 'Non renseigné' }}</div>

                    <div class="font-semibold">Statut:</div>
                    <div>{{ $etudiant->est_actif ? 'Actif' : 'Inactif' }}</div>

                    <div class="font-semibold">Validé le:</div>
                    <div>{{ $etudiant->valide_le ?? 'Non validé' }}</div>
                </div>

                @if($etudiant->url_photo_profil)
                    <div class="mt-4">
                        <div class="font-semibold mb-2">Photo de profil:</div>
                        <img src="{{ $etudiant->url_photo_profil }}"
                             alt="Photo de profil"
                             class="w-32 h-32 object-cover rounded-full">
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
