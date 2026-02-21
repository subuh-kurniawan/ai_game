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
    <title>Simulasi Lab Kimia Sehari-hari</title>
    <!-- Memuat Tailwind CSS untuk styling modern dan responsif -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            line-height: 1.6;
        }
        /* Style untuk Spinner */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #3b82f6;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="p-4 md:p-8 min-h-screen">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-2xl p-6 md:p-10">
        <header class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-blue-700 mb-2">🧪 Simulasi Lab Kimia Sehari-hari</h1>
            <p class="text-lg text-gray-600">Atur kondisi eksperimen dan lihat bagaimana Kimia bekerja!</p>
        </header>

        <section id="input-section" class="mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
            <h2 class="text-2xl font-semibold text-blue-600 mb-4">🔬 Pilih Eksperimen & Atur Kondisi</h2>

            <!-- Pilihan Produk (Eksperimen) -->
            <div class="mb-4">
                <label for="product-select" class="block text-gray-700 font-medium mb-2">Pilih Jenis Reaksi/Eksperimen (Topik Kimia):</label>
                <select id="product-select" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out bg-white text-lg" onchange="updateExperimentVariables()">
                    <option value="Saponifikasi Sabun">Saponifikasi Sabun (Fokus: Stoikiometri & Laju Reaksi)</option>
                    <option value="Netralisasi Cuka">Netralisasi Cuka (Fokus: pH & Titrasi)</option>
                    <option value="Pengendapan Garam">Pengendapan Garam (Fokus: Ksp & Kesetimbangan)</option>
                    <option value="Eksperimen Kustom">🧪 Eksperimen Kustom (Input Bebas)</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">Pilihan ini menentukan bahan baku dan konsep kimia yang diuji.</p>
            </div>

            <!-- Area Variabel Eksperimen Tetap -->
            <div id="experiment-variables" class="grid md:grid-cols-3 gap-4 mt-6">
                <!-- Variabel Eksperimen akan dimuat di sini oleh JavaScript -->
            </div>
            
            <!-- Area Input Kustom Eksperimen -->
            <div id="custom-experiment-input" class="mt-6 hidden">
                <label for="custom-formula-input" class="block text-gray-700 font-medium mb-2">Deskripsikan Eksperimen Kustom Anda:</label>
                <textarea id="custom-formula-input" rows="8" placeholder="Contoh: Saya ingin mencampurkan 100ml larutan Besi(III) Klorida (FeCl3) 0.5M dengan 100ml Natrium Hidroksida (NaOH) 1M. Suhu awal 25°C. Prediksi endapan yang terbentuk dan jelaskan konsep Mol/Stoikiometri yang terlibat."
                class="w-full p-4 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out text-base bg-yellow-50"></textarea>
                <p class="text-sm text-yellow-700 mt-1">Gunakan area ini untuk eksperimen di luar menu. Jelaskan bahan, jumlah, dan kondisi (suhu, katalis, dll.) selengkap mungkin.</p>
            </div>

            <!-- Tombol Aksi -->
            <button id="generate-button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition duration-300 ease-in-out transform hover:scale-105 flex items-center justify-center mt-6" onclick="generateNarrative()">
                <span id="button-text">▶️ Lakukan Simulasi Reaksi!</span>
                <div id="loading-spinner" class="spinner ml-3 hidden"></div>
            </button>
        </section>

        <!-- Area Output Narasi -->
        <section id="output-section" class="p-6 bg-gray-50 rounded-lg border border-gray-200" style="display: none;">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Hasil Percobaan & Analisis Kimia
            </h2>
            <div id="narrative-output" class="text-gray-800 text-base leading-relaxed whitespace-pre-wrap">
                <!-- Narasi hasil simulasi akan dimuat di sini -->
            </div>
            
            <!-- Sumber Grounding -->
            <div id="sources-output" class="mt-6 border-t pt-4 border-gray-300 hidden">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Sumber Kimia (Grounding):</h3>
                <ul id="source-list" class="text-sm text-gray-500 space-y-1">
                    <!-- Sumber akan dimuat di sini -->
                </ul>
            </div>
        </section>

    </div>

    <script type="text/javascript">
        // Inisialisasi Kunci API dan Model
        const apiKey = "<?php echo $apiKey; ?>"; // Dibiarkan kosong, akan diisi oleh lingkungan Canvas
        const modelName = "<?php echo $model; ?>";
       // Correct way to embed the variables into the URL in JavaScript:
