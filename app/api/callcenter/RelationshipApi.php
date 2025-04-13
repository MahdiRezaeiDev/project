<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../utilities/jdf.php';

if (isset($_POST['search_goods_for_relation'])) {
    $pattern = $_POST['pattern'];

    $sql = "SELECT * FROM yadakshop.nisha WHERE partnumber LIKE :partnumber";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindValue(':partnumber', $pattern . '%');
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $searched_ids = [];
    $nisha = [];

    if (count($result) > 0) {
        foreach ($result as $item) {
            $searched_ids[] = $item['id'];
            $nisha[] = $item;
        }


        $similar_sql = "SELECT nisha_id, pattern_id FROM shop.similars WHERE nisha_id IN (" . implode(',', $searched_ids) . ")";
        $similar = PDO_CONNECTION->prepare($similar_sql);
        $similar->execute();
        $similar = $similar->fetchAll(PDO::FETCH_ASSOC);

        $similar_ids = [];

        if (count($similar) > 0) {
            foreach ($similar as $item) {
                array_push($similar_ids, ['pattern_id' => $item['pattern_id'], 'nisha_id' => $item['nisha_id']]);
            }
        }

        $final_result = [];

        foreach ($nisha as $key => $value) {
            $id = $value['id'];

            $get_nisha = null;

            foreach ($similar_ids as $item) {
                if ($item['nisha_id'] == $id) {
                    $get_nisha = $item['pattern_id'];
                }
            }
            array_push($final_result, ['id' => $id, 'partNumber' => $value['partnumber'], 'pattern' => $get_nisha]);
        }




        if (count($final_result) > 0) {
            foreach ($final_result as $item) {
                if ($item['pattern']) { ?>
                    <div class="w-full flex justify-between items-center shadow-md hover:shadow-lg 
                        rounded-md px-4 py-3 mb-2 border-1 border-gray-300" id="search-<?php echo  $item['id'] ?>">
                        <p class=' text-sm font-semibold text-gray-600'><?php echo $item['partNumber'] ?></p>
                        <i data-id="<?php echo $item['id'] ?>" data-pattern="<?php echo $item['pattern'] ?>" data-partNumber="<?php echo $item['partNumber'] ?>" class='material-icons add text-blue-600 cursor-pointer rounded-circle hover:bg-gray-200' onclick="load(this)">cloud_download
                        </i>
                    </div>
                <?php
                } else {
                ?>
                    <div class='w-full flex justify-between items-center shadow-md hover:shadow-lg rounded-md px-4 py-3 mb-2 border-1 border-gray-300' id="search-<?php echo $item['id'] ?>">
                        <p class=' text-sm font-semibold text-gray-600'><?php echo $item['partNumber'] ?></p>
                        <i data-id="<?php echo $item['id'] ?>" data-partNumber="<?php echo $item['partNumber'] ?>" class="add_element material-icons add text-green-600 cursor-pointer rounded-circle hover:bg-gray-200" onclick="add(this)">add_circle_outline
                        </i>
                    </div>
                <?php
                }
                ?>
        <?php }
        }
    } else {
        ?>
        <div class='w-full text-center shadow-md hover:shadow-lg rounded-md px-4 py-3 mb-2 border-1 border-gray-300''>
                <i class=' material-icons text-red-500'>error</i>
            <br />
            <p class='text-sm font-semibold text-red-500'>کد وارد شده در سیستم موجود نمی باشد</p>
        </div>
<?php

    }
}

