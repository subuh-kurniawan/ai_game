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
    <title>Entrepreneur Simulator: Game Master AI</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#10b981', // Emerald 500
                        'secondary': '#facc15', // Amber 400
                        'background-dark': '#1f2937', // Gray 800
                        'surface-dark': '#374151', // Gray 700
                        'accent-blue': '#60a5fa', // Blue 400
                        'risk-red': '#f87171', // Red 400
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        .log-entry {
            border-left: 4px solid;
            padding-left: 1rem;
            margin-bottom: 0.75rem;
        }
        .log-narrative { border-left-color: #facc15; } /* Secondary */
        .log-system { border-left-color: #10b981; } /* Primary */
        .log-decision { border-left-color: #60a5fa; } /* Blue 400 */
        .log-risk { border-left-color: #f87171; } /* Risk Red */
        .decision-button {
            transition: all 0.2s;
            text-align: left;
        }
        .decision-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            opacity: 0.9;
        }
        .decision-button:disabled {
            background-color: #4b5563; /* Gray 600 */
            cursor: not-allowed;
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }
        /* Custom Scrollbar for dark theme */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #374151; /* surface-dark */
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563; /* Gray 600 */
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280; /* Gray 500 */
        }
        /* Style for floating buttons */
        .floating-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 4rem;
            height: 4rem;
            border-radius: 9999px; /* full rounded */
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            transition: all 0.3s;
            z-index: 40; /* Lower than message box (z-50) */
        }
        .floating-button:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.5);
        }
        #help-btn {
            background-color: #facc15; /* Secondary */
            color: #1f2937;
        }
        #dev-info-btn {
            background-color: #60a5fa; /* Accent Blue */
            color: white;
            bottom: 7rem; /* Position above help button */
        }
    </style>
</head>
<body class="bg-background-dark min-h-screen p-4 md:p-8 font-sans text-gray-100">

    <div class="max-w-4xl mx-auto">
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
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight drop-shadow-lg">
            Simulator Wirausaha
        </h1>
        <p id="business-subtitle" class="text-primary-100 mt-3 text-xl drop-shadow-md">
            Game Master AI
        </p>
    </div>
