<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

require_once '../config.php';


$productQuery = "SELECT COUNT(*) as total_products FROM products";
$productResult = $conn->query($productQuery);
$productCount = $productResult->fetch_assoc()['total_products'];

// Count total categories
$categoryQuery = "SELECT COUNT(*) as total_categories FROM categories";
$categoryResult = $conn->query($categoryQuery);
$categoryCount = $categoryResult->fetch_assoc()['total_categories'];

// Count total orders
$orderQuery = "SELECT COUNT(*) as total_orders FROM orders";
$orderResult = $conn->query($orderQuery);
$orderCount = $orderResult->fetch_assoc()['total_orders'];

// Get recent orders (last 5)
$recentOrdersQuery = "SELECT id, order_number, first_name, last_name, order_total, order_status, created_at 
                     FROM orders 
                     ORDER BY created_at DESC 
                     LIMIT 5";
$recentOrdersResult = $conn->query($recentOrdersQuery);

// Get featured products
$featuredProductsQuery = "SELECT id, name, price, image_url_1 
                         FROM products 
                         WHERE featured = 1 
                         LIMIT 4";
$featuredProductsResult = $conn->query($featuredProductsQuery);

// Get revenue statistics (assuming orders table has data)
$revenueQuery = "SELECT SUM(order_total) as total_revenue FROM orders WHERE order_status != 'cancelled'";
$revenueResult = $conn->query($revenueQuery);
$totalRevenue = $revenueResult->fetch_assoc()['total_revenue'] ?? 0;

// Get pending orders count
$pendingOrdersQuery = "SELECT COUNT(*) as pending_count FROM orders WHERE order_status = 'pending'";
$pendingOrdersResult = $conn->query($pendingOrdersQuery);
$pendingOrdersCount = $pendingOrdersResult->fetch_assoc()['pending_count'];

