<?php
include "../admin/fungsi/koneksi.php";
$sql = mysqli_query($koneksi, "SELECT * FROM datasekolah");
$data = mysqli_fetch_assoc($sql);
// Baca konfigurasi API Key dari file JSON
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
    <title>SOP Crisis Management - AI Powered</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom CSS untuk memperindah tampilan game */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            padding: 10px;
        }

        #main-container {
            max-width: 900px;
            margin: 20px auto;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            min-height: 80vh;
            padding: 30px;
        }

        #narasi-area {
            height: 400px;
            overflow-y: auto;
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .narasi-message {
            margin-bottom: 15px;
            padding: 8px;
            border-radius: 4px;
        }

        .gm-prompt {
            background-color: #e0f2f1;
            border-left: 4px solid #14b8a6;
            font-style: italic;
        }

        .player-action {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            font-weight: 500;
        }

        .feedback-penalty {
            color: #ef4444;
            font-weight: bold;
        }
        .feedback-info {
            color: #f97316;
            font-weight: 600;
        }
        .feedback-good {
            color: #10b981;
            font-weight: 600;
        }
        
        #opsi-container button, #custom-action-button {
            transition: background-color 0.3s, transform 0.1s;
        }

        #opsi-container button:hover, #custom-action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .skor-high { color: #10b981; }
        .skor-medium { color: #facc15; }
        .skor-low { color: #ef4444; }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            z-index: 10;
        }
        
        /* Gaya untuk tombol TTS */
        .tts-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin: 0;
        }
        .tts-button svg {
            display: block;
        }

        /* Gaya untuk Review Modal */
        #review-container {
            text-align: left;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .review-grade {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 10px;
        }

    </style>
</head>
<body class="p-4 bg-gray-100 min-h-screen">

    <div id="main-container" class="p-6 md:p-8 relative">
           <div class="relative w-full mb-6">
    <img src="../admin/foto/<?= $data['banner'] ?>" alt="Banner Sekolah" class="w-full h-40 md:h-48 object-cover rounded-xl shadow-lg">
    <img src="../admin/foto/<?= $data['logo'] ?>" alt="Logo Sekolah" 
         class="absolute left-6 top-1/2 transform -translate-y-1/2 w-20 h-20 md:w-28 md:h-28 object-contain rounded-full border-4 border-white shadow-xl">
