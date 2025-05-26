<?php
require_once 'config.php';
checkLogin();

// Check if user is event admin and has Goodies Counter permission
if ($_SESSION['user_type'] !== 'event_admin' || !in_array('Goodies Counter', $_SESSION['permissions'])) {
    header("Location: admin_login.php");
    exit();
}

$event_id = $_SESSION['event_id'];

// Handle goodies distribution submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $participant_id = sanitize($_POST['participant_id']);
    $item_name = sanitize($_POST['item_name']);
    
    // Check if participant is checked in
    $check_sql = "SELECT id FROM checkin_logs WHERE event_id = $event_id AND participant_id = '$participant_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows === 0) {
        $error = "Participant not checked in to the event";
    } else {
        // Check if participant already received this item
        $check_sql = "SELECT id FROM goodies_logs 
                     WHERE event_id = $event_id 
                     AND participant_id = '$participant_id' 
                     AND item_name = '$item_name'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error = "Participant has already received this item";
        } else {
            $sql = "INSERT INTO goodies_logs (event_id, participant_id, item_name) 
                    VALUES ($event_id, '$participant_id', '$item_name')";
            if ($conn->query($sql)) {
                $success = "Goodies distribution recorded successfully";
            } else {
                $error = "Error recording distribution: " . $conn->error;
            }
        }
    }
}

// Fetch recent distributions
$sql = "SELECT * FROM goodies_logs WHERE event_id = $event_id ORDER BY collected_time DESC LIMIT 10";
$recent_distributions = $conn->query($sql);

// Get total distributions for today
$today = date('Y-m-d');
$sql = "SELECT item_name, COUNT(*) as total 
        FROM goodies_logs 
        WHERE event_id = $event_id 
        AND DATE(collected_time) = '$today'
        GROUP BY item_name";
$today_stats = $conn->query($sql);

// Define available goodies items
$goodies_items = [
    'Welcome Kit',
    'T-Shirt',
    'Conference Badge',
    'Swag Bag',
    'Certificate'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goodies Counter Management - Event Management System</title>
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
                            <a href="admin_dashboard.php" class="text-xl font-semibold">Goodies Counter Management</a>
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
                <!-- Today's Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <?php while($stat = $today_stats->fetch_assoc()): ?>
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    <?php echo htmlspecialchars($stat['item_name']); ?> Today
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                    <?php echo $stat['total']; ?>
                                </dd>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Distribution Form -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Record Goodies Distribution</h3>
                        
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
                                <label for="item_name" class="block text-sm font-medium text-gray-700">
                                    Item
                                </label>
                                <select name="item_name" id="item_name" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <?php foreach ($goodies_items as $item): ?>
                                        <option value="<?php echo $item; ?>"><?php echo $item; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Record Distribution
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recent Distributions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Distributions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Participant ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Collection Time
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while($dist = $recent_distributions->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($dist['participant_id']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($dist['item_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y H:i:s', strtotime($dist['collected_time'])); ?>
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
        // Auto-focus the participant ID field after successful distribution
        <?php if (isset($success)): ?>
        document.getElementById('participant_id').focus();
        <?php endif; ?>
    </script>
</body>
</html>
