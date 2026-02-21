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
<html lang="id" class="">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SMK Role-Play Interaktif</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
/* BODY & GENERAL */
body {
    font-family: 'Inter', sans-serif;
    background-color: #f5f6fa;
    color: #111827;
    transition: all 0.3s ease;
}
.dark body {
    background-color: #1f2937;
    color: #e5e7eb;
}

/* APP CARD */
.app-card {
    background-color: white;
    border-radius: 20px;
    padding: 40px;
    width: 100%;
    max-width: 1200px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.dark .app-card {
    background-color: #111827;
    color: #e5e7eb;
}

/* BANNER & LOGO */
.app-card img {
    transition: all 0.3s ease;
}
.dark .app-card img {
    filter: brightness(0.9);
}

/* HEADER */
header h1, header p {
    transition: all 0.3s ease;
}
.dark header h1 { color: #f3f4f6; }
.dark header p { color: #d1d5db; }

/* SCENARIO SELECTOR */
#scenario-selector {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}
.dark #scenario-selector {
    background-color: #1f2937;
    border-color: #374151;
}
#scenario-type {
    background-color: white;
    color: black;
}
.dark #scenario-type {
    background-color: #374151;
    color: #f9fafb;
}

/* CHAT AREA */
.chat-container {
    background-color: white;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}
.dark .chat-container {
    background-color: #1f2937;
    border-color: #374151;
}

/* SCROLLBAR */
.chat-container::-webkit-scrollbar { width: 6px; }
.chat-container::-webkit-scrollbar-thumb { background: #c0c0c0; border-radius: 3px; }
.dark .chat-container::-webkit-scrollbar-thumb { background: #6b7280; }
.chat-container::-webkit-scrollbar-track { background: transparent; }

/* INPUT */
#user-input {
    background-color: white;
    color: black;
}
.dark #user-input {
    background-color: #374151;
    color: #f9fafb;
}

