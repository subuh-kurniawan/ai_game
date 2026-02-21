<?php

include "../admin/fungsi/koneksi.php";
$sql = mysqli_query($koneksi, "SELECT * FROM datasekolah");
$data = mysqli_fetch_assoc($sql);
$configPath = __DIR__ . "/api.json";

if (!file_exists($configPath)) {
    die("File config.json tidak ditemukan di folder fungsi/");
}

$config = json_decode(file_get_contents($configPath), true);

// Pastikan ada array apiKeys
if (!isset($config['apiKeys']) || !is_array($config['apiKeys']) || count($config['apiKeys']) === 0) {
    die("File config.json harus berisi array 'apiKeys'.");
}

// Pilih API key secara acak (rotasi otomatis)
$apiKey = $config['apiKeys'][array_rand($config['apiKeys'])];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guru Bahasa Lampung (Game Interaktif AI)</title>
    <!-- Memuat Tailwind CSS untuk styling responsif dan estetis -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Menggunakan font Inter */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0fdf4; /* Light green background */
            /* Menggunakan tinggi penuh viewport untuk pengalaman mobile yang lebih baik */
            min-height: 100vh;
        }
        /* Mengatur kontainer utama agar mengisi sebagian besar layar dan berpusat */
        #main-container {
            height: 95vh; /* Menggunakan 95% dari viewport height */
            max-height: 95vh;
        }
        
        /* Custom scrollbar for chat history */
        #chat-history::-webkit-scrollbar {
            width: 8px;
        }
        #chat-history::-webkit-scrollbar-thumb {
            background-color: #34d399; /* Emerald 400 */
            border-radius: 4px;
        }
        #chat-history::-webkit-scrollbar-track {
            background-color: #d1fae5; /* Emerald 100 */
        }
        .message-bubble {
            max-width: 85%;
            padding: 0.75rem 1rem;
            border-radius: 1.5rem;
            line-height: 1.4;
        }
        
        /* Penyesuaian untuk Mobile: Memastikan Sidebar tidak terlalu tinggi */
        @media (max-width: 1023px) {
             /* Batasi tinggi sidebar pada mobile agar chat area tetap terlihat */
            #sidebar {
                max-height: 40vh; /* Maksimum 40% tinggi layar pada mobile */
                overflow-y: auto;
                flex-shrink: 0;
            }
            #main-container {
                height: auto; /* Biarkan tinggi menyesuaikan konten di mobile */
                min-height: 100vh;
                max-height: none;
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-2 sm:p-4">

    <!-- Container Utama - Menambah ID untuk styling, menyesuaikan responsivitas tinggi -->
    <div id="main-container" class="w-full max-w-5xl bg-white shadow-2xl rounded-xl flex flex-col lg:flex-row overflow-hidden">
        
        <!-- Sidebar Konfigurasi dan Informasi (Responsive) -->
        <div id="sidebar" class="lg:w-1/4 bg-emerald-700 p-6 flex flex-col text-white rounded-t-xl lg:rounded-l-xl lg:rounded-tr-none">
            <h2 class="text-2xl font-bold mb-4 border-b border-emerald-500 pb-2">Pengaturan Game</h2>
            
            <div id="game-config" class="space-y-4 mb-6 flex-shrink-0">
                <div>
                    <label for="dialek-select" class="block text-sm font-medium mb-1">Pilih Dialek:</label>
                    <select id="dialek-select" class="w-full p-2 rounded-lg text-gray-900 bg-emerald-100 border border-emerald-500 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 transition duration-150">
                        <option value="A / Pepadun">Dialek A (Pepadun)</option>
                        <option value="O / Pesisir">Dialek O (Pesisir)</option>
                    </select>
                </div>
                <div>
                    <label for="level-select" class="block text-sm font-medium mb-1">Level Awal:</label>
                    <select id="level-select" class="w-full p-2 rounded-lg text-gray-900 bg-emerald-100 border border-emerald-500 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 transition duration-150">
                        <option value="1">1 (Pemula)</option>
                        <option value="2">2 (Dasar)</option>
                        <option value="3">3 (Menengah)</option>
                        <option value="4">4 (Mahir)</option>
                        <option value="5">5 (Tantangan)</option>
                    </select>
                </div>
                <button id="start-game-btn" onclick="startGame()"
                        class="w-full py-3 mt-4 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition duration-200 shadow-lg">
                    Mulai Game
                </button>
            </div>

            <!-- Status Game -->
            <h2 class="text-xl font-bold mb-4 border-b border-emerald-500 pb-2 flex-shrink-0">Status Anda</h2>
            <!-- Menggunakan flex-grow agar mengisi sisa ruang di desktop, dan scrollable di mobile jika perlu -->
            <div class="space-y-3 lg:flex-grow lg:overflow-y-auto">
                <div class="bg-emerald-600 p-3 rounded-lg shadow-inner">
                    <p class="text-sm font-semibold">Dialek Dipilih:</p>
                    <p id="dialek-display" class="text-xl font-extrabold italic">A / Pepadun</p>
                </div>
                <div class="bg-emerald-600 p-3 rounded-lg shadow-inner">
                    <p class="text-sm font-semibold">Level Aktif:</p>
                    <p id="level-display" class="text-3xl font-extrabold">1</p>
                </div>
                <div class="bg-emerald-600 p-3 rounded-lg shadow-inner">
                    <p class="text-sm font-semibold">Skor Benar:</p>
                    <p id="score-display" class="text-3xl font-extrabold">0</p>
                </div>
            </div>
        </div>

        <!-- Area Chat Utama (Responsive) -->
        <div class="lg:w-3/4 flex flex-col p-4 sm:p-6 lg:p-8">
            <h1 class="text-xl sm:text-2xl font-bold text-emerald-800 mb-4 border-b pb-2 flex-shrink-0">Kelas Bahasa Lampung</h1>
            
            <!-- Riwayat Chat - Menggunakan flex-grow agar mengisi ruang vertikal yang tersisa -->
            <div id="chat-history" class="flex-grow overflow-y-auto space-y-4 p-2 mb-4 bg-gray-50 rounded-lg shadow-inner">
                <!-- Pesan awal dimuat di sini -->
                <div class="flex justify-start">
                    <div class="message-bubble bg-gray-200 text-gray-800 shadow-md">
                        Halo! Saya Bunda Bunga, Guru Bahasa Lampung Anda. Silakan pilih Dialek dan Level awal Anda di panel kiri, lalu tekan Mulai Game untuk memulai tantangan pertama!
                    </div>
                </div>
            </div>

            <!-- Area Input (Fixed at bottom) -->
            <div class="flex flex-shrink-0">
                <input type="text" id="user-input" placeholder="Pilih konfigurasi dan tekan Mulai Game..."
                       class="flex-grow p-3 border-2 border-emerald-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:bg-gray-100"
                       onkeydown="if(event.key === 'Enter') document.getElementById('send-btn').click()" disabled>
                <button id="send-btn" onclick="sendMessage(document.getElementById('user-input').value)"
                        class="px-6 py-3 bg-emerald-500 text-white font-semibold rounded-r-xl hover:bg-emerald-600 transition duration-200 disabled:bg-gray-400" disabled>
                    Kirim
                </button>
            </div>
            <div id="error-message" class="text-red-500 text-sm mt-2 hidden flex-shrink-0"></div>
        </div>

    </div>

    <script>
        // State Game Global
        let level = 1;
        let score = 0;
        let currentDialect = 'A / Pepadun';
        let isGameStarted = false;
        let isWaitingForResponse = false;
        let chatHistory = [];
        
        // Konstanta API
        const API_KEY = "<?php echo $apiKey; ?>"; // Kunci API akan diisi oleh Canvas
        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${API_KEY}`;
        
        // --- Utility untuk membersihkan Markdown ---
        function stripMarkdownFormatting(text) {
            // 1. Hapus penanda bold/italic (**, __, *, _)
            let cleaned = text.replace(/(\*\*|__|\*|_)/g, '');
            // 2. Hapus penanda header (#) di awal baris
            cleaned = cleaned.replace(/^#+\s/gm, '');
            // 3. Hapus penanda list (-, *, +) di awal baris
            cleaned = cleaned.replace(/^\s*[-*+]\s/gm, '');
            // 4. Hapus penanda blockquote (>) di awal baris
            cleaned = cleaned.replace(/^>\s/gm, '');
            return cleaned;
        }

        // --- Sistem Prompt untuk Game Master AI ---
        function getGameMasterSystemPrompt() {
            const levelDescription = {
                1: "Kosakata dasar (benda/kegiatan)",
                2: "Frasa sederhana dan kata sifat",
                3: "Kalimat pendek dan struktur kalimat",
                4: "Dialog singkat dan topik khusus (makanan/adat)",
                5: "Tata bahasa kompleks dan pantun/pepatah"
            }[level] || "Topik acak.";

            return `Anda adalah 'Guru Bahasa Lampung' (Lampungese Language Teacher) Game Master. 
            Nama Anda adalah 'Bunda Bunga'.
            Persona Anda adalah ramah, sabar, dan sangat berpengetahuan tentang Bahasa Lampung.
            Dialek yang digunakan adalah **${currentDialect}**.
            Selalu gunakan nama panggilan Bunda Bunga dalam balasan Anda.
            
            Tujuan Anda adalah menguji dan mengajar pengguna Bahasa Lampung.
            
            Aturan Interaksi:
            1. Selalu balas dalam Bahasa Indonesia, kecuali saat memberikan contoh kata/frasa Lampung.
            2. Pertahankan konteks game dan status pengguna (Level ${level}, Dialek ${currentDialect}, Skor ${score}).
            3. Tantangan untuk Level ${level} berfokus pada: ${levelDescription}.
            4. Gunakan format "Tantangan Level ${level}" saat memberikan soal baru.

            Alur Game:
            - **Awal:** Sambut pengguna, jelaskan aturan (terjemahkan kata atau frasa yang Bunda Bunga berikan ke Bahasa Lampung atau sebaliknya), dan berikan Tantangan Level ${level} yang sesuai dengan dialek yang dipilih.
            - **Jawaban Benar:** Beri selamat, jelaskan sedikit konteks budaya/linguistik kata tersebut, dan segera naikkan Level (+1 skor, +1 level) dengan tantangan baru. Pastikan skor bertambah 1 dan level naik 1.
            - **Jawaban Salah/Tidak Jelas:** Koreksi dengan lembut, berikan jawaban yang benar, jelaskan kesalahannya, dan **ulangi tantangan yang sama atau berikan tantangan serupa** (skor dan level tetap).

            Berikan Tantangan Level ${level} yang baru sekarang.`;
        }
        
        // --- Fungsi UI dan Status ---

        function updateUI() {
            document.getElementById('level-display').textContent = level;
            document.getElementById('score-display').textContent = score;
            document.getElementById('dialek-display').textContent = currentDialect;

            // Update UI berdasarkan status game
            const inputElement = document.getElementById('user-input');
            const buttonElement = document.getElementById('send-btn');
            const startButton = document.getElementById('start-game-btn');
            
            if (isGameStarted) {
                inputElement.disabled = false;
                buttonElement.disabled = false;
                inputElement.placeholder = "Ketik jawaban atau pertanyaan Anda di sini...";
                startButton.disabled = true;
                startButton.classList.replace('bg-emerald-500', 'bg-gray-500');
            } else {
                inputElement.disabled = true;
                buttonElement.disabled = true;
                inputElement.placeholder = "Pilih konfigurasi dan tekan Mulai Game...";
                startButton.disabled = false;
                startButton.classList.replace('bg-gray-500', 'bg-emerald-500');
            }
        }
        
        function updateChat(sender, message, isHtml = false) {
            const chatHistoryElement = document.getElementById('chat-history');
            const messageContainer = document.createElement('div');
            
            // Tentukan posisi dan warna bubble
            let alignmentClass, colorClass, textColor;
            if (sender === 'user') {
                alignmentClass = 'flex justify-end';
                colorClass = 'bg-emerald-500';
                textColor = 'text-white';
            } else { // Game Master / System
                alignmentClass = 'flex justify-start';
                colorClass = 'bg-gray-200';
                textColor = 'text-gray-800';
            }

            messageContainer.className = alignmentClass;
            
            const messageBubble = document.createElement('div');
            messageBubble.className = `message-bubble ${colorClass} ${textColor} shadow-md`;
            
            if (isHtml) {
                messageBubble.innerHTML = message;
            } else {
                // Gunakan innerText/textContent untuk mencegah XSS pada pesan yang dimasukkan pengguna
                messageBubble.textContent = message;
            }
            
            messageContainer.appendChild(messageBubble);
            chatHistoryElement.appendChild(messageContainer);
            
            // Gulir ke bawah secara otomatis
            chatHistoryElement.scrollTop = chatHistoryElement.scrollHeight;
        }

        function setInputState(enabled) {
            const input = document.getElementById('user-input');
            const button = document.getElementById('send-btn');
            input.disabled = !enabled;
            button.disabled = !enabled;
            isWaitingForResponse = !enabled;
            if (!enabled) {
                button.textContent = 'Memproses...';
            } else {
                button.textContent = 'Kirim';
            }
        }

        // --- Logika Game Startup ---

        function startGame() {
            if (isGameStarted) return;
            
            const selectedDialect = document.getElementById('dialek-select').value;
            const selectedLevel = parseInt(document.getElementById('level-select').value);

            level = selectedLevel;
            score = 0;
            currentDialect = selectedDialect;
            isGameStarted = true;
            chatHistory = []; // Reset history
            
            // Clear chat history display except for the initial greeting (index 0)
            const chatHistoryElement = document.getElementById('chat-history');
            while (chatHistoryElement.children.length > 1) {
                chatHistoryElement.removeChild(chatHistoryElement.lastChild);
            }
            
            updateUI();
            
            const initialMessage = `Bunda Bunga, saya ingin memulai Level ${level} dengan Dialek ${currentDialect}. Berikan tantangan pertama saya!`;
            
            // Start the game by sending the configuration to the AI
            sendMessage(initialMessage, true);
        }

        // --- Logika API dan Chat ---

        async function callGemini(userQuery, isInitial = false) {
            setInputState(false);
            
            // Format riwayat chat untuk Gemini
            let contents = [];
            if (!isInitial) {
                 // Untuk kelanjutan, kirim riwayat penuh (User -> Model -> User -> Model...)
                 chatHistory.forEach(msg => {
                    const role = msg.sender === 'user' ? 'user' : 'model';
                    contents.push({ role: role, parts: [{ text: msg.text }] });
                });
            }
            // Tambahkan query pengguna saat ini
            contents.push({ role: "user", parts: [{ text: userQuery }] });
            
            const payload = {
                contents: contents,
                systemInstruction: {
                    parts: [{ text: getGameMasterSystemPrompt() }]
                }
            };

            let responseText = "Terjadi kesalahan koneksi atau Game Master sedang sibuk. Coba lagi.";
            
            // Logika Retry dengan Exponential Backoff
            const maxRetries = 5;
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                         if (response.status === 429 && i < maxRetries - 1) {
                            const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                            await new Promise(resolve => setTimeout(resolve, delay));
                            continue; // Retry
                        }
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    
                    const result = await response.json();
                    const candidate = result.candidates?.[0];

                    if (candidate && candidate.content?.parts?.[0]?.text) {
                        // FIX APLIKASI: Bersihkan respons dari karakter markdown
                        responseText = stripMarkdownFormatting(candidate.content.parts[0].text);
                        
                        // --- Logika Deteksi Jawaban dan Update Skor/Level ---
                        const successKeywords = ["selamat", "benar", "tepat", "hebat", "pintar", "sukses"];

                        const isSuccess = successKeywords.some(keyword => 
                            responseText.toLowerCase().includes(keyword)
                        );
                        
                        if (isSuccess && level < 5) {
                            level++; 
                            score++; 
                            updateUI();
                        } else if (isSuccess) {
                            score++; 
                            updateUI();
                        }

                        updateChat('ai', responseText);
                        // Simpan respons asli (sebelum di strip) ke riwayat untuk menjaga konteks AI
                        chatHistory.push({ sender: 'ai', text: candidate.content.parts[0].text });
                        
                        break; // Exit loop on success
                    } else {
                        // Log the result for inspection before throwing the error
                        console.error("Gemini API returned an invalid content structure or was blocked:", JSON.stringify(result, null, 2));
                        throw new Error("Respons dari model tidak valid.");
                    }
                } catch (error) {
                    console.error("Error calling Gemini API:", error);
                    responseText = "Terjadi kesalahan pada sistem game (API). Mohon coba kirim pesan Anda lagi.";
                    if (i === maxRetries - 1) {
                         updateChat('system', responseText);
                    }
                } finally {
                     if (i === maxRetries - 1) {
                        setInputState(true); // Re-enable input if all retries fail
                    }
                }
            }

            setInputState(true);
        }

        // --- Fungsi Pengiriman Pesan ---

        function sendMessage(message, isInitial = false) {
            const inputElement = document.getElementById('user-input');
            const userQuery = message.trim();

            if (isWaitingForResponse) return;

            if (userQuery === '' && !isInitial) {
                document.getElementById('error-message').textContent = 'Pesan tidak boleh kosong.';
                document.getElementById('error-message').classList.remove('hidden');
                return;
            }
            
            document.getElementById('error-message').classList.add('hidden');

            if (!isInitial) {
                // Tampilkan pesan pengguna di chat
                updateChat('user', userQuery);
                // Tambahkan ke riwayat chat
                chatHistory.push({ sender: 'user', text: userQuery });
                // Bersihkan input
                inputElement.value = '';
            }

            // Panggil API Gemini
            callGemini(userQuery, isInitial);
        }

        // Event listener untuk memastikan UI diperbarui saat loading selesai
        window.addEventListener('load', () => {
            updateUI();
        });
        
    </script>
</body>
</html>
