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
    <title>SMK SkillUp Challenge: Simulasi Industri</title>
    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* --- Palet Warna & Font Profesional (Elegant Light Mode / macOS Style) --- */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;500;700;900&display=swap');
        
        /* Konfigurasi Tailwind */
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'light-primary': '#F0F4F8', /* Body BG (Very Light Gray/Blue) */
                        'light-card': 'rgba(255, 255, 255, 0.95)', /* Card/Container (Slightly translucent white) */
                        'text-dark': '#1F2937', /* Primary Text */
                        'accent-blue': '#007AFF', /* macOS Standard Blue (Primary Action) */
                        'accent-success': '#34C759', /* Green Highlight */
                        'accent-warning': '#FF9500', /* Orange/Gold (Warning) */
                    },
                }
            }
        }

        body {
            font-family: 'Inter', sans-serif; 
            background-color: #F0F4F8; /* Latar Belakang Terang */
            min-height: 100vh;
            padding: 0; 
            margin: 0;
            display: flex;
            align-items: flex-start; 
            justify-content: center;
            color: #1F2937; /* Default warna teks gelap */
        }

        .app-dashboard {
            /* Mobile Default: Full screen, no borders/shadows */
            width: 100%;
            min-height: 100vh; 
            max-width: 100%;
            
            background-color: rgb(255 255 255 / 49%);
            border-radius: 0; 
            box-shadow: none; 

            /* Desktop/Tablet Override: Centered, constrained, card-like (Glassmorphism) */
            @media (min-width: 768px) {
                max-width: 1024px; 
                background-color: rgb(255 255 255 / 39%);
                height: 90vh; 
                min-height: 0;
                border-radius: 1.5rem; 
                margin: 2rem auto; 
                /* Efek Glassmorphism: Transparansi & Blur */
                background-color: rgba(255, 255, 255, 0.85); 
                backdrop-filter: blur(15px); 
                -webkit-backdrop-filter: blur(15px);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.05); 
            }

            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* --- Header dan Status Bar (Ringan dan Bersih) --- */
        header {
            padding: 1rem 1.5rem; 
            background-color: rgba(255, 255, 255, 0.9); /* Sedikit blur di atas */
            backdrop-filter: blur(10px); 
            border-bottom: 1px solid #E5E7EB; /* Garis pemisah terang */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        #status-bar {
            padding: 0.5rem 1rem;
            background-color: #E5E7EB; /* Latar belakang abu-abu muda */
            border-radius: 9999px;
            color: #1F2937; /* Warna teks gelap */
            font-weight: 600;
            transition: transform 0.2s; 
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            font-size: 0.875rem; 
        }
        .score-animated {
            animation: score-pulse 0.5s ease-out 1;
        }
        @keyframes score-pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 #34C759; }
            50% { transform: scale(1.1); color: #007AFF; box-shadow: 0 0 10px #34C759; } 
            100% { transform: scale(1); box-shadow: 0 0 0 #34C759; }
        }

        /* --- Chat Log Area (Scrollable) --- */
        #chat-log {
            flex-grow: 1;
            padding: 1.5rem; 
            @media (min-width: 768px) {
                padding: 2rem;
            }
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem; 
        }
        #chat-log::-webkit-scrollbar { width: 6px; }
        #chat-log::-webkit-scrollbar-thumb { background-color: #D1D5DB; border-radius: 3px; }
        #chat-log::-webkit-scrollbar-track { background-color: #F0F4F8; }

        /* Dialog Bubble (Light Mode Style) */
        .message-bubble {
    display: flex;
    align-items: flex-start;
    gap: 1rem; /* jarak antara avatar dan isi pesan */
    margin-bottom: 1rem; /* jarak antar pesan di bawah */
    opacity: 0;
    transform: translateY(10px);
    animation: fade-in-up 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; 
}


        .mentor-avatar {
            width: 48px;
            height: 48px;
            min-width: 48px;
            background-color: #007AFF; /* Biru macOS */
            border-radius: 0.75rem;
            border: 2px solid #FF9500;
            font-size: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .mentor-avatar.speaking {
            animation: pulse-light 0.8s ease-in-out infinite alternate;
        }

        .text-content {
            background-color: #E5E7EB; /* Latar belakang pesan abu-abu muda */
            border-left: 4px solid #007AFF; 
            padding: 1rem;
            border-radius: 0.75rem; /* Lebih membulat */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            width: fit-content;
            max-width: 90%; 
            line-height: 1.6; 
            color: #1F2937; /* Teks gelap */
        }
        .message-bubble.justify-end .text-content {
            max-width: 90%;
            background-color: #007AFF; /* Latar belakang biru untuk pesan user */
            border-left: 5px solid #34C759; /* Garis Kontras */
            border-right: none;
            color: white; /* Teks putih untuk kontras tinggi */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .message-bubble.justify-end .text-content p {
            font-style: normal;
        }


        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Input & Kontrol Area --- */
        #interaction-area {
            padding: 1rem 1.5rem; 
            background-color: rgba(255, 255, 255, 0.9); /* Sedikit blur di bawah */
            backdrop-filter: blur(10px); 
            border-top: 1px solid #E5E7EB;
            flex-shrink: 0; 
        }
        
        /* Tombol & Input Styling (High Contrast, Clean Look) */
        .btn-sleek {
            transition: all 0.2s; 
            border-radius: 0.75rem; /* Lebih membulat */
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            white-space: nowrap; 
        }
        .btn-sleek:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .btn-sleek:active {
            transform: translateY(1px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #007AFF;
            color: white;
        }
        .btn-primary:hover {
            background-color: #006ED3;
        }

        .btn-voice {
            background-color: #FF9500;
            color: white;
        }
        .btn-voice.listening {
            background: #34C759;
            color: white;
            animation: pulse-teal 1.5s infinite;
        }
        @keyframes pulse-teal {
          0%, 100% { opacity: 1; box-shadow: 0 0 10px #34C759; }
          50% { opacity: 0.8; box-shadow: 0 0 20px #34C759; }
        }

        #user-input, #major-select, #custom-major-input, #feedback-text {
            background-color: #FFFFFF; /* Input putih bersih */
            color: #1F2937;
            border: 1px solid #D1D5DB; /* Border abu-abu tipis */
            width: 100%; 
            border-radius: 0.75rem; /* Lebih membulat */
        }
        #user-input:focus, #major-select:focus, #custom-major-input:focus, #feedback-text:focus {
            border-color: #007AFF;
            box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.3); 
        }

        /* Input Teks Area: Flexibel untuk mobile */
        #text-input-area .flex {
            display: flex;
            gap: 0.5rem; 
        }
        
        /* Memastikan tombol di input area menyesuaikan ukurannya */
        #text-input-area .flex button {
            padding: 0.75rem 1rem; 
            font-size: 0.875rem; 
        }
        @media (min-width: 640px) {
             #text-input-area .flex button {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }
        }

        /* Styling for the new ending review area */
        #ending-review-area {
            background-color: #FFFFFF;
            border: 2px solid #007AFF;
            border-radius: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        #final-score {
            font-size: 2.5rem;
            line-height: 1;
            font-weight: 900;
            color: #FF9500; /* Skor dengan warna oranye/gold */
            text-shadow: 0 0 5px rgba(255, 149, 0, 0.5);
        }
        #ending-review-area .bg-dark-secondary {
            background-color: #F0F4F8; /* Ganti dark-secondary menjadi light-secondary */
            border-color: #D1D5DB;
        }
          .parallax-layer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    pointer-events: none;
    z-index: -10;
    transition: transform 0.1s ease-out;
}

