<?php
$host = 'localhost';
$db   = 'diseño_ayudas';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'getParroquias') {
        $sql = "SELECT `codparro`, `nombre` FROM `parroquias` ORDER BY `nombre` ASC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }

    if ($_GET['action'] === 'getSectores' && isset($_GET['codparro'])) {
        $codparro = (int)$_GET['codparro'];
        $sql = "SELECT `id_sector`, `nombre_sector` FROM `sector` WHERE `id_parroquia` = $codparro ORDER BY `nombre_sector` ASC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

$submitted = false;
$selectedParroquia = null;
$selectedSector = null;
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedParroquia = $_POST['parroquia'] ?? '';
    $selectedSector = $_POST['sector'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $submitted = true;
}

$sql = "SELECT `codparro`, `nombre` FROM `parroquias` ORDER BY `nombre` ASC";
$result = mysqli_query($conn, $sql);
$parroquias = [];
while ($row = mysqli_fetch_assoc($result)) {
    $parroquias[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Selector de Parroquias y Sectores</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f7f9fc;
        margin: 0;
        padding: 20px;
        display: flex;
        justify-content: center;
    }
    .container {
        background: white;
        padding: 30px 40px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-width: 400px;
        width: 100%;
    }
    h1 {
        text-align: center;
        margin-bottom: 24px;
        color: #333;
    }
    label {
        display: block;
        margin-top: 15px;
        margin-bottom: 6px;
        font-weight: 600;
        color: #444;
    }
    select, textarea, button {
        width: 100%;
        padding: 10px 12px;
        font-size: 1rem;
        border-radius: 4px;
        border: 1px solid #ccc;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }
    select:focus, textarea:focus {
        border-color: #007BFF;
        outline: none;
        box-shadow: 0 0 6px #007BFF;
    }
    button {
        margin-top: 25px;
        background-color: #007BFF;
        border: none;
        color: white;
        font-weight: 700;
        cursor: pointer;
    }
    button:hover {
        background-color: #0056b3;
    }
    .message {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        padding: 15px;
        border-radius: 5px;
        color: #155724;
        margin-top: 20px;
        text-align: center;
        font-weight: 600;
    }
</style>
</head>
<body>
<div class="container">
    <h1>Formulario de Ubicación y Descripción</h1>
    <form method="post" id="locationForm" action="">
        <label for="parroquia">Parroquia</label>
        <select id="parroquia" name="parroquia" required>
            <option value="">-- Seleccione una parroquia --</option>
            <?php foreach ($parroquias as $p): ?>
                <option value="<?= htmlspecialchars($p['codparro']) ?>" <?= ($submitted && $selectedParroquia == $p['codparro']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="sector">Sector</label>
        <select id="sector" name="sector" required <?= ($submitted && $selectedSector) ? '' : 'disabled' ?>>
            <option value="">-- Seleccione un sector --</option>
        </select>

        <label for="description">Descripción</label>
        <textarea id="description" name="description" rows="4" placeholder="Escriba una descripción..." required><?= htmlspecialchars($description) ?></textarea>

        <button type="submit">Enviar</button>
    </form>

    <?php if ($submitted): ?>
    <div class="message">
        <strong>Datos enviados:</strong><br/>
        Parroquia ID: <?= htmlspecialchars($selectedParroquia) ?><br/>
        Sector ID: <?= htmlspecialchars($selectedSector) ?><br/>
        Descripción: <?= nl2br(htmlspecialchars($description)) ?>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const parroquiaSelect = document.getElementById('parroquia');
    const sectorSelect = document.getElementById('sector');

    function fetchOptions(url, targetSelect, placeholder) {
        fetch(url)
            .then(res => res.json())
            .then(data => {
                targetSelect.innerHTML = `<option value="">${placeholder}</option>`;
                data.forEach(item => {
                    const opt = document.createElement('option');
                    const idKey = Object.keys(item).find(k => k.startsWith('id_') || k === 'codparro');
                    const nameKey = Object.keys(item).find(k => !k.startsWith('id_') && k !== 'codparro');
                    opt.value = item[idKey];
                    opt.textContent = item[nameKey];
                    targetSelect.appendChild(opt);
                });
                targetSelect.disabled = false;
            })
            .catch(() => {
                targetSelect.innerHTML = '<option value="">Error al cargar</option>';
                targetSelect.disabled = true;
            });
    }

    parroquiaSelect.addEventListener('change', function () {
        const codparro = this.value;
        sectorSelect.disabled = true;
        sectorSelect.innerHTML = '<option value="">Cargando...</option>';

        if (codparro) {
            fetchOptions('?action=getSectores&codparro=' + encodeURIComponent(codparro), sectorSelect, '-- Seleccione un sector --');
        } else {
            sectorSelect.innerHTML = '<option value="">-- Seleccione un sector --</option>';
            sectorSelect.disabled = true;
        }
    });

    <?php if ($submitted && $selectedParroquia): ?>
    parroquiaSelect.value = <?= json_encode($selectedParroquia) ?>;
    fetchOptions('?action=getSectores&codparro=<?= urlencode($selectedParroquia) ?>', sectorSelect, '-- Seleccione un sector --')
        .then(() => {
            sectorSelect.value = <?= json_encode($selectedSector) ?>;
        });
    <?php endif; ?>
});
</script>
</body>
</html>