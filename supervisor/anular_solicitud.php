<?php
error_reporting(E_ALL);
include 'nav/index.php';

if (!isset($_SESSION['solicitudes'])) {
    $_SESSION['solicitudes'] = []; 
}

require_once "./db.php";

$results = [];
$solicitudes_string = '';

// 🔹 Función para mostrar mensaje con diseño (reemplaza alert)
function mostrarMensaje($tipo, $mensaje) {
    $clase = $tipo === 'error' ? 'modal-error' : 'modal-success';
    $icono = $tipo === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle';
    $titulo = $tipo === 'error' ? 'Error Técnico' : 'Operación Exitosa';
    echo "
    <div class='modal-overlay' id='mensajeModal'>
        <div class='modal-content {$clase}'>
            <div class='modal-header'>
                <i class='fas {$icono}'></i>
                <h3>{$titulo}</h3>
            </div>
            <div class='modal-body'>
                <p>{$mensaje}</p>
            </div>
            <div class='modal-footer'>
                <button class='btn-modal' onclick='cerrarModal()'>Aceptar</button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('mensajeModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
        function cerrarModal() {
            document.getElementById('mensajeModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            window.history.back();
        }
    </script>
    ";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['numsol']) && isset($_POST['anio'])) {
        $numsol = trim($_POST['numsol']);
        $anio = trim($_POST['anio']);

        // 🔹 Validación PHP: Número de Solicitud (1-5 dígitos, SIN ceros a la izquierda, valor ≥ 1)
        if (!preg_match('/^[1-9][0-9]{0,4}$/', $numsol)) {
            mostrarMensaje('error', 'El Número de Solicitud debe iniciar con un dígito del 1 al 9 y contener entre 1 y 5 dígitos numéricos en total. No se permiten ceros a la izquierda.');
            $conn->close();
            exit;
        }

        // 🔹 Validación PHP: Año (exactamente 4 dígitos, rango 1561-2056 inclusive)
        $anioNum = intval($anio);
        if (!preg_match('/^[0-9]{4}$/', $anio) || $anioNum < 1561 || $anioNum > 2056) {
            mostrarMensaje('error', 'El Año debe ser un valor numérico de 4 dígitos comprendido entre 1561 y 2056 (inclusive).');
            $conn->close();
            exit;
        }

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
                    mostrarMensaje('success', 'Solicitud eliminada de la tabla de acumulados.');
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
    } elseif (isset($_POST['restaurar'])) {
        $_SESSION['solicitudes'] = [];
        mostrarMensaje('success', 'Todas las solicitudes han sido restauradas correctamente.');
    } elseif (isset($_POST['desconsultar'])) {
        $numsol = $_POST['numsol_desconsultar'];
        foreach ($_SESSION['solicitudes'] as $key => $solicitud) {
            if ($solicitud['numsol'] === $numsol) {
                unset($_SESSION['solicitudes'][$key]);
                mostrarMensaje('success', 'Solicitud desconsultada exitosamente.');
                break;
            }
        }
    } elseif (isset($_POST['monto'])) {
        $monto = $_POST['monto'];
        $_SESSION['monto'] = $monto;
        mostrarMensaje('success', 'Motivo de anulación registrado correctamente.');
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Solicitudes</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body.light-theme { background-color: #f5f7fa; color: #333; }
        body.dark-theme { background-color: #121212; color: #e0e0e0; }
        .container-form {
            max-width: 1200px; margin: 0 auto; padding: 20px; margin-left: 17%;
            margin-top: 10%; width: 80%; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        body.light-theme .container-form { background-color: #ffffff; }
        body.dark-theme .container-form {
            background-color: #1e1e1e !important; color: #e0e0e0 !important; border: 1px solid #333;
        }
        h1, h2 { color: #333; }
        body.dark-theme h1, body.dark-theme h2 { color: #e0e0e0 !important; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        body.dark-theme label { color: #e0e0e0 !important; }
        input[type="text"], input[type="date"], input[type="number"], select {
             margin-right: 10px; padding: 10px; margin-bottom: 10px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .input-numsol-anio {
            width: 172.66666px; margin-right: 10px; padding: 10px; margin-bottom: 10px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        body.dark-theme input[type="text"], body.dark-theme input[type="number"],
        body.dark-theme select, body.dark-theme .input-numsol-anio {
            background-color: #2d2d2d !important; color: #e0e0e0 !important; border-color: #444 !important;
        }
        button {
            background-color: #007bff; color: white; border: none; padding: 10px 15px;
            border-radius: 4px; cursor: pointer; margin-top: 23px;
        }
        button:hover { background-color: #0056b3; }
        body.dark-theme button {
            background-color: #0d6efd !important; color: white !important; border-color: #444 !important;
        }
        body.dark-theme button:hover { background-color: #0b5ed7 !important; }
        .resultados table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .resultados th, .resultados td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        .resultados th { background-color: #f2f2f2; }
        body.dark-theme .resultados th {
            background-color: #2d2d2d !important; color: #ffffff !important; border-color: #444 !important;
        }
        body.dark-theme .resultados td { border-color: #444 !important; }
        body.dark-theme .resultados {
            background-color: #252525 !important; color: #e0e0e0 !important; border: 1px solid #444;
        }
        p { color: #555; }
        body.dark-theme p { color: #bbb !important; }
        .form-row {
            display: flex; justify-content: space-between; margin-bottom: 15px;
            width: 596px; margin-left: 20px; margin-top: 10px;
        }
        #themeToggle { position: absolute; margin-top:3%; right: 20px; z-index: 1000; }

        /* 🔹 Estilos para modal con diseño */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }
        .modal-content {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
            max-width: 480px;
            width: 90%;
            overflow: hidden;
            animation: modalSlide 0.3s ease-out;
        }
        body.dark-theme .modal-content {
            background: #2a2a2a !important;
            border: 1px solid #444;
        }
        @keyframes modalSlide {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #eee;
        }
        body.dark-theme .modal-header { border-bottom-color: #444; }
        .modal-header i { font-size: 22px; }
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        .modal-body {
            padding: 20px;
            font-size: 15px;
            line-height: 1.5;
        }
        .modal-body p { margin: 0; }
        .modal-footer {
            padding: 12px 20px 20px;
            text-align: right;
        }
        .btn-modal {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-modal:hover { background: #0056b3; }
        body.dark-theme .btn-modal { background: #0d6efd; }
        body.dark-theme .btn-modal:hover { background: #0b5ed7; }

        /* 🔹 Variantes de color */
        .modal-error .modal-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        .modal-error .modal-header i { color: #fff; }
        .modal-error .modal-header h3 { color: #fff; }

        .modal-success .modal-header {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
        }
        .modal-success .modal-header i { color: #fff; }
        .modal-success .modal-header h3 { color: #fff; }

        /* 🔹 Input con indicador de error */
        input.input-error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15) !important;
        }
    </style>
</head>
<body class="light-theme">
    <button id="themeToggle" class="btn btn-outline-secondary">
        <i class="fas fa-moon"></i> Modo Oscuro
    </button>

    <div class="container-form">
        <form method="POST" id="consultaForm">
            <center><div style="font-size: 25px;">Anular Solicitud</div></center><br>
            <div class="form-row">
                <div class="col-md-6">
                    <label for="numsol">Número de Solicitud:</label>
                    <!-- ✅ Inicia con 1-9, seguido de 0-4 dígitos adicionales (total 1-5), SIN ceros a la izquierda -->
                    <input type="text" id="numsol" name="numsol" class="input-numsol-anio" 
                           pattern="[1-9][0-9]{0,4}" minlength="1" maxlength="5" 
                           title="Ingrese un número entre 1 y 99999, sin ceros a la izquierda (ej: 1, 42, 1500)" 
                           required inputmode="numeric" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="anio">Año:</label>
                    <!-- ✅ Exactamente 4 dígitos, rango 1561-2056 -->
                    <input type="text" id="anio" name="anio" class="input-numsol-anio" 
                           pattern="[0-9]{4}" minlength="4" maxlength="4" 
                           title="Ingrese un año válido entre 1561 y 2056 (4 dígitos numéricos)" 
                           required inputmode="numeric" autocomplete="off">
                </div>
                <button type="submit" class="boton3">Consultar</button>
            </div>
        </form>

        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="restaurar">Restaurar Todas las Solicitudes</button>
        </form>

        <form method="POST" action="pre_anular_solicitud.php">
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

                <?php if (isset($_SESSION['monto'])): ?>
                    <p><strong>Motivo:</strong> <?= htmlspecialchars($_SESSION['monto']) ?></p>
                <?php endif; ?>
            </div>

            <input type="hidden" id="solicitudes" name="solicitudes" value="<?= htmlspecialchars($solicitudes_string) ?>" required>

            <center><h2>Registrar anulación</h2></center>
            <div class="form-row">
                <div class="col-md-6">
                    <label for="monto">Motivo de la anulación:</label>
                    <input type="text" id="monto" name="monto" required>
                </div>
            </div>
            <center>
                <button id="btnRealizarPuntoDeCuenta" type="submit">Anular Solicitud</button>
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

        document.getElementById('themeToggle').addEventListener('click', function () {
            const isDark = document.body.classList.contains('dark-theme');
            const newTheme = isDark ? 'light' : 'dark';
            document.body.className = newTheme + '-theme';
            localStorage.setItem('theme', newTheme);
            updateToggleButton(newTheme);
        });

        function updateToggleButton(theme) {
            const btn = document.getElementById('themeToggle');
            btn.innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i> Modo Claro' : '<i class="fas fa-moon"></i> Modo Oscuro';
        }

        // 🔹 Función para mostrar mensaje con diseño (reemplaza alert)
        function mostrarMensajeDiseño(tipo, mensaje) {
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.id = 'mensajeModal';
            
            const icono = tipo === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle';
            const titulo = tipo === 'error' ? 'Error Técnico' : 'Operación Exitosa';
            const claseModal = tipo === 'error' ? 'modal-error' : 'modal-success';
            
            overlay.innerHTML = `
                <div class="modal-content ${claseModal}">
                    <div class="modal-header">
                        <i class="fas ${icono}"></i>
                        <h3>${titulo}</h3>
                    </div>
                    <div class="modal-body">
                        <p>${mensaje}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn-modal" onclick="cerrarMensajeModal()">Aceptar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function cerrarMensajeModal() {
            const modal = document.getElementById('mensajeModal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }

        // 🔹 Validación JavaScript: Filtrado en tiempo real + manejo correcto de teclas
        document.addEventListener('DOMContentLoaded', function() {
            const numsol = document.getElementById('numsol');
            const anio = document.getElementById('anio');

            // Función para filtro numérico genérico
            function numericFilter(input, maxLength, allowLeadingZero = false) {
                input.addEventListener('input', function() {
                    let val = this.value.replace(/[^0-9]/g, '').slice(0, maxLength);
                    // Si no se permiten ceros a la izquierda y el primer carácter es 0
                    if (!allowLeadingZero && val.length > 1 && val[0] === '0') {
                        val = val.replace(/^0+/, '');
                        if (val === '') val = '';
                    }
                    this.value = val;
                });

                input.addEventListener('keydown', function(e) {
                    const allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                    if (allowedKeys.includes(e.key) || (e.ctrlKey && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase()))) {
                        return;
                    }
                    if (e.key < '0' || e.key > '9') {
                        e.preventDefault();
                    }
                });

                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                    let numeric = pasted.replace(/[^0-9]/g, '').slice(0, maxLength);
                    if (!allowLeadingZero && numeric.length > 1 && numeric[0] === '0') {
                        numeric = numeric.replace(/^0+/, '');
                        if (numeric === '') numeric = '';
                    }
                    if (document.queryCommandSupported('insertText')) {
                        document.execCommand('insertText', false, numeric);
                    } else {
                        this.value = numeric;
                    }
                });
            }

            // Aplicar filtros: numsol (NO permite ceros a la izquierda), anio (sí permite)
            numericFilter(numsol, 5, false);  // false = no permitir leading zero
            numericFilter(anio, 4, true);      // true = permitir cualquier dígito inicial

            // 🔹 Validación adicional para campo Año: rango 1561-2056 en tiempo real
            anio.addEventListener('input', function() {
                if (this.value.length === 4) {
                    let val = parseInt(this.value, 10);
                    if (val < 1561) { this.value = '1561'; resaltarCampo(this, true); }
                    else if (val > 2056) { this.value = '2056'; resaltarCampo(this, true); }
                    else { resaltarCampo(this, false); }
                } else {
                    resaltarCampo(this, false);
                }
            });

            // Función para resaltar campo con error
            function resaltarCampo(input, error) {
                if (error) {
                    input.classList.add('input-error');
                } else {
                    input.classList.remove('input-error');
                }
            }

            // ✅ Validación estricta al enviar formulario
            document.getElementById('consultaForm').addEventListener('submit', function(e) {
                const numsolVal = numsol.value.trim();
                const anioVal = anio.value.trim();

                // Validar numsol: patrón ^[1-9][0-9]{0,4}$ (sin ceros a la izquierda, valor ≥ 1)
                if (!/^[1-9][0-9]{0,4}$/.test(numsolVal)) {
                    e.preventDefault();
                    resaltarCampo(numsol, true);
                    mostrarMensajeDiseño('error', 'El Número de Solicitud debe iniciar con un dígito del 1 al 9 y contener entre 1 y 5 dígitos numéricos. No se permiten ceros a la izquierda (ej: 01, 007).');
                    numsol.focus();
                    return false;
                } else {
                    resaltarCampo(numsol, false);
                }

                // Validar año: exactamente 4 dígitos y rango 1561-2056
                const anioNum = parseInt(anioVal, 10);
                if (!/^[0-9]{4}$/.test(anioVal) || isNaN(anioNum) || anioNum < 1561 || anioNum > 2056) {
                    e.preventDefault();
                    resaltarCampo(anio, true);
                    mostrarMensajeDiseño('error', 'El Año debe ser un valor numérico de 4 dígitos comprendido entre 1561 y 2056 (inclusive).');
                    anio.focus();
                    return false;
                } else {
                    resaltarCampo(anio, false);
                }
            });

            // Limpiar resaltado al escribir
            numsol.addEventListener('input', () => resaltarCampo(numsol, false));
            anio.addEventListener('input', () => resaltarCampo(anio, false));
        });
    </script>
</body>
</html>