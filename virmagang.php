<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Skill Arena - Game Kompetitif SMK</title>
    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Konfigurasi Font Inter -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Kontainer Utama Aplikasi -->
    <div id="app-container" class="container mx-auto p-4 md:p-8 flex-grow">

        <!-- Header -->
        <header class="bg-white shadow-lg rounded-xl p-4 mb-6">
            <h1 class="text-3xl font-extrabold text-blue-800 text-center">
                <span class="text-indigo-600">AI</span> Skill Arena
            </h1>
            <p class="text-sm text-gray-600 text-center mt-1">Uji Keahlianmu, Raih Puncak Leaderboard!</p>
            <div id="user-info" class="text-center text-xs mt-2 text-gray-500">Memuat data pengguna...</div>
        </header>

        <!-- Main Content (Flex Layout for Arena and Leaderboard) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Arena & Skill Coach (2/3 width on large screen) -->
            <div id="arena-section" class="lg:col-span-2 space-y-6">

                <!-- Pemilihan Jurusan & Level -->
                <div id="setup-card" class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-indigo-500">
                    <h2 class="text-xl font-bold mb-4 text-gray-800">1. Siapkan Pertarunganmu</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="major-select" class="block text-sm font-medium text-gray-700 mb-1">Pilih Jurusan:</label>
                            <select id="major-select" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                                <option value="RPL (Rekayasa Perangkat Lunak)">RPL (Coding & Database)</option>
                                <option value="TKJ (Teknik Komputer Jaringan)">TKJ (Jaringan & Server)</option>
                                <option value="Multimedia (Desain Grafis & Video Editing)">Multimedia (Desain & Video)</option>
                                <option value="Teknik Instalasi Tenaga Listrik (TITL)">TITL (Wiring & Rangkaian)</option>
                                <option value="Akuntansi dan Keuangan Lembaga (AKL)">AKL (Akuntansi & Pembukuan)</option>
                            </select>
                        </div>
                        <div>
                            <label for="difficulty-select" class="block text-sm font-medium text-gray-700 mb-1">Pilih Level Kesulitan:</label>
                            <select id="difficulty-select" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                                <option value="Dasar (Kelas X)">Level Dasar (Kelas X)</option>
                                <option value="Menengah (Kelas XI)">Level Menengah (Kelas XI)</option>
                                <option value="Lanjutan (Kelas XII)">Level Lanjutan (Kelas XII)</option>
                            </select>
                        </div>
                    </div>
                    <button onclick="startChallenge()" id="start-btn" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300 ease-in-out shadow-md hover:shadow-lg transform hover:scale-[1.01]">
                        Mulai Tantangan AI Coach!
                    </button>
                </div>

                <!-- Arena Mini Game (Quiz) -->
                <div id="quiz-card" class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-emerald-500 hidden">
                    <h2 class="text-xl font-bold mb-4 text-emerald-700">2. Arena Pertarungan Skill</h2>
                    <div id="coach-message" class="bg-emerald-50 text-emerald-800 p-3 rounded-lg mb-4 text-sm font-medium">
                        <span class="font-bold">AI Coach:</span> Tantangan dimulai! Jawab semua 5 pertanyaan.
                    </div>

                    <div id="quiz-area" class="space-y-4">
                        <!-- Pertanyaan akan dimasukkan di sini oleh JS -->
                        <p class="text-center text-gray-500">Memuat pertanyaan dari AI Coach...</p>
                    </div>

                    <div id="quiz-navigation" class="mt-6 flex justify-between items-center">
                        <button onclick="previousQuestion()" id="prev-btn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-150" disabled>
                            &larr; Sebelumnya
                        </button>
                        <div id="question-counter" class="font-semibold text-gray-700">Pertanyaan 1/5</div>
                        <button onclick="nextQuestion()" id="next-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-150">
                            Lanjut &rarr;
                        </button>
                    </div>

                    <button onclick="submitQuiz()" id="submit-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg mt-6 hidden transition duration-300 ease-in-out">
                        Selesaikan Pertarungan!
                    </button>
                </div>
                
                <!-- Loading Indicator -->
                <div id="loading-indicator" class="bg-yellow-50 p-4 rounded-xl shadow-lg hidden text-center text-yellow-800 font-semibold">
                    <div class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        AI Coach sedang menyusun 5 pertanyaan tantangan skill... Mohon tunggu sebentar.
                    </div>
                </div>

                <!-- Hasil Pertarungan -->
                <div id="result-card" class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-pink-500 hidden">
                    <h2 class="text-xl font-bold mb-4 text-pink-700">3. Hasil Pertarungan</h2>
                    <div id="result-details" class="text-center space-y-2">
                        <!-- Hasil akan dimasukkan di sini oleh JS -->
                    </div>
                    <button onclick="resetApp()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg mt-6 transition duration-300 ease-in-out">
                        Mulai Tantangan Baru
                    </button>
                </div>

            </div>

            <!-- Leaderboard (1/3 width on large screen) -->
            <div id="leaderboard-section" class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-lg h-full border-t-4 border-purple-500">
                    <h2 class="text-xl font-bold mb-4 text-purple-700 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM5.388 5.644a1 1 0 00-1.414 1.414l.654.654a1 1 0 001.414-1.414l-.654-.654zM14.612 5.644a1 1 0 00-.654 1.414l.654.654a1 1 0 001.414-1.414l-.654-.654zM10 16a1 1 0 00-1 1v1a1 1 0 002 0v-1a1 1 0 00-1-1zM3 10a1 1 0 001 1h1a1 1 0 000-2H4a1 1 0 00-1 1zM15 10a1 1 0 001 1h1a1 1 0 000-2h-1a1 1 0 00-1 1zM7.5 13a2.5 2.5 0 105 0 2.5 2.5 0 00-5 0z" clip-rule="evenodd"></path></svg>
                        Leaderboard Skill Arena
                    </h2>
                    <p class="text-sm text-gray-600 mb-4">Poinku: <span id="user-score-display" class="font-bold text-indigo-600">0</span></p>
                    <div id="leaderboard-list" class="space-y-2 max-h-96 overflow-y-auto">
                        <p class="text-center text-sm text-gray-400">Memuat Leaderboard...</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Badge Prestasi</h3>
                        <div id="badge-display" class="flex flex-wrap gap-2 text-2xl">
                             <!-- Badge akan dimasukkan di sini oleh JS -->
                            <span title="Mulai Bertanding" class="p-2 bg-gray-200 rounded-full">🔰</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase SDKs -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        import { getFirestore, doc, setDoc, onSnapshot, collection, query, updateDoc, getDoc } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";
        import { setLogLevel } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        // setLogLevel('Debug'); // Aktifkan logging detail Firestore

        // Variabel Global dari Canvas
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-arena-app-id';
        const firebaseConfig = typeof __firebase_config !== 'undefined' ? JSON.parse(__firebase_config) : null;
        const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;
        
        // State Global Aplikasi
        let db, auth;
        window.quizData = [];
        window.currentQuestionIndex = 0;
        window.userAnswers = {}; // { qIndex: 'selectedOption' }
        window.userScore = 0;
        window.userId = null;
        window.userMajor = '';
        window.userBadges = [];

        // Konfigurasi API
        const API_KEY = "APIKEY";
        const GEMINI_MODEL = "gemini-2.5-flash-preview-05-20";
        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${API_KEY}`;
        
        // --- FIREBASE INITIALIZATION & AUTH ---

        if (firebaseConfig) {
            const app = initializeApp(firebaseConfig);
            db = getFirestore(app);
            auth = getAuth(app);
            window.db = db; // Attach to window for global access

            // 1. Authentication
            onAuthStateChanged(auth, async (user) => {
                if (user) {
                    window.userId = user.uid;
                } else {
                    // Sign in anonymously if no token is available
                    try {
                        if (initialAuthToken) {
                            await signInWithCustomToken(auth, initialAuthToken);
                        } else {
                            await signInAnonymously(auth);
                        }
                    } catch (error) {
                        console.error("Firebase Auth Gagal:", error);
                    }
                    return;
                }

                document.getElementById('user-info').textContent = `ID Pengguna (Bagikan!): ${window.userId}`;
                
                // 2. Load/Initialize User Data (Private collection for user's score/badges)
                const userDocRef = doc(db, `artifacts/${appId}/users/${window.userId}/profile`, 'stats');
                
                const userDocSnap = await getDoc(userDocRef);
                if (userDocSnap.exists()) {
                    const data = userDocSnap.data();
                    window.userScore = data.score || 0;
                    window.userMajor = data.major || '';
                    window.userBadges = data.badges || [];
                } else {
                    // Init user profile
                    await setDoc(userDocRef, { score: 0, major: '', badges: ['🔰'], lastUpdate: new Date() });
                }

                // Initial UI update and Leaderboard Listener setup
                updateUserUIDisplay();
                setupLeaderboardListener();
            });
        }

        // --- FIREBASE HELPER FUNCTIONS ---

        // Path ke data publik untuk Leaderboard
        const getPublicCollectionPath = () => `artifacts/${appId}/public/data/skillArenaScores`;

        // Update skor pengguna di Leaderboard publik
        window.updateLeaderboardScore = async (newScore, major, badges) => {
            if (!db || !window.userId) return;
            try {
                const docRef = doc(db, getPublicCollectionPath(), window.userId);
                
                // Ambil data skor saat ini
                const currentDocSnap = await getDoc(docRef);
                const currentMajor = currentDocSnap.exists() ? currentDocSnap.data().major : major;

                await setDoc(docRef, {
                    userId: window.userId,
                    score: newScore,
                    major: currentMajor || major, // Pertahankan major jika sudah ada
                    badges: badges,
                    lastUpdated: new Date()
                }, { merge: true });

                console.log("Skor Leaderboard berhasil diupdate.");

            } catch (e) {
                console.error("Error mengupdate skor Leaderboard:", e);
            }
        };

        // Listener Leaderboard real-time
        function setupLeaderboardListener() {
            if (!db) return;
            const q = collection(db, getPublicCollectionPath());
            
            onSnapshot(q, (snapshot) => {
                let leaderboard = [];
                snapshot.forEach((doc) => {
                    leaderboard.push(doc.data());
                });

                // Urutkan berdasarkan skor tertinggi
                leaderboard.sort((a, b) => b.score - a.score);
                
                renderLeaderboard(leaderboard);
            }, (error) => {
                console.error("Error mengambil Leaderboard:", error);
            });
        }

        // --- UI RENDERING & STATE MANAGEMENT ---

        function updateUserUIDisplay() {
            document.getElementById('user-score-display').textContent = window.userScore;
            renderBadges(window.userBadges);
        }

        function renderBadges(badges) {
            const badgeDisplay = document.getElementById('badge-display');
            badgeDisplay.innerHTML = '';
            const badgeMap = {
                '🔰': 'Mulai Bertanding',
                '⭐': 'Skor > 100',
                '🏆': 'Juara! (Skor > 500)',
                '💻': 'Jago RPL',
                '🌐': 'Master TKJ',
                '🎨': 'Pakar Multimedia',
            };

            badges.forEach(badge => {
                const span = document.createElement('span');
                span.title = badgeMap[badge] || badge;
                span.className = 'p-2 bg-purple-100 rounded-full text-purple-700 hover:bg-purple-200 transition duration-150 cursor-help';
                span.textContent = badge;
                badgeDisplay.appendChild(span);
            });
        }

        function renderLeaderboard(leaderboard) {
            const list = document.getElementById('leaderboard-list');
            list.innerHTML = '';
            
            leaderboard.forEach((data, index) => {
                const rank = index + 1;
                let rankStyle = 'text-gray-600';
                let rankIcon = '';

                if (rank === 1) {
                    rankStyle = 'text-yellow-600 font-extrabold';
                    rankIcon = '🥇';
                } else if (rank === 2) {
                    rankStyle = 'text-gray-500 font-extrabold';
                    rankIcon = '🥈';
                } else if (rank === 3) {
                    rankStyle = 'text-yellow-800 font-extrabold';
                    rankIcon = '🥉';
                }

                const isCurrentUser = data.userId === window.userId;
                const listItem = document.createElement('div');
                listItem.className = `p-3 rounded-lg flex justify-between items-center ${isCurrentUser ? 'bg-indigo-100 border-l-4 border-indigo-500' : 'bg-gray-50 hover:bg-gray-100'} transition duration-150`;
                
                listItem.innerHTML = `
                    <div class="flex items-center">
                        <span class="w-8 text-center text-lg ${rankStyle}">${rankIcon || rank}</span>
                        <div class="ml-2">
                            <p class="font-semibold text-sm ${isCurrentUser ? 'text-indigo-800' : 'text-gray-800'}">${isCurrentUser ? 'Anda' : 'Peserta-' + data.userId.substring(0, 4)}</p>
                            <p class="text-xs text-gray-500">${data.major || 'Umum'}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-lg text-green-600">${data.score}</span>
                        <div class="text-xs">${data.badges.join('')}</div>
                    </div>
                `;
                list.appendChild(listItem);
            });
            if (leaderboard.length === 0) {
                 list.innerHTML = '<p class="text-center text-sm text-gray-400">Belum ada peserta di Leaderboard.</p>';
            }
        }

        // --- GEMINI API (AI COACH) LOGIC ---

        // Fungsi untuk retry API call dengan exponential backoff
        async function fetchWithRetry(url, options, retries = 3, delay = 1000) {
            for (let i = 0; i < retries; i++) {
                try {
                    const response = await fetch(url, options);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response;
                } catch (error) {
                    if (i === retries - 1) throw error;
                    await new Promise(resolve => setTimeout(resolve, delay * Math.pow(2, i)));
                }
            }
        }

        window.startChallenge = async () => {
            const major = document.getElementById('major-select').value;
            const difficulty = document.getElementById('difficulty-select').value;
            window.userMajor = major.split('(')[0].trim(); // Ambil hanya nama jurusan (RPL, TKJ, dll.)

            // Tampilkan loading, sembunyikan setup
            document.getElementById('setup-card').classList.add('hidden');
            document.getElementById('loading-indicator').classList.remove('hidden');
            document.getElementById('quiz-card').classList.add('hidden');

            const systemPrompt = `Anda adalah seorang Pelatih Skill AI yang kompeten di bidang teknis SMK. Tugas Anda adalah menyusun 5 pertanyaan pilihan ganda yang akurat dan menantang untuk jurusan ${window.userMajor}. Setiap pertanyaan harus memiliki 4 opsi dan 1 jawaban benar. Jawaban harus terstruktur sebagai JSON.`;
            const userQuery = `Buatkan 5 pertanyaan pilihan ganda untuk Jurusan ${major} dengan tingkat kesulitan ${difficulty}. Pastikan setiap pertanyaan relevan dengan kurikulum teknis di SMK.`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "quiz": {
                                "type": "ARRAY",
                                "description": "List of 5 quiz questions for the selected major.",
                                "items": {
                                    "type": "OBJECT",
                                    "properties": {
                                        "question": { "type": "STRING", "description": "Pertanyaan teknis seputar jurusan." },
                                        "options": {
                                            "type": "ARRAY",
                                            "description": "Empat pilihan jawaban.",
                                            "items": { "type": "STRING" }
                                        },
                                        "correctAnswer": { "type": "STRING", "description": "Pilihan jawaban yang benar." },
                                        "points": { "type": "INTEGER", "description": "Poin yang didapat jika jawaban benar (e.g., 20)." }
                                    },
                                    "propertyOrdering": ["question", "options", "correctAnswer", "points"]
                                }
                            }
                        }
                    }
                }
            };

            try {
                const response = await fetchWithRetry(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                
                if (jsonText) {
                    const parsedJson = JSON.parse(jsonText);
                    window.quizData = parsedJson.quiz;
                    window.currentQuestionIndex = 0;
                    window.userAnswers = {};
                    
                    if (window.quizData && window.quizData.length > 0) {
                        // Reset UI state
                        document.getElementById('loading-indicator').classList.add('hidden');
                        document.getElementById('quiz-card').classList.remove('hidden');
                        document.getElementById('coach-message').innerHTML = `<span class="font-bold">AI Coach:</span> Bagus! Kamu memilih **${major}** dengan level **${difficulty}**. Tunjukkan keahlianmu!`;
                        renderQuestion();
                    } else {
                        throw new Error("API tidak menghasilkan data kuis yang valid.");
                    }
                } else {
                    throw new Error("Gagal mengambil respon dari Gemini API.");
                }

            } catch (error) {
                console.error("Kesalahan saat memanggil Gemini API:", error);
                document.getElementById('loading-indicator').classList.add('hidden');
                document.getElementById('setup-card').classList.remove('hidden');
                alert("Gagal memuat tantangan. Silakan coba lagi.");
            }
        };

        // --- GAME LOGIC ---

        function renderQuestion() {
            const q = window.quizData[window.currentQuestionIndex];
            if (!q) return;

            const quizArea = document.getElementById('quiz-area');
            quizArea.innerHTML = `
                <div class="p-4 bg-indigo-50 rounded-lg shadow-inner">
                    <p class="font-bold text-lg text-indigo-800 mb-3">Pertanyaan:</p>
                    <p class="text-gray-800">${q.question}</p>
                </div>
                <div class="mt-4 space-y-3" id="options-container">
                    ${q.options.map((option, index) => `
                        <label class="block">
                            <input type="radio" name="q-${window.currentQuestionIndex}" value="${option}" 
                                onchange="saveAnswer(${window.currentQuestionIndex}, '${option.replace(/'/g, "\\'")}')"
                                class="hidden peer" ${window.userAnswers[window.currentQuestionIndex] === option ? 'checked' : ''}>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-indigo-100 transition duration-150 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 font-medium">
                                ${String.fromCharCode(65 + index)}. ${option}
                            </div>
                        </label>
                    `).join('')}
                </div>
            `;
            
            // Update navigasi
            document.getElementById('question-counter').textContent = `Pertanyaan ${window.currentQuestionIndex + 1}/${window.quizData.length}`;
            
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const submitBtn = document.getElementById('submit-btn');

            prevBtn.disabled = window.currentQuestionIndex === 0;
            
            if (window.currentQuestionIndex === window.quizData.length - 1) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        window.saveAnswer = (qIndex, answer) => {
            // Unescape single quotes before saving
            window.userAnswers[qIndex] = answer.replace(/\\'/g, "'");
        };

        window.nextQuestion = () => {
            if (window.currentQuestionIndex < window.quizData.length - 1) {
                window.currentQuestionIndex++;
                renderQuestion();
            }
        };

        window.previousQuestion = () => {
            if (window.currentQuestionIndex > 0) {
                window.currentQuestionIndex--;
                renderQuestion();
            }
        };

        window.submitQuiz = async () => {
            // 1. Hitung Skor
            let newTotalScore = 0;
            let correctCount = 0;
            const majorCode = getMajorCode(window.userMajor);

            window.quizData.forEach((q, index) => {
                const userAnswer = window.userAnswers[index];
                if (userAnswer === q.correctAnswer) {
                    newTotalScore += q.points;
                    correctCount++;
                }
            });

            // 2. Update Skor Global
            window.userScore += newTotalScore;

            // 3. Cek Badge
            let currentBadges = [...window.userBadges];
            if (window.userScore > 100 && !currentBadges.includes('⭐')) {
                currentBadges.push('⭐');
            }
            if (window.userScore > 500 && !currentBadges.includes('🏆')) {
                currentBadges.push('🏆');
            }
            if (newTotalScore > 0 && majorCode && !currentBadges.includes(majorCode)) {
                currentBadges.push(majorCode);
            }
            window.userBadges = currentBadges;

            // 4. Render Hasil
            document.getElementById('quiz-card').classList.add('hidden');
            document.getElementById('result-card').classList.remove('hidden');

            const resultDetails = document.getElementById('result-details');
            resultDetails.innerHTML = `
                <p class="text-3xl font-extrabold text-green-600">${newTotalScore} Poin</p>
                <p class="text-xl font-semibold text-gray-700">Kamu menjawab ${correctCount} dari 5 pertanyaan dengan benar.</p>
                <p class="text-md text-gray-500">Total Poin Akumulasi: <span class="font-bold text-indigo-600">${window.userScore}</span></p>
                <p class="text-md text-gray-500">Badge Baru: ${newTotalScore > 0 ? (currentBadges.length > window.userBadges.length ? currentBadges.slice(-1) : 'Tidak ada') : 'Tidak ada'}</p>
                <div class="mt-4 bg-gray-100 p-3 rounded-lg text-sm">
                    <span class="font-bold text-indigo-700">Pesan AI Coach:</span> Selamat! Dengan skor ini, skill ${window.userMajor} kamu telah terbukti. Lanjutkan bertarung untuk mencapai rank tertinggi di Leaderboard!
                </div>
            `;
            
            // 5. Update Leaderboard & Private User Stats (Async)
            await window.updateLeaderboardScore(window.userScore, window.userMajor, window.userBadges);
            await savePrivateProfile(window.userScore, window.userMajor, window.userBadges);
            
            updateUserUIDisplay();
        };

        window.resetApp = () => {
            window.quizData = [];
            window.currentQuestionIndex = 0;
            window.userAnswers = {};
            document.getElementById('result-card').classList.add('hidden');
            document.getElementById('setup-card').classList.remove('hidden');
        };
        
        async function savePrivateProfile(score, major, badges) {
             if (!db || !window.userId) return;
             try {
                const userDocRef = doc(db, `artifacts/${appId}/users/${window.userId}/profile`, 'stats');
                await setDoc(userDocRef, { score, major, badges, lastUpdate: new Date() }, { merge: true });
                console.log("Profile user berhasil disimpan.");
             } catch (e) {
                console.error("Gagal menyimpan profile user:", e);
             }
        }
        
        function getMajorCode(majorName) {
            switch(majorName) {
                case 'RPL': return '💻';
                case 'TKJ': return '🌐';
                case 'Multimedia': return '🎨';
                default: return '';
            }
        }

        // Jalankan inisialisasi awal UI saat skrip dimuat
        window.onload = () => {
             // Pastikan UI menampilkan skor awal (sebelum Firebase load)
             updateUserUIDisplay();
        };
        
    </script>
</body>
</html>
