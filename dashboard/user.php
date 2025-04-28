<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables
$items = [];
$categories = [];
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;
$category_filter = isset($_GET['category']) ? filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING) : '';

try {
    // Get items with pagination and category filter
    $query = "SELECT i.id, i.name, i.category, i.available_quantity,
                     COALESCE(SUM(CASE WHEN ubi.user_id = :user_id AND ubi.returned_at IS NULL THEN ubi.quantity ELSE 0 END), 0) as user_borrowed,
                     COALESCE(SUM(CASE WHEN ubi.user_id = :user_id AND ubi.returned_at IS NOT NULL THEN ubi.quantity ELSE 0 END), 0) as user_returned
              FROM items i
              LEFT JOIN user_borrowed_items ubi ON i.id = ubi.item_id
              WHERE (:category_filter = '' OR i.category = :category_filter)
              GROUP BY i.id
              ORDER BY i.id DESC 
              LIMIT :start, :limit";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':category_filter', $category_filter, PDO::PARAM_STR);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM items WHERE (:category_filter = '' OR category = :category_filter)");
    $countStmt->bindValue(':category_filter', $category_filter, PDO::PARAM_STR);
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    $pages = max(1, ceil($total / $limit));

    // Get all categories for filter
    $category_stmt = $conn->query("SELECT DISTINCT category FROM items ORDER BY category");
    $categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to load items: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management | User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .table-row:hover {
            background-color: #f9fafb;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .pagination-link {
            min-width: 2.5rem;
        }
        .action-btn {
            transition: all 0.2s ease;
        }
        .action-btn:hover {
            transform: translateY(-1px);
        }
        .action-form {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white p-4 shadow-md flex justify-between items-center">
        <div class="flex items-center">
            <i class="ri-box-2-line text-blue-500 text-2xl mr-2"></i>
            <h1 class="text-xl font-bold text-gray-800">Inventory Management System</h1>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <button id="dropdownButton" class="flex items-center text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md action-btn">
                    <i class="ri-user-line mr-2"></i> User Menu
                </button>
                <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                    <a href="../profile/view_profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="ri-user-settings-line mr-2"></i> Profile
                    </a>
                    <a href="../logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="ri-logout-box-r-line mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <div class="flex justify-between items-center">
                    <div>
                        <i class="ri-checkbox-circle-fill mr-2"></i>
                        <?= $_SESSION['success'] ?>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-green-700">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

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

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                <i class="ri-list-check-2 mr-2 text-blue-500"></i> Available Items
            </h2>
            <div class="flex items-center space-x-3">
                <a href="view_borrowed_items.php" class="flex items-center text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-md action-btn">
                    <i class="ri-history-line mr-2"></i> My Borrowed Items
                </a>
            </div>
        </div>

        <!-- Category Filter -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                <i class="ri-filter-line mr-2 text-blue-500"></i> Filter by Category
            </h3>
            <div class="flex flex-wrap gap-2">
                <a href="?category=" class="px-3 py-1 bg-blue-600 text-white rounded-full text-sm hover:bg-blue-700 transition-colors flex items-center action-btn">
                    <i class="ri-list-unordered mr-1"></i> All Categories
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="?category=<?= urlencode($category['category']) ?>" 
                       class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm hover:bg-blue-200 transition-colors flex items-center action-btn">
                        <i class="ri-price-tag-3-line mr-1"></i> <?= htmlspecialchars($category['category']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Your Borrowed</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Your Returned</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($items) > 0): ?>
                            <?php foreach ($items as $item): ?>
                                <tr class="table-row hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="ri-box-line text-gray-400 mr-2"></i>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($item['category']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        <span class="inline-block px-2 py-1 rounded bg-blue-50 text-blue-800">
                                            <?= htmlspecialchars($item['available_quantity']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        <span class="inline-block px-2 py-1 rounded <?= $item['user_borrowed'] > 0 ? 'bg-yellow-50 text-yellow-800' : 'bg-gray-100 text-gray-800' ?>">
                                            <?= htmlspecialchars($item['user_borrowed']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        <span class="inline-block px-2 py-1 rounded <?= $item['user_returned'] > 0 ? 'bg-green-50 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                            <?= htmlspecialchars($item['user_returned']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        <?php if ($item['available_quantity'] > 0): ?>
                                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                                Available
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-medium">
                                                Not Available
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex flex-col gap-2 items-center">
                                            <!-- Borrow Form -->
                                            <form action="../items/borrow_item.php" method="POST" class="action-form">
                                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                <input type="number" 
                                                       name="quantity" 
                                                       min="1" 
                                                       max="<?= $item['available_quantity'] ?>" 
                                                       <?= $item['available_quantity'] == 0 ? 'disabled' : '' ?>
                                                       required 
                                                       class="quantity-input border rounded px-2 py-1 text-sm">
                                                <button type="submit" 
                                                        class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded-md text-sm action-btn disabled:opacity-50"
                                                        <?= $item['available_quantity'] == 0 ? 'disabled' : '' ?>>
                                                    <i class="ri-download-line mr-1"></i> Borrow
                                                </button>
                                            </form>

                                            <!-- Return Form -->
                                            <form action="../items/return_item.php" method="POST" class="action-form">
                                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                <input type="number" 
                                                       name="quantity" 
                                                       min="1" 
                                                       max="<?= $item['user_borrowed'] ?>" 
                                                       <?= $item['user_borrowed'] == 0 ? 'disabled' : '' ?>
                                                       required 
                                                       class="quantity-input border rounded px-2 py-1 text-sm">
                                                <button type="submit" 
                                                        class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded-md text-sm action-btn disabled:opacity-50"
                                                        <?= $item['user_borrowed'] == 0 ? 'disabled' : '' ?>>
                                                    <i class="ri-upload-line mr-1"></i> Return
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center py-8">
                                        <i class="ri-inbox-line text-4xl text-gray-400 mb-2"></i>
                                        No items found in inventory.
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
            <div class="mt-6 flex justify-center">
                <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page-1 ?><?= $category_filter ? '&category='.urlencode($category_filter) : '' ?>" 
                           class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 flex items-center action-btn">
                            <i class="ri-arrow-left-s-line mr-1"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?page=<?= $i ?><?= $category_filter ? '&category='.urlencode($category_filter) : '' ?>" 
                           class="<?= $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> px-4 py-2 border text-sm font-medium pagination-link action-btn">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $pages): ?>
                        <a href="?page=<?= $page+1 ?><?= $category_filter ? '&category='.urlencode($category_filter) : '' ?>" 
                           class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 flex items-center action-btn">
                            Next <i class="ri-arrow-right-s-line ml-1"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Toggle dropdown
        document.getElementById('dropdownButton').addEventListener('click', function() {
            document.getElementById('dropdownMenu').classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdownMenu');
            const button = document.getElementById('dropdownButton');
            
            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Validate quantity inputs before form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const quantityInput = this.querySelector('input[type="number"]');
                const max = parseInt(quantityInput.max);
                const value = parseInt(quantityInput.value);
                
                if (value < 1) {
                    e.preventDefault();
                    alert('Quantity must be at least 1');
                    quantityInput.focus();
                    return false;
                }
                
                if (value > max) {
                    e.preventDefault();
                    alert(`Maximum quantity allowed is ${max}`);
                    quantityInput.focus();
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>