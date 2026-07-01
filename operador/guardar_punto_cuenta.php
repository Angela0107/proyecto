<?php

session_start();

$id_usu = $_SESSION['id_usu'];
// Configuración de la conexión a la base de datos
$servername = "localhost"; // Cambia esto si es necesario
$username = "root"; // Cambia esto por tu usuario
$password = ""; // Cambia esto por tu contraseña
$dbname = "diseño_ayudas"; // Cambia esto por tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibir los datos del formulario
$monto = $_POST['monto'];
$fecha = $_POST['fecha'];
$argumento = $_POST['argumento'];
$solicitudes = $_POST['argumento']; // solicitudes
$banco = $_POST['banco'];
$nrocta = $_POST['nrocta'];
$tip_cta = $_POST['tip_cta'];
$nom_prv = $_POST['nom_prv'];
$nac_prv = $_POST['nac_prv'];
$ced_prv = $_POST['ced_prv'];
$tipo = $_POST['tipo'];
$asunto = $_POST['asunto'];

// Convertir la fecha a timestamp
$timestamp = strtotime($fecha);

// Obtener el mes y el año
$mes = date('m', $timestamp); // Mes en formato numérico (01 a 12)
$ano = date('Y', $timestamp); // Año en formato de 4 dígitos



// Consulta SQL para obtener datos de provcuenta
$sql_provcuenta = "SELECT numpt from numpt;";

$result_provcuenta = $conn->query($sql_provcuenta);

// Inicializar variables para almacenar los resultados de provcuenta
$numpt = null;

// Verificar si hay resultados
if ($result_provcuenta->num_rows > 0) {
    // Salida de cada fila
    while ($row = $result_provcuenta->fetch_assoc()) {
        // Almacenar los resultados en variables
        $numpt = $row['numpt'];
    }
} else {
    $numpt = "No se encontraron cuentas de proveedor.";
}

$numpt = $numpt + 1;

// Preparar la consulta SQL
$sql = "INSERT INTO `puntocta` (`ano_pta`, `nrp_pta`, `fec_pto`,
 `fec_pag`, `mon_pta`, `ban_pta`, `nro_cta`, `tip_cta`, `nom_ben`,
  `nac_pta`, `ced_ben`, `asu_pta`, `arg_pta`, `usuario`) 
        VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar la declaración
$stmt = $conn->prepare($sql);

// Verificar si la preparación fue exitosa
if ($stmt === false) {
    die("Error en la preparación de la declaración: " . $conn->error);
}

// Vincular los parámetros
$stmt->bind_param("ssssdssssssssi", $ano, $numpt, $fecha, $fecpago, $monto, $banco, $nrocta, $tip_cta, $nomprov, $nacio, $cedprov, $asunto, $argumento,$id_usu);

// Ejecutar la declaración
if ($stmt->execute()) {

    $last_id = $conn->insert_id;
    // Consulta SQL para actualizar el valor de numpt
    $sql_update = "UPDATE numpt SET numpt = ?";

    // Preparar la declaración
    $stmt = $conn->prepare($sql_update);

    // Verificar si la preparación fue exitosa
    if ($stmt === false) {
        die("Error en la preparación de la declaración: " . $conn->error);
    }

    // Vincular el parámetro
    $stmt->bind_param("i", $numpt); // 'i' indica que el parámetro es un entero

    // Ejecutar la declaración
    if ($stmt->execute()) {

        // Supongamos que $solicitudes es una cadena con los IDs separados por comas, por ejemplo: "1,2,3"
        $solicitudes_array = explode(',', $solicitudes); // Convertir la cadena en un array
        $placeholders = implode(',', array_fill(0, count($solicitudes_array), '?')); // Crear los placeholders para la cláusula IN

        // Preparar la consulta SQL
        $sql_update = "UPDATE solicitud SET fechaent = ?, estatus = 1, ptocuenta = ?, mespto = ?, anopto = ?, cedusu = ? WHERE idsolicitud IN ($placeholders)";

        // Preparar la declaración
        $stmt = $conn->prepare($sql_update);

        // Verificar si la preparación fue exitosa
        if ($stmt === false) {
            die("Error en la preparación de la declaración: " . $conn->error);
        }

        // Crear un array de tipos para bind_param
        $types = 'ssssi' . str_repeat('i', count($solicitudes_array)); // 'dsss' para los primeros 4 parámetros, 'i' para los IDs

        // Vincular los parámetros
        $params = array_merge([$fecha, $numpt, $mes, $ano ,$id_usu], $solicitudes_array);
        $stmt->bind_param($types, ...$params); // Usar el operador de expansión para pasar los parámetros

        // Ejecutar la declaración
        if ($stmt->execute()) {


            // Contar el número de registros
            $num_registros = count($solicitudes_array);

            if ($num_registros <= 5) {

                // Almacenar los datos en la sesión
    $_SESSION['last_id'] = $last_id;
    $_SESSION['solicitudes'] = $argumento;
    // Redirigir a imprimir1_5.php
    header("Location: imprimir1_5.php");
     exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 5 && $num_registros <= 10) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir.php");
                exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 10 && $num_registros <= 20) {
                
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir2_5.php");
                
               exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 20 && $num_registros <= 30) {

                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir2_6.php");
               exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 30 && $num_registros <= 40) {

                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir2.php");
               exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 40 && $num_registros <= 58) {

                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir3_5.php");

               exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 58 && $num_registros <= 75) {

                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir3.php");
              exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 75 && $num_registros <= 82) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir3_6.php");
               exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            }elseif ($num_registros > 82 && $num_registros <= 106) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir4.php");
               exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 106 && $num_registros <= 138) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir5.php");
               exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            }elseif ($num_registros > 138 && $num_registros <= 168) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir6.php"); exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            }elseif ($num_registros > 168 && $num_registros <= 200) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir7.php"); exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            }elseif ($num_registros > 200 && $num_registros <= 232) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir8.php");exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            }elseif ($num_registros > 232 && $num_registros <= 264) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir9.php"); exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            }elseif ($num_registros > 264 && $num_registros <= 290) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir10.php"); exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            }elseif ($num_registros > 290 && $num_registros <= 317) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir11.php");exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 317 && $num_registros <= 344) {
                $_SESSION['last_id'] = $last_id;
                $_SESSION['solicitudes'] = $argumento;
                // Redirigir a imprimir1_5.php
                header("Location: imprimir12.php"); exit(); // Asegúrate de llamar a exit() después de header() para detener la ejecución del script

            } elseif ($num_registros > 345) {
                echo "Cantidad de solicitudes superadas";
                
            }
        } else {
            echo "Error al ejecutar la declaración: " . $stmt->error;
        }
    } else {
        echo "Error al actualizar el registro: " . $stmt->error;
    }
} else {
    echo "Error al insertar el registro: " . $stmt->error;
}

// Cerrar la declaración y la conexión
$stmt->close();
$conn->close();
