<?php
require_once "../db.php.php";

// Función para crear beneficiario
function crearBeneficiario($nac_ben, $ced_ben, $nom_ben, $ape_ben, $dir_ben, $cod_par, $cor_ben, $tlf_ben, $sec_ben)
{
    global $conn;

    $sql_check = "SELECT 1 FROM beneficiario WHERE ced_ben = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $ced_ben);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $stmt_check->close();
        return ['error' => 'La cédula de identidad ya está registrada.'];
    }
    $stmt_check->close();

    $sql = "INSERT INTO beneficiario (nac_ben, ced_ben, nom_ben, ape_ben, dir_ben, cod_par, cor_ben, tlf_ben, sec_ben) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $nac_ben, $ced_ben, $nom_ben, $ape_ben, $dir_ben, $cod_par, $cor_ben, $tlf_ben, $sec_ben);

    if ($stmt->execute()) {
        $ids_bene = $stmt->insert_id;
        $stmt->close();
        return ['ids_bene' => $ids_bene];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['error' => 'Error al insertar: ' . $error];
    }
}

// Función para obtener beneficiarios con nombre de parroquia
function obtenerBeneficiarios()
{
    global $conn;
    $sql = "
        SELECT 
    b.ids_bene,
    b.nac_ben,
    b.ced_ben,
    b.nom_ben,
    b.ape_ben,
    b.dir_ben,
    b.cod_par,
    p.nom_par AS nombre_parroquia,
    b.cor_ben,
    b.tlf_ben,
    s.nom_sec
FROM beneficiario b
LEFT JOIN parroquias p ON b.cod_par = p.cod_par
LEFT JOIN sector s ON b.sec_ben = s.ids_sec 
ORDER BY b.ids_bene DESC;";
    return $conn->query($sql);
}

// Función para actualizar beneficiario
function actualizarBeneficiario($ids_bene, $nac_ben, $ced_ben, $nom_ben, $ape_ben, $dir_ben, $cod_par, $cor_ben, $tlf_ben, $sec_ben)
{
    global $conn;
    
    // Validar que la cédula no esté duplicada en otro registro
    $sql_check = "SELECT 1 FROM beneficiario WHERE ced_ben = ? AND ids_bene != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $ced_ben, $ids_bene);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $stmt_check->close();
        return ['error' => 'La cédula de identidad ya está registrada en otro beneficiario.'];
    }
    $stmt_check->close();
    
    $sql = "UPDATE beneficiario 
            SET nac_ben = ?, ced_ben = ?, nom_ben = ?, ape_ben = ?, dir_ben = ?, 
                cod_par = ?, cor_ben = ?, tlf_ben = ?, sec_ben = ? 
            WHERE ids_bene = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $nac_ben, $ced_ben, $nom_ben, $ape_ben, $dir_ben, $cod_par, $cor_ben, $tlf_ben, $sec_ben, $ids_bene);

    $success = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();

    return $success ? ['success' => true] : ['error' => $error];
}

// ------------------------------
// Manejo de peticiones AJAX y POST
// ------------------------------

// Respuestas JSON para AJAX (parroquias y sectores)
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'getParroquias') {
        $sql = "SELECT `cod_par`, `nom_par` FROM `parroquias` ORDER BY `nom_par` ASC";
        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }

    if ($_GET['action'] === 'getsec_benes' && isset($_GET['cod_par'])) {
        $cod_par = (int)$_GET['cod_par'];
        $sql = "SELECT `ids_sec`, `nom_sec` FROM `sector` WHERE `cod_par` = ? ORDER BY `nom_sec` ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cod_par);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        $stmt->close();
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Acción inválida']);
    exit;
}

// Procesar formulario POST
$mensaje = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el valor real de cor_ben desde el campo oculto
    $cor_ben_real = $_POST['cor_ben_value'] ?? $_POST['cor_ben'] ?? '';
    
    // ================= VALIDACIÓN DE CÉDULA =================
    $ced_ben_post = trim($_POST['ced_ben'] ?? '');
    
    if (empty($ced_ben_post)) {
        $error = "La cédula es requerida.";
    } elseif ($ced_ben_post === '0') {
        $error = "La cédula no puede ser 0.";
    } elseif (strpos($ced_ben_post, '0') === 0) {
        $error = "La cédula no puede comenzar con 0.";
    } elseif (!preg_match('/^[1-9][0-9]{0,8}$/', $ced_ben_post)) {
        $error = "La cédula debe contener solo números (1-9 dígitos) y no comenzar con 0.";
    } else {
        // ================= PROCESAR FORMULARIO =================
        if (isset($_POST['crear'])) {
            $response = crearBeneficiario(
                $_POST['nac_ben'],
                $ced_ben_post,
                $_POST['nom_ben'],
                $_POST['ape_ben'], 
                $_POST['dir_ben'],
                $_POST['parroquia'],
                $cor_ben_real,
                $_POST['tlf_ben'],
                $_POST['sec_ben']
            );

            if (isset($response['ids_bene'])) {
                $ids_bene = $response['ids_bene'];
                session_start();
                $_SESSION['ids_bene'] = $ids_bene;
                header("Location: aprobar.php?ids_bene=" . urlencode($ids_bene));
                exit();
            } else {
                $error = $response['error'];
            }
        } elseif (isset($_POST['actualizar'])) {
            $ced_ben_update = $_POST['ced_ben_original'] ?: $ced_ben_post;
            $response = actualizarBeneficiario(
                $_POST['ids_bene'],
                $_POST['nac_ben'],
                $ced_ben_update,
                $_POST['nom_ben'],
                $_POST['ape_ben'],
                $_POST['dir_ben'],
                $_POST['parroquia'],
                $cor_ben_real,
                $_POST['tlf_ben'],
                $_POST['sec_ben']
            );

            if (isset($response['success'])) {
                $mensaje = "Beneficiario actualizado exitosamente.";
                echo "<script>if(window.history.replaceState){window.history.replaceState(null,null,window.location.href);}</script>";
            } else {
                $error = "Error al actualizar: " . $response['error'];
            }
        }
    }
}

