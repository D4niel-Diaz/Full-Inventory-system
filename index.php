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
  <title>InventoryPro | Welcome</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
    }
    .card-hover {
      transition: all 0.3s ease;
    }
    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    .btn-hover {
      transition: all 0.2s ease;
    }
    .btn-hover:hover {
      transform: translateY(-2px);
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-4xl mx-auto">
  <div class="flex flex-col md:flex-row items-center bg-white rounded-xl overflow-hidden shadow-lg card-hover">
    <!-- Left Side -->
    <div class="w-full md:w-1/2 bg-gradient-to-br from-blue-500 to-blue-600 p-8 md:p-12 flex flex-col justify-center text-white">
      <div class="text-center md:text-left mb-6">
        <i class="ri-box-3-line text-5xl mb-4"></i>
        <h1 class="text-3xl md:text-4xl font-bold mb-2">Inventory System</h1>
        <p class="text-blue-100">Manage your items efficiently and smartly</p>
      </div>
      <div class="hidden md:block mt-8">
        <div class="flex items-center mb-4">
          <i class="ri-checkbox-circle-fill text-xl mr-3"></i>
          <span>Efficient item management</span>
        </div>
        <div class="flex items-center mb-4">
          <i class="ri-checkbox-circle-fill text-xl mr-3"></i>
          <span>Real-time updates</span>
        </div>
        <div class="flex items-center">
          <i class="ri-checkbox-circle-fill text-xl mr-3"></i>
          <span>Secure and reliable</span>
        </div>
      </div>
    </div>

    <!-- Right Side -->
    <div class="w-full md:w-1/2 p-8 md:p-12">
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome!</h2>
        <p class="text-gray-600">Login or Create your Account</p>
      </div>

      <div class="space-y-4">
        <a href="auth/login.php" class="block w-full btn-hover">
          <button class="w-full flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
            <i class="ri-login-box-line mr-2"></i> Login to Your Account
          </button>
        </a>

        <a href="auth/register.php" class="block w-full btn-hover">
          <button class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-blue-700 bg-white hover:bg-green-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
            <i class="ri-user-add-line mr-2"></i> Create New Account
          </button>
        </a>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-200 text-center">
        <p class="text-sm text-gray-600">
          Need help? <a href="#" class="text-blue-600 hover:text-blue-500">Contact support</a>
        </p>
      </div>
    </div>
  </div>

  <div class="mt-6 text-center text-sm text-gray-500">
    <p>© <?= date('Y') ?> Inventory System. All rights reserved.</p>
  </div>
</div>

</body>
</html>
