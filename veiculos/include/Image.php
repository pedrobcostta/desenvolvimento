<?php
class Image {
    private $uploadDir;
    private $allowedExtensions;
    private $maxFileSize;

    public function __construct() {
        $this->uploadDir = UPLOAD_DIR;
        $this->allowedExtensions = ALLOWED_EXTENSIONS;
        $this->maxFileSize = MAX_FILE_SIZE;

        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function processUpload($file, $generateThumbnail = true) {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = uniqid('vehicle_') . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            // Process and save image
            list($width, $height) = getimagesize($file['tmp_name']);
            $sourceImage = $this->createImageResource($file['tmp_name'], $extension);

            if ($generateThumbnail) {
                // Calculate thumbnail dimensions maintaining aspect ratio
                $ratio = min(THUMBNAIL_WIDTH / $width, THUMBNAIL_HEIGHT / $height);
                $newWidth = round($width * $ratio);
                $newHeight = round($height * $ratio);
            } else {
                // Calculate dimensions for large image
                $ratio = min(LARGE_IMAGE_WIDTH / $width, LARGE_IMAGE_HEIGHT / $height);
                $newWidth = round($width * $ratio);
                $newHeight = round($height * $ratio);
            }

            // Create new image with calculated dimensions
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG images
            if ($extension === 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }

            // Resize image
            imagecopyresampled(
                $newImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $width, $height
            );

            // Save image with appropriate quality/compression
            $this->saveImage($newImage, $filepath, $extension);

            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            // Return relative path for database storage
            return str_replace($_SERVER['DOCUMENT_ROOT'], '', $filepath);

        } catch (Exception $e) {
            throw new Exception("Image processing failed: " . $e->getMessage());
        }
    }

    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload failed with error code: " . $file['error']);
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("File size exceeds maximum limit of " . ($this->maxFileSize / 1024 / 1024) . "MB");
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception("File type not allowed. Allowed types: " . implode(', ', $this->allowedExtensions));
        }

        // Verify MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception("Invalid file type detected");
        }
    }

    private function createImageResource($filepath, $extension) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($filepath);
            case 'png':
                return imagecreatefrompng($filepath);
            case 'webp':
                return imagecreatefromwebp($filepath);
            default:
                throw new Exception("Unsupported image type");
        }
    }

    private function saveImage($image, $filepath, $extension) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, $filepath, 85); // 85% quality
                break;
            case 'png':
                imagepng($image, $filepath, 8); // Compression level 8 (0-9)
                break;
            case 'webp':
                imagewebp($image, $filepath, 85); // 85% quality
                break;
            default:
                throw new Exception("Unsupported image type for saving");
        }
    }

    public function deleteImage($filepath) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filepath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
