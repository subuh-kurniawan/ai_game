<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cyber-Quest: Game Edukasi Teks | Leadership Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Use Inter for a cleaner, modern look, combined with Chivo Mono for terminal text -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Chivo+Mono:wght==400;700&display=swap" rel="stylesheet">
    
    <style>
        /* CSS Reset and Global Styles */
        :root {
            /* Light/Bright Palette */
            --color-primary: #1d4ed8; /* Blue-700 for accents */
            --color-secondary: #059669; /* Emerald-600 for success */
            --color-success: #059669; /* Emerald-600 */
            --color-warning: #d97706; /* Amber-700 */
            --color-text: #1f2937; /* Dark Gray/Black for readability on light background */
            --color-surface: rgba(255, 255, 255, 0.5); /* Light surface with transparency */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-attachment: fixed;
            
            /* ===== BRIGHT BACKGROUND IMAGE STYLE (Abstract, Minimalist) ===== */
            background-image: url('https://images.unsplash.com/photo-1549490203-c464e830b599?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'); 
            background-size: cover;
            background-position: center;
            background-color: #f3f4f6; /* Fallback light gray color */
            /* ==================================== */
            
            color: var(--color-text);
        }

        /* Glassmorphism/Acrylic Effect for Main Card (Light Tahoe Style) */
        .acrylic-card {
            background-color: rgba(255, 255, 255, 0.65); /* White with transparency */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.1); /* Dark, subtle border */
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
            transition: all 0.3s ease;
        }
        
        /* Softer button press effect */
        @keyframes buttonPress {
            0% { transform: scale(1); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); }
            100% { transform: scale(0.98); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); }
        }

        .btn-press:active {
            animation: buttonPress 0.1s ease-out forwards;
        }

        /* Clean Terminal/Game Text Area - Light Theme */
        #gameText {
            font-family: 'Chivo Mono', monospace;
            background-color: #f9fafb; /* Very light background */
            color: #1f2937; /* Dark text */
            border: 1px solid var(--color-primary);
            box-shadow: 0 0 5px rgba(29, 78, 216, 0.3); /* Muted blue glow */
        }
        
        /* Initial System Text */
        #gameText .system-ready {
            color: #6b7280; /* Muted gray for initial message */
        }

        /* Title style refinement (Adjust gradient for light contrast) */
        .title-gradient {
            background-image: linear-gradient(90deg, #1d4ed8, #059669, #9333ea); /* Blue, Green, Purple */
        }

        /* Custom scrollbar for game text area (Darker on light background) */
        #gameText::-webkit-scrollbar {
            width: 8px;
        }
        #gameText::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        #gameText::-webkit-scrollbar-thumb {
            background: var(--color-primary);
            border-radius: 10px;
        }
        
        /* Select/Input styling for light theme */
        select, input[type="text"] {
             background-color: rgba(255, 255, 255, 0.9);
             color: var(--color-text);
        }
        select:focus, input[type="text"]:focus {
             box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.2); /* Light blue ring */
        }
        
        /* Button text color adjustment for light theme */
        .bg-emerald-500, .bg-yellow-500 {
            color: white !important; /* Keep text white on bright colors */
        }
    </style>
</head>

