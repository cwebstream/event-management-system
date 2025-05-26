<?php
require_once 'config.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $start_date = sanitize($_POST['start_date']);
    $end_date = sanitize($_POST['end_date']);
    
    $sql = "INSERT INTO events (name, description, start_date, end_date) 
            VALUES ('$name', '$description', '$start_date', '$end_date')";
    
    if ($conn->query($sql)) {
        $event_id = $conn->insert_id;
        
        // Create default modules for the event
        $default_modules = ['Check-In', 'Access Zones', 'F&B Counter', 'Goodies Counter', 'Wallet System', 'Analytics'];
        foreach ($default_modules as $module) {
            $sql = "INSERT INTO modules (event_id, module_name) VALUES ($event_id, '$module')";
            $conn->query($sql);
        }
        
        header("Location: modules.php?event_id=" . $event_id);
        exit();
    } else {
        $error = "Error creating event: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Event Management System</title>
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
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Create New Event</h2>
                            
                            <?php if (isset($error)): ?>
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Event Name</label>
                                    <input type="text" name="name" id="name" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="4"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <input type="date" name="start_date" id="start_date" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                        <input type="date" name="end_date" id="end_date" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-inI have created the database schema, configuration file, login page, and the main dashboard (index.php) for the Super Admin.

Next steps in the plan:
- Create the event creation page (create_event.php) to allow Super Admin to create events and store them in the database.
- Create the modules management page (modules.php) to enable/disable modules per event.
- Create event admin management page (admin_management.php) to generate event admins with login and permissions.
- Create event admin login and dashboard pages.
- Implement the individual modules (Check-In, Access Zones, F&B Distribution, Goodies Counter, Wallet System, Analytics Dashboard) with their respective MySQL tables.

Please confirm if you want me to proceed with this plan or if you want to adjust or add anything.