if (isset($_POST['store_relation'])) {

    $relation_name = $_POST['relation_name'];
    $price = null;
    $cars = json_decode($_POST['cars']);
    $status = $_POST['status'];

    // This values are for to set an alert for goods in specific relation 
    // for specific inventory
    $original = $_POST['original'];
    $fake = $_POST['fake'];

    // This values are for to set an alert for goods in specific relation 
    // for over all goods in all inventories
    $original_all = $_POST['original_all'];
    $fake_all = $_POST['fake_all'];

    $description = $_POST['description'];
    $mode = $_POST['mode'];
    $pattern_id = $_POST['pattern_id'];
    $selected_goods = json_decode($_POST['selected_goods']);
    $serial = $_POST['serial'];

    if ($mode === 'create') {
        $selected_index = extract_id($selected_goods);

        $selectedCars = $cars;

        // Create the pattern record
        $pattern_sql = "INSERT INTO shop.patterns (name, price, serial, status_id, created_at, description)
                        VALUES (:relation_name, :price, :serial, :status, NOW(), :description)";
        $stmt = PDO_CONNECTION->prepare($pattern_sql);
        if ($stmt->execute([
            ':relation_name' => $relation_name,
            ':price' => $price,
            ':serial' => $serial,
            ':status' => $status,
            ':description' => $description
        ])) {
            $last_id = PDO_CONNECTION->lastInsertId(); // Latest pattern ID

            // Insert inventory alert for specific inventory
            $stock_id = 9;
            $limit_sql = "INSERT INTO shop.good_limit_inventory (pattern_id, original, fake, user_id, stock_id)
                          VALUES (:pattern_id, :original, :fake, :user_id, :stock_id)";
            $stmt = PDO_CONNECTION->prepare($limit_sql);
            $stmt->execute([
                ':pattern_id' => $last_id,
                ':original' => $original,
                ':fake' => $fake,
                ':user_id' => $_SESSION['id'],
                ':stock_id' => $stock_id
            ]);

            // Insert goods alert within all the available stocks
            $limit_all_sql = "INSERT INTO shop.good_limit_all (pattern_id, original, fake, user_id)
                              VALUES (:pattern_id, :original_all, :fake_all, :user_id)";
            $stmt = PDO_CONNECTION->prepare($limit_all_sql);
            $stmt->execute([
                ':pattern_id' => $last_id,
                ':original_all' => $original_all,
                ':fake_all' => $fake_all,
                ':user_id' => $_SESSION['id']
            ]);

            // Loop over the selected goods and add to the specific pattern ID
            $similar_sql = "INSERT INTO shop.similars (pattern_id, nisha_id) VALUES (:pattern_id, :nisha_id)";
            $stmt = PDO_CONNECTION->prepare($similar_sql);
            foreach ($selected_index as $value) {
                $stmt->execute([
                    ':pattern_id' => $last_id,
                    ':nisha_id' => intval($value)
                ]);
            }

            // Loop over the selected brand of cars and add to the specific pattern ID
            $car_sql = "INSERT INTO shop.patterncars (pattern_id, car_id) VALUES (:pattern_id, :car_id)";
            $stmt = PDO_CONNECTION->prepare($car_sql);
            foreach ($selectedCars as $car) {
                $stmt->execute([
                    ':pattern_id' => $last_id,
                    ':car_id' => intval($car)
                ]);
            }

            echo 'true';
        } else {
            echo 'false';
        }
    }
    if ($mode === 'update') {
        $pattern_sql = "SELECT * FROM shop.patterns WHERE id = :pattern_id";
        $stmt = PDO_CONNECTION->prepare($pattern_sql);
        $stmt->execute([':pattern_id' => $pattern_id]);
        $is_exist = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($is_exist) {
            $similar_sql = "SELECT nisha_id FROM shop.similars WHERE pattern_id = :pattern_id";
            $stmt = PDO_CONNECTION->prepare($similar_sql);
            $stmt->execute([':pattern_id' => $pattern_id]);
            $all_simillers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (existLimit($pattern_id)) {
                // Update the Inventories limit for goods alert for specific pattern
                $updateInventoryLimit = "UPDATE shop.good_limit_inventory SET original = :original, fake = :fake WHERE pattern_id = :pattern_id";
                $stmt = PDO_CONNECTION->prepare($updateInventoryLimit);
                $stmt->execute([
                    ':original' => $original,
                    ':fake' => $fake,
                    ':pattern_id' => $pattern_id
                ]);

                // Update the overall alert for goods in specific relation
                $updateAllLimit = "UPDATE shop.good_limit_all SET original = :original_all, fake = :fake_all WHERE pattern_id = :pattern_id";
                $stmt = PDO_CONNECTION->prepare($updateAllLimit);
                $stmt->execute([
                    ':original_all' => $original_all,
                    ':fake_all' => $fake_all,
                    ':pattern_id' => $pattern_id
                ]);
            } else {
                $stock_id = 9;
                $limit_sql = "INSERT INTO shop.good_limit_inventory (pattern_id, original, fake, user_id, stock_id) VALUES (:pattern_id, :original, :fake, :user_id, :stock_id)";
                $stmt = PDO_CONNECTION->prepare($limit_sql);
                $stmt->execute([
                    ':pattern_id' => $pattern_id,
                    ':original' => $original,
                    ':fake' => $fake,
                    ':user_id' => $_SESSION['id'],
                    ':stock_id' => $stock_id
                ]);

                // Insert goods alert within all the available stocks (general goods amount alert)
                $limit_all_sql = "INSERT INTO shop.good_limit_all (pattern_id, original, fake, user_id) VALUES (:pattern_id, :original_all, :fake_all, :user_id)";
                $stmt = PDO_CONNECTION->prepare($limit_all_sql);
                $stmt->execute([
                    ':pattern_id' => $pattern_id,
                    ':original_all' => $original_all,
                    ':fake_all' => $fake_all,
                    ':user_id' => $_SESSION['id']
                ]);
            }

            // Get the id of all goods in a specific relation
            $selected_index = extract_id($selected_goods);

            $current = [];
            if (count($all_simillers) > 0) {
                foreach ($all_simillers as $item) {
                    $current[] = $item['nisha_id'];
                }
            }

            $cars_sql = "SELECT car_id FROM shop.patterncars WHERE pattern_id = :pattern_id";
            $stmt = PDO_CONNECTION->prepare($cars_sql);
            $stmt->execute([':pattern_id' => $pattern_id]);
            $all_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $current_cars = [];
            if (count($all_cars) > 0) {
                foreach ($all_cars as $item) {
                    $current_cars[] = $item['car_id'];
                }
            }

            $toAdd = toBeAdded($current, $selected_index);
            $toDelete = toBeDeleted($current, $selected_index);

            $subtractNew = array_diff($current, $toAdd);
            $updateRemaining = array_diff($subtractNew, $toDelete);

            $selectedCars = $cars;
            $carsToAdd = toBeAdded($current_cars, $selectedCars);
            $carsToDelete = toBeDeleted($current_cars, $selectedCars);

            $update_pattern_sql = "UPDATE shop.patterns SET name = :relation_name, price = :price, serial = :serial, status_id = :status, created_at = NOW() , description = :description WHERE id = :pattern_id";
            $stmt = PDO_CONNECTION->prepare($update_pattern_sql);
            $stmt->execute([
                ':relation_name' => $relation_name,
                ':price' => $price,
                ':serial' => $serial,
                ':status' => $status,
                ':description' => $description,
                ':pattern_id' => $pattern_id
            ]);

            if (count($toAdd) > 0) {
                $similar_sql = "INSERT INTO shop.similars (pattern_id, nisha_id) VALUES (:pattern_id, :nisha_id)";
                $stmt = PDO_CONNECTION->prepare($similar_sql);
                foreach ($toAdd as $value) {
                    $stmt->execute([
                        ':pattern_id' => $pattern_id,
                        ':nisha_id' => intval($value)
                    ]);
                }
            }
            if (count($toDelete) > 0) {
                $delete_similar_sql = "DELETE FROM shop.similars WHERE nisha_id = :nisha_id AND pattern_id = :pattern_id";
                $stmt = PDO_CONNECTION->prepare($delete_similar_sql);
                foreach ($toDelete as $value) {
                    $stmt->execute([
                        ':nisha_id' => intval($value),
                        ':pattern_id' => $pattern_id
                    ]);
                }
            }

            if (count($carsToAdd) > 0) {
                $car_sql = "INSERT INTO shop.patterncars (pattern_id, car_id) VALUES (:pattern_id, :car_id)";
                $stmt = PDO_CONNECTION->prepare($car_sql);
                foreach ($carsToAdd as $value) {
                    $stmt->execute([
                        ':pattern_id' => $pattern_id,
                        ':car_id' => intval($value)
                    ]);
                }
            }
            if (count($carsToDelete) > 0) {
                $delete_cars_sql = "DELETE FROM shop.patterncars WHERE car_id = :car_id AND pattern_id = :pattern_id";
                $stmt = PDO_CONNECTION->prepare($delete_cars_sql);
                foreach ($carsToDelete as $value) {
                    $stmt->execute([
                        ':car_id' => intval($value),
                        ':pattern_id' => $pattern_id
                    ]);
                }
            }
            echo 'true';
        } else {
            echo 'false';
        }
    }
}

