<?php
include "../admin/fungsi/koneksi.php";
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
    <title>Simulasi Peran Tim Kerja Dinamis</title>
    <!-- Memuat Tailwind CSS untuk styling modern dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Memuat Google Font Inter -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
        }
        .card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background-color: #4f46e5;
            transition: background-color 0.2s, transform 0.1s;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            transform: scale(1.02);
        }
        .selection-btn {
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            text-align: center;
            background-color: white;
            transition: all 0.2s;
            cursor: pointer;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .selection-btn:hover {
            border-color: #a5b4fc;
            background-color: #f5f8ff;
        }
        .selection-btn.active-grade {
            border-color: #4f46e5 !important;
            background-color: #eef2ff !important;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
            font-weight: bold;
        }
        .selection-btn.active-difficulty {
            border-color: #10b981 !important; /* Hijau untuk yang aktif */
            background-color: #ecfdf5 !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
            font-weight: bold;
        }
        /* Custom scrollbar for history area */
        #history {
            scrollbar-width: thin;
            scrollbar-color: #4f46e5 #e5e7eb;
        }
        #history::-webkit-scrollbar {
            width: 8px;
        }
        #history::-webkit-scrollbar-track {
            background: #e5e7eb;
            border-radius: 10px;
        }
        #history::-webkit-scrollbar-thumb {
            background-color: #4f46e5;
            border-radius: 10px;
        }
        
        .radio-option-card {
            cursor: pointer;
            border: 2px solid #e5e7eb;
            transition: all 0.2s;
        }
        .radio-option-card:hover {
            border-color: #a5b4fc; 
            background-color: #f5f8ff; 
        }
        input[type="radio"]:checked + label .radio-option-card {
            border-color: #4f46e5; 
            background-color: #eef2ff; 
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        /* Memastikan titik radio button terlihat terisi saat dipilih */
        input[type="radio"]:checked + label .radio-option-card > div {
            background-color: #4f46e5;
            border-color: #4f46e5;
            box-shadow: inset 0 0 0 3px white; /* Efek lingkaran terisi */
        }
    </style>
    <!-- Memuat Font Awesome untuk ikon mikrofon dan unduh -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Container Utama Aplikasi -->
    <div id="app-container" class="w-full max-w-4xl bg-white rounded-xl p-6 md:p-10 card">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 text-center">Simulasi Kerja Tim: Game Master AI</h1>
        
        <!-- Kontrol TTS -->
        <div class="mb-6 flex justify-end items-center space-x-2">
            <button id="tts-replay-btn" onclick="replayLastGMText()" disabled 
                class="p-2 text-sm rounded-lg flex items-center transition duration-150 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-redo-alt mr-2"></i> Ulangi
            </button>
            
            <button id="tts-stop-btn" onclick="pauseOrStopTTS()" disabled 
                class="p-2 text-sm rounded-lg flex items-center transition duration-150 bg-red-100 text-red-700 hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="stop-icon"><i class="fas fa-stop mr-2"></i></span>
                <span id="stop-status">Stop</span>
            </button>

            <button id="tts-toggle-btn" onclick="toggleTTS()" 
                class="p-2 text-sm rounded-lg flex items-center transition duration-150 bg-gray-200 text-gray-700 hover:bg-gray-300">
                <span id="tts-icon">🔇</span>
                <span class="ml-2" id="tts-status">Nonaktif</span>
            </button>
        </div>

        <!-- Loading Indicator -->
        <div id="loading" class="hidden text-center p-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mx-auto mb-4"></div>
            <p class="text-lg text-indigo-600" id="loading-text">AI Game Master sedang menyusun tantangan...</p>
        </div>

        <!-- 1. Pemilihan Level & Kesulitan (Diperbarui) -->
        <div id="level-selection">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Langkah 1: Tentukan Pengaturan Simulasi</h2>
            <p class="mb-6 text-gray-600">Pilih tingkat kelas (konteks masalah) dan tingkat kesulitan (sikap AI) yang Anda inginkan.</p>

            <!-- Pilihan Tingkat Kelas SMK (Context) -->
            <div class="mb-8 p-4 border border-indigo-200 rounded-lg bg-indigo-50">
                <label class="block text-lg font-bold text-indigo-800 mb-3">A. Tingkat Kelas SMK (Konteks Masalah):</label>
                <div id="gradeContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button onclick="selectGrade('Pemula (Kelas X)')" data-grade="Pemula (Kelas X)" class="grade-btn selection-btn border-indigo-300 text-indigo-700">
                        <p class="font-bold">Pemula</p>
                        <p class="text-sm text-gray-500">Dasar-dasar Proyek.</p>
                    </button>
                    <button onclick="selectGrade('Menengah (Kelas XI)')" data-grade="Menengah (Kelas XI)" class="grade-btn selection-btn border-indigo-300 text-indigo-700">
                        <p class="font-bold">Menengah</p>
                        <p class="text-sm text-gray-500">Tantangan teknis dan tim.</p>
                    </button>
                    <button onclick="selectGrade('Mahir (Kelas XII)')" data-grade="Mahir (Kelas XII)" class="grade-btn selection-btn border-indigo-300 text-indigo-700">
                        <p class="font-bold">Mahir</p>
                        <p class="text-sm text-gray-500">Keputusan strategis dan krisis.</p>
                    </button>
                </div>
            </div>
            
            <!-- Pilihan Tingkat Kesulitan Game (Behavior) -->
            <div class="mb-8 p-4 border border-green-200 rounded-lg bg-green-50">
                <label class="block text-lg font-bold text-green-800 mb-3">B. Tingkat Kesulitan Game (Sikap AI):</label>
                <div id="difficultyContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button onclick="selectDifficulty('Mudah')" data-difficulty="Mudah" class="difficulty-btn selection-btn border-green-300 text-green-700">
                        <p class="font-bold">Mudah</p>
                        <p class="text-sm text-gray-500">AI suportif, bantuan detail, kritik lembut.</p>
                    </button>
                    <button onclick="selectDifficulty('Normal')" data-difficulty="Normal" class="difficulty-btn selection-btn border-blue-300 text-blue-700">
                        <p class="font-bold">Normal</p>
                        <p class="text-sm text-gray-500">Keseimbangan tantangan dan panduan.</p>
                    </button>
                    <button onclick="selectDifficulty('Sulit')" data-difficulty="Sulit" class="difficulty-btn selection-btn border-red-300 text-red-700">
                        <p class="font-bold">Sulit</p>
                        <p class="text-sm text-gray-500">Tekanan tinggi, kritik tajam, sedikit bantuan.</p>
                    </button>
                </div>
            </div>

            <button id="continue-to-theme-btn" onclick="showThemeSelection()" class="btn-primary w-full text-white font-bold py-3 px-4 rounded-lg opacity-50 cursor-not-allowed" disabled>
                Lanjut ke Pemilihan Tema
            </button>
            
        </div>

        <!-- 2. Pemilihan Tema Proyek -->
        <div id="theme-selection" class="hidden">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Langkah 2: Pilih Tema Proyek Anda</h2>
            <p class="mb-6 text-gray-600">Level: <span id="chosen-level" class="font-semibold text-indigo-600"></span> | Kesulitan: <span id="chosen-difficulty" class="font-semibold text-green-600"></span></p>
            
            <div class="space-y-4 mb-8">
                <div>
                    <label for="theme-select" class="block text-sm font-medium text-gray-700 mb-2">Pilih Tema Standar (Cocok untuk RPL, TKJ, atau Bisnis):</label>
                    <select id="theme-select" class="w-full p-3 border border-indigo-300 rounded-lg bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="" disabled selected>-- Pilih salah satu tema --</option>
                        <option value="Pengembangan Aplikasi E-commerce Sekolah">Pengembangan Aplikasi E-commerce Sekolah</option>
                        <option value="Perancangan Jaringan dan Keamanan (Cyber Security)">Perancangan Jaringan dan Keamanan (Cyber Security)</option>
                        <option value="Kampanye Pemasaran Digital Produk Lokal">Kampanye Pemasaran Digital Produk Lokal</option>
                    </select>
                </div>
                <div class="relative flex py-5 items-center">
                    <div class="flex-grow border-t border-gray-300"></div>
                    <span class="flex-shrink mx-4 text-gray-500 text-sm font-medium">ATAU</span>
                    <div class="flex-grow border-t border-gray-300"></div>
                </div>
                <div>
                    <label for="custom-theme-input" class="block text-sm font-medium text-gray-700 mb-2">Masukkan Tema Kustom (Contoh: Proyek Agribisnis, Teknik Otomotif):</label>
                    <input type="text" id="custom-theme-input" placeholder="Contoh: Pembuatan Sistem Inventory Bengkel Baru" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <button id="start-btn" onclick="showRoleSelection()" class="btn-primary w-full text-white font-bold py-3 px-4 rounded-lg opacity-50 cursor-not-allowed" disabled>
                Lanjut ke Pemilihan Peran
            </button>
            <button onclick="resetGame()" class="text-sm text-gray-500 hover:text-gray-700 mt-4 transition duration-150">
                &larr; Kembali ke Pengaturan Awal
            </button>
        </div>

        <!-- 3. Pemilihan Peran (Dinamis) -->
        <div id="role-selection" class="hidden">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Langkah 3: Pilih Peran Anda</h2>
            <p class="mb-6 text-gray-600">Proyek: <span id="chosen-theme" class="font-semibold text-indigo-600"></span>.</p>

            <!-- Kontainer untuk peran yang dihasilkan secara dinamis oleh AI -->
            <div id="dynamicRoleContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <p class="text-center text-gray-500 italic col-span-2">Memuat peran yang sesuai dengan tema...</p>
            </div>
            
            <button onclick="showThemeSelection(selectedLevel)" class="text-sm text-gray-500 hover:text-gray-700 transition duration-150">
                &larr; Kembali
            </button>
        </div>

        <!-- 4. Simulasi Peran & Input Aksi -->
        <div id="simulation" class="hidden">
            <div class="mb-6 p-4 bg-indigo-100 rounded-lg border-l-4 border-indigo-500">
                <p class="text-sm font-medium text-indigo-800">
                    Level: <span id="current-level" class="font-bold"></span> | Kesulitan: <span id="current-difficulty" class="font-bold text-green-700"></span> | Peran Anda: <span id="current-role" class="font-bold"></span> | Proyek: <span id="current-theme" class="font-bold"></span>
                </p>
            </div>

            <!-- Riwayat Narasi Game -->
            <div id="history" class="h-96 overflow-y-auto bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                <div class="text-center text-gray-500 italic p-4">
                    Riwayat interaksi Anda akan muncul di sini.
                </div>
            </div>

            <!-- Area Input Pemain dengan Radio Button -->
            <div id="input-area" class="space-y-4">
                
                <!-- Radio Buttons Dinamis dari AI -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Tindakan yang Disarankan AI (Otomatis Kirim):</label>
                    <div id="radioOptionsContainer" class="space-y-3">
                        <p class="text-sm text-gray-500 p-2 bg-gray-100 rounded-lg">Opsi tindakan dari Game Master akan muncul di sini setelah tantangan pertama.</p>
                    </div>
                </div>

                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t border-gray-300"></div>
                    <span class="flex-shrink mx-4 text-gray-500 text-xs font-medium">ATAU</span>
                    <div class="flex-grow border-t border-gray-300"></div>
                </div>

                <!-- Input Teks Kustom -->
                <div>
                    <label for="customAction" class="block text-sm font-medium text-gray-700 mb-2">Input Tindakan Kustom (Atau ketik "Selesai" untuk mengakhiri game):</label>
                    <textarea id="customAction" oninput="uncheckRadioButtons(); checkInputStatus();"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 resize-none" rows="2" 
                        placeholder="Tuliskan tindakan atau keputusan Anda secara detail..."></textarea>
                </div>
                
                <div class="input-group mt-3 flex flex-row space-x-2">
                    <button id="voice-input-button" onclick="startVoiceRecognition()" class="w-1/3 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out flex items-center justify-center">
                        <i class="fas fa-microphone text-lg mr-2"></i> Input Suara
                    </button>

                    <button id="submit-action-btn" onclick="sendPlayerAction()" class="btn-primary w-2/3 text-white font-bold py-3 px-4 rounded-lg opacity-50 cursor-not-allowed" disabled>
                        Kirim Aksi
                    </button>
                </div>
                
                <p id="status-message" class="hidden mt-2 text-sm text-center text-red-600 font-medium"></p>
            </div>
            
            <button onclick="resetGame()" class="text-sm text-gray-500 hover:text-gray-700 mt-4 transition duration-150">
                &larr; Mulai Ulang Game
            </button>
        </div>

        <!-- 5. Tinjauan Akhir Game -->
        <div id="review-area" class="hidden">
            <h2 class="text-3xl font-extrabold text-indigo-700 mb-4 text-center">Tinjauan Kinerja (Game Review)</h2>
            <div class="mb-6 p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                <p class="text-sm font-medium text-green-800">
                    Simulasi Selesai. Berikut adalah analisis Game Master AI terhadap kinerja Anda.
                </p>
            </div>

            <!-- Konten Review dari AI -->
            <div id="review-content" class="bg-white p-6 border border-gray-200 rounded-lg shadow-inner whitespace-pre-wrap text-gray-800">
                Analisis dimuat...
            </div>
            
            <div class="mt-6 flex flex-col md:flex-row gap-4">
                <button id="download-review-btn" onclick="downloadReview()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-150 flex items-center justify-center">
                    <i class="fas fa-download mr-2"></i> Unduh Tinjauan (.txt)
                </button>
                 <button onclick="resetGame()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-lg transition duration-150">
                    Mulai Simulasi Baru
                </button>
            </div>
        </div>

    </div>

    <!-- Modal Pesan Kesalahan/Status (Ganti Alert) -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 items-center justify-center hidden" onclick="closeModal('statusModal')">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm mx-auto" onclick="event.stopPropagation()">
            <h3 id="modalTitle" class="text-lg font-semibold text-red-600 mb-3">Pesan</h3>
            <p id="modalMessage" class="text-gray-700 mb-4"></p>
            <button onclick="closeModal('statusModal')" class="w-full py-2 px-4 rounded-lg text-white bg-blue-500 hover:bg-blue-600 transition">
                Tutup
            </button>
        </div>
    </div>


    <script>
        // Variabel ini diperlukan untuk TTS, ASR, dan Game Logic
        let userId = 'anon_' + (crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).substring(2)); 
        
        // --- Variabel State Game ---
        let selectedLevel = null; // SMK Grade Level (Context)
        let selectedDifficulty = null; // NEW: Game Difficulty (AI Behavior)
        let selectedTheme = null;
        let selectedRole = null;
        let gameHistory = [];
        let lastGMText = ''; 
        let finalReviewText = ''; 
        let dynamicRoles = []; 
        const END_GAME_KEYWORDS = ['selesai', 'akhiri', 'finish', 'end', 'stop', 'berhenti'];
        const GEMINI_MODEL = "<?php echo $model; ?>";
        const API_BASE_URL = "https://generativelanguage.googleapis.com/v1beta/models/";
        const API_KEY = "<?php echo $apiKey; ?>"; 
        
        // --- Variabel State TTS & ASR ---
        let autoVoiceEnabled = false; 
        let recognition = null;
        let isRecognizing = false;

        // --- Elemen DOM ---
        const levelSelectionEl = document.getElementById('level-selection'); 
        const themeSelectionEl = document.getElementById('theme-selection');
        const roleSelectionEl = document.getElementById('role-selection');
        const simulationEl = document.getElementById('simulation');
        const loadingEl = document.getElementById('loading');
        const loadingTextEl = document.getElementById('loading-text');
        const historyEl = document.getElementById('history');
        const dynamicRoleContainer = document.getElementById('dynamicRoleContainer');
        const continueToThemeBtn = document.getElementById('continue-to-theme-btn'); // Baru

        // Input Aksi
        const radioOptionsContainer = document.getElementById('radioOptionsContainer');
        const customActionInput = document.getElementById('customAction');
        const submitActionBtn = document.getElementById('submit-action-btn');
        const inputAreaEl = document.getElementById('input-area');
        
        // Tinjauan Akhir
        const reviewAreaEl = document.getElementById('review-area');
        const reviewContentEl = document.getElementById('review-content');

        // Elemen DOM untuk TTS
        const ttsToggleBtn = document.getElementById('tts-toggle-btn');
        const ttsIcon = document.getElementById('tts-icon');
        const ttsStatus = document.getElementById('tts-status');
        const ttsReplayBtn = document.getElementById('tts-replay-btn'); 
        const ttsStopBtn = document.getElementById('tts-stop-btn');     
        const stopIcon = document.getElementById('stop-icon');          
        const stopStatus = document.getElementById('stop-status');      

        // Elemen DOM untuk ASR
        const voiceInputButton = document.getElementById('voice-input-button');
        const statusMessage = document.getElementById('status-message');
        
        // Elemen DOM untuk pemilihan tema
        const themeSelectEl = document.getElementById('theme-select');
        const customThemeInputEl = document.getElementById('custom-theme-input');
        
        // Elemen Display
        const chosenLevelEl = document.getElementById('chosen-level');
        const chosenDifficultyEl = document.getElementById('chosen-difficulty'); // Baru
        const currentLevelEl = document.getElementById('current-level');
        const currentDifficultyEl = document.getElementById('current-difficulty'); // Baru
        const modalEl = document.getElementById('statusModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        
        // --- FUNGSI UTILITY MODAL (Mengganti Alert) ---
        
        function showModal(title, message, isError = false) {
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalTitle.classList.toggle('text-red-600', isError);
            modalTitle.classList.toggle('text-blue-600', !isError);
            modalEl.classList.remove('hidden');
            modalEl.classList.add('flex');
        }

        window.closeModal = function(id) {
            document.getElementById(id).classList.remove('flex');
            document.getElementById(id).classList.add('hidden');
        }

        // --- FUNGSI TTS & ASR (Tidak Berubah Signifikan) ---

        function stopTTS() {
            window.speechSynthesis.cancel();
            updateTTSButtonState();
        }
        
        window.replayLastGMText = function() {
            if (lastGMText) {
                speakText(lastGMText);
            } else {
                showModal("Peringatan", "Tidak ada narasi Game Master terakhir untuk diputar ulang.", false);
            }
        }

        window.pauseOrStopTTS = function() {
            const isSpeaking = window.speechSynthesis.speaking;
            const isPaused = window.speechSynthesis.paused;
            
            if (isSpeaking && !isPaused) {
                window.speechSynthesis.pause();
            } else if (isSpeaking && isPaused) {
                window.speechSynthesis.resume();
            } else {
                stopTTS(); 
            }
            updateTTSButtonState();
        }

        function updateTTSButtonState() {
            const isSpeaking = window.speechSynthesis.speaking;
            const isPaused = window.speechSynthesis.paused;

            // 1. Tombol Toggle (Aktif/Nonaktif)
            if (!autoVoiceEnabled) {
                ttsIcon.innerHTML = '🔇';
                ttsStatus.textContent = 'Nonaktif';
                ttsToggleBtn.classList.remove('bg-indigo-300', 'text-indigo-800');
                ttsToggleBtn.classList.add('bg-gray-200', 'text-gray-700');
            } else {
                ttsIcon.innerHTML = '🔊';
                ttsStatus.textContent = 'Aktif';
                ttsToggleBtn.classList.add('bg-indigo-300', 'text-indigo-800');
                ttsToggleBtn.classList.remove('bg-gray-200', 'text-gray-700');
            }

            // 2. Tombol Replay
            const isSimulationActive = !!selectedRole;
            const hasGMText = !!lastGMText;
            ttsReplayBtn.disabled = !isSimulationActive || !hasGMText || isSpeaking;

            // 3. Tombol Stop/Pause
            if (isSpeaking) {
                ttsStopBtn.disabled = false;
                ttsStopBtn.classList.remove('bg-red-100', 'text-red-700', 'bg-red-50', 'text-red-600');
                ttsStopBtn.classList.add('bg-red-400', 'text-white');
                if (isPaused) {
                    stopIcon.innerHTML = '<i class="fas fa-play mr-2"></i>';
                    stopStatus.textContent = 'Lanjut';
                } else {
                    stopIcon.innerHTML = '<i class="fas fa-pause mr-2"></i>';
                    stopStatus.textContent = 'Jeda';
                }
            } else {
                ttsStopBtn.disabled = true;
                ttsStopBtn.classList.remove('bg-red-400', 'text-white');
                ttsStopBtn.classList.add('bg-red-100', 'text-red-700');
                stopIcon.innerHTML = '<i class="fas fa-stop mr-2"></i>';
                stopStatus.textContent = 'Stop';
            }
        }

        window.toggleTTS = function() {
            if (window.speechSynthesis.speaking) {
                stopTTS();
            }
            
            autoVoiceEnabled = !autoVoiceEnabled;
            if (!autoVoiceEnabled) {
                 stopTTS(); 
            }
            updateTTSButtonState();
        }

        function speakText(text) {
            if (!autoVoiceEnabled) {
                stopTTS(); 
                return;
            }
            if (!('speechSynthesis' in window)) {
                return;
            }

            // Menghilangkan Markdown ** sebelum TTS
            const cleanText = text.replace(/\*\*(.*?)\*\*/g, '$1'); 

            stopTTS(); 
            const utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.lang = 'id-ID';
            utterance.pitch = 1.0;
            utterance.rate = 0.95;

            utterance.onstart = updateTTSButtonState;
            utterance.onend = updateTTSButtonState;
            utterance.onerror = (event) => {
                console.error('TTS error:', event);
                updateTTSButtonState();
            };

            const setVoiceAndSpeak = () => {
                const voices = window.speechSynthesis.getVoices();
                const indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google")) 
                                     || voices.find(v => v.lang === 'id-ID');

                if (indoVoice) utterance.voice = indoVoice;
                window.speechSynthesis.speak(utterance);
            };

            if (window.speechSynthesis.getVoices().length > 0) {
                setVoiceAndSpeak();
            } else {
                window.speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
            }
        }

        function setupVoiceRecognition() {
            if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                recognition = new SpeechRecognition();
                
                recognition.continuous = false; 
                recognition.lang = 'id-ID'; 
                recognition.interimResults = false;

                recognition.onstart = () => {
                    isRecognizing = true;
                    voiceInputButton.classList.remove('bg-purple-600', 'hover:bg-purple-700', 'active:bg-purple-800');
                    voiceInputButton.classList.add('bg-red-600', 'hover:bg-red-700', 'active:bg-red-800');
                    voiceInputButton.innerHTML = '<i class="fas fa-microphone-alt-slash mr-2"></i> Berhenti Bicara...';
                    
                    statusMessage.textContent = 'Mendengarkan... (klik tombol untuk berhenti atau tunggu)';
                    statusMessage.classList.remove('hidden');
                };

                recognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript;
                    customActionInput.value = transcript; 
                    uncheckRadioButtons(); 
                    checkInputStatus();
                    stopVoiceRecognition(); 
                    
                    statusMessage.textContent = 'Transkripsi selesai. Klik "Kirim Aksi" untuk melanjutkan.';
                    statusMessage.classList.remove('hidden');
                };

                recognition.onerror = (event) => {
                    console.error('Speech recognition error:', event.error);
                    stopVoiceRecognition();
                    
                    let errorMessageText = 'Error input suara: ' + event.error + '. ';
                    
                    if (event.error === 'not-allowed') {
                        errorMessageText = 'Error: Akses mikrofon ditolak. Mohon izinkan akses mikrofon di pengaturan browser Anda.';
                    } else if (event.error === 'no-speech') {
                        errorMessageText = 'Tidak ada ucapan terdeteksi. Silakan coba lagi.';
                    }

                    statusMessage.textContent = errorMessageText;
                    statusMessage.classList.remove('hidden');
                };
                
                recognition.onend = () => {
                    isRecognizing = false;
                    voiceInputButton.classList.remove('bg-red-600', 'hover:bg-red-700', 'active:bg-red-800');
                    voiceInputButton.classList.add('bg-purple-600', 'hover:bg-purple-700', 'active:bg-purple-800');
                    voiceInputButton.innerHTML = '<i class="fas fa-microphone text-lg mr-2"></i> Input Suara';
                    if (statusMessage.textContent.includes('Mendengarkan...')) {
                        statusMessage.classList.add('hidden');
                    }
                };

            } else {
                voiceInputButton.disabled = true;
                voiceInputButton.classList.add('opacity-50', 'cursor-not-allowed');
                voiceInputButton.innerHTML = '<i class="fas fa-microphone-slash mr-2"></i> Suara Tdk Didukung';
                statusMessage.textContent = 'Peringatan: API Pengenalan Suara tidak didukung di browser ini.';
                statusMessage.classList.remove('hidden');
            }
        }

        window.startVoiceRecognition = function() {
            if (!recognition) return;
            statusMessage.classList.add('hidden');

            if (isRecognizing) {
                stopVoiceRecognition();
            } else {
                stopTTS(); 
                recognition.start();
            }
        }

        function stopVoiceRecognition() {
            if (isRecognizing && recognition) {
                recognition.stop();
            }
        }

        // --- FUNGSI PILIHAN TINGKAT & KESULITAN BARU ---

        function checkInitialSettings() {
            if (selectedLevel && selectedDifficulty) {
                continueToThemeBtn.disabled = false;
                continueToThemeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                continueToThemeBtn.disabled = true;
                continueToThemeBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
        
        window.selectGrade = function(grade) {
            selectedLevel = grade;
            // Hapus kelas aktif dari semua tombol grade
            document.querySelectorAll('.grade-btn').forEach(btn => {
                btn.classList.remove('active-grade');
            });
            // Tambahkan kelas aktif pada tombol yang dipilih
            document.querySelector(`[data-grade="${grade}"]`).classList.add('active-grade');
            checkInitialSettings();
        }

        window.selectDifficulty = function(difficulty) {
            selectedDifficulty = difficulty;
            // Hapus kelas aktif dari semua tombol difficulty
            document.querySelectorAll('.difficulty-btn').forEach(btn => {
                btn.classList.remove('active-difficulty');
            });
            // Tambahkan kelas aktif pada tombol yang dipilih
            document.querySelector(`[data-difficulty="${difficulty}"]`).classList.add('active-difficulty');
            checkInitialSettings();
        }
        
        // --- FUNGSI RADIO BUTTON (Tidak Berubah Signifikan) ---

        function getRadioSelection() {
            const checkedRadio = document.querySelector('input[name="ai-action-option"]:checked');
            return checkedRadio ? checkedRadio.value : null;
        }

        function uncheckRadioButtons() {
            const radioButtons = document.querySelectorAll('input[name="ai-action-option"]');
            radioButtons.forEach(radio => radio.checked = false);
            checkInputStatus();
        }

        function clearCustomInput() {
            customActionInput.value = '';
        }

        function populateActionOptions(options) {
            radioOptionsContainer.innerHTML = ''; 
            
            if (options && options.length > 0) {
                options.forEach((optionText, index) => {
                    const id = `radio-option-${index}`;
                    
                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.id = id;
                    radio.name = 'ai-action-option';
                    radio.value = optionText.trim();
                    radio.classList.add('hidden'); 
                    
                    // --- LOGIKA RADIO BUTTON AUTO SUBMIT ---
                    radio.onclick = () => {
                        clearCustomInput(); 
                        checkInputStatus();
                        sendPlayerAction();
                    };

                    const label = document.createElement('label');
                    label.htmlFor = id;
                    label.classList.add('block', 'p-0'); 
                    
                    const card = document.createElement('div');
                    card.classList.add('radio-option-card', 'p-4', 'rounded-lg', 'flex', 'items-center', 'space-x-3');
                    
                    const dot = document.createElement('div');
                    dot.classList.add('w-4', 'h-4', 'rounded-full', 'border-2', 'border-indigo-400', 'flex-shrink-0'); 
                    
                    const textSpan = document.createElement('span');
                    textSpan.classList.add('text-gray-700', 'font-medium', 'text-sm');
                    textSpan.textContent = optionText.trim();

                    card.appendChild(dot);
                    card.appendChild(textSpan);
                    label.appendChild(card);
                    
                    radioOptionsContainer.appendChild(radio);
                    radioOptionsContainer.appendChild(label);
                });
            } else {
                 radioOptionsContainer.innerHTML = '<p class="text-sm text-gray-500 p-2 bg-gray-100 rounded-lg">Opsi tindakan dari Game Master akan muncul di sini.</p>';
            }
            customActionInput.value = '';
            uncheckRadioButtons();
            checkInputStatus();
        }

        // FUNGSI UTILITY: Memeriksa apakah input/opsi sudah terisi
        function checkInputStatus() {
            const selectedOption = getRadioSelection();
            const customInput = customActionInput.value.trim();

            if (customInput || selectedOption) {
                submitActionBtn.disabled = false;
                submitActionBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitActionBtn.disabled = true;
                submitActionBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // --- FUNGSI NAVIGASI GAME & UI ---

        function showLoading(show, message = "AI Game Master sedang menyusun tantangan...") {
            
            stopTTS(); 

            levelSelectionEl.classList.add('hidden');
            themeSelectionEl.classList.add('hidden');
            roleSelectionEl.classList.add('hidden');
            simulationEl.classList.add('hidden');
            reviewAreaEl.classList.add('hidden');

            if (show) {
                loadingTextEl.textContent = message;
                loadingEl.classList.remove('hidden');
                submitActionBtn.disabled = true;
                customActionInput.disabled = true;
                if (recognition) voiceInputButton.disabled = true;
            } else {
                loadingEl.classList.add('hidden');
                
                const currentView = selectedRole ? simulationEl : (dynamicRoles.length > 0 ? roleSelectionEl : (selectedLevel ? themeSelectionEl : levelSelectionEl));
                currentView.classList.remove('hidden');
                
                if (currentView === simulationEl) {
                    customActionInput.disabled = false;
                    if (recognition) voiceInputButton.disabled = false;
                    checkInputStatus();
                }
            }
            updateTTSButtonState();
        }
        
        function updateHistory(speaker, rawText) {
            if (historyEl.querySelector('.italic')) {
                historyEl.innerHTML = '';
            }

            let narrativeText = rawText;
            
            const optionsIndex = rawText.indexOf('[OPTIONS]');
            if (speaker === 'GM' && optionsIndex !== -1) {
                narrativeText = rawText.substring(0, optionsIndex).trim();
                const optionsString = rawText.substring(optionsIndex + '[OPTIONS]'.length).trim();
                const suggestedOptions = optionsString.split('|').map(s => s.trim()).filter(s => s.length > 0);
                
                populateActionOptions(suggestedOptions);
                lastGMText = narrativeText; 
            } else if (speaker === 'GM') {
                populateActionOptions([]); 
                inputAreaEl.classList.add('hidden');
                lastGMText = narrativeText;
            } else if (speaker === 'Player') {
                stopTTS(); 
            }
            
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('mb-4', 'p-3', 'rounded-lg');
            
            let htmlContent = '';
            if (speaker === 'GM') {
                stopTTS(); 
                messageDiv.classList.add('bg-indigo-50', 'border-l-4', 'border-indigo-500');
                
                // MODIFIKASI: Menghilangkan tanda ** dari teks tampilan
                const formattedText = narrativeText
                    .replace(/\*\*(.*?)\*\*/g, '$1') // Menghapus ** dan menjaga isinya
                    .replace(/\n/g, '<br>');
                htmlContent = `<p class="font-bold text-indigo-800">Game Master:</p><p class="text-gray-700">${formattedText}</p>`;
                
                speakText(narrativeText); 
                
            } else if (speaker === 'Player') {
                messageDiv.classList.add('bg-gray-100', 'border-r-4', 'border-gray-400', 'text-right');
                htmlContent = `<p class="font-bold text-gray-800">Anda:</p><p class="text-gray-600">${rawText.replace(/\n/g, '<br>')}</p>`;
            }
            
            messageDiv.innerHTML = htmlContent;
            historyEl.appendChild(messageDiv);
            historyEl.scrollTop = historyEl.scrollHeight; 
            updateTTSButtonState();
        }

        window.showThemeSelection = function() {
            stopTTS(); 
            if (!selectedLevel || !selectedDifficulty) {
                 showModal("Peringatan", "Mohon pilih Tingkat Kelas dan Tingkat Kesulitan terlebih dahulu.", true);
                 return;
            }

            // Tampilkan pengaturan yang dipilih di Langkah 2
            chosenLevelEl.textContent = selectedLevel;
            chosenDifficultyEl.textContent = selectedDifficulty;

            levelSelectionEl.classList.add('hidden');
            themeSelectionEl.classList.remove('hidden');
            selectedTheme = null; 
            dynamicRoles = []; 
            customThemeInputEl.value = '';
            themeSelectEl.value = '';
            checkThemeSelection(); 
        }
        
        function checkThemeSelection() {
            const customTheme = customThemeInputEl.value.trim();
            const selectedOption = themeSelectEl.value;
            
            if (customTheme) {
                selectedTheme = customTheme;
            } else if (selectedOption) {
                selectedTheme = selectedOption;
            } else {
                selectedTheme = null;
            }

            if (selectedTheme) {
                document.getElementById('start-btn').disabled = false;
                document.getElementById('start-btn').classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                document.getElementById('start-btn').disabled = true;
                document.getElementById('start-btn').classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        window.showRoleSelection = function() {
            stopTTS(); 
            if (!selectedTheme) return;
            themeSelectionEl.classList.add('hidden');
            roleSelectionEl.classList.remove('hidden');
            document.getElementById('chosen-theme').textContent = selectedTheme;
            
            generateAndShowDynamicRoles();
        }

        async function generateAndShowDynamicRoles() {
            showLoading(true, "AI Game Master sedang menentukan 4 peran tim yang relevan untuk tema ini...");
            dynamicRoleContainer.innerHTML = '<p class="text-center text-gray-500 italic col-span-2 p-4">Memuat peran yang sesuai dengan tema...</p>';
            dynamicRoles = []; 
            
            const roleQuery = `Berdasarkan proyek SMK ini: "${selectedTheme}", berikan 4 peran pekerjaan kunci yang paling relevan dan kritikal dalam format JSON. Pastikan peran tersebut cocok untuk level ${selectedLevel} dengan fokus pada kesulitan ${selectedDifficulty}.`;

            const payload = {
                contents: [{ parts: [{ text: roleQuery }] }],
                systemInstruction: { parts: [{ text: "Anda adalah pakar kurikulum vokasi. Tugas Anda adalah memberikan daftar 4 peran pekerjaan yang sangat relevan dan profesional berdasarkan tema proyek yang diberikan, khusus untuk siswa SMK. Nama peran dan deskripsi harus singkat (maks 10 kata) dan dalam Bahasa Indonesia." }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "ARRAY",
                        items: {
                            type: "OBJECT",
                            properties: {
                                "name": { "type": "STRING" },
                                "description": { "type": "STRING" }
                            },
                            required: ["name", "description"]
                        },
                        minItems: 4,
                        maxItems: 4
                    }
                },
            };

            const url = `${API_BASE_URL}${GEMINI_MODEL}:generateContent?key=${API_KEY}`;
            
            for (let attempt = 0; attempt < 3; attempt++) {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    
                    if (response.ok) {
                        const result = await response.json();
                        let roles = [];
                        
                        try {
                            const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                            roles = JSON.parse(jsonText);
                            
                            if (!Array.isArray(roles) || roles.length < 4) {
                                throw new Error("Invalid roles array structure or size.");
                            }
                        } catch (e) {
                            console.error("Failed to parse JSON roles, using fallback:", e);
                            roles = [
                                { name: "Project Manager", description: "Mengatur jadwal, mengawasi budget, dan memimpin tim." },
                                { name: "Lead Developer", description: "Bertanggung jawab pada kualitas kode dan teknis." },
                                { name: "UX/UI Designer", description: "Mendesain tampilan yang mudah dan nyaman dipakai pengguna." },
                                { name: "Marketing Specialist", description: "Merencanakan promosi dan analisis pasar." }
                            ];
                            showModal("Peringatan", "Gagal menghasilkan peran dinamis. Menggunakan peran standar.", false);
                        }
                        
                        renderDynamicRoles(roles);
                        showLoading(false);
                        return;

                    } else if (response.status === 429 && attempt < 2) {
                        const delay = Math.pow(2, attempt) * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    } else {
                        showLoading(false);
                        console.error("API Error:", response.status, await response.text());
                        showModal("Error API", "Server Game Master mengalami masalah. Silakan coba lagi.", true);
                        break; 
                    }

                } catch (error) {
                    showLoading(false);
                    console.error("Fetch Error:", error);
                    showModal("Error Jaringan", "Gagal mengambil peran dari AI. Silakan coba lagi.", true);
                    break;
                }
            }
            if (dynamicRoles.length === 0) {
                 resetGame(); 
            }
        }

        function renderDynamicRoles(roles) {
            dynamicRoles = roles; 
            const container = document.getElementById('dynamicRoleContainer');
            container.innerHTML = ''; 

            const colorClasses = [
                'border-green-300 hover:bg-green-50 text-green-700',
                'border-blue-300 hover:bg-blue-50 text-blue-700',
                'border-yellow-300 hover:bg-yellow-50 text-yellow-700',
                'border-red-300 hover:bg-red-50 text-red-700'
            ];
            
            roles.forEach((role, index) => {
                const btnClass = colorClasses[index % colorClasses.length];
                
                const button = document.createElement('button');
                button.setAttribute('onclick', `startGame('${role.name}')`);
                button.classList.add('role-btn', 'p-4', 'border', 'rounded-lg', 'text-left', 'bg-white', 'transition', 'duration-150', 'shadow-sm', ...btnClass.split(' '));
                
                button.innerHTML = `
                    <p class="font-bold">${role.name}</p>
                    <p class="text-sm text-gray-500">${role.description}</p>
                `;
                container.appendChild(button);
            });
        }


        window.startGame = async function(role) {
            stopTTS(); 
            if (!selectedLevel || !selectedTheme || dynamicRoles.length === 0 || !selectedDifficulty) return; 
            
            selectedRole = role;
            currentLevelEl.textContent = selectedLevel;
            currentDifficultyEl.textContent = selectedDifficulty; // Tampilkan kesulitan di simulasi
            document.getElementById('current-role').textContent = selectedRole;
            document.getElementById('current-theme').textContent = selectedTheme;
            
            roleSelectionEl.classList.add('hidden');
            simulationEl.classList.remove('hidden');
            inputAreaEl.classList.remove('hidden'); 
            
            autoVoiceEnabled = true; 
            updateTTSButtonState(); 

            await initializeSimulation();
        }

        window.resetGame = function() {
            stopTTS(); 
            selectedLevel = null;
            selectedDifficulty = null; // Reset kesulitan
            selectedTheme = null;
            selectedRole = null;
            gameHistory = [];
            lastGMText = ''; 
            finalReviewText = '';
            dynamicRoles = []; 
            
            autoVoiceEnabled = false; 
            updateTTSButtonState(); 

            // Hapus kelas 'active'
            document.querySelectorAll('.selection-btn').forEach(btn => {
                btn.classList.remove('active-grade', 'active-difficulty');
            });

            historyEl.innerHTML = '<div class="text-center text-gray-500 italic p-4">Riwayat interaksi Anda akan muncul di sini.</div>';
            dynamicRoleContainer.innerHTML = '<p class="text-center text-gray-500 italic col-span-2 p-4">Memuat peran yang sesuai dengan tema...</p>';
            
            themeSelectEl.value = "";
            customThemeInputEl.value = "";
            customActionInput.value = '';
            populateActionOptions([]); 
            
            levelSelectionEl.classList.remove('hidden');
            themeSelectionEl.classList.add('hidden');
            roleSelectionEl.classList.add('hidden');
            simulationEl.classList.add('hidden');
            reviewAreaEl.classList.add('hidden');
            
            // Setel kembali tombol continue
            continueToThemeBtn.disabled = true;
            continueToThemeBtn.classList.add('opacity-50', 'cursor-not-allowed');

            stopVoiceRecognition();
            showLoading(false); 
        }

        // --- FUNGSI TINJAUAN AKHIR DAN UNDUH (Tidak Berubah Signifikan) ---
        
        async function runFinalReview() {
            showLoading(true, "AI Game Master sedang menganalisis kinerja Anda...");
            
            const reviewQuery = `Tinjau riwayat game di atas. Berikan feedback, saran, dan hal-hal relevan lainnya secara profesional sebagai Tinjauan Kinerja Akhir. Susun tinjauan dalam beberapa poin kunci menggunakan format Markdown standar (Gunakan Judul, sub-judul, list/bullet points) untuk memudahkan pembacaan, dan pastikan mencakup: 
            1. **Ringkasan Kinerja:** Seberapa baik pemain ${selectedRole} mengatasi masalah utama.
            2. **Kekuatan (Strengths):** Keputusan atau tindakan terbaik.
            3. **Area Peningkatan (Suggestions/Areas for Improvement):** Saran spesifik untuk giliran yang kurang optimal.
            4. **Pelajaran Kunci (Key Takeaways):** Hubungkan dengan kompetensi SMK. Bahasa yang digunakan harus mudah dipahami siswa SMK.`;

            gameHistory.push({ role: "user", parts: [{ text: reviewQuery }] });

            const gmReview = await callGeminiApi(gameHistory, true); 
            
            finalReviewText = gmReview; 
            reviewContentEl.textContent = gmReview.replace(/\*\*/g, '').replace(/###/g, '\n').replace(/##/g, '\n').trim(); 
            
            simulationEl.classList.add('hidden');
            reviewAreaEl.classList.remove('hidden');
            showLoading(false);
            
            gameHistory.pop(); 

            const reviewSummary = gmReview.substring(0, Math.min(200, gmReview.length)); 
            speakText(`Simulasi Selesai. Inilah ringkasan tinjauan Anda: ${reviewSummary}... (Silakan baca detail lengkap di layar)`);
            
            autoVoiceEnabled = false;
            updateTTSButtonState();
        }

        window.downloadReview = function() {
            if (!finalReviewText) {
                showModal("Error", "Konten tinjauan belum siap untuk diunduh.", true);
                return;
            }

            const cleanText = finalReviewText
                .replace(/\*\*(.*?)\*\*/g, '$1') 
                .replace(/#+ /g, '') 
                .replace(/- /g, '\n* ') 
                .replace(/\n\n/g, '\n'); 
            
            const data = 
`=== TINJAUAN KINERJA SIMULASI KERJA TIM ===
Proyek: ${selectedTheme}
Peran: ${selectedRole}
Level: ${selectedLevel}
Kesulitan Game: ${selectedDifficulty}
Tanggal: ${new Date().toLocaleDateString('id-ID')}

${cleanText}

=======================================
Ini adalah review otomatis dari Game Master AI.`;
            
            const filename = `Tinjauan_Simulasi_${selectedRole}_${Date.now()}.txt`;
            const blob = new Blob([data], { type: 'text/plain;charset=utf-8' });
            
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            showModal("Berhasil!", `File '${filename}' telah berhasil diunduh.`, false);
        }
        
        // --- Interaksi AI Game Master ---

        async function callGeminiApi(promptParts, isReview = false) {
            showLoading(true, isReview ? "AI sedang menganalisis dan menyusun tinjauan..." : "AI Game Master sedang menyusun tantangan...");
            
            const allRoles = dynamicRoles.map(r => r.name);
            const npcRoles = allRoles.filter(role => role !== selectedRole).join(', ');

            // MODIFIKASI: Menambahkan Kesulitan Game ke System Prompt
            const dynamicSystemPrompt = `Anda adalah Game Master (GM) untuk simulasi kerja tim profesional.
            Anda harus memberikan tantangan narasi, mengevaluasi respons pemain, dan memajukan cerita berdasarkan peran pemain.

            Aturan Anda:
            1. **RELEVANSI SMK**: Tujukan narasi, terminologi, dan kompleksitas masalah untuk siswa SMK level **${selectedLevel}**. Jaga agar konteksnya tetap praktis dan berbasis kompetensi.
            2. **BAHASA**: Bahasa yang digunakan harus SANGAT MUDAH dipahami siswa SMK. Hindari jargon teknis atau formalitas berlebihan. Gunakan bahasa kerja yang lugas.
            3. Pemain adalah seorang **${selectedRole}** dalam proyek "${selectedTheme}".
            4. Anggota tim lain yang Anda kendalikan sebagai NPC adalah: **${npcRoles}**.
            5. **TINGKAT KESULITAN**: Kesulitan game saat ini adalah **${selectedDifficulty}**. Sesuaikan respons Anda:
                - Jika 'Mudah': Berikan petunjuk yang jelas, kritik lembut, dan skenario yang mudah diatasi.
                - Jika 'Normal': Berikan tantangan yang seimbang, kritik konstruktif, dan alur cerita yang wajar.
                - Jika 'Sulit': Berikan tekanan waktu, kritik tajam, skenario yang melibatkan krisis, dan sedikit bantuan.
            6. Setiap giliran, sampaikan narasi yang realistis, singkat, dan melibatkan interaksi. Dialog/tindakan NPC harus diawali dengan format cetak tebal (bold) **Nama (Peran)**.
            7. ${isReview ? "JANGAN sediakan OPSI TINDAKAN. Berikan Tinjauan Kinerja berdasarkan instruksi terakhir." : "WAJIB MENYEDIAKAN OPSI TINDAKAN: Di akhir setiap respons Anda (setelah narasi), Anda harus menambahkan tag khusus '[OPTIONS]' diikuti oleh minimal 3 opsi tindakan spesifik yang dipisahkan oleh karakter '|'. Contoh Format Akhir: [NARASI]... Apa yang akan Anda lakukan? [OPTIONS]Opsi Tindakan A|Opsi Tindakan B|Opsi Tindakan C."}
            8. Jangan pernah memberikan respon naratif yang terlalu panjang (maksimal 150 kata per giliran).
            `;

            const payload = {
                contents: promptParts,
                systemInstruction: { parts: [{ text: dynamicSystemPrompt }] },
            };

            const url = `${API_BASE_URL}${GEMINI_MODEL}:generateContent?key=${API_KEY}`;
            
            for (let attempt = 0; attempt < 3; attempt++) {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.ok) {
                        const result = await response.json();
                        showLoading(false);
                        return result.candidates?.[0]?.content?.parts?.[0]?.text || (isReview ? "GM: Gagal membuat tinjauan." : "GM: Maaf, ada gangguan komunikasi. Silakan coba kirim aksi lagi.");
                    } else if (response.status === 429 && attempt < 2) {
                        const delay = Math.pow(2, attempt) * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    } else {
                        showLoading(false);
                        console.error("API Error:", response.status, await response.text());
                        showModal("Error API", "Server Game Master mengalami masalah. Silakan coba kirim aksi lagi.", true);
                        return (isReview ? "GM: Server Game Master mengalami masalah serius saat membuat tinjauan." : "GM: Server Game Master mengalami masalah serius.");
                    }
                } catch (error) {
                    showLoading(false);
                    console.error("Fetch Error:", error);
                    showModal("Error Jaringan", "Terjadi kesalahan jaringan. Periksa koneksi Anda.", true);
                    return (isReview ? "GM: Terjadi kesalahan jaringan saat membuat tinjauan." : "GM: Terjadi kesalahan jaringan.");
                }
            }
        }

        async function initializeSimulation() {
            showLoading(true);
            
            if (!selectedLevel || !selectedTheme || !selectedRole || !selectedDifficulty) { 
                resetGame();
                return;
            }

            const initialQuery = `Kita memulai Proyek "${selectedTheme}" di level ${selectedLevel} dengan Kesulitan ${selectedDifficulty}. Saya adalah ${selectedRole}. Berikan skenario pembuka yang singkat dan jelas. Langsung berikan masalah *praktis* pertama yang harus saya atasi dalam peran saya, dan sediakan 3 opsi tindakan. Pastikan bahasanya sangat sesuai untuk siswa SMK.`;

            const promptParts = [
                { role: "user", parts: [{ text: initialQuery }] }
            ];

            const gmResponse = await callGeminiApi(promptParts);

            gameHistory.push(
                { role: "user", parts: [{ text: initialQuery }] },
                { role: "model", parts: [{ text: gmResponse }] }
            );

            updateHistory('GM', gmResponse);
            showLoading(false);
        }

        window.sendPlayerAction = async function() {
            let action = getRadioSelection() || customActionInput.value.trim();

            if (!action) {
                showModal("Peringatan", "Mohon pilih salah satu opsi tindakan atau masukkan tindakan kustom.", true);
                return;
            }
            
            stopVoiceRecognition(); 
            stopTTS(); 

            const lowerAction = action.toLowerCase();
            const isEndingGame = END_GAME_KEYWORDS.some(keyword => lowerAction.includes(keyword));

            if (isEndingGame) {
                 updateHistory('Player', action);
                 await runFinalReview();
                 return; 
            }

            gameHistory.push({ role: "user", parts: [{ text: action }] });
            updateHistory('Player', action);

            submitActionBtn.disabled = true;
            customActionInput.disabled = true;
            if (recognition) voiceInputButton.disabled = true;

            const gmResponse = await callGeminiApi(gameHistory);
            
            gameHistory.push({ role: "model", parts: [{ text: gmResponse }] });
            updateHistory('GM', gmResponse);

            customActionInput.disabled = false;
            if (recognition) voiceInputButton.disabled = false;
        }
        
        // --- Event Listeners Awal ---
        window.addEventListener('beforeunload', stopTTS);

        themeSelectEl.addEventListener('change', () => {
            if (themeSelectEl.value) { customThemeInputEl.value = ''; }
            checkThemeSelection();
        });
        
        customThemeInputEl.addEventListener('input', () => {
            if (customThemeInputEl.value.trim()) { themeSelectEl.value = ''; }
            checkThemeSelection();
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            stopTTS(); 
            setupVoiceRecognition();
            updateTTSButtonState(); 
            showLoading(false); 
        });
    </script>
</body>
</html>
