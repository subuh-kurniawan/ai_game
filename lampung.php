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
    <title>Game Latihan Bahasa Lampung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f0f4f8; }
        .chat-container { height: 70vh; max-height: 800px; }
        .chat-bubble-ai { background-color: #e0f2f1; color: #004d40; border-radius: 1rem 1rem 1rem 0; }
        .chat-bubble-user { background-color: #26a69a; color: white; border-radius: 1rem 1rem 0 1rem; margin-left: auto; }
        .shadow-custom { box-shadow: 0 10px 15px -3px rgba(38, 166, 154, 0.2), 0 4px 6px -2px rgba(38, 166, 154, 0.05); }
        .tts-button { 
            color: #004d40; 
            background: none; 
            border: none; 
            cursor: pointer; 
            margin-left: 0.5rem; 
            padding: 0;
            display: inline-flex;
            align-items: center;
            opacity: 0.8;
            /* Ensure the button stays visually next to the text in the bubble */
            vertical-align: top; 
        }
        .tts-button:hover { opacity: 1; }
        .tts-button svg { width: 1.25rem; height: 1.25rem; }
    </style>
</head>
<body class="p-4 md:p-8 min-h-screen flex items-center justify-center">

    <div id="game-container" class="w-full max-w-2xl bg-white rounded-xl shadow-2xl overflow-hidden">
        
        <!-- Header -->
        <header class="bg-[#004d40] text-white p-4 flex justify-between items-center rounded-t-xl">
            <h1 class="text-2xl font-bold">Guru Permainan Bahasa Lampung</h1>
            <div id="score-display" class="bg-teal-500 py-1 px-3 rounded-full text-sm font-semibold">Skor: 0</div>
        </header>

        <!-- Chat Area -->
        <div id="chat-messages" class="chat-container p-4 overflow-y-auto flex flex-col space-y-4">
            <!-- Pesan akan dimuat di sini -->
        </div>

        <!-- Input Area -->
        <div class="p-4 border-t border-gray-200">
            <div id="loading-indicator" class="hidden text-center p-2 text-teal-600 font-medium">
                AI Game Master sedang berpikir...
            </div>
            <div class="flex space-x-3">
                <input type="text" id="user-input" placeholder="Ketik pilihan level (1, 2, 3) atau jawaban (atau 'hint')..." 
                       class="flex-grow p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                       onkeydown="if(event.key === 'Enter') document.getElementById('send-btn').click()">
                <button id="send-btn" onclick="sendMessage()" 
                        class="bg-teal-500 hover:bg-teal-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 disabled:opacity-50">
                    Kirim
                </button>
            </div>
        </div>
    </div>

    <!-- Game Logic and LocalStorage Persistence -->
    <script type="module">
        // --- Global Variables ---
        let score = 0;
        let chatHistory = [];
        
        // API Configuration (Only for Text Generation)
        const apiKey = "<?php echo $apiKey; ?>"; 
        const textApiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;

        // --- TTS Configuration (Web Speech API) ---
        let synth = window.speechSynthesis;
        let idVoice = null;

        /**
         * Loads available voices and tries to find an Indonesian voice.
         */
        function loadVoices() {
            if (!synth) return;
            const voices = synth.getVoices();
            // Prioritize finding an Indonesian voice
            idVoice = voices.find(voice => voice.lang.startsWith('id-') || voice.name.toLowerCase().includes('indonesia'));
            console.log("TTS voices loaded. Indonesian voice found:", idVoice ? idVoice.name : "None");
        }

        // Load voices initially and listen for the event in case they load later
        loadVoices();
        if (synth && synth.onvoiceschanged !== undefined) {
            synth.onvoiceschanged = loadVoices;
        }

        /**
         * Plays the given text using the Web Speech API (Indonesian voice).
         * @param {string} text The text to synthesize.
         */
        window.playTtsAudio = function(text) {
            if (!synth || !text.trim()) {
                console.warn("Speech synthesis not supported or text is empty.");
                return;
            }

            // Cancel any currently speaking audio
            synth.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            
            // Set voice and language
            if (idVoice) {
                utterance.voice = idVoice;
            } else {
                // Fallback to Indonesian language code
                utterance.lang = 'id-ID';
            }
            
            // Adjust rate slightly for better flow
            utterance.rate = 1.1; 

            synth.speak(utterance);
        }

        // --- LocalStorage Functions ---

        /**
         * Saves the current game state (score and chat history) to localStorage.
         */
        function saveGameState() {
            localStorage.setItem('lampungGameScore', score.toString());
            // Sanitize history by mapping to save only necessary parts
            const historyToSave = chatHistory.map(msg => ({ 
                role: msg.role, 
                parts: msg.parts.map(part => ({ 
                    text: part.text, 
                    ttsText: part.ttsText // Keep ttsText for re-extraction
                })) 
            }));
            localStorage.setItem('lampungGameHistory', JSON.stringify(historyToSave));
            console.log("Game state saved to localStorage.");
        }

        /**
         * Loads the game state from localStorage.
         */
        function loadGameState() {
            const savedScore = localStorage.getItem('lampungGameScore');
            const savedHistory = localStorage.getItem('lampungGameHistory');

            if (savedScore) {
                score = parseInt(savedScore, 10);
                document.getElementById('score-display').textContent = `Skor: ${score}`;
            }

            if (savedHistory) {
                try {
                    chatHistory = JSON.parse(savedHistory);
                    // Re-render history if loaded successfully
                    chatHistory.forEach(msg => {
                        // Only display user/model messages
                        addMessageToChat(
                            msg.parts[0].text, 
                            msg.role === 'user' ? 'user' : 'ai', 
                            false,
                            msg.parts[0].ttsText // Pass ttsText for button generation
                        );
                    });
                    
                    // After loading history, change placeholder to show game is in progress
                    if (chatHistory.length > 1) {
                        document.getElementById('user-input').placeholder = "Ketik jawabanmu (atau 'hint') dalam bahasa Lampung...";
                    }
                    console.log("Game state loaded from localStorage.");
                } catch (e) {
                    console.error("Error parsing chat history from localStorage:", e);
                    chatHistory = []; // Reset if corrupted
                }
            }
        }
        
        // --- Utility Functions ---

        /**
         * Converts plain text with simple formatting to HTML and extracts potential score/TTS text.
         * Now looks for the specific [TTS:...] tag.
         * @param {string} text The raw text from the AI.
         * @returns {object} {html: string, newScore: number | null, ttsText: string}
         */
        function parseAiText(text) {
            let html = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\n/g, '<br>');
            
            let newScore = null;
            let ttsText = "";

            // New: 1. Extract the specific TTS tag content [TTS:...]
            const ttsMatch = text.match(/\[TTS:\s*(.*?)\]/i);
            if (ttsMatch && ttsMatch[1]) {
                // Use the content inside the tag for TTS
                ttsText = ttsMatch[1].trim();
                // Remove the tag from the displayed HTML
                html = html.replace(/\[TTS:\s*.*?\]/ig, '').trim(); 
            }
            // If no TTS tag is found, ttsText remains "" (which suppresses the button)
            
            // 2. Extract score
            const scoreMatch = text.match(/Skor Anda:\s*(\d+)/i);
            if (scoreMatch && scoreMatch[1]) {
                newScore = parseInt(scoreMatch[1], 10);
            }
            
            return { html, newScore, ttsText };
        }

        /**
         * Adds a message to the chat display and scrolls to the bottom.
         * @param {string} text The message content.
         * @param {string} sender 'user' or 'ai'.
         * @param {boolean} shouldScroll Whether to scroll to the bottom (default true).
         * @param {string} ttsText The text to use for TTS (only applicable for 'ai' messages).
         */
        function addMessageToChat(text, sender, shouldScroll = true, ttsText = '') {
            const chatBox = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'}`;

            const bubble = document.createElement('div');
            bubble.className = `max-w-xs md:max-w-md p-3 rounded-xl shadow-lg transition duration-300 ${sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-ai'}`;
            
            // Parse text to HTML and extract final ttsText (if not provided from history)
            const { html, ttsText: parsedTtsText } = parseAiText(text);
            const finalTtsText = ttsText || parsedTtsText;
            
            bubble.innerHTML = html; // Set the main content

            // Only show the TTS button if finalTtsText is NOT empty (meaning the [TTS:...] tag was present)
            if (sender === 'ai' && finalTtsText) {
                // Add button to play TTS audio
                const playButton = document.createElement('button');
                playButton.className = 'tts-button';
                playButton.title = "Dengarkan pelafalan";
                playButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11 5L6 9H2v6h4l5 4zM17 12c0-1.26-.5-2.4-1.34-3.3C15.82 9.77 17 11.28 17 12zM17.85 15.35c.67-.7.99-1.57.99-2.35 0-.78-.32-1.65-.99-2.35L19.3 9.4c.94 1.05 1.45 2.37 1.45 3.6 0 1.23-.51 2.55-1.45 3.6z"/></svg>`;
                playButton.onclick = () => playTtsAudio(finalTtsText);

                // Append the button to the bubble
                bubble.appendChild(playButton);
            }
            
            messageDiv.appendChild(bubble);
            chatBox.appendChild(messageDiv);
            if (shouldScroll) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }


        /**
         * Handles API call with exponential backoff for retries.
         */
        async function fetchWithBackoff(url, options, retries = 5) {
            for (let i = 0; i < retries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response;
                } catch (error) {
                    console.error("Fetch attempt failed:", error);
                    if (i < retries - 1) {
                        const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        console.log(`Retrying in ${delay / 1000} seconds...`);
                    } else {
                        throw new Error("API call failed after multiple retries.");
                    }
                }
            }
        }

        /**
         * The core function to communicate with the Gemini API.
         */
        async function getAiResponse() {
            const loading = document.getElementById('loading-indicator');
            const inputField = document.getElementById('user-input');
            const sendButton = document.getElementById('send-btn');

            loading.classList.remove('hidden');
            inputField.disabled = true;
            sendButton.disabled = true;

            // System prompt sets the AI's persona, goal, and rules
            const systemPrompt = `Anda adalah 'Guru Permainan' (Game Master) dan tutor bahasa Lampung yang ramah, bersemangat, dan ahli. Tujuan utama Anda adalah mengajarkan bahasa Lampung kepada pemain (pengguna) dan meningkatkan skor mereka.

            1. **PILIHAN LEVEL:** Di awal permainan, pemain akan memilih level dengan mengetik '1', '2', atau '3'. Jika ini adalah balasan pertama setelah Anda menampilkan pilihan level, Anda harus:
                a. Konfirmasi level yang dipilih.
                b. Segera berikan skenario pertama yang sesuai dengan tingkat kesulitan tersebut (Pemula, Menengah, atau Mahir). Jangan berikan skor/koreksi di langkah ini.

            2. **Skenario:** Setelah level dipilih (atau saat permainan berlangsung), berikan skenario dalam bahasa Indonesia atau Lampung, dan minta pemain merespons dalam bahasa Lampung.

            3. **Koreksi & Skor (Respons Jawaban Biasa):** Setelah respons pemain (yang bukan hint atau pilihan level), Anda harus:
                a. Berikan terjemahan atau penjelasan dari respons pemain.
                b. Koreksi tata bahasa atau kosakata (jika ada).
                c. Berikan poin (+10 poin untuk jawaban yang tepat, +5 poin untuk usaha yang baik).
                d. Jaga agar total skor pemain selalu ada dalam respons Anda.

            4. **PENANGANAN PETUNJUK:** Jika pemain mengetik kata kunci seperti "hint", "bantuan", atau "petunjuk", JANGAN memberikan koreksi atau skor. Sebaliknya, berikan petunjuk (misalnya, kosakata kunci yang diperlukan, struktur kalimat yang sesuai) dan ulangi skenario asli. Respons Anda harus diakhiri dengan baris: **Skor Anda: ${score}** **Koreksi:** [Tulis: "Petunjuk diberikan. Silakan coba lagi!"]

            5. **Format Wajib:** Respons Anda harus selalu diakhiri dengan baris skor dan koreksi dalam format yang sama persis (kecuali saat mengonfirmasi level). SELALU sertakan frasa atau kata kunci bahasa Lampung yang ingin Anda minta diucapkan atau dilatih oleh pemain di awal respons Anda dengan format \`[TTS: Frasa/Kata Kunci Lampung]\`. Frasa ini harus menjadi **dialog, skenario, atau koreksi yang menggunakan bahasa Lampung**, kecuali saat mengonfirmasi level atau memberikan pengantar, di mana Anda dapat menggunakan frasa Indonesia yang ringkas.

            [Respon Game Master, koreksi, dan skenario berikutnya] 

            **Skor Anda: ${score}** **Koreksi:** [Sertakan Koreksi/Penjelasan jika ada. Jika jawaban sudah sempurna, tulis: "Sempurna!"]
            
            6. **Gaya:** Jaga nada bicara yang mendukung dan menarik. Gunakan frasa-frasa motivasi.`;
            
            const payload = {
                contents: chatHistory,
                systemInstruction: {
                    parts: [{ text: systemPrompt }]
                },
            };

            const options = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            };

            try {
                const response = await fetchWithBackoff(textApiUrl, options);
                const result = await response.json();
                
                const candidate = result.candidates?.[0];
                let aiText = "Maaf, terjadi kesalahan saat memproses jawaban Anda. Coba lagi.";
                let ttsText = "";

                if (candidate && candidate.content?.parts?.[0]?.text) {
                    aiText = candidate.content.parts[0].text;
                    
                    // Extract TTS text and score
                    const { newScore, ttsText: extractedTtsText } = parseAiText(aiText);
                    ttsText = extractedTtsText;

                    if (newScore !== null) {
                        score = newScore;
                        document.getElementById('score-display').textContent = `Skor: ${score}`;
                    }
                    
                    // Add AI response to history and display
                    // Attach the extracted TTS text to the history message part
                    chatHistory.push({ role: "model", parts: [{ text: aiText, ttsText: ttsText }] });
                    addMessageToChat(aiText, 'ai', true, ttsText);
                    
                    // Save state after successful response
                    saveGameState();
                } else {
                    addMessageToChat(aiText, 'ai');
                    console.error("Unexpected API response structure:", result);
                }

            } catch (error) {
                console.error("Error during Gemini API call:", error);
                addMessageToChat("Ups! Game Master sedang istirahat. Terjadi kesalahan koneksi.", 'ai');
            } finally {
                loading.classList.add('hidden');
                inputField.disabled = false;
                sendButton.disabled = false;
                inputField.focus();
            }
        }
        
        /**
         * Initialises the game. Checks for existing state or starts a new session.
         */
        window.onload = function() {
            loadGameState();
            
            if (chatHistory.length === 0) {
                // If no history, prompt for level selection
                const initialMessage = "[TTS: Selamat datang di Game Bahasa Lampung] Selamat datang di Game Latihan Bahasa Lampung! Untuk membuat game lebih seru dan sesuai kemampuan Anda, silakan pilih level Anda dengan mengetik angka 1, 2, atau 3:\n\n1. **Pemula** (Fokus: Sapaan, Perkenalan Diri, Angka)\n2. **Menengah** (Fokus: Percakapan di Pasar, Arah, Kegiatan Sehari-hari)\n3. **Mahir** (Fokus: Diskusi Budaya/Filosofi, Peribahasa, Teks Naratif)\n\nSilakan ketik 1, 2, atau 3.";
                
                // Store the TTS text for the initial message
                const ttsMatch = initialMessage.match(/\[TTS:\s*(.*?)\]/i);
                const initialTtsText = ttsMatch && ttsMatch[1] ? ttsMatch[1].trim() : "";
                
                chatHistory.push({ role: "model", parts: [{ text: initialMessage, ttsText: initialTtsText }] });
                addMessageToChat(initialMessage, 'ai', true, initialTtsText);
                saveGameState();
            } else {
                // If history exists, the last message is already displayed.
                console.log("Resuming game...");
            }
        };

        /**
         * Main function to handle user input and trigger AI response.
         */
        window.sendMessage = async function() {
            const inputField = document.getElementById('user-input');
            const userText = inputField.value.trim();

            if (!userText) return;

            // 1. Display user message
            addMessageToChat(userText, 'user');
            
            // 2. Add user message to history
            chatHistory.push({ role: "user", parts: [{ text: userText }] });

            // 3. Clear input
            inputField.value = '';
            
            // 4. Update input placeholder based on state (visual feedback for interactivity)
            // If this is the first user message (level choice)
            if (chatHistory.length === 2 && (userText === '1' || userText === '2' || userText === '3')) { 
                inputField.placeholder = "Ketik jawabanmu (atau 'hint') dalam bahasa Lampung...";
            }

            // 5. Get AI response
            await getAiResponse();
        };
        
    </script>
</body>
</html>
