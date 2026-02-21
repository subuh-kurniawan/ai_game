<?php
// Baca konfigurasi API Key dari file JSON
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
    <title>Simulasi Wawancara UKG Guru SMK</title>
    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Menggunakan font Inter */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
        }
        /* Custom scrollbar untuk kotak chat */
        #chat-window {
            scrollbar-width: thin;
            scrollbar-color: #6366f1 #e5e7eb;
        }
        #chat-window::-webkit-scrollbar {
            width: 8px;
        }
        #chat-window::-webkit-scrollbar-thumb {
            background-color: #6366f1;
            border-radius: 10px;
        }
        #chat-window::-webkit-scrollbar-track {
            background-color: #e5e7eb;
        }
        .ai-message {
            background-color: #e0f2fe;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
        }
        .user-message {
            background-color: #f3f4f6;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            border-bottom-left-radius: 0.75rem;
        }
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#10b981',
                        'secondary': '#3b82f6',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Global Variables -->
    <script type="module">
        
        // --- Dynamic Context Variables (Variabel Konteks Simulasi) ---
        // Nilai default, akan ditimpa oleh localStorage atau input form
        let NAMA_GURU = "Budi Santoso";
        let NAMA_SEKOLAH = "SMK Teknik Mandiri";
        let MATA_PELAJARAN = "Teknik Komputer dan Jaringan";

        // --- Game State Variables ---
        // Ambil ID sesi aktif dari localStorage
        let sessionId = localStorage.getItem('active_interview_session') || null; 
        let chatHistory = [];
        let interviewTurns = []; // Local array to hold turns for final analysis
        let currentTurn = 0;
        
        const MAX_TURNS = 11; 
        let isWaitingForAI = false;

        // --- Gemini API Config ---
        const API_KEY = "<?php echo $apiKey; ?>";
        const MODEL_NAME = "gemini-2.5-flash-preview-09-2025";
        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${MODEL_NAME}:generateContent?key=${API_KEY}`;
        
        // Fungsi untuk membuat System Instruction dinamis
        function getSystemInstruction() {
            // System Instruction - Persona Pewawancara UKG SMK (Mode Hibrida)
            return `[KONTEKS WAWANCARA: Anda menguji guru bernama ${NAMA_GURU} dari ${NAMA_SEKOLAH} dengan spesialisasi Mata Pelajaran ${MATA_PELAJARAN}. Semua pertanyaan harus disesuaikan dengan konteks keahlian teknis/kejuruan ini.] Anda adalah seorang pewawancara profesional dan objektif untuk Uji Kompetensi Guru (UKG) jenjang Sekolah Menengah Kejuruan (SMK). Peran Anda adalah sebagai Game Master. Wawancara ini bertujuan mengukur kompetensi Pedagogik, Kepribadian, Sosial, dan Profesional calon guru SMK. Wawancara ini terdiri dari 10 pertanyaan bergilir yang SANGAT BERBOBOT dan terintegrasi dengan konteks situasi pembelajaran di kelas SMK. STRUKTUR RESPON AI: 1. Pertanyaan Awal (Turn 0) dan Pertanyaan 2-10: Berikan satu pertanyaan pada satu waktu, beserta 4 opsi jawaban (A, B, C, D) di mana hanya satu yang paling tepat. Opsi jawaban harus dipisahkan dari pertanyaan menggunakan garis \`---OPSI---\`. 2. Setelah Jawaban User (Turn 1-9): **HARUS** berikan umpan balik (evaluasi mendalam terhadap jawaban user) dan jelaskan alasannya. Bagian umpan balik ini **HARUS** diawali dengan \`---FEEDBACK_START---\` dan diakhiri dengan \`---FEEDBACK_END---\`. Setelah itu, **BARU** berikan pertanyaan berikutnya dengan format opsi jawaban yang sama. 3. Final (Turn 10/Last Turn): Berikan evaluasi akhir yang komprehensif, jangan berikan '---OPSI---', dan berikan tanda akhir \`---END_GAME---\`. Jaga nada bicara tetap formal dan profesional. Contoh format respon: 'Selamat datang... [Pertanyaan]... ---OPSI--- A. [Opsi A] B. [Opsi B] C. [Opsi C] D. [Opsi D]'`;
        }

        // --- Utility Function: Clean Markdown Formatting ---
        const cleanMarkdownFormatting = (t) => {
            if (!t) return '';
            // Hapus tanda bintang (**) atau underscore (__) yang sering digunakan AI untuk bold/italic
            return t
                .replace(/\*\*(.*?)\*\*/g, '$1') 
                .replace(/\*(.*?)\*/g, '$1')   
                .replace(/__(.*?)__/g, '$1')   
                .replace(/_(.*?)_/g, '$1')
                .replace(/^(#+)\s*/gm, ''); // Hapus simbol header
        };
        
        // --- Document Management (Local Storage) ---
        function createNewInterviewSession() {
            sessionId = 'interview_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
            localStorage.setItem('active_interview_session', sessionId);
            // Reset semua state saat sesi baru dibuat
            interviewTurns = []; 
            chatHistory = [];
            currentTurn = 0;
            saveFullStateToLocalStorage();
            console.log("New interview session created:", sessionId);
        }
        
        // ** FUNGSI BARU UNTUK MERESET STATE **
        function resetInterviewState() {
            if (sessionId) {
                localStorage.removeItem(sessionId); // Hapus data sesi spesifik
            }
            localStorage.removeItem('active_interview_session'); // Hapus ID sesi aktif
            console.log("Interview state reset. Reloading page...");
            window.location.reload(); // Muat ulang halaman untuk membersihkan state JS
        }
        
        // Assign fungsi reset ke window agar bisa dipanggil dari HTML
        window.resetInterviewState = resetInterviewState;


        function getInterviewData() {
            if (!sessionId) return null;
            const data = localStorage.getItem(sessionId);
            return data ? JSON.parse(data) : null;
        }

        // Fungsi untuk menyimpan seluruh state ke localStorage
        function saveFullStateToLocalStorage() {
            if (!sessionId) {
                createNewInterviewSession();
            }
            const data = {
                sessionId: sessionId,
                currentTurn: currentTurn,
                context: {
                    NAMA_GURU,
                    NAMA_SEKOLAH,
                    MATA_PELAJARAN
                },
                // Simpan riwayat chat dalam format yang mudah direkonstruksi
                history: chatHistory.map(item => ({
                    role: item.role,
                    text: item.parts[0].text
                })),
                turns: interviewTurns,
                status: currentTurn >= MAX_TURNS ? 'completed' : 'in-progress',
                lastUpdated: new Date().toISOString()
            };
            localStorage.setItem(sessionId, JSON.stringify(data));
            localStorage.setItem('active_interview_session', sessionId);
            console.log("Full state saved. Turn:", currentTurn);
        }

        // Fungsi untuk memuat status terakhir dari localStorage
        function loadFullStateFromLocalStorage() {
            const activeId = localStorage.getItem('active_interview_session');
            if (!activeId) {
                console.log("No active session found. Starting fresh.");
                return false;
            }

            sessionId = activeId;
            const data = getInterviewData();
            
            if (!data || data.status === 'completed') {
                console.log("Session completed or invalid. Starting fresh.");
                return false;
            }

            // Memuat konteks
            if (data.context) {
                NAMA_GURU = data.context.NAMA_GURU;
                NAMA_SEKOLAH = data.context.NAMA_SEKOLAH;
                MATA_PELAJARAN = data.context.MATA_PELAJARAN;
            }
            
            // Memuat state sesi
            currentTurn = data.currentTurn;
            interviewTurns = data.turns || [];
            
            // Merekonstruksi chatHistory dalam format yang dibutuhkan API
            chatHistory = (data.history || []).map(item => ({
                role: item.role,
                parts: [{ text: item.text }]
            }));
            
            // Render riwayat chat
            document.getElementById('chat-window').innerHTML = '';
            // Gunakan hanya pesan model terakhir untuk menampilkan opsi
            data.history.forEach((item, index) => {
                const showOptions = (index === data.history.length - 1 && item.role === 'model');
                displayMessage(item.text, item.role, showOptions); 
            });

            console.log(`Session loaded: ${sessionId}. Resuming at turn ${currentTurn}.`);
            
            // Perbarui input form dan tampilan konteks dengan nilai yang dimuat
            document.getElementById('input-nama').value = NAMA_GURU;
            document.getElementById('input-sekolah').value = NAMA_SEKOLAH;
            document.getElementById('input-mapel').value = MATA_PELAJARAN;
            initializeContextDisplay();
            
            return true;
        }

        // --- UI and Display Functions ---
        function showScreen(screenId) {
            document.getElementById('welcome-screen').classList.add('hidden');
            document.getElementById('interview-screen').classList.add('hidden');
            
            const screen = document.getElementById(screenId);
            if (screen) {
                screen.classList.remove('hidden');
            }
        }

        function handleOptionClick(optionText) {
            document.getElementById('message-input').value = optionText;
            sendMessage();
        }

        function renderOptionsDisplay(optionsText) {
            const optionsDisplay = document.getElementById('ai-options-display');
            const optionsContentDiv = document.getElementById('options-content');
            optionsDisplay.classList.remove('hidden');
            optionsContentDiv.innerHTML = ''; 

            const options = optionsText.split(/\s*(?=[A-D]\.\s)/).filter(o => o.trim() !== '');

            options.forEach(option => {
                const button = document.createElement('button');
                button.className = 'w-full text-left p-3 my-1 bg-white border border-secondary text-gray-800 font-medium rounded-lg shadow-sm hover:bg-secondary hover:text-white transition duration-200 ease-in-out transform hover:scale-[1.01]';
                
                const cleanedOption = cleanMarkdownFormatting(option.trim());
                button.innerHTML = cleanedOption.replace(/\n/g, '<br>'); 
                
                button.addEventListener('click', () => handleOptionClick(cleanedOption)); 
                optionsContentDiv.appendChild(button);
            });
        }

        // Tambahkan parameter showOptions untuk mengontrol rendering opsi saat memuat riwayat
        function displayMessage(text, role, showOptions = true) {
            const chatWindow = document.getElementById('chat-window');

            if (role === 'model') {
                
                // Hapus feedback internal dari tampilan jika ada, agar chat terlihat bersih
                const visibleText = text.replace(/---FEEDBACK_START---[\s\S]*?---FEEDBACK_END---/, '').trim();
                
                const parts = visibleText.split('---OPSI---');
                const mainText = parts[0];
                const optionsText = parts.length > 1 ? parts[1].trim() : null;

                const messageDiv = document.createElement('div');
                messageDiv.className = 'mb-4 flex justify-start';
                const contentDiv = document.createElement('div');
                contentDiv.className = `max-w-xs md:max-w-md p-3 shadow-lg rounded-xl text-gray-800 ai-message`;
                
                const roleSpan = document.createElement('span');
                roleSpan.className = 'block text-xs font-semibold mb-1 text-primary';
                roleSpan.textContent = 'Game Master (AI)';

                const textP = document.createElement('p');
                textP.innerHTML = cleanMarkdownFormatting(mainText).replace(/\n/g, '<br>');
                textP.classList.add('whitespace-pre-wrap');

                contentDiv.appendChild(roleSpan);
                contentDiv.appendChild(textP);
                messageDiv.appendChild(contentDiv);
                chatWindow.appendChild(messageDiv);

                // Hanya tampilkan opsi jika ini adalah pesan terbaru DAN showOptions adalah true
                const optionsDisplay = document.getElementById('ai-options-display');
                if (optionsText && showOptions) {
                    renderOptionsDisplay(optionsText);
                } else if (showOptions) {
                    // Sembunyikan jika tidak ada opsi (misalnya, pesan pembuka/penutup)
                    optionsDisplay.classList.add('hidden'); 
                }

            } else { // role === 'user'
                const messageDiv = document.createElement('div');
                messageDiv.className = 'mb-4 flex justify-end';
                const contentDiv = document.createElement('div');
                contentDiv.className = `max-w-xs md:max-w-md p-3 shadow-lg rounded-xl text-gray-800 user-message`;
                
                const roleSpan = document.createElement('span');
                roleSpan.className = 'block text-xs font-semibold mb-1 text-secondary';
                roleSpan.textContent = 'Anda';

                const textP = document.createElement('p');
                textP.innerHTML = cleanMarkdownFormatting(text).replace(/\n/g, '<br>');
                textP.classList.add('whitespace-pre-wrap');

                contentDiv.appendChild(roleSpan);
                contentDiv.appendChild(textP);
                messageDiv.appendChild(contentDiv);
                chatWindow.appendChild(messageDiv);
            }
            
            requestAnimationFrame(() => {
                chatWindow.scrollTop = chatWindow.scrollHeight;
            });
        }

        function setLoading(show) {
            const sendButton = document.getElementById('send-button');
            const loadingIndicator = document.getElementById('loading-indicator');
            const messageInput = document.getElementById('message-input');
            const optionsDisplay = document.getElementById('ai-options-display');
            const resetButton = document.getElementById('reset-button'); // Tambahkan tombol reset

            isWaitingForAI = show;
            
            if (sendButton) sendButton.disabled = show;
            if (messageInput) messageInput.disabled = show;
            if (resetButton) resetButton.disabled = show; // Disable reset saat AI bekerja
            optionsDisplay.style.pointerEvents = show ? 'none' : 'auto'; 
            optionsDisplay.style.opacity = show ? '0.5' : '1';

            if (show) {
                if (sendButton) {
                     sendButton.innerHTML = `
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>`;
                }
                loadingIndicator.classList.remove('hidden');
            } else {
                if (sendButton) {
                    sendButton.innerHTML = 'Kirim';
                }
                loadingIndicator.classList.add('hidden');
                document.getElementById('turn-counter').textContent = currentTurn;
            }
        }

        // Fungsi untuk menampilkan modal hasil akhir
        function showFinalAnalysisModal(title, content) {
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-content').innerHTML = content;
            document.getElementById('final-analysis-modal').classList.remove('hidden');
            // Hapus sesi aktif dari localStorage setelah wawancara selesai
            localStorage.removeItem('active_interview_session');
        }

        function hideFinalAnalysisModal() {
            document.getElementById('final-analysis-modal').classList.add('hidden');
        }
        
        // --- Gemini API Call Logic ---
        async function callGeminiAPI(history, maxRetries = 5) {
            const systemInstruction = getSystemInstruction(); // Dapatkan instruksi terbaru
            const payload = {
                contents: history,
                systemInstruction: {
                    parts: [{ text: systemInstruction }]
                },
            };

            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 429 && i < maxRetries - 1) {
                        const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    }

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    const candidate = result.candidates?.[0];

                    if (candidate && candidate.content?.parts?.[0]?.text) {
                        return candidate.content.parts[0].text;
                    } else {
                        throw new Error("No valid response from AI.");
                    }
                } catch (error) {
                    console.error("API Call Error:", error);
                    if (i === maxRetries - 1) {
                        throw new Error("Gagal terhubung ke AI setelah beberapa kali percobaan.");
                    }
                    const delay = Math.pow(2, i) * 1000 + Math.random() * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
        }

        // --- Final Analysis Logic ---
        async function analyzeAndDisplayFinalResult(finalAiResponse) {
            setLoading(true);
            
            // 1. Dapatkan semua umpan balik tersembunyi dari cache lokal (interviewTurns)
            let fullTranscript = "Transcript Wawancara UKG SMK:\n";
            interviewTurns.forEach(turn => {
                fullTranscript += `\n--- Giliran ${turn.turn} ---\n`;
                fullTranscript += `Pertanyaan AI: ${turn.question}\n`;
                fullTranscript += `Jawaban User: ${turn.userAnswer}\n`;
                fullTranscript += `Umpan Balik Internal AI (Dihilangkan dari Chat): ${turn.aiFeedback}\n`;
            });
            fullTranscript += "\n--- Akhir Wawancara ---\n";
            fullTranscript += finalAiResponse.replace('---END_GAME---', '').trim(); // Tambahkan pesan penutup AI

            // 2. Buat prompt baru untuk analisis akhir
            const finalPrompt = `Berdasarkan transkrip wawancara berikut, yang mencakup pertanyaan (Pertanyaan AI), jawaban user (Jawaban User), dan umpan balik internal AI (Umpan Balik Internal AI) yang berjumlah 10 pertanyaan, berikan analisis komprehensif (minimal 4 indikator utama mencakup Pedagogik, Profesional, Kepribadian, Sosial) dan tentukan skor kelayakan akhir sebagai Guru SMK dalam format **Layak/Kurang Layak/Tidak Layak** di bagian paling akhir. Pisahkan Skor Kelayakan dari teks analisa dengan baris ` + '`---SKOR_KELAYAKAN---`' + `.\n\n Transkrip:\n\n${fullTranscript}`;
            
            // 3. Panggil Gemini untuk Analisis Final
           const analysisHistory = [{ role: "user", parts: [{ text: finalPrompt }] }];

try {
    const analysisResponse = await callGeminiAPI(analysisHistory);
    
    // 4. Pisahkan Skor Kelayakan dan Analisis
    const parts = analysisResponse.split('---SKOR_KELAYAKAN---');
    let analysisText = parts[0].trim();
    let finalScore = parts.length > 1 ? parts[1].trim() : 'Skor tidak terdefinisi';

    // Bersihkan Analisis dan Skor dari Markdown
    analysisText = cleanMarkdownFormatting(analysisText);
    finalScore = cleanMarkdownFormatting(finalScore);

    // Buat konten modal
    let modalContentHTML = `<h3 class="text-2xl font-bold text-gray-800 mb-4">Skor Kelayakan: <span class="text-secondary">${finalScore}</span></h3>`;
    modalContentHTML += `<div class="bg-white p-4 rounded-lg shadow-inner mt-4 border border-gray-200">`;
    modalContentHTML += `<h4 class="text-xl font-semibold mb-2 text-primary">Analisis Komprehensif:</h4>`;
    modalContentHTML += `<p class="whitespace-pre-wrap">${analysisText.replace(/\n/g, '<br>')}</p>`;
    modalContentHTML += `</div>`;

    // --- Tambahkan form tersembunyi untuk insert ke DB ---
    modalContentHTML += `
      <form id="formAnalisis" style="display:none;">
          <input type="hidden" name="analysis_text" value="${encodeURIComponent(analysisText)}">
          <input type="hidden" name="final_score" value="${finalScore}">
         
     <input type="hidden" name="nama_guru" value="${NAMA_GURU}">
      </form>
    `;

    // Tampilkan Modal
    showFinalAnalysisModal('Hasil Evaluasi Wawancara UKG Guru SMK', modalContentHTML);

    // --- Submit form via AJAX agar otomatis tersimpan ---
    const formData = new FormData(document.getElementById('formAnalisis'));
    fetch('save_analysis.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        console.log('Hasil simpan analisis:', result);
    })
    .catch(err => console.error('Error simpan analisis:', err));

} catch (error) {
    // Tampilkan pesan kesalahan di chat
    displayMessage("Gagal menganalisis hasil akhir: " + error.message, 'model', false);
} finally {
    setLoading(false);
}
        }

        // --- Main Interview Flow ---
        async function startInterview(isResuming = false) {
            
            // 0. Ambil dan validasi input dari user
            const guruInput = document.getElementById('input-nama').value.trim();
            const sekolahInput = document.getElementById('input-sekolah').value.trim();
            const mapelInput = document.getElementById('input-mapel').value.trim();

            if (!isResuming && (!guruInput || !sekolahInput || !mapelInput)) {
                // Mengganti alert() dengan pesan di UI
                document.getElementById('input-error').classList.remove('hidden');
                document.getElementById('input-error').textContent = "Mohon isi semua data konteks guru (Nama, Sekolah, dan Mata Pelajaran) sebelum memulai sesi baru.";
                return;
            }
            document.getElementById('input-error').classList.add('hidden');

            // Jika sesi baru, set variabel global dengan nilai input user
            if (!isResuming) {
                NAMA_GURU = guruInput;
                NAMA_SEKOLAH = sekolahInput;
                MATA_PELAJARAN = mapelInput;
                createNewInterviewSession(); // Buat sesi baru
            }
            
            // Update display context once more
            initializeContextDisplay();
            
            showScreen('interview-screen');
            setLoading(true);

            if (isResuming) {
                console.log("Resuming interview...");
                // Jika melanjutkan, chat sudah terisi, tidak perlu prompt awal ke AI.
                // Pastikan opsi untuk pertanyaan terakhir ditampilkan (jika ada)
                const lastAiMessage = chatHistory[chatHistory.length - 1];
                if (lastAiMessage && lastAiMessage.role === 'model') {
                    displayMessage(lastAiMessage.parts[0].text, 'model', true);
                }
                setLoading(false);
                return;
            }

            // Jika sesi baru, kirim prompt awal (Turn 0)
            const initialPrompt = `Mulai wawancara UKG SMK. Berikan sapaan dan perkenalan yang menggunakan konteks guru ${NAMA_GURU}, ${NAMA_SEKOLAH}, ${MATA_PELAJARAN}. Setelah sapaan, berikan pertanyaan pertama (Turn 0).`;
            
            chatHistory.push({
                role: "user",
                parts: [{ text: initialPrompt }]
            });

            try {
                const aiResponse = await callGeminiAPI(chatHistory);
                
                currentTurn++;
                chatHistory.push({
                    role: "model",
                    parts: [{ text: aiResponse }]
                });
                displayMessage(aiResponse, 'model', true);
                saveFullStateToLocalStorage(); // Simpan state setelah giliran pertama
                
            } catch (error) {
                displayMessage("Terjadi kesalahan: " + error.message, 'model', false);
            } finally {
                setLoading(false);
            }
        }

        async function sendMessage() {
            if (isWaitingForAI || currentTurn >= MAX_TURNS) return;

            const inputElement = document.getElementById('message-input');
            const userMessage = inputElement.value.trim();

            if (!userMessage) return;
            
            const previousAiQuestion = chatHistory[chatHistory.length - 1]?.parts[0].text;
            
            displayMessage(userMessage, 'user', false);
            inputElement.value = '';

            document.getElementById('ai-options-display').classList.add('hidden');

            chatHistory.push({
                role: "user",
                parts: [{ text: userMessage }]
            });

            setLoading(true);
            try {
                const aiResponse = await callGeminiAPI(chatHistory);
                
                const feedbackStart = aiResponse.indexOf('---FEEDBACK_START---');
                const feedbackEnd = aiResponse.indexOf('---FEEDBACK_END---');
                const isEndGame = aiResponse.includes('---END_GAME---');
                
                let visibleContent = aiResponse;
                let hiddenFeedback = "";
                
                // 1. Ekstraksi Feedback
                if (feedbackStart > -1 && feedbackEnd > feedbackStart) {
                    hiddenFeedback = aiResponse.substring(feedbackStart + '---FEEDBACK_START---'.length, feedbackEnd).trim();
                } else {
                    hiddenFeedback = "Pesan evaluasi internal tidak terdeteksi atau ini adalah pesan pembuka.";
                }
                
                // 2. Log Turn Data
                // Turn Data menyimpan Q, A, dan Feedback (penting untuk analisis akhir)
                const turnData = {
                    turn: currentTurn,
                    // Ambil pertanyaan sebelum opsi
                    question: previousAiQuestion ? previousAiQuestion.split('---OPSI---')[0].trim() : 'Pertanyaan Awal',
                    userAnswer: userMessage,
                    aiFeedback: hiddenFeedback,
                    timestamp: new Date().toISOString()
                };
                interviewTurns.push(turnData);

                // 3. Tambahkan respon AI ke history
                chatHistory.push({
                    role: "model",
                    parts: [{ text: aiResponse }]
                });
                
                currentTurn++;

                if (isEndGame) { 
                    // Turn 10: Final evaluation.
                    // Tampilkan pesan penutup tanpa opsi
                    visibleContent = aiResponse.replace(/---FEEDBACK_START---[\s\S]*?---FEEDBACK_END---/, '').replace('---END_GAME---', '').trim();
                    displayMessage(visibleContent, 'model', false); 
                    
                    saveFullStateToLocalStorage();
                    analyzeAndDisplayFinalResult(visibleContent);
                    return; 
                } else {
                    // Turn 1-9: Normal question cycle
                    // Tampilkan respon (termasuk pertanyaan baru dan opsi)
                    displayMessage(aiResponse, 'model', true);
                    saveFullStateToLocalStorage();
                }


            } catch (error) {
                // Tampilkan pesan error dan simpan state terakhir (sebelum error)
                displayMessage("Terjadi kesalahan saat menghubungi pewawancara: " + error.message, 'model', false);
                saveFullStateToLocalStorage();
            } finally {
                setLoading(false);
            }
        }
        
        // Fungsi untuk menginisialisasi display konteks di Welcome Screen
        function initializeContextDisplay() {
            // Gunakan nilai input atau nilai variabel global
            document.getElementById('display-nama').textContent = document.getElementById('input-nama').value || NAMA_GURU;
            document.getElementById('display-sekolah').textContent = document.getElementById('input-sekolah').value || NAMA_SEKOLAH;
            document.getElementById('display-mapel').textContent = document.getElementById('input-mapel').value || MATA_PELAJARAN;
        }

        // --- Event Listeners ---
        document.addEventListener('DOMContentLoaded', () => {
            const inputElement = document.getElementById('message-input');
            const startButton = document.getElementById('start-button');
            const sendButton = document.getElementById('send-button');
            const closeModalButton = document.getElementById('close-modal-button');

            // 1. Coba Muat Sesi yang Sudah Ada
            const isSessionLoaded = loadFullStateFromLocalStorage();

            // 2. Perbarui Tampilan Kontainer
            initializeContextDisplay();

            // 3. Tentukan Screen yang ditampilkan
            if (isSessionLoaded) {
                 // Ubah tombol Start menjadi Lanjutkan
                startButton.textContent = "Lanjutkan Wawancara";
                startButton.classList.replace('bg-primary', 'bg-red-500');
                startButton.classList.replace('hover:bg-green-600', 'hover:bg-red-600');
                
                // Tambahkan pesan status melanjutkan
                const statusDiv = document.createElement('p');
                statusDiv.className = 'text-center text-lg font-bold text-red-600 mt-6 mb-2';
                statusDiv.textContent = `SESI DITEMUKAN: Melanjutkan dari giliran ke-${currentTurn}.`;
                startButton.parentNode.insertBefore(statusDiv, startButton); // Letakkan sebelum tombol start

            } else {
                // Hapus ID sesi aktif jika tidak ada atau selesai
                localStorage.removeItem('active_interview_session');
            }


            if (inputElement) {
                inputElement.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !isWaitingForAI) {
                        e.preventDefault(); 
                        sendMessage();
                    }
                });
            }
            
            if (startButton) {
                // Jika ada sesi yang dimuat, startInterview akan dipanggil dengan isResuming=true
                startButton.addEventListener('click', () => startInterview(isSessionLoaded));
            }
            if (sendButton) {
                sendButton.addEventListener('click', sendMessage);
            }
            if (closeModalButton) {
                closeModalButton.addEventListener('click', hideFinalAnalysisModal);
            }
            
            // Tambahkan event listener pada input untuk memperbarui tampilan konteks secara real-time
            document.getElementById('input-nama').addEventListener('input', initializeContextDisplay);
            document.getElementById('input-sekolah').addEventListener('input', initializeContextDisplay);
            document.getElementById('input-mapel').addEventListener('input', initializeContextDisplay);
            
            // Tampilkan screen berdasarkan apakah sesi dimuat atau tidak
            if (isSessionLoaded) {
                // Setelah DOM siap dan state dimuat, tampilkan layar wawancara
                // Namun, kita tetap menampilkan welcome screen dulu agar user bisa klik "Lanjutkan"
                showScreen('welcome-screen');
            } else {
                showScreen('welcome-screen');
            }
        });

    </script>

    <!-- Final Analysis Modal (Popup) -->
    <div id="final-analysis-modal" class="modal-overlay hidden">
        <div class="modal-content bg-white p-6 md:p-8 rounded-xl shadow-2xl transform transition-all duration-300">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h2 id="modal-title" class="text-3xl font-extrabold text-primary">Hasil Evaluasi</h2>
                <button id="close-modal-button" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div id="modal-content" class="text-gray-700">
                <!-- Analisis dan skor akan diinjeksi di sini -->
            </div>

            <div class="mt-6 pt-4 border-t text-center">
                <button 
                    onclick="hideFinalAnalysisModal()"
                    class="px-6 py-2 bg-secondary text-white font-semibold rounded-full hover:bg-blue-700 transition duration-300"
                >
                    Tutup
                </button>
                <button 
                    onclick="resetInterviewState()"
                    class="ml-3 px-6 py-2 bg-red-600 text-white font-semibold rounded-full hover:bg-red-700 transition duration-300"
                >
                    Mulai Ulang Sesi Baru
                </button>
            </div>
        </div>
    </div>

    <!-- Main Container Card -->
    <div class="w-full max-w-4xl bg-white shadow-2xl rounded-2xl overflow-hidden min-h-[80vh] flex flex-col">

        <!-- Welcome Screen -->
        <div id="welcome-screen" class="p-8 flex flex-col justify-center items-center h-full">
            <h1 class="text-4xl font-extrabold text-primary mb-3 text-center">Simulasi Wawancara UKG Guru SMK</h1>
            <p class="text-center text-gray-600 mb-8 text-xl font-medium">Pengujian Kompetensi: Pedagogik, Kepribadian, Sosial, & Profesional</p>
            
            <!-- INPUT FORM -->
            <div class="w-full max-w-xl p-6 bg-gray-50 rounded-lg shadow-inner border border-gray-200 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Atur Konteks Wawancara Anda
                </h3>
                
                <div class="space-y-3">
                    <div>
                        <label for="input-nama" class="block text-sm font-medium text-gray-700">Nama Guru (Simulasi)</label>
                        <input type="text" id="input-nama" value="Budi Santoso" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="input-sekolah" class="block text-sm font-medium text-gray-700">Nama Sekolah</label>
                        <input type="text" id="input-sekolah" value="SMK Teknik Mandiri" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="input-mapel" class="block text-sm font-medium text-gray-700">Mata Pelajaran (Kejuruan)</label>
                        <input type="text" id="input-mapel" value="" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    <p id="input-error" class="hidden text-sm font-semibold text-red-600"></p>
                </div>
            </div>
            
            <!-- CONTEXT DISPLAY CARD (Live Preview) -->
            <div class="w-full max-w-xl p-4 bg-yellow-50 rounded-lg shadow-inner border border-yellow-200 mb-6">
                <h3 class="text-lg font-bold text-yellow-700 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.942 3.313.842 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.942 1.543-.842 3.313-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.942-3.313-.842-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.942-1.543.842-3.313 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Konteks Simulasi Aktif:
                </h3>
                <p class="text-sm text-gray-700">Nama: <span class="font-semibold" id="display-nama"></span></p>
                <p class="text-sm text-gray-700">Sekolah: <span class="font-semibold" id="display-sekolah"></span></p>
                <p class="text-sm text-gray-700">Mata Pelajaran: <span class="font-semibold" id="display-mapel"></span></p>
            </div>

            <button 
                id="start-button" 
                class="mt-4 px-8 py-3 bg-primary text-white font-semibold text-lg rounded-full shadow-lg hover:bg-green-600 transition duration-300 transform hover:scale-105 flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.125l-3.25 3.25m3.25-3.25v1m0 0l-3.25-3.25m3.25 3.25v-1m0 0l-3.25-3.25"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21L3 21m18 0V3m0 18H3"></path></svg>
                Mulai Wawancara (10 Pertanyaan)
            </button>
            
             <button 
                onclick="resetInterviewState()"
                class="mt-4 px-6 py-2 bg-gray-300 text-gray-700 font-semibold text-sm rounded-full hover:bg-gray-400 transition duration-300 transform hover:scale-[1.02] flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2A9 9 0 115 12a9 9 0 0113 0z"></path></svg>
                Hapus & Mulai Sesi Baru
            </button>
        </div>

        <!-- Interview Screen (Chat Interface) -->
        <div id="interview-screen" class="flex flex-col h-full hidden">
            <!-- Header -->
            <div class="p-4 bg-primary text-white font-bold text-xl rounded-t-2xl shadow-md flex justify-between items-center">
                <span>Sesi UKG SMK: Game Master Interview (Giliran <span id="turn-counter">0</span>/10)</span>
                 <button 
                    id="reset-button"
                    onclick="resetInterviewState()"
                    class="ml-4 px-3 py-1 bg-red-500 text-white font-semibold text-sm rounded-full hover:bg-red-600 transition duration-300 transform hover:scale-105"
                >
                    Mulai Ulang Sesi
                </button>
            </div>

            <!-- Chat Window -->
            <div id="chat-window" class="flex-grow p-4 overflow-y-auto space-y-4">
                <!-- Messages will be injected here -->
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-gray-200 bg-white">
                
                <!-- Options Display Area (Now for Buttons) -->
                <div id="ai-options-display" class="mb-4 p-3 bg-blue-50 rounded-xl border border-blue-200 hidden shadow-inner transition duration-300">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Pilihan Jawaban (Klik salah satu di bawah atau ketik di kotak input):</p>
                    <div id="options-content" class="space-y-2">
                        <!-- Clickable Option Buttons will be injected here -->
                    </div>
                </div>
                
                <!-- Text Input and Send Button -->
                <div class="flex space-x-3">
                    <textarea 
                        id="message-input" 
                        placeholder="Ketik jawaban naratif atau opsi Anda di sini..." 
                        class="flex-grow p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary resize-none transition duration-150"
                        rows="1"
                    ></textarea>
                    <button 
                        id="send-button" 
                        class="w-28 bg-secondary text-white font-semibold rounded-xl hover:bg-blue-700 transition duration-300 shadow-md flex items-center justify-center p-3"
                    >
                        Kirim
                    </button>
                </div>
                 <div class="flex justify-center my-2">
                    <span id="loading-indicator" class="hidden text-sm text-gray-500 flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Game Master sedang memproses jawaban (dan menyimpan feedback tersembunyi)...
                    </span>
                </div>
            </div>
        </div>
        
    </div>
</body>
</html>