</header>


        
        <!-- === STARTUP MODAL / PHASE 1 === -->
        <div id="startup-modal" class="bg-surface-dark p-8 rounded-xl shadow-2xl">
            <h2 class="text-3xl font-bold text-center mb-4 text-secondary">Pilih Jenis Bisnis Anda</h2>
            <p class="text-gray-300 text-center mb-6">AI Game Master akan menentukan modal awal, tingkat kesulitan, dan skenario berdasarkan pilihan Anda.</p>
            
            <!-- BUSINESS SELECTION AREA: PRESET OR CUSTOM -->
            <label for="business-preset-select" class="block text-lg font-medium mb-2 text-primary">Pilihan Bisnis (Pilih Preset):</label>
            <select id="business-preset-select" class="w-full p-3 mb-4 bg-gray-600 text-white rounded-lg border border-gray-500 focus:outline-none focus:ring-2 focus:ring-secondary">
                <option value="" disabled selected>--- Pilih salah satu Preset ---</option>
                <option value="Warung Kopi Digital">Warung Kopi Digital (Fokus Reputasi & Inovasi)</option>
                <option value="Jasa Desain Grafis Freelance">Jasa Desain Grafis (Fokus Skill & Portofolio)</option>
                <option value="Toko Online Fashion">Toko Online Fashion (Fokus Tren Pasar & Logistik)</option>
            </select>

            <p class="text-sm text-gray-400 mb-2 text-center">-- ATAU --</p>

            <label for="business-custom-input" class="block text-lg font-medium mb-2 text-accent-blue">Masukkan Nama Bisnis Kustom:</label>
            <input type="text" id="business-custom-input" placeholder="Contoh: Aplikasi EdTech, Jasa Katering Sehat" class="w-full p-3 mb-4 bg-gray-600 text-white rounded-lg border border-gray-500 focus:outline-none focus:ring-2 focus:ring-accent-blue">
            <!-- END BUSINESS SELECTION AREA -->

            <label for="difficulty-select" class="block text-lg font-medium mb-2 text-primary">Tingkat Kesulitan:</label>
            <select id="difficulty-select" class="w-full p-3 mb-6 bg-gray-600 text-white rounded-lg border border-gray-500 focus:outline-none focus:ring-2 focus:ring-secondary">
                <option value="Siswa SMA/SMK">Siswa SMA/SMK (Mudah: Modal besar, Resiko rendah, Gaya Bahasa Mendorong)</option>
                <option value="Mahasiswa/Pemula">Mahasiswa/Pemula (Sedang: Modal sedang, Resiko normal, Gaya Bahasa Realistis)</option>
                <option value="Mahir/Pakar">Mahir/Pakar (Sulit: Modal kecil, Resiko tinggi, Gaya Bahasa Menantang)</option>
            </select>

            <button onclick="startNewGame()" class="w-full bg-primary text-gray-900 font-bold py-3 rounded-lg shadow-md hover:bg-emerald-400">
                Mulai Simulasi!
            </button>
            <div id="startup-loading" class="hidden text-center mt-3 text-secondary font-medium">
                AI Game Master sedang menyiapkan modal dan skenario awal...
            </div>
        </div>

        <!-- === MAIN GAME CONTAINER / PHASE 2 === -->
        <div id="game-container" class="hidden">
            <!-- Status Panel -->
            <div id="status-panel" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 p-4 bg-surface-dark rounded-xl shadow-lg">
                <div class="text-center p-3 bg-gray-600 rounded-lg">
                    <p class="text-sm text-gray-300">Uang (IDR)</p>
                    <p id="stat-money" class="text-xl md:text-2xl font-bold text-white">0</p>
                </div>
                <div class="text-center p-3 bg-gray-600 rounded-lg">
                    <p class="text-sm text-gray-300">Reputasi</p>
                    <p id="stat-reputation" class="text-xl md:text-2xl font-bold text-white">0</p>
                </div>
                <!-- NEW STAT: INOVASI -->
                <div class="text-center p-3 bg-gray-600 rounded-lg">
                    <p class="text-sm text-gray-300">Inovasi</p>
                    <p id="stat-innovation" class="text-xl md:text-2xl font-bold text-white">0</p>
                </div>
                <!-- END NEW STAT -->
                <div class="text-center p-3 bg-gray-600 rounded-lg">
                    <p class="text-sm text-gray-300">Skill Manajemen</p>
                    <p id="stat-skill" class="text-xl md:text-2xl font-bold text-white">0</p>
                </div>
            </div>

            <!-- Game Log / Narrative Display -->
            <div class="bg-surface-dark p-6 rounded-xl shadow-xl mb-8">
                <h2 class="text-2xl font-semibold mb-4 border-b border-gray-600 pb-2 text-primary">Log Permainan (Giliran: <span id="turn-count">0</span>)</h2>
                <div id="game-log" class="h-80 overflow-y-auto custom-scrollbar text-sm text-gray-200">
                    <div class="log-system">Menunggu pilihan bisnis...</div>
                </div>
            </div>

            <!-- Decision Input Area -->
            <div class="bg-surface-dark p-6 rounded-xl shadow-xl">
                <h2 class="text-2xl font-semibold mb-4 text-secondary" id="scenario-title">Skenario Saat Ini</h2>
                <div id="scenario-display" class="mb-6 text-gray-300 italic">...</div>

                <p class="text-lg font-medium text-gray-300 mb-3">Pilih salah satu Opsi AI atau gunakan Keputusan Kustom Anda:</p>
                
                <!-- Decision Options Generated by AI -->
                <div id="decision-options" class="grid grid-cols-1 gap-3 mb-6">
                    <!-- Options buttons will be inserted here -->
                </div>
                <!-- End Decision Options -->

                <!-- Custom Input -->
                <div class="border-t border-gray-600 pt-4">
                    <label for="custom-decision-text" class="block text-md font-medium mb-2 text-accent-blue">Keputusan Kustom (Manajemen Risiko Diterapkan):</label>
                    <textarea id="custom-decision-text" rows="2" class="w-full p-3 mb-3 bg-gray-700 text-white rounded-lg border border-gray-500 focus:outline-none focus:ring-2 focus:ring-accent-blue placeholder-gray-400" placeholder="Contoh: 'Saya akan mengalokasikan IDR 200.000 untuk mengadakan giveaway di Instagram.'"></textarea>

                    <button id="custom-decision-btn" onclick="makeCustomDecision()" 
    class="decision-button w-full bg-accent-blue text-white font-bold py-3 rounded-lg shadow-md hover:bg-blue-300 disabled:bg-gray-500 disabled:cursor-not-allowed flex justify-center items-center">
    Ambil Keputusan Kustom
