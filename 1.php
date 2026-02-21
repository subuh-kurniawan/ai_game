
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizer & Chatbot Suara</title>
    
    <!-- Konfigurasi Tailwind untuk Dark Mode berbasis Class -->
    <script>
        tailwind = {
            config: {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            'light-bg': '#f3f4f6', 
                            'dark-bg': '#1a1a1a',  
                            'light-card': '#ffffff', 
                            'dark-card': '#1f2937',  
                        }
                    }
                }
            }
        };
    </script>
    
    <!-- PENTING: Muat CDN Tailwind (setelah konfigurasi di atas) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            transition: background-color 0.3s;
            background: 
        radial-gradient(circle at top left, #7f00ff 0%, transparent 50%),
        radial-gradient(circle at bottom right, #ff0080 0%, transparent 50%),
        radial-gradient(circle at top right, #00ffff 0%, transparent 50%),
        radial-gradient(circle at center, #1b0042 0%, #0d001f 50%, #02000a 100%);
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-color: #0d1117;
        }

        /* Styling Body berdasarkan Tema */
        body.light {
            background-color: var(--light-bg);
            color: #374151; /* Text dark in light mode */
        }
        body.dark {
            background-color: var(--dark-bg);
            color: #f0f0f0; /* Text light in dark mode */
        }
        
        .main-layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            height: 100%;
        }
        
        
        
        .text-neon {
            color: #ffcc00; /* Gold Neon */
            text-shadow: 0 0 8px #ffaa00, 0 0 16px #ffcc00;
            margin-bottom: 2rem;
        }
        
       /* --- APP WINDOW CONTAINER (Fullscreen) --- */
#appWindow {
    width: 100vw;       /* Lebar penuh layar */
    height: 100vh;      /* Tinggi penuh layar */
    
    background-color: rgba(30, 30, 30, 0.9);
    border-radius: 0;   /* Hilangkan radius agar pas tepi layar */
    box-shadow: none;   /* Hilangkan bayangan agar tampil natural fullscreen */
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);

    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: fixed;    /* Tetap menempel di layar */
    top: 0;
    left: 0;
    z-index: 9999;      /* Pastikan di atas elemen lain */
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}


        /* Tema Terang */
        .light #appWindow {
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        /* CANVAS BACKGROUND STYLE: Full-window background */
        #audioCanvas {
            position: absolute;
            top: 0; 
            left: 0;
            width: 100%;
            height: 100%; 
            z-index: 1; 
            opacity: 0.5; 
            display: none;
            border-radius: 16px;
        }

        /* Semua konten (header, history, input) harus di atas canvas */
        .window-header-area, #chatHistory, .input-area {
            position: relative;
            z-index: 2;
        }

        /* --- WINDOW HEADER AREA --- */
        .window-header-area {
            /* Default: Dark Mode */
            background-color: rgba(40, 40, 40, 0.6); 
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            flex-shrink: 0;
            display: flex;
            justify-content: space-between; 
            align-items: center;
            /* Adjust padding for mobile/desktop */
            padding: 0.75rem 1rem;
        }
        
        /* Light Mode Header */
        .light .window-header-area {
            background-color: rgba(240, 240, 240, 0.8);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .window-controls {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }
        .control-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            cursor: default;
        }
        .red { background-color: #ff605c; }
        .yellow { background-color: #ffbd44; }
        .green { background-color: #00ca4e; }
        
        #headerTitle {
            font-size: 0.9rem;
            font-weight: 600;
            /* Default: Dark Mode Text */
            color: #d1d5db; 
            text-align: center;
            flex-grow: 1;
            user-select: none;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
        }

        /* Light Mode Title Text */
        .light #headerTitle {
            color: #4b5563;
        }

        .mic-controls-group {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        
        #chatHistory {
            flex-grow: 1;
            /* Responsive Padding: Less padding on smaller screens */
            padding: 0.75rem 0.5rem; 
            overflow-y: auto;
            background-color: transparent; 
        }
        
        /* Input Area (Footer) */
        .input-area {
            display: flex;
            /* Responsive Padding: Less padding on smaller screens */
            padding: 0.75rem 0.5rem; 
            gap: 0.75rem; 
            /* Default: Dark Mode */
            border-top: 1px solid rgba(255, 255, 255, 0.15); 
            background-color: rgba(10, 10, 10, 0.8);
            flex-shrink: 0;
        }

        /* Light Mode Footer */
        .light .input-area {
            border-top: 1px solid rgba(0, 0, 0, 0.15);
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        /* Chat Message Styles */
        .message {
            margin-bottom: 0; 
            padding: 0.6rem 1rem; 
            border-radius: 14px; 
            /* Text size adjustment: small on mobile, base on desktop */
            font-size: 0.875rem; /* text-sm default */
            word-wrap: break-word; 
        }

        @media (min-width: 640px) { /* Equivalent to sm: breakpoint in Tailwind */
             .message {
                font-size: 1rem; /* text-base */
            }
        }
        
        /* User Message (Consistent across themes for branding) */
        .user-message {
            background-color: #4f4b3ba8; 
            color: #fff8e0; 
            border-bottom-right-radius: 4px;
        }

        /* Gemini Message (Changes based on theme) */
        .gemini-message {
            /* Default: Dark Mode */
            background-color: #333333b0; color: #f0f0f0;
            border-bottom-left-radius: 4px;
        }
        .light .gemini-message {
            background-color: #15e2e6ab; /* Light gray bubble */
            color: #1f2937; /* Dark text */
        }

        /* --- STYLING MARKDOWN YANG DIRAPIKAN --- */
        .gemini-message .chat-paragraph { /* New class for paragraphs */
            margin: 0;
            padding-bottom: 0.5rem; 
        }
        .gemini-message .chat-paragraph:last-of-type { /* Use last-of-type for better isolation */
            padding-bottom: 0; 
        }

        .gemini-message strong {
            font-weight: 800;
        }
        .gemini-message em {
            font-style: italic;
        }
        
        /* New Styles for Markdown Lists */
        .gemini-message ul, .gemini-message ol {
            margin: 0.5rem 0 0.75rem 0; /* Vertical margin to separate from paragraphs */
            padding-left: 1.5rem;
            list-style-position: outside;
            padding-bottom: 0 !important; 
        }
        .gemini-message li {
            margin-bottom: 0.25rem;
            line-height: 1.4;
        }
        .gemini-message li:last-child {
            margin-bottom: 0;
        }
        /* --- END STYLING MARKDOWN --- */
        
        /* Input Field */
        #userInput {
            border-radius: 10px; border: none;
            padding: 0.75rem 1rem; transition: box-shadow 0.2s, background-color 0.2s, color 0.2s;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.6); 
            /* Default: Dark Mode */
            background-color: #333333; color: white;
            /* Ensure it takes full width of the flexible container */
            width: 100%; 
        }

        /* Light Mode Input */
        .light #userInput {
            background-color: #f9fafb;
            color: #1f2937;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        #userInput:focus { 
            box-shadow: 0 0 0 2px #ffcc00; 
        }
        
        /* Button Styles (Mac style gradient and elevation) - NO THEME CHANGE FOR ACTION BUTTONS */
        #sendButton, #micVisualizerButton {
            background: linear-gradient(180deg, #9333ea 0%, #7e22ce 100%); 
            color: white; padding: 0.75rem 1.25rem; 
            border-radius: 12px; font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.2); 
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            flex-shrink: 0;
            /* Responsive font size for buttons */
            font-size: 0.875rem; /* text-sm default */
        }
        @media (min-width: 640px) { /* Equivalent to sm: breakpoint in Tailwind */
             #sendButton, #micVisualizerButton {
                font-size: 1rem; /* text-base */
            }
        }

        #sendButton:hover, #micVisualizerButton:hover {
            transform: translateY(-1px) scale(1.02);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.3);
            background: linear-gradient(180deg, #a855f7 0%, #9333ea 100%);
            opacity: 1; cursor: pointer;
        }
        #sendButton:disabled, #micVisualizerButton:disabled {
            background: #555; cursor: not-allowed; transform: none; box-shadow: none; opacity: 0.7; border-color: #333;
        }
        
        /* STATE STYLES BARU UNTUK micVisualizerButton */
        #micVisualizerButton.mic-listening {
            background: linear-gradient(180deg, #ffcc00 0%, #ffaa00 100%); 
        }
        #micVisualizerButton.mic-listening:hover {
            background: linear-gradient(180deg, #ffe066 0%, #ffcc00 100%);
        }

        #micVisualizerButton.mic-error {
            background: linear-gradient(180deg, #f43f5e 0%, #e11d48 100%); 
        }
        #micVisualizerButton.mic-error:hover {
            background: linear-gradient(180deg, #fb7185 0%, #f43f5e 100%);
        }

        /* --- STT BUTTON STYLE --- */
        .stt-control-button {
            /* Default: Gold Neon Default */
            background: linear-gradient(180deg, #ffcc00 0%, #ffaa00 100%); 
            color: white; 
            width: 40px; /* Reduced slightly for mobile */
            height: 40px; /* Reduced slightly for mobile */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5); 
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            flex-shrink: 0;
        }

        @media (min-width: 640px) { /* Equivalent to sm: breakpoint in Tailwind */
             .stt-control-button {
                width: 44px; /* Default size on desktop */
                height: 44px;
            }
        }

        .stt-control-button:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.6);
        }
        .stt-control-button.listening {
            background: linear-gradient(180deg, #34d399 0%, #10b981 100%); 
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.7); 
            animation: pulse 1.5s infinite;
        }
        .stt-control-button:disabled {
            background: #555; cursor: not-allowed; transform: none; box-shadow: none; opacity: 0.7; border-color: #333;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* --- THEME TOGGLE BUTTON --- */
        #themeToggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 8px;
            transition: color 0.2s, background-color 0.2s;
            /* Default: Dark Mode */
            color: #d1d5db; 
        }

        .light #themeToggle {
            color: #4b5563; 
        }

        #themeToggle:hover {
            /* Hover di Dark Mode */
            background-color: rgba(255, 255, 255, 0.1); 
        }
        .light #themeToggle:hover {
            /* Hover di Light Mode */
            background-color: rgba(0, 0, 0, 0.1); 
        }
        
        /* Message Box (Notification) */
        #messageBox {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 1.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 40px 0 rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            z-index: 10000;
            display: none; 
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
            position: relative; 
            transition: background-color 0.3s, border-color 0.3s;
            
            /* Default: Dark Mode */
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .light #messageBox {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.2);
            box-shadow: 0 8px 40px 0 rgba(0, 0, 0, 0.3);
        }

        .light #messageBox p {
            color: #1f2937;
        }

        /* Tombol Tutup/Close */
        #closeMessageBox {
            position: absolute;
            top: 0.5rem;
            right: 0.75rem;
            background: none;
            border: none;
            color: #9ca3af; 
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
            padding: 0.2rem;
        }
        #closeMessageBox:hover {
            color: #f3f4f6; 
        }
        
    </style>
