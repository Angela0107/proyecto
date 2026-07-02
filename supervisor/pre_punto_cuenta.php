<?php

session_start(); // Inicia la sesión

if (!isset($_POST['solicitudes']) || empty($_POST['solicitudes'])) {
    header('Location: error_punto.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica si la variable 'solicitudes' está definida
    if (isset($_POST['ids_prc'])) {
        // Obtiene la cadena
        $ids_prc = $_POST['ids_prc'];

        // Verifica si 'ids_prc' está vacío
        if (empty($ids_prc)) {
            

             // Supongamos que estas son las variables que deseas enviar
        $monto = $_POST['monto']; // Asegúrate de que estas variables estén definidas
        $fecha = $_POST['fecha'];
        $solicitudes = $_POST['solicitudes'];
        $nombre_propio = $_POST['nombre_propio'];
        $nac_prv_propio = $_POST['nac_prv_propio'];
        $Cedula_propio = $_POST['Cedula_propio'];
        $numero_cuenta_propio = $_POST['numero_cuenta_propio'];
        $codban_propio = $_POST['codban_propio'];
        $tip_cta_propio = $_POST['tip_cta_propio'];

        // Guardar las variables en la sesión
        $_SESSION['monto'] = $monto;
        $_SESSION['fecha'] = $fecha;
        $_SESSION['solicitudes'] = $solicitudes;
        $_SESSION['nombre_propio'] = $nombre_propio;
        $_SESSION['nac_prv_propio'] = $nac_prv_propio;
        $_SESSION['Cedula_propio'] = $Cedula_propio;
        $_SESSION['numero_cuenta_propio'] = $numero_cuenta_propio;
        $_SESSION['codban_propio'] = $codban_propio;
        $_SESSION['tip_cta_propio'] = $tip_cta_propio;

        // Redirigir a la página deseada
        header('location: pre_punto_cuenta2.php');
        exit;


        }
    } else {
        header('location: error_punto.php');
        exit; // Asegúrate de salir después de redirigir
    }
}



if(isset($_POST['fecha'])){
    $fechanueva = $_POST['fecha'];
    
}
if(isset($_POST['monto'])){
    $monto = $_POST['monto'];
   
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica si la variable 'solicitudes' está definida
    if (isset($_POST['solicitudes'])) {
        // Obtiene la cadena
        $solicitudes = $_POST['solicitudes'];
    } else{
        header('location: error_punto.php');
        exit; // Asegúrate de salir después de redirigir
    }
}

$monto_formateado = number_format($monto, 2, ',', '.');
?>

<?php
// Conexión a la base de datos
require_once "../db.php";// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Validar y limpiar los IDs
$solicitudes_array = explode(',', $solicitudes);
$solicitudes_clean = [];

foreach ($solicitudes_array as $id) {
    if (is_numeric(trim($id))) {
        $solicitudes_clean[] = intval(trim($id));
    }
}

if (empty($solicitudes_clean)) {
    header('Location: error_punto.php');
    exit;
}

$ids_list = implode(',', $solicitudes_clean);

// Consulta SQL
$sql = "SELECT s.idsolicitud, s.numsol, s.nac_ben, s.ced_ben, s.descripcion, s.nom_ben FROM solicitud s INNER JOIN beneficiario b ON b.ced_ben = s.ced_ben
 WHERE s.`anopto` IS NULL AND s.idsolicitud IN ($solicitudes);";
$result = $conn->query($sql);

// Inicializar la variable para la tabla
$tabla = '';

// Verificar si hay resultados
if ($result->num_rows > 0) {
    // Crear la tabla
    $tabla = '<div><table border="1" style="width: 100%; border-collapse: collapse;">';
    $tabla .= '<thead>
                <tr>
                    <th>N°</th>
                    <th>Cédula de identidad</th>
                    <th>Nombres y Apellidos del solicitante</th>
                    <th>Descripción</th>
                </tr>
              </thead>
              <tbody>';

    $contador = 1;
    // Recorrer los resultados y agregar filas a la tabla
    while ($row = $result->fetch_assoc()) {
        $tabla .= '<tr>
                    <td>' . $contador . '</td>
                    <td>' . htmlspecialchars($row['nac_ben']) .'-'.htmlspecialchars($row['ced_ben']) . '</td>
                    <td>' . htmlspecialchars($row['nom_ben']) . '</td>
                    <td>' . htmlspecialchars($row['descripcion']) . '</td>
                  </tr>';

        $contador++; // Incrementar el contador
    }

    // Cerrar la tabla
    $tabla .= '</tbody></table></div>';
} else {
      header('Location: error_punto.php');
    exit;
}


// Consulta SQL
$sql = "SELECT DISTINCT ts.des_tpo 
        FROM `tiposolicitud` ts
        JOIN `solicitud` s ON ts.cod_tip = s.`codsolicitud`
        WHERE s.`idsolicitud` IN ($solicitudes)";

$result = $conn->query($sql);

// Inicializar la variable para almacenar las descripciones
$descripciones = [];

// Verificar si hay resultados
if ($result->num_rows > 0) {
    // Salida de cada fila
    while ($row = $result->fetch_assoc()) {
        // Agregar cada descripción al array
        $descripciones[] = $row['des_tpo'];
    }
} else {
    $descripciones[] = "No se encontraron descripciones.";
}

// Convertir el array a una cadena separada por comas
$descripciones_string = implode(", ", $descripciones);


// Consulta SQL para obtener datos de provcuenta
$sql_provcuenta = "SELECT b.nom_ban, pc.nro_cta, pc.tip_cta, pc.nac_prv, pc.ced_prv, pc.ids_prc, p.nom_prv 
                   FROM provcuenta pc 
                   INNER JOIN proveedor p ON pc.ced_prv = p.ced_prv 
                   INNER JOIN bancos b ON pc.cod_ban = b.cod_ban
                   WHERE pc.ids_prc = '$ids_prc'";

$result_provcuenta = $conn->query($sql_provcuenta);

// Inicializar variables para almacenar los resultados de provcuenta
$nom_ban = $nro_cta = $tip_cta = $nac_prv = $ced_prv = $ids_prc = $nom_prv = null;

// Verificar si hay resultados
if ($result_provcuenta->num_rows > 0) {
    // Salida de cada fila
    while ($row = $result_provcuenta->fetch_assoc()) {
        // Almacenar los resultados en variables
        $nom_ban = $row['nom_ban'];
        $nro_cta = $row['nro_cta'];
        $tip_cta = $row['tip_cta'];
        $nac_prv = $row['nac_prv'];
        $ced_prv = $row['ced_prv'];
        $ids_prc = $row['ids_prc'];
        $nom_prv = $row['nom_prv'];
    }
} else {
    $nom_ban = $nro_cta = $tip_cta = $nac_prv = $ced_prv = $ids_prc = $nom_prv = "No se encontraron cuentas de proveedor.";
}

// Cerrar la conexión
$conn->close();
?>

<?php
function numero_a_texto($numero)
{
    $unidades = [
        "",        "uno",     "dos",      "tres",     "cuatro",
        "cinco",   "seis",    "siete",    "ocho",     "nueve",
        "diez",    "once",    "doce",     "trece",    "catorce",
        "quince",  "dieciséis", "diecisiete", "dieciocho", "diecinueve"
    ];

    $decenas = [
        "",        "",        "veinte",   "treinta",  "cuarenta",
        "cincuenta", "sesenta", "setenta", "ochenta", "noventa"
    ];

    $centenas = [
        "",        "cien",    "doscientos", "trescientos", "cuatrocientos",
        "quinientos", "seiscientos", "setecientos", "ochocientos", "novecientos"
    ];

    if ($numero < 0) {
        return "menos " . numero_a_texto(-$numero);
    }

    if ($numero == 0) {
        return "cero";
    }

    if ($numero < 20) {
        return $unidades[$numero];
    }

    if ($numero < 100) {
        $d = intval($numero / 10);
        $u = $numero % 10;
        $texto = $decenas[$d];
        if ($u > 0) {
            $texto .= ($d == 2 ? "i" : " y ") . $unidades[$u];
        }
        return $texto;
    }

    if ($numero < 1000) {
        $c = intval($numero / 100);
        $r = $numero % 100;
        $texto = $centenas[$c];
        if ($r > 0) {
            $texto .= " " . numero_a_texto($r);
        }
        // Corrección: ciento + resto
        if ($c == 1 && $r > 0) {
            $texto = "ciento " . numero_a_texto($r);
        }
        return $texto;
    }

    if ($numero < 1000000) { // Miles
        $m = intval($numero / 1000);
        $r = $numero % 1000;
        $texto = ($m == 1 ? "mil" : numero_a_texto($m) . " mil");
        if ($r > 0) {
            $texto .= " " . numero_a_texto($r);
        }
        return $texto;
    }

    if ($numero < 1000000000) { // Millones
        $m = intval($numero / 1000000);
        $r = $numero % 1000000;
        $texto = numero_a_texto($m) . ($m == 1 ? " millón" : " millones");
        if ($r > 0) {
            $texto .= " " . numero_a_texto($r);
        }
        return $texto;
    }

    if ($numero < 1000000000000) { // Mil millones
        $m = intval($numero / 1000000000);
        $r = $numero % 1000000000;
        $texto = numero_a_texto($m) . " mil millones";
        if ($r > 0) {
            $texto .= " " . numero_a_texto($r);
        }
        return $texto;
    }

    if ($numero < 1000000000000000) { // Billones
        $m = intval($numero / 1000000000000);
        $r = $numero % 1000000000000;
        $texto = numero_a_texto($m) . ($m == 1 ? " billón" : " billones");
        if ($r > 0) {
            $texto .= " " . numero_a_texto($r);
        }
        return $texto;
    }

    return "Número fuera de rango";
}


$monto_texto = numero_a_texto($monto);




?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        @page {
            size: letter;
            /* Tamaño carta */
            margin: 1in;
            /* Márgenes de 1 pulgada */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            background-color: #fff;
            /* Fondo blanco para el PDF */
            margin: 0;
            /* Sin márgenes */
            padding: 0;
            /* Sin padding */
        }

        .container {
            max-width: 8.5in;
            /* Ancho máximo para carta */
            margin: auto;
            /* Centrar el contenedor */
            padding: 0px;
            /* Espaciado interno */
            border-left: 1cm solid #ffffff;
            /* Borde izquierdo */
            border-right: 1cm solid #ffffff;
            /* Borde derecho */
        }

        header {
            height: 2cm;
            /* Altura del encabezado */
            overflow: hidden;
            /* Ocultar contenido que exceda la altura */
            text-align: center;
            /* Centrar el contenido */
        }

        footer {
            height: 2cm;
            /* Altura del pie de página */
            overflow: hidden;
            /* Ocultar contenido que exceda la altura */
            text-align: center;
            /* Centrar el contenido */
            margin-top: 5px;
            /* Espacio entre el contenido y el pie de página */
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0px;

        }

        .form-row1 {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0px;
            height: 50px;

        }

        .form-row2 {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0px;
            height: 130px;

        }

        .bordered {
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            width: 100%;
            box-shadow: 0 0 0 1px black;
            /* Cuadro visible sin bordes */
        }

        .borderedpunto,
        .borderedfec,
        .borderedcuenta,
        .borderedfirma {
            padding-top: 2px;
            padding-left: 2px;
            padding-bottom: 2px;
            padding-right: 2px;
            width: 48%;
            font-size: 15px;
            /* Ajustar el ancho para que quepan dos en una fila */
        }

        .borderedfec {
            justify-items: center;
        }

        .borderedpunto {
            justify-items: center;
        }

        .depara {
            width: 50px;
        }

        .borderedcuenta2 {
            justify-items: center;
            text-align: center;
            padding-top: 25px;
            padding-left: 2px;
            padding-bottom: 2px;
            padding-right: 2px;
            width: 48%;
            font-size: 15px;
        }

        .asunto_argumento {
            justify-items: center;
            padding-top: 2px;
            padding-left: 2px;
            padding-bottom: 2px;
            padding-right: 2px;
            text-align: center;
        }

        .borderedasunto {
            height: 70px;
            padding-top: 2px;
            padding-bottom: 2px;
            padding-left: 2px;
            padding-right: 2px;
            width: 100%;
        }

        .borderedargumento {

            padding-top: 2px;
            padding-bottom: 2px;
            padding-left: 2px;
            padding-right: 2px;
            width: 100%;
        }

        .borderedfirma {
            width: 100%;
        }

        .borderedfirma_1 {
            width: 100%;
            height: 300px;
        }

        .borderedfirmas {
            width: 100%;
            height: 550px;
        }

        .borderedfirmass {
            width: 100%;
            height: 331px;
        }


        .borderedfirma_pagi22 {
            width: 100%;
            height: 510px;
        }

        .borderedfirma_pagi32 {
            width: 100%;
            height: 776px;
        }


        .texto {
            font-weight: bold;
            font-size: smaller;
        }


        .texto2 {
            font-weight: bold;
            font-size: smaller;
        }

        .subtitulo {
            margin-top: 5px;
        }

        .subtitulo2 {
            font-size: x-small;

        }

        .subtitulo3 {
            font-size: x-small;
            margin-top: 0px;

        }

        .flex {
            display: flex;

        }

        .punto {
            margin-left: 50px;
        }

        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }

        img {
            width: 100%;
            max-height: 100%;
        }

        h5 {
            margin-top: 0px;
            margin-bottom: 0px;
        }

        .small-textt {
            font-size: 10px;
            /* Ajusta el tamaño de la fuente según sea necesario */
            line-height: 1;
            /* Opcional: ajusta el interlineado */
            margin-top: 4px;
            margin-left: 35px;
        }

        .small-texttt {
            font-size: 10px;
            /* Ajusta el tamaño de la fuente según sea necesario */
            line-height: 1;
            /* Opcional: ajusta el interlineado */
            margin-top: 60px;
            margin-left: 29px;
        }

        .small-text {
            font-size: 10px;
            /* Ajusta el tamaño de la fuente según sea necesario */
            line-height: 1;
            /* Opcional: ajusta el interlineado */
        }


        .header-container {
            display: flex;
            /* Utiliza flexbox para alinear los elementos */
            justify-content: space-around;
            align-items: center;
            /* Alinea verticalmente al centro */
            height: 60px;
        }

        /* Estilos para impresión */
        @media print {
            body {
                background-color: #fff;
                /* Asegúrate de que el fondo sea blanco */
            }

            button {
                display: none;
                /* Ocultar el botón al imprimir */
            }

            img {
                width: 100%;
                max-height: 100%;
            }

            .texto2 {
                font-size: 12px;


            }
        }

        .texto_asunto {
            text-align: justify;
            width: 100%;
            box-sizing: border-box;
            font-size: 11px;
            height: 83px;
            width: 814px;

        }

        .texto_argumento {
            height: auto;
            text-align: justify;
            width: 100%;
            box-sizing: border-box;
            font-size: 11px;

        }

        .textooo {
            height: auto;
            text-align: justify;
            width: 100%;
            box-sizing: border-box;
        }

        .textooo_peque {
            height: auto;
            text-align: justify;
            width: 100%;
            box-sizing: border-box;
            font-size: 14.5px;
        }

        .textooo_grande {
            height: auto;
            text-align: justify;
            width: 100%;
            box-sizing: border-box;
            font-size: 14pt;
        }

        .textooo_grande2 {
            height: auto;
            text-align: justify;
            width: 100%;
            box-sizing: border-box;
            font-size: 12.5pt;
        }

        .textooo2 {
            height: 360px;
            text-align: justify;
            width: 100%;
        }

        .textoo {
            text-align: justify;
            width: 100%;
            height: 96px;
        }

        .textoooo {
            text-align: justify;
            width: 100%;
            height: 600px;
        }

        .subtitulo4 {
            margin-left: 80%;
        }

        .container2 {
            border-left-width: 0px;
            border-right-width: 0px;
        }

        .firma-linea {
            border: none;
            /* Elimina el borde predeterminado */
            border-top: 1px solid black;
            /* Crea una línea delgada */

            width: 250px;
            margin-bottom: 0px;
            margin-top: 10px;
        }



        .firma-linea2 {
            border: none;
            /* Elimina el borde predeterminado */
            border-top: 1px solid black;
            /* Crea una línea delgada */

            width: 250px;
            margin-bottom: 0px;

            margin-top: 73px;
        }

        .volver-form {
            display: flex;
            justify-content: center;
            /* Centra el formulario horizontalmente */
            margin-top: 20px;
            /* Espacio superior */
        }

        .btn1 {
            margin-left: 700px;
            margin-top: 0px;
            padding: 10px 20px;
            /* Espaciado interno */
            background-color: #007bff;
            /* Color de fondo */
            color: white;
            /* Color del texto */
            border: none;
            /* Sin borde */
            border-radius: 5px;
            /* Bordes redondeados */
            font-size: 16px;
            /* Tamaño de fuente */
            cursor: pointer;
            /* Cambia el cursor al pasar el mouse */
            transition: background-color 0.3s;
            /* Transición suave para el color de fondo */
        }
    </style>
