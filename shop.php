<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch products (excluding custom orders)
$stmt = $pdo->prepare("SELECT * FROM products WHERE deleted = 0 AND type != 'custom' ORDER BY id DESC");
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - LCJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css" />
    <!-- Edge compatibility meta tag -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <style>
        .playfair { font-family: 'Playfair Display', serif; }
        .cormorant { font-family: 'Cormorant Garamond', serif; }
        .inter { font-family: 'Inter', sans-serif; }
        
        .jewelry-gradient {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 25%, #e2e8f0 50%, #cbd5e1 75%, #94a3b8 100%);
            -webkit-background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 25%, #e2e8f0 50%, #cbd5e1 75%, #94a3b8 100%);
            -ms-background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 25%, #e2e8f0 50%, #cbd5e1 75%, #94a3b8 100%);
        }
        
        .gold-gradient {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8, #1e40af, #1e3a8a);
            -webkit-background: linear-gradient(135deg, #3b82f6, #1d4ed8, #1e40af, #1e3a8a);
            -ms-background: linear-gradient(135deg, #3b82f6, #1d4ed8, #1e40af, #1e3a8a);
        }
        
        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            -webkit-transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            -ms-transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-12px) scale(1.02);
            -webkit-transform: translateY(-12px) scale(1.02);
            -ms-transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1);
            -webkit-box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1);
            -ms-box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1);
        }
        
        .diamond-sparkle {
            animation: sparkle 2s ease-in-out infinite;
        }
        
        @keyframes sparkle {
            0%, 100% { opacity: 0.3; transform: scale(1) rotate(0deg); }
            50% { opacity: 1; transform: scale(1.1) rotate(180deg); }
        }
        
        .floating-element {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(5px) rotate(-1deg); }
        }
        
        .shimmer-effect {
            position: relative;
            overflow: hidden;
        }
        
        .shimmer-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s;
        }
        
        .shimmer-effect:hover::before {
            left: 100%;
        }
        
        .image-glow {
            filter: drop-shadow(0 10px 20px rgba(59, 130, 246, 0.2));
            -webkit-filter: drop-shadow(0 10px 20px rgba(59, 130, 246, 0.2));
            -ms-filter: drop-shadow(0 10px 20px rgba(59, 130, 246, 0.2));
        }
        
        .luxury-shadow {
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            -webkit-box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            -ms-box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8, #1e40af);
            -webkit-background: linear-gradient(135deg, #3b82f6, #1d4ed8, #1e40af);
            -ms-background: linear-gradient(135deg, #3b82f6, #1d4ed8, #1e40af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            -ms-background-clip: text;
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .fade-out {
            animation: fadeOut 0.4s ease-in forwards;
        }
        
        .slide-in-left {
            animation: slideInLeft 0.5s ease-out forwards;
        }
        
        .slide-in-right {
            animation: slideInRight 0.5s ease-out forwards;
        }
        
        .slide-out {
            animation: slideOut 0.3s ease-in forwards;
        }
        
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        
        @keyframes slideInLeft {
            0% { 
                opacity: 0; 
                transform: translateX(-50px) scale(0.9); 
            }
            100% { 
                opacity: 1; 
                transform: translateX(0) scale(1); 
            }
        }
        
        @keyframes slideInRight {
            0% { 
                opacity: 0; 
                transform: translateX(50px) scale(0.9); 
            }
            100% { 
                opacity: 1; 
                transform: translateX(0) scale(1); 
            }
        }
        
        @keyframes slideOut {
            0% { 
                opacity: 1; 
                transform: translateX(0) scale(1); 
            }
            100% { 
                opacity: 0; 
                transform: translateX(-30px) scale(0.9); 
            }
        }
        
        /* Loading animations */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .animate-spin-slow {
            animation: spin 2s linear infinite;
        }
        
        .animate-pulse-slow {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        
        /* Active button hover effect */
        .filter-btn.active:hover {
            background-color: #1a2452 !important;
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(34, 48, 106, 0.3);
        }
        
        /* Ensure active button text is readable */
        .filter-btn.active {
            color: #ffffff !important;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .modal-entrance {
            animation: modalEnter 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .modal-backdrop {
            animation: backdropEnter 0.3s ease-out;
        }
        
        @keyframes modalEnter {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(30px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        @keyframes backdropEnter {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        
        /* Edge-specific fixes */
        .backdrop-blur-xl {
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            -ms-backdrop-filter: blur(24px);
        }
        
        .backdrop-blur-sm {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            -ms-backdrop-filter: blur(4px);
        }
        
        /* Ensure proper flexbox support for Edge */
        .flex {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
        }
        
        .grid {
            display: -webkit-box;
            display: -webkit-grid;
            display: -ms-grid;
            display: grid;
        }
        
        /* Fix for Edge transform issues */
        .transform {
            -webkit-transform: translateZ(0);
            -ms-transform: translateZ(0);
            transform: translateZ(0);
        }
        
        /* Edge-specific layout fixes */
        .header-main {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: justify;
            -webkit-justify-content: space-between;
            -ms-flex-pack: justify;
            justify-content: space-between;
        }
        
        .main-nav ul {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: horizontal;
            -webkit-box-direction: normal;
            -webkit-flex-direction: row;
            -ms-flex-direction: row;
            flex-direction: row;
        }
        
        /* Force hardware acceleration for Edge */
        .card-hover,
        .modal-entrance,
        .diamond-sparkle {
            -webkit-transform: translate3d(0, 0, 0);
            -ms-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
        
        /* Edge-specific grid fixes */
        .grid-cols-1 {
            -ms-grid-columns: 1fr;
        }
        
        .grid-cols-2 {
            -ms-grid-columns: 1fr 1fr;
        }
        
        .grid-cols-3 {
            -ms-grid-columns: 1fr 1fr 1fr;
        }
        
        .grid-cols-4 {
            -ms-grid-columns: 1fr 1fr 1fr 1fr;
        }
        
        @media (min-width: 768px) {
            .md\\:grid-cols-2 {
                -ms-grid-columns: 1fr 1fr;
            }
        }
        
        @media (min-width: 1024px) {
            .lg\\:grid-cols-3 {
                -ms-grid-columns: 1fr 1fr 1fr;
            }
        }
        
        @media (min-width: 1280px) {
            .xl\\:grid-cols-4 {
                -ms-grid-columns: 1fr 1fr 1fr 1fr;
            }
        }
        
        /* Enhanced Jewelry Loading Animations */
        @keyframes sparkle {
            0%, 100% { 
                opacity: 0.2; 
                transform: scale(0.8) rotate(0deg); 
                filter: brightness(1);
            }
            50% { 
                opacity: 1; 
                transform: scale(1.2) rotate(180deg); 
                filter: brightness(1.5);
            }
        }
        
        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg); 
            }
            33% { 
                transform: translateY(-12px) rotate(2deg); 
            }
            66% { 
                transform: translateY(6px) rotate(-2deg); 
            }
        }
        
        @keyframes gemShine {
            0% { 
                background-position: -200% 0;
                opacity: 0.8;
            }
            50% {
                opacity: 1;
            }
            100% { 
                background-position: 200% 0;
                opacity: 0.8;
            }
        }
        
        @keyframes luxuryProgress {
            0% { width: 0%; }
            20% { width: 30%; }
            50% { width: 65%; }
            80% { width: 85%; }
            100% { width: 100%; }
        }
        
        @keyframes ringRotate {
            0% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(90deg) scale(1.05); }
            50% { transform: rotate(180deg) scale(1); }
            75% { transform: rotate(270deg) scale(1.05); }
            100% { transform: rotate(360deg) scale(1); }
        }
        
        @keyframes jewelryGlow {
            0%, 100% { 
                box-shadow: 0 0 20px rgba(36, 116, 182, 0.3), 0 0 40px rgba(36, 116, 182, 0.1);
            }
            50% { 
                box-shadow: 0 0 30px rgba(36, 116, 182, 0.5), 0 0 60px rgba(36, 116, 182, 0.2);
            }
        }
        
        @keyframes cascadeBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0) rotate(45deg); }
            40% { transform: translateY(-15px) rotate(45deg); }
            60% { transform: translateY(-10px) rotate(45deg); }
        }
        
        @keyframes textShimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        
        .diamond-sparkle {
            animation: sparkle 3s ease-in-out infinite;
        }
        
        .floating-element {
            animation: float 8s ease-in-out infinite;
        }
        
        .gem-shine {
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.5) 50%, transparent 70%);
            background-size: 200% 100%;
            animation: gemShine 2.5s ease-in-out infinite;
        }
        
        .luxury-progress {
            animation: luxuryProgress 4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        
        .ring-rotate {
            animation: ringRotate 3s linear infinite;
        }
        
        .jewelry-glow {
            animation: jewelryGlow 2s ease-in-out infinite;
        }
        
        .cascade-bounce-1 {
            animation: cascadeBounce 1.5s ease-in-out infinite;
        }
        
        .cascade-bounce-2 {
            animation: cascadeBounce 1.5s ease-in-out infinite 0.2s;
        }
        
        .cascade-bounce-3 {
            animation: cascadeBounce 1.5s ease-in-out infinite 0.4s;
        }
        
        .text-blue-shimmer {
            background: linear-gradient(90deg, 
                #1e5a8a 0%, 
                #2474b6 25%, 
                #60a5fa 50%, 
                #2474b6 75%, 
                #1e5a8a 100%);
            background-size: 200% auto;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: textShimmer 3s linear infinite;
        }
}
</style>
  </head>
<body class="min-h-screen jewelry-gradient">
    
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50 w-full">
      <div class="w-full px-6">
        <div class="flex items-center justify-between py-4">
          <!-- Logo Section -->
          <div class="flex items-center">
          <a href="index.php">
              <img src="Image/LCJ.png" alt="LA CONSOLACION Jewelry Logo" class="h-12 w-auto">
          </a>
        </div>



                    <!-- Profile Section -->
          <div class="flex items-center space-x-8">
          <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-6">
            <a href="shop.php" class="inter text-gray-700 hover:text-blue-600 transition-colors duration-300 font-medium">Shop All</a>
            <a href="#steps" class="inter text-gray-700 hover:text-blue-600 transition-colors duration-300 font-medium">Order Custom</a>
            <a href="#about" class="inter text-gray-700 hover:text-blue-600 transition-colors duration-300 font-medium">About Us</a>
            <a href="#contact" class="inter text-gray-700 hover:text-blue-600 transition-colors duration-300 font-medium">Contact Us</a>
        </nav>

            <div class="relative">
          <button
            id="profileDropdownBtn"
                class="w-10 h-10 rounded-full flex items-center justify-center hover:scale-105 transition-transform cursor-pointer border-2 border-white shadow-md"
                style="background: linear-gradient(135deg, #2474b6 0%, #1e5a8a 100%);"
          >
            <?php
            $profileImg = '';
            $initial = 'G';
            $profileName = 'Guest';
            $profileEmail = 'Not logged in';
            if (isset($_SESSION['user_id'])) {
              require_once 'db.php';
              $stmt = $pdo->prepare('SELECT username, email, first_name, profile_image FROM users WHERE id = ?');
              $stmt->execute([$_SESSION['user_id']]);
              $user = $stmt->fetch();
              if ($user) {
                $profileName = $user['username'];
                $profileEmail = $user['email'];
                // Generate initials from first name (first two letters)
                $initial = substr(strtoupper($user['first_name']), 0, 2);
                if (!empty($user['profile_image'])) {
                  $profileImg = 'Image/profile/' . $user['profile_image'];
                }
              }
            }
            ?>
            <?php if ($profileImg): ?>
                  <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover shadow-md">
            <?php else: ?>
                  <span class="text-indigo-600 font-bold text-sm"><?php echo $initial; ?></span>
                <?php endif; ?>
              </button>
              
              <!-- Profile Dropdown -->
              <div
                id="profileDropdown"
                class="absolute right-0 mt-3 w-72 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50 hidden transform opacity-0 scale-95 transition-all duration-300 ease-out backdrop-blur-sm"
              >
                <!-- User Info Section -->
                <div class="px-6 py-4 rounded-t-2xl mb-2" style="background: linear-gradient(135deg, #2474b6 0%, #1e5a8a 100%);">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center overflow-hidden">
                      <?php if (!empty($user['profile_image'])): ?>
                        <img src="Image/profile/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                             alt="Profile" 
                             class="w-full h-full object-cover">
                      <?php else: ?>
                        <div class="w-full h-full bg-white flex items-center justify-center border-2 border-white shadow-md">
                          <span class="text-indigo-600 font-bold text-sm"><?php echo $initial; ?></span>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="flex-1">
                      <div class="text-sm font-semibold text-white truncate">
                        <?php echo htmlspecialchars($profileName); ?>
                      </div>
                      <div class="text-xs text-white/80 truncate mt-0.5">
                        <?php echo htmlspecialchars($profileEmail); ?>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Menu Items -->
                <div class="px-2">
                  <a
                    href="profile.php"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-200 group mx-1"
                  >
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-blue-50">
                      <i class="bx bx-user text-base text-blue-600"></i>
                    </div>
                    <span class="font-medium">View Profile</span>
                    <i class="bx bx-chevron-right ml-auto text-gray-400"></i>
                  </a>
                  
                  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <a
                    href="admin-overview.php"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-200 group mx-1 mt-1"
                  >
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-blue-50">
                      <i class="bx bx-cog text-base text-blue-600"></i>
                    </div>
                    <span class="font-medium">Admin Dashboard</span>
                    <i class="bx bx-chevron-right ml-auto text-gray-400"></i>
                  </a>
            <?php endif; ?>
                  
                  <a
                    href="#" onclick="openCartPanel(); return false;"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-200 group mx-1 mt-1"
                  >
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-blue-50">
                      <i class="bx bx-cart text-base text-blue-600"></i>
                    </div>
                    <span class="font-medium">My Cart</span>
                    <i class="bx bx-chevron-right ml-auto text-gray-400"></i>
                  </a>
                  
                  <a
                    href="#" onclick="openOrderHistoryPanel(); return false;"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-200 group mx-1 mt-1"
                  >
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-green-50">
                      <i class="bx bx-history text-base text-green-600"></i>
                    </div>
                    <span class="font-medium">Order History</span>
                    <i class="bx bx-chevron-right ml-auto text-gray-400"></i>
                  </a>
                </div>
                
                <!-- Logout Section -->
                <div class="border-t border-gray-100 mt-2 pt-2 px-2">
                  <a
                    href="logout.php"
                    class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-all duration-200 group mx-1"
                  >
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-red-50">
                      <i class="bx bx-log-out text-base text-red-600"></i>
                    </div>
                    <span class="font-medium">Logout</span>
                    <i class="bx bx-chevron-right ml-auto text-red-600"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <i class="bx bx-menu text-2xl text-gray-700"></i>
          </button>
        </div>
      </div>
    </header>



    <!-- Header Section -->
    <div class="relative z-10 pt-16 pb-12 w-full">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Title and Description -->
            <div class="text-center mb-16">
                <h1 class="playfair text-5xl md:text-6xl font-bold mb-6 leading-tight" style="color: #22306a;">
                    Luxury Jewelry
                </h1>
                <p class="inter text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Discover our exquisite collection of handcrafted jewelry pieces, each telling a unique story of elegance and sophistication.
                </p>
            </div>

                    <!-- Filter/Sort Bar -->
        <div class="flex flex-wrap justify-between items-center mb-12 gap-4">
                <div class="flex flex-wrap gap-3">
                    <button class="filter-btn px-6 py-3 rounded-xl text-white font-medium inter transition-all duration-300 luxury-shadow border border-blue-500/30 active" style="background-color: #22306a;" data-filter="all">
                        All Items
                    </button>
                    <button class="filter-btn px-6 py-3 rounded-xl bg-white/60 backdrop-blur-sm text-gray-600 font-medium inter hover:bg-white/80 hover:text-gray-700 transition-all duration-300 border border-white/30" data-filter="ring">
                        Rings
                    </button>
                    <button class="filter-btn px-6 py-3 rounded-xl bg-white/60 backdrop-blur-sm text-gray-600 font-medium inter hover:bg-white/80 hover:text-gray-700 transition-all duration-300 border border-white/30" data-filter="charm">
                        Charms
                    </button>
                    <button class="filter-btn px-6 py-3 rounded-xl bg-white/60 backdrop-blur-sm text-gray-600 font-medium inter hover:bg-white/80 hover:text-gray-700 transition-all duration-300 border border-white/30" data-filter="bracelet">
                        Bracelets
                    </button>
                    <button class="filter-btn px-6 py-3 rounded-xl bg-white/60 backdrop-blur-sm text-gray-600 font-medium inter hover:bg-white/80 hover:text-gray-700 transition-all duration-300 border border-white/30" data-filter="earring">
                        Earrings
                    </button>
                </div>
                
                <div class="flex items-center gap-4">
                    <span class="inter text-gray-600 text-sm">Sort by:</span>
                    <select id="sortSelect" class="px-4 py-2 rounded-lg bg-white/80 backdrop-blur-sm border border-white/50 text-gray-700 inter text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-200">
                        <option value="featured">Featured</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="newest">Newest</option>
                        <option value="name-asc">Name: A to Z</option>
                        <option value="name-desc">Name: Z to A</option>
                    </select>
                </div>
            </div>
        </div>
      </div>

    <!-- Products Grid -->
    <div class="max-w-7xl mx-auto px-6 pb-20">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-10" id="productsGrid">
            
            <?php foreach ($products as $index => $product): ?>
            <!-- Product Card -->
            <div class="product-card fade-in card-hover bg-white/90 backdrop-blur-xl rounded-2xl overflow-hidden luxury-shadow border border-white/30 cursor-pointer w-64" 
                 data-category="<?php echo strtolower($product['type']); ?>" 
                 onclick="openModal('<?php echo htmlspecialchars($product['name']); ?>', '<?php echo htmlspecialchars($product['type']); ?>', '<?php echo number_format($product['price'], 2); ?>', 'Image/Product/<?php echo htmlspecialchars($product['image']); ?>', <?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['description'] ?? ''); ?>', <?php echo (int)$product['stock']; ?>)">
                
                <!-- Image Section -->
                <div class="relative bg-gradient-to-br from-blue-50/80 via-white/90 to-indigo-50/80 p-4">
                    <img src="Image/Product/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="w-full h-48 object-cover rounded-xl image-glow hover:scale-105 transition-transform duration-700">
                </div>
                
                <!-- Content Section - Clean and Minimal -->
                <div class="p-5">
                    <!-- Category -->
                    <div class="mb-3">
                        <span class="text-xs font-medium text-gray-500 inter uppercase tracking-wider">
                            <?php echo strtoupper(htmlspecialchars($product['type'])); ?>
                        </span>
                    </div>
                    
                    <!-- Product Name -->
                    <h3 class="playfair text-xl font-semibold text-gray-900 mb-3 leading-tight">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    
                    <!-- Price -->
                    <div class="flex items-baseline gap-1">
                        <span class="cormorant text-2xl font-bold gradient-text">₱<?php echo number_format($product['price'], 2); ?></span>
                        <span class="inter text-sm text-gray-400 font-light">PHP</span>
                    </div>
                </div>
              </div>
            <?php endforeach; ?>
            
        </div>
        
        <!-- No Products for Filter Message (Hidden by default) -->
        <div id="noFilterProducts" class="hidden text-center pt-2 pb-20 animate-fade-in">
            <div class="relative max-w-md mx-auto">
                <!-- Simple Icon -->
                <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-white to-gray-50 rounded-full flex items-center justify-center luxury-shadow border border-white/50">
                    <i class="bx bx-search-alt text-2xl text-blue-600"></i>
                </div>
                
                <!-- Simple Title -->
                <h3 class="playfair text-2xl font-semibold text-gray-700 mb-4">No <span id="filterCategoryName">Items</span> Found</h3>
            </div>
        </div>
      </div>

    <!-- Slide-out Cart Panel -->
    <div id="cartPanel" class="fixed top-0 right-0 h-full w-80 md:w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
        <div class="flex flex-col h-full">
            <!-- Cart Header -->
            <div class="relative p-6 text-white overflow-hidden" style="background: linear-gradient(135deg, #2474b6 0%, #1e5a8a 100%);">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-12 -translate-x-12"></div>
                
                <div class="relative flex items-center justify-between">
                    <div>
                        <h2 class="playfair text-2xl font-semibold mb-1">Shopping Cart</h2>
                        <p class="inter text-sm text-white/80">Your selected items</p>
                    </div>
                    <button onclick="closeCartPanel()" class="p-3 hover:bg-white/20 rounded-xl transition-all duration-300 hover:scale-110">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Cart Items -->
            <div id="cartItems" class="flex-1 overflow-y-auto p-6 space-y-4">
                <!-- Cart items will be loaded here -->
            </div>
            
            <!-- Cart Footer -->
            <div class="relative p-6 bg-white border-t border-gray-200">
                <!-- Decorative line -->
                <div class="absolute top-0 left-6 right-6 h-px bg-gradient-to-r from-transparent via-blue-300 to-transparent"></div>
                
                <div class="space-y-4">
                    <!-- Cart Summary -->
                    <div class="space-y-2 py-2">
                        <div class="flex justify-between items-center">
                            <span class="inter text-sm text-gray-600">Items:</span>
                            <span id="cartStockTotal" class="inter text-sm font-medium text-gray-900">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="playfair text-xl font-semibold text-gray-900">Total</span>
                            <span id="cartTotal" class="cormorant text-3xl font-bold gradient-text">₱0.00</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="space-y-3 pt-4">
                        <button id="checkoutBtn" onclick="proceedToCheckout()" class="w-full py-4 px-6 text-white font-semibold rounded-2xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 inter text-lg relative overflow-hidden group" style="background: linear-gradient(135deg, #2474b6 0%, #1e5a8a 100%);">
                            <span class="relative z-10">Proceed to Checkout</span>
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="background: linear-gradient(135deg, #1e5a8a 0%, #154a73 100%);"></div>
                        </button>
                        
                        <button onclick="closeCartPanel()" class="w-full py-3 px-6 border-2 border-blue-200 text-blue-700 font-medium rounded-2xl hover:border-blue-300 hover:bg-blue-50 transition-all duration-300 inter group">
                            <span class="group-hover:text-blue-900">Continue Shopping</span>
                        </button>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="flex items-center justify-center pt-4 space-x-6 text-xs inter text-blue-600">
                        <div class="flex items-center space-x-1">
                            <i class="bx bx-shield-check text-blue-500"></i>
                            <span>Secure</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <i class="bx bx-truck text-blue-500"></i>
                            <span>Free Shipping</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Overlay -->
    <div id="cartOverlay" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeCartPanel()"></div>

        <!-- Order History Panel -->
    <div id="orderHistoryPanel" class="fixed top-0 right-0 h-full w-full md:w-96 bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-out hidden">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="relative p-6 bg-gradient-to-r from-green-600 to-green-700 text-white">
                <!-- Decorative elements -->
                <div class="absolute top-0 left-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 -translate-x-16"></div>
                <div class="absolute bottom-0 right-0 w-24 h-24 bg-white/5 rounded-full translate-y-12 translate-x-12"></div>
                
                <div class="relative flex items-center justify-between">
                    <div>
                        <h2 class="playfair text-2xl font-semibold mb-1">Order History</h2>
                        <p class="inter text-sm text-white/80">Your past orders</p>
                    </div>
                    <button onclick="closeOrderHistoryPanel()" class="p-3 hover:bg-white/20 rounded-xl transition-all duration-300 hover:scale-110">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Order Items -->
            <div id="orderHistoryItems" class="flex-1 overflow-y-auto p-6 space-y-4">
                <!-- Order items will be loaded here -->
            </div>
            
            <!-- Footer -->
            <div class="relative p-6 bg-white border-t border-gray-200">
                <!-- Decorative line -->
                <div class="absolute top-0 left-6 right-6 h-px bg-gradient-to-r from-transparent via-green-300 to-transparent"></div>
                
                <div class="space-y-4">
                    <!-- Summary -->
                    <div class="space-y-2 py-2">
                        <div class="flex justify-between items-center">
                            <span class="inter text-sm text-gray-600">Total Orders:</span>
                            <span id="orderHistoryTotal" class="inter text-sm font-medium text-gray-900">0</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="space-y-3 pt-4">
                        <button onclick="closeOrderHistoryPanel()" class="w-full py-3 px-6 border-2 border-green-200 text-green-700 font-medium rounded-2xl hover:border-green-300 hover:bg-green-50 transition-all duration-300 inter group">
                            <span class="group-hover:text-green-900">Close</span>
                        </button>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="flex items-center justify-center pt-4 space-x-6 text-xs inter text-green-600">
                        <div class="flex items-center space-x-1">
                            <i class="bx bx-shield-check text-green-500"></i>
                            <span>Secure</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <i class="bx bx-history text-green-500"></i>
                            <span>Track Orders</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order History Overlay -->
    <div id="orderHistoryOverlay" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeOrderHistoryPanel()"></div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="fixed inset-0 z-[9999] hidden">
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeOrderDetailsModal()"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full mx-4 h-[90vh] transform transition-all duration-300 scale-95 opacity-0" id="orderDetailsModalContent">
                <!-- Header -->
                <div class="relative p-6 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-t-2xl">
                    <!-- Decorative elements -->
                    <div class="absolute top-0 left-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 -translate-x-16"></div>
                    <div class="absolute bottom-0 right-0 w-24 h-24 bg-white/5 rounded-full translate-y-12 translate-x-12"></div>
                    
                    <div class="relative flex items-center justify-between">
                        <div>
                            <h2 id="orderDetailsTitle" class="playfair text-2xl font-semibold mb-1">Order Details</h2>
                            <p id="orderDetailsDate" class="inter text-sm text-white/80">Loading...</p>
                        </div>
                        <button onclick="closeOrderDetailsModal()" class="p-3 hover:bg-white/20 rounded-xl transition-all duration-300 hover:scale-110">
                            <i class="bx bx-x text-4xl"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="flex-1 p-6">
                    <!-- Loading State -->
                    <div id="orderDetailsLoading" class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
                        <p class="text-gray-500">Loading order details...</p>
                    </div>
                    
                    <!-- Order Details Content -->
                    <div id="orderDetailsContent" class="hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                <!-- Order Status -->
                                <div class="bg-white border border-gray-200 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-base font-semibold text-gray-900">Order Status</h3>
                                        <div id="orderStatusBadge" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                                            <!-- Status badge will be inserted here -->
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs text-gray-600">Order ID</p>
                                            <p id="orderId" class="font-semibold text-gray-900 text-sm">-</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600">Total Amount</p>
                                            <p id="orderTotal" class="font-semibold text-gray-900 text-sm">-</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Information -->
                                <div class="bg-white border border-gray-200 rounded-xl p-4">
                                    <h3 class="text-base font-semibold text-gray-900 mb-3">Payment Information</h3>
                                    <div id="paymentInfo" class="space-y-3">
                                        <!-- Payment info will be inserted here -->
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div id="orderActions" class="flex justify-end space-x-3 pt-4">
                                    <!-- Action buttons will be inserted here -->
                                </div>
                                

                            </div>
                            
                            <!-- Right Column -->
                            <div class="space-y-4">
                                <!-- Products -->
                                <div class="bg-white border border-gray-200 rounded-xl p-4">
                                    <h3 class="text-base font-semibold text-gray-900 mb-3">Products</h3>
                                    <div id="orderProducts" class="space-y-3 max-h-96 overflow-y-auto">
                                        <!-- Products will be inserted here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div id="cancelOrderModal" class="fixed inset-0 z-[9999] hidden">
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeCancelOrderModal()"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="cancelOrderModalContent">
                <!-- Header -->
                <div class="relative p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="bx bx-x-circle text-2xl text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Cancel Order</h3>
                            <p class="text-sm text-gray-600">Are you sure you want to cancel this order?</p>
                        </div>
                    </div>
                    <button onclick="closeCancelOrderModal()" class="absolute top-4 right-4 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="bx bx-x text-xl text-gray-500"></i>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <div class="mb-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <i class="bx bx-info-circle text-red-600 mt-0.5"></i>
                                <div>
                                    <h4 class="font-semibold text-red-800 mb-1">Order #<span id="cancelOrderId"></span></h4>
                                    <p class="text-sm text-red-700 leading-relaxed">
                                        This action will:
                                    </p>
                                    <ul class="text-sm text-red-700 mt-2 space-y-1">
                                        <li class="flex items-center space-x-2">
                                            <i class="bx bx-check text-red-600"></i>
                                            <span>Cancel your order immediately</span>
                                        </li>
                                        <li class="flex items-center space-x-2">
                                            <i class="bx bx-check text-red-600"></i>
                                            <span>Restore product stock</span>
                                        </li>
                                        <li class="flex items-center space-x-2">
                                            <i class="bx bx-x text-red-600"></i>
                                            <span>Cannot be undone</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-600 mb-6">
                        <p>This action is permanent and cannot be reversed. Please make sure you want to cancel this order before proceeding.</p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="flex space-x-3 p-6 border-t border-gray-200">
                    <button onclick="closeCancelOrderModal()" class="flex-1 py-3 px-4 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-300">
                        Keep Order
                    </button>
                    <button onclick="confirmCancelOrder()" class="flex-1 py-3 px-4 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition-all duration-300">
                        Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="productModal" class="fixed inset-0 z-50 hidden">
        <!-- Floating Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="floating-element absolute top-20 left-20 w-4 h-4 gold-gradient rounded-full opacity-20"></div>
            <div class="floating-element absolute top-40 right-32 w-6 h-6 bg-white rounded-full opacity-30" style="animation-delay: -2s;"></div>
            <div class="floating-element absolute bottom-32 left-1/4 w-3 h-3 bg-blue-300 rounded-full opacity-25" style="animation-delay: -4s;"></div>
            <div class="floating-element absolute bottom-20 right-20 w-5 h-5 bg-gradient-to-r from-blue-200 to-indigo-200 rounded-full opacity-20" style="animation-delay: -1s;"></div>
        </div>

        <!-- Modal Overlay -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-md modal-backdrop z-40" onclick="closeModal()"></div>
        
        <!-- Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="relative z-50 w-full max-w-6xl mx-auto modal-entrance" onclick="event.stopPropagation()">
                <div class="bg-white/95 backdrop-blur-xl rounded-3xl luxury-shadow overflow-hidden border border-white/20">
                    
                    <!-- Close Button -->
                    <button onclick="closeModal()" class="absolute top-6 right-6 z-10 w-12 h-12 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-all duration-300 group luxury-shadow">
                        <svg class="w-6 h-6 text-gray-600 group-hover:text-gray-800 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <div class="flex flex-col lg:flex-row gap-0">
                        <!-- Image Section -->
                        <div class="lg:w-1/2 relative bg-gradient-to-br from-blue-50/50 via-white/80 to-indigo-50/50 p-6 flex flex-col items-center justify-center">

                            <!-- Main Product Image -->
                            <div class="relative group mb-6">
                                <div class="absolute inset-0 gold-gradient rounded-2xl blur-xl opacity-20 group-hover:opacity-30 transition-opacity duration-500"></div>
                                <div class="relative bg-white/60 backdrop-blur-sm rounded-2xl p-8 luxury-shadow border border-white/40">
                                    <img id="modalProductImage" 
                                         alt="Product" 
                                         src="Image/Product/prod_12_bracelet_687b5031419d6.jpg"
                                         class="w-full h-[500px] object-cover rounded-xl image-glow hover:scale-105 transition-transform duration-700">
                                </div>
                            </div>

                        </div>
                        <!-- Content Section -->
                        <div class="lg:w-1/2 p-8 lg:p-12 bg-gradient-to-br from-white/95 to-gray-50/80 backdrop-blur-sm">
                            
                            <!-- Product Category -->
                            <div class="mb-6">
                                <span id="modalProductType" class="inline-block px-4 py-2 rounded-xl text-sm font-medium bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 border border-gray-200/50 inter uppercase tracking-wider">
                                    Premium Collection
                                </span>
                            </div>
                            
                            <!-- Product Name -->
                            <h1 id="modalProductName" class="playfair text-4xl lg:text-5xl font-semibold text-gray-900 mb-4 leading-tight">
                                Product Name
                            </h1>
                            
                            <!-- Product Description -->
                            <p id="modalProductDescription" class="inter text-gray-600 text-lg mb-8 leading-relaxed">
                                An exquisite piece crafted with precision and elegance. This stunning jewelry features intricate details that capture and reflect light beautifully, creating a celestial sparkle that embodies luxury and sophistication.
                            </p>
                            
                            <!-- Price Section -->
                            <div class="mb-8">
                                <div class="flex items-baseline gap-2 mb-2">
                                    <span id="modalProductPrice" class="cormorant text-5xl font-bold gold-gradient bg-clip-text text-transparent">₱0.00</span>
                                    <span class="inter text-lg text-gray-400 font-light">PHP</span>
                                </div>
                                <div class="inline-flex items-center px-3 py-1 rounded-full bg-gradient-to-r from-emerald-400 to-green-500 text-white shadow-lg mb-2">
                                    <div class="w-2 h-2 bg-white rounded-full mr-2 opacity-90"></div>
                                    <span class="text-sm font-medium inter" id="modalStockBadge">In Stock - X Available</span>
                                </div>
                                <p class="inter text-sm text-gray-500">Pickup only - Visit our store</p>
                            </div>
                            
                            <!-- Features -->
                            <div class="mb-8">
                                <h3 class="playfair text-xl font-semibold text-gray-900 mb-4">Features</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 gold-gradient rounded-full diamond-sparkle"></div>
                                        <span class="inter text-gray-700">Premium Materials</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 gold-gradient rounded-full diamond-sparkle" style="animation-delay: -0.3s;"></div>
                                        <span class="inter text-gray-700">Handcrafted Design</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 gold-gradient rounded-full diamond-sparkle" style="animation-delay: -0.6s;"></div>
                                        <span class="inter text-gray-700">Quality Guaranteed</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-4">
                                <button id="buyNowBtn" class="w-full py-4 px-6 gold-gradient text-white font-semibold rounded-2xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 inter text-lg">
                                    Buy Now
                                </button>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <button id="addToCartBtn" class="py-3 px-4 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 inter">
                                        Add to Cart
                                    </button>
                                    <button class="py-3 px-4 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 inter">
                                        Share
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Trust Indicators -->
                            <div class="mt-8 pt-8 border-t border-gray-200">
                                <div class="flex items-center justify-between text-sm inter text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>Authentic</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>Certified</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>Insured</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white/95 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="text-center animate-fade-in">
            <!-- Simple Spinner -->
            <div class="mb-8 flex justify-center">
                <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-900 rounded-full animate-spin-slow"></div>
            </div>
            
            <!-- Loading Text -->
            <p class="text-gray-600 font-medium animate-pulse-slow text-lg">Loading...</p>
        </div>
    </div>

    <script>
        // Global function declarations
        window.openModal = function(name, type, price, image, productId, description, stock) {
            document.getElementById('modalProductName').textContent = name;
            document.getElementById('modalProductType').textContent = type;
            document.getElementById('modalProductPrice').textContent = '₱' + price;
            document.getElementById('modalProductImage').src = image;
            document.getElementById('modalProductImage').alt = name;
            
            // Update product description
            const descriptionElement = document.getElementById('modalProductDescription');
            if (descriptionElement) {
                const defaultDescription = 'An exquisite piece crafted with precision and elegance. This stunning jewelry features intricate details that capture and reflect light beautifully, creating a celestial sparkle that embodies luxury and sophistication.';
                const maxLength = defaultDescription.length; // Character limit based on default description
                
                let displayDescription = description || defaultDescription;
                
                // Truncate if description exceeds character limit
                if (displayDescription.length > maxLength) {
                    displayDescription = displayDescription.substring(0, maxLength) + '...';
                }
                
                descriptionElement.textContent = displayDescription;
            }
            
            // Update stock badge
            const stockBadge = document.getElementById('modalStockBadge');
            if (stockBadge) {
                stockBadge.textContent = `In Stock - ${stock} Available`;
            }
            
            // Store product ID for add to cart and buy now functionality
            document.getElementById('addToCartBtn').setAttribute('data-product-id', productId);
            document.getElementById('buyNowBtn').setAttribute('data-product-id', productId);
            
            // Check cart quantity and update add to cart button
            checkCartQuantityAndUpdateButtonInShop({
                id: productId,
                name: name,
                price: price,
                image: image,
                stock: stock
            });
            
            const modal = document.getElementById('productModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.closeModal = function() {
            const modal = document.getElementById('productModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        };

        // Function to check cart quantity and update button in shop.php
        window.checkCartQuantityAndUpdateButtonInShop = function(product) {
            // Get current cart quantity for this product
            fetch(`get-cart-quantity.php?product_id=${product.id}`)
                .then(response => response.json())
                .then(data => {
                    const addToCartBtn = document.getElementById('addToCartBtn');
                    if (!addToCartBtn) return;

                    if (data.success) {
                        const currentQuantity = data.quantity;
                        const maxStock = parseInt(product.stock);

                        if (maxStock <= 0) {
                            // Out of stock - disable button
                            addToCartBtn.textContent = 'Out of Stock';
                            addToCartBtn.disabled = true;
                            addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            addToCartBtn.style.backgroundColor = '#9ca3af';
                            addToCartBtn.style.color = 'white';
                            addToCartBtn.style.borderColor = '#9ca3af';
                        } else if (currentQuantity >= maxStock) {
                            // At max stock - disable button
                            addToCartBtn.textContent = `Max Stock Reached (${currentQuantity}/${maxStock})`;
                            addToCartBtn.disabled = true;
                            addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            addToCartBtn.style.backgroundColor = '#9ca3af';
                            addToCartBtn.style.color = 'white';
                            addToCartBtn.style.borderColor = '#9ca3af';
                        } else {
                            // Can add more - enable button
                            addToCartBtn.textContent = currentQuantity > 0 ? `Add to Cart (${currentQuantity}/${maxStock})` : `Add to Cart (0/${maxStock})`;
                            addToCartBtn.disabled = false;
                            addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            // Reset all inline styles to default
                            addToCartBtn.style.backgroundColor = '';
                            addToCartBtn.style.color = '';
                            addToCartBtn.style.borderColor = '';
                            addToCartBtn.style.cursor = '';
                            addToCartBtn.style.opacity = '';
                            // Reset to default button classes
                            addToCartBtn.className = 'py-3 px-4 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 inter';
                        }
                    } else {
                        // Error or not logged in - show default button
                        console.log('Error or not logged in, showing default button'); // Debug log
                        addToCartBtn.textContent = 'Add to Cart';
                        addToCartBtn.disabled = false;
                        addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        // Reset all inline styles to default
                        addToCartBtn.style.backgroundColor = '';
                        addToCartBtn.style.color = '';
                        addToCartBtn.style.borderColor = '';
                        addToCartBtn.style.cursor = '';
                        addToCartBtn.style.opacity = '';
                        // Reset to default button classes
                        addToCartBtn.className = 'py-3 px-4 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 inter';
                    }
                })
                .catch(error => {
                    console.error('Error checking cart quantity:', error);
                    // Fallback to default button
                    const addToCartBtn = document.getElementById('addToCartBtn');
                    if (addToCartBtn) {
                        addToCartBtn.textContent = 'Add to Cart';
                        addToCartBtn.disabled = false;
                        addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        // Reset all inline styles to default
                        addToCartBtn.style.backgroundColor = '';
                        addToCartBtn.style.color = '';
                        addToCartBtn.style.borderColor = '';
                        addToCartBtn.style.cursor = '';
                        addToCartBtn.style.opacity = '';
                        // Reset to default button classes
                        addToCartBtn.className = 'py-3 px-4 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 inter';
                    }
                });
        };

        // Define loadCartItems first since it's used by openCartPanel
        window.loadCartItems = function() {
            fetch('get-cart-items.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderCartItems(data.items);
                    updateCartTotal(data.total);
                    updateCartBadge(data.items.length);
                    updateCartStockTotal(data.items);
                }
            })
            .catch(error => {
                console.error('Error loading cart items:', error);
            });
        };

        window.renderCartItems = function(items) {
            const cartItemsContainer = document.getElementById('cartItems');
            
            if (items.length === 0) {
                cartItemsContainer.innerHTML = `
                    <div class="text-center py-12">
                        <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                            <i class="bx bx-cart text-3xl text-blue-500"></i>
                        </div>
                        <h3 class="playfair text-lg font-semibold text-gray-700 mb-2">Your cart is empty</h3>
                        <p class="inter text-sm text-gray-500 mb-6">Add some beautiful jewelry to get started</p>
                        <button onclick="closeCartPanel()" class="px-6 py-3 text-white font-medium rounded-xl hover:shadow-lg transition-all duration-300 inter" style="background: linear-gradient(135deg, #2474b6 0%, #1e5a8a 100%);">
                            Start Shopping
                        </button>
                    </div>
                `;
                // Disable checkout button when cart is empty
                const checkoutBtn = document.getElementById('checkoutBtn');
                if (checkoutBtn) {
                    checkoutBtn.disabled = true;
                    checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    checkoutBtn.classList.remove('hover:shadow-2xl', 'hover:-translate-y-1');
                }
                return;
            }

            cartItemsContainer.innerHTML = items.map(item => `
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4">
                        <!-- Top Row: Image and Product Details -->
                        <div class="flex items-start space-x-4 mb-4">
                            <!-- Product Image -->
                            <div class="relative flex-shrink-0">
                                <img src="Image/Product/${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg shadow-sm">
                            </div>
                            
                            <!-- Product Details -->
                            <div class="flex-1 min-w-0">
                                <h4 class="playfair font-semibold text-gray-900 text-sm leading-tight mb-1">${item.name}</h4>
                                <p class="inter text-xs text-gray-500 uppercase tracking-wider mb-2">${item.type}</p>
                                <p class="cormorant font-bold gradient-text text-lg mb-2">₱${parseFloat(item.price).toLocaleString()}</p>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="bx bx-package mr-1"></i>
                                        Stock: ${item.stock}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bottom Row: Controls and Total -->
                        <div class="flex items-center justify-between">
                            <!-- Quantity Controls -->
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center bg-gray-50 rounded-lg p-1 border border-gray-200">
                                    <button onclick="updateCartQuantity(${item.product_id}, -1)" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-white rounded-md transition-all duration-200 ${item.quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''}" ${item.quantity <= 1 ? 'disabled' : ''}>
                                        <i class="bx bx-minus text-sm"></i>
                                    </button>
                                    <span class="px-4 py-1 text-sm font-bold text-gray-900 min-w-[2rem] text-center">${item.quantity}</span>
                                    <button onclick="updateCartQuantity(${item.product_id}, 1)" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-white rounded-md transition-all duration-200 ${item.quantity >= item.stock ? 'opacity-50 cursor-not-allowed' : ''}" ${item.quantity >= item.stock ? 'disabled' : ''}>
                                        <i class="bx bx-plus text-sm"></i>
                                    </button>
                                </div>
                                
                                <!-- Remove Button -->
                                <button onclick="removeCartItem(${item.product_id})" class="w-8 h-8 flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200">
                                    <i class="bx bx-trash text-sm"></i>
                                </button>
                            </div>
                            
                            <!-- Item Total -->
                            <div class="flex items-center space-x-2 bg-gray-50 rounded-lg px-3 py-1">
                                <span class="inter text-sm font-medium text-gray-700">Total:</span>
                                <span class="cormorant font-bold gradient-text text-lg">₱${(parseFloat(item.price) * item.quantity).toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Enable checkout button when cart has items
            const checkoutBtn = document.getElementById('checkoutBtn');
            if (checkoutBtn) {
                checkoutBtn.disabled = false;
                checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                checkoutBtn.classList.add('hover:shadow-2xl', 'hover:-translate-y-1');
            }
        };

        window.updateCartTotal = function(total) {
            document.getElementById('cartTotal').textContent = '₱' + parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2});
        };

        window.updateCartBadge = function(count) {
            const badge = document.getElementById('cartBadge');
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        };

        window.updateCartStockTotal = function(items) {
            const totalStock = items.reduce((sum, item) => sum + parseInt(item.quantity), 0);
            const stockElement = document.getElementById('cartStockTotal');
            if (stockElement) {
                stockElement.textContent = totalStock;
            }
        };

        window.updateCartQuantity = function(productId, change) {
            // Get current quantity from the DOM
            const button = event.target.closest('button');
            
            // Check if button is disabled
            if (button.disabled) {
                return;
            }
            
            const quantityContainer = button.parentElement;
            const quantityElement = quantityContainer.querySelector('span');
            
            if (!quantityElement) {
                console.error('Quantity element not found');
                return;
            }
            
            const currentQuantity = parseInt(quantityElement.textContent);
            if (isNaN(currentQuantity)) {
                console.error('Invalid quantity value');
                return;
            }
            
            const newQuantity = currentQuantity + change;
            
            fetch('update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_quantity&product_id=${productId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartItems();
                    loadCartCount();
                } else {
                    alert('Error updating quantity: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating quantity');
            });
        };

        window.removeCartItem = function(productId) {
            if (!confirm('Remove this item from cart?')) return;
            
            fetch('update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartItems();
                    loadCartCount();
                } else {
                    alert('Error removing item: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing item');
            });
        };

        window.loadCartCount = function() {
            fetch('get-cart-count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                }
            })
            .catch(error => {
                console.error('Error loading cart count:', error);
            });
        };

        window.updateCartCount = function(count) {
            // Update cart count display if it exists
            const cartBadge = document.getElementById('cartBadge');
            if (cartBadge) {
                cartBadge.textContent = count;
                cartBadge.style.display = count > 0 ? 'flex' : 'none';
            }
            
            // Log for debugging
            console.log('Cart count updated:', count);
        };

        // Profile check function
        window.checkProfileCompletion = function() {
            return new Promise((resolve, reject) => {
                fetch('check-profile-completion.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (!data.hasPhoneNumber) {
                            // Show profile update reminder modal
                            showProfileUpdateModal();
                            resolve(false);
                        } else {
                            resolve(true);
                        }
                    } else {
                        reject(new Error('Failed to check profile'));
                    }
                })
                .catch(error => {
                    console.error('Error checking profile:', error);
                    reject(error);
                });
            });
        };

        // Profile update reminder modal
        window.showProfileUpdateModal = function() {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center backdrop-blur-lg';
            modal.style.background = 'rgba(0, 0, 0, 0.5)';
            modal.innerHTML = `
                <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-gray-200/60 max-w-md w-full mx-4 relative overflow-hidden">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                            <i class="bx bx-user-plus text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Complete Your Profile</h3>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            Please update your profile and add your phone number to continue with your purchase. 
                            This helps us contact you regarding your order.
                        </p>
                        <div class="flex space-x-3 w-full">
                            <button onclick="closeProfileModal()" class="flex-1 py-3 px-4 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-300">
                                Continue Shopping
                            </button>
                            <button onclick="goToProfile()" class="flex-1 py-3 px-4 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-all duration-300">
                                Update Profile
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        };

        window.closeProfileModal = function() {
            const modal = document.querySelector('.fixed.inset-0.z-\\[9999\\]');
            if (modal) {
                modal.remove();
            }
        };

        window.goToProfile = function() {
            closeProfileModal();
            window.location.href = 'profile.php';
        };

        // Simple Loading Manager
        window.JewelryLoadingManager = class {
            constructor() {
                this.overlay = document.getElementById('loadingOverlay');
            }

            show() {
                this.overlay.classList.remove('hidden');
            }

            hide() {
                this.overlay.classList.add('hidden');
            }

            completeProgress() {
                return new Promise((resolve) => {
                    setTimeout(resolve, 500);
                });
            }
        };

        // Initialize the loading manager
        window.jewelryLoader = new JewelryLoadingManager();

        // Enhanced loading functions
        window.showLoading = function() {
            jewelryLoader.show();
        };

        window.hideLoading = function() {
            return jewelryLoader.completeProgress().then(() => {
                return new Promise(resolve => {
                    setTimeout(() => {
                        jewelryLoader.hide();
                        resolve();
                    }, 500);
                });
            });
        };

        window.openCartPanel = function() {
            // Close profile dropdown if open
            const profileDropdown = document.getElementById('profileDropdown');
            if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
                profileDropdown.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    profileDropdown.classList.add('hidden');
                }, 200);
            }
            
            document.getElementById('cartPanel').classList.remove('translate-x-full');
            document.getElementById('cartOverlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadCartItems();
        };

        window.closeCartPanel = function() {
            document.getElementById('cartPanel').classList.add('translate-x-full');
            document.getElementById('cartOverlay').classList.add('hidden');
            document.body.style.overflow = 'auto';
        };

        // Order History Panel Functions
        window.openOrderHistoryPanel = function() {
            // Close profile dropdown if open
            const profileDropdown = document.getElementById('profileDropdown');
            if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
                profileDropdown.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    profileDropdown.classList.add('hidden');
                }, 200);
            }
            
            document.getElementById('orderHistoryPanel').classList.remove('translate-x-full', 'hidden');
            document.getElementById('orderHistoryOverlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadOrderHistory();
        };

        window.closeOrderHistoryPanel = function() {
            document.getElementById('orderHistoryPanel').classList.add('translate-x-full');
            document.getElementById('orderHistoryOverlay').classList.add('hidden');
            document.body.style.overflow = 'auto';
        };

        window.loadOrderHistory = function() {
            fetch('get-user-orders.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderOrderHistory(data.orders);
                    updateOrderHistoryTotal(data.total_orders);
                } else {
                    console.error('Error loading orders:', data.error);
                    renderOrderHistory([]);
                }
            })
            .catch(error => {
                console.error('Error loading order history:', error);
                renderOrderHistory([]);
            });
        };

        window.renderOrderHistory = function(orders) {
            const orderHistoryContainer = document.getElementById('orderHistoryItems');
            
            if (orders.length === 0) {
                orderHistoryContainer.innerHTML = `
                    <div class="text-center py-12">
                        <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center">
                            <i class="bx bx-history text-3xl text-green-500"></i>
                        </div>
                        <h3 class="playfair text-lg font-semibold text-gray-700 mb-2">No orders yet</h3>
                        <p class="inter text-sm text-gray-500 mb-6">Start shopping to see your order history</p>
                        <button onclick="closeOrderHistoryPanel()" class="px-6 py-3 text-white font-medium rounded-xl hover:shadow-lg transition-all duration-300 inter" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            Start Shopping
                        </button>
                    </div>
                `;
                return;
            }

            orderHistoryContainer.innerHTML = orders.map(order => `
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4">
                        <!-- Order Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="playfair font-semibold text-gray-900 text-sm leading-tight mb-1">Order #${order.order_id}</h4>
                                <p class="inter text-xs text-gray-500">${order.order_date}</p>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="cormorant font-bold gradient-text text-2xl mb-1">₱${order.total_amount}</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border ${order.status_class}">
                                    ${order.status_text}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="mb-3">
                            <p class="inter text-sm text-gray-600 leading-relaxed">${order.order_items}</p>
                        </div>
                        
                        <!-- Payment Info -->
                        ${order.reference_number ? `
                            <div class="mb-3 p-2 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between text-xs text-gray-600">
                                    <span>Payment: ${order.service}</span>
                                    <span>Ref: ${order.reference_number}</span>
                                </div>
                            </div>
                        ` : ''}
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <button onclick="openOrderDetailsModal(${order.order_id})" class="px-3 py-1 text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition-all duration-200 text-sm font-medium">
                                    View Details
                                </button>
                                ${order.can_cancel ? `
                                    <button onclick="cancelOrder(${order.order_id})" class="px-3 py-1 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200 text-sm font-medium">
                                        Cancel Order
                                    </button>
                                ` : ''}
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-400">ID: ${order.order_id}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        };

        window.updateOrderHistoryTotal = function(count) {
            const totalElement = document.getElementById('orderHistoryTotal');
            if (totalElement) {
                totalElement.textContent = count;
            }
        };

        // Global variable to store the order ID for cancellation
        window.pendingCancelOrderId = null;

        window.cancelOrder = function(orderId) {
            // Store the order ID for later use
            window.pendingCancelOrderId = orderId;
            
            // Update the modal content
            document.getElementById('cancelOrderId').textContent = orderId;
            
            // Show the modal
            const modal = document.getElementById('cancelOrderModal');
            const modalContent = document.getElementById('cancelOrderModalContent');
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Animate the modal in
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        };

        window.closeCancelOrderModal = function() {
            const modal = document.getElementById('cancelOrderModal');
            const modalContent = document.getElementById('cancelOrderModalContent');
            
            // Animate the modal out
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                window.pendingCancelOrderId = null;
            }, 300);
        };

        window.confirmCancelOrder = function() {
            if (!window.pendingCancelOrderId) {
                console.error('No order ID found for cancellation');
                return;
            }
            
            const orderId = window.pendingCancelOrderId;
            
            // Show loading state
            const confirmBtn = document.querySelector('#cancelOrderModal button[onclick="confirmCancelOrder()"]');
            const originalText = confirmBtn.textContent;
            confirmBtn.textContent = 'Cancelling...';
            confirmBtn.disabled = true;
            
            fetch('cancel-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showSuccessMessage(data.message);
                    closeCancelOrderModal();
                    loadOrderHistory(); // Refresh the order history
                } else {
                    // Show error message
                    showErrorMessage('Error cancelling order: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('Error cancelling order');
            })
            .finally(() => {
                // Reset button state
                confirmBtn.textContent = originalText;
                confirmBtn.disabled = false;
            });
        };

        // Success message function
        window.showSuccessMessage = function(message) {
            const successModal = document.createElement('div');
            successModal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
            successModal.innerHTML = `
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="bx bx-check text-3xl text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Success!</h3>
                        <p class="text-gray-600 mb-6">${message}</p>
                        <button onclick="this.closest('.fixed').remove()" class="w-full py-3 px-4 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-all duration-300">
                            OK
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(successModal);
            
            // Animate in
            setTimeout(() => {
                successModal.querySelector('.bg-white').classList.remove('scale-95', 'opacity-0');
                successModal.querySelector('.bg-white').classList.add('scale-100', 'opacity-100');
            }, 10);
        };

        // Error message function
        window.showErrorMessage = function(message) {
            const errorModal = document.createElement('div');
            errorModal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
            errorModal.innerHTML = `
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="bx bx-x text-3xl text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Error</h3>
                        <p class="text-gray-600 mb-6">${message}</p>
                        <button onclick="this.closest('.fixed').remove()" class="w-full py-3 px-4 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition-all duration-300">
                            OK
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(errorModal);
            
            // Animate in
            setTimeout(() => {
                errorModal.querySelector('.bg-white').classList.remove('scale-95', 'opacity-0');
                errorModal.querySelector('.bg-white').classList.add('scale-100', 'opacity-100');
            }, 10);
        };

        // Order Details Modal Functions
        window.openOrderDetailsModal = function(orderId) {
            const modal = document.getElementById('orderDetailsModal');
            const modalContent = document.getElementById('orderDetailsModalContent');
            const loadingDiv = document.getElementById('orderDetailsLoading');
            const contentDiv = document.getElementById('orderDetailsContent');
            
            // Show modal and loading state
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadingDiv.classList.remove('hidden');
            contentDiv.classList.add('hidden');
            
            // Animate the modal in
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Fetch order details
            fetch(`get-user-order-details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Hide loading, show content
                    loadingDiv.classList.add('hidden');
                    contentDiv.classList.remove('hidden');
                    
                    // Update modal content
                    document.getElementById('orderDetailsTitle').textContent = `Order #${data.order.id}`;
                    document.getElementById('orderDetailsDate').textContent = `Ordered on ${new Date(data.order.date_ordered).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}`;
                    
                    // Update order status
                    document.getElementById('orderId').textContent = data.order.id;
                    document.getElementById('orderTotal').textContent = `₱${parseFloat(data.total).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                    
                    // Update status badge
                    const statusBadge = document.getElementById('orderStatusBadge');
                    statusBadge.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${data.order.status_style.bg} ${data.order.status_style.text}`;
                    statusBadge.innerHTML = `
                        <span class="w-2 h-2 rounded-full ${data.order.status_style.dot} mr-2"></span>
                        ${data.order.status_style.label}
                    `;
                    
                    // Update products
                    const productsContainer = document.getElementById('orderProducts');
                    productsContainer.innerHTML = data.products.map(product => `
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                    <img src="Image/Product/${product.image}" 
                                         alt="${product.name}" 
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-full h-full bg-gradient-to-br from-violet-400 to-violet-500 flex items-center justify-center" style="display:none;">
                                        <i class="bx bx-gem text-lg text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 text-sm truncate">${product.name}</h4>
                                    <div class="flex items-center justify-between mt-1">
                                        <div class="text-xs text-gray-500">
                                            <span class="font-medium">Qty: ${product.quantity}</span>
                                            <span class="mx-1">•</span>
                                            <span class="font-medium">₱${parseFloat(product.price).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-gray-900 text-sm">₱${parseFloat(product.total).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    
                    // Update payment info
                    const paymentContainer = document.getElementById('paymentInfo');
                    paymentContainer.innerHTML = `
                        <div class="space-y-3">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center">
                                            <i class="bx bx-credit-card text-blue-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">Payment Method</span>
                                    </div>
                                    <span class="font-medium text-blue-700 text-sm">${data.payment.service.toUpperCase()}</span>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-green-100 rounded flex items-center justify-center">
                                            <i class="bx bx-hash text-green-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">Reference Number</span>
                                    </div>
                                    <span class="font-mono text-xs text-green-700 bg-green-50 px-2 py-1 rounded">${data.payment.reference_number}</span>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-purple-100 rounded flex items-center justify-center">
                                            <i class="bx bx-money text-purple-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">Amount Paid</span>
                                    </div>
                                    <span class="font-bold text-purple-700 text-sm">₱${parseFloat(data.payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-amber-100 rounded flex items-center justify-center">
                                            <i class="bx bx-calendar text-amber-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">Payment Date</span>
                                    </div>
                                    <span class="text-gray-900 text-sm">${data.payment.uploaded_at}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Update action buttons
                    const actionsContainer = document.getElementById('orderActions');
                    let actionButtons = '';
                    
                    if (data.can_cancel) {
                        actionButtons += `
                            <button onclick="closeOrderDetailsModal(); cancelOrder(${data.order.id})" class="px-6 py-3 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition-all duration-300">
                                Cancel Order
                            </button>
                        `;
                    }
                    
                    actionsContainer.innerHTML = actionButtons;
                    
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    loadingDiv.classList.add('hidden');
                    contentDiv.classList.remove('hidden');
                    contentDiv.innerHTML = `
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="bx bx-error text-3xl text-red-600"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Error Loading Order</h3>
                            <p class="text-gray-600 mb-6">${error.message || 'Failed to load order details'}</p>
                            <button onclick="closeOrderDetailsModal()" class="px-6 py-3 bg-gray-600 text-white font-medium rounded-xl hover:bg-gray-700 transition-all duration-300">
                                Close
                            </button>
                        </div>
                    `;
                });
        };

        window.closeOrderDetailsModal = function() {
            const modal = document.getElementById('orderDetailsModal');
            const modalContent = document.getElementById('orderDetailsModalContent');
            
            // Animate the modal out
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        };

        window.proceedToCheckout = function() {
            // Check if button is disabled (cart is empty)
            const checkoutBtn = document.getElementById('checkoutBtn');
            if (checkoutBtn && checkoutBtn.disabled) {
                return; // Don't proceed if button is disabled
            }
            
            // Check profile completion first
            checkProfileCompletion()
            .then(profileComplete => {
                if (profileComplete) {
                    // Show enhanced loading overlay
                    showLoading();
                    // Start 4-second timer for enhanced experience
                    const minWait = new Promise(resolve => setTimeout(resolve, 4000));
                    // Store cart items in localStorage for payment page
                    const cartFetch = fetch('get-cart-items.php')
                        .then(response => response.json());
                    Promise.all([cartFetch, minWait])
                        .then(async ([data]) => {
                            if (data.success && data.items.length > 0) {
                                const cartItems = data.items.map(item => ({
                                    id: item.product_id,
                                    qty: item.quantity,
                                    name: item.name,
                                    price: parseFloat(item.price),
                                    image: 'Image/Product/' + item.image
                                }));
                                localStorage.setItem('cartItems', JSON.stringify(cartItems));
                                localStorage.setItem('fromCart', 'true');
                                closeCartPanel();
                                // Complete loading animation
                                await hideLoading();
                                window.location.href = 'payment/payment.html';
                            } else {
                                hideLoading();
                                alert('Your cart is empty');
                            }
                        })
                        .catch(error => {
                            hideLoading();
                            console.error('Error:', error);
                            alert('Error proceeding to checkout');
                        });
                }
            })
            .catch(error => {
                console.error('Error checking profile:', error);
                alert('Error checking profile completion');
            });
        };

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const productCards = document.querySelectorAll('.product-card');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    // Update active button
                    filterButtons.forEach(btn => {
                        btn.classList.remove('text-white', 'border-blue-500/30', 'active');
                        btn.classList.add('bg-white/60', 'text-gray-600', 'border-white/30');
                        btn.style.backgroundColor = '';
                    });
                    this.classList.remove('bg-white/60', 'text-gray-600', 'border-white/30');
                    this.classList.add('text-white', 'border-blue-500/30', 'active');
                    this.style.backgroundColor = '#22306a';

                    // Smooth filtering with slide animations
                    const animationDelay = 50; // Stagger delay between cards
                    
                    // Count visible cards first
                    let visibleCards = 0;
                    productCards.forEach(card => {
                        const cardCategory = card.getAttribute('data-category');
                        const shouldShow = filter === 'all' || cardCategory === filter;
                        if (shouldShow) visibleCards++;
                    });
                    
                    // First, hide all cards with slide-out animation
                    productCards.forEach((card, index) => {
                        card.classList.remove('fade-in', 'slide-in-left', 'slide-in-right');
                        card.classList.add('slide-out');
                        
                        setTimeout(() => {
                            if (card.classList.contains('slide-out')) {
                                card.style.display = 'none';
                            }
                        }, 300); // Hide after slide-out animation
                    });
                    
                    // Then show matching cards with slide-in animation after a delay
                    setTimeout(() => {
                        productCards.forEach((card, index) => {
                            const cardCategory = card.getAttribute('data-category');
                            const shouldShow = filter === 'all' || cardCategory === filter;
                            
                            if (shouldShow) {
                                // Show card with staggered slide-in animation
                                setTimeout(() => {
                                    card.style.display = 'block';
                                    card.classList.remove('slide-out');
                                    card.classList.add('slide-in-left');
                                }, index * animationDelay);
                            } else {
                                // Ensure card stays hidden if it shouldn't be shown
                                card.style.display = 'none';
                                card.classList.remove('slide-in-left');
                            }
                        });
                    }, 350); // Wait for slide-out animations to complete
                    
                    // Show/hide no products message
                    const noFilterProducts = document.getElementById('noFilterProducts');
                    const productsGrid = document.getElementById('productsGrid');
                    
                    if (visibleCards === 0 && filter !== 'all') {
                        // No products for this filter - show message
                        setTimeout(() => {
                            productsGrid.style.display = 'none';
                            noFilterProducts.classList.remove('hidden');
                            noFilterProducts.classList.add('animate-fade-in');
                        }, 350);
                        
                        // Update message text based on filter
                        const filterCategoryName = document.getElementById('filterCategoryName');
                        const categoryNames = {
                            'ring': 'Rings',
                            'bracelet': 'Bracelets', 
                            'earring': 'Earrings',
                            'charm': 'Charms'
                        };
                        
                        const categoryName = categoryNames[filter] || 'Items';
                        filterCategoryName.textContent = categoryName;
                        
                    } else {
                        // Products found - hide message and show grid
                        noFilterProducts.classList.add('hidden');
                        noFilterProducts.classList.remove('animate-fade-in');
                        productsGrid.style.display = 'grid';
                    }
                    
                    // Reset sort to featured when filtering
                    document.getElementById('sortSelect').value = 'featured';
                });
            });

  // Profile dropdown functionality
  const profileDropdownBtn = document.getElementById('profileDropdownBtn');
  const profileDropdown = document.getElementById('profileDropdown');
            
  if (profileDropdownBtn && profileDropdown) {
    profileDropdownBtn.addEventListener('click', function(e) {
      e.stopPropagation();
                    
      if (profileDropdown.classList.contains('hidden')) {
                        // Show dropdown with animation
        profileDropdown.classList.remove('hidden');
        setTimeout(() => {
          profileDropdown.classList.remove('opacity-0', 'scale-95');
          profileDropdown.classList.add('opacity-100', 'scale-100');
        }, 10);
      } else {
                        // Hide dropdown with animation
        profileDropdown.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
          profileDropdown.classList.add('hidden');
        }, 200);
      }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!profileDropdownBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
        if (!profileDropdown.classList.contains('hidden')) {
          profileDropdown.classList.add('opacity-0', 'scale-95');
          setTimeout(() => {
            profileDropdown.classList.add('hidden');
          }, 200);
        }
      }
    });

    // Close dropdown on escape key
    document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        if (!profileDropdown.classList.contains('hidden')) {
        profileDropdown.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
          profileDropdown.classList.add('hidden');
        }, 200);
                        }
      }
    });
  }

            // Modal functions are now defined globally above

            // Close modal on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });

            // Buy Now functionality
            document.getElementById('buyNowBtn').addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = document.getElementById('modalProductName').textContent;
                const productPrice = parseFloat(document.getElementById('modalProductPrice').textContent.replace('₱', '').replace(',', ''));
                const productImage = document.getElementById('modalProductImage').src;
                
                if (!productId) {
                    alert('Product ID not found');
                    return;
                }

                // Check profile completion first
                checkProfileCompletion()
                .then(profileComplete => {
                    if (profileComplete) {
                        // Show loading state
                        const originalText = this.innerHTML;
                        const originalClasses = this.className;
                        this.innerHTML = '<div class="flex items-center justify-center"><div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>Processing...</div>';
                        this.disabled = true;
                        this.style.backgroundColor = '#6b7280';
                        this.style.color = 'white';

                        // Show enhanced loading overlay
                        showLoading();

                        // Start 4-second timer for enhanced experience
                        const minWait = new Promise(resolve => setTimeout(resolve, 4000));

                        // Process the purchase
                        const processPurchase = new Promise((resolve) => {
                            // Create a single-item cart for direct purchase
                            const directPurchaseItem = [{
                                id: parseInt(productId),
                                qty: 1,
                                name: productName,
                                price: productPrice,
                                image: productImage.replace(window.location.origin + '/LCJ-ver2/', '')
                            }];

                            // Store in localStorage for payment page
                            localStorage.setItem('cartItems', JSON.stringify(directPurchaseItem));
                            localStorage.setItem('fromCart', 'false');
                            localStorage.setItem('cartTotal', productPrice.toString());

                            resolve();
                        });

                        Promise.all([processPurchase, minWait])
                            .then(async () => {
                                // Complete loading animation
                                await hideLoading();
                                // Redirect to payment page
                                window.location.href = 'payment/payment.html';
                            })
                            .catch(error => {
                                // Reset button state on error
                                this.innerHTML = originalText;
                                this.className = originalClasses;
                                this.disabled = false;
                                this.style.backgroundColor = '';
                                this.style.color = '';
                                
                                // Hide loading overlay
                                hideLoading();
                                
                                console.error('Error:', error);
                                alert('Error processing purchase');
                            });
                    }
                })
                .catch(error => {
                    console.error('Error checking profile:', error);
                    alert('Error checking profile completion');
                });
            });

            // Add to Cart functionality
            document.getElementById('addToCartBtn').addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                if (!productId) {
                    alert('Product ID not found');
                    return;
                }

                // Show loading state with spinner
                const originalText = this.textContent;
                const originalClasses = this.className;
                this.innerHTML = '<div class="flex items-center justify-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>Adding...</div>';
                this.disabled = true;
                this.style.backgroundColor = '#6b7280';
                this.style.color = 'white';
                this.style.borderColor = '#6b7280';

                fetch('add-to-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message with checkmark
                        this.innerHTML = '<div class="flex items-center justify-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Added!</div>';
                        this.style.backgroundColor = '#10b981';
                        this.style.color = 'white';
                        this.style.borderColor = '#10b981';
                        
                        setTimeout(() => {
                            // Update button state based on new cart quantity
                            const productId = this.getAttribute('data-product-id');
                            const productName = document.getElementById('modalProductName').textContent;
                            const productPrice = document.getElementById('modalProductPrice').textContent.replace('₱', '');
                            const productImage = document.getElementById('modalProductImage').src;
                            const productStock = document.getElementById('modalStockBadge')?.textContent.match(/\d+/)?.[0] || '0';
                            
                            checkCartQuantityAndUpdateButtonInShop({
                                id: productId,
                                name: productName,
                                price: productPrice,
                                image: productImage,
                                stock: productStock
                            });
                        }, 2000);
                        
                        // Update cart count in header if it exists
                        updateCartCount(data.cart_count);
                        
                        // Open slide-out cart panel
                        openCartPanel();
                    } else {
                        alert('Error: ' + data.error);
                        this.textContent = originalText;
                        this.className = originalClasses;
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error adding to cart');
                    this.textContent = originalText;
                    this.className = originalClasses;
                    this.disabled = false;
                });
            });

            // Function to update cart count
            function updateCartCount(count) {
                // You can add a cart count badge to the header if needed
                console.log('Cart count updated:', count);
            }

            // Load cart count on page load
            function loadCartCount() {
                fetch('get-cart-count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                    }
                })
                .catch(error => {
                    console.error('Error loading cart count:', error);
                });
            }

            // Load cart count when page loads
            loadCartCount();
            
            // Load cart items when page loads
            loadCartItems();
            
            // Sort functionality
            const sortSelect = document.getElementById('sortSelect');
            const productsGrid = document.getElementById('productsGrid');
            
            sortSelect.addEventListener('change', function() {
                const sortValue = this.value;
                const productCards = Array.from(document.querySelectorAll('.product-card'));
                
                // Get current filter
                const activeFilter = document.querySelector('.filter-btn.bg-white\\/80')?.getAttribute('data-filter') || 'all';
                
                // Sort products
                productCards.sort((a, b) => {
                    const aPrice = parseFloat(a.querySelector('.gradient-text').textContent.replace('₱', '').replace(',', ''));
                    const bPrice = parseFloat(b.querySelector('.gradient-text').textContent.replace('₱', '').replace(',', ''));
                    const aName = a.querySelector('h3').textContent.toLowerCase();
                    const bName = b.querySelector('h3').textContent.toLowerCase();
                    
                    switch(sortValue) {
                        case 'price-low':
                            return aPrice - bPrice;
                        case 'price-high':
                            return bPrice - aPrice;
                        case 'name-asc':
                            return aName.localeCompare(bName);
                        case 'name-desc':
                            return bName.localeCompare(aName);
                        case 'newest':
                            // Assuming newer products come first in the DOM
                            return 0; // Keep original order
                        case 'featured':
                        default:
                            return 0; // Keep original order
                    }
                });
                
                // Smooth re-append sorted products with slide animations
                const animationDelay = 30; // Faster stagger for sorting
                
                productCards.forEach((card, index) => {
                    const cardCategory = card.getAttribute('data-category');
                    const shouldShow = activeFilter === 'all' || cardCategory === activeFilter;
                    
                    if (shouldShow) {
                        // Show card with staggered slide-in animation
                        setTimeout(() => {
                            card.style.display = 'block';
                            card.classList.remove('slide-out');
                            card.classList.add('slide-in-left');
                        }, index * animationDelay);
                    } else {
                        // Hide card with slide-out animation
                        card.classList.remove('slide-in-left');
                        card.classList.add('slide-out');
                        
                        setTimeout(() => {
                            if (card.classList.contains('slide-out')) {
                                card.style.display = 'none';
                            }
                        }, 300);
                    }
                    productsGrid.appendChild(card);
                });
            });
            
            // Loading manager and functions are now defined globally above



            // Cart panel functions are now defined globally above

            // renderCartItems function is now defined globally above

            // All cart-related functions are now defined globally above

            // Profile check functions are now defined globally above

            // proceedToCheckout function is now defined globally above
            
            // Check if cart panel should be opened automatically
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('openCart') === 'true') {
                // Small delay to ensure everything is loaded
                setTimeout(() => {
                    openCartPanel();
                    // Remove the parameter from URL without reloading
                    const newUrl = window.location.pathname + window.location.search.replace('?openCart=true', '').replace('&openCart=true', '');
                    window.history.replaceState({}, document.title, newUrl);
                }, 500);
            }
            
            // Check if order history modal should be opened automatically
            if (urlParams.get('openOrderHistory') === 'true') {
                // Small delay to ensure everything is loaded
                setTimeout(() => {
                    openOrderHistoryPanel();
                    // Remove the parameter from URL without reloading
                    const newUrl = window.location.pathname + window.location.search.replace('?openOrderHistory=true', '').replace('&openOrderHistory=true', '');
                    window.history.replaceState({}, document.title, newUrl);
                }, 500);
            }

        }); // Close DOMContentLoaded event listener
    </script>
  </body>
</html>
