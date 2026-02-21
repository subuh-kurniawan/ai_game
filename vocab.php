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
    <title>Master Kosakata Inggris (Scrabble)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .game-container {
            background-color: #ffffff;
            border: 8px solid #4a90e2; /* Blue frame */
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 24px;
            max-width: 650px;
            width: 100%;
        }
        .tile {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 45px;
            height: 45px;
            background-color: #ffcc00; /* Bright Yellow */
            border: 2px solid #e0b300;
            border-radius: 8px;
            margin: 4px;
            cursor: pointer;
            user-select: none;
            transition: transform 0.1s, box-shadow 0.1s, opacity 0.3s;
            position: relative;
        }
        .tile:hover:not(.selected) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .tile.selected {
            opacity: 0.5;
            cursor: default;
            pointer-events: none; /* Make selected tiles non-clickable in the rack */
            border: 2px solid #10b981; /* Green border for used tile */
        }
        .tile-letter {
            font-size: 1.5rem;
            font-weight: 900;
            color: #444;
            line-height: 1;
        }
        .tile-score {
            position: absolute;
            bottom: 2px;
            right: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #666;
            line-height: 1;
        }
        .rack-container {
            background-color: #a87f5d; /* Wood brown for rack */
            padding: 10px;
            border-radius: 12px;
            margin-top: 20px;
            min-height: 80px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        #message-box {
            min-height: 40px;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .word-info p {
            margin-bottom: 4px;
        }
        .word-info strong {
            color: #1e40af;
        }
        /* Style untuk tombol pengucapan */
        .pronounce-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }

        /* Style for the Word Construction Area */
        #word-area-display {
            min-height: 55px;
            border: 2px solid #4a90e2;
            background-color: #e0f2fe; /* Light blue background */
            border-radius: 10px;
            padding: 5px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            cursor: pointer; /* Indicate it's interactive */
        }

        .word-tile {
            width: 40px;
            height: 40px;
            background-color: #4a90e2; /* Blue */
            color: white;
            font-weight: 900;
            font-size: 1.3rem;
            border-radius: 6px;
            margin: 2px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.1s;
        }
        /* Hidden input is just to keep track of the word string easily */
        #word-input {
            opacity: 0; 
            height: 0;
            width: 0;
            position: absolute;
        }
        /* CSS for Score Flash Animation */
        @keyframes score-flash {
            0% { background-color: #d1e7dd; transform: scale(1.05); }
            50% { background-color: #c3e6cb; transform: scale(1.0); }
            100% { background-color: #e0f2fe; }
        }
        .score-flash {
            animation: score-flash 0.5s ease-out;
            background-color: #d1e7dd !important; /* Start color */
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="game-container">
        <h1 class="text-3xl font-extrabold text-center text-gray-800 mb-2">Master Kosakata Inggris</h1>
        <p class="text-center text-sm text-gray-600 mb-4">Uji kosakata Bahasa Inggris Anda! Ubinya acak, namun **informasi kata diatur untuk level Pemula (A1)**.</p>

        <!-- Pilihan Level Kesulitan & Kategori -->
        <div class="mb-4 p-3 bg-indigo-100 rounded-xl shadow-inner">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="difficulty-selector" class="block text-sm font-bold text-indigo-800 mb-2">Pilih Level Kesulitan (Ukuran Rak):</label>
                    <select id="difficulty-selector" class="w-full p-2 border-2 border-indigo-300 rounded-lg text-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="8">Sangat Mudah (8 Huruf)</option>
                        <option value="7" selected>Mudah (7 Huruf)</option>
                        <option value="6">Sedang (6 Huruf)</option>
                        <option value="5">Sulit (5 Huruf)</option>
                    </select>
                </div>
                <div>
                    <label for="pos-selector" class="block text-sm font-bold text-indigo-800 mb-2">Pilih Kategori Kata (POS):</label>
                    <select id="pos-selector" class="w-full p-2 border-2 border-indigo-300 rounded-lg text-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Any">Lainnya (Semua Jenis)</option>
                        <option value="Noun">Kata Benda (Noun)</option>
                        <option value="Verb">Kata Kerja (Verb)</option>
                        <option value="Adjective">Kata Sifat (Adjective)</option>
                    </select>
                </div>
            </div>
            <button id="start-button" onclick="startGame(true)" class="w-full mt-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 rounded-lg shadow-md transition duration-200">
                Mulai Ulang Game
            </button>
        </div>

        <div id="score-display" class="bg-blue-100 p-3 rounded-xl shadow-inner text-center font-bold text-lg mb-4 text-blue-800">
            Skor Anda: <span id="current-score" class="text-3xl">0</span>
        </div>

        <!-- Kotak Pesan Game Master -->
        <div id="message-box" class="bg-gray-200 text-gray-800 shadow-md">
            <!-- Pesan dari Game Master akan muncul di sini -->
            Game Master: Selamat datang! Klik Huruf di bawah untuk membentuk kata!
        </div>

        <!-- Rak Ubin -->
        <div class="rack-container shadow-xl">
            <div id="tile-rack" class="flex flex-wrap justify-center">
                <!-- Ubin akan diinjeksi di sini -->
            </div>
        </div>

        <!-- Input dan Aksi -->
        <div class="mt-6 p-4 bg-white rounded-xl shadow-lg border border-gray-200">
            <label for="word-area-display" class="block text-lg font-bold text-gray-700 mb-2">Kata yang Sedang Dibentuk (Klik Huruf di bawah):</label>
            
            <!-- Area Kata Visual Interaktif -->
            <div id="word-area-display" onclick="backspaceWord()" title="Klik untuk menghapus huruf terakhir">
                <!-- Selected tiles will be injected here -->
                <span class="text-gray-500 italic">Klik Huruf...</span>
            </div>

            <!-- Hidden input for holding the word string for easy processing -->
            <input type="hidden" id="word-input" value="">

            <div class="flex mt-3 space-x-3 input-group">
                <button id="backspace-button" onclick="backspaceWord()" class="w-1/3 bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-3 rounded-lg shadow-md transition duration-200">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2A9 9 0 115 5h2m10 0v1h-1m-4-1V5h-1m-1 0V5h-1m-1 0V5h-1m-1 0V5h-1m-1 0V5H5a9 9 0 0014 0z"></path></svg> Hapus 1
                </button>
                <button id="clear-button" onclick="clearWord()" class="w-1/3 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-3 rounded-lg shadow-md transition duration-200">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2A9 9 0 115 5h2m10 0v1h-1m-4-1V5h-1m-1 0V5h-1m-1 0V5h-1m-1 0V5h-1m-1 0V5H5a9 9 0 0014 0z"></path></svg> Bersihkan
                </button>
                <button id="play-button" onclick="playWord()" class="w-1/3 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-3 rounded-lg shadow-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    Cek & Skor
                </button>
            </div>
            
            <div class="flex mt-3 space-x-3">
                <button id="shuffle-button" onclick="shuffleRack()" class="w-1/2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 rounded-lg shadow-md transition duration-200">
                    Acak Huruf
                </button>
                <button id="pass-button" onclick="passRack()" class="w-1/2 bg-red-500 hover:bg-red-600 text-white font-bold py-2 rounded-lg shadow-md transition duration-200">
                    Lompati & Ganti Huruf (-10 Poin)
                </button>
            </div>
        </div>
        
    </div>

    <script type="module">
        // --- Setup API Constants ---
        const apiKey = "<?php echo $apiKey; ?>"; 
        const md = "<?php echo $model; ?>";
        const apiUrl = `hhttps://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey}`;

        // --- English Scrabble Distribution (Standard Counts and Points) ---
        const LETTER_POINTS = {
            'A': 1, 'B': 3, 'C': 3, 'D': 2, 'E': 1, 'F': 4, 'G': 2, 'H': 4, 
            'I': 1, 'J': 8, 'K': 5, 'L': 1, 'M': 3, 'N': 1, 'O': 1, 'P': 3, 
            'Q': 10, 'R': 1, 'S': 1, 'T': 1, 'U': 1, 'V': 4, 'W': 4, 'X': 8, 
            'Y': 4, 'Z': 10
        };

        const LETTER_COUNTS = {
            'A': 9, 'B': 2, 'C': 2, 'D': 4, 'E': 12, 'F': 2, 'G': 3, 'H': 2, 
            'I': 9, 'J': 1, 'K': 1, 'L': 4, 'M': 2, 'N': 6, 'O': 8, 'P': 2, 
            'Q': 1, 'R': 6, 'S': 4, 'T': 6, 'U': 4, 'V': 2, 'W': 2, 'X': 1, 
            'Y': 2, 'Z': 1
        };

        let currentRackSize = 7; 
        let availableTiles = [];
        let playerRack = [];
        let selectedTileIds = []; 
        let score = 0;
        let englishVoice = null; 

        const tileRackEl = document.getElementById('tile-rack');
        const wordAreaEl = document.getElementById('word-area-display'); 
        const wordInputEl = document.getElementById('word-input'); 
        const scoreEl = document.getElementById('current-score');
        const scoreDisplayEl = document.getElementById('score-display');
        const playButtonEl = document.getElementById('play-button');
        const messageBoxEl = document.getElementById('message-box');
        const difficultySelectorEl = document.getElementById('difficulty-selector');
        const posSelectorEl = document.getElementById('pos-selector'); 


        // --- Game Logic Functions ---

        function initializeAvailableTiles() {
            availableTiles = [];
            for (const letter in LETTER_COUNTS) {
                for (let i = 0; i < LETTER_COUNTS[letter]; i++) {
                    availableTiles.push(letter);
                }
            }
        }

        function drawTiles(count) {
            if (availableTiles.length === 0) {
                showMessage("Huruf habis! Permainan berakhir.", 'bg-red-100 text-red-800');
                playButtonEl.disabled = true;
                return;
            }

            for (let i = 0; i < count; i++) {
                if (availableTiles.length === 0) break;
                
                const randomIndex = Math.floor(Math.random() * availableTiles.length);
                const letter = availableTiles.splice(randomIndex, 1)[0];
                
                playerRack.push({
                    id: Date.now() + Math.random(), 
                    letter: letter,
                    score: LETTER_POINTS[letter]
                });
            }
            renderRack();
        }

        function renderRack() {
            tileRackEl.innerHTML = '';
            playerRack.forEach(tile => {
                const tileDiv = document.createElement('div');
                tileDiv.className = 'tile';
                tileDiv.dataset.id = tile.id; 
                tileDiv.dataset.letter = tile.letter;
                
                if (selectedTileIds.includes(tile.id)) {
                    tileDiv.classList.add('selected');
                } else {
                    tileDiv.onclick = () => selectTile(tile.id);
                }

                tileDiv.innerHTML = `
                    <span class="tile-letter">${tile.letter}</span>
                    <span class="tile-score">${tile.score}</span>
                `;
                tileRackEl.appendChild(tileDiv);
            });
            renderSelectedWord(); 
        }

        function renderSelectedWord() {
            const wordTiles = selectedTileIds.map(id => {
                const tile = playerRack.find(t => t.id === id);
                return tile ? `<div class="word-tile">${tile.letter}</div>` : '';
            }).join('');

            wordAreaEl.innerHTML = wordTiles || '<span class="text-gray-500 italic">Klik Huruf...</span>';
            
            const currentWord = selectedTileIds.map(id => playerRack.find(t => t.id === id)?.letter || '').join('');
            wordInputEl.value = currentWord;
            
            playButtonEl.disabled = currentWord.length < 2;
        }

        function selectTile(id) {
            if (selectedTileIds.length < currentRackSize) {
                selectedTileIds.push(id);
                renderRack(); 
            }
        }

        window.backspaceWord = function() {
            if (selectedTileIds.length > 0) {
                selectedTileIds.pop();
                renderRack(); 
            }
        }

        window.clearWord = function() {
            if (selectedTileIds.length > 0) {
                selectedTileIds = [];
                renderRack(); 
                showMessage("Kata dibersihkan. Bentuk kata baru!", 'bg-gray-200 text-gray-800');
            }
        }
        
        window.shuffleRack = function() {
            if (selectedTileIds.length > 0) {
                showMessage("Bersihkan kata yang sedang dibentuk sebelum mengacak Huruf!", 'bg-red-100 text-red-800');
                return;
            }
            playerRack.sort(() => Math.random() - 0.5);
            renderRack();
            showMessage("Huruf Anda sudah diacak!", 'bg-yellow-100 text-yellow-800');
        }

        window.passRack = function() {
            if (playerRack.length === 0) {
                 showMessage("Rak kosong, tidak bisa dilewati!", 'bg-red-100 text-red-800');
                 return;
            }
            
            if (selectedTileIds.length > 0) {
                showMessage("Bersihkan kata yang sedang dibentuk sebelum melewati giliran!", 'bg-red-100 text-red-800');
                return;
            }

            const penalty = 10;
            score = Math.max(0, score - penalty); 
            scoreEl.textContent = score;

            // KEMBALIKAN SEMUA UBIL DI RAK KE KUMPULAN
            playerRack.forEach(tile => availableTiles.push(tile.letter));
            
            const tilesToDraw = currentRackSize;
            playerRack = [];
            selectedTileIds = [];
            // TARIK UBIL BARU
            drawTiles(tilesToDraw);
            
            wordInputEl.value = '';
            showMessage(`Anda melompati giliran. **-${penalty} poin.** Rak baru ditarik.`, 'bg-red-100 text-red-800');
        }

        function calculateWordScore(wordIds) {
            let totalScore = 0;
            const wordLength = wordIds.length;
            
            wordIds.forEach(id => {
                const tile = playerRack.find(t => t.id === id);
                if (tile) {
                    totalScore += tile.score;
                }
            });
            
            if (wordLength === currentRackSize) {
                totalScore += 50; 
            }
            return totalScore;
        }

        function showMessage(message, classes) {
            messageBoxEl.className = `p-3 rounded-xl shadow-md ${classes}`;
            messageBoxEl.innerHTML = `<span class="font-bold">Game Master:</span> ${message}`;
        }
        
        // --- API Functions (Gemini) ---

        /**
         * API call to validate word and check its Part of Speech against the filter.
         */
        async function validateWordWithGemini(word, requiredPOS) {
            let filterInstruction = '';
            if (requiredPOS !== 'Any') {
                filterInstruction = `CRITICALLY, the primary part of speech of the word must be classified as a ${requiredPOS}. If the word is valid English but does not match the requested part of speech, set 'posMatchesRequiredFilter' to false.`;
            }

            const systemPrompt = `You are a highly accurate English Dictionary bot.
            1. Determine if the provided word is a valid, common English word.
            2. Determine its primary part of speech (Noun, Verb, Adjective, Adverb, etc.).
            ${filterInstruction}
            Respond strictly in JSON format.`;
            
            const userQuery = `Word to validate: "${word}". Required part of speech filter: ${requiredPOS}.`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "word": { "type": "STRING" },
                            "isValidEnglishWord": { "type": "BOOLEAN", "description": "True if the word is a valid, common English word, otherwise False." },
                            "validatedPartOfSpeech": { "type": "STRING", "description": "The primary English Part of Speech (e.g., Noun, Verb, Adjective, Adverb, Interjection)." },
                            "posMatchesRequiredFilter": { "type": "BOOLEAN", "description": "True if validatedPartOfSpeech matches the requested filter (case-insensitive check, or if filter is 'Any'), otherwise False." }
                        },
                        "propertyOrdering": ["word", "isValidEnglishWord", "validatedPartOfSpeech", "posMatchesRequiredFilter"]
                    }
                }
            };
            
            return await callGeminiApi(payload);
        }

        /**
         * API call to get the word definition and info, tailored for absolute beginner students (A1 level).
         */
        async function getWordDefinitionWithGemini(word) {
            const systemPrompt = "You are an English Dictionary and Translator, specializing in materials for Indonesian absolute beginner English students (A1 CEFR level). For the valid English word provided, give the meaning (definition) in English, the Indonesian translation of the meaning, the part of speech in English, and one example sentence in English. CRUCIALLY, ensure the **English definition** and the **English example sentence** use **only the simplest vocabulary** and the **most basic sentence structure** appropriate for a student who is just starting to learn English (A1 level). Also provide the Indonesian translation of the meaning and the Indonesian translation of the example sentence. Provide the response ONLY in JSON format.";
            const userQuery = `Word: ${word}`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "word": { "type": "STRING" },
                            "meaning": { "type": "STRING", "description": "The English definition or meaning of the word (very simple A1 language)." },
                            "part_of_speech": { "type": "STRING", "description": "The English part of speech (e.g., Noun, Verb, Adjective)." },
                            "example_sentence": { "type": "STRING", "description": "A very basic English example sentence using the word (A1 structure)." },
                            "indonesian_translation": { "type": "STRING", "description": "The Indonesian translation of the English meaning/definition." },
                            "indonesian_example": { "type": "STRING", "description": "The Indonesian translation of the example sentence." }
                        },
                        "propertyOrdering": ["word", "part_of_speech", "meaning", "indonesian_translation", "example_sentence", "indonesian_example"]
                    }
                }
            };
            
            return await callGeminiApi(payload);
        }

        /**
         * Helper function to call the Gemini API with retry mechanism.
         */
        async function callGeminiApi(payload) {
            const maxRetries = 5;
            for (let attempt = 0; attempt < maxRetries; attempt++) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        if (response.status === 429) {
                            const delay = Math.pow(2, attempt) * 1000;
                            await new Promise(resolve => setTimeout(resolve, delay));
                            continue;
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                    
                    if (jsonText) {
                        try {
                            return JSON.parse(jsonText);
                        } catch (e) {
                            console.error("Gagal mengurai respons JSON:", e);
                            return null;
                        }
                    }
                    return null;
                } catch (error) {
                    console.error("Gemini API Error:", error);
                    return null;
                }
            }
            return null;
        }
        
        // --- Pronunciation Functions ---
        function setEnglishVoice() {
            if (!('speechSynthesis' in window)) return;

            const voices = speechSynthesis.getVoices();
            if (voices.length === 0) return;

            let bestVoice = null;
            
            for (const voice of voices) {
                if (voice.lang === 'en-US') {
                    if (voice.name.includes('Google') || voice.name.includes('Chrome')) {
                        englishVoice = voice;
                        return;
                    }
                    if (!bestVoice) {
                        bestVoice = voice;
                    }
                }
            }
            englishVoice = englishVoice || bestVoice;
            if (!englishVoice) {
                englishVoice = voices.find(v => v.lang.startsWith('en'));
            }
            
            if (englishVoice) {
                console.log('Selected realistic English voice:', englishVoice.name);
            }
        }

        window.playPronunciation = function(text, btnId) {
            const playBtn = document.getElementById(btnId);
            
            if (!('speechSynthesis' in window)) {
                showMessage("Game Master: Peramban Anda tidak mendukung Web Speech API. Pemutaran suara tidak tersedia.", 'bg-red-100 text-red-800');
                return;
            }

            if (speechSynthesis.speaking) {
                speechSynthesis.cancel();
            }

            if (playBtn) {
                playBtn.disabled = true;
                playBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memuat...';
            }
            
            const utterance = new SpeechSynthesisUtterance(text);
            
            if (englishVoice) {
                utterance.voice = englishVoice;
            } else {
                utterance.lang = 'en-US'; 
            }
            utterance.rate = 0.9; 
            utterance.pitch = 1.0; 
            
            utterance.onend = () => {
                if (playBtn) {
                    playBtn.disabled = false;
                    playBtn.innerHTML = '🔊 Ucapkan';
                }
            };

            utterance.onerror = (event) => {
                console.error('SpeechSynthesis Utterance Error:', event);
                if (playBtn) {
                    playBtn.disabled = false;
                    playBtn.innerHTML = '🔊 Ucapkan';
                }
                showMessage("Game Master: Gagal memutar suara. Pastikan Anda menggunakan peramban modern.", 'bg-red-100 text-red-800');
            };

            speechSynthesis.speak(utterance);
        };


        // --- Main Game Logic (Updated POS Check) ---

        window.playWord = async function() {
            const word = wordInputEl.value.trim().toUpperCase();
            const requiredPOS = posSelectorEl.value; // Get the currently selected POS filter
            const requiredPOSLabel = posSelectorEl.options[posSelectorEl.selectedIndex].text;
            
            playButtonEl.disabled = true;

            if (word.length < 2) {
                showMessage("Kata harus minimal 2 huruf. Klik Huruf di rak untuk membentuk kata.", 'bg-red-100 text-red-800');
                playButtonEl.disabled = false;
                return;
            }
            
            // 1. VALIDASI KATA & POS CHECK
            let validationMessage = `Kata **${word}**. **Memverifikasi keabsahan kata** dan **Kategori: ${requiredPOSLabel}**...`;
            showMessage(validationMessage, 'bg-yellow-200 text-yellow-800');

            const validationResult = await validateWordWithGemini(word, requiredPOS);
            
            if (!validationResult || validationResult.isValidEnglishWord === false) {
                // Case 1: Word is not a valid English word
                showMessage(`Kata **${word}** TIDAK SAH dalam Bahasa Inggris. Huruf dikembalikan ke rak.`, 'bg-red-100 text-red-800');
                clearWord(); 
                playButtonEl.disabled = false;
                return;
            }

            if (validationResult.posMatchesRequiredFilter === false && requiredPOS !== 'Any') {
                // Case 2: Word is valid, but the POS doesn't match the required filter
                const actualPOS = validationResult.validatedPartOfSpeech || 'Lainnya';
                const posIndonesian = translatePOSToIndonesian(actualPOS);
                
                // NOTIFIKASI KATEGORI SALAH: TIDAK ADA SKOR, UBIL DIKEMBALIKAN (clearWord)
                showMessage(`Kata **${word}** SAH, tetapi jenis katanya adalah **${posIndonesian}** (${actualPOS})! Anda memilih kategori **${requiredPOSLabel}**. Huruf dikembalikan ke rak.`, 'bg-red-100 text-red-800');
                clearWord(); 
                playButtonEl.disabled = false;
                return;
            }
            
            // --- KATA SAH DAN KATEGORI COCOK (SKOR DITAMBAHKAN) ---

            // 2. SKOR & DEFINISI
            
            const usedTileIdsSnapshot = [...selectedTileIds];
            const wordScore = calculateWordScore(usedTileIdsSnapshot);
            score += wordScore; // TAMBAH SKOR HANYA DI SINI

            // Efek visual flash skor
            scoreDisplayEl.classList.add('score-flash');
            setTimeout(() => {
                scoreDisplayEl.classList.remove('score-flash');
            }, 500);
            
            showMessage(`Kata **${word}** SAH dan kategori cocok! Anda mendapat **${wordScore}** poin. Mengambil informasi kosakata (Level Pemula/A1)...`, 'bg-yellow-100 text-yellow-800');
            
            const definition = await getWordDefinitionWithGemini(word);

            // 3. UPDATE RAK (GENERATE KATA BARU)
            scoreEl.textContent = score;

            // Hapus ubin yang digunakan
            const usedTileIds = new Set(usedTileIdsSnapshot);
            playerRack = playerRack.filter(tile => !usedTileIds.has(tile.id));
            selectedTileIds = []; // Clear selection for the next turn

            // Isi ulang rak dengan jumlah ubin yang sama dengan yang digunakan
            const refillCount = usedTileIdsSnapshot.length; // Jumlah ubin baru = jumlah ubin yang baru saja digunakan
            drawTiles(refillCount); // INI YANG MENGGENERATE UBIL BARU (SELALU BERUBAH)
            
            // 4. TAMPILKAN HASIL AKHIR
            let infoHtml = `
                Kata **${word}** SAH! Anda mendapat **${wordScore}** poin.`;
            
            if (word.length === currentRackSize) {
                infoHtml += ` <span class="text-xl font-extrabold text-purple-700">(BONUS BINGO +50!)</span>`;
            }
            
            infoHtml += ` <span class="font-bold text-lg text-blue-700">Ditarik **${refillCount}** Huruf baru.</span>`;

            if (definition && definition.meaning) {
                const cleanExampleSentence = (definition.example_sentence || '').replace(/[,.]$/, '').trim(); 
                
                infoHtml += `
                    <div class="mt-4 flex items-center justify-between border-b pb-2">
                        <p class="font-bold text-lg text-blue-700">Pengucapan (Kata):</p>
                        <button id="pronounce-btn-word" onclick="playPronunciation('${word}', 'pronounce-btn-word')" 
                                class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white pronounce-btn shadow-md">
                            🔊 Ucapkan
                        </button>
                    </div>
                `;

                infoHtml += `
                <div class="word-info mt-3 p-3 bg-white rounded-lg border border-green-300">
                    <p class="font-bold text-lg text-green-700">Informasi Kata (Level Pemula A1):</p>
                    <p><strong>Jenis Kata:</strong> ${definition.part_of_speech || 'Tidak Diketahui'}</p>
                    <p><strong>Arti (Inggris):</strong> ${definition.meaning || 'Tidak Tersedia'}</p>
                    <p><strong>Terjemahan (Indonesia):</strong> ${definition.indonesian_translation || 'Tidak Tersedia'}</p>
                    
                    <div class="flex items-center justify-between mt-2 p-2 bg-gray-50 rounded-lg">
                        <p class="w-2/3">
                            <strong>Contoh Kalimat (Inggris):</strong> 
                            <em id="example-text">"${definition.example_sentence || 'Tidak Tersedia'}"</em>
                        </p>
                        <button id="pronounce-btn-example" onclick="playPronunciation('${cleanExampleSentence}', 'pronounce-btn-example')" 
                                class="flex items-center space-x-2 bg-purple-500 hover:bg-purple-600 text-white pronounce-btn shadow-md" 
                                ${!cleanExampleSentence ? 'disabled' : ''}>
                            🔊 Ucapkan Kalimat
                        </button>
                    </div>

                    <p class="mt-2"><strong>Contoh Penggunaan (Indonesia):</strong> <em>"${definition.indonesian_example || 'Tidak Tersedia'}"</em></p>
                </div>
                `;
                showMessage(infoHtml, 'bg-green-100 text-green-800');
            } else {
                showMessage(infoHtml + " (Gagal mendapatkan definisi dari API. Coba kata lain!)", 'bg-green-100 text-green-800');
            }

            playButtonEl.disabled = false;
        }

        // Helper function for translating POS for display
        function translatePOSToIndonesian(pos) {
            pos = pos.toLowerCase().trim();
            if (pos.includes('noun') || pos.includes('kata benda')) return 'Kata Benda';
            if (pos.includes('verb') || pos.includes('kata kerja')) return 'Kata Kerja';
            if (pos.includes('adjective') || pos.includes('kata sifat')) return 'Kata Sifat';
            if (pos.includes('adverb')) return 'Kata Keterangan';
            return 'Lainnya';
        }


        window.startGame = function(isRestart = false) {
            const newRackSize = parseInt(difficultySelectorEl.value);
            currentRackSize = newRackSize;
            score = 0;
            playerRack = []; 
            selectedTileIds = []; 
            scoreEl.textContent = score;

            initializeAvailableTiles(); 
            drawTiles(currentRackSize); 
            wordInputEl.value = '';
            
            wordInputEl.maxLength = currentRackSize; 
            playButtonEl.disabled = true; 

            const difficultyText = difficultySelectorEl.options[difficultySelectorEl.selectedIndex].text;
            const posText = posSelectorEl.options[posSelectorEl.selectedIndex].text;
            
            let message = `Permainan dimulai! Level: **${difficultyText}**. Kategori: **${posText}**. Klik Huruf untuk membentuk kata.`;
            if (isRestart) {
                 message = `Permainan Diulang! Level: **${difficultyText}**. Kategori: **${posText}**. Konten kosakata diatur untuk **Level Pemula (A1)**.`;
            }
            showMessage(message, 'bg-gray-200 text-gray-800');
        }

        // Start the game when DOM is loaded
        window.onload = function() {
            if ('speechSynthesis' in window) {
                speechSynthesis.onvoiceschanged = function() {
                    setEnglishVoice();
                    console.log('Voices loaded and selected.');
                };
                setEnglishVoice(); 
            }
            startGame(false);
        }

    </script>
</body>
</html>
