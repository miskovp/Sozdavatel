<?php
declare(strict_types=1);


// конфиг
const UPLOAD_DIR_NAME = 'uploads';
const UPLOAD_PATH = __DIR__ . '/' . UPLOAD_DIR_NAME . '/';
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 МБ


$message = '';
$error = '';

if (!is_dir(UPLOAD_PATH)) {
    if (!mkdir(UPLOAD_PATH, 0755, true) && !is_dir(UPLOAD_PATH)) {
        $error = "Системная ошибка: Не удалось создать директорию для загрузки.";
    }
}


// Обработка POST-запроса (загрузки файла)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && empty($error)) {
    try {
        $file = $_FILES['image'];

        // Базовые проверки ошибок загрузки
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Ошибка загрузки файла. Код: " . $file['error']);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Ошибка: файл не был загружен через HTTP POST.");
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception("Файл слишком большой. Максимум 5 МБ.");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            throw new Exception("Недопустимое расширение файла.");
        }

        // Защита от подмены расширения: проверяем реальный MIME-тип
        $imageInfo = @getimagesize($file['tmp_name']);

        if ($imageInfo === false || !isset($imageInfo['mime'])) {
            throw new Exception("Файл поврежден или не является изображением.");
        }

        if (!in_array($imageInfo['mime'], ALLOWED_MIME_TYPES)) {
            throw new Exception("Недопустимый тип содержимого файла.");
        }

        // Генерируем случайное имя файла для безопасности (защита от XSS и проблем с кодировками)
        $fileName = bin2hex(random_bytes(8)) . '.' . $extension;
        $destination = UPLOAD_PATH . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // PRG паттерн, чтобы избежать повторной отправки формы по F5
            header("Location: ?status=success");
            exit;
        } else {
            throw new Exception("Не удалось сохранить файл на диск.");
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $message = "Изображение успешно загружено.";
}


// Возвращает массив относительных путей до картинок для вывода в HTML
function getGalleryImages(string $dirPath, string $dirName): array
{
    if (!is_dir($dirPath)) return [];

    $files = scandir($dirPath);
    $images = [];

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $dirPath . $file;

        if (is_file($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_EXTENSIONS)) {
                // Возвращаем веб-путь для HTML, а не физический путь сервера
                $images[] = $dirName . '/' . $file;
            }
        }
    }

    return $images;
}

$galleryImages = getGalleryImages(UPLOAD_PATH, UPLOAD_DIR_NAME);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестовое задание 3.4 - Фотогалерея</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 20px auto; padding: 0 20px; background: #fafafa; }
        .upload-section { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
        .gallery-item { background: #fff; padding: 10px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .gallery-item img { width: 100%; height: 150px; object-fit: cover; border-radius: 3px; display: block; }
        .error { color: #d93025; background: #fce8e6; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { color: #188038; background: #e6f4ea; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        form { display: flex; gap: 10px; align-items: center; }
    </style>
</head>
<body>
    <h1>3.4 Фотогалерея</h1>

    <div class="upload-section">
        <h3>Добавить фото</h3>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="image" accept="image/*" required>
            <button type="submit">Загрузить</button>
        </form>
    </div>

    <div class="gallery">
        <?php if (empty($galleryImages)): ?>
            <p>Галерея пуста. Загрузите первое изображение.</p>
        <?php else: ?>
            <?php foreach ($galleryImages as $imageWebPath): ?>
                <div class="gallery-item">
                    <a href="<?= htmlspecialchars($imageWebPath) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($imageWebPath) ?>" alt="Изображение">
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>