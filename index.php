<?php
// Function to retrieve and sanitize input
function get_input($key) {
    return filter_input(INPUT_POST, $key, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

// Function to validate inputs
function validate_input($value, $name) {
    if (!filter_var($value, FILTER_VALIDATE_FLOAT) || $value <= 0) {
        return "Please enter a valid positive number for $name.";
    }
    return '';
}

// Function to calculate electrical values
function calculate_energy($voltage, $current, $rate) {
    $power = $voltage * $current;
    $energyPerHour = $power / 1000;
    $costPerHour = $energyPerHour * ($rate / 100);
    return [
        'power' => number_format($power, 2) . ' Watts',
        'energy_hour' => number_format($energyPerHour, 3) . ' kWh',
        'cost_hour' => 'RM ' . number_format($costPerHour, 2)
    ];
}

// Function to calculate hourly breakdown
function calculate_hourly_breakdown($voltage, $current, $rate) {
    $hourlyCosts = [];
    $power = $voltage * $current;
    for ($hour = 1; $hour <= 24; $hour++) {
        $energy = ($power * $hour) / 1000;
        $cost = $energy * ($rate / 100);
        $hourlyCosts[] = [
            'hour' => $hour,
            'energy' => number_format($energy, 3) . ' kWh',
            'cost' => 'RM ' . number_format($cost, 2)
        ];
    }
    return $hourlyCosts;
}

// Initialize variables
$voltage = $current = $rate = '';
$errors = [];
$results = [];
$hourlyCosts = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voltage = get_input('voltage');
    $current = get_input('current');
    $rate = get_input('rate');

    // Validate inputs
    $errors[] = validate_input($voltage, 'Voltage');
    $errors[] = validate_input($current, 'Current');
    $errors[] = validate_input($rate, 'Rate');
    $errors = array_filter($errors);

    // Proceed if no errors
    if (empty($errors)) {
        $results = calculate_energy($voltage, $current, $rate);
        $hourlyCosts = calculate_hourly_breakdown($voltage, $current, $rate);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy Consumption Calculator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center">🔌 Energy Consumption Calculator</h2>
        <form method="post" class="card p-4 mb-4">
            <label>Voltage (Volts)</label>
            <input class="form-control" type="text" pattern="^[0-9]*\.?[0-9]+$" name="voltage" placeholder="e.g. 240" required value="<?= htmlspecialchars($voltage) ?>">
            <label>Current (Amperes)</label>
            <input class="form-control" type="text" pattern="^[0-9]*\.?[0-9]+$" name="current" placeholder="e.g. 5" required value="<?= htmlspecialchars($current) ?>">
            <label>Energy Rate (sen/kWh)</label>
            <input class="form-control" type="text" pattern="^[0-9]*\.?[0-9]+$" name="rate" placeholder="e.g. 25.5" required value="<?= htmlspecialchars($rate) ?>">
            <button class="btn btn-primary btn-block mt-3">Calculate</button>
        </form>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mt-4">
                <h3>Error!</h3>
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($results)): ?>
            <div class="card p-4 mb-4">
                <h3>Results:</h3>
                <p>Power Consumption: <?= $results['power'] ?></p>
                <p>Hourly Energy Usage: <?= $results['energy_hour'] ?></p>
                <p>Hourly Cost: <?= $results['cost_hour'] ?></p>
            </div>

            <div class="card p-4 mb-4">
                <h3>24-Hour Cost Breakdown:</h3>
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>Hour</th>
                        <th>Energy Consumed (kWh)</th>
                        <th>Cost (RM)</th>
                    </tr>
                    <?php foreach ($hourlyCosts as $hourly): ?>
                        <tr>
                            <td><?= $hourly['hour'] ?></td>
                            <td><?= $hourly['energy'] ?></td>
                            <td><?= $hourly['cost'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
