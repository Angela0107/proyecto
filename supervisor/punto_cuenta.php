<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'nav/index.php';

if (!isset($_SESSION['solicitudes'])) {
    $_SESSION['solicitudes'] = [];
}

require_once "../db.php.php";

$results = [];
$solicitudes_string = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['numsol']) && isset($_POST['anio'])) {
        $numsol = trim($_POST['numsol']);
        $anio = trim($_POST['anio']);

        if (!preg_match('/^[1-9][0-9]{0,4}$/', $numsol)) {
            $error_message = 'Error técnico: El Número de Solicitud debe contener entre 1 y 5 dígitos numéricos, sin ceros a la izquierda.';
        } elseif (!preg_match('/^[0-9]{4}$/', $anio) || intval($anio) < 1561 || intval($anio) > 2056) {
            $error_message = 'Error técnico: El Año debe estar en el rango de 1561 a 2056.';
        } else {
            $sql = "SELECT s.idsolicitud, s.numsol, s.nac_ben, s.ced_ben, s.descripcion, s.nom_ben 
                    FROM solicitud s 
                    INNER JOIN beneficiario b ON b.ced_ben = s.ced_ben 
                    WHERE s.numsol = ? AND YEAR(s.fechasol) = ? AND ptocuenta IS NULL AND estatus = '0' 
                    LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $numsol, $anio);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }

            if (!empty($results)) {
                $exists = false;
                foreach ($_SESSION['solicitudes'] as $key => $solicitud) {
                    if (
                        $solicitud['numsol'] === $results[0]['numsol'] &&
                        $solicitud['nac_ben'] === $results[0]['nac_ben'] &&
                        $solicitud['ced_ben'] === $results[0]['ced_ben']
                    ) {
                        unset($_SESSION['solicitudes'][$key]);
                        $error_message = '<span class="success-msg">Solicitud eliminada de la tabla.</span>';
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $_SESSION['solicitudes'] = array_merge($_SESSION['solicitudes'], $results);
                }
            }

            $solicitudes = [];
            if (!empty($_SESSION['solicitudes'])) {
                foreach ($_SESSION['solicitudes'] as $solicitud) {
                    $solicitudes[] = $solicitud['idsolicitud'];
                }
            }
            $solicitudes = array_unique($solicitudes);
            $solicitudes_string = implode(", ", $solicitudes);
            $stmt->close();
        }
    } elseif (isset($_POST['restaurar'])) {
        $_SESSION['solicitudes'] = [];
        $error_message = '<span class="success-msg">Todas las solicitudes han sido restauradas.</span>';
    } elseif (isset($_POST['desconsultar'])) {
        $numsol = $_POST['numsol_desconsultar'];
        foreach ($_SESSION['solicitudes'] as $key => $solicitud) {
            if ($solicitud['numsol'] === $numsol) {
                unset($_SESSION['solicitudes'][$key]);
                $error_message = '<span class="success-msg">Solicitud desconsultada.</span>';
                break;
            }
        }
    } elseif (isset($_POST['monto']) && isset($_POST['fecha'])) {
        $monto = $_POST['monto'];
        $fecha = $_POST['fecha'];
        $_SESSION['monto'] = $monto;
        $_SESSION['fecha'] = $fecha;
        $error_message = '<span class="success-msg">Monto y fecha guardados correctamente.</span>';
    }
}

$conn->close();

include '../conexion2.php';

$proveedores = $conexion->query("SELECT DISTINCT ced_prv, nac_prv, id_prov, nom_prv FROM proveedor;")->fetchAll(PDO::FETCH_ASSOC);
$provcuentas = [];

