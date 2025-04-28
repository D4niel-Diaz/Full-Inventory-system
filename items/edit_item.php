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

// Fetch the item
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
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
    $category = trim($_POST['category']);
    $ched_req = trim($_POST['ched_req']);
    $on_hand = trim($_POST['on_hand']);
    $remarks = trim($_POST['remarks'] ?? '');

    try {
        $updateStmt = $conn->prepare("UPDATE items SET 
                                    name = ?, 
                                    category = ?, 
                                    quantity = ?, 
                                    available_quantity = ?, 
                                    remarks = ? 
                                    WHERE id = ?");
        $updateStmt->execute([$name, $category, $ched_req, $on_hand, $remarks, $item_id]);

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
    <title>Edit Item Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white p-4 shadow-md flex justify-between items-center">
        <div class="flex items-center">
            <i class="ri-box-2-line text-blue-500 text-2xl mr-2"></i>
            <h1 class="text-xl font-bold text-gray-800">Edit Inventory Item</h1>
        </div>
        <div>
            <a href="../dashboard/admin_requests.php" class="flex items-center text-gray-700 hover:text-blue-600">
                <i class="ri-arrow-go-back-line mr-1"></i> Back to Inventory
            </a>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <div class="flex justify-between items-center">
                    <div>
                        <i class="ri-error-warning-fill mr-2"></i>
                        <?= $_SESSION['error'] ?>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-red-700">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-6 max-w-3xl mx-auto">
            <h2 class="text-2xl font-bold mb-6 flex items-center text-gray-800">
                <i class="ri-edit-2-line mr-2 text-blue-500"></i> Edit Item: <?= htmlspecialchars($item['name']) ?>
            </h2>

            <form method="POST" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Item Name</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?= htmlspecialchars($item['name']) ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Category</label>
                        <input type="text" name="category" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?= htmlspecialchars($item['category']) ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">CHED Requirement</label>
                        <input type="text" name="ched_req" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?= htmlspecialchars($item['quantity']) ?>"
                               placeholder="e.g. '10 units', 'Not available'">
                        <p class="text-xs text-gray-500 mt-1">Enter the required quantity or status</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">On Hand Quantity</label>
                        <input type="text" name="on_hand" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?= htmlspecialchars($item['available_quantity']) ?>"
                               placeholder="e.g. '5 units', 'In storage'">
                        <p class="text-xs text-gray-500 mt-1">Enter current available quantity or status</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                    <textarea name="remarks" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Additional notes about the item"><?= htmlspecialchars($item['remarks']) ?></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="../dashboard/admin_requests.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 flex items-center">
                        <i class="ri-close-line mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center">
                        <i class="ri-save-line mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>