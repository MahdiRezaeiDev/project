<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['searchCustomers'])) {
    $pattern = trim($_POST['pattern']);
    $stmt = PDO_CONNECTION->prepare("SELECT DISTINCT 
        c.id, 
        c.name, 
        c.family, 
        c.phone, 
        CASE 
            WHEN p.customer_id IS NOT NULL THEN 1 
            ELSE 0 
        END AS has_phone,
        pr.id AS phone_relation_id,
        pr.is_verified,
        pr.admin_des,
        pr.finance_des
    FROM callcenter.customer c
    LEFT JOIN callcenter.phones p ON c.id = p.customer_id
    LEFT JOIN callcenter.phones_relation pr ON p.relation_id = pr.id
    WHERE 
        c.name LIKE :pattern 
        OR c.family LIKE :pattern 
        OR c.phone LIKE :pattern;");

    $stmt->execute([':pattern' => '%' . $pattern . '%']);
    $allCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($allCustomers) > 0) {
        foreach ($allCustomers as $item) {
            if ($item['has_phone']) { ?>
                <div class="w-full flex justify-between items-center shadow-md hover:shadow-lg 
                    rounded-md px-4 py-3 mb-2 border-1 border-gray-300" id="search-<?= $item['id'] ?>">
                    <p class=' text-sm font-semibold text-gray-600'><?= $item['name'] . " " . $item['family'] ?></p>
                    <p class=' text-sm font-semibold text-gray-600'><?= $item['phone'] ?></p>
                    <i
                        data-name="<?= $item['name'] . " " . $item['family'] ?>"
                        data-id="<?= $item['phone_relation_id'] ?>"
                        data-pattern="<?= $item['phone_relation_id'] ?>"
                        data-verified="<?= $item['is_verified'] ?>"
                        data-admin_des="<?= $item['admin_des'] ?>"
                        data-finance_des="<?= $item['finance_des'] ?>"
                        class='material-icons add text-blue-600 cursor-pointer rounded-circle hover:bg-gray-200' onclick="load(this)">cloud_download
                    </i>
                </div>
            <?php
            } else {
            ?>
                <div class='w-full flex justify-between items-center shadow-md hover:shadow-lg rounded-md px-4 py-3 mb-2 border-1 border-gray-300' id="search-<?= $item['id'] ?>">
                    <p class=' text-sm font-semibold text-gray-600'><?= $item['name'] . " " . $item['family'] ?></p>
                    <p class=' text-sm font-semibold text-gray-600'><?= $item['phone'] ?></p>
                    <i data-name="<?= $item['name']  . " " . $item['family'] ?>" data-id="<?= $item['id'] ?>" data-phone="<?= $item['phone'] ?>" data-verified="<?= $item['is_verified'] ?>" class="add_element material-icons add text-green-600 cursor-pointer rounded-circle hover:bg-gray-200" onclick="add(this)">add_circle_outline
                    </i>
                </div>
<?php
            }
        }
    }
}

if (isset($_POST['load_relation'])) {
    $relation_id = $_POST['relation_id'];
    $stmt = PDO_CONNECTION->prepare("SELECT DISTINCT 
            c.id, 
            c.name, 
            c.family, 
            c.phone,
            pr.name as relation_name
        FROM callcenter.phones_relation pr
        JOIN callcenter.phones p ON pr.id = p.relation_id
        JOIN callcenter.customer c ON p.customer_id = c.id
        WHERE pr.id = :relation_id;");
    $stmt->execute([':relation_id' => $relation_id]);
    $allPhones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($allPhones);
}

if (isset($_POST['store_customers_phone'])) {
    $customer_name = $_POST['customer_name'];
    $is_verified = $_POST['is_verified'] == 'true' ? 1 : 0;
    $admin_des = $_POST['admin_des'];
    $finance_des = $_POST['finance_des'];
    $mode = $_POST['mode'];

    $selected_customers = json_decode($_POST['selected_customers'], true);
    $selected_index = array_map(fn($cust) => $cust['id'], $selected_customers);

    if ($mode == 'create') {
        // Insert into phones_relation
        $pattern_sql = "INSERT INTO callcenter.phones_relation (name, is_verified, admin_des, finance_des)
                        VALUES (:customer_name, :is_verified, :admin_des, :finance_des)";
        $stmt = PDO_CONNECTION->prepare($pattern_sql);

        if ($stmt->execute([
            ':customer_name' => $customer_name,
            ':is_verified' => $is_verified,
            ':admin_des' => $admin_des,
            ':finance_des' => $finance_des
        ])) {
            $last_id = PDO_CONNECTION->lastInsertId();

            // Link selected customers
            $similar_sql = "INSERT INTO callcenter.phones (relation_id, customer_id) VALUES (:relation_id, :customer_id)";
            $stmt = PDO_CONNECTION->prepare($similar_sql);

            foreach ($selected_index as $value) {
                $stmt->execute([
                    ':relation_id' => $last_id,
                    ':customer_id' => intval($value)
                ]);
            }
            echo 'true';
        } else {
            echo 'false';
        }
    } elseif ($mode == 'update') {
        // Expecting relation ID to be passed in update mode
        $relation_id = $_POST['relation_id'] ?? null;

        if (!$relation_id) {
            echo 'Missing relation ID';
            exit;
        }

        // Update phones_relation
        $update_sql = "UPDATE callcenter.phones_relation 
                       SET name = :customer_name, is_verified = :is_verified, admin_des = :admin_des, finance_des = :finance_des
                       WHERE id = :relation_id";
        $stmt = PDO_CONNECTION->prepare($update_sql);

        if ($stmt->execute([
            ':customer_name' => $customer_name,
            ':is_verified' => $is_verified,
            ':admin_des' => $admin_des,
            ':finance_des' => $finance_des,
            ':relation_id' => $relation_id
        ])) {
            // Remove old links
            $delete_sql = "DELETE FROM callcenter.phones WHERE relation_id = :relation_id";
            $stmt = PDO_CONNECTION->prepare($delete_sql);
            $stmt->execute([':relation_id' => $relation_id]);

            // Insert new links
            $insert_sql = "INSERT INTO callcenter.phones (relation_id, customer_id) VALUES (:relation_id, :customer_id)";
            $stmt = PDO_CONNECTION->prepare($insert_sql);

            foreach ($selected_index as $value) {
                $stmt->execute([
                    ':relation_id' => $relation_id,
                    ':customer_id' => intval($value)
                ]);
            }
            echo 'true';
        } else {
            echo 'false';
        }
    }
}

function extract_id($array)
{
    $selected_index = [];
    foreach ($array as $value) {
        array_push($selected_index, $value->id);
    }
    return array_unique($selected_index);
}
