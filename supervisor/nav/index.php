<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usu'])) {
  header("Location: ../login.php");
  exit();
}

if ($_SESSION['usu_usu_logueado'] !== "SI") {
  header("Location: ../login.php");
  exit();
}

if ($_SESSION['active'] !== true) {
  header("Location: ../login.php");
  exit();
}

if ($_SESSION['id_rol'] !== '4') {
  header("Location: ../login.php");
  exit();
}

if ($_SESSION['estatus'] !== '1') {
  header("Location: ../login.php");
  exit();
}

$cedula_usu = $_SESSION['cedula'];
$contr_usu = $_SESSION['contr_usu'];
$id_usu = $_SESSION['id_usu'];
$nom_usu = $_SESSION['nom_usu'];

$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Menú Lateral</title>
  <style>
    /* Reset básico */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fa;
      display: flex;
    }

    .sidebar {
      width: 15%;
      height: 100vh;
      background: #1e2a38;
      color: #ffffff;
      position: fixed;
      top: 0;
      left: 0;
      padding-top: 20px;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
      z-index: 1000;
      
    }

    .sidebar a {
      display: flex;
      align-items: center;
      padding: 16px 24px;
      text-decoration: none;
      color: #cbd5e1;
      transition: all 0.25s ease;
      border-left: 4px solid transparent;
    }

    .sidebar a:hover {
      background-color: #2d3748;
      color: #ffffff;
    }

    .sidebar a.activo {
      background-color: #3182ce;
      color: #ffffff;
      border-left-color: #63b3ed;
    }

    .sidebar .icon {
      margin-right: 16px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .sidebar .icon img {
      width: 24px;
      height: 24px;
      object-fit: contain;
    }

    .sidebar .title {
      font-size: 16px;
      font-weight: 500;
      white-space: nowrap;
    }

  </style>
</head>
<body>

  <nav class="sidebar">
  <a href="estadisticas.php"><img src="../imagenes/atencion_ciudad.png" alt="" style="width: 95%;"></a>
    <?php
    $menu_items = [
      ['url' => 'agreayuda.php',   'img' => '../imagenes/estadisticas.png',   'alt' => 'Estadísticas',   'title' => 'Agregar Ayuda'],
      ['url' => 'agregusu.php',   'img' => '../imagenes/estadisticas.png',   'alt' => 'Estadísticas',   'title' => 'Agregar Usuario'],
        ['url' => 'estadisticas.php',   'img' => '../imagenes/estadisticas.png',   'alt' => 'Estadísticas',   'title' => 'Listado Total'],
        ['url' => 'proveedor.php',      'img' => '../imagenes/beneficiarios.png',  'alt' => 'Proveedor',      'title' => 'Proveedor'],
        ['url' => 'beneficiario.php',   'img' => '../imagenes/beneficiarios.png',  'alt' => 'Beneficiarios',  'title' => 'Beneficiarios'],
        ['url' => 'solicitud.php',      'img' => '../imagenes/proveedor.png',      'alt' => 'Solicitud',      'title' => 'Solicitud'],
        ['url' => 'tabla_planilla.php', 'img' => '../imagenes/planillas.png',      'alt' => 'Planillas',      'title' => 'Planillas'],
        ['url' => 'anular_solicitud.php','img' => '../imagenes/proveedor.png',     'alt' => 'Anulación',      'title' => 'Anulación'],
        ['url' => 'punto_cuenta.php',   'img' => '../imagenes/aprobacion.png',     'alt' => 'Punto Cuenta',   'title' => 'Punto Cuenta'],
        ['url' => 'aprobacion_punto_cuenta.php', 'img' => '../imagenes/aprobacion.png', 'alt' => 'Aprobación', 'title' => 'Aprobación'],
        ['url' => 'rechazo_punto_cuenta.php', 'img' => '../imagenes/aprobacion.png', 'alt' => 'Rechazo', 'title' => 'Rechazo'],
        ['url' => 'logout.php', 'img' => '../imagenes/aprobacion.png', 'alt' => 'Cerrar Sesión', 'title' => 'Cerrar Sesión']
   
    ];

    foreach ($menu_items as $item) {
        $clase_activa = ($pagina_actual === $item['url']) ? 'activo' : '';
        echo "<a href=\"{$item['url']}\" class=\"{$clase_activa}\">\n";
        echo "  <span class=\"icon\"><img src=\"{$item['img']}\" alt=\"{$item['alt']}\" class=\"icono2\"></span>\n";
        echo "  <span class=\"title\">{$item['title']}</span>\n";
        echo "</a>\n";
    }
    ?>
  </nav>

</body>
</html>