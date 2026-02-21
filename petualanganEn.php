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
    <title>Menguasai Diksi Bahasa Inggris</title>
    <!-- Load Tailwind CSS CDN for modern, responsive styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Using Inter font for a modern look and Merriweather for narration */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Merriweather:wght=400;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #121c2c, #2b3952); /* Latar belakang gelap, misterius */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        #game-container {
            background-color: #1f2d3d; /* Latar belakang batu tulis gelap */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            max-width: 800px;
            width: 100%;
            border-radius: 1.5rem;
            overflow: hidden;
            border: 5px solid #d97706; /* Border emas */
        }

        /* Styling untuk kotak narasi */
        #narrative-box {
            background-color: #2c3a4d; /* Kotak dalam sedikit lebih terang */
            font-family: 'Merriweather', serif;
            font-size: 1.1rem;
            line-height: 1.7;
            min-height: 180px;
            padding: 1.5rem;
            color: #d1d5db; /* Teks abu-abu terang */
            border-bottom: 3px solid #f59e0b;
            position: relative;
        }

        /* GM Avatar */
        #gm-avatar {
            font-size: 2.5rem;
            margin-right: 1rem;
            text-shadow: 0 0 8px #fcd34d;
        }

        /* Styling untuk area tantangan/input */
        #challenge-area {
            background-color: #1f2d3d;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative; 
        }

        .challenge-prompt {
            font-size: 1.25rem;
            font-weight: 600;
            color: #10b981; /* Teks hijau cerah */
            text-align: center;
            padding: 0.75rem;
            border: 2px solid #34d399;
            border-radius: 0.75rem;
            background-color: #111827;
        }

        .game-button {
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px #047857; /* Bayangan gelap untuk efek 3D */
        }
        .game-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px #047857;
        }

        .selection-button {
            background-color: #d97706;
            color: #1f2d3d;
            font-weight: bold;
            padding: 1rem;
            border-radius: 0.75rem;
            transition: all 0.2s;
            box-shadow: 0 4px #b45309;
        }
        .selection-button:hover {
            background-color: #b45309;
            color: white;
        }
        .selection-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px #b45309;
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
            border-top: 4px solid #f59e0b;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom style for Reset Button */
        #reset-game-button {
            color: #ef4444; 
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
        <!-- HEADER: Skor dan Modul -->
        <div class="p-4 bg-yellow-600 text-gray-900 flex justify-between items-center rounded-t-xl font-bold">
            <span id="game-title" class="text-xl">Menguasai Diksi Bahasa Inggris</span>
            <div class="flex space-x-4 text-sm items-center">
                <!-- Tombol Toggle TTS -->
                <button id="tts-toggle" class="bg-gray-800 text-white p-1 px-3 rounded-full shadow-md text-xs hover:bg-gray-700 transition" onclick="toggleTts()">
                    🔈 Narasi Aktif
                </button>
                <span class="p-1 px-3 bg-yellow-700 text-white rounded-full shadow-md">Skor: <span id="score-display">0</span></span>
                <span class="p-1 px-3 bg-green-700 text-white rounded-full shadow-md">Modul: <span id="region-display">Memuat...</span></span>
            </div>
        </div>

        <!-- AREA NARASI (AI Master Game) -->
        <div id="narrative-box">
            <div class="flex items-start">
                <span id="gm-avatar" class="text-yellow-400">🧠</span>
                <div>
                    <span class="font-bold text-lg text-yellow-500">AI Master Game:</span>
                    <p id="narrative-text" class="text-gray-300 mt-2">Memuat petualangan...</p>
                </div>
            </div>
        </div>

        <!-- AREA TANTANGAN -->
        <div id="challenge-area">
            
            <!-- Peringatan Pesan (Tersembunyi secara default) -->
            <div id="message-alert" class="bg-red-500 text-white hidden"></div>

            <!-- Prompt Tantangan -->
            <div id="challenge-prompt" class="challenge-prompt hidden"></div>
            
            <!-- Area Pemilihan Mode/Kesulitan -->
            <div id="selection-area" class="grid grid-cols-3 gap-4 hidden">
                <!-- Tombol akan dihasilkan di sini (Mode atau Kesulitan) -->
            </div>

            <!-- Input Pengguna -->
            <input type="text" id="user-input" class="p-3 border-2 border-green-500 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:ring-4 focus:ring-green-400 focus:border-green-600 transition duration-150" placeholder="Ketik jawaban Anda di sini...">

            <!-- Tombol Aksi: Petunjuk dan Kirim -->
            <div class="flex gap-4">
                <button id="hint-button" class="game-button bg-yellow-600 text-gray-900 font-bold py-3 rounded-lg hover:bg-yellow-700 disabled:opacity-50 flex-1" onclick="requestHint()">Dapatkan Petunjuk (-5 Pts)</button>
                <button id="action-button" class="game-button bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 disabled:opacity-50 flex-1" onclick="handleAction()">Kirim Jawaban</button>
            </div>
            
            <!-- Area Tinjauan Baru -->
            <div id="review-area" class="mt-4 p-4 bg-gray-800 rounded-xl hidden flex-col items-center">
                <p id="review-message" class="text-yellow-400 mb-3 font-semibold text-center"></p>
                <button id="download-button" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-150" onclick="downloadReview()">
                    ⬇️ Unduh Ringkasan Pembelajaran (.txt)
                </button>
            </div>

            <!-- Indikator Memuat -->
            <div id="loading-indicator" class="flex justify-center items-center gap-2 py-2 hidden">
                <div class="loading-spinner"></div>
                <span class="text-gray-400 text-sm">AI Master Game sedang berpikir...</span>
            </div>

            <!-- Tombol Mulai Ulang -->
            <button id="reset-game-button" class="text-xs text-gray-500 hover:text-red-400 mt-2" onclick="resetGame()">Mulai Ulang Game (Hapus Progres)</button>
        </div>
    </div>

    <script>
        // --- 0. Konfigurasi API dan Variabel Global ---
       const apiKey = "<?php echo $apiKey; ?>"; // Dibiarkan kosong, akan diisi oleh runtime
         const md = "<?php echo $model; ?>";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey}`;
        // Elemen DOM
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

        // Status Game (Dimuat dari Local Storage)
        let gameState = {
            score: 0,
            regionIndex: -1, // -1 berarti belum memilih mode
            difficulty: null, // Menyimpan tingkat kesulitan (Mudah, Sedang, Sulit)
            challengeIndex: 0,
            targetScore: 50,
            currentChallenge: null, // Objek tantangan saat ini: { seed: string, hintUsed: boolean }
            isTtsEnabled: true, // Status TTS
            challengeSeeds: {}, // Menyimpan benih AI yang dihasilkan secara dinamis
            finalReviewText: null, // Menyimpan teks tinjauan akhir
        };

        // Daftar Modul/Mode Game - Dibuat lebih realistis dan menarik
        const REGIONS = [
            { name: "Grammar: Aplikasi Tenses ⚙️", key: "grammar", prompt_type: "grammar_rearrange" },
            { name: "Gaya & Ekspresi Keseharian 💬", key: "figurative", prompt_type: "poetry_creation" },
            { name: "Kosakata & Ejaan Modern 📱", key: "spelling", prompt_type: "spelling_fix" },
            { name: "Phrasal Verbs & Idiom Gaul 🔥", key: "idioms", prompt_type: "proverb_meaning" },
            { name: "Reading: Analisis Teks Pendek 📰", key: "reading", prompt_type: "literature_quiz" },
            { name: "Situasi Percakapan Kritis 🗣️", key: "speaking", prompt_type: "speaking_scenario" },
        ];

        const DIFFICULTIES = {
            "Mudah": { targetMultiplier: 1.0, basePoints: 20, description: "Santai, untuk pemula. Target 50 poin." },
            "Sedang": { targetMultiplier: 1.5, basePoints: 25, description: "Tantangan seimbang, ideal untuk latihan. Target 75 poin." },
            "Sulit": { targetMultiplier: 2.0, basePoints: 30, description: "Intens, untuk pelajar mahir. Target 100 poin." }
        };
        
        // --- 1. Logika TTS & Fungsi Pembantu ---

        function speakNarrative(text) {
            if (!gameState.isTtsEnabled || !('speechSynthesis' in window)) {
                return;
            }

            window.speechSynthesis.cancel();
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = text;
            // Hapus teks narasi AI Master Game untuk pembacaan TTS yang lebih bersih
            const cleanedText = tempDiv.textContent.replace(/AI Master Game: /g, '').replace(/GM: /g, '').trim();

            const utterance = new SpeechSynthesisUtterance(cleanedText);
            // Gunakan TTS Bahasa Indonesia jika tersedia
            utterance.lang = 'id-ID'; 
            utterance.pitch = 1.0;
            utterance.rate = 0.95;

            const setVoiceAndSpeak = () => {
                const voices = window.speechSynthesis.getVoices();
                // Coba temukan suara Bahasa Indonesia
                const idVoice = voices.find(v => v.lang.startsWith('id-'))
                                || voices.find(v => v.lang.startsWith('en-')); // Fallback ke Inggris

                
                if (idVoice) {
                    utterance.voice = idVoice;
                }

                window.speechSynthesis.speak(utterance);
            };

            if (window.speechSynthesis.getVoices().length > 0) {
                setVoiceAndSpeak();
            } else {
                window.speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
            }
        }
        
        // Pembantu untuk memperbarui teks narasi dan memicu TTS
        function setNarrativeText(text) {
            narrativeText.textContent = text;
            speakNarrative(text);
        }

        function toggleTts() {
            gameState.isTtsEnabled = !gameState.isTtsEnabled;
            if (gameState.isTtsEnabled) {
                ttsToggle.textContent = "🔈 Narasi Aktif";
                speakNarrative("Narasi suara diaktifkan.");
            } else {
                ttsToggle.textContent = "🔇 Narasi Mati";
                window.speechSynthesis.cancel();
            }
            updateUI(); // Simpan preferensi TTS
        }
        
        /**
         * Membersihkan teks dari karakter Markdown yang tidak diinginkan
         */
        function cleanText(text) {
            if (!text) return '';
            // Hapus karakter Markdown awal/akhir
            return text.replace(/^[*\s#>]*(.*?)[*\s#]*$/gm, (match, p1) => p1.trim()).trim();
        }

        // Pembantu untuk menampilkan peringatan pesan singkat
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

        // Pembantu untuk mengatur status memuat
        function setLoading(isLoading) {
            if (isLoading) {
                loadingIndicator.classList.remove('hidden');
                actionButton.disabled = true;
                hintButton.disabled = true;
                userInput.disabled = true;
                window.speechSynthesis.cancel(); // Hentikan bicara saat memuat
            } else {
                loadingIndicator.classList.add('hidden');
                // Tombol Aksi diaktifkan jika mode dipilih dan tidak dalam tampilan tinjauan
                if (gameState.regionIndex !== -1 && reviewArea.classList.contains('hidden')) {
                    actionButton.disabled = false;
                    hintButton.disabled = false;
                    userInput.disabled = false;
                }
            }
        }

        // --- Handler API Gemini ---
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

            // Implementasi Exponential Backoff
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
                            continue; // Coba lagi
                        }
                        throw new Error(`Panggilan API gagal dengan status ${response.status}`);
                    }

                    const result = await response.json();
                    setLoading(false);
                    const part = result.candidates?.[0]?.content?.parts?.[0];
                    if (part && part.text) {
                        // Jika skema JSON digunakan, coba parse JSON
                        if (jsonSchema) {
                            try {
                                return JSON.parse(part.text);
                            } catch (e) {
                                console.error("Gagal parse respons JSON:", e);
                                // Fallback untuk parse JSON yang gagal
                                throw new Error("API mengembalikan JSON yang tidak valid.");
                            }
                        }
                        return part.text;
                    }
                    throw new Error("Struktur respons yang tidak valid dari API.");

                } catch (error) {
                    if (i === maxRetries - 1) {
                        setLoading(false);
                        console.error("Kesalahan API Gemini setelah percobaan ulang:", error);
                        return jsonSchema ? { isCorrect: false, pointsAwarded: 0, feedbackNarrative: "GM: Maaf, koneksi ke Dunia Bahasa terputus. Coba lagi!" } : "GM: Terjadi kesalahan koneksi, silakan coba lagi.";
                    }
                }
            }
        }
        
        // --- Logika Pembuatan Tantangan Dinamis ---

        /**
         * Memanggil AI untuk menghasilkan 5 benih tantangan unik per kategori.
         */
        async function generateChallengeSeeds() {
            setLoading(true); // Mulai memuat di sini
            
            const seedSchema = {
                type: "OBJECT",
                properties: REGIONS.reduce((acc, region) => {
                    // Buat properti JSON untuk setiap jenis prompt
                    acc[region.prompt_type] = {
                        type: "ARRAY",
                        items: { type: "STRING" }
                    };
                    return acc;
                }, {}),
                required: REGIONS.map(r => r.prompt_type)
            };

            // INSTRUKSI SISTEM UNTUK PEMBUATAN BENIH: Lebih fokus pada realitas dan konteks siswa modern
            const systemInstruction = `Anda adalah AI Master Game 'Penjaga Diksi'. Tugas Anda adalah membuat 5 benih tantangan unik dan orisinal untuk setiap kategori bahasa Inggris yang disediakan, sesuai dengan tingkat kesulitan ${gameState.difficulty}. SEMUA tantangan dan contoh HARUS menggunakan konteks yang sangat relevan, modern, dan spesifik dari KEHIDUPAN SEHARI-HARI siswa SMA Indonesia (misalnya: topik media sosial, video game, tren viral, proyek sekolah, interaksi dengan guru/teman). Tanggapi HANYA dengan JSON.
            - Untuk grammar_rearrange, berikan kata-kata acak dalam bahasa Inggris yang perlu disusun kembali menjadi kalimat yang benar secara tata bahasa.
            - Untuk spelling_fix, berikan kalimat pendek dalam bahasa Inggris yang mengandung 1-2 kesalahan ejaan/kosakata umum yang sering dilakukan dalam chat/teks.
            - Untuk poetry_creation, berikan instruksi kreatif singkat untuk menulis baris puitis atau menggunakan gaya bahasa tertentu dalam bahasa Inggris tentang emosi atau acara remaja.
            - Untuk proverb_meaning, berikan idiom atau frasa kata kerja bahasa Inggris yang populer di kalangan remaja yang harus dijelaskan maknanya oleh siswa.
            - Untuk literature_quiz, berikan pertanyaan pemahaman sederhana berdasarkan skenario fiksi pendek atau teks informasi yang relevan dengan remaja (misalnya, berita sekolah, pesan grup chat).
            - Untuk speaking_scenario, berikan skenario interaktif atau situasional dalam bahasa Inggris yang memerlukan respons percakapan atau deskriptif yang fasih dari siswa (misalnya, merespons postingan viral, berdebat tentang tugas).
            - JANGAN berikan jawaban dalam benih.
            - Format output harus persis seperti skema JSON yang diberikan.`;
            
            const regionNames = REGIONS.map(r => `${r.name} (${r.key})`).join(', ');
            const userPrompt = `Buat 5 benih tantangan untuk setiap kategori ini: ${regionNames}. Kesulitan: ${gameState.difficulty}.`;

            const generatedSeeds = await callGeminiAPI(systemInstruction, userPrompt, seedSchema);

            setLoading(false); // Hentikan memuat di sini
            
            if (generatedSeeds && Object.keys(generatedSeeds).length === REGIONS.length) {
                gameState.challengeSeeds = generatedSeeds;
                saveGame();
                return true;
            }
            return false;
        }

        // --- Logika Pemilihan Mode ---
        function hideGameElements() {
            userInput.classList.add('hidden');
            challengePrompt.classList.add('hidden');
            actionButton.classList.add('hidden');
            hintButton.classList.add('hidden');
            reviewArea.classList.add('hidden'); 
            selectionArea.classList.remove('hidden');
            selectionArea.innerHTML = '';
            // Gunakan grid-cols-3 untuk menampung semua modul
            selectionArea.className = 'grid grid-cols-3 gap-4 hidden'; 
        }

        function showModeSelection() {
            hideGameElements();
            selectionArea.classList.remove('hidden');
            
            // Mengatur tata letak grid secara dinamis berdasarkan jumlah modul
            const gridClass = REGIONS.length <= 4 ? 'grid-cols-2' : 'grid-cols-3';
            selectionArea.className = `grid ${gridClass} gap-4`;

            setNarrativeText("AI Master Game: Selamat datang, Penjelajah Diksi! Pilih Modul Bahasa Inggris untuk menguji kecakapan bahasa Anda. Setiap modul menawarkan tantangan unik yang disesuaikan dengan kehidupan SMA Anda!");
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
            setNarrativeText(`AI Master Game: Anda memilih ${selectedRegion}. Sekarang, tentukan tingkat perjalanan Anda:`);
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
            
            // Hitung Target Skor berdasarkan kesulitan
            gameState.targetScore = 50 * difficulty.targetMultiplier;
            
            gameState.challengeIndex = 0;
            gameState.score = 0;
            gameState.currentChallenge = null;
            gameState.finalReviewText = null; 

            // 1. BUAT BENIH DINAMIS
            setNarrativeText("AI Master Game: Sedang membuat mantra diksi yang sangat relevan. Harap tunggu, sedang menyiapkan set tantangan baru dari dunia sekolah dan media sosial Anda...");
            const success = await generateChallengeSeeds();
            
            if (!success) {
                setNarrativeText("GM: Gagal membuat tantangan baru. Coba atur ulang game dan periksa koneksi internet Anda!");
                showDifficultySelection(gameState.regionIndex);
                return;
            }
            
            // 2. Lanjutkan Game
            selectionArea.classList.add('hidden');
            userInput.classList.remove('hidden');
            actionButton.classList.remove('hidden');
            hintButton.classList.remove('hidden');
            
            setNarrativeText(`GM: Tantangan ${difficultyKey} telah siap! Mari kita mulai Petualangan di Modul ${REGIONS[gameState.regionIndex].name}!`);

            updateUI();
            nextChallenge();
        }


        /**
         * Memuat status game dari Local Storage atau memulai game baru.
         */
        function initGame() {
            try {
                const savedState = localStorage.getItem('bahasaNusantaraGame');
                if (savedState) {
                    gameState = JSON.parse(savedState);
                    
                    if (typeof gameState.isTtsEnabled === 'undefined') {
                        gameState.isTtsEnabled = true; 
                    }
                    
                    const hasSeeds = gameState.challengeSeeds && Object.keys(gameState.challengeSeeds).length === REGIONS.length;
                    
                    if (gameState.regionIndex === -1 || !gameState.difficulty || !hasSeeds) {
                         // Kembali ke pemilihan mode jika tidak lengkap atau benih hilang
                         showModeSelection();
                    } else {
                        // Lanjutkan game yang disimpan
                        setNarrativeText(`Selamat datang kembali di ${REGIONS[gameState.regionIndex].name}, kesulitan ${gameState.difficulty}. Mari kita lanjutkan!`);
                        userInput.classList.remove('hidden');
                        actionButton.classList.remove('hidden');
                        hintButton.classList.remove('hidden');
                        nextChallenge();
                    }
                    
                } else {
                    resetGame(false); 
                    setNarrativeText("AI Master Game: Selamat datang, Penjelajah Bahasa! Saya adalah Penjaga Diksi. Uji kecakapan bahasa Inggris Anda melalui tantangan yang mereplikasi kehidupan nyata Anda di SMA. Pilih Modul Bahasa Inggris Anda untuk memulai perjalanan diksi Anda!");
                    showModeSelection();
                }
            } catch (e) {
                console.error("Gagal memuat game dari Local Storage:", e);
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
         * Memperbarui tampilan UI (skor, modul, kesulitan, toggle TTS).
         */
        function updateUI() {
            scoreDisplay.textContent = `${gameState.score} / ${gameState.targetScore}`;
            if (gameState.regionIndex !== -1) {
                const regionName = REGIONS[gameState.regionIndex].name;
                const difficultyText = gameState.difficulty ? ` (${gameState.difficulty})` : '';
                regionDisplay.textContent = regionName + difficultyText;
                hintButton.disabled = gameState.currentChallenge && gameState.currentChallenge.hintUsed;
            } else {
                regionDisplay.textContent = "Pilih Mode";
                hintButton.disabled = true;
            }
            
            // Perbarui tampilan tombol TTS
            ttsToggle.textContent = gameState.isTtsEnabled ? "🔈 Narasi Aktif" : "🔇 Narasi Mati";

            saveGame();
        }

        /**
         * Mengatur ulang game dan membersihkan Local Storage.
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
                setNarrativeText("Game telah diatur ulang. Pilih Modul Bahasa Inggris untuk memulai perjalanan baru!");
            }
            // Atur ulang elemen UI ke status awal
            reviewArea.classList.add('hidden');
            showModeSelection();
            updateUI();
        }
        
        /**
         * Memeriksa apakah pemain siap untuk maju ke modul berikutnya.
         */
        function checkRegionProgression() {
            if (gameState.score >= gameState.targetScore) {
                // Tampilkan pesan awal dan mulai proses tinjauan
                setNarrativeText(`SELAMAT! Anda telah mengumpulkan ${gameState.score} Poin dan menaklukkan Modul ${REGIONS[gameState.regionIndex].name} pada tingkat ${gameState.difficulty}! Menyiapkan Ringkasan Tinjauan Pembelajaran...`);
                
                // Sembunyikan semua elemen input/aksi
                actionButton.classList.add('hidden');
                hintButton.classList.add('hidden');
                userInput.classList.add('hidden');
                challengePrompt.classList.add('hidden');
                
                // Panggil fungsi untuk menghasilkan dan menampilkan tinjauan
                generateFinalReview(); 
                
                return true;
            }
            // Atur ulang area tinjauan jika belum selesai
            reviewArea.classList.add('hidden');
            gameState.finalReviewText = null;
            downloadButton.disabled = false;

            return false;
        }

        /**
         * Memanggil AI untuk menghasilkan ringkasan pembelajaran dan umpan balik akhir.
         */
        async function generateFinalReview() {
            const currentRegion = REGIONS[gameState.regionIndex];
            // INSTRUKSI SISTEM UNTUK TINJAUAN AKHIR: Lebih fokus pada realitas dan konteks siswa modern
            const systemInstruction = `Anda adalah AI Master Game 'Penjaga Diksi'. Tugas Anda adalah membuat Ringkasan Tinjauan Pembelajaran yang komprehensif dan suportif untuk siswa SMA Indonesia yang belajar bahasa Inggris. 
            Ringkasan harus berisi 3 bagian utama:
            1. Umpan Balik Kinerja: Berikan pujian spesifik tentang pencapaian ${gameState.score} poin dalam kategori ${currentRegion.name} (Tingkat Kesulitan: ${gameState.difficulty}).
            2. Rekomendasi Belajar: Berikan 3 tips praktis dan relevan untuk peningkatan, terkait langsung dengan materi ${currentRegion.name} (fokus pada tata bahasa, kosa kata, dll.). Saran HARUS dapat diterapkan dalam kehidupan sehari-hari siswa SMA modern (misalnya: saat membuat konten, chatting dengan teman internasional, mengerjakan proyek kelompok).
            3. Motivasi Penutup: Tawarkan kalimat penutup yang memotivasi.
            
            Format keluarannya HARUS BERUPA TEKS BIASA, TANPA Markdown (*, #). Mulai dengan judul 'RINGKASAN TINJAUAN PEMBELAJARAN: PETUALANGAN DIKSI BAHASA INGGRIS' diikuti baris kosong.`;
            
            const userPrompt = `Siswa berhasil menaklukkan Modul ${currentRegion.name} dengan skor ${gameState.score} dari target ${gameState.targetScore} pada kesulitan ${gameState.difficulty}. Berikan Ringkasan Tinjauan Pembelajaran dan motivasi yang berfokus pada peningkatan diri dalam konteks kehidupan sehari-hari siswa.`;
            
            setNarrativeText("GM: Memproses data petualangan Anda... Harap tunggu, Ringkasan Tinjauan Pembelajaran sedang disusun.");

            const reviewText = await callGeminiAPI(systemInstruction, userPrompt);
            
            if (reviewText && !reviewText.startsWith("GM: Kesalahan koneksi")) {
                const cleanedReview = cleanText(reviewText);
                gameState.finalReviewText = cleanedReview;
                
                // Tampilkan Tinjauan di Kotak Narasi
                setNarrativeText(`GM: Petualangan Anda di ${currentRegion.name} telah berakhir. Berikut adalah Ringkasan Tinjauan Pembelajaran Anda. Anda dapat mengunduhnya di bawah.`);
                narrativeText.innerHTML += `<br><br><span class="font-bold text-yellow-500">--- RINGKASAN TINJAUAN PEMBELAJARAN ---</span><br><pre class="whitespace-pre-wrap text-sm mt-2 p-3 bg-gray-700 rounded-lg text-gray-200" style="font-family: 'Merriweather', serif;">${cleanedReview}</pre>`;
                speakNarrative(`Ringkasan pembelajaran sudah siap. Anda dapat melihat detailnya di kotak narasi dan mengunduhnya.`);
                
                // Tampilkan Area Tinjauan
                reviewMessage.textContent = `Ringkasan untuk Modul ${currentRegion.name} selesai.`;
                reviewArea.classList.remove('hidden');

            } else {
                // Tangani kesalahan
                setNarrativeText("GM: Maaf, gagal membuat Ringkasan Tinjauan Pembelajaran. Silakan coba lagi nanti.");
                reviewArea.classList.add('hidden');
            }
            updateUI();
        }

        /**
         * Mengunduh teks tinjauan sebagai file .txt.
         */
        function downloadReview() {
            if (!gameState.finalReviewText) {
                showMessageAlert("Tinjauan belum dimuat sepenuhnya!", 'error');
                return;
            }

            downloadButton.disabled = true;
            downloadButton.textContent = "Mengunduh...";

            try {
                const regionName = REGIONS[gameState.regionIndex].key;
                const fileName = `Tinjauan_BahasaInggris_${regionName}_${gameState.difficulty}.txt`;
                
                // Tambahkan header tanggal ke file
                const dateHeader = `Tanggal Tinjauan: ${new Date().toLocaleDateString('id-ID')}\n\n`;
                const content = dateHeader + gameState.finalReviewText;

                const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                
                // Buat tautan unduhan
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName;
                
                // Picu unduhan
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showMessageAlert("File ringkasan berhasil diunduh!", 'success');
            } catch (error) {
                console.error("Gagal mengunduh file:", error);
                showMessageAlert("Gagal mengunduh file. Periksa konsol.", 'error');
            } finally {
                downloadButton.textContent = "⬇️ Unduh Ringkasan Pembelajaran (.txt)";
                downloadButton.disabled = false;
            }
        }

        /**
         * Menampilkan tantangan berikutnya (Melibatkan API Gemini untuk Narasi & Prompt).
         */
        async function nextChallenge() {
            if (checkRegionProgression() || gameState.regionIndex === -1) return;

            userInput.value = '';
            challengePrompt.classList.add('hidden');
            actionButton.disabled = true;
            
            const currentRegion = REGIONS[gameState.regionIndex];
            
            // Dapatkan benih dari status yang dihasilkan AI
            const seeds = gameState.challengeSeeds[currentRegion.prompt_type]; 

            if (!seeds || seeds.length === 0) {
                setNarrativeText("GM: Data tantangan hilang. Coba pilih kesulitan lagi atau Mulai Ulang Game.");
                return;
            }

            // Jika semua benih digunakan, putar kembali ke benih pertama
            if (gameState.challengeIndex >= seeds.length) {
                gameState.challengeIndex = 0;
                setNarrativeText("GM: Semua tantangan dalam set ini telah diulang. Memulai set dari awal.");
            }

            const seedPrompt = seeds[gameState.challengeIndex];
            
            // 1. Panggil Gemini untuk menghasilkan Narasi Pembuka dan Prompt
            // INSTRUKSI SISTEM YANG DIPERBARUI: Narasi lebih ringkas dan pemisahan jelas
            const systemInstruction = `Anda adalah AI Master Game bernama 'Penjaga Diksi'. Tugas Anda adalah memberikan narasi petualangan yang dramatis, menantang, dan RINGKAS (MAKSIMAL 4-5 KALIMAT). SELALU kaitkan narasi dengan konteks yang RELEVAN dengan KEHIDUPAN SEHARI-HARI siswa SMA Indonesia. Gunakan bahasa Indonesia yang sederhana, menarik, dan bersemangat. 
            
            PENTING: Narasi harus HANYA berisi pendahuluan dramatis dan motivasi. Tantangan spesifik HARUS diapit dalam tanda kurung siku [Tantangan di sini].
            
            Contoh Format Output yang Diinginkan:
            GM: Selamat datang! Kita berada di Modul Kosakata Modern. Media sosial adalah medan perangnya. Siapkan jarimu. [Perbaiki kalimat ini: 'The concert was so lit, I literally died.']
            
            Pastikan narasi dan keluaran tantangan TIDAK mengandung karakter Markdown seperti *, **, atau #.`;
            const userPrompt = `Berikan narasi RINGKAS dan tantangan untuk materi Bahasa Inggris: ${currentRegion.name}. Benih untuk tantangan adalah: ${seedPrompt}.`;

            setNarrativeText(`GM: Menyiapkan tantangan di ${currentRegion.name}...`);

            const fullResponse = await callGeminiAPI(systemInstruction, userPrompt);
            
            // 2. Ekstrak Narasi dan Prompt dari Respons
            const [narrativePart, promptPart] = fullResponse.split(/\[(.*?)\]/s).filter(Boolean);
            
            // Simpan benih dan atur ulang status petunjuk untuk evaluasi berikutnya
            gameState.currentChallenge = { seed: seedPrompt, hintUsed: false };
            
            // Tampilkan Narasi dan Prompt setelah dibersihkan
            setNarrativeText(cleanText(narrativePart || fullResponse.trim()));
            challengePrompt.textContent = cleanText(promptPart ? `[${promptPart.trim()}]` : seedPrompt);
            challengePrompt.classList.remove('hidden');
            actionButton.disabled = false;
            userInput.focus();
            updateUI(); // Perbarui UI untuk mengaktifkan tombol petunjuk
        }

        /**
         * Meminta petunjuk dari AI Master Game (Melibatkan API Gemini).
         */
        async function requestHint() {
            if (gameState.currentChallenge.hintUsed) {
                showMessageAlert("GM: Anda sudah meminta petunjuk untuk tantangan ini.", 'error');
                speakNarrative("Anda sudah meminta petunjuk untuk tantangan ini.");
                return;
            }
            if (gameState.regionIndex === -1) return;

            const currentRegion = REGIONS[gameState.regionIndex];
            const promptText = challengePrompt.textContent;
            const userInputText = userInput.value.trim();

            hintButton.disabled = true;
            hintButton.textContent = "Meminta Petunjuk...";
            
            // 1. Definisikan Instruksi Sistem dan Prompt untuk Petunjuk
            // INSTRUKSI SISTEM UNTUK PETUNJUK: Lebih fokus pada realitas dan konteks siswa modern
            const systemInstruction = `Anda adalah AI Master Game 'Penjaga Diksi'. Tugas Anda adalah memberikan petunjuk yang sangat halus dan tidak langsung (maksimal 2 kalimat) untuk membantu siswa memecahkan masalah ${currentRegion.name} pada tingkat kesulitan ${gameState.difficulty}. SELALU berikan petunjuk dalam konteks yang RELEVAN dengan KEHIDUPAN SEHARI-HARI siswa SMA modern (misalnya: merujuk pada materi di TikTok, tips dari guru, atau percakapan di grup chat). Gunakan bahasa naratif yang sederhana dan menarik dalam bahasa Indonesia. Jangan berikan jawaban secara langsung. Pastikan keluarannya TIDAK mengandung karakter Markdown seperti *, **, atau #.`;
            const userPrompt = `Tantangan saat ini: "${promptText}". Modul: ${currentRegion.name}. Jawaban yang sudah diketik siswa sejauh ini: "${userInputText || 'tidak ada'}" (gunakan ini agar petunjuk lebih relevan). Berikan petunjuk halus.`;

            const hintText = await callGeminiAPI(systemInstruction, userPrompt);
            
            hintButton.textContent = "Dapatkan Petunjuk (-5 Pts)";
            
            if (hintText.startsWith("GM:")) {
                setNarrativeText(cleanText(hintText));
            } else {
                const cleanedHint = cleanText(hintText);
                narrativeText.innerHTML = `<span class="text-yellow-400 font-bold">PETUNJUK GM:</span> ${cleanedHint}`;
                speakNarrative(`Petunjuk GM: ${cleanedHint}`);
                
                // Tandai petunjuk sudah digunakan
                gameState.currentChallenge.hintUsed = true;
                showMessageAlert("Petunjuk Diberikan! 5 poin akan dipotong jika benar.", 'error');
            }
            updateUI(); 
        }


        /**
         * Memproses input pengguna (Melibatkan API Gemini untuk Evaluasi dan Umpan Balik).
         */
        async function handleAction() {
            if (gameState.regionIndex === -1) return; // Tidak ada aksi jika mode belum dipilih

            const input = userInput.value.trim();
            
            if (!input) {
                showMessageAlert("Jawaban Anda kosong, Penjelajah!", 'error');
                speakNarrative("Jawaban Anda kosong, Penjelajah!");
                return;
            }
            
            actionButton.disabled = true;
            actionButton.textContent = "Mengevaluasi Jawaban...";
            
            const currentRegion = REGIONS[gameState.regionIndex];
            const challengeSeed = gameState.currentChallenge.seed;
            const promptText = challengePrompt.textContent;
            const basePoints = DIFFICULTIES[gameState.difficulty].basePoints;

            // 1. Definisikan Skema JSON untuk Evaluasi Terstruktur
            const evaluationSchema = {
                type: "OBJECT",
                properties: {
                    "isCorrect": { "type": "BOOLEAN" },
                    "pointsAwarded": { "type": "INTEGER" }, 
                    "feedbackNarrative": { "type": "STRING" }
                },
                required: ["isCorrect", "pointsAwarded", "feedbackNarrative"]
            };

            // 2. Definisikan Instruksi Sistem dan Prompt untuk Evaluasi
            // INSTRUKSI SISTEM UNTUK EVALUASI: Lebih fokus pada realitas dan konteks siswa modern
            const systemInstruction = `Anda adalah AI Master Game 'Penjaga Diksi'. Tugas Anda adalah mengevaluasi secara ketat jawaban Bahasa Inggris yang diberikan oleh siswa SMA Indonesia untuk materi ${currentRegion.name} pada tingkat kesulitan ${gameState.difficulty}. SELALU gunakan contoh dan konteks yang RELEVAN dari KEHIDUPAN SEHARI-HARI siswa SMA modern (misalnya: saat membuat konten di TikTok, interaksi di Discord, atau drama di sekolah) dalam umpan balik naratif Anda. Umpan balik naratif harus dalam bahasa Indonesia.
            Gunakan bahasa yang sederhana, jelas, dan menarik. Hindari istilah yang terlalu akademis dalam narasi.
            Jika jawaban benar, berikan pujian yang sangat antusias, dramatis, dan memotivasi, seolah-olah siswa baru saja membuka rahasia besar (maksimal 3 kalimat). 
            Jika jawaban salah, berikan narasi yang memotivasi siswa untuk meninjau kembali kesalahan mereka, hindari nada menyalahkan (maksimal 3 kalimat). 
            Tanggapi HANYA dengan JSON. Tetapkan 'pointsAwarded' ke ${basePoints} jika benar, dan 0 jika salah. Pastikan keluaran feedback_narrative DO NOT mengandung karakter Markdown seperti *, **, atau #.`;

            const userPrompt = `Materi: ${currentRegion.name}. Benih Asli: "${challengeSeed}". Prompt yang Dilihat Siswa: "${promptText}". Jawaban Siswa: "${input}". Evaluasi jawaban ini dan berikan umpan balik naratif.`;

            // 3. Panggil API Gemini
            const result = await callGeminiAPI(systemInstruction, userPrompt, evaluationSchema);
            
            actionButton.textContent = "Kirim Jawaban";
            setLoading(false);

            if (result && typeof result.isCorrect !== 'undefined') {
                const { isCorrect, pointsAwarded, feedbackNarrative } = result;
                
                const cleanedFeedback = cleanText(feedbackNarrative);

                if (isCorrect) {
                    let finalPoints = basePoints; // Gunakan basePoints dari kesulitan
                    let narrativeAddition = "";

                    // Periksa apakah petunjuk digunakan
                    if (gameState.currentChallenge && gameState.currentChallenge.hintUsed) {
                        finalPoints = Math.max(0, basePoints - 5); // Potong 5 poin, minimum 0
                        narrativeAddition = ` (5 poin dipotong karena menggunakan petunjuk. Poin akhir: ${finalPoints})`;
                    }

                    gameState.score += finalPoints;
                    
                    showMessageAlert(`+${finalPoints} Pts! Jawaban Benar!`, 'success');
                    const finalNarrative = `✅ ${cleanedFeedback}${narrativeAddition} Anda mendapatkan ${finalPoints} Poin Diksi! Pintu telah terbuka!`;
                    setNarrativeText(finalNarrative);
                    
                    // Lanjutkan ke tantangan berikutnya
                    gameState.challengeIndex++;
                    updateUI();
                    setTimeout(nextChallenge, 2000);

                } else {
                    showMessageAlert("Jawaban Belum Tepat!", 'error');
                    const errorNarrative = `❌ ${cleanedFeedback} Coba lagi, Penjelajah.`;
                    setNarrativeText(errorNarrative);
                    actionButton.disabled = false;
                    updateUI();
                }
            } else {
                 // Tangani kesalahan API/timeout
                 setNarrativeText("GM: Maaf, koneksi ke Dunia Bahasa terputus. Coba lagi!");
                 actionButton.disabled = false;
                 updateUI();
            }
        }


        // --- 3. Inisiasi Game ---

        // Panggil initGame saat halaman selesai memuat
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
