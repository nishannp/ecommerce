<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
include '../config.php';



function getOrders($conn, $page = 1, $limit = 10, $status = null, $search = null) {
    $offset = ($page - 1) * $limit;
    
    $whereClause = "";
    if ($status && $status !== 'all') {
        $whereClause .= " WHERE order_status = '" . $conn->real_escape_string($status) . "'";
    }
    
    if ($search) {
        $search = $conn->real_escape_string($search);
        if ($whereClause) {
            $whereClause .= " AND (order_number LIKE '%$search%' OR first_name LIKE '%$search%' OR 
                last_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
        } else {
            $whereClause .= " WHERE (order_number LIKE '%$search%' OR first_name LIKE '%$search%' OR 
                last_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
        }
    }
    
   
    $countQuery = "SELECT COUNT(*) as total FROM orders" . $whereClause;
    $countResult = $conn->query($countQuery);
    $totalRecords = $countResult->fetch_assoc()['total'];
    
    // Get orders with pagination
    $query = "SELECT * FROM orders" . $whereClause . " ORDER BY created_at DESC LIMIT $offset, $limit";
    $result = $conn->query($query);
    
    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    
    return [
        'orders' => $orders,
        'total' => $totalRecords,
        'pages' => ceil($totalRecords / $limit),
        'current' => $page
    ];
}


function getOrderDetails($conn, $orderId) {
    // Get order information
    $query = "SELECT * FROM orders WHERE id = " . intval($orderId);
    $result = $conn->query($query);
    
    if ($result->num_rows == 0) {
        return null;
    }
    
    $order = $result->fetch_assoc();
    
    // Get order items
    $itemsQuery = "SELECT oi.*, p.name as product_name, p.price 
                   FROM order_items oi 
                   LEFT JOIN products p ON oi.product_id = p.id
                   WHERE oi.order_id = " . intval($orderId);
    $itemsResult = $conn->query($itemsQuery);
    
    $order['items'] = [];
    if ($itemsResult->num_rows > 0) {
        while ($item = $itemsResult->fetch_assoc()) {
            $order['items'][] = $item;
        }
    }
    
    // Get status history
    $historyQuery = "SELECT * FROM order_status_history 
                     WHERE order_id = " . intval($orderId) . " 
                     ORDER BY created_at DESC";
    $historyResult = $conn->query($historyQuery);
    
    $order['status_history'] = [];
    if ($historyResult->num_rows > 0) {
        while ($history = $historyResult->fetch_assoc()) {
            $order['status_history'][] = $history;
        }
    }
    
    return $order;
}


function updateOrderStatus($conn, $orderId, $status, $notes = '') {
    // Update the order status
    $query = "UPDATE orders SET 
              order_status = '" . $conn->real_escape_string($status) . "',
              updated_at = NOW() 
              WHERE id = " . intval($orderId);
    
    $result = $conn->query($query);
    
    if ($result) {
      
        $historyQuery = "INSERT INTO order_status_history (order_id, status, notes, created_at) 
                        VALUES (" . intval($orderId) . ", 
                                '" . $conn->real_escape_string($status) . "', 
                                '" . $conn->real_escape_string($notes) . "', 
                                NOW())";
        $conn->query($historyQuery);
        return true;
    }
    
    return false;
}


$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $newStatus = isset($_POST['status']) ? $_POST['status'] : '';
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
        
        if ($orderId && $newStatus) {
            $updated = updateOrderStatus($conn, $orderId, $newStatus, $notes);
            if ($updated) {
                $message = "Order status updated successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to update order status.";
                $messageType = "danger";
            }
        }
    }
}


$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';


$ordersData = getOrders($conn, $currentPage, 10, $statusFilter === 'all' ? null : $statusFilter, $searchTerm);
$orders = $ordersData['orders'];


$viewOrder = null;
if (isset($_GET['view']) && intval($_GET['view']) > 0) {
    $viewOrder = getOrderDetails($conn, intval($_GET['view']));
}


$statusColors = [
    'pending' => 'warning',
    'processing' => 'info',
    'shipped' => 'primary',
    'delivered' => 'success',
    'cancelled' => 'danger'
];


$statusCountsQuery = "SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status";
$statusCountsResult = $conn->query($statusCountsQuery);
$statusCounts = [];

