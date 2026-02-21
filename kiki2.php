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
    <title>Petualangan Cerdas Si Kiki (3-6 Tahun)</title>
    <!-- Memuat Tailwind CSS untuk styling yang cepat dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Menggunakan font Inter untuk tampilan modern */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f9ff; /* Biru muda cerah */
        }
        .container-game {
            max-width: 700px;
            min-height: 80vh;
        }
        .speech-bubble {
            background-color: #ffffff;
            border: 4px solid #f97316; /* Oranye cerah */
            border-radius: 2rem;
            border-bottom-left-radius: 0.5rem;
            position: relative;
            padding: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .gm-avatar {
            width: 100px;
            height: 100px;
            background-color: #fcd34d; /* Kuning ceria */
            border-radius: 50%;
            border: 4px solid #b45309;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn-choice {
            transition: all 0.1s ease-in-out;
            transform: scale(1);
        }
        .btn-choice:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        .btn-primary {
            background-color: #4f46e5; /* Ungu/biru tua */
            color: white;
            border-bottom: 4px solid #3730a3;
        }
        .btn-primary:active {
            transform: translateY(2px);
            border-bottom: 2px solid #3730a3;
        }
        /* Styling khusus untuk tombol input suara */
        #voice-input-btn {
            background-color: #ef4444; /* Merah cerah */
            color: white;
            border-bottom: 4px solid #b91c1c; /* Merah tua */
        }
        #voice-input-btn:active {
            transform: translateY(2px);
            border-bottom: 2px solid #b91c1c;
        }
        #voice-input-btn.listening {
            background-color: #fca5a5; /* Merah muda saat mendengarkan */
            color: #b91c1c;
            animation: pulse-red 1.5s infinite;
        }
        @keyframes pulse-red {
          0%, 100% { opacity: 1; }
          50% { opacity: 0.5; }
        }

        /* Responsivitas untuk layar kecil */
        @media (max-width: 640px) {
            .container-game {
                min-height: 90vh;
            }
            .gm-avatar {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="p-4">
    <div class="container-game mx-auto flex flex-col items-center justify-between py-6">

        <!-- Header dan Avatar Game Master -->
        <header class="w-full text-center mb-6">
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Petualangan Cerdas Si Kiki</h1>
            <p class="text-sm text-gray-500">Oleh Game Master (GM) 🤖</p>
        </header>
        
        <!-- Skor Saat Ini -->
        <div class="w-full text-right mb-4">
            <p id="score-display" class="text-2xl font-bold text-yellow-600">Skor: 0</p>
        </div>


        <!-- Area Dialog Game Master -->
        <div id="dialog-area" class="w-full bg-white p-6 rounded-xl shadow-lg flex flex-col sm:flex-row items-start space-x-4 mb-8">
            <div class="gm-avatar flex-shrink-0 mb-4 sm:mb-0">
                <span id="gm-icon">🤖</span>
            </div>
            <div class="speech-bubble w-full">
                <p id="gm-text" class="text-xl text-gray-700 font-medium">Selamat datang, Adik Cerdas! Aku adalah Game Master. Tekan tombol Mulai untuk memulai petualangan kita!</p>
                <div id="loading-indicator" class="hidden mt-2 text-sm text-blue-600 font-semibold">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    GM Kiki sedang berbicara...
                </div>
            </div>
        </div>

        <!-- Area Interaksi Anak (Tombol & Input) -->
        <div id="interaction-area" class="w-full flex flex-col items-center p-4 bg-white rounded-xl shadow-xl">
            <!-- Panel Kontrol untuk Mulai dan Input Teks/Suara -->
            <div id="control-panel" class="w-full max-w-sm">
                
                <!-- Tombol Mulai Awal -->
                <button id="start-btn" onclick="showDifficultySelection()" class="btn-choice btn-primary p-4 rounded-xl text-lg font-bold shadow-md w-full">Mulai Petualangan!</button>
                
                <!-- Pilihan Kesulitan (Awalnya tersembunyi) -->
                <div id="difficulty-select-area" class="hidden text-center">
                    <p class="text-lg font-bold mb-3 text-gray-700">Pilih Tingkat Kesulitanmu:</p>
                    <div class="flex justify-between space-x-2">
                        <button onclick="selectDifficulty('easy')" class="btn-choice p-3 rounded-xl text-lg font-bold shadow-md bg-green-500 hover:bg-green-600 text-white w-1/3">Mudah</button>
                        <button onclick="selectDifficulty('medium')" class="btn-choice p-3 rounded-xl text-lg font-bold shadow-md bg-yellow-500 hover:bg-yellow-600 text-gray-800 w-1/3">Sedang</button>
                        <button onclick="selectDifficulty('hard')" class="btn-choice p-3 rounded-xl text-lg font-bold shadow-md bg-red-500 hover:bg-red-600 text-white w-1/3">Sulit</button>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">*Sulit = Tantangan dalam bentuk cerita kasus!</p>
                </div>

                <!-- Input Teks dan Suara (Awalnya tersembunyi) -->
                <div id="text-input-area" class="hidden">
                    <input type="text" id="user-input" placeholder="Tulis jawabanmu di sini..." class="w-full p-3 border-4 border-indigo-400 rounded-lg focus:ring-4 focus:ring-indigo-300 focus:border-indigo-500 text-lg">
                    <div class="flex space-x-2 mt-2">
                        <button id="voice-input-btn" onclick="startVoiceInput()" class="btn-choice w-1/3 p-3 rounded-xl text-lg font-bold shadow-md">
                            🎤 Bicara
                        </button>
                        <button onclick="submitAnswer()" class="btn-choice btn-primary w-2/3 p-3 rounded-xl text-lg font-bold shadow-md">Kirim Jawaban</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan TTS -->
        <p class="text-xs text-gray-400 mt-4 text-center">
            *Suara GM disimulasikan melalui Gemini TTS API untuk suara yang lebih realistis.
            Game ini didukung oleh kecerdasan buatan Gemini.
        </p>
    </div>

    <script>
        // --- Konfigurasi API & Global State ---
        const apiKey = "<?php echo $apiKey; ?>"; // API Key kosong karena akan disupply oleh Canvas
        // Menggunakan API khusus untuk Text-to-Speech (TTS)
        const TTS_API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-tts:generateContent?key=${apiKey}`;
        // Menggunakan API untuk Game Logic (LLM)
        const LLM_API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;

        // Konstanta untuk Retry dan Backoff
        const MAX_RETRIES = 3;
        const INITIAL_DELAY_MS = 1000; // 1 detik
        
        const gmTextElement = document.getElementById('gm-text');
        const scoreDisplay = document.getElementById('score-display');
        const loadingIndicator = document.getElementById('loading-indicator');
        const controlPanel = document.getElementById('control-panel'); 
        const textInputArea = document.getElementById('text-input-area');
        const userInput = document.getElementById('user-input');
        const voiceInputBtn = document.getElementById('voice-input-btn');
        const startButton = document.getElementById('start-btn');
        const difficultyArea = document.getElementById('difficulty-select-area');
        
        let gameState = {
            childName: 'Adik Cerdas', // Default name
            score: 0,
            current_topic: 'start', // State LLM saat ini ('start', 'name_input', 'difficulty_select', 'color_challenge', etc.)
            current_level: 0, // Level Tantangan saat ini (0: Belum dimulai, 1-9: Aktif)
            selected_difficulty: null, // 'easy', 'medium', atau 'hard'
            history: [] // Untuk menyimpan riwayat obrolan untuk konteks LLM
        };

        // --- Utilitas Konversi Audio (Diperlukan untuk TTS API) ---

        function base64ToArrayBuffer(base64) {
            const binaryString = window.atob(base64);
            const len = binaryString.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            return bytes.buffer;
        }

        function pcmToWav(pcm16, sampleRate) {
            const buffer = new ArrayBuffer(44 + pcm16.length * 2);
            const view = new DataView(buffer);

            function writeString(view, offset, string) {
                for (let i = 0; i < string.length; i++) {
                    view.setUint8(offset + i, string.charCodeAt(i));
                }
            }

            // RIFF chunk
            writeString(view, 0, 'RIFF'); 
            view.setUint32(4, 36 + pcm16.length * 2, true); 
            writeString(view, 8, 'WAVE'); 

            // FMT sub-chunk
            writeString(view, 12, 'fmt '); 
            view.setUint32(16, 16, true); 
            view.setUint16(20, 1, true); 
            view.setUint16(22, 1, true); 
            view.setUint32(24, sampleRate, true); 
            view.setUint32(28, sampleRate * 2, true); 
            view.setUint16(32, 2, true); 
            view.setUint16(34, 16, true); 

            // DATA sub-chunk
            writeString(view, 36, 'data'); 
            view.setUint32(40, pcm16.length * 2, true); 

            // Write PCM data
            let offset = 44;
            for (let i = 0; i < pcm16.length; i++) {
                view.setInt16(offset, pcm16[i], true);
                offset += 2;
            }

            return new Blob([view], { type: 'audio/wav' });
        }

        // --- Utilitas TTS (Menggunakan Gemini TTS API) ---
        
        let currentAudio = null;

        async function speakGM(text) {
            
            // Hentikan audio sebelumnya
            if (currentAudio) {
                currentAudio.pause();
                currentAudio = null;
            }

            loadingIndicator.classList.remove('hidden');

            const payload = {
                contents: [{
                    parts: [{ text: text }]
                }],
                generationConfig: {
                    responseModalities: ["AUDIO"],
                    speechConfig: {
                        voiceConfig: {
                            prebuiltVoiceConfig: { voiceName: "Kore" } 
                        }
                    }
                },
                model: "gemini-2.5-flash-preview-tts"
            };

            try {
                const response = await fetch(TTS_API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                if (!response.ok) throw new Error(`TTS API failed with status ${response.status}`);

                const result = await response.json();
                const candidate = result.candidates?.[0];
                const part = candidate?.content?.parts?.[0];
                const audioData = part?.inlineData?.data;
                const mimeType = part?.inlineData?.mimeType;

                if (!audioData || !mimeType) {
                    throw new Error("TTS response missing audio data.");
                }

                const sampleRateMatch = mimeType.match(/rate=(\d+)/);
                const sampleRate = sampleRateMatch ? parseInt(sampleRateMatch[1], 10) : 24000;
                
                const pcmData = base64ToArrayBuffer(audioData);
                const pcm16 = new Int16Array(pcmData);
                const wavBlob = pcmToWav(pcm16, sampleRate);
                const audioUrl = URL.createObjectURL(wavBlob);
                currentAudio = new Audio(audioUrl);

                currentAudio.onended = () => {
                    loadingIndicator.classList.add('hidden');
                    URL.revokeObjectURL(audioUrl); 
                    currentAudio = null;
                };

                currentAudio.onerror = (e) => {
                    console.error("Audio playback error:", e);
                    loadingIndicator.classList.add('hidden');
                    currentAudio = null;
                };

                currentAudio.play();

            } catch (error) {
                console.error("Error in speakGM (TTS API):", error);
                loadingIndicator.classList.add('hidden');
            }
        }

        // --- Konfigurasi Sistem LLM ---
        const systemInstruction = {
            parts: [{ 
                text: `
                    Anda adalah Game Master Kiki, karakter yang ramah, ceria, dan sangat mendidik. 
                    Anda berbicara dalam Bahasa Indonesia.
                    Target audiens Anda adalah anak-anak usia 3-6 tahun.
                    
                    Tujuan Anda adalah memandu pemain melalui tantangan yang memiliki 3 level kesulitan yang terus meningkat per tema.
                    
                    STATUS GAME:
                    - current_topic: ('start', 'name_input', 'difficulty_select', 'color_challenge', 'number_challenge', 'emotion_challenge', atau 'ending')
                    - current_level: (1 hingga 10. 1-3=Warna, 4-6=Angka, 7-9=Emosi, 10=Selesai)
                    - selected_difficulty: ('easy' untuk Mudah, 'medium' untuk Sedang, 'hard' untuk Sulit).
                    
                    Gaya Narasi Berdasarkan 'selected_difficulty':
                    1. 'easy' (Mudah): Berikan pertanyaan yang SANGAT LANGSUNG dan JELAS. Contoh: "Apa warna matahari?"
                    2. 'medium' (Sedang): Berikan pertanyaan yang sedikit lebih detail dan memerlukan pengamatan. Contoh: "Kamu melihat apel merah dan pisang kuning. Sebutkan warna buah lain di sekitarmu!"
                    3. 'hard' (Sulit): Berikan NARASI KASUS SEDERHANA yang memerlukan sedikit penalaran kontekstual untuk menjawab. Gunakan skenario teman atau aktivitas sehari-hari. Contoh: "Kiki sedang menggambar matahari ☀️. Dia ingin warnanya sangat cerah seperti di langit. Warna apa yang harus Kiki pakai untuk matahari?"
                    
                    Aturan Progresi Level:
                    - Pindah ke 'number_challenge' HANYA JIKA current_level mencapai 4 (setelah menyelesaikan Level 3 Warna).
                    - Pindah ke 'emotion_challenge' HANYA JIKA current_level mencapai 7 (setelah menyelesaikan Level 3 Angka).
                    - Pindah ke 'ending' HANYA JIKA current_level mencapai 10 (setelah menyelesaikan Level 3 Emosi).
                    
                    Anda HARUS SELALU merespons dalam format JSON sesuai skema yang disediakan.
                    
                    Jika pengguna memberikan jawaban yang benar, tingkatkan 'score_delta' menjadi 1 DAN 'current_level' harus bertambah 1. Jika salah, set 'score_delta' ke 0 dan ulangi level yang sama.
                    
                    Gunakan emoji yang menarik dalam 'gm_text'.
                    Anda HARUS SELALU menyertakan bidang 'tts_text' yang berisi versi teks yang SANGAT BERSIH (tanpa emoji, tanpa placeholder seperti [nama]) untuk pengucapan yang cepat dan jernih oleh TTS. Ganti [nama] dengan 'Adik Cerdas' dalam tts_text, dan [nama] dengan nilai gameState.childName di gm_text.
                `
            }]
        };

        const responseSchema = {
            type: "OBJECT",
            properties: {
                "gm_text": { "type": "STRING", "description": "Respon GM Kiki yang ramah dan menarik (termasuk emoji dan placeholder [nama])." },
                "tts_text": { "type": "STRING", "description": "Versi teks HANYA berisi kata-kata bersih (tanpa emoji, tanpa placeholder) yang dioptimalkan untuk pengucapan TTS." },
                "challenge_type": { "type": "STRING", "enum": ["input", "end"], "description": "Tipe interaksi berikutnya: input, atau end." },
                "is_correct": { "type": "BOOLEAN", "description": "TRUE jika giliran sebelumnya adalah jawaban yang benar, FALSE jika salah, NULL/hilang jika hanya alur cerita." },
                "score_delta": { "type": "NUMBER", "description": "Perubahan skor (0 atau 1) berdasarkan giliran sebelumnya." },
                "current_topic": { "type": "STRING", "description": "Topik atau status game saat ini." },
                "current_level": { "type": "NUMBER", "description": "Level Tantangan saat ini (1-10)." }
            },
            required: ["gm_text", "tts_text", "challenge_type", "current_topic", "current_level"]
        };

        // --- Logika Interaksi LLM ---
        
        async function processGeminiTurn(userAction) {
            loadingIndicator.classList.remove('hidden');
            controlPanel.style.pointerEvents = 'none';

            // Tambahkan aksi pengguna dan state saat ini ke riwayat
            const stateInfo = `| Current State: {topic: ${gameState.current_topic}, level: ${gameState.current_level}, difficulty: ${gameState.selected_difficulty}}`;
            const fullUserAction = userAction ? `${userAction} ${stateInfo}` : stateInfo;
            
            gameState.history.push({ role: "user", parts: [{ text: fullUserAction }] });

            const payload = {
                contents: gameState.history,
                systemInstruction: systemInstruction,
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: responseSchema
                },
            };
            
            let jsonResponse = null;
            let lastError = null;

            for (let i = 0; i < MAX_RETRIES; i++) {
                try {
                    const response = await fetch(LLM_API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                    if (!jsonText) {
                        throw new Error("Missing JSON text or invalid response structure from API.");
                    }
                    
                    jsonResponse = JSON.parse(jsonText);
                    break; 

                } catch (error) {
                    lastError = error;
                    console.error(`Attempt ${i + 1} failed:`, error);
                    if (i === MAX_RETRIES - 1) { break; }
                    const delay = INITIAL_DELAY_MS * Math.pow(2, i);
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            } 

            if (jsonResponse) {
                handleGMResponse(jsonResponse);
            } else {
                console.error("All retries failed. Last error:", lastError);
                handleError("Maaf, GM Kiki mengalami masalah teknis berulang. Coba lagi nanti ya.");
                loadingIndicator.classList.add('hidden'); 
            }

            controlPanel.style.pointerEvents = 'auto';
        }

        function handleGMResponse(response) {
            
            // 1. Update Game State
            if (response.score_delta) {
                gameState.score += response.score_delta;
            }
            if (response.current_topic) {
                gameState.current_topic = response.current_topic;
            }
            if (response.current_level) {
                gameState.current_level = response.current_level;
            }
            
            // 2. Update Score Display
            scoreDisplay.textContent = `Skor: ${gameState.score}`;

            // 3. Update Dialog and TTS
            const gmMessage = response.gm_text.replace(/\[nama\]/g, gameState.childName); 
            const ttsMessage = response.tts_text.replace(/\[nama\]/g, gameState.childName); 
            
            gmTextElement.textContent = gmMessage;
            speakGM(ttsMessage);

            // 4. Update Interaction Area
            userInput.value = '';
            difficultyArea.classList.add('hidden');
            startButton.classList.add('hidden');

            if (gameState.current_topic === 'difficulty_select') {
                // Saat GM meminta anak memilih kesulitan
                difficultyArea.classList.remove('hidden');
                textInputArea.classList.add('hidden');
            } else if (response.challenge_type === 'input') {
                // Tampilkan input teks/suara
                textInputArea.classList.remove('hidden');
                userInput.focus();
            } else if (response.challenge_type === 'end') {
                // Akhir permainan
                textInputArea.classList.add('hidden');
                startButton.textContent = 'Mulai Petualangan Baru!';
                startButton.classList.remove('hidden'); 
            }
            
            // 5. Tambahkan respons GM ke riwayat
            gameState.history.push({ role: "model", parts: [{ text: JSON.stringify(response) }] });
        }
        
        // --- Logika Alur Game Baru ---

        function showDifficultySelection() {
            // Reset state (kecuali nama)
            gameState.score = 0;
            gameState.history = [];
            gameState.current_topic = 'difficulty_select'; // State baru
            gameState.current_level = 0;
            gameState.selected_difficulty = null;
            scoreDisplay.textContent = `Skor: ${gameState.score}`;
            
            // Tampilkan UI Pemilihan Kesulitan
            startButton.classList.add('hidden');
            textInputArea.classList.add('hidden');
            difficultyArea.classList.remove('hidden');
            
            // Kirim prompt ke LLM untuk meminta pemilihan kesulitan
            const difficultyPrompt = `Aku ingin bermain. Sekarang aku akan memilih tingkat kesulitan. Nama anak adalah ${gameState.childName}. Minta aku memilih kesulitan: 'easy', 'medium', atau 'hard'.`;
            
            // Perbarui teks GM secara langsung tanpa TTS saat UI berganti ke pilihan
            gmTextElement.textContent = `Baik, ${gameState.childName}! Pilih dulu tingkat kesulitan petualanganmu di bawah ini:`;
            speakGM(gmTextElement.textContent);
            
            gameState.history.push({ role: "user", parts: [{ text: difficultyPrompt }] });
        }

        function selectDifficulty(level) {
            gameState.selected_difficulty = level;
            const difficultyMap = { 'easy': 'Mudah', 'medium': 'Sedang', 'hard': 'Sulit' };
            
            // Tampilkan loading saat memproses pilihan
            loadingIndicator.classList.remove('hidden');
            
            // Kirim pilihan dan nama anak untuk memulai tantangan pertama
            const initialPrompt = `Aku memilih kesulitan ${difficultyMap[level]} (${level}). Tolong sapa aku dan mulai tantangan 'color_challenge' Level 1.`;
            
            difficultyArea.classList.add('hidden');
            
            // GM akan merespons dengan tantangan pertama (color_challenge level 1)
            processGeminiTurn(initialPrompt);
        }

        // --- Logika Input Suara (Voice Input) ---
        let recognition = null;

        function startVoiceInput() {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                gmTextElement.textContent = "Maaf, browser Anda tidak mendukung input suara. Silakan gunakan input teks.";
                return;
            }

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (recognition) {
                recognition.stop();
                recognition = null;
                voiceInputBtn.textContent = '🎤 Bicara';
                voiceInputBtn.classList.remove('listening');
                return;
            }

            recognition = new SpeechRecognition();
            recognition.continuous = false; 
            recognition.lang = 'id-ID'; 
            recognition.interimResults = false; 

            recognition.onstart = () => {
                userInput.value = '';
                voiceInputBtn.textContent = '🔴 Mendengar...';
                voiceInputBtn.classList.add('listening');
                userInput.placeholder = "Bicaralah sekarang...";
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                userInput.value = transcript;
                userInput.placeholder = `Hasil: "${transcript}". Mengirim...`;
            };

            recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                voiceInputBtn.textContent = '❌ Coba Lagi';
                userInput.placeholder = "Tulis jawabanmu di sini...";
                handleError(`Maaf, ada masalah dengan mikrofon: ${event.error}. Coba input teks ya.`);
            };

            recognition.onend = () => {
                voiceInputBtn.classList.remove('listening');
                
                const submitted = userInput.value.trim() !== "";
                
                voiceInputBtn.textContent = '🎤 Bicara';
                userInput.placeholder = "Tulis jawabanmu di sini...";
                
                // Auto-submit jika ada transkripsi yang berhasil
                if (submitted) {
                    submitAnswer();
                }
            };

            recognition.start();
        }
        
        // --- Fungsi Kontrol Game Utama ---
        
        function handleError(message) {
            gmTextElement.textContent = message;
            textInputArea.classList.add('hidden');
            difficultyArea.classList.add('hidden');
            startButton.textContent = 'Coba Ulangi Game';
            startButton.classList.remove('hidden');
        }

        /**
         * Menangani submit dari input teks atau suara.
         */
        function submitAnswer() {
            const input = userInput.value.trim();
            if (input.length === 0) {
                speakGM("Tolong tulis jawabanmu atau gunakan tombol Bicara, Adik Cerdas!");
                return;
            }

            // Jika sedang dalam state 'start', anggap ini adalah input nama
            if (gameState.current_topic === 'start' || gameState.current_topic === 'name_input') {
                gameState.childName = input.substring(0, 15);
                // Setelah nama, langsung tampilkan pemilihan kesulitan
                showDifficultySelection();
                return;
            }
            
            // Kirim jawaban sebagai prompt
            processGeminiTurn(`Jawabanku adalah: ${input}`);
        }
        
        window.onload = function() {
            // GM hanya menampilkan teks awal, TTS dipicu saat memulai
        };
    </script>
</body>
</html>
