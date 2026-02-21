<?php

include "../admin/fungsi/koneksi.php";
$sql = mysqli_query($koneksi, "SELECT * FROM datasekolah");
$data = mysqli_fetch_assoc($sql);
$sql = mysqli_query($koneksi, "
    SELECT api_key
    FROM api_keys
    WHERE usage_count = (SELECT MIN(usage_count) FROM api_keys)
    ORDER BY RAND()
    LIMIT 1
");

if (!$sql) {
    die("Error fetching API key: " . mysqli_error($koneksi));
}

$dataApiKey = mysqli_fetch_assoc($sql);

if ($dataApiKey) {
    $apiKey = $dataApiKey['api_key'];
} else {
    die("No API keys found in the database.");
}
$models = [];

$sql = "SELECT model_name 
        FROM api_model 
        WHERE is_supported = 1 
        ORDER BY id ASC";

$res = $koneksi->query($sql);

while ($row = $res->fetch_assoc()) {
    $models[] = $row['model_name'];
}

// Fallback jika database kosong atau tidak ada model yang didukung
if (empty($models)) {
    $models[] = "gemini-2.5-flash-preview-09-2025";
}

// Pilih model pertama / default
$model = $models[0];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Cerdas - Game Akuntansi SMK</title>
    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Memuat Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #10B981; /* Emerald 500 */
            --secondary: #1F2937; /* Gray 800 */
            --accent: #F59E0B; /* Amber 500 */
            --blue: #3B82F6; /* Blue 500 */
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6; /* Gray 100 */
        }
        .container-game {
            max-width: 900px;
        }
        .card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: var(--primary);
            transition: background-color 0.2s;
        }
        .btn-primary:hover:not(:disabled) {
            background-color: #059669; /* Emerald 600 */
        }
        .gemini-chat {
            background-color: #E0F2F1; /* Teal 50 */
            border-left: 4px solid var(--primary);
        }
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
        }
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 50;
        }
        .modal-content {
            animation: fadeIn 0.3s ease-out;
            max-height: 95vh;
            overflow-y: auto;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        #review-modal .spinner {
            border-top-color: var(--blue);
        }
        table {
            border-collapse: collapse;
        }
    </style>
