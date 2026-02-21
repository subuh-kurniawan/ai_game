<!DOCTYPE html>
<html lang="id" class="scroll-smooth overflow-x-hidden">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cyber-Quest Leadership Guru</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Chivo+Mono:wght@400;700&display=swap" rel="stylesheet">
   
  <style>
    :root {
      --color-primary: #1d4ed8;
      --color-secondary: #059669;
      --color-text: #1f2937;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-image: url('https://images.squarespace-cdn.com/content/v1/5e949a92e17d55230cd1d44f/ddd8bd1f-3c9d-41ea-8003-998144dca908/osxcucamonga1x1.png?format=2500w');
      background-attachment: fixed;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      background-color: #f3f4f6;
      color: var(--color-text);
    }

    @media (max-width: 768px) {
      body { background-attachment: scroll; }
      .slide-container { flex-direction: column !important; max-height: 95vh; overflow-y: auto; padding: 1rem; }
      #textAnswerForm .flex { flex-direction: column; gap: 0.5rem; }
      #textAnswerForm button, #textAnswerForm #voiceBtn { width: 100%; }
    }

    .slide-container {
      background-color: rgba(255, 255, 255, 0.97);
      border-radius: 36px;
      padding: 0;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
      backdrop-filter: blur(5px);
      max-height: 95vh;
    }

    .tahoe-panel {
      background-color: rgba(249, 250, 251, 0.9);
      border: 1px solid rgba(209, 213, 219, 0.8);
      border-radius: 20px;
      padding: 1rem;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .btn-press:active {
      transform: scale(0.98);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) inset;
      opacity: 0.9;
    }

    #gameText {
      font-family: 'Chivo Mono', monospace;
      background-color: #1f2937;
      color: #10b981;
      border: 1px solid #4b5563;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) inset;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      overflow-y: auto;
      min-height: 200px;
      max-height: 50vh;
      padding: 1rem;
      border-radius: 19px;
      white-space: pre-wrap;
    }

    select, input[type="text"] {
      background-color: rgba(255, 255, 255, 0.95);
      border: 1px solid #d1d5db;
      transition: all 0.2s;
      width: 100%;
    }

    select:focus, input[type="text"]:focus {
      outline: none;
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.3);
    }

    #modal .slide-container {
      max-width: 95%;
      max-height: 90vh;
      overflow-y: auto;
    }
  </style>
</head>

<body class="min-h-screen flex flex-col items-center justify-center py-4 px-3 sm:px-6 overflow-x-hidden">

  <section class="w-full max-w-7xl slide-container flex flex-col md:flex-row gap-4 md:gap-8 p-3 sm:p-4">
    
    <!-- LEFT COLUMN -->
    <div class="flex-1 flex flex-col min-w-0 gap-3">
      <div class="text-center">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-blue-800 tracking-tight">
          CYBER-QUEST: LEADERSHIP GURU
        </h1>
        <p class="text-xs sm:text-sm text-gray-600 font-medium">Simulasi Kepemimpinan Interaktif</p>
      </div>

      <div id="gameTextWrapper" class="tahoe-panel p-2 sm:p-5 flex-1 min-w-0">
        <div id="gameText">
          // :: SYSTEM READY :: PUSH START TO INITIATE LEADERSHIP PROTOCOL //
        </div>
      </div>

      <div class="tahoe-panel p-3 sm:p-6 flex flex-col gap-2">
        <h2 class="text-base sm:text-lg font-bold text-gray-700 mb-2 flex items-center gap-2">
          ⌨️ Tindakan Pemain
        </h2>
        <div id="choices" class="flex flex-wrap justify-center gap-2 sm:gap-3"></div>

        <form id="textAnswerForm" class="flex flex-col gap-2 mt-2" autocomplete="off">
          <input id="textAnswerInput" type="text" placeholder="INPUT COMMAND (A/B/C) OR TEXT DATA..." disabled />
          <div class="flex flex-col sm:flex-row gap-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-extrabold px-4 py-2 rounded-lg btn-press disabled:opacity-50" disabled>SEND</button>
            <button type="button" id="voiceBtn" class="w-full sm:w-16 bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-2 rounded-lg btn-press disabled:opacity-50" disabled>🎤</button>
          </div>
        </form>
        <div id="statusMessage" class="text-center mt-2 text-xs sm:text-sm font-semibold text-blue-700 italic"></div>
      </div>

      <div class="tahoe-panel p-2 sm:p-4 flex flex-wrap gap-2 justify-center">
        <button id="startBtn" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-extrabold px-4 py-2 sm:px-6 sm:py-3 rounded-lg btn-press text-sm sm:text-lg">▶️ MULAI</button>
        <button id="resetBtn" class="bg-red-600 hover:bg-red-500 text-white font-bold px-3 py-2 rounded-lg btn-press text-sm sm:text-base">🔁 RESET</button>
        <button id="ttsToggleBtn" class="bg-yellow-500 hover:bg-yellow-400 text-white font-bold px-3 py-2 rounded-lg btn-press text-sm sm:text-base">🔈 TTS OFF</button>
        <button id="ttsStopBtn" class="bg-orange-600 hover:bg-orange-500 text-white font-bold px-3 py-2 rounded-lg btn-press text-sm sm:text-base">🛑 STOP</button>
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="md:w-1/3 flex flex-col gap-4 min-w-0">
      <div class="tahoe-panel p-3 sm:p-6 min-w-0">
        <h2 class="text-base sm:text-lg font-bold text-gray-700 mb-3 flex items-center gap-2">📘 Skenario & Level</h2>
        <label for="tema" class="text-xs sm:text-sm font-medium text-gray-600 block mb-1">Pilih Tema</label>
        <select id="tema" class="rounded-lg px-4 sm:px-5 py-2 sm:py-3 text-sm sm:text-base text-gray-700 font-medium mb-3">
          <option value="kepemimpinan pembelajaran guru di kelas" selected>Kepemimpinan Pembelajaran</option>
          <option value="kecerdasan_emosional">Kecerdasan Emosional</option>
          <option value="pengambilan_keputusan">Pengambilan Keputusan Pribadi</option>
          <option value="mindfulness">Mindfulness & Fokus</option>
          <option value="kerja_sama_tim">Kerja Sama Tim</option>
        </select>

        <label for="levelSelect" class="text-xs sm:text-sm font-medium text-gray-600 block mb-1">Pilih Level</label>
        <select id="levelSelect" class="rounded-lg px-4 sm:px-5 py-2 sm:py-3 text-green-700 font-medium mb-3 text-sm sm:text-base">
          <option value="mudah">LEVEL I (Easy)</option>
          <option value="sedang">LEVEL II (Medium)</option>
          <option value="sulit">LEVEL III (Hard)</option>
        </select>

        <input id="temaCustom" type="text" placeholder="Input data skenario kustom..." class="rounded-lg px-4 sm:px-5 py-2 sm:py-3 text-gray-700 w-full mb-3 text-sm sm:text-base" />

        <div class="flex flex-col sm:flex-row gap-2 mt-2">
          <button id="addTemaBtn" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-3 sm:px-4 py-2 sm:py-3 rounded-lg shadow-md btn-press text-xs sm:text-sm">➕ TAMBAH</button>
          <button id="randomTemaBtn" class="flex-1 bg-purple-600 hover:bg-purple-500 text-white font-bold px-3 sm:px-4 py-2 sm:py-3 rounded-lg shadow-md btn-press text-xs sm:text-sm">🎲 Acak</button>
        </div>
      </div>

      <div class="tahoe-panel p-2 flex flex-wrap gap-2 justify-center min-w-0">
        <button id="downloadBtn" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold px-3 sm:px-5 py-2 sm:py-3 rounded-lg btn-press text-xs sm:text-sm">💾 DOWNLOAD LOG</button>
       <form id="analyzeForm" method="POST" action="analisa.php" target="_blank" class="hidden">
  <input type="hidden" id="gameHistoryInput" name="history">
  <input type="hidden" id="temaInput" name="tema">