const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${modelName}:generateContent?key=${apiKey}`;
        const productSelect = document.getElementById('product-select');
        const experimentVariablesDiv = document.getElementById('experiment-variables');
        const customInputDiv = document.getElementById('custom-experiment-input'); // Ambil elemen baru
        const customFormulaInput = document.getElementById('custom-formula-input'); // Ambil elemen textarea baru
        const generateButton = document.getElementById('generate-button');
        const loadingSpinner = document.getElementById('loading-spinner');
        const buttonText = document.getElementById('button-text');
        const narrativeOutput = document.getElementById('narrative-output');
        const outputSection = document.getElementById('output-section');
        const sourcesOutput = document.getElementById('sources-output');
        const sourceList = document.getElementById('source-list');
        const errorMessage = document.getElementById('error-message'); 

        /**
         * Mendefinisikan variabel eksperimen berdasarkan jenis eksperimen yang dipilih.
         */
        const experiments = {
            "Saponifikasi Sabun": [
                { id: "ratio", label: "Perbandingan Stoikiometri (Minyak:Basa)", options: [
                    { value: "Rasio Sub-Stoikiometri (Kelebihan Minyak)", text: "Kurang Basa (Sabun Lunak/Berminyak)" },
                    { value: "Rasio Stoikiometri Tepat", text: "Rasio Tepat (Sabun Terbaik)" },
                    { value: "Rasio Super-Stoikiometri (Kelebihan Basa)", text: "Kelebihan Basa (Sabun Keras/Iritatif)" }
                ], concept: "Stoikiometri & Yield" },
                { id: "temperature", label: "Suhu Proses", options: [
                    { value: "25°C (Dingin)", text: "Dingin (Laju Reaksi Lambat)" },
                    { value: "45°C (Hangat)", text: "Hangat (Laju Reaksi Optimal)" },
                    { value: "70°C (Panas)", text: "Panas (Reaksi Cepat, Risiko Hangus)" }
                ], concept: "Laju Reaksi & Energi Aktivasi" },
                { id: "agitation", label: "Pengadukan", options: [
                    { value: "Lambat", text: "Lambat (Tabrakan Molekul Kurang)" },
                    { value: "Cepat", text: "Cepat (Tabrakan Molekul Efektif)" }
                ], concept: "Laju Reaksi & Teori Tumbukan" }
            ],
            "Netralisasi Cuka": [
                { id: "concentration", label: "Konsentrasi Basa (NaOH)", options: [
                    { value: "0.01 M", text: "Sangat Encer" },
                    { value: "0.1 M", text: "Standar" },
                    { value: "1.0 M", text: "Sangat Pekat" }
                ], concept: "Konsentrasi & Titrasi" },
                { id: "indicator", label: "Indikator pH", options: [
                    { value: "Metil Merah (pH 4.4-6.2)", text: "Metil Merah" },
                    { value: "Fenolftalein (pH 8.3-10.0)", text: "Fenolftalein (Pilihan Tepat)" }
                ], concept: "Kurva Titrasi & Titik Ekuivalen" },
                { id: "reactant_temp", label: "Suhu Awal Reaktan", options: [
                    { value: "20°C", text: "Rendah" },
                    { value: "50°C", text: "Tinggi" }
                ], concept: "Termokimia (Eksoterm)" }
            ],
            "Pengendapan Garam": [
                { id: "ion_concentration", label: "Konsentrasi Ion Pereaksi", options: [
                    { value: "Di bawah Ksp", text: "Jenuh (Belum Mengendap)" },
                    { value: "Sedikit di atas Ksp", text: "Lewat Jenuh (Mengendap Halus)" },
                    { value: "Jauh di atas Ksp", text: "Sangat Jenuh (Mengendap Banyak)" }
                ], concept: "Ksp & Kelarutan" },
                { id: "common_ion", label: "Efek Ion Senama", options: [
                    { value: "Ya (Ada Ion Senama)", text: "Ada Ion Senama (Pengendapan Cepat)" },
                    { value: "Tidak (Tanpa Ion Senama)", text: "Tanpa Ion Senama" }
                ], concept: "Kesetimbangan & Ion Senama" },
                { id: "temperature_ksp", label: "Suhu Larutan", options: [
                    { value: "Dingin (Kelarutan Turun)", text: "Dingin" },
                    { value: "Panas (Kelarutan Naik)", text: "Panas" }
                ], concept: "Prinsip Le Chatelier" }
            ]
        };

        /**
         * Menggambar ulang variabel eksperimen di UI berdasarkan pilihan produk.
         */
        function updateExperimentVariables() {
            const selectedExperiment = productSelect.value;
            
            if (selectedExperiment === "Eksperimen Kustom") {
                experimentVariablesDiv.classList.add('hidden');
                customInputDiv.classList.remove('hidden');
                return;
            }

            // Untuk eksperimen tetap
            customInputDiv.classList.add('hidden');
            experimentVariablesDiv.classList.remove('hidden');

            const variables = experiments[selectedExperiment];
            experimentVariablesDiv.innerHTML = '';
            
            if (variables) {
                variables.forEach(variable => {
                    const html = `
                        <div class="p-4 bg-white rounded-lg shadow-md border border-gray-100">
                            <label for="${variable.id}" class="block text-gray-700 font-semibold mb-2 text-sm">${variable.label} (<span class="text-blue-500">${variable.concept}</span>):</label>
                            <select id="${variable.id}" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out bg-gray-50 text-base">
                                ${variable.options.map(option => `<option value="${option.value}">${option.text}</option>`).join('')}
                            </select>
                        </div>
                    `;
                    experimentVariablesDiv.innerHTML += html;
                });
            }
        }

        // Panggil saat halaman dimuat untuk mengatur variabel awal
        window.onload = function() {
            updateExperimentVariables();
        };

        /**
         * Mengumpulkan data eksperimen dari UI.
         */
        function collectExperimentData() {
            const selectedExperiment = productSelect.value;
            const data = {
                experiment: selectedExperiment,
                variables: {},
                custom_input: null
            };
            
            if (selectedExperiment === "Eksperimen Kustom") {
                data.custom_input = customFormulaInput.value.trim();
                return data;
            }

            const variables = experiments[selectedExperiment];
            variables.forEach(variable => {
                const element = document.getElementById(variable.id);
                if (element) {
                    data.variables[variable.id] = element.value;
                }
            });
            return data;
        }

        /**
         * Konfigurasi System Instruction untuk Gemini AI (Simulasi).
         */
        function getSystemInstruction(experimentData) {
            const experimentType = experimentData.experiment;
            
            if (experimentType === "Eksperimen Kustom") {
                return `Anda adalah Ahli Kimia Reaksi dan Simulator Laboratorium AI. Tugas Anda adalah menganalisis dan menarasikan hasil dari eksperimen kimia kustom yang dijelaskan oleh siswa.

