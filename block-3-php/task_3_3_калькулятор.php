<?php
declare(strict_types=1);


// Выполняет арифметическую операцию.

function calculate(float $num1, float $num2, string $operator): float
{
    return match ($operator) {
        '+' => $num1 + $num2,
        '-' => $num1 - $num2,
        '*' => $num1 * $num2,
        '/' => $num2 === 0.0 ? throw new DivisionByZeroError("Деление на ноль.") : $num1 / $num2,
        default => throw new InvalidArgumentException("Некорректный оператор."),
    };
}

$result = null;
$error = null;
$allowedOperators = ['+', '-', '*', '/'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['num1'], $_GET['num2'], $_GET['operator'])) {
    try {
        if (!is_numeric($_GET['num1']) || !is_numeric($_GET['num2'])) {
            throw new InvalidArgumentException("Введены не числовые значения.");
        }

        $result = calculate(
            (float)$_GET['num1'],
            (float)$_GET['num2'],
            $_GET['operator']
        );
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Задание 3.3</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 500px; margin: auto; }
        .calc-row { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
        input, select, button { padding: 5px; }
        .error { color: red; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Простейший калькулятор</h2>

    <form method="GET">
        <div class="calc-row">
            <input type="number" name="num1" step="any" required
                   value="<?= htmlspecialchars($_GET['num1'] ?? '') ?>">

            <select name="operator">
                <?php foreach ($allowedOperators as $op): ?>
                    <option value="<?= $op ?>" <?= (($_GET['operator'] ?? '') === $op) ? 'selected' : '' ?>>
                        <?= $op ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="num2" step="any" required
                   value="<?= htmlspecialchars($_GET['num2'] ?? '') ?>">

            <button type="submit">=</button>
        </div>
    </form>

    <?php if ($error): ?>
        <p class="error">Ошибка: <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($result !== null): ?>
        <p class="success">Результат: <?= $result ?></p>
    <?php endif; ?>
</body>
</html>