</form>

      </div>
    </div>
  </section>

  <!-- Modal -->
  <div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-20 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="slide-container rounded-3xl p-8 w-full max-w-md border-blue-400 bg-white">
      <h3 id="modalTitle" class="text-2xl font-bold text-blue-700 mb-4 tracking-wider">SYSTEM MESSAGE</h3>
      <p id="modalMessage" class="text-gray-700 mb-6"></p>
      <div id="modalButtons" class="flex justify-end gap-3">
        <button id="modalConfirmBtn" class="bg-red-600 hover:bg-red-500 text-white font-semibold px-5 py-2 rounded-lg btn-press shadow-md hidden">TERMINATE</button>
        <button id="modalCloseBtn" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2 rounded-lg btn-press shadow-md">CLOSE</button>
      </div>
    </div>
  </div>

  <audio id="ttsAudio" src="" preload="auto"></audio>
  <audio id="bgMusic" src="MIG.mp3" loop></audio>
</body>
</html>


<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.3/dist/purify.min.js"></script>
<script>
const analyzeBtn = document.getElementById("analyzeBtn");
  const analyzeForm = document.getElementById("analyzeForm");
  const gameHistoryInput = document.getElementById("gameHistoryInput");

  analyzeBtn.onclick = () => {
    if (!history || history.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Oops!",
        text: "Tidak ada riwayat permainan untuk dianalisis.",
      });
      return;
    }

    // Simpan history ke input form
    gameHistoryInput.value = JSON.stringify(history);

    // Submit form ke halaman analisa
    analyzeForm.submit();
  };
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

  // Scroll to the button inside the scrollable gameText container
  const analyzeBtn = document.getElementById('analyzeBtn');
  if (analyzeBtn) {
    gameText.scrollTo({ top: analyzeBtn.offsetTop - 10, behavior: 'smooth' });
  }
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
2. Bangun narasi dengan target adalah seorang guru:
3. Bangun narasi yang sesuai dengan tingkat kesulitan:
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
     - Buat cerita penuh konflik dan menguras pemikiran.  
     - Tambahkan misteri, pengkhianatan, twist, dan jebakan logis.  
     - Masukkan pilihan abu-abu dan paradoks moral.  
     - Jangan beri tahu mana pilihan terbaik — biarkan pemain berpikir keras.

4. Jika permainan BELUM selesai:
   - Tampilkan tujuan misi utama yang harus dicapai oleh pemain.  
   - Tampilkan tujuan dan narasi awal.  
   - Berikan 2–3 pilihan, dengan sesekali opsi pengecoh atau tidak relevan secara jelas.  
   - Gunakan gaya bahasa yang imajinatif dan membangun suasana.

5. Jika permainan SELESAI:
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

    drawText("⏳ Menunggu jawaban dari Game Master ...");
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
      const response = await fetch("tema2.json");
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
<script>
const analyzeBtn = document.getElementById("analyzeAI");

analyzeBtn.addEventListener("click", () => {
    // Ambil gameHistory dari variabel JS
    document.getElementById("gameHistoryInput").value = JSON.stringify(gameHistory);

    // Ambil tema dari temaSelect
    const tema = temaSelect.value; // temaSelect sudah didefinisikan di kode JS kamu
    document.getElementById("temaInput").value = tema;

    // Submit form ke analisa.php
    document.getElementById("analyzeForm").submit();
});
</script>

</body>
</html>
