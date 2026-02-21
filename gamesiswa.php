<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cyber-Quest: Game Edukasi Teks | Leadership Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Use Inter for a cleaner, modern look, combined with Chivo Mono for terminal text -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Chivo+Mono:wght==400;700&display=swap" rel="stylesheet">
    
    <style>
        /* CSS Reset and Global Styles */
        :root {
            /* Light/Bright Palette */
            --color-primary: #1d4ed8; /* Blue-700 for accents */
            --color-secondary: #059669; /* Emerald-600 for success */
            --color-success: #059669; /* Emerald-600 */
            --color-warning: #d97706; /* Amber-700 */
            --color-text: #1f2937; /* Dark Gray/Black for readability on light background */
            --color-surface: rgba(255, 255, 255, 0.5); /* Light surface with transparency */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-attachment: fixed;
            
            /* ===== BRIGHT BACKGROUND IMAGE STYLE (Abstract, Minimalist) ===== */
            background-image: url('https://4kwallpapers.com/images/walls/thumbs_2t/1495.jpg'); 
            background-size: cover;
            background-position: center;
            background-color: #f3f4f6; /* Fallback light gray color */
            /* ==================================== */
            
            color: var(--color-text);
        }

        /* Glassmorphism/Acrylic Effect for Main Card (Light Tahoe Style) */
        .acrylic-card {
            background-color: rgba(255, 255, 255, 0.65); /* White with transparency */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.1); /* Dark, subtle border */
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
            transition: all 0.3s ease;
        }
        
        /* Softer button press effect */
        @keyframes buttonPress {
            0% { transform: scale(1); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); }
            100% { transform: scale(0.98); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); }
        }

        .btn-press:active {
            animation: buttonPress 0.1s ease-out forwards;
        }

        /* Clean Terminal/Game Text Area - Light Theme */
        #gameText {
            font-family: 'Chivo Mono', monospace;
            background-color: #f9fafb; /* Very light background */
            color: #1f2937; /* Dark text */
            border: 1px solid var(--color-primary);
            box-shadow: 0 0 5px rgba(29, 78, 216, 0.3); /* Muted blue glow */
        }
        
        /* Initial System Text */
        #gameText .system-ready {
            color: #6b7280; /* Muted gray for initial message */
        }

        /* Title style refinement (Adjust gradient for light contrast) */
        .title-gradient {
            background-image: linear-gradient(90deg, #1d4ed8, #059669, #9333ea); /* Blue, Green, Purple */
        }

        /* Custom scrollbar for game text area (Darker on light background) */
        #gameText::-webkit-scrollbar {
            width: 8px;
        }
        #gameText::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        #gameText::-webkit-scrollbar-thumb {
            background: var(--color-primary);
            border-radius: 10px;
        }
        
        /* Select/Input styling for light theme */
        select, input[type="text"] {
             background-color: rgba(255, 255, 255, 0.9);
             color: var(--color-text);
        }
        select:focus, input[type="text"]:focus {
             box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.2); /* Light blue ring */
        }
        
        /* Button text color adjustment for light theme */
        .bg-emerald-500, .bg-yellow-500 {
            color: white !important; /* Keep text white on bright colors */
        }
    </style>
</head>

