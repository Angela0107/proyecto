<?php
session_start();
if (!isset($_SESSION['id_usu'])) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['usu_usu_logueado'] !== "SI") {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['active'] !== true) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['id_rol'] !== '2') {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['estatus'] !== '1') {
    header("Location: ../login.php");
    exit();
}

$ced_ben_usu = $_SESSION['ced_ben'] ?? '';
$contr_usu = $_SESSION['contr_usu'] ?? '';
$id_usu = $_SESSION['id_usu'];
$nom_usu = $_SESSION['nom_usu'] ?? '';

require_once '../db.php';
$conn->query("SET SESSION sql_mode=''");

// ================= ENDPOINT AJAX PARA SECTORES =================
if (isset($_GET['action']) && $_GET['action'] === 'getsec_benes' && isset($_GET['cod_par'])) {
    // ob_clean() asegura que no haya basura HTML rompiendo el JSON
    ob_clean();
    header('Content-Type: application/json');
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
// ===============================================================

$mensaje = '';
$beneficiario = null;
$ced_ben = '';
$tiposolicitud = [];
$cod_pars = []; 

$sql = "SELECT cod_tip, des_tpo FROM tiposolicitud";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tiposolicitud[] = $row;
    }
}

$sql = "SELECT cod_par, nom_par FROM parroquias";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cod_pars[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['buscar'])) {
        $ced_ben = trim($_POST['ced_ben']);
        if ($ced_ben) {
            // CORRECCIÓN: Se extrae b.sec_ben AS id_sector para pre-seleccionar el dropdown correctamente
            $sql = "SELECT b.nom_ben, b.nac_ben, b.ced_ben, b.ape_ben, b.dir_ben, b.cor_ben, b.tlf_ben, p.nom_par, p.cod_par, s.nom_sec, b.sec_ben AS id_sector 
                    FROM beneficiario b
                    LEFT JOIN parroquias p ON p.cod_par = b.cod_par
                    LEFT JOIN sector s ON b.sec_ben = s.ids_sec 
                    WHERE ced_ben = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $ced_ben);
            $stmt->execute();
            $result = $stmt->get_result();
            $beneficiario = $result->num_rows > 0 ? $result->fetch_assoc() : null;
            if (!$beneficiario) {
                $mensaje = "Cédula no encontrada. Por favor, ingrese la información.";
            }
        }
    } elseif (isset($_POST['agregar'])) {
        $ced_ben = $_POST['ced_ben2'] ?? '';
        $ced_ben_requi = $_POST['ced_ben_requi'] ?? '2';
        $nac_ben = $_POST['nac_ben'] ?? '';
        $nom_ben = $_POST['nom_ben'] ?? $_POST['nombre'] ?? ''; 
        $ape_ben = $_POST['ape_ben'] ?? ''; 
        $dir_ben = $_POST['dir_ben'] ?? '';
        $cod_par = $_POST['cod_par'] ?? 6; 
        $cor_ben = $_POST['cor_ben'] ?? '';
        $tlf_ben = $_POST['tlf_ben'] ?? '';
        $sec_ben = $_POST['sec_ben'] ?? '';
        $tipo_ayuda = $_POST['tipo_ayuda'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $fecha_solicitud = $_POST['fecha_solicitud'] ?? date('Y-m-d');
        $rif = $_POST['rif'] ?? '2';
        $carta_alc = $_POST['carta_alc'] ?? '2';
        $cop_nac = $_POST['cop_nac'] ?? '2';
        $dat_ban = $_POST['dat_ban'] ?? '2';
        $inf_medi = $_POST['inf_medi'] ?? '2';
        $recipe_med = $_POST['recipe_med'] ?? '2';
        $act_defun = $_POST['act_defun'] ?? '2';
        $fac_ori = $_POST['fac_ori'] ?? '2';
        $presu_ban = $_POST['presu_ban'] ?? '2';
        $otros = '0';

        $tipo_ayuda_descrip = '';
        foreach ($tiposolicitud as $tipo) {
            if ($tipo['cod_tip'] == $tipo_ayuda) {
                $tipo_ayuda_descrip = $tipo['des_tpo'];
                break;
            }
        }
        $descripcion_completa = $tipo_ayuda_descrip . ' - ' . $descripcion;

        $sql_numsol = "SELECT numsol FROM numsoli ORDER BY numsol DESC LIMIT 1";
        $result_numsol = $conn->query($sql_numsol);
        $numsol = ($result_numsol && $result_numsol->num_rows > 0) 
            ? $result_numsol->fetch_assoc()['numsol'] + 1 
            : 1;

        $estado1 = 1;
        $estatus = 0;

        $sql = "SELECT ids_bene FROM beneficiario WHERE ced_ben = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ced_ben);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $beneficiario_id = $result->fetch_assoc()['ids_bene'];
            // CORRECCIÓN: Actualizar beneficiario existente por si el operador corrigió la parroquia/sector
            $sql_upd = "UPDATE beneficiario SET nom_ben=?, ape_ben=?, dir_ben=?, tlf_ben=?, cod_par=?, sec_ben=? WHERE ids_bene=?";
            $stmt_upd = $conn->prepare($sql_upd);
            $stmt_upd->bind_param("ssssssi", $nom_ben, $ape_ben, $dir_ben, $tlf_ben, $cod_par, $sec_ben, $beneficiario_id);
            $stmt_upd->execute();
        } else {
            $sql_ins = "INSERT INTO beneficiario (nac_ben, ced_ben, nom_ben, ape_ben, dir_ben, cod_par, cor_ben, tlf_ben, sec_ben) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_ins = $conn->prepare($sql_ins);
            $stmt_ins->bind_param("sssssssss", $nac_ben, $ced_ben, $nom_ben, $ape_ben, $dir_ben, $cod_par, $cor_ben, $tlf_ben, $sec_ben);
            if (!$stmt_ins->execute()) {
                $mensaje = "Error al insertar beneficiario: " . $stmt_ins->error;
                goto end;
            }
            $beneficiario_id = $stmt_ins->insert_id;
        }

        $sql_solicitud = "INSERT INTO solicitud (numsol, idbenefi, nac_ben, ced_ben, nom_ben, dir_ben, tlf_ben, descripcion, codsolicitud, cod_par, fechasol, estatus, cedusu) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_solicitud = $conn->prepare($sql_solicitud);
        $stmt_solicitud->bind_param("isssssssissis", 
            $numsol, $beneficiario_id, $nac_ben, $ced_ben, $nom_ben, $dir_ben, $tlf_ben, 
            $descripcion, $tipo_ayuda, $cod_par, $fecha_solicitud, $estatus, $id_usu);

        if (!$stmt_solicitud->execute()) {
            $mensaje = "Error al insertar solicitud: " . $stmt_solicitud->error;
            goto end;
        }

        $sql_planilla = "INSERT INTO planilla (id_planilla, fecha_planilla, ids_bene, nac_ben, nom_ben, tlf_ben, dir_ben, cedula_sol, id_requiayuda, rif, carta_alc, cedula, cop_nac, dat_ban, inf_medi, recipe_med, act_defun, fac_ori, presu_ban, otros, estado) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_planilla = $conn->prepare($sql_planilla);
        $stmt_planilla->bind_param("isssssssssssssssssssi",
            $numsol, $fecha_solicitud, $beneficiario_id, $nac_ben, $nom_ben, $tlf_ben, $dir_ben, 
            $ced_ben, $descripcion_completa, $rif, $carta_alc, $ced_ben_requi, $cop_nac, $dat_ban, 
            $inf_medi, $recipe_med, $act_defun, $fac_ori, $presu_ban, $otros, $estado1);

        if (!$stmt_planilla->execute()) {
            $mensaje = "Error al insertar planilla: " . $stmt_planilla->error;
            goto end;
        }

        $conn->query("UPDATE numsoli SET numsol = numsol + 1");

        header("Location: planilla.php?nun_ayuda=" . urlencode($numsol) . "&ced_ben=" . urlencode($beneficiario_id));
        exit();

        end:
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Ayuda</title>
    <style>
        .container1 {
            max-width: 2000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 109px;
            width: 1120px;
            margin-left: 17%;
            height: auto;
        }
        .container2 {
            height: auto;
            width: 1000px;
        }
        label[for="tipo_ayuda"] {
            font-weight: bold;
            font-size: 18px;
            margin-top: 0px;
            margin-left: 0px;
        }
        input[type="text"],
        input[type="date"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="radio"] {
            margin-left: 10px;
            margin-right: 5px;
        }
        .text-small {
            font-size: 0.8em;
        }
        select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            margin-left: 200px;
        }
        .checkbox {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            margin-left: 0;
            margin-top: 0px;
            color: #333;
            font-size: 14px;
        }
        .form-control {
            margin-left: 0;
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .checkbox.form-control {
            width: 318.66666px;
            margin-left: 216px;
        }
        .tipo-ayuda-container {
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .tipo-ayuda-option {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .tipo-ayuda-option input[type="radio"] {
            margin-right: 10px;
            cursor: pointer;
            margin-left: 0;
        }
        .tipo-ayuda-option label {
            font-weight: bold;
            cursor: pointer;
            color: #333;
            font-size: 14px;
            margin: 0;
        }
        .tipo-ayuda-option:hover {
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .form-label, .form-label-1 {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            margin-left: 0;
            color: #333;
            font-size: 14px;
        }
        input[readonly] {
            background-color: #f9f9f9;
            cursor: not-allowed;
        }
        .datos {
            margin-left: 10px;
            width: 980.66666px;
            padding: 0;
            border: 1px solid #ccc;
        }
        .fecha-label {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
            margin-right: 10px;
        }
        .fecha-input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            flex: 1;
            margin-right: 10px;
        }
        .input-group {
            display: flex;
            align-items: center;
            margin-top: 8px;
        }
        .btn {
            padding: 10px 15px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .buscar-btn {
            background-color: #007bff;
            color: white;
        }
        .buscar-btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: #d9534f;
            font-weight: bold;
        }

        
        body.light-theme {
        background-color: #f5f7fa;
        color: #333;
    }

    /* ===== Tema oscuro ===== */
    body.dark-theme {
        background-color: #121212;
        color: #e0e0e0;
    }

    /* Contenedores principales */
    body.dark-theme .container1,
    body.dark-theme .container2 {
        background-color: #1e1e1e !important;
        color: #e0e0e0 !important;
        border-color: #444 !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4) !important;
    }

    /* Inputs, selects, textareas */
    body.dark-theme input[type="text"],
    body.dark-theme input[type="date"],
    body.dark-theme input[type="number"],
    body.dark-theme input[type="tel"],
    body.dark-theme select,
    body.dark-theme textarea,
    body.dark-theme .form-control {
        background-color: #2d2d2d !important;
        color: #e0e0e0 !important;
        border-color: #444 !important;
    }

    /* Placeholder en modo oscuro */
    body.dark-theme input::placeholder,
    body.dark-theme textarea::placeholder {
        color: #aaa !important;
    }

    /* Labels y texto */
    body.dark-theme label,
    body.dark-theme .form-label,
    body.dark-theme .form-label-1,
    body.dark-theme .checkbox,
    body.dark-theme h2,
    body.dark-theme h3 {
        color: #e0e0e0 !important;
    }

    /* Botones */
    body.dark-theme .btn,
    body.dark-theme .buscar-btn {
        background-color: #007bff !important;
        color: white !important;
        border-color: #444 !important;
    }

    body.dark-theme .btn:hover,
    body.dark-theme .buscar-btn:hover {
        background-color: #0056b3 !important;
    }

    /* Opciones de tipo de ayuda */
    body.dark-theme .tipo-ayuda-option {
        background-color: #252525 !important;
        color: #e0e0e0 !important;
        border-radius: 4px !important;
    }

    body.dark-theme .tipo-ayuda-option:hover {
        background-color: #333 !important;
    }

    body.dark-theme .error {
        color: #ff9999 !important;
    }

    body.dark-theme input[readonly] {
        background-color: #252525 !important;
        color: #ccc !important;
    }

    #themeToggle {
        position: absolute;
        margin-top:5%;
        right: 20px;
        z-index: 1000;
    }
    </style>
</head>
<?php
include 'nav/index2.php';
?>
<body class="light-theme">
    <button id="themeToggle" class="btn btn-outline-secondary">
        <i class="fas fa-moon"></i> Modo Oscuro
    </button>
    
    <div class="container1">
        <form method="post">
            <center><h2>Buscar Beneficiario</h2></center>
            <div class="col-md-6">
                <label for="ced_ben" class="form-label">Cédula:</label>
                <div class="input-group">
                    <input type="text" id="ced_ben" name="ced_ben" class="input-numsol-anio" 
                        value="<?php echo htmlspecialchars($ced_ben); ?>" 
                        required maxlength="9" pattern="[0-9]{1,9}" inputmode="numeric"
                        title="Solo se permiten números (máx. 9 dígitos)">
                    <button type="submit" name="buscar" class="btn buscar-btn">Buscar</button>
                </div>
            </div>
        </form>
        <br>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cedInput = document.getElementById('ced_ben');
        if (cedInput) {
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
            });
            cedInput.addEventListener('focus', function() {
                if (this.value === '0') {
                    this.value = '';
                }
            });
        }

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

    
        <?php if ($mensaje): ?>
            <p class="error"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <?php if ($beneficiario): ?>
            <form method="post" id="formAgregar">
                <div class="container2">
                    <h2>Información del Beneficiario</h2>
                    <?php
                    $valor = isset($beneficiario['cor_ben']) ? trim($beneficiario['cor_ben']) : '';
                    if ($valor === '1') {
                        $condicion_texto = 'Empleado Particular';
                    } elseif ($valor === '2') {
                        $condicion_texto = 'Empleado Público';
                    } else {
                        $condicion_texto = htmlspecialchars($valor !== '' ? $valor : 'Sin especificar');
                    }?>
                    <div class="form-row">
                        <div class="col-md-6">
                            <p>Cédula: <input type="text" name="ced_ben2" class="form-control" value="<?php echo htmlspecialchars($beneficiario['ced_ben']); ?>" readonly required></p>
                        </div>
                        <div class="col-md-6">
                            <p>Nacionalidad: <input type="text" name="nac_ben" class="form-control" value="<?php echo htmlspecialchars($beneficiario['nac_ben']); ?>" readonly required></p>
                        </div>
                    </div>
                     <div class="form-row">
                        <div class="col-md-6">
                            <p>Nombre: <input type="text" name="nom_ben" class="form-control" value="<?php echo htmlspecialchars($beneficiario['nom_ben']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></p>
                        </div>
                        <div class="col-md-6">
                            <p>Apellido: <input type="text" name="ape_ben" class="form-control" value="<?php echo htmlspecialchars($beneficiario['ape_ben']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></p>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6">
                            <p>Dirección: <input type="text" name="dir_ben" class="form-control" value="<?php echo htmlspecialchars($beneficiario['dir_ben']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></p>
                        </div>
                        <div class="col-md-6">
                            <p>Teléfono: <input type="text" name="tlf_ben" class="form-control" value="<?php echo htmlspecialchars($beneficiario['tlf_ben']); ?>" required maxlength="11" pattern="(041|042|0276)[0-9]{7,10}"></p>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6">
                            <label class="form-label">Parroquia:</label>
                            <select name="cod_par" id="cod_par" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <?php 
                                $b_cod_par = $beneficiario['cod_par'] ?? '';
                                foreach ($cod_pars as $par): 
                                ?>
                                    <option value="<?php echo (int)$par['cod_par']; ?>" 
                                        <?php echo ($b_cod_par == $par['cod_par']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($par['nom_par']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sector:</label>
                            <select name="sec_ben" id="sec_ben" class="form-control" required>
                                <option value="">-- Seleccione parroquia primero --</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6">
                            <p>Condición: 
                                <input type="text" class="form-control" value="<?php echo $condicion_texto; ?>" readonly>
                                <input type="hidden" name="cor_ben" value="<?php echo htmlspecialchars($valor); ?>">
                            </p>
                        </div>
                    </div>
                </div>

                <label for="tipo_ayuda">Tipo de Ayuda:</label>
                <div class="tipo-ayuda-container">
                    <?php foreach ($tiposolicitud as $tipo): ?>
                        <div class="tipo-ayuda-option">
                            <input type="radio" name="tipo_ayuda" id="tipo_<?php echo $tipo['cod_tip']; ?>" 
                                value="<?php echo (int)$tipo['cod_tip']; ?>" required>
                            <label for="tipo_<?php echo $tipo['cod_tip']; ?>">
                                <?php echo htmlspecialchars($tipo['des_tpo']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3>Requisitos:</h3>
                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">1. Copia de cédula de identidad</label>
                        <select name="ced_ben_requi" class="form-control" required>
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">2. Copia de RIF</label>
                        <select name="rif" class="form-control" required>
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">3. Carta dirigida al Alcalde</label>
                        <select name="carta_alc" class="form-control" required>
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">4. Presupuesto con cuenta bancaria 
                            <span class="text-small">(Clínica privada)</span>
                        </label>
                        <select name="presu_ban" class="form-control" required>
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">5. Copia de la partida de nacimiento</label>
                        <select name="cop_nac" class="form-control">
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">6. Presupuesto original con datos bancarios</label>
                        <select name="dat_ban" class="form-control">
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">7. Copia de informe médico</label>
                        <select name="inf_medi" class="form-control">
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">8. Copia de receta médica con sello húmedo</label>
                        <select name="recipe_med" class="form-control">
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">9. Acta de defunción</label>
                        <select name="act_defun" class="form-control">
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">10. Factura Original</label>
                        <select name="fac_ori" class="form-control">
                            <option value="2">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
                </div>

                
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" class="form-control" rows="3" required></textarea>

                <div class="form-row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <label for="fecha_solicitud" class="fecha-label">Fecha de Solicitud:</label>
                            <input type="date" name="fecha_solicitud" class="fecha-input" value="<?php echo date('Y-m-d'); ?>" required>
                            <button type="submit" name="agregar" class="btn buscar-btn">Agregar Solicitud</button>
                        </div>
                    </div>
                </div>
            </form>

        <?php else: ?>
            <h3>Agregar Solicitud (Nuevo Beneficiario)</h3><br>
            <form method="post" id="formAgregarNuevo">
                <input type="hidden" name="ced_ben2" value="<?php echo htmlspecialchars($ced_ben); ?>">
                <div class="form-row">
                    <div class="col-md-6">
                        <label class="form-label">Cédula:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($ced_ben); ?>" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nacionalidad:</label>
                        <select name="nac_ben" class="form-control" required>
                            <option value="V" <?php echo (isset($_POST['nac_ben']) && $_POST['nac_ben'] === 'V') ? 'selected' : ''; ?>>V</option>
                            <option value="E" <?php echo (isset($_POST['nac_ben']) && $_POST['nac_ben'] === 'E') ? 'selected' : ''; ?>>E</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nom_ben" class="form-control" value="<?php echo htmlspecialchars($_POST['nom_ben'] ?? ''); ?>" required onkeyup="this.value = this.value.toUpperCase()">
</div>
  <div class="col-md-6">
                <label class="form-label">Apellidos:</label>
                <input type="text" name="ape_ben" class="form-control" value="<?php echo htmlspecialchars($_POST['ape_ben'] ?? ''); ?>" required onkeyup="this.value = this.value.toUpperCase()">
</div></div>
                <div class="form-row">
                    <div class="col-md-6">
                        <label class="form-label">Dirección:</label>
                        <input type="text" name="dir_ben" class="form-control" value="<?php echo htmlspecialchars($_POST['dir_ben'] ?? ''); ?>" required onkeyup="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono:</label>
                        <input type="text" name="tlf_ben" class="form-control" value="<?php echo htmlspecialchars($_POST['tlf_ben'] ?? ''); ?>" required maxlength="11" 
                            pattern="(041|042|0276)[0-9]{7,10}"
                            title="Ej: 04141234567">
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="form-label">Parroquia:</label>
                        <select name="cod_par" id="cod_par" class="form-control" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($cod_pars as $par): ?>
                                <option value="<?php echo (int)$par['cod_par']; ?>" 
                                    <?php echo (isset($_POST['cod_par']) && $_POST['cod_par'] == $par['cod_par']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($par['nom_par']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sector:</label>
                        <select name="sec_ben" id="sec_ben" class="form-control" required disabled>
                            <option value="">-- Seleccione parroquia primero --</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="form-label-1">
                            <input type="radio" name="cor_ben" value="1" required <?php echo (!isset($_POST['cor_ben']) || $_POST['cor_ben'] === '1') ? 'checked' : ''; ?>> PARTICULARES
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-1">
                            <input type="radio" name="cor_ben" value="2" required <?php echo (isset($_POST['cor_ben']) && $_POST['cor_ben'] === '2') ? 'checked' : ''; ?>> EMPLEADOS PÚBLICOS
                        </label>
                    </div>
                </div>

                <label for="tipo_ayuda">Tipo de Ayuda:</label>
                <div class="tipo-ayuda-container">
                    <?php foreach ($tiposolicitud as $tipo): ?>
                        <div class="tipo-ayuda-option">
                            <input type="radio" name="tipo_ayuda" id="tipo_new_<?php echo $tipo['cod_tip']; ?>" 
                                value="<?php echo (int)$tipo['cod_tip']; ?>" required 
                                <?php echo (isset($_POST['tipo_ayuda']) && $_POST['tipo_ayuda'] == $tipo['cod_tip']) ? 'checked' : ''; ?>>
                            <label for="tipo_new_<?php echo $tipo['cod_tip']; ?>">
                                <?php echo htmlspecialchars($tipo['des_tpo']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3>Requisitos:</h3>
                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">1. Copia de cédula de identidad</label>
                        <select name="ced_ben_requi" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['ced_ben_requi']) && $_POST['ced_ben_requi'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['ced_ben_requi']) && $_POST['ced_ben_requi'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">2. Copia de RIF</label>
                        <select name="rif" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['rif']) && $_POST['rif'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['rif']) && $_POST['rif'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">3. Carta dirigida al Alcalde</label>
                        <select name="carta_alc" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['carta_alc']) && $_POST['carta_alc'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['carta_alc']) && $_POST['carta_alc'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">4. Presupuesto con cuenta bancaria 
                            <span class="text-small">(Clínica privada)</span>
                        </label>
                        <select name="presu_ban" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['presu_ban']) && $_POST['presu_ban'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (issetPOST['presu_ban'] && $_POST['presu_ban'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">5. Copia de la partida de nacimiento</label>
                        <select name="cop_nac" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['cop_nac']) && $_POST['cop_nac'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['cop_nac']) && $_POST['cop_nac'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">6. Presupuesto original con datos bancarios</label>
                        <select name="dat_ban" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['dat_ban']) && $_POST['dat_ban'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['dat_ban']) && $_POST['dat_ban'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">7. Copia de informe médico</label>
                        <select name="inf_medi" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['inf_medi']) && $_POST['inf_medi'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['inf_medi']) && $_POST['inf_medi'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">8. Copia de receta médica con sello húmedo</label>
                        <select name="recipe_med" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['recipe_med']) && $_POST['recipe_med'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['recipe_med']) && $_POST['recipe_med'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <label class="checkbox">9. Acta de defunción</label>
                        <select name="act_defun" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['act_defun']) && $_POST['act_defun'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['act_defun']) && $_POST['act_defun'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="checkbox">10. Factura Original</label>
                        <select name="fac_ori" class="form-control" required>
                            <option value="2" <?php echo (isset($_POST['fac_ori']) && $_POST['fac_ori'] === '2') ? 'selected' : ''; ?>>NO</option>
                            <option value="1" <?php echo (isset($_POST['fac_ori']) && $_POST['fac_ori'] === '1') ? 'selected' : ''; ?>>SI</option>
                        </select>
                    </div>
                </div>

                <label class="checkbox">Descripción:</label>
                <textarea name="descripcion" class="form-control" rows="3" required><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>

                <div class="form-row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <label class="fecha-label">Fecha de Solicitud:</label>
                            <input type="date" name="fecha_solicitud" class="fecha-input" value="<?php echo htmlspecialchars($_POST['fecha_solicitud'] ?? date('Y-m-d')); ?>" required>
                        </div>
                    </div>
                </div>
                <br>
                <center>
                    <button type="submit" name="agregar" class="btn buscar-btn">Agregar Solicitud</button>
                </center>
            </form>
        <?php endif; ?>
    </div>
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const formulariosAgregar = document.querySelectorAll('#formAgregar, #formAgregarNuevo');

    const camposObligatoriosSi = [
        'ced_ben_requi', 
        'rif', 
        'carta_alc', 
        'presu_ban',
        'cop_nac',
        'dat_ban',
        'inf_medi',
        'recipe_med',
        'act_defun',
        'fac_ori'
    ];

    formulariosAgregar.forEach(function(formulario) {
        formulario.addEventListener('submit', function(evento) {
            let todoCorrecto = true;
            let primerCampoConError = null;
            let camposFaltantes = [];

            camposObligatoriosSi.forEach(function(nombreCampo) {
                const select = formulario.querySelector(`select[name="${nombreCampo}"]`);
                
                if (select) {
                    if (select.value !== "1") {
                        todoCorrecto = false;
                        select.style.borderColor = "red";
                        select.style.backgroundColor = "#ffe6e6";
                        
                        const label = select.previousElementSibling;
                        const textoRequisito = label ? label.textContent.trim() : nombreCampo;
                        camposFaltantes.push(textoRequisito);
                        
                        if (!primerCampoConError) {
                            primerCampoConError = select;
                        }
                    } else {
                        select.style.borderColor = "";
                        select.style.backgroundColor = "";
                    }
                }
            });

            if (!todoCorrecto) {
                evento.preventDefault();
                
                let mensajeError = "⚠️ Los siguientes requisitos son OBLIGATORIOS y deben marcarse como 'SI':\n\n";
                camposFaltantes.forEach(function(campo, index) {
                    mensajeError += (index + 1) + ". " + campo + "\n";
                });
                
                alert(mensajeError);
                
                if (primerCampoConError) {
                    primerCampoConError.focus();
                    primerCampoConError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
});
</script>
</body>

  <!-- Scripts al final -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
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

        // ================= CARGA DINÁMICA DE SECTORES =================
        document.addEventListener('DOMContentLoaded', function() {
            const parroquiaSelect = document.getElementById('cod_par');
            const sectorSelect = document.getElementById('sec_ben');
            
            function cargarSectores(codPar) {
                if (!sectorSelect) return;
                
                sectorSelect.disabled = true;
                sectorSelect.innerHTML = '<option value="">Cargando...</option>';
                
                if (!codPar) {
                    sectorSelect.innerHTML = '<option value="">-- Seleccione parroquia primero --</option>';
                    sectorSelect.disabled = true;
                    return;
                }
                
                fetch(window.location.pathname + `?action=getsec_benes&cod_par=${encodeURIComponent(codPar)}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '<option value="">-- Seleccione --</option>';
                        data.forEach(item => {
                            html += `<option value="${item.ids_sec}">${item.nom_sec}</option>`;
                        });
                        sectorSelect.innerHTML = html;
                        sectorSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error cargando sectores:', error);
                        sectorSelect.innerHTML = '<option value="">Error al cargar</option>';
                        sectorSelect.disabled = true;
                    });
            }
            
            if (parroquiaSelect) {
                parroquiaSelect.addEventListener('change', function() {
                    cargarSectores(this.value);
                });
                
                // Cargar sectores si hay parroquia preseleccionada (POST o BD)
                const parroquiaSeleccionada = <?php echo json_encode($_POST['cod_par'] ?? $beneficiario['cod_par'] ?? ''); ?>;
                if (parroquiaSeleccionada) {
                    cargarSectores(parroquiaSeleccionada);
                    const sectorSeleccionado = <?php echo json_encode($_POST['sec_ben'] ?? $beneficiario['id_sector'] ?? ''); ?>;
                    if (sectorSeleccionado && sectorSelect) {
                        setTimeout(() => {
                            if (sectorSelect.querySelector(`option[value="${sectorSeleccionado}"]`)) {
                                sectorSelect.value = sectorSeleccionado;
                            }
                        }, 300);
                    }
                }
            }
            
            const form = document.querySelector('form[method="post"]');
            if (form && sectorSelect) {
                form.addEventListener('submit', function(e) {
                    const parroquia = document.getElementById('cod_par')?.value;
                    const sector = sectorSelect?.value;
                    if (parroquia && !sector) {
                        e.preventDefault();
                        alert('Por favor, seleccione un sector.');
                        sectorSelect?.focus();
                    }
                });
            }
        });
    </script>
</body>
</html>
