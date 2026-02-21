<?php
// 1. Include and check connection
include "admin/fungsi/koneksi.php";

/** @var mysqli $koneksi */
if (!$koneksi) {
    die("Database connection error.");
}

// Set charset to ensure emojis and special characters work
mysqli_set_charset($koneksi, "utf8mb4");

$apiKey = null;
$apiId  = null;

// --- 1. Select API Key with Atomic Transaction ---
$koneksi->begin_transaction();

try {
    // Select the key with the lowest usage and lock the row (FOR UPDATE)
    $query = "SELECT id, api_key FROM api_keys ORDER BY usage_count ASC, id ASC LIMIT 1 FOR UPDATE";
    $result = $koneksi->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $apiKey = $row['api_key'];
        $apiId  = $row['id'];

        // Update usage_count inside the lock
        $update = $koneksi->prepare("UPDATE api_keys SET usage_count = usage_count + 1 WHERE id = ?");
        if ($update) {
            $update->bind_param("i", $apiId);
            $update->execute();
            $update->close();
        }
    }

    $koneksi->commit();
} catch (Exception $e) {
    // If something fails, rollback so the lock is released
    $koneksi->rollback();
    error_log("API Key Selection Error: " . $e->getMessage());
}

// --- 2. Fallback Mechanism ---
// Use a secure fallback if DB is empty or fails
if (!$apiKey) {
    $apiKey = "APIKEY"; // Note: Move to .env for security
}

$apiKeyJson = json_encode([$apiKey]);

// --- 3. Fetch Supported Models ---
$models = [];
$sql_model = "SELECT model_name FROM api_model 
              WHERE is_supported = 1 
              AND is_active = 1 
              AND guna_model = 2 
              ORDER BY id ASC";

$res_model = $koneksi->query($sql_model);

if ($res_model && $res_model->num_rows > 0) {
    while ($row = $res_model->fetch_assoc()) {
        $models[] = $row['model_name'];
    }
}

