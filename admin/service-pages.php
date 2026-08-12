<?php
declare(strict_types=1);

/* START: Service page admin bootstrap section */
session_start();
require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/service-pages.php';

if (empty($_SESSION['school_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['service_page_csrf'])) {
    $_SESSION['service_page_csrf'] = bin2hex(random_bytes(24));
}
/* END: Service page admin bootstrap section */

/* START: Service page admin utility section */
function service_admin_redirect(string $slug, string $type, string $message): void
{
    $_SESSION['service_page_flash'] = [
        'type' => $type,
        'message' => $message,
    ];

    header('Location: service-pages.php?page=' . rawurlencode($slug));
    exit;
}

function service_admin_verify_csrf(): void
{
    $submitted = (string) ($_POST['csrf_token'] ?? '');
    $expected = (string) ($_SESSION['service_page_csrf'] ?? '');

    if ($expected === '' || !hash_equals($expected, $submitted)) {
        throw new RuntimeException('নিরাপত্তা যাচাই ব্যর্থ হয়েছে। পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।');
    }
}

function service_admin_selected_slug(array $pages): string
{
    $requested = trim((string) ($_POST['page_slug'] ?? ($_GET['page'] ?? '')));

    if ($requested !== '' && isset($pages[$requested])) {
        return $requested;
    }

    $firstSlug = array_key_first($pages);

    return is_string($firstSlug) ? $firstSlug : '';
}

function service_admin_limit(string $value, int $length): string
{
    return function_exists('mb_substr')
        ? mb_substr($value, 0, $length, 'UTF-8')
        : substr($value, 0, $length);
}

function service_admin_group_pages(array $pages): array
{
    $groups = [];

    foreach ($pages as $slug => $page) {
        $section = trim((string) ($page['sectionTitle'] ?? 'সেবাসমূহ')) ?: 'সেবাসমূহ';
        $groups[$section][$slug] = $page;
    }

    return $groups;
}
/* END: Service page admin utility section */

/* START: Service page admin action processing section */
try {
    $payload = school_read_service_pages();
    $pages = is_array($payload['pages'] ?? null) ? $payload['pages'] : [];
    $selectedSlug = service_admin_selected_slug($pages);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        service_admin_verify_csrf();

        if ($selectedSlug === '' || !isset($pages[$selectedSlug])) {
            throw new RuntimeException('সার্ভিস পেজটি পাওয়া যায়নি।');
        }

        $action = trim((string) ($_POST['action'] ?? ''));

        if ($action === 'save_content') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $sectionTitle = trim((string) ($_POST['section_title'] ?? ''));
            $intro = trim((string) ($_POST['intro'] ?? ''));
            $content = trim((string) ($_POST['content'] ?? ''));

            if ($title === '' || $sectionTitle === '') {
                throw new RuntimeException('পেজের শিরোনাম ও সাব-সেকশনের নাম খালি রাখা যাবে না।');
            }

            $pages[$selectedSlug]['title'] = service_admin_limit($title, 250);
            $pages[$selectedSlug]['sectionTitle'] = service_admin_limit($sectionTitle, 250);
            $pages[$selectedSlug]['intro'] = service_admin_limit($intro, 2000);
            $pages[$selectedSlug]['content'] = service_admin_limit($content, 30000);
            $payload['pages'] = $pages;

            school_write_service_pages($payload);
            service_admin_redirect($selectedSlug, 'success', 'পেজের তথ্য সফলভাবে সংরক্ষণ করা হয়েছে।');
        }

        if ($action === 'upload_document') {
            $uploaded = school_upload_service_document(
                $selectedSlug,
                isset($_FILES['document_file']) && is_array($_FILES['document_file'])
                    ? $_FILES['document_file']
                    : ['error' => UPLOAD_ERR_NO_FILE]
            );

            $documentTitle = trim((string) ($_POST['document_title'] ?? ''));
            $documentDescription = trim((string) ($_POST['document_description'] ?? ''));

            $pages[$selectedSlug]['documents'][] = [
                'id' => bin2hex(random_bytes(10)),
                'title' => service_admin_limit(
                    $documentTitle !== '' ? $documentTitle : $uploaded['originalName'],
                    250
                ),
                'description' => service_admin_limit($documentDescription, 1000),
                'file' => $uploaded['file'],
                'originalName' => $uploaded['originalName'],
                'uploadedAt' => date(DATE_ATOM),
            ];
            $payload['pages'] = $pages;

            try {
                school_write_service_pages($payload);
            } catch (Throwable $exception) {
                school_remove_service_document_file($uploaded['file']);
                throw $exception;
            }

            service_admin_redirect($selectedSlug, 'success', 'ফাইলটি সফলভাবে সংযুক্ত করা হয়েছে।');
        }

        if ($action === 'delete_document') {
            $documentId = trim((string) ($_POST['document_id'] ?? ''));
            $documents = is_array($pages[$selectedSlug]['documents'] ?? null)
                ? $pages[$selectedSlug]['documents']
                : [];
            $keptDocuments = [];
            $deletedFile = '';

            foreach ($documents as $document) {
                if (
                    is_array($document)
                    && hash_equals((string) ($document['id'] ?? ''), $documentId)
                ) {
                    $deletedFile = (string) ($document['file'] ?? '');
                    continue;
                }

                if (is_array($document)) {
                    $keptDocuments[] = $document;
                }
            }

            if ($deletedFile === '') {
                throw new RuntimeException('মুছে ফেলার জন্য ফাইলটি পাওয়া যায়নি।');
            }

            $pages[$selectedSlug]['documents'] = $keptDocuments;
            $payload['pages'] = $pages;
            school_write_service_pages($payload);
            school_remove_service_document_file($deletedFile);

            service_admin_redirect($selectedSlug, 'success', 'সংযুক্ত ফাইলটি মুছে ফেলা হয়েছে।');
        }

        throw new RuntimeException('অনুরোধটি শনাক্ত করা যায়নি।');
    }
} catch (Throwable $exception) {
    $fallbackPayload = school_read_service_pages();
    $pages = is_array($fallbackPayload['pages'] ?? null) ? $fallbackPayload['pages'] : [];
    $selectedSlug = service_admin_selected_slug($pages);
    $_SESSION['service_page_flash'] = [
        'type' => 'error',
        'message' => $exception->getMessage(),
    ];
}
/* END: Service page admin action processing section */

