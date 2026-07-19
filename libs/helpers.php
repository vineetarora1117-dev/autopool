<?php
function generateUserId($pdo) {
    $isUnique = false;
    $userId = '';
    while (!$isUnique) {
        $randomNumber = sprintf("%06d", mt_rand(1, 999999));
        $userId = 'SA' . $randomNumber;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        if ($stmt->fetchColumn() == 0) {
            $isUnique = true;
        }
    }
    return $userId;
}

function addTransaction($pdo, $userId, $type, $amount, $walletType, $status, $narration, $relatedUserId = null) {
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, type, amount, wallet_type, status, narration, related_user_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([$userId, $type, $amount, $walletType, $status, $narration, $relatedUserId]);
}

function updateFinancialSummary($pdo, $userId, $column, $amount, $operation = 'add') {
    $allowedColumns = [
        'total_earnings', 'total_withdrawal', 'available_balance', 
        'direct_income', 'level_income', 'autopool_income', 
        'booster_income', 'royalty_income'
    ];
    if (!in_array($column, $allowedColumns)) {
        throw new Exception("Invalid column for financial summary update.");
    }
    
    $operator = $operation === 'add' ? '+' : '-';
    $sql = "UPDATE user_financial_summary SET $column = $column $operator :amount WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['amount' => $amount, 'user_id' => $userId]);
}

function updateCompanyLedger($pdo, $column, $amount, $operation = 'add') {
    $allowedColumns = [
        'total_revenue', 'total_payouts', 'total_admin_charge', 
        'total_tds', 'company_profit', 'total_system_balance'
    ];
    if (!in_array($column, $allowedColumns)) {
        throw new Exception("Invalid column for company ledger update.");
    }

    $operator = $operation === 'add' ? '+' : '-';
    $sql = "UPDATE company_ledger SET $column = $column $operator :amount WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['amount' => $amount]);
}

function formatCurrency($amount) {
    return '$' . number_format((float)$amount, 2, '.', '');
}

function getPackageConfig() {
    require_once __DIR__ . '/config.php';
    global $PACKAGE_CONFIG;
    return $PACKAGE_CONFIG;
}

function getBoosterConfig() {
    require_once __DIR__ . '/config.php';
    global $BOOSTER_CONFIG;
    return $BOOSTER_CONFIG;
}
?>
