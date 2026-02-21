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
    </style>
</head>
<body>

   <div id="layer1" class="parallax-layer layer-1"></div>
<div id="layer2" class="parallax-layer layer-2"></div>
<div id="layer3" class="parallax-layer layer-3"></div>
<div id="layer4" class="parallax-layer layer-4"></div>
<div id="layer5" class="parallax-layer layer-5"></div>
<div id="layer6" class="parallax-layer layer-6"></div>
    
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

   <script>
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
// Visitor Counter
async function updateGlobalCounter() {
    const countElement = document.getElementById("visitor-count");
    try {
        const response = await fetch('update_counter.php', { method: 'POST' });
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        countElement.textContent = data.count.toLocaleString('id-ID');
    } catch (error) {
        console.error('Error updating counter:', error);
        countElement.textContent = 'Error';
    }
}

window.addEventListener('load', updateGlobalCounter);
const searchInput = document.getElementById('search-input');
const cards = document.querySelectorAll('.grid > a');

searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    
    cards.forEach(card => {
        const title = card.querySelector('h4').textContent.toLowerCase();
        const desc = card.querySelector('p').textContent.toLowerCase();
        
        if(title.includes(query) || desc.includes(query)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
const floatingBtn = document.getElementById('floating-btn');
const iframePopup = document.getElementById('iframe-popup');
const closeIframe = document.getElementById('close-iframe');

function openPopup() {
    iframePopup.classList.remove('pointer-events-none', 'opacity-0', 'scale-95');
    iframePopup.classList.add('opacity-100', 'scale-100', 'wobble');

    // Hapus kelas wobble setelah animasi selesai agar bisa di-trigger lagi
    setTimeout(() => {
        iframePopup.classList.remove('wobble');
    }, 600); // durasi sesuai keyframes
}

function closePopup() {
    iframePopup.classList.remove('opacity-100', 'scale-100');
    iframePopup.classList.add('opacity-0', 'scale-95');
    setTimeout(() => {
        iframePopup.classList.add('pointer-events-none');
    }, 300); // sesuai duration-300
}

floatingBtn.addEventListener('click', () => {
    if (iframePopup.classList.contains('pointer-events-none')) {
        openPopup();
    } else {
        closePopup();
    }
});

closeIframe.addEventListener('click', closePopup);

// Tutup jika klik di luar popup & bukan tombol floating
document.addEventListener('click', (event) => {
    if (!iframePopup.contains(event.target) && !floatingBtn.contains(event.target)) {
        if (!iframePopup.classList.contains('pointer-events-none')) {
            closePopup();
        }
    }
});


</script>

</body>
</html>