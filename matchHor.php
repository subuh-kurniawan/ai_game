<?php
include "../admin/fungsi/koneksi.php";
$sql = mysqli_query($koneksi, "SELECT * FROM datasekolah");
$data = mysqli_fetch_assoc($sql);
// Baca konfigurasi API Key dari file JSON
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
    <title>Math Dungeon Quest</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- KaTeX CSS for mathematical rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css" crossorigin="anonymous">
    <!-- KaTeX JS for mathematical rendering -->
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js" crossorigin="anonymous"></script>

    <style>
        /* Custom styles to mimic a retro text-based RPG/terminal */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
        }
        .chat-container {
            height: calc(100vh - 180px); /* Adjusted for status bar and input */
            max-height: 800px;
        }
        .gm-message {
            background-color: #2a3d45; /* Dark teal background for GM */
            border-left: 3px solid #66bb6a; /* Green accent */
        }
        .player-message {
            background-color: #313131; /* Slightly lighter dark for player */
            align-self: flex-end;
        }
        .gm-message, .player-message {
            padding: 12px;
            margin-bottom: 10px;
            max-width: 90%;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        .status-bar {
            background-color: #1a1a1a;
            border-bottom: 2px solid #66bb6a;
        }
        /* KaTeX rendering style adjustments for dark theme */
        .gm-message .katex {
            font-size: 1.1rem;
            color: #ffee58; /* Yellow/Gold for math equations */
            padding: 5px 0;
            display: inline-block;
        }
        .loading-indicator {
            padding: 8px;
            border-radius: 4px;
            background-color: #4CAF50;
            color: white;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { opacity: 0.8; }
            50% { opacity: 1; }
            100% { opacity: 0.8; }
        }
        /* Overlay Style for Game Setup */
        #game-setup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            z-index: 100;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.5s;
            pointer-events: none; /* Hidden by default */
        }
        #game-setup-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
    </style>
