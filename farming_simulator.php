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
    $apiKey = "AIzaSyAYYBCPplYs1pd3vqu5e13YsbF1hgQz8EY"; // Note: Move to .env for security
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
    <title>AI Smart Farm Simulator</title>
    <!-- Muat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fdee; /* Warna latar belakang tema pertanian */
        }
        /* Responsiveness: Plot akan selalu 1:1 */
        .farm-plot {
            width: 100%;
            padding-bottom: 100%; 
            position: relative;
            cursor: pointer;
            transition: transform 0.1s ease-in-out, box-shadow 0.1s;
            border-radius: 8px;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.3);
        }
        .farm-plot:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .plot-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            line-height: 1;
        }
        .watered {
            box-shadow: inset 0 0 10px rgba(0, 191, 255, 0.7); /* Efek basah */
        }
        .diseased {
            border: 4px solid #f87171; /* Merah untuk penyakit */
        }
        .btn-game {
            transition: all 0.2s;
            box-shadow: 0 4px #10b981;
        }
        .btn-game:active {
            box-shadow: 0 1px #10b981;
            transform: translateY(3px);
        }
        /* Custom scrollbar for Advisor Box */
        .advisor-box::-webkit-scrollbar {
            width: 6px;
        }
        .advisor-box::-webkit-scrollbar-thumb {
            background-color: #4ade80;
            border-radius: 3px;
        }

        /* Adjusting for mobile: column layout becomes 1/2 of screen */
        @media (max-width: 1023px) {
            .lg\:col-span-2 {
                grid-column: span 1 / span 1;
            }
            .lg\:col-span-1 {
                grid-column: span 1 / span 1;
            }
            #game-container main {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="min-h-screen p-4 md:p-8">

    <!-- Kontainer Utama -->
    <div id="game-container" class="max-w-6xl mx-auto bg-white rounded-xl shadow-2xl p-4 md:p-8">

        <!-- Header dan Dashboard -->
        <header class="mb-6 pb-4 border-b-4 border-lime-500">
            <h1 class="text-3xl md:text-5xl font-extrabold text-lime-700 text-center">AI Smart Farm Simulator</h1>
            <p class="text-center text-gray-500 mt-1">Kelola lahanmu dengan bimbingan AI Game Master!</p>
            <div id="status-dashboard" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="p-3 bg-green-100 rounded-lg shadow-md">
                    <p class="text-sm text-gray-600">💰 Uang (IDR)</p>
                    <p id="stat-money" class="text-xl font-bold text-green-700">Rp 0</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg shadow-md">
                    <p class="text-sm text-gray-600">📅 Hari</p>
                    <p id="stat-day" class="text-xl font-bold text-blue-700">Hari 1</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-lg shadow-md">
                    <p class="text-sm text-gray-600">☀️ Cuaca</p>
                    <p id="stat-weather" class="text-xl font-bold text-yellow-700">Cerah</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg shadow-md">
                    <p class="text-sm text-gray-600">👤 ID Pengguna</p>
                    <p id="stat-user-id" class="text-xs font-mono text-red-700 break-all">Memuat...</p>
                </div>
            </div>
        </header>

        <!-- Konten Utama: Farm Grid dan Advisor/Actions -->
        <main class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kolom Kiri: Farm Grid (2/3 lebar di desktop) -->
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold text-lime-600 mb-4">Lahan Pertanian (Klik untuk Interaksi)</h2>
                <!-- Grid 4x4, fixed for game logic consistency -->
                <div id="farm-grid" class="grid grid-cols-4 gap-2 p-3 bg-gray-100 rounded-xl shadow-inner border border-gray-300">
                    <!-- Plot Lahan akan dimasukkan di sini oleh JS -->
                </div>
            </div>

            <!-- Kolom Kanan: AI Advisor dan Aksi (1/3 lebar di desktop) -->
            <div class="lg:col-span-1 flex flex-col space-y-6">

                <!-- Inventaris -->
                <div class="bg-amber-50 p-4 rounded-xl shadow-lg border border-amber-300">
                    <h2 class="text-xl font-bold text-amber-700 mb-2 flex justify-between items-center">
                        📦 Inventaris & Upgrade
                    </h2>
                    <div id="inventory-display" class="text-sm space-y-1">
                        <!-- Konten Inventaris dan Upgrade dimuat di sini oleh JS -->
                        <p class="text-gray-500">Memuat...</p>
                    </div>
                </div>

                <!-- AI Advisor Box -->
                <div class="bg-indigo-700 text-white p-4 rounded-xl shadow-lg flex-grow h-64 lg:h-auto overflow-hidden">
                    <h2 class="text-xl font-bold flex items-center mb-2">
                        🤖 AI Game Master
                        <span id="advisor-status" class="ml-2 text-sm px-2 py-0.5 rounded-full bg-indigo-500">Siaga</span>
                    </h2>
                    <div id="advisor-messages" class="advisor-box h-40 lg:h-64 overflow-y-auto text-sm space-y-2 pr-2">
                        <p class="text-gray-300">Selamat datang, Petani! Saya adalah Game Master (GM) yang akan memandumu. Dunia pertanianmu dimulai sekarang!</p>
                    </div>
                </div>

                <!-- Game Actions -->
                <div class="bg-white p-4 rounded-xl shadow-lg border border-lime-300">
                    <h2 class="text-xl font-bold text-lime-600 mb-3">Aksi Game</h2>
                    <button id="btn-next-day" class="btn-game w-full bg-lime-500 hover:bg-lime-600 text-white font-bold py-3 px-4 rounded-xl mb-3">
                        Lanjut Hari <span class="text-xs">(+1 Hari)</span>
                    </button>
                    <button id="btn-show-market" class="btn-game w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-xl mb-3">
                        Lihat Pasar & Jual Hasil
                    </button>
                    <button id="btn-show-upgrades" class="btn-game w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-xl">
                        Pusat Upgrade ⚙️
                    </button>
                </div>
            </div>
        </main>

    </div>

    <!-- Modal Peringatan/Aksi -->
    <div id="modal-backdrop" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center p-4 z-50">
        <div id="modal-content" class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-100">
            <!-- Konten Modal (Plant/Market/Upgrade/Message) akan dimuat di sini -->
        </div>
    </div>

    <!-- GAME SCRIPT (PURE JS) -->
    <script>
        // --- KONFIGURASI API GEMINI ---
        // API Key diatur kosong agar Canvas dapat menyediakannya saat runtime.
        const apiKey =  <?php echo $apiKeyJson; ?>; 
         const md =  <?php echo json_encode($model); ?>;
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${keys[0]}`;
        
        // PROMPT SISTEM BARU: Lebih dramatis dan personal
        const ADVISOR_SYSTEM_PROMPT = "Anda adalah AI Game Master (GM) dengan kepribadian yang ramah, sedikit dramatis, dan sangat memotivasi. Tugas Anda adalah memberikan saran atau komentar naratif yang *spontan* dan *personal* (maksimal 3 kalimat) berdasarkan laporan status game. Jangan pernah gunakan Markdown atau kode, hanya teks biasa. Selalu sertakan emoji yang kuat (1-2 emoji) untuk penekanan.";

        // --- KONFIGURASI PERSISTENSI LOKAL ---
        const LOCAL_STORAGE_KEY = 'ai_smart_farm_state_v4_rp_gm'; // Diperbarui untuk Rupiah dan GM
        let userId = 'LocalPlayer';

        // --- KONFIGURASI MATA UANG RUPIAH ---
        const CURRENCY_CONVERSION = 15000; // $1 = Rp 15,000
        const convertToRupiah = (usd) => Math.round(usd * CURRENCY_CONVERSION);
        const formatRupiah = (amount) => `Rp ${amount.toLocaleString('id-ID')}`;

        // --- KONFIGURASI GAME & STATE AWAL ---
        const GRID_SIZE = 4; // 4x4 Farm
        const CROPS_DATA = {
            Corn: {
                name: 'Jagung',
                icon: ['🌱', '🌿', '🌽'], 
                daysToGrow: 5,
                basePrice: convertToRupiah(50), 
                color: 'text-yellow-500',
                seasonPref: 'Summer' 
            },
            Wheat: {
                name: 'Gandum',
                icon: ['🌱', '🌾', '🥖'], 
                daysToGrow: 7,
                basePrice: convertToRupiah(40), 
                color: 'text-orange-500',
                seasonPref: 'Autumn' 
            },
            Carrot: {
                name: 'Wortel',
                icon: ['🌱', '🥕', '🥕'], 
                daysToGrow: 4,
                basePrice: convertToRupiah(60), 
                color: 'text-red-500',
                seasonPref: 'Spring' 
            }
        };

        const UPGRADES_DATA = {
            AutoWater: {
                name: "Penyiram Otomatis",
                desc: `Mengairi semua petak yang ditanami secara otomatis setiap hari. Hemat biaya ${formatRupiah(convertToRupiah(5))}/petak!`,
                cost: convertToRupiah(500), 
                effect: (state) => { /* Logika efek di nextDay */ }
            },
            SuperFertilizer: {
                name: "Pupuk Super",
                desc: "Kesehatan tanah menurun 50% lebih lambat di semua petak. Memberi bonus pertumbuhan 1.1x.",
                cost: convertToRupiah(800), 
                effect: (state) => { /* Logika efek di growCrops */ }
            },
             PestControl: {
                name: "Pengendali Hama AI",
                desc: "Mengurangi kemungkinan penyakit menyerang tanaman hingga 75%. Investasi pencegahan yang cerdas.",
                cost: convertToRupiah(1200), 
                effect: (state) => { /* Logika efek di growCrops */ }
            }
        };

        const INITIAL_STATE = {
            money: convertToRupiah(500), // Rp 7,500,000
            day: 1,
            season: 'Spring', 
            weather: 'Sunny', 
            farm: Array(GRID_SIZE * GRID_SIZE).fill(null), 
            marketPrices: {
                Corn: convertToRupiah(50),
                Wheat: convertToRupiah(40),
                Carrot: convertToRupiah(60)
            },
            inventory: {}, 
            advisorHistory: [],
            upgrades: {
                AutoWater: false,
                SuperFertilizer: false,
                PestControl: false
            }
        };

        let gameState = { ...INITIAL_STATE };

        // --- UTILITAS UI & NOTIFIKASI ---

        function showMessage(title, message, actionsHtml = '') {
            const modalBackdrop = document.getElementById('modal-backdrop');
            const modalContent = document.getElementById('modal-content');

            modalContent.innerHTML = `
                <h3 class="text-2xl font-bold text-lime-600 mb-2">${title}</h3>
                <div class="text-gray-700 mb-4 overflow-y-auto max-h-80">${message}</div>
                <div class="flex justify-end space-x-2 pt-2 border-t">
                    <button id="modal-close" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">${actionsHtml ? 'Batal' : 'Tutup'}</button>
                    ${actionsHtml}
                </div>
            `;
            
            document.getElementById('modal-close').onclick = hideModal;
            modalBackdrop.classList.remove('hidden');
            modalBackdrop.classList.add('flex');
        }

        function hideModal() {
            document.getElementById('modal-backdrop').classList.add('hidden');
            document.getElementById('modal-backdrop').classList.remove('flex');
        }

        // --- PERSISTENSI LOKAL (localStorage) ---

        function saveGame() {
            try {
                localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(gameState));
            } catch (error) {
                console.error("Error saving game state to local storage:", error);
            }
        }

        function loadGame() {
            try {
                const storedState = localStorage.getItem(LOCAL_STORAGE_KEY);
                if (storedState) {
                    const loadedState = JSON.parse(storedState);
                    
                    if (Array.isArray(loadedState.farm) && loadedState.farm.length === GRID_SIZE * GRID_SIZE) {
                         // Merge loaded state with INITIAL_STATE to ensure new fields (like 'upgrades') are present
                         gameState = {
                            ...INITIAL_STATE, 
                            ...loadedState,
                            upgrades: { ...INITIAL_STATE.upgrades, ...loadedState.upgrades },
                        };
                         // Ensure plot properties exist
                         gameState.farm = gameState.farm.map(plot => {
                            if (plot && plot.type) {
                                return {
                                    ...plot,
                                    soilHealth: plot.soilHealth !== undefined ? plot.soilHealth : 100,
                                    isDiseased: plot.isDiseased !== undefined ? plot.isDiseased : false
                                };
                            }
                            return plot;
                         });
                    } else {
                         gameState = { ...INITIAL_STATE };
                    }
                } else {
                    gameState = { ...INITIAL_STATE };
                }
            } catch (error) {
                console.error("Error loading game state from local storage:", error);
                gameState = { ...INITIAL_STATE };
            }
            document.getElementById('stat-user-id').textContent = userId + ' (Lokal)';
        }
        
        // --- INICIALISASI GAME MURNI JS ---
        function initGame() {
            loadGame();
            setupEventListeners();
            renderGame();
        }

        // --- LOGIKA GAME CORE ---

        function updateDashboard() {
            // Menggunakan formatRupiah
            document.getElementById('stat-money').textContent = formatRupiah(gameState.money);
            document.getElementById('stat-day').textContent = `Hari ${gameState.day} (${gameState.season})`;
            
            const weatherEmoji = {
                'Sunny': '☀️ Cerah',
                'Cloudy': '☁️ Berawan',
                'Rainy': '🌧️ Hujan',
                'Drought': '🔥 Kekeringan'
            };
            const weatherColor = {
                'Sunny': 'text-yellow-700',
                'Cloudy': 'text-gray-500',
                'Rainy': 'text-blue-700',
                'Drought': 'text-red-700'
            };

            const weatherEl = document.getElementById('stat-weather');
            weatherEl.innerHTML = weatherEmoji[gameState.weather];
            weatherEl.className = `text-xl font-bold ${weatherColor[gameState.weather]}`;
        }
        
        function renderInventory() {
            const invEl = document.getElementById('inventory-display');
            let invHtml = '';
            let totalItems = 0;
            
            // Tampilkan Inventaris
            for (const type in gameState.inventory) {
                const quantity = gameState.inventory[type];
                if (quantity > 0) {
                    const crop = CROPS_DATA[type];
                    invHtml += `<p class="flex justify-between"><span>${crop.icon[crop.icon.length - 1]} ${crop.name}:</span> <span class="font-bold">${quantity} unit</span></p>`;
                    totalItems += quantity;
                }
            }
            if (totalItems === 0) {
                invHtml += `<p class="text-gray-500">Inventaris kosong. Tanam sesuatu!</p>`;
            }

            // Tampilkan Upgrade
            invHtml += `<div class="mt-3 pt-3 border-t border-amber-200">`;
            invHtml += `<h4 class="font-semibold text-sm text-indigo-600 mb-1">Status Upgrade:</h4>`;
            let activeUpgrades = 0;
            for (const key in gameState.upgrades) {
                const upgrade = UPGRADES_DATA[key];
                const isActive = gameState.upgrades[key];
                if (isActive) {
                    invHtml += `<p class="text-xs text-green-600 flex items-center">✅ ${upgrade.name}</p>`;
                    activeUpgrades++;
                }
            }
             if (activeUpgrades === 0) {
                invHtml += `<p class="text-xs text-gray-500">Belum ada upgrade aktif. Mulai berinvestasi!</p>`;
            }
            invHtml += `</div>`;


            invEl.innerHTML = invHtml;
        }

        function renderFarmGrid() {
            const gridEl = document.getElementById('farm-grid');
            gridEl.innerHTML = '';
            
            gameState.farm.forEach((plot, index) => {
                const plotEl = document.createElement('div');
                plotEl.className = 'farm-plot relative group';
                plotEl.dataset.index = index;
                plotEl.onclick = () => handlePlotClick(index);

                let content = '';
                let plotClass = 'bg-stone-600'; 
                let plotModifiers = [];

                if (plot && plot.type) { 
                    const crop = CROPS_DATA[plot.type];
                    const daysGrown = typeof plot.daysGrown === 'number' ? plot.daysGrown : 0;
                    const growthPercentage = Math.min(100, (daysGrown / crop.daysToGrow) * 100);
                    const isHarvestReady = daysGrown >= crop.daysToGrow;
                    
                    // NEW: Determine Growth Stage
                    const stageIndex = Math.floor(daysGrown / crop.daysToGrow * (crop.icon.length - 1));
                    const currentIcon = crop.icon[Math.min(stageIndex, crop.icon.length - 1)];

                    let statusIcon = '';
                    if (isHarvestReady) {
                        statusIcon = `<span class="text-4xl animate-pulse text-green-400">🎉</span>`;
                        plotClass = 'bg-lime-700';
                    } else {
                        statusIcon = `<span class="text-4xl ${crop.color}">${currentIcon}</span>`;
                    }
                    
                    if (plot.watered) {
                        plotClass = 'bg-yellow-700 watered';
                    } else if (isHarvestReady) {
                         plotClass = 'bg-lime-700';
                    } else {
                        plotClass = 'bg-stone-800';
                    }

                    if (plot.isDiseased) {
                        plotModifiers.push('<span class="absolute top-1 left-1 text-red-500 text-xl font-bold animate-ping-slow" title="Terserang Penyakit">🦠</span>');
                        plotEl.classList.add('diseased');
                    }
                    if (plot.soilHealth < 30) {
                        plotModifiers.push('<span class="absolute bottom-1 right-1 text-orange-500 text-lg font-bold" title="Kesehatan Tanah Rendah">💀</span>');
                    }


                    content = `
                        <div class="plot-content text-white">
                            ${statusIcon}
                            <div class="text-xs mt-1 text-center font-semibold leading-tight">
                                ${crop.name} 
                                <span class="block">${Math.floor(growthPercentage)}%</span>
                            </div>
                        </div>
                    `;
                    
                } else if (plot === null) {
                    content = '<div class="plot-content text-white text-xs">Tanah Kosong</div>';
                    plotClass = 'bg-stone-600';
                } else if (plot && plot.status === 'watered') {
                    plotClass = 'bg-blue-600 watered';
                    content = '<div class="plot-content text-white text-xs">Terairi</div>';
                    if (plot.soilHealth < 30) {
                        plotModifiers.push('<span class="absolute bottom-1 right-1 text-orange-500 text-lg font-bold" title="Kesehatan Tanah Rendah">💀</span>');
                    }
                }

                plotEl.className = `farm-plot relative ${plotClass} ${plotModifiers.length > 0 ? 'p-2' : ''}`;
                plotEl.innerHTML = content + plotModifiers.join('');
                gridEl.appendChild(plotEl);
            });
        }
        
        function renderAdvisor() {
            const advisorEl = document.getElementById('advisor-messages');
            advisorEl.innerHTML = gameState.advisorHistory.map(msg => 
                `<p class="p-2 bg-indigo-600 rounded-lg shadow-sm">${msg}</p>`
            ).join('');
            advisorEl.scrollTop = advisorEl.scrollHeight;

            const statusEl = document.getElementById('advisor-status');
            const hasProblem = gameState.weather.includes('Drought') || gameState.farm.some(p => p && (p.isDiseased || p.soilHealth < 30));

            // Tambahkan logika untuk status loading AI
            if (statusEl.textContent !== 'Memproses...') {
                statusEl.textContent = hasProblem ? 'Krisis!' : 'Optimal';
                statusEl.className = `ml-2 text-sm px-2 py-0.5 rounded-full ${hasProblem ? 'bg-red-500' : 'bg-green-500'}`;
            }
        }
        
        function renderGame() {
            updateDashboard();
            renderInventory(); 
            renderFarmGrid();
            renderAdvisor();
            saveGame(); 
        }

        // --- AI GAME MASTER LOGIC (Integrasi Gemini) ---

        /**
         * Panggilan ke Gemini API dengan Exponential Backoff.
         * @param {string} userQuery - Prompt yang akan dikirim.
         * @param {number} attempts - Jumlah percobaan.
         */
        async function callGeminiAdvisor(userQuery, attempts = 0) {
            const MAX_RETRIES = 3;
            if (attempts >= MAX_RETRIES) {
                // Pesan error yang lebih baik dan lebih natural
                return "🚨 **GEMINI ERROR:** Maaf, energi saya habis! Koneksi ke pusat data GM gagal total. Anda harus bertani sendiri untuk sementara waktu. Fokus!";
            }

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: {
                    parts: [{ text: ADVISOR_SYSTEM_PROMPT }]
                },
            };

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    // Coba lagi dengan backoff jika respons HTTP gagal
                    const delay = Math.pow(2, attempts) * 1000;
                    console.error(`Gemini call failed (Attempt ${attempts + 1}). Retrying in ${delay}ms...`);
                    await new Promise(resolve => setTimeout(resolve, delay));
                    return callGeminiAdvisor(userQuery, attempts + 1);
                }

                const result = await response.json();
                const text = result.candidates?.[0]?.content?.parts?.[0]?.text || "GM diam... Mungkin dia sedang tidur.";
                return text.trim();

            } catch (error) {
                // Coba lagi dengan backoff jika terjadi kesalahan jaringan
                const delay = Math.pow(2, attempts) * 1000;
                console.error(`Gemini fetch error (Attempt ${attempts + 1}):`, error);
                await new Promise(resolve => setTimeout(resolve, delay));
                return callGeminiAdvisor(userQuery, attempts + 1);
            }
        }

        /**
         * Merangkum status game menjadi prompt yang detail untuk Gemini.
         * LOGIKA BARU: Lebih detail untuk AI yang lebih natural.
         */
        function generateGeminiPrompt() {
            const state = gameState;
            
            // Hitung status lahan
            let plotsTotal = GRID_SIZE * GRID_SIZE;
            let plotsPlanted = state.farm.filter(p => p && p.type).length;
            let plotsEmpty = plotsTotal - plotsPlanted;
            let plotsHarvestReady = state.farm.filter(p => p && p.type && p.daysGrown >= CROPS_DATA[p.type].daysToGrow).length;
            let plotsDiseased = state.farm.filter(p => p && p.isDiseased).length;
            let lowHealthPlots = state.farm.filter(p => p && p.type && p.soilHealth < 40).length;
            let unwateredPlots = state.farm.filter(p => p && p.type && !p.watered && !state.upgrades.AutoWater).length;
            const hasAutoWater = state.upgrades.AutoWater ? 'Aktif' : 'Tidak Aktif';
            
            let prompt = `LAPORAN STATUS PERTANIAN:\n`;
            prompt += `Hari: ${state.day}, Musim: ${state.season}, Cuaca: ${state.weather}.\n`;
            prompt += `Modal: ${formatRupiah(state.money)}.\n`;
            prompt += `Upgrade Otomatis Air: ${hasAutoWater}.\n`;
            prompt += `Kondisi Lahan: ${plotsPlanted} ditanam, ${plotsEmpty} kosong. Total ${plotsTotal} petak.\n`;
            
            let focusMessage = "";

            if (plotsHarvestReady > 0) {
                focusMessage = `Fokus Utamamu: ADA ${plotsHarvestReady} HASIL PANEN BERHARGA SIAP DIJUAL. Dorong pemain untuk segera panen dan menghasilkan uang.`;
            } else if (plotsDiseased > 0) {
                 focusMessage = `Fokus Utamamu: DARURAT! ADA WABAH PENYAKIT PADA ${plotsDiseased} PETAK. Beri peringatan kritis dan sarankan pengobatan (${formatRupiah(convertToRupiah(20))}).`;
            } else if (lowHealthPlots > 0) {
                 focusMessage = `Fokus Utamamu: KUALITAS TANAH MENURUN di ${lowHealthPlots} petak. Sarankan pemupukan (${formatRupiah(convertToRupiah(30))}) atau tanam tanaman yang sesuai musim.`;
            } else if (plotsEmpty > 0) {
                 focusMessage = `Fokus Utamamu: LAHAN KOSONG. Dorong pemain untuk menanam, terutama ${Object.keys(CROPS_DATA).find(c => CROPS_DATA[c].seasonPref === state.season) || 'tanaman apa pun'} yang cocok dengan musim ${state.season}.`;
            } else if (unwateredPlots > 0) {
                 focusMessage = `Fokus Utamamu: KRISIS AIR! ${unwateredPlots} PETAK KEKERINGAN (upgrade air tidak aktif). Tekankan pentingnya pengairan manual (${formatRupiah(convertToRupiah(5))}).`;
            } else {
                 focusMessage = `Fokus Utamamu: Semua terkendali. Beri komentar dramatis tentang cuaca ${state.weather} atau dorong dia untuk membeli upgrade.`;
            }
            
            prompt += `\n${focusMessage}`;
            prompt += "\nBerikan narasi sebagai Game Master yang dramatis, personal, dan singkat (maks. 3 kalimat) berdasarkan situasi di atas.";
            
            return prompt;
        }


        async function giveAdvisorAdvice() {
            const statusEl = document.getElementById('advisor-status');
            statusEl.textContent = 'Memproses...';
            statusEl.className = 'ml-2 text-sm px-2 py-0.5 rounded-full bg-yellow-500 animate-pulse';

            const userPrompt = generateGeminiPrompt();
            const advice = await callGeminiAdvisor(userPrompt);

            // Setelah mendapatkan saran, push ke history
            pushAdvisorMessage(advice);

            // Render ulang untuk memperbarui status dan pesan
            renderAdvisor(); 
        }

        // --- END AI GAME MASTER LOGIC ---


        function updateWeather() {
            const currentSeason = gameState.season;
            let newWeather;
            const rand = Math.random();
            let weatherProbabilities = { Sunny: 0.4, Cloudy: 0.25, Rainy: 0.2, Drought: 0.15 }; 

            if (currentSeason === 'Spring') { 
                weatherProbabilities = { Sunny: 0.35, Cloudy: 0.3, Rainy: 0.3, Drought: 0.05 };
            } else if (currentSeason === 'Summer') { 
                weatherProbabilities = { Sunny: 0.45, Cloudy: 0.2, Rainy: 0.1, Drought: 0.25 };
            } else if (currentSeason === 'Autumn') { 
                weatherProbabilities = { Sunny: 0.2, Cloudy: 0.35, Rainy: 0.35, Drought: 0.1 };
            } else if (currentSeason === 'Winter') { 
                weatherProbabilities = { Sunny: 0.3, Cloudy: 0.5, Rainy: 0.15, Drought: 0.05 };
            }
            
            let cumulativeProb = 0;
            const keys = Object.keys(weatherProbabilities);
            for (let i = 0; i < keys.length; i++) {
                cumulativeProb += weatherProbabilities[keys[i]];
                if (rand <= cumulativeProb) {
                    newWeather = keys[i];
                    break;
                }
            }
            if (!newWeather) newWeather = 'Sunny';

            if (newWeather === 'Rainy') {
                gameState.farm = gameState.farm.map(plot => {
                    if (plot && plot.type) {
                        plot.watered = true;
                        plot.soilHealth = Math.min(100, plot.soilHealth + 10); 
                    } else if (plot === null) {
                        return { status: 'watered', soilHealth: 100 }; 
                    } else if (plot && plot.status === 'watered') {
                         plot.soilHealth = 100;
                    }
                    return plot;
                });
                pushAdvisorMessage("🌧️ Hujan berkah turun deras di lahanmu! Semua petak terairi secara alami dan tanah mendapat nutrisi ekstra. Anggap ini sebagai 'jackpot' cuaca!", true);
            }

            gameState.weather = newWeather;
        }

        function growCrops() {
            const isSuperFertilizerActive = gameState.upgrades.SuperFertilizer;
            const isPestControlActive = gameState.upgrades.PestControl;
            
            const weatherFactor = (gameState.weather === 'Sunny' ? 1.2 : 
                                 gameState.weather === 'Rainy' ? 1.0 :
                                 gameState.weather === 'Cloudy' ? 0.8 :
                                 gameState.weather === 'Drought' ? 0.3 : 1.0);

            let deathCount = 0;
            let rotCount = 0;
            let diseaseCount = 0;

            gameState.farm = gameState.farm.map(plot => {
                if (plot && plot.type) { 
                    
                    // 1. Disease Check
                    const diseaseChance = isPestControlActive ? 0.0125 : 0.05; // 75% reduction
                    if (!plot.isDiseased && (gameState.weather === 'Rainy' || gameState.season === 'Summer') && Math.random() < diseaseChance) {
                        plot.isDiseased = true;
                        diseaseCount++;
                    }

                    // 2. Watering and Soil Health Penalty
                    let growthModifier = 1.0;
                    if (plot.watered) {
                        plot.watered = false;
                        plot.soilHealth = Math.min(100, plot.soilHealth + 5); 
                    } else {
                        // Tidak diairi
                        growthModifier *= 0.1; 
                        // Soil health penalty (Slower penalty if SuperFertilizer is active)
                        const penalty = isSuperFertilizerActive ? 5 : 10; 
                        plot.soilHealth = Math.max(0, plot.soilHealth - penalty);
                    }
                    
                    // 3. Soil Health Factor
                    growthModifier *= (plot.soilHealth / 100);
                    
                    // 4. Disease Penalty
                    if (plot.isDiseased) {
                        growthModifier *= 0.3; 
                    }
                    
                    // 5. Seasonal Preference Bonus & Super Fertilizer Bonus
                    if (CROPS_DATA[plot.type].seasonPref === gameState.season) {
                        growthModifier *= 1.1;
                    }
                    if (isSuperFertilizerActive) {
                         growthModifier *= 1.1; // Bonus pertumbuhan dari pupuk super
                    }
                    
                    // Apply growth
                    plot.daysGrown += weatherFactor * growthModifier;
                    
                    // 6. Check for soil health collapse (crop death)
                    if (plot.soilHealth <= 0) {
                        deathCount++;
                        return null; 
                    }
                    
                    // 7. Check for rot (overgrowth + rain)
                    if (plot.daysGrown > CROPS_DATA[plot.type].daysToGrow + 3 && gameState.weather === 'Rainy') {
                         rotCount++;
                         return null; 
                    }
                } else if (plot && plot.status === 'watered') {
                    // Lahan kosong terairi: soil health menurun secara perlahan
                     const penalty = isSuperFertilizerActive ? 2 : 5;
                    plot.soilHealth = Math.max(0, plot.soilHealth - penalty);
                    if (plot.soilHealth === 0) return null; 
                }
                
                return plot;
            });

            if (deathCount > 0) {
                 pushAdvisorMessage(`💀 **MALAPETAKA!** Sebanyak ${deathCount} tanaman mati di lahan karena kelalaian atau kesehatan tanah yang buruk. Ini kerugian besar, Petani!`, true);
            }
            if (rotCount > 0) {
                 pushAdvisorMessage(`🤢 **BUSUK!** ${rotCount} tanaman gagal panen karena membusuk. Jelas butuh manajemen panen yang lebih gesit.`, true);
            }
            if (diseaseCount > 0) {
                 pushAdvisorMessage(`🦠 **WASPADA!** Penyakit telah menyebar ke ${diseaseCount} petak! Segera tangani sebelum terlambat!`, true);
            }
        }
        
        function updateMarketPrices() {
            // Logic unchanged, keeps market dynamic
            for (const type in CROPS_DATA) {
                const basePrice = CROPS_DATA[type].basePrice;
                const dailyChange = (Math.random() - 0.5) * 0.4; 
                let newPrice = gameState.marketPrices[type] * (1 + dailyChange);

                if (gameState.weather === 'Drought') {
                    newPrice *= 1.15;
                }
                 if (CROPS_DATA[type].seasonPref === gameState.season) {
                    newPrice *= 0.9;
                }

                newPrice = Math.max(basePrice * 0.7, Math.min(basePrice * 1.8, newPrice));
                gameState.marketPrices[type] = Math.round(newPrice);
            }
        }
        
        // GM function with timestamp
        function pushAdvisorMessage(message, isCritical = false) {
            const timestamp = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            gameState.advisorHistory.push(`[${timestamp}] ${isCritical ? '🚨 ' : ''}${message}`);
            if (gameState.advisorHistory.length > 15) {
                gameState.advisorHistory.shift(); 
            }
        }

        /**
         * Fungsi untuk melanjutkan ke hari berikutnya
         * Dibuat ASYNC karena memanggil AI Game Master
         */
        async function nextDay() {
            // Disable tombol agar tidak bisa diklik berulang kali saat AI memproses
            const nextDayBtn = document.getElementById('btn-next-day');
            nextDayBtn.disabled = true;

            // 0. Auto-Water check 
            if (gameState.upgrades.AutoWater && gameState.weather !== 'Rainy') {
                const plotsToWater = gameState.farm.filter(p => p && p.type && !p.watered).length;
                if (plotsToWater > 0) {
                    gameState.farm = gameState.farm.map(plot => {
                        if (plot && plot.type && !plot.watered) {
                            plot.watered = true;
                        }
                        return plot;
                    });
                    pushAdvisorMessage(`✅ **Sistem Otomatis Aktif!** Penyiram Otomatis bekerja di ${plotsToWater} petak. Anda benar-benar seorang Petani Modern!`, true);
                }
            }
            
            gameState.day += 1;
            
            // Laporan GM harian (sebelum saran AI)
            pushAdvisorMessage(`🌄 **LAPORAN HARIAN HARI ${gameState.day - 1}!** Cuaca hari ini: ${gameState.weather}. Semua tanaman menua satu hari.`, true);


            if (gameState.day > 15) { 
                gameState.day = 1;
                const seasons = ['Spring', 'Summer', 'Autumn', 'Winter'];
                const currentIndex = seasons.indexOf(gameState.season);
                gameState.season = seasons[(currentIndex + 1) % seasons.length];
                pushAdvisorMessage(`🎉 **PERGANTIAN MUSIM!** Kita memasuki musim ${gameState.season}. Tantangan dan peluang baru menantumu, Petani!`, true);
            }
            
            updateWeather();
            growCrops();
            updateMarketPrices();
            
            // Panggil AI Game Master
            await giveAdvisorAdvice(); 
            
            renderGame();
            nextDayBtn.disabled = false; // Aktifkan tombol lagi
        }

        // --- LOGIKA INTERAKSI PENGGUNA ---

        function handlePlotClick(index) {
            const plot = gameState.farm[index];

            if (plot && plot.type && plot.daysGrown >= CROPS_DATA[plot.type].daysToGrow) {
                handleHarvest(index);
            } else if (plot && plot.type) {
                showCropActionsModal(index, plot);
            } else if (plot === null || (plot && plot.status === 'watered')) {
                showPlantModal(index);
            } else {
                 let info = plot ? `${CROPS_DATA[plot.type].name} ditanam. Pertumbuhan: ${Math.floor((plot.daysGrown / CROPS_DATA[plot.type].daysToGrow) * 100)}%.` : 'Petak kosong.';
                 showMessage('Status Petak', info);
            }
        }
        
        function showCropActionsModal(index, plot) {
            const crop = CROPS_DATA[plot.type];
            const waterCost = convertToRupiah(5);
            const fertilizeCost = convertToRupiah(30);
            const treatCost = convertToRupiah(20);
            
            let actionsHtml = '';
            
            if (!plot.watered && !gameState.upgrades.AutoWater) {
                actionsHtml += `<button id="action-water" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold" ${gameState.money < waterCost ? 'disabled' : ''}>💧 Air (${formatRupiah(waterCost)})</button>`;
            } else if (plot.watered || gameState.upgrades.AutoWater) {
                 actionsHtml += `<button class="px-3 py-1 bg-gray-400 text-gray-700 rounded-lg" disabled>Diairi/Auto</button>`;
            }

            if (plot.soilHealth < 100) {
                 actionsHtml += `<button id="action-fertilize" class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold" ${gameState.money < fertilizeCost ? 'disabled' : ''}>🌱 Pupuk (${formatRupiah(fertilizeCost)})</button>`;
            }

             if (plot.isDiseased) {
                 actionsHtml += `<button id="action-treat" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold" ${gameState.money < treatCost ? 'disabled' : ''}>💊 Obati (${formatRupiah(treatCost)})</button>`;
            } 

            const info = `
                <p>Tanaman: <span class="font-bold">${crop.name}</span></p>
                <p>Pertumbuhan: <span class="font-bold">${Math.floor((plot.daysGrown / crop.daysToGrow) * 100)}%</span></p>
                <p>Kesehatan Tanah: <span class="font-bold text-green-600">${plot.soilHealth}%</span></p>
                <p>Status Penyakit: <span class="font-bold text-red-600">${plot.isDiseased ? 'Ya 🦠' : 'Tidak'}</span></p>
                <p class="text-xs mt-2 text-gray-500">Note: Tombol di-disable jika uang tidak cukup.</p>
            `;

            showMessage(
                `Aksi Petak ${index + 1}`,
                info,
                actionsHtml
            );

            document.getElementById('modal-content').querySelector('#action-water')?.addEventListener('click', () => {
                handleWatering(index, waterCost);
                hideModal();
            });
            document.getElementById('modal-content').querySelector('#action-fertilize')?.addEventListener('click', () => {
                handleFertilize(index, fertilizeCost);
                hideModal();
            });
            document.getElementById('modal-content').querySelector('#action-treat')?.addEventListener('click', () => {
                handleTreatDisease(index, treatCost);
                hideModal();
            });
        }
        
        function handleFertilize(index, cost) {
             if (gameState.money < cost) {
                 pushAdvisorMessage('💰 Uangmu tidak cukup untuk memupuk! Jual hasil panen!', true);
                 showMessage('Gagal Memupuk', `Biaya memupuk adalah ${formatRupiah(cost)}, tapi uangmu hanya ${formatRupiah(gameState.money)}.`);
                 return;
             }
             
             gameState.money -= cost;
             if (gameState.farm[index] && gameState.farm[index].type) {
                gameState.farm[index].soilHealth = 100;
             } else if (gameState.farm[index] && gameState.farm[index].status === 'watered') {
                gameState.farm[index].soilHealth = 100;
             }
             
             pushAdvisorMessage(`🌱 Petak ke-${index + 1} berhasil dipupuk. Kesehatan tanah kembali optimal! (-${formatRupiah(cost)}).`);
             renderGame();
        }

        function handleTreatDisease(index, cost) {
             if (gameState.money < cost) {
                 pushAdvisorMessage('💰 Uangmu tidak cukup untuk membeli pestisida! Jual hasil panen!', true);
                 showMessage('Gagal Mengobati', `Biaya mengobati adalah ${formatRupiah(cost)}, tapi uangmu hanya ${formatRupiah(gameState.money)}.`);
                 return;
             }
             
             gameState.money -= cost;
             if (gameState.farm[index] && gameState.farm[index].type) {
                gameState.farm[index].isDiseased = false;
             }
             
             pushAdvisorMessage(`💊 Penyakit di petak ke-${index + 1} berhasil ditaklukkan. Tanaman akan tumbuh normal kembali! (-${formatRupiah(cost)}).`);
             renderGame();
        }


        function handleWatering(index, cost) {
             if (gameState.money < cost) {
                 pushAdvisorMessage('💰 Uangmu tidak cukup untuk mengairi! Lahanmu akan kering!', true);
                 showMessage('Gagal Mengairi', `Biaya mengairi adalah ${formatRupiah(cost)}, tapi uangmu hanya ${formatRupiah(gameState.money)}.`);
                 return;
             }
             
             gameState.money -= cost;
             if (gameState.farm[index] && gameState.farm[index].type) {
                gameState.farm[index].watered = true;
                gameState.farm[index].soilHealth = Math.min(100, gameState.farm[index].soilHealth + 5);
             } else {
                gameState.farm[index] = { status: 'watered', soilHealth: 100 };
             }
             
             pushAdvisorMessage(`💧 Petak ke-${index + 1} berhasil diairi (-${formatRupiah(cost)}).`);
             renderGame();
        }

        function handleHarvest(index) {
            const plot = gameState.farm[index];
            if (!plot || !plot.type || plot.daysGrown < CROPS_DATA[plot.type].daysToGrow) return;

            const cropType = plot.type;
            const yieldAmount = Math.floor(Math.random() * 3) + 2; 

            gameState.inventory[cropType] = (gameState.inventory[cropType] || 0) + yieldAmount;
            
            gameState.farm[index] = { status: 'watered', soilHealth: 80 }; 

            pushAdvisorMessage(`🌾 **PANEN SUKSES!** Anda memanen ${yieldAmount} unit ${CROPS_DATA[cropType].name}. Lahan siap untuk babak penanaman baru!`);
            showMessage('Panen Sukses!', `Kamu memanen **${yieldAmount} unit ${CROPS_DATA[cropType].name}**. Hasil panen sudah ditambahkan ke Inventaris.`, `<button id="modal-plant-after-harvest" data-index="${index}" class="px-4 py-2 bg-lime-500 hover:bg-lime-600 text-white rounded-lg font-semibold">Tanam Lagi</button>`);
            
            document.getElementById('modal-content').querySelector('#modal-plant-after-harvest')?.addEventListener('click', (e) => {
                 hideModal();
                 showPlantModal(index);
            });
            renderGame();
        }

        function showPlantModal(index) {
            let optionsHtml = '';
            for (const type in CROPS_DATA) {
                const crop = CROPS_DATA[type];
                const cost = Math.round(crop.basePrice * 0.5); // Harga bibit 50% dari harga dasar panen
                const buttonColor = gameState.money >= cost ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-400';
                
                optionsHtml += `
                    <button data-type="${type}" data-cost="${cost}" ${gameState.money < cost ? 'disabled' : ''}
                            class="btn-plant w-full text-center p-3 ${buttonColor} text-white rounded-xl shadow font-semibold flex items-center justify-between mb-2">
                        <span>${crop.icon[0]} Tanam ${crop.name} (${crop.seasonPref})</span>
                        <span class="font-bold">${formatRupiah(cost)}</span>
                    </button>
                `;
            }

            const soilHealth = gameState.farm[index]?.soilHealth || 100;
            
            showMessage(
                `Tanam di Petak ${index + 1}`,
                `<p class="text-sm mb-4">Pilih bibit yang ingin kamu tanam. Kesehatan Tanah: <span class="font-bold text-green-600">${soilHealth}%</span></p> ${optionsHtml}`,
                ``
            );
            
            document.getElementById('modal-content').querySelectorAll('.btn-plant').forEach(button => {
                if (!button.disabled) {
                    button.addEventListener('click', () => {
                        handlePlanting(index, button.dataset.type, parseFloat(button.dataset.cost));
                        hideModal();
                    });
                }
            });
        }
        
        function handlePlanting(index, type, cost) {
            if (gameState.money < cost) {
                 pushAdvisorMessage('💰 Gagal menanam! Uang tidak cukup.', true);
                 return;
            }
            
            let soilHealth = 100;
            if (gameState.farm[index] && gameState.farm[index].soilHealth) {
                soilHealth = gameState.farm[index].soilHealth;
            }

            gameState.money -= cost;
            gameState.farm[index] = {
                type: type,
                daysGrown: 0,
                watered: false,
                soilHealth: soilHealth,
                isDiseased: false
            };
            
            pushAdvisorMessage(`🌱 Bibit ${CROPS_DATA[type].name} berhasil ditanam (-${formatRupiah(cost)}). Pertarungan melawan waktu dimulai!`);
            renderGame();
        }
        
        function showMarketModal() {
            let marketHtml = `
                <h3 class="text-2xl font-bold text-yellow-700 mb-4">Pasar Hasil Panen</h3>
                <p class="text-sm mb-4 text-gray-600">Jual persediaan Anda untuk mendapatkan uang. Harga berfluktuasi setiap hari berdasarkan permintaan pasar.</p>
                <div class="space-y-3">
            `;
            let totalItems = 0;

            for (const type in CROPS_DATA) {
                const crop = CROPS_DATA[type];
                const quantity = gameState.inventory[type] || 0;
                const price = gameState.marketPrices[type];
                totalItems += quantity;

                marketHtml += `
                    <div class="flex justify-between items-center p-3 bg-gray-100 rounded-lg">
                        <span class="text-lg font-semibold">${crop.icon[crop.icon.length - 1]} ${crop.name}</span>
                        <span class="text-sm text-gray-500">Harga per unit: <span class="font-bold text-green-600">${formatRupiah(price)}</span></span>
                        <span class="text-sm text-gray-500">Stok: <span class="font-bold">${quantity} unit</span></span>
                        <button data-type="${type}" data-qty="${quantity}" data-price="${price}"
                                class="btn-sell px-4 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold disabled:bg-gray-400"
                                ${quantity === 0 ? 'disabled' : ''}>
                            Jual Semua
                        </button>
                    </div>
                `;
            }

            if (totalItems === 0) {
                 marketHtml += `<p class="text-center text-gray-500">Tidak ada hasil panen untuk dijual. Waktunya menanam!</p>`;
            }
            
            marketHtml += `</div>`;

            showMessage(
                'Pasar & Jual',
                marketHtml
            );

            document.getElementById('modal-content').querySelectorAll('.btn-sell').forEach(button => {
                if (!button.disabled) {
                    button.addEventListener('click', () => {
                        const type = button.dataset.type;
                        const qty = parseInt(button.dataset.qty);
                        const price = parseInt(button.dataset.price);
                        
                        const revenue = qty * price;
                        gameState.money += revenue;
                        gameState.inventory[type] = 0;
                        
                        pushAdvisorMessage(`💰 **TRANSAKSI SUKSES!** Anda menjual ${qty} unit ${CROPS_DATA[type].name} dan mendapatkan ${formatRupiah(revenue)}. Modal bertambah!`);
                        hideModal();
                        renderGame();
                    });
                }
            });
        }
        
        // NEW: Upgrade System Logic
        function showUpgradeModal() {
            let upgradeHtml = `
                <h3 class="text-2xl font-bold text-indigo-700 mb-4">Pusat Peningkatan Teknologi</h3>
                <p class="text-sm mb-4 text-gray-600">Jadilah Petani Cerdas! Investasikan uangmu untuk peningkatan permanen. </p>
                <div class="space-y-3">
            `;
            
            for (const key in UPGRADES_DATA) {
                const upgrade = UPGRADES_DATA[key];
                const isOwned = gameState.upgrades[key];
                const buttonColor = gameState.money >= upgrade.cost ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-gray-400';

                upgradeHtml += `
                    <div class="p-3 bg-white border border-indigo-200 rounded-lg shadow-md">
                        <h4 class="text-lg font-bold text-indigo-600">${upgrade.name}</h4>
                        <p class="text-sm text-gray-700 my-2">${upgrade.desc}</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-bold text-red-600">Biaya: ${formatRupiah(upgrade.cost)}</span>
                            <button data-key="${key}" data-cost="${upgrade.cost}"
                                    class="btn-buy-upgrade px-4 py-1 text-white rounded-lg font-semibold transition duration-150 ease-in-out
                                    ${isOwned ? 'bg-green-600 cursor-not-allowed' : buttonColor}"
                                    ${isOwned || gameState.money < upgrade.cost ? 'disabled' : ''}>
                                ${isOwned ? 'SUDAH DIBELI' : 'BELI'}
                            </button>
                        </div>
                    </div>
                `;
            }
            
            upgradeHtml += `</div>`;
            
            showMessage(
                'Pusat Upgrade',
                upgradeHtml
            );
            
            document.getElementById('modal-content').querySelectorAll('.btn-buy-upgrade').forEach(button => {
                if (!button.disabled && button.textContent === 'BELI') {
                    button.addEventListener('click', () => {
                        const key = button.dataset.key;
                        const cost = parseInt(button.dataset.cost);
                        
                        gameState.money -= cost;
                        gameState.upgrades[key] = true;
                        
                        pushAdvisorMessage(`🎉 **GM MENGUCAPKAN SELAMAT!** Anda mengaktifkan **${UPGRADES_DATA[key].name}**! Permainan Anda baru saja mencapai tingkat kesulitan yang lebih rendah!`);
                        hideModal();
                        renderGame();
                    });
                }
            });
        }

        // --- EVENT LISTENERS ---

        function setupEventListeners() {
            // Event listener diubah agar memanggil nextDay secara asynchronous
            document.getElementById('btn-next-day').addEventListener('click', nextDay);
            document.getElementById('btn-show-market').addEventListener('click', showMarketModal);
            document.getElementById('btn-show-upgrades').addEventListener('click', showUpgradeModal); 
        }

        // --- MULAI GAME ---
        window.onload = initGame;
    </script>
</body>
</html>