</head>
<body class="p-4 md:p-8 min-h-screen flex items-center justify-center">

    <!-- Scenario Selection Modal/Overlay (Awal) -->
    <div id="scenario-modal" class="fixed inset-0 modal-backdrop flex items-center justify-center p-4">
        <div class="modal-content bg-white p-8 rounded-xl shadow-2xl max-w-md w-full text-center">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-4">Mulai Jurnal Cerdas</h2>
            <p class="text-gray-600 mb-6">Sesuaikan latihan Anda bersama Buku-Pintar. (Batas: 5 Transaksi)</p>
            
            <label for="scenario-select" class="block text-left text-sm font-medium text-gray-700 mb-2">Pilih Periode/Alur Akuntansi:</label>
            <select id="scenario-select" class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                <!-- Tambahan variasi skenario baru -->
                <option value="initial_investment">Pendirian Usaha & Investasi Awal</option>
                <option value="daily_operations">Operasi Bisnis Harian (Jasa)</option>
                <option value="cash_management">Transaksi Kas dan Bank (Fokus pada Aliran Kas)</option>
                <option value="non_cash_assets">Perolehan dan Penyusutan Aset Tetap</option>
                <option value="adjusting_entries">Transaksi Penyesuaian (AJP Sederhana)</option>
                <option value="closing_entries">Jurnal Penutup dan Pemindahan Saldo</option>
            </select>
            
            <!-- Difficulty Selection -->
            <label for="difficulty-select" class="block text-left text-sm font-medium text-gray-700 mb-2">Pilih Tingkat Kesulitan:</label>
            <select id="difficulty-select" class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                <option value="Mudah">Mudah (Dasar, Transaksi Jelas)</option>
                <option value="Sedang" selected>Sedang (Sedikit Kebingungan, Istilah Campuran)</option>
                <option value="Sulit">Sulit (Multi-entri Implisit, Bahasa Kompleks)</option>
            </select>

            <!-- Custom Business Name Input -->
            <label for="business-name-input" class="block text-left text-sm font-medium text-gray-700 mb-2">Nama Bisnis/Usaha (Opsional):</label>
            <input type="text" id="business-name-input" class="w-full p-3 mb-6 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-gray-700" placeholder="Contoh: Bengkel Jaya Abadi">

            <button id="start-game-button" class="bg-blue-600 hover:bg-blue-700 w-full py-3 px-4 rounded-xl text-white font-semibold flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-play mr-2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Mulai Latihan
            </button>
        </div>
    </div>
    
    <!-- Game Review/End Modal -->
    <div id="review-modal" class="fixed inset-0 modal-backdrop flex items-center justify-center p-4 hidden">
        <div class="modal-content bg-white p-8 rounded-xl shadow-2xl max-w-4xl w-full text-center">
            <h2 class="text-4xl font-extrabold text-emerald-600 mb-4 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap mr-2"><path d="M22 10v6"/><path d="M2 10l10-5 10 5-10 5z"/><path d="M6.9 14.5l7.2 3.5"/><path d="M6 10v6"/><path d="M18 10v6"/></svg>
                Sesi Latihan Selesai!
            </h2>
            <p class="text-gray-700 text-lg mb-4">Waktunya melihat hasil akhir Anda.</p>
            
            <!-- Summary Stats -->
            <div class="flex justify-center space-x-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-xl flex-1 border-b-4 border-emerald-500">
                    <p class="text-sm font-medium text-gray-500">SKOR AKHIR</p>
                    <p id="final-score-display" class="text-4xl font-bold text-gray-800 mt-1">0</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl flex-1 border-b-4 border-blue-500">
                    <p class="text-sm font-medium text-gray-500">AKURASI</p>
                    <p id="accuracy-display" class="text-4xl font-bold text-gray-800 mt-1">0/5</p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button id="tab-review" class="py-2 px-4 border-b-2 font-medium text-sm transition duration-150 ease-in-out text-blue-600 border-blue-600 focus:outline-none" onclick="showReviewTab('review')">Review Buku-Pintar</button>
                    <button id="tab-ledger" class="py-2 px-4 border-b-2 font-medium text-sm transition duration-150 ease-in-out text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300 focus:outline-none" onclick="showReviewTab('ledger')">Buku Besar Sesi</button>
                </nav>
            </div>

            <!-- Tab Content: Review -->
            <div id="content-review" class="tab-content">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Review dari Buku-Pintar</h3>
                <div id="review-content" class="text-left bg-blue-50 p-4 rounded-lg text-gray-700 italic min-h-[100px] flex items-center justify-center">
                    <div id="review-loading" class="flex items-center">
                        <div class="spinner w-5 h-5 border-4 border-transparent rounded-full animate-spin border-t-blue-500 mr-2"></div>
                        <span class="text-blue-700 font-medium">Buku-Pintar sedang menyusun review performa Anda...</span>
                    </div>
                </div>
            </div>
            
            <!-- Tab Content: Ledger -->
            <div id="content-ledger" class="tab-content hidden text-left">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Hasil Buku Besar Sesi Latihan</h3>
                <div id="ledger-content" class="text-gray-700">
                    <!-- Ledger content will be injected here -->
                </div>
            </div>

            <button id="restart-game-button" class="bg-blue-600 hover:bg-blue-700 w-full py-3 px-4 rounded-xl text-white font-semibold mt-6">
                Mulai Sesi Baru
            </button>
        </div>
    </div>

    <!-- Game Container -->
    <div id="game-container" class="container-game w-full hidden">
        
        <header class="text-center mb-6">
            <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Jurnal Cerdas </h1>
            <p id="scenario-title" class="text-gray-500 mt-1">Latihan Pembukuan Akuntansi Sederhana</p>
        </header>

        <!-- Status and Score -->
        <div class="flex flex-col md:flex-row justify-between mb-6 space-y-4 md:space-y-0 md:space-x-4">
            <div class="card bg-white p-4 rounded-xl flex-1 text-center border-b-4 border-emerald-500">
                <p class="text-sm font-medium text-gray-500">SKOR ANDA</p>
                <p id="score-display" class="text-3xl font-bold text-emerald-600 mt-1">0</p>
            </div>
            <div class="card bg-white p-4 rounded-xl flex-1 text-center border-b-4 border-amber-500">
                <p class="text-sm font-medium text-gray-500">ALUR SAAT INI</p>
                <p id="current-scenario-display" class="text-lg font-bold text-amber-600 mt-1">...</p>
            </div>
        </div>
        
        <div class="card bg-white p-4 rounded-xl text-center mb-6">
            <p id="transaction-counter" class="text-lg font-semibold text-blue-600">Transaksi 0 dari 5</p>
        </div>

        <!-- Game Master (Gemini) Area -->
        <div id="gemini-output" class="gemini-chat card p-5 rounded-xl mb-6 transition duration-300">
            <h3 class="text-lg font-bold text-gray-800 flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-check mr-2 text-emerald-600"><path d="M8 2.3A4.5 4.5 0 0 0 12 7v14"/><path d="M12 2.3a4.5 4.5 0 0 1 4 4.7v14"/><path d="M10 21.7C8.4 21.4 7 20.3 6 19c-3.1-4-3.1-9.7 0-13.7C7 4.7 8.4 3.6 10 3.3"/><path d="M20 15v6"/><path d="m16 17 2 2 4-4"/></svg>
                Buku-Pintar
            </h3>
            <p id="transaction-text" class="text-gray-700 italic">Selamat datang di Jurnal Cerdas! Tunggu Buku-Pintar memuat transaksi pertama...</p>
            
            <!-- Loading Indicator -->
            <div id="loading-spinner" class="mt-4 hidden flex items-center justify-center">
                <div class="spinner w-6 h-6 border-4 border-transparent rounded-full animate-spin"></div>
                <span class="ml-2 text-gray-600">Buku-Pintar sedang berpikir...</span>
            </div>
        </div>

        <!-- Input Form -->
        <div class="card bg-white p-6 rounded-xl border-t-4 border-blue-500">
            <h4 class="text-xl font-semibold text-gray-800 mb-4">Input Jurnal Entri Anda</h4>
            
            <form id="journal-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Debit Entry -->
                    <div>
                        <label for="debit-account" class="block text-sm font-medium text-gray-700">Akun Debit (Bertambah)</label>
                        <input type="text" id="debit-account" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: Kas" required>
                    </div>
                    <div>
                        <label for="credit-account" class="block text-sm font-medium text-gray-700">Akun Kredit (Berkurang)</label>
                        <input type="text" id="credit-account" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: Utang Usaha" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submit-button" class="btn-primary w-full mt-6 py-3 px-4 rounded-xl text-white font-semibold flex items-center justify-center disabled:opacity-50" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-check mr-2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    Periksa Jawaban
                </button>
            </form>

            <!-- Feedback Modal -->
            <div id="feedback-area" class="mt-4 p-4 rounded-lg hidden" role="alert">
                <p id="feedback-message" class="font-medium"></p>
                <p id="correct-answer" class="text-sm mt-2 hidden"></p>
                <button id="next-transaction-button" class="btn-primary mt-3 py-2 px-4 rounded-lg text-white text-sm hidden" disabled>
                    Lanjut ke Transaksi Berikutnya
                </button>
            </div>
            
            <button id="reset-game-button" class="bg-gray-400 hover:bg-gray-500 w-full mt-4 py-2 px-4 rounded-xl text-white font-semibold text-sm flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rotate-ccw mr-1"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-3.23 0"/><path d="M3.6 5.8 8 2.5v5"/></svg>
                Ganti Alur/Reset Skor
            </button>
        </div>

    </div>

    <script>
        // --- INITIALIZATION ---
        const API_KEY = "<?php echo $apiKey; ?>"; // Kunci API dibiarkan kosong
        const GEMINI_MODEL = "<?php echo $model; ?>";
        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${API_KEY}`;
        
        // Game Limits and Tracking
        const TRANSACTION_LIMIT = 5;
        let currentScenario = null;
        let currentDifficulty = 'Sedang';
        let customBusinessName = '';
        let currentTransaction = null;
        let currentScore = 0;
        let attemptCount = 0;
        let totalTransactionsCompleted = 0;
        let totalCorrectlySolved = 0;
        let transactionHistory = []; // Stores {debit, credit, amount, isSolvedCorrectly, transactionText} for ledger generation

        // Scenario Text Mapping (UPDATED)
        const scenarioMap = {
            initial_investment: "Pendirian Usaha & Investasi Awal",
            daily_operations: "Operasi Bisnis Harian (Jasa)",
            cash_management: "Transaksi Kas dan Bank (Fokus pada Aliran Kas)",
            non_cash_assets: "Perolehan dan Penyusutan Aset Tetap",
            adjusting_entries: "Transaksi Penyesuaian (AJP Sederhana)",
            closing_entries: "Jurnal Penutup dan Pemindahan Saldo"
        };

        // UI Elements
        const scenarioModal = document.getElementById('scenario-modal');
        const reviewModal = document.getElementById('review-modal');
        const startGameButton = document.getElementById('start-game-button');
        const scenarioSelect = document.getElementById('scenario-select');
        const difficultySelect = document.getElementById('difficulty-select');
        const businessNameInput = document.getElementById('business-name-input');
        const gameContainer = document.getElementById('game-container');
        const currentScenarioDisplay = document.getElementById('current-scenario-display');
        const transactionTextEl = document.getElementById('transaction-text');
        const debitAccountInput = document.getElementById('debit-account');
        const creditAccountInput = document.getElementById('credit-account');
        const submitButton = document.getElementById('submit-button');
        const loadingSpinner = document.getElementById('loading-spinner');
        const feedbackArea = document.getElementById('feedback-area');
        const feedbackMessageEl = document.getElementById('feedback-message');
        const nextTransactionButton = document.getElementById('next-transaction-button');
        const scoreDisplayEl = document.getElementById('score-display');
        const correctAnswerEl = document.getElementById('correct-answer');
        const resetGameButton = document.getElementById('reset-game-button');
        const scenarioTitle = document.getElementById('scenario-title');
        const transactionCounterEl = document.getElementById('transaction-counter');

        // Review Modal Elements
        const finalScoreDisplay = document.getElementById('final-score-display');
        const accuracyDisplay = document.getElementById('accuracy-display');
        const reviewContentEl = document.getElementById('review-content');
        const reviewLoadingEl = document.getElementById('review-loading');
        const ledgerContentEl = document.getElementById('ledger-content');
        const restartGameButton = document.getElementById('restart-game-button');
        const tabReview = document.getElementById('tab-review');
        const tabLedger = document.getElementById('tab-ledger');
        const contentReview = document.getElementById('content-review');
        const contentLedger = document.getElementById('content-ledger');

        // --- UTILITY FUNCTIONS ---
        
        /**
         * Memformat nilai Rupiah.
         * @param {number} value
         * @param {boolean} includeSymbol - Apakah menyertakan simbol Rp. Default true.
         * @returns {string}
         */
        function formatRupiah(value, includeSymbol = true) {
            const options = { minimumFractionDigits: 0 };
            if (includeSymbol) {
                options.style = 'currency';
                options.currency = 'IDR';
            }
            return new Intl.NumberFormat('id-ID', options).format(value);
        }

        // --- SCORE AND STATE MANAGEMENT (Local Only) ---

        function updateScore(points) {
            currentScore = Math.max(0, currentScore + points);
            scoreDisplayEl.textContent = currentScore;
        }
        
        function showScenarioSelection() {
            gameContainer.classList.add('hidden');
            reviewModal.classList.add('hidden');
            scenarioModal.classList.remove('hidden');
            transactionCounterEl.textContent = `Transaksi 0 dari ${TRANSACTION_LIMIT}`;
            transactionHistory = []; // Clear history on start
        }

        function startGame(scenarioKey) {
            currentScenario = scenarioKey;
            currentScore = 0;
            totalTransactionsCompleted = 0;
            totalCorrectlySolved = 0;
            transactionHistory = []; // Reset history
            scoreDisplayEl.textContent = currentScore;
            
            currentDifficulty = difficultySelect.value;
            customBusinessName = businessNameInput.value.trim() || "Usaha Jasa Anda";

            scenarioModal.classList.add('hidden');
            gameContainer.classList.remove('hidden');
            
            currentScenarioDisplay.textContent = `${scenarioMap[scenarioKey]} (${currentDifficulty})`;
            scenarioTitle.textContent = `${customBusinessName} - ${scenarioMap[scenarioKey]}`;
            
            fetchNewTransaction();
        }

        // --- GAME END/REVIEW LOGIC ---

        function showReviewTab(tabName) {
            // Reset tabs
            tabReview.classList.remove('text-blue-600', 'border-blue-600');
            tabReview.classList.add('text-gray-500', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
            tabLedger.classList.remove('text-blue-600', 'border-blue-600');
            tabLedger.classList.add('text-gray-500', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');

            // Hide all content
            contentReview.classList.add('hidden');
            contentLedger.classList.add('hidden');

            if (tabName === 'review') {
                tabReview.classList.add('text-blue-600', 'border-blue-600');
                tabReview.classList.remove('text-gray-500', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
                contentReview.classList.remove('hidden');
            } else if (tabName === 'ledger') {
                tabLedger.classList.add('text-blue-600', 'border-blue-600');
                tabLedger.classList.remove('text-gray-500', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
                contentLedger.classList.remove('hidden');
                ledgerContentEl.innerHTML = generateGeneralLedger(); // Generate ledger when tab is clicked
            }
        }

        function showReviewModal() {
            gameContainer.classList.add('hidden');
            reviewModal.classList.remove('hidden');
            
            const accuracyCount = `${totalCorrectlySolved}/${TRANSACTION_LIMIT}`;
            
            finalScoreDisplay.textContent = currentScore;
            accuracyDisplay.textContent = accuracyCount;
            
            // Set default tab to Review
            showReviewTab('review');
            
            // Fetch review from Gemini
            reviewContentEl.innerHTML = '';
            reviewLoadingEl.classList.remove('hidden');
            fetchReviewFromGemini();
        }
        
        async function fetchReviewFromGemini() {
            reviewLoadingEl.classList.remove('hidden');
            
            const performanceSummary = `
                Sesi Latihan Selesai.
                - Bisnis: ${customBusinessName}
                - Alur: ${scenarioMap[currentScenario]}
                - Kesulitan: ${currentDifficulty}
                - Skor Akhir: ${currentScore}
                - Transaksi Selesai: ${TRANSACTION_LIMIT}
                - Transaksi Benar (Percobaan 1/2): ${totalCorrectlySolved}
                - Akurasi: ${Math.round((totalCorrectlySolved / TRANSACTION_LIMIT) * 100)}%
            `;
            
            const systemPrompt = "Anda adalah 'Buku-Pintar', Master Game Akuntansi. Tugas Anda adalah memberikan review performa akhir kepada siswa SMK. Analisis skor dan akurasi yang diberikan dan berikan pujian (jika bagus) atau saran spesifik (jika kurang) dalam 3-4 paragraf singkat. Akhiri dengan pesan motivasi.";
            
            const userQuery = `Berdasarkan ringkasan performa berikut, berikan review yang detail dan personal:\n\n${performanceSummary}`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] }
            };

            try {
                const response = await withBackoff(() => fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                }));
                
                const result = await response.json();
                const reviewText = result.candidates?.[0]?.content?.parts?.[0]?.text;

                if (!reviewText) throw new Error("API did not return review content.");

                // Convert Markdown to HTML for display
                const formattedReview = reviewText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                reviewContentEl.innerHTML = formattedReview;

            } catch (error) {
                console.error("Error fetching review:", error);
                reviewContentEl.innerHTML = `<p class="text-red-600">Terjadi kesalahan saat memuat review: ${error.message}. Namun, selamat atas skor ${currentScore} Anda!</p>`;
            } finally {
                reviewLoadingEl.classList.add('hidden');
            }
        }
        
        /**
         * Mengkompilasi riwayat transaksi menjadi Buku Besar.
         * @returns {string} HTML yang diformat sebagai Buku Besar.
         */
        function generateGeneralLedger() {
            const ledger = {};

            // 1. Group transactions by account
            transactionHistory.forEach((entry, index) => {
                const transactionNumber = index + 1;
                const debitAccount = entry.debit;
                const creditAccount = entry.credit;
                const amount = entry.amount;
                const description = `Jurnal Transaksi #${transactionNumber}`;

                // Process Debit
                if (!ledger[debitAccount]) ledger[debitAccount] = { debits: [], credits: [] };
                ledger[debitAccount].debits.push({
                    date: `T${transactionNumber}`,
                    description: description,
                    amount: amount
                });

                // Process Credit
                if (!ledger[creditAccount]) ledger[creditAccount] = { debits: [], credits: [] };
                ledger[creditAccount].credits.push({
                    date: `T${transactionNumber}`,
                    description: description,
                    amount: amount
                });
            });

            // 2. Format HTML output
            let htmlOutput = '<div class="space-y-6">';

            if (Object.keys(ledger).length === 0) {
                return '<p class="text-center text-red-500 font-medium">Tidak ada transaksi yang berhasil dicatat untuk membuat Buku Besar.</p>';
            }

            for (const account in ledger) {
                const data = ledger[account];
                
                let totalDebit = data.debits.reduce((sum, item) => sum + item.amount, 0);
                let totalCredit = data.credits.reduce((sum, item) => sum + item.amount, 0);
                let balance = totalDebit - totalCredit;

                const balanceSide = balance >= 0 ? 'Debet' : 'Kredit';
                balance = Math.abs(balance);

                htmlOutput += `
                    <div class="border border-gray-300 rounded-xl overflow-hidden shadow-md">
                        <h4 class="bg-blue-100 p-3 text-lg font-bold text-gray-800 flex justify-between items-center">
                            Buku Besar: ${account}
                            <span class="text-sm font-semibold text-blue-700">Saldo Akhir: ${formatRupiah(balance)} (${balanceSide})</span>
                        </h4>
                        <div class="p-3 overflow-x-auto">
                            <table class="min-w-full text-sm divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tgl/No.</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Debet (Rp)</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Kredit (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                `;
                
                // Combine all entries and sort by transaction number (T1, T2, etc.)
                const combinedEntries = data.debits.map(d => ({ ...d, type: 'Debit' }))
                    .concat(data.credits.map(c => ({ ...c, type: 'Credit' })))
                    .sort((a, b) => {
                        const numA = parseInt(a.date.substring(1));
                        const numB = parseInt(b.date.substring(1));
                        return numA - numB;
                    });
                    
                combinedEntries.forEach(item => {
                    const isDebit = item.type === 'Debit';
                    htmlOutput += `
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap">${item.date}</td>
                            <td class="px-3 py-2">${item.description}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-green-700">${isDebit ? formatRupiah(item.amount, false) : '-'}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-red-700">${!isDebit ? formatRupiah(item.amount, false) : '-'}</td>
                        </tr>
                    `;
                });
                
                // Add final balance row
                htmlOutput += `
                                </tbody>
                                <tfoot class="bg-gray-200 font-bold">
                                    <tr>
                                        <td colspan="2" class="px-3 py-2 text-right">TOTAL TRANSAKSI</td>
                                        <td class="px-3 py-2 text-right">${formatRupiah(totalDebit, false)}</td>
                                        <td class="px-3 py-2 text-right">${formatRupiah(totalCredit, false)}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="px-3 py-2 text-right text-base">SALDO AKHIR (${balanceSide})</td>
                                        <td class="px-3 py-2 text-right text-lg text-blue-800">${formatRupiah(balance)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `;
            }

            htmlOutput += '</div>';
            return htmlOutput;
        }


        // --- GEMINI API INTERACTION ---

        function toggleLoading(show) {
            if (show) {
                loadingSpinner.classList.remove('hidden');
                submitButton.disabled = true;
                nextTransactionButton.disabled = true;
                transactionTextEl.textContent = "Buku-Pintar sedang menyiapkan skenario...";
            } else {
                loadingSpinner.classList.add('hidden');
                submitButton.disabled = false;
            }
        }

        async function withBackoff(fn, retries = 5) {
            try {
                return await fn();
            } catch (error) {
                if (retries > 0) {
                    const delay = Math.pow(2, 5 - retries) * 1000 + Math.random() * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                    return withBackoff(fn, retries - 1);
                }
                throw error;
            }
        }

        async function fetchNewTransaction() {
            if (!currentScenario) {
                showScenarioSelection();
                return;
            }
            
            if (totalTransactionsCompleted >= TRANSACTION_LIMIT) {
                showReviewModal();
                return;
            }

            toggleLoading(true);
            feedbackArea.classList.add('hidden');
            correctAnswerEl.classList.add('hidden');
            
            const selectedScenarioText = scenarioMap[currentScenario];
            
            const systemPrompt = `Anda adalah 'Buku-Pintar', Master Game Akuntansi profesional dan tegas (namun adil) untuk siswa SMK. Tugas Anda adalah menyajikan satu transaksi bisnis harian pada satu waktu. Setiap transaksi harus berupa skenario akuntansi satu-entri (hanya dua akun yang terlibat: satu debit, satu kredit). Fokuslah HANYA pada alur: ${selectedScenarioText}. Tingkat kesulitan harus disesuaikan dengan level: ${currentDifficulty}. Konteks bisnis yang digunakan adalah: ${customBusinessName}. Berikan jawaban dalam format JSON terstruktur di bawah ini.`;
            
            const userQuery = `Buatlah skenario transaksi ${totalTransactionsCompleted + 1} dari ${TRANSACTION_LIMIT} (${selectedScenarioText}) berikutnya untuk dianalisis, dengan tingkat kesulitan ${currentDifficulty}, dalam konteks bisnis ${customBusinessName}. Sertakan jawaban yang benar (akun debit/kredit yang tepat) dan penjelasannya. Pastikan nama akun menggunakan istilah Akuntansi Bahasa Indonesia (contoh: Kas, Utang Usaha, Beban Gaji).`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "transactionText": { "type": "STRING", "description": "Teks transaksi bisnis harian dalam Bahasa Indonesia. Harus jelas dan ringkas." },
                            "expectedDebitAccount": { "type": "STRING", "description": "Nama akun yang harus di-Debit (e.g., Kas, Perlengkapan, Beban Gaji)." },
                            "expectedCreditAccount": { "type": "STRING", "description": "Nama akun yang harus di-Kredit (e.g., Piutang Usaha, Utang Usaha, Pendapatan Jasa)." },
                            "amount": { "type": "NUMBER", "description": "Jumlah nominal transaksi (dalam Rupiah, tanpa format)." },
                            "explanation": { "type": "STRING", "description": "Penjelasan singkat (1-2 kalimat) mengenai prinsip debit/kredit yang mendasari entri ini." }
                        },
                        propertyOrdering: ["transactionText", "expectedDebitAccount", "expectedCreditAccount", "amount", "explanation"]
                    }
                }
            };

            try {
                const response = await withBackoff(() => fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                }));
                
                const result = await response.json();
                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;

                if (!jsonText) throw new Error("API did not return structured content.");

                currentTransaction = JSON.parse(jsonText);
                currentTransaction.amount = Math.round(currentTransaction.amount);

                transactionTextEl.innerHTML = `**TRANSAKSI (${customBusinessName}):** ${currentTransaction.transactionText} <br><span class="text-sm font-semibold text-amber-600">Nominal: ${formatRupiah(currentTransaction.amount)}</span>`;
                
                debitAccountInput.value = '';
                creditAccountInput.value = '';
                debitAccountInput.disabled = false;
                creditAccountInput.disabled = false;
                submitButton.textContent = 'Periksa Jawaban';
                submitButton.disabled = true;
                attemptCount = 0;
                
                transactionCounterEl.textContent = `Transaksi ${totalTransactionsCompleted + 1} dari ${TRANSACTION_LIMIT}`;

            } catch (error) {
                console.error("Error fetching new transaction:", error);
                transactionTextEl.textContent = "TERJADI ERROR. Buku-Pintar gagal memuat skenario baru. Silakan coba lagi nanti.";
            } finally {
                toggleLoading(false);
            }
        }

        function finishTransactionCycle(wasCorrectOnFinalAttempt) {
            totalTransactionsCompleted++;

            // Store the correct entry for ledger, regardless of user's input
            if (currentTransaction) {
                const entryData = {
                    debit: currentTransaction.expectedDebitAccount,
                    credit: currentTransaction.expectedCreditAccount,
                    amount: currentTransaction.amount,
                    isSolvedCorrectly: wasCorrectOnFinalAttempt,
                    transactionText: currentTransaction.transactionText
                };
                transactionHistory.push(entryData);
            }

            if (wasCorrectOnFinalAttempt && attemptCount <= 2) { 
                totalCorrectlySolved++;
            }

            debitAccountInput.disabled = true;
            creditAccountInput.disabled = true;
            submitButton.disabled = true;

            if (totalTransactionsCompleted >= TRANSACTION_LIMIT) {
                nextTransactionButton.classList.add('hidden');
                setTimeout(showReviewModal, 1500);
            } else {
                nextTransactionButton.classList.remove('hidden');
                nextTransactionButton.disabled = false;
            }
        }

        function handleSubmit(e) {
            e.preventDefault();
            if (!currentTransaction) return;

            const userDebit = debitAccountInput.value.trim();
            const userCredit = creditAccountInput.value.trim();
            
            const normalize = (str) => str.toLowerCase().replace(/[^a-z0-9]/g, '');
            const correctDebit = normalize(currentTransaction.expectedDebitAccount);
            const correctCredit = normalize(currentTransaction.expectedCreditAccount);
            const normalizedUserDebit = normalize(userDebit);
            const normalizedUserCredit = normalize(userCredit);

            let isCorrect = (normalizedUserDebit === correctDebit && normalizedUserCredit === correctCredit);
            let isSwapped = (normalizedUserDebit === correctCredit && normalizedUserCredit === correctDebit);

            feedbackArea.classList.remove('hidden', 'bg-red-100', 'border-red-500', 'bg-amber-100', 'border-amber-500', 'bg-green-100', 'border-green-500');
            correctAnswerEl.classList.add('hidden');
            nextTransactionButton.classList.add('hidden');

            if (isCorrect) {
                const points = 10;
                updateScore(points);
                feedbackArea.classList.add('bg-green-100', 'border-green-500');
                feedbackMessageEl.classList.add('text-green-700');
                feedbackMessageEl.textContent = `BENAR! +${points} Poin. Entri Anda (${userDebit} Debet, ${userCredit} Kredit) sudah tepat.`;
                finishTransactionCycle(true);
                
            } else {
                attemptCount++;
                let message = "SALAH! Perhatikan baik-baik akun yang bertambah dan berkurang.";

                if (isSwapped) {
                    message = "Hampir! Akun benar, tetapi posisi Debit dan Kredit tertukar. Ingat saldo normalnya.";
                } 

                if (attemptCount < 3) {
                    feedbackArea.classList.add('bg-red-100', 'border-red-500');
                    feedbackMessageEl.classList.add('text-red-700');
                    feedbackMessageEl.textContent = message;
                } else {
                    // Reveal Answer (Failure)
                    message = "Gagal. Buku-Pintar akan menunjukkan jawaban yang benar. (-5 Poin)";
                    updateScore(-5);
                    
                    feedbackArea.classList.add('bg-amber-100', 'border-amber-500');
                    feedbackMessageEl.classList.add('text-amber-700');
                    feedbackMessageEl.textContent = message;

                    correctAnswerEl.innerHTML = `**Jawaban Benar:** <span class="font-bold">${currentTransaction.expectedDebitAccount}</span> (Debet) dan <span class="font-bold">${currentTransaction.expectedCreditAccount}</span> (Kredit). <br> Penjelasan: ${currentTransaction.explanation}`;
                    correctAnswerEl.classList.remove('hidden');

                    finishTransactionCycle(false);
                }
            }
        }
        
        // --- EVENT LISTENERS ---

        document.getElementById('journal-form').addEventListener('submit', handleSubmit);
        
        startGameButton.addEventListener('click', () => {
            const selectedScenario = scenarioSelect.value;
            if (selectedScenario) {
                startGame(selectedScenario);
            }
        });

        nextTransactionButton.addEventListener('click', () => {
            currentTransaction = null;
            fetchNewTransaction();
        });

        resetGameButton.addEventListener('click', () => {
            showScenarioSelection();
        });
        
        restartGameButton.addEventListener('click', () => {
            showScenarioSelection();
        });
        
        // Tab click listeners
        tabReview.addEventListener('click', () => showReviewTab('review'));
        tabLedger.addEventListener('click', () => showReviewTab('ledger'));

        // Enable button when inputs are filled
        document.getElementById('journal-form').addEventListener('input', () => {
            const debitFilled = debitAccountInput.value.trim().length > 0;
            const creditFilled = creditAccountInput.value.trim().length > 0;
            if (debitFilled && creditFilled && currentTransaction && attemptCount < 3) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        });

        // --- START GAME ---
        window.onload = showScenarioSelection;
    </script>
</body>
</html>
