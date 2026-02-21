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
<title>AkunQuest – Petualangan Akuntan</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  /* Inter Font */
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
  body {
    font-family: 'Inter', sans-serif;
  }
  .animate-fadeIn {
    animation: fadeIn 0.5s ease-out;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  /* Simple pulse animation for loading */
  .animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }
  @keyframes pulse {
    0%, 100% {
      opacity: 1;
    }
    50% {
      opacity: .8;
      transform: scale(0.99);
    }
  }
  .hover\:bg-blue-700 {
    transition: background-color 0.3s, transform 0.1s;
  }
  .hover\:bg-blue-700:active {
    transform: scale(0.98);
  }

  /* Style untuk Ledger/Buku Besar */
  .ledger-card {
    border: 1px solid #e2e8f0;
    margin-bottom: 1rem;
    padding: 1rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }
  .ledger-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
  }
  .ledger-table th, .ledger-table td {
    padding: 0.5rem;
    text-align: left;
    border: 1px solid #cbd5e1;
  }
  .ledger-table th {
    background-color: #f3f4f6;
    font-weight: 600;
  }
  .ledger-header {
    background-color: #f1f5f9;
    padding: 0.75rem;
    border-radius: 0.5rem 0.5rem 0 0;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: -1px;
    border: 1px solid #e2e8f0;
  }
</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex flex-col items-center py-10 px-4">

<h1 class="text-5xl font-extrabold text-blue-800 mb-8 tracking-tight">
  Akun<span class="text-indigo-600">Quest</span>
</h1>
<p class="text-lg text-gray-600 mb-8 text-center max-w-xl">
  Selamat datang, Akuntan! Tentukan **Akun Debit** dan **Akun Kredit** yang tepat untuk setiap transaksi.
</p>

<!-- Card Game -->
<div id="gameCard" class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-xl flex flex-col items-center animate-fadeIn border-b-4 border-indigo-500">
  
  <!-- Story -->
  <p id="story" class="text-gray-700 italic mb-6 text-center text-sm bg-blue-50 p-3 rounded-xl w-full">Memuat transaksi...</p>

  <!-- Transaction -->
  <div class="mb-6 w-full text-center">
    <span class="text-2xl text-gray-900 font-bold block">
      Transaksi:
    </span>
    <p id="transactionText" class="text-3xl text-indigo-700 font-extrabold mt-1"></p>
  </div>

  <!-- Dropdown -->
  <div id="dropdownContainer" class="flex flex-col sm:flex-row gap-4 mb-6 w-full justify-center">
    <div class="flex-1">
      <label for="debitSelect" class="block text-sm font-medium text-gray-700 mb-1">Pilih DEBIT:</label>
      <select id="debitSelect" class="p-3 border-2 border-indigo-300 rounded-xl w-full focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" disabled></select>
    </div>
    <div class="flex-1">
      <label for="creditSelect" class="block text-sm font-medium text-gray-700 mb-1">Pilih KREDIT:</label>
      <select id="creditSelect" class="p-3 border-2 border-indigo-300 rounded-xl w-full focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" disabled></select>
    </div>
  </div>

  <!-- Button -->
  <button onclick="checkAnswer()" class="bg-indigo-600 text-white font-semibold tracking-wider px-8 py-3 rounded-full hover:bg-indigo-700 transition shadow-lg hover:shadow-xl w-full sm:w-auto" disabled>
    Cek Jurnal!
  </button>

  <!-- Feedback (Used for final summary at the end) -->
  <p id="feedback" class="mt-6 text-xl font-extrabold"></p>
  
  <p id="loadStatus" class="text-xs text-gray-500 mt-2">Status Pemuatan: Memuat dari AI...</p>

  <!-- Progress Bar -->
  <div class="w-full bg-gray-200 rounded-full mt-8 h-3">
    <div id="progressBar" class="bg-green-500 h-3 rounded-full w-0 transition-all duration-500 shadow-inner"></div>
  </div>
</div>

<!-- Score -->
<div class="mt-8 bg-white rounded-xl p-4 shadow-lg border border-gray-200">
  <p class="text-xl font-semibold text-gray-700">Skor Anda: <span id="score" class="text-3xl text-red-600 font-bold ml-2">0</span></p>
</div>

<script>
// --- KONFIGURASI API GEMINI & LOCAL STORAGE ---
 const apiKey =  <?php echo $apiKeyJson; ?>; 
         const md =  <?php echo json_encode($model); ?>;