<body class="min-h-screen flex flex-col items-center py-10 px-3">

    <!-- ===== HEADER (Sleek Title) ===== -->
    <header class="w-full max-w-5xl mb-8 text-center">
        <h1 class="text-6xl sm:text-7xl font-extrabold title-gradient text-transparent bg-clip-text animate-fade-in drop-shadow-lg mb-2 select-none" style="font-family: 'Inter', sans-serif; font-weight: 800;">
            // CYBER-QUEST //
        </h1>
        <p class="text-lg sm:text-xl text-blue-700 font-light tracking-wider animate-fade-in-slow px-2 italic">
            [ SMART LEADER SIMULATION ]
        </p>
    </header>

    <!-- ===== MAIN GAME SECTION (Acrylic Card/Window) ===== -->
    <section class="w-full max-w-5xl acrylic-card rounded-[30px] p-6 sm:p-10 flex flex-col gap-6">

        <!-- === Pilihan Tema & Level === -->
        <div class="flex flex-wrap items-center justify-center gap-4 p-4 bg-gray-100/70 rounded-2xl border border-gray-200">
            <label for="tema" class="text-gray-700 font-semibold text-lg whitespace-nowrap tracking-wide">
                ::: SCENARIO & DIFFICULTY :::
            </label>

            <select id="tema" class="flex-1 min-w-[150px] border border-blue-400 rounded-xl px-4 py-3 bg-white/80 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all cursor-pointer">
                <!-- Theme Options (Kepemimpinan Pembelajaran selected by default) -->
                <option value="Manajemen Waktu Efektif, Kecerdasan Emosional, Pengambilan Keputusan Pribadi, Mindfulness & Fokus, Kerja Sama Tim, Komunikasi Efektif, Empati & Kepedulian, Membangun Jejaring Sosial, Kepemimpinan Sekolah, Manajemen Proyek, Etika & Profesionalisme, Inovasi & Kreativitas Kerja untuk guru">UKOM GURU</option>
                <option value="kepemimpinan pembelajaran guru di kelas" selected>Kepemimpinan Pembelajaran</option>
                <option value="kecerdasan_emosional">Kecerdasan Emosional</option>
                <option value="pengambilan_keputusan">Pengambilan Keputusan Pribadi</option>
                <option value="mindfulness">Mindfulness & Fokus</option>
                <option value="kerja_sama_tim">Kerja Sama Tim</option>
                <option value="komunikasi_efektif">Komunikasi Efektif</option>
                <option value="empati">Empati & Kepedulian</option>
                <option value="jejaring_sosial">Membangun Jejaring Sosial</option>
                <option value="kepemimpinan_guru">Kepemimpinan Sekolah</option>
                <option value="manajemen_proyek">Manajemen Proyek</option>
                <option value="etika_profesional">Etika & Profesionalisme</option>
                <option value="inovasi_kreativitas">Inovasi & Kreativitas Kerja</option>
            </select>

            <select id="levelSelect" class="border border-green-400 rounded-xl px-4 py-3 bg-white/80 text-green-700 font-medium focus:outline-none focus:ring-4 focus:ring-green-300 transition-all cursor-pointer">
                <option value="mudah">LEVEL I (Easy)</option>
                <option value="sedang">LEVEL II (Medium)</option>
                <option value="sulit">LEVEL III (Hard)</option>
            </select>
        </div>

        <!-- === Custom Tema Input === -->
        <div class="flex flex-wrap justify-center gap-4">
            <input
                id="temaCustom"
                type="text"
                placeholder="INPUT custom scenario data..."
                class="flex-1 border border-purple-400 rounded-xl px-5 py-3 bg-white/80 text-gray-700 focus:outline-none focus:ring-4 focus:ring-purple-300 min-w-[200px] transition-all"
                autocomplete="off"
            />
            <button id="randomTemaBtn" class="bg-purple-600 hover:bg-purple-500 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                🎲 Random
            </button>
            <button id="addTemaBtn" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                ➕ ADD DATA
            </button>
        </div>


        <!-- === Kontrol Game (Interface Controls) === -->
        <div class="flex flex-wrap justify-center gap-4 pt-4 border-t border-gray-200">
            <button id="startBtn" class="flex-1 min-w-[140px] max-w-xs bg-blue-600 hover:bg-blue-500 text-white font-extrabold px-6 py-4 rounded-xl transition-all shadow-lg shadow-blue-500/30 hover:shadow-xl btn-press text-lg">
                ▶️ START SIM
            </button>
            <button id="resetBtn" class="flex-1 min-w-[140px] max-w-xs bg-red-600 hover:bg-red-500 text-white font-bold px-6 py-4 rounded-xl transition-all shadow-lg shadow-red-500/30 hover:shadow-xl btn-press">
                🔁 RESET
            </button>
            <button id="ttsToggleBtn" class="flex-1 min-w-[140px] max-w-xs bg-yellow-500 hover:bg-yellow-400 text-white font-bold px-6 py-4 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                🔈 TTS OFF
            </button>
            <!-- Tombol Stop Audio/TTS -->
            <button id="ttsStopBtn" class="flex-1 min-w-[140px] max-w-xs bg-orange-600 hover:bg-orange-500 text-white font-bold px-6 py-4 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                🛑 STOP
            </button>
            <button id="downloadBtn" class="flex-1 min-w-[140px] max-w-xs bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-4 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                💾 LOG
            </button>
            <button id="analyzeBtn" class="flex-1 min-w-[140px] max-w-xs bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-4 rounded-xl transition-all shadow-md hover:shadow-lg btn-press">
                🔍 ANALYZE
            </button>

            <form id="analyzeForm" action="analisa.php" method="POST" target="_blank" style="display:none;">
                <input type="hidden" name="gameHistory" id="gameHistoryInput">
                <input type="hidden" name="tema" id="temaInput">
            </form>
        </div>

        <!-- === Area Teks Narasi (Clean Console) === -->
        <div class="relative mt-4">
            <!-- Console Header Bar (Clean, Minimal) -->
            <div class="absolute top-0 left-0 right-0 p-3 bg-gray-100/90 rounded-t-2xl flex items-center gap-2 border-b border-gray-300">
                <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                <span class="text-xs text-gray-500 ml-3 font-mono tracking-widest">[ console.log ]</span>
            </div>
             <!-- The actual text area -->
            <div id="gameText" class="p-6 pt-14 text-lg leading-relaxed whitespace-pre-wrap max-h-96 overflow-y-auto rounded-2xl transition-all">
                <span class="system-ready font-light">
                    // :: SYSTEM READY :: PUSH START TO INITIATE LEADERSHIP PROTOCOL //
                </span>
            </div>
        </div>


        <!-- === Pilihan Jawaban (Clean Tabs) === -->
        <div id="choices" class="flex flex-wrap justify-center gap-4 mt-6">
            <!-- Pilihan Jawaban Akan Muncul di Sini -->
        </div>

        <!-- === Form Jawaban Teks & Suara (Pill Shape) === -->
        <form id="textAnswerForm" class="flex justify-center mt-6 gap-3" autocomplete="off">
            <input id="textAnswerInput" type="text" placeholder="INPUT COMMAND (A/B/C) OR TEXT DATA..." class="border border-blue-400 rounded-full px-5 py-3 w-full max-w-xl bg-white/80 text-gray-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium transition-all" disabled autocomplete="off" />

            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-extrabold px-6 py-3 rounded-full transition-all shadow-lg hover:shadow-xl btn-press disabled:opacity-50" disabled>
                SEND CMD
            </button>

            <button type="button" id="voiceBtn" class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-3 rounded-full focus:outline-none focus:ring-4 focus:ring-emerald-300 transition-all shadow-md btn-press disabled:opacity-50" title="Gunakan suara" disabled>
                🎤
            </button>
        </form>
        <!-- Status message for voice input -->
        <div id="statusMessage" class="text-center mt-3 text-sm font-semibold text-blue-700 italic">
            <!-- Voice Status will be displayed here -->
        </div>
    </section>

    <!-- Modal for Custom Alert/Confirmation (Light Tahoe Style) -->
    <div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-30 hidden items-center justify-center p-4 z-50 transition-opacity duration-300">
        <div class="acrylic-card rounded-3xl p-8 w-full max-w-md transform scale-100 border-blue-400">
            <h3 id="modalTitle" class="text-2xl font-bold text-blue-700 mb-4 tracking-wider">SYSTEM MESSAGE</h3>
            <p id="modalMessage" class="text-gray-700 mb-6"></p>
            <div id="modalButtons" class="flex justify-end gap-3">
                <button id="modalConfirmBtn" class="bg-red-600 hover:bg-red-500 text-white font-semibold px-5 py-2 rounded-xl transition-all btn-press shadow-md hidden">TERMINATE</button>
                <button id="modalCloseBtn" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2 rounded-xl transition-all btn-press shadow-md">CLOSE</button>
            </div>
        </div>
    </div>


    <!-- ===== FOOTER (Data Signature) ===== -->
    <footer class="mt-auto text-gray-600 text-sm select-none py-4 pt-10 tracking-widest font-mono">
        // Data Integrity Check: OK // Cyber-Quest V2.1.5 (Elegant Light Remaster)
    </footer>

    <audio id="ttsAudio" src="" preload="auto"></audio>
    <audio id="bgMusic" src="MIG.mp3" loop></audio>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.3/dist/purify.min.js"></script>
