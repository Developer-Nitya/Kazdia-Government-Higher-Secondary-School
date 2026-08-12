<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../backend/helpers.php';

$message = '';
$error = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: national-anthem.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_password'])) {
    if (hash_equals(SCHOOL_ADMIN_PASSWORD, (string) $_POST['login_password'])) {
        $_SESSION['school_admin_logged_in'] = true;
        header('Location: national-anthem.php');
        exit;
    }

    $error = 'পাসওয়ার্ড সঠিক নয়।';
}

$loggedIn = !empty($_SESSION['school_admin_logged_in']);
$settings = [];

if ($loggedIn) {
    try {
        $settings = school_read_media_settings();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = (string) $_POST['action'];

            if ($action === 'save') {
                $currentSource = (string) ($settings['source'] ?? '');
                $uploaded = school_upload_media($_FILES['media_file'] ?? [], $currentSource);

                $source = trim((string) ($_POST['source'] ?? ''));
                $type = trim((string) ($_POST['type'] ?? 'audio'));

                if ($uploaded['source'] !== '') {
                    $source = $uploaded['source'];
                    $type = $uploaded['type'] !== '' ? $uploaded['type'] : $type;
                }

                $settings = [
                    'title' => trim((string) ($_POST['title'] ?? 'জাতীয় সংগীত')),
                    'subtitle' => trim((string) ($_POST['subtitle'] ?? 'আমার সোনার বাংলা')),
                    'type' => $type,
                    'source' => $source,
                    'poster' => 'assets/img/national-anthem-poster.svg',
                ];

                school_write_media_settings($settings);
                $message = 'জাতীয় সংগীত মিডিয়া সফলভাবে সংরক্ষণ করা হয়েছে।';
            }

            if ($action === 'reset') {
                school_remove_media_file((string) ($settings['source'] ?? ''));
                $settings = [
                    'title' => 'জাতীয় সংগীত',
                    'subtitle' => 'আমার সোনার বাংলা',
                    'type' => 'audio',
                    'source' => 'https://upload.wikimedia.org/wikipedia/commons/transcoded/b/bc/Amar_Sonar_Bangla_-_official_vocal_music_of_the_National_anthem_of_Bangladesh.ogg/Amar_Sonar_Bangla_-_official_vocal_music_of_the_National_anthem_of_Bangladesh.ogg.mp3',
                    'poster' => 'assets/img/national-anthem-poster.svg',
                ];
                school_write_media_settings($settings);
                $message = 'অফিসিয়াল জাতীয় সংগীত সোর্স পুনরায় সেট করা হয়েছে।';
            }

            $settings = school_read_media_settings();
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        if (!$settings) {
            $settings = school_read_media_settings();
        }
    }
}
?><!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>জাতীয় সংগীত | এডমিন প্যানেল</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-page">
  <header class="admin-header">
    <div class="container">
      <h1>জাতীয় সংগীত ব্যবস্থাপনা</h1>
      <nav aria-label="এডমিন নেভিগেশন">
        <a href="index.php">ড্যাশবোর্ড</a>
        <span aria-hidden="true"> | </span>
        <a href="../index.html" target="_blank" rel="noopener">হোমপেজ</a>
        <?php if ($loggedIn): ?>
          <span aria-hidden="true"> | </span>
          <a href="former-heads.php">প্রতিষ্ঠান প্রধানদের তথ্য</a>
          <span aria-hidden="true"> | </span>
          <a href="national-anthem.php?logout=1">লগআউট</a>
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
            <p>জাতীয় সংগীত আপলোড/পরিবর্তন করতে লগইন করুন।</p>
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
        </form>
      </section>
    <?php else: ?>
      <section class="content-page-card">
        <div class="section-title">
          <div>
            <h2>জাতীয় সংগীত</h2>
            <p>এখান থেকে জাতীয় সংগীতের অডিও/ভিডিও সোর্স আপডেট করা যাবে।</p>
          </div>
          <a class="small-link" href="../index.html" target="_blank" rel="noopener">হোমপেজে দেখুন</a>
        </div>

        <div class="backend-link-note">
          বর্তমান সোর্স:
          <code><?= school_h((string) ($settings['source'] ?? '')) ?></code>
        </div>

        <form method="post" enctype="multipart/form-data" class="editor-panel">
          <input type="hidden" name="action" value="save">

          <div class="form-grid">
            <label>
              টাইটেল
              <input type="text" name="title" value="<?= school_h((string) ($settings['title'] ?? 'জাতীয় সংগীত')) ?>" required>
            </label>

            <label>
              সাবটাইটেল
              <input type="text" name="subtitle" value="<?= school_h((string) ($settings['subtitle'] ?? 'আমার সোনার বাংলা')) ?>" required>
            </label>

            <label>
              মিডিয়া ধরন
              <select name="type">
                <option value="audio" <?= (($settings['type'] ?? 'audio') === 'audio') ? 'selected' : '' ?>>অডিও</option>
                <option value="video" <?= (($settings['type'] ?? '') === 'video') ? 'selected' : '' ?>>ভিডিও</option>
              </select>
            </label>

            <label>
              অফিসিয়াল/বাহিরের মিডিয়া URL
              <input type="url" name="source" value="<?= school_h((string) ($settings['source'] ?? '')) ?>">
            </label>

            <label class="full-row">
              নতুন অডিও/ভিডিও ফাইল আপলোড
              <input type="file" name="media_file" accept="audio/mpeg,audio/ogg,audio/wav,video/mp4,video/webm,video/ogg">
            </label>
          </div>

          <p class="editor-note">
            MP3/OGG/WAV অডিও অথবা MP4/WEBM/OGV ভিডিও আপলোড করা যাবে। সর্বোচ্চ সাইজ ৫০ এমবি।
            নতুন ফাইল আপলোড করলে URL-এর পরিবর্তে আপলোড করা ফাইলটি ব্যবহার হবে।
          </p>

          <div class="editor-actions">
            <button type="submit">সংরক্ষণ করুন</button>
          </div>
        </form>

        <form method="post" class="inline-form" onsubmit="return confirm('অফিসিয়াল জাতীয় সংগীত সোর্স পুনরায় সেট করতে চান?');">
          <input type="hidden" name="action" value="reset">
          <div class="editor-actions">
            <button type="submit" class="secondary-action">অফিসিয়াল সোর্সে রিসেট করুন</button>
          </div>
        </form>

        <div class="editor-panel" style="margin-top:16px;">
          <h3>বর্তমান প্রিভিউ</h3>
          <?php $previewUrl = school_admin_media_preview_url((string) ($settings['source'] ?? '')); ?>
          <?php if (($settings['type'] ?? 'audio') === 'video'): ?>
            <video controls preload="metadata" poster="../assets/img/national-anthem-poster.svg" style="width:100%;max-height:360px;background:#111;">
              <source src="<?= school_h($previewUrl) ?>">
              আপনার ব্রাউজার ভিডিও প্লেয়ার সমর্থন করে না।
            </video>
          <?php else: ?>
            <audio controls preload="metadata" style="width:100%;">
              <source src="<?= school_h($previewUrl) ?>">
              আপনার ব্রাউজার অডিও প্লেয়ার সমর্থন করে না।
            </audio>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
