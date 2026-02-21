<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include '../admin/fungsi/koneksi.php';

// Proteksi jika diakses langsung
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['history'])) {
    die("<p class='text-red-600 font-bold'>❌ Akses tidak valid. Silakan jalankan analisa melalui halaman permainan.</p>");
}

// --- Ambil API key secara rotasi ---
if (!isset($_SESSION['api_key'])) {
    $r = $koneksi->query("SELECT api_key FROM api_keys WHERE usage_count=(SELECT MIN(usage_count) FROM api_keys) ORDER BY RAND() LIMIT 1");
    if ($r && $r->num_rows) {
        $_SESSION['api_key'] = $r->fetch_assoc()['api_key'];
        $stmt = $koneksi->prepare("UPDATE api_keys SET usage_count = usage_count + 1 WHERE api_key = ?");
        $stmt->bind_param("s", $_SESSION['api_key']);
        $stmt->execute();
    } else {
        die("<p class='text-red-600 font-bold'>❌ API key not found.</p>");
    }
}
$apiKey = $_SESSION['api_key'];

// --- Ambil data dari form ---
$gameHistoryJson = $_POST['history'];
$tema = $_POST['tema'] ?? 'Umum';
$gameHistory = json_decode($gameHistoryJson, true);
if (!is_array($gameHistory)) $gameHistory = [];

// --- Siapkan prompt AI ---
$systemInstruction = "Anda adalah Analis Permainan AI. Tugas Anda adalah memberikan umpan balik singkat dan relevan berdasarkan riwayat permainan pengguna.  
Fokus hanya pada dua hal utama:  
1. Relevansi Terhadap Tema: Evaluasi sejauh mana pilihan pemain sesuai dengan tema utama permainan.  
2. Skor Pencapaian Terhadap Tujuan: Berikan skor numerik (0-100) yang mencerminkan seberapa baik pemain mencapai tujuan dari permainan, dan berikan interpretasi singkat.  
Sajikan hasil dalam format Markdown dengan heading ### untuk setiap bagian.";

$promptText = "[TEMA PERMAINAN: $tema]\n\n[RIWAYAT PERMAINAN]\n";
foreach ($gameHistory as $index => $entry) {
    $langkah = $index + 1;
    $narasi = htmlspecialchars_decode($entry['narasi'] ?? '');
    $pilihan = htmlspecialchars_decode($entry['pilihan'] ?? '');
    
    $promptText .= "Langkah $langkah:\nNarasi: $narasi\nPilihan: $pilihan\n";
    if (!empty($entry['selesai'])) $promptText .= "(Permainan Berakhir Di Sini)\n";
    $promptText .= "---\n";
}
$promptText .= "\n[TUGAS]\nAnalisis permainan berdasarkan instruksi sistem di atas.";

$jsSystemInstruction = json_encode($systemInstruction);
$jsPromptText = json_encode($promptText);
$jsApiKey = json_encode($apiKey);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analisa Game History</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
#aiOutput p { margin-bottom: 0.5rem; }
#aiOutput h3 { font-weight: bold; font-size: 1.15rem; margin-top: 1rem; margin-bottom: 0.5rem; color: #3b82f6; }
#aiOutput pre { white-space: pre-wrap; font-family: inherit; }
</style>
</head>
<body class="bg-gray-50 min-h-screen p-6 font-sans">

