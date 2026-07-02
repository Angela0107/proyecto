<?php
require_once "./db.php";

$sql = "SELECT t.des_tpo, s.numsol, s.fechasol, s.ced_ben, s.descripcion, s.nom_ben, s.idbenefi
        FROM beneficiario b 
        INNER JOIN solicitud s ON s.idbenefi = b.ids_bene
        INNER JOIN tiposolicitud t ON t.cod_tip = s.codsolicitud 
        ORDER BY s.fechasol DESC;";
$result = $conn->query($sql);

include 'nav/index.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planilla Beneficiarios</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap4.min.css">

    <style>
        body.light-theme {
            background-color: #f5f7fa;
            color: #333;
        }

        /* ===== Tema oscuro ===== */
        body.dark-theme {
            background-color: #121212;
            color: #e0e0e0;
        }

        /* Contenedores */
        .wrapper {
            padding: 20px;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 1200px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        body.light-theme .wrapper {
            background-color: #ffffff;
        }

        body.dark-theme .wrapper {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
            border: 1px solid #333;
        }

        /* Tabla - modo claro */
        .table th {
            background-color: #2e4ead;
            color: white;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tr:hover {
            background-color: #f1f1f1;
        }

        /* Tabla - modo oscuro */
        body.dark-theme .table {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
        }

        body.dark-theme .table th {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border-color: #444 !important;
        }

        body.dark-theme .table td,
        body.dark-theme .table th {
            border-color: #444 !important;
        }

        body.dark-theme .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        body.dark-theme .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Botones */
        body.dark-theme .btn-primary {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
        }

        body.dark-theme .btn-primary:hover {
            background-color: #0b5ed7 !important;
            border-color: #0a58ca !important;
        }

        body.dark-theme .btn-outline-secondary {
            color: #bbb !important;
            border-color: #444 !important;
        }

        body.dark-theme .btn-outline-secondary:hover {
            background-color: #333 !important;
            color: #fff !important;
        }

        /* Filtro de búsqueda */
        body.dark-theme .dataTables_filter input {
            background-color: #2d2d2d !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }

        /* Botón de tema */
        #themeToggle {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        h2 {
            color: #343a40;
            margin-bottom: 20px;
        }

        body.dark-theme h2 {
            color: #e0e0e0 !important;
        }
    </style>
</head>
<body class="light-theme">
    <!-- Botón de cambio de tema -->
    <button id="themeToggle" class="btn btn-outline-secondary">
        <i class="fas fa-moon"></i> Modo Oscuro
    </button>

    <div class="wrapper" style="margin-left: 22%; margin-top:9%;">
        <h2 class="text-center">Planilla Beneficiarios</h2>

        <table id="ayudasTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Tipo de Ayuda</th>
                    <th>Nota</th>
                    <th>Botón</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["numsol"]) ?></td>
                            <td><?= htmlspecialchars($row["fechasol"]) ?></td>
                            <td><?= htmlspecialchars($row["nom_ben"]) ?></td>
                            <td><?= htmlspecialchars($row["ced_ben"]) ?></td>
                            <td><?= htmlspecialchars($row["des_tpo"]) ?></td>
                            <td><?= htmlspecialchars($row["descripcion"]) ?></td>
                            <td>
                                <a href="planilla2.php?numsol=<?= urlencode($row['numsol']) ?>&idbenefi=<?= urlencode($row['idbenefi']) ?>" 
                                   class="btn btn-primary btn-sm">
                                    Ver planilla
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay ayudas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="text-center mt-4">
            <form action="estadisticas.php" method="post" style="display:inline;">
                <button type="submit" class="btn btn-primary">Volver</button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#ayudasTable').DataTable({
                responsive: true,
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "No hay registros",
                    zeroRecords: "No se encontraron coincidencias",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                }
            });

            // Tema claro/oscuro
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
                if (theme === 'dark') {
                    btn.innerHTML = '<i class="fas fa-sun"></i> Modo Claro';
                } else {
                    btn.innerHTML = '<i class="fas fa-moon"></i> Modo Oscuro';
                }
            }
        });
    </script>
</body>
</html>