<?php

include "../admin/fungsi/koneksi.php";
$sql = mysqli_query($koneksi, "SELECT * FROM datasekolah");
$data = mysqli_fetch_assoc($sql);
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
    <title>Project Sim: Asisten AI Interaktif</title>
    <!-- Tailwind CSS CDN untuk styling modern -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library DOMPurify untuk membersihkan output HTML dari AI -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <!-- Font Awesome untuk ikon mikrofon dan speaker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Menggunakan font Inter */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #e2e8f0; /* Light Slate Blue background */
        }
        /* Custom scrollbar for chat box */
        #chatBox::-webkit-scrollbar {
            width: 8px;
        }
        #chatBox::-webkit-scrollbar-thumb {
            background-color: #94a3b8; /* Slate 400 */
            border-radius: 4px;
        }
        #chatBox::-webkit-scrollbar-track {
            background: #f1f5f9; /* Slate 50 */
        }
        /* Gaya tambahan untuk tombol mikrofon */
        .mic-button.recording {
            animation: pulse-red 1s infinite;
            background-color: #ef4444 !important; /* Red 500 */
        }
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        /* Gaya toggle switch */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #14b8a6;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #14b8a6;
        }

        /* --- Loading Dots Animation --- */
        .dot {
            width: 8px;
            height: 8px;
            margin: 0 3px;
            background-color: #14b8a6; /* accent-teal */
            border-radius: 50%;
            display: inline-block;
            animation: bounce 1s infinite ease-in-out;
        }
        .dot-2 { animation-delay: -0.8s; }
        .dot-3 { animation-delay: -0.6s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }
        /* --- End Loading Dots Animation --- */
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-blue': '#1e40af', // Blue 800
                        'accent-teal': '#14b8a6', // Teal 500
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="p-4 md:p-8 min-h-screen flex items-center justify-center">

<div class="project-container w-full max-w-2xl bg-white p-6 md:p-8 rounded-3xl shadow-2xl border border-gray-100 transition duration-300 hover:shadow-3xl">
    
    <header class="text-center mb-6">
        <h1 class="text-4xl font-extrabold text-primary-blue mb-2">Project Sim: Asisten Digital</h1>
        <p class="text-gray-500">Panduan interaktif untuk simulasi proyek industri masa depan.</p>
    </header>

    <div class="mb-6 bg-blue-50 p-4 rounded-xl border border-blue-200 flex flex-col">
        <!-- Project Type Selector & Custom Input Container -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div class="flex-grow mb-4 md:mb-0 md:mr-4 w-full">
                <label for="projectType" class="block text-sm font-semibold text-primary-blue mb-2">Pilih Jenis Proyek:</label>
                <select id="projectType" onchange="toggleCustomInput()" class="w-full p-3 border border-blue-300 rounded-lg bg-white shadow-inner focus:ring-accent-teal focus:border-accent-teal transition duration-150 ease-in-out">
                    <option value="Pengembangan Aplikasi Digital">Membuat Aplikasi</option>
                    <option value="Perakitan dan Konfigurasi Komputer">Merakit Komputer</option>
                    <option value="Desain dan Produksi Multimedia">Desain Multimedia</option>
                    <option value="Analisis dan Pelaporan Keuangan">Laporan Keuangan/Akuntansi</option>
                    <option value="CUSTOM">Lainnya/Kustom...</option>
                </select>
            </div>
            
            <!-- Custom Project Input (Default hidden) -->
            <div id="customProjectInputContainer" class="w-full md:w-auto md:flex-grow hidden mt-2 md:mt-0">
                <label for="customProjectInput" class="block text-sm font-semibold text-primary-blue mb-2">Jenis Proyek Kustom:</label>
                <input type="text" id="customProjectInput" placeholder="Contoh: Membuat rancangan jembatan baja" class="w-full p-3 border border-blue-300 rounded-lg bg-white shadow-inner focus:ring-accent-teal focus:border-accent-teal transition duration-150 ease-in-out">
            </div>
        </div>

        <!-- TTS & Download Controls -->
        <div class="flex space-x-4 mt-4 pt-4 border-t border-blue-200 justify-end md:justify-start">
            <!-- TTS Toggle Switch -->
            <div class="flex-shrink-0">
                <label for="ttsToggle" class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="ttsToggle" class="sr-only peer" checked>
                    <div class="toggle-label w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent-teal/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-teal"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700">TTS Aktif</span>
                </label>
            </div>
            
            <!-- Download Button -->
            <button onclick="downloadChatHistory()" class="bg-gray-400 text-white font-semibold text-sm py-2 px-4 rounded-lg hover:bg-gray-500 transition duration-200 ease-in-out shadow-md flex items-center">
                <i class="fas fa-download mr-2"></i> Unduh Riwayat
            </button>
        </div>
    </div>

    <div class="chat-box h-80 overflow-y-auto border border-gray-200 p-4 rounded-xl bg-gray-50 mb-6 shadow-inner" id="chatBox">
        <!-- Initial Message -->
        <div class="flex justify-start">
            <div class="max-w-[80%] p-3 rounded-xl mb-3 shadow-md bg-white text-gray-800 rounded-tl-none border border-gray-100">
                <strong class="font-bold text-sm text-primary-blue mr-1">AI Asisten:</strong> 
                <span class="text-gray-800">Halo! Saya siap membantu proyek kamu. Anda dapat memilih jenis proyek dari daftar atau memilih **Lainnya/Kustom** untuk menentukan jenis proyek Anda sendiri.</span>
            </div>
        </div>
    </div>

    <!-- Loading Indicator BARU dengan animasi bouncing dots -->
    <div id="loadingIndicator" class="hidden text-center text-sm text-gray-500 mb-2">
        <div class="inline-flex items-center">
            <div class="dot dot-1"></div>
            <div class="dot dot-2"></div>
            <div class="dot dot-3"></div>
            <span class="ml-2">AI sedang berpikir...</span>
        </div>
    </div>

    <div class="flex space-x-3">
        <button id="micButton" onclick="toggleSpeechInput()" class="mic-button bg-red-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition duration-200 transform hover:scale-105 flex-shrink-0" title="Input Suara (Bahasa Indonesia)">
            <i class="fas fa-microphone"></i>
        </button>
        <!-- Tombol Stop TTS -->
        <button id="stopTtsButton" onclick="stopTTS()" class="bg-gray-400 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:bg-gray-500 transition duration-200 transform hover:scale-105 flex-shrink-0" title="Hentikan Suara">
            <i class="fas fa-stop"></i>
        </button>

        <input type="text" id="userInput" placeholder="Ketik atau tekan mikrofon..." class="flex-grow p-4 border border-gray-300 rounded-xl shadow-inner focus:ring-primary-blue focus:border-primary-blue transition duration-200">
        <button id="sendButton" onclick="sendMessage()" class="bg-accent-teal text-white font-semibold py-3 px-6 rounded-xl hover:bg-teal-600 transition duration-200 ease-in-out shadow-lg transform hover:scale-105 flex-shrink-0">Kirim</button>
    </div>
