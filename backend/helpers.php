<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function school_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function school_ensure_storage(): void
{
    $dataDir = dirname(school_data_file());
    $uploadDir = school_upload_dir();
    $mediaUploadDir = school_media_upload_dir();

    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if (!is_dir($mediaUploadDir)) {
        mkdir($mediaUploadDir, 0775, true);
    }

    if (!file_exists(school_data_file())) {
        file_put_contents(school_data_file(), json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    if (!file_exists(school_media_data_file())) {
        $defaultMedia = [
            'title' => 'জাতীয় সংগীত',
            'subtitle' => 'আমার সোনার বাংলা',
            'type' => 'audio',
            'source' => 'https://upload.wikimedia.org/wikipedia/commons/transcoded/b/bc/Amar_Sonar_Bangla_-_official_vocal_music_of_the_National_anthem_of_Bangladesh.ogg/Amar_Sonar_Bangla_-_official_vocal_music_of_the_National_anthem_of_Bangladesh.ogg.mp3',
            'poster' => 'assets/img/national-anthem-poster.svg',
        ];
        file_put_contents(school_media_data_file(), json_encode($defaultMedia, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

function school_read_heads(): array
{
    school_ensure_storage();
    $json = file_get_contents(school_data_file());
    $data = json_decode($json ?: '[]', true);
    return is_array($data) ? array_values($data) : [];
}

function school_write_heads(array $heads): void
{
    school_ensure_storage();
    $clean = array_values(array_map(static function (array $head): array {
        return [
            'id' => (string) ($head['id'] ?? bin2hex(random_bytes(8))),
            'serial' => trim((string) ($head['serial'] ?? '')),
            'name' => trim((string) ($head['name'] ?? '')),
            'designation' => trim((string) ($head['designation'] ?? '')),
            'period' => trim((string) ($head['period'] ?? '')),
            'bio' => trim((string) ($head['bio'] ?? '')),
            'photo' => trim((string) ($head['photo'] ?? '')),
        ];
    }, $heads));

    $encoded = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        throw new RuntimeException('ডাটা JSON আকারে সংরক্ষণ করা যায়নি।');
    }

    $tempFile = school_data_file() . '.tmp';
    file_put_contents($tempFile, $encoded, LOCK_EX);
    rename($tempFile, school_data_file());
}

function school_find_head_index(array $heads, string $id): int
{
    foreach ($heads as $index => $head) {
        if (($head['id'] ?? '') === $id) {
            return (int) $index;
        }
    }

    return -1;
}

function school_remove_photo(?string $photo): void
{
    if (!$photo || strpos($photo, '../uploads/former-heads/') !== 0) {
        return;
    }

    $fileName = basename($photo);
    $filePath = school_upload_dir() . '/' . $fileName;

    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

function school_upload_photo(array $file, ?string $existingPhoto = ''): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return (string) $existingPhoto;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('ছবি আপলোডে সমস্যা হয়েছে।');
    }

    if (($file['size'] ?? 0) > SCHOOL_MAX_UPLOAD_BYTES) {
        throw new RuntimeException('ছবির সাইজ ২ এমবি-এর কম রাখুন।');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $mime = mime_content_type($tmpName);
    if (!isset(SCHOOL_ALLOWED_IMAGE_TYPES[$mime])) {
        throw new RuntimeException('শুধু JPG, PNG, GIF বা WEBP ছবি আপলোড করুন।');
    }

    school_ensure_storage();

    $extension = SCHOOL_ALLOWED_IMAGE_TYPES[$mime];
    $safeName = 'head-' . date('Ymd-His') . '-' . bin2hex(random_bytes(5)) . '.' . $extension;
    $target = school_upload_dir() . '/' . $safeName;

    if (!move_uploaded_file($tmpName, $target)) {
        throw new RuntimeException('ছবি সার্ভারে সংরক্ষণ করা যায়নি।');
    }

    if ($existingPhoto) {
        school_remove_photo($existingPhoto);
    }

    return school_public_upload_prefix() . $safeName;
}

function school_read_media_settings(): array
{
    school_ensure_storage();
    $json = file_get_contents(school_media_data_file());
    $data = json_decode($json ?: '{}', true);

    if (!is_array($data)) {
        $data = [];
    }

    return array_merge([
        'title' => 'জাতীয় সংগীত',
        'subtitle' => 'আমার সোনার বাংলা',
        'type' => 'audio',
        'source' => 'https://upload.wikimedia.org/wikipedia/commons/transcoded/b/bc/Amar_Sonar_Bangla_-_official_vocal_music_of_the_National_anthem_of_Bangladesh.ogg/Amar_Sonar_Bangla_-_official_vocal_music_of_the_National_anthem_of_Bangladesh.ogg.mp3',
        'poster' => 'assets/img/national-anthem-poster.svg',
    ], $data);
}

function school_write_media_settings(array $settings): void
{
    school_ensure_storage();

    $clean = [
        'title' => trim((string) ($settings['title'] ?? 'জাতীয় সংগীত')),
        'subtitle' => trim((string) ($settings['subtitle'] ?? 'আমার সোনার বাংলা')),
        'type' => trim((string) ($settings['type'] ?? 'audio')),
        'source' => trim((string) ($settings['source'] ?? '')),
        'poster' => trim((string) ($settings['poster'] ?? 'assets/img/national-anthem-poster.svg')),
    ];

    if ($clean['title'] === '') {
        $clean['title'] = 'জাতীয় সংগীত';
    }

    if ($clean['subtitle'] === '') {
        $clean['subtitle'] = 'আমার সোনার বাংলা';
    }

    if (!in_array($clean['type'], ['audio', 'video'], true)) {
        $clean['type'] = 'audio';
    }

    if ($clean['source'] === '') {
        throw new RuntimeException('জাতীয় সংগীতের মিডিয়া সোর্স খালি রাখা যাবে না।');
    }

    $encoded = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        throw new RuntimeException('মিডিয়া সেটিংস JSON আকারে সংরক্ষণ করা যায়নি।');
    }

    $tempFile = school_media_data_file() . '.tmp';
    file_put_contents($tempFile, $encoded, LOCK_EX);
    rename($tempFile, school_media_data_file());
}

function school_remove_media_file(?string $source): void
{
    if (!$source || strpos($source, school_public_media_prefix()) !== 0) {
        return;
    }

    $fileName = basename($source);
    $filePath = school_media_upload_dir() . '/' . $fileName;

    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

function school_upload_media(array $file, ?string $existingSource = ''): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [
            'source' => (string) $existingSource,
            'type' => '',
        ];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('মিডিয়া ফাইল আপলোডে সমস্যা হয়েছে।');
    }

    if (($file['size'] ?? 0) > SCHOOL_MAX_MEDIA_UPLOAD_BYTES) {
        throw new RuntimeException('মিডিয়া ফাইলের সাইজ ৫০ এমবি-এর কম রাখুন।');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $mime = mime_content_type($tmpName);
    if (!isset(SCHOOL_ALLOWED_MEDIA_TYPES[$mime])) {
        throw new RuntimeException('শুধু MP3, OGG, WAV, MP4, WEBM বা OGV ফাইল আপলোড করুন।');
    }

    school_ensure_storage();

    $extension = SCHOOL_ALLOWED_MEDIA_TYPES[$mime];
    $mediaType = strpos($mime, 'video/') === 0 ? 'video' : 'audio';
    $safeName = 'national-anthem-' . date('Ymd-His') . '-' . bin2hex(random_bytes(5)) . '.' . $extension;
    $target = school_media_upload_dir() . '/' . $safeName;

    if (!move_uploaded_file($tmpName, $target)) {
        throw new RuntimeException('মিডিয়া ফাইল সার্ভারে সংরক্ষণ করা যায়নি।');
    }

    if ($existingSource) {
        school_remove_media_file($existingSource);
    }

    return [
        'source' => school_public_media_prefix() . $safeName,
        'type' => $mediaType,
    ];
}

function school_admin_media_preview_url(string $source): string
{
    if ($source === '' || preg_match('/^(https?:|\/)/', $source)) {
        return $source;
    }

    return '../' . ltrim($source, '/');
}



/* START: Academic resource default data section */
function school_default_program_resource_links(string $programSlug, string $levelName): array
{
    $resourceTypes = [
        'teachers-staff' => ['icon' => '👥', 'label' => 'শিক্ষক ও কর্মচারীর তালিকা'],
        'public-results' => ['icon' => '🏆', 'label' => 'বিগত বছরের পাবলিক পরীক্ষার ফলাফল'],
        'institutional-results' => ['icon' => '📊', 'label' => 'প্রাতিষ্ঠানিক পরীক্ষার ফলাফল'],
        'class-teachers' => ['icon' => '🧑‍🏫', 'label' => 'শ্রেণি শিক্ষকগণ'],
    ];

    $links = [];

    foreach ($resourceTypes as $resourceSlug => $resource) {
        $links[] = [
            'icon' => $resource['icon'],
            'title' => $levelName . ' স্তরের ' . $resource['label'],
            'url' => 'pages/' . $programSlug . '-' . $resourceSlug . '.html',
        ];
    }

    return $links;
}

function school_default_program_resources(): array
{
    $programs = [
        'secondary' => ['name' => 'মাধ্যমিক', 'page' => 'pages/secondary.html'],
        'secondary-vocational' => ['name' => 'মাধ্যমিক (ভোকেশনাল)', 'page' => 'pages/secondary-vocational.html'],
        'higher-secondary' => ['name' => 'উচ্চ মাধ্যমিক', 'page' => 'pages/higher-secondary.html'],
        'higher-secondary-bm' => ['name' => 'উচ্চ মাধ্যমিক (বিএম)', 'page' => 'pages/higher-secondary-bm.html'],
    ];

    $resourceTypes = [
        'teachers-staff' => [
            'icon' => '👥',
            'label' => 'শিক্ষক ও কর্মচারীর তালিকা',
            'subtitle' => '%s স্তরে কর্মরত শিক্ষক ও কর্মচারীদের হালনাগাদ তথ্য।',
            'description' => 'নাম, পদবি, বিষয় বা দায়িত্ব এবং যোগাযোগের তথ্য এই টেবিলে প্রকাশ করা যাবে। অ্যাডমিন প্যানেল থেকে প্রয়োজন অনুযায়ী সারি যোগ, পরিবর্তন বা অপসারণ করুন।',
            'columns' => ['ক্রমিক', 'নাম', 'পদবি', 'বিষয়/দায়িত্ব', 'যোগাযোগ'],
            'rows' => [['১', 'তথ্য যোগ করুন', 'পদবি লিখুন', 'বিষয়/দায়িত্ব লিখুন', 'যোগাযোগ লিখুন']],
            'note' => 'ব্যক্তিগত তথ্য প্রকাশের আগে প্রতিষ্ঠানের অনুমোদন ও গোপনীয়তা নীতি অনুসরণ করুন।',
        ],
        'public-results' => [
            'icon' => '🏆',
            'label' => 'বিগত বছরের পাবলিক পরীক্ষার ফলাফল',
            'subtitle' => '%s স্তরের বিগত বছরের পাবলিক পরীক্ষার ফলাফলের সারসংক্ষেপ।',
            'description' => 'বছরভিত্তিক পরীক্ষার্থী, উত্তীর্ণ শিক্ষার্থী, পাসের হার এবং জিপিএ-৫ প্রাপ্তির তথ্য এখানে সংরক্ষণ ও প্রকাশ করা যাবে।',
            'columns' => ['বছর', 'পরীক্ষার নাম', 'পরীক্ষার্থী', 'উত্তীর্ণ', 'পাসের হার', 'জিপিএ-৫'],
            'rows' => [['২০২৫', 'পরীক্ষার নাম লিখুন', '০', '০', '০%', '০']],
            'note' => 'ফলাফল প্রকাশের আগে সংশ্লিষ্ট শিক্ষা বোর্ড বা প্রতিষ্ঠানের অনুমোদিত তথ্যের সঙ্গে মিলিয়ে নিন।',
        ],
        'institutional-results' => [
            'icon' => '📊',
            'label' => 'প্রাতিষ্ঠানিক পরীক্ষার ফলাফল',
            'subtitle' => '%s স্তরের অভ্যন্তরীণ ও প্রাতিষ্ঠানিক পরীক্ষার ফলাফল।',
            'description' => 'পরীক্ষার নাম, শ্রেণি বা পর্ব, শিক্ষাবর্ষ, প্রকাশের তারিখ এবং ফলাফল সংক্রান্ত নির্দেশনা এখানে দেখানো যাবে।',
            'columns' => ['শিক্ষাবর্ষ', 'পরীক্ষা', 'শ্রেণি/পর্ব', 'প্রকাশের তারিখ', 'ফলাফল/নির্দেশনা'],
            'rows' => [['২০২৫', 'পরীক্ষার নাম লিখুন', 'শ্রেণি/পর্ব লিখুন', 'তারিখ লিখুন', 'তথ্য যোগ করুন']],
            'note' => 'শিক্ষার্থীর ব্যক্তিগত ফলাফল প্রকাশে প্রয়োজনীয় অনুমতি ও নিরাপত্তা নিশ্চিত করুন।',
        ],
        'class-teachers' => [
            'icon' => '🧑‍🏫',
            'label' => 'শ্রেণি শিক্ষকগণ',
            'subtitle' => '%s স্তরের শ্রেণি ও শাখাভিত্তিক শ্রেণি শিক্ষকদের তালিকা।',
            'description' => 'প্রতিটি শ্রেণি বা শাখার দায়িত্বপ্রাপ্ত শিক্ষক, বিষয় এবং যোগাযোগ বা প্রয়োজনীয় মন্তব্য এখানে প্রকাশ করা যাবে।',
            'columns' => ['শ্রেণি/শাখা', 'শ্রেণি শিক্ষক', 'বিষয়', 'যোগাযোগ/মন্তব্য'],
            'rows' => [['শ্রেণি/শাখা লিখুন', 'শিক্ষকের নাম লিখুন', 'বিষয় লিখুন', 'তথ্য যোগ করুন']],
            'note' => 'শিক্ষাবর্ষ পরিবর্তনের পর শ্রেণি শিক্ষক তালিকা হালনাগাদ করুন।',
        ],
    ];

    $pages = [];

    foreach ($programs as $programSlug => $program) {
        foreach ($resourceTypes as $resourceSlug => $resource) {
            $slug = $programSlug . '-' . $resourceSlug;
            $pages[$slug] = [
                'page' => 'pages/' . $slug . '.html',
                'program' => $programSlug,
                'type' => $resourceSlug,
                'icon' => $resource['icon'],
                'title' => $program['name'] . ' স্তরের ' . $resource['label'],
                'subtitle' => sprintf($resource['subtitle'], $program['name']),
                'description' => $resource['description'],
                'columns' => $resource['columns'],
                'rows' => $resource['rows'],
                'note' => $resource['note'],
            ];
        }
    }

    return $pages;
}
/* END: Academic resource default data section */

/* START: Whole website content database helper section */
function school_default_site_content(): array
{
    return [
        'siteSettings' => [
            'institutionName' => 'কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়',
            'slogan' => 'জ্ঞান,শৃঙ্খলা ও দক্ষতায় গড়ি আলোকিত ভবিষ্যৎ',
            'eiin' => '117396',
            'established' => '১৯৫৭',
            'email' => 'kazdiahighersecondaryschool57@gmail.com',
            'phone' => '০১XXXXXXXXX',
            'address' => 'আপনার প্রতিষ্ঠানের ঠিকানা লিখুন',
            'officeHours' => 'সকাল ১০টা থেকে বিকাল ৪টা',
            'logo' => 'assets/img/logo.jpg?v=20260727-1',
            'bannerImages' => ['assets/img/bg-1.jpg?v=20260725-4', 'assets/img/bg-2.jpg?v=20260725-4', 'assets/img/bg-3.jpg?v=20260725-4'],
        ],
        /* START: Professional footer default data section */
        'footer' => [
            'homeUrl' => 'index.html',
            'description' => 'ঐতিহ্য, শৃঙ্খলা, আধুনিক শিক্ষা ও দক্ষতা উন্নয়নের সমন্বয়ে আলোকিত নাগরিক গড়ে তোলাই আমাদের অঙ্গীকার।',
            'mapUrl' => 'https://www.google.com/maps/search/?api=1&query=কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়',
            'privacyUrl' => 'pages/privacy-policy.html',
            'termsUrl' => 'pages/terms-and-conditions.html',
            'lastUpdated' => '',
            'developerLabel' => 'কারিগরি ব্যবস্থাপনা:',
            'developerName' => 'ওয়েবসাইট প্রশাসন',
            'quickLinks' => [
                ['title' => 'হোমপেজ', 'url' => 'index.html'],
                ['title' => 'আমাদের সম্পর্কে', 'url' => 'pages/brief-history.html'],
                ['title' => 'নোটিশ', 'url' => 'pages/notice.html'],
                ['title' => 'সেবা সমূহ', 'url' => 'pages/services.html'],
                ['title' => 'গ্যালারি', 'url' => 'pages/gallery.html'],
                ['title' => 'যোগাযোগ', 'url' => 'pages/contact.html'],
            ],
            'socialLinks' => [
                ['title' => 'Facebook', 'url' => '', 'icon' => 'facebook'],
                ['title' => 'YouTube', 'url' => '', 'icon' => 'youtube'],
            ],
            'officialLinks' => [
                ['title' => 'শিক্ষা মন্ত্রণালয়', 'url' => 'https://moedu.gov.bd/'],
                ['title' => 'মাধ্যমিক ও উচ্চ শিক্ষা অধিদপ্তর', 'url' => 'https://dshe.gov.bd/'],
                ['title' => 'যশোর শিক্ষা বোর্ড', 'url' => 'https://www.jessoreboard.gov.bd/'],
            ],
        ],
        /* END: Professional footer default data section */
        'home' => [
            'slides' => [
                ['title' => 'ভর্তি কার্যক্রম ২০২৬', 'text' => 'মাধ্যমিক, ভোকেশনাল, উচ্চ মাধ্যমিক ও বিএম শাখায় ভর্তি তথ্য প্রকাশিত হয়েছে।', 'image' => 'assets/img/home-slide-admission.jpg?v=20260727-1', 'alt' => 'ভর্তি কার্যক্রমে অংশগ্রহণকারী শিক্ষার্থী ও বিদ্যালয় ক্যাম্পাস'],
                ['title' => 'দক্ষতা-ভিত্তিক শিক্ষা', 'text' => 'কারিগরি ও সাধারণ শিক্ষার সমন্বয়ে শিক্ষার্থীদের ভবিষ্যৎ প্রস্তুতি।', 'image' => 'assets/img/home-slide-skills.jpg?v=20260727-1', 'alt' => 'দক্ষতা-ভিত্তিক শিক্ষা ও কারিগরি প্রশিক্ষণ কার্যক্রম'],
                ['title' => 'পরীক্ষা ও ফলাফল', 'text' => 'পরীক্ষা, রুটিন, ফলাফল এবং গুরুত্বপূর্ণ একাডেমিক নির্দেশনা।', 'image' => 'assets/img/home-slide-exams-results.jpg?v=20260727-1', 'alt' => 'পরীক্ষায় অংশগ্রহণকারী শিক্ষার্থী ও ফলাফল কার্যক্রম'],
            ],
            'educationCards' => [
                ['icon' => '📘', 'title' => 'মাধ্যমিক শিক্ষা', 'text' => 'ষষ্ঠ থেকে দশম শ্রেণির সাধারণ শিক্ষা কার্যক্রম।', 'url' => 'pages/secondary.html'],
                ['icon' => '🛠️', 'title' => 'মাধ্যমিক (ভোকেশনাল)', 'text' => 'দক্ষতা ও পেশাভিত্তিক মাধ্যমিক শিক্ষা কার্যক্রম।', 'url' => 'pages/secondary-vocational.html'],
                ['icon' => '🎓', 'title' => 'উচ্চ মাধ্যমিক', 'text' => 'একাদশ-দ্বাদশ শ্রেণির বিজ্ঞান, মানবিক ও ব্যবসায় শিক্ষা।', 'url' => 'pages/higher-secondary.html'],
                ['icon' => '💼', 'title' => 'উচ্চ মাধ্যমিক (বিএম)', 'text' => 'ব্যবসায় ব্যবস্থাপনা ও প্রয়োগমুখী উচ্চ মাধ্যমিক শিক্ষা।', 'url' => 'pages/higher-secondary-bm.html'],
            ],
            'profiles' => [
                ['role' => 'প্রতিষ্ঠাতা', 'name' => 'প্রতিষ্ঠাতার নাম', 'text' => 'প্রতিষ্ঠাতা, কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়', 'image' => 'assets/img/founder.svg', 'url' => '#'],
                ['role' => 'অধ্যক্ষ', 'name' => 'অধ্যক্ষের নাম', 'text' => 'অধ্যক্ষ, কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়', 'image' => 'assets/img/principal.svg', 'url' => '#'],
                ['role' => 'শিক্ষক সংগঠন', 'name' => 'শিক্ষক পরিষদ', 'text' => 'শিক্ষক-কর্মচারী কল্যাণ ও একাডেমিক উন্নয়ন সংগঠন', 'image' => 'assets/img/teacher-organization.svg', 'url' => '#'],
            ],
            'importantLinks' => [
                ['title' => 'অনলাইন আবেদন', 'url' => '#'],
                ['title' => 'রুটিন ও ফলাফল', 'url' => '#'],
                ['title' => 'ডাউনলোড', 'url' => '#'],
                ['title' => 'সচরাচর জিজ্ঞাসা', 'url' => '#'],
            ],
            'officialLinks' => [
                ['title' => 'শিক্ষা মন্ত্রণালয়', 'url' => 'https://moedu.gov.bd/'],
                ['title' => 'বিশ্ববিদ্যালয় মঞ্জুরী কমিশন', 'url' => 'https://ugc.gov.bd/'],
                ['title' => 'জাতীয় বিশ্ববিদ্যালয়', 'url' => 'https://www.nu.ac.bd/'],
                ['title' => 'মাধ্যমিক ও উচ্চ শিক্ষা অধিদপ্তর', 'url' => 'https://dshe.gov.bd/'],
                ['title' => 'যশোর শিক্ষা বোর্ড', 'url' => 'https://www.jessoreboard.gov.bd/'],
                ['title' => 'কারিগরী শিক্ষা বোর্ড', 'url' => 'https://bteb.gov.bd/'],
                ['title' => 'এনসিটিবি', 'url' => 'https://nctb.gov.bd/'],
                ['title' => 'নায়েম', 'url' => 'https://naem.gov.bd/'],
                ['title' => 'ব্যানবেইস', 'url' => 'https://banbeis.gov.bd/'],
            ],
            /* START: Focal point and hotline default data section */
            'focalPoint' => [
                'title' => 'ফোকাল পয়েন্ট',
                'name' => 'ফোকাল পয়েন্ট কর্মকর্তার নাম',
                'designation' => 'পদবী লিখুন',
                'phone' => '০১XXXXXXXXX',
                'image' => 'assets/img/focal-point.svg',
            ],
            'hotline' => [
                'title' => 'হটলাইন',
                'label' => 'জরুরি যোগাযোগ নম্বর',
                'phone' => '০১XXXXXXXXX',
            ],
            /* END: Focal point and hotline default data section */
        ],
        'pages' => [
            'briefHistory' => [
                'title' => 'সংক্ষিপ্ত বিবরণ',
                'subtitle' => 'প্রতিষ্ঠানের ইতিহাস, উদ্দেশ্য ও একাডেমিক কার্যক্রমের সংক্ষিপ্ত পরিচিতি।',
                'text' => 'কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয় একটি ঐতিহ্যবাহী শিক্ষা প্রতিষ্ঠান। এখানে প্রতিষ্ঠানের ইতিহাস, উদ্দেশ্য, শিক্ষার পরিবেশ, অর্জন এবং অন্যান্য প্রয়োজনীয় তথ্য লিখুন।',
            ],
            'contact' => [
                'title' => 'যোগাযোগ',
                'subtitle' => 'প্রতিষ্ঠানের সাথে যোগাযোগের প্রয়োজনীয় তথ্য।',
                'quickTitle' => 'দ্রুত বার্তা',
                'quickText' => 'ইমেইলের মাধ্যমে যোগাযোগ করতে নিচের বাটনে ক্লিক করুন।',
                'buttonText' => 'ইমেইল পাঠান',
            ],
        ],
        'notices' => [
            ['title' => 'ষষ্ঠ থেকে দশম শ্রেণির ভর্তি সহায়তার আবেদন সংক্রান্ত বিজ্ঞপ্তি', 'category' => 'নতুন', 'url' => '#'],
            ['title' => 'একাদশ শ্রেণির ভর্তি কার্যক্রমের সময়সূচি প্রকাশ', 'category' => 'সাধারণ', 'url' => '#'],
            ['title' => 'ভোকেশনাল শাখার ব্যবহারিক পরীক্ষার রুটিন', 'category' => 'পরীক্ষা', 'url' => '#'],
            ['title' => 'বিএম শাখার নির্বাচনী পরীক্ষার ফরম পূরণ বিজ্ঞপ্তি', 'category' => 'গুরুত্বপূর্ণ', 'url' => '#'],
            ['title' => 'ক্লাস রুটিন ও একাডেমিক ক্যালেন্ডার সংক্রান্ত নির্দেশনা', 'category' => 'রুটিন', 'url' => '#'],
            ['title' => 'ফরম পূরণ, ফি জমা ও প্রয়োজনীয় কাগজপত্র সংক্রান্ত নোটিশ', 'category' => 'অফিস', 'url' => '#'],
        ],
        /* START: Complete service page link fallback data section */
        'services' => [
            [
                'title' => 'আমাদের বিষয়ে',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/afa4da9d59424fe88dcb9b680fde0903.png',
                'text' => 'বিভাগের পরিচিতি, কাঠামো, কর্মকর্তা ও কর্মবন্টনের তথ্য।',
                'items' => [
                    ['title' => 'ভিশন ও মিশন', 'url' => 'pages/service-01-01.html'],
                    ['title' => 'সাংগঠনিক কাঠামো', 'url' => 'pages/service-01-02.html'],
                    ['title' => 'কর্মকর্তাবৃন্দ', 'url' => 'pages/service-01-03.html'],
                    ['title' => 'কর্মবন্টন', 'url' => 'pages/service-01-04.html'],
                ],
            ],
            [
                'title' => 'বিজ্ঞপ্তি/আদেশ/পরিপত্র',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/d09e215fb8154cc9ab8470a62db9969f.png',
                'text' => 'প্রজ্ঞাপন, অফিস আদেশ, অনাপত্তিপত্র, প্রেস রিলিজ ও নিয়োগ তথ্য।',
                'items' => [
                    ['title' => 'প্রজ্ঞাপন/অফিস আদেশ/বিদেশ ভ্রমণের জিও', 'url' => 'pages/service-02-01.html'],
                    ['title' => 'পাসপোর্ট অনাপত্তিপত্র', 'url' => 'pages/service-02-02.html'],
                    ['title' => 'সংবাদ বিজ্ঞপ্তি/প্রেস রিলিজ', 'url' => 'pages/service-02-03.html'],
                    ['title' => 'দরপত্র/নিয়োগ বিজ্ঞপ্তি', 'url' => 'pages/service-02-04.html'],
                ],
            ],
            [
                'title' => 'নীতিমালা ও প্রকাশনা',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/c10e66210e0f4a55ad1ff5980ceae848.png',
                'text' => 'নীতিমালা, আইন-বিধি, প্রকাশনা ও বিভিন্ন প্রতিবেদন।',
                'items' => [
                    ['title' => 'নীতিমালা', 'url' => 'pages/service-03-01.html'],
                    ['title' => 'আইন ও বিধি', 'url' => 'pages/service-03-02.html'],
                    ['title' => 'প্রকাশনা ও বার্ষিক প্রতিবেদন', 'url' => 'pages/service-03-03.html'],
                    ['title' => 'বিভিন্ন প্রতিবেদন', 'url' => 'pages/service-03-04.html'],
                ],
            ],
            [
                'title' => 'নাগরিক ই-সেবাসমূহ',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/45cd14fc2f944928829c02ee4299bde3.gif',
                'text' => 'শিক্ষাবৃত্তি, অনলাইন আবেদন ও সার্টিফিকেট সত্যায়ন সেবা।',
                'items' => [
                    ['title' => 'শিক্ষাবৃত্তি বিজ্ঞপ্তি', 'url' => 'pages/service-04-01.html'],
                    ['title' => 'অনলাইন আবেদন', 'url' => 'pages/service-04-02.html'],
                    ['title' => 'সার্টিফিকেট সত্যায়ন', 'url' => 'pages/service-04-03.html'],
                ],
            ],
            [
                'title' => 'সেবা প্রদান প্রতিশ্রুতি',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/43b460d5c8aa4130b644970e83b9da80.png',
                'text' => 'সিটিজেন চার্টার, ফোকাল পয়েন্ট ও পরিবীক্ষণ প্রতিবেদন।',
                'items' => [
                    ['title' => 'সেবা প্রদান প্রতিশ্রুতি', 'url' => 'pages/service-05-01.html'],
                    ['title' => 'ফোকাল পয়েন্ট কর্মকর্তা/পরিবীক্ষণ কমিটি', 'url' => 'pages/service-05-02.html'],
                    ['title' => 'কর্মপরিকল্পনা, পরিবীক্ষণ ও মূল্যায়ন প্রতিবেদন', 'url' => 'pages/service-05-03.html'],
                    ['title' => 'আইন/বিধি/নীতিমালা/পরিপত্র/প্রজ্ঞাপন/নির্দেশিকা', 'url' => 'pages/service-05-04.html'],
                ],
            ],
            [
                'title' => 'সরকারি কর্মসম্পাদন পরিবীক্ষণ পদ্ধতি/GPMS',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/62a0ee1600a94535850ebb0375ce6b5d.png',
                'text' => 'GPMS নির্দেশিকা, ফলাফল, প্রতিবেদন ও সফটওয়্যার লিংক।',
                'items' => [
                    ['title' => 'নির্দেশিকা/পরিপত্র/জিপিএমএস টিম/ফোকাল পয়েন্ট', 'url' => 'pages/service-06-01.html'],
                    ['title' => 'স্ব স্ব অফিসের জিপিএমএস ও ফলাফল', 'url' => 'pages/service-06-02.html'],
                    ['title' => 'পরিবীক্ষণ ও মূল্যায়ন প্রতিবেদন', 'url' => 'pages/service-06-03.html'],
                    ['title' => 'জিপিএমএস সফটওয়্যার লিংক', 'url' => 'pages/service-06-04.html'],
                ],
            ],
            [
                'title' => 'জাতীয় শুদ্ধাচার কৌশল',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/35da0475cb8c4a32ad0fccc2d02a7b2a.png',
                'text' => 'শুদ্ধাচার কৌশল, কমিটি, কর্মপরিকল্পনা ও নির্দেশিকা।',
                'items' => [
                    ['title' => 'জাতীয় শুদ্ধাচার কৌশল', 'url' => 'pages/service-07-01.html'],
                    ['title' => 'কমিটিসমূহ', 'url' => 'pages/service-07-02.html'],
                    ['title' => 'পরিবীক্ষণ ও মূল্যায়ন প্রতিবেদন', 'url' => 'pages/service-07-03.html'],
                    ['title' => 'আইন/বিধি/নীতিমালা/নির্দেশিকা/পরিপত্র/প্রজ্ঞাপন', 'url' => 'pages/service-07-04.html'],
                ],
            ],
            [
                'title' => 'অভিযোগ প্রতিকার ব্যবস্থাপনা',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/0e67b93edb0f4f07a7a1b3888f529f90.png',
                'text' => 'অনিক, আপিল কর্মকর্তা, অভিযোগ আবেদন ও সংশ্লিষ্ট নীতিমালা।',
                'items' => [
                    ['title' => 'অনিক ও আপিল কর্মকর্তা', 'url' => 'pages/service-08-01.html'],
                    ['title' => 'কর্মপরিকল্পনা, পরিবীক্ষণ ও মূল্যায়ন প্রতিবেদন', 'url' => 'pages/service-08-02.html'],
                    ['title' => 'অভিযোগ দাখিল (অনলাইন আবেদন)', 'url' => 'pages/service-08-03.html'],
                    ['title' => 'আইন/বিধি/নীতিমালা/পরিপত্র/প্রজ্ঞাপন/নির্দেশিকা', 'url' => 'pages/service-08-04.html'],
                ],
            ],
            [
                'title' => 'তথ্য অধিকার',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/ece1c7bb214042f8bedab334e655ca9b.png',
                'text' => 'তথ্য অধিকার কর্মকর্তা, ফরম, স্বপ্রণোদিত তথ্য ও নির্দেশিকা।',
                'items' => [
                    ['title' => 'দায়িত্বপ্রাপ্ত কর্মকর্তা ও আপিল কর্তৃপক্ষ', 'url' => 'pages/service-09-01.html'],
                    ['title' => 'কর্মপরিকল্পনা, আবেদন, আপিল, অভিযোগ ফরম ও সফটওয়্যার লিংক', 'url' => 'pages/service-09-02.html'],
                    ['title' => 'স্বপ্রণোদিতভাবে প্রকাশযোগ্য তথ্যসমূহ', 'url' => 'pages/service-09-03.html'],
                    ['title' => 'আইন/বিধি/নীতিমালা/পরিপত্র/প্রজ্ঞাপন/নির্দেশিকা', 'url' => 'pages/service-09-04.html'],
                ],
            ],
            [
                'title' => 'উদ্ভাবনী কার্যক্রম',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/d4b7358117a545d88fb59934cca60597.png',
                'text' => 'উদ্ভাবন টিম, কর্মপরিকল্পনা, অফিস আদেশ ও সেবা সহজিকরণ।',
                'items' => [
                    ['title' => 'প্রজ্ঞাপন/পরিপত্র/নীতিমালা/সংকলন', 'url' => 'pages/service-10-01.html'],
                    ['title' => 'ইনোভেশন টীম', 'url' => 'pages/service-10-02.html'],
                    ['title' => 'বার্ষিক কর্মপরিকল্পনা/কার্যবিবরণী/অফিস আদেশ/প্রজ্ঞাপন', 'url' => 'pages/service-10-03.html'],
                    ['title' => 'সেবা সহজিকরণ/ইনোভেশন রেপ্লিকেশন', 'url' => 'pages/service-10-04.html'],
                ],
            ],
            [
                'title' => 'সেবা সহজিকরণ',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/1fddb3b236e14fb8b7bccd25408c7470.png',
                'text' => 'সেবা সহজিকরণ ম্যানুয়াল, আদেশ, তালিকা ও দৃষ্টান্ত।',
                'items' => [
                    ['title' => 'সেবা সহজিকরণ ম্যানুয়াল', 'url' => 'pages/service-11-01.html'],
                    ['title' => 'প্রজ্ঞাপন/পরিপত্র/নীতিমালা/অফিস আদেশ/সংকলন', 'url' => 'pages/service-11-02.html'],
                    ['title' => 'সহজিকৃত সেবার তালিকা', 'url' => 'pages/service-11-03.html'],
                    ['title' => 'সেবা সহজিকরণের দৃষ্টান্ত', 'url' => 'pages/service-11-04.html'],
                ],
            ],
            [
                'title' => 'বাজেট ও প্রকল্প',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/9c8704177a064dc78ceb3b80b39de197.png',
                'text' => 'ক্রয় পরিকল্পনা, বাজেট, অফিস আদেশ ও সমাপ্ত প্রকল্প।',
                'items' => [
                    ['title' => 'বার্ষিক ক্রয় পরিকল্পনা', 'url' => 'pages/service-12-01.html'],
                    ['title' => 'বাজেট ও এমটিবিএফ বাজেট', 'url' => 'pages/service-12-02.html'],
                    ['title' => 'বাজেট প্রতিবেদন/অফিস আদেশ', 'url' => 'pages/service-12-03.html'],
                    ['title' => 'সমাপ্ত প্রকল্প সমূহ', 'url' => 'pages/service-12-04.html'],
                ],
            ],
            [
                'title' => 'এসডিজি ও উন্নয়ন কর্মপরিকল্পনা',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/afcec87773644ad9b68ec35bb6b48f00.png',
                'text' => 'এসডিজি তথ্য, ফোকাল পয়েন্ট ও পরিকল্পনা-প্রতিবেদন।',
                'items' => [
                    ['title' => 'মন্ত্রণালয়/বিভাগের এসডিজি', 'url' => 'pages/service-13-01.html'],
                    ['title' => 'এসডিজি ফোকাল/বিকল্প ফোকাল পয়েন্ট', 'url' => 'pages/service-13-02.html'],
                    ['title' => 'এসডিজি জাতীয় ডকুমেন্ট', 'url' => 'pages/service-13-03.html'],
                    ['title' => 'পঞ্চবার্ষিকী পরিকল্পনা ও প্রতিবেদন', 'url' => 'pages/service-13-04.html'],
                ],
            ],
            [
                'title' => 'ফরমসমূহ',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/ec92705a7fd843b8b01d0768d410e149.png',
                'text' => 'বিশ্ববিদ্যালয়, কলেজ, মাধ্যমিক ও অন্যান্য ফরমসমূহ।',
                'items' => [
                    ['title' => 'বিশ্ববিদ্যালয় সংক্রান্ত', 'url' => 'pages/service-14-01.html'],
                    ['title' => 'কলেজ সংক্রান্ত', 'url' => 'pages/service-14-02.html'],
                    ['title' => 'মাধ্যমিক সংক্রান্ত', 'url' => 'pages/service-14-03.html'],
                    ['title' => 'অন্যান্য ফরমস', 'url' => 'pages/service-14-04.html'],
                ],
            ],
            [
                'title' => 'প্রেস/টেন্ডার/চাকুরি/ফরম',
                'image' => 'https://objectstorage.ap-dcc-gazipur-1.oraclecloud15.com/n/axvjbnqprylg/b/V2Ministry/o/office-shed/2024/12/bed4732af4f14cc092a96c38ac79705c.png',
                'text' => 'প্রেস রিলিজ, টেন্ডার, চাকুরি বিজ্ঞপ্তি ও ফরম।',
                'items' => [
                    ['title' => 'প্রেস রিলিজ', 'url' => 'pages/service-15-01.html'],
                    ['title' => 'টেন্ডার নোটিশ', 'url' => 'pages/service-15-02.html'],
                    ['title' => 'চাকুরি বিজ্ঞপ্তি', 'url' => 'pages/service-15-03.html'],
                    ['title' => 'ফরম', 'url' => 'pages/service-15-04.html'],
                ],
            ],
        ],
        /* END: Complete service page link fallback data section */
        'gallery' => [
            ['title' => 'অনুষ্ঠান', 'image' => 'assets/img/gallery-album-onusthan.svg', 'images' => ['assets/img/gallery-album-onusthan.svg']],
            ['title' => 'শিক্ষার্থী কার্যক্রম', 'image' => 'assets/img/gallery-album-student-activities.svg', 'images' => ['assets/img/gallery-album-student-activities.svg']],
            ['title' => 'ল্যাব কার্যক্রম', 'image' => 'assets/img/gallery-album-lab-activities.svg', 'images' => ['assets/img/gallery-album-lab-activities.svg']],
            ['title' => 'পাঠাগার কার্যক্রম', 'image' => 'assets/img/gallery-album-library-activities.svg', 'images' => ['assets/img/gallery-album-library-activities.svg']],
            ['title' => 'স্কাউট কার্যক্রম', 'image' => 'assets/img/gallery-album-scout-activities.svg', 'images' => ['assets/img/gallery-album-scout-activities.svg']],
            ['title' => 'ক্লাব কার্যক্রম', 'image' => 'assets/img/gallery-album-club-activities.svg', 'images' => ['assets/img/gallery-album-club-activities.svg']],
            ['title' => 'প্রতিষ্ঠানের ক্যাম্পাস', 'image' => 'assets/img/gallery-album-campus.svg', 'images' => ['assets/img/gallery-album-campus.svg']],
            ['title' => 'শ্রেণিকক্ষ', 'image' => 'assets/img/gallery-album-classroom.svg', 'images' => ['assets/img/gallery-album-classroom.svg']],
            ['title' => 'শিক্ষার্থী', 'image' => 'assets/img/gallery-album-students.svg', 'images' => ['assets/img/gallery-album-students.svg']],
        ],
        'programs' => [
            'secondary' => [
                'page' => 'pages/secondary.html',
                'title' => 'মাধ্যমিক শিক্ষা',
                'subtitle' => 'ষষ্ঠ থেকে দশম শ্রেণির সাধারণ শিক্ষা কার্যক্রম, সহশিক্ষা, পরীক্ষা ও ফলাফল সংক্রান্ত তথ্য।',
                'infoRows' => [['শ্রেণি/স্তর', 'ষষ্ঠ, সপ্তম, অষ্টম, নবম ও দশম শ্রেণি'], ['ভর্তি যোগ্যতা', 'প্রতিষ্ঠানের নীতিমালা অনুযায়ী ভর্তি পরীক্ষা/মেধা তালিকা'], ['বিষয়/ট্রেড', 'বাংলা, ইংরেজি, গণিত, বিজ্ঞান, বাংলাদেশ ও বিশ্বপরিচয়, আইসিটি'], ['যোগাযোগ', 'অফিস কক্ষ, সকাল ১০টা থেকে বিকাল ৪টা']],
                'notices' => [['title' => 'ভর্তি বিজ্ঞপ্তি ডাউনলোড করুন', 'category' => 'PDF', 'url' => '#'], ['title' => 'ক্লাস রুটিন দেখুন', 'category' => 'রুটিন', 'url' => '#'], ['title' => 'পরীক্ষার সময়সূচি', 'category' => 'পরীক্ষা', 'url' => '#']],
                'contactOfficer' => ['role' => 'যোগাযোগ কর্মকর্তা', 'name' => 'কর্মকর্তার নাম', 'text' => 'মাধ্যমিক শিক্ষা শাখা', 'image' => 'assets/img/principal.svg', 'url' => 'pages/contact.html'],
                'resourceLinks' => school_default_program_resource_links('secondary', 'মাধ্যমিক'),
            ],
            'secondary-vocational' => [
                'page' => 'pages/secondary-vocational.html',
                'title' => 'মাধ্যমিক (ভোকেশনাল)',
                'subtitle' => 'মাধ্যমিক পর্যায়ে দক্ষতা ও পেশাভিত্তিক শিক্ষা কার্যক্রমের বিস্তারিত তথ্য।',
                'infoRows' => [['শ্রেণি/স্তর', 'নবম ও দশম শ্রেণি / ভোকেশনাল শাখা'], ['ভর্তি যোগ্যতা', 'অষ্টম শ্রেণি উত্তীর্ণ অথবা সমমান'], ['বিষয়/ট্রেড', 'কম্পিউটার, ইলেকট্রিক্যাল, জেনারেল মেকানিক্স, ড্রেস মেকিং ইত্যাদি'], ['যোগাযোগ', 'অফিস কক্ষ, সকাল ১০টা থেকে বিকাল ৪টা']],
                'notices' => [['title' => 'ভর্তি বিজ্ঞপ্তি ডাউনলোড করুন', 'category' => 'PDF', 'url' => '#'], ['title' => 'ক্লাস রুটিন দেখুন', 'category' => 'রুটিন', 'url' => '#'], ['title' => 'পরীক্ষার সময়সূচি', 'category' => 'পরীক্ষা', 'url' => '#']],
                'contactOfficer' => ['role' => 'যোগাযোগ কর্মকর্তা', 'name' => 'কর্মকর্তার নাম', 'text' => 'মাধ্যমিক (ভোকেশনাল) শাখা', 'image' => 'assets/img/principal.svg', 'url' => 'pages/contact.html'],
                'resourceLinks' => school_default_program_resource_links('secondary-vocational', 'মাধ্যমিক (ভোকেশনাল)'),
            ],
            'higher-secondary' => [
                'page' => 'pages/higher-secondary.html',
                'title' => 'উচ্চ মাধ্যমিক',
                'subtitle' => 'একাদশ ও দ্বাদশ শ্রেণির সাধারণ শিক্ষা কার্যক্রম এবং বিভাগভিত্তিক তথ্য।',
                'infoRows' => [['শ্রেণি/স্তর', 'একাদশ ও দ্বাদশ শ্রেণি'], ['ভর্তি যোগ্যতা', 'এসএসসি/সমমান উত্তীর্ণ'], ['বিষয়/ট্রেড', 'বিজ্ঞান, মানবিক, ব্যবসায় শিক্ষা'], ['যোগাযোগ', 'অফিস কক্ষ, সকাল ১০টা থেকে বিকাল ৪টা']],
                'notices' => [['title' => 'ভর্তি বিজ্ঞপ্তি ডাউনলোড করুন', 'category' => 'PDF', 'url' => '#'], ['title' => 'ক্লাস রুটিন দেখুন', 'category' => 'রুটিন', 'url' => '#'], ['title' => 'পরীক্ষার সময়সূচি', 'category' => 'পরীক্ষা', 'url' => '#']],
                'contactOfficer' => ['role' => 'যোগাযোগ কর্মকর্তা', 'name' => 'কর্মকর্তার নাম', 'text' => 'উচ্চ মাধ্যমিক শাখা', 'image' => 'assets/img/principal.svg', 'url' => 'pages/contact.html'],
                'resourceLinks' => school_default_program_resource_links('higher-secondary', 'উচ্চ মাধ্যমিক'),
            ],
            'higher-secondary-bm' => [
                'page' => 'pages/higher-secondary-bm.html',
                'title' => 'উচ্চ মাধ্যমিক (বিএম)',
                'subtitle' => 'ব্যবসায় ব্যবস্থাপনা ও প্রয়োগমুখী উচ্চ মাধ্যমিক শিক্ষা কার্যক্রমের বিস্তারিত তথ্য।',
                'infoRows' => [['শ্রেণি/স্তর', 'একাদশ ও দ্বাদশ শ্রেণি / বিএম শাখা'], ['ভর্তি যোগ্যতা', 'এসএসসি/দাখিল/ভোকেশনাল উত্তীর্ণ অথবা সমমান'], ['বিষয়/ট্রেড', 'ব্যবসায় সংগঠন, হিসাববিজ্ঞান, কম্পিউটার অ্যাপ্লিকেশন, অফিস ব্যবস্থাপনা'], ['যোগাযোগ', 'অফিস কক্ষ, সকাল ১০টা থেকে বিকাল ৪টা']],
                'notices' => [['title' => 'ভর্তি বিজ্ঞপ্তি ডাউনলোড করুন', 'category' => 'PDF', 'url' => '#'], ['title' => 'ক্লাস রুটিন দেখুন', 'category' => 'রুটিন', 'url' => '#'], ['title' => 'পরীক্ষার সময়সূচি', 'category' => 'পরীক্ষা', 'url' => '#']],
                'contactOfficer' => ['role' => 'যোগাযোগ কর্মকর্তা', 'name' => 'কর্মকর্তার নাম', 'text' => 'উচ্চ মাধ্যমিক (বিএম) শাখা', 'image' => 'assets/img/principal.svg', 'url' => 'pages/contact.html'],
                'resourceLinks' => school_default_program_resource_links('higher-secondary-bm', 'উচ্চ মাধ্যমিক (বিএম)'),
            ],
        ],
        'programResources' => school_default_program_resources(),
    ];
}

function school_is_assoc_array(array $array): bool
{
    return array_keys($array) !== range(0, count($array) - 1);
}

function school_merge_site_content(array $default, array $stored): array
{
    foreach ($stored as $key => $value) {
        if (is_array($value) && isset($default[$key]) && is_array($default[$key]) && school_is_assoc_array($value) && school_is_assoc_array($default[$key])) {
            $default[$key] = school_merge_site_content($default[$key], $value);
        } else {
            $default[$key] = $value;
        }
    }

    return $default;
}

function school_ensure_site_content_storage(): void
{
    $storageDir = dirname(school_site_content_file());

    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0775, true);
    }

    if (!is_dir(school_generic_upload_dir())) {
        mkdir(school_generic_upload_dir(), 0775, true);
    }

    if (!file_exists(school_site_content_file())) {
        school_write_site_content(school_default_site_content());
    }
}

function school_read_site_content(): array
{
    school_ensure_site_content_storage();
    $json = file_get_contents(school_site_content_file());
    $stored = json_decode($json ?: '{}', true);

    if (!is_array($stored)) {
        $stored = [];
    }

    return school_merge_site_content(school_default_site_content(), $stored);
}

function school_write_site_content(array $content): void
{
    $storageDir = dirname(school_site_content_file());

    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0775, true);
    }

    $clean = school_merge_site_content(school_default_site_content(), $content);
    $encoded = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($encoded === false) {
        throw new RuntimeException('সাইট কনটেন্ট JSON ডাটাবেজে সংরক্ষণ করা যায়নি।');
    }

    /* START: Atomic JSON database write section */
    $tempFile = school_site_content_file() . '.tmp';
    $writtenBytes = file_put_contents($tempFile, $encoded, LOCK_EX);

    if ($writtenBytes === false) {
        throw new RuntimeException('অস্থায়ী JSON ডাটাবেজ ফাইল লেখা যায়নি। storage ফোল্ডারের permission পরীক্ষা করুন।');
    }

    if (!rename($tempFile, school_site_content_file())) {
        @unlink($tempFile);
        throw new RuntimeException('JSON ডাটাবেজ হালনাগাদ করা যায়নি। storage ফোল্ডারের write permission পরীক্ষা করুন।');
    }

    @chmod(school_site_content_file(), 0664);
    /* END: Atomic JSON database write section */
}

