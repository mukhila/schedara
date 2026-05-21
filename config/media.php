<?php

return [
    // ── FFmpeg ────────────────────────────────────────────────────────────
    'ffmpeg_path'  => env('FFMPEG_PATH',  '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
    'ffmpeg_threads' => env('FFMPEG_THREADS', 4),

    // ── Upload Limits ─────────────────────────────────────────────────────
    'max_upload_mb'   => env('MEDIA_MAX_UPLOAD_MB', 200),
    'max_files_batch' => env('MEDIA_MAX_FILES_BATCH', 10),

    // ── Image Processing ──────────────────────────────────────────────────
    'image_quality'   => env('MEDIA_IMAGE_QUALITY', 85),
    'thumbnail_width' => env('MEDIA_THUMBNAIL_WIDTH', 400),
    'webp_quality'    => env('MEDIA_WEBP_QUALITY', 80),

    // ── Video Compression ─────────────────────────────────────────────────
    'video_bitrate_kbps'       => env('MEDIA_VIDEO_BITRATE', 1500),
    'video_audio_bitrate_kbps' => env('MEDIA_AUDIO_BITRATE', 128),
    'video_timeout_seconds'    => env('MEDIA_VIDEO_TIMEOUT', 3600),

    // ── CDN ───────────────────────────────────────────────────────────────
    'cdn_warm_url'          => env('CDN_WARM_URL'),
    'cdn_invalidation_url'  => env('CDN_INVALIDATION_URL'),
    'cdn_base_url'          => env('CDN_BASE_URL'),

    // ── Watermark ─────────────────────────────────────────────────────────
    'watermark_text'     => env('MEDIA_WATERMARK_TEXT'),
    'watermark_image'    => env('MEDIA_WATERMARK_IMAGE'),
    'watermark_opacity'  => env('MEDIA_WATERMARK_OPACITY', 50),

    // ── Duplicate Detection ───────────────────────────────────────────────
    'detect_duplicates' => env('MEDIA_DETECT_DUPLICATES', true),

    // ── AI Tagging ────────────────────────────────────────────────────────
    'ai_tagging_enabled' => env('MEDIA_AI_TAGGING', false),

    // ── Allowed MIME Types ────────────────────────────────────────────────
    'allowed_mimes' => [
        'image'    => ['jpg','jpeg','png','gif','webp','svg','bmp','tiff'],
        'video'    => ['mp4','mov','avi','webm','mkv','flv'],
        'audio'    => ['mp3','wav','ogg','aac','flac'],
        'document' => ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv'],
    ],

    // ── Storage Paths ─────────────────────────────────────────────────────
    'paths' => [
        'images'      => 'uploads/images',
        'videos'      => 'uploads/videos',
        'audio'       => 'uploads/audios',
        'documents'   => 'uploads/documents',
        'thumbnails'  => 'uploads/thumbnails',
        'compressed'  => 'uploads/compressed',
        'versions'    => 'uploads/versions',
        'temp'        => 'uploads/temp',
    ],

    // ── Queue Names ───────────────────────────────────────────────────────
    'queues' => [
        'default'      => env('MEDIA_QUEUE', 'media'),
        'image'        => env('MEDIA_IMAGE_QUEUE', 'image-optimization'),
        'video'        => env('MEDIA_VIDEO_QUEUE', 'video-compression'),
        'ai'           => env('MEDIA_AI_QUEUE', 'media-ai'),
    ],
];
