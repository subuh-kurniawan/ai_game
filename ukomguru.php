<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi UKG Guru Kejuruan (Analisis AI)</title>
    <!-- Memuat Tailwind CSS untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fc;
        }
        .ukg-card {
            /* Gaya bayangan untuk kartu utama */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .ukg-card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        .option-button {
            /* Transisi untuk tombol opsi */
            transition: background-color 0.15s, border-color 0.15s;
        }
        .option-button:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
        }
        /* Style untuk tombol yang disabled/tidak bisa diklik karena loading */
        .loading-disabled {
            cursor: progress;
            opacity: 0.8;
        }
    </style>
</head>
<body class="p-4 sm:p-8 flex items-center justify-center min-h-screen">

    <div id="app" class="w-full max-w-4xl">
        <header class="text-center mb-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-teal-700">SIMULASI UKG GURU KEJURUAN</h1>
            <p class="text-lg text-gray-600 mt-2">Uji Kompetensi Pedagogis, Profesional, dan Industri Anda</p>
        </header>

        <main id="content-container" class="bg-white p-6 sm:p-10 rounded-xl ukg-card">
            <!-- Konten (Layar Mulai, Skenario, Hasil) akan dimuat di sini oleh JavaScript -->
        </main>
    </div>

    <script type="module">
        // Variabel global yang disediakan oleh lingkungan untuk panggilan API
        const apiKey = "APIKEY"; // API Key disediakan otomatis oleh platform
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`;
        const SCENARIO_KEY = 'ukg_sim_scenarios_kejuruan';
        
        // Konstanta untuk menghitung jumlah soal contoh (dari data MOCK_SCENARIOS)
        const MOCK_SCENARIO_COUNT = 5; 

        // Daftar Domain Kompetensi yang Diperluas
        const DOMAIN_LIST = [
            'Pedagogis', 
            'Etika Profesional', 
            'Kompetensi Profesional Kejuruan', 
            'Kompetensi Terkait Dunia Kerja', 
            'Kompetensi Kolaboratif', 
            'Kompetensi Abad ke-21'
        ];
        
        // Variabel untuk menyimpan hasil analisis dari Gemini
        let geminiAnalysisResult = null;


        // --- MOCK DATA SIMULATION (Menggantikan soal.json) ---
        /** * Fungsi ini mensimulasikan proses fetching data dari file soal.json */
        const fetchMockScenarios = () => {
            const MOCK_SCENARIOS = [
                {
                    "scenarioTitle": "Pelatihan Ulang Teknologi Baru",
                    "scenarioContext": "Anda adalah Guru Teknik Komputer dan Jaringan. Industri TIK baru saja mengadopsi standar jaringan nirkabel (Wi-Fi 7) yang belum Anda ajarkan. Sekolah tidak memiliki anggaran untuk pelatihan resmi, tetapi Anda harus mengintegrasikannya dalam kurikulum 3 bulan ke depan.",
                    "question": "Langkah proaktif dan realistis yang paling tepat Anda lakukan untuk menguasai materi baru ini adalah...",
                    "options": [
                        {"label": "A", "text": "Menunggu ketersediaan anggaran pelatihan resmi dari dinas atau sekolah."},
                        {"label": "B", "text": "Mencari sumber belajar mandiri (MOOCs, dokumentasi industri, forum) dan membangun simulasi praktis sederhana dengan alat yang ada di bengkel."},
                        {"label": "C", "text": "Mengajarkan versi lama (Wi-Fi 6) karena dianggap masih memadai untuk siswa."},
                        {"label": "D", "text": "Mengalihkan materi ke guru lain yang mungkin sudah menguasai, meskipun itu bukan mata pelajaran utamanya."},
                    ],
                    "domain": "Kompetensi Profesional Kejuruan", 
                    "correctIndex": 1,
                    "explanation": "Jawaban B menunjukkan Kompetensi Profesional Kejuruan dan Adaptasi Industri. Guru dituntut proaktif dalam pembelajaran sepanjang hayat, terutama dalam bidang teknologi yang cepat berubah, tanpa harus menunggu fasilitas atau anggaran formal."
                },
                {
                    "scenarioTitle": "Pengembangan Produk Wirausaha Siswa",
                    "scenarioContext": "Siswa Anda di program Tata Boga berhasil membuat inovasi makanan yang berpotensi dijual. Siswa tersebut hanya fokus pada rasa, namun mengabaikan aspek pengemasan, branding, dan analisis pasar. Produk akan dipamerkan di acara sekolah minggu depan.",
                    "question": "Sebagai guru pembimbing, tindakan pedagogis yang paling tepat untuk mengoptimalkan potensi wirausaha siswa adalah...",
                    "options": [
                        {"label": "A", "text": "Meminta siswa hanya fokus pada resep karena pengemasan bukan bagian dari mata pelajaran utama."},
                        {"label": "B", "text": "Segera memanggil ahli grafis dari luar untuk merancang kemasan profesional agar produk terlihat bagus."},
                        {"label": "C", "text": "Mengarahkan siswa untuk berkolaborasi dengan siswa Desain Grafis (lintas jurusan) untuk aspek branding dan meminta mereka membuat rencana pemasaran digital sederhana."},
                        {"label": "D", "text": "Menganalisis kemasan produk pesaing yang ada di pasaran dan meminta siswa menirunya persis."},
                    ],
                    "domain": "Kompetensi Terkait Dunia Kerja", 
                    "correctIndex": 2,
                    "explanation": "Jawaban C adalah yang paling tepat. Ini menanamkan pola pikir wirausaha yang komprehensif (tidak hanya produksi), melatih kolaborasi (Kompetensi Abad ke-21), dan menyentuh aspek Literasi Digital dan Branding yang dibutuhkan di dunia kerja."
                },
                {
                    "scenarioTitle": "Penyelarasan Kurikulum dengan DUDI",
                    "scenarioContext": "Sekolah Anda ingin menjalin kerja sama dengan perusahaan manufaktur besar. Perusahaan tersebut menyatakan bahwa kurikulum kejuruan Anda sudah ketinggalan 5 tahun dan tidak mengajarkan penggunaan mesin CNC terbaru yang mereka miliki.",
                    "question": "Bagaimana Anda, sebagai Ketua Program Keahlian, merespons temuan gap kompetensi ini secara profesional?",
                    "options": [
                        {"label": "A", "text": "Menolak kritik, menyatakan bahwa kurikulum sekolah sudah sesuai dengan standar nasional yang berlaku."},
                        {"label": "B", "text": "Mengadakan pertemuan darurat dengan perwakilan industri dan semua guru kejuruan untuk merumuskan 'penyesuaian cepat' pada modul praktikum dan meminta pelatihan singkat dari industri."},
                        {"label": "C", "text": "Mencari perusahaan DUDI lain yang standar teknologinya sesuai dengan kurikulum lama sekolah."},
                        {"label": "D", "text": "Meminta industri menyumbangkan mesin CNC terbaru agar sekolah bisa mengajarkannya."},
                    ],
                    "domain": "Kompetensi Kolaboratif", 
                    "correctIndex": 1,
                    "explanation": "Jawaban B menunjukkan Kompentensi Kolaboratif dan Adaptasi Industri. Respon cepat, kolaboratif (melibatkan DUDI dan guru), dan fokus pada penyesuaian kurikulum adalah kunci keberhasilan program kejuruan. Opsi A dan C menolak adaptasi, yang esensial di SMK."
                },
                {
                    "scenarioTitle": "Resolusi Masalah di Tengah Proyek",
                    "scenarioContext": "Dalam proyek akhir Teknik Permesinan, dua kelompok siswa mengalami masalah teknis yang sama, yaitu kegagalan kalibrasi alat ukur. Waktu pengerjaan proyek tersisa 2 jam. Mereka datang kepada Anda meminta solusi siap pakai agar proyek selesai tepat waktu.",
                    "question": "Tindakan pedagogis yang paling efektif untuk mengembangkan kemampuan Abad ke-21 siswa adalah...",
                    "options": [
                        {"label": "A", "text": "Memberikan langkah-langkah kalibrasi secara terperinci (solusi instan) agar mereka dapat segera melanjutkan pekerjaan."},
                        {"label": "B", "text": "Meminta mereka untuk kembali ke manual (teori) dan secara mandiri mendiskusikan 3 kemungkinan penyebab kegagalan sebelum menawarkan bantuan lebih lanjut."},
                        {"label": "C", "text": "Menambah waktu proyek selama satu hari penuh agar siswa bisa menyelesaikannya tanpa tekanan."},
                        {"label": "D", "text": "Mendemonstrasikan cara kalibrasi yang benar di depan kelas dan meminta mereka meniru langkahnya."},
                    ],
                    "domain": "Kompetensi Abad ke-21", 
                    "correctIndex": 1,
                    "explanation": "Jawaban B melatih Berpikir Kritis dan Pemecahan Masalah (Kompetensi Abad ke-21) di bawah tekanan waktu. Guru memfasilitasi proses berpikir analitis siswa (mencari penyebab), bukan hanya memberikan solusi. Opsi A dan D hanya berfokus pada hasil/kecepatan, bukan proses belajar."
                },
                {
                    "scenarioTitle": "Kebijakan Sekolah vs Etika Guru",
                    "scenarioContext": "Kepala Sekolah menetapkan kebijakan bahwa setiap guru harus 'membantu' siswanya mencapai nilai minimal KKM, bahkan jika itu berarti memberikan tugas remedial yang sangat ringan dan tidak substansial (hanya formalitas), demi menjaga citra kelulusan yang tinggi. Anda merasa ini melanggar etika penilaian.",
                    "question": "Bagaimana Anda, sebagai guru yang menjunjung integritas, seharusnya merespons kebijakan ini?",
                    "options": [
                        {"label": "A", "text": "Menolak sepenuhnya kebijakan tersebut dan membuat penilaian Anda sendiri secara diam-diam."},
                        {"label": "B", "text": "Mengikuti kebijakan secara total untuk menghindari konflik dan menjaga kedamaian di sekolah."},
                        {"label": "C", "text": "Mendiskusikan kebijakan ini dengan Kepala Sekolah secara pribadi dan menyarankan modifikasi: yaitu tugas remedial tetap substantif dan edukatif, namun disesuaikan agar mudah diakses siswa."},
                        {"label": "D", "text": "Mengajukan pengunduran diri karena Anda tidak dapat berkompromi dengan integritas penilaian."},
                    ],
                    "domain": "Etika Profesional", 
                    "correctIndex": 2,
                    "explanation": "Pilihan C adalah tindakan Etika Profesional terbaik. Guru wajib menjaga integritas (tidak menerima formalitas A dan B) tetapi juga harus menjaga kolegialitas. Pendekatan persuasif dan menawarkan solusi yang memenuhi kebutuhan administrasi (nilai KKM) sekaligus menjaga kualitas pendidikan (remedial substantif) adalah pilihan yang paling matang. Opsi D terlalu ekstrem."
                }
            ];

            return new Promise(resolve => {
                // Mensimulasikan penundaan jaringan/pemuatan file 
                setTimeout(() => {
                    resolve(MOCK_SCENARIOS);
                }, 500); 
            });
        };


        // --- UTILITY FUNCTIONS ---

        /** Mengimplementasikan exponential backoff untuk panggilan API. */
        const fetchWithBackoff = async (url, options, maxRetries = 5) => {
            for (let attempt = 0; attempt < maxRetries; attempt++) {
                try {
                    const response = await fetch(url, options);
                    if (response.status === 429 && attempt < maxRetries - 1) {
                        const delay = Math.pow(2, attempt) * 1000 + Math.random() * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue; // Coba lagi
                    }
                    if (!response.ok) {
                        try {
                            const errorBody = await response.json();
                            const errorMessage = errorBody.error?.message || response.statusText;
                            throw new Error(`API Error ${response.status}: ${errorMessage}`);
                        } catch (e) {
                            throw new Error(`HTTP Error ${response.status}: ${response.statusText}`);
                        }
                    }
                    return response;
                } catch (error) {
                    if (attempt === maxRetries - 1) {
                        console.error("Fetch gagal setelah semua percobaan:", error);
                        throw error;
                    }
                }
            }
        };

        // --- GAME STATE AND LOGIC ---

        const App = {
            currentQuestionIndex: 0,
            score: 0,
            maxQuestions: 20, 
            allScenarios: [], 
            history: [], 
            isLoading: false,

            // Pengaturan awal untuk game baru
            startGame() {
                this.currentQuestionIndex = 0;
                this.score = 0;
                this.history = [];
                geminiAnalysisResult = null; // Reset analisis Gemini
            },

            // Fungsi untuk mundur satu skenario (Perbaikan: Navigasi Sebelumnya)
            prevScenario() {
                if (this.currentQuestionIndex > 0) {
                    this.currentQuestionIndex--;
                    this.loadScenario();
                }
            },
            
            // Fungsi untuk maju ke skenario berikutnya atau menampilkan hasil
            nextScenario() {
                // Pastikan skenario saat ini sudah dijawab sebelum pindah
                if (!this.history[this.currentQuestionIndex] || !this.history[this.currentQuestionIndex].isAnswered) {
                     // Ini hanya sebagai fallback, tombol 'Next' seharusnya disabled
                     console.warn("Skenario belum dijawab. Tidak bisa pindah.");
                     return; 
                }
                
                this.currentQuestionIndex++;
                if (this.currentQuestionIndex >= this.maxQuestions) {
                    this.showResult();
                } else {
                    this.loadScenario();
                }
            },

            // --- MODE SELECTION LOGIC ---

            showModeSelection() {
                // Perbarui teks untuk mode 'Load Soal'
                const storedScenarios = localStorage.getItem(SCENARIO_KEY);
                const mockQuestionCount = MOCK_SCENARIO_COUNT;
                
                let loadModeText = storedScenarios 
                    ? `Muat Soal Tersimpan (${JSON.parse(storedScenarios).length} Soal)`
                    : `Muat Soal Contoh (${mockQuestionCount} Soal dari JSON)`; 
                let loadModeSubText = storedScenarios
                    ? `Menggunakan soal hasil generate AI dari sesi sebelumnya (Instan).`
                    : `Menggunakan ${mockQuestionCount} soal contoh dari simulasi soal.json (Membutuhkan sedikit waktu muat).`; 
                let loadModeOnClick = storedScenarios 
                    ? `App.startLoadMode(true)` // Mode load dari localStorage
                    : `App.startLoadMode(false)`; // Mode load dari fetchMockScenarios

                this.showScreen(`
                    <h2 class="text-2xl font-semibold mb-4 text-gray-700">Pilih Mode Skenario UKG Kejuruan</h2>
                    <p class="text-gray-600 mb-8">Bagaimana Anda ingin memuat soal ujian? (Fokus pada Skenario SMK/Kejuruan)</p>
                    
                    <div class="space-y-4">
                        <button onclick="${loadModeOnClick}" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-lg shadow-md transition duration-200 text-lg">
                            ${loadModeText}
                            <p class="text-sm font-normal opacity-90 mt-1">${loadModeSubText}</p>
                        </button>
                        
                        <button onclick="App.startGenerateMode()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-8 rounded-lg shadow-md transition duration-200 text-lg">
                            Buat Skenario Baru (AI Generate)
                            <p class="text-sm font-normal opacity-90 mt-1">Menghasilkan ${this.maxQuestions} skenario unik **Tingkat SULIT** dengan domain Kejuruan melalui AI (Membutuhkan waktu).</p>
                        </button>
                    </div>
                    <div class="mt-8 text-center">
                        <button onclick="App.showHomeScreen()" class="text-blue-500 hover:text-blue-700 font-medium">
                            &larr; Kembali
                        </button>
                    </div>
                `);
            },
            
            // Mode 1: Load from Simulated soal.json or localStorage (if available)
            async startLoadMode(fromLocalStorage = false) {
                this.startGame();
                
                if (fromLocalStorage) {
                    // Load dari localStorage (hasil generate AI sebelumnya) - INSTAN
                    const storedScenarios = localStorage.getItem(SCENARIO_KEY);
                    try {
                        const parsedScenarios = JSON.parse(storedScenarios);
                        if (parsedScenarios && parsedScenarios.length >= 1) { 
                            this.allScenarios = parsedScenarios;
                            this.maxQuestions = parsedScenarios.length;
                            this.loadScenario(); 
                            return;
                        }
                    } catch (e) {
                        console.error("Error parsing stored scenarios. Falling back to mock data.", e);
                    }
                    
                    this.showError("Soal tersimpan rusak. Silakan pilih mode 'Buat Soal Baru' untuk melanjutkan, atau coba 'Muat Soal Contoh'.");
                    localStorage.removeItem(SCENARIO_KEY);
                    return;
                } else {
                    // Load dari simulasi soal.json via fetchMockScenarios() - ASYNCHRONOUS
                    this.showLoading(`Memuat ${MOCK_SCENARIO_COUNT} Soal Contoh dari soal.json...`);
                    try {
                        this.allScenarios = await fetchMockScenarios();
                        this.maxQuestions = this.allScenarios.length; 
                        
                        if (this.allScenarios.length > 0) {
                            this.loadScenario();
                        } else {
                            this.showError("Soal Contoh (JSON) tidak ditemukan. Silakan pilih mode 'Buat Soal Baru'.");
                        }
                    } catch (error) {
                         this.showError(`Gagal memuat soal contoh. Detail: ${error.message}`);
                    }
                }
            },
            
            // Mode 2: Force Generate from API
            startGenerateMode() {
                this.startGame();
                this.maxQuestions = 20; // Kembalikan ke 20 untuk generate AI
                // Hapus data lama sebelum generate baru (agar generate baru)
                localStorage.removeItem(SCENARIO_KEY); 
                this.fetchAndStoreAllScenarios();
            },


            async fetchAndStoreAllScenarios() {
                if (this.isLoading) return;

                this.isLoading = true;
                this.showLoading(`Menganalisis dan Membuat ${this.maxQuestions} Skenario UKG **Level SULIT**...`); 

                try {
                    const scenariosArray = await this.fetchScenariosFromAPI(this.maxQuestions);
                    this.allScenarios = scenariosArray;
                    
                    // Simpan ke localStorage untuk penggunaan sesi berikutnya
                    localStorage.setItem(SCENARIO_KEY, JSON.stringify(scenariosArray));
                    
                    this.loadScenario(); // Mulai game
                } catch (error) {
                    console.error("Gagal memuat skenario:", error);
                    this.showError("Gagal memuat skenario. Silakan coba lagi. Rincian: " + error.message);
                } finally {
                    this.isLoading = false;
                }
            },


            async fetchScenariosFromAPI(count) {
                // System Instruction: Set persona, format output, dan tekankan tingkat kesulitan SULIT/DILEMATIS
                const systemPrompt = "Bertindak sebagai generator skenario ujian kompetensi guru (UKG) profesional dan realistis dalam konteks **Sekolah Menengah Kejuruan (SMK)** di Indonesia. Hasilkan SATU ARRAY JSON yang berisi skenario pedagogis, profesional, dan kejuruan yang menantang dan **sangat dilematis (TINGKAT SULIT/HIGH-STAKES)**. Setiap skenario harus unik dan belum pernah dihasilkan. Skenario harus berfokus pada dilema yang spesifik bagi guru kejuruan.";
                
                // User Query: Tentukan tema, jumlah, dan minta domain yang telah diperluas
                const userQuery = `Buatkan ARRAY berisi ${count} skenario UKG **tingkat kesulitan SULIT/DILEMATIS**. Skenario harus dibagi merata ke dalam domain kompetensi berikut: 'Pedagogis', 'Etika Profesional', 'Kompetensi Profesional Kejuruan', 'Kompetensi Terkait Dunia Kerja', 'Kompetensi Kolaboratif', dan 'Kompetensi Abad ke-21'.`;

                // Define the structured JSON response schema (ARRAY of OBJECTs)
                const responseSchema = {
                    type: "ARRAY", 
                    items: { 
                        type: "OBJECT",
                        properties: {
                            "scenarioTitle": { "type": "STRING", "description": "Judul skenario yang ringkas, contoh: 'Dilema Penilaian Proyek'" },
                            "scenarioContext": { "type": "STRING", "description": "Narasi mendalam tentang situasi yang dihadapi guru, minimal 4 kalimat." },
                            "question": { "type": "STRING", "description": "Pertanyaan yang menuntut keputusan profesional terbaik. Contoh: 'Tindakan yang paling tepat yang harus diambil oleh Bu Ani adalah...'" },
                            "options": {
                                "type": "ARRAY",
                                "description": "Empat opsi jawaban (A, B, C, D) yang realistis dan menantang.",
                                "items": {
                                    "type": "OBJECT",
                                    "properties": {
                                        "label": { "type": "STRING" },
                                        "text": { "type": "STRING", "description": "Teks lengkap opsi jawaban, singkat dan padat." }
                                    },
                                    "propertyOrdering": ["label", "text"]
                                }
                            },
                            "domain": { "type": "STRING", "description": "Kategori kompetensi utama dari skenario ini. Harus salah satu dari: 'Pedagogis', 'Etika Profesional', 'Kompetensi Profesional Kejuruan', 'Kompetensi Terkait Dunia Kerja', 'Kompetensi Kolaboratif', atau 'Kompetensi Abad ke-21'." }, 
                            "correctIndex": { "type": "NUMBER", "description": "Indeks 0, 1, 2, atau 3 dari opsi jawaban yang paling tepat secara pedagogis." },
                            "explanation": { "type": "STRING", "description": "Penjelasan pedagogis dan profesional mengapa jawaban tersebut adalah yang terbaik, minimal 3 kalimat." }
                        },
                        "propertyOrdering": ["scenarioTitle", "scenarioContext", "question", "options", "domain", "correctIndex", "explanation"] 
                    }
                };

                const payload = {
                    contents: [{ parts: [{ text: userQuery }] }],
                    systemInstruction: { parts: [{ text: systemPrompt }] },
                    generationConfig: {
                        responseMimeType: "application/json",
                        responseSchema: responseSchema
                    }
                };

                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                };

                const response = await fetchWithBackoff(apiUrl, options);
                const result = await response.json();

                const jsonText = result.candidates?.[0]?.content?.parts?.[0]?.text;
                if (!jsonText) {
                    throw new Error("Respons API kosong atau format tidak terduga.");
                }

                try {
                    // Membersihkan karakter tidak standar yang mungkin ditambahkan oleh model sebelum JSON
                    const cleanedJsonText = jsonText.replace(/```json\s*|```/g, '').trim(); 
                    const data = JSON.parse(cleanedJsonText);
                    
                    // --- Stricter Validation Check (Perbaikan untuk Ketahanan Kode) ---
                    if (!Array.isArray(data) || data.length === 0) {
                        throw new Error("Output AI bukan array atau array kosong.");
                    }
                    
                    const requiredKeys = ['scenarioTitle', 'question', 'options', 'domain', 'correctIndex', 'explanation'];
                    const firstScenario = data[0];

                    const isValidStructure = requiredKeys.every(key => firstScenario.hasOwnProperty(key)) &&
                                             Array.isArray(firstScenario.options) &&
                                             firstScenario.options.length === 4;

                    if (!isValidStructure) {
                        throw new Error("Struktur JSON tidak valid. Beberapa kunci penting (title, options, correctIndex) hilang atau format opsi salah.");
                    }
                    // --- End Stricter Validation Check ---
                    
                    return data; // Ini adalah Array
                } catch (e) {
                    console.error("Gagal memparsing atau memvalidasi JSON:", jsonText, e);
                    throw new Error(`Gagal memproses data skenario. Detail: ${e.message}. Coba lagi atau gunakan Soal Contoh.`);
                }
            },


            async loadScenario() {
                if (this.currentQuestionIndex >= this.maxQuestions) {
                    this.showResult();
                    return;
                }
                
                if (!this.allScenarios[this.currentQuestionIndex]) {
                     this.showError("Data skenario tidak lengkap. Silakan pilih mode 'Buat Soal Baru' untuk memulai ulang.");
                     return;
                }

                const scenarioData = this.allScenarios[this.currentQuestionIndex];
                
                // Cek apakah skenario sudah pernah dijawab (dari sesi sebelumnya, jika ada)
                let currentScenario = this.history[this.currentQuestionIndex];
                if (!currentScenario || currentScenario.scenarioTitle !== scenarioData.scenarioTitle) {
                    currentScenario = {
                        ...scenarioData,
                        questionNumber: this.currentQuestionIndex + 1,
                        userChoiceIndex: null,
                        isAnswered: false,
                        isCorrect: null
                    };
                    this.history[this.currentQuestionIndex] = currentScenario;
                }
                
                this.showScenario(currentScenario);
            },

            // Perbaikan: Fungsi ini sekarang memungkinkan user mengubah jawaban dan me-recalculate skor
            handleChoice(choiceIndex) {
                const current = this.history[this.currentQuestionIndex];
                
                // Update pilihan user
                current.userChoiceIndex = choiceIndex;
                current.isAnswered = true; // Skenario ini sudah dijawab
                
                // Recalculate correctness
                const isCorrect = (choiceIndex === current.correctIndex);
                current.isCorrect = isCorrect;

                // Update score calculation instantly (recalculate total correct answers across history)
                this.score = this.history.filter(h => h.isAnswered && h.isCorrect).length; 

                this.showScenario(current);
            },

            // Fungsi untuk menghitung skor berdasarkan domain
            calculateDomainScores() {
                const domainResults = {};
                
                // Inisialisasi domain
                DOMAIN_LIST.forEach(d => {
                    domainResults[d] = { correct: 0, total: 0 };
                });

                // Mengisi hasil dari riwayat
                this.history.forEach(h => {
                    if (!h.isAnswered) return;
                    const domain = h.domain || 'Lain-lain'; 
                    if (!domainResults[domain]) {
                         domainResults[domain] = { correct: 0, total: 0 };
                    }
                    domainResults[domain].total++;
                    if (h.isCorrect) {
                        domainResults[domain].correct++;
                    }
                });
                
                // Menghitung persentase
                const finalResults = {};
                for (const domain in domainResults) {
                    const { correct, total } = domainResults[domain];
                    // Hanya masukkan domain yang memiliki soal (total > 0)
                    if (total > 0) {
                        const percentage = (correct / total) * 100;
                        finalResults[domain] = { correct, total, percentage: percentage.toFixed(1) };
                    }
                }
                
                return finalResults;
            },


            // --- UI RENDERING ---

            showHomeScreen() {
                this.showScreen(`
                    <h2 class="text-2xl font-semibold mb-4 text-gray-700">Selamat Datang, Bapak/Ibu Guru Kejuruan!</h2>
                    <p class="text-gray-600 mb-6">Simulasi ini menyajikan skenario tantangan nyata yang fokus pada **Kompetensi Kejuruan, Kolaborasi Industri, dan Keterampilan Abad ke-21**.</p>
                    <button onclick="App.showModeSelection()" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-200">
                        Mulai Ujian Kompetensi
                    </button>
                    <p class="text-xs text-gray-500 mt-4">Simulasi menggunakan kecerdasan buatan untuk menghasilkan skenario yang realistis.</p>
                `);
            },
            
            showScreen(htmlContent) {
                 const container = document.getElementById('content-container');
                 container.innerHTML = `<div class="text-center">${htmlContent}</div>`;
            },


            showLoading(message = "Menganalisis dan Membuat Skenario UKG...") {
                const container = document.getElementById('content-container');
                container.innerHTML = `
                    <div class="text-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600 mx-auto mb-4"></div>
                        <p class="text-xl font-medium text-teal-600">${message}</p>
                        <p class="text-sm text-gray-500 mt-2">Mohon tunggu sebentar. Proses ini mungkin memakan waktu beberapa detik karena menghasilkan ${this.maxQuestions} skenario sekaligus.</p>
                    </div>
                `;
            },

            showError(message) {
                 const container = document.getElementById('content-container');
                 container.innerHTML = `
                    <div class="text-center py-12 bg-red-50 border border-red-200 rounded-lg p-6">
                        <svg class="w-12 h-12 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <h2 class="text-2xl font-semibold text-red-700 mb-2">Terjadi Kesalahan!</h2>
                        <p class="text-gray-600 mb-6 whitespace-pre-line">${message}</p>
                        <button onclick="App.showModeSelection()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                            Pilih Mode Lain
                        </button>
                    </div>
                 `;
            },

            showScenario(scenario) {
                const container = document.getElementById('content-container');
                const isAnswered = scenario.isAnswered;
                const userChoice = isAnswered ? scenario.userChoiceIndex : -1;
                const progress = `${scenario.questionNumber} / ${this.maxQuestions}`;
                const currentScore = this.history.filter(h => h.isCorrect).length;

                const optionsHtml = scenario.options.map((opt, index) => {
                    let classes = 'option-button w-full text-left p-3 rounded-lg border-2 shadow-sm text-gray-800 transition duration-150';
                    let onclick = `onclick="App.handleChoice(${index})"`;
                    const isCorrectChoice = index === scenario.correctIndex;

                    if (isAnswered) {
                        // Opsi selalu dapat diklik ulang untuk mengubah jawaban
                        if (isCorrectChoice) {
                            // Highlight JAWABAN BENAR
                            classes += ' bg-green-100 border-green-500 font-bold';
                        } else if (index === userChoice) {
                            // Highlight JAWABAN SALAH (Pilihan user)
                            classes += ' bg-red-100 border-red-500 font-bold';
                        } else {
                            // Opsi lain yang tidak dipilih: Netral
                            classes += ' bg-white border-gray-200 hover:bg-teal-50 hover:border-teal-300';
                        }
                    } else {
                        // Belum dijawab: Normal
                        classes += ' bg-white border-gray-200 hover:bg-teal-50 hover:border-teal-300';
                    }
                    
                    // Tambahkan indikator pilihan user jika sudah dijawab
                    const indicator = index === userChoice ? 
                        `<span class="ml-auto text-sm font-extrabold ${isCorrectChoice ? 'text-green-700' : 'text-red-700'}">(${isCorrectChoice ? 'Pilihan Tepat' : 'Pilihan Anda'})</span>` : 
                        '';

                    return `
                        <button ${onclick} class="${classes} mb-3 flex items-start">
                            <span class="font-extrabold mr-3 mt-0.5 w-6 flex-shrink-0">${opt.label}.</span>
                            <span class="flex-grow">${opt.text}</span>
                            ${indicator}
                        </button>
                    `;
                }).join('');

                // Konten Penjelasan (Hanya muncul jika sudah dijawab)
                const explanationHtml = isAnswered ? `
                    <div class="mt-8 p-6 rounded-xl border-t-4 ${scenario.isCorrect ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} shadow-md text-left">
                        <h3 class="text-xl font-extrabold ${scenario.isCorrect ? 'text-green-700' : 'text-red-700'} mb-2 flex items-center">
                            ${scenario.isCorrect 
                                ? '<svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Jawaban Tepat!' 
                                : '<svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> Perlu Koreksi'
                            }
                        </h3>
                        <p class="text-md font-semibold text-gray-800 mb-2">Jawaban yang Tepat adalah Opsi <span class="text-green-600">${scenario.options[scenario.correctIndex].label}</span></p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">${scenario.explanation}</p>
                    </div>
                ` : '';

                // Navigation buttons (Perbaikan: Selalu tampilkan Previous jika bukan Q1)
                const navigationHtml = `
                    <div class="mt-6 flex justify-between">
                        <!-- Previous Button -->
                        <button onclick="App.prevScenario()" 
                                class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-md ${scenario.questionNumber > 1 ? '' : 'opacity-50 cursor-not-allowed'}"
                                ${scenario.questionNumber > 1 ? '' : 'disabled'}>
                            &larr; Sebelumnya
                        </button>
                        
                        <!-- Next/Result Button (Disabled jika belum dijawab) -->
                        <button onclick="App.nextScenario()" 
                                class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 shadow-md ${isAnswered ? '' : 'opacity-50 cursor-not-allowed'}"
                                ${isAnswered ? '' : 'disabled'}>
                            ${scenario.questionNumber < this.maxQuestions ? 'Skenario Selanjutnya &rarr;' : 'Lihat Hasil Akhir &rarr;'}
                        </button>
                    </div>
                `;


                container.innerHTML = `
                    <div class="pb-4 mb-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center">
                        <span class="text-sm font-semibold text-teal-600 bg-teal-100 px-3 py-1 rounded-full mb-2 sm:mb-0">Skenario Ke-${progress}</span>
                        <span class="text-lg font-bold text-gray-700">Skor Terkoreksi: ${currentScore}</span>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-800 mb-3 text-left">${scenario.scenarioTitle}</h2>
                    
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 mb-6 text-left">
                        <p class="font-semibold text-gray-700 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2h2a1 1 0 000-2H9z" clip-rule="evenodd"></path></svg>
                            Narasi Situasi:
                        </p>
                        <p class="text-gray-600 whitespace-pre-line">${scenario.scenarioContext}</p>
                        <p class="text-xs text-gray-400 mt-2">Domain Fokus: <span class="font-medium text-teal-500">${scenario.domain || 'Tidak Diketahui'}</span></p>
                    </div>

                    <p class="text-xl font-bold text-gray-800 mb-4 text-left">${scenario.question}</p>

                    <div id="options-container">
                        ${optionsHtml}
                    </div>

                    ${explanationHtml}
                    ${navigationHtml}
                `;
            },

            showResult() {
                const finalScore = this.score;
                const percentage = (finalScore / this.maxQuestions) * 100;
                
                const domainScores = this.calculateDomainScores();
                const [resultMessage, resultColor, adviceBorderColor, advice, recommendationList] = this.getReviewAndRecommendation(percentage);

                // Kumpulkan data mentah untuk dikirim ke Gemini
                const rawResults = {
                    totalQuestions: this.maxQuestions,
                    correctAnswers: finalScore,
                    overallPercentage: percentage.toFixed(1) + '%',
                    domainPerformance: domainScores,
                    // Tambahkan riwayat jawaban singkat untuk konteks (opsional)
                    answerSummary: this.history.map(h => ({
                        qNum: h.questionNumber,
                        domain: h.domain,
                        isCorrect: h.isCorrect
                    }))
                };
                
                // Minta Gemini menganalisis hasil
                if (!geminiAnalysisResult) {
                    this.analyzeResultsWithGemini(rawResults);
                }

                this.renderResultScreen(finalScore, percentage, domainScores, resultMessage, resultColor, adviceBorderColor, advice, recommendationList);
            },
            
            async analyzeResultsWithGemini(rawResults) {
                // Tampilkan pesan loading di bagian Analisis Kritis
                document.getElementById('gemini-analysis-section').innerHTML = `
                    <div class="text-center py-6">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-2"></div>
                        <p class="text-md font-medium text-purple-600">Mengolah Data. Gemini sedang merumuskan Analisis Kritis...</p>
                    </div>
                `;

                const systemPrompt = "Anda adalah konsultan pendidikan kejuruan yang ahli. Tugas Anda adalah menganalisis data kompetensi seorang guru SMK dan memberikan umpan balik yang **sangat konstruktif** dan **spesifik**. Berikan analisis kritis terperinci (minimal 2 paragraf) tentang domain yang paling lemah (di bawah 60%) dan kembangkan **Rencana Peningkatan Diri 30/60/90 Hari** yang fokus dan aplikatif untuk mengatasi kelemahan tersebut.";

                const userQuery = `Berikut adalah data hasil simulasi UKG Guru Kejuruan: ${JSON.stringify(rawResults)}. Berikan analisis kritis dan rencana peningkatan diri 30/60/90 hari.`;

                const payload = {
                    contents: [{ parts: [{ text: userQuery }] }],
                    systemInstruction: { parts: [{ text: systemPrompt }] },
                    // Tidak menggunakan JSON skema agar respons lebih fleksibel dan naratif
                };

                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                };
                
                try {
                    const response = await fetchWithBackoff(apiUrl, options);
                    const result = await response.json();
                    
                    geminiAnalysisResult = result.candidates?.[0]?.content?.parts?.[0]?.text || 
                                           "Gagal mendapatkan analisis dari Gemini. Coba lagi.";

                } catch (error) {
                    console.error("Gemini Analysis Failed:", error);
                    geminiAnalysisResult = `Gagal menjalankan analisis AI. (Error: ${error.message}).`;
                }
                
                // Setelah selesai, render ulang bagian hasil untuk menampilkan analisis Gemini
                this.renderResultScreen(this.score, (this.score / this.maxQuestions) * 100, this.calculateDomainScores(), this.getReviewAndRecommendation((this.score / this.maxQuestions) * 100)[0], this.getReviewAndRecommendation((this.score / this.maxQuestions) * 100)[1], this.getReviewAndRecommendation((this.score / this.maxQuestions) * 100)[2], this.getReviewAndRecommendation((this.score / this.maxQuestions) * 100)[3], this.getReviewAndRecommendation((this.score / this.maxQuestions) * 100)[4]);
            },
            
            renderResultScreen(finalScore, percentage, domainScores, resultMessage, resultColor, adviceBorderColor, advice, recommendationList) {
                const container = document.getElementById('content-container');
                
                // Membangun HTML Analisis Domain
                const domainAnalysisHtml = Object.keys(domainScores).map(domain => {
                    const data = domainScores[domain];
                    const barColor = data.percentage >= 80 ? 'bg-green-500' : (data.percentage >= 60 ? 'bg-yellow-500' : 'bg-red-500');
                    return `
                        <div class="mb-4">
                            <h4 class="font-bold text-gray-700">${domain} (${data.correct}/${data.total} soal)</h4>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full ${barColor}" style="width: ${data.percentage}%"></div>
                            </div>
                            <p class="text-sm font-semibold text-right mt-1">${data.percentage}% Tepat</p>
                        </div>
                    `;
                }).join('');

                // Detailed History Section (Resume Detail Kinerja)
                const historyDetails = this.history.map(h => `
                    <div class="mb-4 p-4 border rounded-lg ${h.isCorrect ? 'border-green-300 bg-white' : 'border-red-300 bg-white'} shadow-sm text-left">
                        <p class="font-bold text-lg text-gray-800">Skenario ${h.questionNumber} (${h.domain || 'Domain Tidak Diketahui'}): ${h.scenarioTitle}</p>
                        
                        <div class="mt-3">
                            <p class="text-sm font-semibold">Status Jawaban: <span class="font-extrabold ${h.isCorrect ? 'text-green-600' : 'text-red-600'}">${h.isCorrect ? 'Tepat' : 'Kurang Tepat'}</span></p>
                            <p class="text-sm">Pilihan Anda: <span class="font-mono text-gray-600">${h.options[h.userChoiceIndex].label}</span></p>
                            <p class="text-sm">Jawaban Benar: <span class="font-mono text-green-600 font-bold">${h.options[h.correctIndex].label}</span></p>
                            
                            <div class="mt-2 p-3 bg-gray-50 border-l-2 border-teal-400">
                                <p class="text-sm font-bold text-teal-700 mb-1">Penjelasan Profesional:</p>
                                <p class="text-xs text-gray-700">${h.explanation}</p>
                            </div>
                        </div>
                    </div>
                `).join('');

                const recommendationsHtml = recommendationList.map(item => `
                    <li class="mb-2 flex items-start">
                        <svg class="w-5 h-5 text-teal-500 mr-2 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 13.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        <span class="text-gray-700">${item}</span>
                    </li>
                `).join('');
                
                // Tentukan konten analisis Gemini
                const finalGeminiAnalysis = geminiAnalysisResult 
                    ? `<div class="mt-4 text-gray-700 whitespace-pre-line">${geminiAnalysisResult}</div>`
                    : `<p class="text-md font-medium text-purple-600">Menunggu Analisis Kritis dari Gemini...</p>`;


                container.innerHTML = `
                    <div class="text-center py-4">
                        <h1 class="text-3xl font-extrabold text-teal-800 mb-2">LAPORAN HASIL SIMULASI UKG</h1>
                        <p class="text-md text-gray-600 mb-6">Penilaian ini didasarkan pada keputusan pedagogis, profesional, dan industri Anda.</p>

                        <!-- SCORECARD -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            <div class="p-5 rounded-xl border-4 border-teal-500 bg-teal-50 shadow-lg col-span-1 md:col-span-1">
                                <p class="text-lg font-semibold text-teal-800">Skor Akhir</p>
                                <p class="text-5xl font-extrabold my-2 text-teal-900">${finalScore} / ${this.maxQuestions}</p>
                                <p class="text-sm text-teal-600">${percentage.toFixed(1)}%</p>
                            </div>

                            <div class="p-5 rounded-xl border-l-4 ${adviceBorderColor} text-left shadow-lg col-span-1 md:col-span-2">
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Review Kinerja (Level Kompetensi)</h3>
                                <p class="text-xl font-bold ${resultColor} mb-2">${resultMessage}</p>
                                <p class="text-gray-700 mt-2">${advice}</p>
                            </div>
                        </div>
                        
                        <!-- AI ANALYSIS OF DOMAIN PERFORMANCE -->
                        <div class="mt-8 p-6 rounded-xl border-t-4 border-teal-500 bg-teal-50 text-left shadow-lg">
                            <h3 class="text-xl font-extrabold text-teal-800 mb-4 flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"></path></svg>
                                Analisis Kompetensi Detail (Statistik)
                            </h3>
                            <p class="text-gray-700 mb-4">Berikut adalah persentase akurasi Anda berdasarkan domain kompetensi kejuruan:</p>
                            ${domainAnalysisHtml}
                        </div>

                        <!-- GEMINI CRITICAL ANALYSIS AND ACTION PLAN -->
                        <div id="gemini-analysis-section" class="mt-8 p-6 rounded-xl border-t-4 border-purple-600 bg-purple-50 text-left shadow-lg">
                            <h3 class="text-xl font-extrabold text-purple-800 mb-4 flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 20a1 1 0 001 1h2a1 1 0 001-1V3a1 1 0 00-1-1h-2a1 1 0 00-1 1v17zM5 16a1 1 0 001 1h2a1 1 0 001-1V7a1 1 0 00-1-1H6a1 1 0 00-1 1v9zM15 12a1 1 0 001 1h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v7z"></path></svg>
                                Analisis Kritis & Rencana Peningkatan Diri (Oleh Gemini)
                            </h3>
                            ${finalGeminiAnalysis}
                        </div>
                        
                        <div class="mt-8">
                            <button onclick="App.showModeSelection()" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-200 mr-4">
                                Mulai Simulasi Baru
                            </button>
                            <button onclick="document.getElementById('history-section').classList.toggle('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded-lg transition duration-200">
                                Lihat Detail Resume Jawaban
                            </button>
                        </div>
                    </div>

                    
                    
                    <!-- RESUME DETAIL KINERJA -->
                    <div id="history-section" class="mt-10 pt-6 border-t border-gray-200 hidden">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4 text-left">Detail Jawaban Skenario (Resume Detail):</h3>
                        ${historyDetails}
                    </div>
                `;
            },
            
            getReviewAndRecommendation(percentage) {
                let resultMessage = '';
                let resultColor = '';
                let adviceBorderColor = '';
                let advice = '';
                let recommendationList = [];

                if (percentage >= 80) {
                    resultMessage = 'Sangat Kompeten (Level Cemerlang)';
                    resultColor = 'text-green-700';
                    adviceBorderColor = 'border-green-500 bg-green-50';
                    advice = 'Kinerja Anda melampaui standar. Keputusan Anda menunjukkan pemahaman mendalam tentang pedagogi, etika profesional, dan adaptasi industri kejuruan.';
                    recommendationList = [
                        'Menjadi mentor atau fasilitator dalam pelatihan internal guru (In-House Training) khususnya bidang *Soft Skill* Abad ke-21.',
                        'Mengembangkan publikasi ilmiah (PTK) yang berfokus pada kolaborasi DUDI dan penyelarasan kurikulum.',
                        'Mengikuti program sertifikasi industri tingkat lanjutan untuk memperkuat *Kompetensi Profesional Kejuruan*.'
                    ];
                } else if (percentage >= 60) {
                    resultMessage = 'Cukup Kompeten (Level Memuaskan)';
                    resultColor = 'text-yellow-700';
                    adviceBorderColor = 'border-yellow-500 bg-yellow-50';
                    advice = 'Anda memiliki dasar yang solid. Perlu memperkuat pengambilan keputusan di area Kolaborasi Industri dan implementasi kompetensi Abad ke-21 dalam praktik kejuruan.';
                    recommendationList = [
                        'Fokus pada resolusi konflik dan etika penilaian yang spesifik pada konteks Proyek Kejuruan.',
                        'Meningkatkan pemahaman tentang **Kerja Sama Industri**: pelajari SOP MoU (Memorandum of Understanding) dan mekanisme magang industri.',
                        'Mengikuti workshop implementasi Literasi Digital dan Kewirausahaan dalam mata pelajaran kejuruan Anda.'
                    ];
                } else {
                    resultMessage = 'Perlu Peningkatan (Level Dasar)';
                    resultColor = 'text-red-700';
                    adviceBorderColor = 'border-red-500 bg-red-50';
                    advice = 'Terdapat kebutuhan signifikan untuk meninjau kembali prinsip-prinsip dasar dalam penanganan dilema dan integrasi kompetensi kejuruan/industri.';
                    recommendationList = [
                        'Mendalami materi dan praktik baru yang relevan dengan kompetensi keahlian Anda (penguasaan materi spesifik).',
                        'Mengulang modul dasar penanganan pelanggaran disiplin siswa secara humanis dan edukatif.',
                        'Fokus pada pelatihan untuk mengembangkan kemampuan **Berpikir Kritis** dan **Pemecahan Masalah** di kelas praktikum.'
                    ];
                }
                
                return [resultMessage, resultColor, adviceBorderColor, advice, recommendationList];
            }
        };

        // Mengakses App secara global
        window.App = App; 
        
        // Memastikan aplikasi dimulai di layar beranda
        document.addEventListener('DOMContentLoaded', () => {
             // Langsung memanggil showHomeScreen karena konten awal HTML sudah dihapus
             App.showHomeScreen();
        });
    </script>
</body>
</html>
