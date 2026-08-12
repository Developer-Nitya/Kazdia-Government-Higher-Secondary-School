<?php
declare(strict_types=1);

/* START: Service page backend bootstrap section */
require_once __DIR__ . '/config.php';
/* END: Service page backend bootstrap section */

/* START: Service page storage configuration section */
const SCHOOL_SERVICE_PAGE_MAX_FILE_BYTES = 12 * 1024 * 1024;
const SCHOOL_SERVICE_PAGE_ALLOWED_EXTENSIONS = [
    'pdf' => 'PDF',
    'doc' => 'Word',
    'docx' => 'Word',
    'xls' => 'Excel',
    'xlsx' => 'Excel',
    'ppt' => 'PowerPoint',
    'pptx' => 'PowerPoint',
    'txt' => 'Text',
    'csv' => 'CSV',
    'jpg' => 'Image',
    'jpeg' => 'Image',
    'png' => 'Image',
    'gif' => 'Image',
    'webp' => 'Image',
    'zip' => 'ZIP',
];

function school_service_pages_file(): string
{
    return dirname(__DIR__) . '/storage/service-pages.json';
}

function school_service_pages_defaults_file(): string
{
    return dirname(__DIR__) . '/storage/service-pages-defaults.json';
}

function school_service_upload_root(): string
{
    return dirname(__DIR__) . '/uploads/service-pages';
}

function school_service_public_upload_prefix(): string
{
    return 'uploads/service-pages/';
}
/* END: Service page storage configuration section */

/* START: Service page data normalization section */
function school_service_slug_is_valid(string $slug): bool
{
    return preg_match('/^[a-z0-9-]+$/i', $slug) === 1;
}

function school_service_read_json_file(string $file, array $fallback): array
{
    if (!is_file($file)) {
        return $fallback;
    }

    $json = file_get_contents($file);
    $decoded = json_decode($json ?: '{}', true);

    return is_array($decoded) ? $decoded : $fallback;
}

function school_service_normalize_document(array $document): array
{
    return [
        'id' => trim((string) ($document['id'] ?? '')),
        'title' => trim((string) ($document['title'] ?? '')),
        'description' => trim((string) ($document['description'] ?? '')),
        'file' => trim((string) ($document['file'] ?? '')),
        'originalName' => trim((string) ($document['originalName'] ?? '')),
        'uploadedAt' => trim((string) ($document['uploadedAt'] ?? '')),
    ];
}

function school_service_normalize_page(string $slug, array $page, array $fallback = []): array
{
    $documents = $page['documents'] ?? ($fallback['documents'] ?? []);
    $normalizedDocuments = [];

    if (is_array($documents)) {
        foreach ($documents as $document) {
            if (!is_array($document)) {
                continue;
            }

            $normalized = school_service_normalize_document($document);

            if ($normalized['id'] !== '' && $normalized['file'] !== '') {
                $normalizedDocuments[] = $normalized;
            }
        }
    }

    return [
        'slug' => $slug,
        'page' => trim((string) ($page['page'] ?? ($fallback['page'] ?? ('pages/' . $slug . '.html')))),
        'sectionNumber' => (int) ($page['sectionNumber'] ?? ($fallback['sectionNumber'] ?? 0)),
        'itemNumber' => (int) ($page['itemNumber'] ?? ($fallback['itemNumber'] ?? 0)),
        'sectionTitle' => trim((string) ($page['sectionTitle'] ?? ($fallback['sectionTitle'] ?? 'সেবাসমূহ'))),
        'title' => trim((string) ($page['title'] ?? ($fallback['title'] ?? 'সেবা তথ্য'))),
        'intro' => trim((string) ($page['intro'] ?? ($fallback['intro'] ?? ''))),
        'content' => trim((string) ($page['content'] ?? ($fallback['content'] ?? ''))),
        'referenceUrl' => trim((string) ($page['referenceUrl'] ?? ($fallback['referenceUrl'] ?? ''))),
        'documents' => $normalizedDocuments,
    ];
}
/* END: Service page data normalization section */

