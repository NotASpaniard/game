<?php
function getProductImage($product_id, $fallback_url = 'assets/images/no-image.png') {
    $images_dir = __DIR__ . '/../images/products/';
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Tìm ảnh theo pattern: product_{id}.{extension}
    foreach ($allowed_extensions as $ext) {
        $image_path = $images_dir . "product_{$product_id}.{$ext}";
        if (file_exists($image_path)) {
            return "images/products/product_{$product_id}.{$ext}";
        }
    }
    
    // Tìm ảnh trong thư mục con theo ID
    $product_dir = $images_dir . $product_id . '/';
    if (is_dir($product_dir)) {
        $files = glob($product_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($files)) {
            return 'images/products/' . $product_id . '/' . basename($files[0]);
        }
    }
    
    // Fallback: lấy ảnh mặc định
    return $fallback_url;
}

function getProductImages($product_id) {
    $images_dir = __DIR__ . '/../images/products/';
    $images = [];
    
    // Tìm ảnh trong thư mục con theo ID
    $product_dir = $images_dir . $product_id . '/';
    if (is_dir($product_dir)) {
        $files = glob($product_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        foreach ($files as $file) {
            $images[] = 'images/products/' . $product_id . '/' . basename($file);
        }
    }
    
    // Nếu không có ảnh trong thư mục con, tìm ảnh theo pattern
    if (empty($images)) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        foreach ($allowed_extensions as $ext) {
            $image_path = $images_dir . "product_{$product_id}.{$ext}";
            if (file_exists($image_path)) {
                $images[] = "images/products/product_{$product_id}.{$ext}";
            }
        }
    }
    
    return $images;
}

function uploadProductImage($file, $product_id, $is_primary = false) {
    $upload_dir = __DIR__ . '/../images/products/' . $product_id . '/';
    
    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Kiểm tra file upload
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'File không hợp lệ'];
    }
    
    // Kiểm tra kích thước (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File quá lớn (tối đa 5MB)'];
    }
    
    // Kiểm tra loại file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Định dạng file không được hỗ trợ'];
    }
    
    // Tạo tên file unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $is_primary ? 'main.' . $extension : uniqid() . '.' . $extension;
    $file_path = $upload_dir . $filename;
    
    // Di chuyển file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return [
            'success' => true, 
            'filename' => $filename,
            'path' => 'images/products/' . $product_id . '/' . $filename
        ];
    } else {
        return ['success' => false, 'message' => 'Không thể upload file'];
    }
}

function deleteProductImage($product_id, $filename) {
    $file_path = __DIR__ . '/../images/products/' . $product_id . '/' . $filename;
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

function resizeImage($source, $destination, $max_width = 800, $max_height = 600, $quality = 85) {
    $image_info = getimagesize($source);
    if (!$image_info) return false;
    
    $width = $image_info[0];
    $height = $image_info[1];
    $type = $image_info[2];
    
    // Tính toán kích thước mới
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = intval($width * $ratio);
    $new_height = intval($height * $ratio);
    
    // Tạo ảnh từ file gốc
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $source_image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    // Tạo ảnh mới
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Giữ độ trong suốt cho PNG và GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Resize ảnh
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Lưu ảnh
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($new_image, $destination, $quality);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($new_image, $destination, 9);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($new_image, $destination);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($new_image, $destination, $quality);
            break;
    }
    
    // Giải phóng bộ nhớ
    imagedestroy($source_image);
    imagedestroy($new_image);
    
    return $result;
}
?>
