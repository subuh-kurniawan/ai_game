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
    <title>GuruMaster AI - Simulasi Pedagogik SMK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .chat-container::-webkit-scrollbar { width: 6px; }
        .chat-container::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
        .typing-indicator span {
            animation: blink 1.4s infinite both;
            height: 5px; width: 5px; background-color: #3b82f6;
            display: inline-block; border-radius: 50%; margin: 0 1px;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink { 0% { opacity: 0.2; } 20% { opacity: 1; } 100% { opacity: 0.2; } }
        
        .role-narration { border-left: 4px solid #94a3b8; padding-left: 1rem; font-style: italic; color: #475569; margin-bottom: 0.75rem; }
        .role-dialog { background: #f1f5f9; border-radius: 0.75rem; padding: 0.75rem; margin-bottom: 0.75rem; border-left: 4px solid #6366f1; }
        .role-eval { background: #ecfdf5; border: 1px dashed #10b981; border-radius: 0.75rem; padding: 0.75rem; color: #065f46; font-size: 0.85rem; }

        .diff-btn.active { border-color: #4f46e5; background-color: #eef2ff; color: #4338ca; }
        
        @keyframes pulse-red {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        }
        .animate-pulse-red { animation: pulse-red 2s infinite; }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">

    <!-- Header -->
    <header class="bg-indigo-700 text-white p-4 shadow-lg flex justify-between items-center shrink-0">
        <div class="flex items-center gap-3">
            <i data-lucide="graduation-cap" class="w-8 h-8"></i>
            <div>
                <h1 class="font-bold text-lg leading-tight">GuruMaster AI</h1>
                <p class="text-xs text-indigo-200">Simulasi Game Master untuk Guru SMK</p>
            </div>
        </div>
        <div class="flex gap-4 text-sm font-medium items-center">
            <div id="turn-indicator" class="hidden items-center gap-1 bg-white/10 px-3 py-1 rounded-full text-[10px] border border-white/20 uppercase tracking-tighter">
                Sesi: <span id="current-turn-label">0</span>/<span id="max-turn-label">?</span>
            </div>
            <div id="difficulty-badge" class="hidden items-center gap-1 bg-indigo-900/50 px-3 py-1 rounded-full text-[10px] border border-indigo-400/30 uppercase tracking-tighter">
                Level: <span id="current-diff-label">-</span>
            </div>
            <div class="flex items-center gap-1 bg-indigo-800 px-3 py-1 rounded-full">
                <i data-lucide="users" class="w-4 h-4 text-green-400"></i>
                <span id="stat-order">Ketertiban: 100%</span>
            </div>
            <div class="flex items-center gap-1 bg-indigo-800 px-3 py-1 rounded-full">
                <i data-lucide="brain" class="w-4 h-4 text-yellow-400"></i>
                <span id="stat-understanding">Pemahaman: 0%</span>
            </div>
            <button id="btn-finish-session" onclick="showReview()" class="hidden bg-red-500 hover:bg-red-600 px-3 py-1 rounded-full text-[10px] font-bold transition-all uppercase tracking-wider ml-2">
                Selesaikan Sesi
            </button>
        </div>
    </header>

    <main class="flex-1 flex overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-80 bg-white border-r border-slate-200 p-6 hidden lg:flex flex-col gap-6">
            <section>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Skenario Aktif</h3>
                <div id="scenario-card" class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <p class="font-bold text-indigo-700 mb-1" id="scenario-title">Pilih Skenario...</p>
                    <p class="text-xs text-slate-600 leading-relaxed" id="scenario-desc">Belum ada simulasi yang berjalan.</p>
                </div>
            </section>
            <section class="mt-auto">
                <div class="bg-indigo-50 p-4 rounded-xl">
                    <h4 class="text-xs font-bold text-indigo-800 mb-2">Aturan Batasan:</h4>
                    <p class="text-[11px] text-indigo-700 leading-relaxed" id="dynamic-rules-desc">Jumlah sesi akan ditentukan berdasarkan tingkat kesulitan yang Anda pilih.</p>
                </div>
            </section>
        </aside>

        <!-- Main Game Area -->
        <div class="flex-1 flex flex-col relative bg-slate-50">
            <!-- Game Start Overlay -->
            <div id="start-overlay" class="absolute inset-0 bg-white/95 backdrop-blur-sm z-50 flex items-center justify-center p-6 overflow-y-auto">
                <div class="max-w-md w-full my-auto text-center">
                    <div class="bg-indigo-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 text-white shadow-xl shadow-indigo-200">
                        <i data-lucide="settings-2" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold text-slate-800 mb-2">Konfigurasi Simulasi</h2>
                    <p class="text-slate-600 mb-8 italic text-sm">Pilih level tantangan untuk menentukan durasi sesi.</p>
                    
                    <div class="mb-8">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Pilih Tingkat Kesulitan</p>
                        <div class="flex flex-col gap-2">
                            <button onclick="setDifficulty('Mudah')" class="diff-btn active p-4 text-left border-2 rounded-2xl transition-all">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold">Mudah</span>
                                    <span class="text-[10px] bg-indigo-100 px-2 py-0.5 rounded text-indigo-600">10 SESI</span>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">Siswa kooperatif, banyak waktu untuk berdiskusi.</p>
                            </button>
                            <button onclick="setDifficulty('Sedang')" class="diff-btn p-4 text-left border-2 rounded-2xl transition-all">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold">Sedang</span>
                                    <span class="text-[10px] bg-slate-200 px-2 py-0.5 rounded text-slate-600">7 SESI</span>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">Dinamika kelas SMK yang realistis.</p>
                            </button>
                            <button onclick="setDifficulty('Sulit')" class="diff-btn p-4 text-left border-2 rounded-2xl transition-all">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold">Sulit</span>
                                    <span class="text-[10px] bg-red-100 px-2 py-0.5 rounded text-red-600">5 SESI</span>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">Siswa disruptif, waktu sangat terbatas!</p>
                            </button>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <button onclick="startGame('management')" class="flex items-center p-4 bg-white border-2 border-slate-200 rounded-2xl hover:border-indigo-500 hover:bg-indigo-50 transition-all text-left group">
                            <div class="bg-indigo-100 p-3 rounded-xl group-hover:bg-indigo-200 transition-colors"><i data-lucide="shield-alert" class="w-6 h-6 text-indigo-600"></i></div>
                            <div class="ml-4">
                                <p class="font-bold text-slate-800 text-sm">Manajemen Kelas Berat</p>
                                <p class="text-[11px] text-slate-500">Tantangan jam terakhir SMK.</p>
                            </div>
                        </button>
                        <button onclick="startGame('practical')" class="flex items-center p-4 bg-white border-2 border-slate-200 rounded-2xl hover:border-emerald-500 hover:bg-emerald-50 transition-all text-left group">
                            <div class="bg-emerald-100 p-3 rounded-xl group-hover:bg-emerald-200 transition-colors"><i data-lucide="cpu" class="w-6 h-6 text-emerald-600"></i></div>
                            <div class="ml-4">
                                <p class="font-bold text-slate-800 text-sm">Simulasi Lab Praktik</p>
                                <p class="text-[11px] text-slate-500">Menangani kendala teknis di lab.</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Review Overlay -->
            <div id="review-overlay" class="absolute inset-0 bg-slate-900/90 backdrop-blur-md z-[60] hidden flex items-center justify-center p-4 overflow-y-auto">
                <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="bg-indigo-700 p-6 text-white text-center shrink-0">
                        <i data-lucide="award" class="w-12 h-12 mx-auto mb-3 text-yellow-400"></i>
                        <h2 class="text-2xl font-bold">Hasil Evaluasi Mengajar</h2>
                        <p class="text-indigo-200 text-sm">Review performa Anda pada level <span id="review-diff-label" class="uppercase"></span>.</p>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 text-center">
                                <p class="text-xs text-slate-500 uppercase font-bold mb-1">Ketertiban Akhir</p>
                                <p id="review-stat-order" class="text-3xl font-black text-indigo-600">100%</p>
                            </div>
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 text-center">
                                <p class="text-xs text-slate-500 uppercase font-bold mb-1">Pemahaman Akhir</p>
                                <p id="review-stat-under" class="text-3xl font-black text-emerald-600">0%</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <i data-lucide="scroll-text" class="w-4 h-4"></i> Riwayat Interaksi (<span id="review-turn-count">0</span> Sesi)
                                </h3>
                                <button onclick="downloadReport()" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg transition-all shadow-sm">
                                    <i data-lucide="download" class="w-3 h-3"></i> Unduh Laporan (.txt)
                                </button>
                            </div>
                            <div id="review-history-list" class="space-y-4"></div>
                        </div>
                    </div>

                    <div class="p-6 bg-white border-t border-slate-100 flex gap-3 shrink-0">
                        <button onclick="location.reload()" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">Mulai Sesi Baru</button>
                        <button onclick="closeReview()" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all text-sm">Tutup Review</button>
                    </div>
                </div>
            </div>

            <!-- Chat Logs -->
            <div id="chat-logs" class="chat-container flex-1 overflow-y-auto p-4 md:p-6 space-y-6"></div>

            <!-- Typing Indicator -->
            <div id="typing-indicator" class="hidden px-6 py-2">
                <div class="bg-white rounded-lg p-3 inline-flex items-center gap-2 shadow-sm border border-slate-100">
                    <div class="typing-indicator"><span></span><span></span><span></span></div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Siswa sedang merespon</span>
                </div>
            </div>

            <!-- Input Area -->
            <div class="bg-white border-t border-slate-200 p-4 shrink-0">
                <div id="game-ended-notice" class="hidden text-center py-2 mb-2 text-xs font-bold text-red-600 animate-pulse uppercase tracking-widest">
                    Batas Sesi Tercapai. Silakan Klik Review Akhir.
                </div>
                <form id="game-form" onsubmit="handleUserAction(event)" class="max-w-4xl mx-auto flex gap-3">
                    <input type="text" id="user-input" placeholder="Ketik tindakan guru di sini..." 
                        class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition-all text-sm"
                        autocomplete="off">
                    <button type="submit" id="btn-send" class="bg-indigo-600 text-white p-3 rounded-xl hover:bg-indigo-700 transition-all disabled:opacity-50">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        const apiKey = "<?php echo $apiKey; ?>"; 
        const modelName = "<?php echo $model; ?>";
        let history = [];
        let stats = { order: 100, understanding: 0 };
        let currentScenario = null;
        let selectedDifficulty = "Mudah";
        
        let currentTurn = 0;
        let maxTurns = 7;
        let gameLogs = [];

        const getSystemPrompt = (scenarioType, difficulty, turn, max) => {
            const context = scenarioType === 'management' 
                ? "Kelas SMK jam terakhir, siswa lelah (Budi: main game, Rian: ngobrol, Sari: tidur)." 
                : "Lab Praktik Komputer. Ada kendala teknis dan siswa mulai frustrasi.";

            let difficultyInstructions = difficulty === "Mudah" ? "Siswa cenderung patuh." : (difficulty === "Sulit" ? "Siswa disruptif." : "Dinamika realistis.");

            const isLastTurn = turn >= max;

            return `Kamu adalah Game Master (GM) untuk simulasi guru SMK.
            SESI: ${turn}/${max}. LEVEL: ${difficulty}. ${difficultyInstructions}
            ${isLastTurn ? "BERIKAN EVALUASI AKHIR MENDALAM." : ""}

            Setiap respon HARUS menggunakan tag:
            [NARASI] ... deskripsi ... [/NARASI]
            [SISWA] ... dialog ... [/SISWA]
            [EVALUASI] ... tips pedagogik ... [/EVALUASI]
            
            DI AKHIR RESPON, tambahkan JSON:
            { "stats": { "order": 0-100, "understanding": 0-100 }, "is_over": ${isLastTurn} }`;
        };

        lucide.createIcons();

        function setDifficulty(diff) {
            selectedDifficulty = diff;
            document.querySelectorAll('.diff-btn').forEach(btn => {
                btn.classList.toggle('active', btn.querySelector('.font-bold').innerText === diff);
            });
        }

        async function startGame(type) {
            currentScenario = type;
            currentTurn = 0;
            gameLogs = [];
            history = [];
            
            if (selectedDifficulty === "Mudah") maxTurns = 10;
            else if (selectedDifficulty === "Sedang") maxTurns = 7;
            else if (selectedDifficulty === "Sulit") maxTurns = 5;

            document.getElementById('start-overlay').classList.add('hidden');
            document.getElementById('difficulty-badge').classList.replace('hidden', 'flex');
            document.getElementById('turn-indicator').classList.replace('hidden', 'flex');
            document.getElementById('btn-finish-session').classList.remove('hidden');
            document.getElementById('btn-finish-session').innerText = "Selesaikan Sesi";
            document.getElementById('btn-finish-session').classList.remove('animate-pulse-red');
            
            document.getElementById('current-diff-label').innerText = selectedDifficulty;
            document.getElementById('current-turn-label').innerText = currentTurn;
            document.getElementById('max-turn-label').innerText = maxTurns;
            document.getElementById('dynamic-rules-desc').innerHTML = `Tingkat <b>${selectedDifficulty}</b> aktif. Maksimal <b>${maxTurns} sesi</b>.`;

            const title = type === 'management' ? "Manajemen Kelas Berat" : "Simulasi Lab Praktik";
            document.getElementById('scenario-title').innerText = title;

            const initial = "[NARASI]Sesi dimulai. Anda berdiri di depan kelas. Suasana masih riuh rendah. Budi masih sibuk dengan gawainya.[/NARASI][SISWA]Rian: Bu/Pak, ini nanti kita praktiknya per kelompok apa sendiri?[/SISWA][EVALUASI]Tetapkan fokus kelas sebelum masuk ke materi.[/EVALUASI]";

            addMessage("system", initial);
        }

        async function handleUserAction(event) {
            event.preventDefault();
            if (currentTurn >= maxTurns) return;

            const inputEl = document.getElementById('user-input');
            const action = inputEl.value.trim();
            if (!action) return;

            inputEl.value = "";
            currentTurn++;
            document.getElementById('current-turn-label').innerText = currentTurn;
            
            addMessage("user", action);
            await processGameTurn(action);

            if (currentTurn >= maxTurns) endGameSequence();
        }

        function endGameSequence() {
            document.getElementById('user-input').disabled = true;
            document.getElementById('btn-send').disabled = true;
            document.getElementById('game-ended-notice').classList.remove('hidden');
            document.getElementById('btn-finish-session').classList.add('animate-pulse-red');
            document.getElementById('btn-finish-session').innerText = "Review Akhir";
        }

        function parseAIReponse(text) {
            const getTag = (tag, str) => {
                const regex = new RegExp(`\\[${tag}\\]([\\s\\S]*?)\\[\\/${tag}\\]`, 'i');
                const match = str.match(regex);
                return match ? match[1].trim() : null;
            };
            return {
                narration: getTag('NARASI', text),
                dialog: getTag('SISWA', text),
                eval: getTag('EVALUASI', text)
            };
        }

        function addMessage(role, rawText) {
            const container = document.getElementById('chat-logs');
            const div = document.createElement('div');
            const parsed = parseAIReponse(rawText);
            
            if (role === 'user') {
                div.className = "flex justify-end";
                div.innerHTML = `<div class="bg-indigo-600 text-white p-4 rounded-2xl rounded-tr-none max-w-[85%] shadow-lg">
                                    <p class="text-[10px] font-bold mb-1 opacity-70 uppercase tracking-widest text-right">Anda (Guru)</p>
                                    <p class="text-sm leading-relaxed">${rawText}</p>
                                </div>`;
            } else {
                let contentHTML = "";
                if (parsed.narration) contentHTML += `<div class="role-narration text-sm">${parsed.narration}</div>`;
                if (parsed.dialog) contentHTML += `<div class="role-dialog text-sm font-medium">${parsed.dialog.replace(/\n/g, '<br>')}</div>`;
                if (parsed.eval) contentHTML += `<div class="role-eval"><div class="flex items-center gap-2 mb-1 font-bold"><i data-lucide="lightbulb" class="w-3 h-3"></i> Tips Pedagogik</div>${parsed.eval}</div>`;
                if (!contentHTML) contentHTML = `<div class="text-sm">${rawText.replace(/\{.*\}/s, "")}</div>`;

                div.className = "flex justify-start";
                div.innerHTML = `<div class="bg-white border border-slate-200 p-5 rounded-2xl rounded-tl-none max-w-[90%] shadow-sm">
                                    <p class="text-[10px] font-bold mb-3 text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></span> Game Master
                                    </p>
                                    <div class="space-y-2">${contentHTML}</div>
                                </div>`;

                gameLogs.push({
                    action: role === 'system' && history.length === 0 ? "Kondisi Awal" : (history.length > 0 ? history[history.length-1].parts[0].text : "Aksi"),
                    stats: { ...stats },
                    feedback: parsed.eval || "Observasi berlanjut.",
                    narration: parsed.narration || "",
                    dialog: parsed.dialog || ""
                });
            }

            container.appendChild(div);
            lucide.createIcons();
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }

        async function processGameTurn(userInput) {
            const typingIndicator = document.getElementById('typing-indicator');
            const btnSend = document.getElementById('btn-send');
            typingIndicator.classList.remove('hidden');
            btnSend.disabled = true;

            try {
                const response = await fetchWithRetry(userInput);
                const rawText = response.candidates?.[0]?.content?.parts?.[0]?.text || "";
                const jsonMatch = rawText.match(/\{.*\}/s);
                if (jsonMatch) {
                    try {
                        const data = JSON.parse(jsonMatch[0]);
                        updateUIStats(data.stats);
                    } catch(e) {}
                }
                addMessage("system", rawText);
                history.push({ role: "user", parts: [{ text: userInput }] }, { role: "model", parts: [{ text: rawText }] });
            } catch (err) {
                console.error(err);
                addMessage("system", "[NARASI]Koneksi simulasi terputus.[/NARASI]");
            } finally {
                typingIndicator.classList.add('hidden');
                if (currentTurn < maxTurns) btnSend.disabled = false;
            }
        }

        function updateUIStats(newStats) {
            if (!newStats) return;
            stats.order = newStats.order;
            stats.understanding = newStats.understanding;
            document.getElementById('stat-order').innerText = `Ketertiban: ${stats.order}%`;
            document.getElementById('stat-understanding').innerText = `Pemahaman: ${stats.understanding}%`;
        }

        function showReview() {
            const historyList = document.getElementById('review-history-list');
            document.getElementById('review-stat-order').innerText = `${stats.order}%`;
            document.getElementById('review-stat-under').innerText = `${stats.understanding}%`;
            document.getElementById('review-turn-count').innerText = gameLogs.length - 1;
            document.getElementById('review-diff-label').innerText = selectedDifficulty;
            
            historyList.innerHTML = "";
            gameLogs.forEach((log, index) => {
                if (index === 0) return;
                const item = document.createElement('div');
                item.className = "bg-white p-4 rounded-xl border border-slate-100 shadow-sm relative overflow-hidden";
                item.innerHTML = `<div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
                    <div class="flex justify-between items-start mb-2"><span class="text-[10px] font-black text-slate-300 uppercase">Langkah #${index}</span></div>
                    <p class="text-xs font-bold text-slate-800 mb-2 italic">"${log.action}"</p>
                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100"><p class="text-[10px] text-slate-600 leading-tight">${log.feedback}</p></div>`;
                historyList.appendChild(item);
            });
            document.getElementById('review-overlay').classList.remove('hidden');
            lucide.createIcons();
        }

        function downloadReport() {
            const timestamp = new Date().toLocaleString('id-ID');
            let report = `========================================\n`;
            report += `    LAPORAN EVALUASI GURUMASTER AI    \n`;
            report += `========================================\n\n`;
            report += `Waktu Sesi      : ${timestamp}\n`;
            report += `Skenario        : ${document.getElementById('scenario-title').innerText}\n`;
            report += `Level Kesulitan : ${selectedDifficulty}\n`;
            report += `Total Langkah   : ${gameLogs.length - 1}\n\n`;
            report += `STATISTIK AKHIR:\n`;
            report += `----------------\n`;
            report += `Ketertiban Siswa: ${stats.order}%\n`;
            report += `Pemahaman Siswa : ${stats.understanding}%\n\n`;
            report += `RIWAYAT INTERAKSI:\n`;
            report += `------------------\n\n`;

            gameLogs.forEach((log, index) => {
                if (index === 0) {
                    report += `[KONDISI AWAL]\n${log.narration}\nSiswa: ${log.dialog}\nTips: ${log.feedback}\n\n`;
                } else {
                    report += `Langkah #${index}\n`;
                    report += `Tindakan Guru: "${log.action}"\n`;
                    report += `Respon Siswa : "${log.dialog}"\n`;
                    report += `Tips Master  : "${log.feedback}"\n`;
                    report += `Ketertiban   : ${log.stats.order}%\n`;
                    report += `------------------------------------\n\n`;
                }
            });

            report += `\nKESIMPULAN:\n`;
            report += `Simulasi selesai pada tingkat pemahaman ${stats.understanding}%.\n`;
            report += `GuruMaster AI menyarankan untuk terus melatih teknik ${selectedDifficulty === 'Sulit' ? 'manajemen krisis' : 'instruksional'} Anda.\n\n`;
            report += `(c) 2024 GuruMaster AI - Simulasi Pedagogik SMK`;

            const blob = new Blob([report], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Laporan_GuruMasterAI_${selectedDifficulty}_${new Date().getTime()}.txt`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }

        function closeReview() { document.getElementById('review-overlay').classList.add('hidden'); }

        async function fetchWithRetry(query, retries = 5, delay = 1000) {
            for (let i = 0; i < retries; i++) {
                try {
                    const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/${modelName}:generateContent?key=${apiKey}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            contents: [...history, { role: "user", parts: [{ text: query }] }],
                            systemInstruction: { parts: [{ text: getSystemPrompt(currentScenario, selectedDifficulty, currentTurn, maxTurns) }] }
                        })
                    });
                    if (!response.ok) throw new Error('API Error');
                    return await response.json();
                } catch (err) {
                    if (i === retries - 1) throw err;
                    await new Promise(res => setTimeout(res, delay * Math.pow(2, i)));
                }
            }
        }
    </script>
</body>
</html>