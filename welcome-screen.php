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
      font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    }

    body {
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: radial-gradient(circle at 15% 20%, #dbeafe 0%, transparent 40%),
                  radial-gradient(circle at 85% 80%, #bfdbfe 0%, transparent 40%),
                  linear-gradient(135deg, #e0f2fe 0%, #bae6fd 45%, #7dd3fc 85%, #38bdf8 100%);
      background-attachment: fixed;
      color: #0f172a;
      text-align: center;
      padding: 24px;
    }

    .card {
      background: rgba(255, 255, 255, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.65);
      border-radius: 20px;
      padding: 36px 32px;
      max-width: 520px;
      width: 100%;
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      box-shadow: 0 16px 40px rgba(14, 116, 144, 0.15),
                  inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    h1 {
      font-size: clamp(2rem, 5vw, 2.75rem);
      margin-bottom: 8px;
      letter-spacing: -0.02em;
      color: #0c4a6e;
      font-weight: 800;
    }

    p {
      font-size: 1.05rem;
      line-height: 1.5;
      color: #334155;
    }

    form {
      margin-top: 24px;
      display: grid;
      gap: 16px;
      text-align: left;
    }

    label {
      display: flex;
      flex-direction: column;
      font-size: 0.95rem;
      font-weight: 600;
      color: #1e293b;
      gap: 6px;
    }

    input {
      width: 100%;
      border: 1px solid rgba(255, 255, 255, 0.7);
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border-radius: 12px;
      padding: 12px 14px;
      font-size: 1rem;
      color: #0f172a;
      outline: none;
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
      transition: all 0.2s ease;
    }

    input:focus {
      background: rgba(255, 255, 255, 0.9);
      border-color: #38bdf8;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.35);
    }

    button {
      margin-top: 8px;
      border: none;
      border-radius: 12px;
      padding: 13px;
      font-size: 1.05rem;
      font-weight: 700;
      cursor: pointer;
      background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
      color: #ffffff;
      box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
      transition: all 0.2s ease;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(2, 132, 199, 0.45);
      background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    }

    button:active {
      transform: translateY(0);
    }

    .message {
      margin-top: 16px;
      font-size: 0.95rem;
      font-weight: 600;
      padding: 10px 14px;
      border-radius: 10px;
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }

    .message.success {
      background: rgba(34, 197, 94, 0.18);
      border: 1px solid rgba(34, 197, 94, 0.35);
      color: #166534;
    }

    .message.error {
      background: rgba(239, 68, 68, 0.18);
      border: 1px solid rgba(239, 68, 68, 0.35);
      color: #991b1b;
    }

    .hint {
      margin-top: 16px;
      font-size: 0.88rem;
      color: #475569;
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
