<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($header['name'] ?? 'Resume') ?> — <?= esc($header['position'] ?? '') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Source+Serif+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ─── Reset & Base ─── */
*{margin:0;padding:0;box-sizing:border-box}
body{background:#dde1e7;font-family:'Source Sans Pro',sans-serif;color:#2c3e50;font-size:13px;line-height:1.55;print-color-adjust:exact}

/* ─── Wrapper ─── */
.resume-wrapper{max-width:900px;margin:28px auto 60px;background:#fff;box-shadow:0 6px 40px rgba(0,0,0,0.14);border-radius:2px}

/* ─── Header ─── */
.resume-header{background:#2c3e50;color:#fff;padding:26px 36px 22px;display:flex;justify-content:space-between;align-items:center;gap:24px}
.header-left{}
.header-name{font-family:'Source Serif Pro',serif;font-size:30px;font-weight:600;letter-spacing:0.4px;line-height:1.1}
.header-position{margin-top:5px;font-size:11px;font-weight:300;letter-spacing:3.5px;text-transform:uppercase;color:rgba(255,255,255,0.65)}
.header-contacts{display:flex;flex-direction:column;gap:5px;align-items:flex-end}
.contact-item{display:flex;align-items:center;gap:7px;font-size:11.5px;color:rgba(255,255,255,0.82)}
.contact-item i{color:#3498db;font-size:10.5px;width:12px;text-align:center}

/* ─── Body ─── */
.resume-body{display:grid;grid-template-columns:1fr 0.62fr}

/* ─── Columns ─── */
.col-left{padding:22px 26px 28px;border-right:2px solid #f0f2f4}
.col-right{padding:22px 22px 28px;background:#f9fafb}

/* ─── Section ─── */
.section{margin-bottom:20px}
.section:last-child{margin-bottom:0}
.section-title{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:2.2px;color:#2c3e50;padding-bottom:5px;border-bottom:2px solid #3498db;margin-bottom:11px;display:flex;align-items:center;gap:7px}
.section-title i{color:#3498db;font-size:10px}

/* ─── Summary ─── */
.summary-text{font-size:12.5px;color:#555;line-height:1.75}

/* ─── History ─── */
.history-item{margin-bottom:15px}
.history-item:last-child{margin-bottom:0}
.history-role{display:block;font-weight:700;font-size:13px;color:#1a252f}
.history-meta{display:block;font-size:11px;color:#3498db;font-style:italic;margin-top:1px;margin-bottom:5px}

/* ─── Bullets ─── */
.bullet-list{padding-left:15px;margin-top:4px}
.bullet-list li{font-size:12px;color:#555;margin-bottom:2.5px;line-height:1.55}

/* ─── Education ─── */
.edu-item{margin-bottom:14px}
.edu-item:last-child{margin-bottom:0}
.edu-degree{font-weight:700;font-size:13px}
.edu-school{font-size:11.5px;color:#3498db;margin:1px 0}
.edu-dates{font-size:11px;color:#888;margin-bottom:5px}

/* ─── Certifications ─── */
.cert-item{margin-bottom:10px}
.cert-item:last-child{margin-bottom:0}
.cert-name{font-weight:600;font-size:12px;line-height:1.45;color:#1a252f}
.cert-year{font-size:11px;color:#3498db;margin-top:2px}

/* ─── Languages ─── */
.language-item{display:flex;align-items:center;justify-content:space-between;margin-bottom:7px}
.language-item:last-child{margin-bottom:0}
.lang-name{font-size:13px;color:#2c3e50}
.lang-dots{display:flex;gap:4px}
.dot{width:11px;height:11px;border-radius:50%;background:#d1d5db;border:1.5px solid #c4c9d0;flex-shrink:0}
.dot.filled{background:#3498db;border-color:#2980b9}

/* ─── Admin Floating Bar ─── */
.admin-bar{position:fixed;bottom:18px;right:18px;display:flex;gap:8px;z-index:999}
.admin-btn{display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-family:'Source Sans Pro',sans-serif;font-weight:600;box-shadow:0 4px 18px rgba(0,0,0,0.22);transition:transform 0.15s,box-shadow 0.15s}
.admin-btn:hover{transform:translateY(-2px);box-shadow:0 6px 24px rgba(0,0,0,0.28)}
.admin-btn.manage{background:#2c3e50;color:#fff}
.admin-btn.logout{background:#e74c3c;color:#fff}
.admin-btn.login{background:#3498db;color:#fff}

@media print{.admin-bar{display:none}body{background:#fff}.resume-wrapper{margin:0;box-shadow:none}}
@media(max-width:680px){.resume-body{grid-template-columns:1fr}.col-left{border-right:none}.resume-header{flex-direction:column;align-items:flex-start}.header-contacts{align-items:flex-start}}
</style>
</head>
<body>

<div class="resume-wrapper">

  <!-- ════════ HEADER ════════ -->
  <header class="resume-header">
    <div class="header-left">
      <div class="header-name"><?= esc($header['name'] ?? '') ?></div>
      <div class="header-position"><?= esc($header['position'] ?? '') ?></div>
    </div>
    <div class="header-contacts">
      <?php if (!empty($header['email'])): ?>
      <span class="contact-item"><i class="fas fa-envelope"></i><?= esc($header['email']) ?></span>
      <?php endif; ?>
      <?php if (!empty($header['phone'])): ?>
      <span class="contact-item"><i class="fas fa-phone"></i><?= esc($header['phone']) ?></span>
      <?php endif; ?>
      <?php if (!empty($header['location'])): ?>
      <span class="contact-item"><i class="fas fa-map-marker-alt"></i><?= esc($header['location']) ?></span>
      <?php endif; ?>
      <?php if (!empty($header['linkedin'])): ?>
      <span class="contact-item"><i class="fab fa-linkedin"></i><?= esc($header['linkedin']) ?></span>
      <?php endif; ?>
    </div>
  </header>

  <!-- ════════ BODY ════════ -->
  <div class="resume-body">

    <!-- ── LEFT COLUMN ── -->
    <div class="col-left">

      <!-- Summary -->
      <?php if (!empty($summary['content'])): ?>
      <section class="section">
        <div class="section-title"><i class="fas fa-user"></i>Summary</div>
        <p class="summary-text"><?= esc($summary['content']) ?></p>
      </section>
      <?php endif; ?>

      <!-- History -->
      <?php if (!empty($history)): ?>
      <section class="section">
        <div class="section-title"><i class="fas fa-briefcase"></i>History</div>
        <?php foreach ($history as $job): ?>
        <div class="history-item">
          <span class="history-role"><?= esc($job['role']) ?></span>
          <span class="history-meta">
            <?= esc($job['company']) ?> &nbsp;&middot;&nbsp;
            <?= esc($job['start_month']) ?> <?= esc($job['start_year']) ?> &ndash;
            <?= $job['is_current'] ? 'Present' : esc($job['end_month']).' '.esc($job['end_year']) ?>
          </span>
          <?php if (!empty($job['bullets'])): ?>
          <ul class="bullet-list">
            <?php foreach ($job['bullets'] as $b): ?>
            <li><?= esc($b['content']) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

      <!-- Personal Skills -->
      <?php if (!empty($skills)): ?>
      <section class="section">
        <div class="section-title"><i class="fas fa-star"></i>Personal Skills</div>
        <ul class="bullet-list">
          <?php foreach ($skills as $s): ?>
          <li><?= esc($s['content']) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
      <?php endif; ?>

    </div><!-- /col-left -->

    <!-- ── RIGHT COLUMN ── -->
    <div class="col-right">

      <!-- Stack of Technologies -->
      <?php if (!empty($tech)): ?>
      <section class="section">
        <div class="section-title"><i class="fas fa-code"></i>Stack of Technologies</div>
        <ul class="bullet-list">
          <?php foreach ($tech as $t): ?>
          <li><?= esc($t['content']) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
      <?php endif; ?>

      <!-- Languages -->
      <?php if (!empty($languages)): ?>
      <section class="section">
        <div class="section-title"><i class="fas fa-globe"></i>Languages</div>
        <?php foreach ($languages as $lang): ?>
        <div class="language-item">
          <span class="lang-name"><?= esc($lang['language']) ?></span>
          <div class="lang-dots">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="dot <?= ($lang['mastery'] / 20) >= $i ? 'filled' : '' ?>"></span>
            <?php endfor; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

      <!-- Education -->
      <?php if (!empty($education)): ?>
      <section class="section">
        <div class="section-title"><i class="fas fa-graduation-cap"></i>Education</div>
        <?php foreach ($education as $edu): ?>
        <div class="edu-item">
          <div class="edu-degree"><?= esc($edu['degree']) ?></div>
          <div class="edu-school"><?= esc($edu['school']) ?></div>
          <div class="edu-dates">
            <?= esc($edu['start_month']) ?> <?= esc($edu['start_year']) ?>
            &ndash; <?= esc($edu['end_month']) ?> <?= esc($edu['end_year']) ?>
          </div>
          <?php if (!empty($edu['bullets'])): ?>
          <ul class="bullet-list">
            <?php foreach ($edu['bullets'] as $b): ?>
            <li><?= esc($b['content']) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

      <!-- Certifications -->
      <?php if (!empty($certifications)): ?>
      <section class="section">
        <div class="section-title"><i class="fas fa-certificate"></i>Certification</div>
        <?php foreach ($certifications as $cert): ?>
        <div class="cert-item">
          <div class="cert-name"><?= esc($cert['name']) ?></div>
          <div class="cert-year"><?= esc($cert['year']) ?></div>
        </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

    </div><!-- /col-right -->

  </div><!-- /resume-body -->
</div><!-- /resume-wrapper -->

<!-- Admin floating bar -->
<div class="admin-bar">
  <?php if (!empty($isLoggedIn)): ?>
    <a href="<?= base_url('admin') ?>" class="admin-btn manage"><i class="fas fa-cog"></i>Manage</a>
    <a href="<?= base_url('logout') ?>" class="admin-btn logout"><i class="fas fa-sign-out-alt"></i>Logout</a>
  <?php else: ?>
    <a href="<?= base_url('login') ?>" class="admin-btn login"><i class="fas fa-lock"></i>Admin</a>
  <?php endif; ?>
</div>

</body>
</html>
