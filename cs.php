<?php
// 1. Include and check connection
include "admin/fungsi/koneksi.php";

/** @var mysqli $koneksi */
if (!$koneksi) {
    die("Database connection error.");
}

// Set charset to ensure emojis and special characters work
mysqli_set_charset($koneksi, "utf8mb4");

$apiKey = null;
$apiId  = null;

// --- 1. Select API Key with Atomic Transaction ---
$koneksi->begin_transaction();

try {
    // Select the key with the lowest usage and lock the row (FOR UPDATE)
    $query = "SELECT id, api_key FROM api_keys ORDER BY usage_count ASC, id ASC LIMIT 1 FOR UPDATE";
    $result = $koneksi->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $apiKey = $row['api_key'];
        $apiId  = $row['id'];

        // Update usage_count inside the lock
        $update = $koneksi->prepare("UPDATE api_keys SET usage_count = usage_count + 1 WHERE id = ?");
        if ($update) {
            $update->bind_param("i", $apiId);
            $update->execute();
            $update->close();
        }
    }

    $koneksi->commit();
} catch (Exception $e) {
    // If something fails, rollback so the lock is released
    $koneksi->rollback();
    error_log("API Key Selection Error: " . $e->getMessage());
}

// --- 2. Fallback Mechanism ---
// Use a secure fallback if DB is empty or fails
if (!$apiKey) {
    $apiKey = "APIKEY"; // Note: Move to .env for security
}

$apiKeyJson = json_encode([$apiKey]);

// --- 3. Fetch Supported Models ---
$models = [];
$sql_model = "SELECT model_name FROM api_model 
              WHERE is_supported = 1 
              AND is_active = 1 
              AND guna_model = 2 
              ORDER BY id ASC";

$res_model = $koneksi->query($sql_model);

if ($res_model && $res_model->num_rows > 0) {
    while ($row = $res_model->fetch_assoc()) {
        $models[] = $row['model_name'];
    }
}

