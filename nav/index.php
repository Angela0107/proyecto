
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Bienvenida</title>
  <style>
    @import url('https://fonts.googleapis.com/css?family=Montserrat:400,600,700&display=swap');

    * {
      margin: 0;
      padding: 0;
      list-style: none;
      text-decoration: none;
      box-sizing: border-box;
      font-family: 'Montserrat', sans-serif;
    }

    body {
      background: #e1ecf2;
    }

    .wrapper {
      margin: 10px;
    }

    .wrapper .top_navbar {
      width: calc(100% - 20px);
      height: 90px;
      display: flex;
      position: fixed;
      top: 10px; Z-index: 1000;
    }

    .wrapper .top_navbar .hamburger {
      width: 217px;
      height: 100%;
      background: #2e4ead;
      padding: 15px 17px;
      border-top-left-radius: 20px;
      cursor: pointer;
      padding-left: 33px;

    }

    .wrapper .top_navbar .hamburger div {
      width: 35px;
      height: 4px;
      background: #92a6e2;
      margin: 5px 0;
      border-radius: 5px;
    }

    .wrapper .top_navbar .top_menu {
      width: calc(100% - 70px);
      height: 100%;
      background: #fff;
      border-top-right-radius: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
      z-index: 1000;
    }

    .wrapper .top_navbar .top_menu .logo {
      color: #2e4ead;
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 3px;
    }

    .wrapper .top_navbar .top_menu ul {
      display: flex;
      z-index: 1000;
    }

    .wrapper .top_navbar .top_menu ul li a {
      display: block;
      margin: 0 10px;
      width: 35px;
      height: 35px;
      line-height: 35px;
      text-align: center;
      border: 1px solid #2e4ead;
      border-radius: 50%;
      color: #2e4ead;
      z-index: 1000;
    }

    .wrapper .top_navbar .top_menu ul li a:hover {
      background: #4360b5;
      color: #fff;
    }

    .wrapper .top_navbar .top_menu ul li a:hover i {
      color: #fff;
    }

    .wrapper .sidebar {
      position: fixed;
      top: 100px;
      left: 10px;
      background: #2e4ead;
      width: 200px;
      height: calc(100% - 80px);
      border-bottom-left-radius: 20px;
      transition: all 0.3s ease;
    }

    .wrapper .sidebar ul li a {
      display: block;
      padding-left: 10px;
      padding-right: 0px;
      color: #fff;
      position: relative;
      margin-bottom: 1px;
      color: #92a6e2;
      white-space: nowrap;
      padding-top: 5px;
      padding-bottom: 5px;

    }

    .wrapper .sidebar ul li a:before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 3px;
      height: 100%;
      background: #92a6e2;
      display: none;
    }

    .wrapper .sidebar ul li a span.icon {
      margin-right: 10px;
      display: inline-block;
    }

    .wrapper .sidebar ul li a span.title {
      display: inline-block;
    }

    .wrapper .sidebar ul li a:hover,
    .wrapper .sidebar ul li a.active {
      background: #4360b5;
      color: #fff;
    }

    .wrapper .sidebar ul li a:hover:before,
    .wrapper .sidebar ul li a.active:before {
      display: block;
    }

    .wrapper .main_container {
      width: (100% - 200px);
      margin-top: 100px;
      margin-left: 200px;
      padding: 15px;
      transition: all 0.3s ease;
    }

    .wrapper .main_container2 {
      width: (100% - 200px);
      margin-top: 10px;
      margin-left: 200px;
      padding: 15px;
      transition: all 0.3s ease;
    }

    .wrapper .main_container .item {
      background: #fff;
      margin-bottom: 10px;
      padding: 15px;
      font-size: 14px;
      line-height: 22px;
    }

    .wrapper.collapse .sidebar {
      width: 70px;
    }

    .wrapper.collapse .sidebar ul h4 {
      display: none;
    }

    .wrapper.collapse .sidebar ul li a {
      text-align: center;
    }

    .wrapper.collapse .sidebar ul li a span.icon {
      margin: 0;
    }

    .wrapper.collapse .sidebar ul li a span.title {
      display: none;
    }

    .wrapper.collapse .main_container {
      width: (100% - 70px);
      margin-left: 70px;
    }

    .wrapper.collapse .main_container2 {
      width: (100% - 70px);
      margin-left: 70px;
    }

    .icono {
      margin-right: 10px;
      /* Espacio entre el icono y el texto */
      width: 25px;
      /* Ancho del icono */
      height: 25px;
      /* Alto del icono */
    }

    .icono2 {
      margin-right: 10px;
      /* Espacio entre el icono y el texto */
      width: 25px;
      /* Ancho del icono */
      height: 25px;
      /* Alto del icono */
      filter: invert(1);
    }

    #contador-correos {
      width: 30px;
      /* Ancho del círculo */
      height: 30px;
      /* Alto del círculo */
      font-size: 14px;
      /* Tamaño de fuente */
      color: #ffffff;
      /* Color de texto */
      background-color: #007BFF;
      /* Color de fondo */
      border-radius: 50%;
      /* Bordes redondeados para hacer un círculo */
      display: flex;
      /* Para centrar el texto vertical y horizontalmente */
      justify-content: center;
      /* Centrado horizontal */
      align-items: center;
      /* Centrado vertical */
      margin-left: 10px;
      /* Espacio a la izquierda del círculo */
    }

    #contador-correos2 {
      width: 30px;
      /* Ancho del círculo */
      height: 30px;
      /* Alto del círculo */
      font-size: 14px;
      /* Tamaño de fuente */
      color: #ffffff;
      /* Color de texto */
      background-color: #007BFF;
      /* Color de fondo */
      border-radius: 50%;
      /* Bordes redondeados para hacer un círculo */
      display: flex;
      /* Para centrar el texto vertical y horizontalmente */
      justify-content: center;
      /* Centrado horizontal */
      align-items: center;
      /* Centrado vertical */
      margin-left: 10px;
      /* Espacio a la izquierda del círculo */
    }

    .menu-item {
    display: flex; /* Usar flexbox para alinear el icono y el texto */
    align-items: center; /* Centrar verticalmente */
    padding: 10px; /* Espaciado interno */
    color: #92a6e2; /* Color del texto */
    transition: background 0.3s, color 0.3s; /* Transiciones suaves */
}

