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
    <title>Simulasi Mengajar Guru (Game Master AI)</title>
    
<script src="https://cdn.tailwindcss.com"></script>
    
<script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#4F46E5', // Indigo 600
                        'secondary': '#1E293B', // Slate 800
                        'accent': '#F59E0B', // Amber 500
                        'step-success': '#10B981', // Emerald 500
                        'step-active': '#4F46E5', // Primary
                        'step-fail': '#EF4444', // Red 500
                    }
                }
            }
        }
    </script>
    <style>
        /* Mengatur font Inter jika belum dimuat */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        
        /* Styling untuk pesan AI (Game Master) */
        .message-gm {
            background-color: #1F2937; /* Gray 800 */
            border-left: 4px solid #F59E0B; /* Accent color */
        }
        /* Styling untuk pesan Guru (User) */
        .message-user {
            background-color: #374151; /* Gray 700 */
            border-right: 4px solid #4F46E5; /* Primary color */
        }
        /* Styling untuk pesan Status/Skenario */
        .message-status {
            background-color: #3B82F6; /* Blue 500 */
        }
        
        .action-button {
            transition: all 0.15s;
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        /* CSS untuk Loading Spinner yang mengelilingi logo */
        .loader-container {
            position: relative;
            width: 120px; 
            height: 120px; 
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem; 
        }

        .loader-spinner {
            position: absolute;
            border: 4px solid #374151; 
            border-top-color: #4F46E5; 
            border-radius: 50%;
            width: 100%;
            height: 100%;
            animation: spin 1.5s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loader-logo {
            z-index: 10; 
            border-radius: 50%; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); 
        }

        /* Styling khusus untuk modal review agar Markdown tampil rapi */
        #review-content h3 {
            font-weight: bold;
            font-size: 1.25rem; 
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            color: #F59E0B; 
            border-bottom: 1px solid #4F46E5;
            padding-bottom: 4px;
        }
        #review-content p {
            margin-bottom: 0.75rem;
        }
        #review-content ul {
            list-style: disc;
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 font-sans">

    
<!-- Overlay Loading -->
<div id="loading-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-95 z-50 flex items-center justify-center hidden">
    <div id="loading-content" class="flex flex-col items-center p-8 bg-gray-800 rounded-xl shadow-2xl ring-2 ring-primary w-full max-w-md">
        
        <!-- Area Loading Generik -->
        <div id="generic-loading" class="flex flex-col items-center">
            <div class="loader-container">
                <div class="loader-spinner"></div>
                <img src="../avatars/formal.jpeg" alt="Logo" class="loader-logo h-24 w-24"> 
            </div>
            <p class="text-xl font-bold text-primary animate-pulse" id="loading-text">Game Master sedang memproses...</p>
            <p class="text-sm text-gray-400 mt-2">Menyesuaikan skenario dan opsi dinamis.</p>
        </div>

        <!-- Area Status Review (Disembunyikan secara default) -->
        <div id="review-status-area" class="w-full space-y-4 hidden">
             <h3 class="text-2xl font-bold text-accent mb-4 text-center">Menganalisis Kinerja Guru...</h3>
             <!-- Note/Peringatan Durasi Baru -->
             <p class="text-center text-sm text-gray-400 p-2 bg-gray-700 rounded-lg">Analisis ini kompleks dan mungkin memerlukan waktu 10-20 detik, harap bersabar.</p>
             <!-- Akhir Note/Peringatan Durasi Baru -->
             <div id="status-steps" class="space-y-3">
                 <!-- Steps di-render di sini oleh JS -->
             </div>
             <p id="review-error-message" class="text-red-400 text-sm mt-4 hidden text-center font-medium"></p>
        </div>
    </div>
</div>
    
    <!-- Modal Review -->
    <div id="review-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-40 hidden flex items-center justify-center p-4">
        <div class="bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-accent">Laporan Ulasan Kinerja Guru</h2>
                <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="review-content" class="p-6 overflow-y-auto flex-1 text-gray-200">
                <p>Memuat ulasan. Mohon tunggu...</p>
            </div>
            <div class="p-6 border-t border-gray-700 flex justify-end gap-3">
                <button onclick="downloadDisplayedReview()" class="p-3 bg-primary hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    Unduh Laporan (.txt)
                </button>
            </div>
        </div>
    </div>
    