<div class="max-w-4xl mx-auto bg-white shadow-xl rounded-xl p-8 border border-gray-100">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">🎮 Analisa Riwayat Permainan</h1>

    <?php if (empty($gameHistory)): ?>
        <div class="p-4 bg-red-100 border border-red-300 rounded-lg text-red-800">
            <p class="font-semibold">❌ Riwayat Permainan Kosong.</p>
            <p class="text-sm">Silakan kembali ke permainan untuk memulai sesi baru.</p>
        </div>
    <?php else: ?>
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Detail Permainan:</h2>
        <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
            <?php foreach ($gameHistory as $index => $entry): ?>
                <div class="border-l-4 border-indigo-500 pl-4 py-3 px-4 bg-indigo-50 rounded-lg shadow-sm">
                    <p class="text-sm text-indigo-700 font-bold mb-1">LANGKAH <?= $index + 1 ?></p>
                    <p class="text-sm"><b>Narasi:</b> <span class="text-gray-600"><?= htmlspecialchars($entry['narasi']) ?></span></p>
                    <p class="text-sm"><b>Pilihan:</b> <span class="text-gray-900 font-medium"><?= htmlspecialchars($entry['pilihan']) ?></span></p>
                    <?php if (!empty($entry['selesai'])): ?>
                        <p class="text-xs mt-1 text-green-700 font-bold bg-green-100 inline-block px-2 py-0.5 rounded-full">✅ Permainan Selesai</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8">
            <button id="analyzeAI" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-lg hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-green-300 w-full md:w-auto transform hover:scale-[1.01] flex items-center justify-center space-x-2">
                <span>Analisa dengan AI</span>
            </button>
        </div>

        <div id="aiResult" class="mt-8 p-6 bg-blue-50 border border-blue-300 rounded-xl shadow-inner hidden">
            <h2 class="text-xl font-bold mb-3 text-blue-700">🤖 Hasil Analisa AI</h2>
            <div id="aiOutput" class="text-gray-700 min-h-[5rem] animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-11/12 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
const apiKey = <?php echo $jsApiKey; ?>;
const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5:generateContent?key=${apiKey}`;
const systemInstruction = <?php echo $jsSystemInstruction; ?>;
const promptText = <?php echo $jsPromptText; ?>;

const analyzeBtn = document.getElementById("analyzeAI");
const aiResultDiv = document.getElementById("aiResult");
const aiOutput = document.getElementById("aiOutput");

const fetchWithRetry = async (url, options, maxRetries = 5, delay = 1000) => {
    for (let i = 0; i < maxRetries; i++) {
        try {
            const response = await fetch(url, options);
            if (response.ok) return await response.json();
            else if (response.status === 429 && i < maxRetries - 1) {
                await new Promise(r => setTimeout(r, delay * (2 ** i)));
            } else {
                const errBody = await response.json().catch(() => ({}));
                throw new Error(`API Error ${response.status}: ${errBody.error?.message || response.statusText}`);
            }
        } catch (err) {
            if (i === maxRetries -1) throw err;
            await new Promise(r => setTimeout(r, delay * (2 ** i)));
        }
    }
};

analyzeBtn?.addEventListener("click", async () => {
    aiOutput.innerHTML = '<div class="text-gray-500">Memproses analisa AI...</div>';
    aiResultDiv.classList.remove("hidden");
    analyzeBtn.disabled = true;

    try {
        const payload = {
            contents: [{ parts: [{ text: promptText }] }],
            systemInstruction: { parts: [{ text: systemInstruction }] }
        };
        const data = await fetchWithRetry(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        let aiText = "❌ AI tidak dapat menghasilkan respons yang valid. Silakan coba lagi.";
        const candidate = data.candidates?.[0];
        if (candidate?.content?.parts?.[0]?.text) aiText = candidate.content.parts[0].text;
        else if (data.error) aiText = `<p class="text-red-600 font-semibold">❌ Kesalahan API: ${data.error.message}</p>`;

        aiOutput.innerHTML = markdownToHtml(aiText);

    } catch (err) {
        aiOutput.innerHTML = `<p class="text-red-600 font-semibold">❌ Terjadi kesalahan saat menganalisis: ${err.message}</p>`;
    } finally {
        analyzeBtn.disabled = false;
    }
});

function markdownToHtml(markdown) {
    let html = markdown.replace(/^###\s*(.*)$/gm, '<h3>$1</h3>')
                       .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
    html = html.split('\n\n').map(p => {
        if (p.startsWith('<h3')) return p;
        if (/^(\* |\d+\. )/.test(p)) {
            let lines = p.split('\n').map(line => line.replace(/^(\* |\d+\. )/, '<li>') + '</li>').join('');
            return `<ul>${lines}</ul>`;
        }
        return `<p>${p}</p>`;
    }).join('');
    return html;
}
</script>
</body>
</html>
