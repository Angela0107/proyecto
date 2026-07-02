<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'nav/index.php';

// Inicializar variables para evitar warnings
$puntoctaes_string = '';
$results = [];

if (!isset($_SESSION['puntoctaes'])) {
    $_SESSION['puntoctaes'] = [];
}

require_once "../db.php.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ==========================================
    // 1. CONSULTAR PUNTO DE CUENTA
    // ==========================================
    if (isset($_POST['nrp_pta']) && isset($_POST['anio'])) {
        $nropto = trim($_POST['nrp_pta']);
        $anio = trim($_POST['anio']);

        // 🛡️ VALIDACIÓN SERVIDOR: Número de solicitud/puntocta
        if (!preg_match('/^[1-9]\d{0,4}$/', $nropto)) {
            echo "<script>alert('❌ Número de solicitud inválido.\nSolo se permiten 1 a 5 dígitos numéricos sin ceros a la izquierda.');</script>";
            $conn->close();
            exit;
        }

        // 🛡️ VALIDACIÓN SERVIDOR: Año de consulta
        $anio_int = filter_var($anio, FILTER_VALIDATE_INT);
        if ($anio_int === false || $anio_int < 1562 || $anio_int > 2055) {
            echo "<script>alert('❌ Año inválido.\nEl año debe estar entre 1562 y 2055.');</script>";
            $conn->close();
            exit;
        }

        // Consulta segura
        $sql = "SELECT s.ids_pta, s.nrp_pta, s.asu_pta, s.ano_pta, s.nac_pta, s.fec_pto 
                FROM puntocta s 
                WHERE s.nrp_pta = ? AND YEAR(s.fec_pto) = ? AND fec_pag IS NULL;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nropto, $anio_int);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        if (!empty($results)) {
            $exists = false;
            foreach ($_SESSION['puntoctaes'] as $key => $puntocta) {
                if ($puntocta['nrp_pta'] === $results[0]['nrp_pta'] && 
                    $puntocta['nac_pta'] === $results[0]['nac_pta'] && 
                    $puntocta['ced_ben'] === $results[0]['ced_ben']) {
                    unset($_SESSION['puntoctaes'][$key]);
                    $exists = true;
                    echo "<script>alert('✅ Punto de cuenta eliminado de la tabla.');</script>";
                    break;
                }
            }

            if (!$exists) {
                $_SESSION['puntoctaes'] = array_merge($_SESSION['puntoctaes'], $results);
            }
        }

        // Preparar string para el form de aprobación
        $puntoctaes = [];
        if (!empty($_SESSION['puntoctaes'])) {
            foreach ($_SESSION['puntoctaes'] as $puntocta) {
                $puntoctaes[] = $puntocta['ids_pta'];
            }
        }
        $puntoctaes = array_unique($puntoctaes);
        $puntoctaes_string = implode(", ", $puntoctaes);

        $stmt->close();
        
    // ==========================================
    // 2. RESTAURAR TODO
    // ==========================================
    } elseif (isset($_POST['restaurar'])) {
        $_SESSION['puntoctaes'] = [];
        echo "<script>alert('🔄 Todos los puntos de cuenta han sido restaurados.');</script>";
        
    // ==========================================
    // 3. DESCONSULTAR
    // ==========================================
    } elseif (isset($_POST['desconsultar'])) {
        $nrp_pta = $_POST['nrp_pta_desconsultar'];
        foreach ($_SESSION['puntoctaes'] as $key => $puntocta) {
            if ($puntocta['nrp_pta'] === $nrp_pta) {
                unset($_SESSION['puntoctaes'][$key]);
                echo "<script>alert('🗑️ Punto de cuenta desconsultado.');</script>";
                break;
            }
        }
        
    // ==========================================
    // 4. REGISTRAR DESCRIPCIÓN Y FECHA (APROBACIÓN)
    // ==========================================
    } elseif (isset($_POST['monto']) && isset($_POST['fecha'])) {
        $monto = trim($_POST['monto']);
        $fecha = trim($_POST['fecha']);
        
        // 🛡️ VALIDACIÓN SERVIDOR: Campo fecha (año de 4 dígitos)
        // Reglas: Exactamente 4 dígitos, no inicia con 0, rango 1561-2056
        if (!preg_match('/^[1-9]\d{3}$/', $fecha)) {
            echo "<script>alert('❌ Fecha inválida.\nDebe ingresar exactamente 4 dígitos numéricos sin ceros a la izquierda.');</script>";
            $conn->close();
            exit;
        }
        
        $fecha_int = (int)$fecha;
        if ($fecha_int < 1561 || $fecha_int > 2056) {
            echo "<script>alert('❌ Fecha fuera de rango.\nEl valor debe estar entre 1561 y 2056.');</script>";
            $conn->close();
            exit;
        }
        
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
    <title>Consulta de Punto de Cuenta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body.light-theme { background-color: #f5f7fa; color: #333; }
        body.dark-theme { background-color: #121212; color: #e0e0e0; }

        .container {
            max-width: 1200px; margin: 0 auto; padding: 20px;
            margin-left: 17%; margin-top: 112px; width: 80%;
            border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        body.light-theme .container { background-color: #ffffff; }
        body.dark-theme .container { background-color: #1e1e1e !important; color: #e0e0e0 !important; border: 1px solid #333; }

        .header-blue { background-color: #007bff; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        body.dark-theme .header-blue { background-color: #0d6efd !important; }

        h1, h2 { color: #333; font-size: large; margin: 0; }
        body.dark-theme h1, body.dark-theme h2 { color: #e0e0e0 !important; }

        label { display: block; margin-bottom: 5px; font-weight: bold; }
        body.dark-theme label { color: #e0e0e0 !important; }

        input[type="text"], input[type="date"], input[type="number"], select {
            width: 476px; margin-right: 10px; padding: 10px; margin-bottom: 10px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        input[type="number"], input[type="text"], .input-nrp_pta-anio {
            width: 172.66666px; margin-right: 10px; padding: 10px; margin-bottom: 10px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        body.dark-theme input[type="text"], body.dark-theme input[type="number"],
        body.dark-theme input[type="date"], body.dark-theme select,
        body.dark-theme .input-nrp_pta-anio {
            background-color: #2d2d2d !important; color: #e0e0e0 !important; border-color: #444 !important;
        }

        button {
            background-color: #007bff; color: white; border: none;
            padding: 10px 15px; border-radius: 4px; cursor: pointer; margin-top: 23px;
        }
        button:hover { background-color: #0056b3; }
        button:disabled { background-color: #6c757d !important; cursor: not-allowed; opacity: 0.65; }
        body.dark-theme button { background-color: #0d6efd !important; color: white !important; }
        body.dark-theme button:hover { background-color: #0b5ed7 !important; }
        body.dark-theme button:disabled { background-color: #495057 !important; }

        .resultados { margin-top: 20px; padding: 15px; border-radius: 5px; }
        body.light-theme .resultados { background: #e9ecef; }
        body.dark-theme .resultados { background: #252525 !important; color: #e0e0e0 !important; border: 1px solid #444; }

        .resultados table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .resultados th, .resultados td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        .resultados th { background-color: #f2f2f2; }
        body.dark-theme .resultados th { background-color: #2d2d2d !important; color: #ffffff !important; border-color: #444 !important; }
        body.dark-theme .resultados td { border-color: #444 !important; }

        p { color: #555; }
        body.dark-theme p { color: #bbb !important; }

        .form-row { display: flex; justify-content: space-between; margin-bottom: 15px; width: 429px; margin-left: 20px; margin-top: 10px; align-items: flex-end; }

        /* Mensajes de error */
        .error-message { color: #dc3545; font-size: 0.85em; display: none; margin-top: -5px; margin-bottom: 10px; }
        .error-message.visible { display: block; }
        input.error { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; }

        #themeToggle { position: absolute; top: 20px; right: 20px; z-index: 1000; }
    </style>
</head>
<body class="light-theme">
    <button id="themeToggle" class="btn btn-outline-secondary">
        <i class="fas fa-moon"></i> Modo Oscuro
    </button>

    <div class="container">
        <form method="POST" id="consultaForm" novalidate>
            <div class="header-blue">
                <h1>Aprobación de Punto de Cuenta</h1>
            </div>
            <br>
            <center><h1>Consulta de Punto de Cuenta</h1></center><br>
            
            <div class="form-row">
                <div class="col-md-6">
                    <label for="nrp_pta">Número de solicitud:</label>
                    <input 
                        type="text" 
                        id="nrp_pta" 
                        name="nrp_pta" 
                        class="input-nrp_pta-anio" 
                        required
                        pattern="^[1-9]\d{0,4}$"
                        title="Solo números de 1 a 5 dígitos, sin ceros a la izquierda"
                        maxlength="5"
                        inputmode="numeric"
                        autocomplete="off"
                    >
                    <small id="nrp_pta_error" class="error-message">
                        ⚠️ Solo 1-5 dígitos numéricos, sin ceros iniciales
                    </small>
                </div>
                <div class="col-md-6">
                    <label for="anio">Año:</label>
                    <input 
                        type="text" 
                        id="anio" 
                        name="anio" 
                        class="input-nrp_pta-anio" 
                        required
                        min="1562" 
                        max="2055"
                        title="Año debe estar entre 1562 y 2055"
                        inputmode="numeric"
                        autocomplete="off"
                    >
                </div>
                <button type="submit" class="boton3" id="btnConsultar" disabled>Consultar</button>
            </div>
        </form>

        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="restaurar">Restaurar Todos los puntos de cuenta</button>
        </form>

        <form method="POST" action="pre_aprobacion.php" id="aprobacionForm" novalidate>
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
                    <p>No hay puntos de cuenta acumulados.</p>
                <?php endif; ?>
            </div>

            <input type="hidden" id="puntoctaes" name="puntoctaes" value="<?= htmlspecialchars($puntoctaes_string) ?>">

            <center><h2>Registrar Descripción y Fecha</h2></center>
            <div class="form-row">
                <div class="col-md-6">
                    <label for="monto">Descripción:</label>
                    <input type="text" id="monto" name="monto">
                </div>
                <div class="col-md-6">
                    <label for="fecha">Fecha (año):</label>
                    <input 
                        type="text" 
                        id="fecha" 
                        name="fecha" 
                        required
                        pattern="^[1-9]\d{3}$"
                        title="Año de 4 dígitos entre 1561 y 2056, sin ceros a la izquierda"
                        maxlength="4"
                        minlength="4"
                        inputmode="numeric"
                        autocomplete="off"
                        style="width: 222px;"
                    >
                    <small id="fecha_error" class="error-message">
                        ⚠️ Año inválido: 4 dígitos, rango 1561-2056
                    </small>
                </div>
            </div>

            <center><button type="submit" id="btnAprobar">Realizar aprobación</button></center>
        </form>
    </div>
<script>
    // ==========================================
    // FUNCIÓN UTILITARIA: Solo dígitos puros
    // ==========================================
    function keepDigitsOnly(value) {
        return value.replace(/[^0-9]/g, '');
    }

    // ==========================================
    // VALIDACIÓN CAMPOS DE CONSULTA
    // ==========================================
    function validateNrpPta(input) {
        const errorEl = document.getElementById('nrp_pta_error');
        const value = input.value.trim();
        const pattern = /^[1-9]\d{0,4}$/;
        
        if (value === '') {
            errorEl?.classList.remove('visible');
            input.classList.remove('error');
            input.setCustomValidity('');
            return false;
        }
        
        if (!pattern.test(value)) {
            errorEl?.classList.add('visible');
            input.classList.add('error');
            input.setCustomValidity('Inválido');
            return false;
        } else {
            errorEl?.classList.remove('visible');
            input.classList.remove('error');
            input.setCustomValidity('');
            return true;
        }
    }

    function validateAnio(input) {
        const errorEl = document.getElementById('anio_error');
        const value = input.value.trim();
        
        // Validar: exactamente 4 dígitos
        if (!/^\d{4}$/.test(value)) {
            errorEl?.classList.add('visible');
            input.classList.add('error');
            input.setCustomValidity('Inválido');
            return false;
        }
        
        // Validar rango: 1562 - 2055
        const numValue = parseInt(value, 10);
        if (numValue < 1562 || numValue > 2055) {
            errorEl?.classList.add('visible');
            input.classList.add('error');
            input.setCustomValidity('Fuera de rango');
            return false;
        }
        
        errorEl?.classList.remove('visible');
        input.classList.remove('error');
        input.setCustomValidity('');
        return true;
    }

    // Eventos para nrp_pta
    const nrpInput = document.getElementById('nrp_pta');
    if (nrpInput) {
        nrpInput.addEventListener('input', function() {
            this.value = keepDigitsOnly(this.value).slice(0, 5);
            updateConsultarButton();
        });
        nrpInput.addEventListener('blur', function() {
            validateNrpPta(this);
            updateConsultarButton();
        });
    }
    
    // Eventos para anio (CORREGIDO: bloquea letras, espacios, 'e', etc.)
    const anioInput = document.getElementById('anio');
    if (anioInput) {
        anioInput.addEventListener('input', function() {
            // Eliminar CUALQUIER carácter que no sea dígito 0-9
            this.value = keepDigitsOnly(this.value).slice(0, 4);
            updateConsultarButton();
        });
        anioInput.addEventListener('blur', function() {
            validateAnio(this);
            updateConsultarButton();
        });
        // Prevenir pegado de texto no numérico
        anioInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            const digitsOnly = keepDigitsOnly(pasted).slice(0, 4);
            this.value = digitsOnly;
            updateConsultarButton();
        });
    }

    function updateConsultarButton() {
        const btn = document.getElementById('btnConsultar');
        if (!btn) return;
        const nrpValid = validateNrpPta(document.getElementById('nrp_pta'));
        const anioValid = validateAnio(document.getElementById('anio'));
        btn.disabled = !(nrpValid && anioValid);
    }

    document.getElementById('consultaForm')?.addEventListener('submit', function(e) {
        if (!validateNrpPta(document.getElementById('nrp_pta')) || !validateAnio(document.getElementById('anio'))) {
            e.preventDefault();
        }
    });

    // ==========================================
    // VALIDACIÓN CAMPO FECHA (APROBACIÓN)
    // ==========================================
    function validateFecha(input) {
        const errorEl = document.getElementById('fecha_error');
        const value = input.value.trim();
        const pattern = /^[1-9]\d{3}$/;
        
        if (value === '') {
            errorEl?.classList.remove('visible');
            input.classList.remove('error');
            input.setCustomValidity('');
            return false;
        }
        
        if (!pattern.test(value)) {
            errorEl?.classList.add('visible');
            input.classList.add('error');
            input.setCustomValidity('Formato inválido');
            return false;
        }
        
        const numValue = parseInt(value, 10);
        if (numValue < 1561 || numValue > 2056) {
            errorEl?.classList.add('visible');
            input.classList.add('error');
            input.setCustomValidity('Fuera de rango');
            return false;
        }
        
        errorEl?.classList.remove('visible');
        input.classList.remove('error');
        input.setCustomValidity('');
        return true;
    }

    const fechaInput = document.getElementById('fecha');
    if (fechaInput) {
        fechaInput.addEventListener('input', function() {
            this.value = keepDigitsOnly(this.value).slice(0, 4);
            updateAprobarButton();
        });
        fechaInput.addEventListener('blur', function() {
            validateFecha(this);
            updateAprobarButton();
        });
        fechaInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            this.value = keepDigitsOnly(pasted).slice(0, 4);
            updateAprobarButton();
        });
    }

    function updateAprobarButton() {
        const btn = document.getElementById('btnAprobar');
        if (!btn) return;
        const fechaValid = validateFecha(document.getElementById('fecha'));
        btn.disabled = !fechaValid;
    }

    document.getElementById('aprobacionForm')?.addEventListener('submit', function(e) {
        if (!validateFecha(document.getElementById('fecha'))) {
            e.preventDefault();
            document.getElementById('fecha')?.focus();
        }
    });

    // ==========================================
    // TEMA CLARO/OSCURO
    // ==========================================
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.className = savedTheme + '-theme';
    updateToggleButton(savedTheme);

    document.getElementById('themeToggle')?.addEventListener('click', function () {
        const isDark = document.body.classList.contains('dark-theme');
        const newTheme = isDark ? 'light' : 'dark';
        document.body.className = newTheme + '-theme';
        localStorage.setItem('theme', newTheme);
        updateToggleButton(newTheme);
    });

    function updateToggleButton(theme) {
        const btn = document.getElementById('themeToggle');
        if (!btn) return;
        btn.innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i> Modo Claro' : '<i class="fas fa-moon"></i> Modo Oscuro';
    }

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', function() {
        updateConsultarButton();
        updateAprobarButton();
    });
    </script>
</body>
</html>