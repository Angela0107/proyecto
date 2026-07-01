<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "diseño_ayudas";


    session_start();
$last_id = $_SESSION['last_id'];
$solicitudes_string = $_SESSION['solicitudes'];

    // Convertir la cadena de solicitudes en un array
    $solicitudes_array = explode(',', $solicitudes_string);


    // $solicitudes = '2109, 2509, 2535, 1617, 3006, 3007';
    // $mon_pta = '123245';
    // $fechanueva = '2023-11-21';
    // $id_provcuenta = '10';

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }






    // Consulta SQL para obtener datos de provcuenta
    $sql_provcuenta = "SELECT *
                   FROM puntocta WHERE ids_pta = '$last_id'";

    $result_provcuenta = $conn->query($sql_provcuenta);

    // Inicializar variables para almacenar los resultados de provcuenta
    $mon_pta = $fec_pto = $asu_pta = $nrp_pta = null;

    // Verificar si hay resultados
    if ($result_provcuenta->num_rows > 0) {
        // Salida de cada fila
        while ($row = $result_provcuenta->fetch_assoc()) {
            // Almacenar los resultados en variables
            $mon_pta = $row['mon_pta'];
            $fec_pto = $row['fec_pto'];
            $asu_pta = $row['asu_pta'];
            $nrp_pta = $row['nrp_pta'];
        }
    } else {
        $mon_pta = $fec_pto = $asu_pta = $nrp_pta ="No se encontraron cuentas de proveedor.";
    }


    // Obtener la parte del texto hasta el final deseado
$nueva_variable = substr($asu_pta, 0, strpos($asu_pta, ")") + 1);

// Reemplazar la última coma por un punto
$nueva_variable = preg_replace('/,(?=[^,]*$)/', '.', $nueva_variable);