</button>

                </div>
                
                <div id="loading-indicator" class="hidden text-center mt-3 text-secondary font-medium">
                    AI Game Master sedang memproses skenario... (Mungkin perlu waktu 10-20 detik)
                </div>
            </div>
        </div>
        
        <!-- Error/Info Modal (No alert() allowed) -->
        <div id="message-box" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center p-4 z-50">
            <div class="bg-surface-dark p-6 rounded-xl max-w-2xl w-full h-4/5 overflow-y-auto shadow-2xl border-t-4 border-primary">
                <h3 id="message-title" class="text-2xl font-bold mb-3 text-primary">Pesan Sistem</h3>
                <p id="message-content" class="text-gray-200 mb-4 whitespace-pre-wrap"></p>
                <div id="report-loading-indicator" class="text-center my-4 hidden">
                    <div class="text-lg text-secondary">🤖 AI Game Master sedang menyusun Laporan Akhir. Mohon tunggu...</div>
                </div>
                <button onclick="closeMessage(true)" class="w-full bg-secondary text-gray-900 font-bold py-2 rounded-lg hover:bg-amber-300">Mulai Ulang Simulasi</button>
            </div>
        </div>

        <!-- === NEW: Help/Developer Info Modal === -->
        <div id="info-modal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center p-4 z-50">
            <div class="bg-surface-dark p-6 rounded-xl max-w-lg w-full shadow-2xl border-t-4 border-accent-blue">
                <h3 id="info-title" class="text-2xl font-bold mb-4 text-accent-blue"></h3>
                <div id="info-content" class="text-gray-200 mb-4 h-96 overflow-y-auto custom-scrollbar"></div>
                <button onclick="closeInfoModal()" class="w-full bg-accent-blue text-white font-bold py-2 rounded-lg hover:bg-blue-300">Tutup</button>
            </div>
        </div>

        <!-- === NEW: Floating Buttons === -->
        <!-- Help/Panduan Button -->
        <button id="help-btn" class="floating-button" title="Panduan dan Petunjuk Game" onclick="openInfoModal('help')">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-help-circle"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </button>

        <!-- Developer Info Button -->
        <button id="dev-info-btn" class="floating-button" title="Informasi Developer" onclick="openInfoModal('developer')">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </button>
        <!-- === END Floating Buttons === -->


    </div>

    <script>
        // === KONFIGURASI API GEMINI ===
        const apiKey = "<?php echo $apiKey; ?>"; // Dibiarkan kosong, akan diisi oleh lingkungan Canvas
        const modelName = "<?php echo $model; ?>";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${modelName}:generateContent?key=${apiKey}`;

        // === STATE PERMAINAN ===
        let gameState = {
            money: 0, // Ditetapkan oleh AI
            reputation: 50, // Ditetapkan oleh AI
            skill: 1.0, // Ditetapkan oleh AI
            innovation: 50, // BARU: Indikator Inovasi (0-100)
            history: [],
            currentScenario: "",
            turn: 0,
            businessType: '',
            businessName: '',
            difficultyLevel: ''
        };

        // Skema JSON untuk setiap giliran (turn) game.
        const TURN_SCHEMA = {
            type: "OBJECT",
            properties: {
                "narrative": { "type": "STRING", "description": "Update naratif (disesuaikan dengan gaya bahasa level kesulitan) yang merinci hasil keputusan pemain dan keadaan pasar saat ini." },
                "moneyChange": { "type": "NUMBER", "description": "Perubahan uang pemain. Positif untuk untung, negatif untuk rugi. Harus berupa angka." },
                "reputationChange": { "type": "NUMBER", "description": "Perubahan reputasi pemain. Positif untuk peningkatan, negatif untuk penurunan. Harus berupa angka." },
                "skillIncrease": { "type": "NUMBER", "description": "Peningkatan kecil pada skill Manajemen (0 hingga 0.5). Harus berupa angka." },
                "innovationChange": { "type": "NUMBER", "description": "Perubahan nilai Inovasi (0 hingga 100). Harus berupa angka." }, // NEW STAT
                "riskAssessment": {
                    "type": "OBJECT",
                    "description": "Analisis singkat tentang risiko dan mitigasi terkait keputusan pemain.",
                    "properties": {
                        "riskIdentified": { "type": "STRING", "description": "Risiko utama yang timbul dari keputusan pemain atau situasi pasar (Jika tidak ada, tulis 'Tidak ada risiko signifikan')." },
                        "mitigationTip": { "type": "STRING", "description": "Saran mitigasi atau pelajaran yang bisa diambil pemain untuk giliran berikutnya." }
                    },
                    "required": ["riskIdentified", "mitigationTip"]
                },
                "plotTwist": {
                    "type": "OBJECT",
                    "description": "Plot twist atau peristiwa besar yang SANGAT TIDAK TERDUGA dan sangat berdampak (Hanya masukkan ini 1 dari 5 giliran. Di giliran lain, gunakan objek kosong {}).",
                    "properties": {
                        "title": { "type": "STRING", "description": "Judul plot twist (misal: 'Insiden Viral Mendadak!')." },
                        "description": { "type": "STRING", "description": "Penjelasan singkat tentang peristiwa tak terduga ini dan dampaknya pada pasar." },
                    }
                },
                "scenario": { 
                    "type": "OBJECT",
                    "properties": {
                        "challenge": { "type": "STRING", "description": "Skenario atau tantangan utama untuk giliran pemain berikutnya." },
                        "options": {
                            "type": "ARRAY",
                            "description": "Minimal 3, maksimal 4 pilihan tindakan yang harus dipilih pemain.",
                            "items": {
                                "type": "OBJECT",
                                "properties": {
                                    "text": { "type": "STRING", "description": "Deskripsi singkat dan jelas tentang opsi tindakan ini." }
                                },
                                "required": ["text"]
                            }
                        }
                    },
                    "required": ["challenge", "options"]
                }
            },
            required: ["narrative", "moneyChange", "reputationChange", "skillIncrease", "innovationChange", "riskAssessment", "scenario"]
        };

        // Skema JSON untuk inisialisasi game (modal, reputasi, skill, inovasi, dan skenario pertama).
        const STARTUP_SCHEMA = {
            type: "OBJECT",
            properties: {
                "initialMoney": { "type": "NUMBER", "description": "Modal awal yang ditentukan oleh AI untuk jenis bisnis ini, dalam IDR (misal: 1000000)." },
                "initialReputation": { "type": "NUMBER", "description": "Reputasi awal (misal: 55)." },
                "initialSkill": { "type": "NUMBER", "description": "Skill Manajemen awal (misal: 1.8)." },
                "initialInnovation": { "type": "NUMBER", "description": "Nilai Inovasi awal (misal: 45)." }, // NEW STAT
                "narrative": { "type": "STRING", "description": "Narasi pengantar singkat untuk memulai permainan dan menjelaskan kondisi awal." },
                "scenario": TURN_SCHEMA.properties.scenario // Re-use the scenario definition
            },
            required: ["initialMoney", "initialReputation", "initialSkill", "initialInnovation", "narrative", "scenario"]
        };

        // NEW SCHEMA: Laporan Akhir Game
        const FINAL_REPORT_SCHEMA = {
            type: "OBJECT",
            properties: {
                "title": { "type": "STRING", "description": "Judul Laporan (misal: 'Laporan Kemenangan Wirausaha')"},
                "summary": { "type": "STRING", "description": "Ringkasan naratif (3-4 kalimat) dari perjalanan bisnis pemain, menyoroti keputusan paling berdampak." },
                "analysis": { "type": "STRING", "description": "Analisis kritis (3-5 kalimat) tentang kekuatan (misal: inovasi cepat) dan kelemahan (misal: manajemen kas buruk) strategi pemain." },
                "recommendation": { "type": "STRING", "description": "Rekomendasi atau pelajaran utama (3-4 poin) untuk aplikasi dunia nyata atau permainan berikutnya. Gunakan format poin-poin/list jika memungkinkan, dipisahkan dengan baris baru." }
            },
            required: ["title", "summary", "analysis", "recommendation"]
        };

        let SYSTEM_INSTRUCTION = {}; // Akan diatur secara dinamis di startNewGame

        // === UTILITY FUNGSI ===

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        // Modifikasi showMessage untuk menangani Laporan Akhir
        async function showMessage(title, content, isFinalReport = false) {
            document.getElementById('message-title').textContent = title;
            document.getElementById('message-content').textContent = content;
            document.getElementById('message-box').classList.remove('hidden');
            document.getElementById('message-box').classList.add('flex');
            
            // Nonaktifkan input keputusan saat game over atau error
            disableDecisionInputs(true);
            
            if (isFinalReport) {
                document.getElementById('report-loading-indicator').classList.remove('hidden');
                document.getElementById('message-content').textContent = ''; // Clear content temporarily
                document.querySelector('#message-box button').disabled = true; // Disable restart button

                try {
                    const report = await generateFinalReport();
                    document.getElementById('report-loading-indicator').classList.add('hidden');
                    
                    // Format Laporan Akhir
                    let reportContent = `<h4 class="text-xl font-semibold text-secondary mb-2">Ringkasan Perjalanan</h4>
                        <p class="text-gray-300 mb-4">${report.summary}</p>
                        <h4 class="text-xl font-semibold text-secondary mb-2">Analisis Strategi</h4>
                        <p class="text-gray-300 mb-4">${report.analysis}</p>
                        <h4 class="text-xl font-semibold text-secondary mb-2">Rekomendasi & Pelajaran</h4>
                        <pre class="text-gray-300 whitespace-pre-wrap">${report.recommendation}</pre>
                        <p class="mt-4 font-bold text-lg text-primary">Status Akhir: Uang ${formatRupiah(gameState.money)}, Skill ${gameState.skill.toFixed(1)}, Reputasi ${Math.round(gameState.reputation)}</p>
                    `;

                    document.getElementById('message-title').textContent = report.title;
                    document.getElementById('message-content').innerHTML = reportContent;
                } catch (error) {
                    document.getElementById('report-loading-indicator').classList.add('hidden');
                    document.getElementById('message-content').textContent = "Gagal membuat laporan akhir. Detail: " + error.message;
                } finally {
                    document.querySelector('#message-box button').disabled = false; // Enable restart button
                }

            }
        }

        // Modifikasi closeMessage untuk me-refresh halaman (Mulai Ulang)
        function closeMessage(shouldRefresh = false) {
            document.getElementById('message-box').classList.add('hidden');
            document.getElementById('message-box').classList.remove('flex');
            if (shouldRefresh) {
                window.location.reload();
            }
        }

        // Fungsi baru untuk menghasilkan laporan akhir
        async function generateFinalReport() {
            const finalPrompt = `Permintaan Laporan Akhir: Game telah berakhir (Kemenangan/Bangkrut). Buat Laporan Akhir (summary, analysis, recommendation) untuk bisnis '${gameState.businessName}' pada tingkat kesulitan **${gameState.difficultyLevel}**. Berdasarkan riwayat permainan: ${JSON.stringify(gameState.history)}.`;
            
            // NOTE: Kami menggunakan skema yang berbeda untuk laporan akhir
            return await callGeminiAPI(finalPrompt, FINAL_REPORT_SCHEMA);
        }

        function updateUI() {
            document.getElementById('turn-count').textContent = gameState.turn;
            document.getElementById('stat-money').textContent = formatRupiah(gameState.money);
            document.getElementById('stat-reputation').textContent = `${Math.round(gameState.reputation)}/100`;
            document.getElementById('stat-innovation').textContent = `${Math.round(gameState.innovation)}/100`; // NEW STAT DISPLAY
            document.getElementById('stat-skill').textContent = gameState.skill.toFixed(1);
            document.getElementById('business-subtitle').textContent = `Game Master AI: ${gameState.businessName}`;

            // Scroll log ke bawah
            const logElement = document.getElementById('game-log');
            logElement.scrollTop = logElement.scrollHeight;

            if (gameState.money <= 0) {
                 showMessage("GAME OVER: Bangkrut", `Uang Anda telah habis! Bisnis '${gameState.businessName}' bangkrut setelah ${gameState.turn} giliran. Menganalisis permainan...`, true); // Trigger final report
            } else if (gameState.skill >= 10 || gameState.money >= 5000000000) { // Victory condition: $5M or max skill
                 showMessage("KEMENANGAN!", `Selamat! Anda berhasil mencapai total aset ${formatRupiah(gameState.money)} dan skill manajemen ${gameState.skill.toFixed(1)}! Menganalisis permainan...`, true); // Trigger final report
            }
        }

        function appendLog(type, content) {
            const logElement = document.getElementById('game-log');
            const entry = document.createElement('div');
            entry.classList.add('log-entry');
            
            let prefix = '';
            if (type === 'system') {
                entry.classList.add('log-system');
                prefix = 'GM: ';
            } else if (type === 'narrative') {
                entry.classList.add('log-narrative');
                prefix = 'Naratif: ';
            } else if (type === 'decision') {
                entry.classList.add('log-decision');
                prefix = 'Keputusan Anda: ';
                content = content.trim();
            } else if (type === 'risk') {
                entry.classList.add('log-risk');
                prefix = '⚠️ Risiko/Mitigasi: ';
            }

            entry.innerHTML = `<span class="font-bold">${prefix}</span>${content.replace(/\n/g, '<br>')}`;
            logElement.appendChild(entry);
        }
        
        function disableDecisionInputs(disabled) {
            // Nonaktifkan semua tombol keputusan
            const optionsContainer = document.getElementById('decision-options');
            Array.from(optionsContainer.children).forEach(btn => btn.disabled = disabled);
            
            // Nonaktifkan input kustom
            document.getElementById('custom-decision-text').disabled = disabled;
            document.getElementById('custom-decision-btn').disabled = disabled;

            const loading = document.getElementById('loading-indicator');
            if (disabled && gameState.money > 0 && gameState.skill < 10) {
                loading.classList.remove('hidden');
            } else {
                loading.classList.add('hidden');
            }
        }


        // === FUNGSI API GEMINI (dengan Exponential Backoff) ===

        async function exponentialBackoffFetch(url, options, maxRetries = 5) {
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (response.status !== 429 && response.ok) {
                        return response;
                    }
                    if (response.status === 429 && i < maxRetries - 1) {
                        const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                        console.warn(`Rate limit hit (429). Retrying in ${delay / 1000}s...`);
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    }
                    return response;
                } catch (error) {
                    if (i < maxRetries - 1) {
                        const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                        console.error(`Fetch error. Retrying in ${delay / 1000}s...`, error);
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    }
                    throw error;
                }
            }
            throw new Error("Gagal mengambil API setelah beberapa kali percobaan.");
        }

        async function callGeminiAPI(prompt, schema) {
            const payload = {
                contents: [{ parts: [{ text: prompt }] }],
                systemInstruction: SYSTEM_INSTRUCTION,
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: schema,
                    temperature: 0.8
                }
            };

            try {
                const response = await exponentialBackoffFetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorBody = await response.text();
                    console.error("API Error Response:", errorBody);
                    throw new Error(`Permintaan API gagal dengan status: ${response.status}`);
                }

                const result = await response.json();
                
                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                if (!jsonText) {
                    throw new Error("Respons JSON tidak valid atau kosong dari AI Game Master.");
                }

                return JSON.parse(jsonText);

            } catch (error) {
                console.error("Kesalahan dalam memanggil Gemini API:", error);
                // Menampilkan error hanya jika bukan laporan akhir
                if (schema !== FINAL_REPORT_SCHEMA) {
                    showMessage("Kesalahan API", "Tidak dapat menghubungi AI Game Master. Detail: " + error.message);
                }
                throw error; // Re-throw error for higher level handlers (like report generation)
            }
        }

        // === LOGIKA GAMEPLAY UTAMA ===

        function renderOptions(scenario) {
            const optionsContainer = document.getElementById('decision-options');
            optionsContainer.innerHTML = '';
            
            document.getElementById('scenario-display').textContent = scenario.challenge;

            scenario.options.forEach((option) => {
                const button = document.createElement('button');
                button.classList.add('decision-button', 'w-full', 'bg-primary', 'text-gray-900', 'font-bold', 'py-3', 'px-4', 'rounded-lg', 'shadow-md', 'hover:bg-emerald-400', 'text-left');
                button.textContent = option.text;
                button.onclick = () => makeDecision(option.text); 
                optionsContainer.appendChild(button);
            });

            // Aktifkan kembali semua input setelah loading selesai
            disableDecisionInputs(false);
        }

        function makeCustomDecision() {
            const customInput = document.getElementById('custom-decision-text');
            const decision = customInput.value.trim();
            if (!decision) {
                showMessage("Peringatan", "Keputusan kustom tidak boleh kosong.");
                return;
            }
            // Bersihkan input setelah digunakan
            customInput.value = '';
            makeDecision(decision);
        }


        async function makeDecision(playerDecision) {
            
            if (!playerDecision) {
                showMessage("Peringatan", "Keputusan tidak valid. Mohon pilih salah satu opsi.");
                return;
            }

            // Nonaktifkan semua input saat API sedang memproses
            disableDecisionInputs(true);

            appendLog('decision', playerDecision);

            // Perubahan: Mengirim riwayat sebagai string ke dalam prompt agar AI memiliki konteks.
            const historySummary = gameState.history.map(h => 
                `Giliran ${h.turn}: Keputusan - ${h.decision} | Hasil - Uang: ${h.outcome.moneyChange}, Reputasi: ${h.outcome.reputationChange}`
            ).join('; ');

            const fullPrompt = `Konteks Game State saat ini:\nBisnis: ${gameState.businessName}\nUang: ${formatRupiah(gameState.money)}\nReputasi: ${gameState.reputation.toFixed(1)}\nSkill: ${gameState.skill.toFixed(1)}\nInovasi: ${gameState.innovation.toFixed(1)}\nRiwayat Ringkas: ${historySummary}\n\nSkenario:\n${gameState.currentScenario}\n\nKeputusan Pemain:\n${playerDecision}\n\nNilai keputusan ini dan berikan skenario baru (challenge dan opsi). INGAT: Gunakan gaya bahasa sesuai level kesulitan dan sertakan plot twist (plotTwist) hanya 1 dari 5 giliran.`;

            try {
                const result = await callGeminiAPI(fullPrompt, TURN_SCHEMA);

                if (result) {
                    processGameTurn(result, playerDecision); 
                } else {
                     // Aktifkan kembali input jika API gagal dan game belum berakhir
                    if (gameState.money > 0 && gameState.skill < 10 && gameState.money < 5000000000) {
                        disableDecisionInputs(false);
                    }
                }

            } catch (error) {
                // Pastikan input diaktifkan kembali jika ada error di luar panggilan API
                if (gameState.money > 0 && gameState.skill < 10 && gameState.money < 5000000000) {
                     disableDecisionInputs(false);
                }
            }
        }

        function processGameTurn(result, playerDecision) {
            gameState.turn++;

            // Update Stats (dengan batasan)
            gameState.money += result.moneyChange;
            gameState.reputation = Math.min(100, Math.max(0, gameState.reputation + result.reputationChange));
            gameState.skill = Math.min(10.0, gameState.skill + result.skillIncrease);
            gameState.innovation = Math.min(100, Math.max(0, gameState.innovation + result.innovationChange)); // NEW STAT UPDATE

            // Update History
            gameState.history.push({
                turn: gameState.turn,
                decision: playerDecision, 
                outcome: result
            });

            // Log Narrative Update
            appendLog('narrative', result.narrative);

            // Log Plot Twist (if present)
            if (result.plotTwist && result.plotTwist.title && result.plotTwist.description) {
                 appendLog('system', `✨ **PLOT TWIST: ${result.plotTwist.title}**<br>${result.plotTwist.description}`);
            }

            // Log Risk Assessment & Mitigation
            if (result.riskAssessment) {
                 let riskLog = `Risiko Teridentifikasi: **${result.riskAssessment.riskIdentified}**<br>`;
                 riskLog += `Saran Mitigasi: ${result.riskAssessment.mitigationTip}`;
                 appendLog('risk', riskLog);
            }

            // Log Financial & Status Changes
            let statusLog = `--- Giliran ${gameState.turn} Ringkasan ---<br>`;
            statusLog += `💰 Perubahan Uang: <span class="font-bold ${result.moneyChange >= 0 ? 'text-primary' : 'text-red-400'}">${formatRupiah(result.moneyChange)}</span><br>`;
            statusLog += `⭐ Reputasi: <span class="font-bold ${result.reputationChange >= 0 ? 'text-primary' : 'text-red-400'}">${result.reputationChange.toFixed(1)}</span> (Total: ${Math.round(gameState.reputation)}/100)<br>`;
            statusLog += `💡 Inovasi: <span class="font-bold ${result.innovationChange >= 0 ? 'text-primary' : 'text-red-400'}">${result.innovationChange.toFixed(1)}</span> (Total: ${Math.round(gameState.innovation)}/100)<br>`; // NEW STAT LOG
            statusLog += `🛠️ Peningkatan Skill: <span class="font-bold text-secondary">${result.skillIncrease.toFixed(2)}</span> (Total: ${gameState.skill.toFixed(1)})`;
            appendLog('system', statusLog);

            // Set skenario baru dan render opsi
            gameState.currentScenario = result.scenario.challenge;
            renderOptions(result.scenario); 

            // Update UI (memanggil cek GAME OVER)
            updateUI();
        }

        // === INISIALISASI GAME ===

        function setupSystemInstruction() {
            let toneInstruction;
            if (gameState.difficultyLevel === "Siswa SMA/SMK") {
                toneInstruction = "Gaya Bahasa: **Optimis dan Mendorong**. Gunakan bahasa yang lugas, banyak memberikan dukungan, dan selalu fokus pada sisi positif (walaupun ada kerugian, narasikan sebagai 'pelajaran').";
            } else if (gameState.difficultyLevel === "Mahasiswa/Pemula") {
                toneInstruction = "Gaya Bahasa: **Realistis dan Edukatif**. Gunakan bahasa yang seimbang, tonjolkan sebab-akibat (aksi-reaksi), dan berikan evaluasi yang objektif terhadap kinerja.";
            } else { // Mahir/Pakar
                toneInstruction = "Gaya Bahasa: **Sinis, Menantang, dan Profesional (High-Stakes)**. Gunakan terminologi bisnis yang ketat, jangan berbelas kasihan pada kegagalan, dan tonjolkan bahwa pasar sangat brutal dan kompetitif.";
            }

            SYSTEM_INSTRUCTION = {
                parts: [{
                    text: `Anda adalah Game Master (GM) untuk simulator wirausaha virtual.
                    Tugas Anda adalah:
                    1. Tentukan modal awal (uang, reputasi, skill, inovasi) untuk bisnis yang dipilih pemain.
                    2. **LEVEL KESULITAN:** Tingkat kesulitan saat ini adalah **${gameState.difficultyLevel}**. Anda HARUS menyesuaikan seluruh permainan berdasarkan level ini:
                       - Siswa SMA/SMK (Mudah): Modal besar, risiko rendah, moneyChange yang stabil/positif, tantangan sederhana.
                       - Mahasiswa/Pemula (Sedang): Modal sedang, risiko normal, moneyChange bervariasi, tantangan seimbang.
                       - Mahir/Pakar (Sulit): Modal kecil, risiko sangat tinggi, moneyChange sangat fluktuatif (bisa sangat negatif), tantangan kompleks.

                    3. **GAYA BAHASA & PLOT TWIST:** ${toneInstruction}
                       - **PLOT TWIST:** Sertakan objek 'plotTwist' dengan 'title' dan 'description' hanya **1 dari setiap 5 giliran (turn)** (misal: di giliran 5, 10, 15, dst.). Ini harus berupa peristiwa tak terduga yang sangat mengubah dinamika permainan. Di giliran lain, biarkan objek 'plotTwist' kosong, yaitu **{}**.

                    4. **MANAJEMEN RISIKO (Wajib):** Di setiap giliran, analisis risiko (riskAssessment) yang terkait dengan keputusan pemain atau situasi pasar, dan berikan tips mitigasi yang relevan.

                    5. **INDIKATOR (Inovasi):** Nilai 'innovationChange' harus mencerminkan seberapa berani atau efektifnya pemain dalam mencoba ide baru atau beradaptasi. Tingkat 'Inovasi' yang tinggi dapat mengurangi risiko pasar tetapi membutuhkan investasi.

                    6. Buat skenario pasar yang realistis yang relevan dengan jenis bisnis: ${gameState.businessName}.
                    7. Nilai keputusan bisnis pemain.
                    8. Selalu berikan respons dalam format JSON yang ketat sesuai skema yang diberikan. JANGAN tambahkan teks lain di luar blok JSON.
                    9. Konteks bisnis dan riwayat permainan akan diberikan dalam prompt berikutnya.
                    `
                }]
            };
        }

        async function startNewGame() {
            const presetSelectElement = document.getElementById('business-preset-select');
            const customInputElement = document.getElementById('business-custom-input');
            const difficultyElement = document.getElementById('difficulty-select'); 
            
            const selectedPreset = presetSelectElement.value;
            const customName = customInputElement.value.trim();
            const selectedDifficulty = difficultyElement.value; 

            // Logic to determine the final business type: Custom input takes priority
            let selectedType = '';
            if (customName) {
                selectedType = customName;
            } else if (selectedPreset) {
                selectedType = selectedPreset;
            }
            
            if (!selectedType || !selectedDifficulty) { 
                showMessage("Peringatan", "Jenis bisnis dan tingkat kesulitan harus dipilih/diisi.");
                return;
            }

            const startButton = document.querySelector('#startup-modal button');
            const startupLoading = document.getElementById('startup-loading');
            
            startButton.disabled = true;
            presetSelectElement.disabled = true;
            customInputElement.disabled = true;
            difficultyElement.disabled = true; 
            startupLoading.classList.remove('hidden');

            // 1. Setup Business Name and Difficulty
            gameState.businessName = selectedType;
            gameState.difficultyLevel = selectedDifficulty; 
            
            // 2. Setup System Instruction
            setupSystemInstruction();

            // 3. Prepare Initial Prompt (meminta AI menentukan modal dan skenario pertama)
            const initialPrompt = `Permintaan Startup: Tentukan modal awal (uang, reputasi, skill, inovasi) untuk bisnis '${gameState.businessName}' dengan tingkat kesulitan **${gameState.difficultyLevel}**. Sediakan narasi pengantar dan tantangan/opsi pertama.`;

            try {
                // 4. API Call dengan Skema Startup
                const result = await callGeminiAPI(initialPrompt, STARTUP_SCHEMA);

                if (result && result.scenario && result.scenario.options) {
                    // 5. Setup Initial State dari respons AI
                    gameState.money = result.initialMoney;
                    gameState.reputation = result.initialReputation;
                    gameState.skill = result.initialSkill;
                    gameState.innovation = result.initialInnovation; // NEW STAT INIT
                    gameState.turn = 0;
                    gameState.history = [];

                    // 6. Transition UI
                    document.getElementById('startup-modal').classList.add('hidden');
                    document.getElementById('game-container').classList.remove('hidden');
                    document.getElementById('game-log').innerHTML = ''; 

                    // 7. Log Initial State and Scenario
                    appendLog('system', `Memulai simulasi sebagai: ${gameState.businessName} (Tingkat Kesulitan: ${gameState.difficultyLevel})`); 
                    appendLog('system', `💰 Modal Awal: ${formatRupiah(gameState.money)} | ⭐ Reputasi Awal: ${Math.round(gameState.reputation)}/100 | 💡 Inovasi Awal: ${Math.round(gameState.innovation)}/100 | 🛠️ Skill Awal: ${gameState.skill.toFixed(1)}`);
                    
                    // Log the first narrative
                    if (result.narrative) {
                        appendLog('narrative', result.narrative);
                    }

                    // Set and render the first scenario
                    gameState.currentScenario = result.scenario.challenge;
                    renderOptions(result.scenario); 
                    updateUI();

                } else {
                    throw new Error("Gagal memuat skenario awal atau modal dari AI. Respons tidak valid.");
                }

            } catch (error) {
                showMessage("Kesalahan Startup", `Gagal memulai game: ${error.message}.`);
                startButton.disabled = false;
                presetSelectElement.disabled = false;
                customInputElement.disabled = false;
                difficultyElement.disabled = false; 
            } finally {
                startupLoading.classList.add('hidden');
            }
        }
        
        // === FUNGSI MODAL INFO BARU ===
        
        const helpContent = `
            <h4 class="text-xl font-semibold text-primary mb-3">Tujuan Permainan</h4>
            <p class="text-gray-300 mb-4">Tujuan Anda adalah mengembangkan bisnis Anda hingga mencapai **Kekayaan (Uang) IDR 5.000.000.000** atau **Skill Manajemen 10.0** sebelum kehabisan uang. Ambil keputusan yang bijak di setiap giliran.</p>
            
            <h4 class="text-xl font-semibold text-primary mb-3 mt-4">Indikator Kunci</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-300">
                <li><strong class="text-white">Uang (IDR):</strong> Modal dan keuntungan Anda. Jika mencapai 0, game over.</li>
                <li><strong class="text-white">Reputasi:</strong> Kepercayaan pelanggan (0-100). Mempengaruhi penjualan dan loyalitas.</li>
                <li><strong class="text-white">Inovasi:</strong> Daya saing produk/layanan Anda (0-100). Inovasi tinggi diperlukan untuk sukses jangka panjang.</li>
                <li><strong class="text-white">Skill Manajemen:</strong> Keahlian Anda mengelola bisnis (max 10.0). Meningkat perlahan setiap giliran yang sukses.</li>
            </ul>

            <h4 class="text-xl font-semibold text-primary mb-3 mt-4">Cara Bermain</h4>
            <ul class="list-decimal list-inside space-y-1 text-gray-300">
                <li>Baca Skenario Saat Ini (Tantangan) yang diberikan oleh AI Game Master.</li>
                <li>Pilih salah satu dari Opsi Tindakan yang disediakan, atau masukkan Keputusan Kustom Anda sendiri.</li>
                <li>Setelah keputusan diambil, AI akan memproses hasilnya, memperbarui statistik Anda, memberikan narasi hasil, dan memberikan Analisis Risiko dan Tips Mitigasi (Pelajaran).</li>
                <li>Lanjutkan ke giliran berikutnya hingga mencapai target atau bangkrut.</li>
            </ul>
        `;

        const developerContent = `
            <h4 class="text-xl font-semibold text-accent-blue mb-3">Tentang Simulator Ini</h4>
            <p class="text-gray-300 mb-4">Simulator Wirausaha ini dikembangkan sebagai aplikasi demonstrasi menggunakan kecerdasan buatan (AI) untuk menciptakan pengalaman bisnis yang dinamis dan edukatif.</p>
            
            <h4 class="text-xl font-semibold text-accent-blue mb-3 mt-4">Teknologi Utama</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-300">
                <li><strong class="text-white">AI Game Master:</strong> Ditenagai oleh model AI untuk menghasilkan skenario, menilai keputusan, menyesuaikan gaya bahasa (termasuk untuk level Siswa SMA/SMK), dan membuat laporan akhir.</li>
                <li><strong class="text-white">Frontend:</strong> HTML, JavaScript murni, dan Tailwind CSS untuk tampilan yang responsif dan modern.</li>
                <li><strong class="text-white">Laporan Akhir:</strong> AI secara otomatis menghasilkan Ringkasan, Analisis, dan Rekomendasi berdasarkan seluruh riwayat keputusan Anda di akhir permainan.</li>
                <li><strong class="text-white">Dikembangkan:</strong> Subuh Kurniawan.</li>
            </ul>
        `;

        function openInfoModal(type) {
            const modal = document.getElementById('info-modal');
            const titleElement = document.getElementById('info-title');
            const contentElement = document.getElementById('info-content');
            
            if (type === 'help') {
                titleElement.textContent = "Panduan Game & Petunjuk";
                contentElement.innerHTML = helpContent;
            } else if (type === 'developer') {
                titleElement.textContent = "Informasi Developer & Teknologi";
                contentElement.innerHTML = developerContent;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeInfoModal() {
            document.getElementById('info-modal').classList.add('hidden');
            document.getElementById('info-modal').classList.remove('flex');
        }

        // Pastikan game dimulai dari modal startup
        window.onload = function() {
            // Biarkan game-container tersembunyi, startup-modal ditampilkan secara default
        };
    </script>
</body>
</html>