/* START: Service page JSON database section */
function school_service_default_payload(): array
{
    $fallback = ['version' => 1, 'pages' => []];
    $payload = school_service_read_json_file(school_service_pages_defaults_file(), $fallback);

    if (!isset($payload['pages']) || !is_array($payload['pages'])) {
        $payload['pages'] = [];
    }

    return $payload;
}

function school_ensure_service_page_storage(): void
{
    $storageDirectory = dirname(school_service_pages_file());

    if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0775, true) && !is_dir($storageDirectory)) {
        throw new RuntimeException('সার্ভিস পেজের storage ফোল্ডার তৈরি করা যায়নি।');
    }

    if (!is_dir(school_service_upload_root()) && !mkdir(school_service_upload_root(), 0775, true) && !is_dir(school_service_upload_root())) {
        throw new RuntimeException('সার্ভিস পেজের upload ফোল্ডার তৈরি করা যায়নি।');
    }

    if (!is_file(school_service_pages_file())) {
        school_write_service_pages(school_service_default_payload());
    }
}

function school_read_service_pages(): array
{
    school_ensure_service_page_storage();

    $defaults = school_service_default_payload();
    $stored = school_service_read_json_file(school_service_pages_file(), []);
    $defaultPages = is_array($defaults['pages'] ?? null) ? $defaults['pages'] : [];
    $storedPages = is_array($stored['pages'] ?? null) ? $stored['pages'] : [];
    $pages = [];

    foreach ($defaultPages as $slug => $defaultPage) {
        if (!is_string($slug) || !school_service_slug_is_valid($slug) || !is_array($defaultPage)) {
            continue;
        }

        $storedPage = isset($storedPages[$slug]) && is_array($storedPages[$slug])
            ? $storedPages[$slug]
            : [];

        $pages[$slug] = school_service_normalize_page(
            $slug,
            array_merge($defaultPage, $storedPage),
            $defaultPage
        );
    }

    foreach ($storedPages as $slug => $storedPage) {
        if (
            isset($pages[$slug])
            || !is_string($slug)
            || !school_service_slug_is_valid($slug)
            || !is_array($storedPage)
        ) {
            continue;
        }

        $pages[$slug] = school_service_normalize_page($slug, $storedPage);
    }

    uasort($pages, static function (array $left, array $right): int {
        $leftOrder = ((int) $left['sectionNumber'] * 100) + (int) $left['itemNumber'];
        $rightOrder = ((int) $right['sectionNumber'] * 100) + (int) $right['itemNumber'];

        return $leftOrder <=> $rightOrder;
    });

    return [
        'version' => 1,
        'pages' => $pages,
    ];
}

function school_write_service_pages(array $payload): void
{
    $storageDirectory = dirname(school_service_pages_file());

    if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0775, true) && !is_dir($storageDirectory)) {
        throw new RuntimeException('সার্ভিস পেজের storage ফোল্ডার তৈরি করা যায়নি।');
    }

    $pages = is_array($payload['pages'] ?? null) ? $payload['pages'] : [];
    $normalizedPages = [];

    foreach ($pages as $slug => $page) {
        if (!is_string($slug) || !school_service_slug_is_valid($slug) || !is_array($page)) {
            continue;
        }

        $normalizedPages[$slug] = school_service_normalize_page($slug, $page);
    }

    $encoded = json_encode(
        ['version' => 1, 'pages' => $normalizedPages],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($encoded === false) {
        throw new RuntimeException('সার্ভিস পেজের তথ্য JSON আকারে তৈরি করা যায়নি।');
    }

    $temporaryFile = school_service_pages_file() . '.tmp';
    $writtenBytes = file_put_contents($temporaryFile, $encoded, LOCK_EX);

    if ($writtenBytes === false) {
        throw new RuntimeException('সার্ভিস পেজের অস্থায়ী ডাটাবেজ ফাইল লেখা যায়নি।');
    }

    if (!rename($temporaryFile, school_service_pages_file())) {
        @unlink($temporaryFile);
        throw new RuntimeException('সার্ভিস পেজের JSON ডাটাবেজ হালনাগাদ করা যায়নি।');
    }

    @chmod(school_service_pages_file(), 0664);
}