.layer-1 {
    background: radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.4), transparent 60%);
}
.layer-2 {
    background: radial-gradient(circle at 85% 85%, rgba(236, 72, 153, 0.35), transparent 65%);
}
.layer-3 {
    background: radial-gradient(circle at 50% 10%, rgba(255, 255, 255, 0.15), transparent 50%);
}
.layer-4 {
    background: radial-gradient(circle at 15% 80%, rgba(0, 255, 200, 0.2), transparent 60%);
}
.layer-5 {
    background: radial-gradient(circle at 70% 30%, rgba(255, 255, 0, 0.1), transparent 70%);
}
.layer-6 {
    background: radial-gradient(circle at 50% 50%, rgba(0, 128, 255, 0.1), transparent 75%);
}

.gyro-layer-1 {
    background: radial-gradient(circle at 20% 30%, rgba(255, 0, 128, 0.25), transparent 70%);
}
.gyro-layer-2 {
    background: radial-gradient(circle at 70% 60%, rgba(0, 255, 200, 0.25), transparent 75%);
}
.gyro-layer-3 {
    background: radial-gradient(circle at 30% 80%, rgba(255, 255, 255, 0.15), transparent 65%);
}
.gyro-layer-4 {
    background: radial-gradient(circle at 80% 25%, rgba(255, 200, 0, 0.2), transparent 70%);
}
.gyro-layer-5 {
    background: radial-gradient(circle at 45% 45%, rgba(0, 128, 255, 0.15), transparent 70%);
}
.gyro-layer-6 {
    background: radial-gradient(circle at 60% 70%, rgba(236, 72, 153, 0.2), transparent 70%);
}

.text-pop {
    text-shadow: 0 0 5px rgba(0,0,0,0.4);
}

.initial-hidden { 
    opacity: 0; 
}
.show {
    opacity: 1;
}
.text-pop {
    text-shadow: 0 0 5px rgba(0,0,0,0.4);
}

.initial-hidden { opacity: 0; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    animation-fill-mode: both;
}
@keyframes wobble {
  0% { transform: translateX(-50%) translateY(-50%) rotate(0deg) scale(0.95); }
  15% { transform: translateX(-50%) translateY(-50%) rotate(-5deg) scale(1); }
  30% { transform: translateX(-50%) translateY(-50%) rotate(3deg) scale(1); }
  45% { transform: translateX(-50%) translateY(-50%) rotate(-3deg) scale(1); }
  60% { transform: translateX(-50%) translateY(-50%) rotate(2deg) scale(1); }
  75% { transform: translateX(-50%) translateY(-50%) rotate(-1deg) scale(1); }
  100% { transform: translateX(-50%) translateY(-50%) rotate(0deg) scale(1); }
}

/* Kelas trigger wobble */
.wobble {
  animation: wobble 0.6s ease;
}
    </style>
</head>
<body class="p-0 md:p-4">
     <div id="layer1" class="parallax-layer layer-1"></div>
