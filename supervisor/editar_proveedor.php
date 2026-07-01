<?php
$servername = getenv("DB_HOST")     ?: "localhost";
$username   = getenv("DB_USER")     ?: "root";
$password   = getenv("DB_PASSWORD") ?: "";
$dbname     = getenv("DB_NAME")     ?: "diseño_ayudas";
$port       = getenv("DB_PORT")     ?: "3306";

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

function crearProveedor($nacio, $cedprov, $nomprov)
{
    global $conn;

    $sql_check = "SELECT * FROM proveedor WHERE cedprov = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $cedprov);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo '<div container><div class="error-message">Error: La cédula del proveedor ya está registrada.</div>';
        $stmt_check->close();
        return;
    }

    $stmt_check->close();

    $sql = "INSERT INTO proveedor (nacio, cedprov, nomprov) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nacio, $cedprov, $nomprov);

    if ($stmt->execute()) {
        $id_prov = $stmt->insert_id;
        $stmt->close();
        return $id_prov;
    } else {
        echo "Error: " . $stmt->error;
        $stmt->close();
        return null;
    }
}
function obtenerProveedores()
{
    global $conn;
    $sql = "SELECT p.id_prov, p.nacio, p.cedprov, p.nomprov, pc.codban, pc.nrocuenta, pc.tipcuenta 
            FROM proveedor p
            LEFT JOIN provcuenta pc ON p.cedprov = pc.cedprov;";
    $result = $conn->query($sql);

    return $result;
}

function actualizarProveedor($id_prov, $nacio, $cedprov, $nomprov, $codban, $nrocuenta, $tipcuenta)
{
    global $conn;

    $sql_prov = "UPDATE proveedor SET nacio=?, cedprov=?, nomprov=? WHERE id_prov=?";
    $stmt_prov = $conn->prepare($sql_prov);
    $stmt_prov->bind_param("sssi", $nacio, $cedprov, $nomprov, $id_prov);
    $stmt_prov->execute();

    $sql_cuenta = "UPDATE provcuenta SET codban=?, nrocuenta=?, tipcuenta=? WHERE cedprov=?";
    $stmt_cuenta = $conn->prepare($sql_cuenta);
    $stmt_cuenta->bind_param("ssss", $codban, $nrocuenta, $tipcuenta, $cedprov);
    $stmt_cuenta->execute();

    if ($stmt_prov->affected_rows > 0 || $stmt_cuenta->affected_rows > 0) {
        echo "Proveedor y cuenta actualizados exitosamente.";
    } else {
        echo "No se realizaron cambios.";
    }

    $stmt_prov->close();
    $stmt_cuenta->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['crear'])) {
        $id_prov = crearProveedor($_POST['nacio'], $_POST['cedprov'], $_POST['nomprov']);
        if ($id_prov) {
            header("Location: proveedores.php?id_prov=$id_prov");
            exit();
        }
    } elseif (isset($_POST['actualizar'])) {
        actualizarProveedor($_POST['id_prov'], $_POST['nacio'], $_POST['cedprov'], $_POST['nomprov'], $_POST['codban'], $_POST['nrocuenta'], $_POST['tipcuenta']);
    }
}

$proveedores = obtenerProveedores();

include 'nav/index.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">


    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap.js"></script>
    <title>Listado de Ayudas Finalizadas</title>
</head>