function school_find_service_page(string $slug): ?array
{
    if (!school_service_slug_is_valid($slug)) {
        return null;
    }

    $payload = school_read_service_pages();
    $page = $payload['pages'][$slug] ?? null;

    return is_array($page) ? $page : null;
}
/* END: Service page JSON database section */

/* START: Service page document upload section */
function school_service_safe_original_name(string $name): string
{
    $baseName = basename(str_replace('\\', '/', $name));
    $cleanName = preg_replace('/[\x00-\x1F\x7F]+/u', '', $baseName);

    return trim((string) $cleanName) ?: 'document';
}

function school_upload_service_document(string $slug, array $file): array
{
    if (!school_service_slug_is_valid($slug)) {
        throw new RuntimeException('সার্ভিস পেজ শনাক্ত করা যায়নি।');
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('আপলোড করার জন্য একটি ফাইল নির্বাচন করুন।');
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('ফাইল আপলোডে সমস্যা হয়েছে।');
    }

    if ((int) ($file['size'] ?? 0) <= 0 || (int) ($file['size'] ?? 0) > SCHOOL_SERVICE_PAGE_MAX_FILE_BYTES) {
        throw new RuntimeException('ফাইলের সাইজ ১২ এমবি-এর মধ্যে রাখুন।');
    }

    $originalName = school_service_safe_original_name((string) ($file['name'] ?? 'document'));
    $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

    if (!isset(SCHOOL_SERVICE_PAGE_ALLOWED_EXTENSIONS[$extension])) {
        throw new RuntimeException('শুধু PDF, Office document, text, image অথবা ZIP ফাইল আপলোড করুন।');
    }

    $temporaryName = (string) ($file['tmp_name'] ?? '');

    if ($temporaryName === '' || !is_uploaded_file($temporaryName)) {
        throw new RuntimeException('আপলোডকৃত ফাইল যাচাই করা যায়নি।');
    }

    school_ensure_service_page_storage();

    $pageDirectory = school_service_upload_root() . '/' . $slug;

    if (!is_dir($pageDirectory) && !mkdir($pageDirectory, 0775, true) && !is_dir($pageDirectory)) {
        throw new RuntimeException('ফাইল সংরক্ষণের ফোল্ডার তৈরি করা যায়নি।');
    }

    $safeName = 'document-' . date('Ymd-His') . '-' . bin2hex(random_bytes(5)) . '.' . $extension;
    $target = $pageDirectory . '/' . $safeName;

    if (!move_uploaded_file($temporaryName, $target)) {
        throw new RuntimeException('ফাইল সার্ভারে সংরক্ষণ করা যায়নি।');
    }

    @chmod($target, 0664);

    return [
        'file' => school_service_public_upload_prefix() . $slug . '/' . $safeName,
        'originalName' => $originalName,
    ];
}

function school_remove_service_document_file(string $path): void
{
    $prefix = school_service_public_upload_prefix();

    if (strpos($path, $prefix) !== 0) {
        return;
    }

    $relativePath = substr($path, strlen($prefix));
    $segments = array_values(array_filter(explode('/', str_replace('\\', '/', $relativePath)), 'strlen'));

    if (count($segments) !== 2 || !school_service_slug_is_valid($segments[0])) {
        return;
    }

    $file = school_service_upload_root() . '/' . $segments[0] . '/' . basename($segments[1]);

    if (is_file($file)) {
        @unlink($file);
    }
}
/* END: Service page document upload section */