</head>
<body class="dark"> <!-- Default class for body is dark -->

    <!-- APP WINDOW START (Full Screen) -->
    <div id="appWindow">
            <canvas id="audioCanvas"></canvas>    
                <!-- CANVAS -->


                <!-- HEADER AREA -->
                <div class="window-header-area">
                    <!-- Controls -->
                    <div class="window-controls">
                        <span class="control-dot red"></span>
                        <span class="control-dot yellow"></span>
                        <span class="control-dot green"></span>
                    </div>
                    
                    <!-- Title & Theme Toggle -->
                    <span id="headerTitle">
                        <button id="themeToggle" title="Ganti Tema">
                            <!-- Sun/Moon Icon Placeholder (Will be set by JS) -->
                            <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                        </button>
                        Aura Chat (TTS ID Aktif)
                    </span>
                    
                    <!-- Microphone/Visualizer Control -->
                    <div class="mic-controls-group"> 
                        <button id="micVisualizerButton" class="flex items-center gap-2">
                            <!-- Mic Icon (Lucide) -->
                            <svg id="micIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                            <span id="micButtonText"></span>
                        </button>
                    </div>
                </div>

                <!-- CHAT HISTORY -->
                <div id="chatHistory" class="flex flex-col">
                    <!-- Pesan awal akan di-render di sini oleh JavaScript -->
                </div>

                <!-- INPUT AREA -->
                <div class="input-area">
                    <input type="text" id="userInput" placeholder="Ketik pesan Anda..." class="flex-grow focus:outline-none">
                    
                    <!-- New STT Button -->
                    <button id="sttButton" class="stt-control-button" title="Input Suara Bahasa Indonesia">
                        <svg id="sttIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                    </button>
                    
                    <button id="sendButton" disabled>
                        Kirim
                    </button>
                </div>
            </div>
    </div>

    <!-- Message Box / Notification Modal -->
    <div id="messageBox" style="display: none;">
        <button id="closeMessageBox" aria-label="Tutup Notifikasi">&times;</button>
        <div id="messageBoxContent">
            <!-- Content Here -->
        </div>
    </div>

    <script>
        // --- KONFIGURASI GLOBAL & API ---
        const apiKey = "<?php echo $apiKey; ?>"; 
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;
        
        // Dikosongkan untuk menghindari error HTTP 400 (riwayat harus dimulai dengan 'user')
        let chatHistory = []; 
        let autoVoiceEnabled = true;

        // --- Variabel Visualizer ---
        let audioCtx;
        let analyser;
        let source;
        let dataArray;
        let bufferLength;
        let animationFrameId;
        let mediaStream = null;
        let isMicActive = false;

        // --- Variabel STT ---
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition;
        let isSttListening = false;


        // --- Elemen DOM ---
        const html = document.documentElement;
        const body = document.body;
        const appWindow = document.getElementById('appWindow');
        const canvas = document.getElementById('audioCanvas');
        const micVisualizerButton = document.getElementById('micVisualizerButton'); 
        const sttButton = document.getElementById('sttButton'); 
        const messageBox = document.getElementById('messageBox');
        const closeMessageBox = document.getElementById('closeMessageBox'); 
        const messageBoxContent = document.getElementById('messageBoxContent'); 
        const userInput = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        const chatHistoryDiv = document.getElementById('chatHistory');
        const ctx = canvas.getContext('2d');
        const micButtonText = document.getElementById('micButtonText'); 
        const themeToggle = document.getElementById('themeToggle'); 
        
        let micIcon = document.getElementById('micIcon');
        let sttIcon = document.getElementById('sttIcon');
        let themeIcon = document.getElementById('themeIcon');

        // --- FUNGSI TEMA ---
        function setIcon(element, iconHtml) {
            if (element) {
                element.outerHTML = iconHtml;
                return document.getElementById(element.id);
            }
            return null;
        }

        function toggleTheme() {
            // Memeriksa tema saat ini
            const isDark = body.classList.contains('dark');
            
            if (isDark) {
                // Beralih ke tema terang
                body.classList.remove('dark');
                body.classList.add('light');
                html.classList.remove('dark');
                html.classList.add('light');
                localStorage.setItem('theme', 'light');
                
                // Ganti ikon menjadi Bulan (Moon)
                themeIcon = setIcon(themeIcon, `<svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-moon"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>`);
            } else {
                // Beralih ke tema gelap
                body.classList.remove('light');
                body.classList.add('dark');
                html.classList.remove('light');
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                
                // Ganti ikon menjadi Matahari (Sun)
                themeIcon = setIcon(themeIcon, `<svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>`);
            }
        }
        
        themeToggle.addEventListener('click', toggleTheme);

        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                // Force switch to light mode initially if saved
                body.classList.remove('dark');
                body.classList.add('light');
                html.classList.remove('dark');
                html.classList.add('light');
                themeIcon.outerHTML = `<svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-moon"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>`;
            } else {
                 // Force switch to dark mode initially or use system preference if not set
                body.classList.remove('light');
                body.classList.add('dark');
                html.classList.remove('light');
                html.classList.add('dark');
                themeIcon.outerHTML = `<svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>‌`;
            }
            themeIcon = document.getElementById('themeIcon');
        }


        // --- FUNGSI UTILITY PESAN ---
        
        function hideMessageBox() {
            messageBox.style.display = 'none';
            closeMessageBox.style.display = 'block'; 
        }
        
        closeMessageBox.addEventListener('click', hideMessageBox);

        // --- FUNGSI UTILITY PENGGANTIAN IKON ---
        function replaceMicIcon(svgHtml) {
            if (micIcon) {
                micIcon.outerHTML = svgHtml;
                micIcon = document.getElementById('micIcon'); 
            }
        }

        function replaceSttIcon(svgHtml) {
            if (sttIcon) {
                sttIcon.outerHTML = svgHtml;
                sttIcon = document.getElementById('sttIcon'); 
            }
        }

        // FUNGSI BARU: Membersihkan teks dari markdown untuk TTS
        function stripMarkdown(markdown) {
            let cleanText = markdown;
            
            // 1. Hapus header: #, ##, ###, dst. diikuti spasi, dari awal baris (RegEx multiline)
            cleanText = cleanText.replace(/^#+\s+/gm, ''); 

            // 2. Hapus **bold** dan *italic* / _italic_
            cleanText = cleanText.replace(/(\*\*|__|\*|_)(.*?)\1/g, '$2');
            
            // 3. Hapus penanda daftar (list markers: *, -, 1., 2., etc.)
            cleanText = cleanText.replace(/^[*-]\s+|^(\d+\.)\s+/gm, ''); 
            
            // 4. Ganti dua newline atau lebih dengan spasi tunggal (untuk kelancaran pembacaan)
            cleanText = cleanText.replace(/\n{2,}/g, ' '); 
            
            // 5. Hapus newline tersisa (misalnya dalam list items) dan trim
            cleanText = cleanText.replace(/\n/g, ' ').trim(); 

            return cleanText;
        }

        
        // --- FUNGSI KONVERSI MARKDOWN SEDERHANA KE HTML (DIRAPIKAN) ---
        function markdownToHtml(markdown) {
            let html = markdown;

            // 1. Konversi bold dan italic
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
            html = html.replace(/\_(.*?)\_/g, '<em>$1</em>');
            
            // 2. Konversi Unordered Lists (*, -) - harus sebelum konversi paragraf
            // Cari blok yang diawali dengan list marker dan mungkin memiliki kelanjutan di baris berikutnya tanpa marker baru
            html = html.replace(/(\r?\n|^)([*-] [^\r\n]+(\r?\n[^\r\n]+)*)/g, (match, p1, p2) => {
                // p2 adalah blok list. Kita pisahkan berdasarkan list marker yang valid.
                const items = p2.split(/\r?\n(?=[*-] )/).map(item => item.trim());

                if (items.length === 0) return match;
                
                let listHtml = '<ul>';
                for (const item of items) {
                    const cleanItem = item.replace(/^[*-]\s*/, '').trim();
                    if (cleanItem) {
                        // Ganti baris baru tunggal di dalam list item dengan spasi (untuk list wrap)
                        listHtml += `<li>${cleanItem.replace(/\r?\n/g, ' ')}</li>`;
                    }
                }
                
                listHtml += '</ul>';
                // Bungkus dengan newlines ganda untuk memisahkannya sebagai blok dari teks di sekitarnya
                return '\n\n' + listHtml + '\n\n'; 
            });
            
            // 3. Konversi Ordered Lists (1.)
            html = html.replace(/(\r?\n|^)(\d+\. [^\r\n]+(\r?\n[^\r\n]+)*)/g, (match, p1, p2) => {
                 // Pisahkan berdasarkan nomor list marker
                const items = p2.split(/\r?\n(?=\d+\. )/).map(item => item.trim());

                if (items.length === 0) return match;
                
                let listHtml = '<ol>';
                for (const item of items) {
                    const cleanItem = item.replace(/^\d+\.\s*/, '').trim();
                    if (cleanItem) {
                        listHtml += `<li>${cleanItem.replace(/\r?\n/g, ' ')}</li>`;
                    }
                }
                
                listHtml += '</ol>';
                return '\n\n' + listHtml + '\n\n';
            });


            // 4. Konversi dua atau lebih baris baru menjadi pemisah paragraf (<p>)
            // Pisahkan konten berdasarkan blok (baris kosong)
            const blocks = html.split(/\n{2,}/);
            let finalHtml = blocks.map(block => {
                const trimmedBlock = block.trim();
                if (trimmedBlock === '') return '';
                
                // Jika blok ini sudah berupa list (ul/ol) atau elemen lain, jangan dibungkus <p>
                if (trimmedBlock.startsWith('<ul>') || trimmedBlock.startsWith('<ol>')) {
                    return trimmedBlock;
                }
                
                // Dalam blok paragraf, ganti baris tunggal (\n) dengan <br>
                const paragraphContent = trimmedBlock.replace(/\r?\n/g, '<br>'); 
                // Gunakan class "chat-paragraph"
                return `<p class="chat-paragraph">${paragraphContent}</p>`;
            }).join('');
            
            // 5. Cleanup akhir (hapus tag <p> yang mungkin kosong karena pemisahan)
            finalHtml = finalHtml.replace(/<p class="chat-paragraph"><\/p>/g, '');

            return finalHtml.trim();
        }


        // --- 1. VISUALIZER AUDIO (ADAPTIF TEMA) ---

        function resizeCanvas() {
            canvas.width = appWindow.clientWidth; 
            canvas.height = appWindow.clientHeight;
        }

        function setupAudioStream(stream) {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } else {
                audioCtx.resume();
            }

            analyser = audioCtx.createAnalyser();
            analyser.fftSize = 512; 
            analyser.smoothingTimeConstant = 0.4; 

            bufferLength = analyser.frequencyBinCount;
            dataArray = new Uint8Array(bufferLength);
            
            source = audioCtx.createMediaStreamSource(stream);
            source.connect(analyser);
        }

        // --- PARTIKEL ORB ---
        const particles = [];
        const numParticles = 120; 
        const baseOrbRadius = 80; 
        const particleBaseSize = 2; 

        function createOrbParticles() {
            particles.length = 0; 
            for (let i = 0; i < numParticles; i++) {
                const angle = (i / numParticles) * Math.PI * 2;
                particles.push({
                    baseX: baseOrbRadius * Math.cos(angle),
                    baseY: baseOrbRadius * Math.sin(angle),
                    angle: angle,
                    speed: (Math.random() - 0.5) * 0.03, 
                    size: particleBaseSize,
                    colorOffset: Math.random() * 360, 
                    opacity: 0.5 + Math.random() * 0.5, 
                });
            }
        }

        let hueShift = 0; 

        function drawVisualizer() {
            animationFrameId = requestAnimationFrame(drawVisualizer);

            analyser.getByteFrequencyData(dataArray);
            
            const isDark = body.classList.contains('dark');

            // 1. Efek Trail (jejak) - Warna trail menyesuaikan tema (opacity sangat rendah)
            const trailColor = isDark ? 'rgba(30, 30, 30, 0.1)' : 'rgba(240, 240, 240, 0.1)';
            ctx.fillStyle = trailColor; 
            ctx.fillRect(0, 0, canvas.width, canvas.height); 
            
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;

            // Dapatkan RMS (Root Mean Square) untuk volume keseluruhan
            let sumSquares = 0;
            for (const byte of dataArray) {
                sumSquares += (byte / 255) * (byte / 255);
            }
            const rms = Math.sqrt(sumSquares / bufferLength) * 1.5; // Multiplier untuk sensitivitas
            
            const pulsatingRadiusEffect = rms * 70; 
            const currentOrbRadius = baseOrbRadius + pulsatingRadiusEffect;

            hueShift = (hueShift + 0.8) % 360; 

            // 2. Pengaturan Warna Adaptif
            const particleSaturation = isDark ? 100 : 80; // Lebih jenuh di gelap
            const particleLightness = isDark ? 75 : 50;  // Lebih terang di gelap, lebih gelap di terang
            const particleBaseSizeAdapted = isDark ? particleBaseSize : particleBaseSize * 1.5;

            // Update dan gambar partikel
            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];

                const freqIndex = Math.floor(i / numParticles * bufferLength);
                const freqValue = dataArray[freqIndex] || 0;
                const freqOffset = (freqValue / 255) * 40; 

                p.angle += p.speed * (rms * 2 + 0.5); 
                const x = centerX + (currentOrbRadius + freqOffset) * Math.cos(p.angle);
                const y = centerY + (currentOrbRadius + freqOffset) * Math.sin(p.angle);
                
                const currentSize = particleBaseSizeAdapted + (freqValue / 255) * 4;
                const currentOpacity = p.opacity + (rms * 1.5);

                const particleHue = (hueShift + p.colorOffset + (freqValue * 0.5)) % 360;

                ctx.beginPath();
                ctx.arc(x, y, currentSize, 0, Math.PI * 2);
                
                // Warna Partikel Adaptif
                ctx.fillStyle = `hsla(${particleHue}, ${particleSaturation}%, ${particleLightness}%, ${currentOpacity})`;
                ctx.shadowColor = `hsla(${particleHue}, ${particleSaturation}%, ${particleLightness}%, 1)`;
                ctx.shadowBlur = currentSize * 5; 
                ctx.fill();
            }

            // 3. Gambar lingkaran inti yang berpedar di tengah orb
            ctx.shadowBlur = 0;
            const coreHue = (hueShift + 60) % 360;
            
            // Warna Inti Adaptif
            const coreLightness = isDark ? 80 : 40;
            const coreOpacity = 0.5 + rms * 1.5;

            ctx.fillStyle = `hsla(${coreHue}, 90%, ${coreLightness}%, ${coreOpacity})`; 
            ctx.beginPath();
            ctx.arc(centerX, centerY, baseOrbRadius * 0.3 + pulsatingRadiusEffect * 0.4, 0, Math.PI * 2);
            ctx.shadowColor = `hsla(${coreHue}, 90%, ${coreLightness}%, 1)`;
            ctx.shadowBlur = isDark ? 15 : 10; // Bayangan lebih halus di mode terang
            ctx.fill();


            ctx.shadowBlur = 0; 
        }
        
        function stopMicrophone() {
            if (isSttListening) {
                stopSpeechRecognition();
            }

            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
                mediaStream = null;
            }
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
            }
            if (audioCtx) {
                audioCtx.close().catch(e => console.error("Error closing audio context:", e));
                audioCtx = null;
            }
            
            canvas.style.display = 'none'; 
            
            micButtonText.textContent = '';
            micVisualizerButton.classList.remove('mic-listening', 'mic-error');
            micVisualizerButton.style.background = ''; 
            
            isMicActive = false;
        }

        micVisualizerButton.addEventListener('click', () => {
            hideMessageBox();

            if (isMicActive) {
                stopMicrophone();
                return;
            }

            micVisualizerButton.disabled = true;
            micButtonText.textContent = 'Meminta Akses...';
            resizeCanvas(); 

            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    mediaStream = stream; 
                    setupAudioStream(stream);

                    createOrbParticles(); 

                    if (animationFrameId) {
                        cancelAnimationFrame(animationFrameId);
                    }
                    drawVisualizer();
                    
                    canvas.style.display = 'block'; 
                    
                    micButtonText.textContent = '';
                    micVisualizerButton.disabled = false; 
                    
                    micVisualizerButton.classList.remove('mic-error');
                    micVisualizerButton.classList.add('mic-listening');
                    
                    sendButton.disabled = false;
                    userInput.disabled = false;
                    sttButton.disabled = false; 
                    userInput.focus();
                    isMicActive = true;
                })
                .catch(err => {
                    console.error('Gagal mendapatkan akses mikrofon:', err);
                    
                    messageBoxContent.innerHTML = `<p class="text-red-500 text-xl font-bold">Akses Mikrofon Ditolak</p>
                                                   <p class="text-sm text-gray-400">Pastikan Anda mengizinkan penggunaan mikrofon di browser, lalu klik 'Coba Lagi'.</p>`;
                    
                    messageBox.style.display = 'flex'; 
                    
                    micButtonText.textContent = 'Coba Lagi';
                    micVisualizerButton.disabled = false;
                    
                    micVisualizerButton.classList.remove('mic-listening');
                    micVisualizerButton.classList.add('mic-error');
                    
                    sttButton.disabled = true; 
                    canvas.style.display = 'none'; 
                    isMicActive = false;
                });
        });

        // --- 2. SPEECH-TO-TEXT (STT) INDONESIA ---

        function setupSpeechRecognition() {
            if (!SpeechRecognition) {
                 sttButton.disabled = true;
                 sttButton.classList.add('mic-error');
                 replaceSttIcon(`<svg id="sttIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic-off"><path d="M11 5a3 3 0 0 1 6 0v2"/><line x1="2" x2="22" y1="2" y2="22"/><path d="M9.364 9.364a3.86 3.86 0 0 0 1.272 4.414l-1.819-1.819a3.86 3.86 0 0 0-4.414-1.272"/><path d="M19 10v2a7 7 0 0 1-.96 3.7"/><path d="M17 12c0 .4-.04.8-.12 1.18"/><path d="M12 19v3"/><line x1="7" x2="17" y1="21" y2="21"/></svg>`);
                 console.error("Speech Recognition tidak didukung di browser ini.");
                 return;
            }

            recognition = new SpeechRecognition();
            recognition.lang = 'id-ID'; 
            recognition.interimResults = true; 
            recognition.continuous = false; 

            recognition.onstart = () => {
                isSttListening = true;
                sttButton.classList.add('listening');
                replaceSttIcon(`<svg id="sttIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><circle cx="12" cy="12" r="3" fill="white"/><path d="M19 10v2a7 7 0 0 1-14 0v-2" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" x2="12" y1="19" y2="22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`);
                userInput.placeholder = "Bicara sekarang...";
                userInput.focus();
            };

            recognition.onresult = (event) => {
                let interimTranscript = '';
                let finalTranscript = '';

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript;
                    } else {
                        interimTranscript += transcript;
                    }
                }

                userInput.value = finalTranscript || interimTranscript;
            };

            recognition.onend = () => {
                isSttListening = false;
                sttButton.classList.remove('listening');
                replaceSttIcon(`<svg id="sttIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>`);
                userInput.placeholder = "Ketik pesan Anda...";

                // AUTO SUBMIT
                if (userInput.value.trim() !== '') {
                    sendMessage(); 
                }
            };
            
            recognition.onerror = (event) => {
                isSttListening = false;
                sttButton.classList.remove('listening');
                replaceSttIcon(`<svg id="sttIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>`);
                userInput.placeholder = "Ketik pesan Anda...";

                if (event.error === 'no-speech') {
                    // Do nothing, just stop listening silently
                } else if (event.error === 'network' || event.error === 'not-allowed') {
                     messageBoxContent.innerHTML = `<p class="text-red-500 text-xl font-bold">Kesalahan Suara</p>
                                                   <p class="text-sm text-gray-400">Error: ${event.error}. Pastikan mikrofon Anda aktif dan browser memiliki izin.</p>`;
                     messageBox.style.display = 'flex';
                }
                console.error('Speech recognition error:', event.error);
            };
        }

        function startSpeechRecognition() {
            if (!recognition) return;

            if (isSttListening) {
                recognition.stop();
            } else {
                try {
                    recognition.start();
                } catch (e) {
                    console.warn("Recognition already started or error:", e);
                }
            }
        }

        function stopSpeechRecognition() {
             if (recognition && isSttListening) {
                recognition.stop();
             }
        }
        
        sttButton.addEventListener('click', startSpeechRecognition);

        // --- 3. TEXT-TO-SPEECH (TTS) INDONESIA ---

        function speakText(text) {
    if (!autoVoiceEnabled) return;
    if (!('speechSynthesis' in window)) return;

    // Bersihkan markdown agar TTS lebih natural
    const cleanText = stripMarkdown(text);
    window.speechSynthesis.cancel();

    function speakText(text) {
  if (!voiceEnabled && !autoVoiceEnabled) return;
  if (!('speechSynthesis' in window)) return;

  // Bersihkan teks dari karakter markdown agar TTS lebih natural
  const cleanText = text.replace(/([*_`~])/g, '');

  // Hentikan suara sebelumnya
  window.speechSynthesis.cancel();

  const utterance = new SpeechSynthesisUtterance(cleanText);
  utterance.lang = 'id-ID';
  utterance.pitch = 1.0;
  utterance.rate = 0.95;

  // Fungsi untuk memilih dan memutar suara
  const setVoiceAndSpeak = () => {
    const voices = window.speechSynthesis.getVoices();
    const indoVoice =
      voices.find(v => v.lang === 'id-ID' && v.name.includes('Google')) ||
      voices.find(v => v.lang === 'id-ID');

    if (indoVoice) utterance.voice = indoVoice;
    window.speechSynthesis.speak(utterance);
  };

  // Jika daftar suara belum siap, tunggu event 'voiceschanged'
  if (window.speechSynthesis.getVoices().length === 0) {
    window.speechSynthesis.onvoiceschanged = () => {
      setVoiceAndSpeak();
    };
  } else {
    setVoiceAndSpeak();
  }
}

function stopTTS() {
  if (window.speechSynthesis.speaking) window.speechSynthesis.cancel();
}

window.addEventListener('beforeunload', stopTTS);


    // Pastikan daftar suara sudah dimuat
    if (window.speechSynthesis.getVoices().length === 0) {
        window.speechSynthesis.onvoiceschanged = setVoiceAndSpeak;
    } else {
        setVoiceAndSpeak();
    }

    utterance.onstart = () => console.log("🔊 Membacakan teks...");
    utterance.onend = () => console.log("✅ Selesai membaca teks.");
    utterance.onerror = (e) => console.error("❌ TTS error:", e.error);

    // 🔇 Hentikan TTS otomatis ketika user meninggalkan halaman atau reload
    const stopTTS = () => {
        if (window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
            console.log("🛑 TTS dihentikan karena halaman ditinggalkan.");
        }
    };

    // Event untuk berbagai kondisi keluar halaman
    window.addEventListener('beforeunload', stopTTS);
    window.addEventListener('pagehide', stopTTS);
}


        // --- 4. CHATBOT GEMINI ---
        
        function renderMessage(role, text) {
            const messageRow = document.createElement('div');
            messageRow.classList.add('flex', 'w-full', 'mb-3', 'gap-3'); 

            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message');

            if (role === 'user') {
                messageRow.classList.add('justify-end');
                messageDiv.classList.add('user-message');
                // Untuk pesan pengguna, kita gunakan textContent (tidak perlu render markdown)
                messageDiv.textContent = text; 
                messageRow.appendChild(messageDiv);
                
            } else {
                messageRow.classList.add('justify-start');

                const avatarDiv = document.createElement('div');
avatarDiv.classList.add(
  'w-12', 'h-12', 'rounded-full', 'overflow-hidden', 
  'flex-shrink-0', 'shadow-lg', 'border', 'border-indigo-400'
);

// Ganti dengan URL gambar kamu
const avatarImg = document.createElement('img');
avatarImg.src = '../avatars/anak.jpeg'; // contoh path gambar
avatarImg.alt = 'Avatar Bot';
avatarImg.classList.add('w-full', 'h-full', 'object-cover');

avatarDiv.appendChild(avatarImg);


                messageDiv.classList.add('gemini-message');
                // Menggunakan innerHTML dan markdownToHtml untuk pesan model
                messageDiv.innerHTML = markdownToHtml(text); 

                messageRow.appendChild(avatarDiv);
                messageRow.appendChild(messageDiv);
            }

            chatHistoryDiv.appendChild(messageRow);
            chatHistoryDiv.scrollTop = chatHistoryDiv.scrollHeight;
        }

        
        async function sendMessage() {
            const userQuery = userInput.value.trim();
            if (!userQuery) return;
            
            // 1. Render pesan pengguna
            renderMessage('user', userQuery);
            userInput.value = '';
            sendButton.disabled = true;
            userInput.disabled = true;
            sttButton.disabled = true;
            
            // 2. Tambahkan pesan pengguna ke riwayat untuk API
            chatHistory.push({ role: "user", parts: [{ text: userQuery }] });
            
            // Tampilkan loading indicator
            const loadingRow = document.createElement('div');
            loadingRow.classList.add('flex', 'w-full', 'mb-3', 'gap-3', 'justify-start');

            const avatarDiv = document.createElement('div');
            avatarDiv.classList.add('w-8', 'h-8', 'rounded-full', 'bg-indigo-500', 'flex', 'items-center', 'justify-center', 'text-white', 'text-sm', 'font-bold', 'flex-shrink-0', 'shadow-lg');
            avatarDiv.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bot"><path d="M12 8V4"/><path d="M12 20v-4"/><path d="M4 12h4"/><path d="M16 12h4"/><rect width="18" height="18" x="3" y="3" rx="4"/></svg>‌`;

            const loadingDiv = document.createElement('div');
            loadingDiv.classList.add('message', 'gemini-message', 'loading');
            loadingDiv.innerHTML = '<p class="chat-paragraph">Mengetik...</p>';

            loadingRow.appendChild(avatarDiv);
            loadingRow.appendChild(loadingDiv);

            chatHistoryDiv.appendChild(loadingRow);
            chatHistoryDiv.scrollTop = chatHistoryDiv.scrollHeight;


            const payload = {
                contents: chatHistory,
                systemInstruction: {
                   parts: [
  {
    text: "Anda adalah AI bernama 'Aura'. Aura adalah asisten yang ramah, berpengetahuan luas, dan memberikan jawaban dengan gaya yang menyenangkan dan bersahabat. Tanggapi semua pertanyaan dalam Bahasa Indonesia."
  },
  {
    text: "Berikut referensinya:\n\n--------- Referensi ----\nNama Kepala Dinas Pendidikan Provinsi Lampung : Thomas Amirico, S.STP., M.H\n\n## NARASI PROGRAM KERJA PENDIDIKAN PROVINSI LAMPUNG 2025–2026\n\n**Judul Program:** 'Sekolah Lampung Unggul: Akses, Mutu, dan Daya Saing'\n\nDalam rangka mewujudkan sistem pendidikan menengah yang adil, transparan, dan berdaya saing, Pemerintah Provinsi Lampung melalui Dinas Pendidikan dan Kebudayaan meluncurkan serangkaian program strategis terintegrasi untuk periode 2025–2026. Program ini bertumpu pada visi Gubernur Lampung, Rahmat Mirzani Djausal, yakni membangun generasi muda yang cerdas, berakhlak, dan siap menghadapi tantangan global.\n\n### Pilar Utama Program:\n1. **Reformasi Sistem Penerimaan Siswa Baru (SPMB)** – Menggantikan PPDB berdasarkan Permendikdasmen No. 3 Tahun 2025 dan Keputusan Gubernur No. G/289/V.01/HK/2025.\n2. **Sekolah Unggulan & Program Kelas Cangkok** – Pemerataan akses pendidikan melalui 35 SMA Negeri unggulan dan beasiswa bagi siswa 3T.\n3. **SMK Vokasi Migran** – Menyiapkan tenaga kerja global melalui pelatihan bahasa, keterampilan, dan prosedur migrasi aman.\n4. **Kurikulum Teknologi & Kewirausahaan** – Kolaborasi dengan IIB Darmajaya dan Wadhwani Foundation untuk AI, coding, dan startup siswa.\n5. **Penghapusan Pungutan Sekolah & Dana CSR** – Menghapus uang komite, menggantinya dengan APBD dan dana CSR.\n6. **Reformasi Evaluasi Kepala Sekolah** – Berdasarkan indikator: kelulusan ke PTN, penyerapan kerja, dan jumlah wirausaha.\n\n### Langkah dan Tindak Lanjut:\n1. Penyusunan regulasi teknis pelaksanaan program.\n2. Sosialisasi dan pelatihan panitia SPMB serta pendampingan teknis.\n3. Pengawasan dan penindakan kecurangan.\n4. Penyediaan fasilitas pendukung dan asrama.\n5. Kolaborasi dengan Bank Lampung, Disnaker, BP3MI, IIB Darmajaya, dan CSR perusahaan.\n6. Evaluasi dan pelaporan berkala.\n7. Pengembangan program berkelanjutan.\n8. Pembinaan kepala sekolah.\n\n---\n\n## RINGKASAN ARTIKEL DAN BERITA TERKAIT\n\n### 1. **SPMB SMA/SMK Lampung 2025 Berjalan Sesuai Aturan** – *Fajar Sumatera, 18 Juni 2025*\n- SPMB menggantikan PPDB berdasarkan Permendikdasmen No. 3 Tahun 2025.\n- Kepala Disdikbud Thomas Amirico menegaskan pelaksanaan berjalan objektif dan transparan.\n- Penindakan tegas terhadap kecurangan dan hoaks.\n- Empat jalur penerimaan: Domisili, Afirmasi, Prestasi, Mutasi.\n- Seluruh pendaftaran gratis.\n\n### 2. **Peningkatan Mutu SMA Unggulan**\n- Disdikbud menargetkan 35 SMA Negeri unggulan di 15 kabupaten/kota.\n- Fokus pada pembentukan karakter, inovasi, dan kompetisi akademik.\n\n### 3. **Penghargaan Nasional PIP 2024**\n- Lampung raih *Terbaik 1 Nasional* untuk pengelolaan PIP jenjang menengah.\n- Diserahkan oleh Dirjen PAUD Dikdasmen kepada Thomas Amirico.\n- Disusul prestasi nasional oleh 3 sekolah dari Lampung.\n\n### 4. **Program Kelas Migran Vokasi**\n- Dimulai 2025/2026 di 5 SMK percontohan (2 Metro, 3 Bandar Lampung).\n- Kolaborasi Bank Lampung, Disnaker, BP3MI.\n- Pembiayaan ramah keluarga berpenghasilan rendah.\n\n### 5. **Program Kelas Cangkok (SMA 3T)**\n- Siswa 3T disekolahkan di SMA unggulan dengan biaya hidup ditanggung pemerintah.\n- Prioritas melanjutkan ke Unila/Itera.\n- Target awal 50 siswa.\n\n### 6. **Kolaborasi IIB Darmajaya & Disdik Lampung**\n- Kurikulum AI, coding, dan kewirausahaan untuk 5 sekolah percontohan.\n- Ajang Darmajaya Student Competition (DSC) dan pembentukan inkubator bisnis.\n\n### 7. **Kebijakan Gubernur Rahmat Mirzani Djausal**\n- Hapus pungutan komite sekolah.\n- Tambah mata pelajaran bahasa asing (Jepang, Korea, Arab).\n- Dorong CSR pendidikan.\n- Tiga indikator kinerja kepala sekolah: PTN, kerja, wirausaha.\n\n### 8. **Prestasi Kepala SMK Lampung di BBPPMPV BMTI Cimahi (2022)**\n- 34 kepala SMK Lampung ikuti Diklat Manajerial Nasional.\n- Nilai tertinggi: SMKN 1 Menggala (94,46 – sangat memuaskan).\n- Dominasi Lampung dalam daftar peringkat nasional.\n\n---\n\n**Kesimpulan:**\nProvinsi Lampung di bawah kepemimpinan Gubernur Rahmat Mirzani Djausal dan Kepala Disdikbud Thomas Amirico berhasil menyiapkan sistem pendidikan menengah yang inklusif, inovatif, dan berdaya saing. Program-program unggulan seperti SPMB, Kelas Cangkok, dan Kelas Migran menjadi simbol transformasi pendidikan menuju *Lampung Emas 2045*."
  }
]

                }
            };

            try {
                let response;
                
                // Implementasi Exponential Backoff
                for (let attempt = 0; attempt < 3; attempt++) {
                    response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (response.status !== 429) { 
                        break;
                    }
                    
                    const delay = Math.pow(2, attempt) * 1000;
                    await new Promise(resolve => setTimeout(resolve, delay));
                }

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                
                const candidate = result.candidates?.[0];
                const text = (candidate && candidate.content?.parts?.[0]?.text) ? candidate.content.parts[0].text : "Maaf, saya tidak dapat menghasilkan respons saat ini.";

                // Hapus loading indicator
                chatHistoryDiv.removeChild(loadingRow);

                // Render pesan model
                renderMessage('model', text);
                
                // PENTING: Panggil stripMarkdown sebelum TTS
                speakText(stripMarkdown(text)); 
                
                // Tambahkan pesan model ke riwayat
                chatHistory.push({ role: "model", parts: [{ text: text }] });
                
            } catch (error) {
                console.error("Kesalahan saat memanggil Gemini API:", error);
                // Hapus loading indicator dan tampilkan pesan error
                if (chatHistoryDiv.contains(loadingRow)) {
                    chatHistoryDiv.removeChild(loadingRow);
                }
                renderMessage('model', `Terjadi kesalahan jaringan atau API. (${error.message}). Coba lagi.`);
            } finally {
                sendButton.disabled = false;
                userInput.disabled = false;
                sttButton.disabled = false;
                userInput.focus();
            }
        }
        
        sendButton.addEventListener('click', sendMessage);
        userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !sendButton.disabled) {
                sendMessage();
            }
        });
        
        // --- INISIALISASI UTAMA (Setelah DOM dimuat) ---
        window.addEventListener('resize', resizeCanvas);
        window.onload = () => {
             initializeTheme(); // Inisialisasi Tema
             resizeCanvas(); 
             setupSpeechRecognition();

             // Pesan sambutan awal (di-render via JS untuk konsistensi markdownToHtml)
             const initialGreeting = "Hai! Saya **Aura**, Asisten AI yang siap membantu Anda. Tanyakan apa saja! Saya sekarang dapat menampilkan daftar berpoin/bernomor dengan lebih rapi.\n\nContoh:\n* Sebutkan nama-nama planet\n* Apa itu fisika kuantum?";
             renderMessage('model', initialGreeting);

             if (!('speechSynthesis' in window)) {
                micButtonText.textContent = 'TTS Mati';
                micVisualizerButton.classList.add('mic-error');
                replaceMicIcon(`<svg id="micIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-volume-x"><path d="M11 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/><path d="M15.54 10.46A4.985 4.985 0 0 0 17 12c0 .4-.04.8-.12 1.18"/><path d="M20.42 8.42A9.267 9.267 0 0 1 21 12c0 1.34-.36 2.6-.96 3.7"/><path d="m2 2 20 20"/></svg>`);
             }
             
             sttButton.disabled = true;
        };
       
    </script>
</body>
</html>