</div>
        <div id="loading-overlay" class="loading-overlay hidden">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-700 mx-auto mb-3"></div>
                <p class="text-indigo-700 font-semibold">AI Game Master sedang berpikir...</p>
            </div>
        </div>

        <header class="text-center mb-6">
            <h1 class="text-4xl font-extrabold text-gray-800">SOP Crisis Management</h1>
            <p id="game-subtitle" class="text-xl text-gray-600 mt-2">Pilih Tema Jurusan Anda</p>
        </header>

        <!-- START SCREEN -->
        <div id="start-screen" class="p-8 text-center bg-gray-50 rounded-lg border border-gray-200">
            <h2 class="text-2xl font-bold mb-4 text-indigo-700">Konfigurasi Permainan</h2>
            <div class="space-y-4 max-w-sm mx-auto">
                <div>
                    <label for="theme-select" class="block text-left mb-1 font-medium text-gray-700">Pilih Jurusan/Tema:</label>
                    <select id="theme-select" class="w-full p-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Teknik Komputer dan Jaringan (TKJ)">Teknik Komputer dan Jaringan (TKJ)</option>
                        <option value="Tata Boga (Dapur Komersial)">Tata Boga (Dapur Komersial)</option>
                        <option value="Akuntansi (Administrasi Keuangan)">Akuntansi (Administrasi Keuangan)</option>
                        <option value="custom">Tema Khusus...</option>
                    </select>
                </div>
                
                <!-- Pilihan Kesulitan -->
                <div>
                    <label for="difficulty-select" class="block text-left mb-1 font-medium text-gray-700">Pilih Tingkat Kesulitan:</label>
                    <select id="difficulty-select" class="w-full p-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Mudah">Mudah (Skenario Langsung)</option>
                        <option value="Sedang" selected>Sedang (Sedikit Pengecoh)</option>
                        <option value="Sulit">Sulit (Banyak Pengecoh & Tekanan)</option>
                    </select>
                </div>
                
                <div id="custom-theme-input" class="hidden">
                    <label for="custom-theme" class="block text-left mb-1 font-medium text-gray-700">Masukkan Tema Khusus (Sertakan Kasus Krisis):</label>
                    <input type="text" id="custom-theme" placeholder="Misal: Perawat Hewan, krisis: anjing sakit mendadak" class="w-full p-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button id="start-button" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition duration-300 transform hover:scale-[1.02]">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197 2.132A1 1 0 0110 13.134V8.866a1 1 0 011.555-.832l3.197 2.132a1 1 0 010 1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Mulai Game!
                </button>
            </div>
        </div>

        <!-- GAME SCREEN (Awalnya tersembunyi) -->
        <div id="game-screen" class="hidden flex-grow flex flex-col">
            <!-- Area Narasi & Log Game -->
            <div id="narasi-area" class="text-sm flex-grow">
                <!-- Konten diisi oleh JS -->
            </div>

            <!-- Opsi Pilihan -->
            <div id="opsi-wrapper" class="p-4 bg-gray-50 rounded-lg shadow-inner mt-4">
                <h3 class="font-bold text-gray-700 mb-3 border-b pb-1">Opsi Tindakan (Pilih atau Input)</h3>
                <div id="opsi-container" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                    <!-- Tombol Opsi di-generate oleh JS -->
                </div>

                <!-- Input Aksi Khusus -->
                <div class="mt-4">
                    <label for="custom-action-input" class="block text-sm font-medium text-gray-700 mb-1">Atau masukkan tindakan Anda sendiri:</label>
                    <div class="flex space-x-2">
                        <input type="text" id="custom-action-input" placeholder="Tuliskan langkah SOP Anda..." class="flex-grow p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <button id="custom-action-button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">
                            Ambil Tindakan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tombol Stop TTS -->
            <div class="text-center mt-4">
                <button id="stop-tts-button" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-200 hidden">
                    <svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 4h10a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                    STOP AUDIO (TTS)
                </button>
            </div>


            <!-- Status Panel -->
            <div id="status-panel" class="bg-indigo-600 text-white p-4 rounded-lg shadow-md flex justify-around text-center text-lg font-semibold mt-6">
                <div>
                    <p>Skor Kepatuhan SOP</p>
                    <p id="skor" class="text-3xl mt-1 skor-high">100</p>
                </div>
                <div>
                    <p>Waktu Tersisa (menit)</p>
                    <p id="waktu" class="text-3xl mt-1">60</p>
                </div>
                <div>
                    <p>Langkah Sekarang</p>
                    <p id="langkah-sekarang" class="text-3xl mt-1">1</p>
                </div>
            </div>
        </div>

        <!-- Game Over Modal (Hidden by default) -->
        <div id="modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden">
            <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-lg text-center">
                <h2 id="modal-title" class="text-3xl font-bold mb-2 text-red-600">GAME OVER</h2>
                <div id="modal-summary" class="mb-4 text-gray-700"></div>
                
                <!-- Konten Review akan diisi di sini -->
                <div id="review-container">
                    <p class="text-2xl font-bold text-gray-800 mb-2">Penilaian Kinerja AI Game Master:</p>
                    <div class="flex justify-center items-center mb-4 space-x-6">
                        <div class="text-left">
                            <p class="text-sm font-semibold text-gray-500">Nilai Akhir Kualitatif:</p>
                            <p id="review-grade" class="review-grade text-indigo-600">--</p>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-gray-500">Ringkasan Penilaian:</p>
                            <p id="review-summary" class="text-base text-gray-700 italic">Memuat...</p>
                        </div>
                    </div>
                    
                    <h4 class="text-lg font-bold text-red-500 mt-4 mb-2">Saran Tindak Lanjut:</h4>
                    <ul id="follow-up-advice" class="list-disc list-inside text-gray-600 text-left mx-auto max-w-md space-y-1">
                        <!-- Saran diisi oleh JS -->
                    </ul>
                </div>
                
                <div class="mt-6 flex justify-center space-x-4">
                    <button id="download-review-button" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-full transition duration-300 flex items-center hidden">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        UNDUH RINGKASAN (.txt)
                    </button>
                    <button id="restart-button" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-full transition duration-300">
                        Selesai & Pilih Tema Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- KONFIGURASI API & STATE ---
        const apiKey = "<?php echo $apiKey; ?>"; // Dibiarkan kosong, akan diisi oleh runtime
        const md = "<?php echo $model; ?>";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey}`;
        const themeMap = {
            'Teknik Komputer dan Jaringan (TKJ)': 'spesialis jaringan, krisis jaringan lumpuh',
            'Tata Boga (Dapur Komersial)': 'koki profesional, krisis tumpahan minyak dan alarm kebakaran',
            'Akuntansi (Administrasi Keuangan)': 'staf akuntansi, krisis dokumen audit hilang dan deadline pelaporan',
            'custom': null // Akan diisi dari input
        };

        // State Game
        let currentTheme = '';
        let currentStep = 0;
        let skor = 100;
        let waktu = 60;
        let gameStatus = 'initial';
        let previousNarration = '';
        let difficulty = 'Sedang';
        let finalReviewData = null; // Menyimpan data review dari AI

        // Elemen DOM
        const gameSubtitleEl = document.getElementById('game-subtitle');
        const startScreenEl = document.getElementById('start-screen');
        const gameScreenEl = document.getElementById('game-screen');
        const themeSelectEl = document.getElementById('theme-select');
        const difficultySelectEl = document.getElementById('difficulty-select');
        const customThemeInputEl = document.getElementById('custom-theme-input');
        const customThemeEl = document.getElementById('custom-theme');
        const startButtonEl = document.getElementById('start-button');
        const loadingOverlayEl = document.getElementById('loading-overlay');
        
        const narasiAreaEl = document.getElementById('narasi-area');
        const opsiContainerEl = document.getElementById('opsi-container');
        const skorEl = document.getElementById('skor');
        const waktuEl = document.getElementById('waktu');
        const langkahEl = document.getElementById('langkah-sekarang');
        const modalEl = document.getElementById('modal');
        const modalTitleEl = document.getElementById('modal-title');
        const modalSummaryEl = document.getElementById('modal-summary');
        const restartButtonEl = document.getElementById('restart-button');
        const customActionInputEl = document.getElementById('custom-action-input');
        const customActionButtonEl = document.getElementById('custom-action-button');
        const stopTtsButtonEl = document.getElementById('stop-tts-button');

        // Elemen Review Modal
        const reviewGradeEl = document.getElementById('review-grade');
        const reviewSummaryEl = document.getElementById('review-summary');
        const followUpAdviceEl = document.getElementById('follow-up-advice');
        const downloadReviewButtonEl = document.getElementById('download-review-button'); // BARU: Tombol Unduh


        // --- FUNGSI TTS ---

        function stopTTS() {
            if ('speechSynthesis' in window && speechSynthesis.speaking) {
                speechSynthesis.cancel();
            }
        }

        function speakText(text) {
            if ('speechSynthesis' in window) {
                stopTTS();

                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                
                const voices = speechSynthesis.getVoices();
                const indonesianVoice = voices.find(voice => voice.lang === 'id-ID' || voice.lang.startsWith('id'));
                if (indonesianVoice) {
                    utterance.voice = indonesianVoice;
                } else {
                    utterance.voice = null;
                }

                speechSynthesis.speak(utterance);
            } else {
                console.warn("Text-to-Speech (TTS) tidak didukung di browser ini.");
            }
        }
        
        stopTtsButtonEl.onclick = stopTTS;

        // --- FUNGSI API & UTILITY ---

        async function fetchWithBackoff(url, options, maxRetries = 5) {
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (response.ok) return response;
                    
                    if (response.status === 429 && i < maxRetries - 1) {
                        const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    }

                    throw new Error(`API returned status ${response.status}: ${response.statusText}`);
                } catch (error) {
                    if (i === maxRetries - 1) throw error;
                    const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
        }

        async function callGeminiAPI(userQuery, systemInstruction, responseSchema) {
            loadingOverlayEl.classList.remove('hidden');
            try {
                const payload = {
                    contents: [{ parts: [{ text: userQuery }] }],
                    systemInstruction: { parts: [{ text: systemInstruction }] },
                    generationConfig: {
                        responseMimeType: "application/json",
                        responseSchema: responseSchema
                    }
                };

                const response = await fetchWithBackoff(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.candidates && result.candidates.length > 0 &&
                    result.candidates[0].content && result.candidates[0].content.parts &&
                    result.candidates[0].content.parts.length > 0) {
                    
                    const jsonString = result.candidates[0].content.parts[0].text;
                    const parsedJson = JSON.parse(jsonString);
                    return parsedJson;
                } else {
                    throw new Error("Respon API tidak memiliki konten yang valid.");
                }

            } catch (error) {
                console.error("Gagal memanggil Gemini API:", error);
                addMessage(`❌ ERROR: Gagal memuat data dari AI Game Master. Cek konsol.`, 'feedback-penalty');
                endGame("🔴 KRISIS SISTEM");
                return null;
            } finally {
                loadingOverlayEl.classList.add('hidden');
            }
        }
        
        // Skema JSON untuk penilaian SOP (saat game berjalan)
        const SOPResponseSchema = {
            type: "OBJECT",
            properties: {
                feedback_text: { type: "STRING", description: "Feedback dan penjelasan SOP atas tindakan pemain, termasuk efek skor/waktu." },
                score_change: { type: "NUMBER", description: "Perubahan skor kepatuhan (0, -5, -10, dst)." },
                time_change: { type: "NUMBER", description: "Perubahan waktu dalam menit (0, -5, -10, dst)." },
                game_over: { type: "BOOLEAN", description: "True jika krisis terselesaikan / misi gagal." },
                is_correct_sop: { type: "BOOLEAN", description: "True jika tindakan pemain adalah SOP yang ideal." },
                next_narration: { type: "STRING", description: "Narasi skenario berikutnya atau ringkasan misi/kegagalan." },
                next_options: {
                    type: "ARRAY",
                    items: {
                        type: "OBJECT",
                        properties: {
                            teks: { type: "STRING", description: "Opsi tindakan untuk langkah berikutnya." }
                        }
                    }
                }
            },
            required: ["feedback_text", "score_change", "time_change", "game_over", "is_correct_sop", "next_narration", "next_options"]
        };
        
        // Skema JSON untuk Review Akhir Game
        const ReviewSchema = {
            type: "OBJECT",
            properties: {
                assessment_title: { type: "STRING", description: "Ringkasan status (e.g., Kinerja Hebat, Perlu Perbaikan Cepat). Sesuaikan dengan skor." },
                assessment_grade: { type: "STRING", description: "Penilaian kualitatif (e.g., A+, B-, C. Beri B/C jika skor < 70)." },
                review_summary: { type: "STRING", description: "Analisis singkat (1-2 kalimat) mengapa pemain mendapat skor itu, berdasarkan tema dan kesulitan." },
                follow_up_advice: {
                    type: "ARRAY",
                    items: { type: "STRING" },
                    description: "Daftar 3 saran tindak lanjut spesifik untuk meningkatkan kepatuhan SOP di tema ini (misalnya, 'Pelajari prosedur pemadaman api untuk tumpahan minyak')."
                }
            },
            required: ["assessment_title", "assessment_grade", "review_summary", "follow_up_advice"]
        };

        const systemInstruction = `Anda adalah AI Game Master dan Validator SOP untuk siswa SMK. Tugas Anda adalah mensimulasikan krisis berdasarkan tema, menilai tindakan siswa berdasarkan SOP industri yang ketat, dan memberikan feedback yang jelas dan informatif.
        
        ATURAN PENILAIAN:
        1. Respon harus selalu dalam Bahasa Indonesia.
        2. SOP Benar/Ideal: score_change=0, time_change= -5 sampai -10 (untuk durasi langkah).
        3. SOP Salah/Tidak Efisien: score_change= -10 sampai -20, time_change= -10 sampai -20.
        4. Pelanggaran SOP Kritis (Keselamatan/Regulasi): score_change= -25 sampai -30, time_change= -15.
        5. Jika game_over=true, narasi terakhir harus merangkum keberhasilan/kegagalan.
        6. JANGAN ulangi opsi tindakan sebelumnya jika tindakan yang diambil benar. Lanjutkan alur cerita (next_narration).
        7. JANGAN berikan opsi tindakan yang semuanya benar. Buat opsi yang semuanya plausibel namun hanya satu yang paling sesuai SOP.
        8. Berikan minimal 3 opsi tindakan di 'next_options'.

        PENYESUAIAN KESULITAN NARASI:
        - Kesulitan 'Mudah': Narasi langsung, fokus pada langkah krisis utama.
        - Kesulitan 'Sedang': Narasi mengandung satu skenario/peristiwa pengecoh minor yang tidak relevan dengan SOP utama (misalnya, telepon berdering, gangguan kecil).
        - Kesulitan 'Sulit': Narasi harus memasukkan skenario pengecoh yang signifikan, mendesak, atau bahkan informasi yang salah/kontradiktif dari pihak lain. Ini membutuhkan fokus tinggi pada SOP dan manajemen prioritas.`;

        // --- FUNGSI GAME LOGIC ---

        function updateDisplay() {
            skorEl.textContent = skor;
            waktuEl.textContent = waktu;
            langkahEl.textContent = currentStep;

            skorEl.classList.remove('skor-high', 'skor-medium', 'skor-low');
            if (skor >= 85) skorEl.classList.add('skor-high');
            else if (skor >= 60) skorEl.classList.add('skor-medium');
            else skorEl.classList.add('skor-low');

            if (skor <= 0 || waktu <= 0) endGame("💥 GAGAL MENDESAK.");
        }

        function addMessage(text, className = 'gm-prompt') {
            const messageDiv = document.createElement('div');
            messageDiv.className = `narasi-message ${className} flex items-start`; 
            
            const contentText = text.replace(/(\*\*)/g, '');
            
            if (className === 'gm-prompt') {
                const ttsButton = document.createElement('button');
                ttsButton.className = 'tts-button mr-2 mt-0.5 text-gray-500 hover:text-indigo-600 focus:outline-none flex-shrink-0';
                ttsButton.title = 'Dengarkan Narasi';
                ttsButton.type = 'button';
                ttsButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.004 7.854a.5.5 0 00-.708-.708l-.707.708A4 4 0 0112 10a4 4 0 011.589 3.046l.707.708a.5.5 0 00.708-.708L14.004 12.83A3.001 3.001 0 0015 10a3.001 3.001 0 00-1-2.146zM15.414 5.414l.707-.707a.5.5 0 000-.707.5.5 0 00-.707 0L15.007 5a6.002 6.002 0 010 10l.707.707a.5.5 0 00.707 0 .5.5 0 000-.707L15.414 15.414A5.002 5.002 0 0017 10a5.002 5.002 0 00-1.586-4.586z" clip-rule="evenodd" fill-rule="evenodd" />
                    </svg>
                `;
                ttsButton.onclick = () => speakText(contentText);
                
                messageDiv.appendChild(ttsButton);

                const textSpan = document.createElement('span');
                textSpan.textContent = contentText;
                messageDiv.appendChild(textSpan);

            } else {
                messageDiv.innerHTML = contentText;
            }

            narasiAreaEl.appendChild(messageDiv);
            narasiAreaEl.scrollTop = narasiAreaEl.scrollHeight;
        }
        
        function disableInput(disabled) {
            opsiContainerEl.querySelectorAll('button').forEach(btn => btn.disabled = disabled);
            customActionInputEl.disabled = disabled;
            customActionButtonEl.disabled = disabled;
        }

        function loadOptions(options) {
            opsiContainerEl.innerHTML = '';
            
            options.forEach((opsi) => {
                const button = document.createElement('button');
                button.textContent = opsi.teks;
                button.className = 'bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow-md';
                button.onclick = () => processPlayerAction(opsi.teks);
                opsiContainerEl.appendChild(button);
            });
            disableInput(false);
            customActionInputEl.value = '';
        }

        async function processPlayerAction(action) {
            if (gameStatus !== 'running') return;
            disableInput(true);
            
            stopTTS();

            addMessage(`Tindakan Anda: ${action}`, 'player-action');

            const userQuery = `Difficulty: ${difficulty}. Theme: ${currentTheme}. Current Step: ${currentStep}. Previous Narration: "${previousNarration}". Player's Last Action: "${action}". Generate the response for the next step.`;
            
            const responseData = await callGeminiAPI(userQuery, systemInstruction, SOPResponseSchema);

            if (!responseData) {
                disableInput(false);
                return;
            }

            skor += responseData.score_change;
            waktu += responseData.time_change;
            
            let feedbackClass = 'feedback-info';
            if (responseData.score_change < 0) {
                feedbackClass = 'feedback-penalty';
            } else if (responseData.is_correct_sop) {
                feedbackClass = 'feedback-good';
            }
            addMessage(`AI-GM Feedback: ${responseData.feedback_text} (Skor: ${responseData.score_change}, Waktu: ${responseData.time_change} menit)`, feedbackClass);


            if (responseData.game_over || skor <= 0 || waktu <= 0) {
                addMessage(`AI-GM Final Narasi: ${responseData.next_narration}`, 'gm-prompt');
                endGame(responseData.game_over ? "🏆 MISI SELESAI!" : "💥 GAGAL MENDESAK.", responseData.next_narration);
                return;
            }

            currentStep++;
            previousNarration = responseData.next_narration;
            
            addMessage(`Langkah #${currentStep}: ${responseData.next_narration}`, 'gm-prompt');
            loadOptions(responseData.next_options);

            updateDisplay();
        }

        customActionButtonEl.onclick = () => {
            const customAction = customActionInputEl.value.trim();
            if (customAction) {
                processPlayerAction(customAction);
            } else {
                addMessage(`⚠️ Harap masukkan tindakan kustom Anda.`, 'feedback-info');
            }
        };

        async function initiateFirstStep() {
            gameSubtitleEl.textContent = `AI Game Master: ${currentTheme} (Kesulitan: ${difficulty})`;
            
            startScreenEl.classList.add('hidden');
            gameScreenEl.classList.remove('hidden');
            stopTtsButtonEl.classList.remove('hidden'); 

            addMessage(`[Skenario Dimuat] Harap tunggu AI Game Master menyiapkan krisis...`);
            
            const userQuery = `Difficulty: ${difficulty}. Tema: ${currentTheme}. Ini adalah langkah pertama (Langkah 1). Mulai narasi krisis, jelaskan peran pemain, dan berikan 3 opsi tindakan awal sesuai SOP. Sesuaikan narasi awal berdasarkan tingkat kesulitan.`;
            
            const responseData = await callGeminiAPI(userQuery, systemInstruction, SOPResponseSchema);
            
            if (!responseData) return;

            currentStep = 1;
            previousNarration = responseData.next_narration;

            addMessage(`AI-GM Konfirmasi Tema: Selamat datang di simulasi ${currentTheme}. Tingkat kesulitan **${difficulty}**. Waktu maksimal 60 menit. Kepatuhan SOP Anda dinilai 100%.`, 'gm-prompt');
            addMessage(`Langkah #${currentStep}: ${responseData.next_narration}`, 'gm-prompt');
            
            loadOptions(responseData.next_options);
            updateDisplay();
        }

        startButtonEl.onclick = () => {
            const selectedThemeKey = themeSelectEl.value;
            
            if (selectedThemeKey === 'custom') {
                currentTheme = customThemeEl.value.trim();
                if (!currentTheme) {
                    alert(`Mohon masukkan tema khusus dan krisisnya.`);
                    return;
                }
            } else {
                currentTheme = themeMap[selectedThemeKey];
            }
            
            difficulty = difficultySelectEl.value;

            // Reset status
            skor = 100;
            waktu = 60;
            currentStep = 0;
            gameStatus = 'running';
            narasiAreaEl.innerHTML = '';
            finalReviewData = null; // Reset data review
            
            initiateFirstStep();
        };
        
        async function generateReview() {
            const reviewPrompt = `Berdasarkan hasil game simulasi krisis ini:
            - Tema Krisis: ${currentTheme}
            - Tingkat Kesulitan: ${difficulty}
            - Skor Kepatuhan SOP Akhir: ${skor}
            - Sisa Waktu Akhir: ${waktu} menit
            - Total Langkah: ${currentStep}
            
            Buatlah analisis kinerja pemain, berikan penilaian kualitatif (A+, A, B, C, D), ringkasan mengapa nilai tersebut diberikan, dan berikan 3 saran tindak lanjut spesifik untuk meningkatkan kepatuhan SOP di tema ini.`;
            
            const reviewSystemInstruction = `Anda adalah seorang Konsultan Standar Operasional Prosedur (SOP) dan Analis Kinerja. Anda harus memberikan ulasan akhir yang obyektif dan konstruktif dalam format JSON, berdasarkan data kinerja yang diberikan.`;
            
            return await callGeminiAPI(reviewPrompt, reviewSystemInstruction, ReviewSchema);
        }

        async function endGame(title) {
            gameStatus = 'ended';
            disableInput(true);
            stopTtsButtonEl.classList.add('hidden'); 
            stopTTS(); 
            
            modalTitleEl.textContent = title.replace(/[\*\$]/g, '');

            modalTitleEl.classList.remove('text-red-600', 'text-green-600', 'text-orange-600');

            if (skor > 80 && waktu > 10) {
                modalTitleEl.classList.add('text-green-600');
            } else if (skor > 50 && waktu > 0) {
                modalTitleEl.classList.add('text-orange-600');
            } else {
                modalTitleEl.classList.add('text-red-600');
            }

            modalSummaryEl.innerHTML = `
                <div class="font-bold text-lg mb-1">Status Final:</div>
                Skor Akhir: <b class="text-indigo-600">${skor}%</b> | Sisa Waktu: <b class="text-indigo-600">${waktu}</b> menit. <br>
                Langkah diselesaikan: ${currentStep}.
            `;

            reviewGradeEl.textContent = 'Memuat...';
            reviewSummaryEl.textContent = 'Memuat analisis dari AI...';
            followUpAdviceEl.innerHTML = '';
            downloadReviewButtonEl.classList.add('hidden'); // Sembunyikan tombol download sementara

            modalEl.classList.remove('hidden');

            const reviewData = await generateReview();

            if (reviewData) {
                finalReviewData = reviewData; // Simpan data review
                
                reviewGradeEl.textContent = reviewData.assessment_grade;
                reviewSummaryEl.textContent = reviewData.review_summary;
                
                followUpAdviceEl.innerHTML = '';
                reviewData.follow_up_advice.forEach(advice => {
                    const li = document.createElement('li');
                    li.textContent = advice;
                    followUpAdviceEl.appendChild(li);
                });
                
                downloadReviewButtonEl.classList.remove('hidden'); // Tampilkan tombol download
            } else {
                reviewGradeEl.textContent = 'N/A';
                reviewSummaryEl.textContent = 'Gagal memuat analisis kinerja.';
                followUpAdviceEl.innerHTML = '<li>Coba restart aplikasi dan mainkan lagi.</li>';
            }
        }
        
        /**
         * Fungsi untuk mengunduh review sebagai file TXT (BARU)
         */
        function downloadReview() {
            if (!finalReviewData) {
                alert("Data review belum tersedia.");
                return;
            }

            const adviceText = finalReviewData.follow_up_advice.map((item, index) => `${index + 1}. ${item}`).join('\n');
            const reviewContent = `
======================================================
REVIEW KINERJA SIMULASI KRISIS - SOP CRISIS MANAGEMENT
======================================================

TEMA SIMULASI: ${currentTheme}
TINGKAT KESULITAN: ${difficulty}
STATUS AKHIR: ${modalTitleEl.textContent}
TOTAL LANGKAH: ${currentStep}

======================================================
SKOR DAN PENILAIAN
======================================================
SKOR KEPATUHAN SOP: ${skor}%
SISA WAKTU: ${waktu} menit

NILAI KUALITATIF: ${finalReviewData.assessment_grade}
RINGKASAN ANALISIS: ${finalReviewData.review_summary}
JUDUL PENILAIAN: ${finalReviewData.assessment_title}

======================================================
SARAN TINDAK LANJUT DARI KONSULTAN SOP
======================================================
${adviceText}

======================================================
`;
            const filename = `Review_SOP_${currentTheme.replace(/[^a-zA-Z0-9]/g, '_')}_Skor${skor}.txt`;
            const blob = new Blob([reviewContent], { type: 'text/plain;charset=utf-8' });
            
            // Membuat link sementara untuk trigger download
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Attach download function to the button
        downloadReviewButtonEl.onclick = downloadReview;

        // Kembali ke layar awal
        restartButtonEl.onclick = () => {
            modalEl.classList.add('hidden');
            gameScreenEl.classList.add('hidden');
            startScreenEl.classList.remove('hidden');
            gameSubtitleEl.textContent = 'Pilih Tema Jurusan Anda';
            gameStatus = 'initial';
            stopTtsButtonEl.classList.add('hidden'); 
            stopTTS();
        };

        // Menampilkan/menyembunyikan input custom theme
        themeSelectEl.onchange = () => {
            if (themeSelectEl.value === 'custom') {
                customThemeInputEl.classList.remove('hidden');
            } else {
                customThemeInputEl.classList.add('hidden');
            }
        };

        // Inisialisasi: Pastikan layar awal terlihat saat dimuat
        document.addEventListener('DOMContentLoaded', () => {
            gameScreenEl.classList.add('hidden');
            startScreenEl.classList.remove('hidden');
            gameSubtitleEl.textContent = 'Pilih Tema Jurusan Anda';
        });

    </script>
</body>
</html>
