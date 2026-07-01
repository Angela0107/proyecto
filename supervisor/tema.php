<?php
// tema.php — Sistema de tema claro/oscuro todo en uno

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Si se solicita cambiar el tema, actualizar la sesión y redirigir
if (basename($_SERVER['SCRIPT_NAME']) === 'tema.php' && isset($_GET['tema'])) {
    if (in_array($_GET['tema'], ['claro', 'oscuro'])) {
        $_SESSION['tema'] = $_GET['tema'];
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? './';
    header("Location: " . $referer);
    exit();
}

// 2. Establecer tema por defecto si no existe
if (!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'claro';
}
$tema_actual = $_SESSION['tema'];

// 3. Función para renderizar el botón de cambio de tema
function boton_cambiar_tema() {
    $tema_actual = $_SESSION['tema'];
    $siguiente_tema = ($tema_actual === 'oscuro') ? 'claro' : 'oscuro';
    $icono = ($tema_actual === 'oscuro') ? 'sun' : 'moon';
    $titulo = ($tema_actual === 'oscuro') ? 'Modo claro' : 'Modo oscuro';

    echo '<a href="tema.php?tema=' . htmlspecialchars($siguiente_tema) . '" 
              title="' . htmlspecialchars($titulo) . '" 
              style="display:inline-block; width:35px; height:35px; line-height:35px; text-align:center; 
                     border:1px solid #2e4ead; border-radius:50%; color:#2e4ead;">
            <i class="fas fa-' . $icono . '"></i>
          </a>';
}

// 4. Imprimir estilos CSS para ambos temas (solo una vez por página)
function imprimir_estilos_tema() {
    static $impreso = false;
    if ($impreso) return;
    $impreso = true;
    ?>
    <style>
    /* ===== Temas globales ===== */

    /* Tema claro */
    body.tema-claro {
      background-color: #e1ecf2;
      color: #000;
    }

    body.tema-claro .top_navbar .top_menu {
      background: #fff;
      box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    }

    body.tema-claro .top_navbar .top_menu .logo,
    body.tema-claro .top_navbar .top_menu ul li a {
      color: #2e4ead;
    }

    body.tema-claro .top_navbar .top_menu ul li a {
      border-color: #2e4ead;
    }

    body.tema-claro .sidebar {
      background: #2e4ead;
    }

    body.tema-claro .sidebar ul li a {
      color: #92a6e2;
    }

    body.tema-claro .sidebar ul li a:hover,
    body.tema-claro .sidebar ul li a.active {
      background: #4360b5;
      color: #fff;
    }

    body.tema-claro .main_container .item,
    body.tema-claro .main_container2 .item {
      background: #fff;
      color: #000;
    }

    /* Tema oscuro */
    body.tema-oscuro {
      background-color: #121212;
      color: #e0e0e0;
    }

    body.tema-oscuro .top_navbar .top_menu {
      background: #1e1e1e;
      box-shadow: 0 1px 1px rgba(255, 255, 255, 0.1);
    }

    body.tema-oscuro .top_navbar .top_menu .logo,
    body.tema-oscuro .top_navbar .top_menu ul li a {
      color: #a0b8f0;
    }

    body.tema-oscuro .top_navbar .top_menu ul li a {
      border-color: #a0b8f0;
    }

    body.tema-oscuro .sidebar {
      background: #1a237e;
    }

    body.tema-oscuro .sidebar ul li a {
      color: #bbdefb;
    }

    body.tema-oscuro .sidebar ul li a:hover,
    body.tema-oscuro .sidebar ul li a.active {
      background: #283593;
      color: #fff;
    }

    body.tema-oscuro .main_container .item,
    body.tema-oscuro .main_container2 .item {
      background: #1e1e1e;
      color: #e0e0e0;
    }

    /* Asegurar que los textos dentro de contenedores hereden el color */
    body.tema-oscuro .item h1,
    body.tema-oscuro .item h2,
    body.tema-oscuro .item h3,
    body.tema-oscuro .item p,
    body.tema-oscuro .item span {
      color: #e0e0e0;
    }

    body.tema-claro .item h1,
    body.tema-claro .item h2,
    body.tema-claro .item h3,
    body.tema-claro .item p,
    body.tema-claro .item span {
      color: #000;
    }
    </style>
    <?php
}
?>