$mon_pta_formateado = number_format($mon_pta, 2, ',', '.');

    // Consulta SQL
    $sql = "SELECT COUNT(s.idsolicitud) AS total_solicitudes, s.idsolicitud, s.numsol, s.nac_ben, s.ced_ben, s.descripcion, s.nom_ben FROM solicitud s INNER JOIN beneficiario b ON b.ced_ben = s.ced_ben
 WHERE s.idsolicitud IN ($solicitudes_string) GROUP BY s.idsolicitud;";
    $result = $conn->query($sql);

    // Inicializar variables para las tablas
    $tabla1 = '';
    $tabla2 = '';
    $tabla3 = '';
    $tabla10 = ''; // Variable para guardar la información de la tabla 1
    $tabla20 = ''; // Variable para guardar la información de la tabla 2
    $tabla30 = ''; // Variable para guardar la información de la tabla 3
    $currentTable = 1; // Contador de tablas
    $maxRowsPerTable = [50, 3300, 300]; // Alturas de los contenedores
    $rowsInCurrentTable = 0; // Contador de filas en la tabla actual
    $contador = 1;

    if ($result->num_rows > 0) {
        
        while ($row = $result->fetch_assoc()) {
            // Si hemos alcanzado el límite de filas para la tabla actual, creamos una nueva tabla
            if ($rowsInCurrentTable == ($maxRowsPerTable[$currentTable - 1] / 10)) { // Asumiendo que cada fila ocupa aproximadamente 50px de altura
                $currentTable++;
                $rowsInCurrentTable = 0; // Reiniciar el contador de filas
            }

            // Si hemos superado el número de tablas, salimos del bucle
            if ($currentTable > count($maxRowsPerTable)) {
                break;
            }

            // Si es la primera fila de la tabla actual, comenzamos a crear la tabla
            if ($rowsInCurrentTable == 0) {
                if ($currentTable == 1) {
                    $tabla1 = '<div><table border="1" style="width: 100%; border-collapse: collapse;">';
                    $tabla1 .= '<thead>
                        <tr>
                            <th>N°</th>
                    <th>Cédula de identidad</th>
                    <th>Nombres y Apellidos del solicitante</th>
                    <th>Descripción</th>
                        </tr>
                      </thead>
                      <tbody>';
                } elseif ($currentTable == 2) {
                    $tabla2 = '<div><table border="1" style="width: 100%; border-collapse: collapse;">';
                    $tabla2 .= '<thead>
                        <tr>
                           <th>N°</th>
                    <th>Cédula de identidad</th>
                    <th>Nombres y Apellidos del solicitante</th>
                    <th>Descripción</th>
                        </tr>
                      </thead>
                      <tbody>';
                } elseif ($currentTable == 3) {
                    $tabla3 = '<div><table border="1" style="width: 100%; border-collapse: collapse;">';
                    $tabla3 .= '<thead>
                        <tr>
                            <th>N°</th>
                    <th>Cédula de identidad</th>
                    <th>Nombres y Apellidos del solicitante</th>
                    <th>Descripción</th>
                        </tr>
                      </thead>
                      <tbody>';
                }
            }

            // Agregar la fila a la tabla correspondiente
            if ($currentTable == 1) {
                $tabla1 .= '<tr>
                    <td>' . $contador . '</td>
                    <td>' . htmlspecialchars($row['nac_ben']) .'-'.htmlspecialchars($row['ced_ben']) . '</td>
                    <td>' . htmlspecialchars($row['nom_ben']) . '</td>
                    <td>' . htmlspecialchars($row['descripcion']) . '</td>
                  </tr>';
                $tabla10 .= '<tr>
                    <td>' . $contador . '</td>
                   <td>' . htmlspecialchars($row['nac_ben']) .'-'.htmlspecialchars($row['ced_ben']) . '</td>
                    <td>' . htmlspecialchars($row['nom_ben']) . '</td>
                    <td>' . htmlspecialchars($row['descripcion']) . '</td>
                  </tr>';
            } elseif ($currentTable == 2) {
                $tabla2 .= '<tr>
                    <td>' . $contador . '</td>
                   <td>' . htmlspecialchars($row['nac_ben']) .'-'.htmlspecialchars($row['ced_ben']) . '</td>
                    <td>' . htmlspecialchars($row['nom_ben']) . '</td>
                    <td>' . htmlspecialchars($row['descripcion']) . '</td>
                  </tr>';
                $tabla20 .= '<tr>
                    <td>' . $contador . '</td>
                   <td>' . htmlspecialchars($row['nac_ben']) .'-'.htmlspecialchars($row['ced_ben']) . '</td>
                    <td>' . htmlspecialchars($row['nom_ben']) . '</td>
                    <td>' . htmlspecialchars($row['descripcion']) . '</td>
                  </tr>';
            } elseif ($currentTable == 3) {
                $tabla3 .= '<tr>
                    <td>' . $contador . '</td>
                    <td>' . htmlspecialchars($row['nac_ben']) .'-'.htmlspecialchars($row['ced_ben']) . '</td>
                    <td>' . htmlspecialchars($row['nombre']) . '</td>
                    <td>' . htmlspecialchars($row['descripcion']) . '</td>
                  </tr>';
                $tabla30 .= '<tr>
                    <td>' . $contador . '</td>
                    <td>' . htmlspecialchars($row['nac_ben']) .'-'.htmlspecialchars($row['ced_ben']) . '</td>
                    <td>' . htmlspecialchars($row['nombre']) . '</td>
                    <td>' . htmlspecialchars($row['descripcion']) . '</td>
                  </tr>';
            }

            $rowsInCurrentTable++; // Incrementar el contador de filas
            $contador++;
        }

        // Cerrar las tablas si se han creado
        if ($rowsInCurrentTable > 0) {
            if ($currentTable >= 1) {
                $tabla1 .= '</tbody></table></div>';
                $tabla10 = '<div><table border="1" style="width: 100%; border-collapse: collapse;">
                          <thead>
                            <tr>
                               <th>N°</th>
                    <th>Cédula de identidad</th>
                    <th>Nombres y Apellidos del solicitante</th>
                    <th>Descripción</th>
                            </tr>
                          </thead>
                          <tbody>' . $tabla10 . '</tbody>
                        </table></div>';
            }
            if ($currentTable >= 2) {
                $tabla2 .= '</tbody></table></div>';
                $tabla20 = '<div><table border="1" style="width: 100%; border-collapse: collapse;">
                          <thead>
                            <tr>
                               <th>N°</th>
                    <th>Cédula de identidad</th>
                    <th>Nombres y Apellidos del solicitante</th>
                    <th>Descripción</th>
                            </tr>
                          </thead>
                          <tbody>' . $tabla20 . '</tbody>
                        </table></div>';
            }
            if ($currentTable >= 3) {
                $tabla3 .= '</tbody></table></div>';
                $tabla30 = '<div><table border="1" style="width: 100%; border-collapse: collapse;">
                          <thead>
                            <tr>
                              <th>N°</th>
                    <th>Cédula de identidad</th>
                    <th>Nombres y Apellidos del solicitante</th>
                    <th>Descripción</th>
                            </tr>
                          </thead>
                          <tbody>' . $tabla30 . '</tbody>
                        </table></div>';
            }
        }
    } else {
        $tabla1 = '<div><p>No se encontraron resultados.</p></div>'; // Envolver el mensaje en un div
    }