const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey[0]}`;
const MAX_RETRIES = 5;
const LOCAL_STORAGE_KEY = 'akunquest_transactions'; // Kunci untuk penyimpanan lokal

// --- DAFTAR AKUN YANG DIIZINKAN ---
const mockAccounts = [
  "Pilih Akun",
  "Kas",
  "Piutang Usaha",
  "Peralatan Kantor",
  "Utang Usaha",
  "Modal Pemilik",
  "Prive",
  "Pendapatan Jasa",
  "Beban Gaji",
  "Beban Sewa",
  "Beban Listrik dan Air",
];

// --- VARIABEL GAME ---
let transactions = [];
let accounts = mockAccounts;
let current = 0;
let score = 0;
let results = []; // Menyimpan riwayat jawaban user & jawaban benar
let isGameActive = false;

// --- ELEMEN UI ---
const storyEl = document.getElementById('story');
const transactionText = document.getElementById('transactionText');
const debitSelect = document.getElementById('debitSelect');
const creditSelect = document.getElementById('creditSelect');
const feedback = document.getElementById('feedback');
const scoreDisplay = document.getElementById('score');
const progressBar = document.getElementById('progressBar');
const checkButton = document.querySelector('button');
const gameCard = document.getElementById('gameCard');
const dropdownContainer = document.getElementById('dropdownContainer');
const loadStatusEl = document.getElementById('loadStatus');


// --- FUNGSI UTILITY LOCAL STORAGE ---

/**
 * Menyimpan array transaksi ke localStorage.
 * @param {Array} data - Array objek transaksi.
 */
function saveTransactionsToLocalStorage(data) {
    try {
        const dataToSave = JSON.stringify(data);
        localStorage.setItem(LOCAL_STORAGE_KEY, dataToSave);
        console.log("Transaksi berhasil disimpan di localStorage.");
    } catch (e) {
        console.error("Gagal menyimpan ke localStorage:", e);
    }
}

/**
 * Memuat array transaksi dari localStorage.
 * @returns {Array | null} Array transaksi atau null jika tidak ada.
 */
function loadTransactionsFromLocalStorage() {
    try {
        const data = localStorage.getItem(LOCAL_STORAGE_KEY);
        if (data) {
            console.log("Transaksi berhasil dimuat dari localStorage.");
            return JSON.parse(data);
        }
    } catch (e) {
        console.error("Gagal memuat dari localStorage:", e);
    }
    return null;
}

// --- FUNGSI API CALL DENGAN EXPONENTIAL BACKOFF ---

/**
 * Melakukan fetch ke API dengan mekanisme retry.
 */
async function fetchWithRetry(url, options, retries = 0) {
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    } catch (error) {
        if (retries < MAX_RETRIES) {
            const delay = Math.pow(2, retries) * 1000; // 1s, 2s, 4s, 8s, 16s
            console.warn(`Retry attempt ${retries + 1} in ${delay}ms...`);
            await new Promise(resolve => setTimeout(resolve, delay));
            return fetchWithRetry(url, options, retries + 1);
        }
        throw new Error("Gagal mengambil data setelah beberapa kali percobaan.");
    }
}

/**
 * Mengambil daftar transaksi baru dari Gemini API.
 */
async function fetchTransactionsFromGemini() {
    const allowedAccounts = accounts.slice(1); // Exclude "Pilih Akun"
    
    // Instruksi spesifik untuk AI
    const userQuery = `Buatkan 10 transaksi akuntansi sederhana untuk perusahaan jasa. Setiap transaksi harus berfokus pada debit dan kredit dari DUA akun saja. Akun yang digunakan HANYA boleh dari daftar berikut (dalam Bahasa Indonesia): ${allowedAccounts.join(', ')}.`;
    
    const payload = {
        contents: [{ parts: [{ text: userQuery }] }],
        systemInstruction: {
            parts: [{ text: "Anda adalah pembuat konten akuntansi profesional. Tugas Anda adalah membuat data transaksi akuntansi dalam format JSON yang sangat terstruktur, lengkap dengan cerita (story), deskripsi transaksi (transaction), dan pasangan debit/kredit yang benar (correct_debit, correct_credit). Pastikan output Anda bersih, valid, dan setiap akun yang digunakan harus ada dalam daftar akun yang diizinkan." }]
        },
        generationConfig: {
            responseMimeType: "application/json",
            responseSchema: {
                type: "ARRAY",
                description: "Array of accounting transactions.",
                items: {
                    type: "OBJECT",
                    properties: {
                        "story": { "type": "STRING", "description": "Narasi singkat yang menjelaskan latar belakang transaksi." },
                        "transaction": { "type": "STRING", "description": "Deskripsi jurnal transaksi dan nilai moneter (misalnya: Pembayaran Gaji sebesar Rp 5.000.000)." },
                        "correct_debit": { "type": "STRING", "description": "Akun yang didebit. HARUS diambil dari daftar akun yang diizinkan." },
                        "correct_credit": { "type": "STRING", "description": "Akun yang dikredit. HARUS diambil dari daftar akun yang diizinkan." }
                    },
                    required: ["story", "transaction", "correct_debit", "correct_credit"]
                }
            }
        }
    };

    const result = await fetchWithRetry(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    const jsonString = result?.candidates?.[0]?.content?.parts?.[0]?.text;
    
    if (!jsonString) {
        throw new Error("Gemini tidak mengembalikan teks JSON yang valid.");
    }
    
    // Parse the JSON string into an array of objects
    return JSON.parse(jsonString);
}


// --- FUNGSI GAME LOGIC ---

/**
 * Fungsi utama untuk memuat data transaksi, mengutamakan localStorage.
 * @param {boolean} forceFetch - Jika true, paksa pemanggilan API, abaikan localStorage.
 */
async function loadTransactions(forceFetch = false) {
  current = 0;
  score = 0;
  scoreDisplay.textContent = 0;
  results = []; // Reset riwayat
  
  // Tampilkan UI game
  dropdownContainer.style.display = 'flex';
  debitSelect.style.display = 'block';
  creditSelect.style.display = 'block';
  checkButton.style.display = 'block';
  checkButton.onclick = checkAnswer; // Set kembali ke fungsi cek jawaban

  // Hapus cetak jurnal dan ledger lama jika ada
  const journalPrintout = document.getElementById('journalPrintout');
  if (journalPrintout) {
      journalPrintout.remove();
  }
  const ledgerPrintout = document.getElementById('ledgerPrintout');
  if (ledgerPrintout) {
      ledgerPrintout.remove();
  }

  populateAccounts();
  
  // Reset UI sebelum memuat
  debitSelect.disabled = true;
  creditSelect.disabled = true;
  checkButton.disabled = true;
  feedback.textContent = "";
  isGameActive = false;

  let fetchedData = null;
  
  if (!forceFetch) {
      fetchedData = loadTransactionsFromLocalStorage();
  }
  
  // Tentukan apakah perlu memanggil API atau menggunakan data lokal
  if (fetchedData && fetchedData.length > 0) {
      // Muat dari Local Storage
      loadStatusEl.textContent = "Status Pemuatan: Berhasil dimuat dari Local Storage.";
      transactions = fetchedData.sort(() => Math.random() - 0.5); // Acak urutan
      isGameActive = true;
      showTransaction();
  } else {
      // Panggil API (atau jika forceFetch=true)
      loadStatusEl.textContent = "Status Pemuatan: Memuat dari AI...";
      gameCard.classList.add('animate-pulse');
      storyEl.textContent = "Sedang menghubungi Akuntan A.I. untuk mendapatkan 10 transaksi baru...";
      transactionText.textContent = "Mohon Tunggu (Maks 10 detik)...";

      try {
        fetchedData = await fetchTransactionsFromGemini();
        transactions = fetchedData.filter(t => t.story && t.transaction);
        gameCard.classList.remove('animate-pulse');
        
        if (transactions.length === 0) {
          feedback.innerHTML = "Gagal memuat transaksi. AI mengembalikan data kosong.";
          feedback.className = "text-red-600 mt-6 font-bold";
          loadStatusEl.textContent = "Status Pemuatan: Gagal.";
          return;
        }
        
        // Simpan data baru ke Local Storage
        saveTransactionsToLocalStorage(transactions);
        loadStatusEl.textContent = "Status Pemuatan: Berhasil dimuat dari AI & disimpan di Local Storage.";

        // Acak dan mulai game
        transactions = transactions.sort(() => Math.random() - 0.5); 
        isGameActive = true;
        showTransaction();
      } catch (error) {
        gameCard.classList.remove('animate-pulse');
        console.error("Error loading transactions:", error);
        feedback.innerHTML = "❌ **Koneksi Gagal!** Transaksi A.I. gagal dimuat. Cek konsol untuk detail error.";
        feedback.className = "text-red-600 mt-6 font-bold";
        loadStatusEl.textContent = "Status Pemuatan: Gagal total.";
        // Sembunyikan elemen game jika gagal
        dropdownContainer.style.display = 'none';
        checkButton.style.display = 'none';
      }
  }
}

// Isi dropdown akun
function populateAccounts() {
  debitSelect.innerHTML = "";
  creditSelect.innerHTML = "";
  accounts.forEach(acc => {
    const optionD = document.createElement('option');
    optionD.value = acc;
    optionD.textContent = acc;
    if (acc === "Pilih Akun") {
        optionD.disabled = true;
        optionD.selected = true;
    }
    debitSelect.appendChild(optionD);

    const optionC = document.createElement('option');
    optionC.value = acc;
    optionC.textContent = acc;
    if (acc === "Pilih Akun") {
        optionC.disabled = true;
        optionC.selected = true;
    }
    creditSelect.appendChild(optionC);
  });
}

// Tampilkan transaksi saat ini
function showTransaction() {
  // Aktifkan UI elemen
  debitSelect.disabled = false;
  creditSelect.disabled = false;
  checkButton.disabled = false;
  checkButton.textContent = "Cek Jurnal!";
  checkButton.classList.remove('bg-gray-400', 'hover:bg-gray-500');
  checkButton.classList.add('bg-indigo-600', 'hover:bg-indigo-700');


  if (current >= transactions.length) {
    // KONDISI AKHIR GAME
    isGameActive = false;
    feedback.innerHTML = `🎉 **Selesai!** Anda telah menyelesaikan ${transactions.length} transaksi. Skor akhir: <span class="text-4xl text-red-600">${score}</span>.`;
    feedback.className = "text-indigo-600 mt-6 font-bold text-center";
    storyEl.textContent = "Laporan Keuangan Mini:";
    transactionText.textContent = "Cetak Jurnal dan Buku Besar";
    
    // Sembunyikan dropdown dan ubah fungsi tombol
    dropdownContainer.style.display = 'none';
    
    // Tombol untuk memuat baru dari API (forceFetch = true)
    checkButton.textContent = "Muat Transaksi Baru (Dari AI)"; 
    checkButton.onclick = () => loadTransactions(true); 
    
    // Panggil fungsi cetak jurnal dan ledger
    displayJournal(); 
    displayLedger();
    
    return;
  }

  storyEl.textContent = transactions[current].story;
  transactionText.textContent = transactions[current].transaction;
  debitSelect.value = "Pilih Akun";
  creditSelect.value = "Pilih Akun";
  feedback.textContent = "";
  feedback.className = "mt-6 text-xl font-extrabold"; // Reset class
  updateProgress();
}

/**
 * Mengambil nilai moneter dari string transaksi.
 * @param {string} transactionText - String deskripsi transaksi (contoh: "Pembayaran Gaji sebesar Rp 5.000.000").
 * @returns {number} Nilai moneter.
 */
function extractAmount(transactionText) {
    const regex = /Rp\s*([\d\.,]+)/;
    const match = transactionText.match(regex);
    if (match && match[1]) {
        // Hapus titik sebagai pemisah ribuan, ganti koma menjadi titik (jika ada) untuk desimal, lalu parse
        const cleanNumber = match[1].replace(/\./g, '').replace(/,/g, '.');
        return parseFloat(cleanNumber);
    }
    return 0;
}

// --- FUNGSI LEDGER (BUKU BESAR) ---
/**
 * Menghitung dan menampilkan saldo akhir buku besar (Ledger)
 */
function displayLedger() {
    // 1. Inisialisasi saldo awal untuk semua akun yang diizinkan (non-"Pilih Akun")
    const ledgerBalances = {};
    accounts.slice(1).forEach(acc => {
        // { debit: total_debit, credit: total_credit, normal_balance: 'D'|'K' }
        ledgerBalances[acc] = { 
            debit: 0, 
            credit: 0 
        };
    });

    // Tentukan saldo normal (untuk memudahkan penentuan Saldo Akhir)
    const normalBalances = {
        "Kas": 'D',
        "Piutang Usaha": 'D',
        "Peralatan Kantor": 'D',
        "Utang Usaha": 'K',
        "Modal Pemilik": 'K',
        "Prive": 'D',
        "Pendapatan Jasa": 'K',
        "Beban Gaji": 'D',
        "Beban Sewa": 'D',
        "Beban Listrik dan Air": 'D',
    };

    // 2. Posting (Jurnal) ke Buku Besar
    results.forEach(res => {
        // Hanya posting transaksi yang dijawab BENAR oleh user
        if (res.is_correct) {
            const amount = extractAmount(res.transaction);
            
            // Post Debit
            if (ledgerBalances[res.user_debit]) {
                ledgerBalances[res.user_debit].debit += amount;
            }

            // Post Credit
            if (ledgerBalances[res.user_credit]) {
                ledgerBalances[res.user_credit].credit += amount;
            }
        }
    });

    // 3. Menghitung Saldo Akhir dan membuat HTML
    let ledgerHtml = `
        <div class="mt-8 w-full overflow-x-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Cetak Buku Besar (Ledger)</h2>
            <p class="text-sm text-gray-600 mb-4 text-center italic">Saldo dihitung hanya berdasarkan transaksi yang Anda jawab dengan BENAR.</p>
    `;

    // Fungsi helper untuk memformat mata uang
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    };

    // Iterasi melalui semua akun untuk mencetak Buku Besar
    for (const [accountName, totals] of Object.entries(ledgerBalances)) {
        const saldoNormal = normalBalances[accountName] || 'D';
        const netBalance = totals.debit - totals.credit;
        
        let finalBalanceText = 'Rp 0';
        let finalBalanceType = ''; // D or K
        let balanceClass = 'text-gray-800';

        if (netBalance !== 0) {
            if (netBalance > 0) {
                finalBalanceType = 'Debit';
                finalBalanceText = formatRupiah(netBalance);
                balanceClass = saldoNormal === 'D' ? 'text-green-600 font-bold' : 'text-red-600 font-bold';
            } else {
                finalBalanceType = 'Kredit';
                finalBalanceText = formatRupiah(-netBalance);
                balanceClass = saldoNormal === 'K' ? 'text-green-600 font-bold' : 'text-red-600 font-bold';
            }
        }
        
        // Ledger Card untuk setiap akun
        ledgerHtml += `
            <div class="ledger-card bg-gray-50">
                <div class="ledger-header bg-indigo-100 flex justify-between items-center">
                    <span>Akun: **${accountName}** (Saldo Normal: ${saldoNormal})</span>
                </div>
                <div class="flex">
                    <!-- Sisi Debit -->
                    <div class="flex-1 border-r border-gray-300 p-2">
                        <h4 class="text-xs font-semibold uppercase mb-1 text-center text-indigo-700">Debit (D)</h4>
                        <p class="text-lg text-left">${formatRupiah(totals.debit)}</p>
                    </div>
                    <!-- Sisi Kredit -->
                    <div class="flex-1 p-2">
                        <h4 class="text-xs font-semibold uppercase mb-1 text-center text-indigo-700">Kredit (K)</h4>
                        <p class="text-lg text-right">${formatRupiah(totals.credit)}</p>
                    </div>
                </div>
                <!-- Saldo Akhir -->
                <div class="mt-2 p-2 border-t border-indigo-300 bg-white rounded-b-lg flex justify-between">
                    <span class="text-sm font-semibold text-gray-700">SALDO AKHIR:</span>
                    <span class="text-sm ${balanceClass}">
                        ${finalBalanceType ? `${finalBalanceType} (${finalBalanceText})` : 'Rp 0 (Nihil)'}
                    </span>
                </div>
            </div>
        `;
    }

    ledgerHtml += `</div>`;

    const ledgerContainer = document.createElement('div');
    ledgerContainer.id = 'ledgerPrintout';
    ledgerContainer.innerHTML = ledgerHtml;
    
    // Sisipkan tabel ledger setelah jurnal dan sebelum feedback
    gameCard.insertBefore(ledgerContainer, feedback);
}
// --- AKHIR FUNGSI LEDGER ---


/**
 * Menampilkan ringkasan jurnal dalam bentuk tabel setelah permainan berakhir.
 */
function displayJournal() {
    let tableHtml = `
        <div class="mt-8 w-full overflow-x-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Cetak Jurnal Transaksi (${results.length} Item)</h2>
            <table class="min-w-full divide-y divide-indigo-200 shadow-xl rounded-lg overflow-hidden border border-indigo-200">
                <thead class="bg-indigo-100">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">No.</th>
                        <th class="px-3 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Kisah & Transaksi</th>
                        <th class="px-3 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Jawaban Anda (D/K)</th>
                        <th class="px-3 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Jawaban Benar (D/K)</th>
                        <th class="px-3 py-3 text-center text-xs font-bold text-indigo-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
    `;

    results.forEach((res, index) => {
        const statusClass = res.is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
        const statusText = res.is_correct ? 'BENAR' : 'SALAH';
        
        // Cek apakah jawaban user benar atau salah untuk styling
        const debitClass = res.user_debit === res.correct_debit ? 'text-green-600' : 'text-red-600';
        const creditClass = res.user_credit === res.correct_credit ? 'text-green-600' : 'text-red-600';

        tableHtml += `
            <tr>
                <td class="px-3 py-2 whitespace-nowrap text-center">${index + 1}</td>
                <td class="px-3 py-2 max-w-xs w-1/3">
                    <p class="font-medium text-gray-900">${res.story}</p>
                    <p class="text-xs text-indigo-600 mt-1">(${res.transaction})</p>
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    <div class="text-xs font-semibold ${debitClass}">D: ${res.user_debit}</div>
                    <div class="text-xs font-semibold ${creditClass}">K: ${res.user_credit}</div>
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    <div class="text-xs text-gray-600">D: ${res.correct_debit}</div>
                    <div class="text-xs text-gray-600">K: ${res.correct_credit}</div>
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full ${statusClass}">
                        ${statusText}
                    </span>
                </td>
            </tr>
        `;
    });

    tableHtml += `
                </tbody>
            </table>
        </div>
    `;

    const journalContainer = document.createElement('div');
    journalContainer.id = 'journalPrintout';
    journalContainer.innerHTML = tableHtml;
    
    // Sisipkan tabel jurnal sebelum elemen feedback
    gameCard.insertBefore(journalContainer, feedback); 
}

// Cek jawaban
function checkAnswer() {
  if (!isGameActive) return; // Prevent checks if game isn't running
  
  const debitAnswer = debitSelect.value;
  const creditAnswer = creditSelect.value;
  
  if (debitAnswer === "Pilih Akun" || creditAnswer === "Pilih Akun") {
    feedback.textContent = "⚠️ Harap pilih Akun Debit dan Akun Kredit.";
    feedback.className = "text-yellow-600 mt-6 font-bold";
    return;
  }
  
  const currentTransaction = transactions[current];
  const correctDebit = currentTransaction.correct_debit;
  const correctCredit = currentTransaction.correct_credit;

  // Disable selections and button immediately after submitting
  debitSelect.disabled = true;
  creditSelect.disabled = true;
  checkButton.disabled = true;
  checkButton.textContent = "Memproses...";
  checkButton.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
  checkButton.classList.add('bg-gray-400', 'hover:bg-gray-500');

  const isCorrect = (debitAnswer === correctDebit && creditAnswer === correctCredit);
  
  // LOG THE RESULT
  results.push({
    story: currentTransaction.story,
    transaction: currentTransaction.transaction,
    user_debit: debitAnswer,
    user_credit: creditAnswer,
    correct_debit: correctDebit,
    correct_credit: correctCredit,
    is_correct: isCorrect
  });
  // END LOG THE RESULT

  if (isCorrect) {
    feedback.textContent = "✅ Benar! Neraca tetap seimbang! (+10)";
    feedback.className = "text-green-600 mt-6 font-bold";
    score += 10;
  } else {
    // Determine which part was wrong
    let correctDetails = [];
    if (debitAnswer !== correctDebit) {
      correctDetails.push(`Debit harusnya **${correctDebit}**`);
    }
    if (creditAnswer !== correctCredit) {
      correctDetails.push(`Kredit harusnya **${correctCredit}**`);
    }
    
    feedback.innerHTML = `❌ Salah! ${correctDetails.join(' dan ')}. (-5)`;
    feedback.className = "text-red-600 mt-6 font-bold";
    score = Math.max(0, score - 5); // Score cannot go below 0
  }

  scoreDisplay.textContent = score;
  current++;
  // Wait 2 seconds before showing the next transaction
  setTimeout(showTransaction, 2000);
}

// Update progress bar
function updateProgress() {
  const progressPercent = (current / transactions.length) * 100;
  progressBar.style.width = progressPercent + "%";
}

// Init Game on load
window.onload = loadTransactions; // Muat pertama kali, akan cek localStorage
</script>

</body>
</html>
