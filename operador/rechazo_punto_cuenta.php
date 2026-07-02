<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'nav/index.php';

if (!isset($_SESSION['puntoctaes'])) {
    $_SESSION['puntoctaes'] = [];
}

require_once "../db.php.php";

$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nrp_pta']) && isset($_POST['anio'])) {
        $nropto = $_POST['nrp_pta'];
        $anio = $_POST['anio'];

        $sql = "SELECT s.ids_pta, s.nrp_pta, s.asu_pta, s.ano_pta, s.nac_pta, s.fec_pto FROM puntocta s WHERE s.nrp_pta = ? AND YEAR(s.fec_pto) = ? AND fec_pag IS NULL ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nropto, $anio);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        if (!empty($results)) {
            $exists = false;
            foreach ($_SESSION['puntoctaes'] as $key => $puntocta) {
                if (
                    $puntocta['nrp_pta'] === $results[0]['nrp_pta'] &&
                    $puntocta['nac_pta'] === $results[0]['nac_pta']
                ) {
                    unset($_SESSION['puntoctaes'][$key]);
                    $exists = true;
                    echo "<script>alert('Punto de cuenta eliminado de la tabla.');</script>";
                    break;
                }
            }

            if (!$exists) {
                $_SESSION['puntoctaes'] = array_merge($_SESSION['puntoctaes'], $results);
            }
        }

        $puntoctaes = [];
        if (!empty($_SESSION['puntoctaes'])) {
            foreach ($_SESSION['puntoctaes'] as $puntocta) {
                $puntoctaes[] = $puntocta['ids_pta'];
            }
        }
        $puntoctaes = array_unique($puntoctaes);
        $puntoctaes_string = implode(", ", $puntoctaes);

        $stmt->close();
    } elseif (isset($_POST['restaurar'])) {
        $_SESSION['puntoctaes'] = [];
        echo "<script>alert('Todos los puntos de cuenta han sido restaurados.');</script>";
    } elseif (isset($_POST['desconsultar'])) {
        $nropto = $_POST['nropto_desconsultar'];
        foreach ($_SESSION['puntoctaes'] as $key => $puntocta) {
            if ($puntocta['nropto'] === $nropto) {
                unset($_SESSION['puntoctaes'][$key]);
                echo "<script>alert('Punto de cuenta desconsultado.');</script>";
                break;
            }
        }
    } elseif (isset($_POST['monto']) && isset($_POST['fecha'])) {
        $monto = $_POST['monto'];
        $fecha = $_POST['fecha'];
        $_SESSION['monto'] = $monto;
        $_SESSION['fecha'] = $fecha;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechazo de Punto de Cuenta</title>

    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* ===== Tema claro (por defecto) ===== */
        body.light-theme {
            background-color: #f5f7fa;
            color: #333;
        }

        /* ===== Tema oscuro ===== */
        body.dark-theme {
            background-color: #121212;
            color: #e0e0e0;
        }

        /* Contenedor principal */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-left: 17%;
            margin-top: 112px;
            width: 80%;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        body.light-theme .container {
            background-color: #ffffff;
        }

        body.dark-theme .container {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
            border: 1px solid #333;
        }

        /* Encabezados con fondo azul */
        .header-blue {
            background-color: #007bff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        body.dark-theme .header-blue {
            background-color: #0d6efd !important;
        }

        /* Títulos */
        h1, h2 {
            color: #333;
            font-size: large;
            margin: 0;
        }

        body.dark-theme h1,
        body.dark-theme h2 {
            color: #e0e0e0 !important;
        }

        /* Labels */
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        body.dark-theme label {
            color: #e0e0e0 !important;
        }

        /* Inputs y selects */
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select {
            width: 476px;
            margin-right: 10px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="number"],
        input[type="text"],
        .input-nropto-anio {
            width: 172.66666px;
            margin-right: 10px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        body.dark-theme input[type="text"],
        body.dark-theme input[type="number"],
        body.dark-theme input[type="date"],
        body.dark-theme select,
        body.dark-theme .input-nropto-anio {
            background-color: #2d2d2d !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }

        /* Botones */
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 23px;
        }

        button:hover {
            background-color: #0056b3;
        }

        body.dark-theme button {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #444 !important;
        }

        body.dark-theme button:hover {
            background-color: #0b5ed7 !important;
        }

        /* Tabla de resultados */
        .resultados {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }

        body.light-theme .resultados {
            background: #e9ecef;
        }

        body.dark-theme .resultados {
            background: #252525 !important;
            color: #e0e0e0 !important;
            border: 1px solid #444;
        }

        .resultados table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .resultados th,
        .resultados td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .resultados th {
            background-color: #f2f2f2;
        }

        body.dark-theme .resultados th {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border-color: #444 !important;
        }

        body.dark-theme .resultados td {
            border-color: #444 !important;
        }

        /* Texto general */
        p {
            color: #555;
        }

        body.dark-theme p {
            color: #bbb !important;
        }

        /* Form rows */
        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            width: 429px;
            margin-left: 20px;
            margin-top: 10px;
        }

        /* Botón de tema */
        #themeToggle {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body class="light-theme">
    <!-- Botón de cambio de tema -->
    <button id="themeToggle" class="btn btn-outline-secondary">
        <i class="fas fa-moon"></i> Modo Oscuro
    </button>

    <div class="container">
        <form method="POST" id="consultaForm">
            <div class="header-blue">
                <h1>Rechazo de Punto de Cuenta</h1>
            </div>
            <br>
            <center><h1>Consulta de Punto de Cuenta</h1></center><br>
            
            <div class="form-row">
                <div class="col-md-6">
                    <label for="nrp_pta">Número de punto:</label>
                    <input type="text" id="nrp_pta" name="nrp_pta" class="input-nropto-anio" required>
                </div>
                <div class="col-md-6">
                    <label for="anio">Año:</label>
                    <input type="number" id="anio" name="anio" class="input-nropto-anio" required>
                </div>
                <button type="submit" class="boton3">Consultar</button>
            </div>
        </form>

        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="restaurar">Restaurar Todos los puntos de cuenta</button>
        </form>

        <form method="POST" action="pre_rechazo.php">
            <div class="resultados">
                <?php if (!empty($_SESSION['puntoctaes'])): ?>
                    <h2>Resultados Acumulados</h2>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Puntocta</th>
                                <th>Fecha</th>
                                <th>Asunto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['puntoctaes'] as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nrp_pta']) ?></td>
                                    <td><?= htmlspecialchars($row['fec_pto']) ?></td>
                                    <td><?= htmlspecialchars($row['asu_pta']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No hay punto de cuenta acumuladas.</p>
                <?php endif; ?>
            </div>

            <input type="hidden" id="puntoctaes" name="puntoctaes" value="<?= htmlspecialchars($puntoctaes_string) ?>">

            <center><h2>Registrar Descripción y Fecha</h2></center>
            <div class="form-row">
                <div class="col-md-6">
                    <label for="monto">Descripción:</label>
                    <input type="text" id="monto" name="monto" required>
                </div>
                <div class="col-md-6">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" required style="width: 222px;">
                </div>
            </div>

            <center><button type="submit">Realizar Rechazo</button></center>
        </form>
    </div>

    <!-- Scripts -->
    <script>
        // Aplicar tema guardado al cargar
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.className = savedTheme + '-theme';
        updateToggleButton(savedTheme);

        // Cambiar tema
        document.getElementById('themeToggle').addEventListener('click', function () {
            const isDark = document.body.classList.contains('dark-theme');
            const newTheme = isDark ? 'light' : 'dark';
            document.body.className = newTheme + '-theme';
            localStorage.setItem('theme', newTheme);
            updateToggleButton(newTheme);
        });

        function updateToggleButton(theme) {
            const btn = document.getElementById('themeToggle');
            if (theme === 'dark') {
                btn.innerHTML = '<i class="fas fa-sun"></i> Modo Claro';
            } else {
                btn.innerHTML = '<i class="fas fa-moon"></i> Modo Oscuro';
            }
        }
    </script>
</body>
</html>