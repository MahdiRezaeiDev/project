<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';


if (isset($_POST['SYNC'])) {
    echo json_encode(updateSyncedCustomers());
}

function updateSyncedCustomers()
{
    $stmt = PDO_CONNECTION->prepare("UPDATE callcenter.customer SET sync = 1");
    return $stmt->execute();
}


if (isset($_POST['sveContacts'])) {
    $contacts = json_decode($_POST['sveContacts'], true);

    saveContacts($contacts, PDO_CONNECTION);
}

function saveContacts($contacts, $pdo)
{
    $inserted = 0;

    // Prepare statement to check if the phone already exists
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM callcenter.customer WHERE phone = :phone");
    // Prepare the insert statement
    $insertStmt = $pdo->prepare("INSERT INTO callcenter.customer (name, phone) VALUES (:name, :phone)");

    // Iterate through the contacts
    foreach ($contacts as $contact) {
        if (isset($contact['name'], $contact['phone'])) {
            try {
                $name = $contact['name'] . ' ' . ($contact['family'] ?? ''); // Combine name and family if available
                $phone = (string) $contact['phone']; // Ensure phone is treated as string

                // Check if the phone number already exists in the database
                $checkStmt->execute([':phone' => $phone]);
                $phoneExists = $checkStmt->fetchColumn() > 0;

                if (!$phoneExists) {
                    // If the phone doesn't exist, insert the contact
                    $insertStmt->execute([
                        ':name' => $name,
                        ':phone' => $phone
                    ]);
                    $inserted++;
                }
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to save contact: ' . $e->getMessage()
                ]);
                exit;
            }
        }
    }

    // Return success message
    echo json_encode([
        'success' => true,
        'message' => "$inserted contacts saved successfully."
    ]);
}

if (isset($_POST['getContacts'])) {
    $pattern = trim($_POST['pattern']);
    $stmt = PDO_CONNECTION->prepare("SELECT * FROM callcenter.customer WHERE name LIKE :pattern OR family LIKE :pattern OR phone LIKE :pattern");
    $stmt->execute([':pattern' => '%' . $pattern . '%']);
    $allCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($allCustomers);
} else {
    echo json_encode($_POST);
}