<body class="min-h-screen flex flex-col items-center py-10 px-3">

    <!-- ===== HEADER (Sleek Title) ===== -->
    <header class="w-full max-w-5xl mb-8 text-center">
        <h1 class="text-6xl sm:text-7xl font-extrabold title-gradient text-transparent bg-clip-text animate-fade-in drop-shadow-lg mb-2 select-none" style="font-family: 'Inter', sans-serif; font-weight: 800;">
            // CYBER-QUEST //
        </h1>
        <p class="text-lg sm:text-xl text-blue-700 font-light tracking-wider animate-fade-in-slow px-2 italic">
            [ SMART LEADER SIMULATION ]
        </p>
    </header>

    <!-- ===== MAIN GAME SECTION (Acrylic Card/Window) ===== -->
    <section class="w-full max-w-5xl acrylic-card rounded-[30px] p-6 sm:p-10 flex flex-col gap-6">

        <!-- === Pilihan Tema & Level === -->
        <div class="flex flex-wrap items-center justify-center gap-4 p-4 bg-gray-100/70 rounded-2xl border border-gray-200">
            <label for="tema" class="text-gray-700 font-semibold text-lg whitespace-nowrap tracking-wide">
                ::: SCENARIO & DIFFICULTY :::
            </label>

            <select id="tema" class="flex-1 min-w-[150px] border border-blue-400 rounded-xl px-4 py-3 bg-white/80 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all cursor-pointer">
                <!-- Theme Options (Kepemimpinan Pembelajaran selected by default) -->
                <option value="Manajemen Waktu Efektif, Kecerdasan Emosional, Pengambilan Keputusan Pribadi, Mindfulness & Fokus, Kerja Sama Tim, Komunikasi Efektif, Empati & Kepedulian, Membangun Jejaring Sosial, Kepemimpinan Sekolah, Manajemen Proyek, Etika & Profesionalisme, Inovasi & Kreativitas Kerja untuk guru">UKOM GURU</option>
                <option value="kepemimpinan pembelajaran guru di kelas" selected>Kepemimpinan Pembelajaran</option>
                <option value="kecerdasan_emosional">Kecerdasan Emosional</option>
                <option value="pengambilan_keputusan">Pengambilan Keputusan Pribadi</option>
                <option value="mindfulness">Mindfulness & Fokus</option>
                <option value="kerja_sama_tim">Kerja Sama Tim</option>
                <option value="komunikasi_efektif">Komunikasi Efektif</option>
                <option value="empati">Empati & Kepedulian</option>
                <option value="jejaring_sosial">Membangun Jejaring Sosial</option>
                <option value="kepemimpinan_guru">Kepemimpinan Sekolah</option>
                <option value="manajemen_proyek">Manajemen Proyek</option>
                <option value="etika_profesional">Etika & Profesionalisme</option>
                <option value="inovasi_kreativitas">Inovasi & Kreativitas Kerja</option>
            </select>

            <select id="levelSelect" class="border border-green-400 rounded-xl px-4 py-3 bg-white/80 text-green-700 font-medium focus:outline-none focus:ring-4 focus:ring-green-300 transition-all cursor-pointer">
                <option value="mudah">LEVEL I (Easy)</option>
                <option value="sedang">LEVEL II (Medium)</option>
                <option value="sulit">LEVEL III (Hard)</option>
            </select>
        </div>

        <!-- === Custom Tema Input === -->
        <div class="flex flex-wrap justify-center gap-4">
            <input
                id="temaCustom"
                type="text"
                placeholder="INPUT custom scenario data..."
                class="flex-1 border border-purple-400 rounded-xl px-5 py-3 bg-white/80 text-gray-700 focus:outline-none focus:ring-4 focus:ring-purple-300 min-w-[200px] transition-all"
                autocomplete="off"
            />
            <button id="randomTemaBtn" class="bg-purple-600 hover:bg-purple-500 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                🎲 Random
            </button>
            <button id="addTemaBtn" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                ➕ ADD DATA
            </button>
        </div>


        <!-- === Kontrol Game (Interface Controls) === -->
        <div class="flex flex-wrap justify-center gap-4 pt-4 border-t border-gray-200">
            <button id="startBtn" class="flex-1 min-w-[140px] max-w-xs bg-blue-600 hover:bg-blue-500 text-white font-extrabold px-6 py-4 rounded-xl transition-all shadow-lg shadow-blue-500/30 hover:shadow-xl btn-press text-lg">
                ▶️ START SIM
            </button>
            <button id="resetBtn" class="flex-1 min-w-[140px] max-w-xs bg-red-600 hover:bg-red-500 text-white font-bold px-6 py-4 rounded-xl transition-all shadow-lg shadow-red-500/30 hover:shadow-xl btn-press">
                🔁 RESET
            </button>
            <button id="ttsToggleBtn" class="flex-1 min-w-[140px] max-w-xs bg-yellow-500 hover:bg-yellow-400 text-white font-bold px-6 py-4 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                🔈 TTS OFF
            </button>
            <!-- Tombol Stop Audio/TTS -->
            <button id="ttsStopBtn" class="flex-1 min-w-[140px] max-w-xs bg-orange-600 hover:bg-orange-500 text-white font-bold px-6 py-4 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                🛑 STOP
            </button>
            <button id="downloadBtn" class="flex-1 min-w-[140px] max-w-xs bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-4 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                💾 LOG
            </button>
            <button id="analyzeBtn" class="flex-1 min-w-[140px] max-w-xs bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-4 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                🔍 ANALYZE
            </button>

            <form id="analyzeForm" action="analisa.php" method="POST" target="_blank" style="display:none;">
                <input type="hidden" name="gameHistory" id="gameHistoryInput">
                <input type="hidden" name="tema" id="temaInput">
            </form>
        </div>

        <!-- === Area Teks Narasi (Clean Console) === -->
        <div class="relative mt-4">
            <!-- Console Header Bar (Clean, Minimal) -->
            <div class="absolute top-0 left-0 right-0 p-3 bg-gray-100/90 rounded-t-2xl flex items-center gap-2 border-b border-gray-300">
                <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                <span class="text-xs text-gray-500 ml-3 font-mono tracking-widest">[ console.log ]</span>
            </div>
             <!-- The actual text area -->
            <div id="gameText" class="p-6 pt-14 text-lg leading-relaxed whitespace-pre-wrap max-h-96 overflow-y-auto rounded-2xl transition-all">
                <span class="system-ready font-light">
                    // :: SYSTEM READY :: PUSH START TO INITIATE LEADERSHIP PROTOCOL //
                </span>
            </div>
        </div>


        <!-- === Pilihan Jawaban (Clean Tabs) === -->
        <div id="choices" class="flex flex-wrap justify-center gap-4 mt-6">
            <!-- Pilihan Jawaban Akan Muncul di Sini -->
        </div>

        <!-- === Form Jawaban Teks & Suara (Pill Shape) === -->
        <form id="textAnswerForm" class="flex justify-center mt-6 gap-3" autocomplete="off">
            <input id="textAnswerInput" type="text" placeholder="INPUT COMMAND (A/B/C) OR TEXT DATA..." class="border border-blue-400 rounded-full px-5 py-3 w-full max-w-xl bg-white/80 text-gray-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium transition-all" disabled autocomplete="off" />

            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-extrabold px-6 py-3 rounded-full transition-all shadow-lg hover:shadow-xl btn-press disabled:opacity-50" disabled>
                SEND CMD
            </button>

            <button type="button" id="voiceBtn" class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-3 rounded-full focus:outline-none focus:ring-4 focus:ring-emerald-300 transition-all shadow-md btn-press disabled:opacity-50" title="Gunakan suara" disabled>
                🎤
            </button>
        </form>
        <!-- Status message for voice input -->
        <div id="statusMessage" class="text-center mt-3 text-sm font-semibold text-blue-700 italic">
            <!-- Voice Status will be displayed here -->
        </div>
    </section>

    <!-- Modal for Custom Alert/Confirmation (Light Tahoe Style) -->
    <div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-30 hidden items-center justify-center p-4 z-50 transition-opacity duration-300">
        <div class="acrylic-card rounded-3xl p-8 w-full max-w-md transform scale-100 border-blue-400">
            <h3 id="modalTitle" class="text-2xl font-bold text-blue-700 mb-4 tracking-wider">SYSTEM MESSAGE</h3>
            <p id="modalMessage" class="text-gray-700 mb-6"></p>
            <div id="modalButtons" class="flex justify-end gap-3">
                <button id="modalConfirmBtn" class="bg-red-600 hover:bg-red-500 text-white font-semibold px-5 py-2 rounded-xl transition-all btn-press shadow-md hidden">TERMINATE</button>
                <button id="modalCloseBtn" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2 rounded-xl transition-all btn-press shadow-md">CLOSE</button>
            </div>
        </div>
    </div>


    <!-- ===== FOOTER (Data Signature) ===== -->
    <footer class="mt-auto text-gray-600 text-sm select-none py-4 pt-10 tracking-widest font-mono">
        // Data Integrity Check: OK // Cyber-Quest V2.1.5 (Elegant Light Remaster)
    </footer>

    <audio id="ttsAudio" src="" preload="auto"></audio>
    <audio id="bgMusic" src="MIG.mp3" loop></audio>

    <script type="module">
        // ===== Firebase Imports (Required for all apps with persistence) =====
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        import { getFirestore, doc, getDoc, setDoc, onSnapshot, collection, query, addDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";
        import { setLogLevel } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";
        
        // Ensure to use the provided global variables
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
        const firebaseConfig = typeof __firebase_config !== 'undefined' ? JSON.parse(__firebase_config) : null;
        const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

        let app, db, auth;
        let userId = 'anon'; // Default user ID
        
        // Global Game State
        let gameActive = false;
        let gameHistory = [];
        let ttsEnabled = false;
        let currentLevel = 'mudah';
        let currentTema = 'kepemimpinan_guru'; // Default theme

        // LLM Configuration
        const MODEL_NAME = "gemini-2.5-flash-preview-05-20";
        const API_KEY = "AIzaSyAYYBCPplYs1pd3vqu5e13YsbF1hgQz8EY"; // Canvas will provide this at runtime
        const BASE_URL = `https://generativelanguage.googleapis.com/v1beta/models/${MODEL_NAME}:generateContent?key=${API_KEY}`;
        const TTS_MODEL_NAME = "gemini-2.5-flash-preview-tts";
        const TTS_URL = `https://generativelanguage.googleapis.com/v1beta/models/${TTS_MODEL_NAME}:generateContent?key=${API_KEY}`;
        const TTS_VOICE = "Kore"; // Firm, Clear voice

        // Elements
        const gameTextEl = document.getElementById('gameText');
        const choicesEl = document.getElementById('choices');
        const startBtn = document.getElementById('startBtn');
        const resetBtn = document.getElementById('resetBtn');
        const temaSelect = document.getElementById('tema');
        const levelSelect = document.getElementById('levelSelect');
        const temaCustomInput = document.getElementById('temaCustom');
        const addTemaBtn = document.getElementById('addTemaBtn');
        const randomTemaBtn = document.getElementById('randomTemaBtn');
        const textAnswerForm = document.getElementById('textAnswerForm');
        const textAnswerInput = document.getElementById('textAnswerInput');
        const sendCmdBtn = textAnswerForm.querySelector('button[type="submit"]');
        const ttsToggleBtn = document.getElementById('ttsToggleBtn');
        const ttsStopBtn = document.getElementById('ttsStopBtn');
        const ttsAudioEl = document.getElementById('ttsAudio');
        const downloadBtn = document.getElementById('downloadBtn');
        const analyzeBtn = document.getElementById('analyzeBtn');

        // Modal Elements
        const modalEl = document.getElementById('modal');
        const modalTitleEl = document.getElementById('modalTitle');
        const modalMessageEl = document.getElementById('modalMessage');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalConfirmBtn = document.getElementById('modalConfirmBtn');
        
        // Voice Input Elements
        const voiceBtn = document.getElementById('voiceBtn');
        const statusMessageEl = document.getElementById('statusMessage');
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition = null;
        let isListening = false;


        // === UTILITY FUNCTIONS ===

        /** Displays custom alert/confirmation modal (instead of alert/confirm) */
        function showModal(title, message, isConfirm = false) {
            return new Promise(resolve => {
                modalTitleEl.textContent = title;
                modalMessageEl.textContent = message;
                modalEl.classList.remove('hidden');
                modalEl.classList.add('flex');
                
                // Reset button visibility
                modalConfirmBtn.classList.add('hidden');
                modalConfirmBtn.onclick = null;
                modalCloseBtn.onclick = null;

                if (isConfirm) {
                    modalConfirmBtn.classList.remove('hidden');
                    modalConfirmBtn.onclick = () => {
                        modalEl.classList.add('hidden');
                        modalEl.classList.remove('flex');
                        resolve(true);
                    };
                    modalCloseBtn.onclick = () => {
                        modalEl.classList.add('hidden');
                        modalEl.classList.remove('flex');
                        resolve(false);
                    };
                } else {
                    modalCloseBtn.onclick = () => {
                        modalEl.classList.add('hidden');
                        modalEl.classList.remove('flex');
                        resolve(true);
                    };
                }
            });
        }

        /** Simple exponential backoff for fetch retries */
        async function fetchWithRetry(url, options, maxRetries = 5) {
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (response.ok) return response;
                    
                    // Specific error handling for 4xx/5xx errors
                    if (response.status >= 400 && response.status < 500) {
                        const errorBody = await response.text();
                        console.error(`Client Error (${response.status}):`, errorBody);
                        throw new Error(`API returned client error: ${response.status}`);
                    }
                    
                    // For other retriable errors (e.g., 500/503)
                    throw new Error(`Request failed with status ${response.status}`);
                } catch (error) {
                    if (i === maxRetries - 1) {
                        console.error("Max retries reached. Failing request.", error);
                        throw error;
                    }
                    const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                    // console.log(`Attempt ${i + 1} failed. Retrying in ${delay.toFixed(0)}ms...`);
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
        }
        
        /** Converts base64 audio data to a playable WAV Blob */
        function base64ToArrayBuffer(base64) {
            const binaryString = window.atob(base64);
            const len = binaryString.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            return bytes.buffer;
        }

        /** Helper to create a WAV header for PCM data */
        function pcmToWav(pcm16, sampleRate = 16000) {
            const numChannels = 1;
            const bytesPerSample = 2; // 16-bit PCM
            const byteRate = sampleRate * numChannels * bytesPerSample;
            const blockAlign = numChannels * bytesPerSample;
            const dataSize = pcm16.length * bytesPerSample;
            const buffer = new ArrayBuffer(44 + dataSize);
            const view = new DataView(buffer);

            let offset = 0;

            // RIFF chunk
            writeString('RIFF'); offset += 4;
            view.setUint32(offset, 36 + dataSize, true); offset += 4;
            writeString('WAVE'); offset += 4;

            // fmt chunk
            writeString('fmt '); offset += 4;
            view.setUint32(offset, 16, true); offset += 4; // Sub-chunk size
            view.setUint16(offset, 1, true); offset += 2; // Audio format (1 = PCM)
            view.setUint16(offset, numChannels, true); offset += 2;
            view.setUint32(offset, sampleRate, true); offset += 4;
            view.setUint32(offset, byteRate, true); offset += 4;
            view.setUint16(offset, blockAlign, true); offset += 2;
            view.setUint16(offset, 16, true); offset += 2; // Bits per sample

            // data chunk
            writeString('data'); offset += 4;
            view.setUint32(offset, dataSize, true); offset += 4;

            // Write PCM data
            for (let i = 0; i < pcm16.length; i++, offset += 2) {
                view.setInt16(offset, pcm16[i], true);
            }

            function writeString(str) {
                for (let i = 0; i < str.length; i++) {
                    view.setUint8(offset + i, str.charCodeAt(i));
                }
            }

            return new Blob([view], { type: 'audio/wav' });
        }


        // === TTS AND SPEECH RECOGNITION ===

        /** Plays the game text using TTS API */
        async function speakText(text) {
            if (!ttsEnabled) return;
            ttsAudioEl.pause();
            ttsAudioEl.src = "";
            
            const payload = {
                contents: [{ parts: [{ text: text }] }],
                generationConfig: {
                    responseModalities: ["AUDIO"],
                    speechConfig: {
                        voiceConfig: {
                            prebuiltVoiceConfig: { voiceName: TTS_VOICE }
                        }
                    }
                },
            };

            const options = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            };

            try {
                const response = await fetchWithRetry(TTS_URL, options);
                const result = await response.json();
                
                const part = result?.candidates?.[0]?.content?.parts?.[0];
                const audioData = part?.inlineData?.data;
                const mimeType = part?.inlineData?.mimeType;

                if (audioData && mimeType && mimeType.startsWith("audio/L16")) {
                    // Extract sample rate from the mimeType
                    const match = mimeType.match(/rate=(\d+)/);
                    const sampleRate = match ? parseInt(match[1], 10) : 16000;
                    
                    const pcmData = base64ToArrayBuffer(audioData);
                    const pcm16 = new Int16Array(pcmData);
                    const wavBlob = pcmToWav(pcm16, sampleRate);
                    const audioUrl = URL.createObjectURL(wavBlob);
                    
                    ttsAudioEl.src = audioUrl;
                    ttsAudioEl.play().catch(e => console.error("Error playing audio:", e));
                } else {
                    console.error("TTS failed or returned unexpected data format.", result);
                    showModal('TTS ERROR', 'Gagal memutar audio. Cek konsol untuk detail error.', false);
                }
            } catch (error) {
                console.error("TTS API Call Failed:", error);
            }
        }
        
        /** Stops the currently playing audio */
        function stopAudio() {
            ttsAudioEl.pause();
            ttsAudioEl.currentTime = 0;
        }

        /** Initializes and toggles Speech Recognition */
        function toggleVoiceInput() {
            if (!SpeechRecognition) {
                showModal('ERROR', 'Browser Anda tidak mendukung Web Speech API.', false);
                return;
            }

            if (!recognition) {
                recognition = new SpeechRecognition();
                recognition.lang = 'id-ID'; // Set language to Indonesian
                recognition.interimResults = false;
                recognition.maxAlternatives = 1;

                recognition.onstart = () => {
                    isListening = true;
                    voiceBtn.classList.add('voice-listening');
                    voiceBtn.classList.remove('bg-emerald-500');
                    voiceBtn.classList.add('bg-red-500');
                    statusMessageEl.textContent = 'SYSTEM: Listening... Speak now.';
                };

                recognition.onresult = (event) => {
                    const speechResult = event.results[0][0].transcript;
                    textAnswerInput.value = speechResult;
                    statusMessageEl.textContent = `SYSTEM: Input received: "${speechResult}"`;
                    // Automatically submit the recognized text
                    processAnswer(speechResult);
                };

                recognition.onerror = (event) => {
                    isListening = false;
                    voiceBtn.classList.remove('voice-listening', 'bg-red-500');
                    voiceBtn.classList.add('bg-emerald-500');
                    statusMessageEl.textContent = `SYSTEM: Voice error. (${event.error})`;
                    console.error('Speech Recognition Error:', event.error);
                };

                recognition.onend = () => {
                    isListening = false;
                    voiceBtn.classList.remove('voice-listening', 'bg-red-500');
                    voiceBtn.classList.add('bg-emerald-500');
                    if (statusMessageEl.textContent.startsWith('SYSTEM: Listening')) {
                         statusMessageEl.textContent = 'SYSTEM: Ready for voice input (Tap 🎤 to start).';
                    }
                };
            }

            if (isListening) {
                recognition.stop();
            } else {
                try {
                    recognition.start();
                } catch (e) {
                    console.error('Recognition start error:', e);
                    statusMessageEl.textContent = 'SYSTEM: Could not start recognition. Check permissions.';
                    isListening = false;
                    voiceBtn.classList.remove('voice-listening');
                }
            }
        }


        // === FIREBASE AND GAME LOGIC ===

        /** Writes game history to Firestore */
        async function saveGameHistory() {
            if (!db || !userId) {
                console.error("Database not initialized or user not authenticated.");
                return;
            }
            
            // Public path for collaborative/shared data (using a subcollection per user for organization)
            const logCollectionRef = collection(db, `artifacts/${appId}/public/data/game_logs`);
            
            // Create a simple document structure for the log
            const logData = {
                userId: userId,
                tema: currentTema,
                level: currentLevel,
                history: JSON.stringify(gameHistory), // Serialize complex array data
                timestamp: serverTimestamp()
            };

            try {
                // Use addDoc to create a new log document
                const docRef = await addDoc(logCollectionRef, logData);
                console.log("Game history saved successfully with ID: ", docRef.id);
            } catch (e) {
                console.error("Error saving document: ", e);
                showModal('SAVE ERROR', 'Gagal menyimpan riwayat game ke database.', false);
            }
        }

        /** Clears the game state and UI */
        function resetGame() {
            gameActive = false;
            gameHistory = [];
            stopAudio();
            gameTextEl.innerHTML = `<span class="system-ready font-light">
                // :: SYSTEM READY :: PUSH START TO INITIATE LEADERSHIP PROTOCOL //
            </span>`;
            choicesEl.innerHTML = '';
            textAnswerInput.value = '';
            textAnswerInput.disabled = true;
            sendCmdBtn.disabled = true;
            voiceBtn.disabled = true;
            startBtn.disabled = false;
            
            // Reset button styles if voice was active
            if (isListening) recognition.stop();
            voiceBtn.classList.remove('voice-listening', 'bg-red-500');
            voiceBtn.classList.add('bg-emerald-500');
            statusMessageEl.textContent = '';
            
            showModal('GAME RESET', 'Sistem telah direset. Silakan pilih skenario baru dan klik START.', false);
        }

        /** Appends text to the game console, handles TTS, and logs history */
        function appendToGame(text, isUser = false) {
            const prefix = isUser ? `> [PLAYER_CMD]: ` : `[SYSTEM]: `;
            const newText = `\n${prefix}${text}\n`;
            
            gameTextEl.innerHTML += newText;
            gameTextEl.scrollTop = gameTextEl.scrollHeight; // Auto-scroll

            gameHistory.push({
                speaker: isUser ? 'User' : 'System',
                text: text,
                timestamp: new Date().toISOString()
            });

            if (!isUser) {
                speakText(text);
            }
        }

        /** Renders multiple-choice options */
        function renderChoices(options) {
            choicesEl.innerHTML = '';
            // Hide text input for multiple choice
            textAnswerInput.disabled = true;
            sendCmdBtn.disabled = true;
            voiceBtn.disabled = true;

            options.forEach((choice, index) => {
                const char = String.fromCharCode(65 + index); // A, B, C, ...
                const button = document.createElement('button');
                
                button.textContent = `${char}. ${choice.text}`;
                button.className = 'choice-btn flex-1 min-w-[200px] bg-blue-600 hover:bg-blue-500 text-white font-semibold px-6 py-3 rounded-full transition-all shadow-lg hover:shadow-xl btn-press';
                button.setAttribute('data-choice-id', choice.id);
                button.setAttribute('data-choice-char', char);
                
                button.onclick = () => handleChoiceSelection(char, choice.text);
                choicesEl.appendChild(button);
            });
        }
        
        /** Handles user selecting a choice (click or text input) */
        function handleChoiceSelection(choiceChar, choiceText) {
            stopAudio();
            appendToGame(`Memilih Opsi [${choiceChar}] - ${choiceText}`, true);
            
            // Clear choices and re-enable input for the next prompt
            choicesEl.innerHTML = '';
            textAnswerInput.value = '';
            textAnswerInput.disabled = true; // Wait for LLM response
            sendCmdBtn.disabled = true;
            voiceBtn.disabled = true;
            
            // Get the next response from the LLM
            generateNextScenario(choiceText);
        }

        /**
         * Main function to interact with Gemini API for game logic.
         * Generates the initial scenario or the next step based on user input.
         */
        async function generateNextScenario(userInput = null) {
            startBtn.disabled = true;
            const currentTemaValue = temaSelect.value;
            const currentLevelValue = levelSelect.value;

            // Build the system prompt
            let systemPrompt = `Anda adalah seorang Game Master yang menjalankan simulasi Kepemimpinan dan Soft Skills bernama "Cyber-Quest".
            Tugas Anda adalah:
            1. Menyajikan narasi berbasis teks yang menarik dan imersif.
            2. Berdasarkan tema: "${currentTemaValue}" dan tingkat kesulitan: "${currentLevelValue}", buatlah skenario baru atau tindak lanjut dari skenario sebelumnya.
            3. Jika ini adalah skenario Awal (ditandai dengan userInput 'START_GAME'), buat Narasi Pembuka.
            4. Jika ini adalah tindak lanjut, buat Narasi Respon, dan kemudian Narasi Skenario Baru.
            5. Setiap langkah HARUS menghasilkan SATU narasi (maksimal 3-4 kalimat) diikuti dengan 3 pilihan tindakan (Multiple Choice Options) yang relevan dan menantang bagi pemain.
            6. Jangan pernah menghasilkan narasi tanpa pilihan (kecuali di bagian akhir).
            7. Seluruh output harus dalam format JSON yang ketat.

            FORMAT OUTPUT WAJIB (application/json):
            {
                "narasi": "Teks narasi sistem (maksimal 4 kalimat).",
                "status_game": "Lanjut" atau "Selesai",
                "pilihan": [
                    {"id": "A", "text": "Pilihan A"},
                    {"id": "B", "text": "Pilihan B"},
                    {"id": "C", "text": "Pilihan C"}
                ]
            }
            Jika status_game adalah "Selesai", jangan berikan pilihan. Narasi harus berisi ringkasan hasil permainan dan skor/feedback akhir.
            `;
            
            // Determine user query based on game state
            let userQuery;
            if (userInput === 'START_GAME') {
                userQuery = `Mulailah permainan. Narasi awal berdasarkan tema "${currentTemaValue}" dan tingkat "${currentLevelValue}".`;
            } else {
                userQuery = `Saya memilih tindakan: ${userInput}. Lanjutkan skenario, berikan respon terhadap tindakan saya, dan tawarkan 3 pilihan baru.`;
            }

            // Build history for context (optional, but good for multi-turn games)
            const chatHistory = gameHistory.map(log => ({
                role: log.speaker === 'User' ? 'user' : 'model',
                parts: [{ text: log.text }]
            }));
            chatHistory.push({ role: 'user', parts: [{ text: userQuery }] });


            // Prepare the API payload for Structured JSON Output
            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "narasi": { "type": "STRING", "description": "Teks narasi dan respons sistem." },
                            "status_game": { "type": "STRING", "description": "Status game: 'Lanjut' atau 'Selesai'." },
                            "pilihan": {
                                "type": "ARRAY",
                                "items": {
                                    "type": "OBJECT",
                                    "properties": {
                                        "id": { "type": "STRING", "description": "A, B, atau C" },
                                        "text": { "type": "STRING", "description": "Teks opsi pilihan." }
                                    },
                                    "propertyOrdering": ["id", "text"]
                                }
                            }
                        },
                        "required": ["narasi", "status_game"]
                    }
                }
            };

            const options = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            };
            
            try {
                // Clear choices and show temporary loading message
                choicesEl.innerHTML = '<span class="text-gray-700 font-mono italic animate-pulse">:: DATA STREAM INITIATED... AWAITING RESPONSE ::</span>';
                
                const response = await fetchWithRetry(BASE_URL, options);
                const result = await response.json();
                
                const candidate = result.candidates?.[0];
                if (!candidate || !candidate.content?.parts?.[0]?.text) {
                    throw new Error("Invalid response structure from API.");
                }

                const jsonText = candidate.content.parts[0].text;
                let gameResponse;
                try {
                    gameResponse = JSON.parse(jsonText);
                } catch (e) {
                    console.error("Failed to parse JSON:", jsonText, e);
                    throw new Error("Failed to parse game response into JSON format.");
                }

                appendToGame(gameResponse.narasi, false);

                if (gameResponse.status_game.toLowerCase() === 'selesai') {
                    gameActive = false;
                    textAnswerInput.disabled = true;
                    sendCmdBtn.disabled = true;
                    voiceBtn.disabled = true;
                    startBtn.disabled = true; // Prevent re-start without reset
                    showModal('SIMULASI SELESAI', 'Permainan telah berakhir. Anda dapat me-reset atau mendownload log riwayat.', false);
                    await saveGameHistory();
                } else {
                    renderChoices(gameResponse.pilihan);
                    gameActive = true;
                    
                    // Enable manual input for choice letters (A, B, C)
                    textAnswerInput.disabled = false;
                    sendCmdBtn.disabled = false;
                    voiceBtn.disabled = false;
                    textAnswerInput.focus();
                }

            } catch (error) {
                console.error("Game Generation Error:", error);
                const errorMessage = `[ERROR]: Gagal memuat skenario game. Detail: ${error.message}. Coba reset game dan mulai lagi.`;
                appendToGame(errorMessage, false);
                choicesEl.innerHTML = '';
                gameActive = false;
                startBtn.disabled = false;
            }
        }

        /** Handles text/voice answer form submission */
        function handleFormSubmit(event) {
            event.preventDefault();
            if (!gameActive) return;

            const input = textAnswerInput.value.trim().toUpperCase();
            
            // Check if input is a valid choice (A, B, or C)
            const choiceBtns = choicesEl.querySelectorAll('.choice-btn');
            const selectedBtn = Array.from(choiceBtns).find(btn => btn.getAttribute('data-choice-char') === input);
            
            if (selectedBtn) {
                // User entered a choice letter
                const choiceText = selectedBtn.textContent.substring(3).trim();
                handleChoiceSelection(input, choiceText);
            } else if (choiceBtns.length > 0) {
                // Input is not a valid choice and choices are available
                showModal('INPUT ERROR', 'Mohon masukkan pilihan yang valid (A, B, atau C).', false);
            } else {
                // Input is free text (Not supported in this version, should not happen)
                showModal('SYSTEM ERROR', 'Game sedang menunggu pilihan, bukan input teks bebas.', false);
            }

            textAnswerInput.value = '';
        }

        // === EVENT LISTENERS ===

        startBtn.addEventListener('click', () => {
            if (gameActive) {
                showModal('GAME IN PROGRESS', 'Permainan sedang berjalan. Mohon selesaikan langkah saat ini atau RESET.', false);
                return;
            }
            currentTema = temaSelect.value;
            currentLevel = levelSelect.value;
            generateNextScenario('START_GAME');
        });

        resetBtn.addEventListener('click', () => {
            showModal('KONFIRMASI RESET', 'Apakah Anda yakin ingin me-reset simulasi? Riwayat saat ini akan dihapus.', true).then(confirmed => {
                if (confirmed) {
                    resetGame();
                }
            });
        });

        // Toggle TTS on/off
        ttsToggleBtn.addEventListener('click', () => {
            ttsEnabled = !ttsEnabled;
            ttsToggleBtn.textContent = ttsEnabled ? '🔈 TTS ON' : '🔈 TTS OFF';
            ttsToggleBtn.classList.toggle('bg-green-500', ttsEnabled); // Green for ON
            ttsToggleBtn.classList.toggle('bg-yellow-500', !ttsEnabled); // Yellow for OFF
        });
        
        // Stop current audio playback
        ttsStopBtn.addEventListener('click', stopAudio);

        // Custom Tema Handling
        addTemaBtn.addEventListener('click', () => {
            const customTema = temaCustomInput.value.trim();
            if (customTema) {
                const newOption = document.createElement('option');
                newOption.value = customTema;
                newOption.textContent = `[CUSTOM]: ${customTema}`;
                temaSelect.appendChild(newOption);
                temaSelect.value = customTema; // Select the new custom theme
                temaCustomInput.value = ''; // Clear input
                showModal('DATA ADDED', 'Skenario kustom berhasil ditambahkan dan dipilih.', false);
            } else {
                showModal('INPUT REQUIRED', 'Mohon masukkan teks skenario kustom di kolom input.', false);
            }
        });
        
        // Random Tema (Selects a random pre-defined option)
        randomTemaBtn.addEventListener('click', () => {
            const options = Array.from(temaSelect.options).filter(opt => !opt.value.includes('Manajemen Waktu Efektif')); // Exclude the combined UKOM option
            if (options.length > 0) {
                const randomIndex = Math.floor(Math.random() * options.length);
                temaSelect.value = options[randomIndex].value;
                showModal('TEMA ACAK', `Tema berhasil dipilih secara acak: ${options[randomIndex].textContent.replace('[CUSTOM]:', '').trim()}`, false);
            }
        });

        // Text Answer Form Submission
        textAnswerForm.addEventListener('submit', handleFormSubmit);

        // Voice Input Button
        voiceBtn.addEventListener('click', toggleVoiceInput);
        
        // Download History
        downloadBtn.addEventListener('click', () => {
            if (gameHistory.length === 0) {
                showModal('LOG KOSONG', 'Tidak ada riwayat permainan untuk diunduh.', false);
                return;
            }
            
            const logContent = gameHistory.map(log => 
                `[${log.timestamp}] [${log.speaker.toUpperCase()}]: ${log.text}`
            ).join('\n---\n');

            const blob = new Blob([logContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `CyberQuest_Log_${currentTema}_${new Date().toISOString()}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showModal('DOWNLOAD BERHASIL', 'Riwayat permainan telah diunduh.', false);
        });
        
        // Analyze History
        analyzeBtn.addEventListener('click', () => {
            if (gameHistory.length === 0) {
                showModal('LOG KOSONG', 'Tidak ada riwayat permainan yang cukup untuk dianalisis.', false);
                return;
            }
            // Prepare data for server submission (simulated in this environment)
            const gameHistoryStr = JSON.stringify(gameHistory);
            
            // Populate hidden form fields
            document.getElementById('gameHistoryInput').value = gameHistoryStr;
            document.getElementById('temaInput').value = temaSelect.options[temaSelect.selectedIndex].text;

            // Submit the form (simulating opening a new analysis page)
            const analysisPrompt = `Tugas: Analisis Kinerja Kepemimpinan dalam Cyber-Quest.\n\nTema Skenario: ${temaSelect.options[temaSelect.selectedIndex].text}\nLevel: ${levelSelect.value}\n\nRiwayat Game:\n${gameHistory.map(log => `[${log.speaker.toUpperCase()}]: ${log.text}`).join('\n')}\n\nInstruksi Analisis:\n1. Berikan ringkasan singkat alur cerita.\n2. Analisis Kualitas Keputusan Pemain (Kritis, Kreatif, Beretika) di setiap langkah.\n3. Berikan skor akhir (1-10) dan saran pengembangan diri yang spesifik berdasarkan tema.`;

            // Since we can't submit to a real server, we will use the LLM to generate the analysis response directly (Simulating the backend analysis)
            generateAnalysis(analysisPrompt);
        });

        async function generateAnalysis(prompt) {
             const systemPrompt = `Anda adalah seorang Analis Kinerja Kecerdasan Emosional dan Kepemimpinan. Tugas Anda adalah menganalisis riwayat permainan "Cyber-Quest" yang diberikan. Berikan analisis yang mendalam, konstruktif, dan profesional dalam format Markdown.`;

             const analysisPayload = {
                contents: [{ parts: [{ text: prompt }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
             };

             const options = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(analysisPayload)
            };

            try {
                showModal('ANALYSIS INITIATED', 'Menganalisis riwayat game... Tunggu sebentar.', false);
                const response = await fetchWithRetry(BASE_URL, options);
                const result = await response.json();
                
                const analysisText = result.candidates?.[0]?.content?.parts?.[0]?.text || "Gagal mendapatkan hasil analisis. Coba lagi.";
                
                // Display the analysis in a new window/modal
                const analysisWindow = window.open("", "_blank");
                analysisWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="id">
                    <head>
                        <title>Cyber-Quest Analysis Report</title>
                        <script src="https://cdn.tailwindcss.com"></script>
                        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
                        <style>
                            body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937; padding: 20px; }
                            .report { max-width: 800px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
                            h1 { color: #1d4ed8; border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 20px; }
                            h2 { color: #059669; margin-top: 20px; }
                            pre { background-color: #e5e7eb; padding: 15px; border-radius: 8px; overflow-x: auto; color: #1f2937; font-family: 'Chivo Mono', monospace; }
                        </style>
                    </head>
                    <body>
                        <div class="report">
                            <h1 class="text-3xl font-bold">CYBER-QUEST ANALYSIS REPORT</h1>
                            <p class="text-sm text-gray-500 mb-4">Generated on: ${new Date().toLocaleString()}</p>
                            <div class="markdown-content">
                                <!-- Pre-formatted to show markdown -->
                                <pre>${analysisText}</pre>
                            </div>
                        </div>
                    </body>
                    </html>
                `);
                analysisWindow.document.close();
                showModal('ANALYSIS COMPLETE', 'Laporan analisis telah dibuka di jendela baru.', false);

            } catch (error) {
                console.error("Analysis Generation Error:", error);
                showModal('ANALYSIS ERROR', `Gagal menghasilkan analisis. Detail: ${error.message}.`, false);
            }
        }


        // === INITIALIZATION ===

        window.onload = function() {
            // Set up Firebase/Auth
            if (firebaseConfig) {
                try {
                    setLogLevel('debug'); // Enable Firestore logging
                    app = initializeApp(firebaseConfig);
                    db = getFirestore(app);
                    auth = getAuth(app);
                    
                    onAuthStateChanged(auth, async (user) => {
                        if (user) {
                            userId = user.uid;
                            console.log("Firebase Auth Success. UID:", userId);
                            // Initial setup after auth is ready
                            resetGame(); 
                        } else {
                            // If no user object (but we expect one from token or anonymous sign-in)
                            console.warn("No user found in onAuthStateChanged.");
                        }
                    });

                    // Try to sign in with the provided token or anonymously
                    (async () => {
                        try {
                            if (initialAuthToken) {
                                await signInWithCustomToken(auth, initialAuthToken);
                                console.log("Signed in with custom token.");
                            } else {
                                await signInAnonymously(auth);
                                console.log("Signed in anonymously.");
                            }
                        } catch (error) {
                            console.error("Firebase Sign-In Failed:", error);
                            showModal('AUTH ERROR', 'Gagal melakukan autentikasi dengan Firebase. Cek koneksi Anda.', false);
                        }
                    })();

                } catch (e) {
                    console.error("Firebase Initialization Failed:", e);
                    showModal('INIT ERROR', 'Gagal inisialisasi Firebase. Aplikasi tidak dapat menyimpan data.', false);
                }
            } else {
                console.warn("Firebase config not available. Running game without persistence.");
                resetGame();
            }
        };

    </script>
</body>
</html>
