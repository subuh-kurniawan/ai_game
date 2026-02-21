<?php

include "../admin/fungsi/koneksi.php";
$sql = mysqli_query($koneksi, "SELECT * FROM datasekolah");
$data = mysqli_fetch_assoc($sql);
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Uji Kompetensi Bahasa Lampung</title>
    <!-- Memuat Tailwind CSS CDN untuk styling modern dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Menggunakan font Inter untuk tampilan profesional dan Merriweather untuk konten narasi/analisis */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Merriweather:wght@400;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1c1917, #57534e); /* Dark slate/brown inspired by Lampung soil */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        #game-container {
            background-color: #292524; /* Darker brown background */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            max-width: 800px;
            width: 100%;
            border-radius: 1.5rem;
            overflow: hidden;
            border: 5px solid #fb923c; /* Orange/Gold accent (Lampung color) */
        }

        /* Styling untuk kotak narasi/analisis */
        #narrative-box {
            background-color: #3b3433; /* Slightly lighter inner box */
            font-family: 'Merriweather', serif;
            font-size: 1.05rem;
            line-height: 1.7;
            min-height: 180px;
            padding: 1.5rem;
            color: #f5f5f4; /* Light text */
            border-bottom: 3px solid #f97316; /* Darker Orange */
            position: relative;
        }

        /* S.E.A. Avatar */
        #gm-avatar {
            font-size: 2.5rem;
            margin-right: 1rem;
            text-shadow: 0 0 8px #f97316;
        }

        /* Styling untuk kotak tantangan/input */
        #challenge-area {
            background-color: #292524;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
        }

        .challenge-prompt {
            font-size: 1.15rem;
            font-weight: 600;
            color: #4ade80; /* Green accent */
            text-align: center;
            padding: 0.75rem;
            border: 2px solid #6ee7b7;
            border-radius: 0.75rem;
            background-color: #1c1917;
        }

        .game-button {
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px #c2410c; /* Darker shadow for 3D effect (Red/Brown) */
        }
        .game-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px #c2410c;
        }

        .selection-button {
            background-color: #f97316; /* Orange accent */
            color: white;
            font-weight: bold;
            padding: 1rem;
            border-radius: 0.75rem;
            transition: all 0.2s;
            box-shadow: 0 4px #ea580c;
        }
        .selection-button:hover {
            background-color: #ea580c;
        }
        .selection-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px #ea580c;
        }
        
        #message-alert {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -100%);
            width: 90%;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: bold;
            text-align: center;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            z-index: 10;
        }
        .message-show {
            opacity: 1 !important;
            transform: translate(-50%, -120%) !important;
        }
        
        .loading-spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid #f97316; /* Orange loading color */
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Gaya Khusus untuk Tombol Reset */
        #reset-game-button {
            color: #f87171; /* Merah Muda */
            border: 1px solid #dc2626;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        #reset-game-button:hover {
            background-color: #7f1d1d;
        }
    </style>
