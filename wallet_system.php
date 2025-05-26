<?php
require_once 'config.php';
checkLogin();

// Check if user is event admin and has Wallet System permission
if ($_SESSION['user_type'] !== 'event_admin' || !in_array('Wallet System', $_SESSION['permissions'])) {
    header("Location: admin_login.php");
    exit();
}

$event_id = $_SESSION['event_id'];

// Handle wallet transaction submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $participant_id = sanitize($_POST['participant_id']);
    $amount = (float)$_POST['amount'];
    $transaction_type = sanitize($_POST['transaction_type']);
    
    // Check if participant is checked in
    $check_sql = "SELECT id FROM checkin_logs WHERE event_id = $event_id AND participant_id = '$participant_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows === 0) {
        $error = "Participant not checked in to the event";
    } else {
        // Get current balance
        $balance_sql = "SELECT 
            SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END) as balance
            FROM wallet_transactions 
            WHERE event_id = $event_id AND participant_id = '$participant_id'";
        $balance_result = $conn->query($balance_sql);
        $current_balance = $balance_result->fetch_assoc()['balance'] ?? 0;

        // Check if sufficient balance for debit
        if ($transaction_type === 'debit' && $amount > $current_balance) {
            $error = "Insufficient balance";
        } else {
            $sql = "INSERT INTO wallet_transactions (event_id, participant_id, amount, transaction_type) 
                    VALUES ($event_id, '$participant_id', $amount, '$transaction_type')";
            if ($conn->query($sql)) {
                $success = "Transaction recorded successfully";
            } else {
                $error = "Error recording transaction: " . $conn->error;
            }
        }
    }
}

// Fetch recent transactions
$sql = "SELECT * FROM wallet_transactions WHERE event_id = $event_id ORDER BY transaction_time DESC LIMIT 10";
$recent_transactions = $conn->query($sql);

// Get total transactions for today
$today = date('Y-m-d');
$sql = "SELECT 
    SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_credits,
    SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_debits,
    COUNT(*) as total_transactions
    FROM wallet_transactions 
    WHERE event_id = $event_id 
    AND DATE(transaction_time) = '$today'";
$today_stats = $conn->query($sql)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet System Management - Event Management System</title>
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
                            <a href="admin_dashboard.php" class="text-xl font-semibold">Wallet System Management</a>
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
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Credits Today</dt>
                            <dd class="mt-1 text-3xl font-semibold text-green-600">
                                $<?php echo number_format($today_stats['total_credits'] ?? 0, 2); ?>
                            </dd>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Debits Today</dt>
                            <dd class="mt-1 text-3xl font-semibold text-red-600">
                                $<?php echo number_format($today_stats['total_debits'] ?? 0, 2); ?>
                            </dd>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Transactions Today</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                <?php echo $today_stats['total_transactions'] ?? 0; ?>
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Transaction Form -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Record Transaction</h3>
                        
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
                                <label for="amount" class="block text-sm font-medium text-gray-700">
                                    Amount ($)
                                </label>
                                <input type="number" name="amount" id="amount" required step="0.01" min="0.01"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="transaction_type" class="block text-sm font-medium text-gray-700">
                                    Transaction Type
                                </label>
                                <select name="transaction_type" id="transaction_type" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="credit">Credit (Add Funds)</option>
                                    <option value="debit">Debit (Use Funds)</option>
                                </select>
                            </div>

                            <div>
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Record Transaction
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Participant ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Time
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while($txn = $recent_transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($txn['participant_id']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($txn['amount'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $txn['transaction_type'] === 'credit' ? 
                                                        'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                    <?php echo ucfirst($txn['transaction_type']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y H:i:s', strtotime($txn['transaction_time'])); ?>
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
        // Auto-focus the participant ID field after successful transaction
        <?php if (isset($success)): ?>
        document.getElementById('participant_id').focus();
        <?php endif; ?>
    </script>
</body>
</html>
