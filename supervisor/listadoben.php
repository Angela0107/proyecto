 
 <?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'diseño_ayudas';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

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
 <html lang="en">
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
 <body>
    
  <div class="main_container">
                <h2>Lista de Beneficiarios</h2><div class="form2">
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
                                            <td></td> <td></td> <td></td> <td></td> 
                                        
                                        </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
            </div>
        </div></div>
 </body>
 
 </html>
 