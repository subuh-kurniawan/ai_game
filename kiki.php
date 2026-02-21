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

        <!-- Area Dialog Game Master -->
        <div id="dialog-area" class="w-full bg-white p-6 rounded-xl shadow-lg flex flex-col sm:flex-row items-start space-x-4 mb-8">
            <div class="gm-avatar flex-shrink-0 mb-4 sm:mb-0">
                <span id="gm-icon">🤖</span>
            </div>
            <div class="speech-bubble w-full">
                <!-- Teks ini akan muncul statis, TTS akan dipicu setelah tombol Mulai diklik -->
                <p id="gm-text" class="text-xl text-gray-700 font-medium">Selamat datang, Adik Cerdas! Aku adalah Game Master. Tekan tombol Mulai untuk memulai petualangan kita!</p>
                <audio id="audio-player" class="mt-4 w-full" controls autoplay hidden></audio>
                <div id="loading-indicator" class="hidden mt-2 text-sm text-blue-600 font-semibold">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    GM sedang berpikir...
                </div>
            </div>
        </div>

        <!-- Area Interaksi Anak (Tombol & Input) -->
        <div id="interaction-area" class="w-full flex flex-col items-center p-4 bg-white rounded-xl shadow-xl">
            <!-- Pilihan (Choices) akan muncul di sini -->
            <div id="choice-buttons" class="grid grid-cols-2 gap-4 w-full max-w-md">
                <button onclick="startGame()" class="btn-choice btn-primary p-4 rounded-xl text-lg font-bold shadow-md">Mulai Petualangan!</button>
            </div>
            <!-- Input Teks (Text Input) akan muncul di sini -->
            <div id="text-input-area" class="hidden w-full max-w-sm mt-4">
                <input type="text" id="user-input" placeholder="Tulis jawabanmu di sini..." class="w-full p-3 border-4 border-indigo-400 rounded-lg focus:ring-4 focus:ring-indigo-300 focus:border-indigo-500 text-lg">
                <button onclick="submitAnswer()" class="btn-choice btn-primary w-full mt-2 p-3 rounded-xl text-lg font-bold shadow-md">Kirim Jawaban</button>
            </div>
        </div>

        <!-- Catatan TTS -->
        <p class="text-xs text-gray-400 mt-4 text-center">
            *Suara GM disimulasikan melalui Gemini TTS API untuk pengalaman interaktif.
        </p>
    </div>

    <script>
        // --- Konfigurasi dan Utilitas TTS ---

        const gmTextElement = document.getElementById('gm-text');
        const audioPlayer = document.getElementById('audio-player');
        const loadingIndicator = document.getElementById('loading-indicator');
        const choiceButtonsArea = document.getElementById('choice-buttons');
        const textInputArea = document.getElementById('text-input-area');
        const userInput = document.getElementById('user-input');

        const GM_VOICE = "Charon"; // Suara Informative/Firm yang cocok untuk Game Master

        // Global variables for Firebase and Auth (required for API calls)
        const apiKey = "<?php echo $apiKey; ?>";
        const TTS_API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-tts:generateContent?key=${apiKey}`;

        /**
         * Mengubah base64 PCM data menjadi ArrayBuffer.
         * @param {string} base64 - Base64 string dari audio PCM.
         * @returns {ArrayBuffer}
         */
        function base64ToArrayBuffer(base64) {
            const binaryString = atob(base64);
            const len = binaryString.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            return bytes.buffer;
        }

        /**
         * Mengubah data PCM (Int16Array) menjadi Blob WAV yang dapat diputar.
         * @param {Int16Array} pcm16 - Audio data PCM 16-bit.
         * @param {number} sampleRate - Sample rate (misalnya 24000).
         * @returns {Blob}
         */
        function pcmToWav(pcm16, sampleRate) {
            const buffer = new ArrayBuffer(44 + pcm16.length * 2);
            const view = new DataView(buffer);

            /* RIFF identifier */
            view.setUint32(0, 0x52494646, false); // "RIFF"
            /* RIFF chunk length */
            view.setUint32(4, 36 + pcm16.length * 2, true);
            /* RIFF type */
            view.setUint32(8, 0x57415645, false); // "WAVE"
            /* format chunk identifier */
            view.setUint32(12, 0x666d7420, false); // "fmt "
            /* format chunk length */
            view.setUint32(16, 16, true);
            /* sample format (raw) */
            view.setUint16(20, 1, true); // 1 = PCM
            /* channel count */
            view.setUint16(22, 1, true); // Mono
            /* sample rate */
            view.setUint32(24, sampleRate, true);
            /* byte rate (sample rate * block align) */
            view.setUint32(28, sampleRate * 2, true);
            /* block align (channels * bytes per sample) */
            view.setUint16(32, 2, true); // 16-bit
            /* bits per sample */
            view.setUint16(34, 16, true);
            /* data chunk identifier */
            view.setUint32(36, 0x64617461, false); // "data"
            /* data chunk length */
            view.setUint32(40, pcm16.length * 2, true);

            // Write PCM data
            for (let i = 0; i < pcm16.length; i++) {
                view.setInt16(44 + i * 2, pcm16[i], true);
            }

            return new Blob([buffer], { type: 'audio/wav' });
        }

        /**
         * Memanggil Gemini TTS API dan memainkan audio.
         * @param {string} text - Teks yang akan diucapkan.
         */
        async function speakGM(text) {
            audioPlayer.pause();
            audioPlayer.removeAttribute('src');
            loadingIndicator.classList.remove('hidden');

            const payload = {
                contents: [{
                    parts: [{ text: text }]
                }],
                generationConfig: {
                    responseModalities: ["AUDIO"],
                    speechConfig: {
                        voiceConfig: {
                            prebuiltVoiceConfig: { voiceName: GM_VOICE }
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

                if (!response.ok) {
                    throw new Error(`TTS API failed: ${response.statusText}`);
                }

                const result = await response.json();
                const part = result?.candidates?.[0]?.content?.parts?.[0];
                const audioData = part?.inlineData?.data;
                const mimeType = part?.inlineData?.mimeType;

                if (audioData && mimeType && mimeType.startsWith("audio/")) {
                    // Extract sample rate from mimeType string (e.g., audio/L16;rate=24000)
                    const rateMatch = mimeType.match(/rate=(\d+)/);
                    if (!rateMatch) throw new Error("Could not find sample rate in mimeType.");
                    const sampleRate = parseInt(rateMatch[1], 10);

                    const pcmData = base64ToArrayBuffer(audioData);
                    const pcm16 = new Int16Array(pcmData);

                    const wavBlob = pcmToWav(pcm16, sampleRate);
                    const audioUrl = URL.createObjectURL(wavBlob);

                    audioPlayer.src = audioUrl;
                    audioPlayer.play();
                } else {
                    console.error("TTS response missing audio data.");
                    // Fallback to text if audio fails
                    gmTextElement.textContent = text;
                }
            } catch (error) {
                console.error("Error generating or playing audio:", error);
                // Fallback to text if audio fails
                gmTextElement.textContent = text;
            } finally {
                loadingIndicator.classList.add('hidden');
            }
        }

        // --- Logika Game ---

        let gameState = {
            scene: 'start',
            childName: 'Adik Cerdas', // Default name
            score: 0
        };

        const SCENES = {
            'start': {
                text: "Halo! Aku Kiki si Game Master, dan aku akan jadi teman petualanganmu hari ini! Sebelum kita mulai, siapa nama kamu?",
                type: 'input',
                next: 'intro_name_response',
                action: (input) => {
                    const name = input.trim().substring(0, 15); // Batasi nama
                    if (name.length > 0) {
                        gameState.childName = name;
                        return true;
                    }
                    return false;
                }
            },
            'intro_name_response': {
                text: (name) => `Senang bertemu denganmu, ${name}! Wah, nama yang indah! Apakah kamu siap untuk Petualangan Cerdas yang pertama?`,
                type: 'choices',
                options: [
                    { text: "Siap!", next: 'challenge_color' },
                    { text: "Belum siap...", next: 'intro_not_ready' }
                ]
            },
            'intro_not_ready': {
                text: (name) => `Tidak apa-apa, ${name}. Kalau begitu, coba kita lakukan pemanasan dulu. Bisakah kamu tersenyum lebar untuk Kiki? (😁)`,
                type: 'choices',
                options: [
                    { text: "Aku senyum!", next: 'challenge_color' },
                    { text: "Tetap cemberut (😔)", next: 'intro_not_ready_2' }
                ]
            },
            'intro_not_ready_2': {
                text: (name) => `Ah, kamu lucu sekali! Tapi Kiki lebih suka melihat senyummu. Sekarang, apakah sudah siap main warna?`,
                type: 'choices',
                options: [
                    { text: "Siap!", next: 'challenge_color' }
                ]
            },
            'challenge_color': {
                text: (name) => `Hebat, ${name}! Coba lihat tiga warna di bawah ini. Manakah warna yang sama dengan buah Apel Merah?`,
                type: 'choices',
                answer: 'Merah',
                options: [
                    { text: "Kuning 🟡", next: 'wrong_answer' },
                    { text: "Merah 🔴", next: 'correct_color' },
                    { text: "Biru 🔵", next: 'wrong_answer' }
                ]
            },
            'correct_color': {
                text: (name) => `🎉 Wah, pintar sekali, ${name}! Kamu benar! Merah seperti Apel dan Hati Kiki untukmu! Skor: ${++gameState.score}. Sekarang kita pindah ke tantangan angka!`,
                type: 'choices',
                options: [
                    { text: "Angka!", next: 'challenge_number' }
                ]
            },
            'wrong_answer': {
                text: (name) => `Hmm, hampir benar, ${name}. Ingat, Apel Merah warnanya seperti api! Coba lagi, cari warna Merah 🔴.`,
                type: 'choices',
                options: [
                    { text: "Kuning 🟡", next: 'challenge_color_retry_wrong' },
                    { text: "Merah 🔴", next: 'correct_color' },
                    { text: "Biru 🔵", next: 'challenge_color_retry_wrong' }
                ]
            },
            'challenge_color_retry_wrong': {
                text: (name) => `Aduh, sepertinya kamu butuh bantuan. Merah itu warna yang cerah! Coba tekan tombol "Merah 🔴" lagi ya, ${name}?`,
                type: 'choices',
                options: [
                    { text: "Kuning 🟡", next: 'correct_color' }, // Memaksa ke jawaban benar setelah 2x salah
                    { text: "Merah 🔴", next: 'correct_color' },
                    { text: "Biru 🔵", next: 'correct_color' }
                ]
            },
            'challenge_number': {
                text: (name) => `Sekarang lihat ini! Kiki punya 4 BOLA ⚽⚽⚽⚽. Kalau Kiki beri kamu 1 BOLA, berapa sisa bola Kiki? Tulis angkanya! (1, 2, atau 3?)`,
                type: 'input',
                answer: '3',
                next: 'correct_number',
                action: (input) => {
                    return input.trim() === '3';
                }
            },
            'correct_number': {
                text: (name) => `💫 Sempurna, ${name}! 4 dikurangi 1 memang sama dengan 3! Kamu hebat dalam berhitung! Skor: ${++gameState.score}. Sekarang tantangan terakhir: Emosi.`,
                type: 'choices',
                options: [
                    { text: "Lanjut Emosi", next: 'challenge_emotion' }
                ]
            },
            'challenge_emotion': {
                text: (name) => `Bayangkan ada temanmu yang terjatuh (🥺). Temanmu menangis. Apa yang harus kamu lakukan untuk membuatnya merasa lebih baik?`,
                type: 'choices',
                options: [
                    { text: "Tertawa", next: 'emotion_wrong' },
                    { text: "Membantu berdiri dan memeluk", next: 'emotion_correct' },
                    { text: "Pergi meninggalkannya", next: 'emotion_wrong' }
                ]
            },
            'emotion_correct': {
                text: (name) => `❤️ Benar sekali, ${name}! Membantu teman yang sedih atau sakit adalah perbuatan yang sangat baik! Kamu punya hati yang hangat. Skor: ${++gameState.score}.`,
                type: 'choices',
                options: [
                    { text: "Akhiri Petualangan", next: 'end_game' }
                ]
            },
            'emotion_wrong': {
                text: (name) => `Hmm, Kiki rasa itu bukan cara terbaik. Teman yang sedih butuh kasih sayang. Coba kita ulangi. Apa yang harus dilakukan untuk teman yang menangis?`,
                type: 'choices',
                options: [
                    { text: "Tertawa", next: 'emotion_correct' }, // Memaksa ke jawaban benar
                    { text: "Membantu berdiri dan memeluk", next: 'emotion_correct' },
                    { text: "Pergi meninggalkannya", next: 'emotion_correct' }
                ]
            },
            'end_game': {
                text: (name) => `Hebat, ${name}! Kamu telah menyelesaikan Petualangan Cerdas hari ini dengan total ${gameState.score} bintang! Kamu Cerdas, Baik Hati, dan Pintar! Sampai jumpa di petualangan Kiki berikutnya! 👋`,
                type: 'choices',
                options: [
                    { text: "Mulai Baru", next: 'restart' }
                ]
            },
            'restart': {
                text: "Petualangan baru dimulai! Siapa namamu?",
                type: 'input',
                next: 'intro_name_response',
                action: (input) => {
                    gameState.score = 0;
                    const name = input.trim().substring(0, 15);
                    if (name.length > 0) {
                        gameState.childName = name;
                        return true;
                    }
                    return false;
                }
            }
        };

        /**
         * Memperbarui tampilan dialog dan interaksi sesuai dengan scene saat ini.
         */
        function updateScene() {
            const currentScene = SCENES[gameState.scene];

            // 1. Tampilkan Teks GM
            let gmMessage = typeof currentScene.text === 'function' ? currentScene.text(gameState.childName) : currentScene.text;
            gmTextElement.textContent = gmMessage;
            speakGM(gmMessage); // Panggil TTS untuk membacakan

            // 2. Bersihkan dan atur Area Interaksi
            choiceButtonsArea.innerHTML = '';
            textInputArea.classList.add('hidden');
            userInput.value = '';

            if (currentScene.type === 'choices') {
                // Tampilkan tombol pilihan
                currentScene.options.forEach(option => {
                    const button = document.createElement('button');
                    button.textContent = option.text;
                    button.className = 'btn-choice btn-primary p-4 rounded-xl text-lg font-bold shadow-md';
                    button.onclick = () => handleChoice(option.text, option.next);
                    choiceButtonsArea.appendChild(button);
                });
                choiceButtonsArea.style.display = 'grid';

            } else if (currentScene.type === 'input') {
                // Tampilkan input teks
                textInputArea.classList.remove('hidden');
                choiceButtonsArea.style.display = 'none';
            }
        }

        /**
         * Memulai game.
         */
        function startGame() {
            gameState.scene = 'start';
            updateScene();
        }

        /**
         * Menangani input tombol pilihan.
         * @param {string} choiceText - Teks pada tombol yang diklik.
         * @param {string} nextScene - Nama scene berikutnya.
         */
        function handleChoice(choiceText, nextScene) {
            const currentScene = SCENES[gameState.scene];

            if (currentScene.answer && choiceText.includes(currentScene.answer)) {
                // Jawaban Benar
                gameState.scene = SCENES[gameState.scene].options.find(opt => opt.text === choiceText).next;
            } else if (currentScene.answer) {
                // Jawaban Salah
                // GM memberikan umpan balik dan memuat scene berikutnya (biasanya retry)
                gameState.scene = nextScene;

            } else {
                // Pilihan Alur (Non-jawaban)
                gameState.scene = nextScene;
            }

            updateScene();
        }

        /**
         * Menangani submit dari input teks.
         */
        function submitAnswer() {
            const currentScene = SCENES[gameState.scene];
            const input = userInput.value.trim();

            if (currentScene.action && currentScene.action(input)) {
                // Tindakan berhasil (misalnya, nama diisi, atau jawaban benar)
                gameState.scene = currentScene.next;
            } else if (currentScene.action && !currentScene.action(input) && currentScene.answer) {
                // Jawaban angka salah
                const wrongText = `Ups, bukan itu jawabannya, ${gameState.childName}. Coba hitung lagi! Ingat, Kiki punya 4 bola, kamu ambil 1. Berapa sisanya? Coba masukkan angka yang lain.`;
                gmTextElement.textContent = wrongText;
                speakGM(wrongText);
                return;
            } else {
                // Input nama kosong
                const wrongText = "Tolong tulis namamu di kolom ini ya. Kiki ingin tahu nama teman barunya!";
                gmTextElement.textContent = wrongText;
                speakGM(wrongText);
                return;
            }
            updateScene();
        }
        
        // Mulai dengan scene awal saat window selesai dimuat.
        window.onload = function() {
            // Menghapus panggilan speakGM di sini untuk menghindari NotAllowedError.
            // Panggilan TTS pertama akan terjadi saat pengguna mengklik tombol "Mulai Petualangan!", 
            // yang memicu startGame() dan kemudian updateScene().
        };
    </script>
</body>
</html>
