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
    <title>Simulasi SOP Proyek SMK | Game Master AI</title>
    <!-- Muat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0d1117; /* GitHub Dark Mode Background */
            min-height: 100vh;
            color: #c9d1d9; /* Light text for dark background */
        }
        .container {
            max-width: 900px;
        }
        .card {
            background-color: #161b22; /* Darker Card background */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
            border: 1px solid #30363d; /* Subtle border */
        }
        
        /* Custom Button Styling */
        .primary-button {
            background-image: linear-gradient(to right, #218bff 0%, #006aff 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 106, 255, 0.3);
        }
        .primary-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 106, 255, 0.5);
        }
        
        /* Choice Button Styling */
        .choice-button {
            transition: all 0.2s ease-in-out;
            border-left: 6px solid #58a6ff; /* Blue accent */
            font-weight: 600;
            background-color: #21262d; /* Slightly lighter than card */
            color: #c9d1d9;
        }
        .choice-button:hover {
            background-color: #30363d; /* Darker hover */
            transform: scale(1.005);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .spinner {
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Feedback Styles */
        .feedback-success { background-color: #1b5e20; color: #a5d6a7; border-color: #4caf50; }
        .feedback-failure { background-color: #7f2b2b; color: #ffcdd2; border-color: #f44336; }
        .feedback-continue { background-color: #3e2723; color: #ffccbc; border-color: #ff9800; }

        /* Score Circle Styling */
        .score-circle {
            border: 8px solid;
            transition: all 0.3s;
        }
        .score-success {
            border-color: #38a169; /* Green 600 */
            background-color: #1e3a2b; /* Dark Green */
            color: #68d391; 
        }
        .score-failure {
            border-color: #e53e3e; /* Red 600 */
            background-color: #4a2d2d; /* Dark Red */
            color: #feb2b2; 
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <div id="app" class="container mx-auto">
        <div class="relative w-full mb-6">
    <img src="../admin/foto/<?= $data['banner'] ?>" alt="Banner Sekolah" class="w-full h-40 md:h-48 object-cover rounded-xl shadow-lg">
    <img src="../admin/foto/<?= $data['logo'] ?>" alt="Logo Sekolah" 
         class="absolute left-6 top-1/2 transform -translate-y-1/2 w-20 h-20 md:w-28 md:h-28 object-contain rounded-full border-4 border-white shadow-xl">
</div>

        <header class="text-center mb-10 py-5 bg-gray-900 rounded-xl shadow-2xl border-b border-gray-700">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-400">Simulasi SOP Proyek SMK</h1>
            <p class="text-gray-400 mt-2 text-sm">Game Master AI - Panduan SOP Berbasis Proyek</p>
        </header>

        <!-- Bagian Setup Awal -->
        <div id="setup-screen" class="card p-6 rounded-xl">
            <h2 class="text-2xl font-bold mb-6 text-white border-b border-gray-700 pb-3">⚙️ Atur Simulasi Baru</h2>
            <div class="space-y-5">
                
                <!-- Pilihan Level Kesulitan -->
                <div>
                    <label for="difficulty-select" class="block text-sm font-semibold text-gray-400 mb-1">Level Kesulitan</label>
                    <select id="difficulty-select" class="w-full p-3 border border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 shadow-inner bg-gray-800 text-white">
                        <option value="Mudah">Mudah (Panduan Jelas)</option>
                        <option value="Sedang" selected>Sedang (Tantangan Seimbang)</option>
                        <option value="Sulit">Sulit (Detail Teknis Tinggi)</option>
                    </select>
                </div>

                <!-- Pilihan Tema -->
                <div>
                    <label for="theme-select" class="block text-sm font-semibold text-gray-400 mb-1">Pilih Tema Populer (Jurusan)</label>
                    <select id="theme-select" class="w-full p-3 border border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 shadow-inner bg-gray-800 text-white">
                        <option value="" disabled selected>-- Pilih Salah Satu --</option>
                        <option value="Perakitan Komputer (Teknik Komputer dan Jaringan)">Perakitan Komputer (TKJ)</option>
                        <option value="Pengembangan Website E-Commerce (Rekayasa Perangkat Lunak)">Pengembangan Website E-Commerce (RPL)</option>
                        <option value="Instalasi Panel Listrik Rumah Tangga (Teknik Instalasi Tenaga Listrik)">Instalasi Panel Listrik (TITL)</option>
                        <option value="Perbaikan Mesin Sepeda Motor (Teknik Kendaraan Ringan Otomotif)">Perbaikan Mesin Sepeda Motor (TKRO)</option>
                        <option value="Produksi Konten Video Iklan (Multimedia/Desain Komunikasi Visual)">Produksi Konten Video Iklan (MM/DKV)</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <div class="flex-grow border-t border-gray-700"></div>
                    <span class="flex-shrink mx-4 text-gray-500 text-xs font-medium">ATAU</span>
                    <div class="flex-grow border-t border-gray-700"></div>
                </div>
                <div>
                    <label for="custom-theme-input" class="block text-sm font-semibold text-gray-400 mb-1">Tema Proyek Kustom Anda</label>
                    <input type="text" id="custom-theme-input" placeholder="Contoh: Perancangan Jaringan Fiber Optik" class="w-full p-3 border border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 shadow-inner bg-gray-800 text-white placeholder-gray-500">
                </div>
            </div>
            <button id="start-button" class="primary-button mt-8 w-full py-3 px-4 text-white font-bold rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50" onclick="startGame()">
                Mulai Simulasi SOP
            </button>
            <p id="auth-status" class="mt-4 text-xs text-center text-gray-500">Mode Lokal: Tidak memerlukan koneksi database.</p>
        </div>

        <!-- Area Permainan -->
        <div id="game-screen" class="hidden">

            <!-- Informasi Sesi dan Langkah -->
            <div class="mb-6 flex justify-between items-center bg-gray-900 p-4 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="truncate">
                    <p class="text-sm font-medium text-gray-300 truncate">Proyek: <span id="current-theme" class="font-bold text-white"></span></p>
                    <p class="text-xs text-gray-400">Level: <span id="current-difficulty" class="font-bold"></span></p>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-2xl font-extrabold text-blue-400" id="step-counter">0</span>
                    <span class="block text-xs text-gray-400">Langkah</span>
                </div>
            </div>

            <!-- Kartu Skenario Utama -->
            <div class="card p-6 rounded-xl mb-6">
                <h2 class="text-xl font-bold text-white mb-3 flex items-center" id="scenario-title">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Memuat Skenario...
                </h2>
                <div id="feedback-area" class="text-sm p-3 rounded-lg my-3 hidden font-medium border-l-4"></div>
                
                <!-- Narasi dan TTS Controls -->
                <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
                    <p id="narration-text" class="text-gray-300 leading-relaxed min-h-[100px] text-base">Silakan tunggu, Game Master AI sedang menyusun langkah SOP pertama Anda...</p>

                    <div class="mt-4 flex space-x-3 justify-end pt-3 border-t border-gray-700">
                        <button id="tts-replay-button" class="flex items-center text-xs px-3 py-1 bg-gray-600 text-white rounded-full hover:bg-gray-500 transition duration-150 disabled:opacity-50" onclick="replayNarration()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4a1 1 0 011-1h10a1 1 0 011 1v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4zM10 5a1 1 0 00-1 1v4a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                <path fill-rule="evenodd" d="M12 8a.75.75 0 011.06 0l2.47 2.47a.75.75 0 010 1.06L13.06 14a.75.75 0 11-1.06-1.06l1.72-1.72-1.72-1.72a.75.75 0 010-1.06zM7 8a.75.75 0 011.06 0l2.47 2.47a.75.75 0 010 1.06L8.06 14a.75.75 0 11-1.06-1.06l1.72-1.72-1.72-1.72a.75.75 0 010-1.06z" clip-rule="evenodd" />
                            </svg>
                            Ulangi
                        </button>
                        <button id="tts-stop-button" class="flex items-center text-xs px-3 py-1 bg-red-600 text-white rounded-full hover:bg-red-700 transition duration-150 disabled:opacity-50" onclick="stopSpeaking()" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M5 4a1 1 0 00-1 1v10a1 1 0 001 1h10a1 1 0 001-1V5a1 1 0 00-1-1H5z" />
                            </svg>
                            Hentikan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Area Pilihan Tindakan (Berada di Bawah) -->
            <div id="choices-area">
                <h3 class="text-xl font-bold mb-4 text-white">2. Pilih Tindakan SOP</h3>
                <div id="choices-container" class="space-y-3">
                    <!-- Pilihan akan di-inject di sini -->
                </div>

                <!-- Bagian Input Kustom -->
                <div class="mt-10 pt-6 border-t border-gray-700">
                    <h3 class="text-xl font-bold mb-4 text-white">3. Tindakan Kustom & Suara</h3>
                    <div class="flex flex-col space-y-3">
                        <input type="text" id="custom-action-input" placeholder="Ketikkan tindakan spesifik atau pertanyaaan di sini..." class="w-full p-3 border border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 bg-gray-800 text-white placeholder-gray-500">
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                            <button id="submit-custom-action" class="flex-1 py-3 px-6 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition duration-200 shadow-lg hover:shadow-xl" onclick="handleCustomAction()">
                                Kirim Tindakan Kustom
                            </button>
                            <button id="voice-input-button" class="flex items-center justify-center flex-1 py-3 px-6 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition duration-200 shadow-lg hover:shadow-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4z" clip-rule="evenodd" />
                                    <path fill-rule="evenodd" d="M12 8a3 3 0 10-6 0v1a2 2 0 00-2 2v2a2 2 0 002 2h4a5 5 0 005-5v-1a2 2 0 00-2-2h-1z" clip-rule="evenodd" />
                                </svg>
                                Rekam Suara
                            </button>
                        </div>
                    </div>
                    
                    <span id="voice-status" class="text-sm text-gray-500 italic mt-2 block"></span>
                </div>

            </div>

            <!-- Indikator Loading -->
            <div id="loading-indicator" class="hidden flex flex-col justify-center items-center p-8 bg-gray-900 rounded-xl shadow-lg mt-8 border border-blue-500">
                <div class="spinner border-4 border-blue-400 border-solid h-8 w-8 rounded-full"></div>
                <span class="mt-3 text-blue-400 font-semibold text-center">Game Master AI sedang memproses langkah...</span>
            </div>
        </div>

        <!-- Layar Selesai (Modal) -->
        <div id="end-screen" class="hidden fixed inset-0 bg-gray-900 bg-opacity-95 flex items-center justify-center p-4 z-50">
            <div class="bg-white p-8 rounded-2xl max-w-3xl w-full card shadow-2xl">
                <h2 class="text-3xl font-extrabold mb-4 text-center text-gray-800" id="final-title"></h2>
                
                <div class="mb-6 border-b pb-4 border-gray-300">
                    <p class="text-sm text-gray-600 text-center mb-2">Tema: <span id="review-theme" class="font-bold"></span> | Level: <span id="review-difficulty" class="font-bold"></span> | Langkah Total: <span id="review-steps" class="font-bold"></span></p>
                    
                    <div class="flex justify-center items-center my-6">
                        <div class="score-circle w-32 h-32 rounded-full flex items-center justify-center font-extrabold text-4xl shadow-xl" id="score-circle">
                            <span id="final-score">N/A</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-100 p-5 rounded-xl border border-gray-300 max-h-96 overflow-y-auto">
                    <h3 class="text-xl font-bold mb-3 text-gray-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Penilaian & Tindak Lanjut Detail
                    </h3>
                    <p class="text-gray-700 whitespace-pre-line text-sm" id="final-review-text"></p>
                </div>

                <div class="mt-8 flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <button class="py-3 px-8 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition duration-200 shadow-md hover:shadow-lg" onclick="downloadReview()">
                        Unduh Laporan (.txt)
                    </button>
                    <button class="py-3 px-8 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition duration-200 shadow-md hover:shadow-lg" onclick="resetGame()">
                        Mulai Simulasi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_KEY = "<?php echo $apiKey; ?>"; // Placeholder for Canvas environment

        let currentTheme = '';
        let currentDifficulty = 'Sedang'; // Default
        let stepCount = 0;
        let lastAction = '';

        // Global variable to hold the final review data for download
        let finalReviewData = {
            title: '',
            theme: '',
            difficulty: '',
            steps: 0,
            score: 'N/A',
            reviewText: '',
        };

        // --- TTS and Voice Recognition Setup ---
        
        let recognition = null; 
        const synth = window.speechSynthesis;
        let currentUtterance = null;

        if (window.SpeechRecognition || window.webkitSpeechRecognition) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.lang = 'id-ID'; 
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;
        }

        // --- TTS Functions ---
        
        function updateTTSButtons(isSpeaking, canStop, canReplay) {
            const stopButton = document.getElementById('tts-stop-button');
            const replayButton = document.getElementById('tts-replay-button');

            if (stopButton && replayButton) {
                stopButton.disabled = !canStop;
                // Replay is enabled only when not currently speaking and text exists
                replayButton.disabled = isSpeaking || !document.getElementById('narration-text').textContent || document.getElementById('narration-text').textContent.startsWith('Silakan tunggu');
                
                // Change stop button appearance when speaking
                stopButton.classList.toggle('bg-red-600', isSpeaking);
                stopButton.classList.toggle('bg-gray-600', !isSpeaking);
            }
        }
        
        window.stopSpeaking = function() {
            if (synth.speaking) {
                synth.cancel();
                // Manually update buttons since cancel doesn't always trigger onend
                updateTTSButtons(false, false, true); 
            }
        }

        function speakNarration(text) {
            if (!synth || text.startsWith('Silakan tunggu')) {
                return; // Do nothing if text is loading message or API not supported
            }
            
            stopSpeaking(); // Stop any current speech before starting new one
            
            currentUtterance = new SpeechSynthesisUtterance(text);
            currentUtterance.lang = 'id-ID'; 

            // --- REVISED VOICE SELECTION LOGIC ---
            const voices = synth.getVoices();
            // Prioritize a Google voice for better quality if available
            const indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google")) ||
                              voices.find(v => v.lang === 'id-ID');
            if (indoVoice) currentUtterance.voice = indoVoice;
            // ------------------------------------

            // Event handlers
            currentUtterance.onstart = () => updateTTSButtons(true, true, false);
            currentUtterance.onend = () => updateTTSButtons(false, false, true);
            currentUtterance.onerror = (event) => {
                console.error('Speech Synthesis Error:', event);
                updateTTSButtons(false, false, true); // Fallback state
            };
            
            synth.speak(currentUtterance);
        }

        window.replayNarration = function() {
            stopSpeaking(); // Ensure previous attempt is stopped
            const text = document.getElementById('narration-text').textContent;
            if (text && !text.startsWith('Silakan tunggu')) {
                speakNarration(text);
            }
        }

        // Add a listener to ensure voices are loaded for finding id-ID voice
        if (synth) {
            synth.onvoiceschanged = () => {
                // Initial check for buttons after voices load
                updateTTSButtons(false, false, true);
            };
        }


        // --- LLM API Configuration ---

        const LLM_MODEL = '<?php echo $model; ?>';
        const LLM_API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${LLM_MODEL}:generateContent?key=${API_KEY}`;

        // Schema untuk output terstruktur (MANDATORY) - DIPERBARUI
        const RESPONSE_SCHEMA = {
            type: "OBJECT",
            properties: {
                "scenario_title": { "type": "STRING", "description": "Judul singkat untuk skenario saat ini. Maksimal 6 kata." },
                "narration": { "type": "STRING", "description": "Deskripsi narasi situasi saat ini, terperinci, dan sesuai dengan alur SOP yang berkelanjutan. JANGAN menyertakan pilihan tindakan di sini." },
                "is_game_over": { "type": "BOOLEAN", "description": "True jika SOP telah selesai (Sukses) atau gagal total (Gagal). False jika permainan berlanjut." },
                "outcome_type": { "type": "STRING", "description": "Jenis hasil: 'Success', 'Failure', atau 'Continue'." },
                "feedback": { "type": "STRING", "description": "Umpan balik atau konsekuensi dari tindakan terakhir pemain (jika ini bukan langkah pertama), atau pesan sambutan/konteks awal permainan (jika ini langkah pertama)." },
                "choices": {
                    "type": "ARRAY",
                    "description": "Daftar 3-4 pilihan tindakan yang harus diambil oleh siswa sesuai dengan SOP. SELALU sertakan setidaknya 3 pilihan kecuali permainan berakhir.",
                    "items": {
                        "type": "OBJECT",
                        "properties": {
                            "action_id": { "type": "STRING", "description": "ID unik untuk tindakan (misal: 'a1', 'a2')." },
                            "description": { "type": "STRING", "description": "Deskripsi tindakan yang dapat dipilih." }
                        },
                        "propertyOrdering": ["action_id", "description"]
                    }
                },
                // PROPERTI BARU UNTUK AKHIR GAME
                "score_percentage": { "type": "INTEGER", "description": "Skor keseluruhan (0-100) berdasarkan SOP adherence, efisiensi, dan keamanan, hanya diisi jika is_game_over adalah True." },
                "detailed_review": { "type": "STRING", "description": "Penilaian rinci, termasuk ringkasan kesalahan/keberhasilan utama dan saran tindak lanjut yang konstruktif untuk perbaikan di masa depan. Hanya diisi jika is_game_over adalah True." }
            },
            propertyOrdering: ["scenario_title", "narration", "is_game_over", "outcome_type", "feedback", "choices", "score_percentage", "detailed_review"]
        };

        // System Instruction untuk memandu AI - DIPERBARUI
        const SYSTEM_INSTRUCTION = (theme, difficulty, lastAction) => {
            let difficultyGuidance = '';
            
            if (difficulty === 'Mudah') {
                difficultyGuidance = 'Level: MUDAH. Pastikan pilihan yang benar relatif jelas. Konsekuensi kesalahan tidak terlalu fatal, tetapi tetap mendidik.';
            } else if (difficulty === 'Sedang') {
                difficultyGuidance = 'Level: SEDANG. Berikan keseimbangan antara pilihan benar dan jebakan yang masuk akal. Konsekuensi harus mencakup faktor keamanan dan prosedur standar.';
            } else if (difficulty === 'Sulit') {
                difficultyGuidance = 'Level: SULIT. Pilihan harus sangat spesifik dan teknis. Banyak pilihan yang tampaknya benar tetapi salah dalam detail (misal: urutan atau alat yang salah). Kesalahan kecil dapat mengakibatkan kegagalan total (Failure) karena faktor efisiensi/biaya/keamanan.';
            }
            
            let finalInstruction = '';
            if (lastAction.includes("FINAL_EVALUATION")) {
                 finalInstruction = "Karena is_game_over adalah True, Anda HARUS mengisi properti 'score_percentage' (0-100) dan 'detailed_review' dengan penilaian rinci, termasuk ringkasan performa dan saran tindak lanjut spesifik. Pastikan review formatnya rapi dan mudah dibaca (gunakan line breaks/paragraf).";
            }

            return `Anda adalah 'Game Master SOP' yang membuat skenario simulasi SOP berbasis proyek untuk siswa SMK (Sekolah Menengah Kejuruan).
            Tema Proyek: ${theme}.
            ${difficultyGuidance}
            Tujuan Anda: Memandu pemain melalui serangkaian langkah SOP yang logis, realistis, dan berkesinambungan.
            Tugas:
            1. Buat narasi situasi saat ini ('narration') sebagai kelanjutan dari langkah sebelumnya.
            2. Berikan 'feedback' atas 'Tindakan Terakhir' pemain (${lastAction ? lastAction : 'LANGKAH AWAL'}). Umpan balik harus bersifat mendidik dan menjelaskan mengapa tindakan itu Benar/Salah/Netral.
            3. Tawarkan 3-4 'choices' (pilihan tindakan) yang realistis. Satu pilihan harus merupakan langkah SOP yang benar, dan yang lainnya harus berupa kesalahan umum, penyimpangan SOP, atau langkah yang tidak tepat.
            4. Tetapkan 'is_game_over' ke True hanya jika SOP selesai (Success) atau jika pemain melakukan kesalahan fatal (Failure).
            5. Gunakan bahasa Indonesia yang formal, teknis, dan mendidik. Jaga agar alur cerita SOP tetap fokus pada satu proses proyek yang utuh.
            6. JANGAN ulangi pilihan yang salah di langkah berikutnya, buat pilihan baru yang relevan dengan situasi saat ini.
            ${finalInstruction}`;
        };


        // --- Utility Functions ---

        function showLoading(isLoading) {
            document.getElementById('loading-indicator').classList.toggle('hidden', !isLoading);
            document.getElementById('choices-container').classList.toggle('hidden', isLoading);
            document.getElementById('start-button').disabled = isLoading;
            document.querySelectorAll('.choice-button').forEach(btn => btn.disabled = isLoading);
            
            // Kontrol input kustom saat loading
            const customInput = document.getElementById('custom-action-input');
            const submitButton = document.getElementById('submit-custom-action');
            const voiceButton = document.getElementById('voice-input-button');
            if (customInput && submitButton && voiceButton) {
                customInput.disabled = isLoading;
                submitButton.disabled = isLoading;
                voiceButton.disabled = isLoading;
            }

            // Stop TTS when loading new step
            if (isLoading) {
                stopSpeaking();
            }
        }

        function showFeedback(text, type) {
            const area = document.getElementById('feedback-area');
            area.textContent = text;
            area.classList.remove('hidden', 'feedback-success', 'feedback-failure', 'feedback-continue');

            let emoji = '⚙️';
            if (type === 'Success') {
                area.classList.add('feedback-success');
                emoji = '✅';
            } else if (type === 'Failure') {
                area.classList.add('feedback-failure');
                emoji = '❌';
            } else { // Continue or first step
                area.classList.add('feedback-continue');
                emoji = '⚠️';
            }
            area.textContent = `${emoji} ${text}`;
            area.classList.remove('hidden');
        }

        async function fetchGemini(userPrompt, maxRetries = 5) {
            // Check if this is the final evaluation step
            const isFinalEval = userPrompt.includes("FINAL_EVALUATION");
            const systemPrompt = SYSTEM_INSTRUCTION(currentTheme, currentDifficulty, isFinalEval ? userPrompt : lastAction);

            const payload = {
                contents: [{ parts: [{ text: userPrompt }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: RESPONSE_SCHEMA
                }
            };

            for (let attempt = 0; attempt < maxRetries; attempt++) {
                try {
                    const response = await fetch(LLM_API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 429) {
                        const delay = Math.pow(2, attempt) * 1000;
                        console.warn(`Rate limit exceeded. Retrying in ${delay / 1000}s...`);
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    }

                    if (!response.ok) {
                        const errorBody = await response.text();
                        throw new Error(`HTTP error! Status: ${response.status}. Body: ${errorBody.substring(0, 100)}...`);
                    }

                    const result = await response.json();
                    const jsonString = result.candidates?.[0]?.content?.parts?.[0]?.text;

                    if (!jsonString) {
                        throw new Error("Invalid response format from Gemini API: JSON string missing.");
                    }

                    // Attempt to parse JSON and return
                    return JSON.parse(jsonString);

                } catch (error) {
                    console.error(`Attempt ${attempt + 1} failed:`, error.message);
                    if (attempt === maxRetries - 1) throw new Error("Gagal terhubung dengan Game Master AI setelah beberapa kali percobaan.");
                    const delay = Math.pow(2, attempt) * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
        }

        // --- Custom Input & Voice Logic ---

        // Fungsi untuk menangani pengiriman input teks kustom
        window.handleCustomAction = function() {
            stopSpeaking();
            const input = document.getElementById('custom-action-input');
            const voiceStatus = document.getElementById('voice-status');
            const action = input.value.trim();

            if (action.length > 5) {
                input.value = '';
                voiceStatus.textContent = '';
                handleChoice(action);
            } else {
                voiceStatus.textContent = 'Tindakan kustom minimal 5 karakter.';
                voiceStatus.classList.remove('text-gray-500');
                voiceStatus.classList.add('text-red-400');
                setTimeout(() => {
                    voiceStatus.textContent = '';
                    voiceStatus.classList.add('text-gray-500');
                    voiceStatus.classList.remove('text-red-400');
                }, 3000);
            }
        }

        // Fungsi untuk memulai input suara
        window.startVoiceInput = function() {
            if (!recognition) {
                document.getElementById('voice-status').textContent = 'ERROR: API Rekognisi Suara tidak didukung di browser ini.';
                document.getElementById('voice-status').classList.remove('text-gray-500');
                document.getElementById('voice-status').classList.add('text-red-400');
                return;
            }
            stopSpeaking();

            const voiceStatus = document.getElementById('voice-status');
            const customInput = document.getElementById('custom-action-input');
            const submitButton = document.getElementById('submit-custom-action');
            const voiceButton = document.getElementById('voice-input-button');
            
            // Nonaktifkan tombol lain saat mendengarkan
            submitButton.disabled = true;
            voiceButton.disabled = true;
            voiceButton.classList.add('animate-pulse'); // Visual feedback
            
            voiceStatus.textContent = '🎤 Mendengarkan... Silakan Bicara. (Stop otomatis saat hening)';
            voiceStatus.classList.remove('text-gray-500', 'text-red-400');
            voiceStatus.classList.add('text-green-400');

            customInput.value = '';

            recognition.onresult = (event) => {
                const result = event.results[0][0].transcript;
                customInput.value = result;
                voiceStatus.textContent = `📝 Teks Dikenali: "${result}"`;
            };

            recognition.onerror = (event) => {
                voiceStatus.textContent = `❌ ERROR: ${event.error}. Coba lagi.`;
                voiceStatus.classList.remove('text-green-400');
                voiceStatus.classList.add('text-red-400');
                submitButton.disabled = false;
                voiceButton.disabled = false;
                voiceButton.classList.remove('animate-pulse');
            };

            // Logika Auto-Submit saat rekognisi berakhir
            recognition.onend = () => {
                const recognizedText = customInput.value.trim();
                submitButton.disabled = false;
                voiceButton.disabled = false;
                voiceButton.classList.remove('animate-pulse');

                if (recognizedText.length > 5) {
                    voiceStatus.textContent = '🧠 Menganalisis tindakan...';
                    voiceStatus.classList.remove('text-green-400');
                    voiceStatus.classList.add('text-blue-400');
                    handleChoice(recognizedText);
                } else if (recognizedText.length > 0) {
                    voiceStatus.textContent = 'Tindakan terlalu pendek. Ketik atau coba bicara lagi.';
                    voiceStatus.classList.remove('text-green-400');
                    voiceStatus.classList.add('text-red-400');
                } else {
                    voiceStatus.textContent = 'Tidak ada suara yang terdeteksi.';
                    voiceStatus.classList.remove('text-green-400', 'text-red-400', 'text-blue-400');
                    voiceStatus.classList.add('text-gray-500');
                }
            };

            try {
                recognition.start();
            } catch (e) {
                voiceStatus.textContent = '❌ ERROR: Gagal memulai rekognisi suara. Coba lagi.';
                voiceStatus.classList.remove('text-green-400');
                voiceStatus.classList.add('text-red-400');
                submitButton.disabled = false;
                voiceButton.disabled = false;
                voiceButton.classList.remove('animate-pulse');
                console.error("Speech Recognition Start Error:", e);
            }
        }

        // --- Game Logic ---

        window.startGame = async function() {
            const selectedTheme = document.getElementById('theme-select').value;
            const customTheme = document.getElementById('custom-theme-input').value.trim();
            
            currentTheme = customTheme || selectedTheme;
            currentDifficulty = document.getElementById('difficulty-select').value;

            if (!currentTheme) {
                document.getElementById('auth-status').textContent = 'PERHATIAN: Pilih level kesulitan dan tema (dari daftar atau kustom) untuk memulai.';
                document.getElementById('auth-status').classList.remove('text-gray-500');
                document.getElementById('auth-status').classList.add('text-red-400', 'font-semibold');
                return;
            }
            
            document.getElementById('auth-status').textContent = 'Mode Lokal: Tidak memerlukan koneksi database.';
            document.getElementById('auth-status').classList.remove('text-red-400', 'font-semibold');
            document.getElementById('auth-status').classList.add('text-gray-500');

            // Reset game state
            stepCount = 0;
            lastAction = '';
            document.getElementById('current-theme').textContent = currentTheme;
            document.getElementById('current-difficulty').textContent = currentDifficulty; // Update Difficulty Display
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('feedback-area').classList.add('hidden');
            document.getElementById('narration-text').innerHTML = 'Silakan tunggu, Game Master AI sedang menyusun langkah SOP pertama Anda...';
            document.getElementById('choices-container').innerHTML = '';
            document.getElementById('end-screen').classList.add('hidden');
            document.getElementById('voice-status').textContent = ''; 
            document.getElementById('step-counter').textContent = stepCount;
            finalReviewData = {}; // Reset review data

            // Initialize TTS buttons state
            updateTTSButtons(false, false, false); 

            // Start the first step
            await generateNextStep(`Mulai simulasi SOP proyek: ${currentTheme} dengan Level Kesulitan ${currentDifficulty}.`);
        }

        async function generateNextStep(playerPrompt) {
            showLoading(true);
            try {
                const gameData = await fetchGemini(playerPrompt);
                showLoading(false);
                stopSpeaking(); // Ensure previous speech is cancelled before new one starts

                // Update UI with new scenario
                document.getElementById('scenario-title').textContent = gameData.scenario_title;
                document.getElementById('narration-text').textContent = gameData.narration;
                
                // NEW: Start reading the narration
                speakNarration(gameData.narration);

                // Update Feedback Area (using previous step's feedback)
                if (stepCount > 0) {
                    showFeedback(gameData.feedback, gameData.outcome_type);
                } else {
                    showFeedback(gameData.feedback, 'Continue'); 
                }

                stepCount++;
                document.getElementById('step-counter').textContent = stepCount;
                lastAction = playerPrompt.replace("Tindakan Terakhir Saya: ", ""); // Store the full action for feedback context

                if (gameData.is_game_over) {
                    // Jika game over, kita perlu melakukan evaluasi final jika skor/review belum ada
                    if (!gameData.detailed_review) {
                        // Trigger final evaluation round with LLM
                        await generateFinalReview(gameData);
                    } else {
                        // Game Data already contains the review (from the final evaluation call)
                        endGame(gameData);
                    }
                } else {
                    // Render Choices
                    renderChoices(gameData.choices);
                }

            } catch (error) {
                showLoading(false);
                document.getElementById('narration-text').textContent = `[ERROR]: ${error.message}. Coba periksa koneksi internet Anda atau mulai ulang simulasi.`;
                document.getElementById('choices-container').innerHTML = '<p class="text-center text-red-400 p-4 bg-gray-900 rounded-xl">Simulasi terhenti karena kesalahan. Silakan mulai ulang.</p>';
                console.error("Game Master AI Error:", error);
            }
        }

        async function generateFinalReview(lastGameData) {
            showLoading(true);
            try {
                // Prompt LLM specifically for final review data
                const reviewPrompt = `FINAL_EVALUATION: Permainan berakhir dengan status: ${lastGameData.outcome_type}. Narasi Akhir: ${lastGameData.narration}. Umpan Balik Akhir: ${lastGameData.feedback}. Berikan skor (0-100) dan review rinci (detailed_review).`;
                
                const finalGameData = await fetchGemini(reviewPrompt);
                
                // Combine the last narrative data with the final review data
                const fullGameData = {
                    ...lastGameData,
                    ...finalGameData
                };
                
                showLoading(false);
                endGame(fullGameData);

            } catch (error) {
                 showLoading(false);
                 document.getElementById('narration-text').textContent = `[ERROR FINAL REVIEW]: Gagal mendapatkan penilaian akhir. ${error.message}.`;
                 console.error("Game Master AI Final Review Error:", error);
            }
        }


        function renderChoices(choices) {
            const container = document.getElementById('choices-container');
            container.innerHTML = ''; // Clear previous choices
            if (choices && choices.length > 0) {
                choices.forEach(choice => {
                    const button = document.createElement('button');
                    // Tambahkan kelas responsif untuk tombol pilihan
                    button.className = 'choice-button w-full text-left p-4 rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500 transition duration-150 text-base sm:text-lg';
                    button.textContent = choice.description;
                    button.onclick = () => {
                        stopSpeaking(); // Stop speech immediately upon choice
                        handleChoice(choice.description);
                    };
                    container.appendChild(button);
                });
            } else {
                 container.innerHTML = '<p class="text-center text-gray-400 p-4 bg-gray-800 rounded-xl">Tidak ada pilihan tersedia. Game Master AI mungkin sedang memproses akhir simulasi.</p>';
            }
        }

        window.handleChoice = async function(actionDescription) {
            stopSpeaking();
            document.getElementById('narration-text').textContent = `Anda memilih: "${actionDescription}". Game Master AI sedang mengevaluasi dan menentukan langkah SOP berikutnya...`;
            document.getElementById('choices-container').innerHTML = ''; // Clear choices while processing
            // Clear custom input field after selection/submission
            document.getElementById('custom-action-input').value = '';
            document.getElementById('voice-status').textContent = ''; 

            await generateNextStep(`Tindakan Terakhir Saya: ${actionDescription}.`);
        }

        function endGame(gameData) {
            stopSpeaking();
            const { outcome_type, score_percentage, detailed_review } = gameData;
            
            const titleEl = document.getElementById('final-title');
            const scoreEl = document.getElementById('final-score');
            const reviewEl = document.getElementById('final-review-text');
            const endScreenEl = document.getElementById('end-screen');
            const scoreCircleEl = document.getElementById('score-circle');

            // Set Meta Data
            document.getElementById('review-theme').textContent = currentTheme;
            document.getElementById('review-difficulty').textContent = currentDifficulty;
            document.getElementById('review-steps').textContent = stepCount;

            // Set Title and Score Appearance
            scoreCircleEl.classList.remove('score-success', 'score-failure');
            if (outcome_type === 'Success') {
                titleEl.textContent = 'SIMULASI BERHASIL! SOP Tuntas dengan Baik!';
                titleEl.classList.remove('text-red-600');
                titleEl.classList.add('text-green-600');
                scoreCircleEl.classList.add('score-success');
            } else {
                titleEl.textContent = 'SIMULASI GAGAL! Proyek Terhenti.';
                titleEl.classList.remove('text-green-600');
                titleEl.classList.add('text-red-600');
                scoreCircleEl.classList.add('score-failure');
            }

            // Display Score and Review
            const score = score_percentage !== undefined ? `${score_percentage}%` : 'N/A';
            scoreEl.textContent = score;
            reviewEl.textContent = detailed_review || "Tidak ada review detail yang tersedia dari AI.";

            // Store data for download
            finalReviewData = {
                title: titleEl.textContent,
                theme: currentTheme,
                difficulty: currentDifficulty,
                steps: stepCount,
                score: score,
                reviewText: detailed_review,
            };

            endScreenEl.classList.remove('hidden');
        }

        // Fungsi untuk mengunduh review
        window.downloadReview = function() {
            const data = finalReviewData;
            const reviewContent = 
`===== LAPORAN SIMULASI SOP PROYEK SMK =====
Judul Hasil: ${data.title}
---------------------------------------------
Tema Proyek: ${data.theme}
Level Kesulitan: ${data.difficulty}
Langkah Total: ${data.steps}
Skor Adherensi SOP: ${data.score}
---------------------------------------------

PENILAIAN DETAIL DARI GAME MASTER AI
====================================
${data.reviewText || "Review tidak tersedia."}

====================================
Laporan ini dibuat secara otomatis oleh Game Master AI.
Tanggal: ${new Date().toLocaleString('id-ID')}
`;

            const blob = new Blob([reviewContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `SOP_Review_${data.theme.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toLocaleDateString('id-ID').replace(/\//g, '-')}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }


        window.resetGame = function() {
            stopSpeaking();
            document.getElementById('end-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.add('hidden');
            document.getElementById('setup-screen').classList.remove('hidden');
            document.getElementById('theme-select').value = '';
            document.getElementById('custom-theme-input').value = '';
            currentTheme = '';
            currentDifficulty = 'Sedang';
            stepCount = 0;
            lastAction = '';
            document.getElementById('voice-status').textContent = ''; 
            updateTTSButtons(false, false, false);
            finalReviewData = {};
        }

        // Initialize display for local mode and voice button click handler
        window.onload = () => {
             document.getElementById('start-button').disabled = false;
             const voiceButton = document.getElementById('voice-input-button');
             if(voiceButton && recognition) {
                voiceButton.onclick = startVoiceInput;
                document.getElementById('voice-status').textContent = 'Fitur suara siap digunakan.';
             } else if (voiceButton) {
                voiceButton.disabled = true;
                voiceButton.textContent = 'Suara Tdk Didukung';
                document.getElementById('voice-status').textContent = 'Fitur pengenalan suara tidak didukung oleh browser Anda.';
             }
             
             // Initial check for TTS buttons state
             updateTTSButtons(false, false, false);
        };
        
        // ** Memastikan TTS dihentikan saat meninggalkan/memuat ulang halaman **
        window.addEventListener('beforeunload', stopSpeaking);

    </script>
</body>
</html>