// Cargar datos para selects
$beneficiarios = obtenerBeneficiarios();

$sql_parroquias = "SELECT `cod_par`, `nom_par` FROM `parroquias` ORDER BY `nom_par` ASC";
$result_parroquias = $conn->query($sql_parroquias);
$parroquias = [];
while ($row = $result_parroquias->fetch_assoc()) {
    $parroquias[] = $row;
}

// Estado del formulario (para rellenar tras POST)
$submitted = !empty($_POST);
$selectedParroquia = $_POST['parroquia'] ?? '';
$selectedsec_ben = $_POST['sec_ben'] ?? '';
$predefinedValues = [
    'ids_bene' => $_POST['ids_bene'] ?? '',
    'nac_ben' => $_POST['nac_ben'] ?? 'V',
    'ced_ben' => $_POST['ced_ben'] ?? '',
    'nom_ben' => $_POST['nom_ben'] ?? '',
    'ape_ben' => $_POST['ape_ben'] ?? '',
    'dir_ben' => $_POST['dir_ben'] ?? '',
    'cor_ben' => $_POST['cor_ben'] ?? '',
    'cor_ben_value' => $_POST['cor_ben_value'] ?? $_POST['cor_ben'] ?? '',
    'tlf_ben' => $_POST['tlf_ben'] ?? '',
    'sec_ben' => $_POST['sec_ben'] ?? '',
];

include 'nav/index.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Beneficiarios</title>

      <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap4.min.css">


    <style>
        .wrapper {
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .main_container {
            margin-top: 20px;
        }

        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .success-message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        /* ===== Temas claro/oscuro ===== */
        body.light-theme {
            background-color: #f5f7fa;
            color: #333;
        }

        body.dark-theme {
            background-color: #121212;
            color: #e0e0e0;
        }

        /* Contenedores principales */
        body.dark-theme .wrapper,
        body.dark-theme .main_container,
        body.dark-theme .form2,
        body.dark-theme .dataTables_wrapper {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }

        /* Tabla y celdas */
        body.dark-theme .table {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
        }

        body.dark-theme .table th {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border-color: #444 !important;
        }

        body.dark-theme .table td {
            border-color: #444 !important;
        }

        body.dark-theme .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        body.dark-theme .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Inputs y selects */
        body.dark-theme input[type="text"],
        body.dark-theme input[type="tel"],
        body.dark-theme input[type="hidden"],
        body.dark-theme select,
        body.dark-theme .form-control {
            background-color: #2d2d2d !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }

        body.dark-theme input::placeholder,
        body.dark-theme select:disabled {
            color: #aaa !important;
        }

        /* Etiquetas y texto */
        body.dark-theme label,
        body.dark-theme h2,
        body.dark-theme .text-center {
            color: #e0e0e0 !important;
        }

        /* Mensajes */
        body.dark-theme .error-message,
        body.dark-theme .success-message {
            background-color: #2d2020 !important;
            color: #f8d7da !important;
            border-color: #5d2020 !important;
        }

        body.dark-theme .success-message {
            background-color: #202d20 !important;
            color: #d4edda !important;
            border-color: #205d20 !important;
        }

        /* Botones */
        body.dark-theme .btn-outline-primary,
        body.dark-theme .btn-outline-secondary {
            color: #bbb !important;
            border-color: #444 !important;
        }

        body.dark-theme .btn-outline-primary:hover,
        body.dark-theme .btn-outline-secondary:hover {
            background-color: #333 !important;
            color: #fff !important;
        }

        body.dark-theme .btn-success,
        body.dark-theme .btn-primary {
            border: 1px solid #444 !important;
        }

        /* Botón de tema */
        #themeToggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .form-check-label {
            cursor: pointer;
        }
        
        #employeeTypeContainer {
            display: none;
        }
    </style>
</head>

