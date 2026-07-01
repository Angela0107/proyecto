<?php
// Configuración dinámica de PDO leyendo las variables de entorno de Render / Aiven
$servername = getenv('DB_HOST')     ?: "localhost"; 
$username   = getenv('DB_USER')     ?: "root"; 
$password   = getenv('DB_PASSWORD') ?: ""; 
$dbname     = getenv('DB_NAME')     ?: "diseño_ayudas"; 
$port       = getenv('DB_PORT')     ?: "3306"; 

try {
    // Inicializar conexión formal mediante el controlador PDO
    $conexion = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error crítico de conexión en el módulo de proveedores: " . $e->getMessage());
}

// ===== MANEJO DE PETICIONES AJAX PARA VALIDAR RIF =====
if (isset($_GET['action']) && $_GET['action'] === 'verificar_rif' && isset($_GET['rif'])) {
    header('Content-Type: application/json');
    
    $rif = strtoupper(trim($_GET['rif']));
    
    if (!preg_match('/^[VEJGPC][1-9]\d{7,8}-\d$/', $rif)) {
        echo json_encode(['valid' => false, 'message' => 'Formato de RIF inválido']);
        exit;
    }
    
    try {
        $rifNumero = substr($rif, 1);
        
        $stmt = $conexion->prepare("SELECT id_prov, ced_prv FROM proveedor WHERE SUBSTRING(ced_prv, 2) = :rif_num LIMIT 1");
        $stmt->bindParam(':rif_num', $rifNumero, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode([
                'valid' => false, 
                'message' => 'Este número ya está registrado actualmente con la letra: ' . $row['ced_prv']
            ]);
        } else {
            echo json_encode(['valid' => true, 'message' => 'RIF disponible']);
        }
    } catch (PDOException $e) {
        echo json_encode(['valid' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    }
    exit;
}

// Carga segura de datos utilizando el puente PDO activo
$proveedores = $conexion->query("SELECT * FROM proveedor")->fetchAll(PDO::FETCH_ASSOC);

$provcuentas = [];
if (isset($_POST['ced_prv'])) {
    $ced_prv = $_POST['ced_prv'];
    $provcuentas = $conexion->prepare("SELECT * FROM provcuenta WHERE ced_prv = :ced_prv");
    $provcuentas->bindParam(':ced_prv', $ced_prv);
    $provcuentas->execute();
    $provcuentas = $provcuentas->fetchAll(PDO::FETCH_ASSOC);
}

$sql = "SELECT `id_prov`, `nac_prv`, `ced_prv`, `nom_prv` FROM `proveedor`";
$stmt = $conexion->query($sql);
$proveedores_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'nav/index.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Proveedor</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 16px; color: #333; }
        input[type="text"], select {
            width: 100%; padding: 10px; border: 1px solid #ccc;
            border-radius: 4px; box-sizing: border-box; transition: border-color 0.3s;
        }
        input[type="text"]:focus, select:focus { border-color: #007BFF; outline: none; }
        .submit-button {
            background-color: #007BFF; color: white; border: none;
            padding: 12px 20px; border-radius: 6px; cursor: pointer;
            font-size: 16px; width: 100%; font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: background 0.3s;
        }
        .submit-button:hover { background-color: #0056b3; }
        
        .container2 {
            margin-left: 1%; margin-right: 0px; padding: 25px; 
            border: 1px solid #e9ecef;
            border-radius: 12px; background-color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); text-align: center;
        }
        .container {
            margin-right: 20px; width: 85%; margin-top: 3%; margin-left: 6%;
            padding: 25px; border: 1px solid #e9ecef;
            border-radius: 12px; background-color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .form-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .form-row .col-md-6 { flex: 0 0 48%; }
        .titulop { font-family: 'Segoe UI', cursive, sans-serif; color: #444; }
        
        /* DataTables */
        table.dataTable thead th { background-color: #007BFF; color: white; }
        table.dataTable tbody tr:hover { background-color: #f1f4f8; }
        .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_length { margin-bottom: 15px; }

        /* Temas */
        body.light-theme { background-color: #f4f6f9; color: #333; }
        body.dark-theme { background-color: #1a1c23; color: #e0e0e0; }
        body.dark-theme .container, body.dark-theme .container2 {
            background-color: #242630; border-color: #353740;
        }
        body.dark-theme input[type="text"], body.dark-theme select {
            background-color: #2c2f3a; border-color: #444755; color: #fff;
        }
        body.dark-theme label, body.dark-theme h1, body.dark-theme .titulop, body.dark-theme .dataTables_wrapper .dataTables_info, body.dark-theme .dataTables_wrapper .dataTables_paginate {
            color: #e0e0e0;
        }
        body.dark-theme table.dataTable thead th { background-color: #353b4e; color: #fff; }
        body.dark-theme table.dataTable { background-color: #242630; color: #fff; }
        body.dark-theme table.dataTable tbody tr { background-color: #242630; }
        body.dark-theme table.dataTable tbody tr:hover { background-color: #2c2f3a; }
        
        #themeToggle { position: absolute; top: 20px; right: 20px; z-index: 1000; background-color: transparent; border: 1px solid #ccc; }
        body.dark-theme #themeToggle { border-color: #444; color: #fff; }

        /* Indicadores de validación */
        input.valid {
            border-color: #28a745 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        input.invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .error-message {
            color: #dc3545; font-size: 12px; margin-top: 5px;
            display: none; text-align: left; font-weight: 500;
        }
        .error-message.show { display: block; animation: fadeIn 0.3s; }
        
        .char-counter { font-size: 11px; text-align: right; margin-top: 2px; color: #888; }
        .char-counter.warning { color: #ffc107; }
        .char-counter.danger { color: #dc3545; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>

    <script>
        let rifValidationTimeout = null;
        let rifUltimoEstadoValido = null;

        function autofillRIFConNacionalidad() {
            const nac = document.getElementById('nac_prv');
            const rif = document.getElementById('ced_prv');
            if (!nac || !rif) return;
            
            nac.addEventListener('change', function() {
                const letra = this.value.toUpperCase();
                rif.value = letra;
                rif.classList.remove('valid', 'invalid');
                const msg = document.getElementById('error-ced_prv');
                if (msg) { msg.classList.remove('show'); msg.textContent = ''; }
                rifUltimoEstadoValido = null;
                setTimeout(() => { rif.focus(); rif.setSelectionRange(1, 1); }, 10);
                rif.dispatchEvent(new Event('input'));
            });
        }

        function validarRIF(input) {
            let valor = input.value.toUpperCase();
            const mensaje = document.getElementById('error-ced_prv');
            rifUltimoEstadoValido = null;
            
            if (!valor) {
                input.classList.remove('valid', 'invalid');
                mensaje.classList.remove('show'); mensaje.textContent = '';
                return true;
            }
            
            const regex = /^[VEJGPC][1-9]\d{7,8}-\d$/;
            
            if (regex.test(valor)) {
                input.classList.remove('invalid');
                input.classList.add('valid');
                mensaje.classList.remove('show');
                clearTimeout(rifValidationTimeout);
                rifValidationTimeout = setTimeout(() => verificarRIFEnBD(valor, input, mensaje), 300);
                return true;
            } else {
                clearTimeout(rifValidationTimeout);
                input.classList.remove('valid');
                const p = valor.charAt(0);
                if (!/^[VEJGPC]$/.test(p) && valor.length > 0) {
                    input.classList.add('invalid');
                    mensaje.textContent = 'El RIF debe iniciar con V, E, J, G, P o C';
                    text_content_show(mensaje); return false;
                }
                const s = valor.replace(/^[VEJGPC]/, '');
                const guion = s.includes('-');
                const num = guion ? s.split('-')[0] : s;
                if (num && num.charAt(0) === '0') {
                    input.classList.add('invalid');
                    mensaje.textContent = 'El número no puede iniciar con 0';
                    text_content_show(mensaje); return false;
                }
                if (valor.length > 0) {
                    input.classList.add('invalid');
                    mensaje.textContent = 'Formato: Letra + 8-9 dígitos (sin 0) + guion + dígito';
                    text_content_show(mensaje); return false;
                }
                mensaje.classList.remove('show'); return true;
            }
        }

        function text_content_show(element) {
            element.classList.add('show');
        }

        function verificarRIFEnBD(rif, input, mensaje) {
            fetch(`?action=verificar_rif&rif=${encodeURIComponent(rif)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.valid) {
                        input.classList.add('valid'); input.classList.remove('invalid');
                        mensaje.classList.remove('show'); mensaje.textContent = '';
                        rifUltimoEstadoValido = true;
                    } else {
                        input.classList.add('invalid'); input.classList.remove('valid');
                        mensaje.textContent = data.message;
                        mensaje.classList.add('show');
                        rifUltimoEstadoValido = false;
                    }
                })
                .catch(() => { rifUltimoEstadoValido = null; });
        }

        function validarTeclaRIF(e, input) {
            const code = e.charCode || e.keyCode;
            let val = input.value.toUpperCase();
            if (code === 0 || code === 8 || code === 9 || code === 13 || code === 46 || (code >= 37 && code <= 40)) return true;
            const char = String.fromCharCode(code);
            if (val.length === 0) {
                if (/^[VEJGPCvejpgc]$/.test(char)) { e.preventDefault(); input.value = char.toUpperCase(); return false; }
                return false;
            }
            if (val.length === 1 && /^[VEJGPC]$/.test(val)) {
                if (char >= '1' && char <= '9') return true;
                e.preventDefault(); return false;
            }
            const s = val.replace(/^[VEJGPC]/, '');
            const guion = s.includes('-');
            const num = guion ? s.split('-')[0] : s;
            if (!guion && num.length < 9) {
                if (char >= '0' && char <= '9') return true;
                if (char === '-' && (num.length === 8 || num.length === 9)) return true;
            }
            if (guion && (s.split('-')[1] || '').length === 0 && char >= '0' && char <= '9') return true;
            e.preventDefault(); return false;
        }

        function validarNombre(input) {
            const orig = input.value;
            const clean = orig.substring(0, 50).replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
            if (orig !== clean) input.value = clean;
            const msg = document.getElementById('error-nom_prv');
            const act = input.value.trim();
            const len = input.value.length;
            actualizarContadorNombre(len);
            if (!act) { input.classList.remove('valid', 'invalid'); msg.classList.remove('show'); return true; }
            if (/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/.test(act) && len <= 50) {
                input.classList.add('valid'); input.classList.remove('invalid'); msg.classList.remove('show'); return true;
            }
            input.classList.add('invalid'); input.classList.remove('valid');
            msg.textContent = len > 50 ? 'Máximo 50 caracteres' : 'Solo letras y espacios';
            msg.classList.add('show'); return false;
        }
        
        function actualizarContadorNombre(len) {
            const c = document.getElementById('contadorNom');
            if (!c) return;
            c.textContent = `${len} / 50`;
            c.className = 'char-counter' + (len > 45 ? ' danger' : len > 40 ? ' warning' : '');
        }

        function esSoloCeros(v) { const d = v.replace(/[^0-9]/g, ''); return d.length > 0 && /^0+$/.test(d); }

        function formatearNumeroCuenta(e) {
            const i = e.target;
            let v = i.value.replace(/[^0-9-]/g, '');
            i.dataset.allZeros = esSoloCeros(v) ? 'true' : 'false';
            let f = ''; const p = v.split('-');
            for (let k = 0; k < p.length && k < 5; k++) { if (k > 0) f += '-'; f += p[k].substring(0, 4); }
            if (f.length > 24) f = f.substring(0, 24);
            i.value = f;
            const msg = document.getElementById('error-nro_cta');
            if (esSoloCeros(f) && f.replace(/[^0-9]/g, '').length >= 4) {
                i.classList.add('invalid'); i.classList.remove('valid');
                if (msg) { msg.textContent = 'No puede ser solo ceros'; msg.classList.add('show'); }
            } else if (f.length > 0) {
                i.classList.remove('invalid'); i.classList.add('valid');
                if (msg) msg.classList.remove('show');
            }
        }

        function validarFormulario(e) {
            const ced = document.getElementById("ced_prv");
            const nom = document.getElementById("nom_prv");
            const nro = document.getElementById("nro_cta");
            
            const rifVal = ced.value.toUpperCase().trim();
            if (!/^[VEJGPC][1-9]\d{7,8}-\d$/.test(rifVal)) {
                Swal.fire({ icon: 'warning', title: 'Formato Incorrecto', text: 'El RIF debe tener formato válido. Ej: J12345678-9' });
                ced.focus(); return false;
            }
            
            if (rifUltimoEstadoValido === false) {
                Swal.fire({ icon: 'error', title: 'RIF Duplicado', text: 'Este número de RIF ya se encuentra registrado con otra letra o tipo.' });
                ced.focus(); return false;
            }
            
            const nomVal = nom.value.trim();
            if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/.test(nomVal)) {
                Swal.fire({ icon: 'warning', title: 'Nombre Inválido', text: 'El nombre solo permite letras y espacios' });
                nom.focus(); return false;
            }
            
            const ctaVal = nro.value.replace(/[^0-9]/g, '');
            if (/^0+$/.test(ctaVal) && ctaVal.length > 0) {
                Swal.fire({ icon: 'warning', title: 'Cuenta Inválida', text: 'El número de cuenta no puede ser solo ceros' });
                nro.focus(); return false;
            }
            if (!/^(\d{4}-?){4}\d{0,4}$/.test(nro.value)) {
                Swal.fire({ icon: 'warning', title: 'Formato de Cuenta', text: 'Debe ser: 1234-1234-1234-1234-1234' });
                nro.focus(); return false;
            }
            return true;
        }

        function convertirAMayusculas(input) {
            const pos = input.selectionStart;
            input.value = input.value.toUpperCase();
            input.setSelectionRange(pos, pos);
        }
    </script>
</head>
<body class="light-theme">
    <button id="themeToggle" class="btn btn-outline-secondary"><i class="fas fa-moon"></i> Modo Oscuro</button>
    
    <div style="margin-left: 19%; display: flex; flex-direction: column;">
        <div class="container-fluid mt-4" style="width: 980px;">
            <h2 class="mb-4 titulop">Lista de Proveedores</h2>
            <div class="card shadow-sm p-3">
                <table id="ayudasTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                    <thead><tr><th>N°</th><th>Nac.</th><th>Cédula</th><th>Nombre</th></tr></thead>
                    <tbody>
                        <?php if (!empty($proveedores_lista)): ?>
                            <?php foreach ($proveedores_lista as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["id_prov"]) ?></td>
                                    <td><?= htmlspecialchars($row["nac_prv"]) ?></td>
                                    <td><?= htmlspecialchars($row["ced_prv"]) ?></td>
                                    <td><?= htmlspecialchars($row["nom_prv"]) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No hay proveedores registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="container">
            <div class="container2">
                <h1 class="titulop mb-4">Registrar Proveedor</h1>
                <form action="insertar_proveedor.php" method="POST" onsubmit="return validarFormulario(event);">
                    <div class="form-row">
                        <div class="col-md-6">
                            <label for="nac_prv">Nacionalidad:</label>
                            <select id="nac_prv" name="nac_prv" required>
                                <option value="C">Comuna</option>
                                <option value="E">Extranjero</option>
                                <option value="G">Gobierno</option>
                                <option value="J">Jurídico</option>
                                <option value="P">Pasaporte</option>
                                <option value="V">Venezolano</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ced_prv">RIF:</label>
                            <input type="text" id="ced_prv" name="ced_prv" required maxlength="12" 
                                title="Ej: J12345678-9"
                                oninput="validarRIF(this)" onkeypress="return validarTeclaRIF(event, this)" onblur="validarRIF(this)" autocomplete="off" placeholder="Ej: J31330431-6">
                            <div id="error-ced_prv" class="error-message"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nom_prv">Nombre:</label>
                        <input type="text" id="nom_prv" name="nom_prv" required maxlength="50"
                            oninput="validarNombre(this); convertirAMayusculas(this);"
                            onkeypress="return /[A-Za-zÁÉÍÓÚáéíóúÑñ\s]/.test(String.fromCharCode(event.charCode))" autocomplete="off">
                        <small id="contadorNom" class="char-counter">0 / 50</small>
                        <div id="error-nom_prv" class="error-message"></div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6">
                            <label for="cod_ban">Banco:</label>
                            <select id="cod_ban" name="cod_ban" required>
                                <option value="1">BANCAMIGA</option><option value="2">BANCO NACIONAL DE CREDITO</option>
                                <option value="3">BANESCO</option><option value="4">BFC</option><option value="5">BICENTENARIO</option>
                                <option value="6">BNC</option><option value="7">CARONI</option><option value="8">MERCANTIL</option>
                                <option value="9">PROVINCIAL</option><option value="10">SOFITASA</option><option value="11">TESORO</option>
                                <option value="12">VENEZUELA</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tip_cta">Tipo Cuenta:</label>
                            <select id="tip_cta" name="tip_cta" required>
                                <option value="CORRIENTE">CORRIENTE</option>
                                <option value="AHORRO">AHORRO</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nro_cta">Número Cuenta:</label>
                        <input type="text" id="nro_cta" name="nro_cta" required maxlength="24" 
                            oninput="formatearNumeroCuenta(event);" placeholder="1234-1234-1234-1234-1234">
                        <div id="error-nro_cta" class="error-message"></div>
                    </div>

                    <input type="submit" class="submit-button mt-3" value="Registrar Proveedor">
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#ayudasTable').DataTable({
                responsive: true,
                language: {
                    sProcessing: "Procesando...",
                    sLengthMenu: "Mostrar _MENU_ registros",
                    sZeroRecords: "No se encontraron resultados",
                    sEmptyTable: "Ningún dato disponible en esta tabla",
                    sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                    sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0",
                    sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
                    sSearch: "Buscar:",
                    oPaginate: {
                        sFirst: "Primero",
                        sLast: "Último",
                        sNext: "Siguiente",
                        sPrevious: "Anterior"
                    }
                },
                pageLength: 5, lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "Todos"]],
                order: [[0, 'asc']]
            });
        });
        
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.className = savedTheme + '-theme';
        document.getElementById('themeToggle').addEventListener('click', () => {
            const n = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
            document.body.className = n + '-theme'; localStorage.setItem('theme', n);
            document.getElementById('themeToggle').innerHTML = n === 'dark' ? '<i class="fas fa-sun"></i> Modo Claro' : '<i class="fas fa-moon"></i> Modo Oscuro';
        });

        document.addEventListener('DOMContentLoaded', function() {
            autofillRIFConNacionalidad();
            const rif = document.getElementById('ced_prv');
            if (rif) rif.addEventListener('keydown', e => { if ((e.key==='Backspace'||e.key==='Delete') && rif.value.length<=1) { e.preventDefault(); return false; }});
            if (document.getElementById('nom_prv')) actualizarContadorNombre(document.getElementById('nom_prv').value.length);
        });
    </script>
</body>
</html>
