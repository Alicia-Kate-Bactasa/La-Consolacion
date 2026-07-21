<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LA Consolacion Jewelry</title>
    <link
      href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css" />
    <!-- Note: Using Tailwind CDN for development. For production, install Tailwind CSS locally -->
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body>
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-100 w-full">
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
                      <?php if (isset($_SESSION['user_id']) && !empty($user['profile_image'])): ?>
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
                  <?php if (isset($_SESSION['user_id'])): ?>
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
                      href="shop.php?openCart=true"
                      class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-200 group mx-1 mt-1"
                    >
                      <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-blue-50">
                        <i class="bx bx-cart text-base text-blue-600"></i>
                      </div>
                      <span class="font-medium">My Cart</span>
                      <i class="bx bx-chevron-right ml-auto text-gray-400"></i>
                    </a>
                    
                                      <a
                    href="shop.php?openOrderHistory=true"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-200 group mx-1 mt-1"
                  >
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-green-50">
                      <i class="bx bx-history text-base text-green-600"></i>
                    </div>
                    <span class="font-medium">Order History</span>
                    <i class="bx bx-chevron-right ml-auto text-gray-400"></i>
                  </a>
                    
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
                  <?php else: ?>
                    <a
                      href="login.php"
                      class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-200 group mx-1"
                    >
                      <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-blue-50">
                        <i class="bx bx-log-in text-base text-blue-600"></i>
                      </div>
                      <span class="font-medium">Login</span>
                      <i class="bx bx-chevron-right ml-auto text-gray-400"></i>
                    </a>
                  <?php endif; ?>
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





    <script>
      document.addEventListener('DOMContentLoaded', function() {
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
            if (e.key === 'Escape' && !profileDropdown.classList.contains('hidden')) {
              profileDropdown.classList.add('opacity-0', 'scale-95');
              setTimeout(() => {
                profileDropdown.classList.add('hidden');
              }, 200);
            }
          });
        }




      });
    </script>

    <!-- Banner / Poster Section -->
    <section class="poster">
      <div class="banner-container">
        <div class="banner-content">
          <h1>Feel the Royal Touch of The Crown Diamond</h1>
          <p>This Month's Spotlight Jewelry</p>
          <a href="shop.php" class="shop-btn">Shop Now</a>
        </div>
      </div>
    </section>

    <!-- Our Best Sellers Section -->
    <section class="best-sellers-section">
      <h2 class="best-sellers-title">Our Best Sellers</h2>
      <div class="best-sellers-carousel">
        <button class="carousel-arrow left" aria-label="Previous">
            &#8249;
          </button>
        <div class="carousel-track">
          <div class="carousel-card">
            <img
              src="Image/Product/494324954_1183859846336540_5343954234173565708_n.jpg"
              alt="Crown Diamond Ring"
            />
            <div class="carousel-caption">Crown Diamond Ring</div>
            </div>
          <div class="carousel-card">
            <img
              src="Image/Product/494325447_1231333955173886_6890510470019316369_n.jpg"
              alt="Gold Teddy Bear Ring"
            />
            <div class="carousel-caption">Gold Teddy Bear Ring</div>
            </div>
          <div class="carousel-card">
            <img
              src="Image/Product/494325961_638023342401034_6252364838369281331_n.jpg"
              alt="Silver and Gold Pyramid Ring"
            />
            <div class="carousel-caption">Silver and Gold Pyramid Ring</div>
          </div>
        </div>
        <button class="carousel-arrow right" aria-label="Next">&#8250;</button>
      </div>
    </section>

    <!-- Collage Section  -->
    <section class="ready-section">
      <div class="ready-container">
        <div class="ready-image">
          <img src="Image/Ring.png" alt="" />
        </div>
        <div class="ready-content">
          <h2 class="ready-title">Ready-to-Wear Elegance</h2>
          <p class="ready-desc">
            Discover our curated collection of handcrafted, ready-to-wear pieces
            — perfect for effortless elegance, meaningful gifts, or everyday
            beauty. Each design is made with care and available for pickup
            in-store.
          </p>
          <a href="shop.php" class="shop-btn">
            Browse Collections <span class="ready-arrow">→</span>
          </a>
        </div>
      </div>
    </section>

    <!-- Consultation Section -->
    <section class="how-steps-section" id="steps">
      <div class="container">
        <h2 class="how-steps-title">Your Dream Piece in 3 Simple Steps</h2>
        <p class="how-steps-desc">
          Looking for something uniquely yours? We make it easy to bring your
          vision to life with our custom jewelry service. Just follow these
          simple steps:
        </p>
        <div class="how-steps-row">
          <div class="how-step">
            <i class="bx bx-phone-call"></i>
            <h3>Consult with Us</h3>
            <p>Share your vision, inspiration, and budget.</p>
          </div>
          <div class="how-step">
            <i class="bx bx-diamond"></i>
            <h3>Watch the Magic</h3>
            <p>We'll handcraft your piece with the finest materials.</p>
          </div>
          <div class="how-step">
            <i class="bx bx-gift"></i>
            <h3>Pickup in CDO</h3>
            <p>Once ready, pick it up at our store at your convenience.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Start Your Custom Journey Section -->
    <section class="custom-journey-section">
      <div class="custom-journey-container">
        <div class="custom-journey-content">
          <h2 class="custom-journey-title">Start Your Custom Journey</h2>
          <p class="custom-journey-desc">
            Some stories can't be told with ready-made pieces. Collaborate with
            our artisans to design jewelry that's truly personal—crafted around
            your ideas, style, and meaning. From one-of-a-kind heirlooms to
            deeply symbolic keepsakes, we'll turn your vision into something
            real.
          </p>
          <a
            href="https://docs.google.com/forms/d/e/1FAIpQLSdQcpfmLP5goLEnyX1elKV9T4KNeoWwgSjwtMcBNWBdTsjRSA/viewform?usp=header"
            target="_blank"
            rel="noopener noreferrer"
            class="custom-journey-btn"
            >Book a Consultation <span class="custom-journey-arrow">→</span></a
          >
        </div>
        <div class="custom-journey-image">
          <img src="Image/necklace.png" alt="Custom Jewelry Consultation" />
        </div>
      </div>
    </section>

    <!-- About Store Section -->
    <section class="about-store-section" id="about">
      <div class="container about-store-flex">
        <!-- Row 1: Crafting -->
        <div class="about-row">
          <div class="about-img-col">
            <img
              src="Image/Create.png"
              alt="Crafting Jewelry"
              class="about-craft-img"
            />
          </div>
          <div class="about-text-col">
            <h2 class="about-title">The Heart Behind the Craft</h2>
            <p class="about-desc">
              At La Consolacion Jewelry, we believe jewelry isn't just an
              accessory—it's a story, a memory, and a piece of art. Based in
              Cagayan de Oro, each piece we make is lovingly handcrafted with
              precision, care, and passion. Whether you're selecting from our
              thoughtfully designed premade collections or working with us to
              create a custom piece, you're guaranteed something truly
              one-of-a-kind.
            </p>
          </div>
        </div>
        <div class="about-row">
          <div class="about-text-col">
            <h2 class="store-title">Visit Our Store</h2>
            <p>
              Have a face-to-face consultation for your custom order or pickup
              your jewelry at our physical store.
            </p>
            <p class="store-name">
              <b>La Consolacion Jewelry</b><br />170 Capistrano St, Cagayan De
              Oro City, Misamis Oriental
              </p>
            <p class="store-hours">Open every Mon–Sat, 9:00 AM – 5:00 PM</p>
            <a
              href="https://www.google.com/maps/place/La+Consolacion+Jewelry/@8.4793738,124.6404635,1029m/data=!3m2!1e3!4b1!4m6!3m5!1s0x32fff2d78b3160c3:0x58d6709e18b5c78e!8m2!3d8.4793738!4d124.6430384!16s%2Fg%2F11gsbfpv_9?entry=ttu&g_ep=EgoyMDI1MDYyNi4wIKXMDSoASAFQAw%3D%3D"
              target="_blank"
              rel="noopener noreferrer"
              class="location-btn"
              >See Location →</a
            >
          </div>
          <div class="about-img-col">
            <img
              src="Image/Map.png"
              alt="La Consolacion Jewelry Storefront"
              class="about-craft-img"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer modern-footer">
      <div class="footer-bg">
        <div class="footer-row-modern">
          <div class="footer-contact-modern">
            <i class="bx bx-phone"></i>
            <span>+09203048490</span>
          </div>
          <div class="footer-logo-card">
            <img
              src="Image/LCJ.png"
              alt="La Consolacion Jewelry Logo"
              class="footer-logo-modern"
            />
          </div>
          <div class="footer-social-modern">
            <a href="#" class="footer-social-icon" aria-label="Instagram"
              ><i class="bx bxl-instagram"></i
            ></a>
            <a href="#" class="footer-social-icon" aria-label="Facebook"
              ><i class="bx bxl-facebook"></i
            ></a>
          </div>
        </div>
        <nav class="footer-nav-modern">
          <a href="index.php">Home</a>
          <a href="shop.php">Premade Collection</a>
          <a href="#steps">Order Custom</a>
          <a href="#about">About Us</a>
        </nav>
        <div class="footer-copyright-modern">
          &copy; 1980 La Consolacion Jewelry. All rights reserved.
        </div>
      </div>
    </footer>



    <script src="script.js?v=<?php echo time(); ?>"></script>
    <!-- Login Notification Modal -->
    <div id="loginNotifyModal" class="login-notify-modal-overlay" style="display: none">
      <div class="login-notify-modal">
        <button class="login-notify-close" id="loginNotifyClose">&times;</button>
        <div class="login-notify-title">Welcome to La Consolacion Jewelry</div>
        <div class="login-notify-message">
          Please log in to access your account and enjoy a personalized shopping experience.
        </div>
        <button class="login-notify-login" id="loginNotifyLoginBtn">Login</button>
      </div>
    </div>
    <script>
      // Show login notification modal if not logged in
      <?php if (!isset($_SESSION['user_id'])): ?>
      window.addEventListener('DOMContentLoaded', function() {
        document.getElementById('loginNotifyModal').style.display = 'flex';
      });
      document.getElementById('loginNotifyClose').onclick = function() {
        document.getElementById('loginNotifyModal').style.display = 'none';
      };
      document.getElementById('loginNotifyLoginBtn').onclick = function() {
        window.location.href = 'login.php';
      };
      <?php endif; ?>
      

    </script>
  </body>
</html>
