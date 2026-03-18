<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — Resume CI4</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1a1f2e 0%,#2d3561 50%,#1a1f2e 100%);font-family:'DM Sans',sans-serif}
.login-wrap{width:100%;max-width:420px;padding:20px}
.login-card{background:#fff;border-radius:20px;padding:48px 40px;box-shadow:0 40px 80px rgba(0,0,0,0.35)}
.logo{text-align:center;margin-bottom:36px}
.logo-icon{width:56px;height:56px;background:linear-gradient(135deg,#2d3561,#3b82f6);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:22px;color:#fff}
.logo h1{font-family:'Playfair Display',serif;font-size:26px;color:#1a1f2e;margin-bottom:4px}
.logo p{color:#9ca3af;font-size:13px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:7px}
.form-group input{width:100%;padding:12px 15px;border:2px solid #e5e7eb;border-radius:10px;font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color 0.2s,box-shadow 0.2s;color:#1f2937}
.form-group input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}
.btn-login{width:100%;padding:13px;background:linear-gradient(135deg,#2d3561,#3b82f6);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;letter-spacing:0.3px;transition:opacity 0.2s;margin-top:6px}
.btn-login:hover{opacity:0.9}
.error-msg{background:#fef2f2;border:1.5px solid #fecaca;color:#dc2626;padding:11px 14px;border-radius:8px;margin-bottom:20px;font-size:13px;display:flex;align-items:center;gap:8px}
.back-link{text-align:center;margin-top:22px}
.back-link a{color:#6b7280;text-decoration:none;font-size:13px;transition:color 0.2s}
.back-link a:hover{color:#3b82f6}
.default-note{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:10px 14px;border-radius:8px;margin-bottom:20px;font-size:12px;line-height:1.5}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="logo">
      <div class="logo-icon">&#128196;</div>
      <h1>Resume Admin</h1>
      <p>Sign in to manage your resume</p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="error-msg">&#10060; <?= esc($error) ?></div>
    <?php endif; ?>

    <div class="default-note">
      &#128274; Default credentials: <strong>admin</strong> / <strong>admin123</strong><br>
      Change your password after first login.
    </div>

    <form method="POST" action="<?= base_url('login') ?>">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter username" required autofocus autocomplete="username">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-login">Sign In &rarr;</button>
    </form>

    <div class="back-link">
      <a href="<?= base_url() ?>">&#8592; View Resume</a>
    </div>
  </div>
</div>
</body>
</html>
