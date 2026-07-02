<?php
require_once "../db.php";

$sql = "SELECT s.`numsol`, s.idbenefi, s.ced_ben, s.`descripcion`, s.`codsolicitud`, s.`fechasol`, s.`observa`, s.`fechaent`,
s.`fecpago`,IF(s.`estatus`= 0, 'EN REVISIÓN', IF(s.`estatus`= 1, 'PUNTO DE CUENTA',IF(s.`estatus`= 4, 'ANULADO',IF(s.`estatus`= 5, 'RECHAZADO', IF(s.`estatus`= 3, 'APROBADOS', NULL))))) AS estatus,
s.ptocuenta, b.nom_ben, b.ids_bene, t.cod_tip, t.des_tpo FROM `solicitud` s
INNER JOIN beneficiario b ON s.idbenefi = b.ids_bene
INNER JOIN tiposolicitud t ON s.codsolicitud = t.cod_tip;";

$result_combined = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Ayudas Finalizadas</title>

    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap4.min.css">

    <style>
        body.light-theme {
            background-color: #f5f7fa;
            color: #333;
        }

        body.dark-theme {
            background-color: #121212;
            color: #e0e0e0;
        }

        h2 {
            color: #343a40;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: #b7b29b;
            color: white;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }

        /* ===== Tema oscuro ===== */
        body.dark-theme .wrapper,
        body.dark-theme .main_container,
        body.dark-theme .form2,
        body.dark-theme .dataTables_wrapper {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }

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

        body.dark-theme input[type="text"],
        body.dark-theme input[type="tel"],
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

        body.dark-theme label,
        body.dark-theme h2,
        body.dark-theme .text-center {
            color: #e0e0e0 !important;
        }

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

        #themeToggle {
            position: absolute;
            margin-top: 1%;
            right: 20px;
            z-index: 1000;
        }

        #voiceSearchContainer {
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="light-theme">
    <div style="margin-top:8%; margin-left:18%; width:80%;">
        <div class="container mt-4">
            <button id="themeToggle" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-moon"></i> Modo Oscuro
            </button>

            <h2>Listado de ayudas</h2>

            <table id="ayudasTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Descripción</th>
                        <th>Tipo de Ayuda</th>
                        <th>Estatus</th>
                        <th>N° Punto de cuenta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_combined && $result_combined->num_rows > 0) {
                        while ($row = $result_combined->fetch_assoc()) {
                            $tipo = $row["estatus"];
                            $backgroundColor = "transparent";
                            if ($tipo == "EN REVISIÓN") {
                                $backgroundColor = "#76bca6";
                            } elseif ($tipo == "PUNTO DE CUENTA") {
                                $backgroundColor = "rgb(205 191 73);";
                            } elseif ($tipo == "APROBADOS") {
                                $backgroundColor = "#8BC34A";
                            } elseif ($tipo == "ANULADO") {
                                $backgroundColor = "#ef3e3e";
                            } elseif ($tipo == "RECHAZADO") {
                                $backgroundColor = "#ff00009c";
                            }

                            echo "<tr>
                                <td>" . htmlspecialchars($row["numsol"] ?? '') . "</td>
                                <td>" . htmlspecialchars($row["fechasol"] ?? '') . "</td>
                                <td>" . htmlspecialchars($row["nom_ben"] ?? '') . "</td>
                                <td>" . htmlspecialchars($row["ced_ben"] ?? '') . "</td>
                                <td>" . htmlspecialchars($row["descripcion"] ?? '') . "</td>
                                <td>" . htmlspecialchars($row["des_tpo"] ?? '') . "</td>
                                <td style='background-color: {$backgroundColor};'>" . htmlspecialchars($row["estatus"] ?? '') . "</td>
                                <td>" . htmlspecialchars($row["ptocuenta"] ?? '') . "</td>
                            </tr>";
                        }
                    } else {
                      echo "<tr>
                                <td>No hay ayudas finalizadas.</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap4.min.js"></script>

        <script>
            $(document).ready(function() {
                // Inicializar DataTables
                $('#ayudasTable').DataTable({
                    responsive: true,
                    language: {
                        search: "Buscar:",
                        lengthMenu: "Mostrar _MENU_ registros",
                        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        paginate: {
                            first: "Primero",
                            last: "Último",
                            next: "Siguiente",
                            previous: "Anterior"
                        },
                        emptyTable: "No hay datos disponibles",
                        infoEmpty: "Mostrando 0 a 0 de 0 registros"
                    }
                });

                // Tema
                const savedTheme = localStorage.getItem('theme') || 'light';
                document.body.className = savedTheme + '-theme';
                updateToggleButton(savedTheme);

                $('#themeToggle').on('click', function() {
                    const isDark = document.body.classList.contains('dark-theme');
                    const newTheme = isDark ? 'light' : 'dark';
                    document.body.className = newTheme + '-theme';
                    localStorage.setItem('theme', newTheme);
                    updateToggleButton(newTheme);
                });

                function updateToggleButton(theme) {
                    const btn = $('#themeToggle');
                    if (theme === 'dark') {
                        btn.html('<i class="fas fa-sun"></i> Modo Claro');
                    } else {
                        btn.html('<i class="fas fa-moon"></i> Modo Oscuro');
                    }
                }

                // === Reconocimiento de voz ===
                let recognition;
                if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    recognition = new SpeechRecognition();
                    recognition.continuous = false;
                    recognition.interimResults = false;
                    recognition.lang = 'es-ES';

                    recognition.onresult = function(event) {
                        const transcript = event.results[0][0].transcript.trim();
                        $('#voiceStatus').text('Texto reconocido: "' + transcript + '"');

                        const searchInput = $('#ayudasTable_filter input[type="search"]');
                        if (searchInput.length) {
                            searchInput.val(transcript).trigger('input');
                        }
                    };

                    recognition.onerror = function(event) {
                        let msg = 'Error: ';
                        switch(event.error) {
                            case 'not-allowed':
                                msg += 'Acceso al micrófono denegado. Verifica los permisos del sitio (usa http://localhost).';
                                break;
                            case 'audio-capture':
                                msg += 'No se detectó micrófono.';
                                break;
                            default:
                                msg += event.error;
                        }
                        $('#voiceStatus').text(msg);
                        console.error('SpeechRecognition error:', event.error);
                    };

                    recognition.onend = function() {
                        $('#startVoiceBtn')
                            .removeClass('btn-danger')
                            .addClass('btn-outline-info');
                        $('#startVoiceBtn i')
                            .removeClass('fa-stop')
                            .addClass('fa-microphone');
                    };

                    $('#startVoiceBtn').on('click', function() {
                        if (!recognition) return;

                        const isListening = $('#startVoiceBtn').hasClass('btn-danger');
                        if (isListening) {
                            recognition.stop();
                        } else {
                            $('#voiceStatus').text('Escuchando...');
                            recognition.start();
                            $('#startVoiceBtn')
                                .removeClass('btn-outline-info')
                                .addClass('btn-danger');
                            $('#startVoiceBtn i')
                                .removeClass('fa-microphone')
                                .addClass('fa-stop');
                        }
                    });
                } else {
                    $('#startVoiceBtn').prop('disabled', true).attr('title', 'Reconocimiento de voz no soportado');
                    $('#voiceStatus').text('Tu navegador no soporta búsqueda por voz.');
                }
            });
        </script>
    </div>
</body>

</html>
