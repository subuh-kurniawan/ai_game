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
<title>Puzzle Akuntansi Interaktif Gemini</title>
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<style>
  /* Menghindari pemilihan teks saat drag */
  body { font-family: 'Inter', sans-serif; user-select: none; }
  /* Tinggi minimum untuk kolom drop */
  .column { min-height: 12rem; transition: background-color 0.2s; } 
  .account { cursor: grab; transition: transform 0.1s, box-shadow 0.1s; }
  .account:hover { transform: scale(1.03); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
</style>
</head>
<body class="bg-gray-50 p-4 sm:p-8">

<div class="max-w-4xl mx-auto bg-white p-6 sm:p-10 rounded-2xl shadow-2xl border border-gray-100">
  <h2 class="text-4xl font-extrabold text-gray-800 text-center mb-4">
    Puzzle Jurnal Akuntansi 🧠
  </h2>
  <p class="text-center text-gray-500 mb-6">
    Tentukan akun Debit dan Kredit yang tepat untuk setiap transaksi.
  </p>
  
  <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 mb-8 rounded-lg">
    <p class="text-xl font-bold text-indigo-800 text-center" id="transaction">
      Memuat Transaksi dari Gemini... Mohon tunggu ⏳
    </p>
  </div>

  <div class="flex flex-col sm:flex-row justify-between gap-6 mb-8">
    <!-- Debit Column -->
    <div id="debitColumn" class="column w-full sm:w-1/2 bg-green-50 p-4 border-2 border-green-300 rounded-xl shadow-inner"
         ondragover="event.preventDefault()" ondrop="handleDrop(event, 'debitColumn')">
      <h3 class="text-2xl font-semibold text-green-700 text-center mb-4 pb-2 border-b border-green-200">Debit</h3>
    </div>

    <!-- Credit Column -->
    <div id="creditColumn" class="column w-full sm:w-1/2 bg-red-50 p-4 border-2 border-red-300 rounded-xl shadow-inner"
         ondragover="event.preventDefault()" ondrop="handleDrop(event, 'creditColumn')">
      <h3 class="text-2xl font-semibold text-red-700 text-center mb-4 pb-2 border-b border-red-200">Kredit</h3>
    </div>
  </div>
  
  <!-- Account Options -->
  <div class="bg-gray-200 p-5 rounded-xl shadow-md mb-8">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Pilihan Akun: Seret ke kolom (Hanya satu per kolom)</h3>
    <div id="accounts" class="flex flex-wrap gap-3 justify-center">
      <!-- Akun akan dimuat di sini oleh JavaScript -->
      <div id="loading-accounts" class="text-gray-500">Memuat opsi akun...</div>
    </div>
  </div>

  <button onclick="checkAnswer()" id="check-button" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl rounded-lg transition duration-200 shadow-xl transform hover:scale-[1.005] disabled:opacity-50" disabled>
    Cek Jawaban
  </button>
  
  <!-- Feedback & Score -->
  <div class="mt-6 flex flex-col sm:flex-row justify-between items-center">
    <p id="feedback" class="text-lg font-bold text-center sm:text-left min-h-[1.5rem] mb-2 sm:mb-0"></p>
    <p class="text-xl font-bold text-gray-700">Skor: <span id="score" class="text-blue-600">0</span></p>
  </div>
  
  <p class="text-sm text-center text-gray-500 mt-4">Transaksi ke: <span id="current-index-display">0</span> dari <span id="total-transactions">10</span></p>
</div>

<script>
// Variabel global untuk API
 const apiKey =  <?php echo $apiKeyJson; ?>; 
         const md =  <?php echo json_encode($model); ?>;

// Opsi Akun yang paling umum (digunakan untuk schema API)
const accountOptions = [
    "Kas", "Piutang Usaha", "Perlengkapan", "Peralatan", "Utang Usaha", 
    "Modal Pemilik", "Pendapatan Jasa", "Beban Sewa", "Beban Gaji", "Prive"
];

let transactions = []; // Akan diisi oleh API
let currentIndex = 0;
let score = 0;

// Elemen DOM
const transactionEl = document.getElementById("transaction");
const accountsEl = document.getElementById("accounts");
const debitColumn = document.getElementById("debitColumn");
const creditColumn = document.getElementById("creditColumn");
const feedbackEl = document.getElementById("feedback");
const scoreEl = document.getElementById("score");
const currentIndexDisplayEl = document.getElementById("current-index-display");
const totalTransactionsEl = document.getElementById("total-transactions");
const checkButton = document.getElementById("check-button");

/**
 * 1. Panggil Gemini API untuk menghasilkan 10 transaksi akuntansi dalam format JSON.
 * Menggunakan exponential backoff untuk mencoba kembali jika ada masalah koneksi/throttling.
 */
async function fetchTransactions() {
    const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${apiKey[0]}`;
    const systemPrompt = "Anda adalah seorang ahli akuntansi. Buatkan 10 transaksi jurnal ganda dasar untuk perusahaan jasa. Fokus pada transaksi yang melibatkan akun umum yang tersedia.";
    const userQuery = `Buatkan 10 objek transaksi akuntansi. Setiap transaksi harus memiliki 'text' (deskripsi transaksi), 'debit' (nama akun yang didebit), dan 'credit' (nama akun yang dikredit). Akun yang digunakan harus dipilih dari daftar ini: ${accountOptions.join(', ')}. Pastikan akun debit dan kredit selalu berbeda.`;
    
    // Skema JSON untuk memastikan output terstruktur
    const responseSchema = {
        type: "ARRAY",
        description: "Daftar 10 transaksi akuntansi, masing-masing dengan debit dan kredit.",
        items: {
            type: "OBJECT",
            properties: {
                "text": { "type": "STRING", "description": "Deskripsi singkat transaksi." },
                "debit": { "type": "STRING", "description": "Nama akun yang didebit." },
                "credit": { "type": "STRING", "description": "Nama akun yang dikredit." }
            },
            required: ["text", "debit", "credit"],
            propertyOrdering: ["text", "debit", "credit"]
        }
    };

    const payload = {
        contents: [{ parts: [{ text: userQuery }] }],
        systemInstruction: { parts: [{ text: systemPrompt }] },
        generationConfig: {
            responseMimeType: "application/json",
            responseSchema: responseSchema
        }
    };

    const MAX_RETRIES = 5;
    for (let attempt = 0; attempt < MAX_RETRIES; attempt++) {
        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const result = await response.json();
            const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;

            if (jsonText) {
                // Hapus nomor urut dan karakter non-JSON yang tidak perlu
                const sanitizedJsonText = jsonText.replace(/```json|```/g, '').trim();
                const parsedTransactions = JSON.parse(sanitizedJsonText);
                
                if (Array.isArray(parsedTransactions) && parsedTransactions.length > 0) {
                    return parsedTransactions;
                }
            }
            throw new Error("Respons API tidak valid atau kosong.");

        } catch (error) {
            if (attempt < MAX_RETRIES - 1) {
                const delay = Math.pow(2, attempt) * 1000;
                await new Promise(resolve => setTimeout(resolve, delay));
            } else {
                throw error;
            }
        }
    }
}

/**
 * Memuat transaksi berdasarkan index saat ini dan mengatur ulang UI.
 * @param {number} index - Index transaksi yang akan dimuat.
 */
function loadTransaction(index){
    if (index >= transactions.length) return;

    const t = transactions[index];
    transactionEl.textContent = `${index + 1}. ${t.text}`;
    feedbackEl.textContent = "";
    currentIndexDisplayEl.textContent = index + 1;
    checkButton.disabled = false;

    // Kosongkan kolom dan kembalikan header
    debitColumn.innerHTML = '<h3 class="text-2xl font-semibold text-green-700 text-center mb-4 pb-2 border-b border-green-200">Debit</h3>';
    creditColumn.innerHTML = '<h3 class="text-2xl font-semibold text-red-700 text-center mb-4 pb-2 border-b border-red-200">Kredit</h3>';

    // Buat ulang akun draggable
    accountsEl.innerHTML = "";
    accountOptions.forEach(acc => {
        const div = document.createElement("div");
        div.className = "account bg-blue-500 text-white px-4 py-2 rounded-full font-medium shadow-lg hover:bg-blue-600 transition duration-150 transform hover:scale-105";
        div.textContent = acc;
        div.setAttribute("draggable","true");
        div.dataset.account = acc;
        div.addEventListener("dragstart", e => {
            e.dataTransfer.setData("text/plain", acc);
            setTimeout(() => div.classList.add("opacity-50"), 0); 
        });
        div.addEventListener("dragend", () => div.classList.remove("opacity-50"));
        accountsEl.appendChild(div);
    });
}

/**
 * Handler untuk event drop. Memindahkan elemen akun ke kolom tujuan.
 */
function handleDrop(e, colId){
    e.preventDefault();
    const accountName = e.dataTransfer.getData("text/plain");
    const accountDiv = document.querySelector(`.account[data-account='${accountName}']`);
    const targetCol = document.getElementById(colId);

    if(accountDiv){
        // Kembalikan akun yang sudah ada di kolom tujuan ke area pilihan
        const existingAccount = targetCol.querySelector(".account");
        if (existingAccount) {
            accountsEl.appendChild(existingAccount);
        }
        
        // Pindahkan akun baru ke kolom
        targetCol.appendChild(accountDiv);

        // Pastikan akun yang sama tidak ada di kolom lain, jika ada, pindahkan ke accountsEl
        const otherColId = colId === 'debitColumn' ? 'creditColumn' : 'debitColumn';
        const otherCol = document.getElementById(otherColId);
        const accountInOtherCol = otherCol.querySelector(`.account[data-account='${accountName}']`);
        if(accountInOtherCol) {
            otherCol.removeChild(accountInOtherCol);
            accountsEl.appendChild(accountInOtherCol); 
        }
    }
}

/**
 * Memeriksa jawaban pengguna terhadap jawaban yang benar.
 */
function checkAnswer(){
    if (currentIndex >= transactions.length) return;
    checkButton.disabled = true;
    
    // Ambil data-account dari elemen .account pertama di setiap kolom
    const debitSelected = debitColumn.querySelector(".account")?.dataset.account;
    const creditSelected = creditColumn.querySelector(".account")?.dataset.account;
    const correct = transactions[currentIndex];

    if(!debitSelected || !creditSelected){
        feedbackEl.textContent = "⚠️ Mohon seret satu akun ke Debit dan satu ke Kredit.";
        feedbackEl.style.color = "orange";
        checkButton.disabled = false;
        return;
    }

    if(debitSelected === correct.debit && creditSelected === correct.credit){
        feedbackEl.textContent = "✅ Jawaban benar! Transaksi berhasil dijurnal.";
        feedbackEl.style.color = "green";
        score += 10;
    } else {
        feedbackEl.textContent = `❌ Jawaban salah. Debit seharusnya "${correct.debit}", Kredit seharusnya "${correct.credit}".`;
        feedbackEl.style.color = "red";
    }
    scoreEl.textContent = score;

    // Transaksi berikutnya setelah delay
    setTimeout(() => {
        currentIndex++;
        if(currentIndex < transactions.length){
            loadTransaction(currentIndex);
        } else {
            transactionEl.textContent = `🎉 Semua ${transactions.length} transaksi selesai! Skor Akhir Anda: ${score}`;
            accountsEl.innerHTML = '<p class="text-xl text-green-700 font-semibold">Selamat, Anda telah menyelesaikan puzzle ini!</p>';
            debitColumn.innerHTML = '<h3 class="text-2xl font-semibold text-green-700 text-center mb-4 pb-2 border-b border-green-200">Debit</h3>';
            creditColumn.innerHTML = '<h3 class="text-2xl font-semibold text-red-700 text-center mb-4 pb-2 border-b border-red-200">Kredit</h3>';
            checkButton.style.display = 'none'; 
        }
    }, 2000);
}

/**
 * Fungsi inisialisasi game utama.
 */
async function initGame() {
    transactionEl.textContent = "Sedang membuat 10 Transaksi Akuntansi... Mohon tunggu ⏳";
    totalTransactionsEl.textContent = '...';
    accountsEl.innerHTML = '<div class="text-gray-500">Menghubungi AI untuk data transaksi...</div>';

    try {
        const fetchedTransactions = await fetchTransactions();
        transactions = fetchedTransactions.slice(0, 10); // Ambil hanya 10 transaksi pertama
        totalTransactionsEl.textContent = transactions.length;
        
        if(transactions.length > 0){
            loadTransaction(currentIndex);
        } else {
            transactionEl.textContent = "Gagal memuat transaksi: Data dari API kosong.";
            accountsEl.innerHTML = '<div class="text-red-500">Tidak ada transaksi yang bisa dimainkan.</div>';
        }

    } catch (error) {
        transactionEl.textContent = "Gagal memuat transaksi. Error Koneksi API.";
        accountsEl.innerHTML = '<div class="text-red-500">Terjadi kesalahan. Silakan muat ulang.</div>';
        console.error("Error fetching transactions:", error);
    }
}

// Mulai game saat halaman dimuat
initGame();
</script>

</body>
</html>