// Cerrar la conexión
$conn->close();
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

.texto1234{
    font-weight: bold;
    margin-left: 500px;
    height: 25px;
}

.flex2{
    display: flex;
    font-size: large;
}
        table {
            margin-top: 40px;
    width: 816px; /* Ancho fijo de la tabla */
    border-collapse: collapse; /* Para que las celdas se vean unidas */
    font-size: x-small;
    font-size: unset;
}

th{
    height: 25px; 
     text-align: center;
}


tr{
    height: 25px;
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

        .asu_pta_argumento {
            justify-items: center;
            padding-top: 2px;
            padding-left: 2px;
            padding-bottom: 2px;
            padding-right: 2px;
            text-align: center;
        }

        .borderedasu_pta {
            height: 70px;
            padding-top: 2px;
            padding-bottom: 2px;
            padding-left: 2px;
            padding-right: 2px;
            width: 100%;
        }

        .borderedargumento {
            height: 320px;
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

            .button {
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

        .texto_asu_pta {
            height: auto;
            text-align: justify;
            width: 100%;
            box-sizing: border-box;
            font-size: 13px;

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


    <div class="container">
        <header>
            <img src="../imagenes/cabecera.jpg" alt="Descripción de la imagen 1" class="img">
        </header>

        <div class="form-row1">
            <div class="borderedfec bordered">
                <strong>
                    <div class="textos">Fecha</div>
                </strong>
                <div class="subtitulo1" name="fecha" id="fecha" required><?php echo $fec_pto?></div>
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
                <center><?php echo $nrp_pta?></center>
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

        <form method="POST">
            <div class="form-row">
                <div class="asu_pta_argumento bordered">
                    <strong>
                        <div class="texto">asu_pta:</div>
                    </strong>
                </div>
            </div>
            <div class="form-row">
                <div class="borderedasu_pta bordered">
                    <div class="texto_asu_pta"><?php echo $nueva_variable?>.</div>
                </div>
            </div>

            <div class="form-row">
                <div class="asu_pta_argumento bordered">
                    <strong>
                        <div class="texto">ARGUMENTO:</div>
                    </strong>
                </div>
            </div>


            <div class="form-row">
                <div class="borderedargumento bordered">
                    <div class="texto_argumento">
                    <div class="texto_asu_pta"><?php echo $asu_pta?></div>
                        <?php echo $tabla10 ?>
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
                         <div class="flex2"><div>Monto : </div><div class="texto1234">BS  <?php echo $mon_pta_formateado?></div></div>
                       
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

        <center><button id="btnCrearPDF" onclick="window.print()">Imprimir</button>
        <div class="button">
                <a href="aprobar.php" class="btn1" style="margin-right: 5px">Volver</a>
        </center>
    </div>
    </div>
</body>

</html>

<body>