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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillUp SMK - Game Master AI</title>
    <!-- Memuat Tailwind CSS untuk styling modern dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #059669; /* Emerald 600 */
            --color-secondary: #10b981; /* Emerald 500 */
            --color-dark: #1f2937; /* Gray 800 */
            --color-light: #f9fafb; /* Gray 50 */
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-light);
            min-height: 100vh;
        }
        .container-main {
            min-height: 100vh;
        }
        .card {
            background-color: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-radius: 1rem; /* rounded-xl */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .btn-primary:hover {
            background-color: #047857; /* Emerald 700 */
            transform: translateY(-1px);
        }
        .option-group { /* Wrapper untuk opsi dan tombol TTS */
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .option-button {
            border: 2px solid #e5e7eb;
            transition: background-color 0.2s, border-color 0.2s;
        }
        .option-button:hover:not(:disabled) {
            background-color: #f3f4f6;
            border-color: var(--color-secondary);
        }
        .option-correct {
            background-color: #d1fae5 !important; /* Green 100 */
            border-color: var(--color-primary) !important;
            font-weight: 600;
        }
        .option-incorrect {
            background-color: #fee2e2 !important; /* Red 100 */
            border-color: #ef4444 !important; /* Red 500 */
            font-weight: 600;
        }
        #timerDisplay {
            animation: pulse-red 1.5s infinite;
        }
        @keyframes pulse-red {
            0%, 100% { color: #dc2626; }
            50% { color: #f87171; }
        }
        .badge-icon {
            filter: drop-shadow(2px 2px 2px rgba(0, 0, 0, 0.3));
        }
        /* Loading animation style */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #059669;
            animation: spin 1s ease infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">

    <div id="app" class="container-main max-w-4xl mx-auto p-4 flex flex-col items-center justify-center">

        <!-- Header -->
        <header class="w-full text-center py-6">
            <h1 class="text-4xl font-extrabold text-gray-800">
                <span class="text-green-600">SkillUp</span> SMK
            </h1>
            <p class="text-gray-500 mt-1">Uji Kompetensi Industri dengan Game Master AI</p>
        </header>

        <!-- Main Content Area -->
        <div id="content-area" class="w-full">
            <!-- Content will be rendered here dynamically -->
        </div>

        <!-- Footer / Navigation Bar (Always Visible) -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white shadow-xl border-t border-gray-100">
            <div class="max-w-4xl mx-auto flex justify-around p-3">
                <button onclick="renderMenu('menu')" class="flex flex-col items-center text-gray-600 hover:text-green-600 transition duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="text-xs mt-1">Menu Utama</span>
                </button>
                <button onclick="renderMenu('stats')" class="flex flex-col items-center text-gray-600 hover:text-green-600 transition duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    <span class="text-xs mt-1">Statistik</span>
                </button>
            </div>
        </nav>

    </div>

    <script>
        // --- KONFIGURASI API & MODEL ---
        const API_KEY = "<?php echo $apiKey; ?>"; // Kunci API akan disediakan oleh runtime
        const API_URL_BASE = "https://generativelanguage.googleapis.com/v1beta/models/";
        const MODEL = "gemini-2.5-flash-preview-09-2025";
        const TIMEOUT = 60000; // 60 detik

        // --- KONFIGURASI GAME ---
        const TIME_LIMIT_CHALLENGE = 30; // Batas waktu per pertanyaan dalam detik (Mode Challenge)

        // --- TTS CONFIG ---
        let ttsAvailable = false;
        let indoVoice = null;
        const indoLangCode = 'id-ID';

        // --- STATE GAME (Disimpan di localStorage) ---
        let gameState = {
            points: 0,
            level: 1,
            selectedTopic: '',
            selectedMode: 'Belajar', // 'Belajar' atau 'Challenge'
            selectedDifficulty: 'Dasar (Teori)',
            badges: [],
            stats: {
                totalQuestions: 0,
                correctAnswers: 0,
                topics: {}, // e.g., { 'Teknik Otomotif': { total: 10, correct: 8 } }
            },
            currentQuestion: null,
            isQuizActive: false,
            quizHistory: [], // Menyimpan riwayat soal untuk Mode Belajar
            timer: null, // Untuk Challenge Mode
            timeLeft: TIME_LIMIT_CHALLENGE,
        };

        const TOPICS = [
            { name: 'Teknik Otomotif', icon: '🔧' },
            { name: 'Teknik Elektronika / Listrik', icon: '⚡' },
            { name: 'Teknik Komputer & Jaringan', icon: '💻' },
            { name: 'Teknik Mesin / Industri', icon: '⚙️' },
            { name: 'Keselamatan Kerja (K3)', icon: '⚠️' }
        ];

        // --- FUNGSI UTILITY & STORAGE ---

        /**
         * Menginisialisasi dukungan TTS.
         */
        function initTts() {
            if ('speechSynthesis' in window) {
                ttsAvailable = true;
                const setVoice = () => {
                    const voices = window.speechSynthesis.getVoices();
                    indoVoice = voices.find(voice => voice.lang.startsWith('id') || voice.lang.startsWith('ID'));
                    if (indoVoice) {
                        console.log("Suara Bahasa Indonesia ditemukan:", indoVoice.name);
                    } else {
                        console.warn("Suara Bahasa Indonesia tidak ditemukan. Menggunakan fallback language code.");
                    }
                };

                // Browser mungkin memerlukan waktu untuk memuat suara
                window.speechSynthesis.onvoiceschanged = setVoice;
                setVoice(); // Coba panggil langsung juga
                console.log("Web Speech API (TTS) siap.");
            } else {
                ttsAvailable = false;
                console.warn("Web Speech API (TTS) tidak tersedia di browser ini.");
            }
        }

        /**
         * Memutar teks menggunakan TTS.
         * @param {string} text Teks yang akan dibacakan.
         */
        function speakText(text) {
            if (!ttsAvailable) {
                showMessage('TTS Tidak Tersedia', 'Fitur Text-to-Speech tidak didukung oleh browser Anda.');
                return;
            }
            window.speechSynthesis.cancel(); // Hentikan pembacaan sebelumnya

            const utterance = new SpeechSynthesisUtterance(text);
            
            if (indoVoice) {
                utterance.voice = indoVoice;
            }
            utterance.lang = indoLangCode;
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            
            window.speechSynthesis.speak(utterance);
        }

        /**
         * Memuat state game dari localStorage.
         */
        function loadGameState() {
            try {
                const storedState = localStorage.getItem('skillUpSMKState');
                if (storedState) {
                    const loadedState = JSON.parse(storedState);
                    // Merge with default state to handle new properties
                    gameState = { ...gameState, ...loadedState };

                    // Ensure stats topics are initialized
                    TOPICS.forEach(topic => {
                        if (!gameState.stats.topics[topic.name]) {
                            gameState.stats.topics[topic.name] = { total: 0, correct: 0 };
                        }
                    });

                    // Pastikan selectedDifficulty memiliki nilai default jika kosong
                    if (!gameState.selectedDifficulty) {
                        gameState.selectedDifficulty = 'Dasar (Teori)';
                    }

                    console.log('Game state dimuat:', gameState);
                } else {
                    // Initialize stats for all topics if no state found
                    TOPICS.forEach(topic => {
                        gameState.stats.topics[topic.name] = { total: 0, correct: 0 };
                    });
                    console.log('Game state baru diinisialisasi.');
                }
            } catch (error) {
                console.error('Gagal memuat state dari localStorage:', error);
            }
        }

        /**
         * Menyimpan state game ke localStorage.
         */
        function saveGameState() {
            try {
                localStorage.setItem('skillUpSMKState', JSON.stringify(gameState));
                console.log('Game state disimpan.');
            } catch (error) {
                console.error('Gagal menyimpan state ke localStorage:', error);
            }
        }

        /**
         * Memunculkan pesan modal (pengganti alert/confirm).
         * @param {string} title
         * @param {string} message
         */
        function showMessage(title, message) {
            // Hentikan TTS saat modal muncul
            window.speechSynthesis.cancel(); 
            
            const modalHtml = `
                <div id="modal-msg" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
                    <div class="card p-6 w-full max-w-sm">
                        <h3 class="text-xl font-bold text-gray-800 mb-3">${title}</h3>
                        <p class="text-gray-600 mb-6">${message}</p>
                        <button onclick="document.getElementById('modal-msg').remove()" class="btn-primary w-full">OK</button>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        /**
         * Menampilkan overlay loading (saat memanggil API).
         */
        function showLoading(message = 'Game Master sedang menyiapkan soal...') {
            window.speechSynthesis.cancel(); // Hentikan TTS saat loading
            const loadingHtml = `
                <div id="loading-overlay" class="fixed inset-0 bg-gray-50 bg-opacity-80 flex items-center justify-center z-40">
                    <div class="flex flex-col items-center p-6 bg-white rounded-xl shadow-2xl">
                        <div class="spinner mb-4"></div>
                        <p class="text-gray-700 font-semibold">${message}</p>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loadingHtml);
        }

        /**
         * Menyembunyikan overlay loading.
         */
        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
        }

        /**
         * Implementasi Exponential Backoff untuk retry API.
         */
        async function callGeminiAPI(payload, maxRetries = 3) {
            const apiUrl = `${API_URL_BASE}${MODEL}:generateContent?key=${API_KEY}`;
            const headers = { 'Content-Type': 'application/json' };

            for (let attempt = 0; attempt < maxRetries; attempt++) {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), TIMEOUT);

                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify(payload),
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);

                    if (response.status === 429 && attempt < maxRetries - 1) {
                        const delay = Math.pow(2, attempt) * 1000 + Math.random() * 1000;
                        console.warn(`Rate limit hit. Retrying in ${delay / 1000}s...`);
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    }

                    if (!response.ok) {
                        throw new Error(`API call failed with status: ${response.status}`);
                    }

                    const result = await response.json();
                    const jsonText = result?.candidates?.[0]?.content?.parts?.[0]?.text;

                    if (!jsonText) {
                        throw new Error("Respon API tidak mengandung teks yang valid.");
                    }

                    return JSON.parse(jsonText);

                } catch (error) {
                    if (error.name === 'AbortError') {
                        console.error('Permintaan API kehabisan waktu (timeout).');
                        showMessage('Gagal Koneksi', 'Permintaan ke Game Master kehabisan waktu. Coba lagi.');
                        return null;
                    }
                    console.error(`Attempt ${attempt + 1} failed:`, error);
                    if (attempt === maxRetries - 1) {
                        hideLoading();
                        showMessage('Kesalahan Jaringan/API', 'Tidak dapat terhubung ke Game Master. Cek koneksi Anda atau coba lagi nanti.');
                        return null;
                    }
                    const delay = Math.pow(2, attempt) * 1000 + Math.random() * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
            return null;
        }

        // --- FUNGSI GAME CORE ---

        /**
         * Memulai atau melanjutkan kuis berdasarkan mode yang dipilih.
         */
        function startQuiz() {
            gameState.isQuizActive = true;
            generateQuestion();
        }

        /**
         * Memanggil Gemini untuk menghasilkan soal baru.
         */
        async function generateQuestion() {
            showLoading();

            const difficulty = gameState.selectedDifficulty; // Menggunakan kesulitan yang dipilih user

            const systemInstruction = `Anda adalah Game Master Penguji Kompetensi Industri SMK yang profesional dan cerdas. Tugas Anda adalah menghasilkan soal pilihan ganda (4 opsi) berdasarkan jurusan/topik, tingkat kesulitan, dan mode permainan yang diminta. Soal harus relevan dengan standar kompetensi industri nyata (contoh: otomotif, elektronika, TKJ, K3).
            Soal harus mencakup teori dasar, studi kasus troubleshooting, atau prosedur kerja, sesuai tingkat kesulitan.
            Jawab HANYA dalam format JSON yang terstruktur.`;

            const userQuery = `Buatkan soal kuis kompetensi untuk siswa SMK.
            - Jurusan/Topik: ${gameState.selectedTopic}
            - Tingkat Kesulitan: ${difficulty}
            - Mode Game: ${gameState.selectedMode}
            - Fokus Soal: Jika tingkat kesulitan mengandung kata 'Sulit', buat soal studi kasus/troubleshooting. Jika 'Dasar', buat soal teori/konsep.
            - Jumlah Opsi: 4.
            Berikan juga penjelasan komprehensif (field 'explanation') yang menjelaskan mengapa jawaban benar itu benar dan kesalahan pada opsi lain. Penjelasan harus selalu ada, terlepas dari mode game.`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemInstruction }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "question": { "type": "STRING", description: "Pertanyaan kuis." },
                            "topic": { "type": "STRING", description: "Topik soal (harus sama dengan yang diminta)." },
                            "difficulty": { "type": "STRING", description: "Tingkat kesulitan yang dihasilkan (Dasar/Menengah/Sulit)." },
                            "options": {
                                "type": "ARRAY",
                                "items": { "type": "STRING" },
                                "description": "Daftar 4 opsi jawaban."
                            },
                            "correct_index": { "type": "NUMBER", description: "Indeks (0-3) dari opsi yang benar." },
                            "explanation": { "type": "STRING", description: "Penjelasan detail untuk jawaban yang benar." }
                        }
                    }
                }
            };

            const result = await callGeminiAPI(payload);
            hideLoading();

            if (result) {
                gameState.currentQuestion = result;
                renderQuiz();
            } else {
                showMessage('Error', 'Gagal mendapatkan soal dari Game Master. Silakan coba lagi.');
                renderMenu('menu');
            }
        }

        /**
         * Merender tampilan kuis.
         */
        function renderQuiz() {
            const q = gameState.currentQuestion;
            if (!q) return;

            // 1. Set initial state time if in Challenge Mode (Penting: Set state sebelum merender HTML)
            if (gameState.selectedMode === 'Challenge') {
                gameState.timeLeft = TIME_LIMIT_CHALLENGE;
            }
            
            // Cek apakah mode belajar aktif untuk TTS
            const isLearningMode = gameState.selectedMode === 'Belajar';

            const difficultyIndicator = {
                'Dasar (Teori)': 'bg-green-100 text-green-700',
                'Menengah (Prosedur)': 'bg-yellow-100 text-yellow-700',
                'Sulit (Studi Kasus)': 'bg-red-100 text-red-700'
            }[q.difficulty] || 'bg-gray-100 text-gray-700';
            
            // Tombol TTS untuk pertanyaan
            const questionTtsButton = (ttsAvailable && isLearningMode) ? `
                <button title="Dengarkan Soal" onclick="speakText(\`${q.question.replace(/'/g, "\\'")}\`)" class="p-2 ml-2 text-green-600 hover:text-green-800 focus:outline-none rounded-full bg-gray-100 hover:bg-gray-200 transition duration-150 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a4.99 4.99 0 0 1 0 7.08"></path></svg>
                </button>
            ` : '';

            const quizHtml = `
                <div class="card p-6 md:p-8 space-y-6 mb-20">
                    <div class="flex flex-wrap justify-between items-center pb-4 border-b">
                        <span class="text-xl font-bold text-gray-700">Soal Level ${gameState.level}</span>
                        <div class="flex space-x-2">
                            <span class="px-3 py-1 text-sm font-semibold rounded-full ${difficultyIndicator}">${q.difficulty}</span>
                            ${gameState.selectedMode === 'Challenge' ? `<span id="timerDisplay" class="px-3 py-1 text-sm font-bold rounded-full bg-white shadow-md border">⏳ ${gameState.timeLeft}s</span>` : ''}
                        </div>
                    </div>

                    <!-- Area Pertanyaan dengan Tombol TTS -->
                    <div class="flex items-start">
                        <p class="text-lg font-semibold text-gray-800 flex-grow">${q.question}</p>
                        ${questionTtsButton}
                    </div>

                    <div id="options-container" class="space-y-3">
                        ${q.options.map((opt, index) => {
                            const label = String.fromCharCode(65 + index);
                            const fullOptionText = `${label}. ${opt}`;
                            const optionTtsButton = (ttsAvailable && isLearningMode) ? `
                                <button title="Dengarkan Opsi ${label}" onclick="speakText(\`${fullOptionText.replace(/'/g, "\\'")}\`)" class="p-2 text-green-600 hover:text-green-800 focus:outline-none rounded-full bg-gray-100 hover:bg-gray-200 transition duration-150 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a4.99 4.99 0 0 1 0 7.08"></path></svg>
                                </button>
                            ` : '';

                            return `
                                <div class="option-group">
                                    <button
                                        class="option-button flex-grow w-full text-left p-4 rounded-xl text-gray-700 font-medium transition duration-150"
                                        onclick="submitAnswer(${index})"
                                        data-index="${index}">
                                        <span class="font-bold mr-2">${label}.</span> ${opt}
                                    </button>
                                    ${optionTtsButton}
                                </div>
                            `;
                        }).join('')}
                    </div>

                    <div id="feedback-area" class="hidden mt-6 pt-4 border-t border-gray-100">
                        <h4 class="text-xl font-bold text-green-700 mb-2">Feedback dari Game Master</h4>
                        <p id="feedback-text" class="text-gray-700"></p>
                        <button onclick="nextQuestion()" class="btn-primary mt-4 w-full md:w-auto">Soal Berikutnya</button>
                    </div>
                </div>
            `;
            document.getElementById('content-area').innerHTML = quizHtml;

            // 2. Start timer *setelah* HTML disuntikkan ke DOM
            if (gameState.selectedMode === 'Challenge') {
                startTimer();
            }
        }

        /**
         * Memulai timer untuk Challenge Mode.
         */
        function startTimer() {
            clearInterval(gameState.timer); // Clear existing timer
            gameState.timeLeft = TIME_LIMIT_CHALLENGE; // Set initial time (redundant, but safe)
            const timerElement = document.getElementById('timerDisplay');
            if (!timerElement) return; // Check if element exists

            timerElement.textContent = `⏳ ${gameState.timeLeft}s`;

            gameState.timer = setInterval(() => {
                gameState.timeLeft--;
                timerElement.textContent = `⏳ ${gameState.timeLeft}s`;

                if (gameState.timeLeft <= 0) {
                    clearInterval(gameState.timer);
                    submitAnswer(-1); // Jawaban salah karena waktu habis
                    showMessage('Waktu Habis!', 'Waktu Anda untuk menjawab telah habis. Periksa feedback dari Game Master.');
                }
            }, 1000);
        }

        /**
         * Mengirimkan jawaban dan menampilkan feedback.
         * @param {number} selectedIndex Indeks jawaban yang dipilih (0-3).
         */
        function submitAnswer(selectedIndex) {
            if (!gameState.currentQuestion || !gameState.isQuizActive) return;

            clearInterval(gameState.timer); // Stop timer immediately
            window.speechSynthesis.cancel(); // Hentikan TTS saat submit
            gameState.isQuizActive = false;
            const q = gameState.currentQuestion;
            const correctIndex = q.correct_index;
            const isCorrect = selectedIndex === correctIndex;
            const optionsContainer = document.getElementById('options-container');

            // Disable all options
            optionsContainer.querySelectorAll('button').forEach((btn, index) => {
                btn.disabled = true;
                btn.classList.remove('hover:bg-gray-100', 'hover:border-green-500');
                if (index === selectedIndex && !isCorrect) {
                    // Cek jika tombol adalah opsi jawaban (bukan tombol TTS)
                    if (btn.classList.contains('option-button')) {
                        btn.classList.add('option-incorrect');
                    }
                }
                if (index === correctIndex) {
                    if (btn.classList.contains('option-button')) {
                        btn.classList.add('option-correct');
                    }
                }
            });

            // Update state and score
            let pointsGained = 0;
            if (isCorrect) {
                const basePoints = gameState.level * 10;
                let bonusPoints = 0;
                if (gameState.selectedMode === 'Challenge') {
                    // Bonus poin berdasarkan sisa waktu
                    bonusPoints = Math.floor((gameState.timeLeft / TIME_LIMIT_CHALLENGE) * 10);
                }
                pointsGained = basePoints + bonusPoints;
                gameState.points += pointsGained;
                showMessage('Jawaban Benar!', `Anda mendapatkan ${pointsGained} Poin.`);
            } else if (selectedIndex !== -1) {
                showMessage('Jawaban Salah.', 'Pelajari feedback dari Game Master untuk pemahaman yang lebih baik.');
            }

            // Update stats
            gameState.stats.totalQuestions++;
            if (isCorrect) gameState.stats.correctAnswers++;
            gameState.stats.topics[q.topic].total++;
            if (isCorrect) gameState.stats.topics[q.topic].correct++;

            // Check for level up
            if (gameState.points >= gameState.level * 100) {
                gameState.level++;
                gameState.points = 0; // Reset points for next level challenge
                showMessage('Level UP!', `Selamat! Anda naik ke Level ${gameState.level}. Soal berikutnya akan lebih menantang!`);
            }

            // Check for new badge
            checkBadges();

            // Display feedback
            const feedbackArea = document.getElementById('feedback-area');
            const feedbackText = document.getElementById('feedback-text');
            feedbackText.innerHTML = `<span class="font-bold text-lg">${isCorrect ? '✅ Benar:' : '❌ Salah:'}</span><br>${q.explanation}`;
            feedbackArea.classList.remove('hidden');

            saveGameState();
        }

        /**
         * Lanjut ke soal berikutnya.
         */
        function nextQuestion() {
            gameState.isQuizActive = true;
            generateQuestion();
        }

        /**
         * Logika pemberian badge.
         */
        function checkBadges() {
            const badgesToCheck = [
                { name: 'Pemula Handal', condition: gameState.stats.correctAnswers >= 5, icon: '🌟' },
                { name: 'Master Teori', condition: gameState.level >= 3 && !gameState.badges.includes('Master Teori'), icon: '📚' },
                { name: 'Troubleshooter', condition: gameState.level >= 7 && !gameState.badges.includes('Troubleshooter'), icon: '🛠️' }
            ];

            badgesToCheck.forEach(badge => {
                if (badge.condition && !gameState.badges.includes(badge.name)) {
                    gameState.badges.push(badge.name);
                    showMessage('Badge Baru!', `Anda mendapatkan badge: ${badge.icon} ${badge.name}!`);
                }
            });
        }

        // --- FUNGSI RENDER UI ---

        /**
         * Render menu utama / mode selection.
         * @param {string} view 'menu' atau 'stats'
         */
        function renderMenu(view) {
            clearInterval(gameState.timer);
            window.speechSynthesis.cancel(); // Hentikan TTS saat pindah menu
            gameState.isQuizActive = false;
            const contentArea = document.getElementById('content-area');
            contentArea.innerHTML = ''; // Clear previous content

            if (view === 'stats') {
                renderStats();
                return;
            }

            const menuHtml = `
                <div class="card p-6 md:p-8 w-full max-w-2xl space-y-8 mb-20">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-3">Pilih Mode, Kesulitan & Jurusan</h2>

                    <!-- Statistik Singkat -->
                    <div class="flex justify-between items-center bg-green-50 p-4 rounded-xl shadow-inner">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-green-700">Level Kompetensi</p>
                            <span class="text-3xl font-extrabold text-green-600">${gameState.level}</span>
                        </div>
                        <div class="space-y-1 text-right">
                            <p class="text-sm font-semibold text-green-700">Poin Saat Ini</p>
                            <span class="text-xl font-bold text-gray-800">${gameState.points} XP</span>
                        </div>
                    </div>

                    <!-- Pemilihan Mode -->
                    <div class="space-y-4">
                        <label class="block text-lg font-semibold text-gray-700">Mode Game:</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button
                                onclick="selectMode('Belajar', this)"
                                class="mode-select-btn p-4 rounded-xl shadow-lg transition duration-150 ${gameState.selectedMode === 'Belajar' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}">
                                <span class="text-2xl block mb-1">🧠</span>
                                <span class="font-bold">Mode Belajar</span>
                                <span class="text-xs block mt-1 opacity-75">${gameState.selectedMode === 'Belajar' ? 'Aktif' : 'Tanpa batas waktu, ada penjelasan.'}</span>
                            </button>
                            <button
                                onclick="selectMode('Challenge', this)"
                                class="mode-select-btn p-4 rounded-xl shadow-lg transition duration-150 ${gameState.selectedMode === 'Challenge' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}">
                                <span class="text-2xl block mb-1">🔥</span>
                                <span class="font-bold">Mode Challenge</span>
                                <span class="text-xs block mt-1 opacity-75">${gameState.selectedMode === 'Challenge' ? 'Aktif' : 'Batas waktu 30 detik, skor bonus!'}</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Pemilihan Level Kesulitan (BARU) -->
                    <div class="space-y-4">
                        <label class="block text-lg font-semibold text-gray-700">Pilih Level Kesulitan:</label>
                        <div class="grid grid-cols-3 gap-3">
                            <button
                                onclick="selectDifficulty('Dasar (Teori)', this)"
                                class="difficulty-select-btn p-3 rounded-xl shadow-md transition duration-150 text-sm font-bold ${gameState.selectedDifficulty === 'Dasar (Teori)' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}">
                                🟢 Dasar
                            </button>
                            <button
                                onclick="selectDifficulty('Menengah (Prosedur)', this)"
                                class="difficulty-select-btn p-3 rounded-xl shadow-md transition duration-150 text-sm font-bold ${gameState.selectedDifficulty === 'Menengah (Prosedur)' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}">
                                🟡 Menengah
                            </button>
                            <button
                                onclick="selectDifficulty('Sulit (Studi Kasus)', this)"
                                class="difficulty-select-btn p-3 rounded-xl shadow-md transition duration-150 text-sm font-bold ${gameState.selectedDifficulty === 'Sulit (Studi Kasus)' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}">
                                🔴 Sulit
                            </button>
                        </div>
                    </div>


                    <!-- Pemilihan Jurusan/Topik (Menggunakan Select Box) -->
                    <div class="space-y-4">
                        <label for="topic-select" class="block text-lg font-semibold text-gray-700">Pilih Jurusan/Topik:</label>
                        <select
                            id="topic-select"
                            onchange="handleTopicChange(this.value)"
                            class="w-full p-3 border-2 border-gray-300 rounded-lg text-gray-700 focus:ring-green-500 focus:border-green-500 transition duration-150">
                            <option value="" disabled ${!gameState.selectedTopic ? 'selected' : ''}>-- Pilih Jurusan/Topik Kompetensi --</option>
                            ${TOPICS.map(topic => `
                                <option value="${topic.name}" ${gameState.selectedTopic === topic.name ? 'selected' : ''}>
                                    ${topic.icon} ${topic.name}
                                </option>
                            `).join('')}
                        </select>
                    </div>

                    <button id="start-quiz-btn" onclick="checkStart()" class="btn-primary w-full text-lg shadow-lg" disabled>
                        Mulai Kuis Sekarang!
                    </button>
                    <p id="start-message" class="text-center text-sm text-red-500 mt-2 ${!gameState.selectedTopic ? '' : 'hidden'}">Pilih Jurusan/Topik terlebih dahulu.</p>
                </div>
            `;
            contentArea.innerHTML = menuHtml;

            // Panggil kembali fungsi untuk mengaktifkan tombol jika topik sudah terpilih
            updateStartButtonState();
        }

        function selectMode(mode, element) {
            gameState.selectedMode = mode;
            document.querySelectorAll('.mode-select-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white', 'bg-red-500');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            });
            if (mode === 'Belajar') {
                element.classList.add('bg-blue-500', 'text-white');
            } else {
                element.classList.add('bg-red-500', 'text-white');
            }
            element.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            element.querySelector('.text-xs').textContent = 'Aktif';
            saveGameState();
        }

        /**
         * Menangani perubahan pilihan topik dari elemen select.
         * @param {string} topicName Nama topik yang dipilih.
         */
        function handleTopicChange(topicName) {
            gameState.selectedTopic = topicName;
            updateStartButtonState();
            saveGameState();
        }
        
        /**
         * Menangani perubahan pilihan level kesulitan.
         * @param {string} difficultyName Nama kesulitan yang dipilih (misal: 'Dasar (Teori)').
         * @param {HTMLElement} element Tombol yang diklik.
         */
        function selectDifficulty(difficultyName, element) {
            gameState.selectedDifficulty = difficultyName;
            document.querySelectorAll('.difficulty-select-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            });
            // Update the selected button's appearance
            element.classList.add('bg-indigo-600', 'text-white');
            element.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            saveGameState();
        }

        function updateStartButtonState() {
            const startBtn = document.getElementById('start-quiz-btn');
            const startMsg = document.getElementById('start-message');
            if (startBtn) {
                if (gameState.selectedTopic) {
                    startBtn.disabled = false;
                    startBtn.textContent = `Mulai Kuis ${gameState.selectedTopic} (${gameState.selectedMode}, ${gameState.selectedDifficulty.split(' ')[0]})`;
                    if (startMsg) startMsg.classList.add('hidden');
                } else {
                    startBtn.disabled = true;
                    startBtn.textContent = `Mulai Kuis Sekarang!`;
                    if (startMsg) startMsg.classList.remove('hidden');
                }
            }
        }

        function checkStart() {
            if (gameState.selectedTopic) {
                startQuiz();
            } else {
                showMessage('Pilih Jurusan', 'Anda harus memilih salah satu Jurusan/Topik untuk memulai kuis!');
            }
        }

        /**
         * Render tampilan statistik dan leaderboard.
         */
        function renderStats() {
            const contentArea = document.getElementById('content-area');

            // Hitung Akurasi Global
            const globalAccuracy = gameState.stats.totalQuestions > 0
                ? ((gameState.stats.correctAnswers / gameState.stats.totalQuestions) * 100).toFixed(1)
                : 0;

            const statsHtml = `
                <div class="card p-6 md:p-8 w-full max-w-2xl space-y-8 mb-20">
                    <h2 class="text-3xl font-bold text-gray-800 border-b pb-3 text-center">🏆 Papan Statistik Kompetensi</h2>

                    <!-- Ringkasan Global -->
                    <div class="grid grid-cols-3 gap-4 text-center bg-blue-50 p-4 rounded-xl shadow-inner">
                        <div>
                            <p class="text-4xl font-extrabold text-green-600">${gameState.level}</p>
                            <p class="text-sm text-gray-600">Level</p>
                        </div>
                        <div>
                            <p class="text-4xl font-extrabold text-blue-600">${gameState.stats.correctAnswers}</p>
                            <p class="text-sm text-gray-600">Jawaban Benar</p>
                        </div>
                        <div>
                            <p class="text-4xl font-extrabold text-red-600">${globalAccuracy}%</p>
                            <p class="text-sm text-gray-600">Akurasi</p>
                        </div>
                    </div>

                    <!-- Daftar Badge -->
                    <div class="space-y-3">
                        <h3 class="text-xl font-bold text-gray-700">🏷️ Badge Prestasi Anda</h3>
                        <div class="flex flex-wrap gap-3 p-3 bg-gray-50 rounded-lg border">
                            ${gameState.badges.length > 0
                                ? gameState.badges.map(badge => `
                                    <span class="badge-icon bg-yellow-400 text-white text-sm font-bold px-3 py-1 rounded-full shadow-md">${badge.icon || '🎖️'} ${badge.name}</span>
                                `).join('')
                                : '<p class="text-gray-500">Belum ada badge yang terkumpul. Mulai kuis!</p>'
                            }
                        </div>
                    </div>

                    <!-- Statistik Per Topik -->
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-700">📊 Analisis Area Kuat/Lemah</h3>
                        <div class="space-y-2">
                            ${TOPICS.map(topic => {
                                const topicStats = gameState.stats.topics[topic.name] || { total: 0, correct: 0 };
                                const topicAccuracy = topicStats.total > 0 ? ((topicStats.correct / topicStats.total) * 100).toFixed(1) : 0;
                                const barWidth = topicStats.total > 0 ? Math.max(1, topicAccuracy) : 0; // Minimal 1% untuk visual
                                const barColor = topicAccuracy >= 70 ? 'bg-green-500' : (topicAccuracy >= 40 ? 'bg-yellow-500' : 'bg-red-500');
                                return `
                                    <div class="p-3 border rounded-lg bg-white shadow-sm">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-medium text-gray-700">${topic.icon} ${topic.name}</span>
                                            <span class="text-sm font-semibold text-gray-500">${topicAccuracy}% (${topicStats.correct}/${topicStats.total})</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="${barColor} h-2.5 rounded-full" style="width: ${barWidth}%"></div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>

                    <button onclick="renderMenu('menu')" class="btn-primary w-full mt-4">Kembali ke Menu Utama</button>
                </div>
            `;
            contentArea.innerHTML = statsHtml;
        }

        // --- INTI APLIKASI ---

        /**
         * Memuat data dan merender tampilan awal.
         */
        function initApp() {
            loadGameState();
            initTts(); // Inisialisasi TTS saat aplikasi dimuat
            renderMenu('menu');
        }

        // Jalankan inisialisasi saat window dimuat
        window.onload = initApp;

    </script>
</body>
</html>
