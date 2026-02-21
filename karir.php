<?php

include "../admin/fungsi/koneksi.php";
$sql = mysqli_query($koneksi, "SELECT * FROM datasekolah");
$data = mysqli_fetch_assoc($sql);
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
    <title>Career Quest: Simulator Pilihan Karier</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827; /* Dark background */
            color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 900px;
            margin-top: 20px;
        }
        .card {
            background-color: #1f2937; /* Dark card background */
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        .history-item {
            border-left: 3px solid #6366f1;
            padding-left: 12px;
            margin-bottom: 15px;
        }
        .history-item-ai {
            border-left: 3px solid #10b981;
        }
        .skill-bar-bg {
            background-color: #374151;
        }
        .skill-bar-fill {
            transition: width 0.5s ease-in-out;
            background-color: #6366f1;
            height: 8px;
            border-radius: 9999px;
        }
        /* Custom scrollbar for history */
        #history {
            max-height: 450px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #6366f1 #1f2937;
        }
        #history::-webkit-scrollbar {
            width: 8px;
        }
        #history::-webkit-scrollbar-thumb {
            background-color: #6366f1;
            border-radius: 4px;
        }
        #history::-webkit-scrollbar-track {
            background-color: #1f2937;
        }
        /* Style for range input (slider) */
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #f87171; /* Red color for visibility */
            cursor: pointer;
            box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.3);
            transition: background 0.3s ease;
        }
        input[type=range]::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #f87171;
            cursor: pointer;
            box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.3);
        }

        /* Resume Styling for .txt preview */
        .resume-txt-preview {
            white-space: pre-wrap;
            font-family: monospace;
            padding: 15px;
            background-color: #0f172a; /* Darker background for text preview */
            border: 1px dashed #6366f1;
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Modal Transition Style */
        .modal-card {
            transition: all 0.3s ease-out;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Header dengan Tombol Tentang Game -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-4xl font-extrabold text-indigo-400">Career Quest 🚀</h1>
        <button id="about-button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            Tentang Game
        </button>
    </div>
    <p class="text-center text-gray-400 mb-10 -mt-4">Simulator Pilihan Karier. AI sebagai Game Master.</p>
    <!-- End Header -->

    <div id="game-container" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Panel Status (Kiri/Atas) -->
        <div class="lg:col-span-1 card p-5 h-full">
            <h2 class="text-2xl font-bold mb-4 text-indigo-300 border-b border-indigo-500 pb-2">Profil Karier</h2>
            
            <div id="status-display" class="space-y-4">
                <p class="text-lg font-medium">Bidang: <span id="current-field" class="font-normal text-teal-400">-</span></p>
                <p class="text-lg font-medium">Skenario: <span id="scenario-count" class="font-normal text-teal-400">0 / 5</span></p> 
                <div id="skill-scores" class="space-y-3 pt-3">
                    <!-- Skill Bars will be rendered here -->
                </div>
            </div>

            <!-- Level Kesulitan -->
            <div class="space-y-3 pt-6 mt-6 border-t border-gray-600">
                <h3 class="text-xl font-bold text-red-400">Level Kesulitan</h3>
                <p class="text-sm text-gray-400">Tingkat: <span id="difficulty-level-text" class="font-bold text-red-300">SMA/Diploma</span></p>
                <input type="range" id="difficulty-slider" min="1" max="10" value="5" class="w-full h-2 bg-gray-700 rounded-lg range-sm transition duration-200">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>SMK (1)</span>
                    <span>Mahasiswa (10)</span>
                </div>
            </div>
            <!-- Akhir Level Kesulitan -->

            <button id="reset-button" class="mt-8 w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200" style="display: none;">
                Mulai Ulang Game
            </button>
        </div>

        <!-- Alur Cerita & Keputusan (Kanan/Utama) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Area Histori Game -->
            <div class="card p-6">
                <h2 class="text-2xl font-bold mb-4 text-indigo-300 border-b border-indigo-500 pb-2">Log Interaksi</h2>
                <div id="history" class="text-gray-300 text-sm">
                    <!-- History content will be inserted here -->
                </div>
                <div id="loading-indicator" class="mt-4 text-center hidden">
                    <div class="animate-spin inline-block w-6 h-6 border-4 border-t-4 border-indigo-500 border-opacity-25 rounded-full"></div>
                    <span class="ml-3 text-indigo-400">GM sedang memproses keputusan...</span>
                </div>
            </div>

            <!-- Area Pilihan & Skenario -->
            <div id="gameplay-area" class="card p-6">
                <!-- NEW FLEX CONTAINER FOR TITLE AND TTS CONTROLS -->
                <div class="flex justify-between items-center mb-4 border-b border-indigo-500 pb-2">
                    <h2 class="text-2xl font-bold text-indigo-300">Skenario Aktif</h2>
                    <!-- TTS Controls -->
                    <div id="tts-controls" class="flex items-center space-x-3 text-sm">
                        <label class="flex items-center space-x-2 cursor-pointer text-gray-400 hover:text-indigo-300 transition duration-150">
                            <input type="checkbox" id="autoplay-toggle" class="form-checkbox h-4 w-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
                            <span>Autoplay</span>
                        </label>
                        <button id="tts-button" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-lg flex items-center transition duration-200">
                            <!-- Play Icon -->
                            <svg id="tts-icon-play" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4.004a1 1 0 001.555.832l3.224-2.002a1 1 0 000-1.664l-3.224-2.002z" clip-rule="evenodd"></path>
                            </svg>
                            <!-- Stop Icon (Hidden by default) -->
                            <svg id="tts-icon-stop" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 9a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                    <!-- End TTS Controls -->
                </div>
                <!-- END NEW FLEX CONTAINER -->

                <div id="scenario-text" class="text-lg mb-6 text-gray-200">
                    Selamat datang di Career Quest! Pilih bidang karier Anda untuk memulai simulasi:
                </div>
                <div id="choice-buttons" class="space-y-3">
                    <!-- Choice buttons will be inserted here -->
                </div>

                <!-- Bagian Input Nama dan Tombol Resume -->
                <div id="resume-input-area" class="mt-8 pt-6 border-t border-gray-600 hidden space-y-4">
                    <h3 class="text-xl font-bold text-teal-400">Permainan Selesai!</h3>
                    <p class="text-gray-300">Masukkan nama Anda untuk membuat dan mengunduh resume profesional berdasarkan pengalaman simulasi Anda:</p>
                    <input type="text" id="player-name-input" placeholder="Nama Lengkap Anda" 
                        class="w-full p-3 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    <!-- TOMBOL DOWNLOAD .TXT BARU -->
                    <button id="generate-resume-button" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 disabled:bg-green-400 disabled:cursor-not-allowed">
                        Download Resume (.txt)
                    </button>
                </div>
            </div>
            
            <!-- Bagian Resume Display (Preview TXT) -->
            <div id="resume-display-area" class="card hidden p-6">
                <h2 class="text-2xl font-bold mb-4 text-green-300 border-b border-green-500 pb-2">Preview Resume (TXT Format)</h2>
                <div id="resume-content" class="resume-txt-preview">
                    <!-- Resume rendered content goes here (plain text) -->
                </div>
                <div class="mt-6 text-center">
                    <button id="back-to-menu-button" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        Kembali ke Menu Utama
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Tentang Game -->
<div id="about-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300 hidden">
    <div class="card modal-card p-8 m-4 w-full max-w-lg space-y-4 transform scale-95 opacity-0">
        <h2 class="text-3xl font-bold text-teal-400 border-b border-teal-500 pb-2">Tentang Career Quest</h2>
        
        <div class="space-y-6 text-gray-300">
            <h3 class="text-xl font-semibold text-indigo-300">Keterangan</h3>
            <p><strong>Career Quest</strong> adalah simulator berbasis teks yang didukung oleh AI, dirancang untuk membantu Anda menjelajahi jalur karier potensial. Anda akan dihadapkan pada serangkaian skenario dan dilema profesional yang relevan dengan bidang yang Anda pilih. Setiap keputusan akan memengaruhi profil keahlian (skills) Anda secara real-time.</p>

            <h3 class="text-xl font-semibold text-indigo-300">Cara Bermain</h3>
            <ul class="list-disc pl-5 space-y-2">
                <li>Pilih <strong>Bidang Karier</strong> (Contoh: Teknik, Bisnis) dan <strong>Level Kesulitan</strong> (SMK hingga Profesional).</li>
                <li>Game Master (GM) akan memberikan skenario pertama.</li>
                <li>Pilih satu dari beberapa opsi jawaban yang tersedia.</li>
                <li>GM akan memberikan <strong>Feedback</strong> dan mengumumkan <strong>Perubahan Skill</strong> Anda (misalnya, Leadership +5).</li>
                <li>Setelah jumlah skenario yang ditentukan selesai, GM akan memberikan <strong>Analisis Akhir</strong> dan Anda dapat mengunduh **Resume Simulasi** Anda.</li>
            </ul>
        </div>

        <div class="pt-4 border-t border-gray-700">
             <p class="text-sm text-gray-400">Pengembang: <span class="text-teal-400 font-medium">Subuh Kurniawan</span></p>
        </div>

        <button id="close-modal-button" class="mt-6 w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition duration-200">
            Tutup
        </button>
    </div>
</div>
<!-- End Modal -->

<script type="module">
    // --- Konfigurasi Gemini API dan Game State ---
    const API_KEY = "<?php echo $apiKey; ?>"; // Kunci API akan disediakan oleh lingkungan Canvas
    const md = "<?php echo $model; ?>";
    const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${API_KEY}`;
    
    // FUNGSI BARU: Menentukan jumlah skenario berdasarkan level kesulitan
    function getDynamicMaxScenarios(level) {
        const difficulty = parseInt(level, 10);
        if (difficulty >= 8) return 8; // Mahasiswa/Profesional
        if (difficulty >= 4) return 6; // SMA/Diploma
        return 4; // SMK (Level 1-3)
    }

    const initialSkills = {
        Leadership: 0,
        Komunikasi: 0,
        Kreativitas: 0,
        ProblemSolving: 0,
        Adaptasi: 0,
    };

    const initialFields = [
        { key: 'Teknik', label: 'Teknik (IT, Otomotif, Listrik)' },
        { key: 'Bisnis', label: 'Bisnis (Manajemen, Marketing, Wirausaha)' },
        { key: 'Kreatif', label: 'Kreatif (Desain, Multimedia, Konten Digital)' },
    ];

    let gameState = {
        field: null,
        skills: { ...initialSkills },
        scenarioCount: 0,
        difficulty: 5, // Default level 5 (SMA/Diploma)
        finalAnalysisText: "", 
        loading: false,
    };

    // DOM Elements
    const historyEl = document.getElementById('history');
    const scenarioTextEl = document.getElementById('scenario-text');
    const choiceButtonsEl = document.getElementById('choice-buttons');
    const loadingIndicatorEl = document.getElementById('loading-indicator');
    const skillScoresEl = document.getElementById('skill-scores');
    const currentFieldEl = document.getElementById('current-field');
    const scenarioCountEl = document.getElementById('scenario-count');
    const resetBtn = document.getElementById('reset-button');
    const gameplayAreaEl = document.getElementById('gameplay-area');
    const resumeInputAreaEl = document.getElementById('resume-input-area');
    const playerNameInput = document.getElementById('player-name-input');
    const generateResumeBtn = document.getElementById('generate-resume-button');
    const resumeDisplayAreaEl = document.getElementById('resume-display-area');
    const resumeContentEl = document.getElementById('resume-content');
    const backToMenuBtn = document.getElementById('back-to-menu-button');
    
    // NEW MODAL ELEMENTS
    const aboutButton = document.getElementById('about-button');
    const aboutModal = document.getElementById('about-modal');
    const closeModalButton = document.getElementById('close-modal-button');
    const modalContent = aboutModal.querySelector('.modal-card'); 

    // Difficulty Elements
    const difficultySlider = document.getElementById('difficulty-slider');
    const difficultyTextEl = document.getElementById('difficulty-level-text');

    // --- TTS Elements and Logic ---
    const synth = window.speechSynthesis;
    let utterance = null;
    let isAutoplayEnabled = false; 
    const ttsButton = document.getElementById('tts-button');
    const autoplayToggle = document.getElementById('autoplay-toggle');
    const ttsIconPlay = document.getElementById('tts-icon-play');
    const ttsIconStop = document.getElementById('tts-icon-stop');
    let indonesianVoice = null;

    function setIndonesianVoice() {
        if (indonesianVoice) return;
        const voices = synth.getVoices();
        indonesianVoice = voices.find(voice => voice.lang.toLowerCase() === 'id-id') || 
                          voices.find(voice => voice.name.includes('Google') || voice.default);
        if (!indonesianVoice) {
            indonesianVoice = voices[0];
        }
    }
    if (synth.onvoiceschanged !== undefined) {
        synth.onvoiceschanged = setIndonesianVoice;
    }
    setIndonesianVoice(); 

    function updateTtsButton(isSpeaking) {
        if (!ttsButton) return;
        if (isSpeaking) {
            ttsIconPlay.classList.add('hidden');
            ttsIconStop.classList.remove('hidden');
            ttsButton.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            ttsButton.classList.add('bg-red-600', 'hover:bg-red-700'); 
        } else {
            ttsIconPlay.classList.remove('hidden');
            ttsIconStop.classList.add('hidden');
            ttsButton.classList.remove('bg-red-600', 'hover:bg-red-700');
            ttsButton.classList.add('bg-indigo-600', 'hover:bg-indigo-700'); 
        }
    }

    function stopSpeaking() {
        if (synth.speaking) {
            synth.cancel();
        }
        updateTtsButton(false);
    }

    function speakScenario(text) {
        stopSpeaking(); 
        if (!synth || !text) return;
        utterance = new SpeechSynthesisUtterance(text);
        
        if (indonesianVoice) {
            utterance.voice = indonesianVoice;
        } 
        utterance.lang = 'id-ID'; 
        utterance.rate = 1; 
        utterance.pitch = 1;
        utterance.onstart = () => updateTtsButton(true);
        utterance.onend = () => updateTtsButton(false);
        utterance.onerror = (event) => {
            console.error('SpeechSynthesisUtterance.onerror', event);
            updateTtsButton(false);
        };
        synth.speak(utterance);
    }

    if (ttsButton) {
        ttsButton.addEventListener('click', () => {
            if (synth.speaking) {
                stopSpeaking();
            } else {
                speakScenario(scenarioTextEl.textContent);
            }
        });
    }

    if (autoplayToggle) {
        isAutoplayEnabled = autoplayToggle.checked; 
        autoplayToggle.addEventListener('change', (e) => {
            isAutoplayEnabled = e.target.checked;
        });
    }
    // --- End TTS Logic ---

    // --- LOGIKA MODAL BARU ---
    function showModal() {
        aboutModal.classList.remove('hidden');
        // Trigger reflow to ensure transition starts
        void aboutModal.offsetWidth; 
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }

    function hideModal() {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        // Wait for transition to finish before hiding completely
        setTimeout(() => {
            aboutModal.classList.add('hidden');
        }, 300);
    }
    // --- END LOGIKA MODAL BARU ---

    // --- Logika Level Kesulitan ---
    const DIFFICULTY_MAP = {
        1: { name: 'SMK (Dasar)', description: 'Fokus pada dasar teknis, disiplin, dan kerja tim dasar. Konsekuensi kecil.' },
        5: { name: 'SMA/Diploma (Menengah)', description: 'Memerlukan koordinasi proyek kecil, manajemen waktu, dan tekanan tenggat waktu. Plot twist lebih sering.' },
        10: { name: 'Master/Eksekutif', description: 'Level tertinggi. Skenario krisis global/perusahaan, dilema moral yang ekstrem, dan konsekuensi berdampak luas.' },
    };
    
    function getDifficultyDescription(level) {
        const difficulty = parseInt(level, 10);
        if (difficulty <= 3) return DIFFICULTY_MAP[1].name;
        if (difficulty <= 7) return DIFFICULTY_MAP[5].name;
        return DIFFICULTY_MAP[10].name;
    }

    function updateDifficulty() {
        const level = parseInt(difficultySlider.value, 10);
        gameState.difficulty = level;
        difficultyTextEl.textContent = getDifficultyDescription(level);
        renderSkills(); // Update skill display to reflect new max scenarios
    }

    if (difficultySlider) {
        difficultySlider.addEventListener('input', updateDifficulty);
    }
    updateDifficulty();

    // --- Fungsi Helper UI/State ---

    function renderSkills() {
        skillScoresEl.innerHTML = '';
        const dynamicMaxScenarios = getDynamicMaxScenarios(gameState.difficulty); // Dapatkan max scenarios dinamis
        
        // Asumsi skor maksimum yang bisa dicapai per skill adalah 10 per skenario
        const maxScore = dynamicMaxScenarios * 10; 

        for (const skill in gameState.skills) {
            const score = gameState.skills[skill];
            const percentage = Math.min(100, (score / maxScore) * 100);

            const html = `
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-400">${skill}</span>
                        <span class="text-sm font-medium text-teal-400">${score}</span>
                    </div>
                    <div class="skill-bar-bg rounded-full h-2">
                        <div class="skill-bar-fill" style="width: ${percentage}%"></div>
                    </div>
                </div>
            `;
            skillScoresEl.insertAdjacentHTML('beforeend', html);
        }
        currentFieldEl.textContent = gameState.field || '-';
        scenarioCountEl.textContent = `${gameState.scenarioCount} / ${dynamicMaxScenarios}`; // Update display
    }

    function appendHistory(text, type = 'user') {
        const itemClass = type === 'user' ? 'history-item bg-indigo-900/20' : 'history-item history-item-ai bg-teal-900/20';
        const html = `
            <div class="${itemClass} p-3 rounded-md mb-2">
                <p class="font-bold text-sm text-gray-300">${type === 'user' ? 'Keputusan Anda:' : 'GM Feedback:'}</p>
                <p class="text-sm">${text}</p>
            </div>
        `;
        historyEl.insertAdjacentHTML('beforeend', html);
        historyEl.scrollTop = historyEl.scrollHeight;
    }

    function setLoading(isLoading) {
        gameState.loading = isLoading;
        loadingIndicatorEl.classList.toggle('hidden', !isLoading);
        choiceButtonsEl.querySelectorAll('button').forEach(btn => btn.disabled = isLoading);
        generateResumeBtn.disabled = isLoading; 
    }

    /** Mengurai respons AI dan memperbarui status */
    function parseAIResponse(text) {
        // 1. Dapatkan Feedback
        const feedbackMatch = text.match(/\[FEEDBACK\]\s*([\s\S]*?)(?=\s*\[UPDATE_SKILL\]|$)/i);
        const feedback = feedbackMatch ? feedbackMatch[1].trim() : "GM tidak memberikan feedback yang jelas.";

        // 2. Dapatkan Skill Updates
        const skillMatch = text.match(/\[UPDATE_SKILL\]\s*([\s\S]*?)(?=\s*\[NEXT_SCENARIO\]|\s*\[END_GAME\]|$)/i);
        const skillUpdatesText = skillMatch ? skillMatch[1].trim() : "Tidak ada perubahan skill yang terdeteksi.";

        // 3. Dapatkan Skenario Berikutnya/Analisis Akhir
        const endMatch = text.match(/\[END_GAME\]\s*([\s\S]*)/i);
        const scenarioMatch = text.match(/\[NEXT_SCENARIO\]\s*([\s\S]*)/i); 

        const nextScenario = scenarioMatch ? scenarioMatch[1].trim() : null;
        const finalAnalysis = endMatch ? endMatch[1].trim() : null;

        // Proses Skill Updates
        const updates = {};
        const skillRegex = /(Leadership|Komunikasi|Kreativitas|ProblemSolving|Adaptasi)\s*([+-]\s*\d+)/gi;
        let match;
        while ((match = skillRegex.exec(skillUpdatesText)) !== null) {
            const skillName = match[1];
            try {
                updates[skillName] = eval(match[2].replace(/\s/g, ''));
            } catch (e) {
                console.error("Gagal mengurai skill update:", match[2], e);
            }
        }

        // Terapkan Skill Updates
        let skillsChanged = false;
        for (const skill in updates) {
            gameState.skills[skill] = (gameState.skills[skill] || 0) + updates[skill];
            skillsChanged = true;
        }

        const skillLog = skillsChanged ? 
            Object.entries(updates).map(([skill, change]) => `${skill} ${change > 0 ? `+${change}` : change}`).join(', ') :
            "Tidak ada skill yang berubah.";
        
        appendHistory(`${feedback}\n\nPerubahan Skill: ${skillLog}`, 'ai');
        renderSkills();

        return {
            nextScenario,
            finalAnalysis,
        };
    }


    // --- Logika Inti Game Master (Gemini API) ---

    const getSystemInstruction = (field, difficultyLevel) => {
        const difficulty = getDifficultyDescription(difficultyLevel);
        const dynamicMaxScenarios = getDynamicMaxScenarios(difficultyLevel); // Dapatkan batas dinamis

        return `Anda adalah Game Master (GM) untuk simulator pilihan karier berbasis teks 'Career Quest' untuk siswa/mahasiswa di bidang ${field}.
Level Kesulitan saat ini adalah **${difficulty}** (Skor ${difficultyLevel}/10). Total skenario yang harus diselesaikan adalah **${dynamicMaxScenarios}** skenario.

1.  **Gaya Bahasa & Plot Twist:** Gunakan nada yang mendukung, realistis, dan profesional (Bahasa Indonesia). Skenario dan konsekuensi HARUS disesuaikan dengan Level Kesulitan. Skenario harus memiliki elemen plot twist, dilema etika, atau perubahan situasi yang tidak terduga untuk meningkatkan drama.
2.  **Konteks Game:** Pemain berada di bidang ${field} dan memiliki skor skill saat ini: ${JSON.stringify(gameState.skills)}.
3.  **Tugas:** Berikan feedback atas keputusan terakhir pemain, lalu berikan skenario berikutnya (kecuali permainan berakhir).
4.  **Struktur Respons Wajib:** Respons Anda HARUS mengikuti format yang ketat ini:

    [FEEDBACK]
    (Teks naratif tentang konsekuensi keputusan pemain dan dampaknya. Maksimal 3-4 kalimat.)
    [UPDATE_SKILL]
    (Daftar perubahan skill. Contoh: Leadership +5, Komunikasi -10. Besaran perubahan harus sesuai dengan tingkat kesulitan.)
    [NEXT_SCENARIO] / [END_GAME]

5.  **Instruksi [END_GAME]:** Gunakan tag [END_GAME] hanya jika **scenarioCount mencapai ${dynamicMaxScenarios} (skenario terakhir selesai)**. Jika Anda menggunakan [END_GAME], berikan analisis karier final yang mencakup: Ringkasan total skor skill, Rekomendasi 2-3 Profesi/Jalur Karier, dan 1-2 Saran Pengembangan Diri spesifik. **PENTING: JANGAN GUNAKAN KARAKTER MARKDOWN BOLD (seperti **) PADA TEKS ANALISIS AKHIR. GUNAKAN TEKS POLOS SAJA.**`
    };

    /** Memanggil API Gemini dengan exponential backoff (for text generation) */
    async function callGemini(userPrompt, field, retries = 0) {
        setLoading(true);
        const currentDifficulty = gameState.difficulty; 
        const payload = {
            contents: [{ parts: [{ text: userPrompt }] }],
            systemInstruction: {
                parts: [{ text: getSystemInstruction(field, currentDifficulty) }]
            },
        };

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const errorBody = await response.text();
                throw new Error(`HTTP error! status: ${response.status}. Body: ${errorBody}`);
            }
            const result = await response.json();
            const text = result.candidates?.[0]?.content?.parts?.[0]?.text;
            if (!text) { throw new Error("Respons dari AI kosong."); }
            setLoading(false);
            return text;

        } catch (error) {
            console.error("Gagal memanggil API:", error);
            if (retries < 3) {
                const delay = Math.pow(2, retries) * 1000;
                await new Promise(resolve => setTimeout(resolve, delay));
                return callGemini(userPrompt, field, retries + 1);
            }
            setLoading(false);
            scenarioTextEl.innerHTML = `<p class="text-red-400">Terjadi kesalahan fatal dalam koneksi Game Master. Silakan muat ulang halaman.</p>`;
            choiceButtonsEl.innerHTML = '';
            return null;
        }
    }


    /** Memanggil API Gemini untuk menghasilkan resume (JSON structured output) */
    async function callGeminiForResume(name, field, skills, finalAnalysis, retries = 0) {
        setLoading(true);
        
        const systemPrompt = `Anda adalah seorang Penulis Resume Profesional. Tugas Anda adalah membuat resume singkat berdasarkan simulasi karier yang dijalani oleh ${name}.
        Data yang Anda miliki:
        1. Bidang Fokus: ${field}
        2. Skor Skill Akhir: ${JSON.stringify(skills)}
        3. Analisis Akhir GM (Gunakan ini untuk referensi): ${finalAnalysis}

        Buatlah resume dalam Bahasa Indonesia yang formal dan profesional (maksimal 2-3 kalimat per detail). 
        - Isi 'jobTitle' dengan rekomendasi pekerjaan utama dari analisis GM.
        - Isi 'simulatedExperience' dengan 5 poin pencapaian berdasarkan skor skill tertinggi dan bidang fokus. JANGAN MENGULANG kata-kata dari analisis GM, tetapi gunakan data tersebut untuk membuat klaim profesional yang kredibel.
        - Isi 'education' secara umum (misalnya, "Lulusan SMK/SMA dengan spesialisasi [Bidang Fokus]").`;

        const payload = {
            contents: [{ parts: [{ text: systemPrompt }] }],
            generationConfig: {
                responseMimeType: "application/json",
                responseSchema: {
                    type: "OBJECT",
                    properties: {
                        "name": { "type": "STRING", description: "Nama Lengkap Pemain" },
                        "jobTitle": { "type": "STRING", description: "Jabatan profesional yang paling direkomendasikan" },
                        "summary": { "type": "STRING", description: "Ringkasan profesional 3-4 kalimat" },
                        "skillsProfile": {
                            "type": "ARRAY",
                            "items": { "type": "STRING" },
                            description: "Daftar 6-8 soft skill dan hard skill yang relevan berdasarkan skor"
                        },
                        "simulatedExperience": {
                            "type": "ARRAY",
                            "items": {
                                "type": "OBJECT",
                                "properties": {
                                    "title": { "type": "STRING", description: "Judul Proyek atau Posisi" },
                                    "details": { "type": "STRING", description: "Detail pencapaian/tanggung jawab (2-3 kalimat)" }
                                }
                            },
                            description: "5 poin pengalaman simulasi yang paling menonjol"
                        },
                        "education": { "type": "STRING", description: "Pendidikan terakhir yang relevan" }
                    }
                }
            }
        };

        const API_URL_JSON = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=${API_KEY}`;
        
        try {
            const response = await fetch(API_URL_JSON, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const errorBody = await response.text();
                throw new Error(`HTTP error! status: ${response.status}. Body: ${errorBody}`);
            }
            const result = await response.json();
            const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
            
            if (!jsonText) { throw new Error("Respons JSON kosong atau tidak terstruktur."); }
            
            setLoading(false);
            return JSON.parse(jsonText);

        } catch (error) {
            console.error("Gagal memanggil API JSON:", error);
            if (retries < 3) {
                const delay = Math.pow(2, retries) * 1000;
                await new Promise(resolve => setTimeout(resolve, delay));
                return callGeminiForResume(name, field, skills, finalAnalysis, retries + 1);
            }
            setLoading(false);
            // This is a final error state for resume generation
            const errorText = `❌ Gagal menghasilkan data resume setelah beberapa kali mencoba. Harap periksa koneksi atau coba lagi. (Error: ${error.message})`;
            resumeContentEl.textContent = errorText;
            resumeDisplayAreaEl.classList.remove('hidden');
            return null;
        }
    }

    // --- LOGIKA UNTUK KONVERSI DAN DOWNLOAD TXT ---

    /** Mengkonversi data JSON resume menjadi format teks biasa (.txt) */
    function convertJsonToPlainText(data) {
        let txt = '';

        // Header
        txt += `${data.name.toUpperCase()}\n`;
        txt += `\n`; // Tambahkan baris kosong agar format lebih rapi
        
        // Ringkasan
        txt += `RINGKASAN PROFIL PROFESIONAL\n`;
        txt += `------------------------------\n`;
        txt += `${data.summary}\n\n`;

        // Pengalaman Simulasi
        txt += `PENGALAMAN SIMULASI\n`;
        txt += `---------------------\n`;
        data.simulatedExperience.forEach(exp => {
            txt += `POSISI SIMULASI: ${exp.title.toUpperCase()}\n`;
            txt += `DETAIL:\n`;
            txt += `* ${exp.details}\n\n`;
        });

        // Pendidikan
        txt += `PENDIDIKAN\n`;
        txt += `------------\n`;
        txt += `${data.education}\n`;
        txt += `Bidang Fokus Simulasi: ${gameState.field}\n\n`;

        // Keahlian
        txt += `PROFIL KEAHLIAN\n`;
        txt += `-----------------\n`;
        txt += `* Jabatan Rekomendasi: ${data.jobTitle}\n`;
        txt += `* Keahlian Kunci: ${data.skillsProfile.join(', ')}\n`;
        txt += `\n`;
        txt += `SKOR AKHIR SIMULASI:\n`;
        Object.entries(gameState.skills).forEach(([skill, score]) => {
            txt += `* ${skill}: ${score}\n`;
        });
        txt += `\n`;
        txt += `CATATAN PENTING: Dokumen ini adalah hasil dari simulasi Career Quest dan harus diedit secara manual sebelum digunakan untuk aplikasi pekerjaan nyata.`;
        
        return txt;
    }
    
    /** Memicu unduhan file .txt */
    function triggerDownload(filename, text) {
        const element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
        element.setAttribute('download', filename);
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }
    
    /** Menampilkan skenario dan tombol pilihan */
    function renderScenario(scenarioText) {
        stopSpeaking(); 
        scenarioTextEl.textContent = scenarioText.trim();
        choiceButtonsEl.innerHTML = '';
        resumeInputAreaEl.classList.add('hidden'); // Sembunyikan input resume

        const optionRegex = /[A-D]\.\s*.*?(?=\s*[A-D]\.|$)/gs;
        const optionsRaw = scenarioText.match(optionRegex) || [];
        
        const optionsMap = optionsRaw.reduce((acc, current) => {
            const key = current.trim().charAt(0);
            const text = current.trim();
            acc[key] = text;
            return acc;
        }, {});

        if (Object.keys(optionsMap).length < 2) {
            // Akhir game
            
            // LOGIKA PEMBERSIH MARKDOWN
            let cleanedAnalysisText = gameState.finalAnalysisText
                .replace(/\*\*(.*?)\*\*/g, '$1') // Menghapus **bold**
                .replace(/\*(.*?)\*/g, '$1')     // Menghapus *italic*
                .replace(/__(.*?)__/g, '$1')    // Menghapus __underscore bold__
                .replace(/_(.*?)_/g, '$1');     // Menghapus _underscore italic_

            scenarioTextEl.innerHTML = `<h3 class="text-xl text-teal-300 font-bold mb-4">✨ SIMULASI SELESAI! ✨</h3><p>${cleanedAnalysisText.replace(/\n/g, '<br>')}</p>`;
            
            choiceButtonsEl.innerHTML = '';
            resetBtn.style.display = 'block'; // Tampilkan tombol reset
            resumeInputAreaEl.classList.remove('hidden'); // Tampilkan input resume
        } else {
            // Render tombol untuk setiap opsi
            Object.entries(optionsMap).forEach(([key, value]) => {
                const btn = document.createElement('button');
                btn.className = 'w-full text-left bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg transition duration-200';
                btn.textContent = value;
                btn.onclick = () => handleChoice(key, value);
                choiceButtonsEl.appendChild(btn);
            });
        }
        
        if (isAutoplayEnabled) {
            speakScenario(scenarioText.trim());
        } 
    }

    /** Menangani pilihan pemain dan memanggil AI untuk feedback/skenario berikutnya */
    async function handleChoice(choiceKey, choiceText) {
        if (gameState.loading) return;
        
        stopSpeaking(); 
        appendHistory(`Memilih opsi ${choiceKey}. ${choiceText}`, 'user');
        
        gameState.scenarioCount++;

        const dynamicMaxScenarios = getDynamicMaxScenarios(gameState.difficulty);
        const scenarioNumber = gameState.scenarioCount;
        const totalScenarios = dynamicMaxScenarios;
        const userPrompt = `Saya baru saja menghadapi Skenario #${scenarioNumber - 1}. Saya memilih opsi ${choiceKey} yang berbunyi: "${choiceText}". Berikan feedback dan konsekuensinya, lalu berikan Skenario #${scenarioNumber} berikutnya (atau analisis akhir jika ${scenarioNumber} adalah skenario terakhir, yaitu skenario ke ${totalScenarios}).`;
        
        const aiResponse = await callGemini(userPrompt, gameState.field);

        if (aiResponse) {
            const { nextScenario, finalAnalysis } = parseAIResponse(aiResponse);

            if (finalAnalysis) {
                gameState.finalAnalysisText = finalAnalysis; // Simpan analisis untuk resume
                renderScenario("Game Selesai! Klik tombol 'Download Resume' di bawah ini.");
            } else if (nextScenario) {
                renderScenario(nextScenario);
            } else {
                 console.error("AI Response was not structured correctly.");
                 scenarioTextEl.innerHTML = `<p class="text-red-400">Game Master mengalami gangguan teknis. Harap coba lagi atau mulai ulang.</p>`;
                 choiceButtonsEl.innerHTML = '';
                 resetBtn.style.display = 'block';
            }
        }
    }


    /** Fungsi untuk merender layar pemilihan awal */
    function renderFieldSelection() {
        stopSpeaking(); 
        
        gameState.field = null;
        gameState.scenarioCount = 0;
        gameState.skills = { ...initialSkills };
        gameState.finalAnalysisText = "";

        scenarioTextEl.textContent = 'Selamat datang di Career Quest! Pilih bidang karier Anda untuk memulai simulasi. Keputusan Anda akan menentukan profil akhir Anda.';
        choiceButtonsEl.innerHTML = '';
        resumeInputAreaEl.classList.add('hidden');
        resumeDisplayAreaEl.classList.add('hidden');
        gameplayAreaEl.classList.remove('hidden');
        historyEl.innerHTML = ''; // Clear history

        // 1. Tombol Bidang Standar
        initialFields.forEach(field => {
            const btn = document.createElement('button');
            btn.className = 'w-full text-left bg-teal-500 hover:bg-teal-600 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 text-lg';
            btn.textContent = field.label;
            btn.onclick = () => startGame(field.key);
            choiceButtonsEl.appendChild(btn);
        });

        // 2. Input Bidang Kustom
        const customDiv = document.createElement('div');
        customDiv.className = 'mt-6 pt-6 border-t border-gray-600 space-y-3';
        customDiv.innerHTML = `
            <h3 class="text-xl font-semibold text-indigo-400">Atau Tentukan Bidang Kustom</h3>
            <input type="text" id="custom-field-input" placeholder="Contoh: Energi Terbarukan, E-Sport Management" 
                class="w-full p-3 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            <button id="start-custom-button" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200 disabled:bg-indigo-400 disabled:cursor-not-allowed">
                Mulai dengan Bidang Kustom
            </button>
        `;
        choiceButtonsEl.appendChild(customDiv);

        const customInput = document.getElementById('custom-field-input');
        const startCustomBtn = document.getElementById('start-custom-button');

        const updateButtonState = () => {
            startCustomBtn.disabled = customInput.value.trim() === '';
        };

        customInput.addEventListener('input', updateButtonState);
        updateButtonState(); 

        startCustomBtn.onclick = () => {
            const customField = customInput.value.trim();
            if (customField) {
                startGame(customField);
            }
        };

        currentFieldEl.textContent = '-';
        scenarioCountEl.textContent = `0 / ${getDynamicMaxScenarios(gameState.difficulty)}`;
        // Initial skill rendering (scores = 0)
        renderSkills();
    }
    
    /** Fungsi inisialisasi game */
    function startGame(field) {
        gameState.field = field;
        gameState.scenarioCount = 0;
        gameState.skills = { ...initialSkills };
        gameState.finalAnalysisText = "";
        historyEl.innerHTML = '';
        resetBtn.style.display = 'none';
        gameplayAreaEl.classList.remove('hidden'); 
        resumeDisplayAreaEl.classList.add('hidden'); 
        resumeInputAreaEl.classList.add('hidden'); 
        stopSpeaking(); 

        const currentDifficulty = gameState.difficulty; 
        const dynamicMaxScenarios = getDynamicMaxScenarios(currentDifficulty);

        const initialPrompt = `Saya telah memilih bidang karier **${field}**. Level Kesulitan: ${getDifficultyDescription(currentDifficulty)} (${currentDifficulty}/10). Total Skenario yang dibutuhkan adalah ${dynamicMaxScenarios}. Berikan Skenario #1 yang pertama, relevan dengan bidang ini, dan sesuai dengan tingkat kesulitan yang dipilih. JANGAN sertakan tag [FEEDBACK] atau [UPDATE_SKILL]. HANYA sertakan tag [NEXT_SCENARIO]. Pastikan skenario ini mengandung plot twist awal.`;
        
        scenarioTextEl.textContent = `Selamat datang di Career Quest, calon profesional di bidang ${field} (Level ${getDifficultyDescription(currentDifficulty)}). Anda akan menghadapi ${dynamicMaxScenarios} skenario. Game Master sedang menyiapkan skenario pertama...`;
        choiceButtonsEl.innerHTML = '';
        
        callGemini(initialPrompt, field).then(aiResponse => {
            if (aiResponse) {
                const scenarioMatch = aiResponse.match(/\[NEXT_SCENARIO\]\s*([\s\S]*)/i);
                let firstScenario = scenarioMatch ? scenarioMatch[1].trim() : aiResponse.trim();
                
                const sanitizedScenario = firstScenario.replace(/\[NEXT_SCENARIO\]/gi, '').trim();

                gameState.scenarioCount = 1; 
                appendHistory(`Memulai di bidang ${field} dengan Level Kesulitan ${getDifficultyDescription(currentDifficulty)}. (Total: ${dynamicMaxScenarios} Skenario)`, 'user');
                
                const logText = sanitizedScenario.length > 150 ? sanitizedScenario.substring(0, 150).replace(/\n/g, ' ') + '...' : sanitizedScenario.replace(/\n/g, ' ');
                appendHistory(`GM: ${logText}`, 'ai');
                
                renderScenario(sanitizedScenario);
                renderSkills();
            }
        });
    }


    // --- Inisialisasi & Event Listeners ---
    document.addEventListener('DOMContentLoaded', () => {
        renderFieldSelection();
        resetBtn.addEventListener('click', renderFieldSelection);
        backToMenuBtn.addEventListener('click', renderFieldSelection);
        updateDifficulty(); 

        // Listener untuk tombol Download Resume
        generateResumeBtn.addEventListener('click', async () => {
            const name = playerNameInput.value.trim();
            if (!name) {
                playerNameInput.placeholder = "❌ HARAP MASUKKAN NAMA ANDA!";
                playerNameInput.focus();
                return;
            }

            // 1. Panggil fungsi generasi resume (mendapatkan JSON)
            const resumeData = await callGeminiForResume(
                name,
                gameState.field,
                gameState.skills,
                gameState.finalAnalysisText
            );

            if (resumeData) {
                // 2. Konversi JSON ke format TXT
                const plainTextResume = convertJsonToPlainText(resumeData);

                // 3. Tampilkan preview (opsional, tapi baik untuk UX)
                resumeContentEl.textContent = plainTextResume;
                gameplayAreaEl.classList.add('hidden');
                resumeDisplayAreaEl.classList.remove('hidden');

                // 4. Memicu unduhan file
                const filename = `${name.replace(/\s/g, '_')}_CareerQuest_Resume.txt`;
                triggerDownload(filename, plainTextResume);
            }
        });

        // Listener untuk mengaktifkan tombol Generate Resume saat input diisi
        playerNameInput.addEventListener('input', () => {
            generateResumeBtn.disabled = playerNameInput.value.trim() === '';
            if (playerNameInput.value.trim()) {
                 playerNameInput.placeholder = "Nama Lengkap Anda";
            }
        });
        generateResumeBtn.disabled = true; // Set awal disabled
        
        // Listener Modal
        if (aboutButton) aboutButton.addEventListener('click', showModal);
        if (closeModalButton) closeModalButton.addEventListener('click', hideModal);
        
        // Tutup modal saat klik di luar area card
        aboutModal.addEventListener('click', (e) => {
            if (e.target === aboutModal) {
                hideModal();
            }
        });

        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !aboutModal.classList.contains('hidden')) {
                hideModal();
            }
        });
    });
    
    // --- Penambahan: Hentikan TTS saat navigasi halaman (refresh/tutup tab) ---
    window.addEventListener('beforeunload', stopSpeaking);
    window.addEventListener('pagehide', stopSpeaking);

</script>

</body>
</html>
