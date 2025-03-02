<!DOCTYPE html>
<html>
<head>
    <title>Profil de l'étudiant</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header with navigation -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Profil de l'étudiant
                </h1>
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Retour
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Profile Information Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left column: Basic info -->
                    <div>
                        <h2 class="text-xl font-semibold mb-4">Informations personnelles</h2>
                        <div class="space-y-3">
                            <div class="flex">
                                <span class="font-medium w-32">ID:</span>
                                <span>{{ $etudiant->id }}</span>
                            </div>
                            <div class="flex">
                                <span class="font-medium w-32">Nom:</span>
                                <span id="etudiant-nom">{{ $etudiant->nom }}</span>
                            </div>
                            <div class="flex">
                                <span class="font-medium w-32">Prénom:</span>
                                <span id="etudiant-prenom">{{ $etudiant->prenom }}</span>
                            </div>
                            <div class="flex">
                                <span class="font-medium w-32">Email:</span>
                                <span id="etudiant-email">{{ $etudiant->email }}</span>
                            </div>
                            <div class="flex">
                                <span class="font-medium w-32">Date de naissance:</span>
                                <span id="etudiant-date-naissance">{{ $etudiant->date_naissance ?? 'Non renseigné' }}</span>
                            </div>
                            <div class="flex">
                                <span class="font-medium w-32">Statut:</span>
                                <span id="etudiant-status" class="{{ $etudiant->est_actif ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $etudiant->est_actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('admin.inscriptionFiliere', $etudiant->id) }}" 
                                   class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    Inscrire à une filière
                                </a>
                                <a href="{{ route('admin.editEtudiant', $etudiant->id) }}" 
                                   class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Modifier les informations
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right column: Photo and password -->
                    <div>
                        @if($etudiant->url_photo_profil)
                            <div class="mb-4">
                                <img src="{{ $etudiant->url_photo_profil }}"
                                    alt="Photo de profil"
                                    id="profile-image"
                                    class="w-32 h-32 object-cover rounded-full mx-auto">
                            </div>
                        @else
                            <div class="mb-4 w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center mx-auto">
                                <span class="text-gray-500 text-4xl">{{ substr($etudiant->prenom, 0, 1) . substr($etudiant->nom, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Filiere Enrollments -->
            @php
                // Get unique filieres to avoid duplicates
                $uniqueFilieres = $etudiant->filieres->unique('id');
            @endphp
            
            @foreach($uniqueFilieres as $filiere)
                <div class="bg-white rounded-lg shadow-md p-6 mb-6" 
                     x-data="{ 
                        currentYear: new Date().getFullYear(),
                        selectedYear: new Date().getFullYear(),
                        months: [],
                        monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                        loading: false,
                        showConfirmDialog: false,
                        confirmAction: null,
                        confirmMessage: '',
                        confirmData: null,
                        
                        async loadPayments() {
                            this.loading = true;
                            try {
                                const response = await fetch(`{{ url('admin/etudiants/'.$etudiant->id.'/filieres/'.$filiere->id.'/payments') }}/${this.selectedYear}`);
                                const data = await response.json();
                                this.months = data.months;
                            } catch (error) {
                                console.error('Error loading payments:', error);
                                alert('Erreur lors du chargement des paiements');
                            } finally {
                                this.loading = false;
                            }
                        },
                        
                        confirmAddEnrollment(month) {
                            this.confirmMessage = `Confirmer le paiement pour ${this.monthNames[month-1]} ${this.selectedYear}?`;
                            this.confirmData = { month };
                            this.confirmAction = 'add';
                            this.showConfirmDialog = true;
                        },
                        
                        async confirmDeleteEnrollment(month, paymentId) {
                            this.confirmMessage = `Annuler le paiement pour ${this.monthNames[month-1]} ${this.selectedYear}?`;
                            this.confirmData = { month, paymentId };
                            this.confirmAction = 'delete';
                            this.showConfirmDialog = true;
                        },
                        
                        async handleMonthClick(month) {
                            const monthData = this.months.find(m => m.month === month);
                            if (monthData.paid) {
                                this.confirmDeleteEnrollment(month, monthData.payment_id);
                            } else {
                                this.confirmAddEnrollment(month);
                            }
                        },
                        
                        async executeConfirmedAction() {
                            if (this.confirmAction === 'add') {
                                await this.addEnrollment(this.confirmData.month);
                            } else if (this.confirmAction === 'delete') {
                                await this.deleteEnrollment(this.confirmData.paymentId);
                            }
                            this.showConfirmDialog = false;
                        },
                        
                        async addEnrollment(month) {
                            try {
                                const response = await fetch('{{ route('admin.addMonthlyEnrollment') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    },
                                    body: JSON.stringify({
                                        etudiant_id: '{{ $etudiant->id }}',
                                        filiere_id: {{ $filiere->id }},
                                        year: this.selectedYear,
                                        month: month
                                    })
                                });
                                
                                if (!response.ok) {
                                    const errorData = await response.json();
                                    throw new Error(errorData.error || 'Erreur lors de l\'ajout de l\'inscription');
                                }
                                
                                await this.loadPayments();
                            } catch (error) {
                                console.error('Error adding enrollment:', error);
                                alert(error.message || 'Erreur lors de l\'ajout de l\'inscription');
                            }
                        },
                        
                        async deleteEnrollment(paymentId) {
                            try {
                                const response = await fetch('{{ route('admin.deleteMonthlyEnrollment') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    },
                                    body: JSON.stringify({
                                        payment_id: paymentId
                                    })
                                });
                                
                                if (!response.ok) {
                                    const errorData = await response.json();
                                    throw new Error(errorData.error || 'Erreur lors de la suppression de l\'inscription');
                                }
                                
                                await this.loadPayments();
                            } catch (error) {
                                console.error('Error deleting enrollment:', error);
                                alert(error.message || 'Erreur lors de la suppression de l\'inscription');
                            }
                        },
                        
                        getYearOptions() {
                            const currentYear = new Date().getFullYear();
                            const years = [];
                            // Show 5 years in the past and 5 years in the future
                            for (let year = currentYear - 5; year <= currentYear + 5; year++) {
                                years.push(year);
                            }
                            return years;
                        }
                     }"
                     x-init="loadPayments()">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Filière: {{ $filiere->nom }}</h2>
                        <div class="flex items-center">
                            <label for="year-select-{{ $filiere->id }}" class="mr-2 text-sm font-medium text-gray-700">Année:</label>
                            <select id="year-select-{{ $filiere->id }}" 
                                    x-model="selectedYear" 
                                    @change="loadPayments()"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <template x-for="year in getYearOptions()" :key="year">
                                    <option :value="year" x-text="year" :selected="year === currentYear"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <span class="text-sm font-medium text-gray-700">Statut: </span>
                        <select 
                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            x-data="{ 
                                originalStatus: '{{ $filiere->pivot->statut }}',
                                updateStatus(newStatus, filiereId) {
                                    if (newStatus === this.originalStatus) return;
                                    
                                    fetch('{{ route('admin.updateFiliereStatus') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                        },
                                        body: JSON.stringify({
                                            etudiant_id: '{{ $etudiant->id }}',
                                            filiere_id: filiereId,
                                            status: newStatus
                                        })
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Erreur lors de la mise à jour du statut');
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        this.originalStatus = newStatus;
                                        // Show success message
                                        const successDiv = document.createElement('div');
                                        successDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4';
                                        successDiv.innerHTML = '<span class="block sm:inline">Statut mis à jour avec succès</span>';
                                        document.querySelector('.max-w-4xl.mx-auto').insertBefore(successDiv, document.querySelector('.bg-white.rounded-lg.shadow-md.p-6.mb-6'));
                                        
                                        // Remove success message after 3 seconds
                                        setTimeout(() => {
                                            successDiv.remove();
                                        }, 3000);
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert(error.message);
                                    });
                                }
                            }"
                            @change="updateStatus($event.target.value, {{ $filiere->id }})">
                            <option value="actif" {{ $filiere->pivot->statut === 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="terminé" {{ $filiere->pivot->statut === 'terminé' ? 'selected' : '' }}>Terminé</option>
                            <option value="abandonné" {{ $filiere->pivot->statut === 'abandonné' ? 'selected' : '' }}>Abandonné</option>
                        </select>
                    </div>
                    
                    <div class="mb-2">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Note:</span> Chaque mois représente un paiement mensuel. 
                            Cliquez sur un mois pour ajouter ou supprimer un paiement.
                            Les mois en vert sont payés, les mois en rouge sont non payés.
                        </p>
                    </div>

                    <!-- Monthly Payments Grid -->
                    <div x-show="loading" class="flex justify-center my-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>

                    <div x-show="!loading" class="grid grid-cols-4 gap-4">
                        <template x-for="(month, index) in months" :key="index">
                            <div :class="month.paid ? 'bg-green-100 border-green-500 cursor-pointer' : 'bg-red-100 border-red-500 cursor-pointer'" 
                                 class="border-2 rounded-md p-3 text-center hover:opacity-80 transition-opacity"
                                 @click="handleMonthClick(month.month)">
                                <div x-text="monthNames[month.month - 1]" class="font-medium"></div>
                                <div x-show="month.paid" class="text-green-700 text-sm mt-1">Payé</div>
                                <div x-show="!month.paid" class="text-red-700 text-sm mt-1">Non payé</div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Confirmation Dialog -->
                    <div x-cloak x-show="showConfirmDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
                            <h3 class="text-lg font-medium mb-4">Confirmation</h3>
                            <p class="mb-6" x-text="confirmMessage"></p>
                            <div class="flex justify-end space-x-3">
                                <button @click="showConfirmDialog = false" 
                                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                    Annuler
                                </button>
                                <button @click="executeConfirmedAction()" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Confirmer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($uniqueFilieres->isEmpty())
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <p class="text-gray-500 text-center py-4">Cet étudiant n'est inscrit à aucune filière.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
