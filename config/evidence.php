<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Evidence File Types
    |--------------------------------------------------------------------------
    |
    | File extensions accepted for evidence uploads. Laravel's "mimes" rule
    | checks the file's actual content-sniffed MIME type against these
    | extensions — not just the filename — so this list is a real security
    | boundary, not a cosmetic one.
    |
    */

    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'mp4'],

    /*
    |--------------------------------------------------------------------------
    | Maximum Upload Size
    |--------------------------------------------------------------------------
    |
    | In kilobytes, matching Laravel's "max" validation rule. 50 MB allows
    | for short video evidence while still being a deliberate, visible limit.
    |
    */

    'max_upload_size_kb' => 51200,

    /*
    |--------------------------------------------------------------------------
    | Evidence Storage Disk
    |--------------------------------------------------------------------------
    |
    | Which filesystem disk (from config/filesystems.php) evidence files are
    | written to and read from. Local development keeps this as "local" (a
    | private disk on the server's own hard drive); a deployed instance can
    | switch it to "r2" to store files in Cloudflare R2 instead, without any
    | code change — the upload/download logic always asks for "whichever
    | disk this is", never a hardcoded disk name.
    |
    */

    'storage_disk' => env('EVIDENCE_STORAGE_DISK', 'local'),

];
