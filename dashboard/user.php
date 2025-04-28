<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch available items
$stmt = $conn->prepare("SELECT id, name, category, available_quantity FROM items");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each item, get how many this user has borrowed (not returned)
foreach ($items as &$item) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as user_borrowed 
                           FROM user_borrowed_items 
                           WHERE user_id = ? AND item_id = ? AND returned_at IS NULL");
    $stmt->execute([$user_id, $item['id']]);
    $borrowed = $stmt->fetch(PDO::FETCH_ASSOC);
    $item['user_borrowed'] = $borrowed['user_borrowed'] ?? 0;
}
unset($item); // Break the reference

// Extract categories for filter
$categories = array_unique(array_column($items, 'category'));
sort($categories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Navigation -->
<nav class="bg-white p-4 shadow flex justify-between items-center">
    <h1 class="text-xl font-bold">User Dashboard</h1>
    <div class="relative">
        <button id="dropdownButton" class="bg-blue-500 text-white px-4 py-2 rounded">
            Menu
        </button>
        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white shadow rounded">
            <a href="../profile/view_profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="../logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
        </div>
    </div>
</nav>

<div class="p-8">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <h2 class="text-2xl font-bold mb-4">Available Items</h2>

    <!-- Search and Filter -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <input type="text" id="searchInput" placeholder="Search items..." 
            class="w-full md:w-1/2 p-2 border rounded" onkeyup="filterTable()">
        
        <select id="categoryFilter" onchange="filterTable()" class="w-full md:w-1/4 p-2 border rounded">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Available Items Table -->
    <?php if (empty($items)): ?>
        <p class="text-gray-500">No items currently available</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table id="itemsTable" class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Item Name</th>
                        <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Available Quantity</th>
                        <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Your Borrowed</th>
                        <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Remarks</th>
                        <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr class="text-center border-t">
                            <td class="py-2 item-name"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="py-2 item-category"><?= htmlspecialchars($item['category']) ?></td>
                            <td class="py-2"><?= htmlspecialchars($item['available_quantity']) ?></td>
                            <td class="py-2"><?= htmlspecialchars($item['user_borrowed']) ?></td>
                            <td class="py-2">
                                <?php if ($item['available_quantity'] > 0): ?>
                                    <span class="text-green-500">Available</span>
                                <?php else: ?>
                                    <span class="text-red-500">Not Available</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2 space-y-2">
                                <!-- Borrow Form -->
                                <form action="../items/borrow_item.php" method="POST" class="flex justify-center items-center gap-2">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <input type="number" 
                                           name="quantity" 
                                           min="1" 
                                           max="<?= $item['available_quantity'] ?>" 
                                           <?= $item['available_quantity'] == 0 ? 'disabled' : '' ?>
                                           required 
                                           class="border rounded px-2 py-1 w-20">
                                    <button type="submit" 
                                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 disabled:opacity-50"
                                            <?= $item['available_quantity'] == 0 ? 'disabled' : '' ?>>
                                        Borrow
                                    </button>
                                </form>

                                <!-- Return Form -->
                                <form action="../items/return_item.php" method="POST" class="flex justify-center items-center gap-2">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <input type="number" 
                                           name="quantity" 
                                           min="1" 
                                           max="<?= $item['user_borrowed'] ?>" 
                                           <?= $item['user_borrowed'] == 0 ? 'disabled' : '' ?>
                                           required 
                                           class="border rounded px-2 py-1 w-20">
                                    <button type="submit" 
                                            class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 disabled:opacity-50"
                                            <?= $item['user_borrowed'] == 0 ? 'disabled' : '' ?>>
                                        Return
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
// Toggle dropdown
document.getElementById('dropdownButton').addEventListener('click', function() {
    document.getElementById('dropdownMenu').classList.toggle('hidden');
});

// Filter table function
function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#itemsTable tbody tr');

    rows.forEach(row => {
        const itemName = row.querySelector('.item-name').textContent.toLowerCase();
        const itemCategory = row.querySelector('.item-category').textContent.toLowerCase();

        const matchesSearch = itemName.includes(searchInput);
        const matchesCategory = categoryFilter === "" || itemCategory === categoryFilter;

        if (matchesSearch && matchesCategory) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

</body>
</html>
