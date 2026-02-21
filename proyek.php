<?php
include "../admin/fungsi/koneksi.php";
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
    <title>Monopoli Edukasi Softskill (Siswa/Guru Mode)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module">
         const sekolah = <?php echo json_encode($sekolah); ?>;
        // Gemini API Setup
        const API_KEY = "<?php echo $apiKey; ?>"; // Kunci API disediakan oleh lingkungan
        const md = "<?php echo $model; ?>";
        const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/${md}:generateContent?key=${API_KEY}`;

        // Game Constants
        const SKILLS = ['Kolaborasi', 'Disiplin', 'Kreativitas', 'Inisiatif', 'Tanggung Jawab', 'Ketahanan'];
        
        // Definisikan tema SMK
        const SMK_THEMES = {
    'general': 'Umum (Sekolah)',
    // Teknik & Industri
    'teknik_mesin': 'Teknik Mesin/Industri',
    'tkr': 'Teknik Kendaraan Ringan/Otomotif',
    'elektro': 'Teknik Elektro/Listrik',
    'tkj': 'Teknik Komputer & Jaringan',
    'rpl': 'Rekayasa Perangkat Lunak/IT',
    'multimedia': 'Multimedia/Desain Grafis',
    'kriya_industri': 'Kriya Industri/Kerajinan',
    // Bisnis & Manajemen
    'akuntansi': 'Akuntansi/Keuangan',
    'pemasaran': 'Pemasaran/Bisnis',
    'administrasi': 'Administrasi Perkantoran',
    // Pariwisata & Kuliner
    'tata_boga': 'Tata Boga/Pariwisata',
    'perhotelan': 'Perhotelan/Pariwisata',
    // Kesehatan & Farmasi
    'farmasi': 'Farmasi/Kesehatan',
    'keperawatan': 'Keperawatan/Kesehatan',
    'kebidanan': 'Kebidanan/Kesehatan',
    // Pertanian & Perikanan
    'agribisnis': 'Agribisnis/Pertanian',
    'atph': 'ATPH (Pertanian)',
    'perikanan': 'Perikanan',
    // Lain-lain
    'otomotif': 'Otomotif/Kendaraan',
    'desain_interior': 'Desain Interior/Arsitektur',
    'teknik_gambar_bangunan': 'Teknik Gambar Bangunan/Arsitektur',
};


        // Level Kesulitan
        const GAME_LEVELS = {
            'SMP': 'Sederhana (Dasar)',
            'SMA': 'Formal (Menengah)',
            'PROF': 'Profesional (Tingkat Tinggi)'
        };

        const PLAYER_NAME = "Pemain Tunggal"; 
        const STORAGE_KEY = 'softskillMonopolyGame'; // Kunci untuk localStorage

        // Board Definition (20 squares)
        const BOARD = [
            { name: "START / MULAI", type: "start", skill: null, color: "bg-green-600", icon: "✨" }, // 0
            { name: "Tugas Kelompok", type: "skill", skill: "Kolaborasi", color: "bg-blue-300", icon: "🤝" }, // 1
            { name: "Ide Baru", type: "skill", skill: "Kreativitas", color: "bg-yellow-300", icon: "💡" }, // 2
            { name: "Deadline Proyek", type: "skill", skill: "Disiplin", color: "bg-red-300", icon: "⏱️" }, // 3
            { name: "STOP: Istirahat", type: "free", skill: null, color: "bg-gray-500", icon: "☕" }, // 4
            { name: "Presentasi Dadakan", type: "skill", skill: "Inisiatif", color: "bg-purple-300", icon: "🗣️" }, // 5
            { name: "Kesalahan Fatal", type: "skill", skill: "Tanggung Jawab", color: "bg-red-500", icon: "🚨" }, // 6
            { name: "Tantangan Baru", type: "skill", skill: "Kreativitas", color: "bg-yellow-300", icon: "🎨" }, // 7
            { name: "Konflik Tim", type: "skill", skill: "Kolaborasi", color: "bg-blue-500", icon: "🥊" }, // 8
            { name: "Kritik Pedas", type: "skill", skill: "Tanggung Jawab", color: "bg-red-300", icon: "📝" }, // 9
            { name: "STOP: Perpustakaan", type: "free", skill: null, color: "bg-gray-500", icon: "📚" }, // 10
            { name: "Tugas yang Membosankan", type: "skill", skill: "Disiplin", color: "bg-red-500", icon: "⚙️" }, // 11
            { name: "Melihat Sampah", type: "skill", skill: "Inisiatif", color: "bg-purple-500", icon: "🗑️" }, // 12
            { name: "Bantuan Teman", type: "skill", skill: "Kolaborasi", color: "bg-blue-300", icon: "🫂" }, // 13
            { name: "Pilih Topik", type: "skill", skill: "Kreativitas", color: "bg-yellow-500", icon: "🔍" }, // 14
            { name: "STOP: Kantin", type: "free", skill: null, color: "bg-gray-500", icon: "🍔" }, // 15
            { name: "Janji Terlambat", type: "skill", skill: "Disiplin", color: "bg-red-300", icon: "⏳" }, // 16
            { name: "Ide Ditolak", type: "skill", skill: "Ketahanan", color: "bg-orange-300", icon: "🛡️" }, // 17
            { name: "Penggalangan Dana", type: "skill", skill: "Inisiatif", color: "bg-purple-300", icon: "📢" }, // 18
            { name: "Tugas Berat", type: "skill", skill: "Tanggung Jawab", color: "bg-red-500", icon: "🏋️" }, // 19
        ];
        
        // Status awal game default
        const defaultGameState = {
            position: 0,
            scores: Object.fromEntries(SKILLS.map(s => [s, 0])),
            isRolling: false,
            currentQuestion: null,
            lastRoll: 0,
            theme: null,
            level: 'SMA', 
            userRole: 'Siswa', // NEW: Default role is 'Siswa'
            message: "Selamat datang! Pilih peran, tema, dan level Anda untuk memulai perjalanan softskill.",
            roundsCompleted: 0,
            maxRounds: 4,       // Target putaran sebelum game berakhir
            totalQuestions: 0,
            correctAnswers: 0,
            gameStatus: 'menu', // 'menu', 'playing', 'finished'
            finalReview: null, // Menyimpan teks ulasan akhir
        };

        // State yang akan diupdate selama permainan
        let gameState = {...defaultGameState};
        
        // --- Utility Functions ---
        function showMessage(msg, isError = false) {
            const messageEl = document.getElementById('game-message');
            messageEl.textContent = msg;
            messageEl.className = `text-lg font-semibold p-3 md:p-4 rounded-xl shadow-lg transition-all duration-300 transform-gpu ${isError ? 'bg-red-100 text-red-700 border-l-4 border-red-500' : 'bg-white text-gray-800 border-l-4 border-blue-500'}`;
        }

        /**
         * Menampilkan pesan konfirmasi kustom.
         */
        function showCustomConfirm(message) {
            return new Promise(resolve => {
                const modal = document.getElementById('custom-modal');
                const modalMessage = document.getElementById('modal-message');
                const confirmButton = document.getElementById('confirm-button');
                const cancelButton = document.getElementById('cancel-button');

                modalMessage.textContent = message;
                modal.classList.remove('hidden');

                const handleConfirm = () => {
                    modal.classList.add('hidden');
                    confirmButton.removeEventListener('click', handleConfirm);
                    cancelButton.removeEventListener('click', handleCancel);
                    resolve(true);
                };

                const handleCancel = () => {
                    modal.classList.add('hidden');
                    confirmButton.removeEventListener('click', handleConfirm);
                    cancelButton.removeEventListener('click', handleCancel);
                    resolve(false);
                };

                confirmButton.addEventListener('click', handleConfirm);
                cancelButton.addEventListener('click', handleCancel);
            });
        }
        
        // --- TTS Functions (Web Speech API) ---
        let currentUtterance = null;
        let isSpeaking = false;

        function speakText(text) {
            window.speechSynthesis.cancel();
            
            if (!('speechSynthesis' in window)) {
                showMessage("TTS tidak didukung di browser ini.", true);
                return;
            }

            const cleanText = text.replace(/\*\*(.*?)\*\*/g, '$1').replace(/#/g, ''); // Clean markdown
            currentUtterance = new SpeechSynthesisUtterance(cleanText);
            
            currentUtterance.lang = 'id-ID';
            currentUtterance.pitch = 1.0;
            currentUtterance.rate = 0.95;

            const setVoiceAndSpeak = () => {
                const voices = window.speechSynthesis.getVoices();
                const indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google")) 
                                 || voices.find(v => v.lang === 'id-ID');
                
                if (indoVoice) {
                    currentUtterance.voice = indoVoice;
                }
                
                window.speechSynthesis.speak(currentUtterance);
                isSpeaking = true;
                updateTtsButtons(true); 
                
                currentUtterance.onend = () => {
                    isSpeaking = false;
                    updateTtsButtons(false); 
                };
            };

            if (window.speechSynthesis.getVoices().length > 0) {
                setVoiceAndSpeak();
            } else {
                window.speechSynthesis.onvoiceschanged = () => {
                    setVoiceAndSpeak();
                    window.speechSynthesis.onvoiceschanged = null; 
                };
            }
        }

        function stopSpeaking() {
            window.speechSynthesis.cancel();
            isSpeaking = false;
            updateTtsButtons(false); 
        }

        function updateTtsButtons(speaking) {
            const playButton = document.getElementById('tts-play-button');
            const stopButton = document.getElementById('tts-stop-button');
            if (playButton && stopButton) {
                if (speaking) {
                    playButton.classList.add('hidden');
                    stopButton.classList.remove('hidden');
                } else {
                    playButton.classList.remove('hidden');
                    stopButton.classList.add('hidden');
                }
            }
        }
        window.stopSpeaking = stopSpeaking; 
        window.speakText = speakText;
        
        // --- LocalStorage Functions ---
        function loadGameStateFromLocalStorage() {
            try {
                const storedState = localStorage.getItem(STORAGE_KEY);
                if (storedState) {
                    const loadedState = JSON.parse(storedState);
                    
                    // Pastikan semua properti yang baru ditambahkan ada
                    const mergedState = {...defaultGameState, ...loadedState};
                    
                    // Cleanup checks
                    mergedState.scores = mergedState.scores || {};
                    SKILLS.forEach(skill => {
                         if (mergedState.scores[skill] === undefined) {
                             mergedState.scores[skill] = 0;
                         }
                    });
                    
                    const isCustomThemeValid = mergedState.theme && !SMK_THEMES[mergedState.theme] && mergedState.theme.trim() !== "";

                    if (!mergedState.theme || (!SMK_THEMES[mergedState.theme] && !isCustomThemeValid)) {
                        mergedState.theme = null; 
                    }
                    
                    if (!GAME_LEVELS[mergedState.level]) {
                        mergedState.level = 'SMA'; 
                    }

                    if (mergedState.userRole !== 'Siswa' && mergedState.userRole !== 'Guru') {
                        mergedState.userRole = 'Siswa';
                    }

                    // Reset gameStatus if it's 'finished' but no previous game was played (e.g., first load)
                    if (mergedState.gameStatus === 'finished' && mergedState.roundsCompleted === 0) {
                        mergedState.gameStatus = 'menu';
                    }

                    return mergedState;
                }
            } catch (error) {
                console.error("Kesalahan memuat status game dari localStorage:", error);
            }
            return {...defaultGameState}; 
        }

        function saveGameStateToLocalStorage(state) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            } catch (error) {
                console.error("Kesalahan menyimpan status game ke localStorage:", error);
            }
        }
        
        function updateGameLocal(updates) {
            Object.keys(updates).forEach(key => {
                if (key === 'scores' && typeof updates.scores === 'object') {
                    gameState.scores = { ...gameState.scores, ...updates.scores };
                } else if (key === 'currentQuestion' && gameState.currentQuestion && updates.currentQuestion !== 'loading' && updates.currentQuestion !== null) {
                    updates.currentQuestion.hasSpoken = gameState.currentQuestion.hasSpoken || updates.currentQuestion.hasSpoken;
                    gameState.currentQuestion = updates.currentQuestion;
                } else {
                    gameState[key] = updates[key];
                }
            });
            
            if (updates.currentQuestion === null) {
                stopSpeaking();
            }

            saveGameStateToLocalStorage(gameState);
            renderUI(); 
        }

        // --- NEW FEATURE: Option Shuffling Logic ---

        /**
         * Mengacak urutan opsi jawaban (A, B, C) dan memperbarui kunci jawaban yang benar.
         * @param {object} questionData - Data pertanyaan dari AI.
         * @returns {object} Data pertanyaan dengan opsi yang diacak dan correctOption yang diperbarui.
         */
        function shuffleQuestionOptions(questionData) {
            if (!questionData.options || !questionData.correctOption) return questionData;

            // 1. Konversi objek opsi ke array {originalKey, text}
            let optionArray = Object.entries(questionData.options).map(([key, text]) => ({
                originalKey: key,
                text: text
            }));

            // 2. Acak array (Fisher-Yates shuffle)
            for (let i = optionArray.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [optionArray[i], optionArray[j]] = [optionArray[j], optionArray[i]];
            }

            // 3. Petakan kembali array yang diacak ke objek opsi baru (A, B, C sebagai label display tetap)
            const newOptions = {};
            let newCorrectOption = null;

            // Kunci display tetap
            const displayKeys = ['A', 'B', 'C']; 
            optionArray.forEach((item, index) => {
                const newKey = displayKeys[index];
                newOptions[newKey] = item.text;

                // 4. Perbarui kunci jawaban yang benar
                if (item.originalKey === questionData.correctOption) {
                    newCorrectOption = newKey;
                }
            });

            // 5. Perbarui data pertanyaan
            questionData.options = newOptions;
            questionData.correctOption = newCorrectOption;
            
            return questionData;
        }
        
        // --- Game Logic ---

        /**
         * Mereset seluruh status game dan menghapus data lokal.
         */
        async function resetGame() {
            const confirmed = await showCustomConfirm("Apakah Anda yakin ingin mereset permainan? Semua skor dan progres akan hilang.");
            
            if (confirmed) {
                try {
                    localStorage.removeItem(STORAGE_KEY);
                    gameState = {...defaultGameState};
                    gameState.message = "Game di-reset. Pilih peran, tema, dan level baru untuk memulai perjalanan softskill.";
                    
                    document.getElementById('game-container').classList.add('hidden');
                    renderThemeSelection();
                    showMessage("Game berhasil di-reset!");
                    stopSpeaking(); 
                } catch (error) {
                    console.error("Kesalahan saat mereset game:", error);
                    showMessage("Gagal mereset game. Coba hapus cache browser.", true);
                }
            } else {
                showMessage("Reset dibatalkan.");
            }
        }
        
        function rollDice() {
            if (gameState.isRolling || gameState.currentQuestion || gameState.gameStatus !== 'playing') return;
            
            const roll = Math.floor(Math.random() * 6) + 1;
            animateDice(roll);
            
            updateGameLocal({ isRolling: true, lastRoll: roll, message: "Dadu dilempar, menunggu hasil..." });
            stopSpeaking(); 
            
            setTimeout(async () => {
                await movePlayer(roll);
            }, 2000); 
        }

        async function movePlayer(roll) {
            let newPosition = (gameState.position + roll) % BOARD.length;
            const hasPassedStart = (gameState.position + roll) >= BOARD.length;
            
            let newScores = {...gameState.scores};
            let newRoundsCompleted = gameState.roundsCompleted;

            if (hasPassedStart) {
                newScores.Disiplin += 20; 
                newRoundsCompleted++;
                showMessage(`Selamat! Melewati START, dapat +20 poin Disiplin (Bonus putaran). Total putaran: ${newRoundsCompleted}/${gameState.maxRounds}`);
            }

            updateGameLocal({ position: newPosition, scores: newScores, roundsCompleted: newRoundsCompleted, isRolling: false, lastRoll: roll, message: `Berhenti di kotak ${newPosition}: ${BOARD[newPosition].name}` });
            
            const square = BOARD[newPosition];
            
            // Check for game end BEFORE generating question
            if (newRoundsCompleted >= gameState.maxRounds) {
                await endGame();
                return;
            }

            if (square.type === 'skill') {
                await getAIQuestion(square.skill, square.name, newPosition);
            } else {
                updateGameLocal({ currentQuestion: null, message: `Anda berhenti di ${square.name}. Giliran selanjutnya.` });
                setTimeout(() => {
                    if (gameState.gameStatus === 'playing') { // Only update message if still playing
                        updateGameLocal({ message: "Giliran Anda. Gulirkan dadu." });
                    }
                }, 3000);
            }
        }
        
        /**
         * Menyesuaikan instruksi prompt berdasarkan peran (Siswa/Guru), level, dan tema.
         */
        function getContextPrompt(role, level, theme) {
            let roleContext = "";
            let levelContext = "";

            if (role === 'Siswa') {
                roleContext = "Anda adalah Game Master yang memandu seorang SISWA. Fokuskan skenario pada dilema di lingkungan SEKOLAH (kelas, tugas, teman, guru) dan konsekuensi yang memengaruhi nilai atau hubungan sosial.";
                switch(level) {
                    case 'SMP':
                        levelContext = "Gunakan bahasa yang lugas dan sederhana (SMP/MTS). Konflik dasar dan interpersonal.";
                        break;
                    case 'SMA':
                        levelContext = "Gunakan bahasa formal. Melibatkan dilema etika dan tanggung jawab proyek. Konsekuensi sedang.";
                        break;
                    case 'PROF':
                        levelContext = "Gunakan bahasa yang lebih analitis, melibatkan persiapan karir/bisnis dan dilema tingkat tinggi (tapi masih dalam konteks siswa).";
                        break;
                }
            } else { // Guru Mode
                roleContext = "Anda adalah Game Master yang memandu seorang GURU/PENDIDIK. Fokuskan skenario pada dilema di lingkungan SEKOLAH PROFESIONAL (kurikulum, administrasi, manajemen kelas, interaksi rekan guru/orang tua/kepala sekolah). Konsekuensi memengaruhi reputasi, kinerja mengajar, atau iklim sekolah.";
                switch(level) {
                    case 'SMP':
                        levelContext = "Gunakan bahasa formal dengan menyisipakan nuansa anak muda. Fokus pada manajemen kelas dan interaksi dasar guru-siswa/orang tua.";
                        break;
                    case 'SMA':
                        levelContext = "Gunakan bahasa formal dan profesional. Fokus pada pengembangan kurikulum, kolaborasi tim guru, dan dilema etika keprofesian.";
                        break;
                    case 'PROF':
                        levelContext = "Gunakan bahasa profesional dan analitis. Fokus pada kepemimpinan sekolah, kebijakan, dan konflik strategis/manajerial.";
                        break;
                }
            }

            const themeContext = `Skenario HARUS relevan dengan bidang keahlian atau konteks: "${theme}".`;

            return `${roleContext} ${levelContext} ${themeContext}`;
        }


        async function getAIQuestion(skill, squareName, position) {
            const themeForPrompt = SMK_THEMES[gameState.theme] || gameState.theme || SMK_THEMES.general; 
            const contextPrompt = getContextPrompt(gameState.userRole, gameState.level, themeForPrompt);

            updateGameLocal({ message: `Master Game (AI) sedang membuat skenario untuk Peran: ${gameState.userRole}, Level ${GAME_LEVELS[gameState.level]} | ${themeForPrompt} ...`, currentQuestion: 'loading' });

            const systemPrompt = `Anda adalah Master Game untuk Monopoli Edukasi Softskill. Tugas Anda adalah membuat skenario terkait tema dan softskill yang diberikan dengan memasukan identitas ${sekolah}, 3 pilihan tindakan (A, B, C) yang realistis, dan menentukan pilihan terbaik berdasarkan etika dan softskill yang relevan. ${contextPrompt} Berikan tanggapan Anda sebagai objek JSON. Tambahkan satu keterampilan (skillGained) yang akan didapatkan jika pemain memilih jawaban yang tepat.`;
            
            const userQuery = `Buatkan skenario situasi dan pilihan untuk tema: "${skill}" (Kotak: ${squareName}). Situasi harus berfokus pada dilema yang menguji softskill ini.`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
                generationConfig: {
                    responseMimeType: "application/json",
                    responseSchema: {
                        type: "OBJECT",
                        properties: {
                            "situation": { "type": "STRING", "description": "Skenario situasi kerja/sekolah." },
                            "options": {
                                "type": "OBJECT",
                                "properties": {
                                    "A": { "type": "STRING" },
                                    "B": { "type": "STRING" },
                                    "C": { "type": "STRING" }
                                }
                            },
                            "correctOption": { "type": "STRING", "enum": ["A", "B", "C"], "description": "Jawaban terbaik." },
                            "skillGained": { "type": "STRING", "enum": SKILLS, "description": "Skill yang didapat dari jawaban benar." }
                        }
                    }
                }
            };
            
            const attemptApiCall = async (attempt = 0) => {
                const delay = Math.pow(2, attempt) * 1000;
                if (attempt > 0) await new Promise(res => setTimeout(res, delay));

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    const text = result.candidates?.[0]?.content?.parts?.[0]?.text;
                    let questionData = JSON.parse(text);

                    if (!questionData.situation || !questionData.options || !questionData.correctOption) {
                         throw new Error("Struktur yang tidak valid dari AI.");
                    }
                    
                    // --- Terapkan pengacakan opsi jawaban (Fitur Baru) ---
                    questionData = shuffleQuestionOptions(questionData);
                    // ---------------------------------------------------
                    
                    updateGameLocal({ currentQuestion: {...questionData, position, hasSpoken: false} });
                    showMessage(`Master Game: Skenario siap! Pilih aksi Anda di kotak ${squareName}.`);

                } catch (error) {
                    console.error("Kesalahan API Gemini:", error);
                    if (attempt < 3) {
                        console.log(`Mencoba ulang panggilan API (${attempt + 1})...`);
                        await attemptApiCall(attempt + 1); 
                    } else {
                        const fallbackQuestion = {
                            situation: `(FALLBACK) Rekan Anda (${gameState.userRole}) tidak aktif saat tugas kelompok ${themeForPrompt}. Apa yang Anda lakukan?`,
                            options: { A: "Selesaikan sendiri", B: "Ingatkan sopan", C: "Lapor atasan/guru" },
                            correctOption: "B",
                            skillGained: "Kolaborasi",
                            isFallback: true,
                            position,
                            hasSpoken: false 
                        };
                        
                        // --- Terapkan pengacakan pada Fallback ---
                        const shuffledFallback = shuffleQuestionOptions(fallbackQuestion);
                        // ----------------------------------------
                        
                        updateGameLocal({ currentQuestion: shuffledFallback });
                        showMessage(`Master Game: Skenario siap! (Mode Fallback)`);
                    }
                }
            };
            
            await attemptApiCall();
        }

        function answerQuestion(chosenOption) {
            const q = gameState.currentQuestion;
            if (!q || gameState.isRolling || q.chosenOption) return; 
            
            stopSpeaking(); 
            
            const correct = q.correctOption;
            let feedback = "";
            let scoreChange = 0;
            let skillGained = q.skillGained && SKILLS.includes(q.skillGained) ? q.skillGained : 'Kolaborasi'; 
            let newScores = {...gameState.scores};
            let newCorrectAnswers = gameState.correctAnswers;
            let newTotalQuestions = gameState.totalQuestions + 1;

            if (chosenOption === correct) {
                scoreChange = 50;
                feedback = `👍 Pilihan yang tepat! Kamu mendapat +${scoreChange} poin ${skillGained}.`;
                newScores[skillGained] = (newScores[skillGained] || 0) + scoreChange;
                newCorrectAnswers++;
            } else {
                scoreChange = -10;
                // q.options[correct] akan mengambil teks jawaban yang benar, terlepas dari label aslinya, karena sudah diacak di shuffleQuestionOptions
                feedback = `❌ Pilihan kurang tepat. Kamu kehilangan ${Math.abs(scoreChange)} poin. Jawaban terbaik adalah **${correct}**: ${q.options[correct]}.`;
                newScores[skillGained] = Math.max(0, (newScores[skillGained] || 0) + scoreChange);
            }
            
            showMessage(feedback);
            
            updateGameLocal({ 
                scores: newScores, 
                currentQuestion: { ...q, feedback, chosenOption }, 
                correctAnswers: newCorrectAnswers,
                totalQuestions: newTotalQuestions
            });

            // If game is not finished, set timeout for next turn
            if (gameState.gameStatus === 'playing') {
                setTimeout(() => {
                    updateGameLocal({ currentQuestion: null, message: "Giliran Anda. Gulirkan dadu." });
                }, 5000);
            }
        }
        
        // --- Game End Logic ---

        async function endGame() {
            updateGameLocal({ gameStatus: 'finished', isRolling: false, currentQuestion: 'loading', finalReview: null });
            showMessage("🎉 Permainan Selesai! Menyiapkan ulasan performa Anda...");
            document.getElementById('roll-button').disabled = true;

            await getAIReview();
        }
        
        /**
         * Mengunduh file .txt berisi ulasan dan statistik.
         */
        function downloadReview(reviewText, stats) {
            const date = new Date().toISOString().slice(0, 10);
            const fileName = `Ulasan_Softskill_${stats.userRole}_${date}.txt`;

            // Buat header statistik dalam format teks
            const statsHeader = `
