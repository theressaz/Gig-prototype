<?php
declare(strict_types=1);
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Gig');

$message = "";
$messageType = "";

if (isset($_SESSION["username"])) {
    header("Location: dashboard.php");
    exit;
}

try {
    // Connect to MySQL server and ensure "Gig" database exists.
    $serverDsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS);
    $serverPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $serverPdo->exec(
        "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` " .
        "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    // Connect to the target project database.
    $databaseDsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($databaseDsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create "Login" table if it does not exist.
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `Login` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // Seed default account once.
    $seedUser = "Tessa";
    $seedPasswordHash = password_hash("12345", PASSWORD_DEFAULT);
    $seedStmt = $pdo->prepare(
        "INSERT INTO `Login` (`username`, `password`)
         SELECT :username, :password
         FROM DUAL
         WHERE NOT EXISTS (
            SELECT 1 FROM `Login` WHERE `username` = :check_username
         )"
    );
    $seedStmt->execute([
        ":username" => $seedUser,
        ":password" => $seedPasswordHash,
        ":check_username" => $seedUser
    ]);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim((string)($_POST["username"] ?? ""));
        $password = (string)($_POST["password"] ?? "");

        if ($username === "" || $password === "") {
            $message = "Please fill in username and password.";
            $messageType = "error";
        } else {
            $loginStmt = $pdo->prepare("SELECT `password` FROM `Login` WHERE `username` = :username LIMIT 1");
            $loginStmt->execute([":username" => $username]);
            $userRow = $loginStmt->fetch(PDO::FETCH_ASSOC);

            if ($userRow && isset($userRow["password"]) && password_verify($password, $userRow["password"])) {
                $_SESSION["username"] = $username;
                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Invalid username or password.";
                $messageType = "error";
            }
        }
    }
} catch (Throwable $e) {
    $message = "Database error: " . $e->getMessage();
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Welcome Screen</title>
  <style>
    :root {
      color-scheme: light;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: Arial, Helvetica, sans-serif;
    }

    body {
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: linear-gradient(
        135deg,
        #ff0000 0%,
        #ff7f00 16%,
        #ffff00 32%,
        #00ff00 48%,
        #0000ff 64%,
        #4b0082 80%,
        #8b00ff 100%
      );
      color: #ffffff;
      text-align: center;
      padding: 24px;
    }

    .card {
      background: rgba(0, 0, 0, 0.35);
      border: 2px solid rgba(255, 255, 255, 0.45);
      border-radius: 16px;
      padding: 32px;
      max-width: 560px;
      width: 100%;
      backdrop-filter: blur(3px);
      box-shadow: 0 10px 24px rgba(0, 0, 0, 0.22);
    }

    h1 {
      font-size: clamp(2rem, 5vw, 3rem);
      margin-bottom: 12px;
      letter-spacing: 0.04em;
    }

    p {
      font-size: 1.05rem;
      line-height: 1.5;
      opacity: 0.96;
    }

    form {
      margin-top: 24px;
      display: grid;
      gap: 12px;
      text-align: left;
    }

    label {
      font-size: 0.95rem;
      font-weight: 700;
    }

    input {
      width: 100%;
      margin-top: 6px;
      border: none;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 1rem;
      outline: none;
    }

    input:focus {
      box-shadow: 0 0 0 2px #ffffff99;
    }

    button {
      border: none;
      border-radius: 10px;
      padding: 12px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      background: #ffffff;
      color: #35235a;
      transition: transform 0.12s ease;
    }

    button:hover {
      transform: translateY(-1px);
    }

    .message {
      margin-top: 14px;
      font-size: 0.98rem;
      font-weight: 700;
    }

    .message.success {
      color: #d0ffd0;
    }

    .message.error {
      color: #ffd1d1;
    }

    .hint {
      margin-top: 12px;
      font-size: 0.9rem;
      opacity: 0.85;
    }
  </style>
</head>
<body>
  <main class="card" role="main" aria-label="Welcome Screen">
    <h1>Welcome Screen</h1>
    <p>Login to your Gig project account.</p>

    <form method="post" action="">
      <label for="username">
        Username
        <input id="username" name="username" type="text" autocomplete="username" required />
      </label>

      <label for="password">
        Password
        <input id="password" name="password" type="password" autocomplete="current-password" required />
      </label>

      <button type="submit">Login</button>
    </form>

    <?php if ($message !== ""): ?>
      <p class="message <?php echo htmlspecialchars($messageType, ENT_QUOTES, "UTF-8"); ?>">
        <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
      </p>
    <?php endif; ?>

    <p class="hint">Seeded account: username <strong>Tessa</strong>, password <strong>12345</strong></p>
  </main>
</body>
</html>
