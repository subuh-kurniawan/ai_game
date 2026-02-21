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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Uji Kompetensi Bahasa Indonesia</title>
    <!-- Memuat Tailwind CSS CDN untuk styling modern dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Menggunakan font Inter untuk tampilan profesional dan Merriweather untuk konten narasi/analisis */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Merriweather:wght@400;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b); /* Dark blue/slate background */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        #game-container {
            background-color: #1f2d3d; /* Dark slate background */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            max-width: 800px;
            width: 100%;
            border-radius: 1.5rem;
            overflow: hidden;
            border: 5px solid #0891b2; /* Cyan/Blue professional border */
        }

        /* Styling untuk kotak narasi/analisis */
        #narrative-box {
            background-color: #2c3a4d; /* Slightly lighter inner box */
            font-family: 'Merriweather', serif;
            font-size: 1.05rem;
            line-height: 1.7;
            min-height: 180px;
            padding: 1.5rem;
            color: #d1d5db; /* Light gray text */
            border-bottom: 3px solid #06b6d4;
            position: relative;
        }

        /* S.E.D. Avatar */
        #gm-avatar {
            font-size: 2.5rem;
            margin-right: 1rem;
            text-shadow: 0 0 8px #38bdf8;
        }

        /* Styling untuk kotak tantangan/input */
        #challenge-area {
            background-color: #1f2d3d;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
        }

        .challenge-prompt {
            font-size: 1.15rem;
            font-weight: 600;
            color: #22c55e; /* Green accent */
            text-align: center;
            padding: 0.75rem;
            border: 2px solid #4ade80;
            border-radius: 0.75rem;
            background-color: #111827;
        }

        .game-button {
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px #0d9488; /* Darker shadow for 3D effect */
        }
        .game-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px #0d9488;
        }

        .selection-button {
            background-color: #0ea5e9; /* Blue accent */
            color: white;
            font-weight: bold;
            padding: 1rem;
            border-radius: 0.75rem;
            transition: all 0.2s;
            box-shadow: 0 4px #0369a1;
        }
        .selection-button:hover {
            background-color: #0284c7;
        }
        .selection-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px #0369a1;
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
            border-top: 4px solid #06b6d4; /* Cyan loading color */
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
            color: #ef4444; /* Merah */
            border: 1px solid #b91c1c;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        #reset-game-button:hover {
            background-color: #440000;
        }
    </style>