==============================================
   LAPORAN AKHIR MONOPOLI SOFTSKILL
==============================================
Tanggal: ${new Date().toLocaleString('id-ID')}
Peran Pemain: ${stats.userRole}
Level Permainan: ${stats.level}
Tema Permainan: ${stats.theme}
Total Putaran Diselesaikan: ${stats.rounds}
----------------------------------------------
STATISTIK KINERJA:
- Total Pertanyaan Dijawab: ${gameState.totalQuestions}
- Jawaban Benar: ${gameState.correctAnswers}
- Akurasi Jawaban: ${stats.correctRatio.toFixed(1)}%
- Total Skor Softskill: ${stats.totalScore} Poin
----------------------------------------------
SKOR DETAIL:
${Object.entries(stats.skillScores).map(([skill, score]) => `- ${skill}: ${score} Poin`).join('\n')}
==============================================

`;
            
            // Menggabungkan statistik dan ulasan AI
            const content = statsHeader + reviewText;

            // Membuat Blob dan URL untuk diunduh
            const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showMessage("File ulasan berhasil diunduh!", false);
        }
        window.downloadReview = downloadReview; // expose function

        async function getAIReview() {
             const stats = {
                totalScore: SKILLS.reduce((sum, skill) => sum + (gameState.scores[skill] || 0), 0),
                correctRatio: gameState.totalQuestions > 0 ? (gameState.correctAnswers / gameState.totalQuestions) * 100 : 0,
                rounds: gameState.roundsCompleted,
                theme: SMK_THEMES[gameState.theme] || gameState.theme,
                level: GAME_LEVELS[gameState.level],
                userRole: gameState.userRole, // NEW: Include role
                skillScores: gameState.scores
            };

            const scoreSummary = Object.entries(stats.skillScores)
                .map(([skill, score]) => `${skill}: ${score} poin`)
                .join(', ');

            // Adjust system prompt based on the role
            const roleTitle = gameState.userRole === 'Guru' ? 'Pendidik/Guru' : 'Siswa/Pelajar';

            const systemPrompt = `Anda adalah seorang Konsultan Pengembangan Softskill dan Analis Game untuk ${roleTitle}. Tugas Anda adalah memberikan ulasan (review) dan rekomendasi yang mendalam dan suportif kepada pemain berdasarkan performa mereka di Monopoli Softskill. Gunakan bahasa formal yang memotivasi (sesuai level ${gameState.level}). Berikan respons dalam format Markdown, dengan satu judul utama dan tiga sub-bagian: Analisis Kinerja, Kekuatan dan Area Pengembangan (berdasarkan skor skill), dan Rekomendasi Lanjutan. Pastikan ulasan fokus pada interpretasi skor detail dan spesifik untuk peran **${gameState.userRole}**.`;

            const userQuery = `Permainan telah berakhir setelah ${stats.rounds} putaran. Peran: ${gameState.userRole}, Tema: ${stats.theme}, Level: ${stats.level}. Statistik Performa:
            - Rasio Jawaban Benar: ${stats.correctRatio.toFixed(1)}% dari ${gameState.totalQuestions} pertanyaan.
            - Total Skor: ${stats.totalScore} poin.
            - Skor Detail: ${scoreSummary}.
            Mohon berikan ulasan yang fokus pada kekuatan pemain dan dua area skill yang paling membutuhkan peningkatan (skor terendah), sesuaikan dengan konteks peran ${gameState.userRole}.`;

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                systemInstruction: { parts: [{ text: systemPrompt }] },
            };

            const reviewAttempt = async (attempt = 0) => {
                const delay = Math.pow(2, attempt) * 1000;
                if (attempt > 0) await new Promise(res => setTimeout(res, delay));

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                    const result = await response.json();
                    const reviewText = result.candidates?.[0]?.content?.parts?.[0]?.text || "(Gagal mendapatkan ulasan AI. Mohon coba lagi.)";

                    // Simpan ulasan ke dalam state sebelum di render
                    updateGameLocal({ finalReview: reviewText });
                    renderReviewUI(reviewText, stats);

                } catch (error) {
                    console.error("Kesalahan API Gemini saat mendapatkan ulasan:", error);
                    if (attempt < 2) {
                        await reviewAttempt(attempt + 1);
                    } else {
                        const fallbackText = "(Gagal mendapatkan ulasan AI setelah beberapa kali coba. Mohon periksa koneksi Anda.)";
                        updateGameLocal({ finalReview: fallbackText });
                        renderReviewUI(fallbackText, stats);
                    }
                }
            };

            await reviewAttempt();
        }

        function renderReviewUI(reviewText, stats) {
            const qEl = document.getElementById('question-area');
            
            // Basic Markdown to HTML conversion
            const reviewHTML = reviewText
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Bold
                .replace(/\n\n/g, '<p class="mt-2 mb-2">') // Paragraphs (approximation)
                .replace(/\n/g, '<br>') // Line breaks
                .replace(/#\s*(.*)/g, '<h4 class="text-2xl font-extrabold text-purple-700 mt-4 mb-2 border-b-2 border-purple-200 pb-1">$1</h4>'); // H4 for markdown titles

            qEl.innerHTML = `
                <div class="bg-white p-4 md:p-6 rounded-2xl shadow-2xl border-4 border-green-500">
                    <h3 class="text-3xl font-extrabold text-green-700 mb-4 border-b pb-2">🎉 Permainan Selesai!</h3>
                    
                    <!-- Final Stats Summary -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-700 border-l-4 border-gray-300">
                        <p class="font-bold text-base mb-1">Statistik Akhir (${stats.rounds} Putaran)</p>
                        <p>Peran Bermain: <span class="font-bold text-indigo-600">${stats.userRole}</span></p>
                        <p>Akurasi Jawaban: <span class="font-bold text-green-600">${stats.correctRatio.toFixed(1)}%</span> (${gameState.correctAnswers}/${gameState.totalQuestions})</p>
                        <p>Total Skor Softskill: <span class="font-bold text-purple-600 text-lg">${stats.totalScore}</span></p>
                        <p>Level Bermain: <span class="font-bold">${stats.level}</span></p>
                    </div>
                    
                    <div class="review-content text-gray-800 text-base leading-relaxed">
                        ${reviewHTML}
                    </div>

                    <div class="mt-6 flex flex-col md:flex-row gap-4">
                        <button onclick="downloadReview(\`${reviewText.replace(/`/g, '\\`')}\`, ${JSON.stringify(stats).replace(/`/g, '\\`')})" 
                                class="flex-1 py-3 text-xl font-extrabold text-white rounded-xl transition-all duration-300 shadow-xl 
                                    bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] transform-gpu">
                            ⬇️ Download Ulasan (.txt)
                        </button>
                        <button onclick="resetGame()" class="flex-1 py-3 text-xl font-extrabold text-white rounded-xl transition-all duration-300 shadow-xl 
                                bg-blue-600 hover:bg-blue-700 active:scale-[0.98] transform-gpu">
                            Main Lagi (Mulai Ulang)
                        </button>
                    </div>
                </div>
            `;
            showMessage("Permainan selesai! Baca ulasan Master Game di bawah.");
            stopSpeaking();
        }

        function startGame(themeKeyOrCustom) {
            let themeToSet = themeKeyOrCustom;
            
            const customInputEl = document.getElementById('custom-theme-input');
            const customTheme = customInputEl ? customInputEl.value.trim() : '';
            
            if (customTheme) {
                themeToSet = customTheme; 
            } else if (!themeToSet || themeToSet === "") {
                showMessage("Pilih tema dari daftar atau masukkan tema kustom.", true);
                return;
            }

            const levelSelectEl = document.getElementById('level-select');
            const selectedLevel = levelSelectEl ? levelSelectEl.value : 'SMA';

            const roleSelectEl = document.querySelector('input[name="user_role"]:checked');
            const selectedRole = roleSelectEl ? roleSelectEl.value : null;

            if (!selectedRole) {
                 showMessage("Pilih peran (Siswa/Guru) untuk memulai.", true);
                 return;
            }
            
            const themeDisplayName = SMK_THEMES[themeToSet] || themeToSet;

            const newGameState = {
                ...defaultGameState,
                theme: themeToSet,
                level: selectedLevel, 
                userRole: selectedRole, // Set role
                gameStatus: 'playing', // Set status to playing
                message: `Mode: ${themeDisplayName} (${selectedRole}, Level ${GAME_LEVELS[selectedLevel]}). Tujuan: Selesaikan ${defaultGameState.maxRounds} putaran. Gulirkan dadu!`
            };

            updateGameLocal(newGameState);
            
            document.getElementById('theme-selection-screen').classList.add('hidden');
            document.getElementById('game-container').classList.remove('hidden');
            document.getElementById('roll-button').disabled = false;
            renderUI();
        }

        function renderThemeSelection() {
            const selectionEl = document.getElementById('theme-selection-screen');
            const gameContainerEl = document.getElementById('game-container');

            const isCustomThemeValid = gameState.theme && !SMK_THEMES[gameState.theme] && gameState.theme.trim() !== "";
            
            if (gameState.theme && gameState.gameStatus === 'playing') {
                gameContainerEl.classList.remove('hidden');
                selectionEl.classList.add('hidden');
                return;
            } else if (gameState.gameStatus === 'finished') {
                // If the game is finished, skip menu and show game screen to potentially reload review
                gameContainerEl.classList.remove('hidden');
                selectionEl.classList.add('hidden');
                renderUI();
                return;
            } else if (SMK_THEMES[gameState.theme] || isCustomThemeValid) {
                 // Game initialized but was perhaps 'menu' or reset
                 gameContainerEl.classList.remove('hidden');
                 selectionEl.classList.add('hidden');
                 return;
            }
             else {
                 gameContainerEl.classList.add('hidden');
                 selectionEl.classList.remove('hidden');
            }

            const themeOptionsHTML = Object.entries(SMK_THEMES).map(([key, name]) => 
                `<option value="${key}">${name}</option>`
            ).join('');

            const levelOptionsHTML = Object.entries(GAME_LEVELS).map(([key, name]) => 
                `<option value="${key}" ${key === gameState.level ? 'selected' : ''}>${name}</option>`
            ).join('');
            
            const isSiswa = gameState.userRole === 'Siswa';
            const isGuru = gameState.userRole === 'Guru';


            selectionEl.innerHTML = `
                <div class="bg-white/70 frosted-glass p-6 md:p-10 rounded-3xl shadow-2xl w-full max-w-sm md:max-w-md text-center border-4 border-indigo-600">
                    <h2 class="text-3xl font-extrabold text-indigo-700 mb-2">Pilih Konteks Permainan</h2>
                    <p class="text-gray-600 mb-6 text-sm md:text-base">Tentukan Peran, tema skenario, dan level kesulitan.</p>
                    
                    <!-- Role Selection -->
                    <div class="mb-6 text-left p-3 rounded-xl bg-blue-50/10">
                        <label class="block text-base font-bold text-blue-800 mb-2">Peran Anda di Skenario (Wajib)</label>
                        <div class="flex space-x-4 justify-center">
                            <label class="flex items-center space-x-2 p-2 bg-white rounded-lg shadow-md hover:bg-blue-100 transition-colors cursor-pointer">
                                <input type="radio" name="user_role" value="Siswa" ${isSiswa ? 'checked' : ''} class="form-radio text-blue-600 h-5 w-5">
                                <span class="text-lg font-semibold text-gray-700">🧑‍🎓 Siswa</span>
                            </label>
                            <label class="flex items-center space-x-2 p-2 bg-white rounded-lg shadow-md hover:bg-blue-100 transition-colors cursor-pointer">
                                <input type="radio" name="user_role" value="Guru" ${isGuru ? 'checked' : ''} class="form-radio text-blue-600 h-5 w-5">
                                <span class="text-lg font-semibold text-gray-700">👩‍🏫 Guru</span>
                            </label>
                        </div>
                    </div>

                    <!-- Level Selection -->
                    <div class="mb-4 text-left">
                        <label for="level-select" class="block text-sm font-bold text-gray-700 mb-1">Level Kesulitan (Tingkat Bahasa & Konflik)</label>
                        <select id="level-select" class="w-full p-3 border-2 border-gray-300 rounded-lg text-lg bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-200 cursor-pointer theme-select-custom">
                            ${levelOptionsHTML}
                        </select>
                    </div>

                    <!-- Theme Selection -->
                    <div class="mb-4 text-left">
                        <label for="theme-select" class="block text-sm font-bold text-gray-700 mb-1">Tema Bidang Keahlian</label>
                        <select id="theme-select" class="w-full p-3 border-2 border-gray-300 rounded-lg text-lg bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 cursor-pointer theme-select-custom">
                            <option value="" disabled selected>-- Pilih Tema Standar SMK --</option>
                            ${themeOptionsHTML}
                        </select>
                    </div>


                    <div class="relative flex justify-center items-center my-4">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="flex-shrink mx-4 text-gray-500 font-medium text-sm">ATAU TEMA BEBAS</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    
                    <input type="text" id="custom-theme-input" placeholder="Contoh: 'Dunia Kerja Startup' atau 'Kehidupan Kampus'" 
                           class="w-full p-3 border-2 border-gray-300 rounded-lg text-base mb-6 focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                    
                    <button onclick="
                        const selectedTheme = document.getElementById('theme-select').value;
                        startGame(selectedTheme);
                    " class="w-full py-3 text-xl font-extrabold text-white rounded-xl transition-all duration-300 shadow-xl 
                            bg-gradient-to-r from-blue-600 to-purple-700 hover:from-blue-700 hover:to-purple-800 active:scale-[0.98] transform-gpu">
                        MULAI PERJALANAN
                    </button>
                </div>
            `;
        }

        // --- UI Rendering ---
        function renderBoard() {
            const boardEl = document.getElementById('game-board');
            boardEl.innerHTML = '';
            
            BOARD.forEach((square, index) => {
                const isPlayerHere = gameState.position === index;
                const playerMarker = isPlayerHere ? 
                    `<div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <span class="relative inline-block text-6xl animate-pulse" title="${PLAYER_NAME}">
    <i class="fas fa-chess-king 
               text-transparent 
               bg-clip-text 
               bg-gradient-to-r 
               from-yellow-400 
               via-red-500 
               to-purple-600 
               drop-shadow-lg 
               transition-transform 
               hover:scale-125 
               hover:rotate-12">
    </i>
    <!-- Efek glow tambahan -->
    <span class="absolute top-0 left-0 w-full h-full blur-xl bg-gradient-to-r from-yellow-400 via-red-500 to-purple-600 opacity-50 rounded-full"></span>
