@extends('layouts.dashboard')

@section('title', 'Scanner un Pass')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Scanner un QR Code</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Utilisez votre caméra pour scanner le QR Code du pass</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <!-- Zone de scan -->
                <div class="mb-8">
                    <div class="relative">
                        <div id="reader" class="w-full rounded-lg overflow-hidden" style="min-height: 350px;"></div>
                        <div class="absolute inset-0 pointer-events-none border-2 border-blue-500 rounded-lg opacity-50"></div>
                        <div class="absolute bottom-4 left-0 right-0 text-center">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-black/60 backdrop-blur-sm text-white text-xs rounded-full">
                                <svg class="w-3 h-3 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Caméra active
                            </span>
                        </div>
                    </div>
                    <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-3">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <circle cx="12" cy="13" r="3"/>
                        </svg>
                        Positionnez le QR Code dans le cadre
                    </p>
                </div>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">ou</span>
                    </div>
                </div>

                <!-- Zone de saisie manuelle -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2"/>
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Saisissez l'UUID manuellement
                    </h3>
                    <form id="manual-scan-form" class="space-y-4">
                        @csrf
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                    <line x1="3" y1="9" x2="21" y2="9" stroke="currentColor" stroke-width="2"/>
                                    <line x1="9" y1="21" x2="9" y2="9" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                            <input type="text" id="uuid" name="uuid" 
                                placeholder="ex: abc123-def456-ghi789"
                                class="w-full pl-10 pr-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition placeholder-gray-400">
                        </div>
                        <button type="submit" id="submit-btn"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            Valider le scan
                        </button>
                    </form>
                </div>

                <!-- Résultat du scan -->
                <div id="scan-result" class="mt-6 hidden">
                    <div id="result-content" class="rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrCode;
        let isScanning = true;

        // Configuration CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        const scanUrl = '{{ route("scan.process") }}';

        function startScanner() {
            if (!isScanning) return;
            
            const readerElement = document.getElementById('reader');
            if (!readerElement) return;
            
            html5QrCode = new Html5Qrcode("reader");
            const config = {
                fps: 10,
                qrbox: {
                    width: 280,
                    height: 280
                },
                aspectRatio: 1.0
            };

            const qrCodeSuccessCallback = (decodedText) => {
                if (isScanning && decodedText) {
                    isScanning = false;
                    if (html5QrCode && html5QrCode.isScanning) {
                        html5QrCode.stop();
                    }
                    processScan(decodedText);
                }
            };

            html5QrCode.start({
                facingMode: "environment"
            }, config, qrCodeSuccessCallback).catch(err => {
                console.error("Unable to start scanning:", err);
                showError("Impossible d'accéder à la caméra. Vérifiez les permissions.", false);
                isScanning = true;
            });
        }

        function showError(message, restart = true) {
            const resultDiv = document.getElementById('scan-result');
            const contentDiv = document.getElementById('result-content');
            
            if (!resultDiv || !contentDiv) return;
            
            resultDiv.classList.remove('hidden');
            contentDiv.innerHTML = `
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 p-4 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Erreur</h3>
                            <p class="text-sm">${message}</p>
                        </div>
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                resultDiv.classList.add('hidden');
                if (restart) {
                    isScanning = true;
                    startScanner();
                }
            }, 3000);
        }

        function processScan(uuid) {
            const resultDiv = document.getElementById('scan-result');
            const contentDiv = document.getElementById('result-content');
            
            if (!resultDiv || !contentDiv) return;
            
            resultDiv.classList.remove('hidden');
            contentDiv.innerHTML = `
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 p-4 rounded-lg">
                    <div class="flex items-center justify-center gap-3">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent"></div>
                        <p>Traitement en cours...</p>
                    </div>
                </div>
            `;

            fetch(scanUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ uuid: uuid })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        const percentRemaining = (data.pass.remaining_visits / data.pass.allowed_visits) * 100;
                        
                        contentDiv.innerHTML = `
                            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-5">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-lg text-emerald-800 dark:text-emerald-300 mb-3">✓ ${data.message}</h3>
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wider">Titulaire</p>
                                                <p class="font-medium text-gray-900 dark:text-white">${data.pass.holder_name}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wider">Téléphone</p>
                                                <p class="font-medium text-gray-900 dark:text-white">${data.pass.phone || 'Non renseigné'}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wider">Expiration</p>
                                                <p class="font-medium text-gray-900 dark:text-white">${new Date(data.pass.expiration_date).toLocaleDateString('fr-FR')}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wider">Statut</p>
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">
                                                    ${data.pass.status === 'actif' ? 'Actif' : 'Utilisé'}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="pt-3 border-t border-emerald-200 dark:border-emerald-800">
                                            <div class="flex justify-between text-sm mb-2">
                                                <span class="text-gray-600">Visites restantes</span>
                                                <span class="font-bold">${data.pass.remaining_visits} / ${data.pass.allowed_visits}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-emerald-500 rounded-full h-2 transition-all duration-500" style="width: ${percentRemaining}%"></div>
                                            </div>
                                        </div>
                                        <div class="mt-4 pt-3 border-t border-emerald-200 dark:border-emerald-800">
                                            <span class="text-sm font-medium ${data.pass.remaining_visits > 0 ? 'text-emerald-600' : 'text-red-600'}">
                                                ${data.pass.remaining_visits > 0 ? '✅ Accès autorisé' : '❌ Accès refusé - Plus aucune visite'}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        contentDiv.innerHTML = `
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-5">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/50 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-lg text-red-800 dark:text-red-300 mb-2">✗ ${data.message}</h3>
                                        ${data.pass ? `<p class="text-sm text-gray-600">Titulaire: ${data.pass.holder_name}</p>` : '<p class="text-sm">Pass non trouvé</p>'}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    setTimeout(() => {
                        resultDiv.classList.add('hidden');
                        isScanning = true;
                        startScanner();
                    }, 5000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = `
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-5">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                    <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <div>
                                    <h3 class="font-semibold">Erreur de communication</h3>
                                    <p class="text-sm">Impossible de contacter le serveur. Veuillez réessayer.</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    setTimeout(() => {
                        resultDiv.classList.add('hidden');
                        isScanning = true;
                        startScanner();
                    }, 3000);
                });
        }

        document.getElementById('manual-scan-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const uuidInput = document.getElementById('uuid');
            const uuid = uuidInput.value.trim();
            if (uuid) {
                if (html5QrCode && isScanning) {
                    isScanning = false;
                    if (html5QrCode.isScanning) {
                        html5QrCode.stop();
                    }
                }
                processScan(uuid);
                uuidInput.value = '';
            } else {
                showError('Veuillez saisir un UUID valide', false);
            }
        });

        startScanner();

        window.addEventListener('beforeunload', function() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop();
            }
        });
    </script>
    @endpush
@endsection