<div id="layer2" class="parallax-layer layer-2"></div>
<div id="layer3" class="parallax-layer layer-3"></div>
<div id="layer4" class="parallax-layer layer-4"></div>
<div id="layer5" class="parallax-layer layer-5"></div>
<div id="layer6" class="parallax-layer layer-6"></div>
<!-- Layer khusus mobile gyro -->
<div id="gyro1" class="parallax-layer gyro-layer-1 initial-hidden"></div>
<div id="gyro2" class="parallax-layer gyro-layer-2 initial-hidden"></div>
<div id="gyro3" class="parallax-layer gyro-layer-3 initial-hidden"></div>
<div id="gyro4" class="parallax-layer gyro-layer-4 initial-hidden"></div>
<div id="gyro5" class="parallax-layer gyro-layer-5 initial-hidden"></div>
<div id="gyro6" class="parallax-layer gyro-layer-6 initial-hidden"></div>
    <!-- Overlay Achievement Unlocked -->
    <div id="celebration-overlay" class="hidden fixed inset-0 z-50 bg-light-primary bg-opacity-95 flex flex-col items-center justify-center overlay text-text-dark">
        <div id="compliment-text" class="glowing-text text-5xl md:text-7xl font-black tracking-tight transform scale-0 mb-4 text-accent-success" >
            <!-- Pujian akan dimasukkan di sini -->
        </div>
        <div id="child-name-text" class="subheader-text text-4xl md:text-5xl font-extrabold italic mb-8 text-accent-blue">
            <!-- Nama Siswa akan dimasukkan di sini -->
        </div>
        <p class="text-gray-600 mt-4 text-lg md:text-xl font-light font-inter text-center px-4">Target Kompetensi Tercapai! Memuat modul berikutnya...</p>
    </div>
    
    <div class="app-dashboard mx-auto">

        <!-- Header dan Status Bar (Fixed Top) -->
        <header>
            <h1 class="text-xl sm:text-2xl font-extrabold text-text-dark whitespace-nowrap overflow-hidden text-ellipsis">SMK SkillUp Challenge</h1>
            <div id="status-bar">
                <p id="score-display" class="text-sm">Poin Kompetensi: 0</p>
            </div>
        </header>
        
        <!-- Area Log Dialog (Scrollable Main Content) -->
        <main id="chat-log">
            <!-- Pesan Pembuka (Statik) -->
            <div id="dialog-area">
                <div class="message-bubble">
                    <div class="mentor-avatar flex-shrink-0 flex items-center justify-center">
                        <span class="text-white">👨‍💻</span>
                    </div>
                    <div class="text-content">
                        <p id="gm-text" class="text-base font-medium leading-relaxed">Selamat datang di Simulasi Industri! Aku adalah AI Mentor Anda. Tekan tombol Mulai untuk memulai uji kompetensi Anda.</p>
                    </div>
                </div>
            </div>
            
            <!-- Indikator Loading Global -->
            <div id="loading-indicator" class="hidden text-sm text-accent-blue font-semibold font-inter mt-2">
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-accent-blue inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                AI Mentor sedang memproses data...
            </div>
            
            <!-- Area untuk Pesan Dinamis (akan ditambahkan via JS) -->
            <div id="dynamic-messages"></div>
        </main>

        <!-- Area Interaksi Siswa (Input, Fixed Bottom) -->
        <div id="interaction-area" class="w-full">
            <div class="w-full max-w-2xl mx-auto"> <!-- Max-width di sini untuk membatasi input di desktop -->
                
                <!-- Tombol Mulai Awal -->
                <button id="start-btn" onclick="startNewGame()" class="btn-sleek btn-primary p-3 sm:p-4 text-lg w-full">Mulai Uji Kompetensi</button>
                
                <!-- Input Teks dan Suara (Awalnya tersembunyi) -->
                <div id="text-input-area" class="hidden">
                    <div class="flex space-x-2">
                        <input type="text" id="user-input" placeholder="Masukkan Solusi atau Nama Anda..." class="w-full p-3 sm:p-4 border rounded-lg text-base flex-grow">
                        <button id="voice-input-btn" onclick="startVoiceInput()" class="btn-sleek btn-voice flex-shrink-0">
                            🎤 Bicara
                        </button>
                        <button onclick="submitAnswer()" class="btn-sleek btn-primary flex-shrink-0">Kirim</button>
                    </div>
                </div>

                <!-- Pilihan Jurusan (Baru, Awalnya tersembunyi) -->
                <div id="major-select-area" class="hidden text-center p-4 rounded-xl bg-light-card border border-gray-200 shadow-md">
                    <p class="text-base sm:text-lg font-bold mb-3 text-text-dark">Pilih Jurusan Anda:</p>
                    <select id="major-select" onchange="toggleCustomMajorInput()" class="w-full p-3 mb-3 border rounded-lg text-base text-text-dark bg-white">
                        <option value="RPL (Rekayasa Perangkat Lunak)">RPL (Rekayasa Perangkat Lunak)</option>
                        <option value="TKJ (Teknik Komputer dan Jaringan)">TKJ (Teknik Komputer dan Jaringan)</option>
                        <option value="Akuntansi dan Keuangan Lembaga">Akuntansi dan Keuangan Lembaga (AKL)</option>
                        <option value="Teknik Kendaraan Ringan Otomotif">Teknik Kendaraan Ringan Otomotif (TKR)</option>
                        <option value="Lainnya">Lainnya (Masukkan Jurusan)</option>
                    </select>
                    <input type="text" id="custom-major-input" placeholder="Masukkan nama jurusan Anda..." class="w-full p-3 border rounded-lg text-base hidden mb-3">
                    <button onclick="submitMajor()" class="btn-sleek btn-primary p-3 text-base w-full">Lanjut ke Pemilihan Kesulitan</button>
                </div>
                
                <!-- Pilihan Kesulitan (DIUPDATE LATAR BELAKANGNYA) -->
                <div id="difficulty-select-area" class="hidden text-center p-4 rounded-xl bg-light-primary border border-gray-300 shadow-md">
                    <p class="text-base sm:text-lg font-bold mb-3 text-text-dark">Pilih Tingkat Kompleksitas Simulasi:</p>
                    <div class="flex flex-col sm:flex-row justify-between space-y-3 sm:space-y-0 sm:space-x-3">
                        <button onclick="selectDifficulty('easy')" class="btn-sleek p-3 text-base bg-green-600 hover:bg-yellow-600 text-white w-full sm:w-1/3">Dasar</button>
                        <button onclick="selectDifficulty('medium')" class="btn-sleek p-3 text-base bg-blue-600 hover:bg-yellow-600 text-white w-full sm:w-1/3">Menengah</button>
                        <button onclick="selectDifficulty('hard')" class="btn-sleek p-3 text-base bg-red-500 hover:bg-yellow-600 text-white w-full sm:w-1/3">Kasus Industri</button>
                    </div>
                </div>

                <!-- Modal Lanjut Game (Awalnya tersembunyi) -->
                <div id="resume-modal" class="hidden text-center p-4 mx-auto max-w-sm bg-light-card border border-accent-blue rounded-xl shadow-lg">
                    <p class="font-semibold text-text-dark mb-3">AI Mentor mendeteksi simulasi yang belum selesai.</p>
                    <p id="resume-info" class="text-sm text-gray-600 mb-4"></p>
                    <div class="flex space-x-2 justify-center">
                        <button onclick="loadAndResumeGame()" class="btn-sleek p-3 text-base bg-green-600 hover:bg-red-600 text-white w-full sm:w-1/3">
                            Lanjutkan
                        </button>
                        <button onclick="startNewGame()" class="btn-sleek p-3 text-sm bg-red-500 hover:bg-red-600 text-white">
                            Sesi Baru
                        </button>
                    </div>
                </div>
                
                <!-- AREA REVIEW DAN FEEDBACK (BARU) -->
                <div id="ending-review-area" class="hidden text-center p-6 mt-4 w-full">
                    <h2 class="text-3xl font-extrabold text-accent-blue mb-2">SIMULASI SELESAI!</h2>
                    <p class="text-text-dark text-lg mb-4">Selamat, Anda telah menyelesaikan semua modul kompetensi.</p>

                    <div class="bg-light-primary p-4 rounded-xl mb-6 border border-accent-warning">
                        <p class="text-sm font-medium text-gray-600">Total Poin Kompetensi Anda:</p>
                        <p id="final-score" class="mt-1">0</p>
                    </div>

                    <div class="text-left bg-light-primary p-4 rounded-xl mb-6 text-text-dark">
                        <h3 class="text-xl font-bold mb-2 text-accent-blue">Ringkasan Mentor:</h3>
                        <p id="final-summary" class="text-base text-gray-700 leading-relaxed italic">
                            <!-- Ringkasan dari LLM akan dimuat di sini -->
                        </p>
                    </div>

                    <h3 class="text-xl font-bold text-text-dark mb-3">Beri Kami Umpan Balik</h3>
                    <textarea id="feedback-text" rows="3" placeholder="Bagaimana pengalaman Anda menggunakan AI Mentor ini? Apa saran Anda?" 
                              class="w-full p-3 rounded-lg text-base mb-4"></textarea>
                    
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                         <button onclick="submitFeedback()" class="btn-sleek p-3 text-base bg-accent-success hover:bg-green-600 text-white w-full sm:w-1/2">
                            Kirim Umpan Balik & Selesai
                        </button>
                        <button onclick="startNewGame()" class="btn-sleek btn-primary p-3 text-base w-full sm:w-1/2">
                            Mulai Sesi Baru
                        </button>
                    </div>
                </div>

            </div>
            <!-- Catatan TTS -->
            <p class="text-xs text-gray-500 mt-4 text-center pb-1 font-inter">
                *Aplikasi didukung oleh kecerdasan buatan Gemini.
            </p>
        </div>
    </div>

    <script>
        // --- Konfigurasi API & Global State ---
        const apiKey = "<?php echo $apiKey; ?>";
        const LLM_API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;
        const LOCAL_STORAGE_KEY = 'smkGameSave';

        const MAX_RETRIES = 3;
        const INITIAL_DELAY_MS = 1000; 
        
        // UI Elements
        const gmTextElement = document.getElementById('gm-text'); // Initial message element (used only once)
        const dynamicMessages = document.getElementById('dynamic-messages'); // New area for chat history
        const scoreDisplay = document.getElementById('score-display');
        const loadingIndicator = document.getElementById('loading-indicator');
        const controlPanel = document.getElementById('interaction-area'); 
        const textInputArea = document.getElementById('text-input-area');
        const userInput = document.getElementById('user-input');
        const voiceInputBtn = document.getElementById('voice-input-btn');
        const startButton = document.getElementById('start-btn');
        const difficultyArea = document.getElementById('difficulty-select-area');
        const majorSelectArea = document.getElementById('major-select-area'); 
        const majorSelect = document.getElementById('major-select');
        const customMajorInput = document.getElementById('custom-major-input');
        const resumeModal = document.getElementById('resume-modal');
        const celebrationOverlay = document.getElementById('celebration-overlay'); 
        const chatLog = document.getElementById('chat-log');

        // NEW UI ELEMENTS
        const endingReviewArea = document.getElementById('ending-review-area');
        const finalScoreDisplay = document.getElementById('final-score');
        const finalSummaryText = document.getElementById('final-summary');
        const feedbackTextarea = document.getElementById('feedback-text');
        
        let gameState = {
            studentName: 'Siswa SMK',
            studentMajor: 'Belum Ditentukan', 
            score: 0,
            current_topic: 'start',
            current_level: 0,
            selected_difficulty: null,
            history: [],
            finalSummary: "" // NEW: Menyimpan ringkasan akhir dari LLM
        };

        const compliments = [
            "COMPETENCY ACQUIRED!", "MODULE COMPLETE!", "TARGET EXCEEDED!", "SKILL UNLOCKED!", 
            "INDUSTRY READY!", "EXCELLENT SOLUTION!", "HIGHLY EFFICIENT!", "ACHIEVEMENT UNLOCKED!"
        ];
        function getRandomCompliment() {
            return compliments[Math.floor(Math.random() * compliments.length)];
        }
        
        // --- UI Rendering Functions ---

        /**
         * Menambahkan pesan (teks mentor atau jawaban siswa) ke chat log.
         * @param {string} role 'model' atau 'user'
         * @param {string} text Konten pesan
         * @param {boolean} isSpeaking Apakah mentor sedang berbicara (hanya untuk role 'model')
         */
        function addMessageToLog(role, text, isSpeaking = false) {
            const messageElement = document.createElement('div');
            messageElement.className = 'message-bubble';
            
            // Hapus pesan statis awal setelah pesan dinamis pertama muncul
            const staticMessage = document.querySelector('#dialog-area > .message-bubble');
            if (staticMessage) {
                 staticMessage.classList.add('hidden');
            }
            
            if (role === 'model') {
                messageElement.innerHTML = `
                    <div class="mentor-avatar flex-shrink-0 flex items-center justify-center">
                        <span class="text-white">👨‍💻</span>
                    </div>
                    <div class="text-content">
                        <p class="text-base font-medium leading-relaxed">${text}</p>
                    </div>
                `;
                dynamicMessages.appendChild(messageElement);

            } else if (role === 'user') {
                messageElement.className = 'message-bubble justify-end'; // Geser ke kanan
                messageElement.innerHTML = `
                    <div class="text-content">
                        <p class="text-base font-normal">${text}</p>
                    </div>
                `;
                dynamicMessages.appendChild(messageElement);
            }
            
            // Auto-scroll ke bawah
            setTimeout(() => {
                chatLog.scrollTop = chatLog.scrollHeight;
            }, 100);
            
            return messageElement;
        }

        // --- Logika Achievement & Skor ---
        function showCelebration() {
            celebrationOverlay.classList.remove('hidden');
            document.getElementById('compliment-text').textContent = getRandomCompliment();
            document.getElementById('child-name-text').textContent = `${gameState.studentName}!`;
            
            // Animasi masuk
            const complimentText = document.getElementById('compliment-text');
            complimentText.classList.remove('scale-0');
            complimentText.classList.add('scale-100', 'transition-transform', 'duration-500', 'ease-out');

            setTimeout(() => {
                hideCelebration();
            }, 3000);
        }

        function hideCelebration() {
            const complimentText = document.getElementById('compliment-text');
            complimentText.classList.remove('scale-100');
            complimentText.classList.add('scale-0', 'transition-transform', 'duration-500', 'ease-in');

            celebrationOverlay.classList.add('opacity-0', 'transition-opacity', 'duration-500');
            setTimeout(() => {
                celebrationOverlay.classList.add('hidden');
                celebrationOverlay.classList.remove('opacity-0', 'transition-opacity', 'duration-500');
            }, 500); 
        }

        // --- Logika Penyimpanan Cepat (localStorage) ---
        function saveGameState() {
            try {
                if (gameState.current_topic !== 'start') {
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
                    if (loadedState && loadedState.history && loadedState.current_topic !== 'start') {
                        // Memastikan properti baru ada saat memuat state lama
                        if (!loadedState.studentMajor) {
                            loadedState.studentMajor = 'Belum Ditentukan';
                        }
                        if (!loadedState.finalSummary) {
                            loadedState.finalSummary = '';
                        }
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
            scoreDisplay.textContent = `Poin Kompetensi: ${gameState.score}`;
            
            // Sembunyikan semua elemen kontrol
            startButton.classList.add('hidden');
            difficultyArea.classList.add('hidden');
            textInputArea.classList.add('hidden');
            majorSelectArea.classList.add('hidden'); 
            resumeModal.classList.add('hidden');
            endingReviewArea.classList.add('hidden'); // NEW: Sembunyikan area review

            // Tampilkan elemen yang relevan
            if (gameState.current_topic === 'start') {
                startButton.textContent = 'Mulai Uji Kompetensi';
                startButton.classList.remove('hidden');
            } else if (gameState.current_topic === 'name_input') {
                textInputArea.classList.remove('hidden');
                userInput.placeholder = "Tulis nama Anda di sini...";
                userInput.focus();
            } else if (gameState.current_topic === 'major_input') { 
                majorSelectArea.classList.remove('hidden');
                majorSelect.focus();
            } else if (gameState.current_topic === 'difficulty_select') {
                difficultyArea.classList.remove('hidden');
            } else if (gameState.current_topic.includes('_challenge')) {
                textInputArea.classList.remove('hidden');
                userInput.placeholder = "Masukkan Solusi Anda...";
                userInput.focus();
            } else if (gameState.current_topic === 'ending') {
                showEndingReview(); // Panggil fungsi khusus untuk tampilan akhir
            }
        }
        
        // --- Logika Pilihan Jurusan Baru ---
        function toggleCustomMajorInput() {
            if (majorSelect.value === 'Lainnya') {
                customMajorInput.classList.remove('hidden');
                customMajorInput.focus();
            } else {
                customMajorInput.classList.add('hidden');
            }
        }

        function submitMajor() {
            let selectedMajor = majorSelect.value;

            if (selectedMajor === 'Lainnya') {
                selectedMajor = customMajorInput.value.trim();
            }
            
            if (selectedMajor.length === 0 || selectedMajor === 'Lainnya') {
                speakText("Harap pilih atau masukkan nama Jurusan Anda.");
                return;
            }
            
            gameState.studentMajor = selectedMajor;
            addMessageToLog('user', `Jurusan yang saya pilih adalah: ${gameState.studentMajor}`);
            
            // Lanjut ke pemilihan kesulitan
            showDifficultySelection();
        }

        // --- Utilitas TTS (Menggunakan Web Speech API) ---
        function speakText(text) {
            if (!('speechSynthesis' in window)) {
                loadingIndicator.classList.add('hidden');
                return;
            }
            
            window.speechSynthesis.cancel();
            
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID'; 

            const voices = window.speechSynthesis.getVoices();
            const indonesianVoice = voices.find(voice => voice.lang.startsWith('id'));
            if (indonesianVoice) {
                utterance.voice = indonesianVoice;
            }

            const mentorAvatar = dynamicMessages.lastChild?.querySelector('.mentor-avatar') || document.querySelector('.mentor-avatar');
            
            utterance.onstart = () => { 
                loadingIndicator.classList.remove('hidden'); 
                mentorAvatar?.classList.add('speaking'); 
            };
            utterance.onend = () => { 
                loadingIndicator.classList.add('hidden'); 
                mentorAvatar?.classList.remove('speaking'); 
            };
            utterance.onerror = () => { 
                loadingIndicator.classList.add('hidden'); 
                mentorAvatar?.classList.remove('speaking');
            };

            window.speechSynthesis.speak(utterance);
        }

        // --- Konfigurasi Sistem LLM (DIUPDATE UNTUK SMK) ---
        const systemInstruction = {
            parts: [{ 
                text: `
                    Anda adalah AI Mentor Simulasi Industri, karakter yang profesional, berwibawa, dan fokus pada peningkatan kompetensi. 
                    Anda berbicara dalam Bahasa Indonesia. Target audiens Anda adalah siswa SMK.
                    
                    Tujuan Anda adalah memandu pemain melalui simulasi tantangan yang memiliki 4 level kesulitan yang terus meningkat per tema kompetensi.
                    
                    STATUS GAME SAAT INI:
                    - studentName: ${gameState.studentName}
                    - studentMajor: ${gameState.studentMajor}
                    - current_topic: ('start', 'name_input', 'major_input', 'difficulty_select', 'logic_challenge', 'industry_standard_challenge', 'business_challenge', 'soft_skill_challenge', atau 'ending')
                    - current_level: (1 hingga 16. Level 1-4=Logika, 5-8=Standar Industri, 9-12=Bisnis, 13-16=Soft Skill)
                    - selected_difficulty: ('easy' untuk Dasar, 'medium' untuk Menengah, 'hard' untuk Kasus Industri).
                    
                    Topik Tantangan SMK:
                    1. logic_challenge: Pertanyaan tentang logika dasar, algoritma sederhana, atau matematika/fisika terapan.
                    2. industry_standard_challenge: Pertanyaan tentang standar operasional prosedur (SOP), alat, atau terminologi industri. Sesuaikan tantangan ini dengan Jurusan: ${gameState.studentMajor}.
                    3. business_challenge: Pertanyaan tentang dasar-dasar kewirausahaan, pemasaran digital, atau analisis pasar.
                    4. soft_skill_challenge: Pertanyaan tentang etika kerja, komunikasi tim, atau penyelesaian masalah.
                    
                    Gaya Narasi Berdasarkan 'selected_difficulty':
                    1. 'easy' (Dasar): Berikan pertanyaan teknis yang SANGAT LANGSUNG dan memerlukan definisi atau fakta dasar.
                    2. 'medium' (Menengah): Berikan pertanyaan yang memerlukan penerapan konsep dan analisis kasus pendek.
                    3. 'hard' (Kasus Industri): Berikan NARASI KASUS KOMPLEKS yang memerlukan analisis data, penalaran kritis, atau keputusan manajerial sederhana.
                    
                    Aturan Progresi Level:
                    (Total 4 level per tema)
                    - Pindah ke 'industry_standard_challenge' HANYA JIKA current_level mencapai 5.
                    - Pindah ke 'business_challenge' HANYA JIKA current_level mencapai 9.
                    - Pindah ke 'soft_skill_challenge' HANYA JIKA current_level mencapai 13.
                    - Pindah ke 'ending' HANYA JIKA current_level mencapai 17.
                    
                    Ketika 'current_topic' adalah 'ending' (level 17), 'gm_text' HARUS berisi ringkasan evaluatif TENTANG KINERJA KESELURUHAN siswa dan KUALITAS PEMBELAJARAN yang mereka tunjukkan.
                    
                    Anda HARUS SELALU merespons dalam format JSON sesuai skema yang disediakan.
                    
                    Jika pengguna memberikan jawaban yang benar, tingkatkan 'score_delta' menjadi 1 DAN 'current_level' harus bertambah 1. Jika salah, set 'score_delta' ke 0 dan ulangi level yang sama.
                    
                    Gunakan emoji yang profesional dan relevan dengan teknologi/industri (misalnya 👨‍💻, 📊, 🛠️, 💡) dalam 'gm_text'.
                    Anda HARUS SELALU menyertakan bidang 'tts_text' yang berisi versi teks yang SANGAT BERSIH (tanpa emoji, tanpa placeholder seperti [nama]) untuk pengucapan yang cepat dan jernih oleh TTS. Ganti [nama] dengan 'Siswa SMK' dalam tts_text, dan [nama] dengan nilai gameState.studentName di gm_text.
                `
            }]
        };

        const responseSchema = {
            type: "OBJECT",
            properties: {
                "gm_text": { "type": "STRING", "description": "Respon AI Mentor yang profesional dan instruktif (termasuk emoji dan placeholder [nama])." },
                "tts_text": { "type": "STRING", "description": "Versi teks HANYA berisi kata-kata bersih (tanpa emoji, tanpa placeholder) yang dioptimalkan untuk pengucapan TTS." },
                "challenge_type": { "type": "STRING", "enum": ["input", "end"], "description": "Tipe interaksi berikutnya: input, atau end." },
                "is_correct": { "type": "BOOLEAN", "description": "TRUE jika giliran sebelumnya adalah jawaban yang benar, FALSE jika salah, NULL/hilang jika hanya alur cerita." },
                "score_delta": { "type": "NUMBER", "description": "Perubahan skor (0 atau 1) berdasarkan giliran sebelumnya." },
                "current_topic": { "type": "STRING", "description": "Topik atau status game saat ini. Harus 'ending' jika level mencapai 17." },
                "current_level": { "type": "NUMBER", "description": "Level Tantangan saat ini (1-17). Level 17 adalah 'ending'." }
            },
            required: ["gm_text", "tts_text", "challenge_type", "current_topic", "current_level"]
        };

        // --- Logika Interaksi LLM ---
        async function processGeminiTurn(userAction) {
            loadingIndicator.classList.remove('hidden');
            controlPanel.style.pointerEvents = 'none';

            // Tambahkan pesan pengguna ke log sebelum mengirim (kecuali untuk alur awal)
            if (userAction && 
                gameState.current_topic !== 'name_input' && 
                gameState.current_topic !== 'major_input' &&
                gameState.current_topic !== 'difficulty_select') {
                addMessageToLog('user', userInput.value.trim());
            }

            // PENTING: Gunakan systemInstruction yang diperbarui dengan studentMajor
            const updatedSystemInstruction = JSON.parse(JSON.stringify(systemInstruction));
            updatedSystemInstruction.parts[0].text = updatedSystemInstruction.parts[0].text
                .replace('${gameState.studentName}', gameState.studentName)
                .replace('${gameState.studentMajor}', gameState.studentMajor);
            
            const stateInfo = `| Current State: {topic: ${gameState.current_topic}, level: ${gameState.current_level}, difficulty: ${gameState.selected_difficulty}, major: ${gameState.studentMajor}}`;
            const fullUserAction = userAction ? `${userAction} ${stateInfo}` : stateInfo;
            
            gameState.history.push({ role: "user", parts: [{ text: fullUserAction }] });

            const payload = {
                contents: gameState.history,
                systemInstruction: updatedSystemInstruction, // Menggunakan instruksi yang diperbarui
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
                handleError("⚠️ Gagal koneksi ke Simulasi Industri. Coba lagi.");
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
            
            // Simpan ringkasan akhir jika game berakhir
            if (gameState.current_topic === 'ending') {
                gameState.finalSummary = response.gm_text.replace(/\[nama\]/g, gameState.studentName);
            }

            // 2. Simpan state 
            saveGameState(); 

            // 3. Update Dialog and TTS
            const gmMessage = response.gm_text.replace(/\[nama\]/g, gameState.studentName); 
            const ttsMessage = response.tts_text.replace(/\[nama\]/g, gameState.studentName); 
            
            // 4. TAMPILKAN ACHIEVEMENT JIKA BENAR
            if (isCorrect) {
                showCelebration(); 
            }
            
            // Tambahkan pesan mentor baru (kecuali jika game berakhir, pesan akan ditampilkan di ending screen)
            if (gameState.current_topic !== 'ending') {
                const modelMessageElement = addMessageToLog('model', gmMessage, true);
                speakText(ttsMessage);
            }
            
            // 5. Update UI
            updateUIForCurrentState();
            userInput.value = '';

            // Animasi skor jika berubah
            if (gameState.score !== oldScore) {
                scoreDisplay.classList.add('score-animated');
                scoreDisplay.addEventListener('animationend', () => {
                    scoreDisplay.classList.remove('score-animated');
                }, { once: true });
            }
            
            // 6. Tambahkan respons GM ke riwayat
            gameState.history.push({ role: "model", parts: [{ text: JSON.stringify(response) }] });
        }
        
        // --- Logika Alur Game Baru ---
        
        // NEW FUNCTION: Show Major Input
        function showMajorInput() {
            gameState.current_topic = 'major_input';
            saveGameState();
            updateUIForCurrentState();
            
            const majorMsg = `Baik, ${gameState.studentName}. Sebelum kita mulai, tolong pilih Jurusan SMK Anda di bawah. Ini akan membantu saya menyesuaikan studi kasus industri. 🛠️`;
            addMessageToLog('model', majorMsg);
            speakText(majorMsg);
        }
        
        function showDifficultySelection() {
            gameState.current_topic = 'difficulty_select';
            saveGameState();
            
            updateUIForCurrentState();
            
            const difficultyMsg = `${gameState.studentName} dari jurusan ${gameState.studentMajor}, silakan pilih tingkat kesulitan tantangan Anda.`;
            addMessageToLog('model', difficultyMsg);
            speakText(difficultyMsg);
            
            // Karena ini adalah langkah UI statis, kita tidak perlu memanggil LLM di sini.
        }

        function selectDifficulty(level) {
            gameState.selected_difficulty = level;
            const difficultyMap = { 'easy': 'Dasar', 'medium': 'Menengah', 'hard': 'Kasus Industri' };
            
            loadingIndicator.classList.remove('hidden');
            
            // Kirim ke LLM untuk memulai tantangan pertama (logic_challenge Level 1)
            const initialPrompt = `Saya memilih kesulitan ${difficultyMap[level]} (${level}) untuk jurusan ${gameState.studentMajor}. Tolong sapa saya dan mulai tantangan 'logic_challenge' Level 1.`;
            
            difficultyArea.classList.add('hidden');
            
            processGeminiTurn(initialPrompt);
        }

        /**
         * NEW FUNCTION: Menampilkan layar review dan feedback akhir.
         */
        function showEndingReview() {
            // Sembunyikan semua kontrol
            startButton.classList.add('hidden');
            difficultyArea.classList.add('hidden');
            textInputArea.classList.add('hidden');
            majorSelectArea.classList.add('hidden'); 
            resumeModal.classList.add('hidden');

            // Tampilkan area review
            endingReviewArea.classList.remove('hidden');
            
            // Perbarui data di layar akhir
            finalScoreDisplay.textContent = gameState.score;
            finalSummaryText.innerHTML = gameState.finalSummary;
            feedbackTextarea.value = ''; // Reset feedback text

            // Scroll ke bagian bawah log agar layar akhir terlihat
            chatLog.scrollTop = chatLog.scrollHeight; 
            
            // Berikan notifikasi suara
            speakText(`Simulasi selesai, ${gameState.studentName}! Anda meraih total ${gameState.score} poin. Silakan lihat ringkasan di bawah.`);
        }

        /**
         * NEW FUNCTION: Mengirim umpan balik dan mereset game.
         */
        function submitFeedback() {
            const feedback = feedbackTextarea.value.trim();
            if (feedback.length > 0) {
                console.log(`[FEEDBACK] Umpan Balik dari ${gameState.studentName} (${gameState.studentMajor}): ${feedback}`);
                alert("Terima kasih atas umpan balik Anda! Feedback telah dicatat."); // Gunakan alert sederhana
            } else {
                 alert("Terima kasih telah berpartisipasi!");
            }
            startNewGame();
        }


        // --- Logika Input Suara (Voice Input) ---
        let recognition = null;

        function startVoiceInput() {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                addMessageToLog('model', "⚠️ Browser Anda tidak mendukung input suara. Silakan gunakan input teks.");
                speakText("Browser Anda tidak mendukung input suara. Silakan gunakan input teks.");
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
                voiceInputBtn.textContent = '🔴 MENDENGAR...';
                voiceInputBtn.classList.add('listening');
                userInput.placeholder = "Bicaralah sekarang untuk solusi Anda...";
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                userInput.value = transcript;
                userInput.placeholder = `Hasil Input Suara: "${transcript}". Mengirim...`;
            };

            recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                voiceInputBtn.textContent = '❌ Coba Lagi';
                userInput.placeholder = "Masukkan Solusi Anda...";
                handleError(`⚠️ Masalah input suara: ${event.error}. Gunakan input teks.`);
            };

            recognition.onend = () => {
                voiceInputBtn.classList.remove('listening');
                
                const submitted = userInput.value.trim() !== "";
                
                voiceInputBtn.textContent = '🎤 Bicara';
                userInput.placeholder = "Masukkan Solusi atau Nama Anda...";
                
                if (submitted) {
                    submitAnswer();
                }
            };

            recognition.start();
        }
        
        // --- Fungsi Kontrol Game Utama ---
        function handleError(message) {
            addMessageToLog('model', message);
            speakText(message);
            clearGameState(); 
            updateUIForCurrentState();
            startButton.textContent = 'Mulai Ulang Sesi';
            startButton.classList.remove('hidden');
        }

        function submitAnswer() {
            const input = userInput.value.trim();
            if (input.length === 0) {
                speakText("Input tidak boleh kosong. Harap isi jawaban Anda.");
                return;
            }

            // Logika untuk menyimpan nama (Langkah 1)
            if (gameState.current_topic === 'name_input') {
                gameState.studentName = input.substring(0, 20); // Batasi nama
                userInput.value = ''; // Kosongkan input
                // Tambahkan pesan siswa (nama) ke log
                addMessageToLog('user', input);
                // Langsung lompat ke pemilihan jurusan
                showMajorInput(); 
                return;
            }
            
            // Logika untuk menjawab tantangan (Langkah 3)
            processGeminiTurn(`Solusi yang saya kirimkan adalah: ${input}`);
        }
        
        function loadAndResumeGame() {
            const savedData = loadGameState();
            if (savedData) {
                gameState = savedData;
                
                // Hapus pesan statis
                document.querySelector('#dialog-area > .message-bubble')?.classList.add('hidden');
                
                // Rekonstruksi Chat Log dari History (hanya teks mentor)
                dynamicMessages.innerHTML = '';
                gameState.history.forEach(entry => {
                    if (entry.role === 'model') {
                        try {
                            const response = JSON.parse(entry.parts[0].text);
                            // Jangan tampilkan pesan "ending" di log utama saat resume
                            if (response.current_topic !== 'ending') {
                                const gmMessage = response.gm_text.replace(/\[nama\]/g, gameState.studentName);
                                addMessageToLog('model', gmMessage, false);
                            }
                        } catch (e) {
                            // Abaikan JSON yang gagal parse
                        }
                    } else if (entry.role === 'user') {
                        // Tambahkan riwayat pengguna, bersihkan state info
                        const text = entry.parts[0].text;
                        const cleanText = text
                            .replace(/\| Current State: \{.*?\}/, '')
                            .replace('Solusi yang saya kirimkan adalah: ', '')
                            .trim();
                            
                        if (cleanText.length > 0 && 
                            !cleanText.includes('initial_start_prompt') && 
                            !cleanText.includes('memilih kesulitan') &&
                            !cleanText.includes('memilih Jurusan')) {
                            addMessageToLog('user', cleanText, false);
                        }
                    }
                });

                // Jika game sudah selesai, langsung tampilkan ending screen
                if (gameState.current_topic === 'ending') {
                    showEndingReview();
                    return; // Hentikan proses resume normal
                }
                
                // Ambil respons GM terakhir dari history untuk diucapkan
                const modelResponse = gameState.history.slice().reverse().find(entry => entry.role === 'model' && entry.parts[0].text);
                let lastGMResponse;
                try {
                    lastGMResponse = JSON.parse(modelResponse.parts[0].text);
                } catch (e) {
                    console.error("Gagal mem-parse respons AI Mentor terakhir, memulai ulang.", e);
                    startNewGame();
                    return;
                }
                
                const gmMessage = lastGMResponse.gm_text.replace(/\[nama\]/g, gameState.studentName); 
                const ttsMessage = lastGMResponse.tts_text.replace(/\[nama\]/g, gameState.studentName); 
                
                speakText(`Selamat datang kembali, ${gameState.studentName}! Melanjutkan Simulasi Industri. ${ttsMessage}`);
                
                // Pastikan pesan mentor terakhir terlihat di log
                dynamicMessages.lastChild.querySelector('.text-content p').textContent = gmMessage;
                
                updateUIForCurrentState();
                userInput.focus();
            } else {
                startNewGame();
            }
        }

        function startNewGame() {
            clearGameState();
            gameState = {
                studentName: 'Siswa SMK',
                studentMajor: 'Belum Ditentukan',
                score: 0,
                current_topic: 'name_input', // Pindah ke input nama dulu
                current_level: 0,
                selected_difficulty: null,
                history: [],
                finalSummary: ""
            };
            
            // Hapus semua pesan dinamis
            dynamicMessages.innerHTML = '';
            // Tampilkan pesan statis awal di log
            document.querySelector('#dialog-area > .message-bubble')?.classList.remove('hidden');
            gmTextElement.textContent = "AI Mentor siap. Tuliskan nama lengkap atau nama panggilan Anda untuk memulai. 📝";
            speakText("AI Mentor siap. Tuliskan nama lengkap atau nama panggilan Anda untuk memulai.");
            
            updateUIForCurrentState();
            userInput.placeholder = "Tulis nama Anda di sini...";
            userInput.focus();
        }

        window.onload = function() {
            // Pengaturan untuk memastikan suara tersedia
            if ('speechSynthesis' in window && window.speechSynthesis.onvoiceschanged !== undefined) {
                window.speechSynthesis.onvoiceschanged = () => {
                    window.speechSynthesis.getVoices();
                };
            }
            
            const savedState = loadGameState();
            
            if (savedState) {
                // Tampilkan modal resume
                document.getElementById('resume-info').textContent = `Sesi terakhir: ${savedState.studentName} (${savedState.studentMajor}), Poin Kompetensi: ${savedState.score}.`;
                startButton.classList.add('hidden');
                resumeModal.classList.remove('hidden');
            } else {
                // Mulai dengan input nama
                startNewGame();
            }
        };

        // Mengizinkan kirim jawaban dengan tombol Enter
        userInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); 
                submitAnswer();
            }
        });
      const layers = [
    {el: document.getElementById('layer1'), speedY: 0.08, speedX: 0.02, rotate: 0.015, scale: 0.01, currentX: 0, currentY:0},
    {el: document.getElementById('layer2'), speedY: 0.12, speedX: 0.03, rotate: -0.02, scale: 0.015, currentX: 0, currentY:0},
    {el: document.getElementById('layer3'), speedY: 0.05, speedX: 0.01, rotate: 0.01, scale: 0.008, currentX: 0, currentY:0},
    {el: document.getElementById('layer4'), speedY: 0.18, speedX: 0.025, rotate: -0.03, scale: 0.02, currentX: 0, currentY:0},
    {el: document.getElementById('layer5'), speedY: 0.35, speedX: 0.015, rotate: 0.05, scale: 0.03, currentX: 0, currentY:0},
    {el: document.getElementById('layer6'), speedY: 0.22, speedX: 0.02, rotate: -0.1, scale: 0.025, currentX: 0, currentY:0},
];

