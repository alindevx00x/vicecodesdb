<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Url;
use App\Core\View;
use App\Models\Media;

final class UploadController extends Controller
{
    public function uploadImage(): void
    {
        $user = $this->requireAdmin();

        // Allow TinyMCE header CSRF or form CSRF
        $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
        if ($csrfHeader !== '' && !Security::verifyCsrf($csrfHeader)) {
            $this->json(['error' => 'Invalid CSRF token.'], 403);
        }

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'No valid file uploaded.'], 400);
        }

        $file = $_FILES['file'];
        $config = require dirname(__DIR__, 3) . '/config/app.php';
        $maxBytes = (int) ($config['upload_max_bytes'] ?? 5 * 1024 * 1024);

        if ($file['size'] > $maxBytes) {
            $this->json(['error' => 'File exceeds 5MB maximum size limit.'], 422);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';
        $allowedMimes = $config['allowed_mimes'] ?? [];

        if (!isset($allowedMimes[$mime])) {
            $this->json(['error' => 'Unsupported file type. Allowed: JPG, PNG, WebP, GIF.'], 422);
        }

        $ext = $allowedMimes[$mime];
        $baseToken = date('Ymd_His') . '_' . bin2hex(random_bytes(8));
        $relDir = '/uploads/editor';
        $absDir = dirname(__DIR__, 3) . '/public' . $relDir;
        if (!is_dir($absDir)) {
            mkdir($absDir, 0755, true);
        }

        $safeName = $baseToken . '.' . $ext;
        $dest = $absDir . '/' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->json(['error' => 'Failed to move uploaded file.'], 500);
        }

        $finalMime = $mime;
        $finalSize = (int) $file['size'];

        // Optimize JPG/PNG uploads: resize if wider than 1600px and convert to WebP
        if (in_array($mime, ['image/jpeg', 'image/png'], true) && extension_loaded('gd') && function_exists('imagewebp')) {
            $src = $mime === 'image/jpeg' ? @imagecreatefromjpeg($dest) : @imagecreatefrompng($dest);
            if ($src) {
                $w = imagesx($src);
                $h = imagesy($src);
                $maxW = 1600;
                $targetImg = $src;

                if ($w > $maxW && $w > 0 && $h > 0) {
                    $nh = (int) round($h * ($maxW / $w));
                    $resized = imagecreatetruecolor($maxW, $nh);
                    if ($resized) {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        imagecopyresampled($resized, $src, 0, 0, 0, 0, $maxW, $nh, $w, $h);
                        imagedestroy($src);
                        $targetImg = $resized;
                    }
                } else {
                    imagepalettetotruecolor($targetImg);
                    imagealphablending($targetImg, true);
                    imagesavealpha($targetImg, true);
                }

                $webpName = $baseToken . '.webp';
                $webpDest = $absDir . '/' . $webpName;
                if (@imagewebp($targetImg, $webpDest, 84)) {
                    @unlink($dest);
                    $safeName = $webpName;
                    $dest = $webpDest;
                    $finalMime = 'image/webp';
                    $finalSize = (int) @filesize($webpDest);
                }
                imagedestroy($targetImg);
            }
        }

        $relPath = $relDir . '/' . $safeName;
        $publicUrl = Url::to($relPath);

        Media::create([
            'filename'      => $safeName,
            'original_name' => mb_substr(basename((string) $file['name']), 0, 255),
            'filepath'      => $relPath,
            'url_path'      => $publicUrl,
            'mime_type'     => $finalMime,
            'file_size'     => $finalSize,
            'alt_text'      => trim((string) ($_POST['alt_text'] ?? '')),
            'uploaded_by'   => (int) $user['id'],
        ]);

        // If submitted via Media Library form rather than TinyMCE XHR
        if (!empty($_POST['redirect_to_media'])) {
            View::setFlash('success', 'Image uploaded and optimized to ' . strtoupper(pathinfo($safeName, PATHINFO_EXTENSION)) . '.');
            $this->redirect('/admin/media');
        }

        // TinyMCE expects { "location": "..." }
        $this->json(['location' => $publicUrl]);
    }
}
