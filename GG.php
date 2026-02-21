<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subuh: UKG Simulator Interaktif</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Load Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.3/css/all.min.css">
    <!-- Load SweetAlert2 and DOMPurify -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.3/dist/purify.min.js"></script>
    <style>
        :root {
            font-family: 'Inter', sans-serif;
        }
        /* Custom scrollbar for game text area */
        #gameText::-webkit-scrollbar {
            width: 8px;
            border-radius: 4px;
        }
        #gameText::-webkit-scrollbar-thumb {
            background-color: #6366f1; /* Indigo-500 */
            border-radius: 4px;
        }
        #gameText::-webkit-scrollbar-track {
            background-color: #eef2ff; /* Indigo-50 */
            border-radius: 4px;
        }
        /* Style for voice listening button */
        .voice-listening {
            animation: pulse-ring 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-ring {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); /* Indigo-500 with opacity */
            }
            50% {
                box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
            }
        }
        .choice-button {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        .choice-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-4 md:p-8">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <header class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-indigo-800 tracking-tight">
                <i class="fas fa-graduation-cap text-indigo-500 mr-2"></i> Subuh: UKG/PPPK Simulator
            </h1>
            <p class="text-gray-600 mt-1 italic">Uji Kompetensi Guru melalui Skenario Interaktif</p>
        </header>

        <!-- Main Grid Layout (Fixed to the correct 1:2 ratio on large screens) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kiri: Pengaturan & Kontrol (Settings & Controls) - Takes 1/3 width on large screens -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Box Pengaturan Data -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-yellow-100">
                    <h2 class="text-xl font-bold text-yellow-700 mb-4 flex items-center">
                        <i class="fas fa-user-tie mr-2"></i> Data Guru
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label for="inputNama" class="block text-xs font-medium text-gray-700 mb-1">Nama Guru (misal: Budi Santoso)</label>
                            <input type="text" id="inputNama" value="Nama Guru" class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label for="inputSekolah" class="block text-xs font-medium text-gray-700 mb-1">Nama Sekolah (misal: SMKN 1 Banjit)</label>
                            <input type="text" id="inputSekolah" value="SMKN 1 Banjit" class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label for="inputMapel" class="block text-xs font-medium text-gray-700 mb-1">Mata Pelajaran (misal: Kimia atau Otomatisasi Perkantoran)</label>
                            <input type="text" id="inputMapel" value="Mata Pelajaran" class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3 italic">Narasi skenario akan disesuaikan dengan data di atas.</p>
                </div>
                
                <!-- Box Pengaturan Game -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100">
                    <h2 class="text-xl font-bold text-indigo-700 mb-4 flex items-center">
                        <i class="fas fa-sliders-h mr-2"></i> Pengaturan Ujian
                    </h2>

                    <!-- STATIC DOMAIN SELECTION BLOCK -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Domain Kompetensi Ujian</label>
                        <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl">
                            <p class="text-sm font-medium text-indigo-800 flex items-start">
                                <i class="fas fa-layer-group text-lg mr-2 mt-0.5"></i> 
                                <span>
                                    **UKG Komprehensif (Terpadu):** Semua aspek (Pedagogik, Profesional, Manajerial, Sosial-Emosional, Dunia Kerja, Abad ke-21) diujikan dalam satu kasus terpadu.
                                </span>
                            </p>
                        </div>
                        <!-- HIDDEN INPUT TO STORE THE SELECTED DOMAIN PROMPT -->
                        <input type="hidden" id="temaInput" name="tema" value="">
                    </div>

                    <div class="mt-4 border-t pt-4">
                        <label for="levelSelect" class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kesulitan Skenario</label>
                        <select id="levelSelect" disabled class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                            <option value="Mudah">🟢 Mudah (Dasar)</option>
                            <option value="Sedang">🟡 Sedang (Kompleks)</option>
                            <option value="Sulit" selected>🔴 Sulit (Analisis Regulasi)</option>
                        </select>
                    </div>
                </div>

                <!-- Box Kontrol Game -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100">
                    <h2 class="text-xl font-bold text-indigo-700 mb-4 flex items-center">
                        <i class="fas fa-gamepad mr-2"></i> Kontrol Simulator
                    </h2>
                    <div class="grid grid-cols-2 gap-3">
                        <button id="startBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                            <i class="fas fa-play mr-2"></i> Mulai Ujian
                        </button>
                        <button id="resetBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                            <i class="fas fa-redo mr-2"></i> Reset
                        </button>
                        <button id="downloadBtn" class="bg-gray-500 hover:bg-gray-600 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                            <i class="fas fa-download mr-2"></i> Download Riwayat
                        </button>
                        <button id="analyzeBtn" disabled class="bg-purple-600 disabled:bg-purple-400 hover:bg-purple-700 text-white font-bold p-3 rounded-xl shadow-md transition duration-150" title="Analisis hanya tersedia setelah ujian selesai">
                            <i class="fas fa-chart-line mr-2"></i> Analisis AI
                        </button>
                    </div>
                </div>

                <!-- Box Audio/TTS -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100">
                    <h2 class="text-xl font-bold text-indigo-700 mb-4 flex items-center">
                        <i class="fas fa-volume-up mr-2"></i> Pengaturan Suara
                    </h2>

                    <div class="grid grid-cols-2 gap-3">
                        <button id="ttsToggleBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                            <i class="fas fa-volume-up mr-2"></i> TTS Off
                        </button>
                        <button id="ttsStopBtn" class="bg-orange-500 hover:bg-orange-600 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                            <i class="fas fa-stop mr-2"></i> Stop TTS
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kanan: Area Permainan (Game Area) - Takes 2/3 width on large screens -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Box Narasi Utama -->
                <div class="bg-white p-8 rounded-xl shadow-2xl border border-indigo-200 min-h-[300px] flex flex-col">
                    <h2 class="text-2xl font-extrabold text-indigo-900 mb-4 border-b pb-2">
                        <i class="fas fa-scroll mr-2"></i> Skenario Uji Kompetensi
                    </h2>
                    <div id="gameText" class="text-gray-800 flex-grow overflow-y-auto leading-relaxed max-h-[calc(100vh-450px)] sm:max-h-[calc(100vh-350px)]">
                        <!-- Initial content -->
                        <p class="text-lg italic text-gray-500">Selamat datang di Simulator UKG/PPPK. Lengkapi data guru di kiri, dan klik "Mulai Ujian" untuk memulai uji kasus kompetensi guru Anda.</p>
                    </div>
                </div>

                <!-- Box Pilihan / Jawaban -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100">
                    <h2 class="text-xl font-bold text-indigo-700 mb-4 flex items-center">
                        <i class="fas fa-question-circle mr-2"></i> Pilihan Respon Guru
                    </h2>

                    <!-- Tombol Pilihan (Choice Buttons) -->
                    <div id="choices" class="flex flex-col space-y-3">
                        <!-- Pilihan akan di-render di sini oleh JS -->
                        <p class="text-sm text-gray-500">Pilihan akan muncul di sini setelah ujian dimulai.</p>
                    </div>

                    <!-- Input Teks / Suara -->
                    <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-3 border-t pt-4">Atau Jawab Bebas (Simulasi Kasus Terbuka)</h3>
                    <form id="textAnswerForm" class="flex space-x-3">
                        <input type="text" id="textAnswerInput" placeholder="Ketik jawaban, solusi, atau pilihan Anda di sini..." class="flex-grow p-3 border border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" disabled>
                        <!-- ICON KIRIM -->
                        <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white p-3 rounded-xl font-bold shadow-md transition duration-150" disabled title="Kirim Jawaban Teks">
                            <i class="fas fa-paper-plane text-xl"></i>
                        </button>
                        <!-- ICON SUARA -->
                        <button type="button" id="voiceBtn" class="bg-cyan-500 hover:bg-cyan-600 text-white p-3 rounded-xl font-bold shadow-md transition duration-150" disabled title="Gunakan Input Suara (Indonesian)">
                            <i class="fas fa-microphone text-xl"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Area untuk menampilkan Analisis AI (Setelah diklik) -->
    <div id="analysisResult" class="max-w-7xl mx-auto mt-8 hidden">
        <div class="bg-white p-8 rounded-xl shadow-2xl border border-purple-300">
            <h2 class="text-3xl font-extrabold text-purple-800 mb-6 border-b-4 border-purple-500 pb-2 flex items-center">
                <i class="fas fa-brain mr-3"></i> Hasil Analisis AI Komprehensif
            </h2>
            <div id="analysisContent" class="space-y-6 text-gray-800">
                <!-- Konten analisis akan dimuat di sini -->
            </div>
            <button onclick="hideAnalysis()" class="mt-6 bg-gray-400 hover:bg-gray-500 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                <i class="fas fa-times mr-2"></i> Tutup Analisis
            </button>
        </div>
    </div>
    
    <!-- Hidden Form for Analysis (Required by original JS) -->
    <form id="analyzeForm" method="POST" action="#" class="hidden"> 
        <input type="hidden" id="gameHistoryInput" name="history">
    </form>

  <audio id="ttsAudio" src="" preload="auto"></audio>
  <audio id="bgMusic" src="MIG.mp3" loop></audio>



<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.3/dist/purify.min.js"></script>
<script>
  // --- NEW VARIABLES FOR PERSONALIZED PROMPT ---
  const inputSekolah = document.getElementById("inputSekolah");
  const inputNama = document.getElementById("inputNama");
  const inputMapel = document.getElementById("inputMapel");
  const temaInputHidden = document.getElementById("temaInput");
  // --- END NEW VARIABLES ---

  const analyzeBtn = document.getElementById("analyzeBtn");
  const analysisResultDiv = document.getElementById("analysisResult");
  const analysisContentDiv = document.getElementById("analysisContent");
  const gameHistoryInput = document.getElementById("gameHistoryInput");
  const gameText = document.getElementById("gameText");
  const choicesDiv = document.getElementById("choices");
  // Removed domainButtonsDiv since it's now static
  const levelSelect = document.getElementById("levelSelect");
  const startBtn = document.getElementById("startBtn");
  const resetBtn = document.getElementById("resetBtn");
  const ttsToggleBtn = document.getElementById("ttsToggleBtn");
  const ttsStopBtn = document.getElementById("ttsStopBtn");
  const downloadBtn = document.getElementById("downloadBtn");
  const textAnswerForm = document.getElementById("textAnswerForm");
  const textAnswerInput = document.getElementById("textAnswerInput");
  const voiceBtn = document.getElementById("voiceBtn");
  const bgMusic = document.getElementById("bgMusic");

  const MAX_TURNS = 20;

  let history = JSON.parse(localStorage.getItem("gameHistory")) || [];
  let currentNarasi = "";
  let ttsEnabled = false;
  let utterance = null;
  let recognition = null;
  let recognizing = false;
  
  // FIXED: Single, combined domain prompt as requested
  const selectedDomainPrompt = "UKG Komprehensif (Terpadu): Skenario kasus akan memadukan semua aspek kompetensi guru: Pedagogik, Profesional, Manajerial, Sosial-Emosional, Dunia Kerja, Abad ke-21, Kolaboratif, dan Praktis Kejuruan.";

  // --- FUNGSI BARU UNTUK MENGHASILKAN ANALISIS AI ---
  async function generateAnalysis() {
    if (!history || history.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Oops!",
        text: "Tidak ada riwayat permainan untuk dianalisis.",
      });
      return;
    }

    // Tampilkan loading screen
    Swal.fire({
      title: 'Menganalisis Kompetensi...',
      html: 'AI sedang mengevaluasi <b>seluruh riwayat</b> respon Anda berdasarkan kriteria guru SMK yang kompeten. Mohon tunggu.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const sekolah = inputSekolah.value.trim() || "sekolah";
    const nama = inputNama.value.trim() || "Guru";
    const mapel = inputMapel.value.trim() || "Mata Pelajaran";
    const identitasGuru = `Bapak/Ibu ${nama} dari ${sekolah}, guru mata pelajaran ${mapel}`;

    const analysisPrompt = `
Anda adalah **Analisis UKG berbasis AI**, yang bertugas mengevaluasi riwayat permainan guru ${identitasGuru} dalam Simulator Uji Kompetensi. Fokus analisis Anda harus pada **Kompetensi Guru SMK**.

**TUGAS:**
1.  Evaluasi setiap langkah respon guru.
2.  Berikan penilaian terstruktur berdasarkan 3 kriteria utama.

**KEMBALIKAN HASIL DALAM FORMAT JSON BERIKUT:**
{
  "judul": "Judul Analisis (misal: Evaluasi Kompetensi Guru Kejuruan)",
  "nilai_kompetensi": "Skor keseluruhan (misal: 85/100, atau Grade B)",
  "feedback": "Umpan Balik Kritis dan Terstruktur (Minimal 3 Poin). Fokus pada Kaitan Praktik Nyata, Regulasi, dan Pemecahan Masalah.",
  "saran_peningkatan": "Saran Spesifik untuk Pengembangan Diri (Minimal 3 Poin). Fokus pada Aspek Dunia Kerja (DUDI), Kompetensi Abad ke-21, dan Inovasi Pembelajaran.",
  "prediksi_kelayakan_smk": "Prediksi Kelayakan sebagai Guru SMK Kompeten (Tuliskan dalam 3-5 kalimat naratif, dan berikan predikat: **Sangat Layak / Layak dengan Catatan / Perlu Peningkatan Signifikan**)."
}

---
**Riwayat Lengkap Permainan untuk Analisis:**
${JSON.stringify(history, null, 2)}
---
`;

    const apiKey = ""; 
    const model = "gemini-2.5-flash-preview-09-2025";
    const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;

    const payload = {
        contents: [{ parts: [{ text: analysisPrompt }] }],
        systemInstruction: {
            parts: [{ text: "Anda adalah Analisis UKG berbasis AI. Berikan output Anda HANYA dalam format JSON. Gunakan format Markdown bold (**teks**) untuk penekanan dalam setiap nilai kunci (feedback, saran_peningkatan, prediksi_kelayakan_smk)." }]
        },
        generationConfig: {
            responseMimeType: "application/json",
            responseSchema: {
                type: "OBJECT",
                properties: {
                    judul: { type: "STRING" },
                    nilai_kompetensi: { type: "STRING" },
                    feedback: { type: "STRING" },
                    saran_peningkatan: { type: "STRING" },
                    prediksi_kelayakan_smk: { type: "STRING" }
                },
                required: ["judul", "nilai_kompetensi", "feedback", "saran_peningkatan", "prediksi_kelayakan_smk"]
            }
        }
    };

    try {
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      const aiText = data.candidates?.[0]?.content?.parts?.[0]?.text;

      if (!aiText) {
        throw new Error("Respons AI kosong.");
      }
      
      let parsedAnalysis;
      try {
        const cleanText = aiText
            .trim()
            .replace(/^```json/, '')
            .replace(/^```/, '')
            .replace(/```$/, '')
            .trim();
        parsedAnalysis = JSON.parse(cleanText);
      } catch (e) {
        console.error("JSON parse error:", e);
        throw new Error("Gagal memproses JSON Analisis.");
      }

      Swal.close(); // Tutup loading screen
      displayAnalysis(parsedAnalysis);

    } catch (error) {
      console.error("Fetch error during analysis:", error);
      Swal.fire({
        icon: "error",
        title: "Analisis Gagal",
        text: `Terjadi kesalahan saat mendapatkan analisis AI: ${error.message}.`,
      });
    }
  }

  // Fungsi untuk menampilkan hasil analisis ke UI
  function displayAnalysis(analysisData) {
      // Fungsi untuk mengonversi Markdown bold ke HTML bold
      const markdownToHtml = (text) => text ? text.replace(/\*\*(.+?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>') : '';

      analysisContentDiv.innerHTML = `
          <h3 class="text-2xl font-bold text-gray-700">${DOMPurify.sanitize(markdownToHtml(analysisData.judul))}</h3>
          <p class="text-xl font-extrabold text-purple-600">Nilai Kompetensi Total: ${DOMPurify.sanitize(analysisData.nilai_kompetensi)}</p>
          
          <div class="space-y-4">
              <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                  <h4 class="text-xl font-semibold text-purple-700 mb-2 flex items-center"><i class="fas fa-comment-dots mr-2"></i> Umpan Balik Kritis (Feedback)</h4>
                  <p class="text-sm">${DOMPurify.sanitize(markdownToHtml(analysisData.feedback))}</p>
              </div>

              <div class="p-4 bg-purple-100 rounded-lg border border-purple-300">
                  <h4 class="text-xl font-semibold text-purple-700 mb-2 flex items-center"><i class="fas fa-chart-line mr-2"></i> Saran Peningkatan Kompetensi</h4>
                  <p class="text-sm">${DOMPurify.sanitize(markdownToHtml(analysisData.saran_peningkatan))}</p>
              </div>

              <div class="p-4 bg-purple-200 rounded-lg border border-purple-400">
                  <h4 class="text-xl font-semibold text-purple-700 mb-2 flex items-center"><i class="fas fa-check-circle mr-2"></i> Prediksi Kelayakan Guru SMK</h4>
                  <p class="text-sm">${DOMPurify.sanitize(markdownToHtml(analysisData.prediksi_kelayakan_smk))}</p>
              </div>
          </div>
      `;
      analysisResultDiv.classList.remove('hidden');
      window.scrollTo(0, document.body.scrollHeight); // Scroll ke hasil analisis
  }

  function hideAnalysis() {
      analysisResultDiv.classList.add('hidden');
      analysisContentDiv.innerHTML = '';
      window.scrollTo(0, 0);
  }

  analyzeBtn.onclick = generateAnalysis; // Panggil fungsi analisis baru

  // --- AKHIR FUNGSI BARU UNTUK MENGHASILKAN ANALISIS AI ---

 
 function drawText(text, isHtml = false) {
  // Set the content (sanitized if HTML)
  if (isHtml) {
    gameText.innerHTML = DOMPurify.sanitize(text);
  } else {
    gameText.textContent = text;
  }

  // If an input or textarea is focused, blur it to hide the keyboard
  const activeEl = document.activeElement;
  if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA')) {
    activeEl.blur();
  }

  // Scroll to the bottom of the scrollable gameText container
  gameText.scrollTop = gameText.scrollHeight;
}


  function showChoices(options) {
    choicesDiv.innerHTML = "";
    options.forEach(opt => {
      const btn = document.createElement("button");
      btn.className = "choice-button bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-md transition-shadow shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer";
      btn.textContent = opt;
      btn.onclick = () => makeChoice(opt);
      choicesDiv.appendChild(btn);
    });
  }

  function saveHistory() {
    localStorage.setItem("gameHistory", JSON.stringify(history));
  }

  function downloadHistory() {
    const textData = history.map((entry, idx) => {
      return `Langkah ${idx + 1}:\nNarasi:\n${entry.narasi}\nPilihan: ${entry.pilihan}\n`;
    }).join("\n----------------------\n\n");

    const dataStr = "data:text/plain;charset=utf-8," + encodeURIComponent(textData);
    const dlAnchor = document.createElement('a');
    dlAnchor.setAttribute("href", dataStr);
    dlAnchor.setAttribute("download", "ukg_simulator_history.txt");
    document.body.appendChild(dlAnchor);
    dlAnchor.click();
    dlAnchor.remove();
  }

  function speakText(text) {
    if (!ttsEnabled || !('speechSynthesis' in window) || text === "⏳ Menunggu jawaban dari Game Master...") return;

    if (utterance) speechSynthesis.cancel();

    utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'id-ID';
    utterance.pitch = 1.0;
    utterance.rate = 0.95;

    const setVoiceAndSpeak = () => {
      const voices = speechSynthesis.getVoices();
      const indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google")) ||
                        voices.find(v => v.lang === 'id-ID');
      if (indoVoice) utterance.voice = indoVoice;
      speechSynthesis.speak(utterance);
    };

    if (speechSynthesis.getVoices().length === 0) {
      speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
    } else {
      setVoiceAndSpeak();
    }
  }

function buatPrompt(history, pilihan, isForcedEnd = false) {
  // Ambil data personalisasi dari input
  const sekolah = inputSekolah.value.trim() || "sekolah";
  const nama = inputNama.value.trim() || "Guru";
  const mapel = inputMapel.value.trim() || "Mata Pelajaran";

  const identitasGuru = `Bapak/Ibu ${nama} dari ${sekolah}, guru mata pelajaran ${mapel}`;

  // Mengambil nilai prompt penuh (sudah terpadu/komprehensif)
  const tema = selectedDomainPrompt; 
  const level = levelSelect.value;
  const finalChoiceText = history.length > 0 ? history[history.length - 1].pilihan : pilihan;

  let prompt = `
Kamu adalah Subuh, Game Master dalam permainan edukatif berbasis teks interaktif, yang berperan sebagai **Simulator Ujian Kompetensi Guru (UKG) atau PPPK**.

Tujuan utama permainan ini adalah menguji dan mengembangkan kompetensi guru (Pedagogik, Profesional, Manajerial, Sosial-Emosional, Kejuruan, Dunia Kerja, Kolaboratif, Abad ke-21) melalui skenario kasus.

⚠️ TOLAK permainan dengan sopan namun tegas jika tema yang dipilih tidak sesuai norma pendidikan.

---

👤 KONTEKS GURU: ${identitasGuru}
Gunakan informasi ini untuk membuat skenario yang relevan.

🎮 Domain Kompetensi Ujian: ${tema}  
🎯 Tingkat Kesulitan Skenario: ${level}

---

🧩 TUGASMU:
1. Lanjutkan cerita berdasarkan riwayat sebelumnya dan pilihan terakhir pemain.
2. **HARUS** menghasilkan skenario kasus yang lazim muncul dalam materi UKG/PPPK.
3. Bangun narasi yang sesuai dengan tingkat kesulitan, pastikan ${identitasGuru} menjadi subjek utama kasus tersebut.

4. Jika permainan BELUM selesai:
   - Tampilkan masalah atau situasi kasus baru.
   - Berikan 2–3 pilihan respons yang menguji kompetensi.
   - Pastikan narasi awal memberikan konteks kasus yang jelas.

5. Jika permainan SELESAI (baik secara alami atau **dipaksa berakhir setelah ${MAX_TURNS} langkah**):
   - Tampilkan narasi penutup yang memuaskan.
   - Berikan ringkasan (review) singkat tentang perjalanan dan kekuatan/kelemahan pilihan pemain dalam konteks UKG.
   - Tambahkan simpulan edukatif yang mengaitkan kasus dengan standar kompetensi guru.

---

📦 KEMBALIKAN HASIL DALAM FORMAT JSON SEPERTI INI:

{
  "narasi": "Narasi lanjutan atau akhir...",
  "opsi": ["Opsi A", "Opsi B", "Opsi C"],
  "selesai": false,
  "review": "Ringkasan perjalanan pemain (hanya saat selesai)",
  "simpulan": "Pelajaran edukatif dari cerita (hanya saat selesai)"
}

---

📚 Riwayat Pemain (untuk konteks kelanjutan):
${JSON.stringify(history, null, 2)}

🧭 Pilihan Terakhir:
"${finalChoiceText}"

---
`;
    return prompt;
}


  async function makeChoice(pilihan) {
    let promptContent = "";
    analyzeBtn.disabled = true; // Nonaktifkan Analyze saat permainan berjalan

    
    if (pilihan === "__start__") {
      history = [];
      promptContent = buatPrompt(history, pilihan);
    } else if (history.length >= MAX_TURNS) {
      // Force end logic
      history.push({ narasi: currentNarasi, pilihan });
      saveHistory(); 
      
      const finalChoiceText = history[history.length - 1].pilihan;
      promptContent = buatPrompt(history, finalChoiceText, true);

    } else {
      history.push({ narasi: currentNarasi, pilihan });
      saveHistory();
      promptContent = buatPrompt(history, pilihan);
    }

    drawText("⏳ Menunggu jawaban dari Simulator UKG...");
    choicesDiv.innerHTML = "";
    textAnswerInput.value = "";
    textAnswerInput.disabled = true;
    textAnswerForm.querySelector("button[type='submit']").disabled = true;
    voiceBtn.disabled = true;
    analysisResultDiv.classList.add('hidden'); // Sembunyikan hasil analisis

    // --- FIX: Direct Gemini API Call Setup ---
    const apiKey = ""; 
    const model = "gemini-2.5-flash-preview-09-2025";
    const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;

    const payload = {
        contents: [{ parts: [{ text: promptContent }] }],
        systemInstruction: {
            parts: [{ text: "Anda adalah Subuh, Game Master untuk UKG. Berikan output Anda HANYA dalam format JSON. Gunakan format Markdown bold (**teks**) untuk penekanan dalam 'narasi', 'review', dan 'simpulan'." }]
        },
        generationConfig: {
            responseMimeType: "application/json",
            responseSchema: {
                type: "OBJECT",
                properties: {
                    narasi: { type: "STRING", description: "Narasi lanjutan atau akhir dari skenario kasus UKG." },
                    opsi: { type: "ARRAY", items: { type: "STRING" }, description: "Daftar 2-3 pilihan respons guru." },
                    selesai: { type: "BOOLEAN", description: "Apakah ujian kasus sudah selesai?" },
                    review: { type: "STRING", description: "Ringkasan perjalanan pemain (hanya saat selesai)." },
                    simpulan: { type: "STRING", description: "Pelajaran edukatif dari cerita (hanya saat selesai)." }
                },
                required: ["narasi", "opsi", "selesai"]
            }
        }
    };
    // --- END: Direct Gemini API Call Setup ---

    try {
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        const errorText = await response.text();
        console.error("HTTP error:", response.status, errorText);
        drawText(`❌ Terjadi kesalahan saat mendapatkan respons AI. HTTP ${response.status}`);
        choicesDiv.innerHTML = `<button onclick="startGame()" class="choice-button bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-md transition-shadow shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 cursor-pointer">🔁 Coba Lagi</button>`;
        return;
      }

      const data = await response.json();
      const aiText = data.candidates?.[0]?.content?.parts?.[0]?.text;

      if (!aiText) {
        drawText("❌ Respons AI tidak valid atau kosong.");
        choicesDiv.innerHTML = `<button onclick="startGame()" class="choice-button bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-md transition-shadow shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 cursor-pointer">🔁 Coba Lagi</button>`;
        return;
      }
      
      let parsed;
      try {
        const cleanText = aiText
            .trim()
            .replace(/^```json/, '')
            .replace(/^```/, '')
            .replace(/```$/, '')
            .trim();
        parsed = JSON.parse(cleanText);
      } catch (e) {
        console.error("JSON parse error:", e, aiText);
        drawText("❌ Gagal memproses data AI. Model mungkin tidak mengembalikan JSON yang valid.");
        choicesDiv.innerHTML = `<button onclick="startGame()" class="choice-button bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-md transition-shadow shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 cursor-pointer">🔁 Coba Lagi</button>`;
        return;
      }

      // Fungsi untuk mengonversi Markdown bold ke HTML bold
      const markdownToHtml = (text) => text ? text.replace(/\*\*(.+?)\*\*/g, '<b>$1</b>') : '';
      
      currentNarasi = parsed.narasi || "";
      drawText(markdownToHtml(currentNarasi), true); 
      speakText(currentNarasi); 

      // Cek apakah permainan harus berakhir karena batasan langkah
      const isFinished = parsed.selesai || history.length >= MAX_TURNS;

      if (isFinished) {
        analyzeBtn.disabled = false; // Aktifkan tombol analisis

        choicesDiv.innerHTML = "<strong class='text-indigo-800 text-lg'>🎉 Ujian Selesai!</strong>";
        if (parsed.review) {
          const reviewDiv = document.createElement("div");
          reviewDiv.className = "mt-4 p-3 bg-indigo-100 rounded text-indigo-900";
          reviewDiv.innerHTML = "📝 **Review UKG:** " + DOMPurify.sanitize(markdownToHtml(parsed.review));
          choicesDiv.appendChild(reviewDiv);
        }
        if (parsed.simpulan) {
          const simpulanDiv = document.createElement("div");
          simpulanDiv.className = "mt-2 p-3 bg-indigo-200 rounded text-indigo-900 font-semibold";
          simpulanDiv.innerHTML = "💡 **Kaitan Kompetensi:** " + DOMPurify.sanitize(markdownToHtml(parsed.simpulan));
          choicesDiv.appendChild(simpulanDiv);
        }
        // Pastikan input dimatikan setelah selesai
        textAnswerInput.disabled = true;
        textAnswerForm.querySelector("button[type='submit']").disabled = true;
        voiceBtn.disabled = true;

      } else {
        analyzeBtn.disabled = true; // Nonaktifkan tombol analisis
        
        showChoices(parsed.opsi || []);
        textAnswerInput.disabled = false;
        textAnswerForm.querySelector("button[type='submit']").disabled = false;
        voiceBtn.disabled = false;
        textAnswerInput.focus();
      }

    } catch (error) {
      console.error("Fetch error:", error);
      drawText("❌ Terjadi kesalahan saat mendapatkan respons AI. Cek koneksi atau coba lagi.");
      choicesDiv.innerHTML = `<button onclick="startGame()" class="choice-button bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-md transition-shadow shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 cursor-pointer">🔁 Coba Lagi</button>`;
    }
  }

  function startGame() {
    // Validasi data guru
    if (!inputNama.value.trim() || !inputSekolah.value.trim() || !inputMapel.value.trim()) {
         Swal.fire({
            icon: "warning",
            title: "Data Guru Belum Lengkap",
            text: "Mohon lengkapi Nama Guru, Sekolah, dan Mata Pelajaran sebelum memulai ujian.",
            confirmButtonText: "OK"
        });
        return;
    }
    
    // Set the hidden input value for the combined domain
    temaInputHidden.value = selectedDomainPrompt;

    if (bgMusic) {
      bgMusic.volume = 0.2;
      bgMusic.play().catch(e => {
        console.warn("Autoplay gagal:", e);
      });
    }
    analyzeBtn.disabled = true; // Pastikan nonaktif saat mulai
    analysisResultDiv.classList.add('hidden'); // Sembunyikan hasil analisis
    makeChoice("__start__");
  }

  function resetGame() {
    speechSynthesis.cancel();
    utterance = null;

    if (bgMusic) {
      bgMusic.pause();
      bgMusic.currentTime = 0;
    }

    history = [];
    saveHistory();
    currentNarasi = "";
    analyzeBtn.disabled = true; // WAJIB: Nonaktifkan tombol analisis
    analysisResultDiv.classList.add('hidden'); // Sembunyikan hasil analisis
    
    // Set the hidden input value for the combined domain
    temaInputHidden.value = selectedDomainPrompt; 

    // Reset pesan awal
    drawText('<p class="text-lg italic text-gray-500">Selamat datang di Simulator UKG/PPPK. Lengkapi data guru di kiri, dan klik "Mulai Ujian" untuk memulai uji kasus kompetensi guru Anda.</p>', true);
    choicesDiv.innerHTML = "<p class='text-sm text-gray-500'>Pilihan akan muncul di sini setelah ujian dimulai.</p>";
    textAnswerInput.value = "";
    textAnswerInput.disabled = true;
    textAnswerForm.querySelector("button[type='submit']").disabled = true;
    voiceBtn.disabled = true;
  }

  const stopTTS = () => {
    ttsAudio.pause();
    ttsAudio.removeAttribute('src');
    isTTSSpeaking = false;
  }

  textAnswerForm.addEventListener("submit", e => {
    e.preventDefault();
    if (!textAnswerInput.value.trim()) return;
    makeChoice(textAnswerInput.value.trim());
  });

  startBtn.onclick = startGame;
  resetBtn.onclick = resetGame;
  downloadBtn.onclick = downloadHistory;

  ttsToggleBtn.onclick = () => {
    ttsEnabled = !ttsEnabled;
    ttsToggleBtn.textContent = ttsEnabled ? "🔈 TTS On" : "🔈 TTS Off";
    if (!ttsEnabled && utterance) speechSynthesis.cancel();
  };

  ttsStopBtn.onclick = () => {
    if (utterance) {
      speechSynthesis.cancel();
      utterance = null;
    }
  };

  window.addEventListener("beforeunload", () => {
    if (speechSynthesis.speaking || speechSynthesis.pending) {
      speechSynthesis.cancel();
    }
  });

// Voice Recognition Setup
if ("webkitSpeechRecognition" in window || "SpeechRecognition" in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.lang = "id-ID";
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = () => {
        recognizing = true;
        voiceBtn.innerHTML = "<i class='fas fa-microphone text-xl'></i>"; // Icon
        voiceBtn.classList.add('voice-listening');
        voiceBtn.disabled = true;
    };

    recognition.onresult = (event) => {
      recognizing = false;
        voiceBtn.innerHTML = "<i class='fas fa-microphone text-xl'></i>"; // Icon
        voiceBtn.classList.remove('voice-listening');
        voiceBtn.disabled = false;
      const speechResult = event.results[0][0].transcript;
      textAnswerInput.value = speechResult;
      makeChoice(speechResult.trim());
    };

    recognition.onerror = (event) => {
      recognizing = false;
      voiceBtn.innerHTML = "<i class='fas fa-microphone text-xl'></i>"; // Icon
      voiceBtn.classList.remove('voice-listening');
      voiceBtn.disabled = false;
      Swal.fire({
          icon: "error",
          title: "Gagal Suara",
          text: "Gagal mengenali suara: " + event.error
      });
    };

    recognition.onend = () => {
      recognizing = false;
      voiceBtn.innerHTML = "<i class='fas fa-microphone text-xl'></i>"; // Icon
      voiceBtn.classList.remove('voice-listening');
      voiceBtn.disabled = false;
    };

    voiceBtn.onclick = () => {
      if (recognizing) {
        recognition.stop();
      } else {
        recognition.start();
      }
    };
  } else {
    voiceBtn.disabled = true;
    voiceBtn.title = "Browser tidak mendukung voice recognition";
    voiceBtn.classList.remove('bg-cyan-500', 'hover:bg-cyan-600');
    voiceBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
  }

// Initial setup on load
window.onload = () => {
    resetGame(); 
};

</script>
<script>
// Fungsi ini dipertahankan tetapi hanya untuk simulasi
const analyzeBtnElement = document.getElementById("analyzeBtn");

analyzeBtnElement.addEventListener("click", (e) => {
    e.preventDefault();
    // Panggil fungsi generateAnalysis yang sudah mengurus semuanya
    generateAnalysis();
});
</script>

</body>
</html>
