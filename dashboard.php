<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: welcome-screen.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            (bool)$params["secure"],
            (bool)$params["httponly"]
        );
    }
    session_destroy();
    header("Location: welcome-screen.php");
    exit;
}

$username = (string)$_SESSION["username"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <style>
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
      background: linear-gradient(135deg, #2a0845 0%, #6441a5 50%, #00c9ff 100%);
      color: #ffffff;
      padding: 24px;
    }

    .card {
      width: 100%;
      max-width: 560px;
      background: rgba(0, 0, 0, 0.32);
      border: 2px solid rgba(255, 255, 255, 0.45);
      border-radius: 16px;
      padding: 32px;
      text-align: center;
      box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25);
    }

    h1 {
      font-size: clamp(2rem, 5vw, 3rem);
      margin-bottom: 12px;
    }

    p {
      font-size: 1.05rem;
      line-height: 1.5;
      opacity: 0.95;
    }

    form {
      margin-top: 22px;
    }

    button {
      border: none;
      border-radius: 10px;
      padding: 11px 16px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      background: #ffffff;
      color: #2a0845;
    }
  </style>
</head>
<body>
  <main class="card" role="main" aria-label="Dashboard">
    <h1>Dashboard</h1>
    <p>Welcome, <strong><?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?></strong>. You are logged in to Gig.</p>
    <form method="post" action="">
      <button type="submit" name="logout" value="1">Logout</button>
    </form>
  </main>
</body>
</html>