</head>
<body>

    <div id="game-container">
        <!-- HEADER: Score and Module -->
        <div class="p-4 bg-orange-700 text-white flex justify-between items-center rounded-t-xl font-bold">
            <span id="game-title" class="text-xl">Sistem Uji Kompetensi Bahasa Lampung</span>
            <div class="flex space-x-4 text-sm items-center">
                <!-- TTS Toggle Button -->
                <button id="tts-toggle" class="bg-gray-800 text-white p-1 px-3 rounded-full shadow-md text-xs hover:bg-gray-700 transition" onclick="toggleTts()">
                    🔈 Narasi Aktif
                </button>
                <span class="p-1 px-3 bg-orange-800 text-white rounded-full shadow-md">Skor: <span id="score-display">0</span></span>
                <span class="p-1 px-3 bg-yellow-600 text-black rounded-full shadow-md">Modul Uji: <span id="region-display">Memuat...</span></span>
            </div>
        </div>

        <!-- NARATIVE AREA (S.E.A. - Sistem Evaluasi Aksara) -->
        <div id="narrative-box">
            <div class="flex items-start">
                <!-- Icon of fire/flame representing spirit/Lampung identity -->
                <span id="gm-avatar" class="text-yellow-400">🔥</span> 
                <div>
                    <span class="font-bold text-lg text-orange-400">Sistem Evaluasi Aksara (S.E.A.):</span>
                    <p id="narrative-text" class="text-gray-300 mt-2">Memuat sistem...</p>
                </div>
            </div>
        </div>

        <!-- CHALLENGE AREA -->
        <div id="challenge-area">
            
            <!-- Message Alert (Hidden by default) -->
            <div id="message-alert" class="bg-red-500 text-white hidden"></div>

            <!-- Challenge Prompt (misal: kata-kata acak, teks yang salah) -->
            <div id="challenge-prompt" class="challenge-prompt hidden"></div>
            
            <!-- Mode/Difficulty Selection Area -->
            <div id="selection-area" class="grid grid-cols-3 gap-4 hidden">
                <!-- Buttons will be generated here (Mode or Difficulty) -->
            </div>

            <!-- User Input -->
            <input type="text" id="user-input" class="p-3 border-2 border-orange-500 rounded-lg bg-stone-700 text-white placeholder-stone-400 focus:ring-4 focus:ring-orange-400 focus:border-orange-600 transition duration-150" placeholder="Masukkan jawaban Anda di sini (misal: terjemahan, transliterasi)...">

            <!-- Action Buttons: Hint and Submit -->
            <div class="flex gap-4">
                <button id="hint-button" class="game-button bg-yellow-500 text-gray-900 font-bold py-3 rounded-lg hover:bg-yellow-600 disabled:opacity-50 flex-1" onclick="requestHint()">Berikan Petunjuk (-5 Poin)</button>
                <button id="action-button" class="game-button bg-orange-600 text-white font-bold py-3 rounded-lg hover:bg-orange-700 disabled:opacity-50 flex-1" onclick="handleAction()">Kirim Jawaban</button>
            </div>
            
            <!-- Review Area -->
            <div id="review-area" class="mt-4 p-4 bg-stone-700 rounded-xl hidden flex-col items-center">
                <p id="review-message" class="text-orange-400 mb-3 font-semibold text-center"></p>
                <button id="download-button" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition duration-150" onclick="downloadReview()">
                    ⬇️ Unduh Ringkasan Evaluasi (.txt)
                </button>
            </div>

            <!-- Loading Indicator -->
            <div id="loading-indicator" class="flex justify-center items-center gap-2 py-2 hidden">
                <div class="loading-spinner"></div>
                <span class="text-gray-400 text-sm">Sistem Evaluasi Aksara sedang memproses...</span>
            </div>

            <!-- Reset Button -->
            <button id="reset-game-button" class="text-xs text-gray-500 hover:text-red-400 mt-2" onclick="resetGame()">Reset Uji Kompetensi (Hapus Data)</button>
        </div>
    </div>

    <script>
        // --- 0. Konfigurasi dan Variabel Global API ---
        const apiKey = "<?php echo $apiKey; ?>"; // Dibiarkan kosong, akan diisi oleh runtime
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;

        // DOM Elements (Names kept for functionality, but context is Lampung)
        const scoreDisplay = document.getElementById('score-display');
        const regionDisplay = document.getElementById('region-display');
        const narrativeText = document.getElementById('narrative-text');
        const challengePrompt = document.getElementById('challenge-prompt');
        const userInput = document.getElementById('user-input');
        const actionButton = document.getElementById('action-button');
        const hintButton = document.getElementById('hint-button');
        const messageAlert = document.getElementById('message-alert');
        const selectionArea = document.getElementById('selection-area'); 
        const loadingIndicator = document.getElementById('loading-indicator');
        const ttsToggle = document.getElementById('tts-toggle'); 
        const reviewArea = document.getElementById('review-area');
        const reviewMessage = document.getElementById('review-message');
        const downloadButton = document.getElementById('download-button');


        // Game State
        let gameState = {
            score: 0,
            regionIndex: -1, 
            difficulty: null, 
            challengeIndex: 0,
            targetScore: 50,
            currentChallenge: null,
            isTtsEnabled: true,
            challengeSeeds: {},
            finalReviewText: null,
        };

        // Daftar Modul Uji Kompetensi Bahasa Lampung BARU
        const REGIONS = [
            { name: "Dialek A (Nyo) & O (Api)", key: "dialek", prompt_type: "dialek_comparison" },
            { name: "Aksara Lampung (Ka-Ga-Nga)", key: "aksara", prompt_type: "aksara_reading_writing" },
            { name: "Kosakata Inti & Kata Ganti", key: "kosakata", prompt_type: "lexicon_and_pronoun" },
            { name: "Struktur Kalimat Dasar", key: "sintaksis", prompt_type: "basic_sentence_structure" },
            { name: "Peribahasa & Ungkapan Adat", key: "adat", prompt_type: "adat_proverbs" },
        ];

        // Tingkat Kesulitan (Formal)
        const DIFFICULTIES = {
            "Dasar": { targetMultiplier: 1.0, basePoints: 20, description: "Konsep fundamental Dialek dan Aksara. Target 50 poin." },
            "Menengah": { targetMultiplier: 1.5, basePoints: 25, description: "Penerapan aturan transliterasi dan variasi kosakata. Target 75 poin." },
            "Lanjutan": { targetMultiplier: 2.0, basePoints: 30, description: "Analisis sintaksis kompleks dan interpretasi peribahasa adat. Target 100 poin." }
        };
        
        // --- 1. TTS Logic & Helper Functions ---

        function speakNarrative(text) {
            if (!gameState.isTtsEnabled || !('speechSynthesis' in window)) {
                return;
            }

            window.speechSynthesis.cancel();
            
            // 1. Clean the text from HTML tags (retaining the original functionality for complex content)
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = text;
            let cleanedText = tempDiv.textContent;

            // 2. Remove the specific formal prefixes requested by the user from the start of the text.
            // Mengubah awalan sistem dari S.E.D. menjadi S.E.A.
            cleanedText = cleanedText
                .replace(/^Sistem Evaluasi Aksara \(S\.E\.A\.\):\s*/i, '') 
                .replace(/^S\.E\.A\.:\s*/i, '') 
                .trim();
            
            const utterance = new SpeechSynthesisUtterance(cleanedText);
            utterance.lang = 'id-ID';
            utterance.pitch = 1.0;
            utterance.rate = 0.95;

            const setVoiceAndSpeak = () => {
                const voices = window.speechSynthesis.getVoices();
                const indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google"))
                                || voices.find(v => v.lang === 'id-ID');
                
                if (indoVoice) {
                    utterance.voice = indoVoice;
                }

                window.speechSynthesis.speak(utterance);
            };

            if (window.speechSynthesis.getVoices().length > 0) {
                setVoiceAndSpeak();
            } else {
                window.speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
            }
        }
        
        // Helper to update narrative text and trigger TTS
        function setNarrativeText(text) {
            narrativeText.textContent = text;
            speakNarrative(text);
        }

        function toggleTts() {
            gameState.isTtsEnabled = !gameState.isTtsEnabled;
            if (gameState.isTtsEnabled) {
                ttsToggle.textContent = "🔈 Narasi Aktif";
                speakNarrative("Narasi suara telah diaktifkan.");
            } else {
                ttsToggle.textContent = "🔇 Narasi Mati";
                window.speechSynthesis.cancel();
            }
            updateUI();
        }
        
        /**
         * Membersihkan teks dari karakter Markdown yang tidak diinginkan
         */
        function cleanText(text) {
            if (!text) return '';
            // Membersihkan karakter Markdown umum di awal/akhir baris
            return text.replace(/^[*\s#>]*(.*?)[*\s#]*$/gm, (match, p1) => p1.trim()).trim();
        }

        // Helper untuk menampilkan pesan singkat
        function showMessageAlert(message, type) {
            messageAlert.textContent = message;
            messageAlert.className = `message-show`;
            if (type === 'success') {
                messageAlert.classList.add('bg-green-600', 'text-white');
            } else {
                messageAlert.classList.add('bg-red-600', 'text-white');
            }
            messageAlert.classList.remove('hidden');

            setTimeout(() => {
                messageAlert.classList.remove('message-show');
                setTimeout(() => messageAlert.classList.add('hidden'), 300);
            }, 1500);
        }

        // Helper untuk mengaktifkan/menonaktifkan loading state
        function setLoading(isLoading) {
            if (isLoading) {
                loadingIndicator.classList.remove('hidden');
                actionButton.disabled = true;
                hintButton.disabled = true;
                userInput.disabled = true;
                window.speechSynthesis.cancel();
            } else {
                loadingIndicator.classList.add('hidden');
                
                const isInChallengeMode = gameState.regionIndex !== -1 && 
                                           selectionArea.classList.contains('hidden') && 
                                           reviewArea.classList.contains('hidden');

                if (isInChallengeMode) {
                    actionButton.disabled = false;
                    hintButton.disabled = false;
                    userInput.disabled = false;
                    userInput.focus(); 
                } else {
                    actionButton.disabled = true;
                    hintButton.disabled = true;
                    userInput.disabled = true;
                }
            }
        }

        // --- Gemini API Handler ---
        async function callGeminiAPI(systemInstruction, userPrompt, jsonSchema = null) {
            setLoading(true);
            let payload = {
                contents: [{ parts: [{ text: userPrompt }] }],
                systemInstruction: { parts: [{ text: systemInstruction }] },
            };

            if (jsonSchema) {
                payload.generationConfig = {
                    responseMimeType: "application/json",
                    responseSchema: jsonSchema
                };
            }

            const maxRetries = 3;
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        if (response.status === 429 && i < maxRetries - 1) {
                            const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                            await new Promise(resolve => setTimeout(resolve, delay));
                            continue;
                        }
                        throw new Error(`Panggilan API gagal dengan status ${response.status}`);
                    }

                    const result = await response.json();
                    setLoading(false);
                    const part = result.candidates?.[0]?.content?.parts?.[0];
                    if (part && part.text) {
                        if (jsonSchema) {
                            try {
                                return JSON.parse(part.text);
                            } catch (e) {
                                console.error("Gagal mengurai respons JSON:", e);
                                throw new Error("API mengembalikan format JSON yang tidak valid.");
                            }
                        }
                        return part.text;
                    }
                    throw new Error("Struktur respons dari API tidak valid.");

                } catch (error) {
                    if (i === maxRetries - 1) {
                        setLoading(false);
                        console.error("Kesalahan API Gemini setelah percobaan ulang:", error);
                        // Menggunakan S.E.A. untuk pesan error
                        return jsonSchema ? { isCorrect: false, pointsAwarded: 0, feedbackNarrative: "S.E.A.: Maaf, terjadi gangguan koneksi. Mohon coba kembali." } : "S.E.A.: Terjadi kesalahan koneksi, silakan coba ulang.";
                    }
                }
            }
        }
        
        // --- Dynamic Challenge Generation Logic ---

        /**
         * Memanggil AI untuk menghasilkan 5 tantangan unik per kategori.
         */
        async function generateChallengeSeeds() {
            setLoading(true);
            
            const seedSchema = {
                type: "OBJECT",
                properties: REGIONS.reduce((acc, region) => {
                    acc[region.prompt_type] = {
                        type: "ARRAY",
                        items: { type: "STRING" }
                    };
                    return acc;
                }, {}),
                required: REGIONS.map(r => r.prompt_type)
            };

            // SISTEM INSTRUCTION FORMAL: Sistem Pembuat Soal Akademik Bahasa Lampung
            const systemInstruction = `Anda adalah Sistem Pembuat Soal Akademik Bahasa Lampung. Tugas Anda adalah menyusun 5 tantangan akademik yang unik dan orisinal untuk setiap kategori Bahasa Lampung yang diberikan, sesuai dengan tingkat kesulitan ${gameState.difficulty}. Soal dan contoh harus selalu mengambil konteks dari lingkup BUDAYA LAMPUNG, ADAT, dan komunikasi sehari-hari/formal. Jawab HANYA dengan JSON.
            - Untuk dialek_comparison, berikan kata/frasa dalam satu dialek yang harus diubah ke dialek lainnya (A/O).
            - Untuk aksara_reading_writing, berikan frasa Latin pendek yang harus ditransliterasi ke Aksara Lampung atau sebaliknya (berikan frasa Aksara Lampung).
            - Untuk lexicon_and_pronoun, berikan kalimat Bahasa Indonesia yang menuntut penggunaan kosakata/kata ganti inti Bahasa Lampung yang tepat.
            - Untuk basic_sentence_structure, berikan kalimat acak yang harus disusun menjadi kalimat baku Bahasa Lampung yang benar.
            - Untuk adat_proverbs, berikan peribahasa/ungkapan adat Lampung yang harus diinterpretasikan maknanya.
            - JANGAN BERIKAN JAWABAN.
            - Format output harus persis sesuai skema JSON yang diberikan.`;
            
            const regionNames = REGIONS.map(r => `${r.name} (${r.key})`).join(', ');
            const userPrompt = `Buatkan 5 seed tantangan untuk setiap kategori Bahasa Lampung ini: ${regionNames}. Kesulitan: ${gameState.difficulty}.`;

            const generatedSeeds = await callGeminiAPI(systemInstruction, userPrompt, seedSchema);

            if (generatedSeeds && Object.keys(generatedSeeds).length === REGIONS.length) {
                gameState.challengeSeeds = generatedSeeds;
                saveGame();
                return true;
            }
            return false;
        }

        // --- Mode Selection Logic ---
        function hideGameElements() {
            userInput.classList.add('hidden');
            challengePrompt.classList.add('hidden');
            actionButton.classList.add('hidden');
            hintButton.classList.add('hidden');
            reviewArea.classList.add('hidden');
            selectionArea.classList.remove('hidden');
            selectionArea.innerHTML = '';
            selectionArea.className = 'grid grid-cols-3 gap-4';
        }

        function showModeSelection() {
            hideGameElements();
            selectionArea.classList.add('grid-cols-3');
            
            // Mengubah narasi ke konteks Lampung
            setNarrativeText("S.E.A.: Selamat datang di Sistem Uji Kompetensi Bahasa Lampung. Silakan pilih Modul Uji Keterampilan Aksara dan Bahasa yang akan Anda kerjakan.");
            regionDisplay.textContent = "Pilih Modul";
            
            REGIONS.forEach((region, index) => {
                const button = document.createElement('button');
                button.className = 'selection-button';
                button.textContent = region.name;
                button.onclick = () => showDifficultySelection(index);
                selectionArea.appendChild(button);
            });
        }
        
        function showDifficultySelection(regionIndex) {
            gameState.regionIndex = regionIndex;
            hideGameElements();
            selectionArea.classList.remove('hidden');
            selectionArea.classList.add('grid-cols-3');

            const selectedRegion = REGIONS[regionIndex].name;
            // Mengubah narasi ke konteks Lampung
            setNarrativeText(`S.E.A.: Anda memilih Modul Uji ${selectedRegion}. Selanjutnya, tentukan Tingkat Kesulitan yang Anda inginkan:`);
            regionDisplay.textContent = "Pilih Kesulitan";
            
            Object.keys(DIFFICULTIES).forEach(difficultyKey => {
                const button = document.createElement('button');
                const difficulty = DIFFICULTIES[difficultyKey];
                
                button.className = 'selection-button flex flex-col items-center justify-center';
                button.innerHTML = `<span class="text-xl">${difficultyKey}</span><span class="text-xs font-normal mt-1">${difficulty.description.split('.')[0]}</span>`;
                button.onclick = () => startGame(difficultyKey);
                selectionArea.appendChild(button);
            });
        }
        
        async function startGame(difficultyKey) {
            gameState.difficulty = difficultyKey;
            const difficulty = DIFFICULTIES[difficultyKey];
            
            gameState.targetScore = 50 * difficulty.targetMultiplier;
            
            gameState.challengeIndex = 0;
            gameState.score = 0;
            gameState.currentChallenge = null;
            gameState.finalReviewText = null;

            // 1. GENERATE DYNAMIC SEEDS
            // Mengubah narasi ke konteks Lampung
            setNarrativeText("S.E.A.: Sistem sedang merumuskan soal Aksara dan Bahasa Lampung. Harap tunggu, kami menyiapkan set tantangan baru sesuai konteks Budaya dan Adat Lampung...");
            const success = await generateChallengeSeeds();
            
            if (!success) {
                setNarrativeText("S.E.A.: Gagal menghasilkan set soal baru. Mohon lakukan reset uji kompetensi dan periksa koneksi internet Anda.");
                showDifficultySelection(gameState.regionIndex);
                return;
            }
            
            // 2. Lanjutkan Game
            selectionArea.classList.add('hidden');
            userInput.classList.remove('hidden');
            actionButton.classList.remove('hidden');
            hintButton.classList.remove('hidden');
            
            // Mengubah narasi ke konteks Lampung
            setNarrativeText(`S.E.A.: Set soal ${difficultyKey} telah siap. Sesi Uji Kompetensi Modul ${REGIONS[gameState.regionIndex].name} dimulai.`);

            updateUI();
            nextChallenge();
        }


        /**
         * Memuat status game dari Local Storage atau menginisiasi game baru.
         */
        function initGame() {
            try {
                const savedState = localStorage.getItem('bahasaNusantaraGame');
                if (savedState) {
                    gameState = JSON.parse(savedState);
                    
                    if (typeof gameState.isTtsEnabled === 'undefined') {
                        gameState.isTtsEnabled = true;
                    }
                    
                    const hasSeeds = gameState.challengeSeeds && Object.keys(gameState.challengeSeeds).length > 0;
                    
                    if (gameState.regionIndex === -1 || !gameState.difficulty || !hasSeeds) {
                         showModeSelection();
                    } else {
                        // Mengubah narasi ke konteks Lampung
                        setNarrativeText(`S.E.A.: Selamat datang kembali. Anda berada di Modul ${REGIONS[gameState.regionIndex].name}, tingkat ${gameState.difficulty}. Silakan lanjutkan sesi evaluasi Aksara Anda.`);
                        userInput.classList.remove('hidden');
                        actionButton.classList.remove('hidden');
                        hintButton.classList.remove('hidden');
                        nextChallenge();
                    }
                    
                } else {
                    resetGame(false); 
                    // Mengubah narasi ke konteks Lampung
                    setNarrativeText("S.E.A.: Selamat datang di Sistem Uji Kompetensi Bahasa Lampung. Silakan pilih Modul Uji untuk memulai sesi.");
                    showModeSelection();
                }
            } catch (e) {
                console.error("Gagal memuat data dari Local Storage:", e);
                resetGame(false);
                showModeSelection();
            }
            updateUI();
        }

        /**
         * Menyimpan status game ke Local Storage.
         */
        function saveGame() {
            localStorage.setItem('bahasaNusantaraGame', JSON.stringify(gameState));
        }

        /**
         * Memperbarui tampilan UI (skor, modul, kesulitan, TTS toggle).
         */
        function updateUI() {
            scoreDisplay.textContent = `${gameState.score} / ${gameState.targetScore}`;
            if (gameState.regionIndex !== -1) {
                const regionName = REGIONS[gameState.regionIndex].name;
                const difficultyText = gameState.difficulty ? ` (${gameState.difficulty})` : '';
                regionDisplay.textContent = regionName + difficultyText;
                hintButton.disabled = gameState.currentChallenge && gameState.currentChallenge.hintUsed;
            } else {
                regionDisplay.textContent = "Pilih Modul";
                hintButton.disabled = true;
            }
            
            ttsToggle.textContent = gameState.isTtsEnabled ? "🔈 Narasi Aktif" : "🔇 Narasi Mati";

            saveGame();
        }

        /**
         * Memulai game dari awal dan mereset Local Storage.
         */
        function resetGame(showPrompt = true) {
            window.speechSynthesis.cancel(); 

            gameState = {
                score: 0,
                regionIndex: -1,
                difficulty: null,
                challengeIndex: 0,
                targetScore: 50,
                currentChallenge: null,
                isTtsEnabled: gameState.isTtsEnabled || true,
                challengeSeeds: {},
                finalReviewText: null,
            };
            localStorage.removeItem('bahasaNusantaraGame');
            if (showPrompt) {
                // Mengubah narasi ke konteks Lampung
                setNarrativeText("S.E.A.: Sesi evaluasi telah direset. Silakan pilih Modul Uji Bahasa Lampung untuk memulai sesi baru.");
            }
            reviewArea.classList.add('hidden');
            showModeSelection();
            updateUI();
        }
        
        /**
         * Memeriksa apakah pemain siap untuk mengakhiri modul.
         */
        function checkRegionProgression() {
            if (gameState.score >= gameState.targetScore) {
                // Mengubah narasi ke konteks Lampung
                setNarrativeText(`S.E.A.: SELAMAT! Anda telah mencapai skor minimum ${gameState.score} Poin untuk Modul ${REGIONS[gameState.regionIndex].name} pada tingkat ${gameState.difficulty}. Sistem sedang menyusun Ringkasan Evaluasi Kinerja Aksara Anda...`);
                
                actionButton.classList.add('hidden');
                hintButton.classList.add('hidden');
                userInput.classList.add('hidden');
                challengePrompt.classList.add('hidden');
                
                generateFinalReview(); 
                
                return true;
            }
            reviewArea.classList.add('hidden');
            gameState.finalReviewText = null;
            downloadButton.disabled = false;

            return false;
        }

        /**
         * Memanggil AI untuk menghasilkan ringkasan pembelajaran dan feedback akhir.
         */
        async function generateFinalReview() {
            const currentRegion = REGIONS[gameState.regionIndex];
            // SISTEM INSTRUCTION FORMAL: Sistem Evaluasi Aksara (S.E.A.)
            const systemInstruction = `Anda adalah Sistem Evaluasi Aksara (S.E.A.). Tugas Anda adalah membuat Ringkasan Evaluasi Kinerja yang profesional dan komprehensif terkait kompetensi Bahasa Lampung. 
            Ringkasan ini harus berisi 3 bagian utama:
            1. Analisis Kinerja: Berikan analisis pencapaian ${gameState.score} poin di kategori ${currentRegion.name} (Kesulitan: ${gameState.difficulty}). Fokus pada Dialek, Aksara, Kosakata, atau Adat.
            2. Rekomendasi Peningkatan Kompetensi: Beri 3 saran strategis dan relevan untuk perbaikan kompetensi Bahasa Lampung, terkait langsung dengan materi ${currentRegion.name}. Saran harus aplikatif dalam konteks interaksi BUDAYA dan ADAT Lampung.
            3. Pernyataan Penutup: Berikan pernyataan penutup yang formal dan suportif (misalnya, mendorong pelestarian budaya).
            
            Format output harus berupa TEKS BIASA dan RAPI, tanpa karakter Markdown (*, #). Mulailah dengan Judul 'RINGKASAN EVALUASI KOMPETENSI BAHASA LAMPUNG' dan tambahkan baris kosong setelahnya.`;
            
            const userPrompt = `Siswa berhasil mencapai skor ${gameState.score} dari target ${gameState.targetScore} pada kesulitan ${gameState.difficulty} di Modul ${currentRegion.name} (Bahasa Lampung). Berikan Ringkasan Evaluasi Kinerja dan rekomendasi peningkatan kompetensi.`;
            
            setNarrativeText("S.E.A.: Memproses data kinerja Anda... Harap tunggu, Ringkasan Evaluasi Aksara sedang disusun.");

            const reviewText = await callGeminiAPI(systemInstruction, userPrompt);
            
            if (reviewText && !reviewText.startsWith("S.E.A.: Terjadi kesalahan")) {
                const cleanedReview = cleanText(reviewText);
                gameState.finalReviewText = cleanedReview;
                
                // Mengubah narasi ke konteks Lampung
                setNarrativeText(`S.E.A.: Sesi evaluasi Modul ${currentRegion.name} telah selesai. Berikut adalah Ringkasan Evaluasi Kinerja Bahasa Lampung Anda.`);
                narrativeText.innerHTML += `<br><br><span class="font-bold text-orange-400">--- RINGKASAN EVALUASI ---</span><br><pre class="whitespace-pre-wrap text-sm mt-2 p-3 bg-stone-700 rounded-lg text-gray-200" style="font-family: 'Merriweather', serif;">${cleanedReview}</pre>`;
                speakNarrative(`Ringkasan evaluasi telah siap. Anda dapat melihat detailnya di kotak narasi dan mengunduhnya.`);
                
                reviewMessage.textContent = `Ringkasan untuk Modul ${currentRegion.name} telah tersedia.`;
                reviewArea.classList.remove('hidden');

            } else {
                setNarrativeText("S.E.A.: Maaf, gagal menyusun Ringkasan Evaluasi. Silakan coba lagi.");
                reviewArea.classList.add('hidden');
            }
            updateUI();
        }

        /**
         * Mengunduh teks review sebagai file .txt.
         */
        function downloadReview() {
            if (!gameState.finalReviewText) {
                showMessageAlert("Ringkasan evaluasi belum selesai dimuat!", 'error');
                return;
            }

            downloadButton.disabled = true;
            downloadButton.textContent = "Sedang Mengunduh...";

            try {
                const regionName = REGIONS[gameState.regionIndex].key;
                const fileName = `Evaluasi_Kompetensi_Lampung_${regionName}_${gameState.difficulty}.txt`;
                
                const dateHeader = `Tanggal Evaluasi: ${new Date().toLocaleDateString('id-ID')}\n\n`;
                const content = dateHeader + gameState.finalReviewText;

                const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName;
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showMessageAlert("File Ringkasan berhasil diunduh!", 'success');
            } catch (error) {
                console.error("Gagal mengunduh file:", error);
                showMessageAlert("Gagal mengunduh file. Cek konsol.", 'error');
            } finally {
                downloadButton.textContent = "⬇️ Unduh Ringkasan Evaluasi (.txt)";
                downloadButton.disabled = false;
            }
        }

        /**
         * Menampilkan tantangan berikutnya (Melibatkan Gemini API untuk Narasi & Prompt).
         */
        async function nextChallenge() {
            if (checkRegionProgression() || gameState.regionIndex === -1) return;

            userInput.value = '';
            challengePrompt.classList.add('hidden');
            
            const currentRegion = REGIONS[gameState.regionIndex];
            const seeds = gameState.challengeSeeds[currentRegion.prompt_type]; 

            if (!seeds || seeds.length === 0) {
                // Mengubah narasi ke konteks Lampung
                setNarrativeText("S.E.A.: Data soal tidak tersedia. Mohon pilih tingkat kesulitan kembali atau Reset Uji Kompetensi.");
                return;
            }

            if (gameState.challengeIndex >= seeds.length) {
                gameState.challengeIndex = 0;
                setNarrativeText("S.E.A.: Semua soal dalam set ini telah dikerjakan. Memulai set soal Aksara dan Bahasa dari awal.");
            }

            const seedPrompt = seeds[gameState.challengeIndex];
            
            // SISTEM INSTRUCTION FORMAL: Sistem Evaluasi Aksara (S.E.A.)
            const systemInstruction = `Anda adalah Sistem Evaluasi Aksara (S.E.A.). Tugas Anda adalah memberikan narasi profesional dan instruksi jelas. SELALU kaitkan narasi dan tantangan dengan konteks BUDAYA LAMPUNG, ADAT, atau komunikasi sehari-hari/formal. Gunakan bahasa yang LUGAS dan FORMAL. Berikan narasi pembuka untuk Modul ${currentRegion.name} dengan kesulitan ${gameState.difficulty} dalam Bahasa Indonesia baku, lalu berikan tantangan spesifik yang tertutup kurung siku [Tantangan di sini]. Pastikan output narasi dan tantangan TIDAK mengandung karakter Markdown seperti *, **, atau #.`;
            const userPrompt = `Berikan narasi profesional dan soal evaluasi untuk materi Bahasa Lampung ${currentRegion.name}. Soal intinya adalah: ${seedPrompt}.`;

            setNarrativeText(`S.E.A.: Memuat Soal ke-${gameState.challengeIndex + 1} di Modul ${currentRegion.name}...`);

            const fullResponse = await callGeminiAPI(systemInstruction, userPrompt);
            
            const [narrativePart, promptPart] = fullResponse.split(/\[(.*?)\]/s).filter(Boolean);
            
            gameState.currentChallenge = { seed: seedPrompt, hintUsed: false };
            
            setNarrativeText(cleanText(narrativePart || fullResponse.trim()));
            challengePrompt.textContent = cleanText(promptPart ? `[${promptPart.trim()}]` : seedPrompt);
            challengePrompt.classList.remove('hidden');
            
            updateUI(); 
        }

        /**
         * Meminta petunjuk dari S.E.A. (Melibatkan Gemini API).
         */
        async function requestHint() {
            if (gameState.currentChallenge.hintUsed) {
                showMessageAlert("S.E.A.: Anda sudah menggunakan petunjuk untuk soal ini.", 'error');
                speakNarrative("Anda sudah menggunakan petunjuk untuk soal ini.");
                return;
            }
            if (gameState.regionIndex === -1) return;

            const currentRegion = REGIONS[gameState.regionIndex];
            const promptText = challengePrompt.textContent;
            const userInputText = userInput.value.trim();

            hintButton.disabled = true;
            hintButton.textContent = "Memproses Petunjuk...";
            
            // SISTEM INSTRUCTION FORMAL: Sistem Pendukung Akademik Bahasa Lampung
            const systemInstruction = `Anda adalah Sistem Pendukung Akademik Bahasa Lampung. Tugas Anda adalah memberikan petunjuk yang sangat halus dan **analitis** (maksimal 2 kalimat) untuk membantu siswa memecahkan masalah Bahasa Lampung ${currentRegion.name} pada tingkat kesulitan ${gameState.difficulty}. SELALU berikan petunjuk dalam konteks aturan Dialek A/O, Aksara Lampung, atau tata bahasa Lampung. Gunakan bahasa yang LUGAS dan FORMAL. Jangan berikan jawabannya secara langsung. Pastikan output TIDAK mengandung karakter Markdown seperti *, **, atau #.`;
            const userPrompt = `Soal saat ini: "${promptText}". Modul: ${currentRegion.name} (Bahasa Lampung). Jawaban sementara siswa: "${userInputText || 'belum ada'}" (gunakan ini untuk membuat petunjuk lebih relevan). Berikan petunjuk yang halus.`;

            const hintText = await callGeminiAPI(systemInstruction, userPrompt);
            
            hintButton.textContent = "Berikan Petunjuk (-5 Poin)";
            
            if (hintText.startsWith("S.E.A.:")) {
                setNarrativeText(cleanText(hintText));
            } else {
                const cleanedHint = cleanText(hintText);
                // Mengubah narasi ke konteks Lampung
                narrativeText.innerHTML = `<span class="text-orange-400 font-bold">PETUNJUK S.E.A.:</span> ${cleanedHint}`;
                speakNarrative(`Petunjuk Sistem Evaluasi Aksara: ${cleanedHint}`);
                
                gameState.currentChallenge.hintUsed = true;
                showMessageAlert("Petunjuk Diberikan! Pengurangan 5 poin jika jawaban benar.", 'error');
            }
            updateUI();
        }


        /**
         * Memproses input pengguna (Melibatkan Gemini API untuk Evaluasi dan Feedback).
         */
        async function handleAction() {
            if (gameState.regionIndex === -1) return;

            const input = userInput.value.trim();
            
            if (!input) {
                showMessageAlert("Kolom jawaban tidak boleh kosong.", 'error');
                speakNarrative("Kolom jawaban tidak boleh kosong.");
                return;
            }
            
            actionButton.disabled = true;
            actionButton.textContent = "Menilai Jawaban...";
            
            const currentRegion = REGIONS[gameState.regionIndex];
            const challengeSeed = gameState.currentChallenge.seed;
            const promptText = challengePrompt.textContent;
            const basePoints = DIFFICULTIES[gameState.difficulty].basePoints;

            // 1. Tentukan Skema JSON untuk Penilaian Terstruktur
            const evaluationSchema = {
                type: "OBJECT",
                properties: {
                    "isCorrect": { "type": "BOOLEAN" },
                    "pointsAwarded": { "type": "INTEGER" },
                    "feedbackNarrative": { "type": "STRING" }
                },
                required: ["isCorrect", "pointsAwarded", "feedbackNarrative"]
            };

            // 2. Tentukan System Instruction dan Prompt untuk Evaluasi
            // SISTEM INSTRUCTION FORMAL: Sistem Evaluasi Aksara (S.E.A.) Bahasa Lampung
            const systemInstruction = `Anda adalah Sistem Evaluasi Aksara (S.E.A.). Tugas Anda adalah mengevaluasi jawaban siswa secara ketat untuk materi Bahasa Lampung ${currentRegion.name} dengan kesulitan ${gameState.difficulty}. SELALU gunakan contoh dan konteks DIALEK LAMPUNG (A/O), AKSARA, atau ADAT dalam umpan balik naratif Anda.
            Gunakan bahasa yang LUGAS dan FORMAL.
            Jika jawaban benar, berikan APRESIASI PROFESIONAL dan konfirmasi kompetensi Bahasa Lampung (maksimal 3 kalimat). 
            Jika jawaban salah, berikan UMPAN BALIK KONSTRUKTIF yang memotivasi siswa untuk melakukan analisis ulang pada aturan Bahasa Lampung (maksimal 3 kalimat). 
            Jawab HANYA dengan JSON. Isi 'pointsAwarded' dengan ${basePoints} jika benar, dan 0 jika salah. Pastikan output feedbackNarrative TIDAK mengandung karakter Markdown seperti *, **, atau #.`;

            const userPrompt = `Modul: ${currentRegion.name} (Bahasa Lampung). Soal Inti: "${challengeSeed}". Teks Soal yang Dilihat Siswa: "${promptText}". Jawaban Siswa: "${input}". Evaluasi jawaban ini dan berikan umpan balik naratif yang formal.`;

            // 3. Panggil Gemini API
            const result = await callGeminiAPI(systemInstruction, userPrompt, evaluationSchema);
            
            actionButton.textContent = "Kirim Jawaban";
            setLoading(false);

            if (result && typeof result.isCorrect !== 'undefined') {
                const { isCorrect, pointsAwarded, feedbackNarrative } = result;
                
                const cleanedFeedback = cleanText(feedbackNarrative);

                if (isCorrect) {
                    let finalPoints = basePoints;
                    let narrativeAddition = "";

                    if (gameState.currentChallenge && gameState.currentChallenge.hintUsed) {
                        finalPoints = Math.max(0, basePoints - 5);
                        narrativeAddition = ` (Skor disesuaikan -5 poin karena penggunaan petunjuk. Skor akhir: ${finalPoints})`;
                    }

                    gameState.score += finalPoints;
                    
                    showMessageAlert(`+${finalPoints} Poin! Jawaban Tepat!`, 'success');
                    // Mengubah narasi ke konteks Lampung
                    const finalNarrative = `[Tepat] ${cleanedFeedback}${narrativeAddition} Anda memperoleh ${finalPoints} Poin Kompetensi Aksara.`;
                    setNarrativeText(finalNarrative);
                    
                    gameState.challengeIndex++;
                    updateUI();
                    setTimeout(nextChallenge, 2000);

                } else {
                    showMessageAlert("Jawaban Belum Tepat!", 'error');
                    // Mengubah narasi ke konteks Lampung
                    const errorNarrative = `[Tidak Tepat] ${cleanedFeedback} Mohon lakukan analisis kembali aturan Dialek dan Aksara Lampung.`;
                    setNarrativeText(errorNarrative);
                    actionButton.disabled = false;
                    updateUI();
                }
            } else {
                 setNarrativeText("S.E.A.: Maaf, terjadi gangguan koneksi. Mohon coba kembali.");
                 actionButton.disabled = false;
                 updateUI();
            }
        }


        // --- 3. Inisiasi Game ---

        // Panggil initGame saat halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', initGame);

        // Tambahkan event listener untuk tombol 'Enter' pada input
        userInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !actionButton.disabled && !actionButton.classList.contains('hidden')) {
                handleAction();
            }
        });
    </script>

</body>
</html>