<!-- Kontainer Utama: Menggunakan min-h-screen dan flex-col untuk responsif vertikal -->
<div class="min-h-screen flex flex-col">
    
    <!-- Header: Selalu terlihat, berisi Judul dan Tombol Toggle (mobile) -->
    <div class="bg-gray-800 p-4 sm:p-6 lg:px-8 flex justify-between items-center border-b border-gray-700 flex-shrink-0">
        <h1 class="text-xl sm:text-3xl font-extrabold text-primary">Game Master Simulasi Mengajar</h1>
        <button id="toggle-settings-btn" onclick="toggleSettingsPanel()" class="lg:hidden p-2 bg-primary hover:bg-indigo-500 rounded-lg text-white transition duration-150" aria-label="Toggle Pengaturan">
            <svg id="settings-icon-open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <svg id="settings-icon-close" class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- MAIN INTERFACE AREA: flex-1 untuk mengisi sisa tinggi -->
    <div class="flex-1 flex overflow-hidden relative">
        
        <!-- PANEL PENGATURAN (Sembunyi/Muncul di Mobile) -->
        <div id="settings-panel" class="lg:w-1/4 w-full p-6 bg-gray-800 shadow-2xl flex-shrink-0 overflow-y-auto z-20 absolute lg:static h-full hidden lg:flex flex-col space-y-6 border-r border-gray-700 transition-all duration-300">
            <h2 class="text-2xl font-bold text-accent mb-4 lg:hidden">Pengaturan</h2>
            
            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-300">Aktivitas KBM/Praktek:</label>
                <textarea id="input-subject" placeholder="Contoh: Praktikum Kimia, KBM di Bengkel Otomotif, Konseling" class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 focus:ring-primary focus:border-primary transition duration-150 h-16 resize-none">KBM di Bengkel Otomotif</textarea>
            </div>

            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-300">Tingkat Kelas:</label>
                <select id="input-grade" class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 focus:ring-primary focus:border-primary transition duration-150">
                    <option value="SD Kelas 1">SD Kelas 1</option>
                    <option value="SD Kelas 6">SD Kelas 6</option>
                    <option value="SMP Kelas 7">SMP Kelas 7</option>
                    <option value="SMA Kelas 10" selected>SMA/SMK Kelas 10</option>
                    <option value="SMA Kelas 11">SMA/SMK Kelas 11</option>
                    <option value="SMA Kelas 12">SMA/SMK Kelas 12</option>
                    <option value="Perguruan Tinggi">Perguruan Tinggi</option>
                </select>
            </div>

            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-300">Topik Spesifik:</label>
                <textarea id="input-topic" placeholder="Contoh: Aljabar Dasar, Hukum Newton I, Perang Dunia II" class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 focus:ring-primary focus:border-primary transition duration-150 h-24 resize-none">Prosedur Pemeriksaan dan Perbaikan Mesin Mobil</textarea>
            </div>
            
            <button onclick="startSimulation()" id="start-button" class="w-full p-3 bg-primary hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-md transition duration-150 disabled:bg-gray-600">
                Mulai Simulasi
            </button>
            
            <button onclick="endSimulationAndReview()" id="review-button" class="w-full p-3 bg-red-700 hover:bg-red-600 text-white font-semibold rounded-lg shadow-md transition duration-150 disabled:bg-gray-600 hidden">
                Akhiri Simulasi & Dapatkan Ulasan
            </button>
            
            <!-- Tombol Reset Baru -->
            <button onclick="resetSimulationHistory()" id="reset-button" class="w-full p-3 bg-gray-600 hover:bg-gray-500 text-white font-semibold rounded-lg shadow-md transition duration-150">
                Hapus Riwayat (Reset)
            </button>


            <div class="mt-8 text-xs text-gray-500 pt-4 border-t border-gray-700 flex-shrink-0">
                <p>Status Penyimpanan: Lokal (localStorage)</p>
                <p>Model AI: gemini-2.5-flash-preview-09-2025</p>
                <p>Game Master AI akan mensimulasikan 3-5 siswa dengan kepribadian berbeda.</p>
                <p id="tts-status" class="mt-2 text-primary font-medium text-xs"></p>
            </div>
        </div>

        
        <!-- AREA CHAT UTAMA: flex-1 untuk mengisi sisa ruang horizontal -->
        <div class="flex-1 flex flex-col p-4 sm:p-6 lg:p-8 overflow-hidden">
            <div id="status-message" class="bg-blue-900 p-3 rounded-lg mb-4 text-sm flex-shrink-0 hidden"></div>

            
            <!-- CHAT DISPLAY AREA: flex-1 untuk mengisi sisa ruang vertikal dan memungkinkan scroll -->
            <div id="chat-container" class="chat-area flex-1 p-4 bg-gray-800 rounded-xl shadow-inner mb-4 space-y-4 overflow-y-auto">
                <div class="message-status p-3 rounded-lg">
                    <p class="font-bold">Selamat datang!</p>
                    <p class="text-sm">Atur mata pelajaran, kelas, dan topik Anda di panel Pengaturan (toggle di pojok kanan atas) dan klik "Mulai Simulasi" untuk memulai sesi mengajar Anda.</p>
                </div>
            </div>

            
            <!-- Tombol Aksi & Input: flex-shrink-0 untuk selalu terlihat di bagian bawah -->
            <div id="action-buttons-container" class="mb-4 flex flex-wrap gap-3 flex-shrink-0 hidden">
                
            </div>

            
            <div class="flex space-x-3 items-end flex-shrink-0">
                <textarea id="user-input" placeholder="Tuliskan respons pengajaran Anda di sini, atau tekan tombol Mikrofon untuk rekaman suara..." rows="2" class="flex-1 p-3 rounded-lg bg-gray-700 border border-gray-600 focus:ring-primary focus:border-primary resize-none disabled:bg-gray-600" disabled></textarea>
                
                <div class="flex flex-col space-y-2">
                    <!-- Tombol TTS Play/Stop -->
                    <div class="flex space-x-2">
                         <button id="tts-play-btn" onclick="handlePlayNarrative()" class="p-3 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-lg shadow-md transition duration-150 w-12 h-12 flex items-center justify-center disabled:bg-gray-600" disabled aria-label="Putar Narasi">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button id="tts-stop-btn" onclick="handleStopNarrative()" class="p-3 bg-yellow-600 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg shadow-md transition duration-150 w-12 h-12 flex items-center justify-center disabled:bg-gray-600" disabled aria-label="Hentikan Narasi">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9a1 1 0 00-1 1v2a1 1 0 102 0v-2a1 1 0 00-1-1zM11 9a1 1 0 00-1 1v2a1 1 0 102 0v-2a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Tombol Mikrofon dan Kirim -->
                    <div class="flex space-x-2">
                        <button id="voice-button" onclick="toggleVoiceRecognition()" class="p-3 bg-red-500 hover:bg-red-400 text-white font-semibold rounded-lg shadow-md transition duration-150 w-12 h-12 flex items-center justify-center disabled:bg-gray-600" disabled aria-label="Rekam Suara">
                            <svg id="mic-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7v0a7 7 0 01-7-7v0a7 7 0 017-7v0a7 7 0 017 7v0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21v-2m0-12V3" />
                            </svg>
                        </button>

                        <button onclick="sendUserMessage()" id="send-button" class="p-3 bg-primary hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-md transition duration-150 w-24 disabled:bg-gray-600 h-12" disabled>
                            Kirim
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    

