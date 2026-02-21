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
    <title>Mystery Case: Investigasi Industri</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for Inter font and general aesthetics */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
            min-height: 100vh;
        }
        /* Custom scrollbar for a cleaner look */
        .case-history::-webkit-scrollbar {
            width: 8px;
        }
        .case-history::-webkit-scrollbar-thumb {
            background-color: #a0aec0;
            border-radius: 4px;
        }
        .case-history::-webkit-scrollbar-track {
            background-color: #edf2f7;
        }
        /* Style for interactive buttons */
        .action-button {
            transition: all 0.1s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }
        .action-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    </style>
    <!-- Use Inter font -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#1d4ed8', /* blue-700 */
                        'secondary': '#f97316', /* orange-600 */
                    }
                }
            }
        }
    </script>
</head>
<body class="p-4 md:p-8">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-2xl overflow-hidden">
       <header 
    class="relative text-white p-8 md:p-10 rounded-t-xl flex flex-col md:flex-row items-center md:items-start md:justify-between gap-6 overflow-hidden"
    style="background-image: url('../admin/foto/banner.jpg'); background-size: cover; background-position: center; min-height: 260px;">
    
    <!-- Overlay lembut -->
    <div class="absolute inset-0 bg-primary/60 backdrop-blur-[1px]"></div>

    <!-- Kiri: Logo & Nama Sekolah -->
    <div class="relative z-10 flex items-center gap-5">
        <img src="../admin/foto/<?= $data['logo'] ?>" alt="Logo Sekolah" class="w-20 h-20 rounded-lg shadow-lg border border-white/30">
        <div>
            <h2 class="text-2xl font-semibold leading-tight drop-shadow-md"><?= $data['nama'] ?></h2>
            <p class="text-primary-100 text-sm"><?= $data['deskripsi'] ?></p>
        </div>
    </div>

    <!-- Kanan: Judul Halaman -->
    <div class="relative z-10 text-center md:text-right">
        <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-md">Mystery Case: Investigasi Industri</h1>
        <p class="text-primary-100 mt-2 text-base drop-shadow-sm">Mengasah Analisis & Problem Solving dalam Lingkungan SMK</p>
    </div>
