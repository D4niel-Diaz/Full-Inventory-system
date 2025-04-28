<?php
session_start();

// If already logged in, redirect to the correct dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header('Location: dashboard/admin_requests.php');
        exit();
    } else {
        header('Location: dashboard/user_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .auth-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.95) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-login {
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .btn-register {
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 bg-[url('assets/images/inventory-bg.jpg')] bg-cover bg-center">
    <div class="auth-card rounded-xl shadow-2xl p-8 w-full max-w-md relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-100 rounded-full opacity-20"></div>
        <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-green-100 rounded-full opacity-20"></div>
        
        <!-- Logo/Title -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                <i class="ri-box-2-line text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Inventory System</h1>
            <p class="text-gray-600">Manage your items efficiently</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col gap-4">
            <a href="auth/login.php" class="btn-login bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-lg font-medium flex items-center justify-center gap-2">
                <i class="ri-login-box-line"></i> Login to Your Account
            </a>
            <a href="auth/register.php" class="btn-register bg-green-500 hover:bg-green-600 text-white p-3 rounded-lg font-medium flex items-center justify-center gap-2">
                <i class="ri-user-add-line"></i> Create New Account
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-4 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-500">
                &copy; <?= date('Y') ?> Inventory System. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Animated background elements -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate decorative circles on load
            const circles = document.querySelectorAll('body > div > div:first-child, body > div > div:nth-child(2)');
            circles.forEach((circle, index) => {
                circle.style.transition = 'all 1s ease';
                circle.style.transform = `scale(${index === 0 ? 1.2 : 1.5})`;
                
                setTimeout(() => {
                    circle.style.transform = 'scale(1)';
                }, 100);
            });
        });
    </script>
</body>
</html>