<body>
    <div class="wrapper">
        <div class="main_container2">
            <div class="item">
                <h2>Lista de Proveedores</h2>
                <table id="proveedoresTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nacionalidad</th>
                            <th>Cédula</th>
                            <th>Nombre</th>
                            <th>Banco</th>
                            <th>Número de Cuenta</th>
                            <th>Tipo de Cuenta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($proveedores->num_rows > 0) {
                            while ($row = $proveedores->fetch_assoc()) {
                                echo "<tr>
                            <td>{$row['id_prov']}</td>
                            <td>{$row['nacio']}</td>
                            <td>{$row['cedprov']}</td>
                            <td>{$row['nomprov']}</td>
                            <td>{$row['codban']}</td>
                            <td>{$row['nrocuenta']}</td>
                            <td>{$row['tipcuenta']}</td>
                            <td>
                                <button onclick='editarProveedor({$row['id_prov']}, \"{$row['nacio']}\", \"{$row['cedprov']}\", \"{$row['nomprov']}\", \"{$row['codban']}\", \"{$row['nrocuenta']}\", \"{$row['tipcuenta']}\")'>Editar</button>
                            </td>
                        </tr>";
                            }
                        } else {
                            echo "<tr>
                        <td colspan='8'>No hay proveedores por mostrar.</td>
                    </tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <script>
                    $(document).ready(function() {
                        $('#proveedoresTable').DataTable({
                            language: {
                                search: "Buscar:",
                                lengthMenu: "Mostrar _MENU_ registros",
                                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                paginate: {
                                    first: "Primero",
                                    last: "Último",
                                    next: "Siguiente",
                                    previous: "Anterior"
                                }
                            }
                        });
                    });

                    function editarProveedor(id_prov, nacio, cedprov, nomprov, codban, nrocuenta, tipcuenta) {
                        document.querySelector('input[name="id_prov"]').value = id_prov;
                        document.querySelector('select[name="nacio"]').value = nacio;
                        document.querySelector('input[name="cedprov"]').value = cedprov;
                        document.querySelector('input[name="nomprov"]').value = nomprov;
                        document.querySelector('input[name="codban"]').value = codban;
                        document.querySelector('input[name="nrocuenta"]').value = nrocuenta;
                        document.querySelector('input[name="tipcuenta"]').value = tipcuenta;
                        document.querySelector('input[name="actualizar"]').style.display = 'inline';
                        document.querySelector('input[name="crear"]').style.display = 'none';
                    }
                </script>
            </div>

            <div class="container mt-5">
                <h2>Formulario de Proveedor</h2>
                <form method="POST" action="">
                    <input type="hidden" name="id_prov" value="">
                    <div class="form-row">
                        <div class="col-md-6">
                            <label>Nacionalidad:</label>
                            <select name="nacio" required class="form-control">
                                <option value="1">Venezolano</option>
                                <option value="2">Extranjero</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="cedprov">Cédula</label>
                            <input type="text" class="form-control" id="cedprov" name="cedprov" maxlength="9" pattern="[0-9]{1,9}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-12">
                            <label for="nomprov">Nombre completo:</label>
                            <input type="text" class="form-control" name="nomprov" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6">
                            <label for="parroquia">Parroquia:</label>
                            <select name="codban" id="parroquia" required>
                                <option value="">Seleccione un banco</option>
                                <?php
                                $parroquias = $conn->query("SELECT * FROM bancos")->fetch_all(MYSQLI_ASSOC);
                                foreach ($parroquias as $parroquia): ?>
                                    <option value="<?= $parroquia['codban'] ?>"><?= $parroquia['nombanco'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <br><br>

                        </div>

                        <div class="col-md-6">
                            <label for="nrocuenta">Número de Cuenta:</label>
                            <input type="text" class="form-control" name="nrocuenta" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-12">

                            <label for="tipcuenta">Tipo de Cuenta:</label>
                            <input type="text" class="form-control" name="tipcuenta" required>
                        </div>
                    </div>
                    <center>
                        <input type="submit" name="crear" value="Crear Proveedor" style="display: inline; margin-top: 30px;">
                        <input type="submit" name="actualizar" value="Actualizar Proveedor" style="display: none; margin-top: 30px;">
                    </center>
                </form>
            </div>
        </div>
    </div>
    </div>
</body>

</html>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
        font-size: 15px;
    }

    .wrapper .top_navbar {
        width: calc(100% - 20px);
        height: 90px;
        display: flex;
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 1000;

    }

    h1,
    h2 {
        color: #333;
    }

    form {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .form2 {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .form-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .form-group {
        flex: 1;
        margin-right: 1rem;
    }

    .form-group:last-child {
        margin-right: 0;
    }

    label {
        display: block;
        margin: 10px 0 5px;
    }

    input[type="text"],
    input[type="email"],
    select {
        width: 98%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    input[type="submit"] {
        background-color: #2e4ead;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
    }

    input[type="submit"]:hover {
        background-color: #007bff;
    }

    table {
        width: 96%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    button {
        background-color: #2e4ead;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #007bff;
    }




    .hidden {
        display: none;
    }
</style>
<?php
$conn->close();
?>