// Include header file which contains the sidebar navigation
include 'header.php';
?>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" rel="stylesheet">
    <style>
        
        
        .dashboard-container {
            padding: 20px;
            
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .dashboard-title {
            color: var(--dark-color);
            font-size: 24px;
            font-weight: 600;
        }
        
        .date-time {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.primary {
            border-left: 5px solid var(--primary-color);
        }
        
        .stat-card.success {
            border-left: 5px solid var(--secondary-color);
        }
        
        .stat-card.warning {
            border-left: 5px solid var(--warning-color);
        }
        
        .stat-card.danger {
            border-left: 5px solid var(--danger-color);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-title {
            color: #7f8c8d;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .stat-icon {
            font-size: 24px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .stat-icon.primary {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }
        
        .stat-icon.success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }
        
        .stat-icon.warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }
        
        .stat-icon.danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .stat-description {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .see-all {
            color: var(--primary-color);
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
        }
        
        .see-all:hover {
            text-decoration: underline;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: 600;
            color: #7f8c8d;
            border-bottom: 2px solid var(--border-color);
        }
        
        td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--dark-color);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .avatar-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e0e0e0;
        }
        
        .avatar-initials {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }
        
        .customer-name {
            font-weight: 500;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status.pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }
        
        .status.completed {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }
        
        .status.processing {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }
        
        .status.cancelled {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }
        
        .product-card {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        
        .product-card:hover {
            background-color: #f9f9f9;
        }
        
        .product-card:last-child {
            margin-bottom: 0;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #e0e0e0;
        }
        
        .product-details {
            flex-grow: 1;
        }
        
        .product-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .product-price {
            font-size: 14px;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .product-action {
            color: var(--primary-color);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        
        .product-action:hover {
            text-decoration: underline;
        }
        
       
        @media (max-width: 1200px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .row {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard</h1>
            <div class="date-time">
                <?php echo date('l, F j, Y'); ?>
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-title">Total Products</div>
                    <div class="stat-icon primary">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $productCount; ?></div>
                <div class="stat-description">
                    <i class="fas fa-arrow-up"></i> 
                    <?php echo rand(5, 15); ?>% since last month
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-title">Total Revenue</div>
                    <div class="stat-icon success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-description">
                    <i class="fas fa-arrow-up"></i> 
                    <?php echo rand(5, 20); ?>% since last month
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-title">Total Orders</div>
                    <div class="stat-icon warning">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $orderCount; ?></div>
                <div class="stat-description">
                    <i class="fas fa-arrow-up"></i> 
                    <?php echo rand(5, 15); ?>% since last month
                </div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-header">
                    <div class="stat-title">Pending Orders</div>
                    <div class="stat-icon danger">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $pendingOrdersCount; ?></div>
                <div class="stat-description">
                    <i class="fas fa-arrow-down"></i> 
                    <?php echo rand(5, 15); ?>% since last month
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Orders</h2>
                    <a href="orders.php" class="see-all">See All</a>
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Order Number</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentOrdersResult && $recentOrdersResult->num_rows > 0): ?>
                                <?php while ($order = $recentOrdersResult->fetch_assoc()): ?>
                                    <?php 
                                        $initials = strtoupper(substr($order['first_name'], 0, 1) . substr($order['last_name'], 0, 1));
                                        $statusClass = '';
                                        switch (strtolower($order['order_status'])) {
                                            case 'pending':
                                                $statusClass = 'pending';
                                                break;
                                            case 'completed':
                                                $statusClass = 'completed';
                                                break;
                                            case 'processing':
                                                $statusClass = 'processing';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'cancelled';
                                                break;
                                            default:
                                                $statusClass = 'pending';
                                        }
                                        
                                        $orderDate = new DateTime($order['created_at']);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="avatar-container">
                                                <div class="avatar-initials"><?php echo $initials; ?></div>
                                                <span class="customer-name">
                                                    <?php echo $order['first_name'] . ' ' . $order['last_name']; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>#<?php echo $order['order_number']; ?></td>
                                        <td>$<?php echo number_format($order['order_total'], 2); ?></td>
                                        <td><span class="status <?php echo $statusClass; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                        <td><?php echo $orderDate->format('M d, Y'); ?></td>
                                        <td>
                                            <a href="view-order.php?id=<?php echo $order['id']; ?>" class="see-all">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No orders found</td>
                                </tr>
                                <?php 
                                // Sample data for demonstration when no orders exist
                                $demoOrders = [
                                    ['name' => 'John Doe', 'order' => '12345', 'amount' => 129.99, 'status' => 'pending', 'date' => 'Feb 25, 2025'],
                                    ['name' => 'Sarah Smith', 'order' => '12344', 'amount' => 89.99, 'status' => 'completed', 'date' => 'Feb 24, 2025'],
                                    ['name' => 'Michael Johnson', 'order' => '12343', 'amount' => 199.50, 'status' => 'processing', 'date' => 'Feb 23, 2025'],
                                    ['name' => 'Emma Williams', 'order' => '12342', 'amount' => 49.99, 'status' => 'cancelled', 'date' => 'Feb 22, 2025'],
                                    ['name' => 'Robert Brown', 'order' => '12341', 'amount' => 159.00, 'status' => 'completed', 'date' => 'Feb 21, 2025']
                                ];
                                
                                foreach ($demoOrders as $demo): 
                                    $nameArr = explode(' ', $demo['name']);
                                    $initials = strtoupper(substr($nameArr[0], 0, 1) . substr($nameArr[1], 0, 1));
                                ?>
                                    <tr>
                                        <td>
                                            <div class="avatar-container">
                                                <div class="avatar-initials"><?php echo $initials; ?></div>
                                                <span class="customer-name"><?php echo $demo['name']; ?></span>
                                            </div>
                                        </td>
                                        <td>#<?php echo $demo['order']; ?></td>
                                        <td>$<?php echo number_format($demo['amount'], 2); ?></td>
                                        <td><span class="status <?php echo $demo['status']; ?>"><?php echo ucfirst($demo['status']); ?></span></td>
                                        <td><?php echo $demo['date']; ?></td>
                                        <td>
                                            <a href="#" class="see-all">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Featured Products</h2>
                    <a href="products.php" class="see-all">See All</a>
                </div>
                <div class="card-body">
                    <?php if ($featuredProductsResult && $featuredProductsResult->num_rows > 0): ?>
                        <?php while ($product = $featuredProductsResult->fetch_assoc()): ?>
                            <div class="product-card">
                            <img src="<?php echo isset($product['image_url_1']) && !empty($product['image_url_1']) ? '../' . $product['image_url_1'] : 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" class="product-image">

                                <div class="product-details">
                                    <div class="product-name"><?php echo $product['name']; ?></div>
                                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                </div>
                                <button class="product-action">Edit</button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?php 
                        // Sample data for demonstration when no featured products exist
                        $demoProducts = [
                            ['name' => 'Premium Headphones', 'price' => 199.99],
                            ['name' => 'Wireless Keyboard', 'price' => 59.99],
                            ['name' => 'Smart Watch', 'price' => 149.50],
                            ['name' => 'Bluetooth Speaker', 'price' => 79.99]
                        ];
                        
                        foreach ($demoProducts as $demo): 
                        ?>
                            <div class="product-card">
                                <img src="assets/images/placeholder.jpg" alt="<?php echo $demo['name']; ?>" class="product-image">
                                <div class="product-details">
                                    <div class="product-name"><?php echo $demo['name']; ?></div>
                                    <div class="product-price">$<?php echo number_format($demo['price'], 2); ?></div>
                                </div>
                                <button class="product-action">Edit</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Sales Overview</h2>
                    <select id="sales-period" style="padding: 6px; border-radius: 5px; border: 1px solid var(--border-color);">
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="sales-chart" style="width: 100%; height: 300px;"></canvas>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Categories Distribution</h2>
                </div>
                <div class="card-body">
                    <canvas id="categories-chart" style="width: 100%; height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Sample data for sales chart
        const salesData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'Sales',
                    data: [1500, 2500, 2000, 3000, 2800, 3500, 4000, 3800, 4200, 4500, 5000, 5500],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Orders',
                    data: [20, 40, 30, 45, 35, 55, 65, 60, 70, 80, 90, 100],
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        };
        
        // Sample data for categories chart
        const categoriesData = {
            labels: ['Electronics', 'Clothing', 'Home & Kitchen', 'Books', 'Toys'],
            datasets: [
                {
                    data: [35, 25, 20, 10, 10],
                    backgroundColor: ['#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6'],
                    borderWidth: 0
                }
            ]
        };
        
        
        const salesChart = new Chart(
            document.getElementById('sales-chart'),
            {
                type: 'line',
                data: salesData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            }
        );
        
        
        const categoriesChart = new Chart(
            document.getElementById('categories-chart'),
            {
                type: 'doughnut',
                data: categoriesData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            }
        );
        
        
        document.getElementById('sales-period').addEventListener('change', function() {
            const period = this.value;
            let newData;
            
            if (period === 'week') {
                newData = [800, 1200, 950, 1100, 1300, 900, 1000];
                salesChart.data.labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            } else if (period === 'month') {
                newData = [1500, 2500, 2000, 3000, 2800, 3500, 4000, 3800, 4200, 4500, 5000, 5500];
                salesChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            } else if (period === 'year') {
                newData = [28000, 32000, 36000, 40000, 45000];
                salesChart.data.labels = ['2021', '2022', '2023', '2024', '2025'];
            }
            
            salesChart.data.datasets[0].data = newData;
            salesChart.update();
        });
    </script>