let mouseX = window.innerWidth / 2;
let mouseY = window.innerHeight / 2;
let scrollY = window.scrollY;

function lerp(a, b, t) {
    return a + (b - a) * t;
}

function animateLayers() {
    const targetX = (window.innerWidth / 2 - mouseX) / 100;
    const targetY = (window.innerHeight / 2 - mouseY) / 100;

    layers.forEach(layer => {
        layer.currentX = lerp(layer.currentX, targetX * 50, 0.08);
        layer.currentY = lerp(layer.currentY, scrollY * layer.speedY + targetY * 50, 0.08);
        const rotation = scrollY * layer.rotate;
        layer.el.style.transform = `translate(${layer.currentX}px, ${layer.currentY}px) rotate(${rotation}deg)`;
    });

    requestAnimationFrame(animateLayers);
}

window.addEventListener('scroll', () => {
    scrollY = window.scrollY;
});

// Deteksi perangkat
if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
    // Mobile: gunakan gyro
    if (window.DeviceOrientationEvent) {
        window.addEventListener('deviceorientation', e => {
            // e.beta (x) = miring depan/belakang, e.gamma (y) = miring kiri/kanan
            mouseX = window.innerWidth / 2 + e.gamma * 10; // sensitivitas bisa diatur
            mouseY = window.innerHeight / 2 + e.beta * 10;
        }, true);
    } else {
        console.warn('DeviceOrientation tidak didukung di perangkat ini.');
    }
} else {
    // Desktop: gunakan mouse
    window.addEventListener('mousemove', e => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });
}

requestAnimationFrame(animateLayers);

    </script>
</body>
</html>
