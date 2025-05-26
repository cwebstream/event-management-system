<?php
require_once 'config.php';
checkLogin();

// Check if user is event admin
if ($_SESSION['user_type'] !== 'event_admin') {
    header("Location: login.php");
    exit();
}

$event_id = $_SESSION['event_id'];
$permissions = $_SESSION['permissions'];

// Fetch event details
$sql = "SELECT * FROM events WHERE id = $event_id";
$event_result = $conn->query($sql);
$event = $event_result->fetch_assoc();

// Fetch module statistics
$stats = [
    'checkins' => $conn->query("SELECT COUNT(*) as count FROM checkin_logs WHERE event_id = $event_id")->fetch_assoc()['count'],
    'access_logs' => $conn->query("SELECT COUNT(*) as count FROM access_logs WHERE event_id = $event_id")->fetch_assoc()['count'],
    'fb_dist' => $conn->query("SELECT COUNT(*) as count FROM fb_logs WHERE event_id = $event_id")->fetch_assoc()['count'],
    'goodies' => $conn->query("SELECT COUNT(*) as count FROM goodies_logs WHERE event_id = $event_id")->fetch_assoc()['count'],
    'wallet_txns' => $conn->query("SELECT COUNT(*) as count FROM wallet_transactions WHERE event_id = $event_id")->fetch_assoc()['count']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Admin Dashboard - Event Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <span class="text-xl font-semibold">Event Dashboard</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">Event Admin</span>
                        <a href="logout.php" class="text-red-600 hover:text-red-700">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Event Info -->
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($event['name']); ?>
                        </h2>
                        <p class="text-gray-600">
                            <?php echo htmlspecialchars($event['description']); ?>
                        </p>
                        <div class="mt-2 text-sm text-gray-500">
                            <?php echo date('F j, Y', strtotime($event['start_date'])); ?> - 
                            <?php echo date('F j, Y', strtotime($event['end_date'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Module Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <?php if (in_array('Check-In', $permissions)): ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Check-ins</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $stats['checkins']; ?></dd>
                            <div class="mt-4">
                                <a href="checkin.php" class="text-indigo-600 hover:text-indigo-900">Manage Check-ins →</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array('Access Zones', $permissions)): ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Access Zone Entries</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $stats['access_logs']; ?></dd>
                            <div class="mt-4">
                                <a href="access_zones.php" class="text-indigo-600 hover:text-indigo-900">Manage Access Zones →</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array('F&B Counter', $permissions)): ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">F&B Distributions</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $stats['fb_dist']; ?></dd>
                            <div class="mt-4">
                                <a href="fb_counter.php" class="text-indigo-600 hover:text-indigo-900">Manage F&B Counter →</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array('Goodies Counter', $permissions)): ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Goodies Distributed</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $stats['goodies']; ?></dd>
                            <div class="mt-4">
                                <a href="goodies_counter.php" class="text-indigo-600 hover:text-indigo-900">Manage Goodies →</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array('Wallet System', $permissions)): ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Wallet Transactions</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $stats['wallet_txns']; ?></dd>
                            <div class="mt-4">
                                <a href="wallet_system.php" class="text-indigo-600 hover:text-indigo-900">Manage Wallet →</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array('Analytics Dashboard', $permissions)): ?>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Analytics Overview</dt>
                            <dd class="mt-1 text-lg text-gray-900">View detailed reports</dd>
                            <div class="mt-4">
                                <a href="analytics.php" class="text-indigo-600 hover:text-indigo-900">View Analytics →</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <?php
                                // Fetch recent activity across all modules
                                $sql = "SELECT 'Check-in' as type, participant_id, check_in_time as time 
                                       FROM checkin_logs WHERE event_id = $event_id
                                       UNION ALL
                                       SELECT 'Access' as type, participant_id, access_time as time 
                                       FROM access_logs WHERE event_id = $event_id
                                       UNION ALL
                                       SELECT 'F&B' as type, participant_id, distribution_time as time 
                                       FROM fb_logs WHERE event_id = $event_id
                                       ORDER BY time DESC LIMIT 10";
                                $activities = $conn->query($sql);
                                
                                while($activity = $activities->fetch_assoc()):
                                ?>
                                <li class="pb-4">
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                    <!-- Icon based on activity type -->
                                                    <span class="text-white text-sm">
                                                        <?php echo substr($activity['type'], 0, 1); ?>
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        <?php echo $activity['type']; ?> activity for participant 
                                                        <span class="font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($activity['participant_id']); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <?php echo date('M j, H:i', strtotime($activity['time'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