// Default to gemini-1.5-flash if no active models in DB
$model = !empty($models) ? $models[0] : "gemini-1.5-flash";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Bengkel Virtual - SMK Diagnostik (Gaya macOS)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            /* macOS-like soft blue gradient background */
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            min-height: 100vh; /* Ensure body covers full viewport */
        }

        /* 1. Liquid Glass / Frosted Glass Effect for Main Container */
        .glass-container {
            background-color: rgba(255, 255, 255, 0.75); /* Translucent white */
            backdrop-filter: blur(25px); /* Frosted effect */
            -webkit-backdrop-filter: blur(25px); /* For compatibility */
            border: 1px solid rgba(255, 255, 255, 0.2); /* Light edge */
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15); /* Soft, deep shadow */
        }

        /* 2. Soft Buttons (Adapting 'tool' class) */
        .tool {
            transition: transform 0.1s, box-shadow 0.3s, background-color 0.3s;
            background-image: linear-gradient(145deg, #007aff, #005bb5); /* macOS blue gradient */
            box-shadow: 0 4px 15px rgba(0, 122, 255, 0.3);
        }
        .tool:hover {
            background-image: linear-gradient(145deg, #0088ff, #0066cc);
            box-shadow: 0 6px 20px rgba(0, 122, 255, 0.4);
        }
        .tool:active {
            transform: translateY(1px);
            box-shadow: 0 2px 5px rgba(0, 122, 255, 0.2);
        }
        /* Style for disabled button */
        #startButton:disabled {
            background-image: none !important;
            background-color: #9ca3af !important; /* gray-400 */
            box-shadow: none !important;
            cursor: not-allowed;
        }

        /* 3. Input/Select styles */
        .glass-input {
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid #d1d5db; /* gray-300 */
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05); /* Inner shadow for depth */
        }
        .glass-input:focus {
            border-color: #3b82f6; /* blue-500 */
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3), inset 0 1px 3px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- Apply glass-container style -->
   <div id="appContainer" class="glass-container p-6 md:p-10 rounded-3xl w-full max-w-7xl mx-auto">

        <h1 class="text-3xl font-bold text-gray-800 mb-2">Simulasi Kejuruan Interaktif</h1>
        <p class="text-gray-500 mb-6">SMK Diagnostik</p>

        <form id="simulationForm">
            <!-- Department Selection -->
            <label for="department" class="font-semibold mb-2 block text-gray-700">Pilih Jurusan/Bidang:</label>
            <!-- Apply glass-input style and softer colors -->
            <select name="department" id="department" class="w-full p-3 rounded-lg mb-4 glass-input focus:ring-blue-500 focus:border-blue-500 appearance-none">
                <option value="" disabled selected>-- Pilih Jurusan --</option>
                <option value="TKR">Teknik Kendaraan Ringan (TKR)</option>
                <option value="TBSM">Teknik Bisnis Sepeda Motor (TBSM)</option>
                <option value="TKJ">Teknik Komputer dan Jaringan (TKJ)</option>
                <option value="ATPH">Agribisnis Tanaman Pangan dan Hortikultura (ATPH)</option>
                <option value="AKL">Akuntansi dan Keuangan Lembaga (AKL)</option>
                <option value="TAB">Teknik Alat Berat</option>
                <option value="Umum">Umum/Lainnya</option>
            </select>
            
            <label for="simulation" class="font-semibold mb-2 block text-gray-700">Pilih Simulasi Diagnostik:</label>
            <!-- Apply glass-input style and softer colors -->
            <select name="simulation" id="simulation" class="w-full p-3 rounded-lg mb-3 glass-input focus:ring-blue-500 focus:border-blue-500 appearance-none">
                <option value="" disabled selected>-- Pilih Jenis Kerusakan --</option>
                <!-- Opsi Simulasi akan dimuat di sini secara dinamis oleh JavaScript -->
            </select>
            
            <!-- Custom Input Section Added -->
            <div class="flex items-center my-3">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="flex-shrink mx-4 text-gray-500 text-sm font-medium">ATAU MASUKKAN KASUS KUSTOM</span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>

            <label for="customSimulationInput" class="font-semibold mb-2 block text-gray-700">Tuliskan Kasus Diagnostik:</label>
            <!-- Apply glass-input style -->
            <input type="text" id="customSimulationInput" placeholder="Contoh: Lampu rem tidak menyala atau Selisih Kas Kecil" class="w-full p-3 rounded-lg mb-4 glass-input focus:ring-blue-500 focus:border-blue-500">
            <!-- End Custom Input Section -->

            <!-- Apply tool style for primary action button, initial style set via custom CSS -->
            <button type="submit" id="startButton" class="w-full text-white font-bold py-3 px-4 rounded-xl transition-colors disabled:bg-gray-400 tool" style="background-image: linear-gradient(145deg, #007aff, #005bb5);">Mulai Simulasi</button>
        </form>

        <div id="gameArea" class="mt-6">
            <!-- Game levels will be rendered here -->
        </div>

    </div>
    <script>
        // ==================== Gemini API Configuration ====================
        // PENTING: Kunci API diatur sebagai string kosong ("") agar sistem runtime dapat menginjeksikan kunci yang valid.
        const apiKey =  <?php echo $apiKeyJson; ?>;
        const textApiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${<?php echo json_encode($model); ?>}:generateContent`;
        const ttsApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-tts:generateContent";


        // Structured JSON schema for reliable output
        const responseSchema = {
            type: "OBJECT",
            description: "Simulation package including levels and the dynamic toolset for the current scenario.",
            properties: {
                levels: {
                    type: "ARRAY",
                    description: "Array of ten simulation levels.",
                    items: {
                        type: "OBJECT",
                        properties: {
                            problem: { type: "STRING", description: "Deskripsi berupa narasi masalah mobil/kasus yang perlu diatasi DALAM BENTUK ALUR CERITA YANG MUDAH DIPAHAMI SISWA SMK" },
                            // Jawaban benar harus merupakan salah satu ID di daftar tools yang dibuat
                            correct: { type: "STRING", description: "ID dari jawaban yang benar, harus salah satu dari ID di daftar tools." },
                            feedback: { type: "STRING", description: "Umpan balik singkat (max 1 kalimat) untuk jawaban yang benar." }
                        },
                        required: ["problem", "correct", "feedback"]
                    }
                },
                tools: {
                    type: "ARRAY",
                    // Diperbarui: Daftar 12 alat/komponen/dokumen
                    description: "Daftar 12 komponen/alat/dokumen yang harus ditampilkan. Salah satunya harus menjadi jawaban yang benar untuk level. ID harus sederhana, cth: 'busi', 'kabel_lan', 'jurnal_umum'.",
                    items: {
                        type: "OBJECT",
                        properties: {
                            id: { type: "STRING", description: "ID unik (huruf kecil, underscore)." },
                            label: { type: "STRING", description: "Label tampilan untuk tombol, termasuk emoji jika relevan." }
                        },
                        required: ["id", "label"]
                    }
                }
            },
            required: ["levels", "tools"]
        };

        // System instruction diubah agar meminta 12 alat/komponen/dokumen
        const systemInstruction = `Anda adalah Ahli/Pakar Diagnostik SMK yang menguasai berbagai bidang kejuruan. Buatkan paket simulasi diagnostik yang berisi 10 level soal (levels) dan daftar 12 alat/komponen/dokumen yang harus diperiksa/digunakan (tools). Pastikan jawaban benar (correct ID) di setiap level ada di dalam daftar tools. HASILNYA HANYA OBJECT JSON sesuai skema. JANGAN TAMBAHKAN TEKS PEMBUKA, PENUTUP, ATAU PENJELASAN APAPUN.`;

        // ==================== TTS Utility Functions (No Change) ====================
let audioPlayer = null;
let isSpeaking = false;
let audioContext = null; // New global variable for AudioContext

// Utility to convert Base64 to ArrayBuffer (Unchanged)
const base64ToArrayBuffer = (base64) => {
    const binaryString = atob(base64);
    const len = binaryString.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
};

// ==================== TTS Main Function (Revised for Speed) ====================
async function speakProblem(textToSpeak) {
    const speakButton = document.getElementById('speakProblemButton');
    
    // 1. Logic to Stop Speaking (Updated for AudioContext)
    if (isSpeaking) {
        if (audioContext && audioPlayer) {
            // Stop playback by disconnecting the source
            audioPlayer.stop();
            audioPlayer.disconnect();
            audioPlayer = null;
        }
        
        isSpeaking = false;
        if (speakButton) {
            speakButton.innerHTML = '🎧 Dengar Kasus';
            speakButton.disabled = false;
            speakButton.classList.remove('animate-pulse', 'bg-red-500', 'hover:bg-red-600');
            speakButton.classList.add('bg-blue-500', 'hover:bg-blue-600');
        }
        return;
    }

    // 2. Logic to Start Speaking (Instant Feedback)
    isSpeaking = true;
    if (speakButton) {
        speakButton.innerHTML = '🔊 Memuat...';
        speakButton.disabled = true;
        speakButton.classList.add('animate-pulse', 'bg-red-500', 'hover:bg-red-600'); // Use red/pulse for loading
        speakButton.classList.remove('bg-blue-500', 'hover:bg-blue-600');
    }

    // TTS Payload (Unchanged)
    const payload = {
        contents: [{ parts: [{ text: textToSpeak }] }],
        generationConfig: {
            responseModalities: ["AUDIO"],
            speechConfig: { voiceConfig: { prebuiltVoiceConfig: { voiceName: "Kore" } } } // Voice: Kore (Firm)
        },
        model: "gemini-2.5-flash-preview-tts"
    };

    // Assumed global variables: ttsApiUrl, apiKey
    const urlWithKey = `${ttsApiUrl}?key=${apiKey}`; 

    try {
        // ... (API call logic with retries - Unchanged) ...
        const maxRetries = 3;
        let result = null;

        for (let i = 0; i < maxRetries; i++) {
            const response = await fetch(urlWithKey, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                result = await response.json();
                break;
            } else {
                if (i < maxRetries - 1) {
                    await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
                } else {
                    throw new Error(`TTS API gagal setelah ${maxRetries} percobaan.`);
                }
            }
        }

        if (!result) throw new Error("Gagal mendapatkan respons dari TTS API.");

        const part = result.candidates?.[0]?.content?.parts?.[0];
        const audioData = part?.inlineData?.data;
        const mimeType = part?.inlineData?.mimeType;

        if (audioData && mimeType && mimeType.startsWith("audio/L16")) {
            const rateMatch = mimeType.match(/rate=(\d+)/);
            const sampleRate = rateMatch ? parseInt(rateMatch[1], 10) : 24000;

            // --- REVISED AUDIO PROCESSING FOR SPEED ---
            
            // 3. Initialize AudioContext and Decode Audio
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }

            const pcmData = base64ToArrayBuffer(audioData);
            const pcm16 = new Int16Array(pcmData);

            // Convert Int16 data to Float32 data (required for Web Audio API)
            const float32Data = new Float32Array(pcm16.length);
            for (let i = 0; i < pcm16.length; i++) {
                // Normalize 16-bit PCM values to the range [-1, 1]
                float32Data[i] = pcm16[i] / 32767.0; 
            }

            // Create an AudioBuffer
            const audioBuffer = audioContext.createBuffer(
                1, // Number of channels
                float32Data.length, // Length
                sampleRate // Sample rate
            );
            
            // Copy the data to the buffer
            audioBuffer.getChannelData(0).set(float32Data);

            // Create a buffer source and play
            audioPlayer = audioContext.createBufferSource();
            audioPlayer.buffer = audioBuffer;
            audioPlayer.connect(audioContext.destination);
            audioPlayer.start(0);

            // 4. Update button while playing (allows stopping)
            if (speakButton) {
                speakButton.innerHTML = '⏹️ Berhenti Bicara';
                speakButton.disabled = false;
                speakButton.classList.remove('animate-pulse', 'bg-blue-500', 'hover:bg-blue-600');
                speakButton.classList.add('bg-red-500', 'hover:bg-red-600');
            }

            // 5. Reset button after audio finishes
            audioPlayer.onended = () => {
                isSpeaking = false;
                if (speakButton) {
                    speakButton.innerHTML = '🎧 Dengar Kasus';
                    speakButton.disabled = false;
                    speakButton.classList.remove('animate-pulse', 'bg-red-500', 'hover:bg-red-600');
                    speakButton.classList.add('bg-blue-500', 'hover:bg-blue-600');
                }
            };

        } else {
            throw new Error("Format audio dari API tidak didukung atau data kosong.");
        }

    } catch (error) {
        console.error("Error TTS:", error);
        // ... (Error handling logic - Unchanged) ...
        if (speakButton) {
            speakButton.innerHTML = '⚠️ Gagal';
            speakButton.disabled = false;
            speakButton.classList.remove('animate-pulse', 'bg-blue-500', 'hover:bg-blue-600');
            speakButton.classList.add('bg-red-500', 'hover:bg-red-600');
            setTimeout(() => {
                speakButton.innerHTML = '🎧 Dengar Kasus';
                speakButton.classList.remove('bg-red-500', 'hover:bg-red-600');
                speakButton.classList.add('bg-blue-500', 'hover:bg-blue-600');
                isSpeaking = false;
            }, 500);
        }
    }
}


        // ==================== Game State ====================
        let levels = [];
        let dynamicTools = []; // Variabel baru untuk menyimpan tools dinamis
        let currentLevel = 0;
        let score = 0;
        let isProcessing = false;

        // ==================== DOM Elements ====================
        const form = document.getElementById('simulationForm');
        const gameArea = document.getElementById('gameArea');
        const startButton = document.getElementById('startButton');
        const simulationSelect = document.getElementById('simulation');
        const departmentSelect = document.getElementById('department'); 
        const customInput = document.getElementById('customSimulationInput');

        // ==================== Simulation Options Map (Data Dinamis) ====================
        // Data ini memastikan pilihan "Jenis Masalah" yang sudah ditetapkan TIDAK HILANG.
        const simulationOptionsMap = {
            "TKR": [
                { value: "mobil_mati", label: "Mobil Mati Total (Engine Stop)" },
                { value: "mesin_misfire", label: "Mesin Misfire (Pincang)" },
                { value: "rem_tidak_pakem", label: "Rem Tidak Pakem (Braking Issue)" },
                { value: "alternator_rusak", label: "Alternator Tidak Mengisi (Charging Issue)" },
                { value: "ac_tidak_dingin", label: "AC Tidak Dingin (Cooling Issue)" },
                { value: "oli_bocor", label: "Kebocoran Oli Mesin" },
                { value: "ban_kempes_sering", label: "Ban Kempes Terus-menerus" },
                { value: "sensor_abnormal", label: "Sensor Engine Memberikan Data Abnormal" },
                { value: "starter_error", label: "Starter Mobil Tidak Berfungsi" },
                { value: "knalpot_bocor", label: "Knalpot Bocor / Suara Berisik" }
            ],
            "TBSM": [
                { value: "motor_tidak_mau_hidup", label: "Motor Tidak Mau Hidup" },
                { value: "tarikan_berat", label: "Tarikan Berat / Kurang Tenaga" },
                { value: "kopling_slip", label: "Kopling Selip" },
                { value: "rem_depan_blong", label: "Rem Depan Blong" },
                { value: "lampu_motor_error", label: "Lampu dan Kelistrikan Motor Tidak Normal" },
                { value: "busi_rusak", label: "Busi Rusak / Sulit Menghidupkan Mesin" },
                { value: "rantai_kendur", label: "Rantai Motor Kendur atau Berisik" },
                { value: "suspensi_motor_lemah", label: "Suspensi Motor Lemah / Tidak Stabil" }
            ],
            "TKJ": [
                { value: "server_down", label: "Diagnosa Server Down/Tidak Bisa Diakses" },
                { value: "koneksi_lambat", label: "Optimasi Jaringan (Koneksi Lambat)" },
                { value: "virus_malware", label: "Penanganan Infeksi Virus/Malware" },
                { value: "router_error", label: "Router/Access Point Tidak Merespon" },
                { value: "data_loss", label: "Pemulihan Data Hilang / Terhapus" },
                { value: "software_crash", label: "Software Crash / Tidak Bisa Dibuka" },
                { value: "konfigurasi_firewall", label: "Konfigurasi Firewall Tidak Sesuai" },
                { value: "website_down", label: "Website Internal Tidak Bisa Diakses" }
            ],
            "ATPH": [
                { value: "hama_tanaman", label: "Identifikasi & Penanganan Hama Tanaman Pangan" },
                { value: "gangguan_irigasi", label: "Diagnosa Kerusakan Sistem Irigasi Tetes" },
                { value: "defisiensi_nutrisi", label: "Identifikasi Gejala Defisiensi Nutrisi" },
                { value: "penyakit_tanaman", label: "Deteksi & Penanganan Penyakit Tanaman" },
                { value: "buah_tidak_matang", label: "Buah/Tanaman Tidak Matang Optimal" },
                { value: "kualitas_tanah_rendah", label: "Evaluasi & Perbaikan Kualitas Tanah" },
                { value: "pestisida_berlebihan", label: "Kelebihan Pemakaian Pestisida" },
                { value: "irigasi_bocor", label: "Kebocoran Saluran Irigasi" }
            ],
            "AKL": [
                { value: "jurnal_tidak_balance", label: "Penelusuran Kesalahan Jurnal Tidak Seimbang" },
                { value: "reconcile_bank", label: "Rekonsiliasi Bank: Selisih Saldo" },
                { value: "selisih_kas_kecil", label: "Diagnosa Selisih Dana Kas Kecil" },
                { value: "laporan_keuangan_salah", label: "Kesalahan dalam Laporan Keuangan" },
                { value: "piutang_tak_tertagih", label: "Analisa Piutang Tak Tertagih" },
                { value: "inventaris_salah", label: "Kesalahan Pencatatan Inventaris / Persediaan" },
                { value: "penggajian_error", label: "Kesalahan Proses Penggajian" },
                { value: "pajak_salah", label: "Kesalahan Perhitungan Pajak" }
            ],
            "TAB": [
                { value: "engine_overheat_heavy", label: "Engine Overheat pada Excavator/Bulldozer" },
                { value: "hydraulic_slow", label: "Sistem Hidrolik Lambat atau Lemah" },
                { value: "starting_problem_ab", label: "Masalah Starting Alat Berat" },
                { value: "rem_hidrolik_tidak_responsif", label: "Rem Hidrolik Tidak Responsif" },
                { value: "transmisi_abnormal", label: "Transmisi Alat Berat Bermasalah" },
                { value: "sensor_ab_bermasalah", label: "Sensor Alat Berat Tidak Akurat" },
                { value: "ban_rubber_rusak", label: "Ban/Track Rubber Rusak atau Licin" },
                { value: "getaran_berlebih", label: "Getaran Alat Berat Berlebih saat Operasi" }
            ],
            "Umum": [
                { value: "electrical_general", label: "Masalah Kelistrikan Umum (Lampu/Fuse)" },
                { value: "suspension_noise", label: "Suara Aneh pada Suspensi/Kaki-kaki" },
                { value: "kebocoran_air", label: "Kebocoran Air/AC Bocor" },
                { value: "sensor_error_umum", label: "Sensor Umum Memberikan Data Salah" },
                { value: "kerusakan_interior", label: "Kerusakan Interior/Panel Kontrol" },
                { value: "perawatan_rutin", label: "Simulasi Perawatan Rutin Berkala" },
                { value: "keamanan_operasional", label: "Masalah Keselamatan dan Keamanan Operasional" }
            ]
        };


        // Utility to get the user-friendly label from the value
        function getSimulationLabel(typeId) {
            // Check if the typeId matches one of the predefined options
            const currentDepartment = departmentSelect.value;
            const departmentOptions = simulationOptionsMap[currentDepartment] || [];
            
            const option = departmentOptions.find(opt => opt.value === typeId);
            
            // If option is found, use its label. Otherwise, use the typeId (which would be the custom input text).
            return option ? option.label : typeId;
        }
        
        // Utility to get the department label
        function getDepartmentLabel(deptId) {
            const option = Array.from(departmentSelect.options).find(opt => opt.value === deptId);
            // This ensures we get the full, user-friendly label like "Akuntansi dan Keuangan Lembaga (AKL)"
            return option ? option.textContent : 'Bidang Kejuruan';
        }


        // ==================== API Interaction (Client-side) ====================
        async function fetchLevels(department, simulationType) {
            if (isProcessing) return;

            isProcessing = true;
            startButton.disabled = true;
            startButton.textContent = 'Memuat Level (Tunggu Sebentar)...';
            // Use subtle macos loading indicator
            gameArea.innerHTML = '<div class="text-center text-blue-600 p-4"><div class="animate-spin inline-block w-6 h-6 border-[3px] border-current border-t-transparent text-blue-500 rounded-full" role="status"></div><p class="mt-2">Pakar Diagnostik sedang menyusun 10 soal...</p></div>';
            
            // Menggabungkan Jurusan dan Jenis Simulasi ke dalam user query untuk memastikan soal yang relevan
            const departmentLabel = getDepartmentLabel(department);
            // Diperbarui: Meminta 12 alat/komponen/dokumen
            const userQuery = `Buatkan 10 level soal simulasi dan daftar 12 alat/komponen/dokumen diagnostik yang relevan untuk konteks Jurusan: '${departmentLabel}' dan Kasus: '${simulationType}'. Pastikan jawaban benar untuk setiap level (ID correct) ada dalam daftar tools.`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemInstruction }] },
                generationConfig: {
                    temperature: 0.3,
                    responseMimeType: "application/json",
                    responseSchema: responseSchema
                }
            };

            const urlWithKey = `${textApiUrl}?key=${apiKey}`;

            try {
                const maxRetries = 3;
                let rawText = null;

                for (let i = 0; i < maxRetries; i++) {
                    const response = await fetch(urlWithKey, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.ok) {
                        const result = await response.json();
                        rawText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                        if (rawText) break;
                    } else {
                        // console.warn(`Percobaan ${i + 1} gagal dengan status: ${response.status}. Mencoba lagi...`);
                    }

                    if (i < maxRetries - 1) {
                        await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
                    }
                }

                if (!rawText) {
                    throw new Error("Gagal mendapatkan respons teks dari AI setelah beberapa kali percobaan.");
                }

                // ** PERBAIKAN: Menggunakan regex untuk menemukan blok JSON Object. **
                const jsonMatch = rawText.match(/\{[\s\S]*\}/);
                
                let parsedResponse;
                if (!jsonMatch) {
                    try {
                        parsedResponse = JSON.parse(rawText);
                    } catch (e) {
                        throw new Error(`Respons AI tidak mengandung format JSON Object yang diharapkan ({...}). Respons mentah: ${rawText.substring(0, 100)}...`);
                    }
                } else {
                    const jsonText = jsonMatch[0];
                    parsedResponse = JSON.parse(jsonText);
                }
                
                if (!parsedResponse || !Array.isArray(parsedResponse.levels) || !Array.isArray(parsedResponse.tools) || parsedResponse.levels.length === 0) {
                    throw new Error("Respons AI valid, tetapi struktur level atau tools kosong/tidak lengkap.");
                }

                levels = parsedResponse.levels;
                dynamicTools = parsedResponse.tools; // Menyimpan tools dinamis
                
                currentLevel = 0;
                score = 0;
                
                // Panggil intro screen setelah data dimuat
                showIntroScreen(simulationType); 

            } catch (error) {
                console.error("Gemini API Error:", error);
                // Use a softer, rounded error message
                gameArea.innerHTML = `<div class="p-4 bg-red-100 text-red-700 rounded-xl mt-4 border border-red-200 shadow-md">❌ Gagal memuat simulasi: ${error.message}. Coba lagi.</div>`;
            } finally {
                isProcessing = false;
                startButton.disabled = false;
                startButton.textContent = 'Mulai Simulasi';
            }
        }

        // ==================== New Intro Screen Logic ====================
        function showIntroScreen(simulationTypeId) {
            const simulationLabel = getSimulationLabel(simulationTypeId);
            const departmentLabel = getDepartmentLabel(departmentSelect.value); // Ambil label jurusan

            gameArea.innerHTML = `
                <!-- Use glass style for the info box -->
                <div class="mt-6 p-6 border-4 border-blue-400 bg-blue-50/70 backdrop-blur-sm rounded-xl shadow-lg">
                    <p class="text-3xl font-extrabold text-blue-800 mb-2">Selamat Datang, Ahli!</p>
                    <p class="text-md font-semibold text-blue-700 mb-1 border-b pb-1">Jurusan yang Dipilih: <span class="font-bold text-gray-800">${departmentLabel}</span></p>
                    <p class="text-xl font-semibold text-blue-700 mb-4">Kasus Diagnostik: ${simulationLabel}</p>
                    
                    <h3 class="font-bold text-gray-700 mb-2 mt-4 border-b pb-1">⚙️ Aturan Main Diagnostik</h3>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 text-sm md:text-base">
                        <li>Anda akan menerima **${levels.length} kasus** berurutan. (Maksimal 10)</li>
                        <li>Pilih **satu** alat/komponen/dokumen yang paling tepat untuk diperiksa/digunakan dari **12 Opsi** yang tersedia.</li>
                        <li>Setiap jawaban benar memberikan **10 poin**.</li>
                        <li>Fokus pada langkah diagnostik yang paling efisien!</li>
                    </ul>

                    <!-- Use the updated tool class for the button -->
                    <button id="startDiagnosticButton" class="w-full mt-6 text-white font-bold py-3 px-4 rounded-xl transition duration-150 ease-in-out tool">
                        Mulai Tes Diagnostik Sekarang
                    </button>
                </div>
            `;
            
            document.getElementById('startDiagnosticButton').addEventListener('click', () => {
                loadLevel(currentLevel); // Memuat Level 1 (pertanyaan dan opsi pertama)
            });
        }
        
        // ==================== Game Logic ====================

        function checkAnswer(selectedPart, lv, resultDiv, toolButtons) {
            if (isProcessing) return; // Prevent double clicking
            isProcessing = true;

            // Stop TTS if running
            if (audioPlayer && isSpeaking) {
                speakProblem(''); // Call with empty string to stop
            }

            // Disable all buttons after one click
            toolButtons.forEach(btn => btn.disabled = true);

            const isCorrect = selectedPart === lv.correct;

            // Highlight the correct part for context
            toolButtons.forEach(btn => {
                btn.classList.add(
                    'transition-all',
                    'duration-300',
                    'sm:text-sm',
                    'md:text-base',
                    'lg:text-lg',
                    'xl:text-xl',
                    'sm:px-2',
                    'md:px-3',
                    'lg:px-4',
                    'xl:px-5',
                    'sm:py-1',
                    'md:py-2',
                    'lg:py-3'
                );

                if (btn.getAttribute('data-part') === lv.correct) {
                    btn.classList.add('border-4', 'border-yellow-400', 'bg-yellow-200/50');
                }
            });

            if (isCorrect) {
                // macOS Green for success
                resultDiv.className = `
                    h-auto sm:h-[70px] md:h-[90px] lg:h-[110px] 
                    flex flex-col items-center justify-center text-sm sm:text-base md:text-lg lg:text-xl 
                    font-bold mb-4 p-2 sm:p-3 md:p-4 rounded-xl border-2 border-green-500 
                    bg-green-50/80 text-green-700 shadow-md transition-all duration-300
                `;
                resultDiv.innerHTML = `
                    <p class="text-sm sm:text-base md:text-lg lg:text-xl leading-relaxed mb-1">
                        ✅ Benar! 
                    </p>
                    <p class="text-xs sm:text-sm md:text-base lg:text-lg text-green-800 leading-relaxed">
                        <strong>${lv.feedback}</strong>
                    </p>
                `;
                score += 10;

                // Highlight correct button (selected one) with a distinct green
                toolButtons.forEach(btn => {
                    if (btn.getAttribute('data-part') === selectedPart) {
                        btn.style.backgroundImage = 'linear-gradient(145deg, #10b981, #059669)'; // Emerald/Green gradient
                        btn.classList.add('shadow-green-500/50');
                        btn.classList.remove('bg-teal-600', 'hover:bg-teal-700');
                    }
                });

                setTimeout(() => {
                    currentLevel++;
                    isProcessing = false;
                    loadLevel(currentLevel);
                }, 2500);
            } else {
                const correctLabel = dynamicTools.find(tool => tool.id === lv.correct)?.label || lv.correct;

                // macOS Red for error
                resultDiv.className = `
                    h-auto sm:h-[70px] md:h-[90px] lg:h-[110px] 
                    flex flex-col items-center justify-center text-sm sm:text-base md:text-lg lg:text-xl 
                    font-bold mb-4 p-2 sm:p-3 md:p-4 rounded-xl border-2 border-red-500 
                    bg-red-50/80 text-red-700 shadow-md transition-all duration-300
                `;
                resultDiv.innerHTML = `
                    <p class="text-xs sm:text-sm md:text-sm lg:text-base leading-snug mb-1">
                        ❌ Salah! Jawaban yang benar adalah 
                        <strong class="text-yellow-600">${correctLabel}</strong>.
                    </p>
                    <p class="text-[10px] sm:text-xs md:text-sm lg:text-sm text-red-700 leading-snug">
                        <strong>${lv.feedback}</strong>
                    </p>
                `;

                // Highlight incorrect button (selected one) with red
                toolButtons.forEach(btn => {
                    if (btn.getAttribute('data-part') === selectedPart) {
                        btn.style.backgroundImage = 'linear-gradient(145deg, #ef4444, #b91c1c)';
                        btn.classList.add('shadow-red-500/50');
                        btn.classList.remove('bg-teal-600', 'hover:bg-teal-700');
                    }
                });

                setTimeout(() => {
                    currentLevel++;
                    isProcessing = false;
                    loadLevel(currentLevel);
                }, 3500);
            }
        }

        function loadLevel(index) {
            // Stop any ongoing TTS when loading a new level
            if (audioPlayer && isSpeaking) {
                speakProblem(''); 
            }
            
            if (index >= levels.length) {
                gameArea.innerHTML = `
                    <div class="text-center p-8 bg-white/70 backdrop-blur-md rounded-2xl mt-6 glass-container">
                        <p class="text-5xl mb-4">🏅</p>
                        <p class="text-xl font-bold text-gray-800 mb-2">Simulasi Selesai!</p>
                        <!-- Skor dihitung berdasarkan jumlah level (maksimal 100 poin) -->
                        <p class="text-3xl font-extrabold text-blue-600">Skor Akhir: ${score} dari ${levels.length * 10}</p>
                        <p class="text-gray-600 mt-2 mb-4">Anda telah menyelesaikan tes diagnostik ini. Teruslah berlatih!</p>
                        <!-- Use the updated tool class for the button -->
                        <button onclick="document.location.reload()" class="mt-4 text-white font-bold py-2 px-4 rounded-xl transition duration-150 ease-in-out tool">Mulai Ulang Simulasi</button>
                    </div>
                `;
                return;
            }

            const lv = levels[index];
            // MENGGUNAKAN dynamicTools yang didapatkan dari AI
            const toolMap = dynamicTools; 

            gameArea.innerHTML = `
                <!-- Apply glass style for the level box -->
                <div class="mt-6 p-4 glass-container rounded-2xl">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                        <p class="text-xl font-extrabold text-gray-800">Level Diagnostik ${index + 1}/${levels.length}</p>
                        <p class="text-lg font-bold text-blue-600">Skor: ${score}</p>
                    </div>
                    <!-- Use a softer info box for the problem statement -->
                    <div class="p-4 bg-gray-50/50 border-l-4 border-blue-400 mb-6 rounded-lg text-gray-800 font-medium shadow-inner">
                        
                        <div class="flex justify-between items-start">
                            <div class="mr-4">
                                <p class="font-semibold text-lg mb-1 text-blue-700">🚨 Kasus Klien:</p>
                                <!-- Pertanyaan utama yang disesuaikan berdasarkan konteks Jurusan dan Kasus yang dipilih -->
                                <p id="problemText">${lv.problem}</p>
                            </div>
                            <!-- Tombol TTS baru -->
                            <button id="speakProblemButton" class="py-1 px-3 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600 transition duration-150 ease-in-out text-sm flex-shrink-0">
                                🎧 Dengar Kasus
                            </button>
                        </div>
                    </div>
                    
                    <p class="font-bold mb-3 text-gray-700">➡️ Alat/Dokumen yang Anda putuskan untuk periksa/gunakan (12 Opsi):</p>
                    <!-- UPDATED: Use grid-cols-3 and lg:grid-cols-4 for 12 items -->
                    <div class="grid grid-cols-3 lg:grid-cols-4 gap-3 mb-4" id="toolGrid">
                        ${toolMap.map(tool => `
                            <!-- Apply tool class for the options buttons -->
                            <button class="tool text-white font-semibold py-3 px-2 rounded-xl text-sm md:text-base shadow-md" data-part="${tool.id}">
                                ${tool.label}
                            </button>
                        `).join('')}
                    </div>
                    <!-- Result display area. Height is fixed for stability (avoid CLS) -->
                    <div id="result" class="h-[100px] flex items-center justify-center text-center font-bold p-3 mt-4 rounded-xl border border-gray-300 bg-white/50 text-gray-600 shadow-inner">
                        Silakan pilih jawaban.
                    </div>
                </div>
            `;

            const toolButtons = document.querySelectorAll('#toolGrid .tool');
            const resultDiv = document.getElementById('result');

            // Attach TTS listener
            document.getElementById('speakProblemButton').addEventListener('click', () => {
                speakProblem(lv.problem);
            });

            toolButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const selectedPart = btn.getAttribute('data-part');
                    // Ensure the button itself is passed to allow disabling
                    checkAnswer(selectedPart, lv, resultDiv, toolButtons);
                });
            });
        }
        
        // ==================== Event Listeners ====================
        
        // Function to dynamically update the simulation options
        function updateSimulationOptions(departmentValue) {
            const options = simulationOptionsMap[departmentValue] || [];
            
            // Clear current options, but keep the initial disabled placeholder
            simulationSelect.innerHTML = '<option value="" disabled selected>-- Pilih Jenis Kerusakan --</option>';

            options.forEach(optionData => {
                const option = document.createElement('option');
                option.value = optionData.value;
                option.textContent = optionData.label;
                simulationSelect.appendChild(option);
            });
            
            // Ensure the button state is updated after loading new options
            updateButtonState(); 
        }

        const updateButtonState = () => {
            const departmentValue = departmentSelect.value; 
            const dropdownValue = simulationSelect.value;
            const customInputValue = customInput.value.trim();
            
            // Tombol diaktifkan jika (Jurusan terpilih) DAN (salah satu dari dropdown simulasi (yang sudah terisi) atau input kustom memiliki nilai)
            const isDisabled = !(departmentValue && (dropdownValue || customInputValue));
            startButton.disabled = isDisabled;
            
            // Update button visual state when disabled
            if (isDisabled) {
                startButton.style.backgroundImage = 'none';
                startButton.classList.remove('shadow-lg');
            } else {
                // Re-apply the initial gradient style
                startButton.style.backgroundImage = 'linear-gradient(145deg, #007aff, #005bb5)';
                startButton.classList.add('shadow-lg');
            }
        };

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const departmentValue = departmentSelect.value; 
            const dropdownValue = simulationSelect.value;
            const customInputValue = customInput.value.trim();
            
            // Prioritaskan input kustom jika ada teks, jika tidak gunakan nilai dropdown
            const simulationType = customInputValue || dropdownValue;

            if (departmentValue && simulationType) {
                // Clear previous game area content when starting new game
                gameArea.innerHTML = ''; 
                fetchLevels(departmentValue, simulationType); // Meneruskan Jurusan dan Tipe Simulasi
            } else {
                let errorMessage = '⚠️ Harap pilih Jurusan dan jenis simulasi/kustom.';
                if (!departmentValue) {
                    errorMessage = '⚠️ Harap pilih Jurusan terlebih dahulu.';
                } else if (!simulationType) {
                    errorMessage = '⚠️ Harap pilih jenis simulasi atau masukkan jenis kustom.';
                }
                // Use the new soft error style
                gameArea.innerHTML = `<div class="p-4 bg-red-100 text-red-700 rounded-xl mt-4 border border-red-200 shadow-md">${errorMessage}</div>`;
            }
        });

        // Event listeners untuk mengontrol state tombol mulai dan mengupdate opsi simulasi
        departmentSelect.addEventListener('change', () => {
            // Fungsi ini memastikan opsi simulasi yang terkait dengan jurusan dimuat kembali.
            updateSimulationOptions(departmentSelect.value);
            // reset custom input and simulation dropdown to force re-selection
            simulationSelect.value = "";
            customInput.value = "";
            updateButtonState();
        }); 

        simulationSelect.addEventListener('change', updateButtonState);
        customInput.addEventListener('input', updateButtonState);

        // Initial state set
        updateButtonState();
    </script>
</body>
</html>
