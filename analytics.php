<?php
require_once 'config.php';
checkLogin();

// Check if user is event admin and has Analytics Dashboard permission
if ($_SESSION['user_type'] !== 'event_admin' || !in_array('Analytics Dashboard', $_SESSION['permissions'])) {
    header("Location: admin_login.php");
    exit();
}

$event_id = $_SESSION['event_id'];

// Get event details
$sql = "SELECT * FROM events WHERE id = $event_id";
$event_result = $conn->query($sql);
$event = $event_result->fetch_assoc();

// Get total check-ins
$sql = "SELECT COUNT(*) as total FROM checkin_logs WHERE event_id = $event_id";
$total_checkins = $conn->query($sql)->fetch_assoc()['total'];

// Get hourly check-ins for today
$today = date('Y-m-d');
$sql = "SELECT HOUR(check_in_time) as hour, COUNT(*) as count 
        FROM checkin_logs 
        WHERE event_id = $event_id 
        AND DATE(check_in_time) = '$today'
        GROUP BY HOUR(check_in_time)
        ORDER BY hour";
$hourly_checkins = $conn->query($sql);

// Get zone access statistics
$sql = "SELECT zone_name, COUNT(*) as count 
        FROM access_logs 
        WHERE event_id = $event_id 
        GROUP BY zone_name";
$zone_stats = $conn->query($sql);

// Get F&B distribution stats
$sql = "SELECT item_name, COUNT(*) as count 
        FROM fb_logs 
        WHERE event_id = $event_id 
        GROUP BY item_name";
$fb_stats = $conn->query($sql);

// Get goodies distribution stats
$sql = "SELECT item_name, COUNT(*) as count 
        FROM goodies_logs 
        WHERE event_id = $event_id 
        GROUP BY item_name";
$goodies_stats = $conn->query($sql);

// Get wallet transaction stats
$sql = "SELECT 
        SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_credits,
        SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_debits,
        COUNT(*) as total_transactions
        FROM wallet_transactions 
        WHERE event_id = $event_id";
$wallet_stats = $conn->query($sql)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Event Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="admin_dashboard.php" class="text-xl font-semibold">Analytics Dashboard</a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="admin_dashboard.php" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                        <a href="logout.php" class="text-red-600 hover:text-red-700">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <!-- Event Overview -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($event['name']); ?> - Analytics
                        </h2>
                        <p class="text-gray-600">
                            <?php echo date('F j, Y', strtotime($event['start_date'])); ?> - 
                            <?php echo date('F j, Y', strtotime($event['end_date'])); ?>
                        </p>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Check-ins</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $total_checkins; ?></dd>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Transactions</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                <?php echo $wallet_stats['total_transactions']; ?>
                            </dd>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Net Transaction Value</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                $<?php echo number_format($wallet_stats['total_credits'] - $wallet_stats['total_debits'], 2); ?>
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Hourly Check-ins Chart -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Check-in Pattern</h3>
                            <canvas id="checkinsChart"></canvas>
                        </div>
                    </div>

                    <!-- Zone Access Distribution Chart -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Zone Access Distribution</h3>
                            <canvas id="zoneChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Stats -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- F&B Distribution Stats -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">F&B Distribution</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Item
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Count
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while($fb = $fb_stats->fetch_assoc()): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($fb['item_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $fb['count']; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Goodies Distribution Stats -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Goodies Distribution</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Item
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Count
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while($goodie = $goodies_stats->fetch_assoc()): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($goodie['item_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $goodie['count']; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Prepare data for charts
        const hourlyData = {
            labels: <?php 
                $hours = [];
                $counts = [];
                while($row = $hourly_checkins->fetch_assoc()) {
                    $hours[] = $row['hour'] . ':00';
                    $counts[] = $row['count'];
                }
                echo json_encode($hours);
            ?>,
            datasets: [{
                label: 'Check-ins',
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1
            }]
        };

        const zoneData = {
            labels: <?php 
                $zones = [];
                $zone_counts = [];
                $zone_stats->data_seek(0);
                while($row = $zone_stats->fetch_assoc()) {
                    $zones[] = $row['zone_name'];
                    $zone_counts[] = $row['count'];
                }
                echo json_encode($zones);
            ?>,
            datasets: [{
                data: <?php echo json_encode($zone_counts); ?>,
                backgroundColor: [
                    'rgba(79, 70, 229, 0.2)',
                    'rgba(59, 130, 246, 0.2)',
                    'rgba(16, 185, 129, 0.2)',
                    'rgba(245, 158, 11, 0.2)',
                    'rgba(239, 68, 68, 0.2)'
                ],
                borderColor: [
                    'rgba(79, 70, 229, 1)',
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 1
            }]
        };

        // Create charts
        new Chart(document.getElementById('checkinsChart'), {
            type: 'line',
            data: hourlyData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('zoneChart'), {
            type: 'doughnut',
            data: zoneData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
