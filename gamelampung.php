<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Game Belajar Bahasa Lampung</title>
    <!-- Muat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Muat Font Inter -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        
        /* Keyframes untuk animasi pulse lambat (untuk Siger) */
        @keyframes pulse-slow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.03); opacity: 0.95; }
        }

        body {
            font-family: 'Inter', sans-serif;
            /* Latar Belakang Bernuansa Lampung (Deep Green/Blue dengan Aksen Emas) */
            background: radial-gradient(circle at top left, #154360, #0A1931); 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Kelas Kustom untuk Efek Frosty Glass (Kaca Buram) yang Ditingkatkan pada Kontainer Utama */
        .frosty-glass-main {
            background-color: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(25px) saturate(200%);
            -webkit-backdrop-filter: blur(25px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 60px 0 rgba(0, 0, 0, 0.6);
        }
        
        .siger-icon {
            animation: pulse-slow 5s infinite ease-in-out;
            filter: drop-shadow(0 0 5px rgba(243, 156, 18, 0.8));
        }

        /* --- Styling Card Baru dengan Background Image --- */
        .card-link {
            /* Dimensi dan Tata Letak */
            display: flex;
            flex-direction: column;
            justify-content: flex-end; /* Konten di bawah */
            min-height: 250px; /* Tinggi minimum kartu */
            
            /* Efek Visual */
            border-radius: 1rem; /* rounded-xl */
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease-in-out;
            
            /* Properti Background */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
            /* Overlay agar teks tetap terbaca */
            position: relative;
            text-decoration: none;
            color: white;
        }

        .card-link:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 20px 50px rgba(243, 156, 18, 0.8); /* Glow Emas yang lebih kuat */
        }
        
        /* Kontainer teks di dalam kartu */
        .card-content-overlay {
            padding: 1.5rem;
            width: 100%;
            /* Efek Frosty Glass untuk teks di atas gambar */
            background-color: rgba(0, 0, 0, 0.4); /* Overlay gelap */
            backdrop-filter: blur(10px) saturate(180%);
            -webkit-backdrop-filter: blur(10px) saturate(180%);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Menghilangkan CSS gambar lama */
        .card-image-container, .card-image {
            display: none;
        }
    </style>
</head>
<body>

    <!-- Main Container ala macOS Dashboard -->
    <div class="w-full max-w-6xl frosty-glass-main p-8 md:p-12 rounded-3xl text-white">

        <!-- Header: Sentuhan Budaya Lampung -->
        <header class="text-center mb-10">
            <div class="text-5xl mb-3 text-[#F39C12] tracking-wider">
                <!-- SVG Siger Sederhana untuk sentuhan Lampung, kini dengan animasi -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 inline-block siger-icon">
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm0 17.5a7.75 7.75 0 005.15-13.483l-.711 1.23a6.75 6.75 0 01-8.878 0l-.711-1.23A7.75 7.75 0 0012 19.75zM12 7a2 2 0 100 4 2 2 0 000-4z" clip-rule="evenodd" />
                    <path d="M10.875 16.5A.875.875 0 0112 15.625h.25a.875.875 0 01.875.875v.25H10.875v-.25z" />
                </svg>
            </div>
            <h1 class="text-3xl md:text-5xl font-extrabold text-[#F39C12] drop-shadow-lg">
                <span class="text-white">Portal Game</span> Belajar Bahasa Lampung
            </h1>
            <p class="text-lg mt-2 text-white/70">Pilih modul permainan untuk memulai petualangan belajar Anda!</p>
        </header>

        <!-- Grid Kartu Game (Responsive) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">

            <!-- Card 1: Mode Game 1 -->
            <a href="lampung.php" 
               class="card-link"
               style="background-image: url('https://radartv.disway.id/upload/889d4660d67025ac6dbdbc353918d322.jpg'), url('https://placehold.co/720x400/154360/ffffff?text=Aksara+Digital');"
               alt="Pakaian Adat Lampung">
                <div class="card-content-overlay">
                    <h2 class="text-2xl font-bold mb-1">Mode Game 1</h2>
                </div>
            </a>

            <!-- Card 2: Mode Game 2 -->
            <a href="lampung2.php" 
               class="card-link"
               style="background-image: url('https://www.batamnews.co.id/foto_berita/2023/06/2023-06-09-kekayaan-budaya-lampung-mengagumkan-dengan-lima-suku-asli-yang-berbeda.jpg'), url('https://placehold.co/720x400/154360/ffffff?text=Kamus+Kata');"
               alt="Lima Suku Lampung">
                <div class="card-content-overlay">
                    <h2 class="text-2xl font-bold mb-1">Mode Game 2</h2>
                </div>
            </a>

            <!-- Card 3: Mode Game 3 -->
            <a href="lampung3.php" 
               class="card-link"
               style="background-image: url('https://jayakartanews.com/wp-content/uploads/2022/07/sekala-bekak.jpg'), url('https://placehold.co/720x400/154360/ffffff?text=Dialog+Sederhana');"
               alt="Sekala Bekak">
                <div class="card-content-overlay">
                    <h2 class="text-2xl font-bold mb-1">Mode Game 3</h2>
                </div>
            </a>

            <!-- Card 4: Mode Game 4 -->
            <a href="lampung4.php" 
               class="card-link"
               style="background-image: url('https://media.suara.com/pictures/1600x840/2024/07/08/30728-ilustrasi-pernikahan-adat-lampung.jpg'), url('https://placehold.co/720x400/154360/ffffff?text=Hitung+Cepat');"
               alt="Pernikahan Adat Lampung">
                <div class="card-content-overlay">
                    <h2 class="text-2xl font-bold mb-1">Mode Game 4</h2>
                </div>
            </a>

            <!-- Card 5: Mode Game 5 -->
            <a href="#budaya" 
               class="card-link"
               style="background-image: url('https://akcdn.detik.net.id/visual/2020/09/27/nikita-willy-1_43.jpeg?w=720&q=90'), url('https://placehold.co/720x400/154360/ffffff?text=Kuis+Budaya');"
               alt="Gadis Lampung">
                <div class="card-content-overlay">
                    <h2 class="text-2xl font-bold mb-1">Mode Game 5</h2>
                </div>
            </a>

            <!-- Card 6: Mode Game 6 -->
            <a href="#ujian" 
               class="card-link"
               style="background-image: url('https://radartv.disway.id/upload/889d4660d67025ac6dbdbc353918d322.jpg'), url('https://placehold.co/720x400/154360/ffffff?text=Tes+Kemahiran');"
               alt="Tes Kemahiran Akhir">
                <div class="card-content-overlay">
                    <h2 class="text-2xl font-bold mb-1">Mode Game 6</h2>
                </div>
            </a>

        </div>

        <!-- Footer ala macOS Dock (dengan sentuhan Tapis) -->
        <footer class="mt-12 pt-6 border-t border-white/10 text-center text-sm text-white/50">
            <div class="inline-block px-8 py-3 rounded-xl frosty-glass-main">
                <p>
                    <span class="text-[#F39C12] mr-2 font-semibold">YaTa!</span> | Hak Cipta &copy; 2025 Portal Pembelajaran Lampung.
                </p>
            </div>
        </footer>

    </div>

</body>
</html>