if (isset($_POST['load_relation'])) {
    $pattern = $_POST['pattern'];

    // Fetch similar items
    $similar_sql = "SELECT nisha_id FROM shop.similars WHERE pattern_id = :pattern_id";
    $stmt = PDO_CONNECTION->prepare($similar_sql);
    $stmt->execute([':pattern_id' => $pattern]);
    $similars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $final_result = [];
    if (count($similars) > 0) {
        foreach ($similars as $item) {
            $nisha_sql = "SELECT id, partnumber FROM yadakshop.nisha WHERE id = :nisha_id";
            $stmt = PDO_CONNECTION->prepare($nisha_sql);
            $stmt->execute([':nisha_id' => $item['nisha_id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $final_result[] = ['id' => $data['id'], 'partNumber' => $data['partnumber'], 'pattern' => $item['nisha_id']];
            }
        }

        // Fetch inventory limits
        $limit_sql = "SELECT original, fake FROM shop.good_limit_inventory WHERE pattern_id = :pattern_id";
        $stmt = PDO_CONNECTION->prepare($limit_sql);
        $stmt->execute([':pattern_id' => $pattern]);
        $limit = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch overall limits
        $limit_sql_all = "SELECT original AS original_all, fake AS fake_all FROM shop.good_limit_all WHERE pattern_id = :pattern_id";
        $stmt = PDO_CONNECTION->prepare($limit_sql_all);
        $stmt->execute([':pattern_id' => $pattern]);
        $limit_all = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set default values if limits are not found
        $yadakLimit = $limit ? $limit : ['original' => 0, 'fake' => 0];
        $allLimit = $limit_all ? $limit_all : ['original_all' => 0, 'fake_all' => 0];

        // Combine the limits
        $combinedLimits = array_merge($yadakLimit, $allLimit);

        // Return the result as JSON
        echo json_encode([$combinedLimits, $final_result]);
    }
}

if (isset($_POST['load_pattern_ifo'])) {

    $pattern = $_POST['pattern'];

    // Fetch car IDs associated with the pattern
    $car_sql = "SELECT car_id FROM shop.patterncars WHERE pattern_id = :pattern_id";
    $stmt = PDO_CONNECTION->prepare($car_sql);
    $stmt->execute([':pattern_id' => $pattern]);
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cars_id = [];
    if (count($cars) > 0) {
        foreach ($cars as $item) {
            $cars_id[] = $item['car_id'];
        }
    }

    // Fetch pattern information
    $pattern_sql = "SELECT * FROM shop.patterns WHERE id = :pattern_id";
    $stmt = PDO_CONNECTION->prepare($pattern_sql);
    $stmt->execute([':pattern_id' => $pattern]);
    $pattern_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the result as JSON
    echo json_encode(['pattern' => $pattern_info, 'cars' => $cars_id]);
}

function existLimit($pattern_id)
{
    $query = "SELECT * FROM shop.good_limit_inventory WHERE pattern_id = :pattern_id";
    $stmt = PDO_CONNECTION->prepare($query);
    $stmt->bindParam(':pattern_id', $pattern_id, PDO::PARAM_INT);
    $stmt->execute();

    $limitRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    return $limitRecord ? true : false;
}

function extract_id($array)
{
    $selected_index = [];
    foreach ($array as $value) {
        array_push($selected_index, $value->id);
    }
    $selected_index = array_unique($selected_index);
    return $selected_index;
}

function toBeAdded($existing, $newComer)
{
    $result = [];
    foreach ($newComer as $item) {
        if (!in_array($item, $existing)) {
            array_push($result, $item);
        }
    }
    return $result;
}

function toBeDeleted($existing, $newComer)
{
    $result = [];
    foreach ($existing as $item) {
        if (!in_array($item, $newComer)) {
            array_push($result, $item);
        }
    }
    return $result;
}
