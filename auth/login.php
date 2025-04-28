<?php
session_start();
require_once "../config/database.php";

$error = "";

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];

            if ($user['user_type'] === 'admin') {
                header("Location: ../dashboard/admin_requests.php");
            } else {
                header("Location: ../dashboard/user.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Inventory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .auth-container {
            background: linear-gradient(135deg, rgba(249,250,251,0.95) 0%, rgba(243,244,246,0.95) 100%);
        }
        .auth-card {
            backdrop-filter: blur(8px);
            background: rgba(255,255,255,0.85);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        }
        .btn-primary {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[url('../assets/images/inventory-bg.jpg')] bg-cover bg-center bg-no-repeat">

<div class="auth-container max-w-md w-full rounded-xl overflow-hidden">
    <div class="auth-card p-8 sm:p-10 border border-white/20 rounded-xl">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-4 shadow-md">
                <i class="ri-box-2-line text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Inventory System</h1>
            <p class="text-gray-600 text-sm">Sign in to access your dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 p-3 bg-red-50 text-red-600 rounded-lg text-sm flex items-center">
                <i class="ri-error-warning-line mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="" method="POST" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-mail-line text-gray-400"></i>
                    </div>
                    <input id="email" name="email" type="email" required autofocus
                        class="input-field pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-sm transition duration-200">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-lock-2-line text-gray-400"></i>
                    </div>
                    <input id="password" name="password" type="password" required
                        class="input-field pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-sm transition duration-200">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                        Forgot password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="btn-primary w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="ri-login-box-line mr-2"></i> Sign In
                </button>
            </div>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            Don't have an account? 
            <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                Create one now
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="mt-4 text-center text-xs text-gray-500">
        &copy; <?= date('Y') ?> Inventory System. All rights reserved.
    </div>
</div>

<script>
    // Simple animation on load
    document.addEventListener('DOMContentLoaded', function() {
        const card = document.querySelector('.auth-card');
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.4s ease-out';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
</script>

</body>
</html>