if (isset($_POST['ced_prv'])) {
    $ced_prv = $_POST['ced_prv'];
    $stmt = $conexion->prepare("SELECT p.*, b.nom_ban 
        FROM provcuenta p 
        JOIN bancos b ON p.cod_ban = b.cod_ban 
        WHERE p.ced_prv = :ced_prv");
    $stmt->bindParam(':ced_prv', $ced_prv);
    $stmt->execute();
    $provcuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Solicitudes</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        h1, h2 { color: #333; font-size: large; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"], input[type="number"], select {
            width: 476px; margin-right: 10px; padding: 10px; margin-bottom: 10px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        input[type="number"], input[type="text"], .input-numsol-anio {
            width: 172.66666px; margin-right: 10px; padding: 10px; margin-bottom: 10px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button {
            background-color: #007bff; color: white; border: none; padding: 10px 15px;
            border-radius: 4px; cursor: pointer; margin-top: 23px;
        }
        button:hover { background-color: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f1f1f1; }
        p { color: #555; }
        .resultados { margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 5px; }
        .form-row {
            display: flex; justify-content: space-between; margin-bottom: 15px;
            width: 596px; margin-left: 20px; margin-top: 10px;
        }
        .cuenta-propia { display: none; }
        .boton2 { height: 40px; width: 256px; margin-right: 10px; }
        .boton3 { height: 40px; }
        .btn1 {
            display: inline-block; padding: 10px 20px; background-color: #007bff;
            color: white; text-decoration: none; border-radius: 4px;
            transition: background-color 0.3s ease; margin-right: 10px; margin-bottom: 15px;
        }
        .btn1:hover { background-color: #0056b3; }

        body.light-theme { background-color: #f5f7fa; color: #333; }
        body.dark-theme { background-color: #121212; color: #e0e0e0; }
        .container {
            max-width: 1200px; margin: 0 auto; padding: 20px; margin-left: 17%;
            margin-top: 112px; width: 80%; border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        body.light-theme .container { background-color: #ffffff; }
        body.dark-theme .container {
            background-color: #1e1e1e !important; color: #e0e0e0 !important; border: 1px solid #333;
        }
        body.dark-theme h1, body.dark-theme h2 { color: #e0e0e0 !important; }
        body.dark-theme label { color: #e0e0e0 !important; }
        body.dark-theme input[type="text"], body.dark-theme input[type="number"],
        body.dark-theme input[type="date"], body.dark-theme select,
        body.dark-theme .input-numsol-anio {
            background-color: #2d2d2d !important; color: #e0e0e0 !important; border-color: #444 !important;
        }
        body.dark-theme button, body.dark-theme .btn1 {
            background-color: #0d6efd !important; color: white !important; border-color: #444 !important;
        }
        body.dark-theme button:hover, body.dark-theme .btn1:hover {
            background-color: #0b5ed7 !important;
        }
        body.dark-theme .resultados {
            background: #252525 !important; color: #e0e0e0 !important; border: 1px solid #444;
        }
        body.dark-theme .resultados th {
            background-color: #2d2d2d !important; color: #ffffff !important; border-color: #444 !important;
        }
        body.dark-theme .resultados td { border-color: #444 !important; }
        body.dark-theme p { color: #bbb !important; }
        #proveedorForm, #cuentaPropiaForm { display: none; }
        #themeToggle { position: absolute; margin-top: 3%; right: 20px; z-index: 1000; }
        
        .error-message {
            color: #dc3545; font-weight: bold; margin: 10px 0; padding: 8px 12px;
            background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; text-align: center;
        }
        body.dark-theme .error-message {
            background-color: #4a1c1c !important; border-color: #dc3545 !important; color: #ff6b6b !important;
        }
        .error-message.success {
            color: #155724; background-color: #d4edda; border-color: #c3e6cb;
        }
        body.dark-theme .error-message.success {
            background-color: #1c4a1c !important; border-color: #28a745 !important; color: #6bff6b !important;
        }
        .success-msg { color: #155724; }
        body.dark-theme .success-msg { color: #6bff6b !important; }
    </style>
</head>
<body class="light-theme">
    <button id="themeToggle" class="btn btn-outline-secondary">
        <i class="fas fa-moon"></i> Modo Oscuro
    </button>

    <div class="container">
        <form method="POST" id="consultaForm">
            <div style="background: #007bff;">
                <center><h1>Realizar Punto de Cuenta</h1></center>
            </div><br>
            <center><h1>Consulta de Solicitudes</h1></center><br>
            <div class="form-row">
                <div class="col-md-6">
                    <label for="numsol">Número de Solicitud:</label>
                    <input type="text" id="numsol" name="numsol" class="input-numsol-anio"
                           pattern="[1-9][0-9]{0,4}" minlength="1" maxlength="5"
                           title="Ingrese entre 1 y 5 dígitos numéricos, sin ceros a la izquierda"
                           required inputmode="numeric" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="anio">Año:</label>
                    <input type="text" id="anio" name="anio" class="input-numsol-anio"
                           pattern="[0-9]{4}" minlength="4" maxlength="4"
                           title="Ingrese un año válido entre 1561 y 2056"
                           required inputmode="numeric" autocomplete="off">
                </div>
                <button type="submit" class="boton3">Consultar</button>
            </div>
        </form>

        <?php if (!empty($error_message)): ?>
            <div class="error-message <?= strpos($error_message, 'success-msg') !== false ? 'success' : '' ?>">
                <?= strip_tags($error_message, '<span>') ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="restaurar">Restaurar Todas las Solicitudes</button>
        </form>

        <form method="POST" action="pre_punto_cuenta.php" id="puntoCuentaForm">
            <div class="resultados">
                <?php if (!empty($_SESSION['solicitudes'])): ?>
                    <h2>Resultados Acumulados</h2>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Solicitud</th>
                                <th>Cédula de Identidad</th>
                                <th>Nombre y Apellido</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['solicitudes'] as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['numsol']) ?></td>
                                    <td><?= htmlspecialchars($row['nac_ben']) ?>-<?= htmlspecialchars($row['ced_ben']) ?></td>
                                    <td><?= htmlspecialchars($row['nom_ben']) ?></td>
                                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No hay solicitudes acumuladas.</p>
                <?php endif; ?>

                <?php if (isset($_SESSION['monto']) && isset($_SESSION['fecha'])): ?>
                    <p><strong>Monto:</strong> <?= htmlspecialchars($_SESSION['monto']) ?></p>
                    <p><strong>Fecha:</strong> <?= htmlspecialchars($_SESSION['fecha']) ?></p>
                <?php endif; ?>
            </div>

            <input type="hidden" id="solicitudes" name="solicitudes" value="<?= htmlspecialchars($solicitudes_string) ?>" required>

            <center><h2>Registrar Monto y Fecha</h2></center>
            <div class="form-row">
                <div class="col-md-6">
                   <label for="monto">Monto:</label>
<input type="text" 
       id="monto" 
       name="monto" 
       required 
       inputmode="decimal" 
       placeholder="Ej: 1500.50"
       maxlength="11" 
       pattern="^\d{1,10}(\.\d{1,2})?$"
       autocomplete="off">

<script>
document.getElementById('monto').addEventListener('input', function(e) {
    let value = e.target.value;

    // 1️⃣ Eliminar cualquier carácter que no sea número o punto
    value = value.replace(/[^\d.]/g, '');

    // 2️⃣ Permitir solo UN punto decimal
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, '');
    }

    // 3️⃣ Limitar a máximo 10 dígitos (ignorando el punto)
    const soloNumeros = value.replace(/\./g, '');
    if (soloNumeros.length > 10) {
        const puntoIndex = value.indexOf('.');
        if (puntoIndex === -1) {
            // Sin punto: cortar a 10 caracteres
            value = value.slice(0, 10);
        } else {
            // Con punto: mantener el punto y permitir máximo (10 - dígitos antes del punto) decimales
            const maxDecimales = 10 - puntoIndex;
            value = value.slice(0, puntoIndex + 1 + maxDecimales);
        }
    }

    // ✅ Actualizar el valor del input
    e.target.value = value;
});
</script>
                </div>
                <div class="col-md-6">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" required style="width: 222px;">
                </div>
            </div>

            <div class="form-row">
                <label for="tipoSeleccion">Seleccione Tipo:</label>
                <select id="tipoSeleccion" required>
                    <option value="">Seleccione...</option>
                    <option value="proveedor">Proveedor</option>
                </select>
            </div>

            <div id="proveedorForm" class="form-row">
                <div class="form-row">
                    <div class="col-md-6">
                        <label for="proveedor">Proveedor:</label>
                        <select name="ced_prv" id="proveedor">
                            <option value="">Seleccione un proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?= $proveedor['ced_prv'] ?>"><?= htmlspecialchars($proveedor['nom_prv']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="provcuenta">Provcuenta:</label>
                        <select name="ids_prc" id="provcuenta">
                            <option value="">Seleccione una provcuenta</option>
                            <?php if (!empty($provcuentas)): ?>
                                <?php foreach ($provcuentas as $provcuenta): ?>
                                    <!-- ✅ Muestra número y tipo de cuenta -->
                                    <option value="<?= $provcuenta['ids_prc'] ?>">
                                        <?= htmlspecialchars($provcuenta['nro_pta'] ?? 'N/A') ?> - <?= htmlspecialchars($provcuenta['tip_cta'] ?? 'N/A') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="cuentaPropiaForm" class="form-row">
                <div class="form-row">
                    <div class="col-md-6">
                        <label for="nombre_propio">Nombre:</label>
                        <input type="text" id="nombre_propio" name="nombre_propio" style="width: 322px;" pattern="^[a-zA-Z\s]+$">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-6">
                        <label for="nacio_propio">Nacionalidad:</label>
                        <select id="nacio_propio" name="nacio_propio" required>
                            <option value="E">Extranjero</option>
                            <option value="G">Gobierno</option>
                            <option value="J">Jurídico</option>
                            <option value="P">Pasaporte</option>
                            <option value="V">Venezolano</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="Cedula_propio">Cédula:</label>
                        <input type="text" id="Cedula_propio" name="Cedula_propio" minlength="8" maxlength="9" pattern="[0-9]{1,9}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-6">
                        <label for="numero_cuenta_propio">Número de Cuenta:</label>
                        <input type="text" id="numero_cuenta_propio" name="numero_cuenta_propio" style="width: 322px;">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-6">
                        <label for="tipcuenta_propio">Tipo Cuenta:</label>
                        <select id="tipcuenta_propio" name="tipcuenta_propio" required>
                            <option value="CORRIENTE">CORRIENTE</option>
                            <option value="AHORRO">AHORRO</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="codban_propio">Banco:</label>
                        <select id="codban_propio" name="codban_propio" required>
                            <option value="BANCAMIGA">BANCAMIGA</option>
                            <option value="BANCO NACIONAL DE CREDITO">BANCO NACIONAL DE CREDITO</option>
                            <option value="BANESCO">BANESCO</option>
                            <option value="BFC">BFC</option>
                            <option value="BICENTENARIO">BICENTENARIO</option>
                            <option value="BNC">BNC</option>
                            <option value="CARONI">CARONI</option>
                            <option value="MERCANTIL">MERCANTIL</option>
                            <option value="PROVINCIAL">PROVINCIAL</option>
                            <option value="SOFITASA">SOFITASA</option>
                            <option value="TESORO">TESORO</option>
                            <option value="VENEZUELA">VENEZUELA</option>
                        </select>
                    </div>
                </div>
            </div>

            <center>
                <button id="btnRealizarPuntoDeCuenta" type="submit" disabled>Realizar punto de cuenta</button>
            </center>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        // 🔹 Gestión de tema
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
            document.getElementById('themeToggle').innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i> Modo Claro' : '<i class="fas fa-moon"></i> Modo Oscuro';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const numsol = document.getElementById('numsol');
            const anio = document.getElementById('anio');
            const monto = document.getElementById('monto');

            // 🔹 Filtro genérico mejorado
            function numericFilter(input, maxLength, options = {}) {
                const { allowLeadingZero = false, minRange = null, maxRange = null } = options;
                input.addEventListener('input', function() {
                    let value = this.value.replace(/[^0-9]/g, '').slice(0, maxLength);
                    if (!allowLeadingZero && value.length > 1 && value[0] === '0') value = value.slice(1);
                    if (minRange !== null && maxRange !== null && value.length === maxLength) {
                        const numValue = parseInt(value, 10);
                        this.setCustomValidity((numValue < minRange || numValue > maxRange) ? `Rango válido: ${minRange}-${maxRange}` : '');
                    }
                    this.value = value;
                });
                input.addEventListener('keydown', function(e) {
                    const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'];
                    if (allowed.includes(e.key) || (e.ctrlKey && ['a','c','v','x','z'].includes(e.key.toLowerCase()))) return;
                    if (e.key < '0' || e.key > '9') e.preventDefault();
                    if (!allowLeadingZero && this.value.length === 0 && e.key === '0') e.preventDefault();
                });
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    let numeric = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, maxLength);
                    if (!allowLeadingZero && numeric.length > 1 && numeric[0] === '0') numeric = numeric.slice(1);
                    if (document.queryCommandSupported('insertText')) document.execCommand('insertText', false, numeric);
                    else this.value = numeric;
                });
            }

            numericFilter(numsol, 5, { allowLeadingZero: false });
            numericFilter(anio, 4, { minRange: 1561, maxRange: 2056 });

            // 🔹 VALIDACIÓN MONTO: Sin letras, sin espacios, solo números y un punto decimal
            monto.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9.,]/g, ''); // Elimina todo excepto dígitos, punto y coma
                this.value = this.value.replace(/\s/g, '');       // Elimina espacios explícitamente
                this.value = this.value.replace(',', '.');        // Normaliza coma a punto
                const parts = this.value.split('.');
                if (parts.length > 2) {
                    this.value = parts[0] + '.' + parts.slice(1).join('');
                }
            });
            monto.addEventListener('keydown', function(e) {
                const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End','.'];
                if (allowed.includes(e.key) || (e.ctrlKey && ['a','c','v','x','z'].includes(e.key.toLowerCase()))) return;
                if ((e.key < '0' || e.key > '9') && e.key !== '.') e.preventDefault();
                if (e.key === '.' && this.value.includes('.')) e.preventDefault(); // Solo un punto
                if (e.key === ' ' || e.code === 'Space') e.preventDefault(); // Bloquea espacio
            });
            monto.addEventListener('paste', function(e) {
                e.preventDefault();
                let pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9.]/g, '');
                if (document.queryCommandSupported('insertText')) document.execCommand('insertText', false, pasted);
                else this.value = pasted;
            });

            // ✅ Validación al enviar formulario
            document.getElementById('consultaForm').addEventListener('submit', function(e) {
                if (!/^[1-9][0-9]{0,4}$/.test(numsol.value.trim())) {
                    e.preventDefault(); showError('Error técnico: El Número de Solicitud debe contener entre 1 y 5 dígitos numéricos, sin ceros a la izquierda.'); numsol.focus(); return false;
                }
                const anioNum = parseInt(anio.value.trim(), 10);
                if (!/^[0-9]{4}$/.test(anio.value.trim()) || anioNum < 1561 || anioNum > 2056) {
                    e.preventDefault(); showError('Error técnico: El Año debe estar en el rango de 1561 a 2056.'); anio.focus(); return false;
                }
            });

            function showError(message) {
                let errorDiv = document.querySelector('.error-message');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    const restoreForm = document.querySelector('form[method="POST"]:not(#consultaForm):not(#puntoCuentaForm)');
                    if (restoreForm) restoreForm.parentNode.insertBefore(errorDiv, restoreForm);
                }
                errorDiv.textContent = message;
                errorDiv.classList.remove('success');
                setTimeout(() => { errorDiv.style.opacity = '0'; setTimeout(() => errorDiv.remove(), 300); }, 5000);
            }

            // 🔹 Lógica formularios dinámicos
            const tipoSeleccion = document.getElementById('tipoSeleccion');
            const proveedorForm = document.getElementById('proveedorForm');
            const cuentaPropiaForm = document.getElementById('cuentaPropiaForm');
            const btnSubmit = document.getElementById('btnRealizarPuntoDeCuenta');

            function toggleForms() {
                const tipo = tipoSeleccion.value;
                proveedorForm.style.display = tipo === 'proveedor' ? 'block' : 'none';
                cuentaPropiaForm.style.display = tipo === 'cuenta_propia' ? 'block' : 'none';
                validateForm();
            }
            function validateForm() {
                const tipo = tipoSeleccion.value;
                let isValid = false;
                if (tipo === 'proveedor') {
                    isValid = document.getElementById('proveedor').value !== '' && document.getElementById('provcuenta').value !== '';
                } else if (tipo === 'cuenta_propia') {
                    isValid = document.getElementById('nombre_propio').value.trim() !== '' && 
                              document.getElementById('Cedula_propio').value.trim() !== '' && 
                              document.getElementById('numero_cuenta_propio').value.trim() !== '';
                }
                btnSubmit.disabled = !isValid;
            }
            if (tipoSeleccion) tipoSeleccion.addEventListener('change', toggleForms);
            ['proveedor','provcuenta','nombre_propio','Cedula_propio','numero_cuenta_propio'].forEach(id => {
                const el = document.getElementById(id); if (el) el.addEventListener('input', validateForm);
            });

            // 🔹 Carga dinámica de provcuentas (AHORA MUESTRA NRO Y TIPO)
            const proveedorSelect = document.getElementById('proveedor');
            if (proveedorSelect) {
                proveedorSelect.addEventListener('change', function() {
                    const provcuentaSelect = document.getElementById('provcuenta');
                    provcuentaSelect.innerHTML = '<option value="">Seleccione una provcuenta</option>';
                    const ced = this.value;
                    if (ced) {
                        fetch(`get_provcuentas.php?ced_prv=${ced}`)
                            .then(res => res.json())
                            .then(data => {
                                data.forEach(pc => {
                                    const opt = document.createElement('option');
                                    opt.value = pc.ids_prc;
                                    const nro = pc.nro_cta || 'N/A';
                                    const tipo = pc.tip_cta || pc.tipo_cuenta || 'N/A';
                                    const ban = pc.nombanco || pc.nom_ban || '';
                                    opt.textContent = ban ? `${ban} - ${nro} - ${tipo}` : `${nro} - ${tipo}`;
                                    provcuentaSelect.appendChild(opt);
                                });
                                validateForm();
                            })
                            .catch(err => console.error('Error cargando provcuentas:', err));
                    }
                });
            }

            toggleForms();
            const solicitudesString = "<?php echo addslashes($solicitudes_string); ?>";
            if (solicitudesString && solicitudesString.split(',').filter(s=>s.trim()!=='').length > 0) btnSubmit.disabled = false;
        });
    </script>
</body>
</html>