/* BUTTONS */
.btn-base {
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.btn-primary { background-color: #4f46e5; color: white; }
.btn-primary:hover { background-color: #4338ca; }
.dark .btn-primary { background-color: #6366f1; color: white; }
.dark .btn-primary:hover { background-color: #4f46e5; }

.btn-secondary { background-color: #e5e7eb; color: #111827; }
.btn-secondary:hover { background-color: #d1d5db; }
.dark .btn-secondary { background-color: #374151; color: #f9fafb; }
.dark .btn-secondary:hover { background-color: #4b5563; }

.btn-yellow { background-color: #f59e0b; color: white; }
.btn-yellow:hover { background-color: #d97706; }
.dark .btn-yellow { background-color: #b45309; }

.btn-red { background-color: #ef4444; color: white; }
.btn-red:hover { background-color: #dc2626; }
.dark .btn-red { background-color: #b91c1c; }

.btn-green { background-color: #10b981; color: white; }
.btn-green:hover { background-color: #059669; }
.dark .btn-green { background-color: #059669; }

/* MODAL */
.modal-overlay {
    position: fixed; top:0; left:0; width:100%; height:100%;
    background-color: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
    display:flex; justify-content:center; align-items:center; z-index:1000;
    transition: all 0.3s ease;
}
.dark .modal-overlay { background-color: rgba(0,0,0,0.3); }
.modal-content {
    background-color: rgba(255,255,255,0.95);
    border-radius: 16px; padding:30px; width:90%; max-width:500px;
    box-shadow:0 10px 20px rgba(0,0,0,0.15);
    border:1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.dark .modal-content { background-color: rgba(31,41,55,0.95); color: #e5e7eb; }

/* LOADING INDICATOR */
@keyframes pulse-dot {0%,100%{opacity:0.2;}50%{opacity:1;}}
.dot { display:inline-block; width:6px; height:6px; margin:0 1px; background-color:#6366f1; border-radius:50%; animation:pulse-dot 1.5s infinite ease-in-out; }
.dot:nth-child(2){animation-delay:0.5s;}
.dot:nth-child(3){animation-delay:1s;}
.loading-indicator-box{animation: soft-pulse-glow 1.5s infinite ease-in-out;}
@keyframes soft-pulse-glow{0%{transform:scale(1);opacity:0.8;}50%{transform:scale(1.01);opacity:1;}100%{transform:scale(1);opacity:0.8;}}

/* TRANSITIONS */
* { transition: all 0.3s ease; }
.dark .select2-container--default .select2-selection--single {
    background-color: #1f2937; /* Hitam abu-abu gelap */
    color: #ffffff; /* Teks putih */
    border: 1px solid #4b5563; /* Border gelap */
    border-radius: 0.75rem; /* rounded-xl */
}

/* Placeholder */
.dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #d1d5db; /* Abu-abu terang */
}

/* Dropdown options */
.dark .select2-container--default .select2-results__option {
    background-color: #1f2937; /* Dark bg */
    color: #ffffff; /* Teks putih */
}

/* Option terpilih / highlight */
.dark .select2-container--default .select2-results__option--highlighted {
    background-color: #4f46e5; /* Highlight biru */
    color: #ffffff; /* Teks tetap putih */
}

/* Selected item di select box */
.dark .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #ffffff;
}
.btn-base {
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
    text-white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.2s ease-out;
}

/* Gradient Yellow / Amber */
.btn-yellow-gradient {
    background: linear-gradient(90deg, #facc15, #fbbf24);
    color: white;
}
.btn-yellow-gradient:hover {
    background: linear-gradient(90deg, #fbbf24, #f59e0b);
}

/* Gradient Red / Rose */
.btn-red-gradient {
    background: linear-gradient(90deg, #ef4444, #f87171);
    color: white;
}
.btn-red-gradient:hover {
    background: linear-gradient(90deg, #f87171, #dc2626);
}

</style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

<!-- Theme Toggle -->
<div class="absolute top-4 right-4">
    <button id="theme-toggle" class="btn-base btn-secondary">
        <i class="fas fa-moon mr-2"></i>
    </button>
</div>

<!-- Main Card -->
<div class="app-card">
<div class="relative w-full mb-6">
    <img src="../admin/foto/<?= $data['banner'] ?>" alt="Banner Sekolah" class="w-full h-40 md:h-48 object-cover rounded-xl shadow-lg">
    <img src="../admin/foto/<?= $data['logo'] ?>" alt="Logo Sekolah" 
         class="absolute left-6 top-1/2 transform -translate-y-1/2 w-20 h-20 md:w-28 md:h-28 object-contain rounded-full border-4 border-white shadow-xl">
</div>

<header class="text-center mb-8">
    <h1 class="text-3xl md:text-4xl font-extrabold flex items-center justify-center mb-1">
        <i class="fas fa-school text-3xl md:text-4xl mr-3 text-indigo-500"></i> Role-Play SMK Interaktif
    </h1>
    <p class="text-md text-gray-500 dark:text-gray-300">Latih keterampilan profesional Anda dengan simulasi AI.</p>
</header>

<!-- Scenario Selector -->
<div id="scenario-selector" class="mb-8 p-6 rounded-xl border space-y-4">
    <label class="block text-lg font-bold">Pilih atau Buat Skenario:</label>
    
    <select id="scenario-type" class="select2 w-full">
        <option value="" disabled selected>-- Pilih Skenario Tersedia --</option>
        <option>Staf Pemasaran - Negosiasi Klien</option>
        <option>Teknisi Komputer - Pelanggan Marah</option>
        <option>Pelayanan Hotel - Keluhan Tamu</option>
        <!-- Tema tambahan menarik -->
<option>Customer Service E-Commerce - Retur Barang dan Refund</option>
<option>Perawat UGD - Menenangkan Pasien Gelisah</option>
<option>Guru/Konselor - Menangani Siswa Bermasalah</option>
<option>Waiter/Barista - Pelanggan Protes Pesanan</option>
<option>IT Support - Troubleshooting Remote</option>
<option>Manajer Proyek - Menyelesaikan Konflik Tim</option>
<option>Receptionist - Menangani Reservasi dan Keluhan</option>
<option>HR - Interview Kandidat</option>
<option>Penjual Online - Chatting dengan Pelanggan Sulit</option>
<!-- Skenario SMK - Tema Komplain -->
<option>TKR - Mekanik: Menangani Komplain Pelanggan tentang Kendaraan Tidak Berfungsi</option>
<option>TKR - Mekanik: Menjelaskan Perbaikan yang Telah Dilakukan kepada Pelanggan</option>

<option>TBSM - Customer Service: Menangani Keluhan Pelanggan tentang Produk atau Layanan</option>
<option>TBSM - Marketing: Negosiasi Komplain Klien dan Penawaran Solusi</option>

<option>TKJ - IT Support: Menangani Komplain User tentang Jaringan atau Software</option>
<option>TKJ - Programmer: Memperbaiki Bug dan Menjawab Keluhan User</option>
<option>TKJ - Cyber Security: Mengatasi Komplain Serangan Malware / Phishing</option>

<option>ATPH - Agribisnis: Menangani Keluhan Petani tentang Hama atau Penyakit Tanaman</option>
<option>ATPH - Agribisnis: Menjelaskan Cara Mengatasi Masalah Irigasi atau Panen</option>
<option>ATPH - Agribisnis: Negosiasi Keluhan Harga Hasil Panen dengan Pedagang</option>

<option>Akuntansi - Pembukuan: Memperbaiki Kesalahan Transaksi dan Menjawab Keluhan Klien</option>
<option>Akuntansi - Customer Service: Menangani Keluhan Pelanggan tentang Faktur</option>
<option>Akuntansi - Audit Internal: Menangani Keluhan dan Pertanyaan Auditor</option>


    </select>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button onclick="startScenario()" class="w-full btn-base btn-primary">
            <i class="fas fa-play mr-2"></i> Mulai Skenario
        </button>
        <button onclick="openCustomScenarioModal()" class="w-full btn-base btn-secondary">
            <i class="fas fa-edit mr-2"></i> Buat Kustom
        </button>
    </div>
</div>
<!-- Chat Area -->
<div id="chat-area" class="hidden">
   <!-- Current Scenario Display -->
<p id="current-scenario-display" 
   class="text-center text-sm font-semibold mb-4 p-3 rounded-lg border
          bg-gray-100 text-gray-800 border-gray-300
          dark:bg-gray-800 dark:text-white dark:border-gray-600">
</p>

<!-- Message Container -->
<div id="message-container" 
     class="chat-container p-4 rounded-xl mb-4 border shadow-inner
            bg-white text-gray-900 border-gray-300
            dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700
            max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-800">
</div>

<!-- Status Message -->
<div id="status-message" 
     class="text-center text-sm mb-4 hidden p-3 rounded-xl font-semibold border loading-indicator-box
            bg-indigo-100 text-indigo-700 border-indigo-300
            dark:bg-indigo-800 dark:text-indigo-100 dark:border-indigo-600">
    AI sedang berpikir<span class="dot"></span><span class="dot"></span><span class="dot"></span>
</div>


    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 items-center">
        <div class="flex items-center space-x-2 col-span-1">
            <input type="checkbox" id="auto-voice-toggle" class="form-checkbox h-5 w-5" onclick="toggleAutoVoice()">
            <label for="auto-voice-toggle" class="text-sm">Bicara Otomatis (AI)</label>
        </div>
       <button id="voice-input-button" 
        class="btn-base col-span-1
               bg-purple-600 text-white 
               hover:bg-purple-700 active:bg-purple-800 
               dark:bg-purple-700 dark:hover:bg-purple-600 dark:active:bg-purple-800"
        onclick="startVoiceRecognition()">
    <i class="fas fa-microphone mr-2"></i> Input Suara
</button>

        <div class="col-span-1"></div>
    </div>

    <div class="flex space-x-3 mb-4">
        <input id="user-input" class="flex-grow p-3 border-2 rounded-xl" placeholder="Ketik tanggapan Anda..." onkeydown="if(event.key==='Enter') sendMessage()">
        <button id="send-button" class="btn-base w-14 h-14 disabled:opacity-50" disabled onclick="sendMessage()"><i class="fas fa-paper-plane text-xl"></i></button>
    </div>

    <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-3">
    <!-- Feedback Button -->
    <button id="feedback-button" 
            class="w-full md:w-1/2 btn-base btn-yellow-gradient disabled:opacity-50 disabled:cursor-not-allowed transform transition-all duration-200 hover:scale-105 hover:shadow-lg"
            disabled 
            onclick="requestFeedback()">
        <i class="fas fa-chart-line mr-2"></i> Minta Feedback
    </button>

    <!-- Reset Button -->
    <button class="w-full md:w-1/2 btn-base btn-red-gradient transform transition-all duration-200 hover:scale-105 hover:shadow-lg"
            onclick="resetApp()">
        <i class="fas fa-redo mr-2"></i> Skenario Baru
    </button>
</div>
    <div id="feedback-result" class="hidden mt-8 p-6 rounded-xl shadow-md"></div>
</div>
</div>

<!-- Modal -->
<div id="custom-scenario-modal" class="modal-overlay hidden">
    <div class="modal-content">
        <h2 class="text-2xl font-bold mb-4 flex items-center"><i class="fas fa-lightbulb mr-3 text-indigo-600"></i> Buat Skenario Kustom</h2>
        <p class="text-sm mb-4">Jelaskan peran Anda, AI, dan situasi.</p>
        <textarea id="custom-scenario-input" rows="7" class="w-full p-3 rounded-lg resize-none"></textarea>
        <div class="flex justify-end space-x-3 mt-6">
            <button onclick="closeCustomScenarioModal()" class="btn-base btn-secondary">Batal</button>
            <button onclick="startCustomScenario()" class="btn-base btn-primary"><i class="fas fa-check mr-2"></i> Mulai Simulasi</button>
        </div>
    </div>
</div>

<!-- Dark Mode Script -->
<script>
const themeToggleBtn = document.getElementById('theme-toggle');
const htmlEl = document.documentElement;
themeToggleBtn.addEventListener('click', () => {
    htmlEl.classList.toggle('dark');
    localStorage.setItem('theme', htmlEl.classList.contains('dark') ? 'dark' : 'light');
});
if(localStorage.getItem('theme') === 'dark') htmlEl.classList.add('dark');


$(document).ready(function() {
    $('#scenario-type').select2({
        placeholder: "-- Pilih Skenario Tersedia --",
        allowClear: true,
        width: '100%'  // Supaya responsif dengan Tailwind
    });
});
// --- GLOBAL CONFIGURATION AND UTILITIES ---
const GEMINI_MODEL = "<?php echo $model; ?>";
const API_KEY = "<?php echo $apiKey; ?>"; // Canvas will provide this key at runtime
const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${API_KEY}`;
const MAX_RETRIES = 5;

let chatHistory = [];
let currentScenario = '';
let autoVoiceEnabled = false; 
let recognition; 
let isRecognizing = false;

// DOM Elements
const messageContainer = document.getElementById('message-container');
const userInput = document.getElementById('user-input');
const sendButton = document.getElementById('send-button');
const feedbackButton = document.getElementById('feedback-button');
const feedbackResult = document.getElementById('feedback-result');
const statusMessage = document.getElementById('status-message');
const voiceInputButton = document.getElementById('voice-input-button');
const customScenarioModal = document.getElementById('custom-scenario-modal');
const customScenarioInput = document.getElementById('custom-scenario-input');
const currentScenarioDisplay = document.getElementById('current-scenario-display');

// --- MODAL FUNCTIONS ---

function openCustomScenarioModal() {
    customScenarioModal.classList.remove('hidden');
    customScenarioInput.focus();
}

function closeCustomScenarioModal() {
    customScenarioModal.classList.add('hidden');
}

/**
 * Starts the scenario using the custom text input from the modal.
 */
function startCustomScenario() {
    const customText = customScenarioInput.value.trim();
    if (customText.length < 20) {
        appendMessage('system', 'Deskripsi skenario kustom terlalu pendek (min 20 karakter). Mohon jelaskan peran dan situasi dengan lebih detail.');
        return;
    }
    closeCustomScenarioModal();
    currentScenario = 'Skenario Kustom: ' + customText; 
    initializeScenario();
}


// --- VOICE FUNCTIONS ---

/**
 * Toggles the state of automatic Text-to-Speech for AI responses.
 */
function toggleAutoVoice() {
    autoVoiceEnabled = document.getElementById('auto-voice-toggle').checked;
    if (!autoVoiceEnabled) {
        window.speechSynthesis.cancel();
    }
}

/**
 * Speak text with Web Speech API
 */
function speakText(text) {
    if (!autoVoiceEnabled) return;
    if (!('speechSynthesis' in window)) return;

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'id-ID';
    utterance.pitch = 1.0;
    utterance.rate = 0.95;

    const setVoiceAndSpeak = () => {
        const voices = window.speechSynthesis.getVoices();
        const indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google")) 
                         || voices.find(v => v.lang === 'id-ID');
        if (indoVoice) utterance.voice = indoVoice;
        window.speechSynthesis.speak(utterance);
    };

    if (window.speechSynthesis.getVoices().length > 0) {
        setVoiceAndSpeak();
    } else {
        window.speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
    }
}

/**
 * Sets up the Web Speech Recognition API.
 */
function setupVoiceRecognition() {
    if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        
        recognition.continuous = false; 
        recognition.lang = 'id-ID'; 
        recognition.interimResults = false;

        recognition.onstart = () => {
            isRecognizing = true;
            voiceInputButton.classList.remove('bg-purple-600', 'hover:bg-purple-700', 'active:bg-purple-800');
            voiceInputButton.classList.add('bg-red-600', 'hover:bg-red-700', 'active:bg-red-800');
            voiceInputButton.innerHTML = '<i class="fas fa-microphone-alt-slash mr-2"></i> Berhenti Bicara...';
            statusMessage.textContent = 'Mendengarkan... (klik tombol untuk berhenti atau tunggu)';
            statusMessage.classList.remove('hidden');
        };

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            userInput.value = transcript;
            stopVoiceRecognition();
            if (transcript.trim()) {
                sendMessage(); 
            }
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            stopVoiceRecognition();
            if (event.error === 'not-allowed') {
                 appendMessage('system', 'Error: Akses mikrofon ditolak. Mohon izinkan akses mikrofon untuk input suara.');
            } else if (event.error !== 'aborted' && event.error !== 'no-speech') {
                appendMessage('system', 'Error input suara: ' + event.error);
            }
        };
        
        recognition.onend = () => {
            if (!sendButton.disabled) {
                statusMessage.classList.add('hidden');
            }
            isRecognizing = false;
            voiceInputButton.classList.remove('bg-red-600', 'hover:bg-red-700', 'active:bg-red-800');
            voiceInputButton.classList.add('bg-purple-600', 'hover:bg-purple-700', 'active:bg-purple-800');
            voiceInputButton.innerHTML = '<i class="fas fa-microphone text-lg mr-2"></i> Input Suara';
        };

    } else {
        voiceInputButton.disabled = true;
        voiceInputButton.innerHTML = '<i class="fas fa-microphone-slash mr-2"></i> Suara Tdk Didukung';
        console.warn('Speech Recognition API not supported in this browser.');
    }
}

/**
 * Starts or stops the voice recognition process.
 */
function startVoiceRecognition() {
    if (isRecognizing) {
        stopVoiceRecognition();
    } else if (recognition) {
        window.speechSynthesis.cancel(); 
        recognition.start();
    }
}

function stopVoiceRecognition() {
    if (isRecognizing && recognition) {
        recognition.stop();
    }
}


// --- HELPER FUNCTIONS ---

/**
 * Determines the AI's role based on the current scenario selection.
 */
function getModelRole() {
    if (currentScenario.includes('Pemasaran')) return 'Klien';
    if (currentScenario.includes('Komputer')) return 'Pelanggan';
    if (currentScenario.includes('Hotel')) return 'Tamu Hotel';
    return 'Lawan Bicara AI'; 
}

/**
 * Appends a message to the chat display with role indicators.
 */
function appendMessage(role, text) {
    const isUser = role === 'user';
    const isSystem = role === 'system';
    
    const div = document.createElement('div');
    div.classList.add('flex', 'mb-3', isUser ? 'justify-end' : 'justify-start');
    
    const bubbleContainer = document.createElement('div');
    bubbleContainer.classList.add('chat-message', 'rounded-xl', 'shadow-sm', 'p-3');

    const roleLabel = document.createElement('p');
    roleLabel.classList.add('font-bold', 'text-xs', 'mb-1');

    if (isUser) {
        roleLabel.textContent = 'Anda (Siswa)';
        roleLabel.classList.add('text-indigo-200');
        bubbleContainer.classList.add('bg-indigo-600', 'text-white', 'hover:scale-[1.01]');
    } else if (isSystem) {
        roleLabel.textContent = 'Sistem';
        roleLabel.classList.add('text-red-700');
        bubbleContainer.classList.add('bg-red-100', 'text-red-700', 'border', 'border-red-300');
    } else { // model
        roleLabel.textContent = getModelRole();
        roleLabel.classList.add('text-gray-500');
        bubbleContainer.classList.add('bg-gray-100', 'text-gray-800', 'border', 'border-gray-200', 'hover:scale-[1.01]');
        speakText(text); 
    }

    const content = document.createElement('div');
    content.innerHTML = text.replace(/\n/g, '<br>');
    
    bubbleContainer.appendChild(roleLabel);
    bubbleContainer.appendChild(content);

    div.appendChild(bubbleContainer);
    messageContainer.appendChild(div);
    messageContainer.scrollTop = messageContainer.scrollHeight;
}


// --- GEMINI API UTILITIES ---

/**
 * Implements exponential backoff for fetching data from the API.
 */
async function expBackoffFetch(url, options, retries = 0) {
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response;
    } catch (error) {
        if (retries < MAX_RETRIES) {
            const delay = Math.pow(2, retries) * 1000 + Math.random() * 1000;
            await new Promise(resolve => setTimeout(resolve, delay));
            return expBackoffFetch(url, options, retries + 1);
        } else {
            throw new Error(`Failed to fetch from API after ${MAX_RETRIES} attempts. Error: ${error.message}`);
        }
    }
}

// --- SYSTEM PROMPTS AND SCHEMA ---

const SYSTEM_INSTRUCTION_CHAT = `
Anda berperan sebagai karakter dalam simulasi percakapan kejuruan (role-play SMK). 
Tugas Anda adalah menjadi lawan bicara siswa sesuai skenario yang sedang dimainkan.

Pedoman:
- Gunakan Bahasa Indonesia yang sopan, formal ringan, dan mudah dipahami oleh siswa SMK.
- Tanggapi dengan kalimat singkat, jelas, dan relevan dengan konteks percakapan.
- Jangan memberi penjelasan, petunjuk, atau nilai. Fokus hanya pada menjalankan peran karakter.
- Skenario yang sedang berjalan adalah:
- Sesekali, tambahkan **tantangan ringan atau situasi tidak terduga** agar siswa belajar beradaptasi, misalnya:
  * Pelanggan atau atasan Anda merasa kecewa dan butuh penjelasan.
  * Barang atau layanan mengalami kendala mendadak.
  * Ada perubahan permintaan atau instruksi di tengah percakapan.
- Tantangan cukup muncul **1–2 kali saja** agar tetap realistis dan tidak mengganggu alur utama.
- Jika siswa memberikan tanggapan yang sopan dan solutif, tunjukkan sikap apresiatif, misalnya:
  * “Terima kasih atas penjelasan Anda, sangat membantu.”
  * “Saya senang dengan cara Anda menangani situasi ini.”
  * “Baik, saya akan mengikuti saran Anda.”
- Pastikan percakapan terasa alami dan mendidik, seperti simulasi layanan pelanggan, negosiasi kerja, atau komunikasi kerja lapangan.
`;


const SYSTEM_INSTRUCTION_FEEDBACK = `
Anda adalah seorang Guru Kejuruan/Asesor yang bertugas mengevaluasi percakapan role-play yang baru saja terjadi antara siswa dan karakter simulasi.
Tugas Anda adalah menganalisis SELURUH riwayat chat dan memberikan feedback terstruktur dalam format JSON.

Kriteria Penilaian (Skala 1.0 - 5.0):
1. Kejelasan Komunikasi: Seberapa jelas dan mudah dipahami bahasa yang digunakan siswa.
2. Profesionalisme & Etika: Tingkat kesopanan, penggunaan bahasa yang sesuai, dan kepatuhan pada etika profesi.
3. Keterampilan Solusi Masalah: Seberapa efektif siswa mengidentifikasi masalah, memberikan solusi yang logis, dan negosiasi (jika relevan).
4. Empati & Pengendalian Emosi: Kemampuan siswa menunjukkan empati, menenangkan pelanggan (jika marah), dan menjaga ketenangan diri.

Anda HARUS menghasilkan respons dalam format JSON sesuai skema yang diberikan. JANGAN berikan teks di luar blok JSON.
Isi 'skorTotal' dengan rata-rata dari semua skor kriteria.
Pastikan semua teks feedback (penjelasan dan saran) disajikan dalam Bahasa Indonesia yang baik dan benar.
`;

const FEEDBACK_SCHEMA = {
    type: "OBJECT",
    properties: {
        "skorTotal": {
            "type": "NUMBER",
            "description": "Total score out of 5.0, calculated as the average of all criteria scores."
        },
        "feedbackKriteria": {
            "type": "ARRAY",
            "description": "Detailed feedback based on specific criteria.",
            "items": {
                "type": "OBJECT",
                "properties": {
                    "kriteria": { "type": "STRING", "description": "Evaluation criterion (e.g., Kejelasan Komunikasi, Profesionalisme)." },
                    "skor": { "type": "NUMBER", "description": "Score for this criterion (1.0 to 5.0)." },
                    "penjelasan": { "type": "STRING", "description": "Explanation for the score given." }
                },
                "propertyOrdering": ["kriteria", "skor", "penjelasan"]
            }
        },
        "saranLanjutan": {
            "type": "STRING",
            "description": "Suggestions for improvement and next steps for the student."
        }
    },
    "propertyOrdering": ["skorTotal", "feedbackKriteria", "saranLanjutan"]
};


// --- CORE APPLICATION LOGIC ---

/**
 * Handles communication with the Gemini API for both chat and feedback modes.
 */
async function fetchGemini(messages, mode) {
    let payload = {};

    // 1. Prepare chat history format for API
    const contents = messages.map(msg => ({
        role: msg.role === 'model' ? 'model' : 'user', // Gemini API uses 'user' and 'model'
        parts: [{ text: msg.content }]
    }));

    // 2. Determine System Instruction
    const instruction = mode === 'chat' 
        ? SYSTEM_INSTRUCTION_CHAT + currentScenario 
        : SYSTEM_INSTRUCTION_FEEDBACK;

    // 3. Build the common payload
    payload.contents = contents;
    payload.systemInstruction = { parts: [{ text: instruction }] };

    // 4. Add structured generation config for feedback mode
    if (mode === 'feedback') {
        payload.generationConfig = {
            responseMimeType: "application/json",
            responseSchema: FEEDBACK_SCHEMA,
            temperature: 0.1 
        };
    }

    const options = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    };
    
    const response = await expBackoffFetch(API_URL, options);
    const result = await response.json();
    return result;
}

/**
 * Main function to initiate the role-play session based on the chosen scenario (predefined or custom).
 */
async function initializeScenario() {
    chatHistory = [];
    messageContainer.innerHTML = '';
    feedbackResult.classList.add('hidden');
    document.getElementById('scenario-selector').classList.add('hidden');
    document.getElementById('chat-area').classList.remove('hidden');
    
    currentScenarioDisplay.textContent = 'Skenario Aktif: ' + currentScenario;

    window.speechSynthesis.cancel();
    stopVoiceRecognition(); 

    sendButton.disabled = true;
    feedbackButton.disabled = true;
    userInput.disabled = true;
    statusMessage.textContent = 'AI sedang berpikir...';
    statusMessage.classList.remove('hidden');

    const initialPrompt = `Mulai skenario role-play ini. Beri salam kepada siswa (yang berperan sebagai profesional) dan jelaskan masalah atau permintaan Anda secara singkat sebagai pembuka. Skenario: ${currentScenario}`;
    chatHistory.push({ role: 'user', content: initialPrompt });

    try {
        const response = await fetchGemini(chatHistory, 'chat');
        const aiText = response.candidates?.[0]?.content?.parts?.[0]?.text || "AI tidak merespons (Periksa konsol untuk detail error)";
        
        chatHistory.push({ role: 'model', content: aiText });
        appendMessage('model', aiText);
        
    } catch (err) {
        appendMessage('system', 'Error saat memulai skenario. Silakan coba lagi. Detail: ' + err.message);
    } finally {
        sendButton.disabled = false;
        feedbackButton.disabled = false;
        userInput.disabled = false;
        statusMessage.classList.add('hidden');
        userInput.focus();
    }
}

/**
 * Handles starting a pre-defined scenario from the dropdown.
 */
function startScenario() {
    currentScenario = document.getElementById('scenario-type').value;
    if (!currentScenario || currentScenario.includes('-- Pilih Skenario Tersedia --')) {
        appendMessage('system', 'Mohon pilih salah satu skenario yang tersedia atau buat skenario kustom.');
        return;
    }
    initializeScenario();
}


/**
 * Sends a user message and gets an AI response.
 */
async function sendMessage() {
    const text = userInput.value.trim();
    if (!text) return;
    
    appendMessage('user', text);
    chatHistory.push({ role: 'user', content: text });
    userInput.value = '';
    
    window.speechSynthesis.cancel();
    stopVoiceRecognition(); 

    sendButton.disabled = true;
    userInput.disabled = true;
    statusMessage.textContent = 'AI sedang berpikir...';
    statusMessage.classList.remove('hidden');

    try {
        const response = await fetchGemini(chatHistory, 'chat');
        const aiText = response.candidates?.[0]?.content?.parts?.[0]?.text || "AI tidak merespons (Periksa konsol untuk detail error)";
        
        chatHistory.push({ role: 'model', content: aiText });
        appendMessage('model', aiText);

    } catch (err) {
        appendMessage('system', 'Error saat mengirim pesan. Detail: ' + err.message);
    } finally {
        sendButton.disabled = false;
        userInput.disabled = false;
        statusMessage.classList.add('hidden');
    }
}

/**
 * Requests structured feedback and evaluation of the conversation.
 */
async function requestFeedback() {
    if (chatHistory.length < 2) {
        appendMessage('system', 'Silakan balas minimal 1 pesan sebelum meminta feedback.');
        return;
    }
    
    window.speechSynthesis.cancel();
    stopVoiceRecognition(); 

    sendButton.disabled = true;
    userInput.disabled = true;
    feedbackButton.disabled = true;
    statusMessage.textContent = 'AI sedang menganalisis performa Anda...';
    statusMessage.classList.remove('hidden');
    feedbackResult.classList.add('hidden');

    const feedbackHistory = [...chatHistory, { role: 'user', content: `Evaluasi percakapan di atas dengan fokus pada keahlian profesional ${currentScenario}. Keluarkan output dalam format JSON.` }];

    try {
        const response = await fetchGemini(feedbackHistory, 'feedback');
        
        const jsonText = response.candidates?.[0]?.content?.parts?.[0]?.text;

        if (jsonText) {
            const data = JSON.parse(jsonText);
            
            feedbackResult.classList.remove('hidden');
            feedbackResult.innerHTML = `
                <h2 class="text-2xl font-extrabold text-indigo-700 mb-4 border-b-2 border-indigo-200 pb-2 flex items-center">
                    <i class="fas fa-medal mr-3 text-3xl text-yellow-500"></i> Laporan Feedback dan Evaluasi
                </h2>
                <div class="p-4 mb-5 bg-indigo-700 text-white rounded-xl shadow-xl text-center transition duration-300 transform hover:scale-[1.01]">
                    <span class="text-lg font-medium block">Skor Keseluruhan (Rata-rata):</span>
                    <span class="text-6xl font-extrabold">${data.skorTotal.toFixed(1)} / 5.0</span>
                </div>
                
                <h3 class="text-xl font-bold text-gray-700 mb-4">Detail Kriteria Penilaian</h3>
                
                ${data.feedbackKriteria.map(k => {
                    let color = k.skor >= 4.5 ? 'bg-green-100 text-green-700 border-green-300' : 
                                 k.skor >= 3.5 ? 'bg-lime-100 text-lime-700 border-lime-300' :
                                 k.skor >= 2.5 ? 'bg-yellow-100 text-yellow-700 border-yellow-300' : 'bg-red-100 text-red-700 border-red-300';
                    
                    return `
                        <div class="p-4 mb-3 ${color} rounded-lg border shadow-sm">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-md font-bold">${k.kriteria}</h4>
                                <span class="text-lg font-extrabold flex items-center">
                                    <span class="mr-1 ${k.skor >= 4 ? 'text-green-600' : k.skor >= 3 ? 'text-yellow-600' : 'text-red-600'}">${k.skor.toFixed(1)}</span>
                                    <i class="fas fa-star text-yellow-500 text-sm"></i>
                                </span>
                            </div>
                            <p class="text-sm pt-2">${k.penjelasan}</p>
                        </div>
                    `;
                }).join('')}
                
                <div class="mt-6 p-5 bg-blue-100 rounded-xl border border-blue-300 shadow-md">
                    <h3 class="text-xl font-bold text-blue-800 flex items-center mb-2">
                        <i class="fas fa-lightbulb mr-2"></i> Saran Lanjutan
                    </h3>
                    <p class="text-gray-700">${data.saranLanjutan}</p>
                </div>
            `;
            feedbackResult.scrollIntoView({ behavior: 'smooth' });
            speakText("Laporan feedback Anda sudah siap!");

        } else {
            throw new Error("Respons API tidak mengandung data JSON yang valid.");
        }

    } catch (err) {
        feedbackResult.classList.remove('hidden');
        feedbackResult.innerHTML = `<h2 class="text-xl font-bold text-red-600 mb-2">Gagal Mendapatkan Feedback</h2><p class="text-red-500">Terjadi kesalahan dalam proses evaluasi. Coba kirim pesan lagi atau mulai skenario baru. Detail: ${err.message}</p>`;
    } finally {
        sendButton.disabled = false;
        userInput.disabled = false;
        feedbackButton.disabled = false;
        statusMessage.classList.add('hidden');
    }
}

/**
 * Resets the application state to the scenario selection screen.
 */
function resetApp() {
    chatHistory = [];
    currentScenario = '';
    messageContainer.innerHTML = '';
    userInput.value = '';
    feedbackResult.classList.add('hidden');
    statusMessage.classList.add('hidden');
    document.getElementById('chat-area').classList.add('hidden');
    document.getElementById('scenario-selector').classList.remove('hidden');
    customScenarioModal.classList.add('hidden'); 
    customScenarioInput.value = ''; 
    
    window.speechSynthesis.cancel();
    stopVoiceRecognition(); 
    
    sendButton.disabled = true;
    feedbackButton.disabled = true;
    userInput.disabled = true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    resetApp();
    setupVoiceRecognition(); 
});
</script>

</body>
</html>
