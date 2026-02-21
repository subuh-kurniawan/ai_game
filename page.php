<?php 
include "admin/fungsi/koneksi.php";

// --- FUNGSI HELPER ---
function get_excerpt($html_content, $length = 150) {
    $text = strip_tags($html_content);
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text;
}
// --- END FUNGSI HELPER ---

// 1. Ambil Artikel Unggulan (Featured Post: Artikel Terbaru)
$sql_featured = mysqli_query($koneksi, "SELECT * FROM artikel_landing ORDER BY id DESC LIMIT 1");
$featured_article = mysqli_fetch_assoc($sql_featured);
$featured_id = $featured_article ? $featured_article['id'] : 0;

// PAGINATION SETTINGS (Untuk Artikel Grid di bawah Featured)
$articles_per_page = 9; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $articles_per_page;

// Total artikel DI LUAR artikel unggulan
$total_articles_result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM artikel_landing WHERE id != $featured_id");
$total_articles_minus_featured = mysqli_fetch_assoc($total_articles_result)['total'];
$total_pages = ceil($total_articles_minus_featured / $articles_per_page);

// 2. Ambil Artikel untuk Grid Utama (Tidak termasuk Featured)
$sql_artikel = mysqli_query($koneksi,"SELECT * FROM artikel_landing WHERE id != $featured_id ORDER BY id DESC LIMIT $articles_per_page OFFSET $offset");

// 3. Ambil Artikel untuk Sidebar (3 Postingan Terbaru, tidak termasuk Featured)
$sql_sidebar_posts = mysqli_query($koneksi,"SELECT * FROM artikel_landing WHERE id != $featured_id ORDER BY id DESC LIMIT 3");

// Data Sekolah
$sql_sekolah = mysqli_query($koneksi, "SELECT * FROM datasekolah ORDER BY id_sekolah DESC LIMIT 1");
$data = mysqli_fetch_assoc($sql_sekolah);

// Fallbacks
$bannerPath = !empty($data['banner']) ? "admin/foto/" . $data['banner'] : "admin/foto/banner.jpg";
$nama_sekolah = $data ? htmlspecialchars($data['nama']) : 'Sekolah Kami';
$deskripsi_sekolah = $data ? htmlspecialchars($data['deskripsi']) : 'Portal Informasi Resmi Sekolah.';
$logo_path = $data ? "admin/foto/" . $data['logo'] : "";

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title"><?php echo $nama_sekolah; ?>: Portal Berita Keren</title>
    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Konfigurasi Font, Tema, dan Dark Mode -->
<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // 🌙 Dark Mode
        'bg-dark': '#121212',
        'card-dark': '#1E1E2F',
        'highlight-dark': '#4F46E5',
        'text-dark': '#E0E0E0',
        'muted-dark': '#A1A1AA',

        // ☀️ Light Mode (soft & elegan)
        'bg-light': '#FDFDFD',
        'card-light': '#FFFFFF',
        'highlight-light': '#7F9CF5', // biru lembut
        'text-light': '#1F2937',      // abu gelap nyaman di mata
        'muted-light': '#9CA3AF',     // abu lembut
      },
      fontFamily: {
        sans: ['Inter', 'Nunito Sans', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'Inter', 'sans-serif'],
      },
      boxShadow: {
        'soft-dark': '0 8px 24px rgba(0,0,0,0.6)',
        'soft-light': '0 6px 20px rgba(0,0,0,0.06)', // lebih subtle
        'inner-glow': 'inset 0 0 8px rgba(127,156,245,0.2)', // glow biru lembut
      },
      backgroundImage: {
        'gradient-light': 'linear-gradient(135deg, #F9FAFB 0%, #F1F5F9 50%, #FFFFFF 100%)',
        'gradient-dark': 'linear-gradient(135deg, #0D0D1A 0%, #1E1E2F 50%, #2C2C3F 100%)',
      },
    }
  }
}
</script>

<style>
/* Import Google Font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');

/* ====================================
   BASE STYLES & BACKGROUND
   ==================================== */