<body class="light-theme">
    <!-- Botón de cambio de tema -->
    <button id="themeToggle" class="btn btn-outline-secondary">
        <i class="fas fa-moon"></i> Modo Oscuro
    </button>

    <div style="display: flex; flex-direction: column; margin-left: 17%; margin-top: 4%; width: 81%;">
        <div class="wrapper">
            <div class="main_container">
                <h2>Lista de Beneficiarios</h2>

                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($mensaje): ?>
                    <div class="success-message"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>

                <div class="form2">
    <!-- ✅ Añadimos data-table="true" para forzar lectura del DOM -->
    <table id="beneficiariosTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%" data-table="true">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nac.</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Dirección</th>
                <th>Parroquia</th>
                <th>Tipo</th>
                <th>Teléfono</th>
                <th>Sector</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($beneficiarios && $beneficiarios->num_rows > 0): ?>
                <?php while ($row = $beneficiarios->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ids_bene']) ?></td>
                        <td><?= htmlspecialchars($row['nac_ben']) ?></td>
                        <td><?= htmlspecialchars($row['ced_ben']) ?></td>
                        <td><?= htmlspecialchars($row['nom_ben']) ?></td>
                        <td><?= htmlspecialchars($row['ape_ben']) ?></td>
                        <td><?= htmlspecialchars($row['dir_ben']) ?></td>
                        <td><?= htmlspecialchars($row['nombre_parroquia'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($row['cor_ben'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['tlf_ben']) ?></td>
                        <td><?= htmlspecialchars($row['nom_sec']) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick='editarBeneficiario(
                                <?= (int)$row['ids_bene'] ?>,
                                "<?= htmlspecialchars($row['nac_ben'], ENT_QUOTES, 'UTF-8') ?>",
                                "<?= htmlspecialchars($row['ced_ben'], ENT_QUOTES, 'UTF-8') ?>",
                                "<?= htmlspecialchars($row['nom_ben'], ENT_QUOTES, 'UTF-8') ?>",
                                "<?= htmlspecialchars($row['ape_ben'], ENT_QUOTES, 'UTF-8') ?>",
                                "<?= htmlspecialchars($row['dir_ben'], ENT_QUOTES, 'UTF-8') ?>",
                                "<?= htmlspecialchars($row['cod_par'], ENT_QUOTES, 'UTF-8') ?>",
                                "<?= htmlspecialchars($row['cor_ben'], ENT_QUOTES, 'UTF-8') ?>",
                                "<?= htmlspecialchars($row['tlf_ben'], ENT_QUOTES, 'UTF-8') ?>",
                                "<?= htmlspecialchars($row['nom_sec'], ENT_QUOTES, 'UTF-8') ?>"
                            )'>
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                 <tr>
                                            <td>No hay ayudas finalizadas.</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td> <td></td> <td></td> <td></td> <td></td>
                                        
                                        </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
            </div>
        </div>

        <div class="wrapper">
            <div class="main_container">
                <h2 class="text-center">Formulario de Beneficiario</h2>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <input type="hidden" name="ids_bene" value="<?= htmlspecialchars($predefinedValues['ids_bene']) ?>">
                    <input type="hidden" name="cor_ben_value" id="cor_ben_value" value="<?= htmlspecialchars($predefinedValues['cor_ben_value']) ?>">

                    <!-- Fila 1: Nacionalidad y Cédula -->
                    <div class="form-row mb-3">
                        <div class="col-md-6">
    <label for="nac_ben">Nacionalidad</label>
    <select id="nac_ben" name="nac_ben" class="form-control" required>
        <option value="V" 
            <?= $predefinedValues['nac_ben'] === 'V' ? 'selected' : '' ?> 
            data-restricted="v">
            V
        </option>
        <option value="E" 
            <?= $predefinedValues['nac_ben'] === 'E' ? 'selected' : '' ?> 
            data-restricted="e">
            E
        </option>
    </select>
</div>
                       <div class="col-md-6">
                            <label for="ced_ben">Cédula <small id="aviso_cedula" class="text-danger" style="display:none;">(No editable)</small></label>
                            <input type="text" class="form-control" id="ced_ben" name="ced_ben"
                                value="<?= htmlspecialchars($predefinedValues['ced_ben']) ?>"
                                maxlength="9" pattern="[1-9][0-9]{0,8}" required inputmode="numeric"
                                title="La cédula debe tener 1-9 dígitos, solo números, y no comenzar con 0">
                            <!-- Campo oculto para guardar cédula original -->
                            <input type="hidden" name="ced_ben_original" id="ced_ben_original" value="<?= htmlspecialchars($predefinedValues['ced_ben']) ?>">
                            </div>
                    </div>

                    <!-- Fila 2: Nombre y Apellido -->
                    <div class="form-row mb-3">
                        <div class="col-md-6">
                            <label for="nom_ben">Nombres</label>
                            <input type="text" class="form-control" id="nom_ben" name="nom_ben"
                                value="<?= htmlspecialchars($predefinedValues['nom_ben']) ?>"
                                onkeyup="this.value = this.value.toUpperCase()" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ape_ben">Apellidos</label>
                            <input type="text" class="form-control" id="ape_ben" name="ape_ben"
                                value="<?= htmlspecialchars($predefinedValues['ape_ben']) ?>"
                                onkeyup="this.value = this.value.toUpperCase()" required>
                        </div>
                    </div>

                    <!-- Fila 3: Parroquia y Sector -->
                    <div class="form-row mb-3">
                        <div class="col-md-6">
                            <label for="parroquia">Parroquia</label>
                            <select id="parroquia" name="parroquia" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($parroquias as $p): ?>
                                    <option value="<?= htmlspecialchars($p['cod_par']) ?>"
                                        <?= $selectedParroquia == $p['cod_par'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nom_par']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="sec_ben">Sector</label>
                            <select id="sec_ben" name="sec_ben" class="form-control" required
                                <?= !$selectedParroquia ? 'disabled' : '' ?>>
                                <option value="">-- Seleccione --</option>
                                <!-- Opciones se llenan vía JS -->
                            </select>
                        </div>
                    </div>

                    <!-- Fila 4: Dirección y Teléfono -->
                    <div class="form-row mb-3">
                        <div class="col-md-6">
                        <label for="dir_ben">Dirección</label>
                        <input type="text" class="form-control" id="dir_ben" name="dir_ben" 
                            value="<?= htmlspecialchars($predefinedValues['dir_ben']) ?>" 
                            maxlength="60" required 
                            onkeyup="this.value = this.value.toUpperCase(); actualizarContadorDir(this)">
                        <small id="contadorDir" class="form-text text-muted">0 / 60 caracteres</small>
                    </div>

                    <script>
                        function actualizarContadorDir(input) {
    const contador = document.getElementById('contadorDir');
    const len = input.value.length;
    contador.textContent = `${len} / 60 caracteres`;
    contador.className = len > 50 ? 'form-text text-danger font-weight-bold' : 'form-text text-muted';
}

// Inicializar contador al cargar (para valores prellenados tras POST o edición)
document.addEventListener('DOMContentLoaded', () => {
    const dirInput = document.getElementById('dir_ben');
    if (dirInput) actualizarContadorDir(dirInput);
});
                    </script>
                        <div class="col-md-6">
                            <label for="tlf_ben">Teléfono (041/042/0276)</label>
                            <input type="tel" class="form-control" id="tlf_ben" name="tlf_ben"
                                value="<?= htmlspecialchars($predefinedValues['tlf_ben']) ?>"
                                 maxlength="11"  pattern="(041|042|0276)[0-9]{7,10}" required
                                title="Debe comenzar con 041, 042 o 0276 y tener 10-13 dígitos total.">
                        </div>
                    </div>

                    <!-- Fila 5: Tipo de beneficiario -->
                    <div class="form-row mb-3">
                        <div class="col-md-12">
                            <label>Tipo de beneficiario</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="cor_ben" id="tipo_particular"
                                    value="1" required
                                    <?= ($predefinedValues['cor_ben_value'] === '1' || $predefinedValues['cor_ben_value'] === '') && !strpos($predefinedValues['cor_ben_value'], '2') ? 'checked' : '' ?>
                                    onclick="toggleEmployeeType(false)">
                                <label class="form-check-label" for="tipo_particular">Particular</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="cor_ben" id="tipo_empleado"
                                    value="2" required
                                    <?= strpos($predefinedValues['cor_ben_value'], '2') === 0 ? 'checked' : '' ?>
                                    onclick="toggleEmployeeType(true)">
                                <label class="form-check-label" for="tipo_empleado">Empleado público</label>
                            </div>

                            <div id="employeeTypeContainer" class="mt-2">
                                <label for="employeeType">Especificar (ej: docente, médico)</label>
                                <input type="text" class="form-control" id="employeeType"
                                    oninput="updatecor_benValue()"
                                    placeholder="Ingrese el tipo de empleado"
                                    value="<?= htmlspecialchars(str_replace('2 - ', '', $predefinedValues['cor_ben_value'])) ?>"
                                    <?= strpos($predefinedValues['cor_ben_value'], '2 - ') === 0 ? '' : 'disabled' ?>>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="text-center mt-4">
                        <button type="submit" name="crear" class="btn btn-success" id="btnCrear">
                            <i class="fas fa-plus"></i> Crear Beneficiario
                        </button>
                        <button type="submit" name="actualizar" class="btn btn-primary" id="btnActualizar" style="display:none;">
                            <i class="fas fa-save"></i> Actualizar Beneficiario
                        </button>
                        <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <script>
    // ===== FUNCIÓN: Actualizar nacionalidad según cédula =====
    function actualizarNacionalidadPorCedula(cedValue, nacSelect) {
        const cedInt = parseInt(String(cedValue).replace(/\D/g, ''), 10);
        const optionV = nacSelect?.querySelector('option[value="V"]');
        const optionE = nacSelect?.querySelector('option[value="E"]');
        
        if (!isNaN(cedInt) && cedInt > 0) {
            if (cedInt >= 80000000) {
                // Cédula >= 80M → Extranjero (E) obligatorio
                nacSelect.value = 'E';
                if (optionV) optionV.disabled = true;
                if (optionE) optionE.disabled = false;
                nacSelect.dataset.restricted = 'e';
            } else {
                // Cédula < 80M → Venezolano (V) obligatorio
                nacSelect.value = 'V';
                if (optionV) optionV.disabled = false;
                if (optionE) optionE.disabled = true;
                nacSelect.dataset.restricted = 'v';
            }
        } else {
            // Sin cédula válida → habilitar ambas opciones
            if (optionV) optionV.disabled = false;
            if (optionE) optionE.disabled = false;
            nacSelect.dataset.restricted = '';
        }
    }

    // ===== FUNCIÓN: Manejar tipo de empleado =====
    function toggleEmployeeType(isEmployee) {
        const container = document.getElementById('employeeTypeContainer');
        const input = document.getElementById('employeeType');
        if (isEmployee) {
            container.style.display = 'block';
            input.disabled = false;
            input.focus();
        } else {
            container.style.display = 'none';
            input.disabled = true;
            input.value = '';
        }
        updatecor_benValue();
    }

    // ===== FUNCIÓN: Actualizar valor oculto de cor_ben =====
    function updatecor_benValue() {
        const isEmployee = document.getElementById('tipo_empleado')?.checked;
        const employeeInput = document.getElementById('employeeType');
        const hiddenField = document.getElementById('cor_ben_value');
        
        if (!hiddenField) return;
        
        if (isEmployee && employeeInput?.value.trim()) {
            hiddenField.value = '2 - ' + employeeInput.value.trim().toUpperCase();
        } else if (isEmployee) {
            hiddenField.value = '2';
        } else {
            hiddenField.value = '1';
        }
    }

    // ===== FUNCIÓN: Cargar sectores (Promise) =====
    function loadSectores(cod_par) {
        return new Promise((resolve, reject) => {
            const sec_benSelect = document.getElementById('sec_ben');
            if (!sec_benSelect) { resolve(); return; }
            
            if (!cod_par) {
                sec_benSelect.innerHTML = '<option value="">-- Seleccione --</option>';
                sec_benSelect.disabled = true;
                resolve();
                return;
            }

            sec_benSelect.disabled = true;
            sec_benSelect.innerHTML = '<option value="">Cargando...</option>';

            fetch(`?action=getsec_benes&cod_par=${encodeURIComponent(cod_par)}`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">-- Seleccione --</option>';
                    data.forEach(item => {
                        html += `<option value="${item.ids_sec}">${item.nom_sec}</option>`;
                    });
                    sec_benSelect.innerHTML = html;
                    sec_benSelect.disabled = false;
                    resolve(data);
                })
                .catch(err => {
                    sec_benSelect.innerHTML = '<option value="">Error al cargar</option>';
                    sec_benSelect.disabled = true;
                    reject(err);
                });
        });
    }

    // ===== FUNCIÓN: Editar beneficiario =====
    function editarBeneficiario(ids_bene, nac_ben, ced_ben, nom_ben, ape_ben, dir_ben, cod_par, cor_ben, tlf_ben, sec_ben) {
        // Rellenar campos básicos
        const idsInput = document.querySelector('input[name="ids_bene"]');
        if (idsInput) idsInput.value = ids_bene;
        
        const nacSelect = document.querySelector('select[name="nac_ben"]');
        if (nacSelect) nacSelect.value = nac_ben;
        
        const cedInput = document.getElementById('ced_ben');
        if (cedInput) {
            cedInput.value = ced_ben;
            cedInput.readOnly = true;
            cedInput.style.backgroundColor = '#e9ecef';
        }
        
        const avisoCedula = document.getElementById('aviso_cedula');
        if (avisoCedula) avisoCedula.style.display = 'inline';
        
        const cedOriginal = document.getElementById('ced_ben_original');
        if (cedOriginal) cedOriginal.value = ced_ben;
        
        const nomInput = document.querySelector('input[name="nom_ben"]');
        if (nomInput) nomInput.value = nom_ben;
        
        const apeInput = document.querySelector('input[name="ape_ben"]');
        if (apeInput) apeInput.value = ape_ben;
        
        const dirInput = document.querySelector('input[name="dir_ben"]');
        if (dirInput) {
            dirInput.value = dir_ben;
            if (typeof actualizarContadorDir === 'function') actualizarContadorDir(dirInput);
        }
        
        const tlfInput = document.querySelector('input[name="tlf_ben"]');
        if (tlfInput) tlfInput.value = tlf_ben;
        
        // Parroquia y sectores
        const parroquiaSelect = document.querySelector('select[name="parroquia"]');
        if (parroquiaSelect) {
            parroquiaSelect.value = cod_par;
            loadSectores(cod_par).then(() => {
                const secSelect = document.querySelector('select[name="sec_ben"]');
                if (secSelect && secSelect.querySelector(`option[value="${sec_ben}"]`)) {
                    secSelect.value = sec_ben;
                }
            }).catch(() => console.error('Error cargando sectores'));
        }
        
        // Tipo de beneficiario (cor_ben)
        const tipoContainer = document.getElementById('employeeTypeContainer');
        const employeeInput = document.getElementById('employeeType');
        const hiddenField = document.getElementById('cor_ben_value');
        
        if (hiddenField) hiddenField.value = cor_ben;
        
        if (cor_ben && cor_ben.toString().startsWith('2')) {
            const radioEmpleado = document.getElementById('tipo_empleado');
            if (radioEmpleado) radioEmpleado.checked = true;
            const spec = cor_ben.toString().replace(/^2\s*-\s*/, '').trim();
            if (employeeInput) {
                employeeInput.value = spec;
                employeeInput.disabled = false;
            }
            if (tipoContainer) tipoContainer.style.display = 'block';
        } else {
            const radioParticular = document.getElementById('tipo_particular');
            if (radioParticular) radioParticular.checked = true;
            if (employeeInput) {
                employeeInput.value = '';
                employeeInput.disabled = true;
            }
            if (tipoContainer) tipoContainer.style.display = 'none';
        }
        
        updatecor_benValue();
        
        // Aplicar lógica de nacionalidad según cédula en edición
        if (nacSelect && ced_ben) {
            actualizarNacionalidadPorCedula(ced_ben, nacSelect);
        }
        
        // Cambiar botones
        const btnCrear = document.getElementById('btnCrear');
        const btnActualizar = document.getElementById('btnActualizar');
        if (btnCrear) btnCrear.style.display = 'none';
        if (btnActualizar) btnActualizar.style.display = 'inline-block';
        
        // Scroll suave hacia el formulario
        const formWrapper = document.querySelector('.wrapper:nth-of-type(2)');
        if (formWrapper) formWrapper.scrollIntoView({ behavior: 'smooth' });
    }

    // ===== FUNCIÓN: Resetear formulario =====
    function resetForm() {
        const idsInput = document.querySelector('input[name="ids_bene"]');
        if (idsInput) idsInput.value = '';
        
        const cedInput = document.getElementById('ced_ben');
        if (cedInput) {
            cedInput.readOnly = false;
            cedInput.style.backgroundColor = '';
            cedInput.value = '';
        }
        
        const avisoCedula = document.getElementById('aviso_cedula');
        if (avisoCedula) avisoCedula.style.display = 'none';
        
        const cedOriginal = document.getElementById('ced_ben_original');
        if (cedOriginal) cedOriginal.value = '';
        
        const btnCrear = document.getElementById('btnCrear');
        const btnActualizar = document.getElementById('btnActualizar');
        if (btnCrear) btnCrear.style.display = 'inline-block';
        if (btnActualizar) btnActualizar.style.display = 'none';
        
        const employeeContainer = document.getElementById('employeeTypeContainer');
        const employeeInput = document.getElementById('employeeType');
        if (employeeContainer) employeeContainer.style.display = 'none';
        if (employeeInput) {
            employeeInput.disabled = true;
            employeeInput.value = '';
        }
        
        const radioParticular = document.getElementById('tipo_particular');
        if (radioParticular) radioParticular.checked = true;
        
        const hiddenField = document.getElementById('cor_ben_value');
        if (hiddenField) hiddenField.value = '1';
        
        // Restaurar estado de nacionalidad
        const nacSelect = document.getElementById('nac_ben');
        if (nacSelect) {
            const optionV = nacSelect.querySelector('option[value="V"]');
            const optionE = nacSelect.querySelector('option[value="E"]');
            if (optionV) optionV.disabled = false;
            if (optionE) optionE.disabled = false;
            nacSelect.value = 'V';
            nacSelect.dataset.restricted = '';
        }
        
        updatecor_benValue();
        
        // Resetear contador de dirección
        const dirInput = document.getElementById('dir_ben');
        if (dirInput && typeof actualizarContadorDir === 'function') {
            actualizarContadorDir(dirInput);
        }
    }

    // ===== INICIALIZACIÓN AL CARGAR DOM =====
    document.addEventListener('DOMContentLoaded', function() {
        // === CÉDULA + NACIONALIDAD AUTOMÁTICA ===
        const cedInput = document.getElementById('ced_ben');
        const nacSelect = document.getElementById('nac_ben');
        
        if (cedInput && nacSelect) {
            // Ejecutar al cargar para valores prellenados
            actualizarNacionalidadPorCedula(cedInput.value, nacSelect);
            
            cedInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 1 && value.startsWith('0')) {
                    value = value.replace(/^0+/, '');
                }
                if (value === '0') {
                    value = '';
                    this.setCustomValidity('La cédula no puede ser 0');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
                this.value = value;
                actualizarNacionalidadPorCedula(value, nacSelect);
            });
            
            cedInput.addEventListener('blur', function() {
                if (this.value === '' || this.value === '0') {
                    this.setCustomValidity('La cédula es requerida y no puede ser 0');
                    this.classList.add('is-invalid');
                } else if (this.value.startsWith('0')) {
                    this.setCustomValidity('La cédula no puede comenzar con 0');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
                actualizarNacionalidadPorCedula(this.value, nacSelect);
            });
            
            cedInput.addEventListener('focus', function() {
                if (this.value === '0') this.value = '';
            });
        }

        // === NOMBRES Y APELLIDOS: Solo letras + mayúsculas ===
        const letterRegex = /[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g;
        ['nom_ben', 'ape_ben'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', function() {
                    let clean = this.value.replace(letterRegex, '');
                    this.value = clean.toUpperCase();
                });
            }
        });

        // === TELÉFONO: Solo números + validación de prefijo ===
        const tlfInput = document.getElementById('tlf_ben');
        if (tlfInput) {
            tlfInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
                const validPrefix = /^(041|042|0276)/;
                if (this.value.length > 0 && this.value <= 4) {
                    if (!validPrefix.test(this.value)) {
                        this.setCustomValidity('El teléfono debe comenzar con 041, 042 o 0276');
                        this.classList.add('is-invalid');
                    } else {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                    }
                } else if (this.value.length > 4) {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
        }

        // === CARGA DINÁMICA DE SECTORES ===
        const parroquiaSelect = document.getElementById('parroquia');
        const sec_benSelect = document.getElementById('sec_ben');

        if (parroquiaSelect) {
            parroquiaSelect.addEventListener('change', function() {
                loadSectores(this.value);
            });
        }

        // Cargar sectores si hay parroquia seleccionada tras POST/edición
        const selectedParroquia = <?= json_encode($selectedParroquia) ?>;
        const selectedSec = <?= json_encode($selectedsec_ben) ?>;
        
        if (selectedParroquia && sec_benSelect) {
            loadSectores(selectedParroquia).then(() => {
                if (selectedSec && sec_benSelect.querySelector(`option[value="${selectedSec}"]`)) {
                    sec_benSelect.value = selectedSec;
                }
            });
        }
        
        // Inicializar estado del tipo de beneficiario
        const corBenValue = <?= json_encode($predefinedValues['cor_ben_value']) ?>;
        if (corBenValue && corBenValue.toString().startsWith('2')) {
            const radioEmpleado = document.getElementById('tipo_empleado');
            if (radioEmpleado) radioEmpleado.checked = true;
            const employeeContainer = document.getElementById('employeeTypeContainer');
            const employeeInput = document.getElementById('employeeType');
            if (employeeContainer) employeeContainer.style.display = 'block';
            if (employeeInput) employeeInput.disabled = false;
        }
        updatecor_benValue();

        // === VALIDACIÓN BOOTSTRAP ===
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // === MANEJO DE TEMA CLARO/OSCURO ===
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.className = savedTheme + '-theme';
        updateToggleButton(savedTheme);

        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                const isDark = document.body.classList.contains('dark-theme');
                const newTheme = isDark ? 'light' : 'dark';
                document.body.className = newTheme + '-theme';
                localStorage.setItem('theme', newTheme);
                updateToggleButton(newTheme);
            });
        }

        function updateToggleButton(theme) {
            const btn = document.getElementById('themeToggle');
            if (!btn) return;
            if (theme === 'dark') {
                btn.innerHTML = '<i class="fas fa-sun"></i> Modo Claro';
            } else {
                btn.innerHTML = '<i class="fas fa-moon"></i> Modo Oscuro';
            }
        }
    });

    // === INICIALIZAR DATATABLE ===
    $(document).ready(function() {
        if ($('#beneficiariosTable').length) {
            $('#beneficiariosTable').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                language: {
                    url: '',
                    sProcessing: "Procesando...",
                    sLengthMenu: "Mostrar _MENU_ registros",
                    sZeroRecords: "No se encontraron resultados",
                    sEmptyTable: "Ningún dato disponible en esta tabla",
                    sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                    sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0",
                    sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
                    sInfoPostFix: "",
                    sSearch: "Buscar:",
                    sUrl: "",
                    sInfoThousands: ",",
                    sLoadingRecords: "Cargando...",
                    oPaginate: {
                        sFirst: "Primero",
                        sLast: "Último",
                        sNext: "Siguiente",
                        sPrevious: "Anterior"
                    },
                    oAria: {
                        sSortAscending: ": Activar para ordenar la columna de manera ascendente",
                        sSortDescending: ": Activar para ordenar la columna de manera descendente"
                    },
                    select: {
                        rows: {
                            _: "%d filas seleccionadas",
                            0: "Haga clic en una fila para seleccionarla",
                            1: "1 fila seleccionada"
                        }
                    }
                },
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                data: null,
                columns: null
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. CÉDULA: Solo números, no puede comenzar con 0 ni ser solo 0
        const cedInput = document.getElementById('ced_ben');
        if (cedInput) {
            cedInput.addEventListener('input', function() {
                // Eliminar caracteres no numéricos
                let value = this.value.replace(/\D/g, '');
                
                // Si comienza con 0 y tiene más de 1 dígito, quitar el 0 inicial
                if (value.length > 1 && value.startsWith('0')) {
                    value = value.replace(/^0+/, '');
                }
                
                // Si el valor es exactamente "0", limpiar el campo
                if (value === '0') {
                    value = '';
                    this.setCustomValidity('La cédula no puede ser 0');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
                
                this.value = value;
            });
            
            // Validación al perder el foco (blur)
            cedInput.addEventListener('blur', function() {
                if (this.value === '' || this.value === '0') {
                    this.setCustomValidity('La cédula es requerida y no puede ser 0');
                    this.classList.add('is-invalid');
                } else if (this.value.startsWith('0')) {
                    this.setCustomValidity('La cédula no puede comenzar con 0');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
            
            // Limpiar validación al escribir
            cedInput.addEventListener('focus', function() {
                if (this.value === '0') {
                    this.value = '';
                }
            });
        }

        // 2. NOMBRES Y APELLIDOS: Solo letras + espacios → Mayúsculas
        const letterRegex = /[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g;
        ['nom_ben', 'ape_ben'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', function() {
                    let clean = this.value.replace(letterRegex, '');
                    this.value = clean.toUpperCase();
                });
            }
        });

        // 3. TELÉFONO: Solo números + validación visual de prefijo
        const tlfInput = document.getElementById('tlf_ben');
        if (tlfInput) {
            tlfInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');

                const validPrefix = /^(041|042|0276)/;
                if (this.value.length > 0 && this.value.length <= 4) {
                    if (!validPrefix.test(this.value)) {
                        this.setCustomValidity('El teléfono debe comenzar con 041, 042 o 0276');
                        this.classList.add('is-invalid');
                    } else {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                    }
                } else if (this.value.length > 4) {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
        }
    });
</script>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap4.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Función para manejar el tipo de empleado
        function toggleEmployeeType(isEmployee) {
            const container = document.getElementById('employeeTypeContainer');
            const input = document.getElementById('employeeType');
            if (isEmployee) {
                container.style.display = 'block';
                input.disabled = false;
                input.focus();
            } else {
                container.style.display = 'none';
                input.disabled = true;
                input.value = '';
            }
            updatecor_benValue();
        }

        // Función para actualizar el valor oculto de cor_ben
        function updatecor_benValue() {
            const isEmployee = document.getElementById('tipo_empleado').checked;
            const employeeInput = document.getElementById('employeeType');
            const hiddenField = document.getElementById('cor_ben_value');
            
            if (isEmployee && employeeInput.value.trim()) {
                hiddenField.value = '2 - ' + employeeInput.value.trim().toUpperCase();
            } else if (isEmployee) {
                hiddenField.value = '2';
            } else {
                hiddenField.value = '1';
            }
        }

        // Función para cargar sectores (retorna Promise)
        function loadSectores(cod_par) {
            return new Promise((resolve, reject) => {
                const sec_benSelect = document.getElementById('sec_ben');
                
                if (!cod_par) {
                    sec_benSelect.innerHTML = '<option value="">-- Seleccione --</option>';
                    sec_benSelect.disabled = true;
                    resolve();
                    return;
                }

                sec_benSelect.disabled = true;
                sec_benSelect.innerHTML = '<option value="">Cargando...</option>';

                fetch(`?action=getsec_benes&cod_par=${encodeURIComponent(cod_par)}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">-- Seleccione --</option>';
                        data.forEach(item => {
                            html += `<option value="${item.ids_sec}">${item.nom_sec}</option>`;
                        });
                        sec_benSelect.innerHTML = html;
                        sec_benSelect.disabled = false;
                        resolve(data);
                    })
                    .catch(err => {
                        sec_benSelect.innerHTML = '<option value="">Error al cargar</option>';
                        sec_benSelect.disabled = true;
                        reject(err);
                    });
            });
        }

        // Función para editar beneficiario
        function editarBeneficiario(ids_bene, nac_ben, ced_ben, nom_ben, ape_ben, dir_ben, cod_par, cor_ben, tlf_ben, sec_ben) {
            // Rellenar campos básicos
            document.querySelector('input[name="ids_bene"]').value = ids_bene;
            document.querySelector('select[name="nac_ben"]').value = nac_ben;
            document.querySelector('input[name="ced_ben"]').value = ced_ben;
            document.getElementById('ced_ben').readOnly = true;
            document.getElementById('ced_ben').style.backgroundColor = '#e9ecef';
            document.getElementById('aviso_cedula').style.display = 'inline';
            document.getElementById('ced_ben_original').value = ced_ben;
            document.querySelector('input[name="nom_ben"]').value = nom_ben;
            document.querySelector('input[name="ape_ben"]').value = ape_ben;
            document.querySelector('input[name="dir_ben"]').value = dir_ben;
            document.querySelector('input[name="tlf_ben"]').value = tlf_ben;
            
            // Manejo de parroquia
            const parroquiaSelect = document.querySelector('select[name="parroquia"]');
            parroquiaSelect.value = cod_par;
            
            // Cargar sectores de la parroquia seleccionada y luego seleccionar el sector
            loadSectores(cod_par).then(() => {
                const secSelect = document.querySelector('select[name="sec_ben"]');
                if (secSelect && secSelect.querySelector(`option[value="${sec_ben}"]`)) {
                    secSelect.value = sec_ben;
                }
            }).catch(() => {
                console.error('Error cargando sectores');
            });
            
            // Manejo de tipo de beneficiario (cor_ben)
            const tipoContainer = document.getElementById('employeeTypeContainer');
            const employeeInput = document.getElementById('employeeType');
            const hiddenField = document.getElementById('cor_ben_value');
            
            // Guardar el valor original para usarlo al actualizar
            hiddenField.value = cor_ben;
            
            if (cor_ben && cor_ben.toString().startsWith('2')) {
                document.getElementById('tipo_empleado').checked = true;
                const spec = cor_ben.toString().replace(/^2\s*-\s*/, '').trim();
                employeeInput.value = spec;
                tipoContainer.style.display = 'block';
                employeeInput.disabled = false;
            } else {
                document.getElementById('tipo_particular').checked = true;
                employeeInput.value = '';
                tipoContainer.style.display = 'none';
                employeeInput.disabled = true;
            }
            
            // Actualizar valor oculto
            updatecor_benValue();
            
            // Cambiar botones
            document.getElementById('btnCrear').style.display = 'none';
            document.getElementById('btnActualizar').style.display = 'inline-block';
            
            // Scroll suave hacia el formulario
            document.querySelector('.wrapper:nth-of-type(2)').scrollIntoView({ behavior: 'smooth' });
        }

        // Función para resetear el formulario
        function resetForm() {
            document.querySelector('input[name="ids_bene"]').value = '';
            document.getElementById('ced_ben').readOnly = false;
            document.getElementById('ced_ben').style.backgroundColor = '';
            document.getElementById('aviso_cedula').style.display = 'none';
            document.getElementById('ced_ben_original').value = '';
            document.getElementById('btnCrear').style.display = 'inline-block';
            document.getElementById('btnActualizar').style.display = 'none';
            document.getElementById('employeeTypeContainer').style.display = 'none';
            document.getElementById('employeeType').disabled = true;
            document.getElementById('employeeType').value = '';
            document.getElementById('tipo_particular').checked = true;
            document.getElementById('cor_ben_value').value = '1';
            updatecor_benValue();
        }

        // Inicializar DataTable
        $(document).ready(function() {
    if ($('#beneficiariosTable').length) {
        $('#beneficiariosTable').DataTable({
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            // ✅ Idioma en línea (evita errores de carga de archivo externo)
            language: {
                url: '', // ← Dejar vacío para no intentar cargar archivo externo
                sProcessing: "Procesando...",
                sLengthMenu: "Mostrar _MENU_ registros",
                sZeroRecords: "No se encontraron resultados",
                sEmptyTable: "Ningún dato disponible en esta tabla",
                sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0",
                sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
                sInfoPostFix: "",
                sSearch: "Buscar:",
                sUrl: "",
                sInfoThousands: ",",
                sLoadingRecords: "Cargando...",
                oPaginate: {
                    sFirst: "Primero",
                    sLast: "Último",
                    sNext: "Siguiente",
                    sPrevious: "Anterior"
                },
                oAria: {
                    sSortAscending: ": Activar para ordenar la columna de manera ascendente",
                    sSortDescending: ": Activar para ordenar la columna de manera descendente"
                },
                select: {
                    rows: {
                        _: "%d filas seleccionadas",
                        0: "Haga clic en una fila para seleccionarla",
                        1: "1 fila seleccionada"
                    }
                }
            },
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
            data: null,
            columns: null
        });
    }
});


        // Carga dinámica de sectores al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const parroquiaSelect = document.getElementById('parroquia');
            const sec_benSelect = document.getElementById('sec_ben');

            parroquiaSelect.addEventListener('change', function() {
                loadSectores(this.value);
            });

            // Si hay parroquia seleccionada tras POST, cargar sectores
            const selectedParroquia = <?= json_encode($selectedParroquia) ?>;
            const selectedSec = <?= json_encode($selectedsec_ben) ?>;
            
            if (selectedParroquia) {
                loadSectores(selectedParroquia).then(() => {
                    if (selectedSec && sec_benSelect.querySelector(`option[value="${selectedSec}"]`)) {
                        sec_benSelect.value = selectedSec;
                    }
                });
            }
            
            // Inicializar estado del tipo de beneficiario
            const corBenValue = <?= json_encode($predefinedValues['cor_ben_value']) ?>;
            if (corBenValue && corBenValue.toString().startsWith('2')) {
                document.getElementById('tipo_empleado').checked = true;
                document.getElementById('employeeTypeContainer').style.display = 'block';
                document.getElementById('employeeType').disabled = false;
            }
            updatecor_benValue();
        });

        // Validación Bootstrap
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Manejo del tema claro/oscuro
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.className = savedTheme + '-theme';
        updateToggleButton(savedTheme);

        document.getElementById('themeToggle').addEventListener('click', function() {
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

    <?php $conn->close(); ?>
</body>
</html>