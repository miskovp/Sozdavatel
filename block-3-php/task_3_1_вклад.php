<?php
declare(strict_types=1);

/**
 * Вычисляет итоговую сумму вклада (простые проценты).
 * Работаем в целых числах (копейках), чтобы избежать проблем float.
 */
function getDepositFinalSum(int $amountKopecks, int $months, float $annualRate): int
{
    if ($amountKopecks < 0 || $months < 0 || $annualRate < 0) {
        throw new InvalidArgumentException("Аргументы не могут быть отрицательными.");
    }

    // Возвращает исходную сумму если вклад под 0 процентов или 0 месяцев
    if ($months === 0 || $annualRate === 0.0) {
        return $amountKopecks;
    }

    // Формула: S = P + (P * r * t / 1200)
    // Используем (float) только для промежуточного вычисления процента
    $interest = ($amountKopecks * $annualRate * $months) / 1200;

    return $amountKopecks + (int)round($interest);
}

// 2. Обработка формы (если данные переданы через GET)
$userResult = null;
$error = null;
if (isset($_GET['amount'], $_GET['months'], $_GET['rate'])) {
    try {
        $amount = (int)round((float)$_GET['amount'] * 100);
        $months = (int)$_GET['months'];
        $rate = (float)$_GET['rate'];
        $userResult = getDepositFinalSum($amount, $months, $rate) / 100;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}


// Верстка
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестовое задание 3.1</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; max-width: 800px; margin: 20px auto; padding: 0 20px; }
        .test-box { background: #f4f4f4; padding: 15px; border-radius: 5px; }
        .result { color: green; font-weight: bold; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>3.1 Расчет дохода по вкладу</h1>

    <form method="GET">
        <input type="number" name="amount" placeholder="Сумма (руб)" required step="0.01"
           value="<?= isset($_GET['amount']) ? htmlspecialchars((string)$_GET['amount']) : '' ?>">

        <input type="number" name="months" placeholder="Срок (мес)" required
           value="<?= isset($_GET['months']) ? htmlspecialchars((string)$_GET['months']) : '' ?>">

        <input type="number" name="rate" placeholder="Процент (%)" required step="0.1"
           value="<?= isset($_GET['rate']) ? htmlspecialchars((string)$_GET['rate']) : '' ?>">

        <button type="submit">Рассчитать</button>
    </form>

    <?php if ($error): ?>
        <p class="error">Ошибка: <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($userResult !== null): ?>
        <p class="result">Итоговая сумма: <?= number_format($userResult, 2, '.', ' ') ?> руб.</p>
    <?php endif; ?>

    <hr>

    <h3>Автоматические тесты функции:</h3>
    <div class="test-box">
        <?php
        $testCases = [
            [10000000, 12, 18.0, "Стандартный год 18%"],
            [10000000, 6, 10.0, "Полгода 10%"],
            [5000000, 1, 12.0, "1 месяц 12%"],
            [100000, 0, 20.0, "Срок 0 месяцев"],
        ];

        foreach ($testCases as [$sum, $term, $rate, $label]) {
            try {
                $res = getDepositFinalSum($sum, $term, $rate);
                echo "[$label]: " . ($res / 100) . " руб.<br>";
            } catch (Exception $e) {
                echo "[$label]: <span class='error'>Ошибка - " . $e->getMessage() . "</span><br>";
            }
        }
        ?>
    </div>
</body>
</html>