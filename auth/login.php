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
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
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
    <title>Login | InventoryPro</title>
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
                    <i class="ri-login-box-line text-2xl text-white"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-white">Inventory System</h1>
            <p class="text-blue-100 mt-1">Sign in to your account</p>
        </div>

        <!-- Form Section -->
        <div class="p-6 sm:p-8">
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700">
                    <div class="flex items-center">
                        <i class="ri-error-warning-fill mr-2"></i>
                        <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-mail-line text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" required autofocus
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-200 text-sm"
                            placeholder="you@example.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-lock-line text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-200 text-sm"
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="togglePassword('password')">
                                <i class="ri-eye-line" id="password-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-gray-900">Remember me</label>
                    </div>

                    <div>
                        <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full btn-hover flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="ri-login-circle-line mr-2"></i> Sign In
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Don't have an account? 
                    <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Register here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center text-xs text-gray-500">
        <p>© <?= date('Y') ?> InventoryPro. All rights reserved.</p>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const eyeIcon = document.getElementById(`${fieldId}-eye`);
        
        if (field.type === 'password') {
            field.type = 'text';
            eyeIcon.classList.replace('ri-eye-line', 'ri-eye-off-line');
        } else {
            field.type = 'password';
            eyeIcon.classList.replace('ri-eye-off-line', 'ri-eye-line');
        }
    }
</script>

</body>
</html>
