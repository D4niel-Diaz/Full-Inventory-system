<?php
session_start();
require_once "../config/database.php";

// Check Admin Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$transactions = [];
$page = 1;
$limit = 10;
$total = 0;
$pages = 1;

// Get Pagination Parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

try {
    // Get Transaction Data
    $query = "SELECT t.transaction_id, i.name AS item_name, CONCAT(u.first_name, ' ', u.last_name) AS user_name, 
                 t.borrowed_quantity, t.returned_quantity, t.transaction_date 
          FROM transactions t
          JOIN items i ON t.item_id = i.id
          JOIN users u ON t.user_id = u.id
          ORDER BY t.transaction_date DESC 
          LIMIT ?, ?";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $start, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();

    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count for Pagination
    $countQuery = "SELECT COUNT(*) FROM transactions";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    $pages = ceil($total / $limit);
    if ($pages < 1) $pages = 1;

} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to load transactions: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
    $transactions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions | InventoryPro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .status-borrowed {
            background-color: #f0fdf4;
            color: #166534;
        }
        .status-returned {
            background-color: #eff6ff;
            color: #1e40af;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .pagination-link {
            transition: all 0.2s ease;
        }
        .pagination-link:hover:not(.active) {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Navigation -->
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
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="mb-4 md:mb-0">
            <h1 class="text-2xl font-bold text-gray-900">
            <i class="ri-list-check-2 mr-2 text-blue-500"></i>Transaction History</h1>
            <p class="mt-1 text-sm text-gray-500">View all borrowing and returning transactions</p>
        </div>
        <a href="admin_requests.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="ri-arrow-left-line mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Returned</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md flex items-center justify-center">
                                            <i class="ri-box-2-line text-gray-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($transaction['item_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($transaction['user_name']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    <?= htmlspecialchars($transaction['borrowed_quantity']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    <?= htmlspecialchars($transaction['returned_quantity']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php
                                    $status = 'Pending';
                                    $statusClass = 'status-pending';
                                    if ($transaction['returned_quantity'] == $transaction['borrowed_quantity']) {
                                        $status = 'Returned';
                                        $statusClass = 'status-returned';
                                    } elseif ($transaction['returned_quantity'] > 0) {
                                        $status = 'Partially Returned';
                                        $statusClass = 'status-pending';
                                    } else {
                                        $status = 'Borrowed';
                                        $statusClass = 'status-borrowed';
                                    }
                                    ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    <?= date('M j, Y h:i A', strtotime($transaction['transaction_date'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <i class="ri-inbox-line text-4xl text-gray-400 mb-2"></i>
                                    <p>No transactions found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pages > 1): ?>
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium"><?= $start + 1 ?></span> to 
                            <span class="font-medium"><?= min($start + $limit, $total) ?></span> of 
                            <span class="font-medium"><?= $total ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <i class="ri-arrow-left-line"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pages; $i++): ?>
                                <a href="?page=<?= $i ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium <?= $i === $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $pages): ?>
                                <a href="?page=<?= $page + 1 ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
