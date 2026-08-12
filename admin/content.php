<?php
declare(strict_types=1);

/* START: Admin content bootstrap section */
session_start();
require_once __DIR__ . '/../backend/helpers.php';
/* END: Admin content bootstrap section */

/* START: Admin content utility section */
function admin_uploaded_file(string $field, ?int $index = null): array
{
    if (!isset($_FILES[$field])) {
        return ['error' => UPLOAD_ERR_NO_FILE];
    }

    $file = $_FILES[$field];

    if ($index === null) {
        return is_array($file) ? $file : ['error' => UPLOAD_ERR_NO_FILE];
    }

    if (!isset($file['name'][$index])) {
        return ['error' => UPLOAD_ERR_NO_FILE];
    }

    return [
        'name' => $file['name'][$index],
        'type' => $file['type'][$index] ?? '',
        'tmp_name' => $file['tmp_name'][$index] ?? '',
        'error' => $file['error'][$index] ?? UPLOAD_ERR_NO_FILE,
        'size' => $file['size'][$index] ?? 0,
    ];
}

function admin_post_array(string $key): array
{
    $value = $_POST[$key] ?? [];
    return is_array($value) ? $value : [];
}

function admin_parse_link_lines(string $text): array
{
    $items = [];
    $lines = preg_split('/\R/u', $text) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = array_map('trim', explode('|', $line, 2));
        $items[] = [
            'title' => $parts[0] ?? '',
            'url' => $parts[1] ?? '#',
        ];
    }

    return $items;
}

function admin_parse_notice_lines(string $text): array
{
    $items = [];
    $lines = preg_split('/\R/u', $text) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = array_map('trim', explode('|', $line, 3));
        $items[] = [
            'title' => $parts[0] ?? '',
            'category' => $parts[1] ?? 'নোটিশ',
            'url' => $parts[2] ?? '#',
        ];
    }

    return $items;
}

function admin_parse_info_rows(string $text): array
{
    $rows = [];
    $lines = preg_split('/\R/u', $text) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = array_map('trim', explode('|', $line, 2));
        $rows[] = [$parts[0] ?? '', $parts[1] ?? ''];
    }

    return $rows;
}

function admin_format_link_lines(array $items): string
{
    return implode("\n", array_map(static function (array $item): string {
        return trim((string) ($item['title'] ?? '')) . ' | ' . trim((string) ($item['url'] ?? '#'));
    }, $items));
}

function admin_format_notice_lines(array $items): string
{
    return implode("\n", array_map(static function (array $item): string {
        return trim((string) ($item['title'] ?? '')) . ' | ' . trim((string) ($item['category'] ?? 'নোটিশ')) . ' | ' . trim((string) ($item['url'] ?? '#'));
    }, $items));
}

function admin_format_info_rows(array $rows): string
{
    return implode("\n", array_map(static function (array $row): string {
        return trim((string) ($row[0] ?? '')) . ' | ' . trim((string) ($row[1] ?? ''));
    }, $rows));
}

/* START: Academic resource editor utility section */
function admin_parse_resource_link_lines(string $text): array
{
    $items = [];
    $lines = preg_split('/\R/u', $text) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = array_map('trim', explode('|', $line, 3));
        $items[] = [
            'icon' => $parts[0] !== '' ? $parts[0] : '🔗',
            'title' => $parts[1] ?? '',
            'url' => $parts[2] ?? '#',
        ];
    }

    return $items;
}

function admin_format_resource_link_lines(array $items): string
{
    return implode("\n", array_map(static function (array $item): string {
        return trim((string) ($item['icon'] ?? '🔗'))
            . ' | ' . trim((string) ($item['title'] ?? ''))
            . ' | ' . trim((string) ($item['url'] ?? '#'));
    }, $items));
}

function admin_parse_table_columns(string $text): array
{
    $columns = array_map('trim', explode('|', trim($text)));
    return array_values(array_filter($columns, static fn(string $column): bool => $column !== ''));
}

function admin_parse_table_rows(string $text): array
{
    $rows = [];
    $lines = preg_split('/\R/u', $text) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $rows[] = array_map('trim', explode('|', $line));
    }

    return $rows;
}

function admin_format_table_columns(array $columns): string
{
    return implode(' | ', array_map(static fn($column): string => trim((string) $column), $columns));
}

function admin_format_table_rows(array $rows): string
{
    return implode("\n", array_map(static function (array $row): string {
        return implode(' | ', array_map(static fn($cell): string => trim((string) $cell), $row));
    }, $rows));
}
/* END: Academic resource editor utility section */

function admin_json_pretty(array $data): string
{
    return (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

function admin_image_preview_src(string $path): string
{
    $value = trim($path);

    if ($value === '' || preg_match('/^(https?:|data:|\/)/i', $value)) {
        return $value;
    }

    return '../' . ltrim($value, '/');
}

/* START: Gallery album upload utility section */
function admin_uploaded_files_for_index(string $field, ?int $index = null): array
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
        return [];
    }

    $files = $_FILES[$field];

    if ($index === null) {
        $names = $files['name'] ?? [];
        if (!is_array($names)) {
            return [];
        }

        $result = [];
        foreach ($names as $fileIndex => $name) {
            $result[] = [
                'name' => $name,
                'type' => $files['type'][$fileIndex] ?? '',
                'tmp_name' => $files['tmp_name'][$fileIndex] ?? '',
                'error' => $files['error'][$fileIndex] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$fileIndex] ?? 0,
            ];
        }

        return $result;
    }

    $names = $files['name'][$index] ?? [];
    if (!is_array($names)) {
        return [];
    }

    $result = [];
    foreach ($names as $fileIndex => $name) {
        $result[] = [
            'name' => $name,
            'type' => $files['type'][$index][$fileIndex] ?? '',
            'tmp_name' => $files['tmp_name'][$index][$fileIndex] ?? '',
            'error' => $files['error'][$index][$fileIndex] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index][$fileIndex] ?? 0,
        ];
    }

    return $result;
}

function admin_parse_image_lines(string $text): array
{
    $items = [];
    $lines = preg_split('/\R/u', $text) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $items[] = $line;
        }
    }

    return $items;
}

function admin_normalize_gallery_images($images, string $coverImage = ''): array
{
    $items = [];

    if (is_array($images)) {
        foreach ($images as $image) {
            if (is_array($image)) {
                $value = trim((string) ($image['image'] ?? $image['url'] ?? ''));
            } else {
                $value = trim((string) $image);
            }

            if ($value !== '' && !in_array($value, $items, true)) {
                $items[] = $value;
            }
        }
    }

    $cover = trim($coverImage);
    if ($cover !== '' && !in_array($cover, $items, true)) {
        array_unshift($items, $cover);
    }

    return array_values($items);
}

function admin_format_image_lines($images, string $coverImage = ''): string
{
    return implode("\n", admin_normalize_gallery_images($images, $coverImage));
}

