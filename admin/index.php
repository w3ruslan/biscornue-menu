<?php
require_once __DIR__ . '/../config.php';
session_start();
if (isset($_SESSION['admin'])) { header('Location: dashboard.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($_POST['password'] ?? '') === ADMIN_PASS) {
        $_SESSION['admin'] = true;
        header('Location: dashboard.php'); exit;
    }
    $err = 'Mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — La Biscornue</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',sans-serif;background:#1a1209;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .box{background:#fff;border-radius:20px;padding:40px 36px;max-width:360px;width:100%;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,.5)}
    .logo{font-size:44px;margin-bottom:8px}
    h1{font-size:20px;font-weight:900;color:#1a1209;margin-bottom:4px}
    .sub{font-size:12px;color:#9a8572;margin-bottom:28px}
    input{width:100%;padding:12px 16px;border:2px solid #e8ddd0;border-radius:10px;font-size:15px;outline:none;margin-bottom:14px}
    input:focus{border-color:#d4a853}
    .btn{width:100%;padding:13px;background:#1a1209;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer}
    .btn:hover{background:#2e1f0c}
    .err{color:#c0392b;font-size:13px;margin-bottom:12px}
  </style>
</head>
<body>
<div class="box">
  <div class="logo">🥙</div>
  <h1>La Biscornue</h1>
  <div class="sub">Administration</div>
  <?php if ($err): ?><div class="err">❌ <?= $err ?></div><?php endif; ?>
  <form method="POST">
    <input type="password" name="password" placeholder="Mot de passe" autofocus required>
    <button class="btn" type="submit">Connexion</button>
  </form>
</div>
</body>
</html>