Deskripsi Eksperimen Kustom dari Siswa: "${experimentData.custom_input}"

Berikan respons dalam Bahasa Indonesia yang profesional, mendidik, dan naratif.

Fokus analisis harus mencakup konsep kimia SMA/SMK yang relevan dengan deskripsi siswa, seperti Stoikiometri, Reaksi Ganda (Pengendapan/Netralisasi), Kesetimbangan, atau Laju Reaksi.

FORMAT OUTPUT (HARUS TEPAT):
Buat narasi 3 paragraf yang hidup dan menarik.
1.  **Paragraf 1 (Set-up Eksperimen Kustom):** Ulangi secara ringkas eksperimen yang akan dilakukan, dan identifikasi jenis reaksi kimia (misalnya, "Reaksi Pengendapan Ganda").
2.  **Paragraf 2 (Prediksi Reaksi Kimia):** Deskripsikan langkah-langkah reaksi yang terjadi secara naratif. Analisis proses kimia inti (misalnya, perhitungan mol, pembatas, atau perubahan entalpi) yang menentukan hasil.
3.  **Paragraf 3 (Hasil Akhir & Pembelajaran):** Simpulkan secara tegas hasil akhir (misalnya, massa endapan yang terbentuk atau perubahan pH). Berikan penjelasan dan pembelajaran yang berfokus pada konsep kimia SMA/SMK di balik hasil tersebut.

JANGAN gunakan heading atau bullet point. Gunakan hanya paragraf. Prediksi harus didasarkan pada prinsip kimia yang valid.`;
            }

            const vars = experimentData.variables;
            let variableSummary = Object.keys(vars).map(key => {
                const label = experiments[experimentType].find(v => v.id === key)?.label || key;
                return `${label}: ${vars[key]}`;
            }).join(', ');
            
            return `Anda adalah Ahli Kimia Reaksi dan Simulator Laboratorium AI. Tugas Anda adalah memprediksi dan menarasikan hasil dari eksperimen kimia yang diatur oleh siswa.

Berikan respons dalam Bahasa Indonesia yang profesional, mendidik, dan naratif.

Eksperimen yang dilakukan: "${experimentType}".
Kondisi yang diatur siswa: ${variableSummary}.

Fokus analisis:
- Reaksi Sabun: Fokus pada Laju Reaksi (Suhu/Pengadukan) dan Stoikiometri.
- Reaksi Netralisasi: Fokus pada Kurva Titrasi, pH, dan Termokimia.
- Reaksi Pengendapan: Fokus pada Konstanta Hasil Kali Kelarutan (Ksp), Prinsip Le Chatelier, dan Efek Ion Senama.

