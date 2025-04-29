<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Determine where to go back
if ($user['role'] == 'admin') {
    $back_link = "../dashboard/admin_requests.php"; // change if your admin page name is different
} else {
    $back_link = "../dashboard/user.php";
}

// At the top of your profile page
$back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : (
    ($_SESSION['user_type'] === 'admin') 
        ? 'admin_dashboard.php' 
        : 'user_dashboard.php'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile | InventoryPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        .auth-card {
            transition: all 0.3s ease;
        }
        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .btn-hover {
            transition: all 0.2s ease;
        }
        .btn-hover:hover {
            transform: translateY(-2px);
        }
        .input-focus:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <div class="bg-white rounded-xl shadow-md overflow-hidden auth-card">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 p-6 text-center">
            <div class="flex justify-center mb-4">
                <div class="bg-white/20 p-3 rounded-full">
                    <i class="ri-user-line text-2xl text-white"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-white">Your Profile</h1>
            <p class="text-blue-100 mt-1">Manage your information</p>
        </div>

        <!-- Back Button -->
        <div class="p-4">
            <a href="<?php echo htmlspecialchars($back_url); ?>" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                <i class="ri-arrow-left-line mr-1"></i> Back
            </a>
        </div>


        <!-- Form Section -->
        <div class="px-6 pb-6 sm:px-8">
            <form action="update_profile.php" method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input id="first_name" name="first_name" type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="John">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input id="last_name" name="last_name" type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="Doe">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="you@example.com">
                </div>

                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <input id="department" name="department" type="text" value="<?php echo htmlspecialchars($user['department']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="e.g. Sales, IT, HR">
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full btn-hover flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="ri-save-line mr-2"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-4 text-center text-xs text-gray-500">
        <p>© <?= date('Y') ?> InventoryPro. All rights reserved.</p>
    </div>
</div>

</body>
</html>