<script type="module">
        // --- PENGATURAN GLOBAL ---
        const API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent";
        const apiKey = "<?php echo $apiKey; ?>"; 
        const OPTION_DELIMITER = '$$$OPSI:'; // Delimiter unik untuk memisahkan opsi dari narasi
        const CHAT_STORAGE_KEY = 'gm_sim_chat_history'; 
        
        let chatHistory = [];
        let isProcessing = false;
        let lastReviewContent = ''; 
        
        // Variabel untuk Voice Recognition
        let recognition;
        let isRecording = false;
        
        // Variabel untuk TTS
        let synth = null;
        let selectedVoice = null;
        let currentNarrationText = ''; 
        const targetLang = 'id-ID'; 
        let isTtsCancelling = false; 
        
        // Variabel Status Review yang Disederhanakan
        const reviewStepsData = [
            { id: 0, name: "1. Mengumpulkan & Mengirim Riwayat Chat", status: 'pending' },
            { id: 1, name: "2. Model AI Menganalisis Kinerja Guru", status: 'pending' },
            { id: 2, name: "3. Mempersiapkan dan Menampilkan Laporan", status: 'pending' }
        ];

        // --- Fungsi Toggle Panel Pengaturan (Baru untuk Responsif) ---
        function toggleSettingsPanel() {
            const panel = document.getElementById('settings-panel');
            const openIcon = document.getElementById('settings-icon-open');
            const closeIcon = document.getElementById('settings-icon-close');
            
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                panel.classList.add('flex'); // Gunakan flex untuk layout column
                openIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
                panel.classList.remove('flex');
                openIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        }


        // --- Fungsi Penyimpanan Data Lokal (LocalStorage) ---

        /** Menyimpan riwayat chat ke Local Storage. */
        function saveChatHistory() {
            try {
                localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(chatHistory));
            } catch (e) {
                console.error("Gagal menyimpan riwayat chat ke Local Storage:", e);
                displayStatus("Peringatan: Gagal menyimpan riwayat ke penyimpanan lokal.", 'error');
            }
        }

        /** Memuat riwayat chat dari Local Storage dan merender UI. */
        function loadChatHistory() {
            const storedHistory = localStorage.getItem(CHAT_STORAGE_KEY);
            const chatContainer = document.getElementById('chat-container');
            chatContainer.innerHTML = ''; // Kosongkan chat area

            if (storedHistory) {
                try {
                    chatHistory = JSON.parse(storedHistory);
                    
                    let systemMessageFound = false;
                    let lastNarrative = ''; 
                    
                    chatHistory.forEach(message => {
                        if (message.role === 'user') {
                            const textToRender = message.parts[0].text;
                            let userDisplay = textToRender;

                            if (textToRender.startsWith('Guru merespons dengan:')) {
                                const match = textToRender.match(/Guru merespons dengan: "(.*?)". Lanjutkan simulasi./);
                                userDisplay = match && match[1] ? match[1] : 'Teks respons guru...';
                            } else if (textToRender.startsWith('Guru memilih aksi:')) {
                                const match = textToRender.match(/Guru memilih aksi: "(.*?)". Lanjutkan simulasi./);
                                userDisplay = match && match[1] ? `(Aksi Tombol) ${match[1]}` : 'Aksi tombol dipilih...';
                            }
                            appendMessage('user', userDisplay);
                            
                        } else if (message.role === 'model') {
                            // Ekstrak narasi tanpa delimiter opsi
                            let responseText = message.parts[0].text;
                            const optionIndex = responseText.lastIndexOf(OPTION_DELIMITER);
                            let narrativeText = responseText;

                            if (optionIndex !== -1) {
                                narrativeText = responseText.substring(0, optionIndex).trim();
                            }
                            appendMessage('model', narrativeText, true); 
                            lastNarrative = narrativeText; 
                        } else if (message.role === 'status') {
                            appendMessage('status', message.parts[0].text);
                            systemMessageFound = true;
                        }
                    });

                    currentNarrationText = lastNarrative;
                    if (currentNarrationText) {
                        document.getElementById('tts-play-btn').disabled = false;
                    }


                    if (systemMessageFound || chatHistory.length > 0) {
                        appendMessage('status', 'Simulasi sebelumnya berhasil dimuat dari penyimpanan lokal.');
                        document.getElementById('review-button').classList.remove('hidden'); 
                    }


                    if (chatHistory.length > 0 && chatHistory[chatHistory.length - 1].role === 'model') {
                        const lastModelResponse = chatHistory[chatHistory.length - 1].parts[0].text;
                        const optionIndex = lastModelResponse.lastIndexOf(OPTION_DELIMITER);

                        if (optionIndex !== -1) {
                            const optionsString = lastModelResponse.substring(optionIndex + OPTION_DELIMITER.length).trim();
                            const options = optionsString.split('|').map(o => o.trim()).filter(o => o.length > 0);
                            renderActionButtons(options);
                        }
                    }

                    return true; 
                } catch (e) {
                    console.error("Kesalahan memuat riwayat chat dari local storage:", e);
                    chatHistory = []; 
                    return false;
                }
            }
            return false; 
        }

        /** Menghapus riwayat chat dan memuat ulang UI */
        function resetSimulationHistory() {
            if (isProcessing) {
                 displayStatus("Tidak dapat mereset saat AI sedang memproses.", 'info');
                 return;
            }

            handleStopNarrative();

            if (!confirm("Apakah Anda yakin ingin menghapus seluruh riwayat simulasi dan pengaturan dari penyimpanan lokal?")) {
                return;
            }

            chatHistory = [];
            localStorage.removeItem(CHAT_STORAGE_KEY);
            localStorage.removeItem('input-subject');
            localStorage.removeItem('input-grade');
            localStorage.removeItem('input-topic');
            
            // Set kembali nilai default UI
            document.getElementById('input-subject').value = 'KBM di Bengkel Otomotif';
            document.getElementById('input-grade').value = 'SMA Kelas 10';
            document.getElementById('input-topic').value = 'Prosedur Pemeriksaan dan Perbaikan Mesin Mobil';
            
            document.getElementById('chat-container').innerHTML = `
                <div class="message-status p-3 rounded-lg">
                    <p class="font-bold">Selamat datang!</p>
                    <p class="text-sm">Atur mata pelajaran, kelas, dan topik Anda di panel Pengaturan (toggle di pojok kanan atas untuk mobile) dan klik "Mulai Simulasi" untuk memulai sesi mengajar Anda.</p>
                </div>
            `;
            document.getElementById('start-button').textContent = 'Mulai Simulasi';
            document.getElementById('review-button').classList.add('hidden');
            
            clearActionButtons();
            document.getElementById('user-input').disabled = true;
            document.getElementById('send-button').disabled = true;
            document.getElementById('voice-button').disabled = true;
            document.getElementById('tts-play-btn').disabled = true;
            document.getElementById('tts-stop-btn').disabled = true;
            document.getElementById('user-input').placeholder = "Tuliskan respons pengajaran Anda di sini, atau tekan tombol Mikrofon untuk rekaman suara...";
            
            displayStatus("Riwayat simulasi dan pengaturan telah direset.", 'info');
        }


        // --- Fungsi Utilitas UI ---
        
        function markdownToHtml(markdown) {
            let html = markdown;
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
            html = html.replace(/###\s*(.*)/g, '<h3>$1</h3>');
            html = html.replace(/^\*\s*(.*)/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*?<\/li>)/s, '<ul>$1</ul>');
            // Menangani baris baru ganda sebagai paragraf
            html = html.replace(/<br>\s*<br>/g, '</p><p>'); 
            html = html.replace(/\n/g, '<br>'); // Mengganti \n yang tersisa dengan <br>
            html = `<p>${html}</p>`;
            html = html.replace(/<p><\/p>/g, ''); // Hapus paragraf kosong
            
            // Perbaikan untuk list yang tidak tertutup tag ul
            const listRegex = /(<p><ul>.*?<\/ul><\/p>)/gs; 
            html = html.replace(listRegex, (match) => match.replace(/<\/?p>/g, ''));
            
            return html;
        }


        /** Menampilkan pesan status/error di UI. */
        function displayStatus(message, type = 'info') {
            const statusDiv = document.getElementById('status-message');
            statusDiv.textContent = message;
            statusDiv.className = `p-3 rounded-lg mb-4 text-sm flex-shrink-0 ${type === 'error' ? 'bg-red-900 text-red-100' : 'bg-blue-900 text-blue-100'} `;
            statusDiv.style.display = 'block';
            if(type === 'error') {
                 setTimeout(() => { statusDiv.style.display = 'none'; }, 5000);
            }
        }
        
        /** Menampilkan status TTS di panel pengaturan */
        function displayTtsStatus(message) {
             const ttsStatusEl = document.getElementById('tts-status');
             ttsStatusEl.textContent = message;
        }

        /** Menambahkan pesan ke jendela obrolan (hanya untuk tampilan UI). */
        function appendMessage(role, text, isMarkdown = false) {
            const chatContainer = document.getElementById('chat-container');
            const messageDiv = document.createElement('div');
            
            let roleName = '';
            let className = '';

            if (role === 'user') {
                roleName = 'Guru';
                className = 'message-user self-end max-w-3/4';
            } else if (role === 'model') {
                roleName = 'Game Master AI';
                className = 'message-gm self-start max-w-3/4';
            } else if (role === 'status') {
                roleName = 'Sistem';
                className = 'message-status self-center max-w-full';
            }

            messageDiv.className = `p-3 rounded-xl shadow-md ${className}`;
            
            let contentHtml = '';
            if (isMarkdown) {
                // Konversi Markdown untuk pesan model
                contentHtml = markdownToHtml(text);
            } else {
                contentHtml = text.replace(/\n/g, '<br>');
            }

            messageDiv.innerHTML = `<p class="font-bold text-xs mb-1">${roleName}</p><div class="whitespace-pre-wrap">${contentHtml}</div>`;
            
            chatContainer.appendChild(messageDiv);
            
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        /** Menampilkan status loading, kini lebih cerdas untuk Review. */
        function setProcessing(processing, action = 'Memproses') {
            isProcessing = processing;
            const overlay = document.getElementById('loading-overlay');
            const genericLoading = document.getElementById('generic-loading');
            const reviewStatusArea = document.getElementById('review-status-area');
            
            document.getElementById('send-button').disabled = processing;
            document.getElementById('user-input').disabled = processing;
            document.getElementById('start-button').disabled = processing;
            document.getElementById('review-button').disabled = processing;
            
            document.getElementById('tts-play-btn').disabled = processing || isTtsCancelling; 
            document.getElementById('tts-stop-btn').disabled = processing || isTtsCancelling;
            
            handleStopNarrative(); 

            const voiceButton = document.getElementById('voice-button');
            if (voiceButton) {
                voiceButton.disabled = processing;
                if (processing && isRecording && recognition) {
                     recognition.stop(); 
                }
            }
            
            if (processing) {
                overlay.classList.remove('hidden');
                if (action === 'menganalisis kinerja') {
                    genericLoading.classList.add('hidden');
                    reviewStatusArea.classList.remove('hidden');
                    initializeReviewSteps(); 
                } else {
                    document.getElementById('loading-text').textContent = `Game Master sedang ${action}...`;
                    genericLoading.classList.remove('hidden');
                    reviewStatusArea.classList.add('hidden');
                }
            } else {
                overlay.classList.add('hidden');
                reviewStatusArea.classList.add('hidden');
                document.getElementById('review-error-message').classList.add('hidden');
                
                if (currentNarrationText && !isTtsCancelling) {
                    document.getElementById('tts-play-btn').disabled = false;
                }
            }
        }
        
        /** Menghapus semua tombol aksi dan mengaktifkan kembali input teks. */
        function clearActionButtons() {
            const container = document.getElementById('action-buttons-container');
            container.innerHTML = '';
            container.classList.add('hidden');
            document.getElementById('user-input').disabled = false;
        }
        
        /** Handler untuk klik tombol aksi. */
        function handleOptionClick(optionText) {
            if (isProcessing) return;

            handleStopNarrative(); 
            
            appendMessage('user', `(Aksi Tombol) ${optionText}`);
            
            clearActionButtons();
            
            const userMessage = `Guru memilih aksi: "${optionText}". Lanjutkan simulasi.`;

            const subject = document.getElementById('input-subject').value.trim();
            const grade = document.getElementById('input-grade').value;
            const topic = document.getElementById('input-topic').value.trim();
            const systemInstruction = createSystemInstruction(subject, grade, topic);
            
            callGeminiApi(userMessage, systemInstruction);
            
            // Tutup panel pengaturan di mobile setelah aksi dipilih
            const panel = document.getElementById('settings-panel');
            if (!panel.classList.contains('lg:static')) { // Cek jika di mode mobile (absolute)
                toggleSettingsPanel(); 
            }
        }

        /** Merender tombol aksi di UI. */
        function renderActionButtons(options) {
            const container = document.getElementById('action-buttons-container');
            container.innerHTML = '';
            
            options.forEach(option => {
                const button = document.createElement('button');
                button.textContent = option.trim();
                button.className = 'action-button p-3 text-sm bg-accent hover:bg-amber-400 text-gray-900 font-semibold rounded-lg shadow-lg transition duration-150 flex-grow lg:flex-grow-0';
                button.onclick = () => handleOptionClick(option.trim());
                container.appendChild(button);
            });

            container.classList.remove('hidden');
            document.getElementById('user-input').disabled = true; 
        }
        
        // --- Status Review Baru yang Disederhanakan ---

        /** Inisialisasi tampilan langkah-langkah review di overlay. */
        function initializeReviewSteps() {
            const stepsContainer = document.getElementById('status-steps');
            stepsContainer.innerHTML = '';
            reviewStepsData.forEach(step => step.status = 'pending'); 
            
            const stepDescriptions = [
                "Kompilasi riwayat chat dan kirim ke server AI.",
                "Model Gemini menganalisis Manajemen Kelas, Materi, dan Keterlibatan.",
                "Mengubah hasil analisis menjadi laporan Markdown."
            ];

            reviewStepsData.forEach((step, index) => {
                const statusIcon = getStepIcon('pending');
                const stepElement = document.createElement('div');
                stepElement.id = `step-${step.id}`;
                stepElement.className = 'flex items-center p-3 rounded-lg bg-gray-700 border border-gray-600 transition duration-300';
                stepElement.innerHTML = `
                    <span id="icon-${step.id}" class="icon w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold mr-3 ${statusIcon.class}">${statusIcon.svg}</span>
                    <div>
                        <p class="font-semibold text-gray-300">${step.name}</p>
                        <p class="text-xs text-gray-400">${stepDescriptions[index]}</p>
                    </div>
                `;
                stepsContainer.appendChild(stepElement);
            });
            document.getElementById('review-error-message').classList.add('hidden');
        }

        /** Mendapatkan ikon dan kelas berdasarkan status. */
        function getStepIcon(status) {
            let classString = 'bg-gray-500 text-white';
            let svgContent = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`; 

            if (status === 'active') {
                classString = 'bg-step-active text-white animate-pulse';
                svgContent = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.962 8.962 0 0112 21a9 9 0 01-8.625-10.297M17 5L12 10l-5-5" /></svg>`; 
            } else if (status === 'completed') {
                classString = 'bg-step-success text-gray-900';
                svgContent = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`; 
            } else if (status === 'failed') {
                 classString = 'bg-step-fail text-white';
                 svgContent = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`; 
            }
            return { class: classString, svg: svgContent };
        }

        /** Mengupdate status langkah tertentu. */
        function updateReviewStepStatus(stepId, status, errorMessage = '') {
            const stepElement = document.getElementById(`step-${stepId}`);
            const iconElement = document.getElementById(`icon-${stepId}`);
            const errorElement = document.getElementById('review-error-message');
            
            if (!stepElement) return;

            const iconData = getStepIcon(status);
            
            const stepData = reviewStepsData.find(s => s.id === stepId);
            if (stepData) stepData.status = status;

            stepElement.className = `flex items-center p-3 rounded-lg border transition duration-300 ${status === 'completed' ? 'bg-green-100 border-step-success' : status === 'active' ? 'bg-primary-50 border-primary' : 'bg-gray-700 border-gray-600'}`;
            iconElement.className = `icon w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold mr-3 ${iconData.class}`;
            iconElement.innerHTML = iconData.svg;

            if (status === 'failed') {
                errorElement.textContent = `Kesalahan pada Langkah ${stepId + 1}. ${errorMessage}`;
                errorElement.classList.remove('hidden');
            }
        }

        /** Menandai semua langkah setelah kegagalan. */
        function markStepsAsFailed(failedStepId, errorMessage) {
             for(let i = failedStepId; i < reviewStepsData.length; i++) {
                 updateReviewStepStatus(i, 'failed', errorMessage);
             }
        }


        // --- Logika Simulasi & Review ---

        /** Membuat instruksi sistem untuk Game Master AI. */
        function createSystemInstruction(subject, grade, topic) {
            return `Anda adalah 'Game Master' untuk simulasi pengajaran guru.
Peran Anda adalah mensimulasikan kelas ${grade} untuk mata pelajaran ${subject} dengan topik: ${topic}.
Tujuan utama Anda adalah mensimulasikan kondisi KBM (Kegiatan Belajar Mengajar) secara realistis, termasuk gangguan dan dinamika kelas.

**Wajib:** Format seluruh respons Anda menggunakan sintaks Markdown untuk kejelasan visual.

Anda harus melakukan empat hal, selalu dipisahkan oleh tanda bintang tiga (***):

1.  **Skenario/Pertanyaan Siswa & Gangguan Realistis:** a. Role-play sebagai 3 hingga 5 siswa dengan kepribadian berbeda (misalnya, '**Siswa A: Pintar dan antusias**', '**Siswa B: Bingung dan pemalu**', '**Siswa C: Terganggu atau bosan**', '**Siswa D: Kritis dan menantang**').
    b. Sampaikan pertanyaan, komentar, atau tindakan siswa, termasuk kesalahpahaman yang umum terjadi pada topik tersebut atau pertanyaan di luar topik. Format output untuk ini harus: **[NAMA SISWA]**: [Dialogue Siswa] (Gunakan bold untuk nama siswa)
    c. Secara berkala (setiap 3-4 giliran), perkenalkan GANGGUAN REALISTIS (misalnya, '*Ponsel Siswa D berdering nyaring*', '*Tiba-tiba ada ketukan keras di pintu*'). (Gunakan italic untuk gangguan)
2.  **Deskripsi Kelas:** Berikan deskripsi singkat tentang suasana kelas dan tingkat keterlibatan secara keseluruhan.
3.  **Umpan Balik Privat (Berkala):** Secara berkala (setiap 3-4 giliran), berikan **Umpan Balik Privat** yang konstruktif dan profesional. Fokuskan umpan balik pada: Manajemen Kelas (KBM), Kejelasan Materi, dan Keterlibatan. Gunakan *poin-poin* atau *paragraf terstruktur* dalam bagian ini.
4.  **Pilihan Aksi Guru (Output Format KHUSUS):** Berikan 3-5 opsi tindakan pedagogis yang dapat diambil guru untuk melanjutkan simulasi.
    Opsi HARUS dikeluarkan sebagai baris terakhir respons Anda, diawali dengan string unik:
    ${OPTION_DELIMITER} Pilihan Aksi 1|Pilihan Aksi 2|Pilihan Aksi 3|...

**ATURAN UTAMA:**
-   Selalu tunggu respons guru sebelum Anda membalas.
-   Tampilan respons Anda harus memisahkan 3 bagian (Skenario, Deskripsi, Umpan Balik) di atas dengan tanda bintang tiga (***).
-   Baris terakhir harus SELALU berisi string OPSI jika giliran guru berikutnya, dan tidak boleh ada teks lain setelahnya.
-   Pertahankan persona siswa/gangguan dan Game Master secara terpisah.
-   Gunakan bahasa Indonesia yang sesuai.`;
        }
        
        /** Memulai simulasi baru. */
        function startSimulation() {
            if (isProcessing) return;

            const subject = document.getElementById('input-subject').value.trim();
            const grade = document.getElementById('input-grade').value;
            const topic = document.getElementById('input-topic').value.trim();

            if (!subject || !topic) {
                displayStatus("Aktivitas KBM/Praktek dan Topik harus diisi.", 'error');
                return;
            }
            
            if (chatHistory.length > 0 && !confirm("Simulasi sedang berjalan. Apakah Anda yakin ingin memulai simulasi baru? Riwayat saat ini akan dihapus.")) {
                return;
            }
            
            handleStopNarrative();

            chatHistory = [];
            localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(chatHistory)); // Reset dan simpan
            
            document.getElementById('chat-container').innerHTML = '';
            clearActionButtons();
            
            document.getElementById('user-input').disabled = false;
            document.getElementById('send-button').disabled = false;
            document.getElementById('voice-button').disabled = false;
            document.getElementById('tts-play-btn').disabled = true; 
            document.getElementById('tts-stop-btn').disabled = true; 
            document.getElementById('start-button').textContent = 'Ulangi Simulasi';
            document.getElementById('review-button').classList.add('hidden'); 
            document.getElementById('user-input').placeholder = "Tuliskan respons pengajaran Anda di sini, atau tekan tombol Mikrofon untuk rekaman suara...";


            appendMessage('status', `Simulasi Baru Dimulai: ${subject} (${grade}) - Topik: ${topic}. Game Master AI akan menyiapkan kelas. Silakan tunggu...`);

            const systemInstruction = createSystemInstruction(subject, grade, topic);
            const initialPrompt = `Game Master, siapkan skenario kelas. Mulailah sesi dengan mengatur panggung dan kemudian mintalah guru untuk memberikan pernyataan pembuka mereka tentang topik: ${topic}.`;

            callGeminiApi(initialPrompt, systemInstruction);
            
            // Tutup panel pengaturan di mobile setelah memulai
            const panel = document.getElementById('settings-panel');
            if (!panel.classList.contains('lg:static')) {
                 setTimeout(toggleSettingsPanel, 300);
            }
        }
        
        /** Mengakhiri simulasi dan memulai proses peninjauan AI. */
        function endSimulationAndReview() {
            if (isProcessing) return;

            handleStopNarrative();

            const modelResponses = chatHistory.filter(msg => msg.role === 'model').length;
            if (modelResponses < 2) {
                 displayStatus("Simulasi harus memiliki minimal dua giliran Game Master sebelum dapat diulas.", 'info');
                 return;
            }

            if (!confirm("Apakah Anda yakin ingin mengakhiri simulasi dan memulai proses peninjauan oleh AI?")) {
                return;
            }

            setProcessing(true, 'menganalisis kinerja');
            clearActionButtons();
            document.getElementById('user-input').disabled = true;
            document.getElementById('send-button').disabled = true;
            document.getElementById('voice-button').disabled = true;
            document.getElementById('tts-play-btn').disabled = true;
            document.getElementById('tts-stop-btn').disabled = true;
            
            const subject = document.getElementById('input-subject').value.trim();
            const grade = document.getElementById('input-grade').value;
            const topic = document.getElementById('input-topic').value.trim();
            
            const reviewSystemInstruction = `Anda adalah seorang Konsultan Pendidikan dan Analis Kinerja Guru yang profesional. Tugas Anda adalah menganalisis riwayat obrolan simulasi pengajaran yang diberikan dan memberikan ulasan komprehensif.

Output Anda HARUS dalam format MARKDOWN yang terstruktur dengan judul, poin-poin, dan paragraf yang jelas.

Ulasan HARUS mencakup 3 area penilaian utama:
1. **Manajemen Kelas (Classroom Management):** Keterampilan mengelola gangguan, menangani siswa yang sulit/mengganggu, dan menjaga alur KBM.
2. **Kejelasan Materi (Content Clarity):** Keakuratan, kedalaman, dan kejelasan penjelasan guru mengenai Topik: ${topic}.
3. **Keterlibatan Siswa (Student Engagement):** Kemampuan guru untuk memotivasi, merespons pertanyaan, dan menarik partisipasi dari semua tipe siswa.

Sertakan bagian **Saran dan Tindak Lanjut** di akhir dengan setidaknya 3 poin perbaikan yang spesifik dan dapat ditindaklanjuti.
`;
            
            const reviewPrompt = `Mohon berikan ulasan kinerja Guru secara menyeluruh berdasarkan seluruh riwayat interaksi ini. Fokuskan analisis pada Manajemen Kelas, Kejelasan Materi, dan Keterlibatan Siswa. Selesaikan dengan saran perbaikan spesifik. Berikan ulasan sebagai laporan tunggal, jangan lanjutkan percakapan.`;

            callGeminiApiForReview(reviewPrompt, reviewSystemInstruction);
        }

        /** Mengirim pesan guru dan mendapatkan respons Game Master. */
        function sendUserMessage() {
            const userInput = document.getElementById('user-input');
            const rawUserMessage = userInput.value.trim();

            if (!rawUserMessage || isProcessing) return;

            handleStopNarrative();

            if (isRecording && recognition) {
                recognition.stop();
            }

            clearActionButtons();
            
            appendMessage('user', rawUserMessage); 

            userInput.value = '';
            
            const userMessage = `Guru merespons dengan: "${rawUserMessage}". Lanjutkan simulasi.`;

            const subject = document.getElementById('input-subject').value.trim();
            const grade = document.getElementById('input-grade').value;
            const topic = document.getElementById('input-topic').value.trim();
            const systemInstruction = createSystemInstruction(subject, grade, topic);

            callGeminiApi(userMessage, systemInstruction);
        }
        
        // --- Fungsi Download & Modal ---
        
        function closeReviewModal() {
            document.getElementById('review-modal').classList.add('hidden');
        }

        function displayReview(reviewContent) {
            lastReviewContent = reviewContent;
            const reviewHtml = markdownToHtml(reviewContent);
            document.getElementById('review-content').innerHTML = reviewHtml;
            document.getElementById('review-modal').classList.remove('hidden');
            
            document.getElementById('review-button').classList.add('hidden');
            document.getElementById('user-input').disabled = true;
            document.getElementById('send-button').disabled = true;
            document.getElementById('voice-button').disabled = true;
            document.getElementById('tts-play-btn').disabled = true;
            document.getElementById('tts-stop-btn').disabled = true;
        }
        
        function downloadReview(content, filename) {
            const element = document.createElement('a');
            // Menghapus tag HTML dan konversi <br> ke \n untuk plain text
            const plainTextContent = content.replace(/<br\s*\/?>/gi, '\n').replace(/<\/?(strong|em|h3|ul|li)\/?>/gi, '').replace(/###/g, ''); 
            
            const file = new Blob([plainTextContent], {type: 'text/plain'});
            element.href = URL.createObjectURL(file);
            element.download = filename;
            document.body.appendChild(element); 
            element.click();
            document.body.removeChild(element);
        }
        
        function downloadDisplayedReview() {
            if (lastReviewContent) {
                const topic = document.getElementById('input-topic').value.trim().replace(/[^a-zA-Z0-9]/g, '_');
                downloadReview(lastReviewContent, `Laporan_Review_Simulasi_${topic}.txt`);
            } else {
                console.log("Tidak ada konten ulasan untuk diunduh.");
            }
        }


        // --- Integrasi Gemini API dengan Backoff Eksponensial ---

        async function callGeminiApiForReview(userPrompt, reviewSystemInstruction, maxRetries = 5) {
             
             updateReviewStepStatus(0, 'active'); // 1. Mengumpulkan & Mengirim Riwayat Chat
             
             const reviewPayload = {
                contents: [...chatHistory, { role: "user", parts: [{ text: userPrompt }] }], 
                systemInstruction: {
                    parts: [{ text: reviewSystemInstruction }] 
                },
            };
            
            updateReviewStepStatus(0, 'completed'); // INSTANT: Mengumpulkan & Mengirim Selesai
            
            updateReviewStepStatus(1, 'active'); // 2. Model AI Menganalisis Kinerja Guru (Dimulai - ini yang lama)

            const apiUrlWithKey = `${API_URL}?key=${apiKey}`;
            
            for (let i = 0; i < maxRetries; i++) {
                try {
                    
                    const response = await fetch(apiUrlWithKey, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(reviewPayload)
                    });

                    if (!response.ok) {
                        if (response.status === 429 && i < maxRetries - 1) {
                            const delay = Math.pow(2, i) * 1000;
                            await new Promise(resolve => setTimeout(resolve, delay));
                            continue; 
                        }
                        throw new Error(`Kesalahan HTTP: ${response.status}`);
                    }

                    const result = await response.json();
                    
                    const candidate = result.candidates?.[0];
                    if (candidate && candidate.content?.parts?.[0]?.text) {
                        updateReviewStepStatus(1, 'completed'); // Selesai Analisis

                        updateReviewStepStatus(2, 'active'); // 3. Mempersiapkan dan Menampilkan Laporan
                        const reviewText = candidate.content.parts[0].text;
                        displayReview(reviewText);
                        updateReviewStepStatus(2, 'completed'); 
                        
                        setProcessing(false);
                        return;

                    } else {
                        const blockReason = result.promptFeedback?.blockReason;
                        if (blockReason) {
                             throw new Error(`Konten diblokir oleh filter keamanan AI. Alasan: ${blockReason}.`);
                        }
                        console.error("Gemini Review API returned an unexpected response structure or empty content:", result); 
                        throw new Error("Respons AI tidak valid atau kosong.");
                    }

                } catch (error) {
                    console.error("Kesalahan dalam panggilan API Gemini untuk Review:", error);
                    
                    // Mark step 1 (Analysis) and subsequent steps as failed
                    markStepsAsFailed(1, error.message); 
                    
                    if (i === maxRetries - 1) {
                        displayStatus(`Gagal membuat ulasan setelah ${maxRetries} percobaan: ${error.message}.`, 'error');
                        setProcessing(false);
                    } else {
                         const delay = Math.pow(2, i) * 1000;
                         await new Promise(resolve => setTimeout(resolve, delay));
                    }
                }
            }
        }

        async function callGeminiApi(userPrompt, systemInstruction, maxRetries = 5) {
            setProcessing(true, 'memproses');
            
            const userPayload = { role: "user", parts: [{ text: userPrompt }] };
            chatHistory.push(userPayload);
            
            const payload = {
                contents: chatHistory, 
                systemInstruction: {
                    parts: [{ text: systemInstruction }]
                },
            };

            const apiUrlWithKey = `${API_URL}?key=${apiKey}`;
            let success = false;

            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(apiUrlWithKey, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        if (response.status === 429 && i < maxRetries - 1) {
                            const delay = Math.pow(2, i) * 1000;
                            await new Promise(resolve => setTimeout(resolve, delay));
                            continue;
                        }
                        throw new Error(`Kesalahan HTTP: ${response.status}`);
                    }

                    const result = await response.json();
                    
                    const candidate = result.candidates?.[0];
                    if (candidate && candidate.content?.parts?.[0]?.text) {
                        let responseText = candidate.content.parts[0].text;
                        
                        chatHistory.push({ role: "model", parts: [{ text: responseText }] });
                        saveChatHistory(); 
                        
                        const optionIndex = responseText.lastIndexOf(OPTION_DELIMITER);
                        let narrativeText = responseText;
                        let options = [];

                        if (optionIndex !== -1) {
                            // Pisahkan narasi dari opsi
                            narrativeText = responseText.substring(0, optionIndex).trim();
                            const optionsString = responseText.substring(optionIndex + OPTION_DELIMITER.length).trim(); 
                            options = optionsString.split('|').map(o => o.trim()).filter(o => o.length > 0);
                        }
                        
                        // Perbarui narasi saat ini, penting untuk TTS
                        currentNarrationText = narrativeText; 
                        
                        appendMessage('model', narrativeText, true); 
                        
                        document.getElementById('tts-play-btn').disabled = false;
                        document.getElementById('tts-stop-btn').disabled = true;

                        if (options.length > 0) {
                            renderActionButtons(options);
                        } else {
                            clearActionButtons();
                        }
                        
                        document.getElementById('review-button').classList.remove('hidden'); 
                        
                        success = true;
                        break; 

                    } else {
                        const blockReason = result.promptFeedback?.blockReason;
                        if (blockReason) {
                             throw new Error(`Konten diblokir oleh filter keamanan AI. Alasan: ${blockReason}. Coba formulasi ulang respons guru.`);
                        }
                        console.error("Gemini API returned an unexpected response structure or empty content:", result); 
                        throw new Error("Respons AI tidak valid atau kosong. (Cek konsol untuk detail respons mentah.)");
                    }

                } catch (error) {
                    console.error("Kesalahan dalam panggilan API Gemini:", error);
                    
                    if (i === maxRetries - 1) {
                        displayStatus(`Gagal menghubungi AI setelah ${maxRetries} percobaan: ${error.message}`, 'error');
                        if (chatHistory.length > 0 && chatHistory[chatHistory.length - 1] === userPayload) {
                            chatHistory.pop();
                            saveChatHistory();
                        }
                    } else {
                         const delay = Math.pow(2, i) * 1000;
                         await new Promise(resolve => setTimeout(resolve, delay));
                    }
                }
            }
            
            setProcessing(false);
        }

        // =============================================================
        // LOGIKA TTS (Web Speech API)
        // =============================================================

        /** Mencari suara Bahasa Indonesia yang tersedia di browser. */
        function getIndonesianVoice(voices) {
            const indonesianVoices = voices.filter(voice => voice.lang.startsWith('id'));
            
            if (indonesianVoices.length > 0) {
                return indonesianVoices.find(voice => voice.default) || indonesianVoices[0];
            }
            
            displayTtsStatus("Peringatan: Suara Bahasa Indonesia tidak tersedia. Menggunakan suara default sistem.");
            return voices.find(voice => voice.default) || voices[0];
        }

        /** Menginisialisasi TTS dan menetapkan voice. */
        function initializeTTS() {
            if ('speechSynthesis' in window) {
                synth = window.speechSynthesis;
                
                const loadVoices = () => {
                    const voices = synth.getVoices();
                    if (voices.length > 0) {
                        selectedVoice = getIndonesianVoice(voices);
                        const statusMessage = selectedVoice && selectedVoice.lang.startsWith('id')
                            ? `TTS Siap. Suara ID: ${selectedVoice.name}`
                            : `TTS Siap (Suara ID tidak ditemukan). Menggunakan: ${selectedVoice ? selectedVoice.name : 'Default'}`;
                        displayTtsStatus(statusMessage);
                        synth.onvoiceschanged = null; 
                    }
                };

                if (synth.getVoices().length > 0) {
                    loadVoices();
                } else {
                    synth.onvoiceschanged = loadVoices;
                }

            } else {
                displayTtsStatus("TTS ERROR: Web Speech API tidak didukung browser ini.");
                document.getElementById('tts-play-btn').disabled = true;
                document.getElementById('tts-stop-btn').disabled = true;
            }
        }
        
        /** Memulai pemutaran narasi Game Master saat ini. */
        function handlePlayNarrative() {
            if (isProcessing || !currentNarrationText || !synth || synth.speaking || isTtsCancelling) return;
            
            // Hapus penanda Markdown sebelum diputar (**) (*)
            const textToSpeak = currentNarrationText.replace(/\*\*/g, '').replace(/\*/g, ''); 
            const utterance = new SpeechSynthesisUtterance(textToSpeak);
            
            utterance.lang = targetLang;
            if (selectedVoice) {
                utterance.voice = selectedVoice;
            }
            utterance.pitch = 1.0;
            utterance.rate = 1.0; 

            utterance.onstart = () => {
                displayTtsStatus("Memutar Narasi Game Master...");
                document.getElementById('tts-play-btn').disabled = true;
                document.getElementById('tts-stop-btn').disabled = false;
            };

            utterance.onend = () => {
                if (!isTtsCancelling) {
                    displayTtsStatus(`Selesai memutar. Suara: ${selectedVoice ? selectedVoice.name : 'Default'}`);
                    document.getElementById('tts-play-btn').disabled = false;
                    document.getElementById('tts-stop-btn').disabled = true;
                }
            };
            
            utterance.onerror = (event) => {
                console.error('SpeechSynthesis Utterance Error:', event.error);
                displayTtsStatus(`TTS ERROR: Gagal memutar suara (${event.error}).`);
                document.getElementById('tts-play-btn').disabled = false;
                document.getElementById('tts-stop-btn').disabled = true;
            };

            synth.speak(utterance);
        }

        /** Menghentikan pemutaran TTS. */
        function handleStopNarrative() {
            if (synth && synth.speaking) {
                
                isTtsCancelling = true; 
                setTimeout(() => {
                    isTtsCancelling = false;
                }, 100); 

                synth.cancel();
                displayTtsStatus(`Pemutaran dihentikan. Suara: ${selectedVoice ? selectedVoice.name : 'Default'}`);
                
                if(currentNarrationText) {
                    document.getElementById('tts-play-btn').disabled = false;
                }
                document.getElementById('tts-stop-btn').disabled = true;
            }
        }

        // =============================================================
        // LOGIKA VOICE RECOGNITION (ASR)
        // =============================================================

        function setupVoiceRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            if (SpeechRecognition) {
                recognition = new SpeechRecognition();
                recognition.continuous = false; 
                recognition.interimResults = false;
                recognition.lang = 'id-ID'; 
                
                const voiceButton = document.getElementById('voice-button');

                recognition.onstart = function() {
                    isRecording = true;
                    handleStopNarrative(); 
                    voiceButton.classList.remove('bg-red-500', 'hover:bg-red-400');
                    voiceButton.classList.add('bg-green-500', 'animate-pulse');
                    document.getElementById('user-input').placeholder = "Mendengarkan... Silakan Bicara.";
                    document.getElementById('send-button').disabled = true; 
                };

                recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript;
                    document.getElementById('user-input').value = transcript;
                    sendUserMessage();
                };

                recognition.onerror = function(event) {
                    console.error('Speech Recognition Error:', event.error);
                    
                    if (event.error === 'audio-capture') {
                        displayStatus("Error Pengenalan Suara: Gagal mengakses mikrofon. Harap pastikan mikrofon Anda terhubung dan izin akses mikrofon browser telah diberikan.", 'error');
                    } else if (event.error !== 'no-speech' && event.error !== 'aborted') { 
                        displayStatus(`Error Pengenalan Suara: ${event.error}.`, 'error');
                    }
                    recognition.onend();
                };

                recognition.onend = function() {
                    isRecording = false;
                    voiceButton.classList.remove('bg-green-500', 'animate-pulse');
                    voiceButton.classList.add('bg-red-500', 'hover:bg-red-400');
                    document.getElementById('user-input').placeholder = "Tuliskan respons pengajaran Anda di sini, atau tekan tombol Mikrofon untuk rekaman suara...";
                    
                    if (!document.getElementById('user-input').disabled) {
                         document.getElementById('send-button').disabled = false;
                    }
                };

            } else {
                window.onload = function() {
                    document.getElementById('voice-button').disabled = true;
                    document.getElementById('user-input').placeholder = "Tuliskan respons pengajaran Anda di sini (Pengenalan Suara tidak didukung).";
                    displayStatus("Peringatan: Browser Anda tidak mendukung Web Speech API (ASR). Fitur Rekam Suara dinonaktifkan.", 'info');
                };
            }
        }

        function toggleVoiceRecognition() {
            if (!recognition || isProcessing || document.getElementById('user-input').disabled || isTtsCancelling) return;

            if (isRecording) {
                recognition.stop();
            } else {
                document.getElementById('user-input').value = '';
                
                try {
                    recognition.start();
                } catch (e) {
                    console.error("Gagal memulai pengenalan suara:", e);
                    displayStatus("Gagal memulai rekaman. Pastikan Anda memberikan izin mikrofon.", 'error');
                    isRecording = false;
                    if(recognition && recognition.onend) recognition.onend();
                }
            }
        }
        
        document.getElementById('user-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendUserMessage();
            }
        });
        
        window.onload = function() {
            setupVoiceRecognition(); 
            initializeTTS(); 
            const historyLoaded = loadChatHistory();
            
            const voiceButton = document.getElementById('voice-button');
            const ttsPlayButton = document.getElementById('tts-play-btn');

            if (historyLoaded) {
                document.getElementById('start-button').textContent = 'Ulangi Simulasi';
                document.getElementById('review-button').classList.remove('hidden');
            } else {
                 document.getElementById('chat-container').innerHTML = `
                    <div class="message-status p-3 rounded-lg">
                        <p class="font-bold">Selamat datang!</p>
                        <p class="text-sm">Atur mata pelajaran, kelas, dan topik Anda di panel Pengaturan (toggle di pojok kanan atas untuk mobile) dan klik "Mulai Simulasi" untuk memulai sesi mengajar Anda.</p>
                    </div>
                `;
            }
            
            if (!isProcessing) {
                document.getElementById('user-input').disabled = false;
                document.getElementById('send-button').disabled = false;
                if (voiceButton && recognition) { 
                    voiceButton.disabled = false;
                }
                if (currentNarrationText) {
                    ttsPlayButton.disabled = false;
                }
            }
        };

        window.startSimulation = startSimulation;
        window.endSimulationAndReview = endSimulationAndReview;
        window.closeReviewModal = closeReviewModal;
        window.downloadDisplayedReview = downloadDisplayedReview;
        window.toggleVoiceRecognition = toggleVoiceRecognition;
        window.sendUserMessage = sendUserMessage;
        window.handlePlayNarrative = handlePlayNarrative;
        window.handleStopNarrative = handleStopNarrative;
        window.toggleSettingsPanel = toggleSettingsPanel;
        window.resetSimulationHistory = resetSimulationHistory; 

    </script>
</body>
</html>
