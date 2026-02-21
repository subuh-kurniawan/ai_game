<?php
// Detect domain
include "../admin/fungsi/koneksi.php";
$sql = mysqli_query($koneksi, "SELECT * FROM datasekolah");
$data = mysqli_fetch_assoc($sql);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'];

// Tentukan path gambar OG
$ogImage = $domain . "/game/og.jpg";

// Tentukan URL halaman saat ini
$currentUrl = $domain . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chatbot Full Screen</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body { font-family: 'Inter', sans-serif; }
    .ai-response { white-space: pre-wrap; }
</style>
</head>
<body class="m-0 p-0 bg-gray-50">

<!-- Container full screen -->
<div id="app" class="w-screen h-screen flex flex-col">

    <!-- Header -->
    <header class="p-4 bg-indigo-600 text-white text-xl font-bold flex-shrink-0">
        🤖 Chatbot <?= $data['nama'] ?>
    </header>

    <!-- Chat window -->
    <div id="chat-window" class="flex-grow p-4 overflow-y-auto bg-gray-100 space-y-4">
        <div id="initial-message" class="bg-indigo-100 text-indigo-800 p-3 rounded-xl rounded-tl-none max-w-[80%] shadow-md">
            Memuat instruksi templat dari templete.txt...
        </div>
        <div id="loading-indicator" class="hidden flex justify-start">
            <div class="bg-indigo-200 text-indigo-900 p-3 rounded-xl rounded-tl-none shadow-md flex items-center space-x-2">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Subuh Kurniawan sedang menjawab...</span>
            </div>
        </div>
    </div>

    <!-- Input area -->
    <div class="p-2 border-t bg-white flex-shrink-0">
        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" id="user-input" placeholder="Tunggu, memuat templat instruksi..."
                class="flex-grow p-3 border border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 w-full"
                onkeydown="if(event.key==='Enter') sendMessage()" disabled>
            <button id="send-button" onclick="sendMessage()"
                class="bg-indigo-600 text-white p-3 rounded-xl font-semibold hover:bg-indigo-700 transition duration-150 shadow-md w-full sm:w-auto" disabled>
                Kirim
            </button>
        </div>
    </div>

</div>
    <script type="module">
        // --- Variabel Global & Inisialisasi ---
        const apiKey = "AIzaSyAYYBCPplYs1pd3vqu5e13YsbF1hgQz8EY"; 
        let templateContent = null; // Menyimpan konten dari templete.txt

        // --- Elemen UI ---
        const chatWindow = document.getElementById('chat-window');
        const userInput = document.getElementById('user-input');
        const sendButton = document.getElementById('send-button');
        const loadingIndicator = document.getElementById('loading-indicator');
        const initialMessageDiv = document.getElementById('initial-message');

        // --- Fungsi Helper UI ---

        function appendMessage(text, isUser) {
            const messageElement = document.createElement('div');
            messageElement.className = `flex ${isUser ? 'justify-end' : 'justify-start'}`;
            
            const contentElement = document.createElement('div');
            contentElement.className = `p-3 rounded-xl max-w-[80%] shadow-md ${
                isUser 
                ? 'bg-blue-600 text-white rounded-br-none' 
                : 'bg-indigo-100 text-gray-800 rounded-tl-none ai-response'
            }`;
            contentElement.innerHTML = isUser ? text : formatTemplateResponse(text);
            
            messageElement.appendChild(contentElement);
            chatWindow.appendChild(messageElement);
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }

        function formatTemplateResponse(text) {
            // Mengganti baris baru markdown dengan tag <br>
            let formattedText = text.replace(/\n/g, '<br>');
            
            // Menambahkan styling bold ke nama label (misalnya, **Nama:**)
            formattedText = formattedText.replace(/\*\*(.*?):\*\*/g, '<span class="font-bold text-indigo-800">$1:</span>');
            
            return formattedText;
        }

        function setUIState(isLoading) {
            // Menonaktifkan input/tombol jika sedang memuat API atau template belum dimuat
            sendButton.disabled = isLoading || templateContent === null;
            userInput.disabled = isLoading || templateContent === null;
            if (isLoading) {
                loadingIndicator.classList.remove('hidden');
            } else {
                loadingIndicator.classList.add('hidden');
            }
        }
        
        // --- Fungsi Pemuatan Templat (FETCH) ---
