<?php
session_start();
require_once 'database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Fetch current user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $mobile_number = trim($_POST['mobile_number']);
    $profile_image = $user['profile_image'];

    // Validate fields
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email)) {
        $errors[] = 'First name, last name, username, and email are required.';
    }
    if (!empty($mobile_number) && !preg_match('/^\d{11}$/', $mobile_number)) {
        $errors[] = 'Mobile number must be exactly 11 digits.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (!empty($phone) && !preg_match('/^\d{11}$/', $phone)) {
        $errors[] = 'Phone number must be exactly 11 digits.';
    }

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $imgTmp = $_FILES['profile_image']['tmp_name'];
        $imgName = basename($_FILES['profile_image']['name']);
        $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($imgExt, $allowed)) {
            $newName = 'profile_' . $user_id . '_' . uniqid() . '.' . $imgExt;
            $targetDir = 'Image/profile/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $targetFile = $targetDir . $newName;
            if (move_uploaded_file($imgTmp, $targetFile)) {
                // Optionally delete old image
                if (!empty($profile_image) && file_exists($targetDir . $profile_image)) {
                    @unlink($targetDir . $profile_image);
                }
                $profile_image = $newName;
            } else {
                $errors[] = 'Image upload failed.';
            }
        } else {
            $errors[] = 'Invalid image type.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE users SET profile_image=?, first_name=?, last_name=?, username=?, email=?, mobile_number=? WHERE id=?');
        $success = $stmt->execute([
            $profile_image,
            $first_name,
            $last_name,
            $username,
            $email,
            $mobile_number,
            $user_id
        ]);
        if ($success) {
            // Update session username/email if changed
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            // Refresh user data
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            // Set success message in session to show only once
            $_SESSION['profile_update_success'] = true;
        } else {
            $errors[] = 'Failed to update profile.';
        }
    }
}

