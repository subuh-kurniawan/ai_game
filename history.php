<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mystery Mission: Sejarah Indonesia</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Space+Mono:wght@400;700&display=swap');
        
        /* Custom font for the game screen */
        #game-output {
            font-family: 'Space Mono', monospace;
            white-space: pre-wrap; /* Preserve formatting and wrap text */
        }
        
        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s ease;
        }
        
        /* Ensure the TTS button is on top */
        #tts-button {
            z-index: 10;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- The container classes are now set by JavaScript based on the theme -->
    <div id="game-container" class="w-full max-w-3xl rounded-xl shadow-2xl p-6 md:p-10 transition-colors duration-300">
        
        <!-- UI Selectors -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 text-sm space-y-2 md:space-y-0">
             <div class="text-xs italic text-gray-500">Petualangan Sejarah Dinamis</div>
            <div class="flex space-x-4">
                <!-- Theme Selector -->
                <div>
                    <label for="theme-selector" class="mr-2 text-gray-400 font-semibold">Visual:</label>
                    <select id="theme-selector" class="p-1 rounded bg-gray-700 text-white border border-gray-600 cursor-pointer focus:ring-yellow-400 focus:border-yellow-400 transition-colors">
                        <option value="classic">Misteri Klasik</option>
                        <option value="patriot">Pahlawan Merah</option>
                    </select>
                </div>
                <!-- Mission Selector -->
                <div>
                    <label for="mission-selector" class="mr-2 text-gray-400 font-semibold">Misi:</label>
                    <select id="mission-selector" class="p-1 rounded bg-gray-700 text-white border border-gray-600 cursor-pointer focus:ring-yellow-400 focus:border-yellow-400 transition-colors">
                        <!-- Options filled by JS -->
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Custom Mission Input Area -->
        <div id="custom-mission-area" class="mt-4 mb-4 hidden">
            <label for="custom-prompt" class="block mb-2 text-sm font-semibold text-yellow-400">Tentukan Misi Kustom Anda:</label>
            <input type="text" id="custom-prompt" 
                   placeholder="Contoh: Misi Sumpah Pemuda. Cari tahu peran para pelajar Tionghoa..." 
                   class="w-full p-3 rounded-lg border transition-colors duration-300" 
                   aria-label="Custom Mission Prompt">
        </div>
        <!-- End Custom Mission Input Area -->

        <h1 id="game-title" class="text-3xl md:text-4xl font-bold text-center mb-6 tracking-wider">
            Mystery Mission: Sejarah Indonesia
        </h1>
        <p id="game-subtitle" class="text-center mb-8 italic">Detektif Waktu siap mengungkap misteri.</p>

        <!-- Game Output Area & TTS Control -->
        <div class="relative">
            <!-- TTS Button: Plays/Pauses the latest narrative text -->
            <button id="tts-button" class="absolute top-2 right-2 p-1 rounded-full text-xs font-bold transition-colors duration-200 opacity-80" title="Dengarkan Narasi">
                <!-- Speaker Icon (Default) -->
                <svg id="tts-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728l-1.424-1.424M15.5 9.773l-1.423 1.423M8.001 11.226l1.424-1.424M4.828 14.172l1.424 1.424M12 18.001v-6m0-6v6m0 0l-1.424 1.424M12 18.001l-1.424-1.424M12 12.001l-1.424-1.424M12 12.001l1.424-1.424" />
                </svg>
            </button>
            <div id="game-output" class="p-4 md:p-6 rounded-lg h-80 overflow-y-auto mb-6 text-sm md:text-base border transition-colors duration-300">
                <!-- Game narrative will appear here -->
                <span class="text-gray-500">Memuat Misi Awal...</span>
            </div>
        </div>
        <!-- End Game Output & TTS Control -->

        <!-- Citation/Grounding Sources -->
        <div id="sources-output" class="text-xs mb-6 min-h-[1.5rem]">
            <!-- Sources will appear here -->
        </div>

        <!-- Input Area -->
        <div id="input-section" class="flex flex-col space-y-4">
            <input type="text" id="user-input" placeholder="Ketik jawaban, pilihan (A/B/C), atau tindakan Anda di sini..." 
                   class="w-full p-3 rounded-lg border transition-colors duration-300" 
                   aria-label="Input Jawaban">
            
            <button id="send-button" class="w-full py-3 rounded-lg font-semibold flex items-center justify-center transition-colors duration-300">
                <span id="button-text">Kirim Jawaban / Lanjutkan</span>
                <div id="loading-spinner" class="hidden animate-spin rounded-full h-5 w-5 border-b-2 ml-2"></div>
            </button>
        </div>
        
        <!-- Score and Status -->
        <div class="mt-8 text-center text-gray-400">
            <p>Skor: <span id="score" class="font-bold">0</span> | Misi: <span id="mission-status" class="font-bold"></span></p>
        </div>
    </div>

    <script type="module">
        // ====================================================================
        // GAME STATE & CONFIGURATION
        // ====================================================================
        
        const MODEL_NAME = 'gemini-2.5-flash-preview-05-20';
        const apiKey = "APIKEY"; // API key is handled by the Canvas environment
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${MODEL_NAME}:generateContent?key=${apiKey}`;

        // DEFINISI MISI BARU
        const MISSIONS = {
            proklamasi: {
                title: "Proklamasi Kemerdekaan",
                initialPrompt: "Mulai Misi: Proklamasi Kemerdekaan. Anda berada di Rengasdengklok, 16 Agustus 1945. Tugas Anda adalah mencari tahu mengapa Soekarno dan Hatta 'diculik' dan apa hasil diskusi mereka. Berikan deskripsi lokasi dan tantangan teka-teki pertama."
            },
            diponegoro: {
                title: "Perang Diponegoro",
                initialPrompt: "Mulai Misi: Perang Diponegoro. Anda menyamar sebagai rakyat jelata di area pegunungan Jawa Tengah pada tahun 1825. Tujuan Anda adalah memahami strategi perang gerilya Pangeran Diponegoro. Berikan deskripsi lokasi dan tantangan pertama."
            },
            cutnyakdien: {
                title: "Perjuangan Cut Nyak Dien",
                initialPrompt: "Mulai Misi: Perjuangan Cut Nyak Dien. Anda dikirim ke Aceh di akhir tahun 1800-an. Anda harus menemukan dan mengamankan pesan rahasia yang berisi strategi perlawanan terhadap Belanda dari pejuang wanita legendaris ini. Berikan deskripsi lokasi dan tantangan pertama."
            },
            custom: {
                title: "Misi Kustom", // New custom option
                initialPrompt: "" // Placeholder, prompt taken from input field
            }
        };

        let gameState = {
            score: 0,
            currentMission: MISSIONS.proklamasi.title,
            currentMissionId: 'proklamasi',
            gameHistory: [],
            isLoading: false,
            phase: 'start',
        };

        // ====================================================================
        // THEME CONFIGURATION
        // ====================================================================

        const THEMES = {
            classic: {
                // Background & Container
                bodyBg: 'bg-gray-900', 
                containerClasses: 'bg-gray-800 border-yellow-400 shadow-2xl shadow-yellow-800/50',
                // Text & UI
                titleColor: 'text-yellow-400',
                subtitleColor: 'text-gray-400',
                sourcesColor: 'text-gray-400',
                // Output Console
                outputBg: 'bg-gray-900 border-gray-700',
                naratorText: 'text-green-400',
                userInputText: 'text-yellow-400',
                // Input Field
                inputBg: 'bg-gray-700 border-gray-700 text-white placeholder-gray-400',
                focusRing: 'focus:ring-yellow-400 focus:border-yellow-400',
                // Button
                buttonClasses: 'bg-yellow-600 text-gray-900 hover:bg-yellow-500 disabled:bg-gray-600 disabled:text-gray-400',
                spinnerBorder: 'border-gray-900',
                // Status
                scoreColor: 'text-yellow-400',
                // TTS Button
                ttsClasses: 'bg-gray-700/70 text-yellow-400 hover:bg-gray-600',
            },
            patriot: {
                // Background & Container
                bodyBg: 'bg-gray-100',
                containerClasses: 'bg-white border-red-600 shadow-xl shadow-red-800/50',
                // Text & UI
                titleColor: 'text-red-700',
                subtitleColor: 'text-gray-600',
                sourcesColor: 'text-gray-500',
                // Output Console
                outputBg: 'bg-gray-100 border-gray-300',
                naratorText: 'text-gray-800',
                userInputText: 'text-blue-700',
                // Input Field
                inputBg: 'bg-white border-gray-300 text-gray-800 placeholder-gray-500',
                focusRing: 'focus:ring-red-600 focus:border-red-600',
                // Button
                buttonClasses: 'bg-red-600 text-white hover:bg-red-700 disabled:bg-gray-400 disabled:text-gray-700',
                spinnerBorder: 'border-white',
                // Status
                scoreColor: 'text-red-700',
                // TTS Button
                ttsClasses: 'bg-gray-200/70 text-red-600 hover:bg-gray-300',
            }
        };

        let currentTheme = 'classic'; // Default theme

        // ====================================================================
        // DOM ELEMENTS & UTILITIES
        // ====================================================================

        const outputDiv = document.getElementById('game-output');
        const inputField = document.getElementById('user-input');
        const sendButton = document.getElementById('send-button');
        const buttonText = document.getElementById('button-text');
        const loadingSpinner = document.getElementById('loading-spinner');
        const scoreSpan = document.getElementById('score');
        const missionSpan = document.getElementById('mission-status');
        const sourcesDiv = document.getElementById('sources-output');
        const themeSelector = document.getElementById('theme-selector');
        const missionSelector = document.getElementById('mission-selector');
        const gameContainer = document.getElementById('game-container');
        const gameTitle = document.getElementById('game-title');
        const gameSubtitle = document.getElementById('game-subtitle');
        const customMissionArea = document.getElementById('custom-mission-area'); 
        const customPromptInput = document.getElementById('custom-prompt');     
        const ttsButton = document.getElementById('tts-button');

        // TTS State
        let currentUtterance = null;
        let isSpeaking = false;
        let lastNarrationText = ""; // To store the latest text for playback

        function removeThemeClasses(element, theme) {
            // Collecting all theme-specific classes to remove them cleanly
            const classesToRemove = [
                theme.bodyBg, theme.containerClasses, theme.titleColor, theme.subtitleColor, 
                theme.outputBg, theme.inputBg, theme.focusRing, theme.buttonClasses, 
                theme.naratorText, theme.userInputText, theme.scoreColor, theme.sourcesColor,
                theme.spinnerBorder, theme.ttsClasses,
                // General classes that might conflict (for safety)
                'bg-gray-900', 'bg-gray-800', 'bg-white', 'border-yellow-400', 'border-red-600', 
                'shadow-2xl', 'shadow-xl', 'text-yellow-400', 'text-red-700', 'text-gray-400', 
                'text-green-400', 'text-blue-700', 'bg-yellow-600', 'bg-red-600',
                'focus:ring-yellow-400', 'focus:border-yellow-400', 'focus:ring-red-600', 'focus:border-red-600',
                'bg-gray-700', 'border-gray-700', 'text-white', 'placeholder-gray-400',
                'bg-gray-700/70', 'text-yellow-400', 'hover:bg-gray-600', 'bg-gray-200/70', 'text-red-600', 'hover:bg-gray-300'
            ].join(' ');
            element.classList.remove(...classesToRemove.split(' ').filter(c => c));
        }

        function applyTheme(themeName) {
            const oldTheme = THEMES[currentTheme];
            currentTheme = themeName;
            const newTheme = THEMES[themeName];

            // 1. Body Background
            removeThemeClasses(document.body, oldTheme);
            document.body.classList.add(newTheme.bodyBg);

            // 2. Container
            removeThemeClasses(gameContainer, oldTheme);
            gameContainer.classList.add(...newTheme.containerClasses.split(' '));

            // 3. Title/Subtitle
            removeThemeClasses(gameTitle, oldTheme);
            gameTitle.classList.add(newTheme.titleColor);
            removeThemeClasses(gameSubtitle, oldTheme);
            gameSubtitle.classList.add(newTheme.subtitleColor);

            // 4. Output Area
            removeThemeClasses(outputDiv, oldTheme);
            outputDiv.classList.add(...newTheme.outputBg.split(' '));

            // 5. Input Field (User Input)
            removeThemeClasses(inputField, oldTheme);
            inputField.classList.add(...newTheme.inputBg.split(' '));
            inputField.classList.add(...newTheme.focusRing.split(' '));

            // 5b. Custom Prompt Input
            const customLabel = customMissionArea.querySelector('label');
            if (customLabel) {
                customLabel.classList.remove(THEMES.classic.titleColor, THEMES.patriot.titleColor);
                customLabel.classList.add(newTheme.titleColor);
            }
            
            if (customPromptInput) {
                removeThemeClasses(customPromptInput, oldTheme);
                customPromptInput.classList.add(...newTheme.inputBg.split(' '));
                customPromptInput.classList.add(...newTheme.focusRing.split(' '));
            }
            
            // 6. Send Button
            removeThemeClasses(sendButton, oldTheme);
            sendButton.classList.add(...newTheme.buttonClasses.split(' '));
            
            // 7. Loading Spinner
            removeThemeClasses(loadingSpinner, oldTheme);
            loadingSpinner.classList.add('border-b-2', newTheme.spinnerBorder);

            // 8. Sources
            removeThemeClasses(sourcesDiv, oldTheme);
            sourcesDiv.classList.add(newTheme.sourcesColor);

            // 9. Status Text
            const statusDiv = document.querySelector('.mt-8.text-center');
            removeThemeClasses(statusDiv, oldTheme);
            statusDiv.classList.add(newTheme.subtitleColor);

            // 10. Score/Mission Status Spans
            removeThemeClasses(scoreSpan, oldTheme);
            scoreSpan.classList.add(newTheme.scoreColor, 'font-bold');
            removeThemeClasses(missionSpan, oldTheme);
            missionSpan.classList.add(newTheme.scoreColor, 'font-bold');
            
            // 11. TTS Button Styling (New)
            removeThemeClasses(ttsButton, oldTheme);
            ttsButton.classList.add(...newTheme.ttsClasses.split(' '));
            
            // Re-render existing messages to apply new colors
            updateMessageColors();
        }

        function updateMessageColors() {
            const theme = THEMES[currentTheme];
            outputDiv.querySelectorAll('p').forEach(p => {
                const outerSpan = p.querySelector('span:first-child');
                if (!outerSpan) return;
                
                // Clear previous color classes
                outerSpan.className = outerSpan.className.split(' ').filter(c => !c.startsWith('text-')).join(' ');

                // Apply new color class based on the role (identified by the first child's bold span content)
                const roleSpan = outerSpan.querySelector('.font-bold');
                if (!roleSpan) return;

                const roleText = roleSpan.textContent.trim();
                
                if (roleText === '> Detektif:') {
                    outerSpan.classList.add(theme.userInputText);
                } else if (roleText === '~ Narator:') {
                    outerSpan.classList.add(theme.naratorText);
                }
            });
        }


        function updateUI() {
            scoreSpan.textContent = gameState.score;
            missionSpan.textContent = gameState.currentMission;
            inputField.disabled = gameState.isLoading;
            sendButton.disabled = gameState.isLoading;
            
            if (gameState.isLoading) {
                buttonText.textContent = 'Memuat...';
                loadingSpinner.classList.remove('hidden');
            } else {
                buttonText.textContent = 'Kirim Jawaban / Lanjutkan';
                loadingSpinner.classList.add('hidden');
                if(gameState.phase !== 'start') inputField.focus();
            }
        }

        /**
         * Displays a message in the game output console.
         * The structure is refined for better visual composition (bold label, normal content).
         * @param {string} text 
         * @param {('system'|'user'|'error')} role 
         */
        function displayMessage(text, role = 'system') {
            const messageElement = document.createElement('p');
            messageElement.classList.add('py-1');
            
            const theme = THEMES[currentTheme];
            // HTML-escape the text to prevent injection or breakage
            const safeText = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

            if (role === 'user') {
                // Outer span for color, inner spans for font weight control
                messageElement.innerHTML = `
                    <span class="${theme.userInputText} block">
                        <span class="font-bold">&gt; Detektif:</span> 
                        <span class="font-normal">${safeText}</span>
                    </span>`;
            } else if (role === 'system') {
                lastNarrationText = text; // Captures raw text for TTS
                stopSpeaking(); // Stop current speech when a new message arrives
                
                // Outer span for color, inner spans for font weight control
                messageElement.innerHTML = `
                    <span class="${theme.naratorText} block">
                        <span class="font-bold">~ Narator:</span> 
                        <span class="font-normal">${safeText}</span>
                    </span>`;
            } else if (role === 'error') {
                 messageElement.innerHTML = `<span class="text-red-500 font-bold">! Error:</span> ${safeText}`;
            }
            
            outputDiv.appendChild(messageElement);
            outputDiv.scrollTop = outputDiv.scrollHeight; // Auto-scroll to bottom
        }

        function displaySources(sources) {
            sourcesDiv.innerHTML = '';
            if (sources.length > 0) {
                const uniqueSources = Array.from(new Set(sources.map(s => s.uri)))
                    .map(uri => sources.find(s => s.uri === uri));

                const links = uniqueSources.map((s, index) => 
                    `<a href="${s.uri}" target="_blank" class="underline hover:opacity-75 transition-opacity" title="${s.title || 'Sumber Informasi'}"> [${index + 1}]</a>`
                ).join('');
                sourcesDiv.innerHTML = `<span class="font-bold">Sumber:</span> ${links}`;
            }
        }

        // ====================================================================
        // TTS LOGIC
        // ====================================================================

        function updateTtsIcon(playing) {
            // Pause icon SVG paths: two vertical lines
            const pauseIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" /></svg>`;
            // Speaker icon SVG paths: the default icon
            const speakerIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728l-1.424-1.424M15.5 9.773l-1.423 1.423M8.001 11.226l1.424-1.424M4.828 14.172l1.424 1.424M12 18.001v-6m0-6v6m0 0l-1.424 1.424M12 18.001l-1.424-1.424M12 12.001l-1.424-1.424M12 12.001l1.424-1.424" /></svg>`;
            
            ttsButton.innerHTML = playing ? pauseIcon : speakerIcon;
        }

        function stopSpeaking() {
            if (window.speechSynthesis.speaking || window.speechSynthesis.paused) {
                window.speechSynthesis.cancel();
            }
            isSpeaking = false;
            updateTtsIcon(false);
        }

        function toggleSpeech() {
            if (!lastNarrationText) {
                displayMessage("Tidak ada teks Narasi baru yang tersedia untuk dibacakan.", 'error');
                return;
            }

            if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                // If speaking, pause it
                window.speechSynthesis.pause();
                isSpeaking = false;
                updateTtsIcon(false);
            } else if (window.speechSynthesis.paused) {
                // If paused, resume it
                window.speechSynthesis.resume();
                isSpeaking = true;
                updateTtsIcon(true);
            } else {
                // If not speaking, start new speech
                stopSpeaking(); // Ensure clean start
                
                currentUtterance = new SpeechSynthesisUtterance(lastNarrationText);
                currentUtterance.lang = 'id-ID';
                currentUtterance.pitch = 1.0;
                currentUtterance.rate = 0.95;

                const setVoiceAndSpeak = () => {
                    const voices = window.speechSynthesis.getVoices();
                    // Prioritize Google Indonesian voice, then any Indonesian voice
                    const indoVoice = voices.find(v => v.lang.startsWith('id-') && v.name.includes("Google"))
                                         || voices.find(v => v.lang.startsWith('id-'));
                    if (indoVoice) {
                        currentUtterance.voice = indoVoice;
                    }
                    
                    window.speechSynthesis.speak(currentUtterance);
                    isSpeaking = true;
                    updateTtsIcon(true);

                    currentUtterance.onend = () => {
                        isSpeaking = false;
                        updateTtsIcon(false);
                    };
                    currentUtterance.onerror = (e) => {
                        console.error('Speech synthesis error:', e);
                        stopSpeaking();
                    }
                };
                
                // Wait for voices to load if needed
                if (window.speechSynthesis.getVoices().length === 0) {
                    window.speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
                } else {
                    setVoiceAndSpeak();
                }
            }
        }
        
        ttsButton.addEventListener('click', toggleSpeech);

        // ====================================================================
        // GEMINI API CALL (CORE LOGIC)
        // ====================================================================

        async function callGeminiAPI(query, retries = 3) {
            if (gameState.isLoading) return;

            gameState.isLoading = true;
            updateUI();
            
            const SYSTEM_INSTRUCTION = `
                Anda adalah Game Master dan Narator untuk game edukasi berbasis teks "Mystery Mission: Sejarah Indonesia". 
                Target audiens adalah siswa SMA/SMK.
                
                FOKUS MISI SAAT INI: ${gameState.currentMission}.
                
                ATURAN UTAMA:
                1. Konten Anda harus selalu akurat, menarik, dan sesuai dengan sejarah Indonesia.
                2. Gunakan Google Search Grounding untuk memastikan semua fakta sejarah (nama tokoh, tanggal, peristiwa) benar.
                3. Jaga nada bicara formal, penuh misteri, dan petualangan.
                4. Setiap respons harus mencakup:
                   - Narasi (deskripsi lokasi, dialog, petunjuk).
                   - Sebuah pertanyaan/teka-teki sejarah atau permintaan untuk tindakan berikutnya dari pemain.
                5. Jika pemain menjawab pertanyaan dengan benar, berikan poin (misalnya, 50 poin), berikan penjelasan singkat (Keterangan Sejarah), dan lanjutkan alur cerita.
                6. Jika pemain menjawab salah, berikan petunjuk, dan minta mereka mencoba lagi. Jangan berikan jawaban.
                7. Batasi panjang setiap respons narasi menjadi maksimal 150 kata.
            `;

            const chatHistory = [...gameState.gameHistory, { role: "user", parts: [{ text: query }] }];

            const payload = {
                contents: chatHistory,
                tools: [{ "google_search": {} }], // Use search for grounding
                systemInstruction: {
                    parts: [{ text: SYSTEM_INSTRUCTION }]
                },
            };

            for (let i = 0; i < retries; i++) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    const candidate = result.candidates?.[0];

                    if (candidate && candidate.content?.parts?.[0]?.text) {
                        const text = candidate.content.parts[0].text;
                        
                        let sources = [];
                        const groundingMetadata = candidate.groundingMetadata;
                        if (groundingMetadata && groundingMetadata.groundingAttributions) {
                            sources = groundingMetadata.groundingAttributions
                                .map(attribution => ({
                                    uri: attribution.web?.uri,
                                    title: attribution.web?.title,
                                }))
                                .filter(source => source.uri && source.title);
                        }

                        gameState.gameHistory.push({ role: "model", parts: [{ text: text }] });
                        
                        gameState.isLoading = false;
                        updateUI();
                        return { text, sources };

                    } else {
                        throw new Error("Respons API kosong atau tidak terstruktur.");
                    }
                } catch (error) {
                    console.error(`Attempt ${i + 1} failed:`, error);
                    if (i === retries - 1) {
                        displayMessage(`Gagal menghubungi server. Mohon coba lagi. (${error.message})`, 'error');
                        gameState.isLoading = false;
                        updateUI();
                        return null;
                    }
                    await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
                }
            }
        }

        // ====================================================================
        // GAME FLOW & EVENT HANDLERS
        // ====================================================================

        async function processUserAction(userText) {
            if (!userText.trim() || gameState.isLoading) return;

            displayMessage(userText, 'user');
            inputField.value = '';

            gameState.gameHistory.push({ role: "user", parts: [{ text: userText }] });

            const result = await callGeminiAPI(userText);

            if (result) {
                if (result.text.toLowerCase().includes('poin 50') || result.text.toLowerCase().includes('50 poin')) {
                    gameState.score += 50;
                    scoreSpan.textContent = gameState.score;
                }
                
                displayMessage(result.text, 'system');
                displaySources(result.sources);
                
                if (result.text.toLowerCase().includes('misi selesai') || result.text.toLowerCase().includes('misi berhasil')) {
                    displayMessage("Selamat! Misi berhasil diselesaikan. Silakan pilih Misi Baru di menu atas untuk tantangan baru.", 'system');
                    gameState.phase = 'mission_complete';
                }
            }
        }

        function handleInput() {
            const userText = inputField.value.trim();
            if (userText) {
                if (gameState.phase === 'start' || gameState.phase === 'mission_complete') {
                    
                    let initialQuery;
                    if (gameState.currentMissionId === 'custom') {
                        const customText = customPromptInput.value.trim();
                        if (!customText) {
                            displayMessage("Mohon masukkan prompt untuk Misi Kustom Anda di kolom di atas.", 'error');
                            return;
                        }
                        initialQuery = `Mulai Misi Kustom: ${customText}. Berikan deskripsi lokasi dan tantangan teka-teki pertama berdasarkan misi ini.`;
                    } else {
                        initialQuery = MISSIONS[gameState.currentMissionId].initialPrompt;
                    }

                    startGame(initialQuery);

                } else {
                    processUserAction(userText);
                }
            }
        }
        
        sendButton.addEventListener('click', handleInput);
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleInput();
            }
        });

        // Event listener for theme selector
        themeSelector.addEventListener('change', (e) => {
            applyTheme(e.target.value);
        });

        // Event listener for mission selector
        missionSelector.addEventListener('change', (e) => {
            const newMissionId = e.target.value;
            const newMission = MISSIONS[newMissionId] || MISSIONS.custom;
            
            // Show/hide custom input area
            if (newMissionId === 'custom') {
                customMissionArea.classList.remove('hidden');
                customPromptInput.focus();
            } else {
                customMissionArea.classList.add('hidden');
            }

            // Reset game state for a new mission selection
            gameState.currentMissionId = newMissionId;
            gameState.currentMission = newMission.title;
            gameState.score = 0;
            gameState.gameHistory = [];
            gameState.phase = 'start';
            
            outputDiv.innerHTML = '';
            displayMessage(`Misi diatur ke: ${newMission.title}. Skor direset.`, 'system');
            displayMessage(`Ketik *Mulai* atau klik tombol di bawah untuk memulai petualangan di era ${newMission.title}!`, 'system');
            updateUI();
        });

        // ====================================================================
        // INITIALIZATION
        // ====================================================================

        function populateMissionSelector() {
            missionSelector.innerHTML = '';
            let isFirst = true;
            for (const id in MISSIONS) {
                const mission = MISSIONS[id];
                const option = document.createElement('option');
                option.value = id;
                option.textContent = mission.title;
                missionSelector.appendChild(option);

                if (isFirst) {
                    gameState.currentMissionId = id;
                    gameState.currentMission = mission.title;
                    isFirst = false;
                }
            }
            updateUI(); 
        }

        async function startGame(initialQuery) {
            gameState.phase = 'in_mission';
            displayMessage("Portal Waktu aktif. Memuat data sejarah...", 'system');
            
            outputDiv.innerHTML = '';
            
            const result = await callGeminiAPI(initialQuery);

            if (result) {
                displayMessage(result.text, 'system');
                displaySources(result.sources);
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Populate the mission selector
            populateMissionSelector();

            // 2. Apply the initial theme (also styles the TTS button)
            applyTheme('classic'); 
            
            // 3. Display the welcome message
            displayMessage(`Selamat datang, Detektif Waktu! Misi saat ini: ${gameState.currentMission}.`, 'system');
            displayMessage("Silakan pilih Misi dan Visual Anda. Ketik *Mulai* atau klik tombol di bawah untuk memulai.", 'system');
        });

        // Listener untuk memastikan TTS berhenti saat halaman ditutup atau diperbarui.
        window.addEventListener('beforeunload', stopSpeaking); 

    </script>
</body>
</html>
