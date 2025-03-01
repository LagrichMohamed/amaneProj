<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Administrateur</title>
    <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <script src="https://cdn.tailwindcss.com"></script>
                                </head>

                                <body class="bg-gray-100">
                                    <div class="min-h-screen p-6">
                                        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
                                            <div class="flex justify-between items-center mb-6">
                                                <h1 class="text-2xl font-bold text-gray-800">
                                                    Dashboard Administrateur
                                                </h1>
                                                <form method="POST" action="{{ route('admin.logout') }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                                        Déconnexion
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Search Bar -->
                                            <div class="mb-6">
                                                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex">
                                                    <input type="text" name="search" placeholder="Rechercher par ID, nom, prénom ou email..." 
                                                           class="flex-grow px-4 py-2 border border-gray-300 rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                           value="{{ request('search') }}">
                                                    <button type="submit" 
                                                            class="px-4 py-2 bg-blue-600 text-white rounded-r hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        Rechercher
                                                    </button>
                                                </form>
                                            </div>




                                    <table class="min-w-full table-auto">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    ID</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nom</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Prénom</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Email</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($etudiants as $etudiant)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $etudiant->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $etudiant->nom }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $etudiant->prenom }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $etudiant->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('admin.etudiantDetails', $etudiant->id) }}"
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Détails
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</body>

</html>
