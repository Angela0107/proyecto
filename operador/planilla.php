<?php
require_once "../db.php";

if (isset($_GET['nun_ayuda'])) {
    $id_planilla = $conn->real_escape_string($_GET['nun_ayuda']); 
    if (isset($_GET['ced_ben'])) {
        $ced_ben = $conn->real_escape_string($_GET['ced_ben']); 
    
    $sql = "SELECT 
            p.id_planilla,
            p.fecha_planilla,
            p.ids_bene,
            p.id_requiayuda,
            p.rif,
            p.carta_alc,
            p.cedula AS cedula_planilla,
            p.cop_nac,
            p.dat_ban,
            p.inf_medi,
            p.recipe_med,
            p.act_defun,
            p.fac_ori,
            p.presu_ban,
            p.otros,
            p.nac_ben AS p_nacio,
            p.nom_ben AS p_nombre,
            p.tlf_ben AS p_telefono,
            p.dir_ben AS p_direccion,
            p.cedula AS p_cedula,
            b.nac_ben,
            b.ced_ben AS cedula_beneficiario,
            b.nom_ben,
            b.dir_ben,
            b.cod_par,
            b.cor_ben,
            b.tlf_ben,
            b.sec_ben
        FROM 
            planilla p
        JOIN 
            beneficiario b ON p.ids_bene = b.ids_bene 
        WHERE p.id_planilla = '$id_planilla' AND p.estado = '1' AND p.ids_bene = $ced_ben"; // Asegúrate de usar comillas simples para el valor

    $result = $conn->query($sql);

    $planillas = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $planillas[] = [
                'id_planilla' => $row["id_planilla"],
                'fecha_planilla' => $row["fecha_planilla"],
                'nom_ben' => $row["nom_ben"],
                'cedula' => $row["cedula_beneficiario"],
                'requisito_ayuda' => $row["id_requiayuda"],
                'dir_ben' => $row["dir_ben"],
                'num_tlf' => $row["tlf_ben"],
                'rif' => $row["rif"],
                'carta_alc' => $row["carta_alc"],
                'cedula_box' => $row["cedula_planilla"],
                'cop_nac' => $row["cop_nac"],
                'dat_ban' => $row["dat_ban"],
                'inf_medi' => $row["inf_medi"],
                'recipe_med' => $row["recipe_med"],
                'act_defun' => $row["act_defun"],
                'fac_ori' => $row["fac_ori"],
                'presu_ban' => $row["presu_ban"],
                'otros' => $row["otros"],
                'nac_ben' => $row["nac_ben"],
                'codparro' => $row["cod_par"],
                'cor_ben' => $row["cor_ben"],
                'p_nacio' => $row["p_nacio"],
                'p_nombre' => $row["p_nombre"],
                'p_telefono' => $row["p_telefono"],
                'p_direccion' => $row["p_direccion"],
                'p_cedula' => $row["p_cedula"],
                'sector' => $row["sec_ben"]
            ];
        }
    } else {
        echo "No se encontraron resultados.";
    }
} else {
    echo "El parámetro 'nun_ayuda' no fue recibido.";
}

$conn->close();


