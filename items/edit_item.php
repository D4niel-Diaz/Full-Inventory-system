<?php
session_start();
require_once "../config/database.php";

// Check if Admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get item ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No item specified for editing";
    header('Location: ../dashboard/admin_requests.php');
    exit();
}

$item_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$item_id) {
    $_SESSION['error'] = "Invalid item ID";
    header('Location: ../dashboard/admin_requests.php');
    exit();
}

// Fetch the item with category information
$stmt = $conn->prepare("SELECT i.*, mc.name AS main_category 
                       FROM items i
                       JOIN main_categories mc ON i.main_category_id = mc.id
                       WHERE i.id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    $_SESSION['error'] = "Item not found";
    header('Location: ../dashboard/admin_requests.php');
    exit();
}

// Handle Form Submission 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $main_category_id = (int)$_POST['main_category_id'];
    $sub_category = trim($_POST['sub_category']);
    $ched_req = trim($_POST['ched_req']);
    $on_hand = trim($_POST['on_hand']);
    $remarks = trim($_POST['remarks'] ?? '');
    $unit = trim($_POST['unit'] ?? $item['unit'] ?? 'units');

    try {
        $updateStmt = $conn->prepare("UPDATE items SET 
                                    name = ?, 
                                    main_category_id = ?, 
                                    sub_category = ?, 
                                    quantity = ?, 
                                    available_quantity = ?, 
                                    unit = ?,
                                    remarks = ? 
                                    WHERE id = ?");
        $updateStmt->execute([
            $name, 
            $main_category_id, 
            $sub_category, 
            $ched_req, 
            $on_hand, 
            $unit,
            $remarks, 
            $item_id
        ]);

        $_SESSION['success'] = "Item updated successfully!";
        header('Location: ../dashboard/admin_requests.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating item: " . $e->getMessage();
        header("Location: edit_item.php?id=$item_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item | InventoryPro Admin</title>
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
                        <a href="../profile/view_profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="ri-user-line mr-2"></i>Profile</a>
                        <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="ri-logout-box-r-line mr-2"></i>Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="../dashboard/admin_requests.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 smooth-transition">
            <i class="ri-arrow-left-line mr-1"></i> Back to Inventory
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Edit Item</h1>
        <p class="mt-1 text-sm text-gray-500">Update the fields below to edit the item details</p>
    </div>

    <!-- Display Error Message -->
    <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <strong class="font-bold"><i class="ri-error-warning-line mr-1"></i>Error:</strong>
        <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <button onclick="this.parentElement.parentElement.remove()">
                <i class="ri-close-line"></i>
            </button>
        </span>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form method="POST" class="p-6 sm:p-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                    <input type="text" id="name" name="name" required
                           value="<?= htmlspecialchars($item['name']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none">
                </div>

                <!-- Main Category Dropdown -->
                <div>
                    <label for="main_category_id" class="block text-sm font-medium text-gray-700 mb-1">Main Category *</label>
                    <select id="main_category_id" name="main_category_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none">
                        <option value="">Select a main category</option>
                        <option value="1" <?= ($item['main_category_id'] ?? '') == 1 ? 'selected' : '' ?>>FRONT OFFICE LABORATORY</option>
                        <option value="2" <?= ($item['main_category_id'] ?? '') == 2 ? 'selected' : '' ?>>HOUSEKEEPING</option>
                        <option value="3" <?= ($item['main_category_id'] ?? '') == 3 ? 'selected' : '' ?>>FOOD AND BEVERAGE</option>
                        <option value="4" <?= ($item['main_category_id'] ?? '') == 4 ? 'selected' : '' ?>>FOOD PRODUCTION</option>
                    </select>
                </div>

                <!-- Sub Category Input -->
                <div>
                    <label for="sub_category" class="block text-sm font-medium text-gray-700 mb-1">Sub Category *</label>
                    <input type="text" id="sub_category" name="sub_category" required
                        value="<?= htmlspecialchars($item['sub_category'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none">
                </div>

                <div>
                    <label for="ched_req" class="block text-sm font-medium text-gray-700 mb-1">CHED Requirement *</label>
                    <input type="text" id="ched_req" name="ched_req" required
                           value="<?= htmlspecialchars($item['quantity']) ?>"
                           placeholder="e.g. '10 units', 'Not available'"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Enter the required quantity or status</p>
                </div>

                <div>
                    <label for="on_hand" class="block text-sm font-medium text-gray-700 mb-1">On Hand Quantity *</label>
                    <input type="text" id="on_hand" name="on_hand" required
                           value="<?= htmlspecialchars($item['available_quantity']) ?>"
                           placeholder="e.g. '5 units', 'In storage'"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Enter current available quantity or status</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input focus:outline-none"><?= htmlspecialchars($item['remarks']) ?></textarea>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-4">
                <a href="../dashboard/admin_requests.php"
                   class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-500 hover:bg-gray-600 smooth-transition">
                    <i class="ri-arrow-go-back-line mr-2"></i> Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 smooth-transition">
                    <i class="ri-save-line mr-2"></i> Save Changes
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
