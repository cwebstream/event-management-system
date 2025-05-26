<?php
require_once 'config.php';
checkLogin();

// Check if user is event admin and has access zones permission
if ($_SESSION['user_type'] !== 'event_admin' || !in_array('Access Zones', $_SESSION['permissions'])) {
    header("Location: admin_login.php");
    exit();
}

$event_id = $_SESSION['event_id'];

// Handle access log submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $participant_id = sanitize($_POST['participant_id']);
    $zone_name = sanitize($_POST['zone_name']);
    
    // Check if participant is checked in
    $check_sql = "SELECT id FROM checkin_logs WHERE event_id = $event_id AND participant_id = '$participant_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows === 0) {
        $error = "Participant not checked in to the event";
    } else {
        $sql = "INSERT INTO access_logs (event_id, participant_id, zone_name) 
                VALUES ($event_id, '$participant_id', '$zone_name')";
        if ($conn->query($sql)) {
            $success = "Access recorded successfully";
        } else {
            $error = "Error recording access: " . $conn->error;
        }
    }
}

// Fetch recent access logs
$sql = "SELECT * FROM access_logs WHERE event_id = $event_id ORDER BY access_time DESC LIMIT 10";
$recent_access = $conn->query($sql);

// Get total access counts for today
$today = date('Y-m-d');
$sql = "SELECT zone_name, COUNT(*) as count 
        FROM access_logs 
        WHERE event_id = $event_id 
        AND DATE(access_time) = '$today'
        GROUP BY zone_name";
$zone_counts = $conn->query($sql);

// Define available zones
$zones = ['Main Hall', 'VIP Area', 'Workshop Room', 'Dining Area', 'Exhibition Hall'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Zones Management - Event Management System</title>
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
                            <a href="admin_dashboard.php" class="text-xl font-semibold">Access Zones Management</a>
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
                <!-- Zone Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <?php while($zone = $zone_counts->fetch_assoc()): ?>
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    <?php echo htmlspecialchars($zone['zone_name']); ?> Today
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                    <?php echo $zone['count']; ?>
                                </dd>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Access Log Form -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Record Zone Access</h3>
                        
                        <?php if (isset($error)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($success)): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="participant_id" class="block text-sm font-medium text-gray-700">
                                    Participant ID
                                </label>
                                <input type="text" name="participant_id" id="participant_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    autofocus>
                            </div>

                            <div>
                                <label for="zone_name" class="block text-sm font-medium text-gray-700">
                                    Zone
                                </label>
                                <select name="zone_name" id="zone_name" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <?php foreach ($zones as $zone): ?>
                                        <option value="<?php echo $zone; ?>"><?php echo $zone; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Record Access
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recent Access Logs -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Access Logs</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Participant ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Zone
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Access Time
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while($access = $recent_access->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($access['participant_id']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($access['zone_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y H:i:s', strtotime($access['access_time'])); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-focus the participant ID field after successful access log
        <?php if (isset($success)): ?>
        document.getElementById('participant_id').focus();
        <?php endif; ?>
    </script>
</body>
</html>
