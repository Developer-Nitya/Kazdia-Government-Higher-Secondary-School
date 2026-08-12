<?php
declare(strict_types=1);

/* START: Hosting compatibility checker bootstrap section */
require_once __DIR__ . '/backend/helpers.php';
require_once __DIR__ . '/backend/service-pages.php';
/* END: Hosting compatibility checker bootstrap section */

/* START: Hosting compatibility checker logic section */
$checks = [
    'PHP version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'storage folder writable' => is_writable(__DIR__ . '/storage'),
    'uploads folder writable' => is_writable(__DIR__ . '/uploads'),
    'uploads/media folder writable' => is_writable(__DIR__ . '/uploads/media'),
    'uploads/service-pages folder writable' => is_writable(__DIR__ . '/uploads/service-pages'),
    'JSON extension enabled' => extension_loaded('json'),
    'File upload enabled' => (bool) ini_get('file_uploads'),
];

try {
    school_ensure_site_content_storage();
    $checks['site-content database readable'] = is_readable(school_site_content_file());
    $checks['site-content database writable'] = is_writable(school_site_content_file());

    /* START: Academic resource hosting validation section */
    $siteContent = school_read_site_content();
    $programResources = is_array($siteContent['programResources'] ?? null) ? $siteContent['programResources'] : [];
    $checks['16 academic resource records available'] = count($programResources) === 16;

    $resourceFilesAvailable = count($programResources) === 16;
    foreach ($programResources as $resource) {
        $pagePath = trim((string) ($resource['page'] ?? ''));
        if ($pagePath === '' || !is_file(__DIR__ . '/' . ltrim($pagePath, '/'))) {
            $resourceFilesAvailable = false;
            break;
        }
    }
    $checks['16 academic resource HTML pages available'] = $resourceFilesAvailable;
    /* END: Academic resource hosting validation section */

    /* START: Service page hosting validation section */
    school_ensure_service_page_storage();
    $servicePayload = school_read_service_pages();
    $servicePages = is_array($servicePayload['pages'] ?? null) ? $servicePayload['pages'] : [];
    $checks['59 service page records available'] = count($servicePages) === 59;
    $checks['service page database readable'] = is_readable(school_service_pages_file());
    $checks['service page database writable'] = is_writable(school_service_pages_file());

    $serviceFilesAvailable = count($servicePages) === 59;
    foreach ($servicePages as $servicePage) {
        $pagePath = trim((string) ($servicePage['page'] ?? ''));
        if ($pagePath === '' || !is_file(__DIR__ . '/' . ltrim($pagePath, '/'))) {
            $serviceFilesAvailable = false;
            break;
        }
    }
    $checks['59 service HTML pages available'] = $serviceFilesAvailable;
    /* END: Service page hosting validation section */
} catch (Throwable $exception) {
    $checks['site-content database readable'] = false;
    $checks['site-content database writable'] = false;
}
/* END: Hosting compatibility checker logic section */
?><!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hosting Check | Kazdia School Website</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="admin-page">
  <!-- START: Hosting check output section -->
  <main class="container content-page">
    <section class="content-page-card">
      <div class="section-title">
        <div>
          <h1>Hosting Compatibility Check</h1>
          <p>সবগুলো PASS হলে ওয়েবসাইটের backend/database smooth কাজ করার কথা।</p>
        </div>
      </div>
      <table class="former-heads-table">
        <thead>
          <tr>
            <th>Check</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($checks as $label => $passed): ?>
            <tr>
              <td><?= school_h((string) $label) ?></td>
              <td><?= $passed ? 'PASS' : 'FAIL' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="editor-note">চেক শেষ হলে নিরাপত্তার জন্য এই ফাইলটি server থেকে delete করতে পারেন।</p>
    </section>
  </main>
  <!-- END: Hosting check output section -->
</body>
</html>
