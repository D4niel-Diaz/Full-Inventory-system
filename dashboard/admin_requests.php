<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Initialize variables
$items = [];
$total = 0;
$pages = 1;
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;
$category_filter = isset($_GET['category']) ? filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING) : '';

// Handle single item deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $item_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($item_id) {
        try {
            // Check for existing transactions
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE item_id = ?");
            $check_stmt->execute([$item_id]);
            $transaction_count = $check_stmt->fetchColumn();
            
            if ($transaction_count > 0) {
                $_SESSION['error'] = "Cannot delete item with existing transactions.";
            } else {
                $delete_stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
                $delete_stmt->execute([$item_id]);
                
                if ($delete_stmt->rowCount() > 0) {
                    $_SESSION['success'] = "Item deleted successfully.";
                } else {
                    $_SESSION['error'] = "Item not found or already deleted.";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            error_log("Delete Error: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = "Invalid item ID.";
    }
    header("Location: admin_requests.php");
    exit();
}

// Handle bulk deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    if (!empty($_POST['selected_items'])) {
        $selected_items = array_map('intval', $_POST['selected_items']); // Ensure all values are integers
        $selected_items = array_filter($selected_items); // Remove any empty values
        $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
        
        try {
            $conn->beginTransaction();
            
            // Check for any items with transactions
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE item_id IN ($placeholders)");
            $check_stmt->execute($selected_items);
            $transaction_count = $check_stmt->fetchColumn();
            
            if ($transaction_count > 0) {
                throw new Exception("Cannot delete items with existing transactions.");
            }
            
            // Delete selected items
            $delete_stmt = $conn->prepare("DELETE FROM items WHERE id IN ($placeholders)");
            $delete_stmt->execute($selected_items);
            $deleted_count = $delete_stmt->rowCount();
            
            $conn->commit();
            $_SESSION['success'] = "Successfully deleted $deleted_count item(s).";
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error'] = $e->getMessage();
            error_log("Bulk Delete Error: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = "No items selected for deletion.";
    }
    header("Location: admin_requests.php");
    exit();
}

try {
    // Get items with pagination and category filter
    $query = "SELECT id, name AS item, category, 
                     quantity AS ched_req, 
                     available_quantity AS on_hand,
                     remarks, unit
              FROM items 
              WHERE (:category_filter = '' OR category = :category_filter)
              ORDER BY id DESC 
              LIMIT :start, :limit";
    
    $stmt = $conn->prepare($query);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | Admin Dashboard</title>
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
        .checkbox-cell {
            width: 40px;
        }
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .pagination-link:hover:not(.active) {
        background-color: #f3f4f6;
    }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <i class="ri-box-2-line text-blue-600 text-2xl mr-2"></i>
                    <span class="text-xl font-semibold text-gray-900">Inventory Management System</span>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <div class="ml-3 relative">
                    <div>
                        <button id="user-menu" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="sr-only">Open user menu</span>
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                <i class="ri-user-line"></i>
                            </div>
                            <span class="ml-2 text-gray-700"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
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

    <div class="container mx-auto p-4 md:p-6">
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
                <i class="ri-list-check-2 mr-2 text-blue-500"></i> Inventory Summary
            </h2>
            <div class="flex flex-wrap gap-3">
                <a href="../items/add_item.php"  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                    <i class="ri-add-line mr-2"></i> Add Item
                </a>
                <a href="view_transactions.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                    <i class="ri-history-line mr-2"></i> Transactions
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

        <!-- Bulk Actions Form - Now wrapping the table -->
        <form id="bulkActionForm" method="post" action="admin_requests.php">
            <div class="mb-4 flex flex-wrap gap-3">
                <button type="button" id="selectAllBtn" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition-colors flex items-center action-btn">
                    <i class="ri-checkbox-line mr-1"></i> Select All
                </button>
                <button type="button" id="deselectAllBtn" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition-colors flex items-center action-btn">
                    <i class="ri-checkbox-blank-line mr-1"></i> Deselect All
                </button>
                <button type="submit" name="bulk_delete" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 transition-colors flex items-center action-btn">
                    <i class="ri-delete-bin-line mr-1"></i> Delete Selected
                </button>
            </div>

            <!-- Inventory Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="checkbox-cell px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAllCheckbox" class="rounded text-blue-600 focus:ring-blue-500">
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
                                    <tr class="table-row hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" class="item-checkbox rounded text-blue-600 focus:ring-blue-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <i class="ri-box-line text-gray-400 mr-2"></i>
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['item']) ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($item['category']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <span class="inline-block px-2 py-1 rounded <?= is_numeric($item['ched_req']) ? 'bg-blue-50 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= htmlspecialchars($item['ched_req']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <span class="inline-block px-2 py-1 rounded <?= is_numeric($item['on_hand']) ? 'bg-green-50 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= htmlspecialchars($item['on_hand']) ?>
                                                <?php if (is_numeric($item['on_hand'])): ?>
                                                    <span class="text-gray-400 ml-1"><?= htmlspecialchars($item['unit']) ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= !empty($item['remarks']) ? htmlspecialchars($item['remarks']) : '<span class="text-gray-400">None</span>' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex justify-center space-x-3">
                                                <a href="../items/edit_item.php?id=<?= $item['id'] ?>" 
                                                   class="text-blue-600 hover:text-blue-900 action-btn"
                                                   title="Edit Item">
                                                    <i class="ri-edit-line text-lg"></i>
                                                </a>
                                                <span class="text-gray-300">|</span>
                                                <a href="?action=delete&id=<?= $item['id'] ?>" 
                                                    class="text-red-600 hover:text-red-900 action-btn delete-btn"
                                                    title="Delete Item">
                                                    <i class="ri-delete-bin-line text-lg"></i>
                                                </a>
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
        </form>

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

    <div id="delete-modal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="ri-error-warning-fill text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Delete Item
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 modal-description">
                                Are you sure you want to delete this item? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirm-delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Delete
                </button>
                <button type="button" id="cancel-delete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Delete modal functionality
    const deleteModal = document.getElementById('delete-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    let currentAction = '';
    let currentForm = null;

    // Function to open the modal
    function openDeleteModal(action, form = null) {
        currentAction = action;
        currentForm = form;
        deleteModal.classList.remove('hidden');
    }

    // Function to close the modal
    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }

    // Confirm delete action
    confirmDeleteBtn.addEventListener('click', function() {
        if (currentAction === 'single') {
            window.location.href = currentDeleteUrl;
        } else if (currentAction === 'bulk' && currentForm) {
            currentForm.submit();
        }
    });

    // Cancel delete action
    document.getElementById('cancel-delete').addEventListener('click', closeDeleteModal);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });

    // Single item delete
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openDeleteModal('single', null);
            currentDeleteUrl = this.getAttribute('href');
        });
    });

    // Bulk delete confirmation
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one item to delete.');
            return false;
        }
        
        e.preventDefault();
        openDeleteModal('bulk', this);
        
        // Update modal text for bulk delete
        document.querySelector('#delete-modal [id="modal-title"]').textContent = `Delete ${checkedBoxes.length} Item(s)`;
        document.querySelector('#delete-modal .modal-description').textContent = 
            `Are you sure you want to delete ${checkedBoxes.length} selected item(s)? This action cannot be undone.`;
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

    // Dropdown functionality
    const userMenuButton = document.getElementById('user-menu');
    const userDropdown = document.getElementById('user-dropdown');

    userMenuButton.addEventListener('click', function() {
        userDropdown.classList.toggle('hidden');
    });

    // Optional: Hide dropdown when clicking outside
    window.addEventListener('click', function(e) {
        if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.add('hidden');
        }
    });
</script>
</body>
</html>