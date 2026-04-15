<?php
declare(strict_types=1);

/**
 * Возвращает дату в формате "число месяц" на русском языке.
 * @param int $day Число (1..31)
 * @param int $month Номер месяца (1..12)
 * @return string
 * @throws InvalidArgumentException
 */

function getRussianDate(int $day, int $month): string
{
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта',
        4 => 'апреля', 5 => 'мая', 6 => 'июня',
        7 => 'июля', 8 => 'августа', 9 => 'сентября',
        10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    ];

    if ($month < 1 || $month > 12) {
        throw new InvalidArgumentException("Некорректный номер месяца: $month");
    }

    // Базовая проверка дней в месяце (без учета високосного года, так как год не передается)
    $maxDays = [1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
                7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31];

    if ($day < 1 || $day > $maxDays[$month]) {
        throw new InvalidArgumentException("Некорректное число $day для выбранного месяца");
    }

    return $day . ' ' . $months[$month];
}

// Обработка формы
$userResult = null;
$error = null;
if (isset($_GET['day'], $_GET['month'])) {
    try {
        $day = (int)$_GET['day'];
        $month = (int)$_GET['month'];
        $userResult = getRussianDate($day, $month);
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
    <title>Тестовое задание 3.2</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; max-width: 800px; margin: 20px auto; padding: 0 20px; }
        .test-box { background: #f4f4f4; padding: 15px; border-radius: 5px; }
        .result { color: green; font-weight: bold; font-size: 1.2em; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>3.2 Форматирование даты</h1>

    <form method="GET">
        <input type="number" name="day" placeholder="Число (1-31)" required min="1" max="31"
               value="<?= isset($_GET['day']) ? htmlspecialchars((string)$_GET['day']) : '' ?>">

        <select name="month" required>
            <option value="">Выберите месяц</option>
            <?php
            $mNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                       'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
            foreach ($mNames as $idx => $name):
                $val = $idx + 1;
                $selected = (isset($_GET['month']) && (int)$_GET['month'] === $val) ? 'selected' : '';
            ?>
                <option value="<?= $val ?>" <?= $selected ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Преобразовать</button>
    </form>

    <?php if ($error): ?>
        <p class="error">Ошибка: <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($userResult !== null): ?>
        <p class="result">Результат: <?= htmlspecialchars($userResult) ?></p>
    <?php endif; ?>

    <hr>

    <h3>Автоматические тесты:</h3>
    <div class="test-box">
        <?php
        $testCases = [
            [1, 1, "Начало года"],
            [9, 5, "День Победы"],
            [31, 12, "Конец года"],
            [14, 4, "Дата когда выполнялось задание"],
            [31, 6, "Некорректная дата (июнь)"],
        ];

        foreach ($testCases as [$d, $m, $label]) {
            try {
                $res = getRussianDate($d, $m);
                echo "[$label]: $d.$m -> <b>$res</b><br>";
            } catch (Exception $e) {
                echo "[$label]: $d.$m -> <span class='error'>Ожидаемая ошибка: " . $e->getMessage() . "</span><br>";
            }
        }
        ?>
    </div>
</body>
</html>