.menu-item:hover {
    background: #4360b5; /* Color de fondo al pasar el ratón */
    color: #fff; /* Color del texto al pasar el ratón */
}

.icon {
    margin-right: 20px; /* Espacio entre el icono y el texto */
}

.icono {
    width: 25px; /* Ancho del icono */
    height: 25px; /* Alto del icono */
    margin-top: 2px; /* Ajuste de margen superior */
}
  </style>

</head>

<body>
  <!-- partial:index.partial.html -->
  <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>

  <div class="wrapper">

    <div class="top_navbar">


      <div class="hamburger">
        <a href="estadisticas.php"><img src="../imagenes/logo.png" alt="" style="width: 150px;">
      </div>
      <div class="top_menu">
        <div class="logo">
          <a href="estadisticas.php"><img src="../imagenes/atencion_ciudad.png" alt="" style="width: 220px;"></a>
        </div>



        <p style="color:black">¡Bienvenido/a   <?php echo $nom_usu ?> al sistema de ayudas! </p>


        <ul>
          <li><a href="cambio_contra.php">
              <img src="../imagenes/perfil-del-usuario.png" alt="Icono 3" class="icono" style="    margin-top: 2px;    margin-left: 4px;"> <!-- Cambia la ruta -->
            </a></li>
          <li><a href="../logout.php">
              <img src="../imagenes/cerrar-sesion.png" alt="Icono 3" class="icono" style="    margin-top: 4px;    margin-left: 6px;"> <!-- Cambia la ruta -->
            </a></li>
        </ul>

      </div>
    </div>

    <div class="sidebar">
    <li>
    <a href="opcion_usu.php" class="menu-item">
        <span class="icon">
            <img src="../imagenes/usuarios.png" alt="Icono 3" class="icono">
        </span>
        <span class="title">Usuarios</span>
    </a>
</li>


      <ul>
        <li><a href="estadisticas.php">
            <span class="icon"><img src="../imagenes/estadisticas.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"> <!-- Cambia la ruta -->
              </i></span>
            <span class="title">Estadisticas</span>
          </a></li>
        <li><a href="beneficiario.php">
            <span class="icon"><img src="../imagenes/beneficiarios.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"></i></span>
            <span class="title">Beneficiario</span>
          </a></li>

        <li><a href="proveedor.php">
            <span class="icon"><img src="../imagenes/proveedor.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"></i></span>
            <span class="title">Proveedor</span>
          </a></li>

        <!-- Línea de separación movida hacia la izquierda -->
        <hr style="border: 1px solid #ccc; width: 90%; margin: 10px 0 10px 0; margin-left: 10px; ">

        <center>
          <h4 style="color:darkgray;">Tramites</h4>
        </center>

        <li><a href="tabla_planilla.php" class="">
            <span class="icon"><img src="../imagenes/planillas.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"></i></span>
            <span class="title">Planillas</span>
          </a></li>

        <li><a href="en_revision.php" class="">
            <span class="icon"><img src="../imagenes/en_revision.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"></i></span>
            <span class="title">En revision</span>
          </a></li>

          


          <li><a href="realizacion.php" class="">
            <span class="icon"><img src="../imagenes/aprobacion.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"></i></span>
            <span class="title">Realizacion</span>
          </a></li>

        <li><a href="aprobacion.php" class="">
            <span class="icon"><img src="../imagenes/aprobacion.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"></i></span>
            <span class="title">Aprobacion</span>
          </a></li>

        <li><a href="aprobados.php" class="">
            <span class="icon"><img src="../imagenes/aprobados.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"></i></span>
            <span class="title">Aprobados</span>
          </a></li>


        <li><a href="tram_recha.php" class="">
            <span class="icon"><img src="../imagenes/rechazado.png" alt="Icono 3" class="icono2" style="    margin-top: 4px;    margin-left: 4px;"></i></span>
            <span class="title">Rechazados</span>
          </a></li>

          <li><a href="tram_finalizados.php" class="">
            <span class="icon"><img src="../imagenes/tram_finalizados.png" alt="Icono 3" class="icono2" style="    margin-top: 2px;    margin-left: 4px;"></i></span>
            <span class="title">Finalizados</span>
          </a></li>
      </ul>
    </div>


  </div>


  <!-- partial -->
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script>
  <script>
    $(".hamburger").click(function() {
      $(".wrapper").toggleClass("collapse");
    });
  </script>

</body>

</html>