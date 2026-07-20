<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/admin_auth.php';

header('Content-Type: application/json');
requireAdminLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'update_general') {
    $usdt = $_POST['company_usdt_address'] ?? '';
    $min_withdraw = floatval($_POST['min_withdrawal_amount'] ?? 0);
    $max_withdraw = floatval($_POST['max_withdrawal_amount'] ?? 0);
    $reg_enabled = isset($_POST['registration_enabled']) ? intval($_POST['registration_enabled']) : 0;
    $with_enabled = isset($_POST['withdrawal_enabled']) ? intval($_POST['withdrawal_enabled']) : 0;
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'company_usdt_address'");
        $stmt->execute([$usdt]);
        
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'min_withdrawal_amount'");
        $stmt->execute([$min_withdraw]);
        
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'max_withdrawal_amount'");
        $stmt->execute([$max_withdraw]);
        
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'registration_enabled'");
        $stmt->execute([$reg_enabled]);

        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'withdrawal_enabled'");
        $stmt->execute([$with_enabled]);
        
        // Handle QR Code file upload
        if (isset($_FILES['company_qr_code']) && $_FILES['company_qr_code']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['company_qr_code']['tmp_name'];
            $fileName = $_FILES['company_qr_code']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadFileDir = __DIR__ . '/../../assets/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                $destPath = $uploadFileDir . 'company_qr.' . $fileExtension;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $dbPath = 'assets/company_qr.' . $fileExtension;
                    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'company_qr_code_path'");
                    $stmt->execute([$dbPath]);
                }
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'update_wallet_fee') {
    $walletType = $_POST['wallet_type'] ?? '';
    $internalFee = floatval($_POST['internal_transfer_fee_percent'] ?? 0);
    $externalFee = floatval($_POST['external_withdrawal_fee_percent'] ?? 0);
    
    try {
        $stmt = $pdo->prepare("UPDATE wallet_configurations SET internal_transfer_fee_percent = ?, external_withdrawal_fee_percent = ? WHERE wallet_type = ?");
        $stmt->execute([$internalFee, $externalFee, $walletType]);
        echo json_encode(['success' => true, 'message' => 'Fee updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
