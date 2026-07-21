<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Initials Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Profile Initials Test</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">How Profile Initials Work</h2>
            <p class="text-gray-600 mb-4">
                When a new user registers, they automatically get profile initials based on their first name.
                If no profile image is uploaded, the system generates an avatar with their initials.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-2">Examples:</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-500 text-white font-bold">
                                JO
                            </div>
                            <span>John Doe → "JO"</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-500 text-white font-bold">
                                MA
                            </div>
                            <span>Mary Smith → "MA"</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-purple-500 text-white font-bold">
                                RO
                            </div>
                            <span>Robert Johnson → "RO"</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-2">Implementation:</h3>
                    <div class="bg-gray-100 p-3 rounded text-sm font-mono">
                        <div class="text-green-600">// Generate initials from first name</div>
                        <div>$initials = substr(strtoupper($user['first_name']), 0, 2);</div>
                        <br>
                        <div class="text-green-600">// Use ui-avatars.com for avatar generation</div>
                        <div>$avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=2474b6&color=fff';</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Files Updated</h2>
            <ul class="list-disc list-inside space-y-2 text-gray-600">
                <li><strong>profile.php</strong> - Main profile page now uses first two letters of first name</li>
                <li><strong>shop.php</strong> - Shop page profile display updated</li>
                <li><strong>index.php</strong> - Home page profile display updated</li>
                <li><strong>layout-header.php</strong> - Admin header profile display updated</li>
                <li><strong>admin-users.php</strong> - Admin users list updated</li>
                <li><strong>admin-orders.php</strong> - Admin orders list updated</li>
                <li><strong>admin-logs.php</strong> - Admin logs updated</li>
                <li><strong>admin-overview.php</strong> - Admin overview updated</li>
            </ul>
        </div>
        
        <div class="mt-6 text-center">
            <a href="index.php" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
                Back to Home
            </a>
        </div>
    </div>
</body>
</html> 