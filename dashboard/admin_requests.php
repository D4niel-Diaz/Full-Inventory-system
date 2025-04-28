<?php
// [Previous PHP code remains exactly the same until the HTML section]
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --success: #10b981;
            --warning: #f59e0b;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .card {
            @apply bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden;
        }
        
        .badge {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }
        
        .badge-primary {
            @apply bg-blue-100 text-blue-800;
        }
        
        .badge-success {
            @apply bg-green-100 text-green-800;
        }
        
        .badge-neutral {
            @apply bg-gray-100 text-gray-800;
        }
        
        .btn {
            @apply inline-flex items-center px-4 py-2 rounded-lg font-medium transition-all duration-200;
        }
        
        .btn-primary {
            @apply bg-blue-600 text-white hover:bg-blue-700;
        }
        
        .btn-danger {
            @apply bg-red-600 text-white hover:bg-red-700;
        }
        
        .btn-secondary {
            @apply bg-gray-200 text-gray-700 hover:bg-gray-300;
        }
        
        .btn-sm {
            @apply px-3 py-1 text-sm;
        }
        
        .table-row-hover:hover {
            @apply bg-gray-50;
        }
        
        .pagination-link {
            @apply min-w-[2.5rem] flex items-center justify-center;
        }
        
        .action-btn {
            @apply transition-all duration-200 hover:scale-110;
        }
        
        .animate-bounce {
            animation: bounce 1s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(-3px); }
            50% { transform: translateY(3px); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white p-4 shadow-sm flex justify-between items-center border-b border-gray-100">
        <div class="flex items-center space-x-2">
            <i class="ri-box-2-line text-blue-500 text-2xl"></i>
            <h1 class="text-xl font-bold text-gray-800">Inventory Management</h1>
        </div>
        <div class="flex items-center space-x-3">
            <a href="../items/add_item.php" class="btn btn-primary">
                <i class="ri-add-line mr-2"></i> Add Item
            </a>
            <a href="../logout.php" class="btn btn-danger">
                <i class="ri-logout-box-r-line mr-2"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container mx-auto p-4 md:p-6">
        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="card bg-green-50 border-l-4 border-green-500 mb-6 animate-bounce">
                <div class="flex justify-between items-center p-4">
                    <div class="flex items-center">
                        <i class="ri-checkbox-circle-fill text-green-500 text-xl mr-3"></i>
                        <span class="text-green-700"><?= $_SESSION['success'] ?></span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-green-700 hover:text-green-900">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="card bg-red-50 border-l-4 border-red-500 mb-6 animate-bounce">
                <div class="flex justify-between items-center p-4">
                    <div class="flex items-center">
                        <i class="ri-error-warning-fill text-red-500 text-xl mr-3"></i>
                        <span class="text-red-700"><?= $_SESSION['error'] ?></span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 hover:text-red-900">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="ri-list-check-2 mr-2 text-blue-500"></i> Inventory Summary
                </h2>
                <p class="text-gray-500 text-sm mt-1">Manage your inventory items and quantities</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="view_transactions.php" class="btn btn-primary">
                    <i class="ri-history-line mr-2"></i> View Transactions
                </a>
            </div>
        </div>

        <!-- Category Filter -->
        <div class="card mb-6">
            <div class="p-4">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="ri-filter-line mr-2 text-blue-500"></i> Filter by Category
                </h3>
                <div class="flex flex-wrap gap-2">
                    <a href="?category=" class="badge badge-primary flex items-center">
                        <i class="ri-list-unordered mr-1"></i> All Categories
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="?category=<?= urlencode($category['category']) ?>" 
                           class="badge badge-neutral flex items-center hover:bg-blue-100 hover:text-blue-800 transition-colors">
                            <i class="ri-price-tag-3-line mr-1"></i> <?= htmlspecialchars($category['category']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Form -->
        <form id="bulkActionForm" method="post" action="admin_requests.php">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <button type="button" id="selectAllBtn" class="btn btn-secondary btn-sm">
                    <i class="ri-checkbox-line mr-1"></i> Select All
                </button>
                <button type="button" id="deselectAllBtn" class="btn btn-secondary btn-sm">
                    <i class="ri-checkbox-blank-line mr-1"></i> Deselect All
                </button>
                <button type="submit" name="bulk_delete" class="btn btn-danger btn-sm" onclick="return confirmBulkDelete()">
                    <i class="ri-delete-bin-line mr-1"></i> Delete Selected
                </button>
            </div>

            <!-- Inventory Table -->
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="w-12 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAllCheckbox" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">CHED Req.</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">On Hand</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($items) > 0): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr class="table-row-hover">
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" class="item-checkbox rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                                    <i class="ri-box-line text-lg"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['item']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($item['category']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="badge <?= is_numeric($item['ched_req']) ? 'badge-primary' : 'badge-neutral' ?>">
                                                <?= htmlspecialchars($item['ched_req']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="badge <?= is_numeric($item['on_hand']) ? 'badge-success' : 'badge-neutral' ?>">
                                                <?= htmlspecialchars($item['on_hand']) ?>
                                                <?php if (is_numeric($item['on_hand'])): ?>
                                                    <span class="text-gray-400 ml-1 text-xs"><?= htmlspecialchars($item['unit']) ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($item['remarks']) ?>">
                                                <?= !empty($item['remarks']) ? htmlspecialchars($item['remarks']) : '<span class="text-gray-400">None</span>' ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex justify-center space-x-2">
                                                <a href="../items/edit_item.php?id=<?= $item['id'] ?>" 
                                                   class="text-blue-600 hover:text-blue-900 action-btn"
                                                   title="Edit Item">
                                                    <i class="ri-edit-line text-lg"></i>
                                                </a>
                                                <a href="?action=delete&id=<?= $item['id'] ?>" 
                                                   class="text-red-600 hover:text-red-900 action-btn"
                                                   onclick="return confirm('Are you sure you want to delete this item?')"
                                                   title="Delete Item">
                                                    <i class="ri-delete-bin-line text-lg"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="ri-inbox-line text-4xl mb-3"></i>
                                            <p class="text-lg">No items found in inventory</p>
                                            <a href="../items/add_item.php" class="mt-4 btn btn-primary">
                                                <i class="ri-add-line mr-2"></i> Add New Item
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
            <div class="mt-6 flex justify-center">
                <nav class="flex items-center space-x-1">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page-1 ?><?= $category_filter ? '&category='.urlencode($category_filter) : '' ?>" 
                           class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-s-line"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?page=<?= $i ?><?= $category_filter ? '&category='.urlencode($category_filter) : '' ?>" 
                           class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-secondary' ?> pagination-link">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $pages): ?>
                        <a href="?page=<?= $page+1 ?><?= $category_filter ? '&category='.urlencode($category_filter) : '' ?>" 
                           class="btn btn-secondary btn-sm">
                            Next <i class="ri-arrow-right-s-line"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Confirm before deleting
        document.querySelectorAll('a[href*="action=delete"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
        });

        // Select/Deselect all functionality
        document.getElementById('selectAllBtn').addEventListener('click', function() {
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAllCheckbox').checked = true;
        });

        document.getElementById('deselectAllBtn').addEventListener('click', function() {
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
        });

        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });

        // Update "select all" checkbox when individual checkboxes change
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(document.querySelectorAll('.item-checkbox')).every(cb => cb.checked);
                document.getElementById('selectAllCheckbox').checked = allChecked;
            });
        });

        // Confirm bulk delete
        function confirmBulkDelete() {
            const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one item to delete.');
                return false;
            }
            return confirm(`Are you sure you want to delete ${checkedBoxes.length} selected item(s)?`);
        }
    </script>
</body>
</html>