function admin_upload_gallery_album_images(array $files): array
{
    $uploaded = [];

    foreach ($files as $file) {
        $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($errorCode === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $uploaded[] = school_upload_generic_image($file, '');
    }

    return $uploaded;
}
/* END: Gallery album upload utility section */
/* END: Admin content utility section */

/* START: Admin authentication section */
$message = '';
$error = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: content.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_password'])) {
    if (hash_equals(SCHOOL_ADMIN_PASSWORD, (string) $_POST['login_password'])) {
        $_SESSION['school_admin_logged_in'] = true;
        header('Location: content.php');
        exit;
    }

    $error = 'পাসওয়ার্ড সঠিক নয়।';
}

$loggedIn = !empty($_SESSION['school_admin_logged_in']);
$content = $loggedIn ? school_read_site_content() : school_default_site_content();
/* END: Admin authentication section */

/* START: Admin save action section */
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $action = (string) $_POST['action'];

        if ($action === 'save_settings') {
            $settingsPost = admin_post_array('settings');
            $settings = $content['siteSettings'] ?? [];

            foreach (['institutionName', 'slogan', 'eiin', 'established', 'email', 'phone', 'address', 'officeHours'] as $field) {
                $settings[$field] = trim((string) ($settingsPost[$field] ?? ''));
            }

            $settings['logo'] = school_upload_generic_image(admin_uploaded_file('logo_file'), trim((string) ($settingsPost['logo'] ?? ($settings['logo'] ?? 'assets/img/logo.jpg?v=20260727-1'))));

            $bannerImages = [];
            $postedBanners = admin_post_array('bannerImages');
            for ($i = 0; $i < 3; $i++) {
                $existing = trim((string) ($postedBanners[$i] ?? (($settings['bannerImages'][$i] ?? ''))));
                $bannerImages[$i] = school_upload_generic_image(admin_uploaded_file('banner_files', $i), $existing);
            }
            $settings['bannerImages'] = $bannerImages;

            $content['siteSettings'] = $settings;
            school_write_site_content($content);
            $message = 'প্রতিষ্ঠানের মূল সেটিংস সংরক্ষণ করা হয়েছে।';
        }

        /* START: Professional footer save handler section */
        if ($action === 'save_footer') {
            $footerPost = admin_post_array('footer');
            $footer = $content['footer'] ?? [];

            foreach ([
                'homeUrl',
                'description',
                'mapUrl',
                'privacyUrl',
                'termsUrl',
                'lastUpdated',
                'developerLabel',
                'developerName',
            ] as $field) {
                $footer[$field] = trim((string) ($footerPost[$field] ?? ''));
            }

            $footer['quickLinks'] = admin_parse_link_lines((string) ($_POST['footerQuickLinksText'] ?? ''));
            $footer['officialLinks'] = admin_parse_link_lines((string) ($_POST['footerOfficialLinksText'] ?? ''));

            $socialLinks = [];
            foreach (admin_post_array('footerSocialLinks') as $socialLink) {
                if (!is_array($socialLink)) {
                    continue;
                }

                $title = trim((string) ($socialLink['title'] ?? ''));
                $url = trim((string) ($socialLink['url'] ?? ''));
                $icon = trim((string) ($socialLink['icon'] ?? 'website'));

                if ($title === '' && $url === '') {
                    continue;
                }

                $socialLinks[] = [
                    'title' => $title,
                    'url' => $url,
                    'icon' => in_array($icon, ['facebook', 'youtube', 'website'], true) ? $icon : 'website',
                ];
            }

            $footer['socialLinks'] = $socialLinks;
            $content['footer'] = $footer;
            school_write_site_content($content);
            $message = 'প্রফেশনাল ফুটারের তথ্য সংরক্ষণ করা হয়েছে।';
        }
        /* END: Professional footer save handler section */

        if ($action === 'save_home') {
            $home = $content['home'] ?? [];

            $slidesPost = admin_post_array('slides');
            $slides = [];
            foreach ($slidesPost as $index => $slide) {
                if (!is_array($slide)) {
                    continue;
                }

                $image = school_upload_generic_image(admin_uploaded_file('slide_image_files', (int) $index), trim((string) ($slide['image'] ?? '')));
                $slides[] = [
                    'title' => trim((string) ($slide['title'] ?? '')),
                    'text' => trim((string) ($slide['text'] ?? '')),
                    'image' => $image,
                    'alt' => trim((string) ($slide['alt'] ?? '')),
                ];
            }

            $cardsPost = admin_post_array('educationCards');
            $cards = [];
            foreach ($cardsPost as $card) {
                if (!is_array($card)) {
                    continue;
                }

                $cards[] = [
                    'icon' => trim((string) ($card['icon'] ?? '📘')),
                    'title' => trim((string) ($card['title'] ?? '')),
                    'text' => trim((string) ($card['text'] ?? '')),
                    'url' => trim((string) ($card['url'] ?? '#')),
                ];
            }

            $profilesPost = admin_post_array('profiles');
            $profiles = [];
            foreach ($profilesPost as $index => $profile) {
                if (!is_array($profile)) {
                    continue;
                }

                $image = school_upload_generic_image(admin_uploaded_file('profile_image_files', (int) $index), trim((string) ($profile['image'] ?? '')));
                $profiles[] = [
                    'role' => trim((string) ($profile['role'] ?? '')),
                    'name' => trim((string) ($profile['name'] ?? '')),
                    'text' => trim((string) ($profile['text'] ?? '')),
                    'image' => $image,
                    'url' => trim((string) ($profile['url'] ?? '#')),
                ];
            }

            $home['slides'] = $slides;
            $home['educationCards'] = $cards;
            $home['profiles'] = $profiles;
            $home['importantLinks'] = admin_parse_link_lines((string) ($_POST['importantLinksText'] ?? ''));
            $home['officialLinks'] = admin_parse_link_lines((string) ($_POST['officialLinksText'] ?? ''));

            /* START: Focal point and hotline save handler section */
            $focalPointPost = admin_post_array('focalPoint');
            $currentFocalPoint = is_array($home['focalPoint'] ?? null) ? $home['focalPoint'] : [];
            $home['focalPoint'] = [
                'title' => trim((string) ($focalPointPost['title'] ?? 'ফোকাল পয়েন্ট')),
                'name' => trim((string) ($focalPointPost['name'] ?? '')),
                'designation' => trim((string) ($focalPointPost['designation'] ?? '')),
                'phone' => trim((string) ($focalPointPost['phone'] ?? '')),
                'image' => school_upload_generic_image(
                    admin_uploaded_file('focal_point_image_file'),
                    trim((string) ($focalPointPost['image'] ?? ($currentFocalPoint['image'] ?? 'assets/img/focal-point.svg')))
                ),
            ];

            $hotlinePost = admin_post_array('hotline');
            $home['hotline'] = [
                'title' => trim((string) ($hotlinePost['title'] ?? 'হটলাইন')),
                'label' => trim((string) ($hotlinePost['label'] ?? 'জরুরি যোগাযোগ নম্বর')),
                'phone' => trim((string) ($hotlinePost['phone'] ?? '')),
            ];
            /* END: Focal point and hotline save handler section */

            $content['home'] = $home;
            school_write_site_content($content);
            $message = 'হোমপেজের এডিটেবল অংশ সংরক্ষণ করা হয়েছে।';
        }

        if ($action === 'save_pages') {
            $pagesPost = admin_post_array('pages');

            $content['pages']['briefHistory'] = [
                'title' => trim((string) ($pagesPost['briefHistory']['title'] ?? 'সংক্ষিপ্ত বিবরণ')),
                'subtitle' => trim((string) ($pagesPost['briefHistory']['subtitle'] ?? '')),
                'text' => trim((string) ($pagesPost['briefHistory']['text'] ?? '')),
            ];

            $content['pages']['contact'] = [
                'title' => trim((string) ($pagesPost['contact']['title'] ?? 'যোগাযোগ')),
                'subtitle' => trim((string) ($pagesPost['contact']['subtitle'] ?? '')),
                'quickTitle' => trim((string) ($pagesPost['contact']['quickTitle'] ?? 'দ্রুত বার্তা')),
                'quickText' => trim((string) ($pagesPost['contact']['quickText'] ?? '')),
                'buttonText' => trim((string) ($pagesPost['contact']['buttonText'] ?? 'ইমেইল পাঠান')),
            ];

            school_write_site_content($content);
            $message = 'সংক্ষিপ্ত বিবরণ ও যোগাযোগ পেজ সংরক্ষণ করা হয়েছে।';
        }

        if ($action === 'save_notices') {
            $content['notices'] = admin_parse_notice_lines((string) ($_POST['noticesText'] ?? ''));
            school_write_site_content($content);
            $message = 'নোটিশ বোর্ড সংরক্ষণ করা হয়েছে।';
        }

        if ($action === 'save_services') {
            $servicesPost = admin_post_array('services');
            $services = [];

            foreach ($servicesPost as $index => $service) {
                if (!is_array($service)) {
                    continue;
                }

                $title = trim((string) ($service['title'] ?? ''));
                $image = school_upload_generic_image(admin_uploaded_file('service_image_files', (int) $index), trim((string) ($service['image'] ?? '')));
                $items = admin_parse_link_lines((string) ($service['itemsText'] ?? ''));

                if ($title !== '' || $items) {
                    $services[] = [
                        'title' => $title,
                        'image' => $image,
                        'items' => $items,
                    ];
                }
            }

            $content['services'] = $services;
            school_write_site_content($content);
            $message = 'সেবা সমূহ সংরক্ষণ করা হয়েছে।';
        }

        if ($action === 'save_gallery') {
            /* START: Gallery album save handler section */
            $titles = admin_post_array('galleryTitle');
            $images = admin_post_array('galleryImage');
            $albumImagesText = admin_post_array('galleryImagesText');
            $deletes = admin_post_array('galleryDelete');
            $gallery = [];

            foreach ($titles as $index => $title) {
                if (isset($deletes[$index])) {
                    continue;
                }

                $cleanTitle = trim((string) $title);
                $image = school_upload_generic_image(admin_uploaded_file('gallery_image_files', (int) $index), trim((string) ($images[$index] ?? '')));
                $typedAlbumImages = admin_parse_image_lines((string) ($albumImagesText[$index] ?? ''));
                $uploadedAlbumImages = admin_upload_gallery_album_images(admin_uploaded_files_for_index('gallery_album_files', (int) $index));
                $albumImages = admin_normalize_gallery_images(array_merge($typedAlbumImages, $uploadedAlbumImages), $image);
                if ($image === '' && $albumImages) {
                    $image = $albumImages[0];
                }

                if ($cleanTitle !== '' || $image !== '' || $albumImages) {
                    $gallery[] = [
                        'title' => $cleanTitle,
                        'image' => $image,
                        'images' => $albumImages,
                    ];
                }
            }

            $newTitle = trim((string) ($_POST['newGalleryTitle'] ?? ''));
            $newImageText = trim((string) ($_POST['newGalleryImage'] ?? ''));
            $newImage = school_upload_generic_image(admin_uploaded_file('new_gallery_file'), $newImageText);
            $newTypedAlbumImages = admin_parse_image_lines((string) ($_POST['newGalleryImagesText'] ?? ''));
            $newUploadedAlbumImages = admin_upload_gallery_album_images(admin_uploaded_files_for_index('new_gallery_album_files'));
            $newAlbumImages = admin_normalize_gallery_images(array_merge($newTypedAlbumImages, $newUploadedAlbumImages), $newImage);
            if ($newImage === '' && $newAlbumImages) {
                $newImage = $newAlbumImages[0];
            }

            if ($newTitle !== '' || $newImage !== '' || $newAlbumImages) {
                $gallery[] = [
                    'title' => $newTitle !== '' ? $newTitle : 'নতুন অ্যালবাম',
                    'image' => $newImage,
                    'images' => $newAlbumImages,
                ];
            }

            $content['gallery'] = $gallery;
            school_write_site_content($content);
            $message = 'গ্যালারি অ্যালবাম সংরক্ষণ করা হয়েছে।';
            /* END: Gallery album save handler section */
        }

        if ($action === 'save_programs') {
            $programsPost = admin_post_array('programs');
            $programs = $content['programs'] ?? [];

            foreach ($programsPost as $slug => $programPost) {
                if (!is_array($programPost)) {
                    continue;
                }

                $existing = is_array($programs[$slug] ?? null) ? $programs[$slug] : [];
                $officerPost = is_array($programPost['contactOfficer'] ?? null) ? $programPost['contactOfficer'] : [];
                $officerImage = school_upload_generic_image(admin_uploaded_file('program_officer_files', array_search($slug, array_keys($programsPost), true) ?: 0), trim((string) ($officerPost['image'] ?? ($existing['contactOfficer']['image'] ?? ''))));

                $programs[$slug] = [
                    'page' => trim((string) ($programPost['page'] ?? ($existing['page'] ?? ''))),
                    'title' => trim((string) ($programPost['title'] ?? '')),
                    'subtitle' => trim((string) ($programPost['subtitle'] ?? '')),
                    'infoRows' => admin_parse_info_rows((string) ($programPost['infoRowsText'] ?? '')),
                    'notices' => admin_parse_notice_lines((string) ($programPost['noticesText'] ?? '')),
                    'resourceLinks' => admin_parse_resource_link_lines((string) ($programPost['resourceLinksText'] ?? '')),
                    'contactOfficer' => [
                        'role' => trim((string) ($officerPost['role'] ?? 'যোগাযোগ কর্মকর্তা')),
                        'name' => trim((string) ($officerPost['name'] ?? '')),
                        'text' => trim((string) ($officerPost['text'] ?? '')),
                        'image' => $officerImage,
                        'url' => trim((string) ($officerPost['url'] ?? 'contact.html')),
                    ],
                ];
            }

            $content['programs'] = $programs;
            school_write_site_content($content);
            $message = 'একাডেমিক পেজসমূহ সংরক্ষণ করা হয়েছে।';
        }

        /* START: Academic resource page save handler section */
        if ($action === 'save_program_resources') {
            $resourcesPost = admin_post_array('programResources');
            $resources = is_array($content['programResources'] ?? null) ? $content['programResources'] : [];

            foreach ($resourcesPost as $slug => $resourcePost) {
                if (!is_array($resourcePost)) {
                    continue;
                }

                $existing = is_array($resources[$slug] ?? null) ? $resources[$slug] : [];
                $columns = admin_parse_table_columns((string) ($resourcePost['columnsText'] ?? ''));
                $rows = admin_parse_table_rows((string) ($resourcePost['rowsText'] ?? ''));

                $resources[$slug] = [
                    'page' => trim((string) ($resourcePost['page'] ?? ($existing['page'] ?? ''))),
                    'program' => trim((string) ($resourcePost['program'] ?? ($existing['program'] ?? ''))),
                    'type' => trim((string) ($resourcePost['type'] ?? ($existing['type'] ?? ''))),
                    'icon' => trim((string) ($resourcePost['icon'] ?? ($existing['icon'] ?? '📄'))),
                    'title' => trim((string) ($resourcePost['title'] ?? '')),
                    'subtitle' => trim((string) ($resourcePost['subtitle'] ?? '')),
                    'description' => trim((string) ($resourcePost['description'] ?? '')),
                    'columns' => $columns,
                    'rows' => $rows,
                    'note' => trim((string) ($resourcePost['note'] ?? '')),
                ];
            }

            $content['programResources'] = $resources;
            school_write_site_content($content);
            $message = 'একাডেমিক তথ্যের বিস্তারিত পেজসমূহ সংরক্ষণ করা হয়েছে।';
        }
        /* END: Academic resource page save handler section */

        if ($action === 'save_json') {
            $decoded = json_decode((string) ($_POST['siteJson'] ?? ''), true);

            if (!is_array($decoded)) {
                throw new RuntimeException('JSON ফরম্যাট সঠিক নয়।');
            }

            school_write_site_content($decoded);
            $message = 'অ্যাডভান্সড JSON ডাটাবেজ সংরক্ষণ করা হয়েছে।';
        }

        $content = school_read_site_content();
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        $content = school_read_site_content();
    }
}
/* END: Admin save action section */

