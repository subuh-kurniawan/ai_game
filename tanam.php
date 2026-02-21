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
    <title>Simulasi Petani Pintar Berbasis AI</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
        }
        .progress-bar-container {
            height: 12px;
            background-color: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            transition: width 0.5s ease-in-out;
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Custom styles for action buttons */
        .action-btn {
            position: relative;
        }
        .action-btn:disabled {
            cursor: not-allowed;
            opacity: 0.6;
            filter: grayscale(0.5);
        }
        .action-btn:disabled::after {
            content: attr(title);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            white-space: nowrap;
            font-size: 0.7rem;
            opacity: 0; /* Hidden by default, will be controlled by title attribute */
            pointer-events: none;
            transition: opacity 0.2s;
        }
    </style>
</head>
<body class="p-4 md:p-8 min-h-screen bg-green-50">

    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-xl p-6 md:p-10 border-t-4 border-green-600">
         <header 
    class="relative text-center text-white py-16 md:py-20 rounded-xl mb-10 overflow-hidden"
    style="background-image: url('../admin/foto/<?= $data['banner'] ?>'); background-size: cover; background-position: center; min-height: 280px;">

    <!-- Overlay lembut -->
    <div class="absolute inset-0 bg-primary/60 backdrop-blur-[1px]"></div>

    <!-- Logo di pojok kiri atas -->
    <div class="absolute top-4 left-4 z-20 flex items-center gap-2">
        <img src="../admin/foto/<?= $data['logo'] ?>" 
             alt="Logo Sekolah" 
             class="w-14 h-14 md:w-16 md:h-16 rounded-lg shadow-md border border-white/30 bg-white/10 backdrop-blur-[2px] p-1">
        <span class="hidden md:block font-semibold text-white drop-shadow-md"><?= $data['nama'] ?></span>
    </div>

    <!-- Konten Header -->
    <div class="relative z-10">
        
    </div>
</header>
        <h1 class="text-3xl font-bold text-center mb-6 text-green-700">🌱 Simulasi Petani Pintar (AI GM) 🧑‍🌾</h1>
        <p class="text-center text-sm text-gray-500 mb-8">Setiap tindakan Anda disimulasikan dan dinarasikan oleh AI Game Master.</p>

        <!-- Area Pengaturan Awal -->
        <div id="setup-area" class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 border-b pb-2 mb-4">Pengaturan Awal Budidaya</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Theme Select -->
                <div>
                    <label for="theme-select" class="block text-sm font-medium text-gray-700">Pilih Jenis Tanaman (Tema)</label>
                    <select id="theme-select" onchange="handleThemeChange()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md shadow-sm">
                        <option value="Padi">Padi (Tanah - Butuh air)</option>
                        <option value="Tomat">Tomat (Tanah - Sensitif kelembaban)</option>
                        <option value="Cabai">Cabai (Tanah - Suka panas)</option>
                        <option value="Custom">Custom (Input Nama Tanaman)</option>
                    </select>
                </div>
                
                <!-- Difficulty Select -->
                <div>
                    <label for="difficulty-select" class="block text-sm font-medium text-gray-700">Pilih Tingkat Kesulitan</label>
                    <select id="difficulty-select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md shadow-sm">
                        <option value="Sedang">Sedang (Ancaman standar)</option>
                        <option value="Mudah">Mudah (Ancaman rendah, modal lebih)</option>
                        <option value="Sulit">Sulit (Ancaman tinggi, modal terbatas)</option>
                    </select>
                </div>
            </div>

            <!-- Custom Input Area -->
            <div id="custom-input-div" class="w-full" style="display:none;">
                <label for="custom-plant-name" class="block text-sm font-medium text-gray-700">Nama Tanaman Custom (Sertakan "Hidroponik" untuk sistem non-tanah)</label>
                <input type="text" id="custom-plant-name" placeholder="Contoh: Selada Hidroponik" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
            </div>

            <button onclick="startGame()" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg shadow-lg transition duration-150 ease-in-out disabled:opacity-50" id="start-button">
                Mulai Simulasi!
            </button>
            <p id="setup-message" class="text-red-500 text-sm mt-2 hidden">Pilih tanaman atau masukkan nama custom.</p>
        </div>

        <!-- Area Permainan Utama -->
        <div id="game-area" class="space-y-6" style="display:none;">
            
            <!-- Status Harian -->
            <div class="bg-yellow-100 p-4 rounded-lg border-l-4 border-yellow-500 shadow-md">
                <h2 class="text-xl font-bold text-yellow-700">Status Hari ke-<span id="day-counter">1</span></h2>
                <p class="text-sm text-yellow-600 mt-1">Tanaman: <span id="plant-theme" class="font-semibold"></span> | Tahap: <span id="plant-stage" class="font-semibold">Benih</span></p>
            </div>

            <!-- Panel Kesehatan dan Kondisi (Dinamis) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-green-600">Kesehatan Tanaman</h3>
                    
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-500">Kesehatan (Health)</label>
                        <div class="progress-bar-container">
                            <div id="health-bar" class="progress-bar bg-red-500" style="width: 100%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 text-right mt-1"><span id="health-value">100</span>%</p>
                    </div>

                    <!-- METRIC 1 (Kelembaban / pH) -->
                    <div class="mb-3">
                        <label id="metric-1-label" class="block text-xs font-medium text-gray-500"></label>
                        <div class="progress-bar-container">
                            <div id="metric-1-bar" class="progress-bar bg-blue-500"></div>
                        </div>
                        <p class="text-xs text-gray-500 text-right mt-1"><span id="metric-1-value"></span></p>
                    </div>

                    <!-- METRIC 2 (Nutrisi / EC) -->
                    <div class="mb-3">
                        <label id="metric-2-label" class="block text-xs font-medium text-gray-500"></label>
                        <div class="progress-bar-container">
                            <div id="metric-2-bar" class="progress-bar bg-orange-500"></div>
                        </div>
                        <p class="text-xs text-gray-500 text-right mt-1"><span id="metric-2-value"></span></p>
                    </div>

                    <!-- METRIC 3 (Suhu Udara / Suhu Air) -->
                    <div>
                        <label id="metric-3-label" class="block text-xs font-medium text-gray-500"></label>
                        <div class="progress-bar-container">
                            <div id="metric-3-bar" class="progress-bar bg-red-500"></div>
                        </div>
                        <p class="text-xs text-gray-500 text-right mt-1"><span id="metric-3-value"></span></p>
                    </div>

                </div>

                <div class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-red-600">Risiko & Inventaris</h3>
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500">Tingkat Hama/Penyakit</label>
                        <div class="progress-bar-container">
                            <div id="pest-bar" class="progress-bar bg-purple-500"></div>
                        </div>
                        <p class="text-xs text-gray-500 text-right mt-1"><span id="pest-value">0</span>%</p>
                    </div>
                    
                    <h3 class="text-md font-semibold mb-2 text-gray-700">Inventaris</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>💰 Modal: <span id="inv-money" class="font-bold text-gray-700">100</span></li>
                        <li>💧 Air: <span id="inv-water" class="font-bold text-blue-500">5</span> unit</li>
                        <li>🧪 Pupuk: <span id="inv-fertilizer" class="font-bold text-orange-500">2</span> unit</li>
                        <li>🦠 Pestisida: <span id="inv-pesticide" class="font-bold text-purple-500">1</span> unit</li>
                    </ul>
                </div>
            </div>

            <!-- Narasi dan Aksi -->
            <div class="bg-gray-100 p-5 rounded-lg shadow-inner">
                <h3 class="text-xl font-bold mb-3 text-gray-800">Laporan Game Master (GM)</h3>
                <p id="narration-text" class="text-gray-700 leading-relaxed min-h-[5rem] border-l-2 border-green-500 pl-3"></p>
                <div class="flex space-x-3 mt-4">
                    <button onclick="playNarration()" id="play-button" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150 flex items-center disabled:opacity-50" disabled>
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v4a1 1 0 11-2 0V8z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        <span id="play-btn-text">Putar Narasi</span>
                    </button>
                    <button onclick="stopNarration()" id="stop-button" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150 flex items-center disabled:opacity-50" disabled>
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8 7a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1zm4 0a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        Jeda/Hentikan
                    </button>
                </div>
                <div id="loading-indicator" class="mt-4 text-center text-green-600 font-semibold hidden">
                    <span class="spinner mr-2"></span> AI sedang menghitung hasil...
                </div>
            </div>

            <!-- OPSI TINDAKAN DINAMIS (UPDATED) -->
            <div class="space-y-4">
                <h3 class="text-xl font-bold text-gray-800">Opsi Tindakan Petani</h3>
                <div id="action-buttons-container">
                    <!-- Dynamic Buttons will be rendered here -->
                    <div id="dynamic-action-buttons" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <!-- Buttons injected here by JS -->
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <input type="text" id="custom-action-input" placeholder="Tindakan Custom (Contoh: Beli Pupuk/Cek pH Tanah) - Biaya 💰5" class="flex-1 shadow-sm text-sm border-gray-300 rounded-md p-3">
                        <button onclick="applyAction('Custom')" class="action-btn bg-gray-700 hover:bg-gray-800 text-white font-semibold py-3 px-4 rounded-lg shadow-md transition duration-150 w-full sm:w-auto text-sm md:text-base" id="btn-custom">Lakukan Tindakan Custom (💰5)</button>
                    </div>
                </div>
            </div>

            <!-- Log Aktivitas -->
            <div class="bg-white p-4 rounded-lg border border-gray-300 shadow-inner">
                <h3 class="text-lg font-semibold mb-2 text-gray-800">Log Aktivitas</h3>
                <div id="log-output" class="text-sm text-gray-600 h-32 overflow-y-auto space-y-1">
                    <!-- Log akan muncul di sini -->
                </div>
            </div>
            
            <!-- Game Message Box (for alerts) -->
            <div id="game-message-box" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display:none;">
                <div class="bg-white p-8 rounded-lg shadow-2xl max-w-sm w-full text-center">
                    <h4 id="msg-title" class="text-xl font-bold mb-3 text-red-600">Pesan Game</h4>
                    <p id="msg-text" class="mb-5 text-gray-700"></p>
                    <div id="msg-actions" class="flex justify-center space-x-4">
                        <!-- Tombol akan di-inject di sini oleh JS -->
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // --- DEFINISI BIAYA AKSI (BARU, DITAMBAH UNTUK HIDROPONIK) ---
        const ACTION_DEFS = {
            // SOIL ACTIONS
            'Siram': { cost: { water: 1, money: 0 }, label: "💧 Siram (Air: 1)", id: "btn-siriam", color: "blue" },
            'Pupuk': { cost: { fertilizer: 1, money: 5 }, label: "🧪 Beri Pupuk (Pupuk: 1, 💰5)", id: "btn-pupuk", color: "orange" },
            
            // HYDROPONIC ACTIONS
            'AdjustSolution': { cost: { money: 10 }, label: "💧 Cek/Atur Larutan (💰10)", id: "btn-adjust-sol", color: "teal" },
            'FlushAndRefill': { cost: { money: 25 }, label: "💦 Refill Nutrisi Baru (💰25)", id: "btn-flush", color: "indigo" },

            // COMMON ACTIONS
            'Pestisida': { cost: { pesticide: 1, money: 10 }, label: "🦠 Semprot Hama (Pestisida: 1, 💰10)", id: "btn-pestisida", color: "purple" },
            'Mitigasi': { cost: { money: 15 }, label: "🚨 Mitigasi Khusus", id: "btn-mitigasi", color: "red" }, 
            'Custom': { cost: { money: 5 }, label: "Lakukan Tindakan Custom (💰5)", id: "btn-custom", color: "gray" }
        };

        // --- DEFINISI METRIK LINGKUNGAN DINAMIS ---
        const ENVIRONMENT_METRICS = {
            'Soil': {
                metric1: { key: 'soilMoisture', name: 'Kelembaban Tanah', unit: '%', color: 'blue', default: 50 },
                metric2: { key: 'nutrientLevel', name: 'Tingkat Nutrisi', unit: '%', color: 'orange', default: 50 },
                metric3: { key: 'tempAir', name: 'Suhu Udara', unit: '°C', color: 'red', default: 25 }, 
            },
            'Hydroponic': {
                metric1: { key: 'pH', name: 'pH Larutan Nutrisi', unit: '', color: 'purple', default: 6.0, decimals: 1 },
                metric2: { key: 'EC', name: 'Konduktivitas Listrik (EC)', unit: 'mS/cm', color: 'green', default: 1.8, decimals: 1 },
                metric3: { key: 'tempWater', name: 'Suhu Air Larutan', unit: '°C', color: 'blue', default: 22, decimals: 1 },
            }
        };

        // Data Spesifikasi Tanaman
        const plantSpecs = {
            "Padi": {
                environmentType: 'Soil',
                optimalMoisture: [60, 85], optimalNutrient: [50, 80], optimalTempAir: [22, 30],
                pestSensitivity: 1.2,
                stages: { "Benih": 1, "Anakan": 6, "Berbunga": 12, "Panen": 20 },
                mitigationSOP: "Kontrol Hama Wereng (Fokus mengurangi hama)",
                narrationStart: "Anda mulai menanam Padi di sawah yang telah disiapkan. Jaga air tetap tinggi dan nutrisi mencukupi! Pilih tindakan pertama Anda untuk memulai Hari ke-1."
            },
            "Tomat": {
                environmentType: 'Soil',
                optimalMoisture: [40, 70], optimalNutrient: [40, 75], optimalTempAir: [20, 28],
                pestSensitivity: 1.0,
                stages: { "Benih": 1, "Vegetatif": 5, "Berbuah Hijau": 10, "Panen": 15 },
                mitigationSOP: "Pemasangan Ajir/Penyulaman (Fokus kesehatan)",
                narrationStart: "Anda menanam bibit Tomat di lahan kebun. Kelembaban sedang adalah kunci, hindari tanah becek. Pilih tindakan pertama Anda untuk memulai Hari ke-1."
            },
            "Cabai": {
                environmentType: 'Soil',
                optimalMoisture: [30, 60], optimalNutrient: [60, 90], optimalTempAir: [25, 33],
                pestSensitivity: 1.5,
                stages: { "Benih": 1, "Tunas": 5, "Berbunga": 10, "Panen": 18 },
                mitigationSOP: "Pencegahan Virus Kuning (Fokus mengurangi hama)",
                narrationStart: "Tanaman Cabai Anda siap tumbuh. Tanaman ini suka tanah yang tidak terlalu basah dan nutrisi yang kuat. Pilih tindakan pertama Anda untuk memulai Hari ke-1."
            },
            "HydroponicDefault": { // Template untuk Custom Hydroponic
                environmentType: 'Hydroponic',
                optimalpH: [5.8, 6.2], optimalEC: [1.5, 2.2], optimalTempWater: [20, 24],
                pestSensitivity: 1.1,
                stages: { "Benih": 1, "Vegetatif": 5, "Panen": 15 },
                mitigationSOP: "Cek Kualitas Larutan Nutrisi & pH (Fokus lingkungan)",
                narrationStart: "Anda memulai budidaya sistem non-tanah. Fokus utama adalah menjaga pH, EC, dan suhu air tetap stabil."
            }
        };

        // Pengaturan Tingkat Kesulitan (Tetap)
        const difficultySettings = {
            "Mudah": {
                pestRate: 0.8, moistureLoss: 0.8, healthPenalty: 0.7, moneyStart: 150,
                description: "Ancaman Hama/Cuaca rendah, sumber daya lebih banyak.", weatherVolatility: "Rendah"
            },
            "Sedang": {
                pestRate: 1.0, moistureLoss: 1.0, healthPenalty: 1.0, moneyStart: 100,
                description: "Ancaman standar, kondisi realistis.", weatherVolatility: "Sedang"
            },
            "Sulit": {
                pestRate: 1.4, moistureLoss: 1.3, healthPenalty: 1.5, moneyStart: 75,
                description: "Ancaman Hama/Cuaca tinggi, sumber daya terbatas.", weatherVolatility: "Tinggi"
            }
        };

        const MAX_DAYS = 25;
        let gameStatus = {};
        const MAX_RETRIES = 3;

        // --- TTS and Audio Control Variables (NATIVE WEB API) ---
        const synth = window.speechSynthesis;
        let utterance = null;
        let isSpeaking = false; 
        let availableVoices = [];

        // Konfigurasi Gemini API (Untuk simulasi teks)
     const GEMINI_MODEL = "<?php echo $model; ?>";
        const API_KEY = "<?php echo $apiKey; ?>"; // Diisi oleh canvas saat runtime
        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${API_KEY}`;
        
        // --- NATIVE TTS FUNCTION (Tidak berubah) ---

        function populateVoices() {
            availableVoices = synth.getVoices();
        }

        if (synth) {
            populateVoices();
            if (synth.onvoiceschanged !== undefined) {
                synth.onvoiceschanged = populateVoices;
            }
        }
        
        function prepareNarration(narrationText) {
            stopNarration(true);
            
            if (!synth) {
                console.error("Web Speech API not supported.");
                return;
            }

            utterance = new SpeechSynthesisUtterance(narrationText);
            utterance.lang = 'id-ID'; 
            
            if (availableVoices.length > 0) {
                let idVoice = availableVoices.find(voice => voice.lang === 'id-ID' || voice.lang.startsWith('id-'));
                if (idVoice) {
                    utterance.voice = idVoice;
                } else {
                    console.warn("Suara Bahasa Indonesia tidak ditemukan secara eksplisit. Menggunakan fallback browser.");
                }
            } 
            
            utterance.onstart = () => {
                isSpeaking = true;
                document.getElementById('play-button').disabled = true;
                document.getElementById('stop-button').disabled = false;
                document.getElementById('play-btn-text').textContent = 'Memutar...';
            };

            utterance.onend = () => {
                isSpeaking = false;
                document.getElementById('play-button').disabled = false;
                document.getElementById('stop-button').disabled = true;
                document.getElementById('play-btn-text').textContent = 'Putar Narasi';
            };

            utterance.onerror = (event) => {
                isSpeaking = false;
                console.error('SpeechSynthesisUtterance.onerror:', event);
                document.getElementById('play-btn-text').textContent = 'Gagal TTS';
                document.getElementById('play-button').disabled = false;
                document.getElementById('stop-button').disabled = true;
            };

            document.getElementById('play-button').disabled = false;
            document.getElementById('play-btn-text').textContent = 'Putar Narasi';
        }

        function playNarration() {
            if (!utterance || isSpeaking) return;
            if (synth.speaking) {
                synth.cancel();
            }
            synth.speak(utterance);
        }

        function stopNarration(isInternalStop = false) {
            if (synth.speaking) {
                synth.cancel();
            }

            if (!isInternalStop) {
                isSpeaking = false;
                document.getElementById('play-button').disabled = false;
                document.getElementById('stop-button').disabled = true;
                document.getElementById('play-btn-text').textContent = 'Putar Narasi';
            }
        }
        
        // --- UI HELPER FUNCTIONS ---

        function setActionButtonsDisabled(disabled) {
            // Disables the entire block temporarily while waiting for AI response
            document.querySelectorAll('#action-buttons-container button').forEach(btn => {
                // Only override standard buttons if AI is calculating, otherwise let dynamic logic control it
                if (btn.id !== 'start-button') { // Do not disable start button if visible
                    btn.disabled = disabled;
                }
            });
            document.getElementById('custom-action-input').disabled = disabled;
            document.getElementById('loading-indicator').classList.toggle('hidden', !disabled);
            
            // Re-render to restore dynamic state if not disabled
            if (!disabled) {
                renderDynamicActions(); 
            }
        }

        function downloadReview() {
            const reviewContent = generateReviewText(gameStatus);
            const filename = `Laporan_Simulasi_Petani_Pintar_${gameStatus.theme.replace(/\s/g, '_')}_Hari${gameStatus.day - 1}.txt`;
            
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(reviewContent));
            element.setAttribute('download', filename);

            element.style.display = 'none';
            document.body.appendChild(element);

            element.click();

            document.body.removeChild(element);
        }

        function showMessage(title, text) {
            document.getElementById('msg-title').textContent = title;
            document.getElementById('msg-text').innerHTML = text; 
            
            const actionsDiv = document.getElementById('msg-actions');
            actionsDiv.innerHTML = ''; 

            if (gameStatus.isGameOver) {
                const downloadBtn = document.createElement('button');
                downloadBtn.onclick = downloadReview;
                downloadBtn.className = 'bg-gray-700 hover:bg-gray-800 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150';
                downloadBtn.textContent = 'Unduh Analisis (TXT)';
                actionsDiv.appendChild(downloadBtn);
            }

            const restartBtn = document.createElement('button');
            restartBtn.onclick = () => window.location.reload();
            restartBtn.className = 'bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150';
            restartBtn.textContent = 'Main Lagi';
            actionsDiv.appendChild(restartBtn);

            document.getElementById('game-message-box').style.display = 'flex';
        }

        // FUNGSI UTAMA UNTUK MERENDER TOMBOL AKSI SECARA DINAMIS
        function renderDynamicActions() {
            const s = gameStatus;
            const inv = s.inventory;
            const money = inv.money;
            const container = document.getElementById('dynamic-action-buttons');
            container.innerHTML = ''; // Clear existing buttons
            
            const env = s.environmentType;
            let actions = [];

            // 1. ENVIRONMENT SPECIFIC ACTIONS
            if (env === 'Soil') {
                actions.push(ACTION_DEFS.Siram, ACTION_DEFS.Pupuk);
            } else if (env === 'Hydroponic') {
                actions.push(ACTION_DEFS.AdjustSolution, ACTION_DEFS.FlushAndRefill);
            }

            // 2. COMMON ACTIONS
            actions.push(ACTION_DEFS.Pestisida, ACTION_DEFS.Mitigasi);

            // Helper to check conditions
            const isActionAffordable = (costDef) => {
                if (costDef.money && money < costDef.money) return { afford: false, reason: `Modal (💰${money}) tidak cukup. Butuh 💰${costDef.money}.` };
                if (costDef.water && inv.water < costDef.water) return { afford: false, reason: `Inventaris Air (${inv.water} unit) tidak cukup.` };
                if (costDef.fertilizer && inv.fertilizer < costDef.fertilizer) return { afford: false, reason: `Inventaris Pupuk (${inv.fertilizer} unit) tidak cukup.` };
                if (costDef.pesticide && inv.pesticide < costDef.pesticide) return { afford: false, reason: `Inventaris Pestisida (${inv.pesticide} unit) tidak cukup.` };
                return { afford: true, reason: "" };
            };
            
            // Generate buttons
            actions.forEach(actionDef => {
                const affordability = isActionAffordable(actionDef.cost);
                const disabled = !affordability.afford;
                const actionType = actionDef.id.split('-').map((w, i) => i === 0 ? w : w.charAt(0).toUpperCase() + w.slice(1)).join('').replace('btn', '');
                
                // Special handling for Mitigasi label
                let buttonLabel = actionDef.label;
                if (actionDef.id === 'btn-mitigasi') {
                    const mitigationDisplay = s.mitigationText.split('(')[0].trim();
                    buttonLabel = `🚨 ${mitigationDisplay} (💰${actionDef.cost.money})`;
                }

                const buttonHtml = `
                    <button 
                        onclick="applyAction('${actionType}')" 
                        class="action-btn bg-${actionDef.color}-500 hover:bg-${actionDef.color}-600 text-white font-semibold py-3 rounded-lg shadow-md transition duration-150 text-sm md:text-base" 
                        id="${actionDef.id}"
                        ${disabled ? 'disabled title="' + affordability.reason + '"' : ''}>
                        ${buttonLabel}
                    </button>
                `;
                container.innerHTML += buttonHtml;
            });
            
            // --- Custom Action Placeholder Update ---
            const customCost = ACTION_DEFS.Custom.cost;
            const customAfford = isActionAffordable(customCost);
            const customInput = document.getElementById('custom-action-input');
            const customBtn = document.getElementById('btn-custom');

            if (!customAfford.afford) {
                 customInput.placeholder = "Modal tidak cukup untuk Tindakan Custom.";
                 customBtn.disabled = true;
                 customBtn.title = customAfford.reason;
            } else {
                 customInput.placeholder = `Tindakan Custom (Contoh: Beli Pupuk/Cek pH Tanah) - Biaya 💰${customCost.money}`;
                 customBtn.disabled = false;
                 customBtn.title = "";
            }
        }

        // FUNGSI BARU: Mendapatkan metrik saat ini
        function getCurrentMetrics() {
            return ENVIRONMENT_METRICS[gameStatus.environmentType];
        }

        function updateUI() {
            const s = gameStatus;
            
            if (!s.isGameOver) {
                stopNarration(true);
                document.getElementById('play-button').disabled = true;
                document.getElementById('stop-button').disabled = true;
                document.getElementById('play-btn-text').textContent = 'Putar Narasi';
            }

            // Metrik umum
            const health = Math.max(0, Math.min(100, Math.round(s.plantHealth)));
            const pest = Math.max(0, Math.min(100, Math.round(s.pestLevel)));
            
            document.getElementById('day-counter').textContent = s.day;
            document.getElementById('plant-theme').textContent = s.theme;
            document.getElementById('plant-stage').textContent = s.growthStage;

            // Update Health Bar
            document.getElementById('health-value').textContent = health;
            document.getElementById('health-bar').style.width = health + '%';
            document.getElementById('health-bar').className = `progress-bar ${health > 70 ? 'bg-green-500' : health > 30 ? 'bg-yellow-500' : 'bg-red-500'}`;

            // Update Pest Bar
            document.getElementById('pest-value').textContent = pest;
            document.getElementById('pest-bar').style.width = pest + '%';
            document.getElementById('pest-bar').className = `progress-bar ${pest < 20 ? 'bg-purple-400' : pest < 50 ? 'bg-purple-600' : 'bg-purple-800'}`;

            // --- Update Metrik Dinamis (Metric 1, 2, 3) ---
            const metrics = getCurrentMetrics();

            [1, 2, 3].forEach(index => {
                const metricKey = metrics[`metric${index}`].key;
                const metricDef = metrics[`metric${index}`];
                const metricValue = s[metricKey];

                const value = metricValue || metricDef.default;
                const unit = metricDef.unit;
                const decimals = metricDef.decimals !== undefined ? metricDef.decimals : 0;
                
                document.getElementById(`metric-${index}-label`).textContent = metricDef.name;

                // Tentukan nilai tampilan & warna bar
                let displayValue = value.toFixed(decimals) + unit;
                let barWidth = 0;
                let barColor = `bg-${metricDef.color}-500`;

                // Logika Bar Khusus
                if (metricKey === 'pH') {
                    // pH (5.8 - 6.2 ideal): 100% pada 6.0, 0% pada 4.0 atau 8.0
                    barWidth = Math.max(0, 100 - Math.abs(value - 6.0) * 50); 
                    barColor = barWidth > 80 ? 'bg-green-600' : barWidth > 50 ? 'bg-yellow-500' : 'bg-red-500';
                } else if (metricKey === 'EC') {
                    // EC (1.5 - 2.2 ideal): 100% pada 1.8. Max 5.0, Min 0.5
                    barWidth = Math.max(0, Math.min(100, value / 2.5 * 100)); // Normalisasi ke 100% pada EC 2.5
                    barColor = barWidth > 60 && barWidth < 90 ? 'bg-green-600' : barWidth > 90 ? 'bg-red-500' : 'bg-orange-500';
                } else if (metricKey.includes('temp')) {
                    // Suhu (Target di tengah rentang optimal)
                    const tempOptimal = metricKey === 'tempAir' ? plantSpecs[s.specKey].optimalTempAir[0] + (plantSpecs[s.specKey].optimalTempAir[1] - plantSpecs[s.specKey].optimalTempAir[0]) / 2 : metricDef.default;
                    barWidth = Math.max(0, 100 - Math.abs(value - tempOptimal) * 10); // 10% penalty per derajat penyimpangan
                    barColor = barWidth > 80 ? 'bg-blue-500' : barWidth > 50 ? 'bg-yellow-500' : 'bg-red-500';
                } else {
                    // Default untuk % (Kelembaban/Nutrisi)
                    barWidth = Math.max(0, Math.min(100, Math.round(value)));
                    barColor = `bg-${metricDef.color}-500`;
                }
                
                document.getElementById(`metric-${index}-value`).textContent = displayValue;
                document.getElementById(`metric-${index}-bar`).style.width = barWidth + '%';
                document.getElementById(`metric-${index}-bar`).className = `progress-bar ${barColor}`;
            });


            // Perbarui Inventaris
            document.getElementById('inv-money').textContent = s.inventory.money;
            document.getElementById('inv-water').textContent = s.inventory.water;
            document.getElementById('inv-fertilizer').textContent = s.inventory.fertilizer;
            document.getElementById('inv-pesticide').textContent = s.inventory.pesticide;
            
            // Perbarui Narasi dan Log
            document.getElementById('narration-text').innerHTML = s.narration.replace(/\n/g, '<br>'); 
            const logOutput = document.getElementById('log-output');
            logOutput.innerHTML = s.log.map(entry => `<p class="text-xs">[H${entry.day}] ${entry.text}</p>`).reverse().join('');
            logOutput.scrollTop = 0; 
            
            // PENTING: Update Opsi Tindakan Dinamis
            renderDynamicActions();

            // Nonaktifkan tombol jika Game Over
            if (s.isGameOver) {
                setActionButtonsDisabled(true);
                stopNarration(true); 
                showMessage(s.finalResult.title, s.finalResult.text);
            }
        }

        // --- FUNGSI BARU: PEMBENTUKAN LAPORAN REVIEW (Tidak berubah signifikan) ---
        function generateReviewText(status) {
            const theme = status.theme;
            const difficulty = status.difficultyFactors.description;
            const days = status.day - 1; 
            const finalHealth = Math.round(status.plantHealth);
            const resultTitle = status.finalResult.title;
            const resultText = status.finalResult.text;
            const spec = plantSpecs[status.specKey];
            const metrics = getCurrentMetrics();

            // A. Ringkas Log Tindakan
            const actionSummary = status.log.reduce((acc, entry) => {
                const action = entry.text.split(':')[0].trim();
                acc[action] = (acc[action] || 0) + 1;
                return acc;
            }, {});
            
            let summaryText = Object.entries(actionSummary).map(([action, count]) => 
                `- ${action}: Dilakukan ${count} kali.`
            ).join('\n');

            if (summaryText === "") {
                summaryText = "- Tidak ada tindakan yang tercatat."
            }

            // B. Analisis Kinerja
            let performanceAnalysis = "";
            let grade = 'C';

            if (status.growthStage === "Panen" && finalHealth > 80) {
                performanceAnalysis = "Kinerja SANGAT BAIK. Pemain berhasil melalui semua tahap pertumbuhan dan mencapai Panen. Manajemen sumber daya dan waktu sangat optimal.";
                grade = 'A';
            } else if (status.growthStage === "Panen") {
                performanceAnalysis = "Kinerja BAIK. Panen tercapai, namun Kesehatan Tanaman Akhir yang kurang optimal menunjukkan adanya periode stres atau penyakit yang harus dihindari di masa depan.";
                grade = 'B';
            } else if (finalHealth <= 0) {
                performanceAnalysis = "Kinerja GAGAL. Tanaman mati (Kesehatan 0%). Analisis log menunjukkan kegagalan dalam merespons ancaman (Hama tinggi) atau kondisi lingkungan (Terlalu kering/basah).";
                grade = 'D';
            } else if (days >= MAX_DAYS) {
                performanceAnalysis = "Kinerja TIDAK LENGKAP. Permainan berakhir karena batas hari tercapai. Tanaman bertahan, tetapi Panen belum tercapai. Perlu fokus pada nutrisi dan percepatan pertumbuhan.";
                grade = 'B';
            } else {
                performanceAnalysis = "Kinerja Menengah. Permainan berakhir mendadak sebelum Panen, atau Kesehatan berada dalam kondisi kritis. Peluang untuk Panen terlewatkan. Lakukan audit pada tindakan hari-hari kritis.";
                grade = 'C';
            }
            
            // C. Feedback & Saran Strategis based on mechanics
            let feedback = ``;
            if (status.environmentType === 'Hydroponic') {
                 feedback = `
1. Manajemen pH: Jaga pH larutan ketat pada rentang optimal (${spec?.optimalpH[0]} - ${spec?.optimalpH[1]}). Penyimpangan pH sangat cepat memblokir penyerapan nutrisi.
2. Manajemen EC: EC yang terlalu rendah berarti tanaman lapar, EC yang terlalu tinggi dapat menyebabkan "nutrient burn" atau keracunan.
3. Kontrol Suhu Air: Suhu air yang terlalu panas atau dingin dapat merusak akar dan menurunkan penyerapan oksigen.
`;
            } else {
                feedback = `
1. Manajemen Air (Kelembaban): Pastikan kelembaban tanah berada dalam rentang optimal (${spec?.optimalMoisture[0]}%- ${spec?.optimalMoisture[1]}%). Kelebihan atau kekurangan air secara konsisten dapat memotong Health.
2. Timing Nutrisi: Pupuk sangat krusial untuk maju ke Tahap pertumbuhan berikutnya. Jangan menunda pemupukan saat Tingkat Nutrisi mulai rendah.
3. Kontrol Suhu Udara: Suhu ekstrem akan menekan kesehatan tanaman secara keseluruhan.
`;
            }
            
            feedback += `
4. Kontrol Hama: Risiko hama meningkat dengan faktor kesulitan. Gunakan Pestisida secara proaktif jika level Hama/Penyakit mencapai 20% ke atas.
5. Manajemen Modal: Pertahankan modal untuk membeli sumber daya darurat.
`;

            const review = `
LAPORAN ANALISIS KOMPREHENSIF SIMULASI PETANI PINTAR

================================================================
I. DETAIL PERMAINAN
================================================================
Tanggal Review: ${new Date().toLocaleDateString('id-ID')}
Nama Game: Simulasi Petani Pintar (${theme} - Lingkungan ${status.environmentType})
Tingkat Kesulitan: ${status.difficultyFactors.description}
Total Hari Bermain: ${days} Hari
Hasil Akhir: ${resultTitle}

================================================================
II. RINGKASAN KINERJA KESELURUHAN
================================================================
Permainan ini berakhir dengan hasil: **${resultTitle}**.
Kesehatan Tanaman Akhir: **${finalHealth}%**
Tahap Pertumbuhan Akhir: **${status.growthStage}**

Analisis Kinerja:
${performanceAnalysis}

Narasi Akhir Game Master:
"${resultText}"

================================================================
III. ANALISIS TINDAKAN KUNCI
================================================================
Berikut adalah ringkasan frekuensi tindakan yang Anda lakukan sepanjang permainan:

${summaryText}

================================================================
IV. UMPAN BALIK DAN SARAN STRATEGIS
================================================================
Untuk sesi berikutnya, pertimbangkan saran strategis ini berdasarkan mekanik game dan spesifikasi tanaman ${theme}:

${feedback}

================================================================
V. PENILAIAN AKHIR
================================================================
Kesehatan Akhir Tanaman: ${finalHealth}%

**Penilaian Kualitas Manajemen (Grade): ${grade}**

**Kesimpulan Reviewer:**
${status.growthStage === "Panen" ? "Selamat! Anda menunjukkan manajemen yang sangat baik. Coba tantang diri Anda dengan meningkatkan kesulitan di sesi berikutnya." : "Permainan ini adalah sesi pembelajaran yang berharga. Fokus pada pemantauan metrik kritis dan respons yang lebih cepat terhadap kondisi non-optimal adalah kunci untuk mencapai panen penuh. Analisis setiap giliran secara hati-hati!"}
`;
            return review.trim();
        }
        // --- AKHIR FUNGSI REVIEW ---

        // --- GAME INITIALIZATION (Diperbarui untuk lingkungan dinamis) ---

        function initializeGame(theme, customName = null, difficultyKey) {
            
            let specKeyForParams = theme === 'Custom' ? 'HydroponicDefault' : theme; 
            const nameLower = (customName || theme).toLowerCase();
            let envType = 'Soil';

            // Cek apakah custom theme adalah hidroponik
            if (theme === 'Custom' && (nameLower.includes('hidroponik') || nameLower.includes('aeroponik') || nameLower.includes('nft') || nameLower.includes('dft'))) {
                envType = 'Hydroponic';
                specKeyForParams = 'HydroponicDefault';
            } else if (theme !== 'Custom') {
                envType = plantSpecs[theme].environmentType;
            } else {
                // Custom, tapi bukan hidroponik (asumsi tanah/media tanam)
                specKeyForParams = 'Tomat'; // Gunakan Tomat sebagai default tanah untuk Custom
            }
            
            const spec = plantSpecs[specKeyForParams];
            const difficulty = difficultySettings[difficultyKey] || difficultySettings.Sedang;
            const metrics = ENVIRONMENT_METRICS[envType];

            let initialState = {
                theme: customName || theme,
                specKey: specKeyForParams, 
                environmentType: envType, // New dynamic field
                day: 1,
                pestLevel: 5,
                plantHealth: 100,
                growthStage: "Benih",
                inventory: {
                    water: 5, fertilizer: 2, pesticide: 1, money: difficulty.moneyStart
                },
                difficultyFactors: difficulty,
                log: [], isGameOver: false, finalResult: null,
                narration: spec.narrationStart.replace("sistem non-tanah", nameLower),
                mitigationText: spec.mitigationSOP 
            };
            
            // Set initial dynamic metric values based on environment
            initialState[metrics.metric1.key] = metrics.metric1.default;
            initialState[metrics.metric2.key] = metrics.metric2.default;
            initialState[metrics.metric3.key] = metrics.metric3.default;

            gameStatus = initialState;
        }

        function startGame() {
            const themeSelect = document.getElementById('theme-select');
            const difficultySelect = document.getElementById('difficulty-select'); 
            const selectedTheme = themeSelect.value;
            const selectedDifficulty = difficultySelect.value; 
            const customNameInput = document.getElementById('custom-plant-name').value.trim();

            if (selectedTheme === "" || (selectedTheme === "Custom" && customNameInput === "")) {
                document.getElementById('setup-message').classList.remove('hidden');
                return;
            }

            document.getElementById('setup-message').classList.add('hidden');
            document.getElementById('setup-area').style.display = 'none';
            document.getElementById('game-area').style.display = 'block';

            const name = selectedTheme === "Custom" ? customNameInput : selectedTheme;
            initializeGame(selectedTheme, name, selectedDifficulty); 
            
            updateUI();
            prepareNarration(gameStatus.narration);
        }

        function handleThemeChange() {
            const selectedTheme = document.getElementById('theme-select').value;
            const customDiv = document.getElementById('custom-input-div');
            if (selectedTheme === 'Custom') {
                customDiv.style.display = 'block';
            } else {
                customDiv.style.display = 'none';
            }
        }
        
        // --- AI INTERACTION LOGIC ---

        async function exponentialBackoffFetch(url, options, retries = 0) {
            try {
                const response = await fetch(url, options);
                if (response.status === 429 && retries < MAX_RETRIES) {
                    const delay = Math.pow(2, retries) * 1000 + Math.random() * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                    return exponentialBackoffFetch(url, options, retries + 1);
                }
                if (!response.ok) {
                    const errorBody = await response.text();
                    throw new Error(`API call failed: ${response.status} ${response.statusText}. Response: ${errorBody}`);
                }
                return response.json();
            } catch (error) {
                if (retries < MAX_RETRIES) {
                    const delay = Math.pow(2, retries) * 1000 + Math.random() * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                    return exponentialBackoffFetch(url, options, retries + 1);
                }
                console.error("Fetch error after retries:", error);
                throw new Error("Gagal terhubung dengan Game Master AI. Silakan coba lagi.");
            }
        }

        const SYSTEM_INSTRUCTION = `Anda adalah Game Master (GM) ahli untuk game simulasi budidaya tanaman. Tugas Anda adalah menerima status game saat ini, tindakan pemain, dan meniru simulasi satu hari penuh, kemudian mengembalikan status game yang diperbarui.
        
        ROLEPLAY: Berikan narasi yang mendalam dan imersif. Gunakan nama tanaman yang sebenarnya dari field 'theme' (bukan hanya 'specKey') dalam narasi Anda.
        
        ATURAN SIMULASI:
        1. Gunakan 'environmentType' ('Soil' atau 'Hydroponic') dan 'ENVIRONMENT_METRICS' global untuk menentukan metrik mana yang akan disimulasikan (Soil: soilMoisture, nutrientLevel, tempAir. Hydroponic: pH, EC, tempWater). Hanya simulasikan metrik yang relevan.
        2. Gunakan 'specKey' dan 'plantSpecs' untuk mengambil aturan optimal (misalnya, optimalMoisture vs optimalpH) yang sesuai dengan 'environmentType'.
        3. Gunakan 'difficultyFactors' dalam gameStatus untuk memodifikasi simulasi: moistureLoss/pestRate/healthPenalty/weatherVolatility.
        4. Terapkan efek langsung dari tindakan pemain (Siram, Pupuk, AdjustSolution, FlushAndRefill, Pestisida, Mitigasi, Custom) pada inventaris dan metrik yang relevan.
           - Kurangi inventaris sesuai biaya yang ditetapkan.
           - Tindakan 'Siram' (Soil) meningkatkan soilMoisture.
           - Tindakan 'Pupuk' (Soil) meningkatkan nutrientLevel.
           - Tindakan 'AdjustSolution' (Hydroponic) memungkinkan koreksi halus pada pH dan EC.
           - Tindakan 'FlushAndRefill' (Hydroponic) akan mereset pH dan EC mendekati nilai optimal dan mengisi air.
        5. Simulasikan penurunan alami harian (seperti kehilangan air/penyerapan nutrisi/fluktuasi suhu).
        6. Simulasikan risiko hama/penyakit harian (Pest level meningkat).
        7. Hitung penurunan Kesehatan (Health) berdasarkan seberapa jauh SEMUA metrik lingkungan (metric1, metric2, metric3, Pest) menyimpang dari kondisi optimal (gunakan rentang optimal yang ketat).
        8. Periksa Kenaikan Tahap: Jika hari telah mencapai hari yang ditentukan di 'stages' tanaman dan plantHealth > 50, naikkan 'growthStage' ke tahap berikutnya.
        9. Cek Game Over/Win:
           - Jika plantHealth <= 0 ATAU day > ${MAX_DAYS}, set isGameOver = true.
           - Jika growthStage mencapai "Panen", set isGameOver = true.
        10. Jika isGameOver true, isi objek 'finalResult' dengan 'title' (Contoh: "GAME OVER: Gagal Panen") dan 'text' (Narasi hasil akhir).
        
        Output Anda HARUS berupa satu objek JSON, TIDAK ADA teks lain. Sertakan SEMUA field dari gameStatus yang Anda terima, bahkan jika nilainya tidak berubah, PLUS field 'logEntry' (String). Semua output narasi dan log harus dalam Bahasa Indonesia.`;

        async function applyAction(actionType) {
            if (gameStatus.isGameOver) return;
            
            // --- CLIENT-SIDE COST AND INVENTORY CHECK ---
            const def = ACTION_DEFS[actionType];
            const inv = gameStatus.inventory;
            
            if (def) {
                if (def.cost.money && inv.money < def.cost.money) {
                    showMessage("Modal Kurang", `Anda tidak memiliki cukup 💰Modal untuk melakukan tindakan ${actionType} (Butuh 💰${def.cost.money}).`);
                    return;
                }
                if (def.cost.water && inv.water < def.cost.water) {
                    showMessage("Inventaris Kosong", `Anda tidak memiliki unit 💧Air yang cukup di inventaris.`);
                    return;
                }
                if (def.cost.fertilizer && inv.fertilizer < def.cost.fertilizer) {
                     showMessage("Inventaris Kosong", `Anda tidak memiliki unit 🧪Pupuk yang cukup di inventaris.`);
                    return;
                }
                if (def.cost.pesticide && inv.pesticide < def.cost.pesticide) {
                     showMessage("Inventaris Kosong", `Anda tidak memiliki unit 🦠Pestisida yang cukup di inventaris.`);
                    return;
                }
            }
            // --- END CLIENT-SIDE CHECK ---

            setActionButtonsDisabled(true);
            stopNarration(true); 

            let customActionText = "";
            if (actionType === 'Custom') {
                customActionText = document.getElementById('custom-action-input').value.trim();
                if (!customActionText) {
                    showMessage("Kesalahan Input", "Masukkan deskripsi tindakan custom Anda.");
                    setActionButtonsDisabled(false);
                    return;
                }
            }
            
            // Call 1: Simulation (Get Text)
            const globalContext = {
                plantSpecs: plantSpecs,
                difficultySettings: difficultySettings,
                ACTION_DEFS: ACTION_DEFS,
                ENVIRONMENT_METRICS: ENVIRONMENT_METRICS 
            };
            const currentStatus = JSON.stringify(gameStatus);
            const playerAction = actionType === 'Custom' ? `Tindakan Custom: ${customActionText}` : actionType;

            const userQuery = `Status game saat ini adalah: ${currentStatus}. Konteks global adalah: ${JSON.stringify(globalContext)}.
            Tindakan yang dipilih oleh pemain untuk Hari ke-${gameStatus.day} adalah: ${playerAction}.
            Terapkan tindakan (kurangi biaya inventaris yang sesuai), simulasikan akhir hari (gunakan difficultyFactors dalam status), majukan hari (day+1), dan berikan status akhir hari. Berikan seluruh respons sebagai JSON saja.`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: SYSTEM_INSTRUCTION }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "day": { "type": "INTEGER" },
                            "soilMoisture": { "type": "NUMBER", "nullable": true }, // Bisa null jika Hydroponic
                            "nutrientLevel": { "type": "NUMBER", "nullable": true }, // Bisa null jika Hydroponic
                            "tempAir": { "type": "NUMBER", "nullable": true }, // Bisa null jika Hydroponic
                            "pH": { "type": "NUMBER", "nullable": true }, // Bisa null jika Soil
                            "EC": { "type": "NUMBER", "nullable": true }, // Bisa null jika Soil
                            "tempWater": { "type": "NUMBER", "nullable": true }, // Bisa null jika Soil
                            "pestLevel": { "type": "NUMBER" },
                            "plantHealth": { "type": "NUMBER" },
                            "growthStage": { "type": "STRING" },
                            "inventory": { 
                                "type": "OBJECT", 
                                "properties": {
                                    "water": { "type": "INTEGER" },
                                    "fertilizer": { "type": "INTEGER" },
                                    "pesticide": { "type": "INTEGER" },
                                    "money": { "type": "INTEGER" }
                                }
                            },
                            "narration": { "type": "STRING" },
                            "logEntry": { "type": "STRING" },
                            "mitigationText": { "type": "STRING" },
                            "isGameOver": { "type": "BOOLEAN" },
                            "finalResult": { "type": "OBJECT", "nullable": true, "properties": { "title": { "type": "STRING" }, "text": { "type": "STRING" } } }
                        }
                    }
                }
            };

            let newStatus;
            try {
                const response = await exponentialBackoffFetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const resultText = response.candidates?.[0]?.content?.parts?.[0]?.text;
                if (!resultText) {
                    throw new Error("Respons AI simulasi kosong atau tidak terstruktur.");
                }

                newStatus = JSON.parse(resultText);

                // Update game state with simulation results
                // Menggabungkan newStatus ke gameStatus, menimpa nilai yang ada
                Object.assign(gameStatus, newStatus); 
                
                // Pastikan logEntry diproses setelah day diperbarui
                if (newStatus.logEntry) {
                    gameStatus.log.push({ day: gameStatus.day - 1, text: newStatus.logEntry }); 
                }

                updateUI();
                document.getElementById('custom-action-input').value = ''; 
                
                if (!gameStatus.isGameOver) {
                    prepareNarration(newStatus.narration); 
                }

            } catch (error) {
                console.error("Error during AI turn:", error);
                showMessage("Kesalahan Simulasi", `Terjadi kesalahan saat menghubungi Game Master AI: ${error.message}. Coba lagi.`);
            } finally {
                setActionButtonsDisabled(false);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            handleThemeChange(); 
        });

    </script>
</body>
</html>
