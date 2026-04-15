<?php
declare(strict_types=1);

/**
 * Вычисляет угол между часовой и минутной стрелками
 */

function getClockAngle(int $hours, int $minutes): float
{
    if ($hours < 0 || $hours > 23) {
        throw new InvalidArgumentException("Некорректное значение часов: $hours");
    }
    if ($minutes < 0 || $minutes > 59) {
        throw new InvalidArgumentException("Некорректное значение минут: $minutes");
    }

    // Приводим к 12-часовому формату
    $h = $hours % 12;

    // Позиция часовой стрелки в градусах от 12:00
    $hourPos = ($h * 30) + ($minutes * 0.5);

    // Позиция минутной стрелки в градусах от 12:00
    $minPos = $minutes * 6;

    // Абсолютная разница
    $angle = abs($hourPos - $minPos);

    // Возвращаем минимальный угол
    return $angle > 180 ? 360 - $angle : $angle;
}

// Обработка данных формы
$resultAngle = null;
$error = null;
if (isset($_GET['h'], $_GET['m'])) {
    try {
        $resultAngle = getClockAngle((int)$_GET['h'], (int)$_GET['m']);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!-- Вёрстка -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестовое задание 3.5</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 20px auto; line-height: 1.6; }
        .result-box { background: #e7f3fe; padding: 10px; border-left: 5px solid #2196F3; margin-top: 10px; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>3.5 Угол между стрелками</h1>

    <form method="GET">
        <input type="number" name="h" placeholder="Часы (0-23)" required min="0" max="23"
               value="<?= isset($_GET['h']) ? htmlspecialchars((string)$_GET['h']) : '' ?>">
        <input type="number" name="m" placeholder="Минуты (0-59)" required min="0" max="59"
               value="<?= isset($_GET['m']) ? htmlspecialchars((string)$_GET['m']) : '' ?>">
        <button type="submit">Рассчитать</button>
    </form>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($resultAngle !== null): ?>
        <div class="result-box">
            Результат: <b><?= $resultAngle ?>°</b>
        </div>
    <?php endif; ?>

    <hr>
    <h3>Тестовые примеры:</h3>
    <ul>
        <li>3:00 -> <?= getClockAngle(3, 0) ?>° (90°)</li>
        <li>3:15 -> <?= getClockAngle(3, 15) ?>° (должно быть 7.5°, так как часовая ушла вперед)</li>
        <li>18:00 -> <?= getClockAngle(18, 0) ?>° (180°)</li>
        <li>12:01 -> <?= getClockAngle(12, 1) ?>° (5.5°)</li>
    </ul>
</body>
</html>