</head>
<body class="p-4 flex flex-col h-screen antialiased">

    <!-- Game Setup Overlay -->
    <div id="game-setup-overlay" class="active">
        <div class="bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md border border-green-500/50">
            <h2 class="text-2xl font-bold text-green-400 mb-4 text-center">Mulai Petualangan Baru</h2>
            <p class="text-sm text-gray-400 mb-6 text-center">Pilih pengaturan awal untuk Dungeon Matematika Anda.</p>
            
            <form id="game-setup-form">
                
                <!-- Level / Kesulitan -->
                <div class="mb-5">
                    <label for="difficulty" class="block text-sm font-medium text-gray-300 mb-1">Tingkat Kesulitan (Level Awal)</label>
                    <select id="difficulty" name="difficulty" class="w-full p-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-green-500 focus:ring-1 focus:ring-green-500">
                        <option value="1">Mudah (Level 1 - Aljabar Dasar)</option>
                        <option value="3">Sedang (Level 3 - Geometri & Perbandingan)</option>
                        <option value="5">Sulit (Level 5 - Logika & Matriks)</option>
                    </select>
                </div>

                <!-- Topik / Tema -->
                <div class="mb-5">
                    <label for="topic" class="block text-sm font-medium text-gray-300 mb-1">Tema Dungeon</label>
                    <select id="topic" name="topic" class="w-full p-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-green-500 focus:ring-1 focus:ring-green-500">
                        <option value="Reruntuhan Kuno Mesir">Reruntuhan Kuno Mesir (Piramida, Hieroglif)</option>
                        <option value="Stasiun Luar Angkasa Rusak">Stasiun Luar Angkasa Rusak (Sistem, Robot)</option>
                        <option value="Hutan Fantasi Berhantu">Hutan Fantasi Berhantu (Peri, Mantra)</option>
                        <option option value="Kapal Bajak Laut Mistis">Kapal Bajak Laut Mistis (Harta Karun, Lautan)</option>
                    </select>
                </div>

                <!-- Alur Cerita / Misi -->
                <div class="mb-6">
                    <label for="storyline" class="block text-sm font-medium text-gray-300 mb-1">Misi / Alur Cerita</label>
                    <textarea id="storyline" name="storyline" rows="3" class="w-full p-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-green-500 focus:ring-1 focus:ring-green-500" placeholder="Tulis tujuan Anda, e.g., 'Mencari artefak kuno yang hilang'"></textarea>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition-colors duration-200 shadow-lg">
                    MASUK DUNGEON
                </button>
            </form>
        </div>
    </div>
    
    <!-- Status Bar -->
    <div id="status-bar" class="status-bar p-3 rounded-t-lg shadow-xl mb-4 flex justify-between items-center text-sm font-mono transition-opacity duration-500">
        <span id="game-status" class="text-green-400 font-bold">Memuat Game...</span>
        <!-- Tombol Reset Game -->
        <button id="reset-button" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-3 rounded-md transition-colors duration-200 shadow-md" onclick="resetGame()">Hapus Progres</button>
    </div>

    <!-- Chat Log / Game Output -->
    <div id="chat-box" class="chat-container flex flex-col space-y-2 overflow-y-auto p-4 bg-gray-900 rounded-lg shadow-inner mb-4 flex-grow">
        <!-- Messages will be injected here -->
        <div id="loading-initial" class="gm-message text-center">
            <span class="loading-indicator">Memuat progres dari penyimpanan lokal...</span>
        </div>
    </div>

    <!-- Input Area -->
    <form id="input-form" class="flex space-x-2">
        <input type="text" id="player-input" placeholder="Masukkan jawaban (e.g. 7, Hint)..." class="flex-grow p-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:border-green-500 focus:ring-1 focus:ring-green-500 disabled:opacity-50" autocomplete="off" disabled>
        <button type="submit" id="submit-button" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200 shadow-md disabled:bg-gray-500" disabled>Kirim</button>
        <button type="button" id="hint-button" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200 shadow-md disabled:bg-gray-500">Hint</button>
    </form>
    
    <!-- Custom Modal for Confirmation (REPLACING window.confirm) -->
    <div id="custom-confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
        <div class="bg-gray-800 p-6 rounded-lg shadow-xl max-w-sm w-full border border-red-500">
            <h3 class="text-xl font-bold text-red-400 mb-3">Konfirmasi Reset Progres</h3>
            <p id="confirm-message" class="text-gray-300 mb-6">PERHATIAN: Ini akan menghapus SEMUA progres game Anda secara permanen. Lanjutkan?</p>
            <div class="flex justify-end space-x-3">
                <button id="confirm-no" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                    Batal
                </button>
                <button id="confirm-yes" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>


    <script type="module">
        // --- GLOBAL STATE & CONFIGURATION ---
        const MODEL_NAME = 'gemini-2.5-flash-preview-09-2025';
        const apiKey = "AIzaSyAYYBCPplYs1pd3vqu5e13YsbF1hgQz8EY"; // API Key is provided by the environment
        const MAX_RETRIES = 5;
        const storageKey = 'mathDungeonQuestState'; // Kunci penyimpanan LocalStorage
        
        let gameState = {
            level: 1,
            xp: 0,
            maxXP: 100,
            hints: 3, 
            history: [],
            isNewGame: true, 
            topic: '',
            storyline: ''
        };

        // --- LOCALSTORAGE STATE MANAGEMENT (Pengganti Firebase) ---
        
        /**
         * Shows a custom confirmation dialog.
         * @param {string} message The message to display.
         * @returns {Promise<boolean>} Resolves to true if confirmed, false otherwise.
         */
        function showCustomConfirm(message) {
            return new Promise(resolve => {
                const modal = document.getElementById('custom-confirm-modal');
                const msgElement = document.getElementById('confirm-message');
                const yesButton = document.getElementById('confirm-yes');
                const noButton = document.getElementById('confirm-no');

                msgElement.textContent = message;
                modal.classList.remove('hidden');

                const cleanup = (result) => {
                    modal.classList.add('hidden');
                    yesButton.onclick = null;
                    noButton.onclick = null;
                    resolve(result);
                };

                yesButton.onclick = () => cleanup(true);
                noButton.onclick = () => cleanup(false);
            });
        }

        /**
         * Clears the game state from localStorage and reloads the page.
         */
        function resetGame() {
            // Menggunakan modal kustom sebagai ganti window.confirm()
            showCustomConfirm("PERHATIAN: Ini akan menghapus SEMUA progres game Anda secara permanen. Lanjutkan?")
                .then(confirmed => {
                    if (confirmed) {
                        localStorage.removeItem(storageKey); 
                        // Set a flag to prevent re-initializing with corrupted data right away
                        window.location.reload(); 
                    }
                });
        }
        window.resetGame = resetGame; // Membuat fungsi tersedia secara global

        /**
         * Robustly loads game state, handling corrupted or missing keys.
         */
        function loadGameState() {
            const setupOverlay = document.getElementById('game-setup-overlay');
            try {
                const storedState = localStorage.getItem(storageKey);
                
                if (storedState) {
                    const loadedState = JSON.parse(storedState);

                    // Robustness Check 1: Ensure essential keys exist and are of correct type
                    const isValidState = loadedState && 
                                         typeof loadedState.level === 'number' &&
                                         typeof loadedState.xp === 'number' &&
                                         Array.isArray(loadedState.history);

                    if (isValidState && loadedState.history.length > 0 && !loadedState.isNewGame) {
                        // Valid, continuing game
                        gameState = { 
                            ...gameState, 
                            ...loadedState
                        };
                        setupOverlay.classList.remove('active');
                        updateUIFromState(true); // Initial render from history
                        renderSystemMessage("Progres dimuat. Lanjutkan petualangan!");
                    } else {
                        // State is corrupted, incomplete, or marked as new game
                        console.warn("Stored state is invalid or marked as new game. Starting setup.");
                        setupOverlay.classList.add('active');
                        document.getElementById('loading-initial')?.remove();
                    }
                    return true;
                } else {
                    console.log("No game state found. Starting new game setup.");
                    setupOverlay.classList.add('active');
                    document.getElementById('loading-initial')?.remove();
                    return false;
                }
            } catch (error) {
                console.error("Error loading or parsing game state from LocalStorage. Forcing new game.", error);
                renderSystemMessage(`[ERROR KRITIS] Gagal memuat progres (LocalStorage rusak). Anda harus mengklik 'Hapus Progres' dan memulai baru.`);
                setupOverlay.classList.add('active');
                return false;
            }
        }

        /**
         * Saves current game state to localStorage.
         */
        async function saveGameState() {
            try {
                // Ensure history is not excessively long
                const historyToSave = gameState.history.slice(-30); // Save last 30 turns
                const stateToSave = { 
                    ...gameState, 
                    history: historyToSave,
                    isNewGame: false 
                };
                localStorage.setItem(storageKey, JSON.stringify(stateToSave));
            } catch (error) {
                console.error("Error saving game state to LocalStorage:", error);
                renderSystemMessage(`[ERROR] Gagal menyimpan progres game. Pastikan browser Anda mengizinkan penyimpanan lokal.`);
            }
        }
        
        /**
         * Handles the start of a new game.
         */
        function handleGameSetup(e) {
            e.preventDefault();
            const form = e.target;
            
            const difficulty = form.elements['difficulty'].value;
            const topic = form.elements['topic'].value;
            const storyline = form.elements['storyline'].value.trim() || "Mencari artefak kuno yang hilang.";
            
            // Set initial state based on setup
            gameState.level = parseInt(difficulty);
            gameState.isNewGame = false;
            gameState.topic = topic;
            gameState.storyline = storyline;

            const setupOverlay = document.getElementById('game-setup-overlay');
            setupOverlay.classList.remove('active'); 
            document.getElementById('loading-initial')?.remove();

            // Initial prompt with full context
            const initialPrompt = `Mulai permainan Math Dungeon Quest. Pengaturan:
            - Kesulitan Awal: Level ${gameState.level}
            - Tema Dungeon: ${topic}
            - Misi/Alur Cerita: ${storyline}
            
            Ceritakan kisah awal petualangan ini dan berikan soal pertama. Respons GM harus selalu dalam Bahasa Indonesia.`;
            
            updateUIFromState(false); 
            sendPlayerAction('initial', initialPrompt);
            saveGameState(); // Save state immediately after setup
        }


        // --- UI & MESSAGE RENDERING ---

        /**
         * Renders text, handling KaTeX math blocks (robustly).
         */
        function renderKaTeX(text) {
            // Robustly clean up known LLM formatting errors before rendering
            let cleanedText = text.replace(/<ext>|<\/ext>|\[ext\]|\[\/ext\]/gi, ' '); 
            
            // Split by $$...$$ blocks
            const parts = cleanedText.split(/(\$\$[\s\S]*?\$\$)/g); 
            const fragment = document.createDocumentFragment();

            parts.forEach(part => {
                if (part.startsWith('$$') && part.endsWith('$$')) {
                    const mathExpression = part.slice(2, -2).trim();
                    const mathSpan = document.createElement('span');
                    mathSpan.className = 'inline-block my-2 text-center w-full overflow-x-auto'; // Add overflow protection
                    try {
                        katex.render(mathExpression, mathSpan, {
                            throwOnError: false,
                            displayMode: true 
                        });
                        fragment.appendChild(mathSpan);
                    } catch (e) {
                        console.error("KaTeX rendering error for:", mathExpression, e);
                        fragment.appendChild(document.createTextNode(`[ERROR KaTeX: Formula Tidak Valid]`));
                    }
                } else {
                    if (part.trim().length > 0) {
                         // Convert narrative newlines to <br>
                        part.split('\n').forEach((line, index) => {
                            if (index > 0) fragment.appendChild(document.createElement('br'));
                            fragment.appendChild(document.createTextNode(line));
                        });
                    }
                }
            });
            return fragment;
        }

        function renderMessage(text, sender) {
            const chatBox = document.getElementById('chat-box');
            const msgDiv = document.createElement('div');
            
            if (sender === 'gm') {
                msgDiv.className = 'gm-message self-start shadow-xl';
            } else if (sender === 'player') {
                msgDiv.className = 'player-message self-end text-right';
            } else {
                 // System messages (errors, status updates)
                 msgDiv.className = 'gm-message self-center text-center text-yellow-300 border-none bg-gray-700/50';
            }

            const content = renderKaTeX(text);
            msgDiv.appendChild(content);
            
            chatBox.appendChild(msgDiv);
            // Ensure scroll to bottom after new message
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function renderSystemMessage(message) {
            renderMessage(`[SISTEM] ${message}`, 'system');
        }

        function updateUIFromState(isInitialLoad) {
            const statusText = `LEVEL ${gameState.level} | XP ${gameState.xp}/${gameState.maxXP} | PETUNJUK: ${gameState.hints}`;
            document.getElementById('game-status').textContent = statusText;
            
            const chatBox = document.getElementById('chat-box');
            if (isInitialLoad) {
                // Initial load: remove spinner and render history
                document.getElementById('loading-initial')?.remove();
                 gameState.history.forEach(item => {
                     renderMessage(item.text, item.role);
                 });
            }

            // Enable/Disable input based on state
            const inputField = document.getElementById('player-input');
            const submitButton = document.getElementById('submit-button');
            const hintButton = document.getElementById('hint-button');

            const isGameActive = !gameState.isNewGame && gameState.history.length > 0;

            inputField.disabled = !isGameActive;
            submitButton.disabled = !isGameActive;
            hintButton.disabled = !isGameActive || gameState.hints <= 0;
        }
        
        // --- GAME MASTER LOGIC (GEMINI API CALL) ---

        const GM_SYSTEM_INSTRUCTION = (currentLevel) => `
            Anda adalah Game Master (GM) yang menjalankan game petualangan berbasis teks "Math Dungeon Quest".
            1. Persona: Narasi RPG yang singkat, penuh ketegangan, dan adaptif, sesuai dengan tema dan alur cerita yang dipilih pemain: (Tema: ${gameState.topic}, Misi: ${gameState.storyline}).
            2. Tujuan: Pemain harus memecahkan teka-teki matematika untuk maju.
            3. Konten Matematika WAJIB:
               a. Soal matematika HARUS disajikan HANYA di antara simbol dolar ganda (\$\$) untuk KaTeX (Display Mode). Contoh WAJIB: \$\$\text{Jika } 5x + 12 = 37\text{, berapa nilai } x\text{?}\$\$.
               b. **SANGAT PENTING:** JANGAN ulangi persamaan di luar blok \$\$\$\$. Pastikan narasi (teks sebelum dan sesudah \$\$\$\$) mengalir secara alami sehingga soal dan cerita terasa menyatu dalam satu pesan. JANGAN gunakan format Markdown seperti \*\*bold\*\* atau \#heading\# di dalam narasi.
            4. Aturan Jawaban:
               - Jawaban Benar: Beri narasi kemajuan, berikan **+25 XP**, dan berikan soal berikutnya yang sesuai dengan Level saat ini.
               - Jawaban Salah: Beri narasi kegagalan/damage kecil, ulangi soal, atau berikan petunjuk halus. JANGAN berikan jawaban benar.
               - Jika pemain meminta 'Hint' atau 'Petunjuk': Berikan satu petunjuk tanpa mengurangi status, dan ulangi soal.
            5. Tingkat Kesulitan (saat ini Level ${currentLevel}): Sesuaikan jenis soal.
               - Level 1-2: Aljabar Linear, Aritmetika Dasar (Eksponen, Akar).
               - Level 3-4: Geometri (Luas, Volume), Perbandingan.
               - Level 5+: Logika Pola Bilangan, Matriks Dasar (Penjumlahan/Pengurangan).
            6. Output Format WAJIB: Akhiri setiap respons Anda dengan baris khusus yang HANYA berisi status baru pemain. JANGAN tambahkan teks lain di baris ini.
            [NEW_STATUS:L${gameState.level} XP${gameState.xp}/${gameState.maxXP} H${gameState.hints}]
        `;

        async function callGeminiGM(userPrompt, retryCount = 0) {
            const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${MODEL_NAME}:generateContent?key=${apiKey}`;
            
            const contents = [
                // Map history for context
                ...gameState.history.map(item => ({
                    role: item.role === 'gm' ? 'model' : 'user',
                    parts: [{ text: item.text }]
                })),
                { role: 'user', parts: [{ text: userPrompt }] }
            ];

            const payload = {
                contents: contents,
                systemInstruction: {
                    parts: [{ text: GM_SYSTEM_INSTRUCTION(gameState.level) }]
                },
                config: {
                    temperature: 0.7,
                    topP: 0.9,
                }
            };

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    // Handle non-200 responses (e.g., 429 Rate Limit)
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                const candidate = result.candidates?.[0];

                if (candidate && candidate.content?.parts?.[0]?.text) {
                    return candidate.content.parts[0].text;
                } else {
                    // Handle cases where response is OK but content is empty
                    throw new Error("Invalid or empty content response from GM.");
                }

            } catch (error) {
                console.error("Gemini API Error:", error);
                if (retryCount < MAX_RETRIES) {
                    // Exponential backoff
                    const delay = Math.pow(2, retryCount) * 1000;
                    // Do not log retries as errors in console
                    await new Promise(resolve => setTimeout(resolve, delay));
                    return callGeminiGM(userPrompt, retryCount + 1);
                } else {
                    renderSystemMessage(`[FATAL] Gagal berkomunikasi dengan GM setelah ${MAX_RETRIES} kali percobaan. Server API mungkin sibuk. Coba muat ulang.`);
                    // Disable inputs completely on fatal error
                    document.getElementById('player-input').disabled = true;
                    document.getElementById('submit-button').disabled = true;
                    return null;
                }
            }
        }
        
        // --- GAME FLOW AND HANDLERS ---
        
        function parseAndApplyGMResponse(gmResponse) {
            if (!gmResponse) return;

            // 1. Parse Status Line: Find the specific NEW_STATUS tag at the end
            const statusTagRegex = /(\[NEW_STATUS:L(\d+) XP(\d+)\/(\d+) H(\d+)\])$/m;
            const statusMatch = gmResponse.match(statusTagRegex);
            
            // Clean the narrative by removing the status tag
            let narrative = gmResponse.replace(statusTagRegex, '').trim();

            if (statusMatch) {
                // Extract and safely parse status
                const newLevel = parseInt(statusMatch[2]);
                const newXP = parseInt(statusMatch[3]);
                const newMaxXP = parseInt(statusMatch[4]);
                const newHints = parseInt(statusMatch[5]);

                // Check for Level Up condition
                const isLevelUp = newLevel > gameState.level;

                // Apply updates only if parsing was successful
                gameState.level = newLevel;
                gameState.xp = newXP;
                gameState.maxXP = newMaxXP;
                gameState.hints = newHints;
                
                if (isLevelUp) {
                    narrative += "\n\n**LEVEL UP!** Kamu merasakan kekuatan matematika baru mengalir dalam dirimu, siap untuk tantangan yang lebih besar.";
                }

            } else {
                 // Fallback if status tag is missing/corrupted: Log warning and proceed with narrative
                 console.warn("GM response missing or invalid status tag. Using current state for UI refresh.", gmResponse);
                 renderSystemMessage("Peringatan: Data status GM tidak lengkap. Status Anda tidak diubah.");
            }

            // 2. Add GM message to history and UI
            gameState.history.push({ role: 'gm', text: narrative });
            renderMessage(narrative, 'gm');
            
            // 3. Update UI and save progress
            updateUIFromState(false);
            saveGameState(); 
        }

        async function sendPlayerAction(actionType, input) {
            const inputField = document.getElementById('player-input');
            const submitButton = document.getElementById('submit-button');
            const hintButton = document.getElementById('hint-button');
            
            // Disable inputs during processing
            const disableInputs = () => {
                inputField.disabled = true;
                submitButton.disabled = true;
                hintButton.disabled = true;
            };
            const enableInputs = () => {
                inputField.disabled = false;
                submitButton.disabled = false;
                hintButton.disabled = gameState.hints <= 0;
            };

            disableInputs();
            
            let playerText;
            if (actionType === 'hint') {
                if (gameState.hints <= 0) {
                    renderSystemMessage("Tidak ada Petunjuk tersisa. Coba pecahkan sendiri!");
                    enableInputs();
                    return;
                }
                gameState.hints--;
                playerText = `Player meminta Petunjuk. Status Petunjuk: ${gameState.hints}`;
            } else {
                playerText = input.trim();
                inputField.value = '';
            }

            if (!playerText) {
                enableInputs();
                return;
            }

            // Record player message in history and UI
            gameState.history.push({ role: 'user', text: playerText });
            renderMessage(playerText, 'player');

            // Show temporary loading message
            const loadingMsg = document.createElement('div');
            loadingMsg.id = 'temp-loading';
            loadingMsg.className = 'gm-message self-start text-center';
            loadingMsg.innerHTML = '<span class="loading-indicator">GM sedang menyusun tantangan...</span>';
            document.getElementById('chat-box').appendChild(loadingMsg);
            document.getElementById('chat-box').scrollTop = document.getElementById('chat-box').scrollHeight;

            // Call Gemini GM
            const gmResponse = await callGeminiGM(playerText);
            
            // Remove loading message
            document.getElementById('temp-loading')?.remove();

            parseAndApplyGMResponse(gmResponse);
            enableInputs(); // Re-enable inputs after processing
        }


        // --- EVENT LISTENERS & INITIALIZATION ---
        document.getElementById('input-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const inputField = document.getElementById('player-input');
            const input = inputField.value;
            if (input.trim()) {
                sendPlayerAction('answer', input);
            } else {
                renderSystemMessage("Input tidak boleh kosong.");
            }
        });

        document.getElementById('hint-button').addEventListener('click', () => {
            sendPlayerAction('hint', 'Hint');
        });
        
        document.getElementById('game-setup-form').addEventListener('submit', handleGameSetup);

        // Memuat status game saat DOM selesai dimuat
        document.addEventListener('DOMContentLoaded', () => {
            loadGameState();
        });
        
    </script>
</body>
</html>