FORMAT OUTPUT (HARUS TEPAT):
Buat narasi 3 paragraf yang hidup dan menarik.
1.  **Paragraf 1 (Set-up Eksperimen):** Jelaskan bahan yang tersedia dan kondisi awal yang dipilih oleh siswa (Suhu, Rasio, dll.), serta tujuan kimia dari eksperimen ini.
2.  **Paragraf 2 (Prediksi Reaksi Kimia):** Deskripsikan secara naratif apa yang terjadi saat reaktan dicampur berdasarkan kondisi yang dipilih. Analisis proses kimia inti (misalnya, kinetika, kesetimbangan, atau stoikiometri) yang menentukan hasil.
3.  **Paragraf 3 (Hasil Akhir & Pembelajaran):** Simpulkan secara tegas hasil akhir produk (Misalnya: "Sabun Gagal/Berhasil/Terlalu Keras", "Endapan Terlalu Cepat/Lambat"). Berikan penjelasan dan pembelajaran yang berfokus pada konsep kimia SMA/SMK di balik hasil tersebut.

JANGAN gunakan heading atau bullet point. Gunakan hanya paragraf. Prediksi harus didasarkan pada prinsip kimia yang valid.`;
        }


        /**
         * Fungsi utama untuk memanggil Gemini API dan mendapatkan narasi.
         */
        async function generateNarrative() {
            const experimentData = collectExperimentData();
            
            // Validasi Input Kustom
            if (experimentData.experiment === "Eksperimen Kustom" && experimentData.custom_input.length < 20) {
                 // Langsung tampilkan pesan error jika input kustom kosong/terlalu pendek
                 narrativeOutput.textContent = '⚠️ Mohon jelaskan eksperimen kustom Anda di kotak deskripsi kustom, minimal 20 karakter, agar AI dapat menganalisisnya dengan baik.';
                 outputSection.style.display = 'block';
                 return;
            }

            // Nonaktifkan tombol dan tampilkan spinner
            generateButton.disabled = true;
            buttonText.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');

            narrativeOutput.textContent = '';
            outputSection.style.display = 'none';
            sourcesOutput.classList.add('hidden');

            const systemPrompt = getSystemInstruction(experimentData);
            
            let userQuery;
            if (experimentData.experiment === "Eksperimen Kustom") {
                userQuery = `Lakukan simulasi dan analisis untuk eksperimen kustom berikut: ${experimentData.custom_input}`;
            } else {
                userQuery = `Simulasikan hasil eksperimen "${experimentData.experiment}" dengan kondisi yang saya pilih: ${JSON.stringify(experimentData.variables)}`;
            }

            const payload = {
                contents: [{ parts: [{ text: userQuery }] }],
                // Menggunakan Google Search untuk Grounding (agar analisisnya berdasarkan data kimia nyata)
                tools: [{ "google_search": {} }],
                systemInstruction: {
                    parts: [{ text: systemPrompt }]
                },
            };

            const maxRetries = 5;
            let delay = 1000;

            for (let i = 0; i < maxRetries; i++) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        if (response.status === 429 && i < maxRetries - 1) {
                            // Exponential backoff
                            await new Promise(resolve => setTimeout(resolve, delay));
                            delay *= 2;
                            continue; // Coba lagi
                        }
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const result = await response.json();
                    const candidate = result.candidates?.[0];

                    if (candidate && candidate.content?.parts?.[0]?.text) {
                        const text = candidate.content.parts[0].text;
                        narrativeOutput.textContent = text;
                        outputSection.style.display = 'block';

                        // Ekstrak sumber (citations) jika ada grounding
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
                        
                        // Tampilkan Sumber
                        sourceList.innerHTML = '';
                        if (sources.length > 0) {
                            sources.forEach((source, index) => {
                                const li = document.createElement('li');
                                li.innerHTML = `<a href="${source.uri}" target="_blank" class="text-blue-500 hover:underline">${index + 1}. ${source.title}</a>`;
                                sourceList.appendChild(li);
                            });
                            sourcesOutput.classList.remove('hidden');
                        } else {
                            sourcesOutput.classList.add('hidden');
                        }

                        // Keluar dari loop setelah berhasil
                        break; 
                    } else {
                        narrativeOutput.textContent = '❌ Maaf, Gemini gagal menghasilkan narasi. Coba lagi.';
                        outputSection.style.display = 'block';
                    }
                } catch (error) {
                    console.error("Error generating content:", error);
                    narrativeOutput.textContent = `❌ Terjadi kesalahan koneksi atau server: ${error.message}`;
                    outputSection.style.display = 'block';
                }
            }
            
            // Aktifkan kembali tombol
            generateButton.disabled = false;
            buttonText.classList.remove('hidden');
            loadingSpinner.classList.add('hidden');
        }

    </script>
</body>
</html>
