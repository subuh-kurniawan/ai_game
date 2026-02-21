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
<title>AI Yata: Gerbang Game Masa Depan</title>
<script src="https://cdn.tailwindcss.com"></script>

<!-- Open Graph -->
<meta property="og:title" content="AI Yata: Gerbang Game Masa Depan">
<meta property="og:description" content="Masuki dunia game futuristik dengan AI Yata. Pelajari, mainkan, dan temukan strategi masa depan!">
<meta property="og:image" content="<?= $ogImage; ?>">
<meta property="og:url" content="<?= $currentUrl; ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="AI Yata">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="AI Yata: Gerbang Game Masa Depan">
<meta name="twitter:description" content="Masuki dunia game futuristik dengan AI Yata. Pelajari, mainkan, dan temukan strategi masa depan!">
<meta name="twitter:image" content="<?= $ogImage; ?>">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');

      body {
    font-family: 'Inter', sans-serif;
    overflow-x: hidden;
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
       #parallax-container {
            width: 100vw;
            height: 100vh;
            position: relative;
            
            /* --- 3D EFFECT CSS --- */
            perspective: 1200px; /* Jarak perspektif untuk efek 3D */
            transform-style: preserve-3d; /* Memastikan elemen anak di render dalam ruang 3D */
            /* --------------------- */
        }
        .parallax-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%; /* Disesuaikan untuk 3D */
            height: 100%;
            transition: transform 0.1s linear; /* Smooth transition for parallax */
            pointer-events: none;
            will-change: transform;
            transform-origin: center center; /* Titik asal transformasi */
        }
        
        /* Layer 1: Inti Galaksi Utama - PALING JAUH */
        #layer-galaxy-core {
            background: radial-gradient(circle at 50% 50%, 
                        rgba(255, 220, 150, 0.4) 0%, 
                        rgba(255, 165, 0, 0.2) 20%, 
                        transparent 40%);
            filter: blur(100px);
            opacity: 0.8;
            animation: pulse-glow 8s infinite alternate; 
            
            /* 3D Static Position: Dorong ke belakang, skala agar mengisi view */
            transform: translateZ(-800px) scale(2); 
        }

        /* Layer 2: Lengan Spiral Galaksi - TENGAH */
        #layer-galaxy-arms {
            background: radial-gradient(ellipse at 70% 30%, 
                        rgba(120, 180, 255, 0.3) 0%, 
                        transparent 40%),
                        radial-gradient(ellipse at 30% 70%, 
                        rgba(200, 100, 255, 0.3) 0%, 
                        transparent 40%);
            filter: blur(80px);
            opacity: 0.6;
            animation: subtle-rotate 30s linear infinite; 

            /* 3D Static Position */
            transform: translateZ(-400px) scale(1.4);
        }

        /* Layer 3: Bintang dan Objek Acak - DEKAT */
        #layer-random-objects {
             /* Objek bintang diisi oleh JS */
            
            /* 3D Static Position */
            transform: translateZ(-200px) scale(1.2);
        }

        /* Layer 4: Foreground - PALING DEKAT DENGAN KONTEN */
        #layer-foreground {
            /* 3D Static Position */
            transform: translateZ(-50px) scale(1.05);
        }

        /* Objek Bintang dan Debu */
        .star-object {
            position: absolute;
            background-color: white;
            border-radius: 50%;
            opacity: 0; 
            animation: appear-fade-blink 5s ease-out infinite;
            pointer-events: none;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        }

        /* Animasi untuk Inti Galaksi */
        @keyframes pulse-glow {
            from { opacity: 0.8; }
            to { opacity: 0.9; }
        }

        /* Animasi untuk Lengan Galaksi */
        @keyframes subtle-rotate {
            from { transform: translateZ(-400px) scale(1.4) rotate(0deg); }
            to { transform: translateZ(-400px) scale(1.4) rotate(360deg); }
        }

        /* Animasi untuk bintang random */
        @keyframes appear-fade-blink {
            0% { opacity: 0; transform: scale(0.5); }
            10% { opacity: 0.8; transform: scale(1.0); }
            50% { opacity: 0.7; transform: scale(0.9); }
            100% { opacity: 0; transform: scale(0.7); }
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
    </style>
</head>
<body>
 <!-- Container Utama Paralaks --><div id="parallax-container" class="flex items-center justify-center">

        <!-- Layer 1: Paling Belakang (Kecepatan 5) --><div id="layer-galaxy-core" class="parallax-layer" data-speed="5"></div>

        <!-- Layer 2: Lengan Galaksi Utama (Kecepatan 15) --><div id="layer-galaxy-arms" class="parallax-layer" data-speed="15"></div>

        <!-- Layer 3: Bintang dan Objek Acak (Kecepatan 25) --><div id="layer-random-objects" class="parallax-layer" data-speed="25"></div>

        <!-- Layer 4: Foreground (Kecepatan 35) --><div id="layer-foreground" class="parallax-layer flex items-center justify-center" data-speed="35">
             <div class="w-40 h-40 bg-gradient-to-br from-purple-700 to-indigo-900 rounded-full opacity-60 filter blur-xl absolute top-[70%] left-[20%]" style="box-shadow: 0 0 60px rgba(100, 50, 200, 0.7);"></div>
             <div class="w-24 h-24 bg-gradient-to-tr from-cyan-500 to-blue-700 rounded-full opacity-50 filter blur-lg absolute top-[30%] right-[15%]" style="box-shadow: 0 0 40px rgba(50, 150, 200, 0.6);"></div>
        </div>
    <!-- Main Container -->
    <div class="min-h-screen pt-20 pb-12 px-4 sm:px-8 lg:px-16 relative z-10">

        <!-- Header -->
       <header class="fixed top-0 left-0 w-full z-20 p-4 frosted-glass shadow-lg">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <!-- Logo + Judul -->
        <div class="flex items-center space-x-3 animate-fade-in-up initial-hidden text-pop" style="animation-delay: 0.1s;">
            <img src="../admin/foto/<?= $data['logo'] ?>" alt="Logo AI YaTa" class="w-10 h-10 rounded-full border border-white/30 shadow-md">
            <h1 class="text-xl font-bold text-white tracking-widest">AI YaTa</h1>
        </div>

        <!-- Navigasi -->
        <nav class="flex space-x-4 animate-fade-in-up initial-hidden" style="animation-delay: 0.2s;">
            <a href="#" class="text-gray-100 hover:text-indigo-400 transition text-pop">Beranda</a>
            <a href="#" class="text-gray-100 hover:text-indigo-400 transition text-pop">Tentang</a>
            <a href="#" class="px-3 py-1 bg-indigo-600 rounded-full text-white hover:bg-indigo-700 transition text-pop">Masuk</a>
        </nav>
    </div>
</header>


        <!-- Hero -->
      <section class="max-w-6xl mx-auto mb-20 animate-fade-in-up initial-hidden" style="animation-delay: 0.5s;">
           
        </section>


        <!-- Featured CTA -->
        <section class="max-w-6xl mx-auto mb-20 animate-fade-in-up initial-hidden" style="animation-delay: 0.5s;">
            <div class="frosted-glass liquid-card p-8 md:p-12 text-center border-indigo-500/30">
                <p class="text-sm uppercase tracking-widest text-yellow-400 font-semibold mb-4 text-pop">Eksklusif: Peluncuran Beta</p>
                <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-4 leading-tight text-pop">
                    Coba <span class="text-gray-100">Gerbang Game Berbasis AI</span> Sekarang
                </h3>
                <p class="text-lg text-white max-w-3xl mx-auto mb-8 text-pop">
                    Temukan masa depan hiburan interaktif, di mana setiap dunia dan karakter diprogram untuk belajar, beradaptasi, dan berevolusi bersama Anda.
                </p>
                <!-- Tambahkan di atas grid card -->
<div class="max-w-7xl mx-auto mb-8">
    <input type="text" id="search-input" placeholder="Cari game..." 
        class="w-full p-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-900">
</div>

            </div>
        </section>

        <!-- Game Cards Grid -->
        <section class="max-w-7xl mx-auto">
            <h3 class="text-3xl font-bold text-white mb-10 text-center animate-fade-in-up initial-hidden text-pop" style="animation-delay: 0.7s;">Jelajahi Dunia Permainan</h3>
            
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Contoh Card -->
                 <a href="gamelampung.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://radartv.disway.id/upload/889d4660d67025ac6dbdbc353918d322.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Game Berbahasa Lampung</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Alat bantu inovatif bagi siswa untuk berlatih keterampilan berbahasa Lampung
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                        
                    </div>
                </a>
                 <a href="teamw.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://asset.kompas.com/crops/cL0CEILyNOdQ51oOnNvwOiLdgwU=/0x0:893x595/1200x800/data/photo/2022/10/12/634633e8a46d0.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Team Work Simulation</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Alat bantu inovatif bagi siswa untuk berlatih keterampilan kolaborasi dalam tim work
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                        
                    </div>
                </a>
                <a href="proyek.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://asset.kompas.com/crops/tjh_r4LkieToajTkdBqRSohZLtQ=/13x0:703x460/1200x800/data/photo/2020/01/26/5e2d5da57d4cd.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Monopoli Edukasi Softskill</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Mainkan game Monopoli untuk melatih Softskill kamu di dunia SMK
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                        
                    </div>
                </a>
                <a href="petualanganbahasa.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://assets.promediateknologi.id/crop/0x0:0x0/750x500/webp/photo/2022/11/28/16361470.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Petualangan Bahasa Indonesia</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Alat bantu inovatif bagi siswa untuk berlatih keterampilan bahasa Indonesia dengan bantuan AI.
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                        
                    </div>
                </a>
                 <a href="petualanganEn.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS4eSmS5VGzD4EAl8OrIddY3oXq8S9eVe4gFQ&s" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Petualangan Bahasa Inggris</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Alat bantu inovatif bagi siswa untuk berlatih keterampilan bahasa Inggris dengan bantuan AI.
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                        
                    </div>
                </a>
                 <a href="gameguru.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS8Q8-w_6Pc1egjiVyoUkDkekest5s61n-luw&s" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Problem Solving Interaktif Guru</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Alat bantu inovatif bagi guru untuk melatih & memantau kemampuan berpikir kritis siswa berbasis AI.
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                        
                    </div>
                </a>
                 <!-- Contoh Card -->
                <a href="investigasi.php" target="_blank"  class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://www.integrity-indonesia.com/wp-content/uploads/sites/3/2019/05/3-kinds-of-business-investigations-that-can-save-your-companies.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Game investigasi Kasus</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Alat bantu inovatif bagi Siswa dan guru untuk melatih kemampuan investigasi kasusu dengan berpikir kritis AI.
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                 <a href="sop.php" target="_blank"  class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR45n1q0rNB67VSXX20uOsBv3uIm6BsPTIPfg&s" 
                                 alt="Gambar Cyber Chess" 
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">SOP Crisis Management</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           SOP ini dirancang untuk memastikan penanganan cepat, terukur, dan edukatif terhadap setiap situasi krisis yang terjadi dalam pengembangan atau penggunaan game edukasi berbasis pembelajaran siswa SMK
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                 <a href="sop2.php" target="_blank"  class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQoLsa-MtoL1pjh9k8udrY-lDlwnrBx-bZkuQ4T1jVVBhhdwjITWnro5pgUnMxrlUcvWIo&usqp=CAU" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">SOP Crisis Management V2</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           SOP ini dirancang untuk memastikan penanganan cepat, terukur, dan edukatif terhadap setiap situasi krisis yang terjadi dalam pengembangan atau penggunaan game edukasi berbasis pembelajaran siswa SMK
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                 <!-- Contoh Card -->
                <a href="gamesiswa.php" target="_blank"  class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://media.gettyimages.com/id/1405048341/video/a-beautiful-smiling-african-american-scientist-in-a-modern-lab-working-with-a-hud-screen.jpg?s=640x640&k=20&c=uc1C0NciUVr0HJySs2U25aeZWWukX8isPhqgEtqI6Rg=" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Problem Solving Interaktif Siswa</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Alat bantu inovatif bagi guru untuk melatih & memantau kemampuan berpikir kritis siswa berbasis AI.
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                 <!-- Contoh Card -->
                <a href="MC.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://eva.id/wp-content/uploads/2024/04/Mengenal-Lebih-Dekat-Tugas-Customer-Service-Representative.png" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Simulasi Custumer Service</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game yang mengasyikan yang menjadikan pemain sebagai custumer service 
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a> <!-- Contoh Card -->
                 <a href="kimia.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://img.okezone.com/content/2022/02/09/624/2544653/3-ahli-kimia-yang-populer-dari-indonesia-siapa-saja-uwL03lPXB9.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Game Simulasi Kimia</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game yang mengasyikan yang menjadikan pemain sebagai seorang kimiawan 
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a> <!-- Contoh Card -->
                 <a href="wawancara.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://www.sisternet.co.id/assets/images/632e52207c381d2c50448d8fa2acb41a.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy" loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Simulasi Wawancara Kerja</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game yang mengasyikan yang menjadikan pemain dalam Wawancara kerja
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a> <!-- Contoh Card -->
                <a href="pembukuan.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://sleekr.co/wp-content/uploads/2017/09/calculator-385506_1920-1.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy" loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Jurnal Cerdas</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game yang mengasyikan Akuntansi dalam mensimulasikan Jurnal Cerdas
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a> <!-- Contoh Card -->
                 <a href="vocab.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ7wxzR9fZXJib_U08ryHIzsTDpl6ynBVU4zA&s" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">English EduScrabble</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Asah kosakata Bahasa Indonesia kamu sambil bermain! Bentuk kata dari huruf acak dan kumpulkan poin.
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a> <!-- Contoh Card -->
                <a href="karir.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://www.aeccglobal.co.id/images/easyblog_articles/653/Blog-Banner.png" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Career Quest</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Jelajahi pilihan kariermu, ambil keputusan cerdas, dan temukan profesi yang paling cocok untukmu!
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a> <!-- Contoh Card -->
                <a href="misteri.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://pakki.org/storage/artikel/20220726043357.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Mystery Case: Investigasi Industri</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Mengasah Analisis & Problem Solving dalam Lingkungan SMK
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a> <!-- Contoh Card -->
                 <a href="enter.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://storage.googleapis.com/sahabat-pegadaian-asset-prd/migrated-media/2024--04--wirausaha-adalah.webp" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"
                                 onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Simulator Wirausaha</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Kelola usaha virtual, ambil keputusan cerdas, dan kembangkan keterampilan bisnis serta kewirausahaan.
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a> <!-- Contoh Card -->
                <a href="gamediag.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://media.licdn.com/dms/image/v2/D5612AQGoST-EEYCD1Q/article-cover_image-shrink_600_2000/article-cover_image-shrink_600_2000/0/1725222493641?e=2147483647&v=beta&t=cra66_dhBfBYzmtXVizK9QOe5WxeXj-vCevGU9gWLsg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Game Diags</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game siswa SMK untuk troubleshoting permasalahan
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                <a href="gak.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://www.linovhr.com/wp-content/uploads/Accounting-adalah-Pengertian-Fungsi-dan-Perbedaannya-dengan-Finance.webp" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">AkunQuest</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game Siswa Akuntansi Pembukan Debit Kredit 
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                <a href="gak3.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://cluequest.co.uk/images/gallery/new/cq-origenes-escape-room-london-4.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Escape Room Akuntansi</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game Siswa Akuntansi Pecahkan teka-teki jurnal untuk melarikan diri! 
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                <a href="gak4.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://sevima.com/wp-content/uploads/2021/10/jurusan-akuntansi.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Accounting Quiz Battle</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game Siswa Akuntansi Pecahkan teka-teki jurnal untuk melarikan diri! 
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                 <a href="virproj.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://www.ad-ins.com/wp-content/uploads/2023/08/Tools-Project-Management.jpg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Project Sim: Asisten Digital</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Panduan interaktif untuk simulasi proyek industri masa depan.
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                <a href="nego.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://www.humasindonesia.id/images/berita/humas-indonesia-4-kemungkinan-hasil-negosiasi-40.jpeg" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Sang Negosiator</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game Simulasi yang membantu kamu sebagai sang Negosiator Ulung
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                 <a href="tanam.php" target="_blank" class="block liquid-card frosted-glass group animate-fade-in-up initial-hidden" style="animation-delay: 0.8s;">
                    <div class="p-6">
                        <div class="w-full h-48 bg-gray-900 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="https://tanimerdeka.com/wp-content/uploads/2025/04/1796758656.webp" 
                                 alt="Gambar Cyber Chess"  loading="lazy"
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110"  onerror="this.onerror=null; this.src='../admin/foto/<?= $data['banner'] ?>';">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-300 transition text-pop">Simulasi Budidaya Tanaman</h4>
                        <p class="text-gray-100 text-sm text-pop">
                           Game Simulasi yang membantu kamu Sang Petani dalam mensimulasikan budidaya tanaman dan pertanian 
                        </p>
                        <span class="mt-4 inline-block text-white font-semibold group-hover:underline text-pop">Mainkan Sekarang &rarr;</span>
                    </div>
                </a>
                <!-- Tambahkan card lain sesuai desain -->
            </div>
        </section>

        <!-- Stats Counter (JSON-based) -->
        <section class="max-w-7xl mx-auto mt-20 text-center animate-fade-in-up initial-hidden" style="animation-delay: 1.4s;">
            <div class="frosted-glass liquid-card inline-block px-8 py-4 border-indigo-500/30">
                <h4 class="text-lg font-semibold text-white mb-1 text-pop">Total Kunjungan Global</h4>
                <p id="visitor-count" class="text-4xl font-extrabold text-pink-400 text-pop">0</p>
            </div>
        </section>
<!-- Popup Iframe Container Centered with Frosted Glass -->
<div id="iframe-popup" class="fixed top-1/2 left-1/2 w-4/5 md:w-1/2 h-[650px] opacity-0 bg-white/30 backdrop-blur-xl backdrop-saturate-150 border border-white/20 rounded-2xl shadow-2xl overflow-hidden z-50 transition-all duration-300 transform -translate-x-1/2 -translate-y-1/2 scale-95 pointer-events-none">
    <iframe src="chat.php" class="w-full h-full border-0"></iframe>
    <button id="close-iframe" class="absolute top-3 right-3 text-white bg-red-500 rounded-full w-8 h-8 flex items-center justify-center font-bold">×</button>
</div>

<button id="floating-btn" class="fixed bottom-6 right-6 bg-indigo-600 text-white rounded-full w-12 h-12 shadow-lg flex items-center justify-center z-50 hover:bg-indigo-700 transition-colors">
    <!-- Icon Chat SVG -->
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.964 9.964 0 01-4.254-.885L3 21l1.885-4.746A9.964 9.964 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>
</button>





        <!-- Footer -->
        <footer class="mt-8 text-center text-gray-400 pt-8 animate-fade-in-up initial-hidden text-pop" style="animation-delay: 1.5s;">
            <p>&copy; 2024 AI Yata. Powered by Generative Gaming Intelligence. By Subuh kurniawan</p>
        </footer>

    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', () => {
            const layers = document.querySelectorAll('.parallax-layer');
            const messageBox = document.getElementById('messageBox');
            const requestPermissionBtn = document.getElementById('requestPermission');
            const randomObjectsLayer = document.getElementById('layer-random-objects');

            // Deteksi otomatis
            const isMobile = 'ontouchstart' in window || (navigator.maxTouchPoints > 0);
            const needsExplicitPermission = typeof DeviceOrientationEvent.requestPermission === 'function';

            // --- Fungsi untuk membuat bintang/objek acak ---
            function createRandomObjects(count) {
                for (let i = 0; i < count; i++) {
                    const obj = document.createElement('div');
                    obj.classList.add('star-object');
                    
                    const size = Math.random() * 3 + 1; // Ukuran 1-4px
                    obj.style.width = `${size}px`;
                    obj.style.height = `${size}px`;
                    
                    const x = Math.random() * 100; // Posisi X acak %
                    const y = Math.random() * 100; // Posisi Y acak %
                    obj.style.left = `${x}%`;
                    obj.style.top = `${y}%`;

                    // Variasi animasi
                    obj.style.animationDelay = `${Math.random() * 5}s`;
                    obj.style.animationDuration = `${Math.random() * 4 + 3}s`; // Durasi 3-7s

                    // Set warna untuk variasi
                    const color = Math.random();
                    if (color < 0.3) obj.style.backgroundColor = '#ADD8E6'; // LightBlue
                    else if (color < 0.6) obj.style.backgroundColor = '#FFFFE0'; // LightYellow
                    else obj.style.backgroundColor = 'white';

                    // Beri bayangan yang berbeda
                    obj.style.boxShadow = `0 0 ${size + 2}px ${obj.style.backgroundColor}`;


                    randomObjectsLayer.appendChild(obj);
                }
            }

            // Buat sejumlah objek acak (misal: 100 bintang/debu)
            createRandomObjects(100); 

            // Fungsi untuk mendapatkan bagian transform statis (translateZ dan scale)
            function getBaseTransform(layer) {
                if (layer.id === 'layer-galaxy-core') return 'translateZ(-800px) scale(2)';
                if (layer.id === 'layer-galaxy-arms') return 'translateZ(-400px) scale(1.4)';
                if (layer.id === 'layer-random-objects') return 'translateZ(-200px) scale(1.2)';
                if (layer.id === 'layer-foreground') return 'translateZ(-50px) scale(1.05)';
                return ''; 
            }

            // Fungsi untuk menerapkan transformasi ke layer-layer
            function applyParallax(x, y) {
                layers.forEach(layer => {
                    const speed = parseFloat(layer.getAttribute('data-speed')) / 100; 
                    
                    const xTranslate = x * speed;
                    const yTranslate = y * speed;
                    
                    // Ambil transform 3D statis (Z-depth)
                    const baseTransform = getBaseTransform(layer);
                    
                    // Gabungkan transform statis dan dinamis (X/Y)
                    layer.style.transform = `${baseTransform} translateX(${xTranslate}px) translateY(${yTranslate}px)`;
                });
            }

            // --- Logika Penanganan Gyroscope/Device Motion ---
            let lastX = 0;
            let lastY = 0;
            const GYRO_SENSITIVITY = 2.5; // Agresif

            function handleDeviceOrientation(event) {
                const x = (event.alpha || 0) * GYRO_SENSITIVITY; 
                const y = (event.beta || 0) * GYRO_SENSITIVITY; 

                const smoothX = lastX + (x - lastX) * 0.1;
                const smoothY = lastY + (y - lastY) * 0.1;
                
                lastX = smoothX;
                lastY = smoothY;

                requestAnimationFrame(() => applyParallax(smoothX, smoothY));
            }

            // Fungsi yang dipanggil oleh tombol (diperlukan pada iOS)
            window.requestMotionPermission = function() {
                if (needsExplicitPermission) {
                    DeviceOrientationEvent.requestPermission()
                        .then(permissionState => {
                            if (permissionState === 'granted') {
                                messageBox.textContent = 'Mode Gyroscope Aktif! Miringkan perangkat Anda.';
                                window.addEventListener('deviceorientation', handleDeviceOrientation);
                                requestPermissionBtn.classList.add('hidden');
                            } else {
                                messageBox.textContent = 'Izin gerakan ditolak. Paralaks tidak aktif.';
                            }
                        })
                        .catch(error => {
                            messageBox.textContent = 'Kesalahan saat meminta izin: ' + error.message;
                            console.error('Error requesting device motion permission:', error);
                        });
                } else {
                    messageBox.textContent = 'Mode Gyroscope Aktif secara otomatis! Miringkan perangkat Anda.';
                    window.addEventListener('deviceorientation', handleDeviceOrientation);
                    requestPermissionBtn.classList.add('hidden');
                }
            };
            // --- END Logika Penanganan Gyroscope ---


            // --- Logika Deteksi dan Aktivasi Otomatis ---
            const MOUSE_SENSITIVITY = 0.3; 
            if (!isMobile) {
                // --- Desktop Logic (Mouse) ---
                messageBox.textContent = 'Mode Mouse Paralaks Aktif.';

                document.addEventListener('mousemove', (e) => {
                    const centerX = window.innerWidth / 2;
                    const centerY = window.innerHeight / 2;
                    const mouseX = e.clientX - centerX; 
                    const mouseY = e.clientY - centerY; 

                    // Mengalikan dengan sensitivitas 
                    const x = -mouseX * MOUSE_SENSITIVITY; 
                    const y = -mouseY * MOUSE_SENSITIVITY;

                    requestAnimationFrame(() => applyParallax(x, y));
                });
            } 
            else {
                // --- Mobile Logic (Gyroscope) ---
                if (needsExplicitPermission) {
                    messageBox.textContent = 'Mode Gyroscope/Tilt. Harap klik tombol di bawah untuk mengaktifkan sensor gerakan.';
                    requestPermissionBtn.classList.remove('hidden');
                } else {
                    messageBox.textContent = 'Mode Gyroscope Aktif secara otomatis! Miringkan perangkat Anda.';
                    window.addEventListener('deviceorientation', handleDeviceOrientation);
                    requestPermissionBtn.classList.add('hidden');
                }
            }
        });
    </script>

</body>
</html>