// Default to gemini-1.5-flash if no active models in DB
$model = !empty($models) ? $models[0] : "gemini-1.5-flash";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Game Master: Petualangan Interaktif</title>
    <!-- Memuat Tailwind CSS untuk styling responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        /* Styling khusus untuk log cerita */
        #story-log {
            min-height: 50vh;
            max-height: 70vh;
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        .message-box {
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .user-message {
            background-color: #d1e7dd; /* Hijau muda */
            border-right: 4px solid #0f5132;
            text-align: right;
        }
        /* Mengubah GM message ke warna fantasi yang lebih tenang */
        .gm-message {
            background-color: #f0f9ff; /* Biru muda/Dingin */
            border-left: 4px solid #312e81; /* Indigo gelap */
            text-align: left; /* Biar narasi lebih mudah dibaca */
        }
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4CAF50;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .tts-button {
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 9999px;
            background-color: transparent;
            border: none;
            transition: color 0.15s;
        }
        .tts-button:hover {
            opacity: 0.8;
        }
        .tts-button.playing svg {
            color: #ef4444; /* Warna merah saat sedang bermain (Stop) */
        }
        .tts-button.paused svg {
            color: #f59e0b; /* Warna kuning saat dijeda */
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <!-- Kontainer Utama -->
    <div class="w-full max-w-3xl bg-white rounded-xl shadow-2xl p-6 md:p-8">
        <header class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800" id="main-title">Pengaturan Petualangan</h1>
            <p class="text-gray-600 mt-1" id="main-subtitle">Tentukan dunia yang akan Anda jelajahi.</p>
        </header>

        <!-- SETUP SCREEN -->
        <div id="setup-screen">
            <form id="setup-form" class="space-y-4">
                <!-- Input 1: Tema -->
                <label class="block">
                    <span class="text-gray-700 font-semibold">Tema Cerita (cth: Fantasi Gelap, Sci-Fi Pasca-Apokaliptik, Bajak Laut):</span>
                    <input type="text" id="setup-theme" required value="Fantasi Klasik"
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                </label>
                <!-- Input 2: Tokoh Utama -->
                <label class="block">
                    <span class="text-gray-700 font-semibold">Nama & Deskripsi Tokoh Utama (cth: Ksatria bernama Valen, Pencuri Elf yang lincah):</span>
                    <input type="text" id="setup-protagonist" required value="Penyihir muda bernama Elara"
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                </label>
                <!-- Input 3: Deskripsi Dunia -->
                <label class="block">
                    <span class="text-gray-700 font-semibold">Deskripsi Dunia/Suasana (cth: Kota terapung yang damai, Hutan yang dihantui sihir kuno):</span>
                    <textarea id="setup-description" rows="3" required
                              class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">Kerajaan tua yang diselimuti kabut dan rumor tentang artefak yang hilang.</textarea>
                </label>
                
                <!-- Input 4: Level Plot Twist -->
                <label class="block pt-2">
                    <span class="text-gray-700 font-semibold mb-2 block">Level Plot Twist Awal (0-100): <span id="twist-value-label" class="font-bold text-red-600">Sedang (50) - Atmosferik</span></span>
                    <input type="range" id="plot-twist-level" min="1" max="100" value="50"
                           class="mt-1 block w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg accent-red-600">
                </label>
                <!-- Akhir Input 4 -->

                <button type="submit" id="start-button"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-lg disabled:bg-gray-400">
                    Mulai Petualangan!
                </button>
                <div id="setup-status" class="mt-4 text-center text-red-600 font-medium hidden"></div>
            </form>
        </div>

        <!-- GAME SCREEN (Hidden initially) -->
        <div id="game-screen" class="hidden">
            <!-- Area Log Cerita -->
            <div id="story-log" class="bg-gray-50 border border-gray-200 p-4 rounded-lg mb-6">
                <!-- Narasi awal akan diisi oleh AI setelah setup -->
            </div>

            <!-- Formulir Input Pengguna -->
            <form id="input-form" class="flex flex-col md:flex-row gap-3">
                <input type="text" id="user-input" placeholder="Masukkan tindakan Anda..." required 
                       class="flex-grow p-3 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-800">
                
                <!-- Tombol Input Suara -->
                <button type="button" id="voice-input-button" title="Input Suara (id-ID)"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md disabled:bg-gray-400 w-auto md:w-1/6 flex items-center justify-center">
                    <!-- Ikon Default: Mikrofon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7v1a1 1 0 01-2 0v-1a7 7 0 01-7-7h1a1 1 0 010 2H5a5 5 0 005 5v2h4v-2a5 5 0 005-5h-1a1 1 0 01-2 0zM12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/>
                    </svg>
                </button>
                
                <button type="submit" id="submit-button"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md disabled:bg-gray-400">
                    Kirim Aksi
                </button>
            </form>

            <!-- Area Status & Error -->
            <div id="status-message" class="mt-4 text-center text-red-600 font-medium hidden"></div>
        </div>
    </div>

    <script>
        // Variabel global untuk AI (API Key harus kosong)
        const apiKey =  <?php echo $apiKeyJson; ?>; 
         const md =  <?php echo json_encode($model); ?>;
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${keys[0]}`;
        
        // Riwayat obrolan untuk menjaga konteks cerita
        let chatHistory = []; 
        let isProcessing = false;

        // System Instruction - Akan diinisialisasi setelah setup
        let systemPrompt = `Anda adalah Game Master (GM) yang imajinatif untuk game petualangan berbasis teks.
        Perintah Penting: Selalu berikan respons dalam bentuk narasi MURNI. Jangan pernah menyertakan awalan seperti "GM:", "Game Master:", "Narator:", atau label lainnya.
        Anda harus merespons tindakan pengguna dengan menjelaskan konsekuensinya dan memajukan alur cerita.
        Jaga respons tetap ringkas (maksimal 3 kalimat), fokus pada suasana, dan aksi.
        Cerita harus selalu bertema fantasi dan petualangan, serta SAMA SEKALI tidak boleh memuat konten seksual, kekerasan berdarah (gore), atau konten dewasa lainnya.
        Selalu tanggapi dalam Bahasa Indonesia.`;

        // TTS Variables
        const synth = window.speechSynthesis;
        let currentUtterance = null;
        let currentPlaybackButton = null;
        let selectedVoice = null; // Dikelola secara dinamis di speakGMResponse sekarang
        let voiceLoadInterval; 

        // Speech Recognition Variables
        let recognition = null;
        
        // Ambil elemen DOM Game
        const gameScreen = document.getElementById('game-screen');
        const form = document.getElementById('input-form');
        const input = document.getElementById('user-input');
        const log = document.getElementById('story-log');
        const button = document.getElementById('submit-button');
        const statusMessage = document.getElementById('status-message');
        const mainTitle = document.getElementById('main-title');
        const mainSubtitle = document.getElementById('main-subtitle');
        const voiceButton = document.getElementById('voice-input-button'); 

        // Ambil elemen DOM Setup
        const setupScreen = document.getElementById('setup-screen');
        const setupForm = document.getElementById('setup-form');
        const setupTheme = document.getElementById('setup-theme');
        const setupProtagonist = document.getElementById('setup-protagonist');
        const setupDescription = document.getElementById('setup-description');
        const plotTwistSlider = document.getElementById('plot-twist-level'); 
        const twistValueLabel = document.getElementById('twist-value-label'); 
        const startButton = document.getElementById('start-button');
        const setupStatus = document.getElementById('setup-status');

        // --- TTS FUNCTIONS ---

        function loadVoices() {
             // Fungsi ini disederhanakan karena pemilihan suara utama ada di speakGMResponse
            if (!synth) return;
            const voices = synth.getVoices();
            
            if (voices.length > 0) {
                // Gunakan logika pemilihan suara yang baru untuk inisialisasi awal
                selectedVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google")) 
                             || voices.find(v => v.lang === 'id-ID')
                             || voices[0];

                if (voiceLoadInterval) {
                    clearInterval(voiceLoadInterval);
                    voiceLoadInterval = null;
                }
                console.log("TTS Voices loaded. Initial selected voice:", selectedVoice ? selectedVoice.name : "Default/None");
            } else {
                console.log("TTS voices not loaded yet, polling...");
            }
        }

        // --- Voice Loading Logic ---
        loadVoices(); 

        if (synth && 'onvoiceschanged' in synth) {
            synth.onvoiceschanged = loadVoices;
        } else if (synth) {
            voiceLoadInterval = setInterval(loadVoices, 500); 
            setTimeout(() => {
                if (voiceLoadInterval) {
                    clearInterval(voiceLoadInterval);
                    voiceLoadInterval = null;
                    console.warn("TTS voices polling stopped after 5s. Voices may not be available.");
                }
            }, 5000);
        }
        // --- End Voice Loading Logic ---


        function stopSpeaking() {
            if (synth.speaking) {
                synth.cancel();
            }
            if (currentPlaybackButton) {
                currentPlaybackButton.classList.remove('playing');
                currentPlaybackButton.title = 'Putar Narasi';
                currentPlaybackButton.innerHTML = getPlayIcon();
                currentPlaybackButton = null;
            }
            currentUtterance = null;
        }

        function getPlayIcon() {
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.26c0 .816.98 1.258 1.69.701l3.197-2.132a1 1 0 000-1.782z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
        }

        function getStopIcon() {
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9h4v6h-4z" />
            </svg>`;
        }

        function getMicIcon() {
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7v1a1 1 0 01-2 0v-1a7 7 0 01-7-7h1a1 1 0 010 2H5a5 5 0 005 5v2h4v-2a5 5 0 005-5h-1a1 1 0 01-2 0zM12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/>
            </svg>`;
        }
        
        function getMicListeningIcon() {
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 animate-pulse text-white" fill="currentColor" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M12 1c3.866 0 7 3.134 7 7v4c0 3.866-3.134 7-7 7s-7-3.134-7-7V8c0-3.866 3.134-7 7-7zm0 14c2.76 0 5-2.24 5-5V8c0-2.76-2.24-5-5-5s-5 2.24-5 5v4c0 2.76 2.24 5 5 5zm-3 4h6v1a1 1 0 01-2 0v-1H9v1a1 1 0 01-2 0v-1H7v2a1 1 0 002 0v-1h6v1a1 1 0 002 0v-2h-1a1 1 0 010-2h2a3 3 0 003-3V8a9 9 0 10-18 0v4a3 3 0 003 3h2a1 1 0 010 2z"/>
            </svg>`;
        }


        function speakGMResponse(text, buttonElement) {
            if (!synth) {
                console.error("Web Speech API tidak didukung di browser ini.");
                return;
            }
            
            if (synth.speaking && currentPlaybackButton === buttonElement) {
                stopSpeaking(); 
                return;
            }
            
            stopSpeaking(); 

            currentUtterance = new SpeechSynthesisUtterance(text);
            currentUtterance.lang = 'id-ID';
            
            // --- IMPLEMENTASI PARAMETER BARU ---
            currentUtterance.pitch = 1.0;
            currentUtterance.rate = 0.95; // Sedikit melambat

            const voices = synth.getVoices();
            // Memprioritaskan suara Google id-ID, lalu suara id-ID lainnya.
            const indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google")) 
                             || voices.find(v => v.lang === 'id-ID');
            
            if (indoVoice) {
                currentUtterance.voice = indoVoice;
                // Opsional: Perbarui global selectedVoice
                selectedVoice = indoVoice; 
            } else {
                console.warn("TTS: Gagal mendapatkan suara id-ID, menggunakan suara default browser.");
            }
            // --- AKHIR IMPLEMENTASI PARAMETER BARU ---


            currentPlaybackButton = buttonElement;
            currentPlaybackButton.classList.add('playing');
            currentPlaybackButton.title = 'Hentikan Narasi';
            currentPlaybackButton.innerHTML = getStopIcon();


            currentUtterance.onend = () => {
                stopSpeaking();
            };

            currentUtterance.onerror = (event) => {
                console.error('SpeechSynthesisUtterance.onerror', event);
                stopSpeaking();
            };

            synth.speak(currentUtterance);
        }

        // --- END TTS FUNCTIONS ---
        
        // --- SPEECH RECOGNITION (INPUT SUARA) FUNCTIONS ---
        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = false; 
            recognition.interimResults = false;
            recognition.lang = 'id-ID'; 

            recognition.onstart = () => {
                toggleLoading(true, false); 
                voiceButton.classList.add('bg-red-500', 'hover:bg-red-600');
                voiceButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                voiceButton.title = 'Mendengar... Klik untuk Batal';
                voiceButton.innerHTML = getMicListeningIcon();
                
                statusMessage.textContent = "AI sedang mendengarkan... Katakan aksi Anda!";
                statusMessage.classList.remove('hidden');
                statusMessage.classList.add('text-blue-600');
                statusMessage.classList.remove('text-red-600');
            };

            recognition.onend = () => {
                toggleLoading(false, false); 
                voiceButton.classList.remove('bg-red-500', 'hover:bg-red-600');
                voiceButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
                voiceButton.title = 'Input Suara (id-ID)';
                voiceButton.innerHTML = getMicIcon();
                
                statusMessage.textContent = "";
                statusMessage.classList.add('hidden');
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                input.value = transcript;
                form.dispatchEvent(new Event('submit'));
            };

            recognition.onerror = (event) => {
                console.error('Speech Recognition Error:', event.error);
                statusMessage.textContent = `Error Suara: ${event.error}. Pastikan mikrofon aktif dan Anda memberikan izin.`;
                statusMessage.classList.remove('hidden');
                statusMessage.classList.remove('text-blue-600');
                statusMessage.classList.add('text-red-600');
                recognition.onend(); 
            };

            voiceButton.addEventListener('click', (e) => {
                e.preventDefault();
                stopSpeaking(); 
                
                if (isProcessing) return;

                if (recognition) {
                    try {
                        if (voiceButton.classList.contains('bg-red-500')) {
                             recognition.stop();
                        } else {
                             recognition.start();
                        }
                    } catch (e) {
                        console.warn("Recognition start/stop error:", e);
                        try {
                           recognition.stop();
                        } catch (e2) {
                           console.error("Critical recognition stop error:", e2);
                        }
                    }
                }
            });

        } else {
            voiceButton.classList.add('hidden');
            console.warn("Speech Recognition API (webkitSpeechRecognition) tidak didukung di browser ini.");
        }
        // --- END SPEECH RECOGNITION FUNCTIONS ---


        // Fungsi untuk menambahkan pesan ke log cerita
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message-box', sender === 'user' ? 'user-message' : 'gm-message');
            
            const senderLabel = document.createElement('span');
            senderLabel.classList.add('font-semibold', sender === 'user' ? 'text-green-800' : 'text-indigo-800');
            senderLabel.textContent = sender === 'user' ? 'Anda:' : 'GM:';
            
            const paragraph = document.createElement('p');
            paragraph.classList.add('mt-1', 'text-gray-700');
            paragraph.innerHTML = text; 

            let playButton = null;

            if (sender === 'gm') {
                const headerDiv = document.createElement('div');
                headerDiv.classList.add('flex', 'justify-between', 'items-center', 'mb-2');
                
                const labelWrapper = document.createElement('div');
                labelWrapper.classList.add('flex', 'items-center');
                labelWrapper.appendChild(senderLabel);

                playButton = document.createElement('button'); 
                playButton.classList.add('tts-button', 'ml-2');
                playButton.innerHTML = getPlayIcon();
                playButton.title = 'Putar Narasi';
                
                playButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    speakGMResponse(text, playButton);
                });

                labelWrapper.appendChild(playButton);
                headerDiv.appendChild(labelWrapper);
                messageDiv.appendChild(headerDiv);
                
                messageDiv.appendChild(paragraph);

            } else {
                messageDiv.appendChild(senderLabel);
                messageDiv.appendChild(paragraph);
            }

            log.appendChild(messageDiv);
            log.scrollTop = log.scrollHeight;
            
            return playButton; 
        }

        // Fungsi untuk menampilkan/menyembunyikan indikator loading
        function toggleLoading(show, isSetup = false) {
            isProcessing = show;
            
            if (isSetup) {
                setupTheme.disabled = show;
                setupProtagonist.disabled = show;
                setupDescription.disabled = show;
                plotTwistSlider.disabled = show; 
                startButton.disabled = show;
                if (show) {
                    startButton.innerHTML = '<span class="loading-spinner"></span> Membuat Dunia...';
                    setupStatus.classList.remove('hidden');
                    setupStatus.textContent = "AI sedang menyusun narasi awal...";
                } else {
                    startButton.innerHTML = 'Mulai Petualangan!';
                    setupStatus.classList.add('hidden');
                }
            } else {
                input.disabled = show;
                button.disabled = show;
                voiceButton.disabled = show; 
                if (show) {
                    button.innerHTML = '<span class="loading-spinner"></span> Memproses...';
                } else {
                    button.innerHTML = 'Kirim Aksi';
                }
            }
            
            if (show) {
                stopSpeaking();
            }
        }

        // Fungsi untuk menerjemahkan nilai slider ke instruksi plot twist (Ketat + Gaya Bahasa)
        function getTwistInstruction(value) {
            if (value >= 76) return { 
                label: "Ekstrem",
                styleInstruction: "Gunakan GAYA BAHASA HIPERBOLIK, DRAMATIS, DAN MENGGEBU-GEBU. Narasi harus kejam dan fatalistik.",
                instruction: "MANDATORI: Perkenalkan plot twist tingkat ekstrem dan **TAK TERHINDARKAN**. Twist harus secara fundamental MENGHANCURKAN DAN MENGUBAH PREMIS TEMA/DUNIA/PERAN TOKOH UTAMA yang diberikan. Twist harus TERINTEGRASI PENUH dan disampaikan dalam narasi pembuka TANPA PENGECUALIAN. Dampaknya harus segera terasa." 
            };
            if (value >= 51) return { 
                label: "Tinggi", 
                styleInstruction: "Gunakan GAYA BAHASA FORMAL, INTENS, DAN MISTERIUS. Fokus pada ketidakpastian dan implikasi besar.",
                instruction: "WAJIB memperkenalkan plot twist tingkat tinggi dan mengejutkan. Twist ini harus MENGUBAH TUJUAN UTAMA Tokoh Utama atau mengungkapkan FAKTA KRITIS tentang latar belakangnya yang tersembunyi. Twist harus terkait erat dan menjadi inti narasi awal." 
            };
            if (value >= 26) return { 
                label: "Sedang", 
                styleInstruction: "Gunakan GAYA BAHASA ATMOSFERIK dan SEDIKIT MENDESAK. Fokus pada deskripsi suasana yang tiba-tiba berbahaya.",
                instruction: "Perkenalkan plot twist tingkat sedang. Twist ini harus berupa komplikasi besar dan mendesak yang secara langsung terkait dengan salah satu elemen SETUP (Tema, Tokoh, atau Deskripsi Dunia). Narasi pembuka harus menciptakan masalah ini." 
            };
            return { 
                label: "Rendah", 
                styleInstruction: "Gunakan GAYA BAHASA DESKRIPTIF, TENANG, dan SEDIKIT CURIOUS. Fokus pada pengamatan yang tidak biasa.",
                instruction: "Perkenalkan plot twist tingkat rendah, yaitu sebuah rintangan tak terduga atau pengungkapan detail kecil yang mengubah cara Tokoh Utama melihat situasi saat ini. Twist ini harus tetap relevan dan disampaikan dengan jelas di akhir narasi awal." 
            };
        }

        // Listener untuk pergerakan slider
        plotTwistSlider.addEventListener('input', () => {
            const value = parseInt(plotTwistSlider.value);
            const twistLevel = getTwistInstruction(value);
            twistValueLabel.textContent = `${twistLevel.label} (${value}) - ${twistLevel.styleInstruction.split(',')[0].replace('Gunakan GAYA BAHASA ', '')}`;
        });

        // Inisialisasi label slider saat dimuat
        window.addEventListener('load', () => {
            const value = parseInt(plotTwistSlider.value);
            const twistLevel = getTwistInstruction(value);
            twistValueLabel.textContent = `${twistLevel.label} (${value}) - ${twistLevel.styleInstruction.split(',')[0].replace('Gunakan GAYA BAHASA ', '')}`;
        });


        // Fungsi utama untuk memanggil AI Game Master
        async function callGemini(userQuery) {
            const payload = {
                contents: chatHistory,
                systemInstruction: {
                    parts: [{ text: systemPrompt }]
                },
            };

            const maxRetries = 3;
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const result = await response.json();
                    
                    const text = result.candidates?.[0]?.content?.parts?.[0]?.text;

                    if (text) {
                        return text;
                    } else {
                        return "GM terdiam sejenak. Silakan coba tindakan lain yang lebih jelas dan sesuai tema petualangan.";
                    }

                } catch (error) {
                    console.error("Kesalahan API:", error);
                    if (i < maxRetries - 1) {
                        const delay = Math.pow(2, i) * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        console.log(`Mencoba lagi (${i + 2}/${maxRetries})...`);
                    } else {
                        statusMessage.textContent = "Error: Tidak dapat menghubungi AI Game Master. Periksa koneksi atau coba lagi.";
                        statusMessage.classList.remove('hidden');
                        statusMessage.classList.remove('text-blue-600');
                        statusMessage.classList.add('text-red-600');
                        return null; 
                    }
                }
            }
        }

        // Event listener untuk SETUP FORM
        setupForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            
            if (isProcessing) return;

            toggleLoading(true, true);

            const theme = setupTheme.value.trim();
            const protagonist = setupProtagonist.value.trim();
            const description = setupDescription.value.trim();
            const twistValue = parseInt(plotTwistSlider.value); 
            const twist = getTwistInstruction(twistValue); 

            // System prompt diatur ulang dengan tema dan tokoh
            systemPrompt = `Anda adalah Game Master (GM) yang imajinatif untuk game petualangan berbasis teks.
            Tema cerita adalah ${theme}. Tokoh utama adalah ${protagonist}. Latar dunia adalah ${description}.
            Perintah Penting: Selalu berikan respons dalam bentuk narasi MURNI. Jangan pernah menyertakan awalan seperti "GM:", "Game Master:", "Narator:", atau label lainnya.
            Anda harus merespons tindakan pengguna dengan menjelaskan konsekuensinya dan memajukan alur cerita.
            Jaga respons tetap ringkas (maksimal 3 kalimat), fokus pada suasana, dan aksi.
            Cerita harus selalu bertema fantasi dan petualangan, serta SAMA SEKALI tidak boleh memuat konten seksual, kekerasan berdarah (gore), atau konten dewasa lainnya.
            Selalu tanggapi dalam Bahasa Indonesia.`;

            // Query awal yang SANGAT MEMAKSA plot twist sesuai instruksi ketat
            const initialUserQuery = `Mulai narasi. Gunakan Tema: "${theme}", Tokoh: "${protagonist}", dan Latar: "${description}" sebagai inti cerita.
            WAJIB Terapkan GAYA BAHASA berikut untuk narasi pembuka: "${twist.styleInstruction}".
            
            KONTEN PLOT TWIST: ${twist.instruction}.
            
            PASTIKAN PLOT TWIST INI SUDAH TERJADI PADA RESPON PERTAMA ANDA dan sangat relevan dengan salah satu elemen SETUP di atas.
            Narasi harus diakhiri dengan twist ini dan permintaan untuk aksi pertama pemain.`;
            
            chatHistory.push({ role: "user", parts: [{ text: initialUserQuery }] });
            
            const gmResponseText = await callGemini(initialUserQuery);

            toggleLoading(false, true);

            if (gmResponseText) {
                setupScreen.classList.add('hidden');
                gameScreen.classList.remove('hidden');
                mainTitle.textContent = "Petualangan: " + theme;
                mainSubtitle.textContent = "Tokoh Anda: " + protagonist;

                addMessage(gmResponseText, 'gm');
                chatHistory.push({ role: "model", parts: [{ text: gmResponseText }] });
            }
        });


        // Event listener untuk GAME FORM 
        form.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            
            if (isProcessing) return;

            const userQuery = input.value.trim();
            if (!userQuery) return;

            statusMessage.classList.add('hidden');
            statusMessage.classList.remove('text-blue-600', 'text-red-600');

            if (recognition && voiceButton.classList.contains('bg-red-500')) {
                recognition.onend();
            }

            toggleLoading(true, false);

            addMessage(userQuery, 'user');
            chatHistory.push({ role: "user", parts: [{ text: userQuery }] });

            input.value = '';

            const gmResponseText = await callGemini(userQuery);

            if (gmResponseText) {
                addMessage(gmResponseText, 'gm');
                chatHistory.push({ role: "model", parts: [{ text: gmResponseText }] });
            }
            
            toggleLoading(false, false);
        });
        
        // Hentikan TTS saat halaman ditinggalkan atau di-refresh
        window.addEventListener('beforeunload', stopSpeaking);
        window.addEventListener('pagehide', stopSpeaking);
    </script>
</body>
</html>
