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
    <title>Simulasi Negosiasi - GM Gemini</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        .game-master-bg {
            background: linear-gradient(135deg, #1f2937 0%, #0f172a 100%);
        }
        .text-gm-accent {
            color: #fcd34d; /* Amber-300 */
        }
        /* Custom scrollbar for narrative area */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #1f2937;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans min-h-screen flex items-center justify-center p-4">

    <div id="gameContainer" class="game-master-bg text-white rounded-xl shadow-2xl w-full max-w-4xl p-6 md:p-10 border-4 border-amber-400">
         <header 
    class="relative text-center text-white py-16 md:py-20 rounded-xl mb-10 overflow-hidden"
    style="background-image: url('../admin/foto/<?= $data['banner'] ?>'); background-size: cover; background-position: center; min-height: 280px;">

    <!-- Overlay lembut -->
    <div class="absolute inset-0 bg-primary/60 backdrop-blur-[1px]"></div>

    <!-- Logo di pojok kiri atas -->
    <div class="absolute top-4 left-4 z-20 flex items-center gap-2">
        <img src="../admin/foto/<?= $data['logo'] ?>" 
             alt="Logo Sekolah" 
             class="w-14 h-14 md:w-16 md:h-16 rounded-lg shadow-md border border-white/30 bg-white/10 backdrop-blur-[2px] p-1">
        <span class="hidden md:block font-semibold text-white drop-shadow-md"><?= $data['nama'] ?></span>
    </div>

    <!-- Konten Header -->
    <div class="relative z-10">
         <h1 class="text-3xl md:text-4xl font-bold text-amber-400 mb-2 tracking-wide">
                Simulasi Negosiasi <span class="text-lg block md:inline-block text-gray-300">GM Gemini</span>
            </h1>
            <p class="text-gray-400 text-sm">Pilih tema dan level kesulitan, lalu masukkan tanggapan Anda!</p>
    </div>
</header>
        <header class="text-center mb-8">
         
            <!-- SCENARIO SELECT & CUSTOM INPUT -->
            <div id="scenarioSelection" class="mt-4 flex flex-col items-center p-4 bg-gray-900 rounded-xl">
                
                <!-- PILIH TEMA -->
                <label for="scenarioSelect" class="text-gray-300 mb-2 text-lg font-semibold">Pilih Tema Negosiasi:</label>
                <select id="scenarioSelect" class="p-3 rounded-lg bg-gray-700 border border-gray-600 text-white w-full max-w-xs md:max-w-md cursor-pointer focus:ring-amber-500 focus:border-amber-500">
                    <!-- Options populated by JS -->
                </select>

                <!-- LEVEL KESULITAN INPUT (NEW) -->
                <label for="difficultySelect" class="text-gray-300 mt-4 mb-2 text-lg font-semibold block">Pilih Level Kesulitan:</label>
                <select id="difficultySelect" class="p-3 rounded-lg bg-gray-700 border border-gray-600 text-white w-full max-w-xs md:max-w-md cursor-pointer focus:ring-amber-500 focus:border-amber-500">
                    <option value="SMA/SMK">SMA/SMK (Dasar - Negosiasi Langsung)</option>
                    <option value="Mahasiswa">Mahasiswa (Menengah - Negosiasi Strategis)</option>
                    <option value="Profesional" selected>Profesional (Lanjutan - Negosiasi Kompleks & Psikologis)</option>
                </select>
                
                <!-- CUSTOM SCENARIO INPUT AREA -->
                <div id="customScenarioInput" class="hidden mt-4 w-full max-w-md">
                    <label for="customInputText" class="block text-sm font-medium text-gray-300 mb-2">
                        Deskripsikan skenario (Contoh: "Saya adalah manajer yang meminta kenaikan gaji 15% dari CEO Tuan Bima. Gaji saya saat ini Rp80.000.000."):
                    </label>
                    <textarea id="customInputText" rows="3" placeholder="Masukkan detail skenario negosiasi kustom Anda di sini..." class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-amber-500 focus:border-amber-500 resize-none"></textarea>
                </div>
                <!-- END CUSTOM SCENARIO INPUT AREA -->

            </div>
            <!-- END SCENARIO SELECT -->
            
        </header>

        <!-- Status Bar -->
        <div id="statusBar" class="flex flex-wrap justify-between gap-4 mb-8 p-4 bg-gray-700 rounded-lg shadow-inner hidden">
            <div class="flex-1 min-w-[150px]">
                <p class="text-xs uppercase font-semibold text-gray-400">Tingkat Kepercayaan</p>
                <div class="relative pt-1">
                    <div class="overflow-hidden h-2 mb-2 text-xs flex rounded bg-red-200">
                        <div id="trustBar" style="width:50%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500 transition-all duration-500"></div>
                    </div>
                    <span id="trustValue" class="text-sm font-medium text-green-400">50</span> / 100
                </div>
            </div>
            <div class="flex-1 min-w-[150px] text-right">
                <p class="text-xs uppercase font-semibold text-gray-400">Nilai Negosiasi (Harga/Nilai Kontrak)</p>
                <span id="negotiationValue" class="text-2xl font-bold text-gm-accent">Rp10.000.000</span>
            </div>
        </div>

        <!-- Narrative Area -->
        <div id="narrativeArea" class="bg-gray-800 p-6 rounded-xl shadow-lg h-64 overflow-y-auto custom-scrollbar mb-8">
            <p id="narrativeText" class="text-lg text-gray-300 leading-relaxed italic">
                Pilih skenario di atas dan klik "Mulai Negosiasi Baru" di bawah.
            </p>
        </div>

        <!-- Options (Hidden) - Only used as placeholder for old logic -->
        <div id="optionsArea" class="hidden"></div>
        <!-- END Options (Hidden) -->

        <!-- TEXT INPUT AREA -->
        <div id="textInputArea" class="mt-4 hidden">
            <label for="playerInput" class="block text-sm font-medium text-gray-300 mb-2">Tanggapan Kustom Anda:</label>
            <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-3">
                <textarea id="playerInput" rows="4" placeholder="Input akan aktif setelah Tuan Bima selesai berbicara (sekitar 1 detik)..." class="flex-grow p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-amber-500 focus:border-amber-500 resize-none" disabled></textarea>
                <button id="sendButton" onclick="handleInput()" class="bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold py-3 px-6 rounded-lg shadow-md transition-all duration-300 w-full md:w-auto" disabled>
                    Kirim
                </button>
            </div>
            <div id="suggestionArea" class="mt-3">
                <!-- Suggestions will be rendered here -->
            </div>
        </div>
        <!-- END TEXT INPUT AREA -->

        <!-- Review and Export Area (NEW) -->
        <div id="reviewArea" class="mt-8 p-6 bg-gray-900 rounded-xl shadow-inner hidden">
            <h2 class="text-2xl font-bold text-amber-400 mb-4">Laporan & Penilaian Negosiasi Akhir</h2>
            <div id="reviewContent" class="text-gray-300 space-y-3">
                <!-- Review details will be inserted here -->
            </div>
            <button id="exportButton" onclick="exportReview()" class="mt-6 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-300">
                Ekspor Laporan (TXT)
            </button>
        </div>

        <!-- Loading/Game Over Message -->
        <div id="messageArea" class="text-center mt-8">
            <div id="loadingIndicator" class="hidden text-amber-400 text-xl font-semibold flex items-center justify-center animate-pulse">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Tuan Bima sedang mempertimbangkan...
            </div>
            <p id="gameOverText" class="text-3xl font-bold text-red-500 hidden"></p>
            <button id="restartButton" onclick="startGame()" class="mt-4 bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-300">
                Mulai Negosiasi Baru
            </button>
        </div>

    </div>

    <script>
        // --- Konfigurasi API dan State Game ---
        const GEMINI_MODEL = "<?php echo $model; ?>";
const API_KEY = "<?php echo $apiKey; ?>"; 
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${API_KEY}`;

        // State Game
        let trustLevel = 50; // Max 100
        let negotiationValue = 10000000; // Default Harga (Rp10.000.000)
        let initialNegotiationValue = 0; // NEW: Menyimpan nilai awal untuk penilaian
        let currentScenario = "";
        let isGameActive = false;
        let selectedScenarioContext = ""; 
        let isFirstTurn = true; // Status untuk melacak giliran pertama

        // Scenarios List (Nilai default disesuaikan ke Rupiah)
        const SCENARIOS = [
            { id: 'chips', name: 'Chip Komputer Langka (Harga)', value: 1000000000, context: 'Pemain adalah pembeli yang mencoba mendapatkan harga terbaik untuk 100 unit chip komputer langka dari Anda (Tuan Bima). Harga standar adalah Rp1.000.000.000. Negosiasi harus berfokus pada harga, syarat pembayaran, dan pengiriman.' },
            { id: 'mobil', name: 'Jual Beli Mobil Klasik (Kondisi & Harga)', value: 500000000, context: 'Anda (Tuan Bima) menjual mobil klasik langka senilai Rp500.000.000. Pemain adalah pembeli yang harus bernegosiasi mengenai kondisi mobil, riwayat perbaikan, dan harga akhir. Kepercayaan meningkat jika pemain menunjukkan pengetahuan tentang mobil klasik.' },
            { id: 'kontrak', name: 'Kontrak Layanan TI Jangka Panjang (Syarat)', value: 5000000000, context: 'Anda (Tuan Bima) adalah penyedia layanan TI, menegosiasikan kontrak 5 tahun dengan pemain. Harga awal kontrak adalah Rp5.000.000.000. Negosiasi harus berfokus pada SLA, penalti keterlambatan, dan klausul pembatalan.' },
            { id: 'custom', name: 'Buat Skenario Kustom', value: 10000000, context: 'Skenario kustom yang akan ditentukan oleh input pemain.' }
        ];

        // Elemen DOM
        const narrativeTextEl = document.getElementById('narrativeText');
        const trustBarEl = document.getElementById('trustBar');
        const trustValueEl = document.getElementById('trustValue');
        const negotiationValueEl = document.getElementById('negotiationValue');
        const loadingIndicatorEl = document.getElementById('loadingIndicator');
        const gameOverTextEl = document.getElementById('gameOverText');
        const restartButtonEl = document.getElementById('restartButton');
        const messageAreaEl = document.getElementById('messageArea');
        const statusBarEl = document.getElementById('statusBar');
        
        const scenarioSelectEl = document.getElementById('scenarioSelect');
        const difficultySelectEl = document.getElementById('difficultySelect');
        const customScenarioInputEl = document.getElementById('customScenarioInput');
        const customInputTextEl = document.getElementById('customInputText');

        const textInputAreaEl = document.getElementById('textInputArea');
        const playerInputEl = document.getElementById('playerInput');
        const sendButtonEl = document.getElementById('sendButton');
        const suggestionAreaEl = document.getElementById('suggestionArea');
        const scenarioSelectionEl = document.getElementById('scenarioSelection');

        const reviewAreaEl = document.getElementById('reviewArea'); // NEW
        const reviewContentEl = document.getElementById('reviewContent'); // NEW


        // --- PENGATURAN MODEL (GEMINI SEBAGAI GAME MASTER) ---
        // PENTING: Perubahan besar pada system prompt untuk menambahkan alur cerita dan kompleksitas
        const systemPrompt = `
            Anda adalah Game Master dan lawan negosiasi yang cerdas, keras, namun adil, bernama **Tuan Bima**. Anda harus memimpin pemain melalui skenario negosiasi yang menantang dengan narasi yang mendalam dan alur cerita yang berkembang.

            Setiap respons Anda HARUS berupa objek JSON yang valid dan lengkap mengikuti skema yang disediakan.

            KONTROL ALUR GAME DAN NARASI:
            1. **Gunakan bahasa yang kaya dan deskriptif** dalam 'narrativeResponse' Anda. Jadikan negosiasi terasa seperti pertemuan bisnis nyata dengan deskripsi singkat tentang suasana hati, gerakan tubuh, atau lingkungan Tuan Bima.
            2. **Sesuaikan alur cerita dan respons dengan Level Kesulitan yang dipilih:**

                * **Level SMA/SMK (Dasar):** Negosiasi harus **langsung, logis, dan transparan**. Tuan Bima akan bereaksi terhadap angka dan fakta dasar. 'narrativeResponse' harus pendek dan fokus pada inti masalah. 'nextScenario' fokus pada penawaran/permintaan berikutnya.
                * **Level Mahasiswa (Menengah):** Negosiasi harus **strategis dan berbasis data**. Tuan Bima akan mencari alasan yang didukung data, analisis, atau perbandingan pasar. 'narrativeResponse' mencakup sedikit manipulasi data atau 'bluff'. 'nextScenario' akan mengenalkan variabel tambahan (misalnya, tenggat waktu, kondisi pasar).
                * **Level Profesional (Lanjutan):** Negosiasi harus **kompleks, berlapis, dan psikologis**. Tuan Bima akan menggunakan taktik negosiasi tingkat tinggi (misalnya, *good cop/bad cop* tersirat, penundaan, tekanan waktu, BATNA). 'narrativeResponse' akan menggambarkan perubahan suasana hati Tuan Bima (misalnya, tiba-tiba dingin atau terlalu ramah). 'nextScenario' akan memaksa pemain untuk memilih antara moralitas dan keuntungan, atau menghadapi konflik internal.

            3. Update status (trustChange, valueChange) harus mencerminkan dampak negosiasi.

            LEVEL KESULITAN SAAT INI (Penting): **{{DIFFICULTY_LEVEL}}**.
            KONTEKS NEGOSIASI: {{SCENARIO_CONTEXT}}
            
            Tingkat Kepercayaan saat ini: {trustLevel}/100. Nilai Negosiasi saat ini: {negotiationValue} Rupiah.
            
            SKEMA JSON yang HARUS Anda ikuti (Sangat Penting):
            {
              "narrativeResponse": "string", // Reaksi Tuan Bima, termasuk deskripsi suasana/emosi. HARUS mencantumkan tanggapan pemain yang baru dianalisis. (Maks 150 kata)
              "statusUpdate": {
                "trustChange": "number", // Perubahan Tingkat Kepercayaan (+ atau - angka integer). Max ±20 per langkah.
                "valueChange": "number" // Perubahan Nilai Negosiasi (Harga beli/nilai kontrak dalam Rupiah). Angka NEGATIF untuk pengurangan harga (baik untuk pemain), POSITIF untuk kenaikan harga (buruk untuk pemain). Max ±100.000.000 per langkah.
              },
              "nextScenario": "string", // Situasi atau pertanyaan baru yang menantang pemain, MENUNTUT RESPON TEKS BARU.
              "options": [
                {"text": "string"}, // 3 saran tanggapan strategis untuk inspirasi pemain, disesuaikan dengan level kesulitan.
                {"text": "string"},
                {"text": "string"}
              ],
              "isGameOver": "boolean", // Atur ke true jika pemain mencapai kesepakatan, gagal, atau Trust terlalu rendah/tinggi.
              "finalReview": "string" // Review/analisis performa akhir Tuan Bima (Max 150 kata). HANYA diisi saat "isGameOver: true".
            }
        `;

        // --- Fungsi Utilitas ---

        /** Mengubah angka menjadi format mata uang Rupiah */
        function formatCurrency(num) {
            // Gunakan locale 'id-ID' untuk format Rupiah
            return 'Rp' + new Intl.NumberFormat('id-ID', { 
                maximumFractionDigits: 0 
            }).format(num);
        }

        /** Memperbarui UI status bar */
        function updateUI() {
            // Batasi Trust Level antara 0 dan 100
            trustLevel = Math.max(0, Math.min(100, trustLevel));

            // Update Trust UI
            trustValueEl.textContent = trustLevel;
            trustBarEl.style.width = trustLevel + '%';
            trustBarEl.className = `shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center transition-all duration-500 ${
                trustLevel >= 70 ? 'bg-green-500' : trustLevel >= 30 ? 'bg-amber-500' : 'bg-red-500'
            }`;
            trustValueEl.className = `text-sm font-medium ${
                trustLevel >= 70 ? 'text-green-400' : trustLevel >= 30 ? 'text-amber-400' : 'text-red-400'
            }`;

            // Update Value UI
            negotiationValueEl.textContent = formatCurrency(negotiationValue);
            negotiationValueEl.classList.toggle('text-gm-accent', negotiationValue <= initialNegotiationValue);
            negotiationValueEl.classList.toggle('text-red-500', negotiationValue > initialNegotiationValue);
        }

        /** Mengakhiri game dan menampilkan laporan akhir */
        function endGame(message, gmResponse) {
            isGameActive = false;
            // Disable input & hide input area
            playerInputEl.disabled = true;
            sendButtonEl.disabled = true;
            textInputAreaEl.classList.add('hidden');
            
            // Show necessary end-game UI
            statusBarEl.classList.remove('hidden'); 
            scenarioSelectionEl.classList.remove('hidden');
            customScenarioInputEl.classList.add('hidden');
            scenarioSelectEl.disabled = false;
            difficultySelectEl.disabled = false;

            // Show outcome message
            gameOverTextEl.textContent = "NEGOSIASI SELESAI";
            gameOverTextEl.classList.remove('hidden');
            restartButtonEl.classList.remove('hidden');
            loadingIndicatorEl.classList.add('hidden');
            messageAreaEl.classList.remove('hidden');
            
            // Append final GM message to narrative
            narrativeTextEl.innerHTML += `<p class="mt-4 text-2xl font-bold text-red-500">${message}</p>`;
            suggestionAreaEl.innerHTML = '';
            narrativeTextEl.scrollTop = narrativeTextEl.scrollHeight;

            // NEW: Call the function to display the final report
            displayFinalReport(gmResponse); 
        }

        // --- Logika Penilaian dan Pelaporan (NEW) ---

        function calculateScoreAndFeedback(initialValue, finalValue, finalTrust, difficulty) {
            const maxScore = 100;
            const valueDifference = initialValue - finalValue; // Positive is good (buyer gets lower price)
            
            // 1. Value Score (70% weight)
            let valueScore = 0;
            let valueFeedback = "Hasil negosiasi Anda:";

            if (valueDifference >= 0) { // Berhasil menurunkan atau mempertahankan harga
                const percentageGain = (valueDifference / initialValue) * 100;
                
                if (percentageGain >= 15) {
                    valueScore = 70; 
                    valueFeedback += " **Kenaikan Nilai Sangat Luar Biasa!** Anda mencapai diskon >15%.";
                } else if (percentageGain >= 5) {
                    valueScore = 50 + (percentageGain - 5) * 1.5;
                    valueFeedback += " **Kenaikan Nilai yang Solid.** Anda mencapai diskon yang signifikan.";
                } else if (percentageGain > 0) {
                    valueScore = 35 + percentageGain * 2;
                    valueFeedback += " **Kenaikan Nilai Marginal.** Anda berhasil mempertahankan dan sedikit menurunkan harga.";
                } else { // valueDifference is 0
                     valueScore = 40;
                     valueFeedback += " **Tidak Ada Perubahan Nilai.** Netral, Anda mempertahankan harga awal.";
                }
            } else { // Harga naik
                const percentageLoss = (Math.abs(valueDifference) / initialValue) * 100;
                valueScore = Math.max(0, 40 - (percentageLoss * 3)); // Penalty for loss
                valueFeedback += " **Terjadi Penurunan Nilai!** Harga menjadi ${percentageLoss.toFixed(1)}% lebih mahal dari yang ditawarkan Tuan Bima.";
            }
            
            // 2. Trust Score (30% weight)
            let trustScore = 0;
            let trustFeedback;

            if (finalTrust >= 85) {
                trustScore = 30;
                trustFeedback = "Tingkat Kepercayaan **Sangat Tinggi**. Anda membangun hubungan yang sangat baik.";
            } else if (finalTrust >= 60) {
                trustScore = 20 + (finalTrust - 60) * 0.4;
                trustFeedback = "Tingkat Kepercayaan **Cukup Kuat**. Hubungan bisnis yang solid.";
            } else if (finalTrust >= 30) {
                trustScore = 10;
                trustFeedback = "Tingkat Kepercayaan **Rendah**. Anda mencapai kesepakatan, tetapi hubungan tegang.";
            } else {
                 trustScore = 0;
                 trustFeedback = "Tingkat Kepercayaan **Sangat Kritis**. Kesepakatan diwarnai keraguan atau Tuan Bima marah.";
            }
            
            // 3. Difficulty Modifier 
            let finalScore = valueScore + trustScore;
            let difficultyBonus = 0;

            if (difficulty === 'Profesional') {
                difficultyBonus = finalScore > 75 ? 5 : finalScore < 30 ? -10 : 0;
            } else if (difficulty === 'Mahasiswa') {
                difficultyBonus = finalScore > 80 ? 3 : 0;
            }

            finalScore = Math.round(finalScore + difficultyBonus);
            
            return {
                score: Math.max(0, Math.min(100, finalScore)),
                valueFeedback,
                trustFeedback,
                difficulty
            };
        }


        function displayFinalReport(gmResponse) {
            reviewAreaEl.classList.remove('hidden');

            // Gunakan initialNegotiationValue yang sudah disimpan
            const initialValue = initialNegotiationValue; 
            const difficulty = difficultySelectEl.value;
            const scenarioName = SCENARIOS.find(s => s.id === scenarioSelectEl.value).name;

            // 1. Calculate Score
            const result = calculateScoreAndFeedback(initialValue, negotiationValue, trustLevel, difficulty);

            // 2. Determine Overall Rank
            let rank;
            let rankColor;
            if (result.score >= 90) { rank = "Master Negosiator (Sangat Luar Biasa)"; rankColor = "text-green-400"; }
            else if (result.score >= 75) { rank = "Negosiator Hebat (Di Atas Rata-Rata)"; rankColor = "text-lime-400"; }
            else if (result.score >= 50) { rank = "Negosiator Kompeten (Rata-Rata)"; rankColor = "text-amber-400"; }
            else if (result.score >= 25) { rank = "Negosiator Pemula (Perlu Latihan)"; rankColor = "text-orange-400"; }
            else { rank = "Perlu Bimbingan (Gagal Total)"; rankColor = "text-red-400"; }
            
            // Hitung Perubahan Nilai Mutlak
            const absoluteValueChange = initialValue - negotiationValue;

            // 3. Construct Review Content
            let contentHTML = `
                <div class="p-4 rounded-lg border-2 border-amber-500 bg-gray-800">
                    <p class="text-xl font-bold">Skor Akhir: <span class="${rankColor} text-3xl">${result.score}/100</span></p>
                    <p class="text-lg font-semibold text-gray-400">Peringkat: <span class="${rankColor} font-bold">${rank}</span></p>
                </div>
                
                <h3 class="text-xl font-semibold text-amber-300 mt-4">Statistik Negosiasi</h3>
                <ul class="list-disc list-inside ml-4 text-gray-300">
                    <li>**Level Kesulitan:** ${difficulty}</li>
                    <li>**Nilai Awal Tuan Bima:** ${formatCurrency(initialValue)}</li>
                    <li>**Nilai Akhir Kesepakatan:** ${formatCurrency(negotiationValue)}</li>
                    <li>**Perubahan Nilai:** ${absoluteValueChange >= 0 ? 'Meningkat' : 'Menurun'} (${formatCurrency(Math.abs(absoluteValueChange))})</li>
                    <li>**Kepercayaan Akhir:** ${trustLevel}/100</li>
                </ul>
                
                <h3 class="text-xl font-semibold text-amber-300 mt-4">Umpan Balik Kinerja</h3>
                <p class="mt-2 text-gray-300">${result.valueFeedback}</p>
                <p class="mt-2 text-gray-300">${result.trustFeedback}</p>

                <h3 class="text-xl font-semibold text-amber-300 mt-4">Analisis Tuan Bima (GM)</h3>
                <div class="bg-gray-800 p-4 rounded-lg border border-gray-700 italic">
                    <p class="text-gray-300">${gmResponse.finalReview || "GM tidak memberikan ulasan akhir. Negosiasi berakhir tiba-tiba."}</p>
                </div>
            `;
            reviewContentEl.innerHTML = contentHTML;
        }
        
        // --- Export Function ---
        function exportReview() {
            const reviewText = reviewContentEl.innerText;
            const date = new Date().toISOString().slice(0, 10);
            const filename = `Laporan_Negosiasi_${date}.txt`;
            const scenarioName = SCENARIOS.find(s => s.id === scenarioSelectEl.value).name;

            const fullReport = `=== LAPORAN SIMULASI NEGOSIASI ===\n\n` + 
                               `Tanggal: ${date}\n` + 
                               `Skenario: ${scenarioName}\n` +
                               `Level Kesulitan: ${difficultySelectEl.value}\n\n` +
                               reviewText + 
                               `\n\n=== AKHIR LAPORAN ===`;

            const blob = new Blob([fullReport], { type: 'text/plain;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        

        async function fetchWithRetry(url, options, retries = 3) {
            for (let i = 0; i < retries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (!response.ok) {
                        // Throw an error to be caught below, triggering a retry
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response;
                } catch (error) {
                    if (i < retries - 1) {
                        const delay = Math.pow(2, i) * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        // console.log(`Retry attempt ${i + 1} after ${delay}ms`);
                    } else {
                        throw error;
                    }
                }
            }
        }

        async function callGeminiApi(userChoiceText) {
            if (!isGameActive) return;

            loadingIndicatorEl.classList.remove('hidden');
            
            // Get current difficulty level
            const selectedDifficulty = difficultySelectEl.value;
            
            // Determine if this is the final turn by checking if user explicitly said 'selesai' 
            // or if trust is critically low, forcing an immediate final analysis from GM.
            const forcedFinalTurn = userChoiceText.toLowerCase().includes('selesai') || trustLevel <= 15;
            
            // Construct dynamic system prompt with current context
            const rawNegotiationValue = negotiationValue; 
            const dynamicSystemPrompt = systemPrompt
                .replace("{{SCENARIO_CONTEXT}}", selectedScenarioContext)
                .replace("{{DIFFICULTY_LEVEL}}", selectedDifficulty)
                .replace("{trustLevel}", trustLevel)
                .replace("{negotiationValue}", rawNegotiationValue);


            const contextPrompt = `\n\nKeputusan Pemain: "${userChoiceText}".\n\nApa dampaknya, bagaimana Tuan Bima merespons (narrativeResponse), apa skenario berikutnya (nextScenario), dan apa 3 saran tanggapan baru (options)?
            ${forcedFinalTurn ? "\n\nINI ADALAH GILIRAN AKHIR YANG DIPAKSA/SUDAH TERCAPAI. Pastikan 'isGameOver' diatur ke TRUE dan berikan analisis profesional (max 150 kata) dalam 'finalReview' tentang strategi pemain, keberhasilan, dan apa yang bisa ditingkatkan. Jika 'finalReview' kosong, game tidak dapat menyelesaikan laporan akhir." : ""}`;

            const payload = {
                contents: [{ parts: [{ text: contextPrompt }] }],
                systemInstruction: { parts: [{ text: dynamicSystemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "narrativeResponse": { "type": "STRING", "description": "Respon naratif Tuan Bima." },
                            "statusUpdate": {
                                "type": "OBJECT",
                                "properties": {
                                    "trustChange": { "type": "INTEGER" },
                                    "valueChange": { "type": "INTEGER" }
                                }
                            },
                            "nextScenario": { "type": "STRING" },
                            "options": {
                                "type": "ARRAY",
                                "items": {
                                    "type": "OBJECT",
                                    "properties": { "text": { "type": "STRING" } }
                                }
                            },
                            "isGameOver": { "type": "BOOLEAN" },
                            "finalReview": { "type": "STRING", "description": "Review akhir (hanya diisi jika isGameOver: true)." }
                        }
                    }
                }
            };

            try {
                const response = await fetchWithRetry(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                
                if (!jsonText) {
                    throw new Error("Respon dari GM kosong atau tidak terstruktur.");
                }

                const gmResponse = JSON.parse(jsonText);
                processGmResponse(gmResponse);

            } catch (error) {
                console.error("Kesalahan API atau Parsing:", error);
                loadingIndicatorEl.classList.add('hidden');
                narrativeTextEl.innerHTML = `Terjadi kesalahan koneksi atau Tuan Bima kehabisan kata-kata. Coba muat ulang atau mulai game baru. Kesalahan: ${error.message}`;
                restartButtonEl.classList.remove('hidden');
                playerInputEl.disabled = true;
                sendButtonEl.disabled = true;
            } finally {
                loadingIndicatorEl.classList.add('hidden');
            }
        }

        // --- Pemrosesan Respon GM dan Update Game State ---

        function processGmResponse(response) {
            // 1. Update State
            const { trustChange, valueChange } = response.statusUpdate;
            trustLevel += trustChange;
            negotiationValue += valueChange;
            currentScenario = response.nextScenario;
            
            // 2. Tampilkan Narrative Response
            let responseHtml = `<p class="text-lg text-gray-300 leading-relaxed">
                ${response.narrativeResponse}
            </p>`;
            
            // Tampilkan perubahan status secara visual
            const trustSign = trustChange >= 0 ? '+' : '';
            const valueChangeAbs = Math.abs(valueChange);
            const trustColor = trustChange > 0 ? 'text-green-400' : trustChange < 0 ? 'text-red-400' : 'text-gray-400';
            // Value color is green if valueChange is negative (good for player/buyer)
            const valueColor = valueChange < 0 ? 'text-green-400' : valueChange > 0 ? 'text-red-400' : 'text-gray-400'; 
            
            responseHtml += `<div class="mt-4 p-3 bg-gray-900/50 rounded-lg text-sm italic">
                <p class="${trustColor}">Kepercayaan: ${trustSign}${trustChange} &rarr; **${trustLevel}/100**</p>
                <p class="${valueColor}">Perubahan Nilai: ${valueChange < 0 ? '-' : '+'}${formatCurrency(valueChangeAbs)} &rarr; Nilai Baru: **${formatCurrency(negotiationValue)}**</p>
            </div>`;

            // Mengganti pesan loading pada giliran pertama
            if (isFirstTurn) {
                narrativeTextEl.innerHTML = `<p class="text-2xl font-bold text-amber-400">Tuan Bima Membuka Negosiasi:</p>${responseHtml}`;
                isFirstTurn = false;
            } else {
                narrativeTextEl.innerHTML += `<p class="mt-4 text-2xl font-bold text-amber-400">Tuan Bima Menanggapi:</p>${responseHtml}`;
            }

            // 3. Cek Game Over dari GM atau Kondisi Internal
            if (response.isGameOver || trustLevel <= 5) { 
                const finalMessage = response.isGameOver ? response.narrativeResponse : `Tingkat Kepercayaan jatuh terlalu rendah (${trustLevel}). Tuan Bima menghentikan negosiasi karena merasa dipermainkan. Anda kehilangan kesempatan ini.`;
                endGame(finalMessage, response);
                return;
            }
            
            // Logika Game Over untuk skenario standar (nilai terlalu ekstrem)
            if (scenarioSelectEl.value !== 'custom') {
                 if (negotiationValue > initialNegotiationValue * 1.5) { // 50% di atas harga awal
                    const finalMessage = `Harga melonjak terlalu tinggi (${formatCurrency(negotiationValue)}). Anda tidak mampu lagi dan harus mundur.`;
                    // Force a final review from GM
                    callGeminiApi(`Negosiasi dipaksa berakhir karena harga yang diminta Tuan Bima (${formatCurrency(negotiationValue)}) terlalu tinggi. Berikan 'finalReview' untuk skenario ini.`);
                    return;
                }
            }


            // 4. Tampilkan Next Scenario dan Saran
            currentScenario = response.nextScenario;
            setTimeout(() => {
                narrativeTextEl.innerHTML += `<p class="mt-6 text-lg font-semibold text-amber-400 border-t border-gray-600 pt-4">Tuan Bima: ${currentScenario}</p>`;
                
                renderSuggestions(response.options); 
                
                // Re-enable input
                playerInputEl.disabled = false;
                sendButtonEl.disabled = false;
                playerInputEl.value = '';
                playerInputEl.focus();
                narrativeTextEl.scrollTop = narrativeTextEl.scrollHeight;

            }, 1000); 

            updateUI();
        }

        function renderSuggestions(suggestions) {
            if (!Array.isArray(suggestions) || suggestions.length === 0) {
                suggestionAreaEl.innerHTML = '';
                return;
            }
            
            const suggestionsHtml = suggestions.map(s => `<span class="inline-block bg-gray-700 text-gray-300 text-sm px-3 py-1 rounded-full mr-2 mb-2 italic cursor-pointer hover:bg-amber-500 hover:text-gray-900" onclick="playerInputEl.value = this.textContent; playerInputEl.focus();">${s.text}</span>`).join('');
            suggestionAreaEl.innerHTML = `<p class="text-sm font-medium text-gray-400 mb-2">Saran Strategi (Klik untuk mengisi, atau ketik sendiri):</p> ${suggestionsHtml}`;
        }

        function handleInput() {
            const input = playerInputEl.value.trim();
            if (!input) {
                playerInputEl.placeholder = "Tolong masukkan tanggapan negosiasi Anda!";
                return;
            }
            
            // Clear suggestions and disable input
            suggestionAreaEl.innerHTML = '';
            playerInputEl.disabled = true;
            sendButtonEl.disabled = true;
            
            // Prepend player's choice to narrative for context
            narrativeTextEl.innerHTML += `<p class="mt-4 text-white text-right italic border-b border-gray-700 pb-2">Anda: **${input}**</p>`;
            narrativeTextEl.scrollTop = narrativeTextEl.scrollHeight;

            callGeminiApi(input);
        }
        
        // --- Inisialisasi Game ---
        
        function handleScenarioChange() {
            const selectedId = scenarioSelectEl.value;
            const selected = SCENARIOS.find(s => s.id === selectedId);
            
            if (selectedId === 'custom') {
                customScenarioInputEl.classList.remove('hidden');
                negotiationValueEl.textContent = "Kustom";
            } else {
                customScenarioInputEl.classList.add('hidden');
                negotiationValueEl.textContent = formatCurrency(selected.value);
            }
        }

        function populateScenarios() {
            SCENARIOS.forEach(scenario => {
                const option = document.createElement('option');
                option.value = scenario.id;
                option.textContent = scenario.name;
                scenarioSelectEl.appendChild(option);
            });
            scenarioSelectEl.addEventListener('change', handleScenarioChange);
        }


        function startGame() {
            const selectedId = scenarioSelectEl.value;
            const scenario = SCENARIOS.find(s => s.id === selectedId);
            const selectedDifficulty = difficultySelectEl.value;
            
            if (!scenario) {
                narrativeTextEl.textContent = "Error: Skenario tidak ditemukan.";
                return;
            }
            
            reviewAreaEl.classList.add('hidden'); // NEW: Sembunyikan laporan akhir

            // 1. Setup Context and Initial Value
            if (selectedId === 'custom') {
                const customText = customInputTextEl.value.trim();
                if (!customText) {
                    narrativeTextEl.innerHTML = `<p class="text-red-400 font-bold">Harap isi deskripsi skenario kustom Anda sebelum memulai!</p>`;
                    return;
                }
                selectedScenarioContext = `Negosiasi kustom yang ditentukan pemain: ${customText}. Anda (Tuan Bima) adalah pihak lawan dalam negosiasi ini. Harap segera tentukan nilai awal yang relevan untuk skenario ini (gunakan Rupiah) dan teruskan negosiasi.`;
                negotiationValue = 50000000; 
                initialNegotiationValue = 50000000;
            } else {
                selectedScenarioContext = scenario.context; 
                negotiationValue = scenario.value;
                initialNegotiationValue = scenario.value; // Simpan nilai awal
            }
            
            // 2. UI State Changes
            scenarioSelectEl.disabled = true; 
            difficultySelectEl.disabled = true;
            scenarioSelectionEl.classList.add('hidden');

            textInputAreaEl.classList.remove('hidden');
            messageAreaEl.classList.add('hidden'); 
            statusBarEl.classList.remove('hidden');
            
            isGameActive = true;
            trustLevel = 50;
            isFirstTurn = true;
            updateUI();
            
            gameOverTextEl.classList.add('hidden');
            restartButtonEl.classList.add('hidden');
            loadingIndicatorEl.classList.remove('hidden');

            playerInputEl.disabled = true;
            sendButtonEl.disabled = true;
            playerInputEl.value = '';
            playerInputEl.placeholder = 'Input akan aktif setelah Tuan Bima selesai berbicara (sekitar 1 detik)...';

            
            // 3. Initial Prompt to GM
            let initialPrompt = '';
            if (selectedId === 'custom') {
                 initialPrompt = `Mulai negosiasi skenario kustom (Level: ${selectedDifficulty}). Konteks: "${customInputTextEl.value.trim()}". Perkenalkan diri Anda sebagai 'Tuan Bima' dan tentukan nilai awal yang logis (gunakan Rupiah) untuk negosiasi ini. Sertakan suasana ruangan dan deskripsi awal yang sesuai dengan level kesulitan.`;
            } else {
                initialPrompt = `Mulai negosiasi skenario: ${scenario.name} (Level: ${selectedDifficulty}). Perkenalkan skenario awal: Anda adalah 'Tuan Bima', dan harga/nilai awal yang Anda tawarkan adalah ${formatCurrency(negotiationValue)}. Sertakan suasana ruangan dan deskripsi awal yang sesuai dengan level kesulitan.`;
            }

            narrativeTextEl.innerHTML = `<p class="text-lg text-amber-300 leading-relaxed italic">
                Negosiasi **${scenario.name === 'Buat Skenario Kustom' ? 'Skenario Kustom' : scenario.name}** pada Level **${selectedDifficulty}** sedang dimulai. Harap tunggu sebentar, Tuan Bima sedang menyiapkan penawaran pertamanya...
            </p>`;
            
            callGeminiApi(initialPrompt);
        }

        // Mulai game saat halaman dimuat
        window.onload = function() {
            populateScenarios();
            // Set initial value based on the first scenario or default
            negotiationValue = SCENARIOS[0].value; 
            initialNegotiationValue = SCENARIOS[0].value;

            // Initial state: ready to start
            playerInputEl.disabled = true;
            sendButtonEl.disabled = true;
            document.getElementById('textInputArea').classList.add('hidden');
            statusBarEl.classList.add('hidden');
            reviewAreaEl.classList.add('hidden');
            narrativeTextEl.innerHTML = `<p class="text-lg text-gray-300 leading-relaxed italic">
                Pilih tema dan level di atas, lalu klik **"Mulai Negosiasi Baru"** untuk bertemu Tuan Bima.
            </p>`;
            updateUI(); 
            handleScenarioChange(); 
        };

    </script>
</body>
</html>