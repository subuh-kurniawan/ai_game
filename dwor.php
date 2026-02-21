<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Workshop Simulator: SMK Mode Selection</title>
    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Memuat Font Inter (Import diperbaiki) -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
        }
        /* Custom CSS untuk efek bayangan dan gaya yang lebih hidup */
        .card {
            transition: all 0.2s;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .ai-master-bubble {
            background-color: #e0f2f1;
            border-left: 4px solid #00796b;
        }
        .code-area {
            font-family: 'Consolas', 'Courier New', monospace;
        }
        /* Style untuk output yang lebih bersih dari Markdown */
        .clean-text p {
            margin-bottom: 0.5rem; /* Memberi jarak antar paragraf */
        }
        /* Style untuk input yang aktif */
        .input-active {
            border-color: #10b981; /* Green-500 */
            animation: pulse-green 1.5s infinite;
        }
        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            50% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
        }
    </style>
</head>
<body class="p-4 md:p-8 min-h-screen flex items-center justify-center">

    <!-- 1. START SCREEN (Selection) -->
    <div id="start-screen" class="w-full max-w-2xl bg-white rounded-xl card p-6 md:p-8">
        <header class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-teal-700">
                <span class="text-gray-900">SMK </span>AI Workshop Simulator
            </h1>
            <h2 class="text-xl text-gray-600 mt-2">Pilih Jurusan dan Mulai Proyek Anda!</h2>
        </header>

        <!-- Pilihan Jurusan (Dropdown) -->
        <h3 class="text-2xl font-bold text-gray-800 mb-4">⚙️ Pilih Jurusan SMK:</h3>
        
        <select id="major-select" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:border-teal-500 transition duration-150 mb-4">
            <option value="" disabled selected>--- Pilih Jurusan Anda ---</option>
        </select>

        <!-- Input Jurusan Kustom (Tersembunyi) -->
        <input type="text" id="custom-major-input" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:border-teal-500 transition duration-150 hidden" placeholder="Tulis nama Jurusan Kustom Anda di sini (misalnya: Perhotelan)"/>
        
        <!-- Tema Proyek -->
        <h3 class="text-2xl font-bold text-gray-800 mb-4 mt-6">💡 Tentukan Tema Proyek Anda:</h3>
        <input type="text" id="project-theme-input" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:border-teal-500 transition duration-150" placeholder="Contoh: 'Buatlah sistem kasir berbasis web' atau 'Merakit mesin V6'"/>
        <p class="text-sm text-gray-500 mt-2">Tema ini akan digunakan AI Master untuk menentukan tantangan spesifik Anda.</p>
        
        <!-- Tombol Mulai -->
        <button id="start-game-btn" class="w-full mt-8 bg-teal-600 hover:bg-teal-700 text-white font-bold py-4 rounded-xl transition duration-150 disabled:bg-teal-300" disabled>
            Mulai Workshop!
        </button>
    </div>

    <!-- 2. MAIN GAME WORKSHOP (Hidden by default) -->
    <div id="app" class="w-full max-w-4xl bg-white rounded-xl card p-6 md:p-8 hidden">
        <header class="text-center mb-6">
            <h1 class="text-3xl md:text-4xl font-extrabold text-teal-700">
                <span class="text-gray-900">SMK </span>AI Workshop Simulator
            </h1>
            <h2 id="module-title" class="text-xl text-gray-600 mt-1"></h2>
        </header>

        <!-- Bagian Utama Game -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Kiri: Tantangan dan Area Kerja -->
            <div>
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">🎯 Tantangan Proyek</h3>
                    <div id="challenge-display" class="p-4 bg-gray-50 rounded-lg border-l-4 border-indigo-500 text-gray-700 min-h-[100px] clean-text">
                        Klik "Mulai Tantangan Baru" untuk menerima tugas dari AI Master.
                    </div>
                </div>

                <div class="mb-6">
                    <h3 id="work-area-title" class="text-2xl font-bold text-gray-800 mb-2">💻 Area Kerja Kode</h3>
                    <textarea id="code-input" class="code-area w-full h-40 p-3 border-2 border-gray-300 rounded-lg focus:border-indigo-500 transition duration-150 resize-none text-sm" placeholder="Masukkan solusi Anda di sini (kode, daftar komponen, pola, atau deskripsi perakitan)..." disabled></textarea>
                </div>

                <!-- Kontrol Game (Diperbarui dengan Saran AI) -->
                <div class="flex flex-col gap-3">
                    <!-- Row 1: Primary Action -->
                    <button id="start-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition duration-150 disabled:bg-indigo-300">
                        Mulai Tantangan Baru
                    </button>

                    <!-- Row 2: Secondary/Working Actions -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button id="suggestion-btn" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-xl transition duration-150 disabled:bg-blue-300" disabled>
                            Saran AI (Rekomendasi)
                        </button>
                        <button id="hint-btn" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 rounded-xl transition duration-150 disabled:bg-yellow-300" disabled>
                            Minta Petunjuk AI (Koreksi)
                        </button>
                        <button id="submit-btn" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 rounded-xl transition duration-150 disabled:bg-teal-300" disabled>
                            Kirim Solusi & Evaluasi
                        </button>
                    </div>
                </div>
                <p id="loading-status" class="text-center text-sm mt-3 text-red-500 hidden font-medium animate-pulse">
                    AI Master sedang menganalisis... Tunggu sebentar.
                </p>
            </div>

            <!-- Kanan: AI Master Feedback -->
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">🧠 AI Master (<span id="ai-role-title">Mentor</span>)</h3>
                <div id="ai-response-area" class="ai-master-bubble p-4 rounded-xl shadow-inner min-h-[250px] overflow-auto clean-text">
                    <p class="text-gray-800">
                        Selamat datang di Workshop! Silakan pilih jurusan Anda terlebih dahulu.
                    </p>
                </div>
                <div class="mt-4 p-4 bg-white border-2 border-dashed border-gray-300 rounded-lg">
                    <h4 class="font-bold text-lg text-gray-700">🏆 Progres Modul</h4>
                    <p class="text-sm text-gray-500 mt-1">Tantangan Selesai: <span id="challenges-completed">0</span></p>
                    <p class="text-sm text-gray-500">Skor Akumulasi: <span id="total-score">0</span></p>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals untuk Pemberitahuan -->
    <div id="modal-container" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-sm">
            <h4 id="modal-title" class="text-xl font-bold mb-3 text-indigo-600">Pemberitahuan</h4>
            <p id="modal-message" class="text-gray-700 mb-4">Pesan akan ditampilkan di sini.</p>
            <button id="modal-close-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition duration-150">Tutup</button>
        </div>
    </div>

    <!-- Script untuk Game Logic -->
    <script type="module">
        // Global Constants & Variables
       const apiKey =  <?php echo $apiKeyJson; ?>; 
         const md =  <?php echo json_encode($model); ?>;
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey}`;

        // Global Game State
        let currentChallenge = null;
        let currentMajor = null;
        let currentTheme = null;
        let gameProgress = {
            challengesCompleted: 0,
            totalScore: 0,
        };
        
        // UI Elements
        const $ = (id) => document.getElementById(id);
        const $startScreen = $('start-screen');
        const $app = $('app');
        const $majorSelect = $('major-select');
        const $customMajorInput = $('custom-major-input');
        const $projectThemeInput = $('project-theme-input');
        const $startGameBtn = $('start-game-btn');
        const $moduleTitle = $('module-title');
        const $workAreaTitle = $('work-area-title');
        const $aiRoleTitle = $('ai-role-title');

        const $challengeDisplay = $('challenge-display');
        const $codeInput = $('code-input');
        const $aiResponseArea = $('ai-response-area');
        const $startBtn = $('start-btn');
        const $suggestionBtn = $('suggestion-btn');
        const $hintBtn = $('hint-btn');
        const $submitBtn = $('submit-btn');
        const $loadingStatus = $('loading-status');
        const $modalContainer = $('modal-container');
        const $modalTitle = $('modal-title');
        const $modalMessage = $('modal-message');
        const $modalCloseBtn = $('modal-close-btn');

        // Major Definitions
        const majors = {
            RPL: {
                name: "Rekayasa Perangkat Lunak",
                emoji: "💻",
                role: "Startup Mentor",
                workArea: "Area Kerja Kode (JavaScript)",
                // Tantangan harus relevan dengan tema proyek (%s)
                challengePrompt: "Buatkan tantangan pemrograman JavaScript murni. Tantangan harus relevan dengan proyek '%s' dan membutuhkan implementasi fungsi/logika. Contoh: 'Buatlah fungsi utama untuk fitur login pada sistem kasir yang menerima username dan password.'",
                masterPromptBase: "Anda adalah AI Startup Mentor yang tegas, cerdas, dan memotivasi. TUGAS UTAMA Anda adalah menilai fungsionalitas, efisiensi, dan kebersihan kode."
            },
            Otomotif: {
                name: "Teknik Otomotif",
                emoji: "⚙️",
                role: "Mekanik Ahli",
                workArea: "Area Kerja Perakitan (Daftar/Prosedur)",
                // Tantangan harus relevan dengan tema proyek (%s)
                challengePrompt: "Buatkan tantangan perakitan atau diagnosa kerusakan mesin yang relevan dengan proyek '%s'. Minta siswa membuat daftar urutan perakitan, komponen yang dibutuhkan, atau langkah diagnosa. Contoh: 'Buatlah urutan langkah perakitan yang benar untuk memasang piston pada blok mesin V6.'",
                masterPromptBase: "Anda adalah AI Mekanik Ahli yang fokus pada presisi dan efisiensi mekanik. TUGAS UTAMA Anda adalah menilai urutan, kelengkapan komponen, dan logika mekanik. Peringatkan soal keselamatan kerja."
            },
            Elektro: {
                name: "Teknik Elektro",
                emoji: "⚡",
                role: "Ahli Rangkaian",
                workArea: "Area Kerja Rangkaian (Deskripsi/Komponen)",
                // Tantangan harus relevan dengan tema proyek (%s)
                challengePrompt: "Buatkan tantangan perancangan rangkaian listrik atau mikrokontroler yang relevan dengan proyek '%s'. Minta siswa mendeskripsikan komponen dan cara kerjanya. Contoh: 'Jelaskan komponen yang dibutuhkan dan cara merangkai sensor suhu digital (DS18B20) dengan Arduino.'",
                masterPromptBase: "Anda adalah AI Ahli Rangkaian yang berfokus pada aliran arus, keselamatan, dan efisiensi daya. TUGAS UTAMA Anda adalah menilai komponen yang dipilih, logika rangkaian, dan petunjuk keselamatan kerja."
            },
            Busana: {
                name: "Tata Busana",
                emoji: "👗",
                role: "Fashion Consultant",
                workArea: "Area Kerja Desain (Pola/Bahan)",
                // Tantangan harus relevan dengan tema proyek (%s)
                challengePrompt: "Buatkan tantangan desain busana yang relevan dengan proyek '%s'. Minta siswa mendeskripsikan pola dasar yang digunakan, pilihan bahan, dan justifikasi estetikanya. Contoh: 'Rancanglah blus kerja formal untuk iklim tropis. Sebutkan pola yang digunakan, jenis bahan, dan alasan pemilihan warna.'",
                masterPromptBase: "Anda adalah AI Fashion Consultant yang fokus pada estetika, efisiensi bahan, dan relevansi desain. TUGAS UTAMA Anda adalah menilai kreativitas, kepraktisan, dan kesesuaian bahan dengan desain."
            },
            Bangunan: {
                name: "Teknik Bangunan",
                emoji: "🏗️",
                role: "Project Manager",
                workArea: "Area Kerja Struktur (Rencana/Biaya)",
                // Tantangan harus relevan dengan tema proyek (%s)
                challengePrompt: "Buatkan tantangan perencanaan struktur atau manajemen biaya yang relevan dengan proyek '%s'. Minta siswa mendeskripsikan tahapan konstruksi, material utama, dan estimasi risiko. Contoh: 'Buatlah rencana pondasi yang tepat dan estimasi material utama untuk pembangunan rumah 1 lantai di tanah lunak.'",
                masterPromptBase: "Anda adalah AI Project Manager yang fokus pada stabilitas struktural, efisiensi material, dan anggaran. TUGAS UTAMA Anda adalah menilai perencanaan struktur, analisis risiko, dan efisiensi biaya."
            },
            CUSTOM_PLACEHOLDER: { // Placeholder for logic template
                name: "Jurusan Kustom",
                emoji: "🌟",
                role: "Spesialis Proyek",
                workArea: "Area Kerja Solusi/Langkah",
                // Tantangan harus relevan dengan tema proyek (%s)
                challengePrompt: "Buatkan tantangan spesifik yang relevan dengan Jurusan yang dipilih, fokus pada proyek '%s'. Minta siswa mendeskripsikan langkah kerja, material, atau pola yang diperlukan. Contoh: 'Jelaskan prosedur sanitasi dan pembuatan masakan utama yang tepat untuk acara katering bertema Western.'",
                masterPromptBase: "Anda adalah AI Spesialis Proyek yang fokus pada prosedur, kelengkapan, dan kualitas hasil di bidang yang tidak terdaftar. TUGAS UTAMA Anda adalah menilai langkah-langkah kerja, kesesuaian material, dan efisiensi waktu. Peringatkan tentang keamanan atau standar industri jika relevan."
            }
        };


        // --- Utility Functions ---

        /**
         * Membersihkan teks dari karakter Markdown umum (**bold**, *italic*, #heading)
         * dan memformat baris baru menjadi tag HTML <p> untuk tampilan yang lebih rapi.
         * @param {string} text Teks mentah dari respons AI.
         * @returns {string} Teks yang sudah dibersihkan dan diformat dalam tag <p>.
         */
        function cleanMarkdownText(text) {
            if (!text) return '';
            // 1. Hapus karakter bold dan italic (** dan *)
            let cleaned = text.replace(/\*\*/g, '').replace(/\*/g, '');
            // 2. Hapus karakter heading (#, ##, dll.)
            cleaned = cleaned.replace(/^#+\s*/gm, '');
            // 3. Trim whitespace di awal/akhir
            cleaned = cleaned.trim();
            
            // 4. Pisahkan teks menjadi paragraf berdasarkan baris baru ganda
            const paragraphs = cleaned.split(/\n\s*\n/).filter(p => p.trim() !== '');

            // 5. Ubah setiap paragraf menjadi tag <p> dan gabungkan, 
            //    mengganti baris baru tunggal (\n) di dalamnya dengan spasi
            let htmlContent = paragraphs.map(p => {
                // Ganti baris baru tunggal yang mungkin ada di tengah paragraf dengan spasi
                return `<p class="mb-2">${p.replace(/\n/g, ' ')}</p>`;
            }).join('');
            
            return htmlContent;
        }

        function showModal(title, message) {
            $modalTitle.textContent = title;
            $modalMessage.innerHTML = message;
            $modalContainer.classList.remove('hidden');
        }

        $modalCloseBtn.onclick = () => {
            $modalContainer.classList.add('hidden');
        };

        function updateUI() {
            $('challenges-completed').textContent = gameProgress.challengesCompleted;
            $('total-score').textContent = gameProgress.totalScore;

            // Start button disabled if a challenge is currently active
            $startBtn.disabled = currentChallenge !== null; 

            // Working buttons disabled if no challenge is active
            $codeInput.disabled = currentChallenge === null;
            $suggestionBtn.disabled = currentChallenge === null;
            $hintBtn.disabled = currentChallenge === null;
            $submitBtn.disabled = currentChallenge === null;

            // Toggle active input style
            if (currentChallenge !== null) {
                $codeInput.classList.add('input-active');
            } else {
                $codeInput.classList.remove('input-active');
            }
        }

        function setLoading(isLoading) {
            $loadingStatus.classList.toggle('hidden', !isLoading);
            
            // Disable all working buttons while loading
            $startBtn.disabled = isLoading || currentChallenge !== null;
            $suggestionBtn.disabled = isLoading || currentChallenge === null;
            $hintBtn.disabled = isLoading || currentChallenge === null;
            $submitBtn.disabled = isLoading || currentChallenge === null;

            // Ensure input remains disabled if loading OR no challenge
            $codeInput.disabled = isLoading || currentChallenge === null;
        }
        
        async function saveProgress() {
            try {
                localStorage.setItem('aiWorkshopGameProgress', JSON.stringify(gameProgress));
                localStorage.setItem('aiWorkshopCurrentMajor', currentMajor);
                localStorage.setItem('aiWorkshopCurrentTheme', currentTheme);
                console.log("Game progress saved to local storage.");
            } catch (error) {
                console.error("Error saving game progress:", error);
            }
        }

        async function loadProgress() {
            try {
                const savedProgress = localStorage.getItem('aiWorkshopGameProgress');
                const savedMajor = localStorage.getItem('aiWorkshopCurrentMajor');
                const savedTheme = localStorage.getItem('aiWorkshopCurrentTheme');

                if (savedProgress) {
                    gameProgress = JSON.parse(savedProgress);
                }
                
                if (savedMajor && savedTheme) {
                    // Automatically start the game if progress exists
                    startGame(savedMajor, savedTheme, false); // Don't save again
                } else {
                    $startScreen.classList.remove('hidden');
                    $app.classList.add('hidden');
                }
                console.log("Game progress loaded.");
            } catch (error) {
                console.error("Error loading game progress:", error);
            }
        }

        // --- Selection Screen Logic ---

        function renderSelectionScreen() {
            const majorKeys = Object.keys(majors).filter(key => key !== 'CUSTOM_PLACEHOLDER');

            majorKeys.forEach(key => {
                const major = majors[key];
                const option = document.createElement('option');
                option.value = key;
                option.textContent = `${major.emoji} ${major.name}`;
                $majorSelect.appendChild(option);
            });
            
            // Tambahkan opsi kustom
            const customOption = document.createElement('option');
            customOption.value = 'CUSTOM';
            customOption.textContent = `Lainnya (Tulis Sendiri)`;
            $majorSelect.appendChild(customOption);


            $majorSelect.addEventListener('change', handleMajorSelection);
            $projectThemeInput.addEventListener('input', checkStartButtonStatus);
            $customMajorInput.addEventListener('input', checkStartButtonStatus);
        }

        function handleMajorSelection() {
            const selectedValue = $majorSelect.value;
            
            if (selectedValue === 'CUSTOM') {
                $customMajorInput.classList.remove('hidden');
                $customMajorInput.focus();
                currentMajor = null; // Clear major until custom input is valid
            } else if (selectedValue !== '') {
                $customMajorInput.classList.add('hidden');
                currentMajor = selectedValue;
            }
            checkStartButtonStatus();
        }

        function checkStartButtonStatus() {
            const theme = $projectThemeInput.value.trim();
            const themeValid = theme.length >= 5;
            let majorValid = false;

            if ($majorSelect.value === 'CUSTOM') {
                // If custom is selected, validate the custom input field
                majorValid = $customMajorInput.value.trim().length >= 3;
            } else {
                // Otherwise, validate the select dropdown value
                majorValid = $majorSelect.value !== '';
            }

            $startGameBtn.disabled = !(majorValid && themeValid);
        }

        function startGame(majorKeyOrName, theme, shouldSave = true) {
            currentTheme = theme;
            let majorConfig;
            let actualMajorName;

            if (majors[majorKeyOrName] && majors[majorKeyOrName].name !== "Jurusan Kustom") {
                // Predefined major selected
                currentMajor = majorKeyOrName;
                majorConfig = majors[currentMajor];
                actualMajorName = majorConfig.name;
            } else {
                // Custom major logic (majorKeyOrName is the custom name)
                currentMajor = majorKeyOrName; // Store the custom name as the key
                majorConfig = majors['CUSTOM_PLACEHOLDER']; // Use placeholder template
                actualMajorName = majorKeyOrName; // Use the custom name for display/prompts
            }
            
            // Update UI elements based on selection
            $moduleTitle.textContent = `Modul: ${actualMajorName} - Proyek: ${theme}`;
            $aiRoleTitle.textContent = majorConfig.role;
            $workAreaTitle.textContent = majorConfig.workArea;

            // Show main game, hide start screen
            $startScreen.classList.add('hidden');
            $app.classList.remove('hidden');
            
            const initialMessageRaw = shouldSave 
                ? `Selamat, ${majorConfig.role} Anda siap! Kita akan fokus pada proyek tentang '${currentTheme}' di Jurusan ${actualMajorName}. Klik tombol di bawah untuk menerima tantangan pertama!`
                : `Selamat datang kembali di Workshop ${actualMajorName}! Proyek Anda: '${currentTheme}'. Lanjutkan pekerjaan Anda!`;

            $aiResponseArea.innerHTML = cleanMarkdownText(initialMessageRaw);

            if (shouldSave) {
                // Reset progress for a new game/major
                gameProgress = { challengesCompleted: 0, totalScore: 0 };
                saveProgress();
            }

            updateUI();
        }
        
        // --- Gemini API Handler (Unchanged) ---

        async function fetchGeminiResponse(systemPrompt, userQuery, maxRetries = 5) {
            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                tools: [{ "google_search": {} }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
            };

            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        if (response.status === 429 && i < maxRetries - 1) {
                            // Exponential backoff
                            const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                            await new Promise(resolve => setTimeout(resolve, delay));
                            continue;
                        }
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const result = await response.json();
                    const text = result.candidates?.[0]?.content?.parts?.[0]?.text || 'Error: No response text.';
                    return text;

                } catch (error) {
                    console.error("Gemini API call failed:", error);
                    if (i === maxRetries - 1) {
                         return "Maaf, terjadi kesalahan komunikasi dengan AI Master. Silakan coba lagi.";
                    }
                }
            }
            return "Maaf, terjadi kesalahan komunikasi yang tidak terduga. Silakan coba lagi.";
        }

        // --- Game Logic (Modified to use Major/Theme) ---
        
        function getMajorConfig() {
            if (majors[currentMajor] && majors[currentMajor].name !== "Jurusan Kustom") {
                return { config: majors[currentMajor], name: majors[currentMajor].name };
            } else {
                return { config: majors['CUSTOM_PLACEHOLDER'], name: currentMajor };
            }
        }
        
        async function startNewChallenge() {
            setLoading(true);
            const { config: majorConfig, name: actualMajorName } = getMajorConfig();

            $aiResponseArea.innerHTML = cleanMarkdownText(`AI Master: Menganalisis kebutuhan proyek '${currentTheme}' untuk tantangan yang pas...`);

            // ******************************************************
            // RELEVANSI: Mengganti %s di prompt tantangan dengan tema proyek.
            // Konteks Jurusan/Peran sudah ditangani oleh System Prompt di bawah.
            // ******************************************************
            const specificChallengeQuery = majorConfig.challengePrompt.replace('%s', currentTheme);

            try {
                // RELEVANSI: System Prompt menetapkan peran AI sebagai generator tugas di jurusan yang spesifik.
                const challengeResponse = await fetchGeminiResponse(
                    `Anda adalah generator tugas untuk siswa SMK jurusan ${actualMajorName} dengan peran ${majorConfig.role}. Berikan hanya teks tantangan spesifik yang harus diselesaikan siswa tanpa penjelasan tambahan.`,
                    specificChallengeQuery
                );
                
                currentChallenge = challengeResponse.trim(); // Simpan respons mentah untuk prompt berikutnya
                const cleanedChallengeDisplay = cleanMarkdownText(currentChallenge);
                
                $challengeDisplay.innerHTML = `<h4 class="font-bold text-lg text-indigo-700">TANTANGAN BARU!</h4>${cleanedChallengeDisplay}<p class="mt-2 text-xs italic text-gray-500">Masukan solusi di ${majorConfig.workArea}.</p>`;
                $codeInput.value = '';
                
                const initialMessage = `Waktunya berkarya! ${currentChallenge} Ayo, tunjukkan bagaimana kita membangun solusi yang efisien!`;
                $aiResponseArea.innerHTML = cleanMarkdownText(initialMessage);
                
                showModal("Tantangan Dimulai!", "Anda telah menerima tugas baru dari AI Master. Segera tulis solusi Anda di Area Kerja.");
                
            } catch (error) {
                $aiResponseArea.textContent = 'Gagal memuat tantangan. Coba lagi.';
                currentChallenge = null;
            } finally {
                updateUI(); // Panggil updateUI untuk mengaktifkan input
                setLoading(false);
            }
        }
        
        async function requestSuggestion() {
            setLoading(true);
            const { config: majorConfig, name: actualMajorName } = getMajorConfig();
            
            $aiResponseArea.innerHTML = cleanMarkdownText(`AI Master: Mencari praktik terbaik dan rekomendasi untuk tantangan ini...`);
            
            const prompt = `Tantangannya adalah: "${currentChallenge}". Berikan saran, praktik terbaik standar industri, atau rekomendasi sumber daya yang relevan (misalnya: jenis material yang efisien, standar coding, atau langkah keamanan). JANGAN berikan solusi penuh.`;
            
            // RELEVANSI: System Prompt menetapkan peran AI sebagai Senior Consultant/Guru Besar di bidang yang spesifik.
            const suggestionSystemPrompt = `Anda adalah AI Senior Consultant/Guru Besar di bidang ${actualMajorName}. TUGAS UTAMA Anda adalah memberikan saran yang proaktif, cerdas, dan informatif kepada siswa. Jangan berikan skor.`;

            try {
                const response = await fetchGeminiResponse(suggestionSystemPrompt, prompt);
                const cleanedResponse = cleanMarkdownText(response);
                $aiResponseArea.innerHTML = `<p class="text-blue-700 font-bold">Saran AI Master:</p>${cleanedResponse}`;
                showModal("Saran AI Diberikan!", "AI Master telah memberikan rekomendasi. Baca saran di panel kanan sebelum melanjutkan!");

            } finally {
                setLoading(false);
            }
        }

        async function requestHint() {
            setLoading(true);
            const { config: majorConfig, name: actualMajorName } = getMajorConfig();
            
            const userCode = $codeInput.value;
            const prompt = `Ini tantangannya: "${currentChallenge}". Dan ini adalah pekerjaan saya saat ini:\n\n---\n${userCode}\n---\n. Berikan petunjuk cerdas, fokus pada kesalahan logika atau urutan tanpa memberikan solusi penuh. Jangan berikan skor.`;
            
            // RELEVANSI: System Prompt diadaptasi dari masterPromptBase, mempertahankan persona dan fokus kritik.
            const hintSystemPrompt = majorConfig.masterPromptBase.replace("TUGAS UTAMA", "TUGAS UTAMA (HANYA MEMBERIKAN PETUNJUK)");

            try {
                const response = await fetchGeminiResponse(hintSystemPrompt, prompt);
                const cleanedResponse = cleanMarkdownText(response);
                $aiResponseArea.innerHTML = `<p class="text-yellow-700 font-bold">Petunjuk Koreksi:</p>${cleanedResponse}`;
                showModal("Petunjuk dari AI Master", "AI Master telah menganalisis pekerjaan Anda dan memberikan petunjuk!");

            } finally {
                setLoading(false);
            }
        }

        async function submitSolution() {
            setLoading(true);
            const { config: majorConfig } = getMajorConfig();
            
            const userCode = $codeInput.value;
            
            if (userCode.length < 10) {
                 showModal("Peringatan", "Solusi terlalu pendek! Tulis pekerjaan Anda lebih dahulu.");
                 setLoading(false);
                 return;
            }

            const prompt = `Saya telah menyelesaikan tantangan ini: "${currentChallenge}". Ini adalah solusi/pekerjaan saya:\n\n---\n${userCode}\n---\n. Harap evaluasi KELENGKAPAN, EFISIENSI, dan KUALITAS HASIL. Berikan skor akurat (0-100) dan kritik membangun.`;
            
            // RELEVANSI: Full System Prompt menggunakan masterPromptBase, memastikan AI menilai berdasarkan kriteria jurusan.
            const fullMasterPrompt = majorConfig.masterPromptBase + "\nSkor total harus dicantumkan di AKHIR respon dalam format: 'SKOR AKHIR: [Skor]/100'.";

            try {
                const response = await fetchGeminiResponse(fullMasterPrompt, prompt);
                
                // Extract score using regex
                const scoreMatch = response.match(/SKOR AKHIR: \[(\d+)\/100\]/i);
                let score = 0;
                if (scoreMatch && scoreMatch[1]) {
                    score = parseInt(scoreMatch[1], 10);
                }

                const cleanedResponse = cleanMarkdownText(response);
                $aiResponseArea.innerHTML = cleanedResponse;

                // Update game progress
                gameProgress.totalScore += score;
                gameProgress.challengesCompleted += 1;
                
                await saveProgress();
                
                currentChallenge = null;
                $challengeDisplay.innerHTML = 'Tantangan Selesai! Klik "Mulai Tantangan Baru" untuk melanjutkan level Anda.';
                $codeInput.value = `// Selesai! Klik tombol Mulai untuk tugas berikutnya. Skor Anda: ${score}/100`;
                
                showModal("Evaluasi Selesai!", `AI Master telah memberikan skor: ${score}/100. Baca kritik di panel kanan!`);

            } finally {
                updateUI(); // Panggil updateUI untuk menonaktifkan input dan memperbarui skor
                setLoading(false);
            }
        }

        // --- Event Listeners ---
        $startGameBtn.addEventListener('click', () => {
            let selectedMajorKey = $majorSelect.value;
            const theme = $projectThemeInput.value.trim();
            
            if (selectedMajorKey === 'CUSTOM') {
                selectedMajorKey = $customMajorInput.value.trim();
            }

            if (selectedMajorKey && theme.length >= 5) {
                startGame(selectedMajorKey, theme);
            } else {
                showModal("Peringatan", "Mohon pilih Jurusan (atau isi Jurusan Kustom) dan masukkan Tema Proyek minimal 5 karakter.");
            }
        });
        
        $startBtn.addEventListener('click', startNewChallenge);
        $suggestionBtn.addEventListener('click', requestSuggestion);
        $hintBtn.addEventListener('click', requestHint);
        $submitBtn.addEventListener('click', submitSolution);

        // --- Initial Setup ---
        async function initializeGame() {
            renderSelectionScreen();
            await loadProgress();
        }
        
        window.onload = initializeGame;

    </script>
</body>
</html>