async function loadTemplate() {
    setUIState(true); // Nonaktifkan UI saat memuat template

    // Fungsi mendapatkan waktu Indonesia dari NTP/API
    async function getWaktuIndonesia() {
        try {
            const response = await fetch('https://worldtimeapi.org/api/timezone/Asia/Jakarta');
            if (!response.ok) throw new Error("Gagal mengambil waktu dari server");
            const data = await response.json();
            // Mengembalikan Date object dari datetime API
            return new Date(data.datetime);
        } catch (error) {
            console.error("Gagal mengambil waktu Indonesia, fallback ke waktu lokal:", error);
            return new Date(); // fallback ke jam lokal user
        }
    }

    // Fungsi menentukan salam berdasarkan jam
    function getSalam(jam) {
        if (jam >= 4 && jam < 10) return "Selamat pagi";
        else if (jam >= 10 && jam < 15) return "Selamat siang";
        else if (jam >= 15 && jam < 18) return "Selamat sore";
        else return "Selamat malam";
    }

    try {
        // Mengambil konten templat dari templete.txt
        const response = await fetch('templete.txt');
        if (!response.ok) throw new Error(`Gagal memuat templete.txt: ${response.statusText}`);
        templateContent = await response.text();

        // Ambil waktu akurat Indonesia
        const waktuIndonesia = await getWaktuIndonesia();
        const jam = waktuIndonesia.getHours();

        // Perbarui UI dengan salam sesuai waktu Indonesia
        initialMessageDiv.innerHTML = `<p>${getSalam(jam)}! Saya Subuh Kurniawan!! Tanyakan topik apa pun.</p>`;
        userInput.placeholder = "Ketik pertanyaan Anda di sini...";
        setUIState(false);
        console.log("Template instruksi berhasil dimuat dengan waktu akurat Indonesia.");

    } catch (error) {
        console.error("Kesalahan saat memuat templat:", error);
        initialMessageDiv.innerHTML = `<p class="text-red-600">🚨 Kesalahan: Gagal memuat templat instruksi. ${error.message}</p>`;
        userInput.placeholder = "Error memuat templat.";
    }
}

// Muat templat saat DOM siap
document.addEventListener('DOMContentLoaded', loadTemplate);


        // --- Gemini API Logic ---

        // Fungsi untuk exponential backoff
        async function fetchWithBackoff(url, options, maxRetries = 5) {
            let delay = 1000;
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (response.ok) {
                        return response;
                    }
                    if (response.status === 429 || response.status >= 500) {
                        // Jangan log retries sebagai error di konsol
                        await new Promise(resolve => setTimeout(resolve, delay));
                        delay *= 2;
                        continue;
                    }
                    throw new Error(`API returned status ${response.status}: ${response.statusText}`);
                } catch (error) {
                    if (i === maxRetries - 1) {
                        throw error;
                    }
                    // Jangan log retries sebagai error di konsol
                    await new Promise(resolve => setTimeout(resolve, delay));
                    delay *= 2;
                }
            }
            throw new Error("Failed to fetch after multiple retries.");
        }

        window.sendMessage = async function() {
            // Pastikan template sudah dimuat sebelum mengirim
            if (!templateContent) {
                appendMessage("Templat instruksi belum dimuat. Silakan tunggu sebentar atau periksa konsol untuk error.", false);
                return;
            }

            const query = userInput.value.trim();
            if (!query) return;

            // Tampilkan pesan pengguna
            appendMessage(query, true);
            userInput.value = '';
            setUIState(true);

            const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=${apiKey}`;

            const payload = {
                contents: [{ parts: [{ text: query }] }],
                // Mengaktifkan Google Search untuk fakta terkini
                tools: [{ "google_search": {} }],
                systemInstruction: {
                    parts: [{ text: templateContent }] // Menggunakan konten yang dimuat dari file
                },
            };

            try {
                const response = await fetchWithBackoff(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                const candidate = result.candidates?.[0];

                let aiText = "Maaf, saya tidak dapat menghasilkan respons yang valid.";
                
                if (candidate && candidate.content?.parts?.[0]?.text) {
                    aiText = candidate.content.parts[0].text;
                }

                appendMessage(aiText, false);

            } catch (error) {
                console.error("Kesalahan API:", error);
                appendMessage(`Terjadi kesalahan saat menghubungi AI: ${error.message}`, false);
            } finally {
                setUIState(false);
            }
        };
    </script>
</body>
</html>