$settings = $content['siteSettings'] ?? [];
$footer = $content['footer'] ?? [];
$home = $content['home'] ?? [];
$pages = $content['pages'] ?? [];
$programLabels = [
    'secondary' => 'মাধ্যমিক শিক্ষা',
    'secondary-vocational' => 'মাধ্যমিক (ভোকেশনাল)',
    'higher-secondary' => 'উচ্চ মাধ্যমিক',
    'higher-secondary-bm' => 'উচ্চ মাধ্যমিক (বিএম)',
];
?><!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>সম্পূর্ণ ওয়েবসাইট কনটেন্ট | এডমিন প্যানেল</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-page">
  <!-- START: Admin header section -->
  <header class="admin-header">
    <div class="container">
      <h1>সম্পূর্ণ ওয়েবসাইট কনটেন্ট ব্যবস্থাপনা</h1>
      <nav aria-label="এডমিন নেভিগেশন">
        <a href="index.php">ড্যাশবোর্ড</a>
        <span aria-hidden="true"> | </span>
        <a href="../index.html" target="_blank" rel="noopener">হোমপেজ</a>
        <?php if ($loggedIn): ?>
          <span aria-hidden="true"> | </span>
          <a href="former-heads.php">সাবেক প্রতিষ্ঠান প্রধানগণ</a>
          <span aria-hidden="true"> | </span>
          <a href="national-anthem.php">জাতীয় সংগীত</a>
          <span aria-hidden="true"> | </span>
          <a href="content.php?logout=1">লগআউট</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <!-- END: Admin header section -->

  <!-- START: Admin main content section -->
  <main class="container content-page">
    <?php if ($message): ?>
      <div class="admin-message success"><?= school_h($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="admin-message error"><?= school_h($error) ?></div>
    <?php endif; ?>

    <?php if (!$loggedIn): ?>
      <!-- START: Login form section -->
      <section class="content-page-card login-card">
        <div class="section-title">
          <div>
            <h2>এডমিন লগইন</h2>
            <p>ওয়েবসাইটের কনটেন্ট এডিট করতে লগইন করুন।</p>
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
      <!-- END: Login form section -->
    <?php else: ?>
      <!-- START: Admin quick navigation section -->
      <section class="content-page-card">
        <div class="section-title">
          <div>
            <h2>দ্রুত এডিট মেনু</h2>
            <p>প্রতিটি সেকশন আলাদা করে সংরক্ষণ করুন। এতে ভুল হলে শুধু সংশ্লিষ্ট অংশ পরিবর্তন হবে।</p>
          </div>
        </div>
        <div class="admin-section-nav">
          <a href="#settings">মূল সেটিংস</a>
          <a href="#footer">ফুটার</a>
          <a href="#home">হোমপেজ</a>
          <a href="#pages">ইনফো পেজ</a>
          <a href="#notices">নোটিশ</a>
          <a href="#services">সেবা</a>
          <a href="#gallery">গ্যালারি</a>
          <a href="#programs">একাডেমিক পেজ</a>
          <a href="#program-resources">একাডেমিক তথ্যপেজ</a>
          <a href="#advanced">JSON</a>
        </div>
      </section>
      <!-- END: Admin quick navigation section -->

      <!-- START: Site settings edit section -->
      <section id="settings" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>প্রতিষ্ঠানের মূল সেটিংস</h2>
            <p>নাম, EIIN, ইমেইল, ঠিকানা, লোগো ও ব্যানার ছবি সব পেজে আপডেট হবে।</p>
          </div>
        </div>
        <form method="post" enctype="multipart/form-data" class="editor-panel">
          <input type="hidden" name="action" value="save_settings">
          <div class="form-grid">
            <label>প্রতিষ্ঠানের নাম <input type="text" name="settings[institutionName]" value="<?= school_h((string) ($settings['institutionName'] ?? '')) ?>" required></label>
            <label>স্লোগান <input type="text" name="settings[slogan]" value="<?= school_h((string) ($settings['slogan'] ?? '')) ?>"></label>
            <label>EIIN <input type="text" name="settings[eiin]" value="<?= school_h((string) ($settings['eiin'] ?? '')) ?>"></label>
            <label>স্থাপিত <input type="text" name="settings[established]" value="<?= school_h((string) ($settings['established'] ?? '')) ?>"></label>
            <label>ইমেইল <input type="email" name="settings[email]" value="<?= school_h((string) ($settings['email'] ?? '')) ?>"></label>
            <label>ফোন <input type="text" name="settings[phone]" value="<?= school_h((string) ($settings['phone'] ?? '')) ?>"></label>
            <label class="full-row">ঠিকানা <textarea name="settings[address]" rows="2"><?= school_h((string) ($settings['address'] ?? '')) ?></textarea></label>
            <label>অফিস সময় <input type="text" name="settings[officeHours]" value="<?= school_h((string) ($settings['officeHours'] ?? '')) ?>"></label>
            <label>লোগো path <input type="text" name="settings[logo]" value="<?= school_h((string) ($settings['logo'] ?? 'assets/img/logo.jpg?v=20260727-1')) ?>"></label>
            <label>নতুন লোগো আপলোড <input type="file" name="logo_file" accept="image/jpeg,image/png,image/gif,image/webp"></label>
            <?php for ($i = 0; $i < 3; $i++): ?>
              <label>ব্যানার ছবি <?= $i + 1 ?> path <input type="text" name="bannerImages[<?= $i ?>]" value="<?= school_h((string) (($settings['bannerImages'][$i] ?? ''))) ?>"></label>
              <label>ব্যানার ছবি <?= $i + 1 ?> আপলোড <input type="file" name="banner_files[]" accept="image/jpeg,image/png,image/gif,image/webp"></label>
            <?php endfor; ?>
          </div>
          <div class="editor-actions"><button type="submit">মূল সেটিংস সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Site settings edit section -->

      <!-- START: Professional footer edit section -->
      <section id="footer" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>প্রফেশনাল ফুটার</h2>
            <p>পরিচিতি, লিংক, সামাজিক যোগাযোগ, Google Maps, আইনি লিংক ও ক্রেডিট সব পেজে একসঙ্গে আপডেট করুন।</p>
          </div>
        </div>

        <form method="post" class="editor-panel">
          <input type="hidden" name="action" value="save_footer">

          <!-- START: Footer identity and policy fields section -->
          <div class="form-grid">
            <label>হোমপেজ URL <input type="text" name="footer[homeUrl]" value="<?= school_h((string) ($footer['homeUrl'] ?? 'index.html')) ?>"></label>
            <label>সর্বশেষ হালনাগাদ <input type="text" name="footer[lastUpdated]" value="<?= school_h((string) ($footer['lastUpdated'] ?? '')) ?>" placeholder="খালি রাখলে স্বয়ংক্রিয় তারিখ"></label>
            <label class="full-row">ফুটার পরিচিতি <textarea name="footer[description]" rows="3"><?= school_h((string) ($footer['description'] ?? '')) ?></textarea></label>
            <label class="full-row">Google Maps URL <input type="url" name="footer[mapUrl]" value="<?= school_h((string) ($footer['mapUrl'] ?? '')) ?>"></label>
            <label>গোপনীয়তা নীতির URL <input type="text" name="footer[privacyUrl]" value="<?= school_h((string) ($footer['privacyUrl'] ?? 'pages/privacy-policy.html')) ?>"></label>
            <label>ব্যবহারের শর্তাবলির URL <input type="text" name="footer[termsUrl]" value="<?= school_h((string) ($footer['termsUrl'] ?? 'pages/terms-and-conditions.html')) ?>"></label>
            <label>ক্রেডিট লেবেল <input type="text" name="footer[developerLabel]" value="<?= school_h((string) ($footer['developerLabel'] ?? 'কারিগরি ব্যবস্থাপনা:')) ?>"></label>
            <label>ক্রেডিট নাম <input type="text" name="footer[developerName]" value="<?= school_h((string) ($footer['developerName'] ?? 'ওয়েবসাইট প্রশাসন')) ?>"></label>
          </div>
          <!-- END: Footer identity and policy fields section -->

          <!-- START: Footer navigation links edit section -->
          <div class="form-grid admin-footer-links-grid">
            <label class="full-row">
              গুরুত্বপূর্ণ লিংক
              <textarea name="footerQuickLinksText" rows="7"><?= school_h(admin_format_link_lines($footer['quickLinks'] ?? [])) ?></textarea>
            </label>
            <label class="full-row">
              দাপ্তরিক লিংক
              <textarea name="footerOfficialLinksText" rows="6"><?= school_h(admin_format_link_lines($footer['officialLinks'] ?? [])) ?></textarea>
            </label>
          </div>
          <p class="editor-note">লিংক লেখার নিয়ম: <code>শিরোনাম | URL</code> — প্রতি লাইনে একটি। খালি URL-এর আইটেম ফুটারে দেখানো হবে না।</p>
          <!-- END: Footer navigation links edit section -->

          <!-- START: Footer social media links edit section -->
          <h3>সামাজিক যোগাযোগ</h3>
          <?php
          $footerSocialLinks = array_values($footer['socialLinks'] ?? []);
          while (count($footerSocialLinks) < 3) {
              $footerSocialLinks[] = ['title' => '', 'url' => '', 'icon' => 'website'];
          }
          ?>
          <?php foreach ($footerSocialLinks as $index => $socialLink): ?>
            <div class="admin-repeat-box">
              <div class="form-grid">
                <label>নাম <input type="text" name="footerSocialLinks[<?= $index ?>][title]" value="<?= school_h((string) ($socialLink['title'] ?? '')) ?>"></label>
                <label>URL <input type="url" name="footerSocialLinks[<?= $index ?>][url]" value="<?= school_h((string) ($socialLink['url'] ?? '')) ?>" placeholder="অফিসিয়াল লিংক দিন"></label>
                <label>
                  আইকন
                  <select name="footerSocialLinks[<?= $index ?>][icon]">
                    <?php foreach (['facebook' => 'Facebook', 'youtube' => 'YouTube', 'website' => 'Website'] as $iconValue => $iconLabel): ?>
                      <option value="<?= school_h($iconValue) ?>" <?= (($socialLink['icon'] ?? '') === $iconValue) ? 'selected' : '' ?>><?= school_h($iconLabel) ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
              </div>
            </div>
          <?php endforeach; ?>
          <!-- END: Footer social media links edit section -->

          <div class="editor-actions"><button type="submit">ফুটার সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Professional footer edit section -->

      <!-- START: Homepage edit section -->
      <section id="home" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>হোমপেজ কনটেন্ট</h2>
            <p>স্লাইডার, শিক্ষার ধরন, সাইডবার প্রোফাইল ও গুরুত্বপূর্ণ লিংক এডিট করুন।</p>
          </div>
        </div>
        <form method="post" enctype="multipart/form-data" class="editor-panel">
          <input type="hidden" name="action" value="save_home">

          <h3>স্লাইডার</h3>
          <?php foreach (($home['slides'] ?? []) as $index => $slide): ?>
            <div class="admin-repeat-box">
              <h4>স্লাইড <?= $index + 1 ?></h4>
              <div class="form-grid">
                <label>টাইটেল <input type="text" name="slides[<?= $index ?>][title]" value="<?= school_h((string) ($slide['title'] ?? '')) ?>"></label>
                <label>ছবির Alt <input type="text" name="slides[<?= $index ?>][alt]" value="<?= school_h((string) ($slide['alt'] ?? '')) ?>"></label>
                <label class="full-row">বর্ণনা <textarea name="slides[<?= $index ?>][text]" rows="2"><?= school_h((string) ($slide['text'] ?? '')) ?></textarea></label>
                <label>ছবি path <input type="text" name="slides[<?= $index ?>][image]" value="<?= school_h((string) ($slide['image'] ?? '')) ?>"></label>
                <label>নতুন ছবি <input type="file" name="slide_image_files[]" accept="image/jpeg,image/png,image/gif,image/webp"></label>
              </div>
            </div>
          <?php endforeach; ?>

          <h3>শিক্ষার ধরন কার্ড</h3>
          <?php foreach (($home['educationCards'] ?? []) as $index => $card): ?>
            <div class="admin-repeat-box">
              <h4>কার্ড <?= $index + 1 ?></h4>
              <div class="form-grid">
                <label>আইকন <input type="text" name="educationCards[<?= $index ?>][icon]" value="<?= school_h((string) ($card['icon'] ?? '')) ?>"></label>
                <label>টাইটেল <input type="text" name="educationCards[<?= $index ?>][title]" value="<?= school_h((string) ($card['title'] ?? '')) ?>"></label>
                <label class="full-row">বর্ণনা <textarea name="educationCards[<?= $index ?>][text]" rows="2"><?= school_h((string) ($card['text'] ?? '')) ?></textarea></label>
                <label class="full-row">লিংক <input type="text" name="educationCards[<?= $index ?>][url]" value="<?= school_h((string) ($card['url'] ?? '#')) ?>"></label>
              </div>
            </div>
          <?php endforeach; ?>

          <h3>সাইডবার প্রোফাইল</h3>
          <?php foreach (($home['profiles'] ?? []) as $index => $profile): ?>
            <div class="admin-repeat-box">
              <h4>প্রোফাইল <?= $index + 1 ?></h4>
              <div class="form-grid">
                <label>ভূমিকা <input type="text" name="profiles[<?= $index ?>][role]" value="<?= school_h((string) ($profile['role'] ?? '')) ?>"></label>
                <label>নাম <input type="text" name="profiles[<?= $index ?>][name]" value="<?= school_h((string) ($profile['name'] ?? '')) ?>"></label>
                <label class="full-row">বর্ণনা <textarea name="profiles[<?= $index ?>][text]" rows="2"><?= school_h((string) ($profile['text'] ?? '')) ?></textarea></label>
                <label>ছবি path <input type="text" name="profiles[<?= $index ?>][image]" value="<?= school_h((string) ($profile['image'] ?? '')) ?>"></label>
                <label>নতুন ছবি <input type="file" name="profile_image_files[]" accept="image/jpeg,image/png,image/gif,image/webp"></label>
                <label class="full-row">লিংক <input type="text" name="profiles[<?= $index ?>][url]" value="<?= school_h((string) ($profile['url'] ?? '#')) ?>"></label>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- START: Focal point and hotline edit section -->
          <?php
          $focalPoint = is_array($home['focalPoint'] ?? null) ? $home['focalPoint'] : [];
          $hotline = is_array($home['hotline'] ?? null) ? $home['hotline'] : [];
          ?>
          <h3>ফোকাল পয়েন্ট</h3>
          <div class="admin-repeat-box">
            <div class="form-grid">
              <label>সেকশন শিরোনাম <input type="text" name="focalPoint[title]" value="<?= school_h((string) ($focalPoint['title'] ?? 'ফোকাল পয়েন্ট')) ?>"></label>
              <label>নাম <input type="text" name="focalPoint[name]" value="<?= school_h((string) ($focalPoint['name'] ?? '')) ?>"></label>
              <label>পদবী <input type="text" name="focalPoint[designation]" value="<?= school_h((string) ($focalPoint['designation'] ?? '')) ?>"></label>
              <label>মোবাইল নম্বর <input type="text" name="focalPoint[phone]" value="<?= school_h((string) ($focalPoint['phone'] ?? '')) ?>" inputmode="tel"></label>
              <label>ছবি path <input type="text" name="focalPoint[image]" value="<?= school_h((string) ($focalPoint['image'] ?? 'assets/img/focal-point.svg')) ?>"></label>
              <label>নতুন ছবি <input type="file" name="focal_point_image_file" accept="image/jpeg,image/png,image/gif,image/webp"></label>
            </div>
          </div>

          <h3>হটলাইন</h3>
          <div class="admin-repeat-box">
            <div class="form-grid">
              <label>সেকশন শিরোনাম <input type="text" name="hotline[title]" value="<?= school_h((string) ($hotline['title'] ?? 'হটলাইন')) ?>"></label>
              <label>সহায়ক লেখা <input type="text" name="hotline[label]" value="<?= school_h((string) ($hotline['label'] ?? 'জরুরি যোগাযোগ নম্বর')) ?>"></label>
              <label class="full-row">মোবাইল নম্বর <input type="text" name="hotline[phone]" value="<?= school_h((string) ($hotline['phone'] ?? '')) ?>" inputmode="tel"></label>
            </div>
          </div>
          <!-- END: Focal point and hotline edit section -->

          <div class="form-grid">
            <label class="full-row">
              গুরুত্বপূর্ণ লিংক
              <textarea name="importantLinksText" rows="5"><?= school_h(admin_format_link_lines($home['importantLinks'] ?? [])) ?></textarea>
            </label>
            <label class="full-row">
              দাপ্তরিক লিংকসমূহ
              <textarea name="officialLinksText" rows="7"><?= school_h(admin_format_link_lines($home['officialLinks'] ?? [])) ?></textarea>
            </label>
          </div>
          <p class="editor-note">লিংক লেখার নিয়ম: <code>শিরোনাম | URL</code> — প্রতি লাইনে একটি।</p>
          <div class="editor-actions"><button type="submit">হোমপেজ সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Homepage edit section -->

      <!-- START: Informational pages edit section -->
      <section id="pages" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>সংক্ষিপ্ত বিবরণ ও যোগাযোগ পেজ</h2>
            <p>এই কনটেন্ট visitor-facing HTML পেজে স্বয়ংক্রিয়ভাবে বসবে।</p>
          </div>
        </div>
        <form method="post" class="editor-panel">
          <input type="hidden" name="action" value="save_pages">
          <div class="form-grid">
            <label>সংক্ষিপ্ত বিবরণ টাইটেল <input type="text" name="pages[briefHistory][title]" value="<?= school_h((string) ($pages['briefHistory']['title'] ?? '')) ?>"></label>
            <label>সংক্ষিপ্ত বিবরণ সাবটাইটেল <input type="text" name="pages[briefHistory][subtitle]" value="<?= school_h((string) ($pages['briefHistory']['subtitle'] ?? '')) ?>"></label>
            <label class="full-row">সংক্ষিপ্ত বিবরণ বিস্তারিত <textarea name="pages[briefHistory][text]" rows="8"><?= school_h((string) ($pages['briefHistory']['text'] ?? '')) ?></textarea></label>
            <label>যোগাযোগ টাইটেল <input type="text" name="pages[contact][title]" value="<?= school_h((string) ($pages['contact']['title'] ?? 'যোগাযোগ')) ?>"></label>
            <label>যোগাযোগ সাবটাইটেল <input type="text" name="pages[contact][subtitle]" value="<?= school_h((string) ($pages['contact']['subtitle'] ?? '')) ?>"></label>
            <label>দ্রুত বার্তা টাইটেল <input type="text" name="pages[contact][quickTitle]" value="<?= school_h((string) ($pages['contact']['quickTitle'] ?? '')) ?>"></label>
            <label>বাটন লেখা <input type="text" name="pages[contact][buttonText]" value="<?= school_h((string) ($pages['contact']['buttonText'] ?? '')) ?>"></label>
            <label class="full-row">দ্রুত বার্তা টেক্সট <textarea name="pages[contact][quickText]" rows="3"><?= school_h((string) ($pages['contact']['quickText'] ?? '')) ?></textarea></label>
          </div>
          <div class="editor-actions"><button type="submit">ইনফো পেজ সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Informational pages edit section -->

      <!-- START: Notice edit section -->
      <section id="notices" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>নোটিশ বোর্ড</h2>
            <p>হোমপেজে প্রথম ৪টি এবং নোটিশ পেজে সব নোটিশ দেখা যাবে।</p>
          </div>
        </div>
        <form method="post" class="editor-panel">
          <input type="hidden" name="action" value="save_notices">
          <label>
            নোটিশ তালিকা
            <textarea name="noticesText" rows="10"><?= school_h(admin_format_notice_lines($content['notices'] ?? [])) ?></textarea>
          </label>
          <p class="editor-note">নিয়ম: <code>নোটিশের শিরোনাম | ক্যাটাগরি | URL</code> — URL না থাকলে <code>#</code> দিন।</p>
          <div class="editor-actions"><button type="submit">নোটিশ সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Notice edit section -->

      <!-- START: Services edit section -->
      <section id="services" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>সেবা সমূহ</h2>
            <p>হোমপেজ ও সেবা পেজে একই ডাটা দেখানো হবে।</p>
          </div>
        </div>
        <form method="post" enctype="multipart/form-data" class="editor-panel">
          <input type="hidden" name="action" value="save_services">
          <?php foreach (($content['services'] ?? []) as $index => $service): ?>
            <div class="admin-repeat-box">
              <h4>সেবা <?= $index + 1 ?></h4>
              <div class="form-grid">
                <label>শিরোনাম <input type="text" name="services[<?= $index ?>][title]" value="<?= school_h((string) ($service['title'] ?? '')) ?>"></label>
                <label>ছবি path <input type="text" name="services[<?= $index ?>][image]" value="<?= school_h((string) ($service['image'] ?? '')) ?>"></label>
                <label>নতুন ছবি <input type="file" name="service_image_files[]" accept="image/jpeg,image/png,image/gif,image/webp"></label>
                <label class="full-row">
                  সেবার লিংকসমূহ
                  <textarea name="services[<?= $index ?>][itemsText]" rows="4"><?= school_h(admin_format_link_lines($service['items'] ?? [])) ?></textarea>
                </label>
              </div>
            </div>
          <?php endforeach; ?>
          <p class="editor-note">সার্ভিস আইটেমের নিয়ম: <code>শিরোনাম | URL</code> — নতুন সার্ভিস যোগ/মুছতে নিচের Advanced JSON অংশ ব্যবহার করুন।</p>
          <div class="editor-actions"><button type="submit">সেবা সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Services edit section -->

      <!-- START: Gallery edit section -->
      <section id="gallery" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>গ্যালারি</h2>
            <p>প্রতিটি বক্সে Cover image এবং একাধিক অ্যালবাম ছবি আপলোড করা যাবে। বক্সে ক্লিক করলে নতুন ট্যাবে ছবিগুলো পর্যায়ক্রমে দেখাবে।</p>
          </div>
        </div>
        <form method="post" enctype="multipart/form-data" class="editor-panel">
          <input type="hidden" name="action" value="save_gallery">
          <?php foreach (($content['gallery'] ?? []) as $index => $item): ?>
            <div class="admin-repeat-box gallery-admin-row">
              <div class="gallery-admin-preview">
                <img src="<?= school_h(admin_image_preview_src((string) ($item['image'] ?? 'assets/img/logo.jpg?v=20260727-1'))) ?>" alt="<?= school_h((string) ($item['title'] ?? 'গ্যালারি')) ?>">
              </div>
              <div class="form-grid">
                <label>বক্সের শিরোনাম <input type="text" name="galleryTitle[<?= $index ?>]" value="<?= school_h((string) ($item['title'] ?? '')) ?>"></label>
                <label>Cover image path <input type="text" name="galleryImage[<?= $index ?>]" value="<?= school_h((string) ($item['image'] ?? '')) ?>"></label>
                <label>নতুন Cover image <input type="file" name="gallery_image_files[]" accept="image/jpeg,image/png,image/gif,image/webp"></label>
                <label>অ্যালবামের একাধিক ছবি আপলোড <input type="file" name="gallery_album_files[<?= $index ?>][]" accept="image/jpeg,image/png,image/gif,image/webp" multiple></label>
                <label class="full-row">
                  অ্যালবাম image path/URL
                  <textarea name="galleryImagesText[<?= $index ?>]" rows="4" placeholder="প্রতিটি লাইনে একটি image path বা URL"><?= school_h(admin_format_image_lines($item['images'] ?? [], (string) ($item['image'] ?? ''))) ?></textarea>
                </label>
                <label class="admin-check-label"><input type="checkbox" name="galleryDelete[<?= $index ?>]" value="1"> এই বক্স মুছুন</label>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="admin-repeat-box">
            <h4>নতুন গ্যালারি বক্স যোগ করুন</h4>
            <div class="form-grid">
              <label>বক্সের শিরোনাম <input type="text" name="newGalleryTitle"></label>
              <label>Cover image path <input type="text" name="newGalleryImage" placeholder="assets/img/new-image.jpg"></label>
              <label>Cover image আপলোড <input type="file" name="new_gallery_file" accept="image/jpeg,image/png,image/gif,image/webp"></label>
              <label>অ্যালবামের একাধিক ছবি আপলোড <input type="file" name="new_gallery_album_files[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple></label>
              <label class="full-row">
                অ্যালবাম image path/URL
                <textarea name="newGalleryImagesText" rows="4" placeholder="প্রতিটি লাইনে একটি image path বা URL"></textarea>
              </label>
            </div>
          </div>

          <p class="editor-note">টিপস: Cover image হলো বক্সে দেখানো ছবি। Album image তালিকায় যত ছবি থাকবে, নতুন ট্যাবের slideshow-তে তত ছবি পর্যায়ক্রমে দেখাবে।</p>
          <div class="editor-actions"><button type="submit">গ্যালারি অ্যালবাম সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Gallery edit section -->

      <!-- START: Academic programs edit section -->
      <section id="programs" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>একাডেমিক পেজসমূহ</h2>
            <p>মাধ্যমিক, ভোকেশনাল, উচ্চ মাধ্যমিক ও বিএম পেজের টেবিল/নোটিশ/যোগাযোগ কর্মকর্তা এডিট করুন।</p>
          </div>
        </div>
        <form method="post" enctype="multipart/form-data" class="editor-panel">
          <input type="hidden" name="action" value="save_programs">
          <?php $programFileIndex = 0; ?>
          <?php foreach (($content['programs'] ?? []) as $slug => $program): ?>
            <div class="admin-repeat-box">
              <h3><?= school_h($programLabels[$slug] ?? $slug) ?></h3>
              <div class="form-grid">
                <label>পেজ path <input type="text" name="programs[<?= school_h((string) $slug) ?>][page]" value="<?= school_h((string) ($program['page'] ?? '')) ?>"></label>
                <label>টাইটেল <input type="text" name="programs[<?= school_h((string) $slug) ?>][title]" value="<?= school_h((string) ($program['title'] ?? '')) ?>"></label>
                <label class="full-row">সাবটাইটেল <textarea name="programs[<?= school_h((string) $slug) ?>][subtitle]" rows="2"><?= school_h((string) ($program['subtitle'] ?? '')) ?></textarea></label>
                <label class="full-row">বিস্তারিত তথ্য টেবিল <textarea name="programs[<?= school_h((string) $slug) ?>][infoRowsText]" rows="5"><?= school_h(admin_format_info_rows($program['infoRows'] ?? [])) ?></textarea></label>
                <label class="full-row">প্রয়োজনীয় নোটিশ <textarea name="programs[<?= school_h((string) $slug) ?>][noticesText]" rows="4"><?= school_h(admin_format_notice_lines($program['notices'] ?? [])) ?></textarea></label>
                <label class="full-row">ডান পাশের রঙিন তথ্য লিংক <textarea name="programs[<?= school_h((string) $slug) ?>][resourceLinksText]" rows="5"><?= school_h(admin_format_resource_link_lines($program['resourceLinks'] ?? [])) ?></textarea></label>
                <label>কর্মকর্তার ভূমিকা <input type="text" name="programs[<?= school_h((string) $slug) ?>][contactOfficer][role]" value="<?= school_h((string) ($program['contactOfficer']['role'] ?? 'যোগাযোগ কর্মকর্তা')) ?>"></label>
                <label>কর্মকর্তার নাম <input type="text" name="programs[<?= school_h((string) $slug) ?>][contactOfficer][name]" value="<?= school_h((string) ($program['contactOfficer']['name'] ?? '')) ?>"></label>
                <label class="full-row">কর্মকর্তার বর্ণনা <input type="text" name="programs[<?= school_h((string) $slug) ?>][contactOfficer][text]" value="<?= school_h((string) ($program['contactOfficer']['text'] ?? '')) ?>"></label>
                <label>কর্মকর্তার ছবি path <input type="text" name="programs[<?= school_h((string) $slug) ?>][contactOfficer][image]" value="<?= school_h((string) ($program['contactOfficer']['image'] ?? '')) ?>"></label>
                <label>নতুন ছবি <input type="file" name="program_officer_files[]" accept="image/jpeg,image/png,image/gif,image/webp"></label>
                <label class="full-row">যোগাযোগ লিংক <input type="text" name="programs[<?= school_h((string) $slug) ?>][contactOfficer][url]" value="<?= school_h((string) ($program['contactOfficer']['url'] ?? 'contact.html')) ?>"></label>
              </div>
            </div>
            <?php $programFileIndex++; ?>
          <?php endforeach; ?>
          <p class="editor-note">টেবিল: <code>লেবেল | মান</code>; নোটিশ: <code>শিরোনাম | ক্যাটাগরি | URL</code>; রঙিন লিংক: <code>আইকন | শিরোনাম | URL</code></p>
          <div class="editor-actions"><button type="submit">একাডেমিক পেজ সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Academic programs edit section -->

      <!-- START: Academic resource pages edit section -->
      <section id="program-resources" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>একাডেমিক তথ্যের বিস্তারিত পেজসমূহ</h2>
            <p>শিক্ষক-কর্মচারী, পাবলিক ফলাফল, প্রাতিষ্ঠানিক ফলাফল এবং শ্রেণি শিক্ষক—মোট ১৬টি পেজের তথ্য এডিট করুন।</p>
          </div>
        </div>
        <form method="post" class="editor-panel">
          <input type="hidden" name="action" value="save_program_resources">
          <?php foreach (($content['programResources'] ?? []) as $slug => $resource): ?>
            <div class="admin-repeat-box">
              <h3><?= school_h((string) ($resource['title'] ?? $slug)) ?></h3>
              <div class="form-grid">
                <label>পেজ path <input type="text" name="programResources[<?= school_h((string) $slug) ?>][page]" value="<?= school_h((string) ($resource['page'] ?? '')) ?>"></label>
                <label>মূল শিক্ষাস্তর <input type="text" name="programResources[<?= school_h((string) $slug) ?>][program]" value="<?= school_h((string) ($resource['program'] ?? '')) ?>"></label>
                <label>তথ্যের ধরন <input type="text" name="programResources[<?= school_h((string) $slug) ?>][type]" value="<?= school_h((string) ($resource['type'] ?? '')) ?>"></label>
                <label>আইকন <input type="text" name="programResources[<?= school_h((string) $slug) ?>][icon]" value="<?= school_h((string) ($resource['icon'] ?? '📄')) ?>"></label>
                <label class="full-row">পেজ টাইটেল <input type="text" name="programResources[<?= school_h((string) $slug) ?>][title]" value="<?= school_h((string) ($resource['title'] ?? '')) ?>"></label>
                <label class="full-row">সাবটাইটেল <textarea name="programResources[<?= school_h((string) $slug) ?>][subtitle]" rows="2"><?= school_h((string) ($resource['subtitle'] ?? '')) ?></textarea></label>
                <label class="full-row">ভূমিকা/বর্ণনা <textarea name="programResources[<?= school_h((string) $slug) ?>][description]" rows="3"><?= school_h((string) ($resource['description'] ?? '')) ?></textarea></label>
                <label class="full-row">টেবিলের কলাম <input type="text" name="programResources[<?= school_h((string) $slug) ?>][columnsText]" value="<?= school_h(admin_format_table_columns($resource['columns'] ?? [])) ?>"></label>
                <label class="full-row">টেবিলের সারি <textarea name="programResources[<?= school_h((string) $slug) ?>][rowsText]" rows="5"><?= school_h(admin_format_table_rows($resource['rows'] ?? [])) ?></textarea></label>
                <label class="full-row">নোট/সতর্কতা <textarea name="programResources[<?= school_h((string) $slug) ?>][note]" rows="2"><?= school_h((string) ($resource['note'] ?? '')) ?></textarea></label>
              </div>
            </div>
          <?php endforeach; ?>
          <p class="editor-note">কলাম ও প্রতিটি সারির ঘর <code>|</code> চিহ্ন দিয়ে আলাদা করুন। প্রতিটি নতুন লাইনে একটি নতুন টেবিল সারি হবে।</p>
          <div class="editor-actions"><button type="submit">সব বিস্তারিত তথ্যপেজ সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Academic resource pages edit section -->

      <!-- START: Advanced JSON edit section -->
      <section id="advanced" class="content-page-card admin-editor-section">
        <div class="section-title">
          <div>
            <h2>Advanced JSON Database</h2>
            <p>অতিরিক্ত আইটেম যোগ/মুছতে সম্পূর্ণ JSON ডাটাবেজ এডিট করুন। ভুল JSON দিলে সংরক্ষণ হবে না।</p>
          </div>
        </div>
        <form method="post" class="editor-panel">
          <input type="hidden" name="action" value="save_json">
          <textarea name="siteJson" rows="22" spellcheck="false" class="code-textarea"><?= school_h(admin_json_pretty($content)) ?></textarea>
          <div class="editor-actions"><button type="submit">JSON সংরক্ষণ</button></div>
        </form>
      </section>
      <!-- END: Advanced JSON edit section -->
    <?php endif; ?>
  </main>
  <!-- END: Admin main content section -->
</body>
</html>