<script>
  const gameText = document.getElementById("gameText");
  const choicesDiv = document.getElementById("choices");
  const temaSelect = document.getElementById("tema");
  const levelSelect = document.getElementById("levelSelect");
  const temaCustomInput = document.getElementById("temaCustom");
  const addTemaBtn = document.getElementById("addTemaBtn");
  const startBtn = document.getElementById("startBtn");
  const resetBtn = document.getElementById("resetBtn");
  const ttsToggleBtn = document.getElementById("ttsToggleBtn");
  const ttsStopBtn = document.getElementById("ttsStopBtn");
  const downloadBtn = document.getElementById("downloadBtn");
  const textAnswerForm = document.getElementById("textAnswerForm");
  const textAnswerInput = document.getElementById("textAnswerInput");
  const voiceBtn = document.getElementById("voiceBtn");
  const bgMusic = document.getElementById("bgMusic");

  let history = JSON.parse(localStorage.getItem("gameHistory")) || [];
  let currentNarasi = "";
  let ttsEnabled = false;
  let utterance = null;
  let recognition = null;
  let recognizing = false;

  // ✅ Modified to support HTML output
  function drawText(text, isHtml = false) {
    if (isHtml) {
      gameText.innerHTML = DOMPurify.sanitize(text);
    } else {
      gameText.textContent = text;
    }
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
    dlAnchor.setAttribute("download", "game_history.txt");
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

function buatPrompt(history, pilihan) {
  const tema = temaSelect.value;
  const level = levelSelect.value;

  return `
Kamu adalah Subuh, Game Master dalam permainan edukatif berbasis teks interaktif.

⚠️ TOLAK permainan dengan sopan namun tegas jika tema yang dipilih tidak sesuai norma pendidikan — misalnya mengandung:
- Kekerasan ekstrem
- SARA (Suku, Agama, Ras, dan Antargolongan)
- Pornografi
- Narkoba

---

🎮 Tema Permainan: ${tema}  
🎯 Tingkat Kesulitan: ${level}

---

🧩 TUGASMU:
1. Lanjutkan cerita berdasarkan riwayat sebelumnya dan pilihan terakhir pemain.
2. Bangun narasi yang sesuai dengan tingkat kesulitan:

   - 🟢 **Mudah**:  
     - Cerita sederhana dan ringan.  
     - Pilihan jelas dan langsung.  
     - Konflik minimal, berorientasi pada pembelajaran langsung.

   - 🟡 **Sedang**:  
     - Tambahkan konflik emosional, dilema moral, atau pertentangan tujuan.  
     - Perkenalkan karakter baru dengan niat tidak langsung terlihat.  
     - Berikan pilihan yang menuntut pertimbangan dan intuisi.

   - 🔴 **Sulit**:  
     - Buat cerita bercabang dengan dampak besar dari pilihan pemain.  
     - Tambahkan misteri, pengkhianatan, twist, dan jebakan logis.  
     - Masukkan pilihan abu-abu dan paradoks moral.  
     - Jangan beri tahu mana pilihan terbaik — biarkan pemain berpikir keras.

3. Jika permainan BELUM selesai:
   - Tampilkan tujuan misi utama yang harus dicapai oleh pemain.  
   - Tampilkan tujuan dan narasi awal.  
   - Berikan 2–3 pilihan, dengan sesekali opsi pengecoh atau tidak relevan secara jelas.  
   - Gunakan gaya bahasa yang imajinatif dan membangun suasana.

4. Jika permainan SELESAI:
   - Tampilkan narasi penutup yang memuaskan.  
   - Berikan ringkasan (review) tentang perjalanan dan pilihan pemain.  
   - Tambahkan simpulan edukatif berdasarkan tema.

---

📦 KEMBALIKAN HASIL DALAM FORMAT JSON SEPERTI INI:

{
  "narasi": "Narasi lanjutan atau akhir...",
  "opsi": ["Opsi A", "Opsi B", "Opsi C"],
  "selesai": false,
  "review": "Ringkasan perjalanan pemain (hanya saat selesai)",
  "simpulan": "Pelajaran edukatif dari cerita (hanya saat selesai)"
}

❌ Jika tema tidak sesuai norma:
{
  "narasi": "Tema yang dipilih tidak sesuai dengan norma pendidikan. Mohon pilih tema lain.",
  "opsi": [],
  "selesai": true
}

---

📚 Riwayat Pemain:
${JSON.stringify(history, null, 2)}

🧭 Pilihan Terakhir:
"${pilihan}"

---

${!history.length || !history[history.length - 1].selesai ? 
`🔎 Tujuan Misi: Pemain harus mencapai tujuan utama permainan sesuai tema yang dipilih, misalnya memecahkan teka-teki, menyelesaikan konflik, atau belajar keterampilan baru.` : ''}
`;
}


  async function makeChoice(pilihan) {
    if (pilihan === "__start__") {
      history = [];
    } else {
      history.push({ narasi: currentNarasi, pilihan });
      saveHistory();
    }

    drawText("⏳ Menunggu jawaban dari Master Subuh Kurniawan...");
    choicesDiv.innerHTML = "";
    textAnswerInput.value = "";
    textAnswerInput.disabled = true;
    textAnswerForm.querySelector("button[type='submit']").disabled = true;
    voiceBtn.disabled = true;

    const prompt = buatPrompt(history, pilihan);

    try {
      const response = await fetch("gemini_proxy.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          messages: [
            {
              role: "user",
              content: { text: prompt }
            }
          ]
        })
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

      const cleanText = aiText
        .trim()
        .replace(/^```json/, '')
        .replace(/^```/, '')
        .replace(/```$/, '')
        .trim()
        .replace(/\*\*(.+?)\*\*/g, '<b>$1</b>');

      document.getElementById('gameText').innerHTML = DOMPurify.sanitize(cleanText);

      let parsed;
      try {
        parsed = JSON.parse(cleanText);
      } catch (e) {
        console.error("JSON parse error:", e, cleanText);
        drawText("❌ Gagal memproses data AI.");
        choicesDiv.innerHTML = `<button onclick="startGame()" class="choice-button bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-md transition-shadow shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 cursor-pointer">🔁 Coba Lagi</button>`;
        return;
      }

      currentNarasi = parsed.narasi || "";
      drawText(currentNarasi, true); // ✅ RENDER HTML DENGAN <b>
      speakText(currentNarasi);

      if (parsed.selesai) {
        choicesDiv.innerHTML = "<strong class='text-indigo-800 text-lg'>🎉 Permainan selesai!</strong>";
        if (parsed.review) {
          const reviewDiv = document.createElement("div");
          reviewDiv.className = "mt-4 p-3 bg-indigo-100 rounded text-indigo-900";
          reviewDiv.textContent = "📝 Review: " + parsed.review;
          choicesDiv.appendChild(reviewDiv);
        }
        if (parsed.simpulan) {
          const simpulanDiv = document.createElement("div");
          simpulanDiv.className = "mt-2 p-3 bg-indigo-200 rounded text-indigo-900 font-semibold";
          simpulanDiv.textContent = "💡 Simpulan: " + parsed.simpulan;
          choicesDiv.appendChild(simpulanDiv);
        }
      } else {
        showChoices(parsed.opsi || []);
        textAnswerInput.disabled = false;
        textAnswerForm.querySelector("button[type='submit']").disabled = false;
        voiceBtn.disabled = false;
        textAnswerInput.focus();
      }

    } catch (error) {
      console.error("Fetch error:", error);
      drawText("❌ Terjadi kesalahan saat mendapatkan respons AI.");
      choicesDiv.innerHTML = `<button onclick="startGame()" class="choice-button bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-md transition-shadow shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 cursor-pointer">🔁 Coba Lagi</button>`;
    }
  }

  function startGame() {
    if (bgMusic) {
      bgMusic.volume = 0.2;
      bgMusic.play().catch(e => {
        console.warn("Autoplay gagal:", e);
      });
    }
    makeChoice("__start__");
  }

  const randomTemaBtn = document.getElementById("randomTemaBtn");

  randomTemaBtn.onclick = async () => {
    try {
      const response = await fetch("tema.json");
      if (!response.ok) throw new Error("Gagal memuat tema.json");

      const temaList = await response.json();
      if (!Array.isArray(temaList) || temaList.length === 0) {
        Swal.fire({
          icon: "warning",
          title: "Oops!",
          text: "Daftar tema kosong atau tidak valid.",
        });
        return;
      }

      const randomIndex = Math.floor(Math.random() * temaList.length);
      const selectedTema = temaList[randomIndex];

      let found = false;
      for (let option of temaSelect.options) {
        if (option.value === selectedTema) {
          found = true;
          break;
        }
      }

      if (!found) {
        const newOption = document.createElement("option");
        newOption.value = selectedTema;
        newOption.textContent = selectedTema;
        temaSelect.appendChild(newOption);
      }

      temaSelect.value = selectedTema;

      Swal.fire({
        icon: "info",
        title: "🎲 Tema Acak Terpilih",
        text: `${selectedTema}\n\n\nSilakan gunakan tema ini untuk memulai permainan.\nLanjutkan dengan Klik Mulai Game`,
        confirmButtonText: "Oke",
      });

    } catch (err) {
      console.error("Error mengambil tema acak:", err);
      Swal.fire({
        icon: "error",
        title: "Gagal Memuat Tema",
        text: "❌ Gagal mengambil tema acak. Periksa koneksi atau file tema.json.",
      });
    }
  };

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
    drawText("");
    choicesDiv.innerHTML = "";
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

  addTemaBtn.onclick = () => {
    const val = temaCustomInput.value.trim();
    if (val) {
      for (let option of temaSelect.options) {
        if (option.value.toLowerCase() === val.toLowerCase()) {
          alert("Tema sudah ada!");
          return;
        }
      }
      const newOption = document.createElement("option");
      newOption.value = val;
      newOption.textContent = val;
      temaSelect.appendChild(newOption);
      temaSelect.value = val;
      temaCustomInput.value = "";
    }
  };

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
if ("webkitSpeechRecognition" in window || "SpeechRecognition" in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.lang = "id-ID";
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = () => {
        recognizing = true;
        voiceBtn.textContent = "🎙️ Listening...";
        voiceBtn.classList.add('voice-listening');
        voiceBtn.disabled = true;
        showStatus("🎙️ Silakan berbicara.", 'text-cyan-400');
    };

    recognition.onresult = (event) => {
      recognizing = false;
        voiceBtn.textContent = "🎤";
        voiceBtn.disabled = false;
      const speechResult = event.results[0][0].transcript;
      textAnswerInput.value = speechResult;
      makeChoice(speechResult.trim());
    };

    recognition.onerror = (event) => {
      recognizing = false;
      voiceBtn.textContent = "🎤";
      voiceBtn.disabled = false;
      alert("Gagal mengenali suara: " + event.error);
    };

    recognition.onend = () => {
      recognizing = false;
      voiceBtn.textContent = "🎤";
      voiceBtn.disabled = false;
    };

    voiceBtn.onclick = () => {
      if (recognizing) {
        recognition.stop();
        recognizing = false;
        voiceBtn.textContent = "🎤";
      } else {
        recognition.start();
      }
    };
  } else {
    voiceBtn.disabled = true;
    voiceBtn.title = "Browser tidak mendukung voice recognition";
  }
</script>


</body>
</html>