</header>


        <main class="p-6 md:p-8">
            <div id="loadingIndicator" class="hidden text-center my-8">
                <div class="animate-spin inline-block w-8 h-8 border-4 border-primary border-t-transparent rounded-full" role="status"></div>
                <p class="mt-3 text-lg text-primary font-semibold">Memuat Skenario / Menjalankan Tindakan...</p>
            </div>

            <!-- Start Screen -->
            <div id="startScreen">
                <p class="text-lg text-gray-700 mb-6">Selamat datang, Investigator! Anda ditugaskan untuk menyelesaikan kasus masalah industri di lingkungan proyek SMK. Kumpulkan petunjuk, pilih tindakan dengan bijak, dan temukan akar masalahnya.</p>
                
                <!-- New Case Selection Controls -->
                <div class="mb-6 space-y-4 p-4 bg-gray-50 rounded-lg border">
                    
                    <!-- New Difficulty Selector -->
                    <div>
                        <label for="difficultySelect" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tingkat Kesulitan (Fokus Laporan Akhir):</label>
                        <select id="difficultySelect" class="w-full p-2 border border-gray-300 rounded-md focus:ring-secondary focus:border-secondary">
                            <option value="mudah">SMK (Mudah - 7 Langkah, Fokus: Akar Masalah)</option>
                            <option value="sedang" selected>Mahasiswa (Sedang - 5 Langkah, Fokus: Solusi & Mitigasi)</option>
                            <option value="sulit">Profesional (Sulit - 3 Langkah, Fokus: Manajemen Risiko & DP)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Semakin sedikit langkah, semakin sulit kasusnya, dan semakin tinggi tuntutan pada solusi strategis.</p>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-gray-50 text-gray-500">PENGATURAN KASUS</span>
                        </div>
                    </div>

                    <div>
                        <label for="predefinedCaseSelect" class="block text-sm font-medium text-gray-700 mb-1">Pilih Jenis Kasus:</label>
                        <select id="predefinedCaseSelect" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                            <option value="random">--- Buat Kasus Acak ---</option>
                            <option value="Kegagalan Sistem Jaringan dan Server">Kegagalan Sistem Jaringan dan Server</option>
                            <option value="Produksi Mesin CNC yang Terus Menghasilkan Cacat">Produksi Mesin CNC yang Terus Menghasilkan Cacat</option>
                          
                            <option value="Masalah Keamanan dan Kesehatan Kerja (K3) di Bengkel">Masalah Keamanan dan Kesehatan Kerja (K3) di Bengkel</option>
                        </select>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-gray-50 text-gray-500">ATAU</span>
                        </div>
                    </div>
                    <div>
                        <label for="customCaseInput" class="block text-sm font-medium text-gray-700 mb-1">Tulis Topik Kasus Kustom:</label>
                        <input type="text" id="customCaseInput" placeholder="Contoh: Proyek robotika gagal berfungsi" class="w-full p-2 border border-gray-300 rounded-md focus:ring-secondary focus:border-secondary">
                        <p class="text-xs text-gray-500 mt-1">Jika diisi, input kustom akan diprioritaskan.</p>
                    </div>
                </div>
                <!-- End Case Selection Controls -->

                <div class="border p-4 bg-yellow-50 rounded-lg mb-6">
                    <h3 class="font-bold text-lg text-yellow-800 mb-2">Manfaat Edukasi:</h3>
                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                        <li>Mengembangkan kemampuan analisis dan berpikir kritis.</li>
                        <li>Melatih pengambilan keputusan yang efektif.</li>
                        <li>Meningkatkan keterampilan komunikasi untuk mendapatkan informasi.</li>
                    </ul>
                </div>
                <button id="startButton" class="w-full bg-secondary text-white py-3 rounded-lg font-bold text-xl hover:bg-orange-700 action-button" onclick="startGame()">Mulai Investigasi Baru</button>
            </div>

            <!-- Game Screen -->
            <div id="gameScreen" class="hidden">
                <div class="grid md:grid-cols-3 gap-6">
                    <!-- Case Summary and History (Left/Top) -->
                    <div class="md:col-span-2">
                        <div class="mb-6">
                            <h2 id="caseTitle" class="text-2xl font-bold text-gray-800 mb-2"></h2>
                            <!-- Step counter now uses dynamic stepLimit -->
                            <p class="text-sm font-medium text-primary-600 mb-4">Langkah ke: <span id="stepCounter">0</span> / <span id="stepLimitDisplay">5</span></p>
                            
                            <!-- TTS Toggle Button added here -->
                            <div class="flex justify-end mb-2">
                                <button id="ttsToggle" class="flex items-center space-x-2 px-3 py-1 text-xs font-medium rounded-full transition duration-150" onclick="toggleTTS()">
                                    <span id="ttsIcon">🔇</span>
                                    <span id="ttsText">Aktifkan Suara Kasus</span>
                                </button>
                            </div>
                            <!-- End TTS Toggle Button -->

                            <div class="bg-gray-100 p-4 rounded-lg border-l-4 border-primary">
                                <h3 class="font-semibold text-lg text-gray-800 mb-1">Latar Belakang Kasus:</h3>
                                <p id="caseBackground" class="text-gray-600 text-sm"></p>
                            </div>
                        </div>

                        <!-- Investigation History -->
                        <div class="bg-white p-4 rounded-lg shadow-inner border max-h-96 overflow-y-auto case-history">
                            <h3 class="font-bold text-xl text-gray-800 border-b pb-2 mb-3">Jurnal Investigasi</h3>
                            <div id="investigationHistory" class="space-y-4">
                                <!-- History entries will be appended here -->
                            </div>
                        </div>
                    </div>

                    <!-- Actions and Controls (Right/Bottom) -->
                    <div class="md:col-span-1">
                        <div id="actionContainer" class="sticky top-8 space-y-4">
                            <div id="actionOptions" class="bg-primary-50 p-4 rounded-lg shadow-md">
                                <h3 class="font-bold text-xl text-primary mb-3">Pilihan Tindakan</h3>
                                <div id="actionButtons" class="space-y-3">
                                    <!-- Action buttons will be generated here -->
                                </div>
                            </div>

                            <button id="submitButton" class="w-full bg-red-600 text-white py-3 rounded-lg font-bold text-lg hover:bg-red-700 action-button disabled:opacity-50" onclick="submitInvestigation()" disabled>
                                Kumpulkan Laporan Akhir (Langkah Terakhir)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Result Screen -->
            <div id="resultScreen" class="hidden p-8 bg-green-50 rounded-xl border border-green-300">
                <h2 class="text-3xl font-extrabold text-green-800 mb-4">Investigasi Selesai!</h2>
                <div id="finalAnalysis" class="space-y-6">
                    <!-- Final analysis content will be inserted here -->
                </div>
                <button class="mt-8 w-full bg-primary text-white py-3 rounded-lg font-bold text-xl hover:bg-blue-700 action-button" onclick="resetGame()">Mulai Kasus Baru</button>
            </div>
        </main>
    </div>

    <script type="module">
        // Gemini API Configuration
        // Note: The apiKey variable is intentionally left blank. The Canvas environment will securely inject the key during runtime.
        const apiKey = "<?php echo $apiKey; ?>";
        const model = "<?php echo $model; ?>";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;

        // Game State Variables
        let caseData = {};
        let stepCount = 0;
        let stepLimit = 5; // Batas langkah default (Sedang)
        let investigationHistory = [];
        
        // Global TTS State
        let isTTSOn = false; // Default: OFF
        let utterance = null;
        
        // Difficulty mapping
        const difficultySteps = {
            "mudah": 7, // SMK / Beginner
            "sedang": 5, // Mahasiswa / Intermediate
            "sulit": 3  // Profesional / Expert
        };


        // DOM Elements
        const startScreen = document.getElementById('startScreen');
        const gameScreen = document.getElementById('gameScreen');
        const resultScreen = document.getElementById('resultScreen');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const caseTitleEl = document.getElementById('caseTitle');
        const caseBackgroundEl = document.getElementById('caseBackground');
        const stepCounterEl = document.getElementById('stepCounter');
        const stepLimitDisplayEl = document.getElementById('stepLimitDisplay'); // New element
        const actionButtonsEl = document.getElementById('actionButtons');
        const historyContainerEl = document.getElementById('investigationHistory');
        const submitButton = document.getElementById('submitButton');
        
        // New DOM elements for selection and TTS
        const predefinedCaseSelect = document.getElementById('predefinedCaseSelect');
        const customCaseInput = document.getElementById('customCaseInput');
        const difficultySelect = document.getElementById('difficultySelect'); // New element
        const ttsToggle = document.getElementById('ttsToggle');
        const ttsIcon = document.getElementById('ttsIcon');
        const ttsText = document.getElementById('ttsText');


        /**
         * Helper untuk melakukan fetch API dengan exponential backoff.
         * @param {object} payload - Payload untuk body request.
         * @param {number} maxRetries - Jumlah maksimum percobaan.
         * @param {number} delay - Waktu tunda awal (ms).
         * @returns {Promise<object>} Hasil JSON dari API.
         */
        async function fetchWithBackoff(payload, maxRetries = 5, delay = 1000) {
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 429 && i < maxRetries - 1) {
                        // Too many requests, retry
                        await new Promise(resolve => setTimeout(resolve, delay * (2 ** i) + Math.random() * 1000));
                        continue;
                    }
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return await response.json();

                } catch (error) {
                    if (i === maxRetries - 1) {
                        console.error("Gemini API call failed after multiple retries:", error);
                        throw new Error("Gagal terhubung ke AI. Silakan coba lagi.");
                    }
                    // Continue loop for retry
                }
            }
        }
        
        /**
         * Menghentikan sintesis suara yang sedang berjalan.
         */
        function stopSpeech() {
            if ('speechSynthesis' in window && speechSynthesis.speaking) {
                speechSynthesis.cancel();
            }
        }

        /**
         * Membaca teks latar belakang kasus menggunakan Web Speech API.
         * @param {string} text - Teks yang akan dibacakan.
         */
        function speakCaseBackground(text) {
            stopSpeech(); // Hentikan suara sebelumnya

            if ('speechSynthesis' in window && isTTSOn) {
                utterance = new SpeechSynthesisUtterance(text);

                // Pilih suara Indonesia
                const voices = speechSynthesis.getVoices();
                const indoVoice = voices.find(v => v.lang === 'id-ID' || v.lang.startsWith('id'));
                if (indoVoice) utterance.voice = indoVoice;

                utterance.rate = 1.0;   // kecepatan normal
                utterance.pitch = 1.0;  // pitch alami

                speechSynthesis.speak(utterance);
            }
        }
        // Stop TTS saat user pindah halaman atau reload
        window.addEventListener('beforeunload', () => {
            stopSpeech();
        });

        // Stop TTS saat tab blur (opsional, jika user pindah tab)
        window.addEventListener('visibilitychange', () => {
            if (document.hidden) stopSpeech();
        });

        /**
         * Mengaktifkan/menonaktifkan TTS dan memperbarui tombol.
         */
        window.toggleTTS = function() {
            isTTSOn = !isTTSOn;
            updateTTSToggleUI();
            
            if (!isTTSOn) {
                stopSpeech();
            } else {
                // If turning on, and on the game screen, read the current case data
                if (!gameScreen.classList.contains('hidden') && caseData.background) {
                    const fullText = `Kasus: ${caseData.title}. Masalah: ${caseData.problemDescription}. Latar Belakang: ${caseData.background}`;
                    speakCaseBackground(fullText);
                }
            }
        }
        
        /**
         * Memperbarui tampilan tombol toggle TTS (warna, ikon, teks).
         */
        function updateTTSToggleUI() {
            if (isTTSOn) {
                ttsIcon.textContent = '🔊';
                ttsText.textContent = 'Nonaktifkan Suara Kasus';
                ttsToggle.classList.remove('text-gray-600', 'bg-gray-200', 'hover:bg-gray-300');
                ttsToggle.classList.add('text-white', 'bg-primary', 'hover:bg-blue-700');
            } else {
                ttsIcon.textContent = '🔇';
                ttsText.textContent = 'Aktifkan Suara Kasus';
                ttsToggle.classList.remove('text-white', 'bg-primary', 'hover:bg-blue-700');
                ttsToggle.classList.add('text-gray-600', 'bg-gray-200', 'hover:bg-gray-300');
            }
        }


        /**
         * Mengatur tampilan UI (hanya satu yang terlihat).
         * @param {string} screenId - ID layar yang akan ditampilkan ('start', 'game', 'result').
         */
        function setScreen(screenId) {
            startScreen.classList.add('hidden');
            gameScreen.classList.add('hidden');
            resultScreen.classList.add('hidden');

            if (screenId === 'start') {
                startScreen.classList.remove('hidden');
            } else if (screenId === 'game') {
                gameScreen.classList.remove('hidden');
            } else if (screenId === 'result') {
                resultScreen.classList.remove('hidden');
            }
            stopSpeech();
        }

        /**
         * Menampilkan atau menyembunyikan indikator loading.
         * @param {boolean} show - True untuk menampilkan, False untuk menyembunyikan.
         */
        function toggleLoading(show) {
            loadingIndicator.classList.toggle('hidden', !show);
            const buttons = document.querySelectorAll('.action-button');
            buttons.forEach(btn => btn.disabled = show);
            submitButton.disabled = show || stepCount === 0;
            
            // Disable TTS toggle while loading data
            if (ttsToggle) {
                ttsToggle.disabled = show;
                if (show) {
                    stopSpeech(); // Stop speech during load
                }
            }
        }

        /**
         * Memulai permainan baru dengan menghasilkan skenario awal dari Gemini.
         */
        async function startGame() {
            setScreen('game');
            toggleLoading(true);
            
            // Set the step limit based on selected difficulty
            const selectedDifficulty = difficultySelect.value;
            stepLimit = difficultySteps[selectedDifficulty] || 5; // Default to 5 if value is missing
            
            resetGameState(); // Reset state after setting stepLimit

            // --- Logika Penentuan Konteks SOP Berdasarkan Level ---
            let sopContext = '';
            if (selectedDifficulty === 'mudah') {
                // Fokus: Investigasi Dasar (SMK)
                sopContext = 'Opsi tindakan harus berupa langkah investigasi dasar dan langsung (e.g., wawancara, pemeriksaan visual, uji coba sederhana).';
            } else if (selectedDifficulty === 'sedang') {
                // Fokus: Analisis Terstruktur (Mahasiswa)
                sopContext = 'Opsi tindakan harus berupa langkah analisis data dan prosedur formal (e.g., Analisis 5 Whys, pengumpulan log data terstruktur).';
            } else if (selectedDifficulty === 'sulit') {
                // Fokus: Strategis & Sistematis (Profesional)
                sopContext = 'Opsi tindakan harus berupa langkah strategis dan sistematis tingkat manajerial (e.g., Audit internal, analisis FMEA, tinjauan kepatuhan SOP).';
            }
            
            const systemPrompt = `Anda adalah generator kasus simulasi untuk pelatihan siswa SMK di Indonesia. Buatlah skenario investigasi yang realistis (produksi, servis, atau sistem) di lingkungan proyek atau industri SMK. Berikan output dalam format JSON yang ketat. Semua teks harus dalam Bahasa Indonesia. Fokus SOP: ${sopContext}`;
            
            // --- Logic for custom/selected case topic ---
            let selectedTopic = predefinedCaseSelect.value;
            let customTopic = customCaseInput.value.trim();
            let topicForAI = '';

            if (customTopic) {
                // Priority to custom input
                topicForAI = `tentang kasus: **${customTopic}**.`;
            } else if (selectedTopic && selectedTopic !== 'random') {
                // Use predefined selection
                topicForAI = `dengan fokus pada masalah: **${selectedTopic}**.`;
            } else {
                // Random case
                topicForAI = 'secara acak.';
            }

            const userQuery = `Buat kasus misteri, minimal 700 kata ${topicForAI} Skenario harus mencakup latar belakang, deskripsi masalah, dan 3 opsi tindakan investigasi awal untuk pemain. Judul kasus harus menarik dan berhubungan dengan dunia SMK. ${sopContext}`;
            // --- End Logic for custom/selected case topic ---


            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "title": { "type": "STRING", "description": "Judul kasus, e.g., 'Misteri Kerusakan Mesin CNC'" },
                            "background": { "type": "STRING", "description": "Latar belakang masalah dan deskripsi singkat perusahaan/proyek SMK. Minimal 300 kata." },
                            "problemDescription": { "type": "STRING", "description": "Detail spesifik masalah saat ini yang harus dipecahkan pemain. Minimal 200 kata." },
                            "initialClues": {
                                "type": "ARRAY",
                                "items": {
                                    "type": "OBJECT",
                                    "properties": {
                                        "text": { "type": "STRING", "description": "Deskripsi tindakan investigasi, e.g., 'Wawancara Kepala Bengkel'." },
                                        "resultPrompt": { "type": "STRING", "description": "Prompt yang akan digunakan untuk meminta hasil dari tindakan ini di langkah selanjutnya. E.g., 'Berikan hasil rinci dari wawancara Kepala Bengkel mengenai masalah ini.'" }
                                    },
                                    "propertyOrdering": ["text", "resultPrompt"]
                                }
                            }
                        },
                        "propertyOrdering": ["title", "background", "problemDescription", "initialClues"]
                    }
                }
            };

            try {
                const result = await fetchWithBackoff(payload);
                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                if (!jsonText) throw new Error("Respons AI kosong atau tidak valid.");

                const initialData = JSON.parse(jsonText);
                
                caseData = {
                    title: initialData.title || "Kasus Baru",
                    background: initialData.background || "Latar belakang tidak tersedia.",
                    problemDescription: initialData.problemDescription || "Deskripsi masalah tidak tersedia.",
                    currentOptions: initialData.initialClues || []
                };

                // Render UI
                caseTitleEl.textContent = caseData.title;
                const fullBackgroundText = `<strong>Masalah:</strong> ${caseData.problemDescription} <br><br> ${caseData.background}`;
                caseBackgroundEl.innerHTML = fullBackgroundText;
                
                // VITAL CHANGE: Explicitly state the case source in the history for context confirmation
                const caseSourceText = topicForAI.replace(/\*\*/g, '').trim(); 
                addHistoryEntry('Skenario Dimulai', `Kasus **${caseData.title}** telah dimulai, skenario dibuat ${caseSourceText}. Tugas Anda adalah menemukan akar masalah berdasarkan deskripsi: ${caseData.problemDescription}. Pilih tindakan awal Anda.`, 'system');
                
                renderActions();
                checkSubmissionStatus();
                
                // --- TTS Integration: Speak the case background if TTS is on ---
                const ttsContent = `Kasus: ${caseData.title}. Masalah: ${caseData.problemDescription}. Latar Belakang: ${caseData.background}`;
                speakCaseBackground(ttsContent);
                // --- End TTS Integration ---

            } catch (error) {
                console.error("Error generating scenario:", error);
                addHistoryEntry('ERROR', `Gagal memuat skenario: ${error.message}. Coba lagi.`, 'error');
                setScreen('start'); // Kembali ke awal jika gagal
            } finally {
                toggleLoading(false);
            }
        }

        /**
         * Mereset semua state game ke awal.
         */
        function resetGameState() {
            stepCount = 0;
            caseData = {};
            investigationHistory = [];
            stepCounterEl.textContent = 0;
            stepLimitDisplayEl.textContent = stepLimit; // Display the current limit
            historyContainerEl.innerHTML = '';
            actionButtonsEl.innerHTML = '';
            submitButton.disabled = true;
            document.getElementById('finalAnalysis').innerHTML = '';
            // Reset custom input and select
            predefinedCaseSelect.value = 'random';
            customCaseInput.value = '';
            // --- TTS Reset ---
            stopSpeech();
            // --- End TTS Reset ---
        }

        /**
         * Menambahkan entri ke Jurnal Investigasi.
         * @param {string} title - Judul entri (e.g., 'Wawancara Mandor').
         * @param {string} content - Konten atau hasil dari entri.
         * @param {string} type - Tipe entri ('player', 'ai', 'system', 'error').
         */
        function addHistoryEntry(title, content, type) {
            const entry = document.createElement('div');
            let colorClass, icon;

            switch (type) {
                case 'player':
                    colorClass = 'bg-primary-100 border-primary-500';
                    icon = '🕵️‍♂️';
                    break;
                case 'ai':
                    colorClass = 'bg-gray-100 border-gray-500';
                    icon = '💡';
                    break;
                case 'system':
                    colorClass = 'bg-green-100 border-green-500';
                    icon = '✅';
                    break;
                case 'error':
                    colorClass = 'bg-red-100 border-red-500';
                    icon = '❌';
                    break;
                default:
                    colorClass = 'bg-gray-50 border-gray-300';
                    icon = '📝';
            }

            entry.className = `p-3 rounded-lg border-l-4 ${colorClass} shadow-sm`;
            entry.innerHTML = `
                <p class="font-bold text-gray-800">${icon} ${title}</p>
                <p class="text-sm text-gray-600 mt-1">${content}</p>
            `;
            historyContainerEl.appendChild(entry);
            historyContainerEl.scrollTop = historyContainerEl.scrollHeight;
        }
        
        /**
         * Render tombol aksi berdasarkan opsi saat ini di caseData.
         */
        function renderActions() {
            actionButtonsEl.innerHTML = '';
            // Use dynamic stepLimit
            if (caseData.currentOptions && caseData.currentOptions.length > 0 && stepCount < stepLimit) {
                caseData.currentOptions.forEach((option) => {
                    const button = document.createElement('button');
                    button.className = 'action-button w-full text-left bg-white text-gray-700 py-3 px-4 rounded-lg border-2 border-primary-300 hover:bg-primary-100 font-semibold text-sm';
                    button.textContent = option.text;
                    button.onclick = () => executeAction(option.text, option.resultPrompt);
                    actionButtonsEl.appendChild(button);
                });
            } else {
                actionButtonsEl.innerHTML = '<p class="text-gray-600">Tidak ada lagi opsi investigasi. Silakan ajukan laporan akhir Anda.</p>';
            }
        }

        /**
         * Memeriksa dan memperbarui status tombol Kumpulkan Laporan Akhir.
         */
        function checkSubmissionStatus() {
            stepCounterEl.textContent = stepCount;
            stepLimitDisplayEl.textContent = stepLimit; // Update limit display
            submitButton.disabled = stepCount === 0; // Disable submission if no steps taken
            
            // Use dynamic stepLimit
            if (stepCount >= stepLimit) {
                submitButton.textContent = "Kumpulkan Laporan Akhir (Batas Langkah Tercapai)";
                submitButton.classList.remove('disabled:opacity-50');
            } else {
                submitButton.textContent = `Kumpulkan Laporan Akhir (Langkah ke ${stepCount})`;
            }
        }

        /**
         * Menjalankan tindakan investigasi yang dipilih pemain dan mendapatkan hasilnya dari Gemini.
         * @param {string} actionText - Teks tindakan yang dipilih.
         * @param {string} resultPrompt - Prompt spesifik untuk hasil tindakan ini.
         */
        async function executeAction(actionText, resultPrompt) {
            // Use dynamic stepLimit
            if (stepCount >= stepLimit) {
                addHistoryEntry('Peringatan', `Anda telah mencapai batas maksimum langkah investigasi (${stepLimit}). Silakan ajukan laporan akhir.`, 'error');
                return;
            }
            
            stopSpeech(); // Stop speech when player makes a move

            stepCount++;
            investigationHistory.push({ type: 'player', action: actionText });
            addHistoryEntry('Tindakan Anda', actionText, 'player');
            toggleLoading(true);
            checkSubmissionStatus();

            // --- Logika Penentuan Konteks SOP Berdasarkan Level ---
            const selectedDifficulty = difficultySelect.value;
            let sopContext = '';
            if (selectedDifficulty === 'mudah') {
                sopContext = 'Pastikan 2 opsi lanjutan berupa langkah investigasi dasar dan langsung (e.g., wawancara, pemeriksaan visual, uji coba sederhana).';
            } else if (selectedDifficulty === 'sedang') {
                sopContext = 'Pastikan 2 opsi lanjutan berupa langkah analisis data dan prosedur formal (e.g., Analisis 5 Whys, pengumpulan log data terstruktur).';
            } else if (selectedDifficulty === 'sulit') {
                sopContext = 'Pastikan 2 opsi lanjutan berupa langkah strategis dan sistematis tingkat manajerial (e.g., Audit internal, analisis FMEA, tinjauan kepatuhan SOP).';
            }
            // --- End Logika Penentuan Konteks SOP ---

            // Construct the detailed prompt for the AI to generate the result and new options
            // Note: Context is maintained by passing back caseData.title and caseData.problemDescription
            const fullPrompt = `
                Skenario: ${caseData.title} - ${caseData.problemDescription}
                Riwayat Tindakan: ${investigationHistory.filter(h => h.type === 'player').map(h => h.action).join('; ')}
                Langkah Ini: Pemain memilih tindakan: "${actionText}".
                Tugas Anda:
                1. Berikan temuan yang sangat rinci dan mendalam dari tindakan ini. Gunakan prompt spesifik: "${resultPrompt}". Hasil temuan harus minimal 250 kata.
                2. Berikan 2 opsi tindakan lanjutan baru yang logis berdasarkan temuan tersebut (maksimal 15 kata per opsi). ${sopContext}
                3. Pastikan output Anda berupa JSON.
            `;

            const systemPrompt = "Anda adalah sumber informasi dan pengembang plot. Berikan temuan yang sangat rinci (minimal 250 kata) dan 2 opsi tindakan lanjutan yang logis. Jika kasus sudah terpecahkan, tetapkan isFinal ke true.";
            
            const payload = {
                contents: [{ parts: [{ text: fullPrompt }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "newFinding": { "type": "STRING", "description": "Temuan baru yang terperinci dan mendalam (minimal 250 kata) dari tindakan pemain." },
                            "newClues": {
                                "type": "ARRAY",
                                "items": {
                                    "type": "OBJECT",
                                    "properties": {
                                        "text": { "type": "STRING", "description": "Opsi tindakan investigasi lanjutan (maks 15 kata)." },
                                        "resultPrompt": { "type": "STRING", "description": "Prompt yang akan digunakan untuk meminta hasil dari tindakan ini di langkah selanjutnya. E.g., 'Tanyakan pada Teknisi A tentang sensor X.'" }
                                    },
                                    "propertyOrdering": ["text", "resultPrompt"]
                                }
                            },
                            "isFinal": { "type": "BOOLEAN", "description": "Setel ke true jika temuan baru ini secara definitif memecahkan masalah atau mengakhiri investigasi." }
                        },
                        "propertyOrdering": ["newFinding", "newClues", "isFinal"]
                    }
                }
            };

            try {
                const result = await fetchWithBackoff(payload);
                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                if (!jsonText) throw new Error("Respons AI kosong atau tidak valid.");

                const stepResult = JSON.parse(jsonText);

                // Add result to history
                investigationHistory.push({ type: 'ai', finding: stepResult.newFinding, actionTaken: actionText });
                addHistoryEntry(`Hasil Tindakan: ${actionText}`, stepResult.newFinding, 'ai');

                // Update current options
                caseData.currentOptions = stepResult.newClues || [];
                renderActions();
                
                // If AI suggests final step, allow submission
                if (stepResult.isFinal) {
                    addHistoryEntry('BREAKING NEWS', 'Temuan baru ini tampaknya mengarah pada akar masalah! Anda dapat mengajukan laporan akhir Anda sekarang.', 'system');
                    stepCount = stepLimit; // Force max step to allow submission
                    checkSubmissionStatus();
                }

            } catch (error) {
                console.error("Error executing action:", error);
                addHistoryEntry('ERROR Tindakan', `Gagal mendapatkan hasil tindakan: ${error.message}. Coba tindakan lain.`, 'error');
            } finally {
                toggleLoading(false);
            }
        }

        /**
         * Mengirimkan investigasi dan meminta analisis akhir dari Gemini.
         */
        async function submitInvestigation() {
            toggleLoading(true);
            stopSpeech(); // Stop speech before final submission
            
            // Collect all findings
            const allFindings = investigationHistory
                .filter(h => h.type === 'ai')
                .map(h => `Tindakan: ${h.actionTaken} -> Temuan: ${h.finding}`)
                .join('\n- ');
            
            const selectedDifficulty = difficultySelect.value;
            let finalRequirements = '';
            let responseSchemaExtensions = {};
            let propertyOrdering = ["rootCause", "analysisSummary"];
            
            // Dynamic requirements based on difficulty
            if (selectedDifficulty === 'sedang') {
                finalRequirements = "3. Rancang Rencana Mitigasi (Mitigation Plan) yang mendalam untuk mencegah terulangnya masalah ini.";
                responseSchemaExtensions.mitigationPlan = { "type": "STRING", "description": "Rencana langkah-langkah konkret dan mendalam untuk mencegah masalah terulang." };
                propertyOrdering.push('mitigationPlan');
            } else if (selectedDifficulty === 'sulit') {
                finalRequirements = "3. Rancang Strategi Manajemen Risiko dan Disaster Planning (DP) yang komprehensif dan sangat rinci untuk mengatasi dampak terburuk dari kegagalan sistemik yang serupa. Ini harus menjadi bagian yang terperinci.";
                responseSchemaExtensions.disasterPlanning = { "type": "STRING", "description": "Strategi Manajemen Risiko dan Disaster Planning (DP) yang fokus pada kesiapan sistem dan respons cepat." };
                propertyOrdering.push('disasterPlanning');
            }
            
            propertyOrdering.push("score", "educationalAdvice");


            const userPrompt = `
                Judul Kasus: ${caseData.title}
                Deskripsi Masalah: ${caseData.problemDescription}
                Riwayat Investigasi:
                - ${allFindings}

                Tugas Anda adalah bertindak sebagai Ketua Investigator.
                1. Analisis seluruh temuan investigasi pemain dan berikan ringkasan yang panjang (minimal 300 kata).
                2. Identifikasi *Akar Masalah* yang seharusnya ditemukan.
                ${finalRequirements}
                4. Berikan nilai (0-100) berdasarkan efektivitas dan logika langkah-langkah pemain, DENGAN MEMPERTIMBANGKAN KEBUTUHAN AKSI YANG SISTEMATIS (SOP) UNTUK LEVEL INI (${selectedDifficulty}).
                5. Berikan *Saran Edukasi* spesifik (analitis, komunikasi, teknis) untuk pemain.
                6. Berikan output dalam format JSON.
            `;
            
            const systemPrompt = "Anda adalah Ketua Investigator yang profesional dan edukatif. Berikan analisis akhir investigasi pemain (dalam Bahasa Indonesia) dengan identifikasi akar masalah, penilaian, dan saran yang terstruktur dalam format JSON.";
            
            const fullSchema = {
                type: "OBJECT",
                properties: {
                    "rootCause": { "type": "STRING", "description": "Akar masalah sebenarnya dari kasus ini." },
                    "analysisSummary": { "type": "STRING", "description": "Ringkasan analisis terhadap langkah investigasi pemain. Minimal 300 kata." },
                    ...responseSchemaExtensions, // Add dynamic properties
                    "score": { "type": "INTEGER", "description": "Nilai total (0-100) berdasarkan efektivitas investigasi." },
                    "educationalAdvice": { 
                        "type": "ARRAY",
                        "items": { "type": "STRING" },
                        "description": "3-5 saran spesifik untuk meningkatkan kemampuan analitis, komunikasi, dan/atau problem-solving pemain."
                    }
                },
                propertyOrdering: propertyOrdering
            };


            const payload = {
                contents: [{ parts: [{ text: userPrompt }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: fullSchema
                }
            };

            try {
                const result = await fetchWithBackoff(payload);
                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                if (!jsonText) throw new Error("Respons AI kosong atau tidak valid.");

                const finalResult = JSON.parse(jsonText);

                // Determine the correct numbering for the final section
                const hasExtraSection = finalResult.mitigationPlan || finalResult.disasterPlanning;
                const adviceNumber = hasExtraSection ? '4.' : '3.';
                const extraTitleNumber = '3.';

                // Render result screen
                const finalAnalysisEl = document.getElementById('finalAnalysis');
                finalAnalysisEl.innerHTML = `
                    <div class="p-4 bg-green-200 text-green-900 rounded-lg font-bold text-center text-3xl">
                        Nilai Investigasi Anda: ${finalResult.score}/100
                    </div>

                    <div>
                        <h3 class="font-bold text-2xl text-green-800 border-b pb-2 mb-3">1. Akar Masalah Sebenarnya:</h3>
                        <p class="text-gray-700">${finalResult.rootCause}</p>
                    </div>

                    <div>
                        <h3 class="font-bold text-2xl text-green-800 border-b pb-2 mb-3">2. Ringkasan Kinerja:</h3>
                        <p class="text-gray-700">${finalResult.analysisSummary}</p>
                    </div>
                    
                    <!-- Dynamic Section for Mitigation/DP -->
                    ${finalResult.mitigationPlan ? `
                        <div>
                            <h3 class="font-bold text-2xl text-green-800 border-b pb-2 mb-3">${extraTitleNumber} Rencana Mitigasi (Pencegahan):</h3>
                            <p class="text-gray-700">${finalResult.mitigationPlan}</p>
                        </div>
                    ` : ''}
                    
                    ${finalResult.disasterPlanning ? `
                        <div>
                            <h3 class="font-bold text-2xl text-green-800 border-b pb-2 mb-3">${extraTitleNumber} Strategi Manajemen Risiko & DP:</h3>
                            <p class="text-gray-700">${finalResult.disasterPlanning}</p>
                        </div>
                    ` : ''}

                    <div>
                        <h3 class="font-bold text-2xl text-green-800 border-b pb-2 mb-3">${adviceNumber} Saran Edukasi:</h3>
                        <ul class="list-disc list-inside text-gray-700 space-y-2">
                            ${finalResult.educationalAdvice.map(advice => `<li>${advice}</li>`).join('')}
                        </ul>
                    </div>
                `;

                setScreen('result');

            } catch (error) {
                console.error("Error generating final analysis:", error);
                // Handle failure gracefully on the game screen
                addHistoryEntry('ERROR Analisis Akhir', `Gagal mendapatkan analisis AI: ${error.message}. Silakan coba lagi.`, 'error');
            } finally {
                toggleLoading(false);
            }
        }

        /**
         * Fungsi untuk memulai kasus baru (reset total).
         */
        window.resetGame = function() {
            setScreen('start');
        }

        /**
         * Expose functions globally for HTML elements
         */
        window.startGame = startGame;
        window.submitInvestigation = submitInvestigation;

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            setScreen('start');
            updateTTSToggleUI(); // Set initial button state (off)
            
            // Pre-load voices for faster start, though it's asynchronous
            if ('speechSynthesis' in window) {
                speechSynthesis.getVoices();
            }
        });
    </script>
</body>
</html>
