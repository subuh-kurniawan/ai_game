<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subuh: Game Master Edukatif Interaktif</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Load Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                <i class="fas fa-book-open text-indigo-500 mr-2"></i> Subuh: Game Master
            </h1>
            <p class="text-gray-600 mt-1 italic">Permainan Edukatif Interaktif Berbasis Teks</p>
        </header>

        <!-- Main Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kiri: Pengaturan & Kontrol (Settings & Controls) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Box Pengaturan Game -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100">
                    <h2 class="text-xl font-bold text-indigo-700 mb-4 flex items-center">
                        <i class="fas fa-sliders-h mr-2"></i> Pengaturan Game
                    </h2>

                    <div class="mb-4">
                        <label for="tema" class="block text-sm font-medium text-gray-700 mb-1">Tema Cerita</label>
                        <select id="tema" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                             <option value="kepemimpinan untuk guru" selected>Kepemimpinan Sekolah</option>
                <option value="kewirausahaan untuk guru">Kewirausahaan Pendidikan</option>
                <option value="etika kerja di dunia industri untuk guru SMK">Etika Kerja Industri</option>
                <option value="kerja sama tim dalam proyek sekolah untuk guru">Kerja Sama Tim Proyek</option>
                        </select>
                    </div>

                    <div class="flex space-x-2 mb-4">
                        <input type="text" id="temaCustom" placeholder="Tambah Tema Kustom..." class="flex-grow p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <button id="addTemaBtn" class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg font-semibold transition duration-150" title="Tambahkan tema ke daftar">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    
                    <button id="randomTemaBtn" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg font-semibold transition duration-150 shadow-md">
                        <i class="fas fa-dice mr-2"></i> Pilih Tema Acak
                    </button>

                    <div class="mt-4">
                        <label for="levelSelect" class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kesulitan</label>
                        <select id="levelSelect" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                            <option value="Mudah">🟢 Mudah</option>
                            <option value="Sedang" selected>🟡 Sedang</option>
                            <option value="Sulit">🔴 Sulit</option>
                        </select>
                    </div>
                </div>

                <!-- Box Kontrol Game -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100">
                    <h2 class="text-xl font-bold text-indigo-700 mb-4 flex items-center">
                        <i class="fas fa-gamepad mr-2"></i> Kontrol Aksi
                    </h2>
                    <div class="grid grid-cols-2 gap-3">
                        <button id="startBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                            <i class="fas fa-play mr-2"></i> Mulai Game
                        </button>
                        <button id="resetBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                            <i class="fas fa-redo mr-2"></i> Reset
                        </button>
                        <button id="downloadBtn" class="bg-gray-500 hover:bg-gray-600 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
                            <i class="fas fa-download mr-2"></i> Download Riwayat
                        </button>
                        <button id="analyzeBtn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold p-3 rounded-xl shadow-md transition duration-150">
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

            <!-- Kanan: Area Permainan (Game Area) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Box Narasi Utama -->
                <div class="bg-white p-8 rounded-xl shadow-2xl border border-indigo-200 min-h-[300px] flex flex-col">
                    <h2 class="text-2xl font-extrabold text-indigo-900 mb-4 border-b pb-2">
                        <i class="fas fa-scroll mr-2"></i> Narasi Game Master Subuh
                    </h2>
                    <div id="gameText" class="text-gray-800 flex-grow overflow-y-auto leading-relaxed max-h-[calc(100vh-450px)] sm:max-h-[calc(100vh-350px)]">
                        <!-- Initial content -->
                        <p class="text-lg italic text-gray-500">Selamat datang, Guru. Pilih tema, tingkat kesulitan, lalu klik "Mulai Game" untuk memulai petualangan edukatif Anda bersama Game Master Subuh.</p>
                    </div>
                </div>

                <!-- Box Pilihan / Jawaban -->
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100">
                    <h2 class="text-xl font-bold text-indigo-700 mb-4 flex items-center">
                        <i class="fas fa-question-circle mr-2"></i> Pilihan Aksi
                    </h2>

                    <!-- Tombol Pilihan (Choice Buttons) -->
                    <div id="choices" class="flex flex-col space-y-3">
                        <!-- Pilihan akan di-render di sini oleh JS -->
                        <p class="text-sm text-gray-500">Pilihan akan muncul di sini setelah game dimulai.</p>
                    </div>

                    <!-- Input Teks / Suara -->
                    <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-3 border-t pt-4">Atau Jawab Bebas</h3>
                    <form id="textAnswerForm" class="flex space-x-3">
                        <input type="text" id="textAnswerInput" placeholder="Ketik jawaban atau pilihan Anda di sini..." class="flex-grow p-3 border border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" disabled>
                        <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white p-3 rounded-xl font-bold shadow-md transition duration-150" disabled title="Kirim Jawaban Teks">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        <button type="button" id="voiceBtn" class="bg-cyan-500 hover:bg-cyan-600 text-white p-3 rounded-xl font-bold shadow-md transition duration-150" disabled title="Gunakan Input Suara (Indonesian)">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Analysis (Required by original JS) -->
    <form id="analyzeForm" method="POST" action="analisa.php" class="hidden">
        <input type="hidden" id="gameHistoryInput" name="history">
        <input type="hidden" id="temaInput" name="tema">
    </form>

  <audio id="ttsAudio" src="" preload="auto"></audio>
  <audio id="bgMusic" src="MIG.mp3" loop></audio>



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
