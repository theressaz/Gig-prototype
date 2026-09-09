<?php
declare(strict_types=1);
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Gig');

$message = "";
$messageType = "";

if (isset($_SESSION["username"]) && isset($_SESSION["role"])) {
    if ($_SESSION["role"] === 'employer') {
        header("Location: dashboard-employer.php");
    } else {
        header("Location: dashboard-worker.php");
    }
    exit;
}

$dbOffline = false;
$pdo = null;

try {
    // Connect to MySQL server with a 2-second timeout to ensure instant rendering.
    $serverDsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2
    ]);
    $serverPdo->exec(
        "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` " .
        "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    // Connect to the target project database.
    $databaseDsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($databaseDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2
    ]);

    // Create "Login" table with 'role' column
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `Login` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('worker', 'employer') NOT NULL DEFAULT 'worker'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // Ensure 'role' column exists for existing tables
    try {
        $pdo->exec("ALTER TABLE `Login` ADD COLUMN `role` ENUM('worker', 'employer') NOT NULL DEFAULT 'worker'");
    } catch (Throwable $e) {
        // Ignore if column already exists
    }

    // Seed default accounts once
    $seedPasswordHash = password_hash("12345", PASSWORD_DEFAULT);
    
    $seedStmt = $pdo->prepare(
        "INSERT IGNORE INTO `Login` (`username`, `password`, `role`) VALUES 
         ('Tessa', :pass1, 'worker'),
         ('Perusahaan', :pass2, 'employer')"
    );
    $seedStmt->execute([
        ":pass1" => $seedPasswordHash,
        ":pass2" => $seedPasswordHash
    ]);

} catch (Throwable $e) {
    $dbOffline = true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim((string)($_POST["username"] ?? ""));
    $password = (string)($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $message = "Please fill in username and password.";
        $messageType = "error";
    } else {
        if ($pdo !== null) {
            try {
                $loginStmt = $pdo->prepare("SELECT `password`, `role` FROM `Login` WHERE `username` = :username LIMIT 1");
                $loginStmt->execute([":username" => $username]);
                $userRow = $loginStmt->fetch(PDO::FETCH_ASSOC);

                if ($userRow && isset($userRow["password"]) && password_verify($password, $userRow["password"])) {
                    $_SESSION["username"] = $username;
                    $_SESSION["role"] = $userRow["role"];
                    if ($userRow["role"] === 'employer') {
                        header("Location: dashboard-employer.php");
                    } else {
                        header("Location: dashboard-worker.php");
                    }
                    exit;
                } else {
                    $message = "Invalid username or password.";
                    $messageType = "error";
                }
            } catch (Throwable $e) {
                $message = "Database query error: " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            // Fallback prototype mode when MySQL is offline
            if ($username === "Tessa" && $password === "12345") {
                $_SESSION["username"] = $username;
                $_SESSION["role"] = 'worker';
                header("Location: dashboard-worker.php");
                exit;
            } elseif ($username === "Perusahaan" && $password === "12345") {
                $_SESSION["username"] = $username;
                $_SESSION["role"] = 'employer';
                header("Location: dashboard-employer.php");
                exit;
            } else {
                $message = "Account not found. Use demo credentials: Tessa / 12345 (worker) or Perusahaan / 12345 (employer)";
                $messageType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 350px;
            text-align: center;
        }
        .login-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .login-card h2 {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #000;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .login-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            width: 100%;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
        }
        .login-btn:hover {
            background-color: #0056b3;
        }
        .register-link {
            display: block;
            margin-top: 20px;
            font-size: 14px;
            color: #0000ff;
            text-decoration: underline;
        }
        .message {
            margin-top: 10px;
            font-size: 14px;
            color: red;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-icon">🦜</div>
        <h2>Login</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <?php if ($message !== ""): ?>
            <div class="message"><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></div>
        <?php endif; ?>
        <a href="#" class="register-link">Don't have an account? Register</a>
    </div>
</body>
</html>
