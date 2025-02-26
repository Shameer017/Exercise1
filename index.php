<?php
$voltage = $current = $rate = '';
$errors = [];
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $voltage = $_POST['voltage'] ?? '';
    $current = $_POST['current'] ?? '';
    $rate = $_POST['rate'] ?? '';

    // Validate inputs
    if (!is_numeric($voltage) || $voltage <= 0) {
        $errors[] = 'Voltage must be a positive number';
    }
    if (!is_numeric($current) || $current <= 0) {
        $errors[] = 'Current must be a positive number';
    }
    if (!is_numeric($rate) || $rate <= 0) {
        $errors[] = 'Rate must be a positive number';
    }

    if (empty($errors)) {
        // Calculate power in watts
        $power = $voltage * $current;

        // Calculate energy consumption per hour
        $energyPerHour = $power / 1000; // kWh for 1 hour
        $costPerHour = $energyPerHour * ($rate / 100); // Convert sen to RM

        // Format results
        $results = [
            'power' => number_format($power, 2) . ' W',
            'energy_hour' => number_format($energyPerHour, 3) . ' kWh',
            'cost_hour' => 'RM ' . number_format($costPerHour, 2)
        ];

        // Calculate cost for each hour till 24
        $hourlyCosts = [];
        for ($hour = 1; $hour <= 24; $hour++) {
            $energy = ($power * $hour) / 1000;
            $cost = $energy * ($rate / 100);
            $hourlyCosts[] = [
                'hour' => $hour,
                'energy' => number_format($energy, 3) . ' kWh',
                'cost' => 'RM ' . number_format($cost, 2)
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Cost Calculator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Electricity Cost Calculator</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="post">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="voltage">Voltage (V)</label>
                            <input type="number" step="0.01" class="form-control" 
                                   id="voltage" name="voltage" required 
                                   value="<?= htmlspecialchars($voltage) ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="current">Current (A)</label>
                            <input type="number" step="0.01" class="form-control" 
                                   id="current" name="current" required 
                                   value="<?= htmlspecialchars($current) ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="rate">Rate (sen/kWh)</label>
                            <input type="number" step="0.01" class="form-control" 
                                   id="rate" name="rate" required 
                                   value="<?= htmlspecialchars($rate) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Calculate</button>
                </form>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p class="mb-0"><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($results)): ?>
            <div class="card mb-4">
                <div class="card-header">Results (Per Hour)</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Power Consumption</dt>
                        <dd class="col-sm-6"><?= $results['power'] ?></dd>

                        <dt class="col-sm-6">Energy Usage (per hour)</dt>
                        <dd class="col-sm-6"><?= $results['energy_hour'] ?></dd>

                        <dt class="col-sm-6">Cost (per hour)</dt>
                        <dd class="col-sm-6"><?= $results['cost_hour'] ?></dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Cost Per Hour (1 to 24 Hours)</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Hour</th>
                                <th>Energy (kWh)</th>
                                <th>Cost (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hourlyCosts as $hourly): ?>
                                <tr>
                                    <td><?= $hourly['hour'] ?></td>
                                    <td><?= $hourly['energy'] ?></td>
                                    <td><?= $hourly['cost'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 