if (!empty($planillas)) {
    foreach ($planillas as $planilla) {
?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Planilla de Solicitud</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <style>
                body {
                    width: 8.5in; 
                    height: 11in; 
                    margin: 0 auto;
                    padding: 20px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
                    background-color: white; 
                    margin-left: 20%;
                }

                input[type="checkbox"] {
                    display: none;
                }

                .custom-checkbox {
                    display: inline-block;
                    width: 12px;
                    height: 12px;
                    border: 2px solid black;
                    background-color: white;
                    position: relative;
                    cursor: pointer;
                }

                input[type="checkbox"]:checked + .custom-checkbox {
                    background-color: white;
                }

                input[type="checkbox"]:checked + .custom-checkbox::after {
                    content: '';
                    position: absolute;
                    left: 4px;
                    top: 0px;
                    width: 4px;
                    height: 8px;
                    border: solid black;
                    border-width: 0 2px 2px 0;
                    transform: rotate(45deg);
                }

                @media print {
                    @page {
                        size: 8.5in 11in; 
                        margin: 0; 
                    }

                    body {
                        margin: 0;
                        padding: 0;
                        font-size: 12pt;
                    }

                    header {
                        position: relative; 
                        top: 0;
                        left: 0;
                        right: 0;
                        height: 100px;
                        background: white;
                        z-index: 1000;
                    }

                    footer {
                        position: relative; 
                        bottom: 40px; 
                        left: 0;
                        right: 0;
                        height: 50px; 
                        background: white;
                        z-index: 1000;
                    }

                    .botones {
                        display: none; 
                    }

                    .content {
                        page-break-inside: avoid; 
                    }
                }
            </style>
        </head>

        <body class="bg-gray-100 p-6 flex flex-col min-h-screen">
            <header class="header mb-4">
                <img src="../imagenes/cabecera.jpg" alt="Descripción de la imagen 1" class="w-full h-auto">
            </header>
            <div class="content flex-grow" style="margin: 0 30px;"> <!-- Ajusta el valor según sea necesario -->
                <div class="flex justify-between mb-4">
                    <div>
                        <p class="text-sm font-semibold">
                            <strong>Fecha:</strong> <?php echo $planilla['fecha_planilla']; ?>
                        </p>
                        <p class="text-sm font-semibold">
                            <strong>N° de planilla:</strong> <?php echo $planilla['id_planilla']; ?>
                        </p>
                    </div>
                </div>
                <h2 class="text-center text-xl font-bold mb-4">PLANILLA DE SOLICITUD</h2>
                <div class="mb-4">
                    <p class="text-sm"><strong>Cédula Identidad: </strong> <?php echo $planilla['p_nacio']; ?>-<?php echo $planilla['cedula']; ?></p>
                    <p class="text-sm"><strong>Nombre:</strong> <?php echo $planilla['nom_ben']; ?></p>
                    <p class="text-sm"><strong>Dirección:</strong> <?php echo $planilla['dir_ben']; ?></p>
                    <p class="text-sm"><strong>Teléfono:</strong> <?php echo $planilla['p_telefono']; ?></p>
                    <p class="text-sm"><strong>Solicitud:</strong> <?php echo $planilla['requisito_ayuda']; ?></p>
                </div>
                <div class="mb-2">
                    <h3 class="text-sm font-bold">REQUISITOS:</h3>
                    <ul class="list-disc list-inside text-xs">
                        <?php
                        $requisitos = [
                            'cedula_box' => 'COPIA CEDULA DE IDENTIDAD',
                            'rif' => 'COPIA DE RIF',
                            'carta_alc' => 'CARTA DIRIGIDA AL ALCALDE',
                            'presu_ban' => 'PRESUPUESTO CON CUENTA BANCARIA (En caso de ser en una clínica privada)',
                            'cop_nac' => 'PARTIDA DE NACIMIENTO',
                            'dat_ban' => 'PRESUPUESTO ORIGINAL CON DATOS BANCARIOS',
                            'inf_medi' => 'INFORME MÉDICO',
                            'recipe_med' => 'COPIA DE RÉCIPE MEDICO CON SELLO HÚMEDO',
                            'act_defun' => 'ACTA DE DEFUNCIÓN',
                            'fac_ori' => 'FACTURA ORIGINAL'
                        ];

                        foreach ($requisitos as $key => $label) {
                            echo '<li>
                                    <label>
                                        <input type="checkbox" ' . ($planilla[$key] == 1 ? 'checked' : '') . ' disabled>
                                        <span class="custom-checkbox"></span>
                                        ' . $label . '
                                    </label>
                                  </li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="mb-4">
                    <center>
                        <h3 class="text-sm font-bold">DONACION</h3>
                    </center>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <label class="text-sm mr-2"><strong>Nombres:</strong></label>
                            <input type="text" class="border-b border-gray-400 flex-1 text-sm">
                        </div>
                        <div class="flex items-center">
                            <label class="text-sm mr-2"><strong>Apellidos:</strong></label>
                            <input type="text" class="border-b border-gray-400 flex-1 text-sm">
                        </div>
                        <div class="flex items-center">
                            <label class="text-sm mr-2"><strong>Número de Cédula:</strong></label>
                            <input type="text" class="border-b border-gray-400 flex-1 text-sm">
                        </div>
                        <div class="flex items-center">
                            <label class="text-sm mr-2"><strong>Apoyo Recibido:</strong></label>
                            <input type="text" class="border-b border-gray-400 flex-1 text-sm">
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="flex items-center">
                        <label class="text-sm mr-2"><strong>Fecha de entrega:</strong></label>
                        <input type="text" class="" placeholder="______/______/___________">
                    </div>
                    <div class="flex items-center justify-center mt-4">
                        <div class="flex-1 text-center">
                            <div class="border-b border-gray-400 w-1/2 h-8 mx-auto"></div>
                            <p class="text-sm">Recibí conforme</p>
                        </div>
                        <div class="flex items-center ml-4">
                            <div class="border border-gray-400 w-16 h-20 mr-2"></div>
                            <div class="border border-gray-400 w-16 h-20"></div>
                        </div>
                    </div>
                </div>
            </div>
            <footer>
                <img src="../imagenes/piepagina.jpg" alt="Descripción de la imagen 1">
            </footer>
            <div class="botones">
                <a href="estadisticas.php" class="mt-4 bg-blue-500 text-white font-bold py-2 px-4 rounded" style="margin-right: 5px">
                    Volver </a>
                </button>
                <button type="button" onclick="window.print()" class="mt-4 bg-blue-500 text-white font-bold py-2 px-4 rounded">
                    Imprimir
                </button>
                
            </div>
            <div class="botones2" style="background-color: rgb(243, 244, 246);">
                <h1 style="color: rgb(243, 244, 246);">.</h1>
            </div>
        </body>

        </html>
<?php
    }
}}
?>