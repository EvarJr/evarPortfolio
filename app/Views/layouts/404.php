<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>404 Not Found</title>
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f0f2f5}
.box{text-align:center;padding:40px}.box h1{font-size:72px;color:#2c3e50;margin:0}.box p{color:#666;margin:10px 0 20px}
a{color:#3498db;text-decoration:none}</style>
</head>
<body>
<div class="box">
  <h1>404</h1>
  <p>The page <strong><?= esc($uri ?? '') ?></strong> was not found.</p>
  <a href="<?= base_url() ?>">← Back to Resume</a>
</div>
</body>
</html>
