<?php
include "../admin/fungsi/koneksi.php";
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
    <title>Simulasi Wawancara Kerja oleh Gemini AI</title>
    <!-- Memuat Tailwind CSS untuk styling yang cepat dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
        }
        /* Custom class for a smooth, professional card look */
        .interview-card {
            background-color: white;
            border-radius: 1.5rem; /* Large rounded corners */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 1024px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .ai-message {
            background-color: #e0f2fe; /* Light blue for AI */
            border-radius: 1rem 1rem 1rem 0;
        }
        .user-message {
            background-color: #f0fdf4; /* Light green for User */
            border-radius: 1rem 1rem 0 1rem;
        }
    </style>
</head>
<body>

    <div class="interview-card p-6 md:p-10">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-2">Simulasi Wawancara Kerja (Level SMA/SMK)</h1>
        <!-- DISPLAY WAKTU JAKARTA (WIB) -->
        <p id="jakarta-time" class="text-center text-sm text-gray-500 mb-4 font-mono"></p>
        
        <p class="text-center text-lg text-gray-500 mb-6">Anda akan diwawancarai oleh **Mr Kurniawan Subuh** untuk posisi <strong id="job-title-display">Software Developer</strong>.</p>
        
        <!-- Pemilihan Posisi -->
        <div class="mb-8">
            <label for="jobSelect" class="block text-md font-medium text-gray-700 mb-2">Pilih Posisi yang Diwawancarai:</label>
            <select id="jobSelect" class="w-full p-3 border border-gray-300 rounded-xl focus:ring-sky-500 focus:border-sky-500 bg-white shadow-sm appearance-none cursor-pointer transition duration-150 ease-in-out hover:border-sky-400">
                <option value="Software Developer" selected>Software Developer</option>
                <option value="UX Designer">UX Designer</option>
                <option value="Data Scientist">Data Scientist</option>
                <option value="Marketing Specialist">Marketing Specialist</option>
                <option value="CUSTOM_ENTRY">Lainnya/Kustom...</option> <!-- Opsi Kustom Baru -->
            </select>
            
            <!-- Input Kustom (Awalnya Tersembunyi) -->
            <input type="text" id="customJobInput" placeholder="Masukkan judul posisi kustom (misalnya: 'Staf Administrasi Magang')" class="hidden w-full p-3 mt-3 border border-gray-300 rounded-xl focus:ring-sky-500 focus:border-sky-500 shadow-sm">
        </div>

        <!-- Difficulty Slider Section (Baru Ditambahkan) -->
        <div class="mb-8 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
            <label for="difficultySlider" class="block text-md font-medium text-gray-700 mb-2">Tingkat Kesulitan Wawancara:</label>
            <input type="range" id="difficultySlider" min="1" max="3" value="1" step="1" class="w-full h-2 bg-yellow-400 rounded-lg appearance-none cursor-pointer focus:outline-none focus:ring-4 focus:ring-yellow-500/50">
            <div class="flex justify-between text-xs mt-2 text-gray-600">
                <span class="font-medium">1: SMA/SMK (Pemula)</span>
                <span class="font-medium">2: S1 Junior (Menengah)</span>
                <span class="font-medium">3: S1 Senior (Mahir)</span>
            </div>
            <p class="text-center mt-3 font-semibold text-yellow-800">Tingkat Terpilih: <span id="difficultyDisplay" class="font-bold text-yellow-900">SMA/SMK (Pemula)</span></p>
        </div>


        <!-- Area Riwayat Percakapan -->
        <div id="history" class="h-96 overflow-y-auto p-4 mb-6 border border-gray-200 rounded-xl bg-gray-50">
            <div id="initial-prompt" class="flex justify-start mb-4">
                <div class="ai-message p-3 max-w-3/4 shadow">
                    <p class="font-semibold text-sky-800">Mr Kurniawan Subuh:</p>
                    <!-- Teks ini akan diperbarui secara dinamis oleh JS untuk menambahkan sapaan (Pagi/Siang/Sore/Malam) -->
                    <p>Selamat datang! Saya Mr Kurniawan Subuh. Silakan pilih posisi dan tingkat kesulitan di atas, lalu tekan tombol **Mulai Wawancara** di bawah untuk menerima pertanyaan pertama Anda. Wawancara ini bersifat dinamis dan akan berlanjut hingga saya memiliki informasi yang cukup. Semangat!</p>
                </div>
            </div>
        </div>

        <!-- Indikator Loading dan Status -->
        <div id="status-area" class="text-center mb-4 min-h-[20px]">
            <div id="loading-spinner" class="hidden text-sky-600 font-medium">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-sky-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Gemini sedang memproses jawaban Anda...
            </div>
            <p id="interview-status-display" class="text-sm text-gray-600"></p>
        </div>
        
        <!-- Kontrol Audio/Voice Input Baru -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 p-3 bg-gray-100 rounded-xl border border-gray-200">
            <div class="flex items-center space-x-3 mb-2 sm:mb-0">
                <input type="checkbox" id="ttsToggle" class="h-4 w-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500 cursor-pointer">
                <label for="ttsToggle" class="text-sm font-medium text-gray-700 select-none">Aktifkan TTS Permanen (Suara Native)</label>
            </div>
            <button id="stopTtsBtn" class="px-3 py-1 text-sm bg-red-100 text-red-700 font-medium rounded-lg shadow-sm hover:bg-red-200 transition duration-150 flex items-center disabled:opacity-50" disabled>
                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9 10a1 1 0 000 2v3a1 1 0 002 0v-3a1 1 0 000-2H9zM15 10a1 1 0 000 2v3a1 1 0 002 0v-3a1 1 0 000-2h-2z"/></svg>
                Hentikan Bicara
            </button>
        </div>


        <!-- Area Input Pengguna -->
        <div id="input-area" class="flex space-x-3">
            <textarea id="userInput" class="flex-grow p-3 border border-gray-300 rounded-xl focus:ring-sky-500 focus:border-sky-500 resize-none" rows="3" placeholder="Tulis jawaban Anda di sini..."></textarea>
            
            <!-- Tombol Voice Input (Mic) -->
            <button id="micBtn" class="p-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition duration-200 shadow-md transform active:scale-95 disabled:opacity-50 self-end w-14 h-14 flex items-center justify-center" title="Input Suara (Bahasa Indonesia)">
                <svg id="mic-icon" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 14c1.66 0 2.99-1.34 2.99-3L15 5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3.53-2.91 6.4-6.3 6.6V21h2v-2h-4v2h4v-3.7c3.54-.25 6.3-3.23 6.3-6.7h-2z"/></svg>
            </button>

            <button id="submitBtn" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 px-6 rounded-xl transition duration-200 shadow-md transform active:scale-95 disabled:opacity-50" disabled>
                Mulai Wawancara
            </button>
        </div>
        <p class="text-xs text-red-500 mt-2" id="error-message"></p>

    </div>

    <script type="module">
        const apiKey = "<?php echo $apiKey; ?>";
        const md = "<?php echo $model; ?>";
        // Konstanta dan Variabel Global
        //const apiKey = "AIzaSyAYYBCPplYs1pd3vqu5e13YsbF1hgQz8EY"; // API key is handled by the Canvas environment
        
        // --- State Management ---
        let conversationHistory = [];
        let interviewCompleted = false;
        let interviewStarted = false; 
        let questionNumber = 0; 
        let jobPosition = 'Software Developer'; 
        let interviewLevel = 1; // 1: SMA/SMK, 2: S1 Junior, 3: S1 Senior

        // --- Level Mapping untuk System Prompt ---
        const LEVEL_MAP = {
            1: { name: 'SMA/SMK (Pemula)', prompt: 'lulusan SMA/SMK (Sekolah Menengah Atas/Kejuruan)', focus: 'pengalaman magang (jika SMK), proyek sekolah, tugas jurusan, keterlibatan organisasi di sekolah (OSIS/ekskul, pencapaian belajar, motivasi belajar dan bekerja.' },
            2: { name: 'S1 Junior/Fresh Grad (Menengah)', prompt: 'lulusan S1 Junior/Fresh Graduate', focus: 'pemahaman konsep dasar teori, portofolio proyek kecil/magang yang relevan, pengalaman organisasi yang berhubungan dengan peran, pengetahuan dasar industri.' },
            3: { name: 'S1 Senior/Experienced (Mahir)', prompt: 'lulusan S1 Senior/Berpengalaman', focus: 'aplikasi konsep lanjutan, kontribusi pada proyek besar, kemampuan memecahkan masalah kompleks (studi kasus), tujuan karir jangka panjang, kepemimpinan tim kecil.' }
        };

        // Referensi elemen DOM
        const historyEl = document.getElementById('history');
        const userInputEl = document.getElementById('userInput');
        const submitBtnEl = document.getElementById('submitBtn');
        const loadingSpinnerEl = document.getElementById('loading-spinner');
        const errorEl = document.getElementById('error-message');
        const statusDisplayEl = document.getElementById('interview-status-display');
        const jobSelectEl = document.getElementById('jobSelect');
        const jobTitleDisplayEl = document.getElementById('job-title-display');
        const customJobInputEl = document.getElementById('customJobInput');
        
        // NEW: Audio/Voice/Difficulty DOM references
        const ttsToggleEl = document.getElementById('ttsToggle');
        const stopTtsBtnEl = document.getElementById('stopTtsBtn');
        const micBtnEl = document.getElementById('micBtn');
        const micIconEl = document.getElementById('mic-icon');
        const difficultySliderEl = document.getElementById('difficultySlider');
        const difficultyDisplayEl = document.getElementById('difficultyDisplay');
        
        // NEW: Jakarta Time Display
        const jakartaTimeEl = document.getElementById('jakarta-time');


        // --- TIME HANDLING FUNCTION (JAKARTA WIB) ---
        
        /**
         * Mengambil jam saat ini di Jakarta (WIB).
         * @returns {number} Jam dalam format 24-jam (0-23).
         */
        function getJakartaHour() {
            const now = new Date();
            // Menggunakan Intl.DateTimeFormat untuk mendapatkan jam di zona waktu Jakarta
            const formatter = new Intl.DateTimeFormat('en-US', {
                hour: 'numeric',
                hourCycle: 'h23', // 24-hour format
                timeZone: 'Asia/Jakarta'
            });
            return parseInt(formatter.format(now), 10);
        }

        /**
         * Menentukan sapaan (Pagi/Siang/Sore/Malam) berdasarkan jam di Jakarta.
         * @param {number} hour Jam dalam 24-jam.
         * @returns {string} Sapaan yang sesuai.
         */
        function getJakartaGreeting(hour) {
            if (hour >= 5 && hour < 11) {
                return "Selamat Pagi";
            } else if (hour >= 11 && hour < 15) {
                return "Selamat Siang";
            } else if (hour >= 15 && hour < 18) {
                return "Selamat Sore";
            } else {
                return "Selamat Malam";
            }
        }

        /**
         * Mengambil waktu saat ini dan memformatnya untuk zona waktu Jakarta (Asia/Jakarta - WIB), 
         * lalu menampilkannya dan memperbarui sapaan awal statis di UI.
         */
        function updateJakartaTime() {
            const now = new Date();
            // Menggunakan Intl.DateTimeFormat untuk penanganan zona waktu yang handal
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta',
                hour12: false
            };
            
            // Format string waktu
            const dateString = now.toLocaleDateString('id-ID', options);
            
            // Tampilkan waktu dan tanggal
            jakartaTimeEl.textContent = `Waktu Jakarta: ${dateString} WIB`;

            // NEW: Update initial welcome message based on time
            const currentHour = getJakartaHour();
            const currentGreeting = getJakartaGreeting(currentHour);
            
            // Cari elemen pesan statis di prompt awal
            const initialPromptContentEl = document.querySelector('#initial-prompt .ai-message p:last-child');
            if (initialPromptContentEl) {
                // Teks statis awal adalah "Selamat datang!"
                let existingText = initialPromptContentEl.textContent;
                // Jika sudah pernah diupdate, jangan ulangi "Selamat datang!"
                if (existingText.includes("Saya Mr Kurniawan Subuh")) {
                     existingText = existingText.replace(/Selamat (Pagi|Siang|Sore|Malam|datang)!/g, '');
                } else {
                    existingText = existingText.replace("Selamat datang!", '');
                }

                // Ganti atau tambahkan sapaan dinamis
                const staticSuffix = " Saya Mr Kurniawan Subuh. Silakan pilih posisi dan tingkat kesulitan di atas, lalu tekan tombol **Mulai Wawancara** di bawah untuk menerima pertanyaan pertama Anda. Wawancara ini bersifat dinamis dan akan berlanjut hingga saya memiliki informasi yang cukup. Semangat!";

                initialPromptContentEl.innerHTML = `${currentGreeting}!${staticSuffix}`;
            }
        }


        // --- Audio/Voice State and Setup (NATIVE BROWSER TTS) ---
        
        const synth = window.speechSynthesis;
        let isTTSEnabled = false; // State untuk toggle TTS
        let idVoice = null; // To store the selected Indonesian voice

        /**
         * Mencari dan mengatur suara Bahasa Indonesia yang tersedia di browser/OS.
         */
        function setIndonesianVoice() {
            // Tunggu hingga voices tersedia
            const voices = synth.getVoices();
            // Prioritize finding a native Indonesian voice
            idVoice = voices.find(voice => voice.lang.startsWith('id-') || voice.lang.startsWith('ID-'));
            console.log("Selected TTS Voice:", idVoice ? idVoice.name : "Default Voice (id-ID preferred)");
        }

        // Load voices once they are available (they load asynchronously)
        if (synth.onvoiceschanged !== undefined) {
            synth.onvoiceschanged = setIndonesianVoice;
        } else {
            // Fallback if the event is not available immediately
            setIndonesianVoice();
        }

        function stopTTS() {
            if (synth.speaking) {
                synth.cancel();
            }
            // Menggunakan display block/flex saat aktif, dan none saat berhenti
            stopTtsBtnEl.style.display = 'none'; 
            stopTtsBtnEl.disabled = true;
        }

        /**
         * Fungsi untuk mengubah teks menjadi suara (TTS) menggunakan API native browser.
         * @param {string} text Teks yang akan dibaca.
         */
        function speakText(text) {
            // Check if TTS is supported and enabled
            if (!('speechSynthesis' in window) || !isTTSEnabled || !text) return;

            stopTTS(); // Stop any currently speaking utterance

            // FIX: Remove Markdown formatting (like asterisks for bold) before speaking.
            // This prevents the TTS engine from reading the asterisks literally or stopping.
            const cleanText = text
                                .replace(/\*\*(.*?)\*\*/g, '$1') // Remove ** and keep content
                                .replace(/\*/g, '');             // Remove any remaining * (e.g., for italics)

            // Create a new utterance object
            const utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.lang = 'id-ID'; 
            utterance.rate = 1.0; 
            utterance.pitch = 1.0; 
            
            // Re-check voices and assign if available
            if (!idVoice) {
                setIndonesianVoice();
            }

            if (idVoice) {
                utterance.voice = idVoice;
            } else {
                console.warn("Indonesian voice not found. Using system default voice for id-ID.");
            }

            // Set up start and end handlers
            utterance.onstart = () => {
                // Tampilkan tombol stop saat mulai bicara
                stopTtsBtnEl.style.display = 'flex';
                stopTtsBtnEl.disabled = false;
            };

            utterance.onend = () => {
                stopTTS(); 
            };

            utterance.onerror = (event) => {
                console.error('SpeechSynthesisUtterance.onerror', event);
                errorEl.textContent = `Kesalahan TTS Native: ${event.error}. Coba ganti pengaturan bahasa/suara di sistem operasi Anda.`;
                stopTTS();
            };

            synth.speak(utterance);
        }

        // --- Speech Recognition Setup ---
        
        let recognition = null;
        let isListening = false;
        
        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = false; 
            recognition.interimResults = true; 
            recognition.lang = 'id-ID'; // Menggunakan Bahasa Indonesia

            recognition.onstart = () => {
                isListening = true;
                micIconEl.classList.add('text-red-500'); 
                micBtnEl.classList.add('ring-4', 'ring-red-300', 'animate-pulse');
                userInputEl.placeholder = "Mendengarkan... Silakan berbicara dalam Bahasa Indonesia.";
                toggleLoading(true); 
            };

            recognition.onresult = (event) => {
                let finalTranscript = '';
                
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript;
                    }
                }
                userInputEl.value = finalTranscript; 
                
                if (finalTranscript.trim() !== "") {
                    recognition.stop();
                }
            };

            recognition.onend = () => {
                isListening = false;
                micIconEl.classList.remove('text-red-500');
                micBtnEl.classList.remove('ring-4', 'ring-red-300', 'animate-pulse');
                userInputEl.placeholder = "Tulis jawaban Anda di sini...";
                toggleLoading(false); 
                
                submitBtnEl.disabled = interviewCompleted || !interviewStarted || userInputEl.value.trim() === '';

                if (userInputEl.value.trim() !== '' && interviewStarted && !interviewCompleted) {
                    handleSubmit();
                }
            };

            recognition.onerror = (event) => {
                console.error('Speech recognition error', event.error);
                errorEl.textContent = `Kesalahan Input Suara: ${event.error}. Pastikan mikrofon Anda terhubung dan izin diberikan.`;
                recognition.onend(); 
            };
        } else {
            // Fallback for unsupported browsers
            window.onload = () => {
                micBtnEl.disabled = true;
                micBtnEl.title = "Input Suara tidak didukung oleh browser Anda.";
                errorEl.textContent = "Input Suara (Voice Recognition) tidak didukung oleh browser ini. Mohon gunakan Chrome atau browser modern lainnya.";
                
                jobPosition = jobSelectEl.value;
                jobTitleDisplayEl.textContent = jobPosition;
                submitBtnEl.disabled = false; 
                updateStatusDisplay(0, 'START');
                toggleLoading(false);
                
                updateJakartaTime(); // Start time update interval
                setInterval(updateJakartaTime, 1000);
            };
        }
        
        function startRecognition() {
            if (!recognition) return;

            if (isListening) {
                recognition.stop();
                return;
            }
            
            stopTTS(); 
            userInputEl.value = ''; 
            
            try {
                recognition.start();
            } catch (e) {
                console.error("Error starting recognition:", e);
                errorEl.textContent = "Gagal memulai mikrofon. Coba muat ulang halaman atau periksa izin mikrofon.";
            }
        }
        
        
        // --- UI Update Functions ---

        function appendMessage(sender, content, feedback = null) {
            const messageContainer = document.createElement('div');
            messageContainer.className = `flex mb-4 ${sender === 'user' ? 'justify-end' : 'justify-start'}`;

            const messageBubble = document.createElement('div');
            messageBubble.className = `p-3 max-w-[85%] md:max-w-[70%] shadow ${sender === 'user' ? 'user-message text-gray-800' : 'ai-message'}`;

            if (sender === 'ai') {
                const interviewer = document.createElement('p');
                interviewer.className = 'font-semibold text-sky-800 mb-1';
                interviewer.textContent = 'Mr Kurniawan Subuh:';
                messageBubble.appendChild(interviewer);
            }

            const contentEl = document.createElement('p');
            contentEl.innerHTML = content.replace(/\n/g, '<br>');
            messageBubble.appendChild(contentEl);

            if (feedback && feedback.trim() !== "") {
                const feedbackEl = document.createElement('div');
                feedbackEl.className = 'mt-3 pt-3 border-t border-sky-300 text-sm text-sky-900';
                
                // --- Peningkatan Formatting: Konversi Markdown Bold ---
                // Konversi **teks** menjadi <strong>teks</strong> untuk memformat header di umpan balik.
                let formattedFeedback = feedback.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                // Tambahkan line break sebelum angka yang memulai list (e.g., "1. " atau "2. ")
                formattedFeedback = formattedFeedback.replace(/(\d+\.\s)/g, '<br>$1');
                // --- Akhir Peningkatan Formatting ---

                feedbackEl.innerHTML = `<span class="font-bold">Umpan Balik & Analisis:</span> ${formattedFeedback}`;
                messageBubble.appendChild(feedbackEl);
            }

            messageContainer.appendChild(messageBubble);
            historyEl.appendChild(messageContainer);
            historyEl.scrollTop = historyEl.scrollHeight; 
        }

        function toggleLoading(isLoading) {
            loadingSpinnerEl.classList.toggle('hidden', !isLoading);
            
            if (isListening) {
                submitBtnEl.disabled = true;
                userInputEl.disabled = false;
            } else if (interviewStarted) {
                submitBtnEl.disabled = isLoading || (userInputEl.value.trim() === '') || interviewCompleted;
                userInputEl.disabled = isLoading || interviewCompleted;
            } else {
                const isCustom = jobSelectEl.value === 'CUSTOM_ENTRY';
                const isCustomEmpty = isCustom && customJobInputEl.value.trim() === '';
                const isStandardEmpty = !isCustom && jobSelectEl.value.trim() === '';

                submitBtnEl.disabled = isLoading || interviewCompleted || isCustomEmpty || isStandardEmpty;
                userInputEl.disabled = true; 
            }
            
            // Disable controls during loading/interview if not started
            jobSelectEl.disabled = isLoading || interviewCompleted || interviewStarted;
            customJobInputEl.disabled = isLoading || interviewCompleted || interviewStarted;
            difficultySliderEl.disabled = isLoading || interviewCompleted || interviewStarted; // New
            
            const micDisabledBySupport = !('webkitSpeechRecognition' in window);
            micBtnEl.disabled = isLoading || interviewCompleted || !interviewStarted || micDisabledBySupport;
            micBtnEl.style.display = interviewCompleted ? 'none' : 'flex';
        }
        
        function updateStatusDisplay(questionNum, status) {
            if (status === 'COMPLETED') {
                statusDisplayEl.textContent = "Wawancara Selesai! Gulir ke atas untuk melihat ringkasan kinerja Anda.";
                statusDisplayEl.className = "text-md text-green-600 font-bold";
                document.getElementById('input-area').classList.remove('flex');
                document.getElementById('input-area').classList.add('hidden');
                stopTTS(); 
            } else if (questionNum > 0) {
                statusDisplayEl.textContent = `Pertanyaan ke-${questionNum} (IN_PROGRESS)`;
                statusDisplayEl.className = "text-sm text-gray-600";
            } else {
                statusDisplayEl.textContent = "Pilih posisi, tingkat kesulitan, dan klik 'Mulai Wawancara'.";
                statusDisplayEl.className = "text-sm text-gray-600";
            }
        }

        // --- Gemini API Handler: SYSTEM PROMPT Disesuaikan untuk SMA/SMK/S1 ---

        function getSystemPrompt(position, level) {
            const levelData = LEVEL_MAP[level] || LEVEL_MAP[1];
            const levelPrompt = levelData.prompt;
            const focusArea = levelData.focus;
            let trapInstruction = ""; // Variabel baru untuk instruksi jebakan/tekanan

            if (level === 2) {
                // S1 Junior: Tekanan sedang
                trapInstruction = "Saat IN_PROGRESS, pastikan setidaknya satu dari setiap 3 pertanyaan adalah **pertanyaan situasional atau tekanan sedang** (misalnya, meminta kandidat menjelaskan cara mengatasi konflik kecil dengan rekan tim, atau meminta mereka membenarkan ekspektasi gaji dasar mereka).";
            } else if (level === 3) {
                // S1 Senior: Tekanan tinggi/Jebakan
                trapInstruction = "Saat IN_PROGRESS, pastikan setidaknya satu dari setiap 2 pertanyaan adalah **pertanyaan jebakan (trap question) atau studi kasus/tekanan tinggi** (misalnya, menanyakan cara mengatasi kegagalan proyek besar, dilema etika, atau menguji kemampuan negosiasi dan pengambilan risiko).";
            }

            // SYSTEM PROMPT DINAMIS BERDASARKAN LEVEL
            return `Anda adalah 'Mr Kurniawan Subuh,' seorang pewawancara yang ramah, hangat, dan sangat suportif, berfokus pada kandidat level **${levelPrompt}** untuk posisi '${position}'. 
            
            Tujuan Anda adalah melakukan wawancara yang **dinamis, memotivasi, dan berfokus pada potensi**.
            **Pada Awal berikan salam tanyakan nama agar lebih personal dengan tetap menjaga kontek formal, dilanjutkan dengan narasi lowongan, jika belum menyebutkan nama tanyakan dengan sopan kembali baru kemudian ke pertanyyan pembuka **
            **Tone dan Konten Khusus:**
            1. **Bahasa:** Gunakan Bahasa Indonesia yang jelas, mudah dipahami, dan **sangat suportif**. Berikan kesan sebagai mentor atau kakak tingkat. Hindari jargon bisnis yang terlalu kompleks.
            2. **Fokus Pertanyaan:** Sesuaikan pertanyaan untuk menanyakan pengalaman yang relevan dengan level **${levelPrompt}**, seperti: ${focusArea}.
            3. **Umpan Balik (feedbackOnLastAnswer):** Umpan balik harus **selalu positif dan membangun**. Akui usaha dan potensi mereka, lalu berikan saran spesifik tentang bagaimana jawaban mereka dapat diperkaya dengan contoh yang lebih konkret, disesuaikan dengan fokus area level kesulitan ini. **Gunakan format Markdown bold (**teks**) untuk menyorot kata kunci atau header di umpan balik.** Jangan pernah terdengar menghakimi atau mengkritik pilihan kata mereka.

            **Aturan Wawancara Dinamis:**
            * Setiap pertanyaan berikutnya harus merupakan **tindak lanjut langsung** yang mendalami jawaban terakhir kandidat.
            * **Saat memulai (Pertanyaan 1):** Anda harus mulai dengan sapaan (berdasarkan prompt awal), lalu **jelaskan secara singkat posisi '${position}' (maksimal 2 kalimat, misalnya: tanggung jawab utamanya) dan jelaskan apa yang Anda cari dari kandidat level ${levelPrompt}**. Setelah itu, ajukan pertanyaan perkenalan standar yang sesuai untuk lulusan baru/kandidat level ini.
            * ${trapInstruction} 
            * **Penting:** Ketika memberikan umpan balik (feedbackOnLastAnswer), fokuslah pada **kedalaman, relevansi, dan detail konten**. Jangan pernah mengomentari atau mengkritik **panjang** jawaban kandidat secara eksplisit.
            * **Saat IN_PROGRESS:** Berikan umpan balik yang membangun, lalu ajukan pertanyaan berikutnya.
            * **TETAPKAN INTERVIEW SEBAGAI COMPLETED:** Wawancara harus diakhiri (setel 'interviewStatus' menjadi 'COMPLETED') setelah 5-7 pertanyaan, atau ketika Anda yakin telah mengumpulkan informasi yang cukup untuk membuat rekomendasi.
            * **Pesan Penutup/Ringkasan (Status: COMPLETED):** Ketika 'interviewStatus' diatur menjadi 'COMPLETED', 'feedbackOnLastAnswer' terakhir harus berisi ringkasan evaluasi komprehensif yang mencakup: **Kekuatan** (khususnya potensi dan semangat), **Area Perbaikan** (fokus pada bagaimana mengemas pengalaman/pengetahuan dengan lebih baik sesuai level), dan **Tips Bermanfaat** untuk persiapan di masa depan. Gunakan Markdown bold untuk semua header dan poin utama. 'currentQuestion' harus berisi pesan penutup formal dan ucapan terima kasih.
                    
            Tulis semua output Anda dalam Bahasa Indonesia yang suportif dan profesional. Pastikan 'questionNumber' terus bertambah (1, 2, 3, ...) untuk setiap pertanyaan baru. Jangan pernah menyertakan komentar tentang struktur JSON dalam output.`;
        }

        async function callGeminiAPI(userQuery) {
            toggleLoading(true);
            errorEl.textContent = ''; 

            // Menggunakan level kesulitan yang dipilih
            const currentSystemPrompt = getSystemPrompt(jobPosition, interviewLevel);

            const chatHistory = conversationHistory.map(item => ({
                role: item.role,
                parts: [{ text: item.text }]
            }));
            chatHistory.push({ role: "user", parts: [{ text: userQuery }] });

            const payload = {
                contents: chatHistory,
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "currentQuestion": { "type": "STRING" },
                            "feedbackOnLastAnswer": { "type": "STRING" },
                            "interviewStatus": { "type": "STRING", "enum": ["IN_PROGRESS", "COMPLETED"] },
                            "questionNumber": { "type": "NUMBER" }
                        },
                        "required": ["currentQuestion", "feedbackOnLastAnswer", "interviewStatus", "questionNumber"]
                    }
                },
                systemInstruction: {
                    parts: [{ text: currentSystemPrompt }]
                }
            };
            
            const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey}`;

            for (let i = 0; i < 3; i++) { 
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        const errorDetails = await response.text();
                        throw new Error(`API error: ${response.status} ${response.statusText} (${errorDetails.substring(0, 100)}...)`);
                    }

                    const result = await response.json();
                    const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                    
                    if (!jsonText) {
                        throw new Error("Received empty or malformed JSON text from API.");
                    }

                    const cleanJsonText = jsonText.replace(/^```json\n|```$/g, '').trim();
                    const structuredResponse = JSON.parse(cleanJsonText);
                    
                    return structuredResponse;

                } catch (error) {
                    console.error(`Attempt ${i + 1} failed:`, error);
                    if (i < 2) { 
                        const delay = Math.pow(2, i) * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                    } else {
                        throw new Error(`Gagal terhubung dengan Mr Kurniawan Subuh setelah ${i + 1} kali percobaan. Status terakhir: ${error.message || "Tidak diketahui."}`);
                    }
                }
            }
        }

        // --- Game/Interview Logic ---

        async function startInterviewFlow() {
            let finalJobPosition = jobPosition;
            
            if (jobSelectEl.value === 'CUSTOM_ENTRY') {
                finalJobPosition = customJobInputEl.value.trim();
                if (finalJobPosition === '') {
                    errorEl.textContent = 'Mohon masukkan judul posisi kustom.';
                    toggleLoading(false); 
                    return;
                }
            } else if (jobPosition.trim() === '') {
                errorEl.textContent = 'Mohon pilih posisi pekerjaan terlebih dahulu.';
                toggleLoading(false);
                return;
            }

            jobPosition = finalJobPosition; 
            jobTitleDisplayEl.textContent = jobPosition; 
            
            conversationHistory = [];
            questionNumber = 0;
            interviewCompleted = false;
            interviewStarted = true;
            
            document.getElementById('initial-prompt').style.display = 'none';
            historyEl.innerHTML = ''; 
            userInputEl.value = '';
            submitBtnEl.textContent = 'Kirim Jawaban';
            userInputEl.placeholder = 'Tulis jawaban Anda di sini...';
            errorEl.textContent = '';
            
            // Dapatkan sapaan dinamis (Pagi/Siang/Sore/Malam)
            const currentHour = getJakartaHour();
            const dynamicGreeting = getJakartaGreeting(currentHour);

            // Sisipkan sapaan ke prompt awal untuk memandu AI memulai percakapan
            // Teks ini adalah pemicu untuk AI agar mengikuti instruksi 'Saat memulai (Pertanyaan 1)' di System Prompt
            const initialPromptText = `${dynamicGreeting}. Mulai wawancara untuk posisi ${jobPosition} dengan tingkat kesulitan ${LEVEL_MAP[interviewLevel].name}. Pertanyaan 1, silakan.`;
            
            appendMessage('user', `Mulai Wawancara untuk posisi: ${jobPosition} (Level ${LEVEL_MAP[interviewLevel].name})`); 
            conversationHistory.push({ role: 'user', text: initialPromptText }); 
            
            try {
                const apiResponse = await callGeminiAPI(initialPromptText);
                
                questionNumber = apiResponse.questionNumber;
                interviewCompleted = apiResponse.interviewStatus === 'COMPLETED';
                
                appendMessage('ai', apiResponse.currentQuestion, apiResponse.feedbackOnLastAnswer);
                
                // Speak the initial question using native TTS
                speakText(apiResponse.currentQuestion); 
                
                conversationHistory.push({ 
                    role: 'model', 
                    text: `[Feedback: ${apiResponse.feedbackOnLastAnswer}] [Question: ${apiResponse.currentQuestion}]` 
                });
                
                updateStatusDisplay(questionNumber, apiResponse.interviewStatus);

            } catch (error) {
                console.error("Kesalahan dalam memulai wawancara:", error);
                errorEl.textContent = `Kesalahan: ${error.message}`;
                interviewStarted = false;
                submitBtnEl.textContent = 'Mulai Wawancara';
                jobSelectEl.disabled = false;
                customJobInputEl.disabled = false;
                difficultySliderEl.disabled = false;
            } finally {
                toggleLoading(false);
            }
        }

        async function handleSubmit() {
            const userText = userInputEl.value.trim();
            if (userText === '') return;

            stopTTS();
            
            appendMessage('user', userText);
            
            conversationHistory.push({ role: 'user', text: userText });
            userInputEl.value = ''; 
            submitBtnEl.disabled = true;

            try {
                const apiResponse = await callGeminiAPI(userText);
                
                questionNumber = apiResponse.questionNumber;
                interviewCompleted = apiResponse.interviewStatus === 'COMPLETED';
                
                let aiResponseText = apiResponse.currentQuestion;
                let feedback = apiResponse.feedbackOnLastAnswer;

                appendMessage('ai', aiResponseText, feedback);
                
                // Speak the AI's question text using native TTS
                speakText(aiResponseText);
                
                conversationHistory.push({ 
                    role: 'model', 
                    text: `[Feedback: ${feedback}] [Question: ${aiResponseText}]` 
                });
                
                updateStatusDisplay(questionNumber, apiResponse.interviewStatus);

            } catch (error) {
                console.error("Kesalahan dalam wawancara:", error);
                errorEl.textContent = `Kesalahan: ${error.message}`;
            } finally {
                toggleLoading(false);
                if (interviewCompleted) {
                    userInputEl.placeholder = "Wawancara telah berakhir. Segarkan halaman untuk mencoba lagi.";
                    submitBtnEl.style.display = 'none';
                    userInputEl.style.display = 'none';
                    document.getElementById('input-area').classList.remove('flex');
                    document.getElementById('input-area').classList.add('hidden');
                }
            }
        }

        // --- Event Listeners and Initialization ---

        submitBtnEl.addEventListener('click', () => {
            if (interviewStarted) {
                handleSubmit();
            } else {
                startInterviewFlow();
            }
        });

        userInputEl.addEventListener('input', () => {
            submitBtnEl.disabled = interviewCompleted || !interviewStarted || userInputEl.value.trim() === '';
        });
        
        userInputEl.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey && !submitBtnEl.disabled) {
                e.preventDefault();
                handleSubmit();
            }
        });

        jobSelectEl.addEventListener('change', (e) => {
            const selectedValue = e.target.value;
            
            if (selectedValue === 'CUSTOM_ENTRY') {
                customJobInputEl.classList.remove('hidden');
                jobPosition = customJobInputEl.value.trim() || 'Posisi Kustom'; 
            } else {
                customJobInputEl.classList.add('hidden');
                jobPosition = selectedValue;
            }

            jobTitleDisplayEl.textContent = jobPosition;
            
            conversationHistory = [];
            questionNumber = 0;
            interviewCompleted = false;
            interviewStarted = false;
            submitBtnEl.textContent = 'Mulai Wawancara';
            stopTTS(); 

            const isCustomEmpty = (selectedValue === 'CUSTOM_ENTRY' && customJobInputEl.value.trim() === '');
            submitBtnEl.disabled = isCustomEmpty;
            
            userInputEl.placeholder = 'Tulis jawaban Anda di sini...';

            document.getElementById('initial-prompt').style.display = 'flex'; 
            historyEl.innerHTML = document.getElementById('initial-prompt').outerHTML; 
            errorEl.textContent = ''; 
            updateStatusDisplay(0, 'START');
            
            // Panggil updateJakartaTime untuk memastikan sapaan di UI diperbarui setelah reset
            updateJakartaTime(); 
        });

        customJobInputEl.addEventListener('input', () => {
            jobPosition = customJobInputEl.value.trim() || 'Posisi Kustom';
            jobTitleDisplayEl.textContent = jobPosition;

            if (!interviewStarted) {
                submitBtnEl.disabled = customJobInputEl.value.trim() === '';
            }
        });

        // Event Listener BARU untuk Slider Kesulitan
        difficultySliderEl.addEventListener('input', (e) => {
            interviewLevel = parseInt(e.target.value);
            difficultyDisplayEl.textContent = LEVEL_MAP[interviewLevel].name;

            // Reset interview state when level changes
            jobSelectEl.dispatchEvent(new Event('change'));
        });
        
        ttsToggleEl.addEventListener('change', (e) => {
            isTTSEnabled = e.target.checked;
            if (!isTTSEnabled) {
                stopTTS();
            }
        });
        
        stopTtsBtnEl.addEventListener('click', stopTTS);

        if (recognition) {
            micBtnEl.addEventListener('click', startRecognition);
        }

        window.onload = () => {
            // Initial setup based on default values
            interviewLevel = parseInt(difficultySliderEl.value);
            difficultyDisplayEl.textContent = LEVEL_MAP[interviewLevel].name;

            jobPosition = jobSelectEl.value;
            jobTitleDisplayEl.textContent = jobPosition;
            
            submitBtnEl.disabled = false; 
            
            updateStatusDisplay(0, 'START');
            toggleLoading(false); 
            stopTtsBtnEl.style.display = 'none'; 
            
            // Inisialisasi dan mulai pembaruan waktu Jakarta
            updateJakartaTime(); 
            setInterval(updateJakartaTime, 1000); 
        };
        
        window.addEventListener('beforeunload', stopTTS);

    </script>
</body>
</html>
