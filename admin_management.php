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

// Handle admin creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $permissions = json_encode($_POST['permissions'] ?? []);
    
    // Check if username already exists
    $check_sql = "SELECT id FROM event_admins WHERE username = '$username'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $error = "Username already exists";
    } else {
        $sql = "INSERT INTO event_admins (event_id, username, password, permissions) 
                VALUES ($event_id, '$username', '$password', '$permissions')";
        
        if ($conn->query($sql)) {
            $success = "Event admin created successfully";
        } else {
            $error = "Error creating admin: " . $conn->error;
        }
    }
}

// Fetch existing admins for this event
$sql = "SELECT * FROM event_admins WHERE event_id = $event_id";
$admins = $conn->query($sql);

// Fetch available modules for this event
$sql = "SELECT * FROM modules WHERE event_id = $event_id AND status = 'enabled'";
$modules = $conn->query($sql);
$available_modules = [];
while ($module = $modules->fetch_assoc()) {
    $available_modules[] = $module;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - Event Management System</title>
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
                    <!-- Create Admin Form -->
                    <div class="bg-white shadow rounded-lg mb-6">
                        <div class="px-4 py-5 sm:p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">
                                Create Event Admin - <?php echo htmlspecialchars($event['name']); ?>
                            </h2>

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
                                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                    <input type="text" name="username" id="username" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    <input type="password" name="password" id="password" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Module Permissions</label>
                                    <div class="space-y-2">
                                        <?php foreach ($available_modules as $module): ?>
                                            <div class="flex items-center">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?php echo htmlspecialchars($module['module_name']); ?>"
                                                       id="module_<?php echo $module['id']; ?>"
                                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="module_<?php echo $module['id']; ?>" 
                                                       class="ml-2 block text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($module['module_name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <a href="index.php" 
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Cancel
                                    </a>
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Create Admin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Admins List -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Existing Event Admins</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Username
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Permissions
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if ($admins->num_rows > 0): ?>
                                            <?php while($admin = $admins->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($admin['username']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?php 
                                                        $perms = json_decode($admin['permissions'], true);
                                                        echo implode(', ', $perms ?? []);
                                                        ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <a href="edit_admin.php?id=<?php echo $admin['id']; ?>" 
                                                           class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    No admins found
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
