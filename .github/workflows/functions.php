<?php
session_start();

define('BOOKS_FILE', 'books.json');
define('USERS_FILE', 'users.json');
define('TRANSACTIONS_FILE', 'transactions.json');

function loadData() {
    $booksData = [];
    $usersData = [];
    $transData = [];

    // Load Books
    if (file_exists(BOOKS_FILE)) {
        $jsonContext = file_get_contents(BOOKS_FILE);
        $booksData = json_decode($jsonContext, true) ?? [];
    }
    
    // Load Users
    if (file_exists(USERS_FILE)) {
        $jsonContext = file_get_contents(USERS_FILE);
        $usersData = json_decode($jsonContext, true) ?? [];
    }

    // Load Transactions
    if (file_exists(TRANSACTIONS_FILE)) {
        $jsonContext = file_get_contents(TRANSACTIONS_FILE);
        $transData = json_decode($jsonContext, true) ?? [];
    }

    // Merge into single structure for app compatibility
    return [
        'users' => $usersData['users'] ?? [],
        'books' => $booksData['books'] ?? [],
        'peminjaman' => $transData['peminjaman'] ?? [],
        'pengembalian' => $transData['pengembalian'] ?? []
    ];
}

function saveData($data) {
    // Split Data
    $booksPayload = ['books' => $data['books']];
    $usersPayload = ['users' => $data['users']];
    $transPayload = [
        'peminjaman' => $data['peminjaman'],
        'pengembalian' => $data['pengembalian']
    ];

    file_put_contents(BOOKS_FILE, json_encode($booksPayload, JSON_PRETTY_PRINT));
    file_put_contents(USERS_FILE, json_encode($usersPayload, JSON_PRETTY_PRINT));
    file_put_contents(TRANSACTIONS_FILE, json_encode($transPayload, JSON_PRETTY_PRINT));
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isset($_SESSION['user']) && ($_SESSION['user']['status'] === 'Administrator' || (isset($_SESSION['user']['isAdmin']) && $_SESSION['user']['isAdmin'] === true));
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// Flash messages helpers
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Fine Calculation Logic
function calculateFine($dueDate, $returnDate) {
    $due = new DateTime($dueDate);
    $return = new DateTime($returnDate);
    
    if ($return > $due) {
        $diff = $return->diff($due);
        $daysLate = $diff->days;
        return $daysLate * 1000; // Rp 1.000 per day
    }
    return 0;
}

// Get Active Loan Count for User
function getActiveLoanCount($userId, $data) {
    $count = 0;
    foreach ($data['peminjaman'] as $loan) {
        // Check by ID if available, otherwise by name (legacy support)
        if ((isset($loan['user_id']) && $loan['user_id'] == $userId) || 
            (!isset($loan['user_id']) && $loan['nama'] == $userId)) { // If userId pass is name
            $count++;
        }
    }
    return $count;
}

// Check for overdue loans for user
function checkOverdueLoans($user, $data) {
    if (!$user) return [];
    
    $overdueItems = [];
    $today = new DateTime();
    
    foreach ($data['peminjaman'] as $loan) {
        if ($loan['nama'] === $user['nama']) { // Match by name as primary for now
            $dueDate = new DateTime($loan['jatuhTempo']);
            if ($today > $dueDate) {
                // Calculate days late
                $diff = $today->diff($dueDate);
                $denda = $diff->days * 1000;
                $loan['days_late'] = $diff->days;
                $loan['current_fine'] = $denda;
                $overdueItems[] = $loan;
            }
        }
    }
    return $overdueItems;
}
?>
