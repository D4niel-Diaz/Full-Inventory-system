<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $ched_req = trim($_POST['ched_req']);
    $on_hand = trim($_POST['on_hand']);
    $remarks = trim($_POST['remarks'] ?? '');

    try {
        $stmt = $conn->prepare("INSERT INTO items (name, category, quantity, available_quantity, unit, remarks) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $ched_req, $on_hand, 'unit', $remarks]);
        
        $_SESSION['success'] = "Item added successfully!";
        header('Location: ../dashboard/admin_requests.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding item: " . $e->getMessage();
        header('Location: add_item.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Item | InventoryPro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .smooth-transition {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Navigation -->
<nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <i class="ri-dashboard-line text-blue-600 text-2xl mr-2"></i>
                    <span class="text-xl font-semibold text-gray-900">Inventory Management System</span>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <div class="ml-3 relative">
                    <div>
                        <button id="user-menu" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="sr-only">Open user menu</span>
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                <i class="ri-admin-line"></i>
                            </div>
                            <span class="ml-2 text-gray-700">Admin</span>
                            <i class="ri-arrow-down-s-line ml-1 text-gray-500"></i>
                        </button>
                    </div>
                    <div id="user-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <a href="../profile/view_profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="ri-user-line mr-2"></i>Profile
                        </a>
                        <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="ri-logout-box-r-line mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Back Button -->
    <div class="mb-6">
        <a href="../dashboard/admin_requests.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 smooth-transition">
            <i class="ri-arrow-left-line mr-1"></i> Back to Inventory
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Add New Item</h1>
        <p class="mt-1 text-sm text-gray-500">Fill in the details below to add a new item to the inventory</p>
    </div>

    <!-- Error Message -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-error-warning-fill text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-500">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form method="POST" class="p-6 sm:p-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                <!-- Item Name -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-blue-200"
                           placeholder="Enter item name">
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <input type="text" id="category" name="category" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-blue-200"
                           placeholder="Enter category">
                </div>

                <!-- CHED Requirement -->
                <div>
                    <label for="ched_req" class="block text-sm font-medium text-gray-700 mb-1">CHED Requirement *</label>
                    <input type="text" id="ched_req" name="ched_req" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-blue-200"
                           placeholder="e.g. '10 units', 'Not available'">
                    <p class="text-xs text-gray-500 mt-1">Enter the required quantity or status</p>
                </div>

                <!-- On Hand Quantity -->
                <div>
                    <label for="on_hand" class="block text-sm font-medium text-gray-700 mb-1">On Hand Quantity *</label>
                    <input type="text" id="on_hand" name="on_hand" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-blue-200"
                           placeholder="e.g. '5 units', 'In storage'">
                    <p class="text-xs text-gray-500 mt-1">Enter current available quantity or status</p>
                </div>

                <!-- Remarks -->
                <div class="sm:col-span-2">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-blue-200"
                              placeholder="Additional notes about the item"></textarea>
                </div>
            </div>

            <!-- Submit and Reset Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="reset"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 flex items-center smooth-transition">
                    <i class="ri-eraser-line mr-1"></i> Clear
                </button>
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 smooth-transition">
                    <i class="ri-save-line mr-2"></i> Save Item
                </button>
            </div>

        </form>
    </div>
</div>

<script>
// Toggle user dropdown
document.getElementById('user-menu').addEventListener('click', function() {
    document.getElementById('user-dropdown').classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!document.getElementById('user-menu').contains(event.target) && 
        !document.getElementById('user-dropdown').contains(event.target)) {
        document.getElementById('user-dropdown').classList.add('hidden');
    }
});
</script>

</body>
</html>