</div>

<script>
    const chatBox = document.getElementById("chatBox");
    const loadingIndicator = document.getElementById("loadingIndicator");
    const userInput = document.getElementById("userInput");
    const sendButton = document.getElementById("sendButton");
    const micButton = document.getElementById("micButton");
    const ttsToggle = document.getElementById("ttsToggle");
    const projectTypeSelect = document.getElementById("projectType");
    const customProjectContainer = document.getElementById("customProjectInputContainer");
    const customProjectInput = document.getElementById("customProjectInput");

    const isSpeechRecognitionSupported = 'SpeechRecognition' in window || 'webkitSpeechRecognition' in window;
    
    let recognition = null;
    let isListening = false;
    
    // --- CUSTOM PROJECT INPUT LOGIC ---
    function toggleCustomInput() {
        if (projectTypeSelect.value === 'CUSTOM') {
            customProjectContainer.classList.remove('hidden');
            customProjectInput.focus();
        } else {
            customProjectContainer.classList.add('hidden');
        }
    }
    
    function getCurrentProjectType() {
        if (projectTypeSelect.value === 'CUSTOM') {
            const customValue = customProjectInput.value.trim();
            return customValue || "Proyek Kustom (Tidak Ditentukan)";
        }
        return projectTypeSelect.value;
    }
    
    window.addEventListener('load', toggleCustomInput);


    // --- UTILITY FUNCTIONS ---

    const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

    // --- FITUR UNDUH RIWAYAT ---
    function downloadChatHistory() {
        const messages = chatBox.querySelectorAll('.flex > div');
        let historyText = `===== Riwayat Percakapan Project Sim (${new Date().toLocaleString()}) =====\n\n`;

        messages.forEach(messageDiv => {
            const isUser = messageDiv.classList.contains('bg-accent-teal');
            const isError = messageDiv.classList.contains('bg-red-100');

            let sender, content;
            if (isUser) {
                sender = "Kamu";
                content = messageDiv.querySelector('span').textContent.trim();
            } else if (isError) {
                const fullText = messageDiv.textContent.trim();
                const match = fullText.match(/(.+?)\s*\((Error)\):\s*(.*)/s);
                sender = match ? match[1] + " (" + match[2] + ")" : "AI Asisten (Error)";
                content = match ? match[3] : fullText;
            } else {
                sender = "AI Asisten";
                const span = messageDiv.querySelector('span');
                content = span ? span.innerHTML.replace(/<br>/g, '\n').replace(/<[^>]*>/g, '').trim() : messageDiv.textContent.trim();
                content = content.replace(/Sumber:\s*.+/s, '').trim();
            }

            historyText += `${sender}: ${content}\n---\n`;
        });

        const blob = new Blob([historyText], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Riwayat_ProjectSim_${Date.now()}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    // --- AKHIR FITUR UNDUH RIWAYAT ---

    // --- SPEECH RECOGNITION (STT) ---

    if (isSpeechRecognitionSupported) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.lang = 'id-ID'; 
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onstart = () => {
            isListening = true;
            micButton.classList.add('recording');
            micButton.querySelector('i').classList.remove('fa-microphone');
            micButton.querySelector('i').classList.add('fa-stop-circle');
            userInput.placeholder = "Mendengarkan... Bicara sekarang.";
            stopTTS(); 
        };

        recognition.onend = () => {
            isListening = false;
            micButton.classList.remove('recording');
            micButton.querySelector('i').classList.add('fa-microphone');
            micButton.querySelector('i').classList.remove('fa-stop-circle');
            userInput.placeholder = "Ketik atau tekan mikrofon...";
        };

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            userInput.value = transcript;
            if (transcript.trim().length > 0) {
                sendMessage();
            }
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            if (event.error !== 'no-speech' && event.error !== 'aborted') {
                appendMessage("AI Asisten", "Gagal mengenali suara. Pastikan mikrofon berfungsi dan izin diberikan.", true);
            }
        };

        function toggleSpeechInput() {
            if (isListening) {
                recognition.stop();
            } else {
                recognition.start();
            }
        }
    } else {
        micButton.disabled = true;
        micButton.title = "Input Suara tidak didukung oleh browser Anda";
        micButton.style.opacity = 0.5;
    }

    // --- TEXT TO SPEECH (TTS) ---

    function stopTTS() {
        if (window.speechSynthesis && window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
            console.log("TTS dihentikan.");
        }
    }
    
    window.addEventListener('load', stopTTS);
    
    // ** IMPLEMENTASI beforeunload UNTUK MENGHENTIKAN TTS **
    window.addEventListener('beforeunload', () => {
        if (window.speechSynthesis) {
            window.speechSynthesis.cancel();
        }
    });

    function getVoices() {
        return new Promise(resolve => {
            let voices = window.speechSynthesis.getVoices();
            if (voices.length) {
                resolve(voices);
                return;
            }
            window.speechSynthesis.onvoiceschanged = () => {
                voices = window.speechSynthesis.getVoices();
                resolve(voices);
            };
        });
    }

    async function playAudioResponse(text) {
        if (!ttsToggle.checked || !window.speechSynthesis) return;

        stopTTS();
        
        try {
            const voices = await getVoices();
            
            const utterance = new SpeechSynthesisUtterance(text);
            
            utterance.lang = 'id-ID';
            utterance.pitch = 1.0;
            utterance.rate = 0.95; 

            // 1. Cari suara Google Bahasa Indonesia ('id-ID' DAN 'Google')
            let indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google"));
            
            // 2. Jika tidak ditemukan, cari suara Bahasa Indonesia ('id-ID' manapun)
            if (!indoVoice) {
                indoVoice = voices.find(v => v.lang === 'id-ID');
            }

            if (indoVoice) {
                utterance.voice = indoVoice;
            } else {
                console.warn("Suara Bahasa Indonesia tidak ditemukan, menggunakan suara default.");
                // Fallback terakhir: gunakan default system voice
                utterance.voice = voices.find(v => v.default); 
            }

            window.speechSynthesis.speak(utterance);

        } catch (error) {
            console.error("Kesalahan TTS saat memuat suara:", error);
        }
    }

    // --- CHAT MESSAGE & GEMINI API LOGIC ---

    function appendMessage(sender, message, isError = false) {
        const sanitizedMessage = DOMPurify.sanitize(message);
        
        const isUser = sender !== "AI Asisten";
        
        const containerClass = isUser ? 'flex justify-end' : 'flex justify-start'; 
        
        let bubbleClass = 'max-w-[80%] p-3 rounded-xl mb-3 shadow-md transition duration-200';
        
        if (isError) {
            bubbleClass = 'max-w-[80%] bg-red-100 text-red-800 p-3 rounded-xl mb-3 shadow-md border border-red-300';
        } else if (isUser) {
            bubbleClass += ' bg-accent-teal text-white rounded-br-none';
        } else {
            bubbleClass += ' bg-white text-gray-800 rounded-tl-none border border-gray-100';
        }

        let messageContent;
        if (isError) {
             messageContent = `<strong>${sender} (Error):</strong> ${sanitizedMessage.replace(/\n/g, '<br>')}`;
        } else if (isUser) {
             messageContent = `<span class="text-white">${sanitizedMessage}</span>`;
        } else {
             messageContent = `
                <strong class="font-bold text-sm text-primary-blue mr-1">AI Asisten:</strong> 
                <span class="text-gray-800">${sanitizedMessage}</span>
            `;
        }

        chatBox.innerHTML += `
            <div class="${containerClass}">
                <div class="${bubbleClass}">
                    ${messageContent}
                </div>
            </div>
        `;
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    async function sendMessage() {
        const input = userInput.value;
        if (!input.trim()) return;
        
        const projectType = getCurrentProjectType();
        if (projectType === "Proyek Kustom (Tidak Ditentukan)") {
            appendMessage("Kamu", input);
            appendMessage("AI Asisten", "Mohon tentukan **Jenis Proyek Kustom** Anda di kolom input di atas agar saya dapat memberikan panduan yang relevan.", true);
            return;
        }

        appendMessage("Kamu", input);
        userInput.value = "";
        userInput.disabled = true;
        sendButton.disabled = true;
        micButton.disabled = true;
        loadingIndicator.classList.remove("hidden");
        stopTTS();

        try {
            const response = await callGeminiAPI(input, projectType);
            appendMessage("AI Asisten", response.displayHtml);
            
            const cleanTextForSpeech = response.rawText.replace(/\*\*/g, '').replace(/\n/g, ' ').replace(/<[^>]*>/g, '').trim();
            playAudioResponse(cleanTextForSpeech); 

        } catch (error) {
            console.error("Kesalahan API:", error);
            appendMessage("AI Asisten", "Maaf, terjadi kesalahan saat menghubungi asisten AI. Silakan coba lagi. (" + error.message + ")", true);
        } finally {
            userInput.disabled = false;
            sendButton.disabled = false;
            micButton.disabled = false;
            loadingIndicator.classList.add("hidden");
            userInput.focus();
        }
    }

    userInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    async function callGeminiAPI(userText, projectType) {
        const apiKey = "<?php echo $apiKey; ?>"; 
        const model = "<?php echo $model; ?>";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;
        
        const systemPrompt = `Anda adalah AI Asisten Proyek yang ahli dan sangat membantu. Tugas Anda adalah memberikan saran, langkah-langkah, atau jawaban yang relevan dan mendetail dalam bahasa Indonesia, berdasarkan jenis proyek yang dipilih pengguna. Gunakan bahasa yang mudah dipahami oelh siswa. Respons harus menggunakan markdown untuk penekanan (seperti **tebal**) dan tidak menggunakan header markdown (misalnya '###'). Jawab pertanyaan pengguna dengan fokus pada konteks proyek yang telah dipilih.`;

        const userQuery = `Jenis Proyek yang sedang dikerjakan: ${projectType}. Pertanyaan/Instruksi pengguna: ${userText}`;

        const payload = {
            contents: [{ parts: [{ text: userQuery }] }],
            systemInstruction: {
                parts: [{ text: systemPrompt }]
            },
            tools: [{ "google_search": {} }], 
        };

        const MAX_RETRIES = 5;
        let attempt = 0;

        while (attempt < MAX_RETRIES) {
            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                const candidate = result.candidates?.[0];

                if (candidate && candidate.content?.parts?.[0]?.text) {
                    let rawText = candidate.content.parts[0].text;
                    
                    rawText = rawText.replace(/###\s?/g, ''); 
                    
                    let displayHtml = rawText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    
                    displayHtml = displayHtml.replace(/\n/g, '<br>');

                    let sources = [];
                    const groundingMetadata = candidate.groundingMetadata;

                    if (groundingMetadata && groundingMetadata.groundingAttributions) {
                        sources = groundingMetadata.groundingAttributions
                            .map(attribution => ({
                                uri: attribution.web?.uri,
                                title: attribution.web?.title,
                            }))
                            .filter(source => source.uri && source.title);
                    }

                    if (sources.length > 0) {
                        const citationList = sources.map((s, index) => 
                            `<a href="${s.uri}" target="_blank" class="text-blue-500 hover:underline font-medium">${s.title}</a>`
                        ).join(' | ');
                        
                        displayHtml += `<br><br><em class="text-xs text-gray-500 block mt-2 pt-2 border-t border-gray-200">Sumber: ${citationList}</em>`;
                    }
                    
                    return { rawText, displayHtml }; 

                } else {
                    return { rawText: "Model AI tidak memberikan respons yang valid. Coba ulangi pertanyaan.", displayHtml: "Model AI tidak memberikan respons yang valid. Coba ulangi pertanyaan." };
                }

            } catch (error) {
                attempt++;
                if (attempt >= MAX_RETRIES) {
                    throw new Error("Gagal terhubung dengan AI setelah beberapa kali percobaan.");
                }
                const delayMs = Math.pow(2, attempt) * 1000 + Math.random() * 1000;
                await delay(delayMs);
            }
        }
    }
</script>

</body>
</html>