</head>

<body>


<form action="guardar_punto_cuenta.php" method="post">
    <input type="hidden" id="monto" name="monto" value="<?php echo $monto; ?>"> 
    <input type="hidden" id="fecha" name="fecha" value="<?php echo $fechanueva; ?>"> 
    <input type="hidden" id="argumento" name="argumento" value="<?php echo $solicitudes; ?>"> 
    <input type="hidden" id="banco" name="banco" value="<?php echo $nom_ban; ?>"> 
    <input type="hidden" id="nrocta" name="nrocta" value="<?php echo $nro_cta; ?>"> 
    <input type="hidden" id="tip_cta" name="tip_cta" value="<?php echo $tip_cta; ?>"> 
    <input type="hidden" id="nom_prv" name="nom_prv" value="<?php echo $nom_prv; ?>"> 
    <input type="hidden" id="nac_prv" name="nac_prv" value="<?php echo $nac_prv; ?>"> 
    <input type="hidden" id="ced_prv" name="ced_prv" value="<?php echo $ced_prv; ?>"> 
    <input type="hidden" id="tipo" name="tipo" value="<?php echo $tip_cta; ?>"> 


    <div class="container">
        <header>
            <img src="../imagenes/cabecera.jpg" alt="Descripción de la imagen 1" class="img">
        </header>

        <div class="form-row1">
            <div class="borderedfec bordered">
                <strong>
                    <div class="textos">Fecha</div>
                </strong>
                <div class="subtitulo1" name="fecha" id="fecha" required><?php echo $fechanueva ?></div>
            </div>
            <div class="borderedpunto bordered">
                <strong>
                    <div class="texto1">PUNTO DE CUENTA </div>
                </strong>
                <div class="subtitulol">Atencion al Ciudadano</div>
            </div>
            <div class="borderedcuenta bordered">
                <strong>
                    <center>
                        <div class="texto">Nro. Punto de cuenta</div>
                    </center>
                </strong>
                <center></center>
                <div class="punto"></div>
            </div>
        </div>

        <div class="form-row">
            <div class="depara bordered">
                <h5>Para:</h5>
            </div>
            <div class="borderedfirma bordered">
                <h5>DR. SILFREDO ZAMBRANO</h5>
                <div class="subtitulo2" name="id_func" id="id_func" required>ALCALDE DEL MUNICIPIO SAN CRISTOBAL</div>
            </div>
        </div>

        <div class="form-row">
            <div class="depara bordered">
                <h5>De:</h5>
            </div>
            <div class="borderedfirma bordered">
                <h5>LCDA. KARLA JUAREZ</h5>
                <div class="subtitulo2" name="id_func2" id="id_func2" required>DIRECTORA DE ATENCION AL CIUDADANO</div>
            </div>
        </div>

       
            <div class="form-row">
                <div class="asunto_argumento bordered">
                    <strong>
                        <div class="texto">ASUNTO:</div>
                    </strong>
                </div>
            </div>
            <div class="form-row">
                <div class="borderedasunto bordered">
                    <textarea name="asunto" id="asunto" class="texto_asunto">Se somete a consideración y aprobación del Ciudadano Alcalde <strong>Dr. SILFREDO ZAMBRANO</strong>, la cancelación de ayudas económicas por concepto de <strong> <?php echo implode(", ", $descripciones); ?></strong> por un monto de <strong><?php echo strtoupper($monto_texto); ?></strong> con cero Centimos de BS (BS <?php echo $monto_formateado ?>), el cual se le será cancelado al Banco <?php echo $nom_ban ?> Cuenta <?php echo $tip_cta ?> N° <strong><?php echo $nro_cta ?></strong> a Nombre de <strong><?php echo $nom_prv ?> (<?php echo $nac_prv ?>-<?php echo $ced_prv ?>)</strong> para los beneficiarios que se mencionan a continuación.</textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="asunto_argumento bordered">
                    <strong>
                        <div class="texto">ARGUMENTO:</div>
                    </strong>
                </div>
            </div>


            <div class="form-row">
                <div class="borderedargumento bordered">
                    <div class="texto_argumento">
                        <?php echo $tabla ?>
                    </div>
                </div>
            </div>

            <div class="form-row2">
                <div class="borderedcuenta2 bordered">
                    <strong>
                        <div class="texto small-text2">Aprobación</div>
                    </strong>
                    <div class="checkbox-container">
                        <input type="checkbox" id="respuesta_si" name="respuesta" value="si">
                        <label for="respuesta_si">Sí</label>
                    </div>
                    <div class="checkbox-container">
                        <input type="checkbox" id="respuesta_no" name="respuesta" value="no">
                        <label for="respuesta_no">No</label>
                    </div>
                </div>
                <div class="borderedcuenta bordered">

                    <div class="texto">
                        <center>
                            <hr class="firma-linea2">
                            <div class="subtitulo3" name="id_func2" id="id_func2" required>DR. SILFREDO ZAMBRANO <br> ALCALDE DEL MUNICIPIO SAN CRISTOBAL</div>
                            <div class="subtitulo2 small-text" name="id_func2" id="id_func2" required>Acta N° 043-2021 Según Gaceta Extraordinaria</div>
                            <div class="subtitulo2 small-text" name="id_func2" id="id_func2" required>N° 046-2021 de fecha 03 de diciembre 2021</div>
                        </center>
                    </div>

                </div>
                <div class="borderedcuenta bordered">
                    <strong>
                        <center>
                            <div class="texto2" name="nota_alcalde" id="nota_alcalde">Instrucciones adicionales del Alcalde</div>
                        </center>
                    </strong>
                </div>
            </div>

            <div class="form-row">
                <div class="borderedfirma bordered">
                    <strong>
                        <div class="texto">Monto: Bs <?php echo $monto_formateado ?></div>
                    </strong>
                </div>
            </div>


            <div class="form-row">
                <div class="borderedfirma bordered">
                    <div class="header-container">
                        <h4>PREPARADO</h4>
                        <h4>PRESENTADO</h4>
                    </div>
                    <center>
                        <hr class="firma-linea">
                        <div class="subtitulo2" name="id_func2" id="id_func2" required>LCDA. KARLA JUAREZ <br> DIRECTORA DE ATENCION AL CIUDADANO</div>
                        <div class="subtitulo2 small-text" name="id_func2" id="id_func2" required>Segun Resolución N° 122-2023 de fecha 17/08/2023</div>
                    </center>
                </div>
            </div>
        </form>

        <footer>
            <img src="../imagenes/piepagina.jpg" alt="Descripción de la imagen 1" class="img">
        </footer>

    
        <center><button type="submit">Generar Punto de cuenta</button>

        <div class="button">
                <a href="borrar.php" class="btn1" style="margin-right: 5px">Volver</a>
        </center>
    </div>
      
</body>

</html>

<body></body>