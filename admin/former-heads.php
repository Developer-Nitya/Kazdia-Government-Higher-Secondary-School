<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../backend/helpers.php';

$message = '';
$error = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: former-heads.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_password'])) {
    if (hash_equals(SCHOOL_ADMIN_PASSWORD, (string) $_POST['login_password'])) {
        $_SESSION['school_admin_logged_in'] = true;
        header('Location: former-heads.php');
        exit;
    }

    $error = 'পাসওয়ার্ড সঠিক নয়।';
}

$loggedIn = !empty($_SESSION['school_admin_logged_in']);
$heads = [];

if ($loggedIn) {
    try {
        $heads = school_read_heads();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = (string) $_POST['action'];

            if ($action === 'save') {
                $id = trim((string) ($_POST['id'] ?? ''));
                $index = $id !== '' ? school_find_head_index($heads, $id) : -1;
                $existing = $index >= 0 ? $heads[$index] : [];

                $photo = school_upload_photo($_FILES['photo'] ?? [], $existing['photo'] ?? '');

                $nextHead = [
                    'id' => $id !== '' ? $id : bin2hex(random_bytes(8)),
                    'serial' => trim((string) ($_POST['serial'] ?? '')),
                    'name' => trim((string) ($_POST['name'] ?? '')),
                    'designation' => trim((string) ($_POST['designation'] ?? '')),
                    'period' => trim((string) ($_POST['period'] ?? '')),
                    'bio' => trim((string) ($_POST['bio'] ?? '')),
                    'photo' => $photo,
                ];

                if ($nextHead['serial'] === '' || $nextHead['name'] === '' || $nextHead['designation'] === '' || $nextHead['period'] === '') {
                    throw new RuntimeException('ক্রমিক নং, নাম, পদবী ও মেয়াদকাল অবশ্যই পূরণ করুন।');
                }

                if ($index >= 0) {
                    $heads[$index] = $nextHead;
                    $message = 'তথ্য আপডেট করা হয়েছে।';
                } else {
                    $heads[] = $nextHead;
                    $message = 'নতুন তথ্য সংরক্ষণ করা হয়েছে।';
                }

                school_write_heads($heads);
                $heads = school_read_heads();
            }

            if ($action === 'delete') {
                $id = trim((string) ($_POST['id'] ?? ''));
                $index = school_find_head_index($heads, $id);

                if ($index >= 0) {
                    school_remove_photo($heads[$index]['photo'] ?? '');
                    array_splice($heads, $index, 1);
                    school_write_heads($heads);
                    $heads = school_read_heads();
                    $message = 'তথ্য মুছে ফেলা হয়েছে।';
                }
            }
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$editId = $loggedIn ? trim((string) ($_GET['edit'] ?? '')) : '';
$editHead = [
    'id' => '',
    'serial' => '',
    'name' => '',
    'designation' => '',
    'period' => '',
    'bio' => '',
    'photo' => '',
];

if ($loggedIn && $editId !== '') {
    $index = school_find_head_index($heads, $editId);
    if ($index >= 0) {
        $editHead = array_merge($editHead, $heads[$index]);
    }
}
?><!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>এডমিন প্যানেল | সাবেক প্রতিষ্ঠান প্রধানগণ</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-page">
  <header class="admin-header">
    <div class="container">
      <h1>এডমিন প্যানেল | সাবেক প্রতিষ্ঠান প্রধানগণ</h1>
      <nav aria-label="ব্যাকেন্ড নেভিগেশন">
        <a href="index.php">ড্যাশবোর্ড</a>
        <span aria-hidden="true"> | </span>
        <a href="../index.html" target="_blank" rel="noopener">হোমপেজ</a>
        <span aria-hidden="true"> | </span>
        <a href="../pages/former-heads.html" target="_blank" rel="noopener">ফ্রন্টেন্ড তালিকা</a>
        <?php if ($loggedIn): ?>
          <span aria-hidden="true"> | </span>
          <a href="index.php?logout=1">লগআউট</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="container content-page">
    <?php if ($message): ?>
      <div class="admin-message success"><?= school_h($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="admin-message error"><?= school_h($error) ?></div>
    <?php endif; ?>

    <?php if (!$loggedIn): ?>
      <section class="content-page-card login-card">
        <div class="section-title">
          <div>
            <h2>এডমিন লগইন</h2>
            <p>প্রতিষ্ঠান প্রধানদের তথ্য এন্ট্রি/আপডেট করতে লগইন করুন।</p>
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
            মূল এডমিন প্যানেল: <a href="index.php">admin/index.php</a><br>
            প্রথমবার আপলোডের আগে <code>backend/config.php</code> ফাইল থেকে ডিফল্ট পাসওয়ার্ড পরিবর্তন করুন।
          </p>
        </form>
      </section>
    <?php else: ?>
      <section class="content-page-card">
        <div class="section-title">
          <div>
            <h2><?= $editHead['id'] ? 'তথ্য আপডেট করুন' : 'নতুন তথ্য ইনপুট করুন' ?></h2>
            <p>এখানে সংরক্ষিত তথ্য ফ্রন্টেন্ডের সাবেক প্রতিষ্ঠান প্রধানগণ তালিকায় দেখা যাবে।</p>
          </div>
        </div>

        <div class="backend-link-note">
          ফ্রন্টেন্ড তালিকা দেখতে:
          <a href="../pages/former-heads.html" target="_blank" rel="noopener">সাবেক প্রতিষ্ঠান প্রধানগণ</a>
        </div>

        <form method="post" enctype="multipart/form-data" class="editor-panel">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= school_h($editHead['id']) ?>">
          <div class="form-grid">
            <label>
              ক্রমিক নং
              <input type="text" name="serial" value="<?= school_h($editHead['serial']) ?>" placeholder="যেমন: ১" required>
            </label>
            <label>
              সাবেক প্রতিষ্ঠান প্রধানের নাম
              <input type="text" name="name" value="<?= school_h($editHead['name']) ?>" placeholder="নাম লিখুন" required>
            </label>
            <label>
              পদবী
              <input type="text" name="designation" value="<?= school_h($editHead['designation']) ?>" placeholder="যেমন: প্রধান শিক্ষক" required>
            </label>
            <label>
              মেয়াদকাল
              <input type="text" name="period" value="<?= school_h($editHead['period']) ?>" placeholder="যেমন: ১৯৮০-১৯৯০" required>
            </label>
            <label class="full-row">
              সংক্ষিপ্ত পরিচিতি
              <textarea name="bio" rows="4" placeholder="সংক্ষিপ্ত পরিচিতি লিখুন"><?= school_h($editHead['bio']) ?></textarea>
            </label>
            <label>
              ছবি আপলোড
              <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
            </label>
            <div>
              <span>বর্তমান ছবি</span>
              <img class="head-photo" src="<?= school_h($editHead['photo'] ?: '../assets/img/logo.jpg') ?>" alt="বর্তমান ছবি">
            </div>
          </div>
          <div class="editor-actions">
            <button type="submit"><?= $editHead['id'] ? 'আপডেট করুন' : 'সংরক্ষণ করুন' ?></button>
            <?php if ($editHead['id']): ?>
              <a class="table-action secondary-action" href="former-heads.php">নতুন এন্ট্রি</a>
            <?php endif; ?>
          </div>
          <p class="editor-note">ছবি ২ এমবি-এর কম রাখুন। JPG, PNG, GIF বা WEBP ফরম্যাট ব্যবহার করুন।</p>
        </form>
      </section>

      <section class="content-page-card" style="margin-top:18px">
        <div class="section-title">
          <div>
            <h2>সংরক্ষিত তালিকা</h2>
            <p>এডিট বা মুছে ফেলার জন্য নিচের অ্যাকশন ব্যবহার করুন।</p>
          </div>
        </div>

        <div class="former-heads-table-wrap">
          <table class="former-heads-table">
            <thead>
              <tr>
                <th>ক্রমিক নং</th>
                <th>ছবি</th>
                <th>নাম</th>
                <th>পদবী</th>
                <th>মেয়াদকাল</th>
                <th>সংক্ষিপ্ত পরিচিতি</th>
                <th>অ্যাকশন</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$heads): ?>
                <tr><td colspan="7">এখনো কোনো তথ্য যোগ করা হয়নি।</td></tr>
              <?php endif; ?>

              <?php foreach ($heads as $head): ?>
                <tr>
                  <td><?= school_h($head['serial'] ?? '') ?></td>
                  <td><img class="head-photo" src="<?= school_h(($head['photo'] ?? '') ?: '../assets/img/logo.jpg') ?>" alt="<?= school_h($head['name'] ?? 'প্রতিষ্ঠান প্রধান') ?>"></td>
                  <td><?= school_h($head['name'] ?? '') ?></td>
                  <td><?= school_h($head['designation'] ?? '') ?></td>
                  <td><?= school_h($head['period'] ?? '') ?></td>
                  <td><?= school_h($head['bio'] ?? '') ?></td>
                  <td>
                    <div class="row-actions">
                      <a class="table-action" href="?edit=<?= urlencode((string) ($head['id'] ?? '')) ?>">এডিট</a>
                      <form method="post" class="inline-form" onsubmit="return confirm('এই তথ্যটি মুছে ফেলবেন?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= school_h($head['id'] ?? '') ?>">
                        <button type="submit" class="table-action secondary-action">মুছুন</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