</head>
<body>

    <div id="game-container">
        <!-- HEADER: Score and Module -->
        <div class="p-4 bg-cyan-700 text-white flex justify-between items-center rounded-t-xl font-bold">
            <span id="game-title" class="text-xl">Sistem Uji Kompetensi Bahasa Indonesia</span>
            <div class="flex space-x-4 text-sm items-center">
                <!-- TTS Toggle Button -->
                <button id="tts-toggle" class="bg-gray-800 text-white p-1 px-3 rounded-full shadow-md text-xs hover:bg-gray-700 transition" onclick="toggleTts()">
                    🔈 Narasi Aktif
                </button>
                <span class="p-1 px-3 bg-cyan-800 text-white rounded-full shadow-md">Skor: <span id="score-display">0</span></span>
                <span class="p-1 px-3 bg-blue-700 text-white rounded-full shadow-md">Modul Uji: <span id="region-display">Memuat...</span></span>
            </div>
        </div>

        <!-- NARATIVE AREA (S.E.D. - Sistem Evaluasi Diksi) -->
        <div id="narrative-box">
            <div class="flex items-start">
                <span id="gm-avatar" class="text-blue-400">🎓</span>
                <div>
                    <span class="font-bold text-lg text-blue-500">Sistem Evaluasi Diksi (S.E.D.):</span>
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
            <input type="text" id="user-input" class="p-3 border-2 border-teal-500 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:ring-4 focus:ring-teal-400 focus:border-teal-600 transition duration-150" placeholder="Masukkan jawaban Anda di sini...">

            <!-- Action Buttons: Hint and Submit -->
            <div class="flex gap-4">
                <button id="hint-button" class="game-button bg-yellow-600 text-gray-900 font-bold py-3 rounded-lg hover:bg-yellow-700 disabled:opacity-50 flex-1" onclick="requestHint()">Berikan Petunjuk (-5 Poin)</button>
                <button id="action-button" class="game-button bg-teal-600 text-white font-bold py-3 rounded-lg hover:bg-teal-700 disabled:opacity-50 flex-1" onclick="handleAction()">Kirim Jawaban</button>
            </div>
            
            <!-- Review Area -->
            <div id="review-area" class="mt-4 p-4 bg-gray-800 rounded-xl hidden flex-col items-center">
                <p id="review-message" class="text-blue-400 mb-3 font-semibold text-center"></p>
                <button id="download-button" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-150" onclick="downloadReview()">
                    ⬇️ Unduh Ringkasan Evaluasi (.txt)
                </button>
            </div>

            <!-- Loading Indicator -->
            <div id="loading-indicator" class="flex justify-center items-center gap-2 py-2 hidden">
                <div class="loading-spinner"></div>
                <span class="text-gray-400 text-sm">Sistem Evaluasi Diksi sedang memproses...</span>
            </div>

            <!-- Reset Button -->
            <button id="reset-game-button" class="text-xs text-gray-500 hover:text-red-400 mt-2" onclick="resetGame()">Reset Uji Kompetensi (Hapus Data)</button>
        </div>
    </div>

    <script>
        // --- 0. Konfigurasi dan Variabel Global API ---
        const apiKey = "<?php echo $apiKey; ?>"; // Dibiarkan kosong, akan diisi oleh runtime
         const md = "<?php echo $model; ?>";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey}`;

        // DOM Elements
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

        // Daftar Modul Uji Kompetensi (Formal)
        const REGIONS = [
            { name: "Sintaksis & Tata Kalimat", key: "grammar", prompt_type: "grammar_rearrange" },
            { name: "Analisis Majas & Struktur Puisi", key: "poetry", prompt_type: "poetry_creation" },
            { name: "Kesesuaian Ejaan (PUEBI/KBBI)", key: "spelling", prompt_type: "spelling_fix" },
            { name: "Interpretasi Peribahasa & Ungkapan", key: "proverb", prompt_type: "proverb_meaning" },
            { name: "Analisis Unsur Intrinsik Sastra", key: "literature", prompt_type: "literature_quiz" },
        ];

        // Tingkat Kesulitan (Formal)
        const DIFFICULTIES = {
            "Dasar": { targetMultiplier: 1.0, basePoints: 20, description: "Tingkat dasar, menguji pemahaman konsep fundamental. Target 50 poin." },
            "Menengah": { targetMultiplier: 1.5, basePoints: 25, description: "Tingkat menengah, melibatkan penalaran dan penerapan aturan. Target 75 poin." },
            "Lanjutan": { targetMultiplier: 2.0, basePoints: 30, description: "Tingkat lanjutan, menuntut analisis dan sintesis mendalam. Target 100 poin." }
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
            // Memastikan awalan yang bersifat struktural (nama sistem) tidak dibacakan.
            cleanedText = cleanedText
                .replace(/^Sistem Evaluasi Diksi \(S\.E\.D\.\):\s*/i, '') // Hapus awalan panjang
                .replace(/^S\.E\.D\.:\s*/i, '') // Hapus awalan pendek
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
                
                // KOREKSI UTAMA: Mengaktifkan input dan tombol hanya jika
                // mode sudah dipilih DAN kita tidak dalam mode seleksi/review akhir.
                const isInChallengeMode = gameState.regionIndex !== -1 && 
                                           selectionArea.classList.contains('hidden') && 
                                           reviewArea.classList.contains('hidden');

                if (isInChallengeMode) {
                    actionButton.disabled = false;
                    hintButton.disabled = false;
                    userInput.disabled = false;
                    userInput.focus(); // Pastikan fokus kembali ke input
                } else {
                    // Jika dalam mode review atau seleksi, pastikan input dinonaktifkan
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
                        return jsonSchema ? { isCorrect: false, pointsAwarded: 0, feedbackNarrative: "S.E.D.: Maaf, terjadi gangguan koneksi. Mohon coba kembali." } : "S.E.D.: Terjadi kesalahan koneksi, silakan coba ulang.";
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

            // SISTEM INSTRUCTION FORMAL: Sistem Pembuat Soal Akademik
            const systemInstruction = `Anda adalah Sistem Pembuat Soal Akademik. Tugas Anda adalah menyusun 5 tantangan akademik yang unik dan orisinal untuk setiap kategori bahasa Indonesia yang diberikan, sesuai dengan tingkat kesulitan ${gameState.difficulty}. Soal dan contoh harus selalu mengambil konteks dari lingkup ACADEMIC dan PROFESIONAL siswa SMA/SMK (pelajaran, tugas akhir, pidato formal, korespondensi). Jawab HANYA dengan JSON.
            - Untuk grammar_rearrange, berikan elemen kata acak yang harus disusun menjadi kalimat baku yang kompleks.
            - Untuk spelling_fix, berikan kalimat yang mengandung 1-2 kesalahan ejaan PUEBI/KBBI.
            - Untuk poetry_creation, berikan instruksi analitis singkat terkait gaya bahasa atau struktur kalimat.
            - Untuk proverb_meaning, berikan peribahasa/ungkapan formal yang harus diinterpretasikan maknanya dalam konteks modern.
            - Untuk literature_quiz, berikan pertanyaan faktual atau analisis tentang unsur intrinsik teks.
            - JANGAN BERIKAN JAWABAN.
            - Format output harus persis sesuai skema JSON yang diberikan.`;
            
            const regionNames = REGIONS.map(r => `${r.name} (${r.key})`).join(', ');
            const userPrompt = `Buatkan 5 seed tantangan untuk setiap kategori ini: ${regionNames}. Kesulitan: ${gameState.difficulty}.`;

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
            
            setNarrativeText("S.E.D.: Selamat datang di Sistem Uji Kompetensi Bahasa Indonesia. Silakan pilih Modul Uji yang akan Anda kerjakan.");
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
            setNarrativeText(`S.E.D.: Anda memilih Modul Uji ${selectedRegion}. Selanjutnya, tentukan Tingkat Kesulitan yang Anda inginkan:`);
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
            setNarrativeText("S.E.D.: Sistem sedang memproses perumusan soal akademik. Harap tunggu sebentar, kami sedang menyiapkan set tantangan baru sesuai lingkup akademis dan profesional...");
            const success = await generateChallengeSeeds();
            
            if (!success) {
                setNarrativeText("S.E.D.: Gagal menghasilkan set soal baru. Mohon lakukan reset uji kompetensi dan periksa koneksi internet Anda.");
                showDifficultySelection(gameState.regionIndex);
                return;
            }
            
            // 2. Lanjutkan Game
            selectionArea.classList.add('hidden');
            userInput.classList.remove('hidden');
            actionButton.classList.remove('hidden');
            hintButton.classList.remove('hidden');
            
            setNarrativeText(`S.E.D.: Set soal ${difficultyKey} telah siap. Sesi Uji Kompetensi Modul ${REGIONS[gameState.regionIndex].name} dimulai.`);

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
                        setNarrativeText(`S.E.D.: Selamat datang kembali. Anda berada di Modul ${REGIONS[gameState.regionIndex].name}, tingkat ${gameState.difficulty}. Silakan lanjutkan sesi evaluasi Anda.`);
                        userInput.classList.remove('hidden');
                        actionButton.classList.remove('hidden');
                        hintButton.classList.remove('hidden');
                        nextChallenge();
                    }
                    
                } else {
                    resetGame(false); 
                    setNarrativeText("S.E.D.: Selamat datang di Sistem Uji Kompetensi Bahasa Indonesia. Silakan pilih Modul Uji untuk memulai sesi.");
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
                setNarrativeText("S.E.D.: Sesi evaluasi telah direset. Silakan pilih Modul Uji untuk memulai sesi baru.");
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
                setNarrativeText(`S.E.D.: SELAMAT! Anda telah mencapai skor minimum ${gameState.score} Poin untuk Modul ${REGIONS[gameState.regionIndex].name} pada tingkat ${gameState.difficulty}. Sistem sedang menyusun Ringkasan Evaluasi Kinerja Anda...`);
                
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
            // SISTEM INSTRUCTION FORMAL: Sistem Evaluasi Diksi (S.E.D.)
            const systemInstruction = `Anda adalah Sistem Evaluasi Diksi (S.E.D.). Tugas Anda adalah membuat Ringkasan Evaluasi Kinerja yang profesional dan komprehensif untuk siswa SMA/SMK. 
            Ringkasan ini harus berisi 3 bagian utama:
            1. Analisis Kinerja: Berikan analisis pencapaian ${gameState.score} poin di kategori ${currentRegion.name} (Kesulitan: ${gameState.difficulty}).
            2. Rekomendasi Peningkatan Kompetensi: Beri 3 saran strategis dan relevan untuk perbaikan kompetensi belajar, terkait langsung dengan materi ${currentRegion.name}. Saran harus aplikatif dalam konteks akademis dan profesional siswa.
            3. Pernyataan Penutup: Berikan pernyataan penutup yang formal dan suportif.
            
            Format output harus berupa TEKS BIASA dan RAPI, tanpa karakter Markdown (*, #). Mulailah dengan Judul 'RINGKASAN EVALUASI KOMPETENSI' dan tambahkan baris kosong setelahnya.`;
            
            const userPrompt = `Siswa berhasil mencapai skor ${gameState.score} dari target ${gameState.targetScore} pada kesulitan ${gameState.difficulty} di Modul ${currentRegion.name}. Berikan Ringkasan Evaluasi Kinerja dan rekomendasi peningkatan kompetensi.`;
            
            setNarrativeText("S.E.D.: Memproses data kinerja Anda... Harap tunggu, Ringkasan Evaluasi sedang disusun.");

            const reviewText = await callGeminiAPI(systemInstruction, userPrompt);
            
            if (reviewText && !reviewText.startsWith("S.E.D.: Terjadi kesalahan")) {
                const cleanedReview = cleanText(reviewText);
                gameState.finalReviewText = cleanedReview;
                
                setNarrativeText(`S.E.D.: Sesi evaluasi Anda di Modul ${currentRegion.name} telah selesai. Berikut adalah Ringkasan Evaluasi Kinerja Anda.`);
                narrativeText.innerHTML += `<br><br><span class="font-bold text-blue-500">--- RINGKASAN EVALUASI ---</span><br><pre class="whitespace-pre-wrap text-sm mt-2 p-3 bg-gray-700 rounded-lg text-gray-200" style="font-family: 'Merriweather', serif;">${cleanedReview}</pre>`;
                speakNarrative(`Ringkasan evaluasi telah siap. Anda dapat melihat detailnya di kotak narasi dan mengunduhnya.`);
                
                reviewMessage.textContent = `Ringkasan untuk Modul ${currentRegion.name} telah tersedia.`;
                reviewArea.classList.remove('hidden');

            } else {
                setNarrativeText("S.E.D.: Maaf, gagal menyusun Ringkasan Evaluasi. Silakan coba lagi.");
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
                const fileName = `Evaluasi_Kompetensi_${regionName}_${gameState.difficulty}.txt`;
                
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
                setNarrativeText("S.E.D.: Data soal tidak tersedia. Mohon pilih tingkat kesulitan kembali atau Reset Uji Kompetensi.");
                return;
            }

            if (gameState.challengeIndex >= seeds.length) {
                gameState.challengeIndex = 0;
                setNarrativeText("S.E.D.: Semua soal dalam set ini telah dikerjakan. Memulai set soal dari awal.");
            }

            const seedPrompt = seeds[gameState.challengeIndex];
            
            // SISTEM INSTRUCTION FORMAL: Sistem Evaluasi Diksi (S.E.D.)
            const systemInstruction = `Anda adalah Sistem Evaluasi Diksi (S.E.D.). Tugas Anda adalah memberikan narasi profesional dan instruksi jelas. SELALU kaitkan narasi dan tantangan dengan konteks ACADEMIC dan PROFESIONAL siswa SMA/SMK. Gunakan bahasa yang LUGAS dan FORMAL. Berikan narasi pembuka untuk Modul ${currentRegion.name} dengan kesulitan ${gameState.difficulty} dalam Bahasa Indonesia baku, lalu berikan tantangan spesifik yang tertutup kurung siku [Tantangan di sini]. Pastikan output narasi dan tantangan TIDAK mengandung karakter Markdown seperti *, **, atau #.`;
            const userPrompt = `Berikan narasi profesional dan soal evaluasi untuk materi ${currentRegion.name}. Soal intinya adalah: ${seedPrompt}.`;

            setNarrativeText(`S.E.D.: Memuat Soal ke-${gameState.challengeIndex + 1} di Modul ${currentRegion.name}...`);

            const fullResponse = await callGeminiAPI(systemInstruction, userPrompt);
            
            const [narrativePart, promptPart] = fullResponse.split(/\[(.*?)\]/s).filter(Boolean);
            
            gameState.currentChallenge = { seed: seedPrompt, hintUsed: false };
            
            setNarrativeText(cleanText(narrativePart || fullResponse.trim()));
            challengePrompt.textContent = cleanText(promptPart ? `[${promptPart.trim()}]` : seedPrompt);
            challengePrompt.classList.remove('hidden');
            
            updateUI(); 
        }

        /**
         * Meminta petunjuk dari S.E.D. (Melibatkan Gemini API).
         */
        async function requestHint() {
            if (gameState.currentChallenge.hintUsed) {
                showMessageAlert("S.E.D.: Anda sudah menggunakan petunjuk untuk soal ini.", 'error');
                speakNarrative("Anda sudah menggunakan petunjuk untuk soal ini.");
                return;
            }
            if (gameState.regionIndex === -1) return;

            const currentRegion = REGIONS[gameState.regionIndex];
            const promptText = challengePrompt.textContent;
            const userInputText = userInput.value.trim();

            hintButton.disabled = true;
            hintButton.textContent = "Memproses Petunjuk...";
            
            // SISTEM INSTRUCTION FORMAL: Sistem Pendukung Akademik
            const systemInstruction = `Anda adalah Sistem Pendukung Akademik. Tugas Anda adalah memberikan petunjuk yang sangat halus dan **analitis** (maksimal 2 kalimat) untuk membantu siswa memecahkan masalah ${currentRegion.name} pada tingkat kesulitan ${gameState.difficulty}. SELALU berikan petunjuk dalam konteks ACADEMIC dan PROFESIONAL siswa. Gunakan bahasa yang LUGAS dan FORMAL. Jangan berikan jawabannya secara langsung. Pastikan output TIDAK mengandung karakter Markdown seperti *, **, atau #.`;
            const userPrompt = `Soal saat ini: "${promptText}". Modul: ${currentRegion.name}. Jawaban sementara siswa: "${userInputText || 'belum ada'}" (gunakan ini untuk membuat petunjuk lebih relevan). Berikan petunjuk yang halus.`;

            const hintText = await callGeminiAPI(systemInstruction, userPrompt);
            
            hintButton.textContent = "Berikan Petunjuk (-5 Poin)";
            
            if (hintText.startsWith("S.E.D.:")) {
                setNarrativeText(cleanText(hintText));
            } else {
                const cleanedHint = cleanText(hintText);
                narrativeText.innerHTML = `<span class="text-blue-400 font-bold">PETUNJUK S.E.D.:</span> ${cleanedHint}`;
                speakNarrative(`Petunjuk Sistem Evaluasi Diksi: ${cleanedHint}`);
                
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
            // SISTEM INSTRUCTION FORMAL: Sistem Evaluasi Diksi (S.E.D.)
            const systemInstruction = `Anda adalah Sistem Evaluasi Diksi (S.E.D.). Tugas Anda adalah mengevaluasi jawaban siswa SMA/SMK secara ketat untuk materi ${currentRegion.name} dengan kesulitan ${gameState.difficulty}. SELALU gunakan contoh dan konteks ACADEMIC dan PROFESIONAL dalam umpan balik naratif Anda.
            Gunakan bahasa yang LUGAS dan FORMAL.
            Jika jawaban benar, berikan APRESIASI PROFESIONAL dan konfirmasi kompetensi (maksimal 3 kalimat). 
            Jika jawaban salah, berikan UMPAN BALIK KONSTRUKTIF yang memotivasi siswa untuk melakukan analisis ulang (maksimal 3 kalimat). 
            Jawab HANYA dengan JSON. Isi 'pointsAwarded' dengan ${basePoints} jika benar, dan 0 jika salah. Pastikan output feedbackNarrative TIDAK mengandung karakter Markdown seperti *, **, atau #.`;

            const userPrompt = `Modul: ${currentRegion.name}. Soal Inti: "${challengeSeed}". Teks Soal yang Dilihat Siswa: "${promptText}". Jawaban Siswa: "${input}". Evaluasi jawaban ini dan berikan umpan balik naratif yang formal.`;

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
                    const finalNarrative = `[Tepat] ${cleanedFeedback}${narrativeAddition} Anda memperoleh ${finalPoints} Poin Kompetensi.`;
                    setNarrativeText(finalNarrative);
                    
                    gameState.challengeIndex++;
                    updateUI();
                    setTimeout(nextChallenge, 2000);

                } else {
                    showMessageAlert("Jawaban Belum Tepat!", 'error');
                    const errorNarrative = `[Tidak Tepat] ${cleanedFeedback} Mohon lakukan analisis kembali.`;
                    setNarrativeText(errorNarrative);
                    actionButton.disabled = false;
                    updateUI();
                }
            } else {
                 setNarrativeText("S.E.D.: Maaf, terjadi gangguan koneksi. Mohon coba kembali.");
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
