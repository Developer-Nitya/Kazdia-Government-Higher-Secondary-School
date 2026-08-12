<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../backend/helpers.php';

$error = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_password'])) {
    if (hash_equals(SCHOOL_ADMIN_PASSWORD, (string) $_POST['login_password'])) {
        $_SESSION['school_admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    }

    $error = 'পাসওয়ার্ড সঠিক নয়।';
}

$loggedIn = !empty($_SESSION['school_admin_logged_in']);
$headsCount = 0;

if ($loggedIn) {
    try {
        $headsCount = count(school_read_heads());
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?><!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>এডমিন প্যানেল | কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-page">
  <header class="admin-header">
    <div class="container">
      <h1>এডমিন প্যানেল</h1>
      <nav aria-label="এডমিন নেভিগেশন">
        <a href="../index.html" target="_blank" rel="noopener">হোমপেজ</a>
        <?php if ($loggedIn): ?>
          <span aria-hidden="true"> | </span>
          <a href="content.php">ওয়েবসাইট কনটেন্ট</a>
          <span aria-hidden="true"> | </span>
          <a href="service-pages.php">সেবা পেজসমূহ</a>
          <span aria-hidden="true"> | </span>
          <a href="former-heads.php">প্রতিষ্ঠান প্রধানদের তথ্য</a>
          <span aria-hidden="true"> | </span>
          <a href="national-anthem.php">জাতীয় সংগীত</a>
          <span aria-hidden="true"> | </span>
          <a href="index.php?logout=1">লগআউট</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="container content-page">
    <?php if ($error): ?>
      <div class="admin-message error"><?= school_h($error) ?></div>
    <?php endif; ?>

    <?php if (!$loggedIn): ?>
      <section class="content-page-card login-card">
        <div class="section-title">
          <div>
            <h2>এডমিন লগইন</h2>
            <p>ওয়েবসাইটের ব্যাকেন্ড ব্যবস্থাপনা করতে লগইন করুন।</p>
          </div>
        </div>

        <form method="post" class="editor-panel">
          <label>
            পাসওয়ার্ড
            <input type="password" name="login_password" autocomplete="current-password" required>
          </label>
          <div class="editor-actions">
            <button type="submit">লগইন</button>
          </div>
          <p class="editor-note">
            হোস্টিংয়ে আপলোড করার পর এডমিন প্যানেল পাবেন: <code>your-domain.com/admin/</code><br>
            নিরাপত্তার জন্য <code>backend/config.php</code> ফাইলের ডিফল্ট পাসওয়ার্ড পরিবর্তন করুন।
          </p>
        </form>
      </section>
    <?php else: ?>
      <section class="content-page-card">
        <div class="section-title admin-dashboard-title">
          <div>
            <h2>ড্যাশবোর্ড</h2>
            <p>এখান থেকে ব্যাকেন্ডের প্রয়োজনীয় অংশগুলো পরিচালনা করুন।</p>
          </div>
        </div>

        <div class="admin-dashboard-grid">

          <a class="admin-dashboard-card" href="content.php">
            <span class="dashboard-card-icon" aria-hidden="true">📝</span>
            <span>
              <strong>সম্পূর্ণ ওয়েবসাইট কনটেন্ট</strong>
              <small>প্রতিষ্ঠানের সেটিংস, হোমপেজ, নোটিশ, সেবা, গ্যালারি ও একাডেমিক পেজ এডিট করুন।</small>
            </span>
          </a>

          <!-- START: Service pages administration dashboard card section -->
          <a class="admin-dashboard-card" href="service-pages.php">
            <span class="dashboard-card-icon" aria-hidden="true">🗂️</span>
            <span>
              <strong>সেবাসমূহের পৃথক পেজ</strong>
              <small>৫৯টি সেবা পেজের তথ্য লিখুন এবং প্রয়োজনীয় ফাইল আপলোড করুন।</small>
            </span>
          </a>
          <!-- END: Service pages administration dashboard card section -->

          <a class="admin-dashboard-card" href="former-heads.php">
            <span class="dashboard-card-icon" aria-hidden="true">👥</span>
            <span>
              <strong>সাবেক প্রতিষ্ঠান প্রধানগণ</strong>
              <small>ডাটা এন্ট্রি, এডিট, ডিলিট ও ছবি আপলোড করুন।</small>
              <em>মোট এন্ট্রি: <?= school_h((string) $headsCount) ?></em>
            </span>
          </a>

          <a class="admin-dashboard-card" href="../pages/former-heads.html" target="_blank" rel="noopener">
            <span class="dashboard-card-icon" aria-hidden="true">📋</span>
            <span>
              <strong>ফ্রন্টেন্ড তালিকা দেখুন</strong>
              <small>ভিজিটররা যে তালিকা দেখবে সেটি নতুন ট্যাবে খুলবে।</small>
            </span>
          </a>


          <a class="admin-dashboard-card" href="national-anthem.php">
            <span class="dashboard-card-icon" aria-hidden="true">🎵</span>
            <span>
              <strong>জাতীয় সংগীত</strong>
              <small>অডিও/ভিডিও আপলোড অথবা অফিসিয়াল সোর্স পরিবর্তন করুন।</small>
            </span>
          </a>

          <a class="admin-dashboard-card" href="../index.html" target="_blank" rel="noopener">
            <span class="dashboard-card-icon" aria-hidden="true">🏠</span>
            <span>
              <strong>হোমপেজ দেখুন</strong>
              <small>ওয়েবসাইটের মূল পেজ নতুন ট্যাবে খুলবে।</small>
            </span>
          </a>
        </div>

        <div class="backend-link-note admin-access-note">
          <strong>এডমিন প্যানেল কীভাবে পাবেন:</strong><br>
          হোস্টিংয়ের মূল ফোল্ডারে ফাইল আপলোড করলে ব্রাউজারে লিখুন:
          <code>https://your-domain.com/admin/</code><br>
          ফোল্ডারের ভিতরে আপলোড করলে লিখুন:
          <code>https://your-domain.com/shed_homepage_design/admin/</code>
        </div>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
