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
    <title>Petualangan Cerdas Ceria Si Kiki (3-6 Tahun)</title>
    <!-- Memuat Tailwind CSS untuk styling yang cepat dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* --- Palet Warna & Font Ceria --- */
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;700;800&family=Inter:wght@400;700;800&display=swap');
        
        /* Konfigurasi Tailwind untuk warna pastel/ceria */
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'kiki-pink': '#FFC0CB', /* Pink Pastel */
                        'kiki-blue': '#AEC6CF', /* Blue Pastel */
                        'kiki-yellow': '#FDFD96', /* Kuning Lemon */
                        'kiki-green': '#77DD77', /* Hijau Mint */
                        'kiki-red': '#FF6961', /* Merah Salmon */
                        'kiki-purple': '#B39EB5' /* Ungu Pastel */
                    },
                }
            }
        }

        body {
            font-family: 'Fredoka', sans-serif; /* Font yang lebih playful */
            background: linear-gradient(135deg, #FDFD96, #FFC0CB, #AEC6CF); /* Gradient Ceria */
            background-size: 400% 400%;
            animation: gradient-animation 15s ease infinite; /* Animasi latar belakang */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container-game {
            max-width: 700px;
            width: 100%;
            background-color: #FFFFFF;
            border-radius: 2.5rem; /* Sudut Sangat Bulat */
            border: 8px solid #FFD700; /* Border Kuning Mengkilap */
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3), 0 0 0 4px #FF6961; /* Shadow pop-out */
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 85vh; 
        }

        /* --- Header dan Skor Ceria --- */
        header {
            padding: 1.5rem;
            background-color: #FFC0CB; /* Pink Ceria */
            border-bottom: 5px solid #FF6961; /* Garis Merah */
            text-shadow: 2px 2px 0 rgba(255, 255, 255, 0.7);
        }
        
        #score-display {
            padding: 0.5rem 1.5rem;
            background-color: #FDFD96; /* Kuning Lemon */
            color: #FF6961; /* Merah */
            border-bottom: 3px solid #FFD700; 
            font-size: 1.875rem; /* text-3xl */
            font-weight: 800; /* Extra bold */
            text-shadow: 1px 1px 0 #FFF;
            transition: transform 0.2s, color 0.2s; /* Transisi untuk animasi pop */
        }
        
        /* ANIMASI BARU: Animasi Skor Saat Bertambah */
        @keyframes score-pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); color: #FF4500; } /* Warna berubah saat pop */
            100% { transform: scale(1); }
        }
        .score-animated {
            animation: score-pop 0.5s ease-out 1;
        }

        /* --- Avatar dan Dialog --- */
        .gm-avatar {
            width: 100px;
            height: 100px;
            background-color: #AEC6CF; /* Biru Pastel */
            border-radius: 50%;
            border: 6px solid #B39EB5; /* Ungu Pastel */
            font-size: 3.5rem;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s;
        }

        .gm-avatar.speaking {
            animation: bounce-scale 0.8s ease-in-out infinite alternate;
        }

        @keyframes bounce-scale {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(-10px) scale(1.1); }
        }
        
        /* ANIMASI BARU: Animasi Border Berkedip Lembut pada Kotak Dialog */
        @keyframes pulse-border {
            0% { border-color: #FFD700; box-shadow: 0 8px 0 #AEC6CF; }
            50% { border-color: #FF6961; box-shadow: 0 8px 15px rgba(255, 105, 97, 0.5); }
            100% { border-color: #FFD700; box-shadow: 0 8px 0 #AEC6CF; }
        }

        .speech-bubble {
            background-color: #ffffff;
            border: 4px solid #FFD700; /* Kuning terang */
            border-radius: 2rem 2rem 2rem 0.5rem; /* Sudut bicara kiki */
            position: relative;
            padding: 1.5rem;
            box-shadow: 0 8px 0 #AEC6CF; /* Shadow lembut 3D effect */
            opacity: 0;
            transform: translateY(20px);
            /* Gabungkan animasi pulse-border dan fade-in-up */
            animation: pulse-border 5s infinite alternate ease-in-out, fade-in-up 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; 
        }
        
        /* Membuat ujung bubble */
        .speech-bubble:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: -18px; /* Posisi relatif terhadap avatar */
            width: 0;
            height: 0;
            border: 10px solid transparent;
            border-right-color: #FFFFFF;
            border-bottom: 0;
            border-left: 0;
        }

        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Tombol & Input Ceria --- */
        .btn-choice {
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Animasi Elastis */
            transform: scale(1);
            border-radius: 18px; /* Sudut yang sangat bulat */
            font-weight: 800;
            box-shadow: 0 6px 0 #a0a0a0; /* Shadow 3D */
            border: 3px solid #444; /* Border tegas */
        }
        .btn-choice:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 0 #a0a0a0;
        }
        .btn-choice:active {
            transform: translateY(4px) scale(0.98);
            box-shadow: 0 2px 0 #a0a0a0; /* Efek ditekan */
        }

        /* Tombol Aksi Utama (Mulai/Kirim) */
        .btn-primary {
            background: linear-gradient(145deg, #77DD77, #4CAF50); /* Gradient Hijau Cerah */
            color: white;
            box-shadow: 0 6px 0 #468246; /* Shadow Hijau Tua */
            border-color: #468246;
        }
        .btn-primary:active {
            box-shadow: 0 2px 0 #468246;
        }

        /* Tombol Input Suara */
        #voice-input-btn {
            background: linear-gradient(145deg, #FF6961, #E53935); /* Gradient Merah Salmon */
            color: white;
            box-shadow: 0 6px 0 #B71C1C; /* Shadow Merah Tua */
            border-color: #B71C1C;
        }
        #voice-input-btn.listening {
            background: #FDFD96; /* Kuning saat mendengarkan */
            color: #FF6961;
            box-shadow: 0 2px 0 #FFD700;
            animation: pulse-yellow 1.5s infinite;
        }
        @keyframes pulse-yellow {
          0%, 100% { opacity: 1; border-color: #FFD700; }
          50% { opacity: 0.7; border-color: #FF6961; }
        }
        
        /* Tombol Kesulitan */
        .bg-green-500 { background-color: #77DD77; border-color: #468246; box-shadow: 0 4px 0 #468246; }
        .bg-yellow-500 { background-color: #FDFD96; color: #444; border-color: #FFD700; box-shadow: 0 4px 0 #FFD700; }
        .bg-red-500 { background-color: #FF6961; border-color: #B71C1C; box-shadow: 0 4px 0 #B71C1C; }
        
        .bg-green-500:active { box-shadow: 0 1px 0 #468246; transform: translateY(3px) scale(0.98); }
        .bg-yellow-500:active { box-shadow: 0 1px 0 #FFD700; transform: translateY(3px) scale(0.98); }
        .bg-red-500:active { box-shadow: 0 1px 0 #B71C1C; transform: translateY(3px) scale(0.98); }

        /* Input Teks */
        #user-input {
            border: 4px solid #B39EB5;
            border-radius: 12px;
            font-size: 1.125rem;
            transition: all 0.2s;
        }
        #user-input:focus {
            border-color: #FF6961;
            box-shadow: 0 0 0 3px rgba(255, 105, 97, 0.5); /* Shadow fokus merah lembut */
        }
        
        /* Modal Lanjut */
        #resume-modal {
            background-color: #AEC6CF; /* Biru Pastel */
            border: 3px solid #B39EB5;
        }

        /* --- Animasi Kembang Api (Ditingkatkan) --- */
        .firework {
            /* Warna Partikel Acak - Didefinisikan di JS */
            animation: burst 1.5s ease-out forwards;
        }
        .overlay {
            animation: fadeIn 0.3s ease-in forwards;
        }
        .text-pop {
            font-family: 'Fredoka', cursive;
            animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards; /* Lebih bouncy */
            animation-delay: 0.1s;
        }
        /* Menggunakan warna cerah untuk teks pujian */
        #compliment-text {
            color: #FDFD96; 
            text-shadow: 0 0 10px #FF6961, 0 0 20px #FFC0CB;
        }
        #child-name-text {
            color: #77DD77;
            text-shadow: 0 0 10px #AEC6CF, 0 0 20px #B39EB5;
        }

        /* Responsivitas untuk layar kecil */
        @media (max-width: 640px) {
            .container-game {
                min-height: 95vh;
                border-radius: 2rem;
            }
            .gm-avatar {
                width: 70px;
                height: 70px;
                font-size: 2.5rem;
            }
            .speech-bubble {
                padding: 1rem;
                font-size: 1.1rem;
            }
            header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="p-4">
    <!-- Overlay Perayaan -->
    <div id="celebration-overlay" class="hidden fixed inset-0 z-50 bg-black bg-opacity-80 flex flex-col items-center justify-center overlay">
        
        <!-- Kontainer Kembang Api -->
        <div id="fireworks-container" class="absolute inset-0 overflow-hidden">
            <!-- Partikel kembang api akan dimasukkan di sini oleh JavaScript -->
        </div>

        <!-- Pesan Pujian Dinamis -->
        <div id="compliment-text" class="text-pop text-5xl md:text-7xl font-extrabold tracking-tight transform scale-0 mb-4" >
            <!-- Pujian akan dimasukkan di sini -->
        </div>
        
        <!-- Nama Anak Dinamis -->
        <div id="child-name-text" class="text-6xl md:text-8xl font-black italic mb-6">
            <!-- Nama Anak akan dimasukkan di sini -->
        </div>

        <!-- Pesan Statis -->
        <p class="text-white mt-4 text-xl md:text-3xl font-light font-inter">Luar biasa! Animasi akan hilang sebentar lagi...</p>
    </div>
    
    <div class="container-game mx-auto flex flex-col">

        <!-- Header dan Avatar Game Master -->
        <header class="w-full text-center">
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Petualangan Cerdas Si Kiki</h1>
            <p class="text-sm text-gray-600 font-inter">Oleh Game Master (GM) 🤖</p>
        </header>
        
        <!-- Skor Saat Ini -->
        <div class="w-full text-right">
            <p id="score-display" class="text-3xl font-bold">Skor: 0</p>
        </div>

        <main class="flex-grow flex flex-col justify-center p-4 sm:p-6">
            <!-- Area Dialog Game Master -->
            <div id="dialog-area" class="w-full bg-white p-4 sm:p-6 rounded-xl shadow-lg flex flex-col sm:flex-row items-start space-x-0 sm:space-x-4 mb-8 relative">
                <div class="gm-avatar flex-shrink-0 mb-4 sm:mb-0 mx-auto sm:mx-0">
                    <span id="gm-icon">🤖</span>
                </div>
                <!-- Speech Bubble akan memiliki animasi 'pulse-border' dari CSS -->
                <div class="speech-bubble w-full relative">
                    <p id="gm-text" class="text-xl text-gray-700 font-medium">Selamat datang, Adik Cerdas! Aku adalah Game Master. Tekan tombol Mulai untuk memulai petualangan kita!</p>
                    <div id="loading-indicator" class="hidden mt-2 text-sm text-kiki-blue font-semibold font-inter">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-kiki-blue inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        GM Kiki sedang berbicara...
                    </div>
                </div>
            </div>
        </main>

        <!-- Area Interaksi Anak (Tombol & Input) -->
        <div id="interaction-area" class="w-full flex flex-col items-center p-4 bg-kiki-blue/40 rounded-t-3xl shadow-xl border-t-8 border-kiki-purple">
            <!-- Panel Kontrol untuk Mulai dan Input Teks/Suara -->
            <div id="control-panel" class="w-full max-w-sm">
                
                <!-- Tombol Mulai Awal -->
                <button id="start-btn" onclick="showDifficultySelection()" class="btn-choice btn-primary p-4 text-xl w-full">Mulai Petualangan!</button>
                
                <!-- Pilihan Kesulitan (Awalnya tersembunyi) -->
                <div id="difficulty-select-area" class="hidden text-center">
                    <p class="text-xl font-bold mb-3 text-gray-800">Pilih Tingkat Kesulitanmu:</p>
                    <div class="flex justify-between space-x-2">
                        <button onclick="selectDifficulty('easy')" class="btn-choice p-3 text-lg bg-green-500 hover:bg-green-600 text-white w-1/3">Mudah</button>
                        <button onclick="selectDifficulty('medium')" class="btn-choice p-3 text-lg bg-yellow-500 hover:bg-yellow-600 text-gray-800 w-1/3">Sedang</button>
                        <button onclick="selectDifficulty('hard')" class="btn-choice p-3 text-lg bg-red-500 hover:bg-red-600 text-white w-1/3">Sulit</button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2 font-inter">*Sulit = Tantangan dalam bentuk cerita kasus!</p>
                </div>

                <!-- Input Teks dan Suara (Awalnya tersembunyi) -->
                <div id="text-input-area" class="hidden">
                    <input type="text" id="user-input" placeholder="Tulis jawabanmu di sini..." class="w-full p-3 border-4 border-indigo-400 rounded-lg focus:ring-4 focus:ring-indigo-300 focus:border-indigo-500 text-lg">
                    <div class="flex space-x-2 mt-2">
                        <button id="voice-input-btn" onclick="startVoiceInput()" class="btn-choice w-1/3 p-3 text-lg">
                            🎤 Bicara
                        </button>
                        <button onclick="submitAnswer()" class="btn-choice btn-primary w-2/3 p-3 text-lg">Kirim Jawaban</button>
                    </div>
                </div>

                <!-- Modal Lanjut Game (Awalnya tersembunyi) -->
                <div id="resume-modal" class="hidden text-center p-4 rounded-xl">
                    <p class="font-semibold text-gray-800 mb-3">GM Kiki menemukan petualanganmu yang belum selesai!</p>
                    <div class="flex space-x-2 justify-center">
                        <button onclick="loadAndResumeGame()" class="btn-choice p-3 text-sm bg-green-600 hover:bg-green-700 text-white">
                            Lanjutkan
                        </button>
                        <button onclick="startNewGame()" class="btn-choice p-3 text-sm bg-red-600 hover:bg-red-700 text-white">
                            Mulai Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan TTS -->
        <p class="text-xs text-gray-500 mt-4 text-center pb-4 font-inter">
            *Suara GM disimulasikan melalui fitur TTS bawaan browser (Web Speech API).
            Game ini didukung oleh kecerdasan buatan Gemini.
        </p>
    </div>

    <script>
        // --- Konfigurasi API & Global State ---
        const apiKey = "<?php echo $apiKey; ?>";
        const LLM_API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;
        const LOCAL_STORAGE_KEY = 'kikiGameSave';

        const MAX_RETRIES = 3;
        const INITIAL_DELAY_MS = 1000; 
        
        const gmTextElement = document.getElementById('gm-text');
        const scoreDisplay = document.getElementById('score-display');
        const loadingIndicator = document.getElementById('loading-indicator');
        const controlPanel = document.getElementById('control-panel'); 
        const textInputArea = document.getElementById('text-input-area');
        const userInput = document.getElementById('user-input');
        const voiceInputBtn = document.getElementById('voice-input-btn');
        const startButton = document.getElementById('start-btn');
        const difficultyArea = document.getElementById('difficulty-select-area');
        const resumeModal = document.getElementById('resume-modal');
        const gmAvatar = document.querySelector('.gm-avatar'); // Dapatkan elemen avatar
        const celebrationOverlay = document.getElementById('celebration-overlay'); // Overlay Kembang Api
        const fireworksContainer = document.getElementById('fireworks-container'); // Kontainer Kembang Api

        // Elemen Teks Pujian Dinamis
        const complimentText = document.getElementById('compliment-text');
        const childNameText = document.getElementById('child-name-text');
        
        let gameState = {
            childName: 'Adik Cerdas',
            score: 0,
            current_topic: 'start',
            current_level: 0,
            selected_difficulty: null,
            history: [] 
        };

        // Daftar Pujian Acak
        const compliments = [
            "Kamu Hebat Sekali!",
            "Jenius!",
            "Hebat!",
            "Luar Biasa!",
            "Pintar!",
            "Kerja Bagus!",
            "Super Sekali!",
            "Aduhai!"
        ];
        function getRandomCompliment() {
            return compliments[Math.floor(Math.random() * compliments.length)];
        }


        // --- Logika Kembang Api (Ditingkatkan) ---
        // Warna cerah baru sesuai tema
        const colors = [
            '#FFC0CB', '#77DD77', '#AEC6CF', '#FDFD96', '#FF6961', 
            '#B39EB5', '#FFD700', '#FFFFFF', '#FFB6C1', '#ADD8E6'
        ];
        
        function getRandomPosition() {
            return {
                x: Math.random() * 100 + 'vw', 
                y: Math.random() * 80 + 10 + 'vh' 
            };
        }

        function createFirework(centerX, centerY) {
            const particleCount = 20;
            
            const group = document.createElement('div');
            group.className = 'firework-group';
            fireworksContainer.appendChild(group);

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('span');
                particle.className = 'firework';
                
                const color = colors[Math.floor(Math.random() * colors.length)];
                
                const angle = (360 / particleCount) * i;
                const distance = 150 + Math.random() * 100; 
                
                const dx = Math.cos(angle * (Math.PI / 180)) * distance;
                const dy = Math.sin(angle * (Math.PI / 180)) * distance;

                particle.style.cssText = `
                    background-color: ${color};
                    left: ${centerX};
                    top: ${centerY};
                    width: ${Math.random() * 3 + 2}px; 
                    height: ${Math.random() * 3 + 2}px;
                    --x: ${dx}px;
                    --y: ${dy}px;
                    animation-delay: ${Math.random() * 0.5}s; 
                `;
                
                group.appendChild(particle);
                
                // Pastikan partikel dihapus setelah selesai animasi
                setTimeout(() => particle.remove(), 2000);
            }
            // Hapus grup setelah semua partikelnya hilang
            setTimeout(() => group.remove(), 2100);
        }

        function showCelebration() {
            // 1. Tampilkan overlay (layar redup)
            celebrationOverlay.classList.remove('hidden');
            celebrationOverlay.classList.remove('opacity-0', 'transition-opacity', 'duration-500'); // Hapus fade-out class jika ada
            
            // 2. Bersihkan container dari sisa partikel lama
            fireworksContainer.innerHTML = ''; 

            // 3. Set Teks Pujian dan Nama
            complimentText.textContent = getRandomCompliment();
            childNameText.textContent = `${gameState.childName}!`;

            // 4. Buat 5 kembang api di posisi acak dengan delay
            for (let i = 0; i < 5; i++) {
                const pos = getRandomPosition();
                setTimeout(() => createFirework(pos.x, pos.y), i * 300);
            }

            // 5. Atur timer 3 detik untuk menyembunyikan
            const duration = 3000; // 3000ms = 3 detik
            setTimeout(() => {
                hideCelebration();
            }, duration);
        }

        function hideCelebration() {
            // Tambahkan kelas fade-out sebelum disembunyikan
            celebrationOverlay.classList.add('opacity-0', 'transition-opacity', 'duration-500');
            
            // Sembunyikan setelah transisi selesai
            setTimeout(() => {
                celebrationOverlay.classList.add('hidden');
                celebrationOverlay.classList.remove('opacity-0', 'transition-opacity', 'duration-500');
            }, 500); // Durasi transisi opacity
        }


        // --- Logika Penyimpanan Cepat (localStorage) ---
        function saveGameState() {
            try {
                if (gameState.current_level > 0 || gameState.current_topic === 'name_input') {
                    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(gameState));
                }
            } catch (error) {
                console.error("Gagal menyimpan state ke localStorage:", error);
            }
        }

        function loadGameState() {
            try {
                const savedState = localStorage.getItem(LOCAL_STORAGE_KEY);
                if (savedState) {
                    const loadedState = JSON.parse(savedState);
                    if (loadedState && loadedState.history && (loadedState.current_level > 0 || loadedState.current_topic === 'name_input')) {
                        return loadedState;
                    }
                }
            } catch (error) {
                console.error("Gagal memuat state dari localStorage:", error);
            }
            return null;
        }

        function clearGameState() {
            localStorage.removeItem(LOCAL_STORAGE_KEY);
        }

        function updateUIForCurrentState() {
            scoreDisplay.textContent = `Skor: ${gameState.score}`;
            
            startButton.classList.add('hidden');
            difficultyArea.classList.add('hidden');
            textInputArea.classList.add('hidden');
            resumeModal.classList.add('hidden');
            
            if (gameState.current_topic === 'start' || gameState.current_topic === 'name_input') {
                if(gameState.current_topic === 'start' || gameState.current_topic === 'ending') {
                     startButton.textContent = gameState.current_topic === 'ending' ? 'Mulai Petualangan Baru!' : 'Mulai Petualangan!';
                     startButton.classList.remove('hidden');
                } else {
                    textInputArea.classList.remove('hidden');
                }
            } else if (gameState.current_topic === 'difficulty_select') {
                difficultyArea.classList.remove('hidden');
            } else if (gameState.current_topic === 'ending') {
                startButton.textContent = 'Mulai Petualangan Baru!';
                startButton.classList.remove('hidden');
            } else {
                textInputArea.classList.remove('hidden');
            }
        }
        
        // --- Utilitas TTS (Menggunakan Web Speech API) ---
        function speakText(text) {
    if (!('speechSynthesis' in window)) {
        console.warn('Browser tidak mendukung Speech Synthesis API.');
        loadingIndicator.classList.add('hidden');
        return;
    }

    // Hentikan suara sebelumnya
    speechSynthesis.cancel();

    // Pisahkan teks berdasarkan tanda baca untuk menciptakan jeda alami
    const sentences = text
        .replace(/([.?!])\s*/g, "$1|") // ubah titik, tanya, seru jadi pemisah
        .split("|")
        .map(s => s.trim())
        .filter(s => s.length > 0);

    let voices = [];
    const utterNext = (index = 0) => {
        if (index >= sentences.length) {
            gmAvatar.classList.remove('speaking');
            loadingIndicator.classList.add('hidden');
            return;
        }

        const utterance = new SpeechSynthesisUtterance(sentences[index]);
        utterance.lang = 'id-ID';
        utterance.rate = 0.95 + Math.random() * 0.1; // kecepatan acak sedikit
        utterance.pitch = 0.9 + Math.random() * 0.2; // nada acak sedikit
        utterance.volume = 1;

        const indonesianVoice = voices.find(v => v.lang === 'id-ID' || v.lang.startsWith('id'));
        if (indonesianVoice) utterance.voice = indonesianVoice;

        // Animasi saat bicara
        utterance.onstart = () => {
            gmAvatar.classList.add('speaking');
            loadingIndicator.classList.remove('hidden');
        };

        utterance.onend = () => {
            gmAvatar.classList.remove('speaking');

            // jeda alami antar kalimat
            setTimeout(() => {
                utterNext(index + 1);
            }, 150 + Math.random() * 250);
        };

        speechSynthesis.speak(utterance);
    };

    const startSpeaking = () => {
        voices = speechSynthesis.getVoices();
        if (!voices || voices.length === 0) {
            console.warn('Daftar suara belum siap.');
            speechSynthesis.onvoiceschanged = startSpeaking;
            return;
        }

        utterNext(0);
    };

    startSpeaking();
}


        // --- Konfigurasi Sistem LLM (Tidak Berubah) ---
        const systemInstruction = {
            parts: [{ 
                text: `
                    Anda adalah Game Master Kiki, karakter yang ramah, ceria, dan sangat mendidik dan kadang lucu memotivasi. 
                    Anda berbicara dalam Bahasa Indonesia.
                    Target audiens Anda adalah anak-anak usia 3-6 tahun.
                    Tujuan Anda adalah memandu pemain melalui tantangan yang memiliki 4 level kesulitan yang terus meningkat per tema.
                    
                    STATUS GAME:
                    - current_topic: ('start', 'name_input', 'difficulty_select', 'color_challenge', 'number_challenge', 'shape_challenge', 'social_emotion_challenge', atau 'ending')
                    - current_level: (1 hingga 16. Level 1-4=Warna, 5-8=Angka, 9-12=Bentuk, 13-16=Sosial/Emosional)
                    - selected_difficulty: ('easy' untuk Mudah, 'medium' untuk Sedang, 'hard' untuk Sulit).
                    
                    Gaya Narasi Berdasarkan 'selected_difficulty':
                    1. 'easy' (Mudah): Berikan pertanyaan yang SANGAT LANGSUNG dan JELAS. Contoh: "Apa warna matahari?"
                    2. 'medium' (Sedang): Berikan pertanyaan yang sedikit lebih detail dan memerlukan pengamatan. Contoh: "Kamu melihat apel merah dan pisang kuning. Sebutkan warna buah lain di sekitarmu!"
                    3. 'hard' (Sulit): Berikan NARASI KASUS SEDERHANA yang memerlukan sedikit penalaran kontekstual untuk menjawab. Gunakan skenario teman atau aktivitas sehari-hari. Contoh: "Kiki sedang menggambar matahari ☀️ Dia ingin warnanya sangat cerah seperti di langit. Warna apa yang harus Kiki pakai untuk matahari?"
                    
                    Aturan Progresi Level:
                    (Total 4 level per tema)
                    - Pindah ke 'number_challenge' HANYA JIKA current_level mencapai 5.
                    - Pindah ke 'shape_challenge' HANYA JIKA current_level mencapai 9.
                    - Pindah ke 'social_emotion_challenge' HANYA JIKA current_level mencapai 13.
                    - Pindah ke 'ending' HANYA JIKA current_level mencapai 17.
                    
                    Anda HARUS SELALU merespons dalam format JSON sesuai skema yang disediakan.
                    
                    Jika pengguna memberikan jawaban yang benar, tingkatkan 'score_delta' menjadi 1 DAN 'current_level' harus bertambah 1. Jika salah, set 'score_delta' ke 0 dan ulangi level yang sama, beri motivasi.
                    
                    Gunakan emoji yang menarik dalam 'gm_text'.
                    Anda HARUS SELALU menyertakan bidang 'tts_text' yang berisi versi teks yang SANGAT BERSIH (tanpa emoji, tanpa placeholder seperti [nama]) untuk pengucapan yang cepat dan jernih oleh TTS. Ganti [nama] dengan '[nama] Cerdas, [nama] Keren dan julukan keren lainnya' dalam tts_text, dan [nama] dengan nilai gameState.childName di gm_text.
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
                "current_level": { "type": "NUMBER", "description": "Level Tantangan saat ini (1-16)." }
            },
            required: ["gm_text", "tts_text", "challenge_type", "current_topic", "current_level"]
        };

        // --- Logika Interaksi LLM ---
        async function processGeminiTurn(userAction) {
            loadingIndicator.classList.remove('hidden');
            controlPanel.style.pointerEvents = 'none';

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
            }

            controlPanel.style.pointerEvents = 'auto';
            loadingIndicator.classList.add('hidden');
        }

        function handleGMResponse(response) {
            
            // 1. Update Game State
            const oldScore = gameState.score;
            const isCorrect = response.is_correct === true && response.score_delta > 0;
            
            if (response.score_delta) {
                gameState.score += response.score_delta;
            }
            if (response.current_topic) {
                gameState.current_topic = response.current_topic;
            }
            if (response.current_level) {
                gameState.current_level = response.current_level;
            }
            
            // 2. Simpan state setelah setiap giliran
            saveGameState(); 

            // 3. Update Dialog and TTS
            const gmMessage = response.gm_text.replace(/\[nama\]/g, gameState.childName); 
            const ttsMessage = response.tts_text.replace(/\[nama\]/g, gameState.childName); 
            
            // TTS harus dipanggil segera, bahkan saat animasi berjalan 
            speakText(ttsMessage);
            
            // 4. Update UI
            updateUIForCurrentState();
            userInput.value = '';

            // Animasi skor jika berubah
            if (gameState.score !== oldScore) {
                scoreDisplay.classList.add('score-animated');
                // Hapus kelas animasi setelah selesai
                scoreDisplay.addEventListener('animationend', () => {
                    scoreDisplay.classList.remove('score-animated');
                }, { once: true });
            }
            
            // 5. TAMPILKAN KEMBANG API JIKA BENAR
            if (isCorrect) {
                // Tampilkan animasi kembang api full screen selama 3 detik
                showCelebration(); 
            }
            
            // Setelah animasi selesai (atau segera jika tidak ada animasi), perbarui teks GM.
            // Kita gunakan setTimeout 3.5 detik untuk memastikan teks GM muncul setelah animasi 3 detik hilang, 
            // jika ada animasi. Jika tidak, teks akan diperbarui segera.
            const delay = isCorrect ? 3500 : 0; 
            setTimeout(() => {
                gmTextElement.textContent = gmMessage;
                // Animasi masuk speech bubble baru
                const speechBubbleElement = document.querySelector('.speech-bubble');
                // Reset animasi pulse dan fade-in-up untuk memulai ulang
                speechBubbleElement.style.animation = 'none'; 
                void speechBubbleElement.offsetWidth; // Trigger reflow
                speechBubbleElement.style.animation = 'pulse-border 5s infinite alternate ease-in-out, fade-in-up 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards'; 
            }, delay);


            // 6. Tambahkan respons GM ke riwayat
            gameState.history.push({ role: "model", parts: [{ text: JSON.stringify(response) }] });
        }
        
        // --- Logika Alur Game Baru ---
        function showDifficultySelection() {
            gameState.score = 0;
            gameState.history = [];
            gameState.current_topic = 'difficulty_select'; 
            gameState.current_level = 0;
            gameState.selected_difficulty = null;
            // Simpan state setelah nama diisi sebelum memulai level
            saveGameState(); 
            
            updateUIForCurrentState();
            
            const difficultyPrompt = `Aku ingin bermain. Sekarang aku akan memilih tingkat kesulitan. Nama anak adalah ${gameState.childName}. Minta aku memilih kesulitan: 'easy', 'medium', atau 'hard'.`;
            
            gmTextElement.textContent = `Baik, ${gameState.childName}! Pilih dulu tingkat kesulitan petualanganmu di bawah ini:`;
            speakText(gmTextElement.textContent);
            
            gameState.history.push({ role: "user", parts: [{ text: difficultyPrompt }] });
        }

        function selectDifficulty(level) {
            gameState.selected_difficulty = level;
            const difficultyMap = { 'easy': 'Mudah', 'medium': 'Sedang', 'hard': 'Sulit' };
            
            loadingIndicator.classList.remove('hidden');
            
            const initialPrompt = `Aku memilih kesulitan ${difficultyMap[level]} (${level}). Tolong sapa aku dan mulai tantangan 'color_challenge' Level 1.`;
            
            difficultyArea.classList.add('hidden');
            
            processGeminiTurn(initialPrompt);
        }

        // --- Logika Input Suara (Voice Input) ---
        let recognition = null;

        function startVoiceInput() {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                gmTextElement.textContent = "Maaf, browser Anda tidak mendukung input suara. Silakan gunakan input teks.";
                speakText(gmTextElement.textContent);
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
                
                if (submitted) {
                    submitAnswer();
                }
            };

            recognition.start();
        }
        
        // --- Fungsi Kontrol Game Utama ---
        function handleError(message) {
            gmTextElement.textContent = message;
            speakText(message);
            clearGameState(); 
            updateUIForCurrentState();
            startButton.textContent = 'Mulai Ulang Game';
            startButton.classList.remove('hidden');
        }

        function submitAnswer() {
            const input = userInput.value.trim();
            if (input.length === 0) {
                speakText("Tolong tulis jawabanmu atau gunakan tombol Bicara, Adik Cerdas!");
                return;
            }

            // Logika untuk menyimpan nama
            if (gameState.current_topic === 'start' || gameState.current_topic === 'name_input') {
                gameState.childName = input.substring(0, 15);
                // Langsung lompat ke pemilihan kesulitan setelah nama
                showDifficultySelection();
                return;
            }
            
            // Logika untuk menjawab tantangan
            processGeminiTurn(`Jawabanku adalah: ${input}`);
        }
        
        function loadAndResumeGame() {
            const savedData = loadGameState();
            if (savedData) {
                gameState = savedData;
                
                // Ambil respons GM terakhir dari history untuk ditampilkan
                const lastGMResponseText = gameState.history.slice(-1)[0]?.parts?.[0]?.text;
                let lastGMResponse;
                try {
                    // Cek apakah data terakhir adalah respons JSON dari model
                    lastGMResponse = JSON.parse(lastGMResponseText);
                } catch (e) {
                    // Jika gagal parse (mungkin user input terakhir), cari respons model sebelumnya
                    const modelResponse = gameState.history.slice().reverse().find(entry => entry.role === 'model' && entry.parts[0].text);
                    try {
                        lastGMResponse = JSON.parse(modelResponse.parts[0].text);
                    } catch (e2) {
                        console.error("Gagal mem-parse respons GM terakhir, memulai ulang.");
                        startNewGame();
                        return;
                    }
                }
                
                const gmMessage = lastGMResponse.gm_text.replace(/\[nama\]/g, gameState.childName); 
                const ttsMessage = lastGMResponse.tts_text.replace(/\[nama\]/g, gameState.childName); 
                
                gmTextElement.textContent = gmMessage;
                speakText(`Selamat datang kembali, ${gameState.childName}! Mari kita lanjutkan. ${ttsMessage}`);
                
                updateUIForCurrentState();
                userInput.focus();
            } else {
                startNewGame();
            }
        }

        function startNewGame() {
            clearGameState();
            gameState = {
                childName: 'Adik Cerdas',
                score: 0,
                current_topic: 'name_input', // Set topik untuk meminta nama
                current_level: 0,
                selected_difficulty: null,
                history: []
            };
            
            gmTextElement.textContent = "Halo! Siapa namamu, Adik Cerdas? 😊";
            speakText(gmTextElement.textContent);
            
            startButton.classList.add('hidden');
            resumeModal.classList.add('hidden');
            textInputArea.classList.remove('hidden');
            userInput.placeholder = "Tulis namamu di sini...";
            userInput.focus();
            updateUIForCurrentState();
        }

        window.onload = function() {
            if ('speechSynthesis' in window && window.speechSynthesis.onvoiceschanged !== undefined) {
                window.speechSynthesis.onvoiceschanged = () => {
                    window.speechSynthesis.getVoices();
                };
            }
            
            const savedState = loadGameState();
            
            if (savedState) {
                // Jika ada data tersimpan, tampilkan modal resume
                // Pastikan tombol resume menggunakan kelas btn-choice baru
                resumeModal.innerHTML = `
                    <p class="font-semibold text-gray-800 mb-3">GM Kiki menemukan petualanganmu yang belum selesai!</p>
                    <div class="flex space-x-2 justify-center">
                        <button onclick="loadAndResumeGame()" class="btn-choice p-3 text-sm bg-kiki-green hover:bg-green-600 text-white">
                            Lanjutkan (${savedState.childName}, Skor: ${savedState.score})
                        </button>
                        <button onclick="startNewGame()" class="btn-choice p-3 text-sm bg-kiki-red hover:bg-red-600 text-white">
                            Mulai Baru
                        </button>
                    </div>
                `;
                startButton.classList.add('hidden');
                resumeModal.classList.remove('hidden');
            } else {
                // Jika tidak ada data tersimpan, langsung mulai proses menanyakan nama
                startNewGame();
            }
        };
    </script>
</body>
</html>
