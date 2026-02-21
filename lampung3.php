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
    <title>Uji Kompetensi Bahasa Lampung</title>
    <!-- Memuat Tailwind CSS CDN untuk styling modern dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Menggunakan font Inter untuk tampilan profesional dan Merriweather untuk konten narasi/analisis */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Merriweather:wght@400;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            /* Warna khas Lampung: Merah, Kuning, Hijau */
            background: linear-gradient(135deg, #1c1917, #57534e); 
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        #game-container {
            background-color: #f7f7f7; /* Background Cerah */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            max-width: 800px;
            width: 100%;
            border-radius: 1.5rem;
            overflow: hidden;
            border: 5px solid #ef4444; /* Merah sebagai aksen */
        }

        /* Styling untuk kotak narasi/analisis */
        #narrative-box {
            background-color: #fca5a5; /* Merah muda lembut */
            font-family: 'Merriweather', serif;
            font-size: 1.05rem;
            line-height: 1.7;
            min-height: 120px;
            padding: 1.5rem;
            color: #1e293b; /* Text gelap */
            border-bottom: 3px solid #ef4444;
            position: relative;
        }

        /* Avatar */
        #gm-avatar {
            font-size: 2.5rem;
            margin-right: 1rem;
            text-shadow: 0 0 8px #f97316;
        }

        /* Styling untuk kotak tantangan/input */
        #challenge-area {
            background-color: #ffffff;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            position: relative;
        }

        .challenge-prompt {
            font-size: 1.25rem;
            font-weight: 700;
            color: #059669; /* Hijau accent */
            text-align: center;
            padding: 1rem;
            border: 2px solid #34d399;
            border-radius: 0.75rem;
            background-color: #ecfdf5;
        }

        .selection-button {
            background-color: #fb923c; /* Oranye accent (Kuning Emas) */
            color: white;
            font-weight: bold;
            padding: 1rem;
            border-radius: 0.75rem;
            transition: all 0.2s;
            box-shadow: 0 4px #ea580c;
        }
        .selection-button:hover {
            background-color: #f97316;
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
        
        .answer-button {
            background-color: #10b981; /* Hijau */
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s, transform 0.1s;
            box-shadow: 0 3px #059669;
        }
        .answer-button:hover {
            background-color: #059669;
        }
        .answer-button:active {
            transform: translateY(1px);
            box-shadow: 0 2px #059669;
        }

        .correct-answer {
            background-color: #d1fae5;
            border: 2px solid #10b981;
            color: #065f46;
        }
        
        .incorrect-answer {
            background-color: #fee2e2;
            border: 2px solid #f87171;
            color: #991b1b;
        }

        .disabled-button {
             opacity: 0.6;
             cursor: not-allowed;
        }
        
        /* New for Loading Indicator */
        .loading-text {
            color: #f97316;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

    <div id="game-container">
        <!-- HEADER: Score and Dialect Info -->
        <div class="p-4 bg-red-600 text-white flex justify-between items-center rounded-t-xl font-bold">
            <span id="game-title" class="text-xl">Uji Kompetensi Bahasa Lampung</span>
            <div class="flex space-x-4 text-sm items-center">
                <!-- TTS Toggle Button -->
                <button id="tts-toggle" class="bg-gray-800 text-white p-1 px-3 rounded-full shadow-md text-xs hover:bg-gray-700 transition" onclick="toggleTts()">
                    🔈 Narasi Aktif
                </button>
                <span class="p-1 px-3 bg-red-800 text-white rounded-full shadow-md">Skor: <span id="score-display">0</span></span>
                <span class="p-1 px-3 bg-yellow-500 text-black rounded-full shadow-md">Dialek Uji: <span id="dialect-display">Pilih</span></span>
            </div>
        </div>

        <!-- NARATIVE AREA -->
        <div id="narrative-box">
            <div class="flex items-start">
                <span id="gm-avatar" class="text-orange-500">🔥</span>
                <div>
                    <span class="font-bold text-lg text-red-700">Penasihat Aksara:</span>
                    <p id="narrative-text" class="text-gray-900 mt-2">Memuat sistem...</p>
                </div>
            </div>
        </div>

        <!-- CHALLENGE AREA -->
        <div id="challenge-area">
            
            <!-- Message Alert (Hidden by default) -->
            <div id="message-alert" class="bg-red-500 text-white hidden"></div>

            <!-- Challenge Prompt (Question text) -->
            <div id="challenge-prompt" class="challenge-prompt hidden"></div>
            
            <!-- Dialect Selection Area / Answer Options Area -->
            <div id="selection-area" class="grid grid-cols-2 gap-4">
                <!-- Buttons will be generated here -->
            </div>
            
            <!-- Loading Indicator -->
            <div id="loading-indicator" class="text-center p-4 text-gray-700 font-bold hidden flex-col items-center">
                 <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="loading-text mt-2" id="loading-message">Sedang memuat soal...</span>
            </div>

            <!-- Action Buttons: -->
            <div id="action-buttons-container" class="hidden">
                 <button id="next-button" class="selection-button w-full" onclick="nextChallenge()">Soal Selanjutnya</button>
            </div>
            
            <!-- Quiz End Review Area -->
            <div id="review-area" class="mt-4 p-4 bg-yellow-100 rounded-xl hidden flex-col items-center">
                <p id="review-message" class="text-red-700 mb-3 font-semibold text-center"></p>
                <button id="restart-button" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition duration-150" onclick="resetGame()">
                    🔄 Ulangi Uji Kompetensi
                </button>
            </div>

            <!-- Reset Button -->
            <button id="reset-game-button" class="text-xs text-gray-500 hover:text-red-400 mt-2" onclick="resetGame()">Reset Uji Kompetensi (Hapus Data)</button>
        </div>
    </div>

    <script>
        // --- 0. API & Data Configuration ---
        const apiKey = "<?php echo $apiKey; ?>"; // Dibiarkan kosong sesuai instruksi
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;
        const QUESTION_COUNT = 10;
        const POINTS_PER_QUESTION = 10;

        // Skema JSON untuk memastikan Gemini mengembalikan format data yang dapat diproses
        const QUIZ_SCHEMA = {
            type: "ARRAY",
            items: {
                type: "OBJECT",
                properties: {
                    id: { type: "INTEGER" },
                    question: { type: "STRING", description: "Pertanyaan yang menguji perbedaan antara Dialek A dan Dialek O." },
                    options: { type: "ARRAY", items: { type: "STRING" }, description: "Empat pilihan jawaban untuk pertanyaan tersebut." },
                    correctIndex: { type: "INTEGER", description: "Indeks (0-3) dari pilihan jawaban yang benar." },
                    points: { type: "INTEGER", description: "Jumlah poin yang diberikan untuk jawaban yang benar (selalu 10)." }
                },
                required: ["id", "question", "options", "correctIndex", "points"]
            }
        };

        // Data Kuis Cadangan (FALLBACK_QUIZ_DATA) jika API gagal
        const FALLBACK_QUIZ_DATA = [
             { id: 1, question: "Kata ganti orang pertama jamak inklusif ('kita') dalam **Dialek A** (Nyo) adalah...", options: ["Tih", "Sekam", "Ram", "Nyak"], correctIndex: 2, points: 10 },
             { id: 2, question: "Kata kerja 'melihat' dalam **Dialek O** (Api) diterjemahkan sebagai...", options: ["Ngakuk", "Ngeliyak", "Ngeliyom", "Ngamang"], correctIndex: 1, points: 10 },
             { id: 3, question: "Kata tanya 'apa' dalam **Dialek A** (Nyo) diucapkan dengan...", options: ["Nyo", "Sapa", "Api", "Pikei"], correctIndex: 2, points: 10 },
             { id: 4, question: "Pilih terjemahan yang tepat untuk kalimat \"Saya mau makan nasi.\" dalam **Dialek O** (Api).", options: ["Niku haga ngon sangu.", "Nyak haga ngon sangu.", "Nyo haga ngakuk sangu.", "Nyak haga ngakuk sangu."], correctIndex: 3, points: 10 },
             { id: 5, question: "Kata benda 'rumah' dalam **Dialek A** (Nyo) adalah...", options: ["Lambon", "Bandakh", "Lamban", "Pekon"], correctIndex: 2, points: 10 },
             { id: 6, question: "Kata 'pintu' dalam bahasa Lampung, baik **Dialek A** maupun **Dialek O**, umumnya menggunakan kata...", options: ["Lawang", "Pintuwan", "Jendela", "Liyak"], correctIndex: 0, points: 10 },
             { id: 7, question: "Bagaimana menyebut \"kamu\" atau \"anda\" (orang kedua tunggal) dalam **Dialek O** (Api)?", options: ["Pikik", "Niku", "Ia", "Iko"], correctIndex: 1, points: 10 },
             { id: 8, question: "Dalam **Dialek A** (Nyo), terjemahan yang benar untuk frasa \"Mau ke mana kamu?\" adalah...", options: ["Haga ngapi niku?", "Hendi dipa nikew?", "Haga kemano niku?", "Haga nyo niku?"], correctIndex: 1, points: 10 },
             { id: 9, question: "Kata 'pulang' dalam **Dialek O** (Api) memiliki bentuk yang mirip dengan...", options: ["Lapah", "Mulang", "Nuli", "Nihan"], correctIndex: 2, points: 10 },
             { id: 10, question: "Kata 'air' (Dialek O) dan 'air' (Dialek A) secara berturut-turut adalah...", options: ["Wai (O) dan Wai (A)", "Way (O) dan Way (A)", "Woi (O) dan Wai (A)", "Wai (O) dan Woy (A)"], correctIndex: 0, points: 10 }
        ];
        
        // --- 1. Variabel Global dan DOM Elements ---
        const scoreDisplay = document.getElementById('score-display');
        const dialectDisplay = document.getElementById('dialect-display');
        const narrativeText = document.getElementById('narrative-text');
        const challengePrompt = document.getElementById('challenge-prompt');
        const selectionArea = document.getElementById('selection-area'); 
        const ttsToggle = document.getElementById('tts-toggle'); 
        const reviewArea = document.getElementById('review-area');
        const reviewMessage = document.getElementById('review-message');
        const actionButtonsContainer = document.getElementById('action-buttons-container');
        const loadingIndicator = document.getElementById('loading-indicator');
        const loadingMessage = document.getElementById('loading-message');

        // Game State
        let gameState = {
            score: 0,
            questionIndex: -1, 
            selectedDialect: null, 
            isTtsEnabled: true,
            isAnswered: false,
            quizData: null,
            totalQuestions: 0,
            maxScore: 0
        };

        let TOTAL_QUESTIONS = QUESTION_COUNT; 
        let MAX_SCORE = QUESTION_COUNT * POINTS_PER_QUESTION; 

        // --- 2. Utility Functions (Loading and API Fetch) ---

        function showLoading(show, message = "Sedang memuat soal...") {
            loadingMessage.textContent = message;
            if (show) {
                loadingIndicator.classList.remove('hidden');
                selectionArea.classList.add('hidden');
                challengePrompt.classList.add('hidden');
                actionButtonsContainer.classList.add('hidden');
            } else {
                loadingIndicator.classList.add('hidden');
            }
        }
        
        async function exponentialBackoffFetch(url, options, maxRetries = 5, delay = 1000) {
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (response.status === 429 || response.status >= 500) {
                        throw new Error(`Rate limit or server error: ${response.status}`);
                    }
                    return response;
                } catch (error) {
                    // console.error(`Fetch attempt ${i + 1} failed. Retrying...`, error); // Do not log retries
                    if (i === maxRetries - 1) throw error; 
                    await new Promise(resolve => setTimeout(resolve, delay * Math.pow(2, i)));
                }
            }
        }
        
        async function fetchGeminiQuestions(dialect) {
            showLoading(true, `Menyiapkan ${QUESTION_COUNT} soal khusus untuk Dialek ${dialect} (Api)...`);
            
            const systemPrompt = `Anda adalah seorang ahli bahasa Lampung. Buatlah ${QUESTION_COUNT} pertanyaan kuis pilihan ganda (4 pilihan) yang secara spesifik menguji pemahaman pengguna tentang perbedaan kosakata dan tata bahasa antara Dialek A (Nyo) dan Dialek O (Api). Pastikan setiap pertanyaan menantang dan jelas. Total poin kuis harus ${MAX_SCORE}. Berikan output hanya dalam format JSON sesuai skema yang diberikan.`;
            
            const userQuery = `Buatkan ${QUESTION_COUNT} soal kuis Bahasa Lampung yang berfokus pada Dialek A (Nyo) dan Dialek O (Api). Dialek yang dipilih pengguna adalah: ${dialect}.`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: QUIZ_SCHEMA
                },
            };

            try {
                const response = await exponentialBackoffFetch(
                    apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                
                if (jsonText) {
                    const fetchedData = JSON.parse(jsonText);
                    // Filter untuk memastikan data memiliki 10 item dan semua properti ada
                    const validData = fetchedData.filter(q => 
                        q.id && q.question && Array.isArray(q.options) && q.options.length === 4 && 
                        typeof q.correctIndex === 'number' && q.points
                    );

                    if (validData.length > 0) return validData;
                }
                throw new Error("Gemini response was empty or malformed.");

            } catch (error) {
                console.error("Error fetching questions from Gemini:", error);
                showMessageAlert("Gagal memuat soal dari Gemini. Menggunakan soal cadangan.", 'error');
                return FALLBACK_QUIZ_DATA; // Fallback
            } finally {
                showLoading(false);
            }
        }

        // --- 3. TTS Logic & Helper Functions ---

        function speakNarrative(text) {
            if (!gameState.isTtsEnabled || !('speechSynthesis' in window)) {
                return;
            }

            window.speechSynthesis.cancel();
            
            let cleanedText = text
                .replace(/^Penasihat Aksara:\s*/i, '') 
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
        
        function showMessageAlert(message, type) {
            const messageAlert = document.getElementById('message-alert');
            messageAlert.textContent = message;
            messageAlert.className = `message-show`;
            if (type === 'success') {
                messageAlert.classList.add('bg-green-600', 'text-white');
                messageAlert.classList.remove('bg-red-600');
            } else {
                messageAlert.classList.add('bg-red-600', 'text-white');
                messageAlert.classList.remove('bg-green-600');
            }
            messageAlert.classList.remove('hidden');

            setTimeout(() => {
                messageAlert.classList.remove('message-show');
                setTimeout(() => messageAlert.classList.add('hidden'), 300);
            }, 1500);
        }

        // --- 4. Game State Management ---

        function updateUI() {
            scoreDisplay.textContent = gameState.score;
            dialectDisplay.textContent = gameState.selectedDialect ? gameState.selectedDialect : "Pilih";
            saveGame();
        }
        
        function saveGame() {
            localStorage.setItem('lampungQuizState', JSON.stringify(gameState));
        }

        function resetGame(showPrompt = true) {
            window.speechSynthesis.cancel(); 

            gameState = {
                score: 0,
                questionIndex: -1,
                selectedDialect: null,
                isTtsEnabled: gameState.isTtsEnabled || true,
                isAnswered: false,
                quizData: null,
                totalQuestions: 0,
                maxScore: 0
            };
            localStorage.removeItem('lampungQuizState');
            
            if (showPrompt) {
                 setNarrativeText("Penasihat Aksara: Sesi uji kompetensi telah direset. Silakan pilih Dialek Uji untuk memulai sesi baru.");
            }
            
            showDialectSelection();
            updateUI();
        }

        // --- 5. Game Flow Logic ---

        function hideGameElements() {
            challengePrompt.classList.add('hidden');
            actionButtonsContainer.classList.add('hidden');
            reviewArea.classList.add('hidden');
            selectionArea.classList.remove('hidden');
            selectionArea.innerHTML = '';
            selectionArea.className = 'grid grid-cols-2 gap-4';
        }

        function showDialectSelection() {
            hideGameElements();
            showLoading(false);
            selectionArea.classList.add('grid-cols-2');
            
            setNarrativeText("Penasihat Aksara: Selamat datang di Uji Kompetensi Bahasa Lampung. Pilih dialek yang ingin Anda fokuskan (A/Nyo) atau (O/Api) untuk penyesuaian narasi.");
            
            const dialects = [
                { key: "A (Nyo)", description: "Dialek A (Nyo). Meliputi Lampung Timur, Tengah, Utara, dsb." },
                { key: "O (Api)", description: "Dialek O (Api). Meliputi pesisir Teluk Lampung, Pesisir Barat, dsb." }
            ];
            
            dialects.forEach(dialect => {
                const button = document.createElement('button');
                button.className = 'selection-button flex flex-col items-center justify-center';
                button.innerHTML = `<span class="text-2xl">${dialect.key}</span><span class="text-xs font-normal mt-1">${dialect.description.split('.')[0]}.</span>`;
                button.onclick = () => startGame(dialect.key);
                selectionArea.appendChild(button);
            });
        }
        
        async function startGame(dialectKey) {
            // Reset state untuk sesi baru
            gameState.selectedDialect = dialectKey;
            gameState.score = 0;
            gameState.questionIndex = 0;
            gameState.isAnswered = false;
            
            selectionArea.classList.add('hidden');

            // 1. Fetch questions from Gemini
            const fetchedQuizData = await fetchGeminiQuestions(dialectKey);
            
            // 2. Update game state with fetched data
            gameState.quizData = fetchedQuizData;
            gameState.totalQuestions = fetchedQuizData.length; 
            gameState.maxScore = fetchedQuizData.reduce((sum, q) => sum + q.points, 0); 
            
            if (gameState.totalQuestions === 0) {
                 setNarrativeText("Penasihat Aksara: Gagal memuat soal. Silakan pilih dialek lagi.");
                 showDialectSelection();
                 return;
            }

            // 3. Start game
            setNarrativeText(`Penasihat Aksara: Anda memilih Dialek ${dialectKey}. ${gameState.totalQuestions} soal telah dimuat. Jawab semua soal untuk menguji kompetensi Anda.`);
            updateUI();
            setTimeout(nextChallenge, 1500);
        }

        function nextChallenge() {
            if (gameState.questionIndex >= gameState.totalQuestions) {
                showFinalReview();
                return;
            }

            gameState.isAnswered = false;
            actionButtonsContainer.classList.add('hidden');
            
            const currentQ = gameState.quizData[gameState.questionIndex];
            
            challengePrompt.textContent = `Soal ke-${gameState.questionIndex + 1}: ${currentQ.question}`;
            challengePrompt.classList.remove('hidden');

            // Hapus tombol-tombol lama dan buat tombol-tombol pilihan jawaban baru
            selectionArea.innerHTML = '';
            selectionArea.classList.remove('hidden');
            selectionArea.className = 'grid grid-cols-2 gap-4';
            
            currentQ.options.forEach((optionText, index) => {
                const button = document.createElement('button');
                button.className = 'answer-button';
                button.textContent = optionText;
                button.onclick = () => checkAnswer(index, currentQ);
                selectionArea.appendChild(button);
            });

            setNarrativeText(`Penasihat Aksara: Silakan jawab soal ke-${gameState.questionIndex + 1} terkait perbedaan leksikon Dialek A dan O.`);
        }
        
        function checkAnswer(selectedIndex, currentQ) {
            if (gameState.isAnswered) return;

            gameState.isAnswered = true;
            const buttons = selectionArea.querySelectorAll('.answer-button');
            
            // Menonaktifkan semua tombol setelah menjawab
            buttons.forEach(btn => btn.classList.add('disabled-button'));
            
            if (selectedIndex === currentQ.correctIndex) {
                // Jawaban Benar
                gameState.score += currentQ.points;
                buttons[selectedIndex].classList.remove('bg-green-600', 'shadow-md');
                buttons[selectedIndex].classList.add('correct-answer');
                showMessageAlert(`BENAR! (+${currentQ.points} Poin)`, 'success');
                setNarrativeText(`[BENAR] Penasihat Aksara: Jawaban Anda tepat. Skor ditambahkan.`);
            } else {
                // Jawaban Salah
                buttons[selectedIndex].classList.remove('bg-green-600', 'shadow-md');
                buttons[selectedIndex].classList.add('incorrect-answer');
                buttons[currentQ.correctIndex].classList.add('correct-answer');
                showMessageAlert("SALAH. Pelajari kembali!", 'error');
                setNarrativeText(`[SALAH] Penasihat Aksara: Jawaban belum tepat. Jawaban yang benar telah ditandai.`);
            }

            gameState.questionIndex++;
            updateUI();
            
            // Tampilkan tombol Lanjut setelah 2 detik
            setTimeout(() => {
                actionButtonsContainer.classList.remove('hidden');
                actionButtonsContainer.innerHTML = `<button id="next-button" class="selection-button w-full" onclick="nextChallenge()">Soal Selanjutnya (${gameState.questionIndex}/${gameState.totalQuestions})</button>`;
            }, 2000);
        }

        function showFinalReview() {
            const finalScore = gameState.score;
            const percentage = (finalScore / gameState.maxScore) * 100;

            let reviewMsg = `Uji Kompetensi Selesai! Anda berhasil mengumpulkan **${finalScore} dari ${gameState.maxScore} Poin** (${percentage.toFixed(0)}%).`;
            let narrativeMsg = "";

            if (percentage >= 80) {
                reviewMsg += " Kompetensi Anda dalam memahami perbedaan Dialek A dan O sudah SANGAT BAIK! Teruskan belajar!";
                narrativeMsg = "Penasihat Aksara: Selamat! Tingkat kompetensi Anda dalam membedakan dialek bahasa Lampung sangat memuaskan. Anda layak diacungi jempol!";
            } else if (percentage >= 50) {
                reviewMsg += " Kompetensi Anda CUKUP BAIK. Teruslah berlatih pada kosakata yang memiliki variasi dialek yang khas.";
                narrativeMsg = "Penasihat Aksara: Kompetensi Anda cukup baik, namun masih perlu perbaikan pada leksikon kunci. Latihan rutin akan sangat membantu.";
            } else {
                reviewMsg += " Anda perlu LATIHAN LEBIH KERAS! Fokus pada kata ganti orang dan kata kerja inti dalam masing-masing dialek.";
                narrativeMsg = "Penasihat Aksara: Jangan berkecil hati. Tingkat kompetensi Anda masih di bawah rata-rata. Kami sarankan Anda mengulangi kuis ini setelah mempelajari kembali perbedaan mendasar Dialek A dan Dialek O.";
            }

            reviewMessage.innerHTML = reviewMsg;
            setNarrativeText(narrativeMsg);

            challengePrompt.classList.add('hidden');
            selectionArea.classList.add('hidden');
            actionButtonsContainer.classList.add('hidden');
            reviewArea.classList.remove('hidden');
        }


        // --- 6. Inisiasi Game ---

        function initGame() {
            try {
                const savedState = localStorage.getItem('lampungQuizState');
                if (savedState) {
                    const loadedState = JSON.parse(savedState);
                    // Jika data kuis ada di localStorage, gunakan data tersebut untuk melanjutkan
                    if (loadedState.quizData && loadedState.totalQuestions > 0) {
                        gameState = loadedState;
                        
                        if (gameState.questionIndex < gameState.totalQuestions && gameState.questionIndex !== -1) {
                            setNarrativeText(`Penasihat Aksara: Selamat datang kembali. Anda berada di Kuis Dialek ${gameState.selectedDialect}. Silakan lanjutkan dari soal sebelumnya.`);
                            setTimeout(nextChallenge, 1500);
                        } else {
                            showFinalReview(); 
                        }
                    } else {
                         // Jika state ada tapi kuis data hilang, reset
                        resetGame(false);
                        setNarrativeText("Penasihat Aksara: Selamat datang di Uji Kompetensi Bahasa Lampung. Sistem siap menguji pemahaman Anda.");
                        showDialectSelection();
                    }
                } else {
                    resetGame(false); 
                    setNarrativeText("Penasihat Aksara: Selamat datang di Uji Kompetensi Bahasa Lampung. Sistem siap menguji pemahaman Anda.");
                    showDialectSelection();
                }
            } catch (e) {
                console.error("Gagal memuat data dari Local Storage:", e);
                resetGame(false);
                showDialectSelection();
            }
            updateUI();
        }

        // Panggil initGame saat halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', initGame);
    </script>

</body>
</html>
