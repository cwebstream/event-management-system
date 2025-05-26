<?php
require_once 'config.php';
checkLogin();

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Fetch event details
$sql = "SELECT * FROM events WHERE id = $event_id";
$event_result = $conn->query($sql);
$event = $event_result->fetch_assoc();

if (!$event) {
    header("Location: index.php");
    exit();
}

// Handle module status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['modules'] as $module_id => $status) {
        $status = $status === 'enabled' ? 'enabled' : 'disabled';
        $sql = "UPDATE modules SET status = '$status' WHERE id = $module_id AND event_id = $event_id";
        $conn->query($sql);
    }
    $success = "Modules updated successfully";
}

// Fetch modules for this event
$sql = "SELECT * FROM modules WHERE event_id = $event_id";
$modules = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Management - Event Management System</title>
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
                            <a href="index.php" class="text-xl font-semibold">Event Management</a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">Super Admin</span>
                        <a href="logout.php" class="text-red-600 hover:text-red-700">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="max-w-3xl mx-auto">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">
                                Module Management - <?php echo htmlspecialchars($event['name']); ?>
                            </h2>

                            <?php if (isset($success)): ?>
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                    <?php echo $success; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="space-y-6">
                                <div class="space-y-4">
                                    <?php while($module = $modules->fetch_assoc()): ?>
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($module['module_name']); ?>
                                                </h3>
                                            </div>
                                            <div class="flex items-center">
                                                <select name="modules[<?php echo $module['id']; ?>]" 
                                                        class="block w-32 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option value="enabled" <?php echo $module['status'] === 'enabled' ? 'selected' : ''; ?>>
                                                        Enabled
                                                    </option>
                                                    <option value="disabled" <?php echo $module['status'] === 'disabled' ? 'selected' : ''; ?>>
                                                        Disabled
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <a href="index.php" 
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Cancel
                                    </a>
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Save Changes
                                    </button>
                                </div>
                            </form>

                            <div class="mt-8 border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Next Steps</h3>
                                <div class="space-y-3">
                                    <a href="admin_management.php?event_id=<?php echo $event_id; ?>" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Generate Event Admin
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