body {
    font-family: 'Inter', sans-serif;
    overflow-x: hidden;
    /* Consolidate main body background */
    background:
        radial-gradient(circle at top left, #7f00ff 0%, transparent 50%),
        radial-gradient(circle at bottom right, #ff0080 0%, transparent 50%),
        radial-gradient(circle at top right, #00ffff 0%, transparent 50%),
        radial-gradient(circle at center, #1b0042 0%, #0d001f 50%, #02000a 100%);
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-color: #0d1117; /* Fallback color */
}

/* Animated Dark Gradient */
@keyframes gradientAnimation {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.bg-gradient-animated-dark {
    background: linear-gradient(270deg, #0B0F19, #141A25, #1E1E2F, #0B0F19);
    background-size: 800% 800%;
    animation: gradientAnimation 20s ease infinite;
}

/* ====================================
   PARALLAX LAYERS (CLEANED & CONSOLIDATED)
   ==================================== */
.parallax-layer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    pointer-events: none;
    z-index: -10;
    transition: transform 0.1s ease-out; /* Kept for smoothness */
}

/* Layer specific gradient styles */
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

/* ====================================
   GLASS & CARD EFFECTS
   ==================================== */

/* Frosted Glass (used on header, article) - Standard Definition */
.frosted-glass {
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    background-color: rgba(255, 255, 255, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease-in-out;
}
.dark .frosted-glass {
    background-color: rgba(30, 30, 47, 0.35); /* Darker, slightly more transparent for dark mode */
    backdrop-filter: blur(14px) saturate(200%) brightness(1.05);
    -webkit-backdrop-filter: blur(14px) saturate(200%) brightness(1.05);
    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
}

/* Liquid Glass (used on news-card background in HTML) */
.liquid-glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(18px) saturate(180%);
    -webkit-backdrop-filter: blur(18px) saturate(180%);
    border-radius: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 30px rgba(79, 70, 229, 0.25);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

/* News Card Base & Hover */
.news-card {
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}
.news-card:hover {
    transform: translateY(-6px) scale(1.01);
}

/* Image Hover Effect */
.image-container img {
    transition: transform 0.6s ease;
}
.news-card:hover .image-container img {
    transform: scale(1.07);
}

/* ====================================
   UTILITIES & ANIMATIONS
   ==================================== */
.text-pop {
    text-shadow: 0 0 5px rgba(0,0,0,0.4);
}
.initial-hidden { 
    opacity: 0; 
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    animation-fill-mode: both;
}

@keyframes wobble {
    0% { transform: rotate(0deg) scale(0.95); }
    15% { transform: rotate(-5deg) scale(1); }
    30% { transform: rotate(3deg) scale(1); }
    45% { transform: rotate(-3deg) scale(1); }
    60% { transform: rotate(2deg) scale(1); }
    75% { transform: rotate(-1deg) scale(1); }
    100% { transform: rotate(0deg) scale(1); }
}
.wobble {
    animation: wobble 0.6s ease;
}

/* Custom Shadows (Used when not relying on Tailwind's defaults) */
.shadow-custom-light {
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.shadow-custom-dark {
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}

</style>
</head>
<!-- Default theme: Dark -->
<body class="dark bg-gradient-animated-dark text-text-dark font-sans transition-colors duration-500">
 <div id="layer1" class="parallax-layer layer-1"></div>
<div id="layer2" class="parallax-layer layer-2"></div>
<div id="layer3" class="parallax-layer layer-3"></div>
<div id="layer4" class="parallax-layer layer-4"></div>
<div id="layer5" class="parallax-layer layer-5"></div>
<div id="layer6" class="parallax-layer layer-6"></div>
    

   <header class="frosted-glass sticky top-0 z-50 border-b border-white dark:border-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
        <!-- Logo -->
        <a href="#" onclick="showHomepage(); return false;" class="flex items-center space-x-2">
            <img src="admin/foto/<?php echo $data['logo']; ?>" alt="Logo Sekolah" class="h-10 w-10 object-contain">
            <span class="text-3xl font-extrabold text-text-light dark:text-text-dark tracking-wide cursor-pointer">
                <?php echo $nama_sekolah; ?>
            </span>
        </a>

        <!-- Navigasi Desktop & Theme Toggle -->
        <div class="flex items-center space-x-6">
            <!-- Dropdown Menu -->
            <div class="relative">
                <button id="dropdownButton" class="text-text-light dark:text-text-dark hover:text-highlight-light dark:hover:text-highlight-dark transition duration-150 font-medium focus:outline-none flex items-center space-x-1">
                    Menu
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <!-- Dropdown Items -->
                <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-40 bg-card-light dark:bg-card-dark rounded-md shadow-lg py-1 z-50">
                    <a href="#" onclick="showHomepage(); return false;" class="block px-4 py-2 text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-800">Beranda</a>
                    <?php
                      
                      $sql = mysqli_query($koneksi,"select * FROM menu WHERE status='active' order by urutan DESC");   
                      $no = 1;
                      while($data = mysqli_fetch_array($sql)){
                         
                      ?> 
                    <a href="<?php echo $data['link']; ?>" class="block px-4 py-2 text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-800"> <?php echo $data['judul']; ?></a>
                    	<?php } ?>  
                    
                </div>
            </div>

            <!-- Theme Toggle Button -->
            <button id="theme-toggle" onclick="toggleTheme()" class="p-2 rounded-full bg-gray-100 dark:bg-gray-800 text-text-light dark:text-text-dark hover:shadow-md transition duration-300 focus:outline-none">
                <svg id="sun-icon" class="w-6 h-6 icon-sun hidden dark:block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.106a.75.75 0 00-1.06-1.06l-1.59 1.59a.75.75 0 101.06 1.06l1.59-1.59zM21.75 12h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM15.59 17.59a.75.75 0 001.06 1.06l1.59-1.59a.75.75 0 00-1.06-1.06l-1.59 1.59zM12 18.75a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H12a.75.75 0 01.75.75zM5.566 17.59a.75.75 0 001.06 1.06l1.59-1.59a.75.75 0 00-1.06-1.06l-1.59 1.59zM4.5 12a.75.75 0 01-.75.75H2.25a.75.75 0 010-1.5H3.75a.75.75 0 01.75.75zM5.566 6.106a.75.75 0 00-1.06 1.06l1.59 1.59a.75.75 0 001.06-1.06l-1.59-1.59z"/>
                </svg>
                <svg id="moon-icon" class="w-6 h-6 icon-moon dark:hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.547 2.195a.75.75 0 01.127.513l.035.152a21.63 21.63 0 0010.231 7.234 9.172 9.172 0 004.28 7.398c-.144.08-.285.155-.429.227A23.83 23.83 0 0110.59 19.333 11.455 11.455 0 015.016 11.02a.75.75 0 01.75-.75c.18 0 .354.041.513.127l.152.035A21.63 21.63 0 0016.92 10.957a9.172 9.172 0 007.398-4.28c-.08-.144-.155-.285-.227-.429a23.83 23.83 0 01-7.234-10.231c.086-.18.127-.354.127-.513z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Menu Mobile (Dropdown) -->
    <div id="mobile-menu" class="frosted-glass hidden md:hidden bg-card-light dark:bg-card-dark pb-3 px-2 pt-2 space-y-1 sm:px-3 shadow-md border-t border-gray-200 dark:border-gray-800">
        <div class="relative">
            <button id="mobileDropdownButton" class="w-full text-left px-3 py-2 rounded-md text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none flex justify-between items-center">
                Menu
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="mobileDropdownMenu" class="hidden mt-1 space-y-1">
                <a href="#" onclick="showHomepage(); return false;" class="block px-4 py-2 text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-800">Beranda</a>
                <?php
                      
                      $sql = mysqli_query($koneksi,"select * FROM menu WHERE status='active' order by urutan DESC");   
                      $no = 1;
                      while($data = mysqli_fetch_array($sql)){
                         
                      ?> 
                    <a href="<?php echo $data['link']; ?>" class="block px-4 py-2 text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-800"> <?php echo $data['judul']; ?></a>
                    	<?php } ?>  
                    
            </div>
        </div>
    </div>
</header>

<script>
    // Desktop Dropdown
    const dropdownButton = document.getElementById('dropdownButton');
    const dropdownMenu = document.getElementById('dropdownMenu');

    dropdownButton.addEventListener('click', () => {
        dropdownMenu.classList.toggle('hidden');
    });

    // Mobile Dropdown
    const mobileDropdownButton = document.getElementById('mobileDropdownButton');
    const mobileDropdownMenu = document.getElementById('mobileDropdownMenu');

    mobileDropdownButton.addEventListener('click', () => {
        mobileDropdownMenu.classList.toggle('hidden');
    });

    // Optional: klik di luar dropdown untuk menutup
    window.addEventListener('click', function(e) {
        if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
        if (!mobileDropdownButton.contains(e.target) && !mobileDropdownMenu.contains(e.target)) {
            mobileDropdownMenu.classList.add('hidden');
        }
    });
</script>


    <!-- KONTEN UTAMA - PEMISAH HALAMAN -->

    <!-- HALAMAN BERANDA -->
    <main id="homepage-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <?php if ($featured_article): 
        $feat_excerpt = get_excerpt($featured_article['isiartikel'], 300);
    ?>
        <!-- Berita Utama (Featured Story) - NON-OVERLAPPING CARD -->
        <section class="mb-12">
            
            <div class="frosted-glass rounded-xl overflow-hidden shadow-custom-light dark:shadow-custom-dark bg-card-light/60 dark:bg-card-dark/60 border border-gray-200 dark:border-gray-800">
                
                <!-- Image Section (Full Width Top) -->
                <div class="image-container h-64 md:h-96">
                    <img src="admin/uploads/<?php echo $featured_article['bn1']; ?>" 
                         onerror="this.onerror=null;this.src='https://placehold.co/1200x600/161B22/58A6FF?text=Berita+Utama+AI';" 
                         alt="Gambar Berita Utama AI" class="w-full h-full object-cover transition-transform duration-500 hover:scale-[1.03]">
                </div>
                
                <!-- Content Section (Below Image) -->
                <div class="p-6 md:p-8">
                   
                    
                    <h1 class="text-3xl md:text-4xl font-extrabold leading-tight mb-3 text-text-light dark:text-text-dark">
                        <?php echo htmlspecialchars($featured_article['judul']); ?>
                    </h1>
                    
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">
                         <?php echo htmlspecialchars($feat_excerpt); ?>
                    </p>
                    
                   <a href="#" onclick="showArticle(<?php echo $featured_id; ?>); return false;" class="text-text-light dark:text-text-dark font-bold mt-3 hover:underline flex items-center">
                        Baca Selengkapnya 
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
        </section>

 <?php endif; ?>
        <!-- GRID BERITA UTAMA & SIDEBAR -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- Kolom Kiri: Berita Terbaru (2/3 lebar) -->
            <div class="lg:col-span-2">
              
                <!-- Grid Berita 4 Kolom di Desktop, 2 Kolom di Tablet, 1 Kolom di Mobile -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 ">
 <?php while($artikel = mysqli_fetch_array($sql_artikel)): 
                    $excerpt = get_excerpt($artikel['isiartikel'], 100);
                ?> 
                    <!-- Berita 1: DENGAN IMAGE CARD BARU -->
                    <article class="bg-card-light/60 dark:bg-card-dark/30 liquid-glass rounded-xl overflow-hidden news-card border border-gray-200 dark:border-gray-800">
                        <div class="image-container">
                            <img src="admin/uploads/<?php echo $artikel['bn1']; ?>" 
                                onerror="this.onerror=null;this.src='https://placehold.co/600x400/161B22/58A6FF?text=Berita+Ekonomi';" 
                                alt="Berita Ekonomi" class="w-full h-48 object-cover">
                        </div>
                        <div class="p-5">
                           
                            <h3 class="text-xl font-bold mt-2 mb-3 text-text-light dark:text-text-dark leading-snug cursor-pointer transition-colors" onclick="showArticle(<?php echo $artikel['id']; ?>); return false;">
    <?php echo htmlspecialchars($artikel['judul']); ?>
</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                 <?php echo htmlspecialchars($excerpt); ?>
                            </p>
                        </div>
                    </article>
                     <?php endwhile; ?>  
                    
                </div>
            </div>

            <!-- Kolom Kanan: Sidebar (1/3 lebar) -->
            <div class="lg:col-span-1">
                
                <!-- Widget Trending -->
                <div class="bg-card-light/80 dark:bg-card-dark/80 p-6 frosted-glass rounded-xl shadow-custom-light dark:shadow-custom-dark border border-gray-200 dark:border-gray-800 mb-8">
                    <h3 class="text-2xl font-bold mb-5 border-b border-gray-300 dark:border-gray-700 pb-3 text-highlight-light dark:text-highlight-dark">Sedang Tren</h3>
                    
                    <ul class="space-y-4">
                       <?php
$sql = mysqli_query($koneksi, "SELECT * FROM artikel_landing LIMIT 2");
$no = 1;
while ($data = mysqli_fetch_array($sql)) {
?>
    <li class="border-b border-gray-200 dark:border-gray-800 pb-3 last:border-b-0 last:pb-0">
        <a href="#" class="block hover:text-highlight-light dark:hover:text-highlight-dark transition duration-150">
            <span class="text-3xl font-extrabold text-highlight-light dark:text-highlight-dark mr-3">
                <?php echo $no; ?>
            </span>
            <span class="font-medium text-text-light dark:text-text-dark">
                <?php echo htmlspecialchars($data['judul']); ?>
            </span>
        </a>
    </li>
<?php
    $no++;
}
?>

                        
                    </ul>
                </div>

                <!-- Widget Iklan/Promosi -->
                <div class="bg-highlight-light dark:bg-highlight-dark p-6 rounded-xl text-white dark:text-text-dark shadow-xl text-center">
                    <h3 class="text-2xl font-bold mb-3">Tingkatkan Akses Anda!</h3>
                    <p class="text-sm mb-4">Dapatkan berita eksklusif tanpa iklan. Berlangganan sekarang.</p>
                    <button class="bg-bg-dark text-white font-bold py-2 px-6 rounded-lg hover:bg-opacity-90 transition duration-150 shadow-md">
                        Daftar Premium
                    </button>
                </div>

            </div>
        </div>

    </main>

    <!-- HALAMAN ARTIKEL -->
    <main id="article-content" class="hidden max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Tombol Kembali -->
        <button onclick="showHomepage()" class="flex items-center text-highlight-light dark:text-highlight-dark mb-8 hover:underline">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Beranda
        </button>

        <!-- KONTEN ARTIKEL -->
        <article class="frosted-glass bg-card-light/80 dark:bg-card-dark/80 p-6 sm:p-10 rounded-xl shadow-custom-dark border border-gray-200 dark:border-gray-800">
            
            <span class="inline-block px-4 py-1 text-sm font-semibold bg-gray-200 dark:bg-gray-700 text-text-light dark:text-text-dark rounded-full mb-4" id="article-category-display">
                
            </span>
            <div class="mt-6 mb-4 flex flex-wrap gap-2">
 <button onclick="playTTS()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Play</button>
<button onclick="stopTTS()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Stop</button>





</div>
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4 text-text-light dark:text-text-dark" id="article-title-display">
                Kebangkitan AI: Bagaimana Kecerdasan Buatan Mendefinisikan Ulang Pekerjaan Global
            </h1>
            
            <div class="text-gray-500 dark:text-gray-400 text-sm mb-6 flex items-center space-x-4">
                <span>Oleh: <span class="font-medium text-text-light dark:text-text-dark"><?php echo $nama_sekolah; ?></span></span>
                <span>|</span>
                <span id="article-date-display">20 Oktober 2025</span>
            </div>

            <figure class="mb-8 overflow-hidden rounded-lg shadow-xl">
                <img id="article-image-display" src="https://placehold.co/1000x500/1a1a2e/e94560?text=Gambar+Detail+Artikel" 
                     onerror="this.onerror=null;this.src='https://placehold.co/1000x500/1a1a2e/e94560?text=Gambar+Artikel';" 
                     alt="Ilustrasi Kecerdasan Buatan" class="w-full h-auto object-cover">
                <figcaption class="text-xs text-gray-500 mt-2 text-center">
                    [Ilustrasi Kecerdasan Buatan]
                </figcaption>
            </figure>

            <div class="text-lg text-gray-700 dark:text-gray-300 space-y-6" id="article-body-display">
                
                <p class="leading-relaxed first-line:font-bold first-line:text-2xl first-line:text-highlight-light dark:first-line:text-highlight-dark first-line:mr-1">
                    Gelombang adopsi Kecerdasan Buatan (AI) telah mencapai titik kritis, bukan lagi sekadar inovasi futuristik, melainkan pendorong utama restrukturisasi pasar kerja global. Dari otomatisasi tugas rutin hingga munculnya peran baru yang berfokus pada manajemen data dan etika AI, lanskap profesional berada di tengah transformasi yang radikal dan tak terhindarkan.
                </p>

                <p class="leading-relaxed">
                    Dalam waktu dekat, sektor-sektor seperti layanan pelanggan, entri data, dan beberapa bidang akuntansi akan melihat dampak terbesar. Meskipun kekhawatiran tentang PHK massal meningkat, fokus sebenarnya beralih pada peningkatan efisiensi. AI bertindak sebagai 'asisten super' yang memungkinkan karyawan fokus pada tugas-tugas yang membutuhkan kreativitas, interaksi manusia, dan pemikiran strategis.
                </p>

                <blockquote class="border-l-4 border-highlight-light dark:border-highlight-dark pl-4 py-2 italic text-gray-600 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-r-lg">
                    "AI tidak akan menggantikan manusia, tetapi manusia yang menggunakan AI akan menggantikan manusia yang tidak menggunakannya. Ini adalah perlombaan adaptasi, bukan eliminasi."
                </blockquote>

                <h3 class="text-2xl font-bold text-highlight-light dark:text-highlight-dark pt-4">Mempersiapkan Tenaga Kerja Masa Depan</h3>
                <p class="leading-relaxed">
                    Pendidikan dan pelatihan ulang (*reskilling*) menjadi kunci. Permintaan terhadap keahlian di bidang *prompt engineering*, analisis *big data*, dan *machine learning* melonjak tajam. Pemerintah dan institusi pendidikan didorong untuk merevisi kurikulum secara cepat agar sejalan dengan kebutuhan industri 4.0. Investasi dalam literasi digital kini sama pentingnya dengan literasi dasar.
                </p>

                <p class="leading-relaxed">
                    Tantangan etika juga tak terelakkan. Bias dalam algoritma dan masalah privasi data membutuhkan regulasi yang ketat. Masa depan pekerjaan global akan cerah, asalkan kita dapat mengarahkan kekuatan AI ini dengan bijak, memastikan bahwa kemajuan teknologi melayani tujuan kemanusiaan yang lebih besar.
                </p>
            </div>
        </article>
        
        <!-- Bagian Komentar (Placeholder) -->
        <section class="mt-12 p-6 bg-card-light dark:bg-card-dark rounded-xl shadow-xl border border-gray-200 dark:border-gray-800">
            <h3 class="text-2xl font-bold mb-5 text-highlight-light dark:text-highlight-dark">Diskusi & Komentar</h3>
            <p class="text-gray-500 dark:text-gray-400">Fitur komentar akan segera hadir. Berikan pendapat Anda melalui media sosial kami!</p>
        </section>

    </main>
  
    <!-- FOOTER -->
    <footer class="bg-card-light/70 dark:bg-card-dark/50 mt-10 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          
            
            <div class="text-center text-sm text-gray-500 pt-4">
                &copy; 2025 <?php echo $data['nama']; ?>. Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <!-- JavaScript untuk Pindah Halaman dan Theme Toggle -->
    <script>
        // Data Artikel dummy
        const articles = {};
<?php
$sql = mysqli_query($koneksi,"SELECT * FROM artikel_landing");
while($row = mysqli_fetch_assoc($sql)) {
    echo "articles[" . json_encode($row['id']) . "] = " . json_encode([
        'title' => $row['judul'],
        'date' => 'Admin',
        'image' => "admin/uploads/".$row['bn1']
    ]) . ";\n";
}
?>


        // --- FUNGSI PENGATUR TAMPILAN HALAMAN ---
        function showArticle(articleId) {
            const homepage = document.getElementById('homepage-content');
            const articlepage = document.getElementById('article-content');
            const pageTitle = document.getElementById('page-title');

            const data = articles[articleId] || articles['ai-rise']; // Default jika ID tidak valid

            // Update konten artikel
            document.getElementById('article-title-display').textContent = data.title;
            document.getElementById('article-date-display').textContent = data.date;
            document.getElementById('article-category-display').textContent = data.category;
            document.getElementById('article-image-display').src = data.image;
            pageTitle.textContent = data.title + " | <?php echo $data['nama']; ?>";

            // Sembunyikan Homepage, Tampilkan Article Page
            homepage.classList.add('hidden');
            articlepage.classList.remove('hidden');

            window.scrollTo(0, 0); // Scroll ke atas
        }

        function showHomepage() {
            const homepage = document.getElementById('homepage-content');
            const articlepage = document.getElementById('article-content');
            const pageTitle = document.getElementById('page-title');

            // Tampilkan Homepage, Sembunyikan Article Page
            homepage.classList.remove('hidden');
            articlepage.classList.add('hidden');
            pageTitle.textContent = "<?php echo $data['nama']; ?>: Portal Berita Keren";
            
            window.scrollTo(0, 0); // Scroll ke atas
        }

        // --- FUNGSI THEME TOGGLE (DARK/LIGHT MODE) ---
      function toggleTheme() {
    const body = document.body;

    body.classList.toggle('dark');

    if (body.classList.contains('dark')) {
        body.classList.remove('bg-gradient-animated-light');
        body.classList.add('bg-gradient-animated-dark');
    } else {
        body.classList.remove('bg-gradient-animated-dark');
        body.classList.add('bg-gradient-animated-light');
    }

    localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
}

// Saat halaman dimuat
(function initialThemeCheck() {
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        body.classList.remove('dark', 'bg-gradient-animated-dark');
        body.classList.add('bg-gradient-animated-light');
    } else {
        body.classList.add('dark', 'bg-gradient-animated-dark');
    }
})();


        // JavaScript untuk Toggle Menu Mobile
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
        
        // --- FIREBASE PLACEHOLDER SETUP (MANDATORY) ---
        // Variabel global yang disediakan oleh Canvas
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
        const firebaseConfig = typeof __firebase_config !== 'undefined' ? JSON.parse(__firebase_config) : {};

        console.log("App ID:", appId);
        console.log("Portal Berita Keren siap diluncurkan!");
        // Akhir dari Firebase Placeholder

   </script>
  <script>
document.addEventListener('DOMContentLoaded', (event) => {
    // Now it's safe to run document.getElementById()

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
            // Check if the element was found before trying to transform it
            if (layer.el) { 
                // Interpolate smoothly
                layer.currentX = lerp(layer.currentX, targetX * 50, 0.08);
                layer.currentY = lerp(layer.currentY, scrollY * layer.speedY + targetY * 50, 0.08);
                const rotation = scrollY * layer.rotate;
                // Apply transform
                layer.el.style.transform = `translate(${layer.currentX}px, ${layer.currentY}px) rotate(${rotation}deg)`;
            }
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
let utterance = null;


// Fungsi untuk stop TTS
function stopTTS() {
    if (speechSynthesis.speaking || speechSynthesis.pending) {
        speechSynthesis.cancel();
    }
}

// Fungsi untuk play TTS
function playTTS() {
    stopTTS(); // Pastikan TTS sebelumnya dihentikan

    const article = document.getElementById('article-body-display');
    if (!article) return;
    const text = article.innerText.trim();
    if (!text) return;

    utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'id-ID';
    utterance.pitch = 1;
    utterance.rate = 1;
    utterance.volume = 1;

    const speak = () => {
        const voices = speechSynthesis.getVoices();
        const indoVoice = voices.find(v => v.lang === 'id-ID' && v.name.includes("Google"))
                         || voices.find(v => v.lang === 'id-ID');
        if (indoVoice) utterance.voice = indoVoice;

        speechSynthesis.speak(utterance);
    };

    // Jika voices sudah ada, langsung speak
    if (speechSynthesis.getVoices().length > 0) {
        speak();
    } else {
        // Tunggu voices siap
        speechSynthesis.onvoiceschanged = speak;
    }
}

// Stop TTS saat reload atau tutup tab
window.addEventListener('beforeunload', stopTTS);

// Stop TTS saat klik di luar artikel
document.addEventListener('click', (e) => {
    const article = document.getElementById('article-body-display');
    if (article && !article.contains(e.target)) {
        stopTTS();
    }
});
</script>

</body>
</html>
