<?php
declare(strict_types=1);

/*
  Backend settings for সাবেক প্রতিষ্ঠান প্রধানগণ.
  IMPORTANT: Upload the full project to PHP-enabled hosting and change the default password below.
*/
const SCHOOL_ADMIN_PASSWORD = 'ChangeThisPassword123!';
const SCHOOL_MAX_UPLOAD_BYTES = 2 * 1024 * 1024; // 2 MB
const SCHOOL_ALLOWED_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];

const SCHOOL_MAX_MEDIA_UPLOAD_BYTES = 50 * 1024 * 1024; // 50 MB
const SCHOOL_ALLOWED_MEDIA_TYPES = [
    'audio/mpeg' => 'mp3',
    'audio/mp3' => 'mp3',
    'audio/ogg' => 'ogg',
    'audio/wav' => 'wav',
    'audio/x-wav' => 'wav',
    'video/mp4' => 'mp4',
    'video/webm' => 'webm',
    'video/ogg' => 'ogv',
];

function school_data_file(): string
{
    return dirname(__DIR__) . '/storage/former-heads.json';
}

function school_upload_dir(): string
{
    return dirname(__DIR__) . '/uploads/former-heads';
}

function school_public_upload_prefix(): string
{
    return '../uploads/former-heads/';
}

function school_media_data_file(): string
{
    return dirname(__DIR__) . '/storage/media-settings.json';
}

function school_media_upload_dir(): string
{
    return dirname(__DIR__) . '/uploads/media';
}

function school_public_media_prefix(): string
{
    return 'uploads/media/';
}


/* START: Whole website CMS storage settings section */
const SCHOOL_SITE_CONTENT_FILE_NAME = 'site-content.json';
const SCHOOL_GENERIC_UPLOAD_DIR_NAME = 'media';
const SCHOOL_MAX_GENERIC_IMAGE_BYTES = 3 * 1024 * 1024; // 3 MB

function school_site_content_file(): string
{
    return dirname(__DIR__) . '/storage/' . SCHOOL_SITE_CONTENT_FILE_NAME;
}

function school_generic_upload_dir(): string
{
    return dirname(__DIR__) . '/uploads/' . SCHOOL_GENERIC_UPLOAD_DIR_NAME;
}

function school_public_generic_upload_prefix(): string
{
    return 'uploads/' . SCHOOL_GENERIC_UPLOAD_DIR_NAME . '/';
}
/* END: Whole website CMS storage settings section */
