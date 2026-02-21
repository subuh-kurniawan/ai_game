<?php
// Baca konfigurasi API Key dari file JSON
$configPath = __DIR__ . "/api.json";

if (!file_exists($configPath)) {
    die("File config.json tidak ditemukan di folder fungsi/");
}

$config = json_decode(file_get_contents($configPath), true);

// Pastikan ada array apiKeys
if (!isset($config['apiKeys']) || !is_array($config['apiKeys']) || count($config['apiKeys']) === 0) {
    die("File config.json harus berisi array 'apiKeys'.");
}

// Pilih API key secara acak (rotasi otomatis)
$apiKey = $config['apiKeys'][array_rand($config['apiKeys'])];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alur Akuntansi Interaktif AI</title>
    <!-- Memuat Tailwind CSS untuk styling yang responsif dan modern -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .journal-entry {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .debit { color: #10b981; } /* Green */
        .credit { color: #f59e0b; } /* Amber */
        .feedback-error {
            border: 2px solid #ef4444; /* Red */
            background-color: #fef2f2;
        }
        .feedback-success {
            border: 2px solid #10b981; /* Green */
            background-color: #ecfdf5;
        }
    </style>
</head>
<body class="p-4 md:p-8 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-4xl bg-white shadow-2xl rounded-xl p-6 md:p-10 border border-gray-100">
        <h1 class="text-3xl font-extrabold text-indigo-700 mb-2">
            AI Jurnal Akuntansi Berkelanjutan 📈
        </h1>
        <p class="text-lg text-gray-500 mb-6">
            Pilih tema bisnis, level kesulitan, dan periode pelaporan. AI akan memberikan skenario, dan tugas Anda adalah mencatat entri jurnal yang benar.
        </p>

        <!-- Area Pengaturan Awal -->
        <div id="setupArea" class="space-y-4 p-6 bg-indigo-50 rounded-lg">
            <h2 class="text-xl font-semibold text-indigo-800">1. Atur Simulasi</h2>
            
            <!-- Pemilihan Level Kesulitan -->
            <div>
                <label for="difficultySelect" class="block text-sm font-medium text-gray-700 mb-2">Pilih Level Kesulitan:</label>
                <select id="difficultySelect" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                    <option value="Dasar">Dasar (Mudah)</option>
                    <option value="Menengah">Menengah</option>
                    <option value="Mahir">Mahir (Sulit)</option>
                </select>
            </div>
            
            <!-- Pemilihan Periode Pencatatan -->
            <div>
                <label for="periodSelect" class="block text-sm font-medium text-gray-700 mb-2">Periode Pelaporan (Mempengaruhi Jenis Transaksi):</label>
                <select id="periodSelect" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                    <option value="Harian">Harian (Transaksi Operasional Rutin)</option>
                    <option value="Bulanan">Bulanan (Berpotensi melibatkan Jurnal Penyesuaian Bulanan)</option>
                    <option value="Triwulanan">Triwulanan (Berpotensi melibatkan Jurnal Penyesuaian Triwulanan)</option>
                    <option value="Tahunan">Tahunan (Berpotensi melibatkan Jurnal Penyesuaian Akhir Tahun)</option>
                </select>
            </div>

            <!-- Pemilihan Tema Bisnis -->
            <div>
                <label for="themeSelect" class="block text-sm font-medium text-gray-700 mb-2">Pilih Tema Bisnis:</label>
                <select id="themeSelect" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                    <option value="Toko Kopi Lokal">Toko Kopi Lokal</option>
                    <option value="Startup Teknologi (SaaS)">Startup Teknologi (SaaS)</option>
                    <option value="Jasa Konsultasi Pemasaran">Jasa Konsultasi Pemasaran</option>
                    <option value="Properti Sewa">Properti Sewa</option>
                    <option value="custom">Tema Kustom...</option>
                </select>
            </div>
            
            <div id="customThemeDiv" class="hidden">
                <label for="customThemeInput" class="block text-sm font-medium text-gray-700 mb-2">Tema Kustom Anda:</label>
                <input type="text" id="customThemeInput" placeholder="Contoh: Bisnis Kue Rumahan" 
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
            </div>
            <button id="startButton"
                class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-300 shadow-md focus:outline-none focus:ring-4 focus:ring-green-300">
                Mulai Simulasi (5 Transaksi)
            </button>
        </div>

        <!-- Area Alur Utama (Tersembunyi Awalnya) -->
        <div id="flowArea" class="hidden mt-8 space-y-6">
            
            <h2 id="stepIndicator" class="text-2xl font-bold text-gray-800 border-b pb-2"></h2>

            <!-- Narasi Skenario -->
            <div class="p-5 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg journal-entry">
                <p class="text-sm font-medium text-yellow-800 mb-2">Skenario Transaksi Saat Ini (Tugas Anda):</p>
                <div id="narrativeOutput" class="text-gray-700 text-base italic min-h-[60px] flex items-center">
                    Menunggu AI membuat skenario...
                </div>
            </div>

            <!-- Form Input Pemain -->
            <div id="playerInputForm" class="p-5 bg-white border border-gray-300 rounded-lg space-y-4">
                <h3 class="text-lg font-semibold text-indigo-700">Catatan Jurnal Anda (Debet = Kredit):</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Akun Debet:</label>
                        <input type="text" id="playerDebet" placeholder="Contoh: Kas" 
                               class="w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Akun Kredit:</label>
                        <input type="text" id="playerKredit" placeholder="Contoh: Pendapatan Jasa" 
                               class="w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jumlah (IDR):</label>
                        <input type="number" id="playerAmount" placeholder="Contoh: 1500000" 
                               class="w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                
                <!-- Feedback Area -->
                <div id="feedbackOutput" class="p-3 rounded-lg hidden"></div>

                <button id="checkButton" disabled
                    class="w-full px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition duration-300 shadow-md focus:outline-none focus:ring-4 focus:ring-indigo-300 disabled:bg-gray-400">
                    Periksa Jurnal
                </button>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="mt-4 text-center hidden">
                <div class="flex justify-center items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="loadingText" class="text-indigo-600 font-medium">Memulai simulasi...</span>
                </div>
            </div>

            <!-- Riwayat Jurnal -->
            <div id="journalHistoryArea" class="mt-8 pt-4 border-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Riwayat Jurnal Tepat (<span id="totalTransactions">0</span> dari 5)</h3>
                <div id="journalHistoryList" class="space-y-4">
                    <!-- Jurnal yang BENAR akan dimasukkan di sini -->
                </div>
            </div>

            <!-- Pesan Selesai -->
            <div id="completionMessage" class="hidden p-6 bg-green-100 border border-green-400 text-green-700 rounded-lg text-center text-xl font-semibold">
                Selamat! Anda telah berhasil mencatat 5 entri jurnal. Anda lulus ujian pencatatan!
            </div>

        </div>

        <!-- Area Pesan Kesalahan -->
        <div id="errorOutput" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg hidden">
            <!-- Pesan kesalahan akan ditampilkan di sini -->
        </div>
    </div>

    <script>
        // --- Konfigurasi Global ---
       const apiKey = "<?php echo $apiKey; ?>";
       
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;
        const MAX_STEPS = 5; 

        // --- State Aplikasi ---
        let state = {
            theme: '',
            difficulty: '',
            reportingPeriod: '', // State baru untuk periode pelaporan
            currentStep: 0,
            currentNarrative: '',
            financialHistory: []
        };

        // --- Elemen DOM ---
        const difficultySelect = document.getElementById('difficultySelect');
        const periodSelect = document.getElementById('periodSelect'); // Elemen baru
        const themeSelect = document.getElementById('themeSelect');
        const customThemeDiv = document.getElementById('customThemeDiv');
        const customThemeInput = document.getElementById('customThemeInput');
        const startButton = document.getElementById('startButton');
        const setupArea = document.getElementById('setupArea');
        const flowArea = document.getElementById('flowArea');
        const stepIndicator = document.getElementById('stepIndicator');
        const narrativeOutput = document.getElementById('narrativeOutput');
        const playerInputForm = document.getElementById('playerInputForm');
        const playerDebet = document.getElementById('playerDebet');
        const playerKredit = document.getElementById('playerKredit');
        const playerAmount = document.getElementById('playerAmount');
        const checkButton = document.getElementById('checkButton');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const loadingText = document.getElementById('loadingText');
        const journalHistoryList = document.getElementById('journalHistoryList');
        const totalTransactions = document.getElementById('totalTransactions');
        const errorOutput = document.getElementById('errorOutput');
        const completionMessage = document.getElementById('completionMessage');
        const feedbackOutput = document.getElementById('feedbackOutput');

        // --- Skema JSON untuk Output Jurnal Terstruktur (Model Answer) ---
        const journalSchema = {
            type: "OBJECT",
            properties: {
                "transaction_name": { "type": "STRING", "description": "Nama singkat transaksi." },
                "account_debet": { "type": "STRING", "description": "Akun yang BENAR yang didebet, misal: 'Kas', 'Biaya Sewa', 'Piutang Usaha'." },
                "account_kredit": { "type": "STRING", "description": "Akun yang BENAR yang dikredit, misal: 'Pendapatan Jasa', 'Kas', 'Utang Usaha'." },
                "currency": { "type": "STRING", "description": "Mata uang, selalu IDR." },
                "amount": { "type": "NUMBER", "description": "Nilai transaksi numerik yang realistis, tanpa format mata uang. Harus bernilai ratusan ribu hingga puluhan juta." },
                "memo_description": { "type": "STRING", "description": "Penjelasan singkat transaksi." }
            },
            required: ["transaction_name", "account_debet", "account_kredit", "currency", "amount", "memo_description"],
            propertyOrdering: ["transaction_name", "account_debet", "account_kredit", "currency", "amount", "memo_description"]
        };
        
        // --- System Prompts ---
        const journalSystemPrompt = (difficulty) => {
            return `Anda adalah Akuntan AI yang ahli dalam pembukuan ganda (double-entry bookkeeping) di Indonesia. Tugas Anda adalah menganalisis skenario transaksi dan menghasilkan Entri Jurnal Umum yang PALING BENAR dan TEPAT dalam format JSON. 
Nilai transaksi harus realistis dan menggunakan IDR. Pastikan entri jurnal yang dihasilkan adalah jawaban model yang sempurna.
Tingkat kesulitan yang diminta: ${difficulty}. Sesuaikan akun dan jumlah transaksi dengan tingkat kesulitan ini.`;
        };

        const narrativeSystemPrompt = (theme, difficulty, reportingPeriod, history) => {
            let historyText = history.map((tx, index) => 
                `Transaksi ${index + 1}: ${tx.transaction_name} (Debet: ${tx.account_debet}, Kredit: ${tx.account_kredit}, Jumlah: ${tx.amount} IDR)`
            ).join('\n');

            let periodInfo = '';
            if (reportingPeriod !== 'Harian') {
                periodInfo = `Saat ini adalah akhir dari periode Pelaporan ${reportingPeriod}. Transaksi mungkin berupa penyesuaian akhir periode.`;
            }

            let instruction = `Anda adalah pencerita AI yang bertugas membuat skenario bisnis yang realistis dan berkelanjutan.
Tema bisnisnya adalah "${theme}". Tingkat kesulitannya adalah "${difficulty}". Periode pelaporan adalah "${reportingPeriod}".
${periodInfo}
Tujuan Anda adalah menciptakan skenario transaksi berikutnya (hanya teks narasi, tidak perlu menyebut akun, nilai, atau Debet/Kredit) yang sesuai dengan tingkat kesulitan, periode, dan logis mengikuti riwayat berikut:

--- RIWAYAT TRANSAKSI (${history.length} dari ${MAX_STEPS}) ---
${historyText || 'Ini adalah transaksi pertama. Mulailah dengan setoran modal pemilik atau pembelian aset awal.'}
---

Buat skenario transaksi berikutnya yang ringkas dan jelas (maksimal 2 kalimat) dan pastikan jenis transaksi (misal: penyesuaian, utang/piutang, akrual) sesuai dengan tingkat kesulitan "${difficulty}" dan periode "${reportingPeriod}".`;
            
            return instruction;
        };


        // --- Fungsi Utilitas ---
        function displayError(message) {
            errorOutput.textContent = `Terjadi kesalahan: ${message}. Silakan coba lagi.`;
            errorOutput.classList.remove('hidden');
        }

        function hideError() {
            errorOutput.classList.add('hidden');
            errorOutput.textContent = '';
        }

        function toggleLoading(isLoading, message = 'Memuat...') {
            loadingText.textContent = message;
            if (isLoading) {
                loadingIndicator.classList.remove('hidden');
                checkButton.disabled = true;
                startButton.disabled = true;
            } else {
                loadingIndicator.classList.add('hidden');
                startButton.disabled = false;
            }
        }

        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        function getCurrentDate() {
            const now = new Date();
            return `${String(now.getDate()).padStart(2, '0')}/${String(now.getMonth() + 1).padStart(2, '0')}/${now.getFullYear()}`;
        }

        function clearPlayerInput() {
            playerDebet.value = '';
            playerKredit.value = '';
            playerAmount.value = '';
            feedbackOutput.classList.add('hidden');
            feedbackOutput.className = 'p-3 rounded-lg hidden';
            feedbackOutput.innerHTML = '';
        }
        
        // Fungsi dengan Exponential Backoff
        async function exponentialBackoffFetch(url, options, maxRetries = 3) {
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (response.ok) {
                        return response;
                    }
                    if (response.status === 400 || response.status === 404) {
                        const errorBody = await response.json();
                        throw new Error(`Kesalahan Klien: ${errorBody.error?.message || response.statusText}`);
                    }
                    throw new Error(`API mengembalikan status ${response.status}. Mencoba ulang...`);
                } catch (error) {
                    if (i === maxRetries - 1) {
                        throw new Error(`Gagal setelah ${maxRetries} kali percobaan: ${error.message}`);
                    }
                    const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
        }
        
        // --- Fungsi Logika Alur ---

        // 1. Fetch Narasi Transaksi dari AI
        async function fetchNarrative() {
            toggleLoading(true, `AI sedang membuat skenario ${state.difficulty} (${state.reportingPeriod}) ke-${state.currentStep + 1} dari ${MAX_STEPS}...`);
            checkButton.disabled = true;
            narrativeOutput.textContent = 'Memuat...';
            hideError();
            clearPlayerInput();

            try {
                const userQuery = `Buatkan narasi skenario transaksi bisnis untuk tema ${state.theme}, tingkat kesulitan ${state.difficulty}, dan periode ${state.reportingPeriod}.`;
                const systemInstruction = narrativeSystemPrompt(state.theme, state.difficulty, state.reportingPeriod, state.financialHistory);

                const payload = {
                    contents: [{ parts: [{ text: userQuery }] }],
                    systemInstruction: { parts: [{ text: systemInstruction }] }
                };

                const response = await exponentialBackoffFetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                const narrativeText = result?.candidates?.[0]?.content?.parts?.[0]?.text;

                if (!narrativeText) {
                    throw new Error("Gagal mendapatkan narasi dari AI.");
                }

                state.currentNarrative = narrativeText.trim();
                narrativeOutput.textContent = state.currentNarrative;
                checkButton.disabled = false;

            } catch (error) {
                console.error("Kesalahan Narasi API:", error);
                displayError(`Gagal mendapatkan skenario: ${error.message}. Coba mulai ulang simulasi.`);
            } finally {
                toggleLoading(false);
            }
        }

        // 2. Fetch Jawaban Model dari AI
        async function fetchModelAnswer() {
            const userQuery = `Skenario: "${state.currentNarrative}". Berikan entri jurnal yang paling tepat dan benar.`;
            
            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: journalSystemPrompt(state.difficulty) }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: journalSchema
                }
            };

            const response = await exponentialBackoffFetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            const jsonPart = result?.candidates?.[0]?.content?.parts?.[0]?.text;

            if (!jsonPart) {
                throw new Error("Respons AI tidak mengandung Jurnal JSON yang valid.");
            }
            return JSON.parse(jsonPart);
        }
        
        // 3. Periksa Entri Pemain
        async function checkPlayerEntry() {
            
            // Validasi Input
            const playerDebetVal = playerDebet.value.trim();
            const playerKreditVal = playerKredit.value.trim();
            const playerAmountVal = parseFloat(playerAmount.value);

            if (!playerDebetVal || !playerKreditVal || isNaN(playerAmountVal) || playerAmountVal <= 0) {
                displayError("Harap isi semua kolom Debet, Kredit, dan Jumlah dengan benar.");
                return;
            }
            
            toggleLoading(true, 'AI Guru Akuntansi sedang memeriksa jawaban Anda...');
            hideError();

            try {
                const modelAnswer = await fetchModelAnswer();
                
                let isCorrect = true;
                let feedback = [];
                let debetTip = "Debet adalah akun yang *bertambah* (Aset/Beban) atau *berkurang* (Liabilitas/Modal/Pendapatan).";
                let kreditTip = "Kredit adalah akun yang *berkurang* (Aset/Beban) atau *bertambah* (Liabilitas/Modal/Pendapatan).";
                
                // Normalisasi untuk perbandingan yang fleksibel (memperbolehkan variasi penamaan)
                const normalize = (str) => str.toLowerCase().replace(/[^a-z0-9]/g, '').replace('usaha', '').replace('biaya', '').replace('beban', '');

                const modelDebet = normalize(modelAnswer.account_debet);
                const modelKredit = normalize(modelAnswer.account_kredit);
                const playerDebetNorm = normalize(playerDebetVal);
                const playerKreditNorm = normalize(playerKreditVal);
                const amountDiff = Math.abs(playerAmountVal - modelAnswer.amount);

                // Check Amount
                if (amountDiff > 1) { // Toleransi perbedaan kecil
                    isCorrect = false;
                    feedback.push(`❌ Jumlah salah. Nilai transaksi yang benar adalah ${formatRupiah(modelAnswer.amount)}.`);
                }
                
                // Check Debet Account (perbandingan yang lebih longgar)
                if (modelDebet !== playerDebetNorm && !modelDebet.includes(playerDebetNorm) && playerDebetNorm.length > 2) {
                    isCorrect = false;
                    feedback.push(`❌ Akun Debet Anda (${playerDebetVal}) salah atau kurang spesifik. Ingat: ${debetTip}`);
                }
                
                // Check Kredit Account (perbandingan yang lebih longgar)
                if (modelKredit !== playerKreditNorm && !modelKredit.includes(playerKreditNorm) && playerKreditNorm.length > 2) {
                    isCorrect = false;
                    feedback.push(`❌ Akun Kredit Anda (${playerKreditVal}) salah atau kurang spesifik. Ingat: ${kreditTip}`);
                }
                
                // Check Swapped (jika terbalik tapi benar)
                if (!isCorrect && modelDebet === playerKreditNorm && modelKredit === playerDebetNorm) {
                    feedback = [`⚠️ Akun Debet dan Kredit Anda terbalik! Akunnya sudah mengarah ke yang benar, tetapi penempatannya salah. ${debetTip}`];
                    isCorrect = false; 
                }

                // Jika akun dan jumlah (dengan toleransi normalisasi) cocok
                if (!feedback.length && amountDiff <= 1) {
                    isCorrect = true;
                }
                
                // Display Feedback / Record Entry
                if (isCorrect) {
                    feedbackOutput.className = 'p-3 rounded-lg feedback-success';
                    feedbackOutput.innerHTML = `✅ **Jurnal Tepat!** Akun dan Jumlah Benar. Transaksi dicatat.`;
                    
                    state.financialHistory.push(modelAnswer);
                    state.currentStep++;
                    displayJournalEntry(modelAnswer);
                    updateFlowUI();
                } else {
                    feedbackOutput.className = 'p-3 rounded-lg feedback-error';
                    feedbackOutput.innerHTML = `❌ **Jurnal Salah.** Periksa kembali entri Anda. <br> <div class="mt-2 space-y-1 text-sm">${feedback.join('<br>')}</div>`;
                }

            } catch (error) {
                console.error("Kesalahan Saat Memeriksa Jurnal:", error);
                displayError(`Gagal memverifikasi jawaban: ${error.message}.`);
            } finally {
                toggleLoading(false);
                checkButton.disabled = false;
                feedbackOutput.classList.remove('hidden');
            }
        }

        // 4. Tampilkan Jurnal yang Benar ke Riwayat
        function displayJournalEntry(data) {
            const entryDiv = document.createElement('div');
            entryDiv.className = 'p-4 bg-white border border-gray-200 rounded-lg journal-entry';
            
            const html = `
                <div class="font-bold text-sm text-indigo-600 mb-2">${getCurrentDate()} - Transaksi Tepat #${state.currentStep}</div>
                <div class="text-xs text-gray-500 mb-3">${data.memo_description}</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <tr>
                            <td class="w-1/2 text-left">${data.account_debet}</td>
                            <td class="w-1/4 text-right debit font-semibold">${formatRupiah(data.amount)}</td>
                            <td class="w-1/4 text-right"></td>
                        </tr>
                        <tr>
                            <td class="text-left pl-6 italic">(${data.account_kredit})</td>
                            <td class="text-right"></td>
                            <td class="text-right credit font-semibold">${formatRupiah(data.amount)}</td>
                        </tr>
                    </table>
                </div>
            `;
            entryDiv.innerHTML = html;
            journalHistoryList.prepend(entryDiv); // Tambahkan entri terbaru di atas
        }

        // 5. Perbarui UI Alur
        function updateFlowUI() {
            totalTransactions.textContent = state.currentStep;

            if (state.currentStep >= MAX_STEPS) {
                stepIndicator.textContent = `Alur Selesai! (Langkah ${MAX_STEPS} dari ${MAX_STEPS})`;
                narrativeOutput.innerHTML = 'Simulasi telah selesai. Riwayat jurnal Anda ada di bawah.';
                playerInputForm.classList.add('hidden');
                completionMessage.classList.remove('hidden');
            } else {
                stepIndicator.textContent = `Langkah Akuntansi ${state.currentStep + 1} dari ${MAX_STEPS} (Tingkat: ${state.difficulty}, Periode: ${state.reportingPeriod})`;
                // Lanjutkan ke narasi berikutnya
                fetchNarrative();
            }
        }

        // --- Event Listener ---

        // Toggle input kustom
        themeSelect.addEventListener('change', (e) => {
            if (e.target.value === 'custom') {
                customThemeDiv.classList.remove('hidden');
            } else {
                customThemeDiv.classList.add('hidden');
            }
        });

        // Mulai Alur
        startButton.addEventListener('click', () => {
            let theme = themeSelect.value;
            if (theme === 'custom') {
                theme = customThemeInput.value.trim();
            }

            if (!theme) {
                displayError('Harap pilih atau masukkan tema bisnis untuk memulai.');
                return;
            }

            // Set state baru
            state.theme = theme;
            state.difficulty = difficultySelect.value;
            state.reportingPeriod = periodSelect.value; // Ambil nilai periode pelaporan
            state.currentStep = 0;
            state.financialHistory = [];
            
            // Update UI
            setupArea.classList.add('hidden');
            flowArea.classList.remove('hidden');
            journalHistoryList.innerHTML = '';
            playerInputForm.classList.remove('hidden');
            completionMessage.classList.add('hidden');

            updateFlowUI(); // Akan memanggil fetchNarrative pertama
        });

        // Tombol Periksa Jurnal
        checkButton.addEventListener('click', checkPlayerEntry);

        // Inisialisasi: Sembunyikan area alur saat dimuat
        window.onload = () => {
            flowArea.classList.add('hidden');
        };
    </script>
</body>
</html>
