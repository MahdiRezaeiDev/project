<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';


if (isLogin()) {
    header("Location: ../inventory/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define variables and initialize with empty values
    $username = $password = "";
    $username_err = $password_err = $login_err = "";
    // Check if username is empty
    if (empty(sanitizeInput($_POST["username"]))) {
        $username_err = "لطفا نام کاربری خود را وارد کنید.";
    } else {
        $username = sanitizeInput($_POST["username"]);
    }

    // Check if password is empty
    if (empty(sanitizeInput($_POST["password"]))) {
        $password_err = "لطفا رمز عبور خود را وارد کنید.";
    } else {
        $password = sanitizeInput($_POST["password"]);
    }

    $financialYear = $_POST['financialYear'];
    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        try {
            // Prepare a select statement
            $sql = "SELECT id, username, name, family, password, roll, profile FROM users WHERE username = :username";

            // Prepare the SQL statement
            $stmt = PDO_CONNECTION->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if username exists
                if ($stmt->rowCount() == 1) {
                    // Fetch result
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Extract data from the user
                    $id = $user['id'];
                    $hashed_password = $user['password'];
                    $roll = $user['roll'];

                    // Verify password
                    if (password_verify($password, $hashed_password)) {
                        // Regenerate the session ID after a successful login
                        session_regenerate_id(true);
                        // Calculate the expiration timestamp for 6 AM the next day
                        $expiration_time = strtotime("6AM tomorrow");

                        $_SESSION["isLogin"] = true;
                        $_SESSION["user"] = $user;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["roll"] = $roll;
                        $_SESSION["financialYear"] = $financialYear;
                        $_SESSION["expiration_time"] = $expiration_time;

                        $notAllowed = array();
                        $auth = json_decode(getUserAuthority($id), true);

                        foreach ($auth as $key => $value) {
                            if (!$value) {
                                array_push($notAllowed, $key);
                            }
                        }

                        $_SESSION['not_allowed'] = $notAllowed;

                        clearModifiedAuth($id);

                        sendAjaxRequest($id, $username, $financialYear);
                    } else {
                        // Password is not valid, display a generic error message
                        $login_err = "رمز عبور یا اسم کاربری اشتباه است.";
                        sendLoginAttemptAlert($username, $password);
                    }
                } else {
                    // Username doesn't exist, display a generic error message
                    $login_err = "رمز عبور یا اسم کاربری اشتباه است.";
                    sendLoginAttemptAlert($username, $password);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        } catch (PDOException $e) {
            // Handle PDO exception
            echo "Error: " . $e->getMessage();
        }
    }
}

function validateInput($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

function sanitizeInput($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

function sendAjaxRequest($id, $username, $financialYear)
{
    $postData = array(
        "sendMessage" => "local",
        "id" => $id,
        "username" => $username,
        "time" => date("Y-m-d h:i:sa"),
        "host" => $_SERVER['HTTP_HOST'],
        "ip" => $_SERVER['REMOTE_ADDR']
    );

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://auto.yadak.center/");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Optional: timeout in 10 seconds

    $result = curl_exec($ch);

    // Check for connection error
    if (curl_errno($ch)) {
        $errorMessage = curl_error($ch);

        // Log the error (optional)
        error_log("cURL Error: " . $errorMessage);

        // Redirect user to an offline-safe page
        curl_close($ch);
        header("Location: ../../views/inventory/index.php");
        exit();
    }

    curl_close($ch);

    // Continue with normal redirect
    header("Location: ../../views/inventory/index.php");
    exit();
}


function sendLoginAttemptAlert($username, $password)
{
    // Prepare data for POST request
    $postData = array(
        "sendMessage" => "attempt",
        "origen" => "local",
        "host" => $_SERVER['HTTP_HOST'],
        "ip" => $_SERVER['REMOTE_ADDR'],
        "time" => date("Y-m-d h:i:sa"),
        "username" => $username,
        "password" => $password
    );

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, "http://auto.yadak.center/");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request
    $result = curl_exec($ch);

    // Close cURL session
    curl_close($ch);
}

function getUserAuthority($id)
{
    try {
        $users_sql = "SELECT user_authorities AS auth FROM yadakshop.authorities WHERE user_id = :id";
        $stmt = PDO_CONNECTION->prepare($users_sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['auth'];
    } catch (PDOException $e) {
        // Handle PDO exception
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function clearModifiedAuth($id)
{
    try {
        $sql = "UPDATE yadakshop.authorities SET modified = 0 WHERE user_id = :id";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        // Handle PDO exception
        echo "Error: " . $e->getMessage();
        return false;
    }
}