function school_upload_generic_image(array $file, ?string $existingImage = ''): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return (string) $existingImage;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('ছবি আপলোডে সমস্যা হয়েছে।');
    }

    if (($file['size'] ?? 0) > SCHOOL_MAX_GENERIC_IMAGE_BYTES) {
        throw new RuntimeException('ছবির সাইজ ৩ এমবি-এর কম রাখুন।');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $mime = mime_content_type($tmpName);

    if (!isset(SCHOOL_ALLOWED_IMAGE_TYPES[$mime])) {
        throw new RuntimeException('শুধু JPG, PNG, GIF বা WEBP ছবি আপলোড করুন।');
    }

    school_ensure_site_content_storage();

    $extension = SCHOOL_ALLOWED_IMAGE_TYPES[$mime];
    $safeName = 'cms-' . date('Ymd-His') . '-' . bin2hex(random_bytes(5)) . '.' . $extension;
    $target = school_generic_upload_dir() . '/' . $safeName;

    if (!move_uploaded_file($tmpName, $target)) {
        throw new RuntimeException('ছবি সার্ভারে সংরক্ষণ করা যায়নি।');
    }

    return school_public_generic_upload_prefix() . $safeName;
}

function school_api_json_response(array $payload, int $statusCode = 200): void
{
    /* START: JSON API response header section */
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    /* END: JSON API response header section */

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
/* END: Whole website content database helper section */
