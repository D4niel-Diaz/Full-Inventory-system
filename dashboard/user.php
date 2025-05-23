<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$user_id = $_SESSION['user_id'];

// Initialize variables
$items = [];
$categories = [];
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;
$category_filter = isset($_GET['category']) ? filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING) : '';
$main_categories = ['FRONT OFFICE LABORATORY', 'HOUSEKEEPING', 'FOOD AND BEVERAGE', 'FOOD PRODUCTION'];

// Initialize overdue items flag
$has_overdue_items = false;

try {
    // Check for overdue items
    $overdue_check = $conn->prepare("
        SELECT COUNT(*) as overdue_count 
        FROM user_borrowed_items 
        WHERE user_id = :user_id 
        AND returned_at IS NULL 
        AND due_date < NOW()
    ");
    $overdue_check->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $overdue_check->execute();
    $overdue_result = $overdue_check->fetch(PDO::FETCH_ASSOC);
    $has_overdue_items = ($overdue_result['overdue_count'] > 0);
    
    if ($has_overdue_items) {
        $_SESSION['error'] = "You have overdue items. Please return them before borrowing new items.";
    }

    // Get items with pagination and category filter
    $query = "SELECT i.id, i.name, mc.name as main_category, i.sub_category, i.available_quantity,
                 COALESCE(SUM(CASE WHEN ubi.user_id = :user_id AND ubi.returned_at IS NULL THEN ubi.quantity ELSE 0 END), 0) as user_borrowed,
                 COALESCE(SUM(CASE WHEN ubi.user_id = :user_id AND ubi.returned_at IS NOT NULL THEN ubi.quantity ELSE 0 END), 0) as user_returned,
                 MAX(CASE WHEN ubi.user_id = :user_id AND ubi.returned_at IS NULL THEN ubi.due_date END) as nearest_due_date
          FROM items i
          LEFT JOIN user_borrowed_items ubi ON i.id = ubi.item_id
          JOIN main_categories mc ON i.main_category_id = mc.id
          WHERE (:main_category_filter = '' OR mc.name = :main_category_filter)
          AND (:sub_category_filter = '' OR i.sub_category = :sub_category_filter)
          AND (:search_query = '' 
               OR i.name LIKE CONCAT('%', :search_query, '%')
               OR mc.name LIKE CONCAT('%', :search_query, '%')
               OR i.sub_category LIKE CONCAT('%', :search_query, '%'))
          GROUP BY i.id
          ORDER BY i.id DESC 
          LIMIT :start, :limit";

$stmt = $conn->prepare($query);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindValue(':main_category_filter', $_GET['main_category'] ?? '', PDO::PARAM_STR);
$stmt->bindValue(':sub_category_filter', $_GET['category'] ?? '', PDO::PARAM_STR);
$stmt->bindValue(':search_query', $search_query, PDO::PARAM_STR);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countStmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM items i
    JOIN main_categories mc ON i.main_category_id = mc.id
    WHERE (:main_category_filter = '' OR mc.name = :main_category_filter)
    AND (:sub_category_filter = '' OR i.sub_category = :sub_category_filter)
    AND (:search_query = '' 
         OR i.name LIKE CONCAT('%', :search_query, '%')
         OR mc.name LIKE CONCAT('%', :search_query, '%')
         OR i.sub_category LIKE CONCAT('%', :search_query, '%'))
");
$countStmt->bindValue(':main_category_filter', $_GET['main_category'] ?? '', PDO::PARAM_STR);
$countStmt->bindValue(':sub_category_filter', $_GET['category'] ?? '', PDO::PARAM_STR);
$countStmt->bindValue(':search_query', $search_query, PDO::PARAM_STR);
$countStmt->execute();
    $total = $countStmt->fetchColumn();
    $pages = max(1, ceil($total / $limit));

    // Get all categories for filter
    $sub_category_stmt = $conn->query("
    SELECT DISTINCT i.sub_category, mc.name as main_category 
    FROM items i
    JOIN main_categories mc ON i.main_category_id = mc.id
    ORDER BY mc.name, i.sub_category
    ");
    $sub_categories = $sub_category_stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped_sub_categories = [];
    foreach ($sub_categories as $sub) {
    $grouped_sub_categories[$sub['main_category']][] = $sub['sub_category'];
    }


} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
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
        .smooth-transition {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-semibold text-gray-900">Inventory Management System</span>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="ml-3 relative">
                        <!-- User Dropdown Button -->
                        <div>
                            <button id="dropdownButton" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <span class="sr-only">Open user menu</span>
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="ri-user-line"></i>
                                </div>
                                <span class="ml-2 text-gray-700">
                                    <?php 
                                    // Display first name if available, otherwise "User"
                                    echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : 'User';
                                    ?>
                                </span>
                                <i class="ri-arrow-down-s-line ml-1 text-gray-500"></i>
                            </button>
                        </div>
                        <!-- Dropdown Menu -->
                        <div id="user-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                            <div class="px-4 py-2 border-b">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php 
                                    // Display full name if available
                                    if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                                        echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                                    } else {
                                        echo 'User Account';
                                    }
                                    ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?= isset($_SESSION['user_type']) ? htmlspecialchars(ucfirst($_SESSION['user_type'])) : 'User' ?>
                                </p>
                            </div>
                            <a href="../profile/view_profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="ri-user-line mr-2"></i> Profile
                            </a>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="ri-logout-box-r-line mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

          <!-- Success/Error Messages -->
    <div class="container mx-auto p-6">
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

        <!-- Add this after your success/error messages section -->
        <?php
            try {
                // Get overdue items with details
                $overdue_items_stmt = $conn->prepare("
                    SELECT i.name, ubi.due_date, DATEDIFF(NOW(), ubi.due_date) as days_overdue
                    FROM user_borrowed_items ubi
                    JOIN items i ON ubi.item_id = i.id
                    WHERE ubi.user_id = :user_id 
                    AND ubi.returned_at IS NULL 
                    AND ubi.due_date < NOW()
                    ORDER BY ubi.due_date ASC
                ");
                $overdue_items_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $overdue_items_stmt->execute();
                $overdue_items = $overdue_items_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($overdue_items) > 0): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded">
                        <div class="flex justify-between items-center">
                            <div>
                                <i class="ri-alarm-warning-fill mr-2"></i>
                                <strong>You have overdue items!</strong> Please return these items to borrow new ones:
                            </div>
                        </div>
                        <ul class="mt-2 list-disc list-inside">
                            <?php foreach ($overdue_items as $item): ?>
                                <li>
                                    <?= htmlspecialchars($item['name']) ?> - 
                                    Was due <?= date('M j, Y', strtotime($item['due_date'])) ?> 
                                    (<?= $item['days_overdue'] ?> day<?= $item['days_overdue'] != 1 ? 's' : '' ?> overdue)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif;
            } catch (PDOException $e) {
                error_log("Failed to fetch overdue items: " . $e->getMessage());
            }
            ?>

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
        </div>

       <!-- Search and Category Filter Section -->
<div class="mb-6 bg-white p-4 rounded-lg shadow">
    <!-- Search Bar -->
    <div class="mb-4">
        <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
            <i class="ri-search-line mr-2 text-blue-500"></i> Search Inventory
        </h3>
        <form method="GET" action="" class="flex">
            <input type="text" 
                   name="search" 
                   value="<?= htmlspecialchars($search_query) ?>" 
                   placeholder="Search items or categories..." 
                   class="flex-1 border rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition-colors">
                Search
            </button>
            <?php if (!empty($search_query)): ?>
                <a href="?" class="ml-2 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors flex items-center">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Main Category Filter -->
    <div class="mb-4">
        <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
            <i class="ri-filter-line mr-2 text-blue-500"></i> Main Categories
        </h3>
        <div class="flex flex-wrap gap-2">
            <a href="?search=<?= urlencode($search_query) ?>"
               class="px-3 py-1 <?= empty($_GET['main_category']) ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800' ?> rounded-full text-sm hover:bg-blue-200 transition-colors flex items-center action-btn">
                <i class="ri-list-unordered mr-1"></i> All Categories
            </a>
            <?php foreach ($main_categories as $main_category): ?>
                <a href="?search=<?= urlencode($search_query) ?>&main_category=<?= urlencode($main_category) ?>"
                   class="px-3 py-1 <?= (isset($_GET['main_category']) && $_GET['main_category'] === $main_category) ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800' ?> rounded-full text-sm hover:bg-blue-200 transition-colors flex items-center action-btn">
                    <i class="ri-price-tag-3-line mr-1"></i> <?= htmlspecialchars($main_category) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sub-Category Filter (only shown when a main category is selected) -->
    <?php if (isset($_GET['main_category']) && !empty($grouped_sub_categories[$_GET['main_category']])): ?>
    <div>
        <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
            <i class="ri-filter-line mr-2 text-blue-500"></i> Sub-Categories for <?= htmlspecialchars($_GET['main_category']) ?>
        </h3>
        <div class="flex flex-wrap gap-2">
            <a href="?search=<?= urlencode($search_query) ?>&main_category=<?= urlencode($_GET['main_category']) ?>"
               class="px-3 py-1 <?= empty($_GET['category']) ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800' ?> rounded-full text-sm hover:bg-blue-200 transition-colors flex items-center action-btn">
                <i class="ri-list-unordered mr-1"></i> All Sub-Categories
            </a>
            <?php foreach ($grouped_sub_categories[$_GET['main_category']] as $sub_category): ?>
                <a href="?search=<?= urlencode($search_query) ?>&main_category=<?= urlencode($_GET['main_category']) ?>&category=<?= urlencode($sub_category) ?>"
                   class="px-3 py-1 <?= (isset($_GET['category']) && $_GET['category'] === $sub_category) ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800' ?> rounded-full text-sm hover:bg-blue-200 transition-colors flex items-center action-btn">
                    <i class="ri-price-tag-3-line mr-1"></i> <?= htmlspecialchars($sub_category) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub-Category</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Your Borrowed</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
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
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($item['sub_category']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    <span class="inline-block px-2 py-1 rounded bg-blue-50 text-blue-800">
                                        <?= htmlspecialchars($item['available_quantity']) ?>
                                    </span>
                                </td>
                                
                                <!-- Your Borrowed (Quantity Only) -->
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    <span class="inline-block px-2 py-1 rounded <?= $item['user_borrowed'] > 0 ? 'bg-yellow-50 text-yellow-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= htmlspecialchars($item['user_borrowed']) ?>
                                    </span>
                                </td>
                                
                                <!-- Due Date (Standalone Column) -->
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    <?php if ($item['user_borrowed'] > 0 && !empty($item['nearest_due_date'])): ?>
                                        <?php 
                                        $due_date = new DateTime($item['nearest_due_date']);
                                        $now = new DateTime();
                                        $is_overdue = $due_date < $now;
                                        ?>
                                        <span class="inline-block px-2 py-1 rounded <?= $is_overdue ? 'bg-red-100 text-red-800' : 'bg-yellow-50 text-yellow-800' ?>">
                                            <?= $due_date->format('M j, Y') ?>
                                            <?php if ($is_overdue): ?>
                                                <i class="ri-alarm-warning-fill ml-1" title="Overdue"></i>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-block px-2 py-1 rounded bg-gray-100 text-gray-800">
                                            N/A
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Your Returned -->
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    <span class="inline-block px-2 py-1 rounded <?= $item['user_returned'] > 0 ? 'bg-green-50 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= htmlspecialchars($item['user_returned']) ?>
                                    </span>
                                </td>
                                
                                <!-- Status -->
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
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-3 items-center">
                                        <!-- Borrow Form -->
                                        <form action="../items/borrow_item.php" method="POST" class="w-full max-w-xs">
                                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                            <div class="flex flex-col sm:flex-row gap-2 items-center">
                                                <div class="flex-1 flex gap-2 w-full">
                                                    <input type="number" 
                                                        name="quantity" 
                                                        min="1" 
                                                        max="<?= $item['available_quantity'] ?>" 
                                                        <?= $item['available_quantity'] == 0 ? 'disabled' : '' ?>
                                                        required 
                                                        placeholder="Qty"
                                                        class="w-16 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                                        
                                                    <input type="date" 
                                                        name="due_date" 
                                                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                                        required
                                                        oninvalid="this.setCustomValidity('Please select a future return date')"
                                                        oninput="this.setCustomValidity('')"
                                                        class="flex-1 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                
                                                <button type="submit" 
                                                        class="w-full sm:w-auto px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                        <?= $item['available_quantity'] == 0 ? 'disabled' : '' ?>>
                                                    <i class="ri-download-line mr-1"></i> Borrow
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Return Form -->
                                        <form action="../items/return_item.php" method="POST" class="w-full max-w-xs">
                                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                            <div class="flex gap-2 w-full">
                                                <input type="number" 
                                                    name="quantity" 
                                                    min="1" 
                                                    max="<?= $item['user_borrowed'] ?>" 
                                                    <?= $item['user_borrowed'] == 0 ? 'disabled' : '' ?>
                                                    required 
                                                    placeholder="Qty"
                                                    class="flex-1 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500">
                                                    
                                                <button type="submit" 
                                                        class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                        <?= $item['user_borrowed'] == 0 ? 'disabled' : '' ?>>
                                                    <i class="ri-upload-line mr-1"></i> Return
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
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
            var menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('hidden');
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
        document.getElementById('dropdownButton').addEventListener('click', function() {
            document.getElementById('user-dropdown').classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('user-dropdown');
            const button = document.getElementById('dropdownButton');
            if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>