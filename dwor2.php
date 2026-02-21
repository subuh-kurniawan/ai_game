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
    <title>Simulasi Proyek SMK: Text RPG</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
        }
        .chat-container {
            height: calc(100vh - 200px); /* Adjust based on footer/header height */
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        .message-ai {
            background-color: #e0f2f1; /* Teal 50 */
            border-left: 4px solid #00796b; /* Teal 700 */
        }
        .message-player {
            background-color: #f3e5f5; /* Purple 50 */
            border-right: 4px solid #4a148c; /* Purple 900 */
        }
        .option-button {
            transition: all 0.2s;
            white-space: normal; /* Allow text wrapping */
            min-height: 40px;
        }
        /* Style untuk tombol TTS dan MIC */
        .interactive-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
            margin-left: 8px;
            z-index: 10; 
        }
        .interactive-button:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
        /* Style khusus untuk tombol mic */
        #mic-button {
            padding: 1rem; 
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#00796b', // Dark Teal
                        'secondary': '#4a148c', // Dark Purple
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex flex-col">
    <div id="setup-screen" class="flex flex-col items-center justify-center min-h-screen p-4 bg-gray-50">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg">
            <h1 class="text-3xl font-bold text-center text-primary mb-6">Simulasi Proyek SMK</h1>
            <p class="text-center text-gray-600 mb-8">Uji ketelitian, keselamatan, efisiensi, dan kreativitas Anda dalam skenario perakitan!</p>

            <div class="mb-6">
                <label for="major-select" class="block text-sm font-medium text-gray-700 mb-2">Pilih Jurusan Anda:</label>
                <!-- Select Jurusan -->
                <select id="major-select" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary transition duration-150 ease-in-out shadow-sm mb-3">
                    <option value="" disabled selected>-- Pilih Jurusan --</option>
                    <option value="Teknik Komputer dan Jaringan (TKJ)">Teknik Komputer dan Jaringan (TKJ)</option>
                    <option value="Teknik Otomotif (TO)">Teknik Otomotif (TO)</option>
                    <option value="Teknik Instalasi Tenaga Listrik (TITL)">Teknik Instalasi Tenaga Listrik (TITL)</option>
                    <option value="Tata Boga (TB)">Tata Boga (TB)</option>
                    <option value="custom">Lainnya/Kustom...</option>
                </select>
                <!-- Custom Jurusan Input (Awalnya Tersembunyi) -->
                <input type="text" id="custom-major-input" placeholder="Masukkan nama jurusan kustom..." class="w-full p-3 border border-gray-300 rounded-lg focus:ring-secondary focus:border-secondary transition duration-150 ease-in-out shadow-sm hidden">
            </div>

            <div class="mb-8">
                <label for="theme-input" class="block text-sm font-medium text-gray-700 mb-2">Tema/Skenario Proyek (misal: Perakitan Jaringan LAN Sekolah, Tune-up Mesin Diesel, Instalasi Smart Home):</label>
                <input type="text" id="theme-input" placeholder="Masukkan tema proyek Anda..." class="w-full p-3 border border-gray-300 rounded-lg focus:ring-secondary focus:border-secondary transition duration-150 ease-in-out shadow-sm">
            </div>

            <button id="start-button" class="w-full bg-secondary hover:bg-purple-700 text-white font-bold py-3 rounded-lg transition duration-300 ease-in-out shadow-lg transform hover:scale-[1.01] disabled:opacity-50" disabled>
                Mulai Simulasi
            </button>

            <p id="setup-error" class="text-red-500 text-sm mt-4 hidden text-center">Harap pilih jurusan dan masukkan tema proyek.</p>
        </div>
    </div>

    <div id="game-screen" class="hidden flex-1 flex flex-col max-w-4xl mx-auto w-full bg-white shadow-xl rounded-xl mt-4 mb-4">
        <!-- Header -->
        <header class="p-4 border-b border-gray-200 bg-primary rounded-t-xl">
            <h2 class="text-xl font-semibold text-white">Proyek: <span id="project-title" class="font-light italic"></span></h2>
            <p class="text-sm text-gray-200">Anda berinteraksi dengan AI Game Master (Instruktur 🧑‍🏫 | Konsultan 🧰 | Evaluator 🧪)</p>
        </header>

        <!-- Chat Log -->
        <div id="chat-log" class="chat-container p-4 space-y-4">
            <!-- Pesan akan dimasukkan di sini secara dinamis -->
        </div>

        <!-- Input Area (Dynamic) -->
        <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
            <div id="loading-indicator" class="hidden text-center text-sm text-secondary mb-2">
                <div class="animate-spin inline-block w-4 h-4 border-2 border-secondary border-t-transparent rounded-full mr-2"></div>
                AI sedang berpikir...
            </div>
            <div id="error-message" class="hidden text-red-500 text-sm mb-2"></div>

            <!-- Options Container (Initially hidden, used for AI-generated buttons) -->
            <div id="options-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2 hidden">
                <!-- Opsi tombol akan dirender di sini -->
            </div>

            <!-- End Game Actions Container (Akan muncul saat game selesai) -->
            <div id="end-game-actions" class="hidden flex flex-col space-y-3">
                <p class="text-lg font-semibold text-center text-primary">Simulasi Selesai!</p>
                <button id="download-button" class="bg-teal-500 hover:bg-teal-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300 shadow-md">
                    Unduh Laporan Proyek (.txt)
                </button>
                <button id="restart-button" class="bg-secondary hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 shadow-md">
                    Mulai Simulasi Lain
                </button>
            </div>

            <!-- Fallback Text Input (Used only when options are hidden, or for final message) -->
            <div id="text-input-container" class="flex space-x-2 items-center">
                <!-- Tombol Mikrofon -->
                <button id="mic-button" class="interactive-button bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300 ease-in-out shadow-md disabled:opacity-50" title="Input Suara (Bahasa Indonesia)" disabled>
                    <!-- Mic Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7v0a7 7 0 01-7-7v0a7 7 0 017-7v0a7 7 0 017 7zM12 19v3"></path></svg>
                </button>
                <input type="text" id="action-input" placeholder="Masukkan tindakan Anda..." class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-secondary focus:border-secondary shadow-sm disabled:bg-gray-100" disabled>
                <button id="send-action-button" class="bg-secondary hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 ease-in-out shadow-md disabled:opacity-50" disabled>
                    Kirim
                </button>
            </div>
        </div>
    </div>

    <!-- Script Utama -->
    <script type="module">
        // Global variables
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
       const apiKey =  <?php echo $apiKeyJson; ?>; 
         const md =  <?php echo json_encode($model); ?>;

        // --- State Management ---
        let chatHistory = [];
        let major = '';
        let theme = '';
        let isGameRunning = false;
        let isLoading = false;
        
        // **VARIABEL BARU UNTUK BROWSER TTS**
        let currentUtterance = null; 
        let voicesLoaded = false;
        let availableVoices = [];

        // --- DOM Elements ---
        const setupScreen = document.getElementById('setup-screen');
        const gameScreen = document.getElementById('game-screen');
        const majorSelect = document.getElementById('major-select');
        const customMajorInput = document.getElementById('custom-major-input'); 
        const themeInput = document.getElementById('theme-input');
        const startButton = document.getElementById('start-button');
        const chatLog = document.getElementById('chat-log');
        const actionInput = document.getElementById('action-input');
        const sendActionButton = document.getElementById('send-action-button');
        const projectTitle = document.getElementById('project-title');
        const loadingIndicator = document.getElementById('loading-indicator');
        const errorMessage = document.getElementById('error-message');
        const setupError = document.getElementById('setup-error');
        const optionsContainer = document.getElementById('options-container');
        const textInputContainer = document.getElementById('text-input-container');
        const micButton = document.getElementById('mic-button');
        
        // Elemen baru untuk fitur 'Selesai'
        const endGameActions = document.getElementById('end-game-actions');
        const downloadButton = document.getElementById('download-button');
        const restartButton = document.getElementById('restart-button');


        // --- TTS & MIC Icons ---
        const playIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path d="M6.394 2.09A.75.75 0 0 0 6 2.75v14.5c0 .597.437 1.09.933 1.155l.135.005L17.75 10l-10.61-8.5c-.17-.136-.363-.205-.561-.205Z" /></svg>`;
        const pauseIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0ZM9.25 8.5A.75.75 0 0 0 8.5 9v2.5a.75.75 0 0 0 1.5 0V9a.75.75 0 0 0-.75-.75Zm3.5 0A.75.75 0 0 0 12 9v2.5a.75.75 0 0 0 1.5 0V9a.75.75 0 0 0-.75-.75Z" clip-rule="evenodd" /></svg>`;
        const errorIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="red" class="w-5 h-5"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0ZM7.25 7.25a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Z" clip-rule="evenodd" /></svg>`;

        const micIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7v0a7 7 0 01-7-7v0a7 7 0 017-7v0a7 7 0 017 7zM12 19v3"></path></svg>`;
        const listeningIcon = `<svg class="animate-pulse w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7 6a3 3 0 016 0v5a3 3 0 01-6 0V6zm6 6a1 1 0 001-1V6a1 1 0 10-2 0v5a1 1 0 001 1zM5 10a1 1 0 011-1V6a1 1 0 112 0v4a1 1 0 01-1 1zm8 0a1 1 0 011-1V6a1 1 0 112 0v4a1 1 0 01-1 1zM10 20a1 1 0 001-1v-3a1 1 0 00-2 0v3a1 1 0 001 1zM3 10a1 1 0 011-1V6a1 1 0 112 0v4a1 1 0 01-1 1z" clip-rule="evenodd"></path></svg>`;
        
        // Fungsi untuk memuat suara yang tersedia
        function loadVoices() {
            if ('speechSynthesis' in window && !voicesLoaded) {
                availableVoices = window.speechSynthesis.getVoices();
                voicesLoaded = true;
                console.log("TTS Voices loaded:", availableVoices.length);
            }
        }
        
        if ('speechSynthesis' in window) {
             window.speechSynthesis.onvoiceschanged = loadVoices;
             loadVoices(); 
        }

        // --- Browser TTS (Web Speech API) Functions ---
        
        function speakText(text, button) {
            if (!('speechSynthesis' in window) || !text || isLoading) {
                console.warn("TTS tidak didukung atau sedang loading.");
                return;
            }

            if (currentUtterance && currentUtterance.button === button) {
                window.speechSynthesis.cancel();
                stopAndResetAudio(); 
                return;
            }

            stopAndResetAudio(button);

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID'; 
            
            if (!voicesLoaded) loadVoices(); 
            
            const indoVoice = availableVoices.find(v => v.lang === 'id-ID' || v.lang.startsWith('id'));
            if (indoVoice) {
                utterance.voice = indoVoice;
            } else {
                console.warn("Suara Bahasa Indonesia tidak ditemukan. Menggunakan suara default.");
            }
            
            utterance.rate = 1.0;   // kecepatan normal
            utterance.pitch = 1.0;  // pitch alami
            
            utterance.button = button; 
            currentUtterance = utterance;

            utterance.onstart = () => {
                button.innerHTML = pauseIcon;
                button.dataset.state = 'playing';
                button.disabled = false;
            };

            utterance.onend = () => {
                button.innerHTML = playIcon;
                button.dataset.state = 'ready';
                button.disabled = false;
                currentUtterance = null;
            };
            
            utterance.onerror = (event) => {
                console.error("Speech synthesis error:", event.error);
                button.innerHTML = errorIcon;
                button.dataset.state = 'error';
                button.disabled = false;
                currentUtterance = null;
            };

            button.dataset.state = 'playing'; 
            window.speechSynthesis.speak(utterance);
        }

        function stopAndResetAudio(buttonToExclude = null) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel(); 
                currentUtterance = null; 
            }

            document.querySelectorAll('.interactive-button[data-type="tts"]').forEach(btn => {
                if (btn !== buttonToExclude) {
                    btn.innerHTML = playIcon;
                    btn.dataset.state = 'ready';
                    btn.disabled = false;
                }
            });
        }

        // Expose TTS function globally
        window.speakText = speakText;
        window.stopAndResetAudio = stopAndResetAudio; 


        // --- Voice Input (Speech Recognition) Functions ---
        let recognition = null;
        let isListening = false;

        function setMicState(state) {
            isListening = state === 'listening';
            micButton.disabled = !isGameRunning || isLoading; 
            
            if (state === 'listening') {
                micButton.innerHTML = listeningIcon;
                micButton.classList.remove('bg-red-500', 'hover:bg-red-600');
                micButton.classList.add('bg-secondary', 'hover:bg-purple-700'); 
                micButton.title = 'Mendengarkan... (Klik untuk berhenti)';
            } else if (state === 'disabled') {
                // Biarkan disabled
            } else { // 'ready' or 'loading'
                micButton.innerHTML = micIcon;
                micButton.classList.add('bg-red-500', 'hover:bg-red-600');
                micButton.classList.remove('bg-secondary', 'hover:bg-purple-700');
                micButton.title = 'Input Suara (Bahasa Indonesia)';
                micButton.disabled = !isGameRunning || isLoading; 
            }
        }

        function startVoiceInput() {
            if (!isGameRunning || isLoading) return;

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                // Mengganti alert dengan console.warn dan pemberitahuan di UI jika memungkinkan
                errorMessage.textContent = 'Maaf, peramban Anda tidak mendukung Input Suara (Speech Recognition).';
                errorMessage.classList.remove('hidden');
                return;
            }

            stopAndResetAudio(); 
            
            if (!recognition) {
                recognition = new SpeechRecognition();
                recognition.continuous = false; 
                recognition.interimResults = true; 
                recognition.lang = 'id-ID'; 
            }

            if (isListening) {
                recognition.stop(); 
                return; 
            }

            let finalTranscript = '';
            actionInput.value = ''; 

            recognition.onstart = () => {
                setMicState('listening');
                errorMessage.classList.add('hidden');
            };

            recognition.onresult = (event) => {
                let interimTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript + ' ';
                    } else {
                        interimTranscript += transcript;
                    }
                }
                actionInput.value = finalTranscript.trim() || interimTranscript.trim();
            };

            recognition.onend = () => {
                setMicState('ready');
                if (finalTranscript.trim()) {
                    const submissionText = finalTranscript.trim();
                    actionInput.value = submissionText; 
                    submitAction(submissionText);
                }
            };

            recognition.onerror = (event) => {
                setMicState('ready');
                if (event.error !== 'no-speech' && event.error !== 'aborted') {
                    errorMessage.classList.remove('hidden');
                    errorMessage.textContent = 'Gagal merekam suara: ' + event.error;
                }
            };

            try {
                recognition.start();
            } catch (e) {
                console.warn("Recognition already started or error:", e);
                setMicState('ready');
            }
        }
        
        // --- End Game & Download Functions ---

        /** * Mereset game state dan kembali ke layar setup.
         */
        function resetGame() {
            // Reset state
            chatHistory = [];
            major = '';
            theme = '';
            isGameRunning = false;
            isLoading = false;
            
            // Clear DOM
            chatLog.innerHTML = '';
            themeInput.value = '';
            customMajorInput.value = '';
            majorSelect.value = ''; 
            customMajorInput.classList.add('hidden');
            
            // Switch screens
            gameScreen.classList.add('hidden');
            endGameActions.classList.add('hidden');
            setupScreen.classList.remove('hidden');
            
            // Re-initialize UI states
            checkReadiness(); 
            displayInitialMessage(); 
            setMicState('disabled'); 
        }

        /**
         * Mengubah riwayat chat menjadi string teks yang diformat.
         * @returns {string} Seluruh riwayat chat dalam format teks.
         */
        function formatChatHistoryForDownload() {
            let output = `--- LAPORAN SIMULASI PROYEK SMK ---\n\n`;
            output += `JURUSAN: ${major}\n`;
            output += `TEMA PROYEK: ${theme}\n`;
            output += `WAKTU SIMULASI: ${new Date().toLocaleString('id-ID')}\n\n`;
            output += `=================================================\n\n`;
            
            chatHistory.forEach(message => {
                const role = message.role === 'user' ? 'ANDA' : 'AI MASTER';
                let text = message.parts[0].text;
                
                // Cleanup AI text: remove role tags and OPTIONS tag for cleaner output
                text = text.replace(/^(🧑‍🏫: |🧰: |🧪: )/m, '').replace(/OPTIONS:[\s\S]*/i, '').trim();
                
                output += `[${role}]\n${text}\n\n`;
            });
            
            output += `=================================================\n`;
            output += `--- AKHIR LAPORAN ---`;
            return output;
        }

        /**
         * Memicu pengunduhan file teks.
         */
        function handleDownload() {
            const historyText = formatChatHistoryForDownload();
            // Buat nama file dari Jurusan dan bersihkan spasi
            const filename = `Laporan_Simulasi_SMK_${major.replace(/[^a-zA-Z0-9]/g, '_')}.txt`; 

            const blob = new Blob([historyText], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        
        // --- Utility Functions (Other) ---
        function getRoleName(icon) {
            switch (icon.trim()) {
                case '🧑‍🏫:': return 'Instruktur';
                case '🧰:': return 'Konsultan Teknis';
                case '🧪:': return 'Evaluator';
                default: return 'AI Game Master';
            }
        }
        
        function appendMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'p-3 rounded-xl shadow-md';
            let textContent = text.trim(); 
            
            if (sender === 'AI') {
                textContent = textContent.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>'); 
                textContent = textContent.replace(/#+\s*/g, ''); 
                textContent = textContent.replace(/\n[-_]{3,}\n/g, '<hr class="my-3 border-gray-300">');
                
                let roleLabel = '';
                const roleMatch = textContent.match(/^(🧑‍🏫: |🧰: |🧪: )/);
                let cleanText = textContent;
                let roleIcon = '';

                if (roleMatch) {
                    roleIcon = roleMatch[0];
                    cleanText = textContent.substring(roleMatch[0].length).trim();
                    
                    const ttsText = cleanText.split('OPTIONS:')[0].trim().replace(/<br\s*\/?>/g, ' ').replace(/<[^>]*>?/gm, '');

                    const playButtonHtml = `
                        <button class="interactive-button text-primary hover:text-teal-600 transition duration-150 ml-2" title="Putar Audio" 
                            onclick="window.speakText(this.dataset.text, this)" 
                            data-text="${ttsText.replace(/"/g, '&quot;')}"
                            data-state="ready"
                            data-type="tts">
                            ${playIcon}
                        </button>
                    `;
                    
                    roleLabel = `<p class="font-semibold text-primary inline-flex items-center">${roleIcon.trim()} ${getRoleName(roleIcon)}${playButtonHtml}</p>`;
                } else {
                    roleLabel = `<p class="font-semibold text-primary">AI Game Master</p>`;
                }
                
                const displayContent = cleanText.split('OPTIONS:')[0].trim().replace(/\n/g, '<br>');
                messageDiv.innerHTML = roleLabel + `<p class="mt-1 text-gray-700">${displayContent}</p>`;
                messageDiv.className += ' message-ai self-start text-left max-w-[90%] mx-2';
            
            } else {
                messageDiv.className += ' message-player self-end text-right max-w-[90%] mx-2';
                let roleLabel = `<p class="font-semibold text-secondary">Anda</p>`;
                messageDiv.innerHTML = roleLabel + `<p class="mt-1 text-gray-700">${textContent.replace(/\n/g, '<br>')}</p>`;
            }

            chatLog.appendChild(messageDiv);
            chatLog.scrollTop = chatLog.scrollHeight;
        }

        function setLoadingState(loading) {
            isLoading = loading;
            loadingIndicator.classList.toggle('hidden', !loading);

            actionInput.disabled = loading || !isGameRunning;
            sendActionButton.disabled = loading || !isGameRunning;
            setMicState(loading ? 'loading' : isListening ? 'listening' : 'ready'); 
            
            if (loading) {
                 optionsContainer.classList.add('hidden'); 
                 endGameActions.classList.add('hidden'); // Sembunyikan tombol end game saat loading
                 stopAndResetAudio(); 
            }
        }

        function displayOptions(options) {
            optionsContainer.innerHTML = '';
            
            endGameActions.classList.add('hidden'); // Pastikan tombol end game tersembunyi
            textInputContainer.classList.remove('hidden');
            actionInput.disabled = !isGameRunning;
            sendActionButton.disabled = !isGameRunning;
            setMicState('ready'); 

            if (!options || options.length === 0) {
                optionsContainer.classList.add('hidden');
                actionInput.placeholder = isGameRunning ? 'Masukkan tindakan Anda...' : 'Simulasi Selesai. Silakan klik tombol di bawah.';
                micButton.disabled = !isGameRunning;
                return;
            }

            optionsContainer.classList.remove('hidden');
            actionInput.placeholder = 'Masukkan tindakan KUSTOM, atau pertanyaan klarifikasi...';
            micButton.disabled = !isGameRunning;

            options.forEach(optionText => {
                const button = document.createElement('button');
                button.textContent = optionText;
                button.className = 'option-button bg-primary hover:bg-teal-700 text-white font-medium py-3 px-4 rounded-xl shadow-md transition duration-200 text-sm break-words';
                button.addEventListener('click', () => submitAction(optionText));
                optionsContainer.appendChild(button);
            });
        }
        
        function submitAction(action) {
            const actionText = action.trim();
            if (!actionText || !isGameRunning || isLoading) return;

            stopAndResetAudio();
            
            chatHistory.push({ role: "user", parts: [{ text: actionText }] });
            appendMessage(actionText, 'Player');

            callGeminiApi(actionText);

            actionInput.value = '';
            
            displayOptions(null); 
        }

        async function callGeminiApi(userQuery) {
            setLoadingState(true);
            errorMessage.classList.add('hidden');
            const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey}`;

            const systemPrompt = `Anda adalah seorang Game Master (GM) yang menjalankan simulasi RPG berbasis teks untuk siswa SMK (Sekolah Menengah Kejuruan) dalam skenario perakitan dan proyek teknis. Anda harus menilai kinerja pemain secara berkelanjutan dan memberikan umpan balik yang relevan dengan jurusan dan tema yang dipilih.

**PENTING:** JANGAN gunakan format Markdown seperti heading (seperti #, ##, ###) atau terlalu banyak **bolding** dalam teks narasi utama. Gunakan format paragraf biasa.

Tugas Anda meliputi tiga peran:
1.  **Instruktur (🧑‍🏫):** Berikan teori, **tangani pertanyaan 'mengapa' atau 'bagaimana'**, jelaskan langkah prosedural, dan pastikan keselamatan kerja (K3).
2.  **Konsultan Teknis (🧰):** Berikan saran optimalisasi, petunjuk terselubung (clue), dan panduan untuk efisiensi/kreativitas, **termasuk merespons permintaan klarifikasi**.
3.  **Evaluator (🧪):** Ketika pemain mengetik "Selesai", berikan skor (0-100) dan umpan balik rinci tentang **Ketelitian, Keselamatan, Efisiensi, dan Kreativitas**. Setelah evaluasi, permainan berakhir.

**KONTEKS GAME:**
* **Jurusan:** ${major}
* **Tema Proyek:** ${theme}
* **Mode:** Interaktif, berbasis giliran (turn-based).
* **OUTPUT WAJIB:** Setiap respons Anda HARUS dimulai dengan identitas peran yang paling dominan saat itu, diikuti dengan respons Anda. Jika permainan belum berakhir, respons Anda HARUS diakhiri dengan baris baru diikuti oleh tag **OPTIONS:** dan daftar minimal 3 opsi tindakan. Format opsi:
[Opsi 1|Opsi 2|Opsi 3|Opsi Tambahan|Selesai]
Contoh: 🧑‍🏫: Langkah pertama adalah... **OPTIONS:** [Ambil Obeng|Pakai Sarung Tangan|Cek Skema Proyek|Selesai]
Pastikan format OPTIONS: [...] selalu ada selama game berjalan.
`;

            const currentHistory = [...chatHistory, { role: "user", parts: [{ text: userQuery }] }];

            const payload = {
                contents: currentHistory.slice(-10), 
                systemInstruction: {
                    parts: [{ text: systemPrompt }]
                },
            };

            const maxRetries = 5;
            for (let attempt = 0; attempt < maxRetries; attempt++) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 429 && attempt < maxRetries - 1) {
                        const delay = Math.pow(2, attempt) * 1000 + Math.random() * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    }

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    setLoadingState(false);

                    const candidate = result.candidates?.[0];
                    if (candidate && candidate.content?.parts?.[0]?.text) {
                        const aiText = candidate.content.parts[0].text;
                        chatHistory.push({ role: "model", parts: [{ text: aiText }] });
                        appendMessage(aiText, 'AI');

                        const optionsMatch = aiText.match(/OPTIONS:([\s\S]*?)\[([^\]]+)\]/i);
                        
                        if (optionsMatch) {
                            const optionsString = optionsMatch[2]; 
                            // PERBAIKAN: Menggunakan .map(o => o.trim()) yang benar
                            const optionsArray = optionsString.split('|').map(o => o.trim()).filter(o => o.length > 0);
                            displayOptions(optionsArray);

                        } else if (userQuery.toLowerCase().includes('selesai') || aiText.includes('Evaluator (🧪)')) {
                            // --- LOGIKA GAME SELESAI ---
                            isGameRunning = false;
                            optionsContainer.classList.add('hidden');
                            textInputContainer.classList.add('hidden'); // Sembunyikan input teks/mic
                            endGameActions.classList.remove('hidden'); // Tampilkan tombol End Game
                            setMicState('disabled'); 
                            // --- AKHIR LOGIKA GAME SELESAI ---
                        } else {
                            displayOptions(null);
                        }

                    } else {
                        throw new Error("Respons AI kosong atau tidak terstruktur.");
                    }
                    return; 

                } catch (error) {
                    console.error("Gemini API call failed:", error);
                    setLoadingState(false);
                    errorMessage.classList.remove('hidden');
                    errorMessage.textContent = 'Gagal terhubung ke AI. Coba lagi. (Error: ' + error.message + ')';
                    displayOptions(null); 
                }
            }
        }

        // --- Game Setup/Start Logic (Unchanged) ---
        function checkReadiness() {
            const selectedMajor = majorSelect.value;
            const themeText = themeInput.value.trim();
            let majorReady = false;

            if (selectedMajor && selectedMajor !== 'custom') {
                majorReady = true;
            } else if (selectedMajor === 'custom' && customMajorInput.value.trim()) {
                majorReady = true;
            }

            startButton.disabled = !(majorReady && themeText);
            setupError.classList.toggle('hidden', majorReady && themeText);
        }

        function displayInitialMessage() {
             const welcomeMessage = "Selamat datang di Simulasi Proyek SMK! Silakan pilih jurusan Anda dan masukkan tema proyek untuk memulai tantangan. Anda akan dipandu oleh AI Game Master.";
             appendMessage(welcomeMessage, 'AI');
        }

        function handleStartGame() {
            const selectedMajor = majorSelect.value;
            theme = themeInput.value.trim();

            if (selectedMajor === 'custom') {
                major = customMajorInput.value.trim();
            } else {
                major = selectedMajor;
            }

            if (!major || !theme) {
                setupError.classList.remove('hidden');
                return;
            }

            setupScreen.classList.add('hidden');
            gameScreen.classList.remove('hidden');
            isGameRunning = true;
            projectTitle.textContent = `${major} - ${theme}`;
            setupError.classList.add('hidden');
            
            setMicState('ready'); 
            
            const initialQuery = `Saya siap memulai simulasi. Jurusan saya adalah ${major} dengan tema proyek: ${theme}. Berikan langkah awal dan teori dasar sebagai Instruktur.`;
            submitAction(initialQuery); 
        }

        function handleSendActionFromInput() {
            submitAction(actionInput.value);
        }

        // --- Event Listeners ---

        majorSelect.addEventListener('change', () => {
            if (majorSelect.value === 'custom') {
                customMajorInput.classList.remove('hidden');
                customMajorInput.focus();
            } else {
                customMajorInput.classList.add('hidden');
                customMajorInput.value = ''; 
            }
            checkReadiness();
        });

        customMajorInput.addEventListener('input', checkReadiness); 
        themeInput.addEventListener('input', checkReadiness); 
        startButton.addEventListener('click', handleStartGame);
        sendActionButton.addEventListener('click', handleSendActionFromInput);
        micButton.addEventListener('click', startVoiceInput); 
        
        // Event Listener Baru
        restartButton.addEventListener('click', resetGame);
        downloadButton.addEventListener('click', handleDownload);


        actionInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleSendActionFromInput();
            }
        });

        // Set awal state
        document.addEventListener('DOMContentLoaded', () => {
            setupScreen.classList.remove('hidden'); 
            gameScreen.classList.add('hidden');
            
            chatLog.innerHTML = '';
            displayInitialMessage();

            setLoadingState(false);
            displayOptions(null);
            setMicState('disabled'); 
        });

    </script>
</body>
</html>
