<?php
session_start();
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
    <title>Tantangan Matematika Glassmorphism Terang Kustom</title>
    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        
        .font-inter { font-family: 'Inter', sans-serif; }

        /* Color Palette: Light & Elegant */
        :root {
            --bg-light: #F0F4F8; /* Soft Light Blue-Gray Background */
            --accent-color: #FF577F; /* Pink/Red Modern Accent (Untuk tema ceria) */
            --text-dark: #1F2937; /* Dark Charcoal Text */
            --status-correct: #34D399; /* Green Accent */
            --status-wrong: #F87171; /* Red Accent */
            --glass-border: rgba(255, 255, 255, 0.6);
            --glass-bg: rgba(255, 255, 255, 0.4);
        }

        /* Background - Soft Light */
        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
            background-image: linear-gradient(135deg, #FFDDE1 0%, #FDFBFB 100%);
        }

        /* Overlay styles for full-screen loading */
        #full-screen-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9); /* Light semi-transparent white */
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 1000; /* Ensure it's on top */
            display: none; /* Hidden by default, controlled by JS */
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
            transition: opacity 0.3s ease;
        }

        /* Glassmorphism Effect */
        .glass-card {
            background-color: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            transition: all 0.3s ease;
        }
        
        /* Main Application Container */
        #app {
            max-width: 1100px;
            height: 90vh;
            min-height: 600px;
            overflow: hidden;
        }

        /* Score Display (Glass) */
        .score-box {
            background-color: rgba(255, 255, 255, 0.5);
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Buttons & Inputs - General Style */
        .game-btn {
            transition: background-color 0.2s, transform 0.1s, box-shadow 0.2s;
            border-radius: 12px;
            font-weight: 600;
        }
        .game-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Choice Button Styling (Main Content) */
        .choice-btn {
            padding: 1.25rem;
            background-color: rgba(255, 255, 255, 0.7); /* Slightly more opaque glass */
            color: var(--text-dark);
            font-size: 1.125rem; /* text-lg */
            border: 1px solid var(--glass-border);
        }
        .choice-btn:hover {
            background-color: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        /* Question Display - Clean Focus */
        #question-display {
            min-height: 7rem; 
            border-bottom: 3px solid var(--accent-color);
            padding-bottom: 1rem;
            transition: border-bottom-color 0.5s ease;
        }
        
        /* Style for Selects and Inputs */
        .form-select, .form-input {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%236B7280'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 0.8em;
        }

        /* Focus Ring Color */
        .focus\:ring-accent-color:focus {
            --tw-ring-color: var(--accent-color);
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- FULL SCREEN LOADING OVERLAY -->
    <div id="full-screen-overlay" class="hidden font-inter" style="color: var(--accent-color);">
        <svg class="animate-spin h-10 w-10 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-2xl font-semibold">Master Game sedang merangkai tantangan matematis...</p>
        <p class="text-lg text-gray-600 mt-2">Tunggu sebentar ya...</p>
    </div>

    <!-- Kontainer Aplikasi Utama (Glass Card) -->
    <div id="app" class="w-full glass-card p-8 flex flex-col space-y-8">
        
        <!-- HEADER: Judul & Score Status -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-4 mb-4 border-gray-300">
            <div class="mb-4 md:mb-0">
                <h1 class="text-4xl font-extrabold tracking-tight" style="color: var(--accent-color);">M. MASTER KUSTOM</h1>
                <p class="text-sm text-gray-600 mt-1">Ukur Kecerdasanmu Sesuai Tema Pilihan!</p>
            </div>
            
            <!-- Score Boxes -->
            <div class="flex space-x-4 w-full md:w-auto">
                <div class="score-box flex-1">
                    <p class="text-xs uppercase text-gray-500 font-semibold">Skor Saat Ini</p>
                    <span id="score" class="text-4xl font-extrabold" style="color: var(--accent-color);">0</span>
                </div>
                <div class="score-box flex-1">
                    <p class="text-xs uppercase text-gray-500 font-semibold">Rekor Tertinggi</p>
                    <span id="high-score" class="text-2xl font-bold text-red-500">0</span>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-grow flex flex-col overflow-y-auto">
            
            <!-- Master Game Message Area (Frosty Glass Box) -->
            <div id="game-master-message-area" class="glass-card p-4 mb-6">
                <p class="text-lg font-bold" style="color: var(--accent-color);">Master Game Says:</p>
                <div class="mt-2 text-gray-700">
                    <p id="game-master-message" class="italic">
                        Pilih level dan tema untuk memulai tantangan unik!
                    </p>
                </div>
            </div>

            <!-- Area Pemilih Level dan Tema (Tampil di awal) -->
            <div id="level-selector" class="flex-grow flex flex-col items-center justify-center space-y-8 p-4">
                <p class="text-3xl font-bold text-center text-gray-700">Atur Tantangan Anda</p>
                <div class="w-full max-w-md space-y-4 glass-card p-6">
                    <!-- Pilihan Level -->
                    <div>
                        <label for="difficulty-select" class="block text-lg font-semibold mb-2">Tingkat Kesulitan (Level)</label>
                        <select id="difficulty-select" class="form-select w-full py-3 px-4 rounded-lg border-2 border-gray-300 focus:ring-accent-color focus:border-accent-color text-lg bg-white/70">
                            <option value="SD">SD (Sekolah Dasar)</option>
                            <option value="SMP">SMP (Sekolah Menengah Pertama)</option>
                            <option value="SMA/SMK">SMA/SMK (Sekolah Menengah Atas/Kejuruan)</option>
                        </select>
                    </div>

                    <!-- Pilihan Tema -->
                    <div>
                        <label for="theme-select" class="block text-lg font-semibold mb-2 mt-4">Pilih Tema Narasi</label>
                        <select id="theme-select" class="form-select w-full py-3 px-4 rounded-lg border-2 border-gray-300 focus:ring-accent-color focus:border-accent-color text-lg bg-white/70">
                            <option value="Kocak, gombal, dan keceriaan anak muda">Kocak / Gombal (Default)</option>
                            <option value="Pahlawan Super dan Kekuatan Ajaib">Pahlawan Super</option>
                            <option value="Petualangan Bajak Laut dan Harta Karun">Bajak Laut</option>
                            <option value="Fiksi Ilmiah dan Robot">Fiksi Ilmiah</option>
                            <option value="Kustom">Kustom (Tentukan Sendiri)</option>
                        </select>
                    </div>

                    <!-- Input Kustom (Tersembunyi Awal) -->
                    <div id="custom-theme-area" style="display: none;" class="mt-4">
                        <label for="custom-theme-input" class="block text-sm font-medium mb-1 text-gray-600">Masukkan Tema Kustom Anda:</label>
                        <input type="text" id="custom-theme-input" placeholder="Misalnya: Drama Korea Romantis" class="form-input w-full py-3 px-4 rounded-lg border-2 border-gray-300 focus:ring-accent-color focus:border-accent-color bg-white/70">
                    </div>

                    <!-- Tombol Mulai Game -->
                    <button id="start-game-btn" class="game-btn w-full py-4 text-xl font-bold mt-6 text-white" style="background-color: var(--accent-color);">
                        Mulai Tantangan!
                    </button>
                </div>
            </div>

            <!-- Area Soal dan Jawaban (Tersembunyi di awal) -->
            <div id="game-area" class="space-y-8 flex-grow flex flex-col p-4" style="display: none;">
                
                <!-- Soal Matematika -->
                <div id="question-display" class="w-full text-left font-inter flex items-center justify-start min-h-24">
                    <p class="w-full text-3xl font-extrabold text-gray-800">... Menunggu Soal ...</p>
                </div>

                <!-- Opsi Jawaban (Multiple Choice) -->
                <div id="choices-container" class="grid grid-cols-2 gap-4">
                    <!-- Tombol pilihan akan dimasukkan di sini oleh JavaScript -->
                </div>
                
            </div>
            
            
        </div>
        
        <!-- FOOTER / ACTION BUTTON -->
        <div class="border-t pt-4 mt-4 border-gray-300 flex justify-center" id="action-button-area" style="display: none;">
            <button id="restart-btn" class="game-btn bg-gray-500 hover:bg-gray-700 text-white py-2 px-6 text-md">
                Pilih Level Baru / Mulai Ulang
            </button>
        </div>
        
    </div>
    
    <!-- FEEDBACK MODAL (Gaya Glassmorphism) DIHILANGKAN -->

    <script>
        // Data Game State
        let currentScore = 0;
        let highScore = 0;
        let currentQuestion = "";
        let currentCorrectAnswer = null; 
        let isGameStarted = false;
        let currentLevel = 0; // Melacak level progression
        let selectedDifficulty = null; 
        let selectedTheme = null; // Menyimpan tema yang dipilih
        
        // Data sementara untuk pertanyaan berikutnya tidak diperlukan lagi
        
        // Elemen UI
        const scoreElement = document.getElementById('score');
        const highScoreElement = document.getElementById('high-score');
        const gmMessageElement = document.getElementById('game-master-message');
        const questionDisplayElement = document.getElementById('question-display').querySelector('p');
        const choicesContainer = document.getElementById('choices-container');
        const levelSelector = document.getElementById('level-selector');
        const gameArea = document.getElementById('game-area');
        const loadingOverlay = document.getElementById('full-screen-overlay');
        const restartButton = document.getElementById('restart-btn');
        const actionButtonArea = document.getElementById('action-button-area');
        
        // Elemen Form
        const difficultySelect = document.getElementById('difficulty-select');
        const themeSelect = document.getElementById('theme-select');
        const customThemeArea = document.getElementById('custom-theme-area');
        const customThemeInput = document.getElementById('custom-theme-input');
        const startGameButton = document.getElementById('start-game-btn');
        
        // Elemen Modal DIHILANGKAN (modalOkButton, dll.)


        // Warna status untuk feedback visual (diambil dari CSS root variables)
        const STATUS_CORRECT_COLOR = 'var(--status-correct)';
        const STATUS_WRONG_COLOR = 'var(--status-wrong)';
        const DEFAULT_ACCENT_COLOR = 'var(--accent-color)';


        // URL API dan Kunci
        const MODEL_NAME = "gemini-2.5-flash-preview-09-2025"; 
        const API_KEY = "<?php echo $apiKey; ?>"; // Disediakan oleh lingkungan Canvas
        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${MODEL_NAME}:generateContent?key=${API_KEY}`;

        // Konfigurasi Master Game (System Instruction)
        const SYSTEM_INSTRUCTION = {
            parts: [{ 
                text: "Anda adalah Master Game yang energik, karismatik, dan selalu sesuai dengan tema yang diberikan. Anda bertanggung jawab penuh untuk memberikan soal, memeriksa jawaban, dan memperbarui skor. Soal harus UNIK, berupa NARASI, dan HARUS BERDASARKAN TEMA yang diberikan pengguna (misalnya: 'Kocak/Gombal', 'Pahlawan Super', 'Drama Korea'). Soal harus melibatkan ALGEBRA, ARITMETIKA, atau GEOMETRI sederhana, dan jawabannya harus berupa bilangan bulat positif. PENTING: Untuk 'feedbackMessage', SELALU sertakan deskripsi KONSEKUENSI NARATIF atau hasil mini-cerita dari jawaban yang diberikan, sesuai dengan tema yang sedang berjalan. Berikan respons Anda dalam format JSON terstruktur untuk menjaga alur game."
            }]
        };

        // Skema Respons JSON untuk Game State Update
        const RESPONSE_SCHEMA = {
            type: "OBJECT",
            properties: {
                "feedbackMessage": { "type": "STRING", "description": "Pesan dari Master Game (selamat/koreksi) berdasarkan jawaban pengguna. HARUS LUCU/RAMAH/SESUAI TEMA yang dipilih, dan MENGANDUNG KONSEKUENSI NARATIF jawaban." },
                "newScore": { "type": "NUMBER", "description": "Skor yang diperbarui setelah putaran ini. Tambahkan 10 jika benar, kurangi 5 jika salah (minimal 0)." },
                "nextQuestion": { "type": "STRING", "description": "Soal matematika naratif baru yang sesuai tema dan level kesulitan. Gunakan bahasa yang santai dan ceria." },
                "isCorrect": { "type": "BOOLEAN", "description": "True jika jawaban pengguna benar, False jika salah." },
                "choices": { 
                    "type": "ARRAY",
                    "description": "Daftar 4 opsi jawaban dalam bentuk string (termasuk jawaban yang benar).",
                    "items": { "type": "STRING" }
                },
                "correctAnswer": { 
                    "type": "NUMBER",
                    "description": "Nilai numerik jawaban yang benar untuk soal yang baru digenerasi."
                }
            },
            propertyOrdering: ["feedbackMessage", "newScore", "nextQuestion", "isCorrect", "choices", "correctAnswer"]
        };

        // --- Fungsi localStorage untuk Skor Tertinggi ---
        function loadHighScore() {
            const storedScore = localStorage.getItem('math_challenge_high_score');
            highScore = storedScore ? parseInt(storedScore, 10) : 0;
            highScoreElement.textContent = highScore;
        }

        function saveHighScore(score) {
            if (score > highScore) {
                highScore = score;
                localStorage.setItem('math_challenge_high_score', score);
                highScoreElement.textContent = highScore;
                return true;
            }
            return false;
        }

        /**
         * Mengatur status UI loading dan tombol.
         * @param {boolean} isLoading 
         */
        function setLoading(isLoading) {
            // Disables game choices and control buttons
            choicesContainer.querySelectorAll('button').forEach(btn => {
                btn.disabled = isLoading;
            });
            
            restartButton.disabled = isLoading;
            startGameButton.disabled = isLoading;
            
            // Disables selectors
            difficultySelect.disabled = isLoading;
            themeSelect.disabled = isLoading;
            customThemeInput.disabled = isLoading;


            if (isLoading) {
                loadingOverlay.classList.remove('hidden'); 
                loadingOverlay.style.display = 'flex'; // Tampilkan sebagai flex untuk centering
            } else {
                loadingOverlay.classList.add('hidden'); 
                loadingOverlay.style.display = 'none'; // Sembunyikan
            }
        }

        /**
         * Memanggil Gemini API dengan payload tertentu.
         */
        async function callGeminiAPI(userPrompt, structured = false) {
            const payload = {
                contents: [{ parts: [{ text: userPrompt }] }],
                systemInstruction: SYSTEM_INSTRUCTION,
            };

            if (structured) {
                payload.generationConfig = {
                    responseMimeType: "application/json",
                    responseSchema: RESPONSE_SCHEMA
                };
            }

            let lastDelay = 1000;
            const maxRetries = 3;

            for (let i = 0; i < maxRetries; i++) {
                try {
                    // --- DEBUG LOGS START ---
                    console.log(`[DEBUG] Attempt ${i + 1}. Calling URL: ${API_URL}`);
                    console.log("[DEBUG] Payload:", JSON.stringify(payload, null, 2));
                    // --- DEBUG LOGS END ---
                    
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 429 && i < maxRetries - 1) {
                        await new Promise(resolve => setTimeout(resolve, lastDelay));
                        lastDelay *= 2;
                        continue; 
                    }

                    if (!response.ok) {
                        const errorText = await response.text();
                        let errorData = {};
                        try {
                            errorData = JSON.parse(errorText);
                        } catch(e) { /* Abaikan jika body error bukan JSON */ }
                        
                        throw new Error(`API call failed: ${response.status} ${response.statusText}. Details: ${JSON.stringify(errorData)}`);
                    }

                    const responseText = await response.text();
                    if (!responseText) {
                        throw new Error("API returned an empty response body.");
                    }
                    
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (e) {
                        console.error("Failed to parse API response as JSON. Raw response:", responseText);
                        throw new Error("API returned invalid JSON format at top level.");
                    }
                    
                    const candidate = result.candidates?.[0];
                    if (candidate && candidate.content?.parts?.[0]?.text) {
                        const text = candidate.content.parts[0].text;
                        
                        if (structured) {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error("Failed to parse model's structured response. Raw text:", text);
                                throw new Error("Model generated malformed JSON: " + e.message);
                            }
                        } else {
                            return text;
                        }
                    } else {
                        throw new Error("Respons API valid, tetapi konten (candidates.parts) kosong atau tidak terstruktur.");
                    }
                } catch (error) {
                    console.error(`Attempt ${i + 1} failed:`, error);
                    if (i === maxRetries - 1) throw error;
                    
                    await new Promise(resolve => setTimeout(resolve, lastDelay));
                    lastDelay *= 2;
                }
            }
            throw new Error("Gagal memanggil Gemini API setelah beberapa kali percobaan.");
        }

        /**
         * Membuat tombol-tombol pilihan jawaban dari data yang dikembalikan AI.
         */
        function renderChoices(choices) {
            choicesContainer.innerHTML = '';
            choices.forEach((choice, index) => {
                const button = document.createElement('button');
                const choiceLabel = String.fromCharCode(65 + index); 
                
                button.textContent = `${choiceLabel}. ${choice}`;
                button.classList.add('choice-btn', 'game-btn', 'glass-card'); 
                button.setAttribute('data-answer', choice); 
                button.addEventListener('click', () => submitAnswer(choice));
                choicesContainer.appendChild(button);
            });
        }

        // Fungsi showFeedbackModal dan continueGameFlow DIHILANGKAN


        /**
         * Memulai game dan mengambil soal pertama berdasarkan kesulitan dan tema yang dipilih.
         */
        async function startGame() {
            // Dapatkan nilai dari select dan input
            const difficulty = difficultySelect.value;
            let theme = themeSelect.value;
            
            if (theme === 'Kustom') {
                theme = customThemeInput.value.trim();
                if (!theme) {
                    gmMessageElement.textContent = "Master Game: Pilih tema atau masukkan tema kustom terlebih dahulu!";
                    return;
                }
            }

            // Simpan ke state global
            selectedDifficulty = difficulty;
            selectedTheme = theme;


            if (isGameStarted) return;
            isGameStarted = true;
            currentScore = 0;
            currentLevel = 1; // Mulai di level 1
            updateScoreDisplay();
            
            levelSelector.style.display = 'none';
            gameArea.style.display = 'flex'; 
            actionButtonArea.style.display = 'flex';
            
            setLoading(true);
            gmMessageElement.textContent = `Master Game: Persiapan tantangan Level ${currentLevel} (${selectedDifficulty}) dengan tema *${selectedTheme}*...`;
            
            try {
                // Prompt sekarang mencakup level
                const userPrompt = `Level ${currentLevel} dimulai dengan tingkat kesulitan ${selectedDifficulty} dan tema NARASI ${selectedTheme}! Berikan soal matematika yang sesuai dengan kurikulum ${selectedDifficulty} dan tema ${selectedTheme}, 4 opsi jawaban, dan jawaban numerik yang benar. Isi 'newScore' dengan 0, 'isCorrect' dengan False, dan 'feedbackMessage' dengan pesan sambutan yang lucu/gombal/sesuai tema.`;
                
                const responseData = await callGeminiAPI(userPrompt, true);

                if (responseData && responseData.nextQuestion && responseData.choices && responseData.correctAnswer !== undefined) {
                    currentQuestion = responseData.nextQuestion;
                    currentCorrectAnswer = responseData.correctAnswer;
                    
                    questionDisplayElement.textContent = currentQuestion;
                    // Gunakan innerHTML untuk pesan sambutan awal
                    gmMessageElement.innerHTML = responseData.feedbackMessage || `Master Game: Selamat datang di Level ${currentLevel} tema ${selectedTheme}! Mari kita mulai tantangan Anda!`;
                    
                    renderChoices(responseData.choices);

                } else {
                    throw new Error("Gagal mendapatkan soal pertama atau data tidak lengkap.");
                }

            } catch (error) {
                console.error("Kesalahan saat memulai game:", error);
                gmMessageElement.textContent = `Master Game: ERROR! Terjadi kesalahan koneksi. Coba lagi. Detail: ${error.message}`;
                resetToLevelSelector();
            } finally {
                setLoading(false); // Matikan loading screen saat game dimulai/gagal
            }
        }

        /**
         * Mengirim jawaban pengguna dan memproses hasilnya, lalu langsung memuat soal berikutnya.
         */
        async function submitAnswer(selectedChoice) {
            if (!isGameStarted || !selectedDifficulty) return;
            
            // 1. Tampilkan loading dan update pesan
            setLoading(true); 
            gmMessageElement.textContent = `Master Game: Sedang memeriksa jawaban Anda di Level ${currentLevel}. Menunggu tantangan berikutnya... (Tema: ${selectedTheme})...`;
            
            const nextLevelPrompt = currentLevel + 1;
            const userPrompt = `Soal sebelumnya: ${currentQuestion}. Jawaban pengguna (dipilih): ${selectedChoice}. Jawaban yang benar (secara numerik): ${currentCorrectAnswer}. Skor saat ini: ${currentScore}. Tingkat kesulitan saat ini: ${selectedDifficulty}. TEMA NARASI saat ini: ${selectedTheme}. Periksa jawaban, perbarui skor (tambah 10 jika benar, kurangi 5 jika salah, skor minimum 0), dan berikan soal NARASI BARU untuk Level ${nextLevelPrompt} yang sesuai tema dan level kesulitan yang progresif.`;

            try {
                const responseData = await callGeminiAPI(userPrompt, true);
                
                if (responseData && responseData.nextQuestion && responseData.choices && responseData.correctAnswer !== undefined) {
                    
                    const isCorrect = responseData.isCorrect;
                    
                    // 2. Update state game
                    const updatedLevel = isCorrect ? currentLevel + 1 : currentLevel;
                    const newScore = Math.max(0, responseData.newScore);
                    
                    currentQuestion = responseData.nextQuestion;
                    currentCorrectAnswer = responseData.correctAnswer;
                    currentScore = newScore;
                    currentLevel = updatedLevel;
                    
                    // 3. Update High Score dan tampilan skor
                    const isNewHighScore = saveHighScore(currentScore);
                    updateScoreDisplay();
                    
                    let feedback = responseData.feedbackMessage;
                    
                    // 4. Update Master Game Message (tampilkan feedback + status di area pesan)
                    let statusMessage = '';
                    if (isNewHighScore) {
                        statusMessage = "REKOR TERTINGGI BARU!";
                    } else if (isCorrect) {
                        statusMessage = `+10 Berhasil! Lanjut Level ${currentLevel}!`;
                    } else {
                        statusMessage = `-5 Gagal, Tetap di Level ${currentLevel}.`;
                    }
                    
                    gmMessageElement.innerHTML = `**${isCorrect ? 'BENAR!' : 'SALAH!'}** (${statusMessage})<br>${feedback}`;

                    // 5. Render pertanyaan berikutnya segera
                    questionDisplayElement.textContent = currentQuestion;
                    renderChoices(responseData.choices);

                } else {
                    throw new Error("Respons Master Game tidak valid atau data soal baru tidak lengkap.");
                }

            } catch (error) {
                console.error("Kesalahan saat mengirim jawaban:", error);
                gmMessageElement.textContent = `Master Game: ERROR! Waduh, ada yang salah dengan koneksi (Error Data). Detail: ${error.message}`;
            } finally {
                setLoading(false); // Matikan loading screen
            }
        }


        /**
         * Mengatur ulang game kembali ke tampilan pemilih level.
         */
        function resetToLevelSelector() {
            isGameStarted = false;
            currentLevel = 0; // Reset level
            selectedDifficulty = null;
            selectedTheme = null;
            currentScore = 0;
            updateScoreDisplay();
            gmMessageElement.textContent = "Pilih level dan tema Anda untuk memulai petualangan matematika!";
            questionDisplayElement.textContent = "... Menunggu Soal ...";
            choicesContainer.innerHTML = '';
            
            levelSelector.style.display = 'flex';
            gameArea.style.display = 'none';
            actionButtonArea.style.display = 'none';
            
            // Pastikan loading screen mati
            loadingOverlay.classList.add('hidden');
        }

        /**
         * Memperbarui tampilan skor.
         */
        function updateScoreDisplay() {
            scoreElement.textContent = currentScore;
        }
        
        /**
         * Menangani perubahan pilihan tema untuk menampilkan/menyembunyikan input kustom.
         */
        function handleThemeChange() {
            if (themeSelect.value === 'Kustom') {
                customThemeArea.style.display = 'block';
            } else {
                customThemeArea.style.display = 'none';
                customThemeInput.value = ''; // Kosongkan input kustom saat tidak digunakan
            }
        }


        // --- Inisialisasi ---
        window.onload = function() {
            loadHighScore(); 
            
            // 1. Tambahkan listener untuk menampilkan input kustom
            themeSelect.addEventListener('change', handleThemeChange);

            // 2. Tambahkan listener ke tombol mulai game baru
            startGameButton.addEventListener('click', startGame);

            // 3. Tambahkan listener ke tombol restart
            restartButton.addEventListener('click', resetToLevelSelector);
            
            // Listener untuk modal DIHILANGKAN
            
            // Panggil sekali saat dimuat untuk mengatur tampilan awal input kustom
            handleThemeChange();
        };

    </script>
</body>
</html>