/* START: Service page admin view preparation section */
$flash = $_SESSION['service_page_flash'] ?? null;
unset($_SESSION['service_page_flash']);

$selectedPage = $selectedSlug !== '' && isset($pages[$selectedSlug])
    ? $pages[$selectedSlug]
    : null;
$groupedPages = service_admin_group_pages($pages);
$csrfToken = (string) $_SESSION['service_page_csrf'];
/* END: Service page admin view preparation section */
?><!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>সেবাসমূহের পেজ ব্যবস্থাপনা | কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়</title>
  <link rel="stylesheet" href="../assets/css/styles.css?v=20260808-service-pages-1">
</head>
<body class="admin-page">
  <!-- START: Service page admin header section -->
  <header class="admin-header">
    <div class="container">
      <h1>সেবাসমূহের পেজ ব্যবস্থাপনা</h1>
      <nav aria-label="এডমিন নেভিগেশন">
        <a href="index.php">ড্যাশবোর্ড</a>
        <span aria-hidden="true"> | </span>
        <a href="content.php">ওয়েবসাইট কনটেন্ট</a>
        <span aria-hidden="true"> | </span>
        <a href="../index.html" target="_blank" rel="noopener">হোমপেজ</a>
        <span aria-hidden="true"> | </span>
        <a href="index.php?logout=1">লগআউট</a>
      </nav>
    </div>
  </header>
  <!-- END: Service page admin header section -->

  <!-- START: Service page admin workspace section -->
  <main class="container content-page">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
      <div class="admin-message <?= ($flash['type'] ?? '') === 'success' ? 'success' : 'error' ?>">
        <?= school_h((string) $flash['message']) ?>
      </div>
    <?php endif; ?>

    <section class="content-page-card">
      <div class="section-title">
        <div>
          <h2>৫৯টি সেবা পেজের তথ্য ও ফাইল</h2>
          <p>সাব-সেকশন ও লিংকের শিরোনাম অনুযায়ী পেজ নির্বাচন করে তথ্য লিখুন অথবা প্রয়োজনীয় ফাইল সংযুক্ত করুন।</p>
        </div>
      </div>

      <?php if ($selectedPage === null): ?>
        <div class="admin-message error">কোনো সার্ভিস পেজ পাওয়া যায়নি।</div>
      <?php else: ?>
        <div class="service-admin-layout">
          <!-- START: Service page selector section -->
          <aside class="service-admin-sidebar">
            <form method="get" class="editor-panel service-admin-selector">
              <label for="servicePageSelect">পেজ নির্বাচন করুন</label>
              <select id="servicePageSelect" name="page" required>
                <?php foreach ($groupedPages as $sectionTitle => $sectionPages): ?>
                  <optgroup label="<?= school_h($sectionTitle) ?>">
                    <?php foreach ($sectionPages as $slug => $page): ?>
                      <option value="<?= school_h($slug) ?>" <?= $slug === $selectedSlug ? 'selected' : '' ?>>
                        <?= school_h((string) ($page['title'] ?? $slug)) ?>
                      </option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="table-action">পেজ খুলুন</button>
            </form>

            <a
              class="service-admin-page-link"
              href="../<?= school_h((string) ($selectedPage['page'] ?? '')) ?>"
              target="_blank"
              rel="noopener"
            >
              পাবলিক পেজ দেখুন ↗
            </a>

            <?php if (!empty($selectedPage['referenceUrl'])): ?>
              <p class="service-admin-reference">
                পূর্বের দাপ্তরিক উৎস:
                <a
                  href="<?= school_h((string) $selectedPage['referenceUrl']) ?>"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  লিংক দেখুন
                </a>
              </p>
            <?php endif; ?>
          </aside>
          <!-- END: Service page selector section -->

          <!-- START: Service page editing section -->
          <div class="service-admin-workspace">
            <form method="post" class="editor-panel">
              <input type="hidden" name="csrf_token" value="<?= school_h($csrfToken) ?>">
              <input type="hidden" name="page_slug" value="<?= school_h($selectedSlug) ?>">
              <input type="hidden" name="action" value="save_content">

              <div class="form-grid">
                <label>
                  সাব-সেকশনের শিরোনাম
                  <input
                    type="text"
                    name="section_title"
                    value="<?= school_h((string) ($selectedPage['sectionTitle'] ?? '')) ?>"
                    maxlength="250"
                    required
                  >
                </label>

                <label>
                  পেজের শিরোনাম
                  <input
                    type="text"
                    name="title"
                    value="<?= school_h((string) ($selectedPage['title'] ?? '')) ?>"
                    maxlength="250"
                    required
                  >
                </label>

                <label class="full-row">
                  হিরো সেকশনের সংক্ষিপ্ত লেখা
                  <textarea name="intro" rows="4" maxlength="2000"><?= school_h((string) ($selectedPage['intro'] ?? '')) ?></textarea>
                </label>

                <label class="full-row">
                  বিস্তারিত তথ্য
                  <textarea name="content" rows="14" maxlength="30000"><?= school_h((string) ($selectedPage['content'] ?? '')) ?></textarea>
                </label>
              </div>

              <div class="editor-actions">
                <button type="submit">তথ্য সংরক্ষণ করুন</button>
              </div>
              <p class="editor-note">একাধিক অনুচ্ছেদের জন্য লেখার মাঝে একটি খালি লাইন রাখুন।</p>
            </form>

            <!-- START: Service document upload form section -->
            <form method="post" enctype="multipart/form-data" class="editor-panel service-upload-form">
              <input type="hidden" name="csrf_token" value="<?= school_h($csrfToken) ?>">
              <input type="hidden" name="page_slug" value="<?= school_h($selectedSlug) ?>">
              <input type="hidden" name="action" value="upload_document">

              <h3>নতুন ফাইল সংযুক্ত করুন</h3>
              <div class="form-grid">
                <label>
                  ফাইলের প্রদর্শিত শিরোনাম
                  <input type="text" name="document_title" maxlength="250" placeholder="খালি রাখলে ফাইলের নাম দেখাবে">
                </label>

                <label>
                  ফাইল নির্বাচন
                  <input
                    type="file"
                    name="document_file"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.webp,.zip"
                    required
                  >
                </label>

                <label class="full-row">
                  সংক্ষিপ্ত বর্ণনা
                  <textarea name="document_description" rows="3" maxlength="1000"></textarea>
                </label>
              </div>

              <div class="editor-actions">
                <button type="submit">ফাইল আপলোড করুন</button>
              </div>
              <p class="editor-note">সর্বোচ্চ ১২ এমবি। PDF, Office document, text, image ও ZIP ফাইল গ্রহণযোগ্য।</p>
            </form>
            <!-- END: Service document upload form section -->

            <!-- START: Uploaded service document management section -->
            <div class="service-admin-document-list">
              <?php $documents = is_array($selectedPage['documents'] ?? null) ? $selectedPage['documents'] : []; ?>
              <?php if (!$documents): ?>
                <p class="service-document-empty">এই পেজে এখনো কোনো ফাইল সংযুক্ত করা হয়নি।</p>
              <?php else: ?>
                <?php foreach ($documents as $document): ?>
                  <article class="service-admin-document">
                    <div>
                      <strong><?= school_h((string) ($document['title'] ?? 'ফাইল')) ?></strong>
                      <span><?= school_h((string) ($document['originalName'] ?? '')) ?></span>
                    </div>
                    <div class="service-admin-document-actions">
                      <a
                        href="../<?= school_h((string) ($document['file'] ?? '')) ?>"
                        target="_blank"
                        rel="noopener"
                      >
                        দেখুন
                      </a>
                      <form method="post" class="inline-form" onsubmit="return confirm('ফাইলটি মুছে ফেলবেন?');">
                        <input type="hidden" name="csrf_token" value="<?= school_h($csrfToken) ?>">
                        <input type="hidden" name="page_slug" value="<?= school_h($selectedSlug) ?>">
                        <input type="hidden" name="action" value="delete_document">
                        <input type="hidden" name="document_id" value="<?= school_h((string) ($document['id'] ?? '')) ?>">
                        <button type="submit">মুছুন</button>
                      </form>
                    </div>
                  </article>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <!-- END: Uploaded service document management section -->
          </div>
          <!-- END: Service page editing section -->
        </div>
      <?php endif; ?>
    </section>
  </main>
  <!-- END: Service page admin workspace section -->
</body>
</html>