</span>
                    </div>` : ''; 

                boardEl.innerHTML += `
                    <div id="square-${index}" 
                         class="relative flex flex-col items-center justify-center p-1 md:p-3 text-center border-4 border-gray-700/50 rounded-xl shadow-inner
                         ${square.color} transition-all duration-300 
                         ${isPlayerHere ? 'ring-4 ring-yellow-400 scale-105 z-10 shadow-2xl shadow-yellow-500/50' : 'hover:scale-[1.02]'}
                         ${gameState.gameStatus === 'finished' ? 'opacity-50' : ''}">
                        <div class="absolute top-1 left-2 text-xs font-bold text-gray-700 opacity-70">${index}</div>
                        <div class="text-3xl md:text-4xl mb-1">${square.icon}</div>
                        <div class="text-sm md:text-base font-extrabold leading-tight text-gray-800">${square.name}</div>
                        <div class="text-[10px] md:text-xs font-medium italic text-gray-700/80">${square.skill || 'Zona Bebas'}</div>
                        ${playerMarker}
                    </div>
                `;
            });
        }

        function renderScoreboard() {
            const scoreEl = document.getElementById('score-board');
            scoreEl.innerHTML = `
                <h2 class="text-xl font-extrabold text-indigo-700 mb-4 border-b-2 pb-2 border-indigo-200">Poin Softskill</h2>
                ${SKILLS.map(skill => `
                    <div class="flex justify-between items-center py-2 border-b last:border-b-0 border-gray-100 transition duration-300 hover:bg-gray-50 rounded-md px-1">
                        <span class="font-semibold text-gray-700 text-sm md:text-base">${skill}</span>
                        <span class="text-xl md:text-2xl font-bold text-purple-600">${gameState.scores[skill] || 0}</span>
                    </div>
                `).join('')}
            `;
            const totalScore = SKILLS.reduce((sum, skill) => sum + (gameState.scores[skill] || 0), 0);
            document.getElementById('total-score').textContent = `Total Kontribusi: ${totalScore} Poin`;
            document.getElementById('rounds-info').textContent = `Putaran: ${gameState.roundsCompleted}/${gameState.maxRounds}`;
        }

        function renderQuestionArea() {
            const qEl = document.getElementById('question-area');
            const q = gameState.currentQuestion;
            
            if (gameState.gameStatus === 'finished') {
                if (q === 'loading') {
                    qEl.innerHTML = `
                        <div class="bg-white p-6 rounded-2xl shadow-2xl border-2 border-indigo-400 text-center">
                            <div class="w-16 h-16 border-4 border-purple-500 border-t-transparent border-solid rounded-full animate-spin mx-auto mb-4"></div>
                            <p class="text-xl font-bold text-indigo-600">Master Game sedang Menyusun Ulasan Akhir...</p>
                            <p class="text-sm mt-1 text-gray-500">Menganalisis ${gameState.totalQuestions} keputusan yang telah Anda buat.</p>
                        </div>
                    `;
                } else if (gameState.finalReview) {
                    // Jika ulasan sudah ada di state, render ulang ulasan (ini penting untuk reload)
                    const stats = {
                        totalScore: SKILLS.reduce((sum, skill) => sum + (gameState.scores[skill] || 0), 0),
                        correctRatio: gameState.totalQuestions > 0 ? (gameState.correctAnswers / gameState.totalQuestions) * 100 : 0,
                        rounds: gameState.roundsCompleted,
                        theme: SMK_THEMES[gameState.theme] || gameState.theme,
                        level: GAME_LEVELS[gameState.level],
                        userRole: gameState.userRole,
                        skillScores: gameState.scores
                    };
                    renderReviewUI(gameState.finalReview, stats);
                }
                return;
            }

            if (q && q !== 'loading') {
                const isAnswered = q.chosenOption;
                
                // Gunakan opsi yang sudah diacak
                const fullQuestionText = 
                    `${q.situation || ''}. Pilihan Anda. ` + 
                    `A: ${q.options.A || ''}. ` + 
                    `B: ${q.options.B || ''}. ` + 
                    `C: ${q.options.C || ''}.`;

                if (!q.hasSpoken && !isAnswered) {
                    speakText(fullQuestionText);
                    gameState.currentQuestion.hasSpoken = true; 
                }

                
                let optionsHTML = '';
                if (q.options) {
                    optionsHTML = ['A', 'B', 'C'].map(option => `
                        <button onclick="answerQuestion('${option}')" 
                                ${isAnswered ? 'disabled' : ''}
                                id="option-${option}"
                                class="w-full text-left p-3 my-2 bg-blue-600/50 rounded-xl shadow-md transition-all duration-300 text-sm md:text-base border-2
                                ${isAnswered ? 'cursor-not-allowed opacity-70' : 'bg-white hover:shadow-lg hover:border-blue-400 active:ring-4 active:ring-blue-200'}
                                ${isAnswered && option === q.correctOption ? 'bg-green-100 border-green-500 font-bold shadow-lg' : ''}
                                ${isAnswered && option === q.chosenOption && option !== q.correctOption ? 'bg-red-100 border-red-500 font-bold shadow-lg' : ''}
                                ${isAnswered && option !== q.correctOption && option !== q.chosenOption ? 'bg-gray-100 border-gray-300' : ''}
                                ">
                            <span class="font-extrabold mr-2">${option}.</span> ${q.options[option]}
                        </button>
                    `).join('');
                }
                
                const themeDisplayName = SMK_THEMES[gameState.theme] || gameState.theme; 
                
                qEl.innerHTML = `
                    <div class="bg-white/70 p-4 md:p-6 rounded-2xl shadow-2xl border-4 border-purple-400">
                        <div class="flex items-center mb-4 pb-2 border-b">
                            <span class="text-2xl md:text-3xl mr-3 text-purple-600">&#128172;</span>
                            <h3 class="text-xl md:text-2xl font-extrabold text-purple-700">Master Game: Skenario Softskill</h3>
                            <span class="ml-auto text-xs bg-purple-200 p-1 px-3 rounded-full font-bold text-purple-800">${themeDisplayName} (${gameState.userRole})</span>
                        </div>
                        
                        <!-- TTS Controls -->
                        <div class="flex items-center justify-between mb-3 p-2 bg-gray-50/50 rounded-lg border">
                            <span class="text-sm font-semibold text-gray-600">Baca Skenario:</span>
                            <div>
                                <button id="tts-stop-button" onclick="stopSpeaking()" 
                                        class="py-1 px-3 bg-red-500 text-white rounded-lg font-semibold text-sm hover:bg-red-600 transition-colors hidden">
                                    <span class="text-lg">◼️</span> Stop
                                </button>
                                <button id="tts-play-button" onclick="speakText(document.getElementById('question-text').textContent + 
                                        '. Pilihan Anda: A. ' + document.getElementById('option-A').textContent + 
                                        '. B. ' + document.getElementById('option-B').textContent + 
                                        '. C. ' + document.getElementById('option-C').textContent)"
                                        class="py-1 px-3 bg-blue-500 text-white rounded-lg font-semibold text-sm hover:bg-blue-600 transition-colors">
                                    <span class="text-lg">▶️</span> Play
                                </button>
                            </div>
                        </div>

                        <p id="question-text" class="text-base md:text-lg mb-4 p-3 bg-gray-50/40 rounded-lg italic font-medium border-l-4 border-purple-300">${q.situation || 'Error: Skenario tidak ditemukan.'}</p>
                        <p class="font-bold text-gray-700 mt-4 mb-2">Pilih aksi terbaik Anda:</p>
                        <div class="flex flex-col space-y-2">
                            ${optionsHTML}
                        </div>
                        ${q.feedback ? `<div class="mt-4 p-3 md:p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 font-semibold rounded text-sm md:text-base shadow-inner">${q.feedback}</div>` : ''}
                    </div>
                `;
                
                updateTtsButtons(isSpeaking && !isAnswered);

            } else if (q === 'loading') {
                 qEl.innerHTML = `
                  <div class="relative overflow-hidden p-6 rounded-3xl shadow-2xl text-center frosted-glass bg-gradient-to-r from-indigo-900 via-purple-800 to-pink-800">
    <!-- Partikel animasi -->
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
        <div class="particle animate-pulse bg-white opacity-20 rounded-full" style="width:6px;height:6px;top:20%;left:30%"></div>
        <div class="particle animate-pulse bg-white opacity-20 rounded-full" style="width:4px;height:4px;top:50%;left:70%"></div>
        <div class="particle animate-pulse bg-white opacity-20 rounded-full" style="width:5px;height:5px;top:80%;left:40%"></div>
        <!-- Bisa ditambah lebih banyak partikel -->
    </div>

    <!-- Loader neon -->
    <div class="relative w-24 h-24 mx-auto mb-6 rounded-full border-4 border-t-transparent border-solid border-purple-400 animate-spin shadow-[0_0_30px_#9d4edd]">
        <div class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-500 via-pink-500 to-indigo-500 opacity-30 blur-xl"></div>
    </div>

    <!-- Teks utama -->
    <p class="text-2xl font-extrabold text-white tracking-wide mb-2 animate-pulse">
        AI Game Master sedang menyusun skenario...
    </p>

    <!-- Teks detail -->
    <p class="text-sm text-gray-200 animate-[fadeIn_2s_ease-in-out]">
        Menganalisis peran <strong>${gameState.userRole}</strong>, tema, skill, dan level <strong>${GAME_LEVELS[gameState.level]}</strong>...
    </p>