if ($statusCountsResult->num_rows > 0) {
    while ($row = $statusCountsResult->fetch_assoc()) {
        $statusCounts[$row['order_status']] = $row['count'];
    }
}


$statsQuery = "SELECT 
               COUNT(*) as total_orders,
               SUM(order_total) as total_revenue,
               COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_orders
               FROM orders";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

include 'header.php';
?>


  
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --white-color: #ffffff;
        }
        
        .orders-container {
            padding: 20px;
           
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: var(--white-color);
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
        }
        
        .stats-card {
            padding: 20px;
            border-left: 4px solid;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats-card.primary {
            border-left-color: var(--primary-color);
        }
        
        .stats-card.success {
            border-left-color: var(--secondary-color);
        }
        
        .stats-card.info {
            border-left-color: var(--info-color);
        }
        
        .stats-card.warning {
            border-left-color: var(--warning-color);
        }
        
        .stats-card h3 {
            margin: 0;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--primary-color);
        }
        
        .stats-card .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--dark-color);
        }
        
        .card-header {
            padding: 15px 20px;
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .card-body {
            padding: 20px;
        }
        
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .search-box {
            flex-grow: 1;
            max-width: 400px;
        }
        
        .search-box input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
        }
        
        .filter-status select {
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            min-width: 150px;
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            color: #fff;
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-warning {
            color: #212529;
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .btn-info {
            color: #fff;
            background-color: var(--info-color);
            border-color: var(--info-color);
        }
        
        .btn-danger {
            color: #fff;
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        
        .alert {
            position: relative;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
        }
        
        table th {
            background-color: #f8f9fc;
            font-weight: 500;
        }
        
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        
        .badge-pending { background-color: var(--warning-color); color: #212529; }
        .badge-processing { background-color: var(--info-color); color: #fff; }
        .badge-shipped { background-color: var(--primary-color); color: #fff; }
        .badge-delivered { background-color: var(--secondary-color); color: #fff; }
        .badge-cancelled { background-color: var(--danger-color); color: #fff; }
        
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            justify-content: center;
            margin: 20px 0;
        }
        
        .pagination li {
            margin: 0 5px;
        }
        
        .pagination a {
            position: relative;
            display: block;
            padding: 0.5rem 0.75rem;
            line-height: 1.25;
            color: var(--primary-color);
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            text-decoration: none;
        }
        
        .pagination .active a {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .order-detail {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .status-timeline {
            margin: 30px 0;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 45px;
            padding-bottom: 20px;
        }
        
        .timeline-item:before {
            content: "";
            position: absolute;
            left: 15px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #e3e6f0;
        }
        
        .timeline-item:last-child:before {
            height: 15px;
        }
        
        .timeline-marker {
            position: absolute;
            left: 8px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: var(--primary-color);
        }
        
        .timeline-content {
            padding: 15px;
            background-color: #f8f9fc;
            border-radius: 8px;
        }
        
        .timeline-date {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 5px;
        }
        
        .timeline-note {
            margin-top: 10px;
            padding: 10px;
            background-color: #fff;
            border-radius: 4px;
            border-left: 3px solid var(--info-color);
        }
        
        .status-form {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        textarea.form-control {
            min-height: 100px;
        }
    </style>

    <div class="orders-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!$viewOrder): ?>
            
            <div class="stats-cards">
                <div class="stats-card primary">
                    <div>
                        <h3>Total Orders</h3>
                        <div class="value"><?php echo number_format($stats['total_orders']); ?></div>
                    </div>
                    <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
                </div>
                
                <div class="stats-card success">
                    <div>
                        <h3>Total Revenue</h3>
                        <div class="value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                </div>
                
                <div class="stats-card info">
                    <div>
                        <h3>Last 30 Days</h3>
                        <div class="value"><?php echo number_format($stats['recent_orders']); ?> orders</div>
                    </div>
                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                </div>
                
                <div class="stats-card warning">
                    <div>
                        <h3>Pending Orders</h3>
                        <div class="value"><?php echo isset($statusCounts['pending']) ? number_format($statusCounts['pending']) : 0; ?></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                </div>
            </div>
            
           
            <div class="card">
                <div class="card-header">
                    <h2>Orders Management</h2>
                </div>
                <div class="card-body">
                  
                    <form method="GET" action="orders.php" class="filters">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        </div>
                        
                        <div class="filter-status">
                            <select name="status">
                                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                    
                    
                    <?php if (count($orders) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                                            <small><?php echo htmlspecialchars($order['email']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>$<?php echo number_format($order['order_total'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $order['order_status']; ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?view=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                      
                        <?php if ($ordersData['pages'] > 1): ?>
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $ordersData['pages']; $i++): ?>
                                    <li class="<?php echo $i === $ordersData['current'] ? 'active' : ''; ?>">
                                        <a href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchTerm); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No orders found.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
           
            <div class="back-btn">
                <a href="orders.php" class="btn btn-primary">&laquo; Back to Orders</a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Order #<?php echo htmlspecialchars($viewOrder['order_number']); ?></h2>
                </div>
                <div class="card-body">
                    <div class="order-detail">
                        <div>
                            <h3>Order Information</h3>
                            <p><strong>Date:</strong> <?php echo date('F d, Y H:i', strtotime($viewOrder['created_at'])); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge badge-<?php echo $viewOrder['order_status']; ?>">
                                    <?php echo ucfirst($viewOrder['order_status']); ?>
                                </span>
                            </p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($viewOrder['payment_method'])); ?></p>
                            <p><strong>Total:</strong> $<?php echo number_format($viewOrder['order_total'], 2); ?></p>
                            <?php if ($viewOrder['notes']): ?>
                                <p><strong>Order Notes:</strong> <?php echo nl2br(htmlspecialchars($viewOrder['notes'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <h3>Customer Information</h3>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($viewOrder['first_name'] . ' ' . $viewOrder['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($viewOrder['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($viewOrder['phone']); ?></p>
                            <p><strong>Address:</strong><br>
                                <?php echo htmlspecialchars($viewOrder['address']); ?><br>
                                <?php echo htmlspecialchars($viewOrder['city'] . ', ' . $viewOrder['state'] . ' ' . $viewOrder['zip_code']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h3>Order Items</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($viewOrder['items']) > 0): ?>
                                    <?php foreach ($viewOrder['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name'] ?? 'Unknown Product'); ?></td>
                                            <td>$<?php echo number_format($item['price'] ?? 0, 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format(($item['price'] ?? 0) * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">No items found for this order.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    
                    <div class="status-timeline">
                        <h3>Order Status History</h3>
                        
                        <?php if (count($viewOrder['status_history']) > 0): ?>
                            <?php foreach ($viewOrder['status_history'] as $history): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-date">
                                            <?php echo date('F d, Y H:i', strtotime($history['created_at'])); ?>
                                        </div>
                                        <strong>Status changed to: <?php echo ucfirst($history['status']); ?></strong>
                                        
                                        <?php if ($history['notes']): ?>
                                            <div class="timeline-note">
                                                <?php echo nl2br(htmlspecialchars($history['notes'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No status history available.</p>
                        <?php endif; ?>
                    </div>
                    
                   
                    <div class="status-form">
                        <h3>Update Order Status</h3>
                        <form method="POST" action="orders.php?view=<?php echo $viewOrder['id']; ?>">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo $viewOrder['id']; ?>">
                            
                            <div class="form-group">
                                <label for="status">New Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="pending" <?php echo $viewOrder['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $viewOrder['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $viewOrder['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $viewOrder['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $viewOrder['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Status Notes</label>
                                <textarea name="notes" id="notes" class="form-control" placeholder="Add notes about this status change (optional)"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

   
    <script>
       
        document.addEventListener('DOMContentLoaded', function() {
           
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 1s';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 1000);
                });
            }, 5000);
            
            
            const statusForm = document.querySelector('.status-form form');
            if (statusForm) {
                statusForm.addEventListener('submit', function(e) {
                    const newStatus = document.getElementById('status').value;
                    if (newStatus === 'cancelled') {
                        if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
                            e.preventDefault();
                        }
                    }
                });
            }
            
          
            const searchInput = document.querySelector('.search-box input');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        searchInput.form.submit();
                    }, 500);
                });
            }
        });
    </script>
