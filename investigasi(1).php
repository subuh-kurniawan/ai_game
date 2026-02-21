<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Detektif AI: Jejak Kasus</title>
    <!-- Memuat Tailwind CSS untuk styling modern dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom styling untuk tema Detektif Cyber Glass */
        body {
            font-family: 'Inter', sans-serif;
            /* Latar Belakang Gradasi Dinamis untuk Efek Kaca */
            background: linear-gradient(135deg, #1f004b, #290066, #4b0082);
            background-size: 400% 400%;
            animation: gradient-animation 15s ease infinite;
            overflow-x: hidden;
            min-height: 100vh;
            position: relative; /* Penting untuk pseudo-element ::before */
        }
        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- ANIMASI DETEKTIF LATAR BELAKANG BARU --- */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Membuat pola grid digital yang sangat subtil dengan warna neon */
            background-image: linear-gradient(0deg, transparent 98%, rgba(0, 184, 148, 0.1) 100%),
                              linear-gradient(90deg, transparent 98%, rgba(0, 184, 148, 0.1) 100%);
            background-size: 70px 70px; /* Ukuran pola */
            opacity: 0.1; /* Sangat subtil */
            z-index: -1; /* Ditempatkan di belakang semua konten */
            animation: background-move 90s linear infinite; /* Gerakan sangat lambat */
        }

        @keyframes background-move {
            from {
                background-position: 0 0;
            }
            to {
                background-position: 700px 700px; /* Gerakan diagonal yang sangat jauh dan lambat */
            }
        }
        /* ------------------------------------------- */


        /* Kelas Dasar Glassmorphism */
        .glass-effect {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            background-color: rgba(255, 255, 255, 0.05); /* Transparansi sangat sedikit */
            border: 1px solid rgba(255, 255, 255, 0.15); /* Border tipis */
            box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.4); /* Shadow untuk kedalaman */
            border-radius: 16px;
        }

        /* Log Game dengan efek Glass */
        #game-log {
            height: 70vh;
            /* Mengganti background statis dengan kelas glass-effect */
            overflow-y: auto;
            scroll-behavior: smooth;
            width: 100%;
        }

        /* Warna pesan di atas latar belakang kaca */
        .message-gm {
            background-color: rgba(0, 184, 148, 0.1); /* Transparan dengan aksen neon */
            border-left: 4px solid #00cec9; /* Blue accent for GM */
        }
        .message-player {
            background-color: rgba(255, 121, 198, 0.1); /* Transparan dengan aksen pink */
            border-left: 4px solid #ff79c6; /* Pink accent for player */
        }
        .terminal-text {
            color: #00b894; /* Neon green text */
        }
        
        /* Input & Select fields */
        .glass-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .glass-input:focus {
             border-color: #00b894 !important;
             box-shadow: 0 0 0 2px #00b894;
        }

        /* Custom style untuk slider */
        #difficulty-slider {
            -webkit-appearance: none;
            appearance: none;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        #difficulty-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            background: #00b894; /* Neon green thumb */
            cursor: pointer;
            border-radius: 50%;
            border: 3px solid #1a1a2e;
            box-shadow: 0 0 8px rgba(0, 184, 148, 0.7);
        }
        #difficulty-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            background: #00b894;
            cursor: pointer;
            border-radius: 50%;
            border: 3px solid #1a1a2e;
            box-shadow: 0 0 8px rgba(0, 184, 148, 0.7);
        }
        /* Animasi Mikrofon Berdenging */
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .mic-listening {
            animation: pulse-red 1.5s infinite;
            background-color: #ef4444; /* bg-red-500 */
        }
        
        /* Gaya untuk Resume Box */
        .score-box {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="p-4 sm:p-8 flex flex-col min-h-screen text-white">

    <div class="max-w-4xl mx-auto w-full">
        <header class="text-center mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold terminal-text drop-shadow-lg">Detektif AI: Jejak Kasus</h1>
            <p class="text-lg text-gray-300 mt-1">Game Master: Gemini Flash</p>
        </header>

        <!-- Area Pemilihan Tema Awal (Glass Effect) -->
        <div id="theme-selector" class="glass-effect p-6 sm:p-8 rounded-xl shadow-2xl transition duration-500">
            <h2 class="text-2xl font-bold mb-4 text-center text-white">Pilih Tema & Kesulitan</h2>
            
            <!-- Tema Selector -->
            <div class="space-y-4 mb-6">
                <label for="theme-select" class="block text-sm font-medium text-gray-300">Tema Siap Pakai:</label>
                <select id="theme-select" class="w-full p-3 rounded-lg glass-input text-white border-2 border-gray-600 focus:ring-00b894 focus:border-00b894 transition duration-300">
                    <option value="Pencurian di Lab Komputer">Pencurian di Lab Komputer</option>
                    <option value="Sabotase Proyek Akhir">Sabotase Proyek Akhir</option>
                    <option value="Hilangnya Arsip Penting Sekolah">Hilangnya Arsip Penting Sekolah</option>
                    <option value="Misteri di Bengkel Otomotif">Misteri di Bengkel Otomotif</option>
                    <option value="Jejak Peretasan Jaringan Sekolah">Jejak Peretasan Jaringan Sekolah</option>
                </select>

                <div class="flex items-center space-x-2">
                    <hr class="flex-grow border-gray-600">
                    <span class="text-sm text-gray-400">ATAU</span>
                    <hr class="flex-grow border-gray-600">
                </div>

                <!-- Input Tema Kustom (Tanpa Voice Input di sini) -->
                <label for="custom-theme-input" class="block text-sm font-medium text-gray-300">Tema Kustom (Opsional):</label>
                <input
                    type="text"
                    id="custom-theme-input"
                    placeholder="Contoh: 'Skandal kecurangan Ujian Praktik'"
                    class="w-full p-3 rounded-lg glass-input text-white border-2 border-gray-600 focus:ring-00b894 focus:border-00b894 transition duration-300 placeholder-gray-400"
                >
            </div>
            
            <!-- Difficulty Slider -->
            <div class="mt-8 p-4 score-box rounded-lg border border-gray-700">
                <label for="difficulty-slider" class="block text-xl font-bold mb-4 text-white text-center">Tingkat Kesulitan</label>
                <div class="text-center mb-4">
                    <span id="difficulty-level-label" class="text-2xl font-extrabold text-green-400">SMA/SMK (3): Mudah</span>
                </div>
                
                <input 
                    type="range" 
                    id="difficulty-slider" 
                    min="1" 
                    max="10" 
                    value="3" 
                    step="1" 
                    class="w-full h-2 rounded-lg appearance-none cursor-pointer"
                >
                <div class="flex justify-between text-sm mt-2 text-gray-400 font-mono">
                    <span class="text-left">1: Dasar (Clue Langsung)</span>
                    <span class="text-right">10: Profesional (Plot Twist Berat)</span>
                </div>
            </div>

            
            <!-- KONTROL TTS -->
            <div class="mt-6 flex items-center justify-between p-3 score-box rounded-lg border border-gray-700">
                <label for="tts-toggle" class="text-sm font-medium text-white select-none">
                    Aktifkan Narasi Suara (TTS)
                </label>
                <!-- checked="false" secara default, tidak dicentang -->
                <input type="checkbox" id="tts-toggle" class="h-5 w-5 text-00b894 rounded focus:ring-00b894 border-gray-500 bg-gray-900 cursor-pointer">
            </div>

            <button
                id="start-button"
                onclick="startGame()"
                class="mt-6 w-full bg-00b894 hover:bg-00cec9 text-yellow-400 font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg flex items-center justify-center disabled:opacity-50"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                Mulai Game
            </button>
        </div>
        
        <!-- Area Log Permainan & Input (Disembunyikan saat awal) -->
        <div id="game-container" class="hidden">
            <div id="game-log" class="glass-effect p-4 mb-6 rounded-lg shadow-xl transition duration-300">
                <!-- Pesan akan ditambahkan di sini -->
                <p class="text-lg text-yellow-300">
                    <span class="font-bold">Game Master (GM):</span> Memuat kasus...
                </p>
            </div>

            <!-- Container Tombol Tindakan Cepat (Glass Effect & Responsive Grid) -->
            <div id="action-buttons-container" class="glass-effect mt-4 mb-6 grid gap-3 p-4 rounded-xl shadow-inner border border-gray-700
                grid-cols-2 sm:grid-cols-3 lg:grid-cols-4"> 
                <!-- Tombol aksi akan muncul di sini. Default: 2 kolom (mobile), 3 kolom (tablet), 4 kolom (desktop) -->
                <p class="text-gray-400 text-sm italic col-span-full">Opsi tindakan cepat akan muncul di sini setelah kasus dimuat.</p>
            </div>


            <!-- Area Input Pemain BARU dengan Input Suara (Flexibel di semua ukuran) -->
            <div class="flex flex-col gap-3">
                <div class="flex gap-2 items-center w-full">
                    <input
                        type="text"
                        id="player-input"
                        placeholder="Ketik tindakan (Contoh: 'Cari sidik jari') atau ketik 'Tuduh: [Nama]' untuk menyelesaikan kasus."
                        class="flex-grow w-full p-3 rounded-lg glass-input text-white border-2 border-gray-600 focus:ring-00b894 focus:border-00b894 transition duration-300 placeholder-gray-400"
                        onkeypress="if(event.key === 'Enter') { sendMessage(); }"
                        autofocus
                    >
                    <button
                        id="voice-input-button"
                        onclick="startVoiceInput()"
                        class="p-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-300 shadow-md flex items-center justify-center disabled:opacity-50 flex-shrink-0"
                        title="Input Suara (Bahasa Indonesia). Akan otomatis dikirim."
                    >
                         <!-- Ikon Mikrofon -->
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                            <line x1="12" y1="19" x2="12" y2="23"></line>
                            <line x1="8" y1="23" x2="16" y2="23"></line>
                        </svg>
                    </button>
                </div>
                <!-- Tombol Kirim di bawah Input Suara/Text -->
                <button
                    id="send-button"
                    onclick="sendMessage()"
                    class="w-full bg-00b894 hover:bg-00cec9 text-yellow-400 font-bold py-3 px-6 rounded-lg transition duration-300 shadow-md flex items-center justify-center disabled:opacity-50 mt-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path>
                    </svg>
                    Kirim Tindakan
                </button>
            </div>
        </div>
        
        <!-- Area Resume Permainan (Glass Effect) -->
        <div id="game-resume" class="glass-effect p-6 sm:p-8 rounded-xl shadow-2xl transition duration-500 hidden">
            <h2 class="text-3xl font-bold mb-6 text-center text-pink-400 border-b-2 border-pink-400 pb-2">Laporan Akhir Investigasi</h2>
            
            <div id="resume-content">
                <!-- Konten akan dimuat oleh JavaScript -->
            </div>
            
            <button
                id="download-resume-button"
                onclick="downloadResume()"
                class="mt-8 w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg flex items-center justify-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Unduh Laporan (.txt)
            </button>
            <button
                onclick="window.location.reload()"
                class="mt-4 w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg flex items-center justify-center"
            >
                Mulai Kasus Baru
            </button>
        </div>
        
    </div>

    <script type="text/javascript">
        // =============================================================
        // Variabel Konfigurasi & State
        // =============================================================
        const MODEL_NAME = "gemini-2.5-flash-preview-09-2025";
        
        const apiKey = "APIKEY"; 

        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${MODEL_NAME}:generateContent?key=${apiKey}`;
        
        const gameLog = document.getElementById('game-log');
        const playerInput = document.getElementById('player-input');
        const sendButton = document.getElementById('send-button');
        
        // Elemen UI
        const themeSelectorDiv = document.getElementById('theme-selector');
        const gameContainerDiv = document.getElementById('game-container');
        const gameResumeDiv = document.getElementById('game-resume');
        const resumeContentDiv = document.getElementById('resume-content');
        const themeSelect = document.getElementById('theme-select');
        const customThemeInput = document.getElementById('custom-theme-input');
        const startButton = document.getElementById('start-button');
        const actionButtonsContainer = document.getElementById('action-buttons-container');
        const ttsToggle = document.getElementById('tts-toggle');
        const difficultySlider = document.getElementById('difficulty-slider');
        const voiceInputButton = document.getElementById('voice-input-button'); 


        let chatHistory = [];
        let isProcessing = false;
        let isTtsEnabled = false; // Default: TTS tidak aktif
        let moveCount = 0;

        // Web Speech API setup
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition = null;
        if (SpeechRecognition) {
            recognition = new SpeechRecognition();
            recognition.continuous = false; 
            recognition.lang = 'id-ID';     
            recognition.interimResults = false;
        }


        // Pemisah khusus yang digunakan untuk memisahkan narasi dari opsi tindakan
        const ACTION_DELIMITER = "---ACTION_OPTIONS---"; 
        // Pemisah khusus untuk sinyal akhir game dan data ringkasan
        const SUMMARY_DELIMITER = "---GAME_OVER_SUMMARY---";

        // =============================================================
        // Fungsi Text-to-Speech (TTS)
        // =============================================================

        /**
         * Menghentikan TTS yang sedang berjalan.
         */
        function stopTTS() {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        }

        /**
         * Mengucapkan teks yang sudah dibersihkan (clean text) yang diberikan.
         * @param {string} cleanText Teks yang sudah bersih dari format, siap diucapkan.
         * @param {boolean} [force=false] Jika true, lewati pemeriksaan isTtsEnabled global.
         */
        function speakText(cleanText, force = false) { 
            // PENTING: Pemeriksaan berdasarkan state JavaScript (isTtsEnabled)
            if (!force && (!isTtsEnabled || !('speechSynthesis' in window))) { 
                return;
            }
            
            stopTTS(); // Stop any previous speech

            const utterance = new SpeechSynthesisUtterance(cleanText);
            
            utterance.lang = 'id-ID'; 

            const voices = window.speechSynthesis.getVoices();
            const indonesianVoice = voices.find(voice => voice.lang.startsWith('id'));
            if (indonesianVoice) {
                utterance.voice = indonesianVoice;
            }

            window.speechSynthesis.speak(utterance);
        }

        


        // =============================================================
        // Fungsi Voice Recognition (In-Game Action)
        // =============================================================

        /**
         * Mengatur status tombol mikrofon (UI/UX)
         * @param {boolean} isListening
         */
        function setMicState(isListening) {
            if (!voiceInputButton) return;

            if (isListening) {
                voiceInputButton.classList.add('mic-listening');
                voiceInputButton.title = "MENDENGARKAN... Bicara sekarang.";
                playerInput.placeholder = "MENDENGARKAN... Bicara sekarang...";
                playerInput.disabled = true;
                sendButton.disabled = true;
            } else {
                voiceInputButton.classList.remove('mic-listening');
                voiceInputButton.title = "Input Suara (Bahasa Indonesia). Akan otomatis dikirim.";
                playerInput.placeholder = "Ketik tindakan (Contoh: 'Cari sidik jari') atau ketik 'Tuduh: [Nama]' untuk menyelesaikan kasus.";
                playerInput.disabled = false;
                sendButton.disabled = false;
            }
            voiceInputButton.disabled = isListening;
        }

        /**
         * Memulai proses input suara (Web Speech API) untuk Aksi Pemain.
         */
        function startVoiceInput() {
            if (!recognition) {
                // Mengganti alert() dengan pesan di input
                playerInput.value = "ERROR: Browser Anda tidak mendukung input suara (Web Speech API)."; 
                return;
            }
            
            recognition.abort(); 

            recognition.onstart = () => {
                setMicState(true);
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                playerInput.value = transcript;
                setMicState(false);
                
                if (transcript.trim().length > 0) {
                    sendMessage();
                }
            };

            recognition.onend = () => {
                setMicState(false);
            };

            recognition.onerror = (event) => {
                setMicState(false);
                console.error('Speech recognition error:', event.error);
                playerInput.placeholder = `ERROR Suara: ${event.error}`;
            };
            
            try {
                recognition.start();
            } catch (e) {
                console.warn('Recognition already started:', e);
            }
        }


        // =============================================================
        // Logika Interaksi UI & Parsing
        // =============================================================

        /**
         * Menciptakan pesan HTML baru di log permainan.
         */
        function appendMessage(sender, text) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `p-4 my-2 rounded-md ${sender === 'GM' ? 'message-gm' : 'message-player'} flex flex-col space-y-2`;
            
            let cleanText = null; 
            
            if (sender === 'GM') {
                // 1. Bersihkan teks untuk TTS dan penyimpanan data (tanpa * dan HTML)
                // Hapus juga delimiter SUMMARY jika ada, karena ini tidak boleh diucapkan
                cleanText = text.split(SUMMARY_DELIMITER)[0]
                              .replace(/<[^>]*>?/gm, '')
                              .replace(/\*/g, '')
                              .replace(/[\n\r]/g, ' '); 

                const headerDiv = document.createElement('div');
                headerDiv.className = 'flex justify-between items-center mb-1';

                const senderSpan = document.createElement('span');
                senderSpan.className = 'font-bold mr-2';
                senderSpan.textContent = 'Game Master (GM):';
                
                const controlGroup = document.createElement('div');
                controlGroup.className = 'flex space-x-2';


                // Tombol REPLAY (Play Manual)
                const replayButton = document.createElement('button');
                replayButton.className = 'flex items-center text-xs text-blue-400 hover:text-blue-300 transition duration-150 p-1 rounded hover:bg-blue-900/50';
                replayButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 mr-1">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                    REPLAY
                `;
                replayButton.onclick = function() {
                    stopTTS(); 
                    speakText(cleanText, true); // Memaksa pemutaran (unconditional replay)
                };
                
                // Tombol STOP ALL (Global Stop)
                const stopButton = document.createElement('button');
                stopButton.className = 'flex items-center text-xs text-red-400 hover:text-red-300 transition duration-150 p-1 rounded hover:bg-red-900/50';
                stopButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 mr-1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <rect x="9" y="9" width="6" height="6"></rect>
                    </svg>
                    STOP ALL
                `;
                stopButton.onclick = stopTTS;

                controlGroup.appendChild(replayButton);
                controlGroup.appendChild(stopButton);


                headerDiv.appendChild(senderSpan);
                headerDiv.appendChild(controlGroup); // Tambahkan grup kontrol

                const textContent = document.createElement('p');
                
                // Konversi Markdown bold (*teks*) ke HTML bold (<b>teks</b>)
                let formattedText = text.replace(/\*([^\*]+)\*/g, '<b>$1</b>');

                // Konversi newline ke <br>
                textContent.innerHTML = formattedText.replace(/\n/g, '<br>');

                messageDiv.appendChild(headerDiv);
                messageDiv.appendChild(textContent);

            } else {
                // --- KONTEN PEMAIN (Format Tindakan: **Tindakan Anda**) ---
                const senderSpan = document.createElement('span');
                senderSpan.className = 'font-bold mr-2';
                senderSpan.textContent = 'Investigator:';
                
                const textContent = document.createElement('p');
                // Tindakan pemain selalu dibold
                textContent.innerHTML = `Tindakan: <b>${text.replace(/\n/g, '<br>')}</b>`;

                messageDiv.appendChild(senderSpan);
                messageDiv.appendChild(textContent);
            }
            
            gameLog.appendChild(messageDiv);
            
            gameLog.scrollTop = gameLog.scrollHeight;

            return cleanText; 
        }

        /**
         * Mengatur status UI selama pemrosesan AI (hanya untuk input in-game).
         */
        function setLoadingState(loading) {
            isProcessing = loading;
            playerInput.disabled = loading;
            sendButton.disabled = loading;
            voiceInputButton.disabled = loading; 
            if (loading) {
                sendButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses...`;
            } else {
                sendButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path></svg>Kirim Tindakan`;
                playerInput.focus();
            }
        }

        /**
         * MERENDER tombol tindakan yang disarankan.
         */
        function renderActionButtons(actions) {
            actionButtonsContainer.innerHTML = '<p class="text-gray-400 text-sm italic col-span-full">Opsi tindakan cepat:</p>'; 

            if (actions && actions.length > 0) {
                actions.forEach(action => {
                    const trimmedAction = action.trim();
                    if (trimmedAction) {
                        const button = document.createElement('button');
                        button.textContent = trimmedAction;
                        // Gaya tombol di atas latar belakang kaca
                        button.className = 'px-4 py-2 bg-pink-600/70 hover:bg-pink-700 text-white font-medium rounded-lg transition duration-200 shadow-md';
                        button.onclick = () => {
                            playerInput.value = trimmedAction;
                            sendMessage();
                        };
                        actionButtonsContainer.appendChild(button);
                    }
                });
            }
        }
        
        // =============================================================
        // Logika API & Game Flow
        // =============================================================

        /**
         * Mendefinisikan instruksi sistem untuk AI.
         */
        function getSystemInstruction(difficultyLevel, isAccusationAttempt) {
            let instruction = `
                Anda adalah Game Master (GM) untuk game petualangan teks berbasis investigasi.
                Tugas Anda adalah:
                1. Menciptakan dan menjaga alur cerita kasus misteri yang logis dan menarik (bertema SMK/SMA).
                2. Tanggapi aksi pemain dengan konsekuensi yang realistis dalam konteks game.
                3. Berikan deskripsi lokasi, petunjuk, atau reaksi karakter (saksi/tersangka) dengan jelas.
                4. Jaga nada bicara formal, misterius, dan profesional.
                5. Selalu gunakan tanda bintang tunggal *teks* untuk menyoroti nama objek, bukti, atau petunjuk penting. Contoh: "Anda menemukan sebuah *kunci USB* di bawah meja."
                
                Tingkat Kesulitan Saat Ini: ${difficultyLevel} dari 10.
            `;

            if (isAccusationAttempt) {
                instruction += `
                    Pemain baru saja membuat tuduhan terakhirnya. Tugas Anda sekarang adalah MENGAKHIRI GAME.
                    1. Berikan narasi kesimpulan yang dramatis dan logis (apakah tuduhan itu benar atau salah), dan jelaskan nasib kasus tersebut.
                    2. Setelah narasi selesai, Anda HARUS menyertakan string pemisah tunggal pada baris baru: "${SUMMARY_DELIMITER}"
                    3. Setelah pemisah, Anda HARUS menghasilkan objek JSON tunggal yang berisi metrik penilaian pemain. JANGAN sertakan markdown code block untuk JSON.
                    4. Metrik harus mencakup:
                       - "score": Nilai numerik 0-100.
                       - "indicator_1" hingga "indicator_3": Judul metrik (misal: "Keakuratan Bukti").
                       - "value_1" hingga "value_3": Nilai deskriptif metrik tersebut (misal: "Sangat Tinggi").
                       - "feedback": Umpan balik tekstual komprehensif tentang kinerja pemain (min. 3 kalimat).
                       - "total_moves": Jumlah langkah yang diambil pemain dalam game ini (gunakan: ${moveCount}).
                    5. Contoh format JSON yang WAJIB Anda hasilkan: {"score": 75, "indicator_1": "...", "value_1": "...", "indicator_2": "...", "value_2": "...", "indicator_3": "...", "value_3": "...", "feedback": "...", "total_moves": 10}.
                `;
            } else {
                instruction += `
                    6. Setelah narasi utama selesai, Anda HARUS menyertakan pemisah string tunggal: "${ACTION_DELIMITER}" pada baris baru.
                    7. Setelah pemisah, Anda HARUS memberikan daftar 3 hingga 5 opsi tindakan yang relevan dengan situasi saat ini.
                    8. Opsi tindakan HARUS dipisahkan oleh baris baru dan JANGAN sertakan nomor atau bullet point.
                    9. PASTIKAN narasi dan opsi tindakan sepenuhnya dalam Bahasa Indonesia.
                `;
            }
            return instruction;
        }


        /**
         * Memanggil Gemini API dengan logic backoff eksponensial.
         */
        async function callGeminiAPI(payload, retries = 3) {
            if (retries === 0) {
                throw new Error("Gagal terhubung ke AI Game Master setelah beberapa percobaan.");
            }

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const result = await response.json();
                const text = result.candidates?.[0]?.content?.parts?.[0]?.text;
                
                if (text) {
                    return text;
                } else {
                    throw new Error("Respon API tidak mengandung teks yang valid.");
                }

            } catch (error) {
                console.error(`Percobaan ${3 - retries + 1} gagal:`, error.message);
                const delay = Math.pow(2, 3 - retries) * 1000;
                await new Promise(resolve => setTimeout(resolve, delay));
                return callGeminiAPI(payload, retries - 1);
            }
        }

        /**
         * Mengirim pesan pemain, mendapatkan balasan dari GM, dan mem-parsing aksinya.
         */
        async function sendMessage() {
            if (isProcessing) return;

            const playerAction = playerInput.value.trim();
            if (!playerAction) return;

            stopTTS();

            // Peningkatan moveCount saat aksi valid dikirim
            moveCount++; 
            
            const isAccusation = playerAction.toLowerCase().startsWith('tuduh:');

            appendMessage('Pemain', playerAction);

            chatHistory.push({ role: "user", parts: [{ text: playerAction }] });

            playerInput.value = '';
            setLoadingState(true);
            renderActionButtons([]);

            try {
                const initialPromptPart = chatHistory.find(item => item.role === 'user' && item.parts[0].text.includes("Tingkat Kesulitan"));
                const currentDifficulty = initialPromptPart ? initialPromptPart.parts[0].text.match(/Tingkat Kesulitan yang saya tentukan adalah (\d+)/)?.[1] || 3 : 3;

                const payload = {
                    contents: chatHistory,
                    systemInstruction: {
                        parts: [{ text: getSystemInstruction(currentDifficulty, isAccusation) }]
                    },
                };

                const rawGmResponse = await callGeminiAPI(payload);
                
                // Cek apakah ini adalah akhir permainan
                if (rawGmResponse.includes(SUMMARY_DELIMITER)) {
                    endGame(rawGmResponse);
                    return; // Hentikan alur normal
                }
                
                const parts = rawGmResponse.split(ACTION_DELIMITER);
                const narrativeText = parts[0].trim();
                let actionOptions = [];

                if (parts.length > 1) {
                    actionOptions = parts[1].trim()
                        .split('\n')
                        .map(line => line.trim())
                        .filter(line => line.length > 0);
                }

                const cleanText = appendMessage('GM', narrativeText);
                if (cleanText) {
                    speakText(cleanText); 
                }

                renderActionButtons(actionOptions);
                
                chatHistory.push({ role: "model", parts: [{ text: rawGmResponse }] });
                
            } catch (error) {
                console.error("Kesalahan dalam memanggil AI:", error);
                // Kurangi moveCount karena gagal memanggil AI
                moveCount = Math.max(0, moveCount - 1); 
                appendMessage('GM', `[ERROR JARINGAN] Maaf, Game Master sedang offline atau terjadi masalah koneksi. Silakan coba lagi. (${error.message})`);
                renderActionButtons([]);
            } finally {
                setLoadingState(false);
            }
        }

        /**
         * Fungsi untuk menangani akhir permainan dan menampilkan resume.
         * @param {string} rawGmResponse Respon GM yang berisi narasi dan JSON summary.
         */
        function endGame(rawGmResponse) {
            const parts = rawGmResponse.split(SUMMARY_DELIMITER);
            const narrative = parts[0].trim();
            let summaryJsonString = parts.length > 1 ? parts[1].trim() : "{}";
            let summaryData;

            // Pastikan narrative terakhir dicatat di log
            appendMessage('GM', narrative);
            
            // Coba parsing JSON
            try {
                // Hapus segala sesuatu yang bukan JSON murni (misal: `json` code block marker)
                summaryJsonString = summaryJsonString.replace(/```json|```/g, '').trim();
                summaryData = JSON.parse(summaryJsonString);
            } catch (e) {
                console.error("Gagal mem-parsing data ringkasan JSON:", e);
                summaryData = {
                    score: "N/A",
                    indicator_1: "Data Error", value_1: "...",
                    indicator_2: "Data Error", value_2: "...",
                    indicator_3: "Data Error", value_3: "...",
                    feedback: "Terjadi kesalahan dalam menerima data skor dari Game Master. Coba lagi.",
                    total_moves: moveCount
                };
            }
            
            // Sembunyikan game utama, tampilkan resume
            gameContainerDiv.classList.add('hidden');
            gameResumeDiv.classList.remove('hidden');
            
            displayResume(narrative, summaryData);
        }
        
        /**
         * Merender konten resume ke UI.
         */
        function displayResume(narrative, data) {
            let metricHtml = '';
            for (let i = 1; i <= 3; i++) {
                if (data[`indicator_${i}`] && data[`value_${i}`]) {
                    metricHtml += `
                        <div class="flex justify-between items-center py-2 border-b border-gray-700">
                            <span class="text-gray-300 font-medium">${data[`indicator_${i}`]}</span>
                            <span class="text-pink-400 font-bold">${data[`value_${i}`]}</span>
                        </div>
                    `;
                }
            }
            
            // Format Konten Resume
            resumeContentDiv.innerHTML = `
                <div class="mb-6 p-4 rounded-lg score-box">
                    <h3 class="text-xl font-semibold mb-3 text-white border-b border-gray-600 pb-2">Kesimpulan Kasus</h3>
                    <p class="text-gray-200 whitespace-pre-wrap">${narrative.replace(/\*/g, '<b>').replace(/\*/g, '</b>')}</p>
                </div>
                
                <div class="flex justify-center items-center mb-6">
                    <div class="score-box p-6 rounded-full w-40 h-40 flex flex-col items-center justify-center text-center">
                        <span class="text-sm text-gray-400">Skor Detektif</span>
                        <span class="text-6xl font-extrabold terminal-text">${data.score}</span>
                        <span class="text-xs text-gray-400">(${data.total_moves} langkah)</span>
                    </div>
                </div>

                <div class="mb-6 p-4 rounded-lg score-box">
                    <h3 class="text-xl font-semibold mb-3 text-white border-b border-gray-600 pb-2">Metrik Penilaian</h3>
                    ${metricHtml}
                </div>
                
                <div class="p-4 rounded-lg bg-red-900/40 border border-red-400/50">
                    <h3 class="text-xl font-semibold mb-3 text-red-300 border-b border-red-500 pb-2">Umpan Balik GM</h3>
                    <p class="text-red-200">${data.feedback}</p>
                </div>
            `;
        }
        
        /**
         * Mengunduh konten resume sebagai file TXT.
         */
        function downloadResume() {
            const narrative = document.querySelector('#resume-content > div:first-child p').textContent;
            const score = document.querySelector('.score-box .terminal-text').textContent;
            const feedback = document.querySelector('.bg-red-900\\/40 p').textContent;
            
            let metricsText = '';
            const metrics = document.querySelectorAll('#resume-content .score-box > div.flex');
            metrics.forEach(metric => {
                const indicator = metric.querySelector('span:nth-child(1)').textContent;
                const value = metric.querySelector('span:nth-child(2)').textContent;
                metricsText += `- ${indicator}: ${value}\n`;
            });

            const fileContent = `
LAPORAN AKHIR INVESTIGASI
=================================

SKOR DETEKTIF: ${score}/100
TOTAL LANGKAH: ${moveCount}

---------------------------------
KESIMPULAN KASUS:
${narrative}

---------------------------------
METRIK PENILAIAN:
${metricsText}
---------------------------------
UMPAN BALIK GM:
${feedback}
=================================
Dibuat oleh Detektif AI - Game Master
`;
            
            const blob = new Blob([fileContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Laporan_Investigasi_${new Date().toISOString().slice(0, 10)}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }


        /**
         * Memulai game setelah pemain memilih tema.
         */
        async function startGame() {
            stopTTS(); 
            
            // Memperbarui state TTS dari checkbox
            isTtsEnabled = ttsToggle.checked;

            startButton.disabled = true;
            startButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memuat Kasus...`;
            
            let selectedTheme = themeSelect.value;
            const customTheme = customThemeInput.value.trim();
            const difficultyLevel = difficultySlider.value;

            if (customTheme) {
                selectedTheme = customTheme;
            } else if (!selectedTheme) {
                selectedTheme = "Pencurian di Lab Komputer";
            }
            
            moveCount = 0; // Reset langkah

            themeSelectorDiv.classList.add('hidden');
            gameContainerDiv.classList.remove('hidden');
            gameResumeDiv.classList.add('hidden'); // Pastikan resume tersembunyi

            const initialPrompt = `Mulailah kasus baru. Saya adalah seorang Investigator. Tema kasus yang saya pilih adalah: "${selectedTheme}". Tingkat Kesulitan yang saya tentukan adalah ${difficultyLevel} dari 10. Beri saya deskripsi awal kasus dan lokasi pertama saya untuk memulai penyelidikan.`;
            
            chatHistory = [];
            chatHistory.push({ role: "user", parts: [{ text: initialPrompt }] });

            try {
                const payload = {
                    contents: chatHistory,
                    systemInstruction: {
                        parts: [{ text: getSystemInstruction(difficultyLevel, false) }]
                    },
                };

                gameLog.innerHTML = ''; 

                const rawGmResponse = await callGeminiAPI(payload);
                
                const parts = rawGmResponse.split(ACTION_DELIMITER);
                const narrativeText = parts[0].trim();
                let actionOptions = [];

                if (parts.length > 1) {
                    actionOptions = parts[1].trim()
                        .split('\n')
                        .map(line => line.trim())
                        .filter(line => line.length > 0);
                }

                const cleanText = appendMessage('GM', narrativeText);
                if (cleanText) {
                    speakText(cleanText); 
                }
                
                renderActionButtons(actionOptions);
                
                chatHistory.push({ role: "model", parts: [{ text: rawGmResponse }] });
                
            } catch (error) {
                console.error("Kesalahan saat inisialisasi AI:", error);
                gameLog.innerHTML += `<p class="text-red-500 p-4">Gagal memuat kasus. Silakan refresh halaman. Error: ${error.message}</p>`;
                renderActionButtons([]);
            } finally {
                startButton.disabled = false;
                startButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>Mulai Game`;
                setLoadingState(false);
            }
        }
        
        /**
         * Memperbarui label kesulitan berdasarkan nilai slider.
         */
        function updateDifficultyLabel(value) {
            const label = document.getElementById('difficulty-level-label');
            const numValue = parseInt(value, 10);
            let text;
            let color;
            
            if (numValue >= 1 && numValue <= 3) {
                text = `SMA/SMK (${numValue}): Mudah`;
                color = 'text-green-400';
            } else if (numValue >= 4 && numValue <= 7) {
                text = `Menengah (${numValue}): Analisis`;
                color = 'text-yellow-400';
            } else { // 8 to 10
                text = `Profesional (${numValue}): Kompleks`;
                color = 'text-red-400';
            }

            label.textContent = text;
            label.className = `text-2xl font-extrabold ${color}`;
        }

        /**
         * Menyiapkan UI awal (menampilkan pemilih tema dan slider).
         */
        function setupUI() {
            customThemeInput.focus();
            stopTTS();
            
            updateDifficultyLabel(difficultySlider.value); 
            
            difficultySlider.addEventListener('input', (event) => {
                updateDifficultyLabel(event.target.value);
            });
            
            // Sinkronkan state TTS dari checkbox ke variabel JavaScript
            ttsToggle.addEventListener('change', (event) => {
                isTtsEnabled = event.target.checked;
            });
        }

        // =============================================================
        // Inisialisasi
        // =============================================================
        window.onload = setupUI;
    </script>
</body>
</html>