// Generate initials from first name (first two letters)
$initials = substr(strtoupper($user['first_name']), 0, 2);
$profileImgUrl = !empty($user['profile_image']) ? 'Image/profile/' . $user['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=2474b6&color=fff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - La Consolacion Jewelry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    (function() {
      const isLoggedIn = <?php echo (isset($_SESSION['user_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['admin_logged_in'])) ? 'true' : 'false'; ?>;
      if (isLoggedIn) {
        if (!sessionStorage.getItem('session_active')) {
          const pathname = window.location.pathname;
          let logoutUrl = 'logout.php';
          if (pathname.includes('/admin/')) {
            logoutUrl = '../logout.php';
          } else if (pathname.includes('/payment/')) {
            logoutUrl = '../logout.php';
          }
          window.location.href = logoutUrl;
        } else {
          sessionStorage.setItem('session_active', 'true');
        }
      } else {
        sessionStorage.setItem('session_active', 'true');
      }
    })();
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 50%, #e8eaf6 100%);
        }
        .glass {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .profile-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.85) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.4);
        }
        .input-field {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(36, 116, 182, 0.1);
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #2474b6;
            box-shadow: 0 0 0 4px rgba(36, 116, 182, 0.1);
            background: rgba(255,255,255,0.95);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2474b6 0%, #1e5a8a 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e5a8a 0%, #154a73 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(36, 116, 182, 0.3);
        }
        .profile-image-container {
            position: relative;
            display: inline-block;
        }
        .profile-image-container::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            background: linear-gradient(135deg, #2474b6, #1e5a8a, #2474b6);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.3;
        }
        .upload-btn {
            background: linear-gradient(135deg, #2474b6 0%, #1e5a8a 100%);
            transition: all 0.3s ease;
        }
        .upload-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(36, 116, 182, 0.4);
        }
        
        /* Mobile number validation styles */
        .input-field.border-red-500 {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1) !important;
        }
        
        .input-field.border-green-500 {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1) !important;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <!-- Header with Back Button -->
    <div class="container mx-auto px-4 py-6">
        
        <!-- Main Profile Card -->
        <div class="max-w-4xl mx-auto">
            <div class="profile-card rounded-2xl shadow-xl p-6 lg:p-8">
                <!-- Back Button in upper left -->
                <div class="flex justify-between items-start mb-6">
                    <button onclick="goBack()" class="inline-flex items-center justify-center w-10 h-10 text-gray-700">
                        <i class='bx bx-arrow-back text-lg'></i>
                    </button>
                    <div class="text-center flex-1">
                        <h1 class="text-2xl font-bold text-gray-800 mb-2">Profile Settings</h1>
                        <p class="text-gray-600 text-sm">Manage your account information</p>
                    </div>
                    <div class="w-10"></div> <!-- Spacer for centering -->
                </div>
                <!-- Success/Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <i class='bx bx-error-circle text-xl text-red-500'></i>
                            <h3 class="text-base font-semibold text-red-800">Please fix the following errors:</h3>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-red-700 text-sm">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php elseif (isset($_SESSION['profile_update_success']) && $_SESSION['profile_update_success']): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                        <div class="flex items-center gap-2">
                            <i class='bx bx-check-circle text-xl text-green-500'></i>
                            <div>
                                <h3 class="text-base font-semibold text-green-800">Success!</h3>
                                <p class="text-green-700 text-sm">Your profile has been updated successfully.</p>
                            </div>
                        </div>
                    </div>
                    <?php 
                    // Clear the success message after displaying
                    unset($_SESSION['profile_update_success']); 
                    ?>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6" onsubmit="return validateForm()">
                    <!-- Profile Image Section -->
                    <div class="flex flex-col items-center gap-4 mb-8">
                        <div class="profile-image-container">
                            <img src="<?php echo htmlspecialchars($profileImgUrl); ?>" 
                                 alt="Profile Picture" 
                                 class="w-24 h-24 lg:w-32 lg:h-32 rounded-full object-cover border-4 border-white shadow-xl hover:shadow-2xl transition-all duration-300">
                        </div>
                        <label class="upload-btn inline-flex items-center gap-2 px-4 py-2 text-white rounded-xl cursor-pointer shadow-lg hover:shadow-xl transition-all duration-300">
                            <i class='bx bx-camera text-lg'></i>
                            <span class="font-semibold text-sm">Change Photo</span>
                            <input type="file" name="profile_image" accept="image/*" class="hidden" onchange="previewProfileImage(event)">
                        </label>
                    </div>

                    <!-- Form Fields -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="block text-gray-800 font-semibold mb-2 text-base flex items-center gap-2">
                                    <i class='bx bx-user text-lg' style="color: #2474b6;"></i>
                                    First Name
                                </label>
                                <input type="text" 
                                       name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" 
                                       class="input-field w-full px-4 py-3 rounded-xl text-base shadow-sm transition-all duration-300" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="block text-gray-800 font-semibold mb-2 text-base flex items-center gap-2">
                                    <i class='bx bx-user text-lg' style="color: #2474b6;"></i>
                                    Last Name
                                </label>
                                <input type="text" 
                                       name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" 
                                       class="input-field w-full px-4 py-3 rounded-xl text-base shadow-sm transition-all duration-300" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="block text-gray-800 font-semibold mb-2 text-base flex items-center gap-2">
                                    <i class='bx bx-at text-lg' style="color: #2474b6;"></i>
                                    Username
                                </label>
                                <input type="text" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" 
                                       class="input-field w-full px-4 py-3 rounded-xl text-base shadow-sm transition-all duration-300" 
                                       required>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="block text-gray-800 font-semibold mb-2 text-base flex items-center gap-2">
                                    <i class='bx bx-envelope text-lg' style="color: #2474b6;"></i>
                                    Email Address
                                </label>
                                <input type="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       class="input-field w-full px-4 py-3 rounded-xl text-base shadow-sm transition-all duration-300" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="block text-gray-800 font-semibold mb-2 text-base flex items-center gap-2">
                                    <i class='bx bx-phone text-lg' style="color: #2474b6;"></i>
                                    Mobile Number
                                </label>
                                <input type="tel" 
                                       name="mobile_number" 
                                       value="<?php echo htmlspecialchars($user['mobile_number'] ?? ''); ?>" 
                                       class="input-field w-full px-4 py-3 rounded-xl text-base shadow-sm transition-all duration-300" 
                                       maxlength="11" 
                                       placeholder="09XXXXXXXXX">
                                <div id="mobileNumberWarning" class="hidden mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <i class='bx bx-error-circle text-red-500'></i>
                                        <span class="text-red-700 text-sm font-medium">Mobile number must be exactly 11 digits (e.g., 09123456789)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-center pt-6">
                        <button type="submit" class="btn-primary inline-flex items-center gap-2 px-8 py-3 text-white font-bold text-base rounded-xl shadow-lg">
                            <i class='bx bx-save text-lg'></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
  
    <script>
        function previewProfileImage(event) {
            const img = document.querySelector('img[alt="Profile Picture"]');
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        }
        
        function goBack() {
            if (document.referrer && document.referrer !== window.location.href) {
                window.history.back();
            } else {
                window.location.href = 'index.php';
            }
        }
        
        function validateMobileNumber(value) {
            const warning = document.getElementById('mobileNumberWarning');
            const input = document.querySelector('input[name="mobile_number"]');
            
            // Remove any non-digit characters
            const cleanValue = value.replace(/\D/g, '');
            
            // Update input value to only contain digits
            if (cleanValue !== value) {
                input.value = cleanValue;
            }
            
            // Show warning if not empty and not exactly 11 digits
            if (cleanValue.length > 0 && cleanValue.length !== 11) {
                warning.classList.remove('hidden');
                input.classList.add('border-red-500');
                input.classList.remove('border-green-500');
                return false;
            } else {
                warning.classList.add('hidden');
                input.classList.remove('border-red-500');
                if (cleanValue.length === 11) {
                    input.classList.add('border-green-500');
                } else {
                    input.classList.remove('border-green-500');
                }
                return true;
            }
        }
        
        function preventNonNumericInput(event) {
            // Allow: backspace, delete, tab, escape, enter, and navigation keys
            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(event.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (event.keyCode === 65 && event.ctrlKey === true) ||
                (event.keyCode === 67 && event.ctrlKey === true) ||
                (event.keyCode === 86 && event.ctrlKey === true) ||
                (event.keyCode === 88 && event.ctrlKey === true)) {
                return;
            }
            
            // Allow only numbers (0-9)
            if (event.keyCode >= 48 && event.keyCode <= 57) {
                return;
            }
            
            // Allow numpad numbers
            if (event.keyCode >= 96 && event.keyCode <= 105) {
                return;
            }
            
            // Prevent all other keys
            event.preventDefault();
        }
        
        function validateForm() {
            const mobileInput = document.querySelector('input[name="mobile_number"]');
            const mobileValue = mobileInput.value.trim();
            
            // Only validate if mobile number is provided
            if (mobileValue.length > 0) {
                const isValid = validateMobileNumber(mobileValue);
                if (!isValid) {
                    // Scroll to the mobile number field
                    mobileInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    mobileInput.focus();
                    return false; // Prevent form submission
                }
            }
            
            return true; // Allow form submission
        }
        
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    sessionStorage.setItem('profileReturnUrl', document.referrer || 'index.php');
                });
            }
            
            // Add fade-in animation to form elements
            const formElements = document.querySelectorAll('.form-group');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Prevent non-numeric input in mobile number field
            const mobileInput = document.querySelector('input[name="mobile_number"]');
            if (mobileInput) {
                mobileInput.addEventListener('keydown', preventNonNumericInput);
            }
            
            // No initial validation - only validate on form submission
        });
    </script>
</body>
</html> 