</div>

                 `;
            } else {
                const nextPosition = (gameState.position + (gameState.lastRoll || 0)) % BOARD.length;
                const nextSquare = BOARD[nextPosition];
                qEl.innerHTML = `
                    <div class="text-center p-6 bg-white rounded-2xl shadow-2xl text-gray-500 border-2 border-dashed border-gray-300">
                        <div class="flex justify-center items-center">
  <img src="../avatars/formal.jpeg" 
       alt="Thinking" 
       class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 object-cover rounded-full shadow-lg border-2 border-gray-300 dark:border-gray-600 animate-pulse">
</div>

                        <p class="text-xl font-bold text-gray-700">Giliran Anda!</p>
                        <p class="text-sm mt-2">Dadu terakhir: <span class="font-bold text-lg text-purple-500">${gameState.lastRoll || 0}</span></p>
                        <p class="text-sm mt-1">Anda akan mendarat di: <span class="font-semibold text-blue-600">${nextSquare.name}</span></p>
                    </div>
                `;
            }

            document.getElementById('roll-button').disabled = gameState.isRolling || !!gameState.currentQuestion || gameState.gameStatus === 'finished';
        }

        // Dice Animation
        function animateDice(result) {
            const diceEl = document.getElementById('dice');
            diceEl.classList.remove('bg-white');
            diceEl.classList.add('bg-yellow-300', 'animate-spin-fast', 'shadow-2xl', 'shadow-yellow-400/80');
            diceEl.innerHTML = '...';

            setTimeout(() => {
                diceEl.classList.remove('bg-yellow-300', 'animate-spin-fast', 'shadow-2xl', 'shadow-yellow-400/80');
                diceEl.classList.add('bg-white');
                diceEl.innerHTML = result;
            }, 1000);
        }

        function renderUI() {
            const themeDisplayName = SMK_THEMES[gameState.theme] || gameState.theme || "Belum Dipilih";
            const levelDisplayName = GAME_LEVELS[gameState.level] || "Default";
            const roleDisplayName = gameState.userRole || "Peran Belum Dipilih";

            if (gameState.theme && gameState.gameStatus !== 'menu') {
                const headerP = document.getElementById('theme-info');
                if (headerP) headerP.innerHTML = `Peran: <span class="font-extrabold text-red-600">${roleDisplayName}</span> | Mode Aktif: <span class="font-extrabold text-indigo-700">${themeDisplayName}</span> | Level: <span class="font-extrabold text-purple-700">${levelDisplayName}</span>`;

                renderBoard();
                renderScoreboard();
                renderQuestionArea();
                showMessage(gameState.message); 
            }
        }

        // --- Initialization ---
        function initializeGame() {
            gameState = loadGameStateFromLocalStorage();
            document.getElementById('user-id').textContent = `Mode Penyimpanan: Local Storage`;
            
            if (gameState.gameStatus === 'finished') {
                // If game ended last time, show the game container and start review load
                document.getElementById('theme-selection-screen').classList.add('hidden');
                document.getElementById('game-container').classList.remove('hidden');
                renderUI(); 
                // Cek jika finalReview sudah ada, jika belum, panggil lagi
                if (!gameState.finalReview || gameState.finalReview.includes("(Gagal mendapatkan ulasan AI")) {
                     getAIReview(); 
                } else {
                     const stats = {
                        totalScore: SKILLS.reduce((sum, skill) => sum + (gameState.scores[skill] || 0), 0),
                        correctRatio: gameState.totalQuestions > 0 ? (gameState.correctAnswers / gameState.totalQuestions) * 100 : 0,
                        rounds: gameState.roundsCompleted,
                        theme: SMK_THEMES[gameState.theme] || gameState.theme,
                        level: GAME_LEVELS[gameState.level],
                        userRole: gameState.userRole,
                        skillScores: gameState.scores
                    };
                    renderReviewUI(gameState.finalReview, stats);
                }
            } else if (gameState.gameStatus === 'playing') {
                 // Game in progress
                document.getElementById('theme-selection-screen').classList.add('hidden');
                document.getElementById('game-container').classList.remove('hidden');
                renderUI();
            } else {
                // Not initialized or reset
                renderThemeSelection();
            }
        }
        
        window.onload = initializeGame;
        window.rollDice = rollDice;
        window.answerQuestion = answerQuestion;
        window.startGame = startGame;
        window.resetGame = resetGame;
        window.getAIReview = getAIReview; // expose for manual review reload if needed

        // Tailwind config for custom animation
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'spin-fast': 'spin 0.2s linear infinite',
                        'king-pulse': 'king-pulse 1.5s ease-in-out infinite',
                    },
                    keyframes: {
                        'king-pulse': {
                            '0%, 100%': { transform: 'scale(1)', opacity: '1' },
                            '50%': { transform: 'scale(1.1)', opacity: '0.8' },
                        }
                    }
                }
            }
        }
        const layers = [
    {el: document.getElementById('layer1'), speedY: 0.08, speedX: 0.02, rotate: 0.015, scale: 0.01, currentX: 0, currentY:0},
    {el: document.getElementById('layer2'), speedY: 0.12, speedX: 0.03, rotate: -0.02, scale: 0.015, currentX: 0, currentY:0},
    {el: document.getElementById('layer3'), speedY: 0.05, speedX: 0.01, rotate: 0.01, scale: 0.008, currentX: 0, currentY:0},
    {el: document.getElementById('layer4'), speedY: 0.18, speedX: 0.025, rotate: -0.03, scale: 0.02, currentX: 0, currentY:0},
    {el: document.getElementById('layer5'), speedY: 0.35, speedX: 0.015, rotate: 0.05, scale: 0.03, currentX: 0, currentY:0},
    {el: document.getElementById('layer6'), speedY: 0.22, speedX: 0.02, rotate: -0.1, scale: 0.025, currentX: 0, currentY:0},
];


let mouseX = window.innerWidth/2;
let mouseY = window.innerHeight/2;
let scrollY = window.scrollY;

function lerp(a, b, t) {
    return a + (b - a) * t;
}

function animateLayers() {
    const targetX = (window.innerWidth/2 - mouseX) / 100;
    const targetY = (window.innerHeight/2 - mouseY) / 100;

    layers.forEach(layer => {
        // Interpolate smoothly
        layer.currentX = lerp(layer.currentX, targetX * 50, 0.08);
        layer.currentY = lerp(layer.currentY, scrollY * layer.speedY + targetY * 50, 0.08);
        const rotation = scrollY * layer.rotate;
        layer.el.style.transform = `translate(${layer.currentX}px, ${layer.currentY}px) rotate(${rotation}deg)`;
    });

    requestAnimationFrame(animateLayers);
}

window.addEventListener('mousemove', e => {
    mouseX = e.clientX;
    mouseY = e.clientY;
});

window.addEventListener('scroll', () => {
    scrollY = window.scrollY;
});

// Start animation loop
requestAnimationFrame(animateLayers);
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
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
        
        #game-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr); 
            gap: 8px; 
            width: 100%;
        }
        
        @media (min-width: 768px) { 
            #game-board {
                grid-template-columns: repeat(5, 1fr);
                gap: 12px;
            }
        }

        .theme-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%234c51bf'%3E%3Cpath d='M7 7l3-3 3 3m0 6l-3 3-3-3' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        
        .modal {
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }
        .frosted-glass {
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease-in-out;
        }

        .liquid-card {
            border-radius: 2rem; 
            transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
            position: relative;
            overflow: hidden;
        }

        .liquid-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 40px rgba(79, 70, 229, 0.5);
        }

        .parallax-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            pointer-events: none;
            z-index: -10;
        }

        .layer-1 {
            background: radial-gradient(circle at top left, rgba(79, 70, 229, 0.3), transparent 70%);
        }

        .layer-2 {
            background: radial-gradient(circle at bottom right, rgba(236, 72, 153, 0.3), transparent 70%);
        }

        .text-pop {
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.4);
        }

        .initial-hidden { opacity: 0; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            animation-fill-mode: both;
        }
        .parallax-layer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    pointer-events: none;
    z-index: -10;
    transition: transform 0.1s ease-out;
}

.layer-1 {
    background: radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.4), transparent 60%);
}
.layer-2 {
    background: radial-gradient(circle at 85% 85%, rgba(236, 72, 153, 0.35), transparent 65%);
}
.layer-3 {
    background: radial-gradient(circle at 50% 10%, rgba(255, 255, 255, 0.15), transparent 50%);
}
.layer-4 {
    background: radial-gradient(circle at 15% 80%, rgba(0, 255, 200, 0.2), transparent 60%);
}
.layer-5 {
    background: radial-gradient(circle at 70% 30%, rgba(255, 255, 0, 0.1), transparent 70%);
}
.layer-6 {
    background: radial-gradient(circle at 50% 50%, rgba(0, 128, 255, 0.1), transparent 75%);
}


.text-pop {
    text-shadow: 0 0 5px rgba(0,0,0,0.4);
}

.initial-hidden { opacity: 0; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    animation-fill-mode: both;
}
@keyframes wobble {
  0% { transform: translateX(-50%) translateY(-50%) rotate(0deg) scale(0.95); }
  15% { transform: translateX(-50%) translateY(-50%) rotate(-5deg) scale(1); }
  30% { transform: translateX(-50%) translateY(-50%) rotate(3deg) scale(1); }
  45% { transform: translateX(-50%) translateY(-50%) rotate(-3deg) scale(1); }
  60% { transform: translateX(-50%) translateY(-50%) rotate(2deg) scale(1); }
  75% { transform: translateX(-50%) translateY(-50%) rotate(-1deg) scale(1); }
  100% { transform: translateX(-50%) translateY(-50%) rotate(0deg) scale(1); }
}

/* Kelas trigger wobble */
.wobble {
  animation: wobble 0.6s ease;
}
keyframes fadeIn {
    0% { opacity: 0; transform: translateY(10px); }
    100% { opacity: 1; transform: translateY(0); }
}
.particle {
    position: absolute;
    animation: float 3s infinite ease-in-out;
}
@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(45deg); }
}
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
         <div id="layer1" class="parallax-layer layer-1"></div>
<div id="layer2" class="parallax-layer layer-2"></div>
<div id="layer3" class="parallax-layer layer-3"></div>
<div id="layer4" class="parallax-layer layer-4"></div>
<div id="layer5" class="parallax-layer layer-5"></div>
<div id="layer6" class="parallax-layer layer-6"></div>
   <header class="relative w-full p-4 md:p-6 rounded-xl shadow-lg overflow-hidden mb-8"
        style="background-image: url('../admin/foto/<?= $data['banner'] ?>'); background-size: cover; background-position: center;">
  
  <!-- Dark overlay untuk teks lebih terbaca -->
  <div class="absolute inset-0 bg-black/80 pointer-events-none"></div>

  <!-- Banner Gradient / Decorative -->
  <div class="absolute inset-0 bg-gradient-to-r from-blue-400 via-purple-500 to-pink-500 opacity-30 pointer-events-none"></div>
  
  <div class="relative flex items-center justify-center md:justify-start space-x-4 md:space-x-6">
    <!-- Logo -->
    <div class="flex-shrink-0">
      <img src="../admin/foto/<?= $data['logo'] ?>" alt="Logo" class="w-12 h-12 md:w-16 md:h-16 rounded-full shadow-md border-2 border-white/50">
    </div>

    <!-- Title & Subtitle -->
    <div class="text-center md:text-left">
      <h1 class="text-3xl md:text-5xl font-extrabold text-white drop-shadow-lg">
        Monopoli Edukasi Softskill
      </h1>
      <p id="theme-info" class="text-sm md:text-lg text-gray-100/90 mt-1 md:mt-2 font-medium drop-shadow">
        AI Game Master untuk Latihan Etika dan Nilai Diri
      </p>
      <div id="user-id" class="text-xs md:text-sm text-gray-200/70 mt-1 drop-shadow">
        Mode Penyimpanan: Local Storage
      </div>
    </div>
  </div>

  <!-- Optional floating decorations -->
  <div class="absolute top-0 right-0 w-32 h-32 bg-purple-400/20 rounded-full -translate-x-1/4 -translate-y-1/4 animate-pulse"></div>
  <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-400/20 rounded-full translate-x-1/4 translate-y-1/4 animate-pulse"></div>
</header>




        <!-- Theme Selection Screen -->
        <div id="theme-selection-screen" class="w-full min-h-[90vh] flex items-center justify-center mt-8">

            <!-- Konten pemilihan tema akan dirender di sini -->
        </div>

        <!-- Main Game Container -->
        <div id="game-container" class="hidden">
            
            <main class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
                
                <!-- Sidebar / Score & Controls -->
                <div class="lg:col-span-1 space-y-4 md:space-y-6 order-2 lg:order-1">
                    
                    <!-- Papan Skor -->
                    <div class="frosted-glass p-4 md:p-6 bg-white/70 rounded-2xl shadow-2xl border-2 border-indigo-100">
                        <div id="score-board">
                            <!-- Papan Skor akan dirender di sini -->
                        </div>
                        <div class="text-center mt-3 pt-3 border-t-2 border-gray-200">
                            <span id="total-score" class="text-xl md:text-2xl font-extrabold text-purple-700">Total Kontribusi: 0 Poin</span>
                            <p id="rounds-info" class="text-sm font-semibold text-gray-500 mt-1">Putaran: 0/5</p>
                        </div>
                    </div>
<!-- 🎲 Floating Dice Roller Keren -->
<div class="fixed bottom-4 left-1/2 -translate-x-1/2 md:left-8 md:translate-x-0 md:bottom-8 z-50">
  <div class="w-28 h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 
              rounded-3xl border border-green-300/50 bg-white/50 backdrop-blur-lg 
              shadow-[0_0_10px_rgba(34,197,94,0.2),0_10px_20px_rgba(0,0,0,0.1)] 
              animate-float flex flex-col items-center justify-center space-y-2
              transition-all duration-500 hover:scale-[1.08] hover:shadow-[0_0_15px_rgba(34,197,94,0.4),0_15px_25px_rgba(0,0,0,0.15)]">

    <!-- Dice (Proporsional + Glow) -->
    <div id="dice" 
        class="flex items-center justify-center w-2/3 h-2/3 text-2xl sm:text-5xl md:text-6xl
               bg-white/90 text-gray-900 font-extrabold rounded-full border-4 border-green-300
               shadow-[0_0_15px_rgba(34,197,94,0.3),inset_0_0_5px_rgba(0,0,0,0.1)] 
               transition-all duration-300">
      1
    </div>

    <!-- Button (Proporsional + Gradient Glow) -->
    <button id="roll-button" onclick="rollDice()" 
        class="mt-auto w-4/5 py-[6%] text-xs sm:text-sm md:text-base font-bold text-white
               rounded-full bg-gradient-to-r from-green-400 via-teal-500 to-green-500 
               hover:from-green-500 hover:via-teal-600 hover:to-green-600 
               active:scale-[0.95] transition-all duration-300 shadow-lg shadow-green-300/50 disabled:opacity-60">
      Lempar Dadu
    </button>

  </div>
</div>

<!-- ✨ Animasi & Script -->
<style>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-8px); }
}
.animate-float {
  animation: float 3s ease-in-out infinite;
}
</style>

<script>
function rollDice() {
  const dice = document.getElementById("dice");
  const rollButton = document.getElementById("roll-button");

  // Animasi goyang dadu
  dice.classList.add("scale-110", "rotate-12");
  rollButton.disabled = true;

  setTimeout(() => {
    const result = Math.floor(Math.random() * 6) + 1;
    dice.textContent = result;
    dice.classList.remove("scale-110", "rotate-12");
    rollButton.disabled = false;
  }, 700);
}
</script>


                    <!-- Kontrol Dadu & Aksi -->
                    <div class="p-4 md:p-6 bg-white/70 frosted-glass rounded-2xl shadow-2xl border-2 border-green-100">
                        
                        <div class="mt-4 flex justify-center items-center space-x-4">
                            
                        </div>
                        
                        <!-- Tombol Reset Game -->
                        <button onclick="resetGame()" 
                                class="mt-4 w-full py-2 text-base font-semibold text-white rounded-xl transition-all duration-300 shadow-md 
                                bg-red-500 hover:bg-red-600 active:scale-[0.99] transform-gpu">
                            &#128472; Reset & Ganti Tema
                        </button>
                    </div>
                </div>

                <!-- Main Game Area -->
                <div class="lg:col-span-2 space-y-4 md:space-y-6 order-1 lg:order-2">
                    
                    <!-- Message Area -->
                    <div id="game-message" class="text-center text-lg font-semibold p-3 md:p-4 rounded-xl shadow-lg bg-white text-gray-800 border-l-4 border-blue-500">
                        Memuat game...
                    </div>

                    <!-- Game Board -->
                    <div id="game-board" class="overflow-x-auto liquid-card p-4 md:p-6 bg-gray-100/50 rounded-3xl shadow-2xl border-4 border-gray-300">
                        <!-- Kotak papan akan dirender di sini -->
                    </div>

                    <!-- Question Area (AI Interaction/Review) -->
                    <div id="question-area" class="liquid-card">
                        <!-- Pertanyaan AI, status loading, atau review akan dirender di sini -->
                    </div>

                </div>
            </main>
        </div>
    </div>
    
    <!-- Custom Confirmation Modal -->
    <div id="custom-modal" class="modal fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="bg-white/60 liquid-card p-6 md:p-8 rounded-xl shadow-2xl max-w-sm w-full mx-4 border-t-8 border-red-500 transform-gpu transition-all duration-300 scale-100">
            <h4 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <span class="text-red-500 text-3xl mr-2">⚠️</span> Konfirmasi Reset
            </h4>
            <p id="modal-message" class="text-gray-600 mb-6 text-base">Apakah Anda yakin ingin mereset permainan? Semua skor dan progres akan hilang.</p>
            <div class="flex justify-end space-x-4">
                <button id="cancel-button" class="py-2 px-4 bg-gray-300 text-gray-800 rounded-lg font-semibold hover:bg-gray-400 transition-colors">
                    Batal
                </button>
                <button id="confirm-button" class="py-2 px-4 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 transition-colors shadow-md">
                    Ya, Reset
                </button>
            </div>
        </div>
    </div>
    <!-- Modal Petunjuk Permainan (Lebar & Scrollable) -->
<div id="tutorial-modal" class="modal fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50">
    <div class="bg-white/95 liquid-card p-6 md:p-8 rounded-xl shadow-2xl w-full max-w-4xl mx-4 border-t-8 border-blue-500 transform-gpu transition-all duration-300 scale-100 max-h-[90vh] overflow-y-auto">
        <h4 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
            <span class="text-blue-500 text-4xl mr-3">📘</span> Petunjuk Permainan
        </h4>

        <!-- Sambutan & langkah permainan -->
        <p class="text-gray-700 mb-6 text-base">
            Selamat datang di Monopoli Edukasi Softskill! 🎲<br>
            <ul class="list-disc ml-6 mt-2">
                <li>Klik dadu untuk memulai giliranmu.</li>
                <li>Setiap langkah akan menantangmu dengan skenario softskill.</li>
                <li>Pilih jawaban terbaik untuk mendapatkan skill baru.</li>
                <li>Perhatikan level dan peranmu untuk strategi yang lebih efektif.</li>
            </ul>
        </p>

        <!-- Keterangan visual -->
        <div class="text-gray-700 mb-6 text-base space-y-4">
            <p class="flex items-start">
                <span class="text-2xl mr-3">📊</span>
                <span><strong>Level Kesulitan:</strong> Tentukan tingkat bahasa & kompleksitas konflik yang dihadapi dalam skenario.</span>
            </p>
            <p class="flex items-start">
                <span class="text-2xl mr-3">🎭</span>
                <span><strong>Peran Anda di Skenario:</strong> Pilih salah satu peran: <strong>Siswa</strong> atau <strong>Guru</strong>. Ini akan memengaruhi skenario dan pilihan yang muncul.</span>
            </p>
            <p class="flex items-start">
                <span class="text-2xl mr-3">🏷️</span>
                <span><strong>Tema Bidang Keahlian (SMK):</strong><br>
                    Pilih sesuai jurusanmu. Contoh:<br>
                    - <strong>TKJ:</strong> Troubleshooting, Problem Solving, Kerja Tim<br>
                    - <strong>RPL:</strong> Pemrograman, Analisis Sistem, Kolaborasi<br>
                    - <strong>Multimedia:</strong> Kreativitas, Desain Grafis, Manajemen Proyek<br>
                    - <strong>Akuntansi:</strong> Manajemen Keuangan, Decision Making<br>
                    - <strong>Administrasi Perkantoran:</strong> Komunikasi, Manajemen Waktu<br>
                    - <strong>TKR:</strong> Troubleshooting, Safety Awareness<br>
                    - <strong>Teknik Elektro:</strong> Problem Solving, Analisis, Kreativitas<br>
                    - <strong>Farmasi / Kesehatan:</strong> Teliti, Kepedulian, Etika<br>
                    - <strong>Pariwisata / Perhotelan:</strong> Pelayanan, Komunikasi, Problem Solving<br>
                    - <strong>Bisnis / Pemasaran:</strong> Negosiasi, Komunikasi, Kreativitas
                </span>
            </p>
            <p class="flex items-start">
                <span class="text-2xl mr-3">✨</span>
                <span><strong>Tema Kustom:</strong> Bisa menentukan tema khusus sesuai keinginan atau fokus pembelajaran.</span>
            </p>
        </div>

        <!-- Tombol Mulai -->
        <div class="flex justify-end">
            <button id="tutorial-close" class="py-3 px-6 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600 transition-colors shadow-md">
                Mulai Permainan
            </button>
        </div>
    </div>
</div>

<script>
// Tampilkan modal saat page load
window.addEventListener('DOMContentLoaded', () => {
    const tutorialModal = document.getElementById('tutorial-modal');
    tutorialModal.classList.remove('hidden');

    document.getElementById('tutorial-close').addEventListener('click', () => {
        tutorialModal.classList.add('hidden');
    });
});
</script>

</body>
</html>
