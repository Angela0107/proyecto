<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú Derecha con Font Awesome</title>
  <!-- Font Awesome 6 Free (CDN) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2P3xG6u1X6B+3xqZ1z5K4U2kR8LJ8Z4KQfZxR2yV2Vz3z4RzZ7vJ5VfRz5A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      color: #333;
      overflow-x: hidden;
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.4);
      z-index: 997;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.35s ease, visibility 0.35s ease;
    }

    .overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .sidebar-right {
      position: fixed;
      top: 0;
      right: 0;
      width: 260px;
      height: 100vh;
      background: #ffffff;
      box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
      z-index: 999;
      transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      display: flex;
      flex-direction: column;
    }

    .sidebar-right.collapsed {
      width: 72px;
    }

    .sidebar-header {
      position: relative;
      padding: 16px 16px 12px;
    }

    .sidebar-toggle {
      position: absolute;
      top: 12px;
      right: 12px;
      background: #f1f3f4;
      color: #5f6368;
      border: none;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
    }

    .sidebar-toggle:hover {
      background: #e0e0e0;
      transform: scale(1.05);
    }

    .menu {
      list-style: none;
      padding: 0 12px;
      flex: 1;
      overflow-y: auto;
    }

    .menu li {
      margin-bottom: 6px;
    }

    .menu a {
      display: flex;
      align-items: center;
      padding: 14px 16px;
      text-decoration: none;
      color: #2c3e50;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.25s ease;
      white-space: nowrap;
    }

    .menu a:hover {
      background: #f1f5f9;
      color: #1a73e8;
    }

    .menu-item-icon {
      width: 20px;
      text-align: center;
      margin-right: 14px;
      font-size: 18px;
      transition: margin-right 0.3s;
    }

    .sidebar-right.collapsed .menu-item-icon {
      margin-right: 0;
    }

    .menu-item-title {
      font-size: 15px;
      opacity: 1;
      transition: opacity 0.3s, margin-left 0.3s;
    }

    .sidebar-right.collapsed .menu-item-title {
      opacity: 0;
      margin-left: -10px;
      width: 0;
      overflow: hidden;
    }

    .menu hr {
      border: 0;
      height: 1px;
      background: #eaeaea;
      margin: 14px 0;
      width: calc(100% - 24px);
      margin-left: 12px;
    }

    .sidebar-right.collapsed hr {
      display: none;
    }

    .section-header {
      text-align: center;
      padding: 10px 0 6px;
      color: #6c757d;
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      opacity: 1;
      transition: opacity 0.3s;
    }

    .sidebar-right.collapsed .section-header {
      opacity: 0;
      height: 0;
      padding: 0;
      overflow: hidden;
    }

    .expand-arrow {
      position: fixed;
      top: 50%;
      right: 0;
      transform: translateY(-50%);
      background: #2c3e50;
      color: white;
      width: 28px;
      height: 48px;
      border-radius: 4px 0 0 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      cursor: pointer;
      z-index: 998;
      box-shadow: -2px 0 8px rgba(0,0,0,0.15);
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s, visibility 0.3s;
    }

    .sidebar-right.collapsed ~ .expand-arrow {
      opacity: 1;
      visibility: visible;
    }

    .expand-arrow:hover {
      background: #1a252f;
    }

    /* Scrollbar */
    .menu {
      scrollbar-width: thin;
      scrollbar-color: #c1c7cd transparent;
    }
    .menu::-webkit-scrollbar { width: 6px; }
    .menu::-webkit-scrollbar-track { background: transparent; }
    .menu::-webkit-scrollbar-thumb { background-color: #c1c7cd; border-radius: 3px; }
  </style>
</head>
<body>

  <div class="overlay" id="overlay"></div>

  <aside class="sidebar-right" id="sidebar">
    <div class="sidebar-header">
      <button class="sidebar-toggle" id="toggleBtn">→</button>
    </div>

    <ul class="menu">
      <li>
        <a href="listado_total.php">
          <i class="fa-solid fa-list-check menu-item-icon"></i>
          <span class="menu-item-title">Listado Total</span>
        </a>
      </li>
      <hr>

      <li>
        <a href="proveedor.php">
          <i class="fa-solid fa-building menu-item-icon"></i>
          <span class="menu-item-title">Proveedor</span>
        </a>
      </li>
      <hr>

      <li>
        <a href="beneficiario.php">
          <i class="fa-solid fa-users menu-item-icon"></i>
          <span class="menu-item-title">Beneficiarios</span>
        </a>
      </li>
      <hr>

      <li class="section-header">Solicitud</li>

      <li>
        <a href="solicitud.php">
          <i class="fa-solid fa-file-lines menu-item-icon"></i>
          <span class="menu-item-title">Solicitud</span>
        </a>
      </li>
      <li>
        <a href="tabla_planilla.php">
          <i class="fa-solid fa-table menu-item-icon"></i>
          <span class="menu-item-title">Planillas</span>
        </a>
      </li>
      <li>
        <a href="anular_solicitud.php">
          <i class="fa-solid fa-ban menu-item-icon"></i>
          <span class="menu-item-title">Anulación</span>
        </a>
      </li>
      <hr>

      <li class="section-header">Punto Cuenta</li>

      <li>
        <a href="punto_cuenta.php">
          <i class="fa-solid fa-file-invoice menu-item-icon"></i>
          <span class="menu-item-title">Punto Cuenta</span>
        </a>
      </li>
      <li>
        <a href="aprobacion_punto_cuenta.php">
          <i class="fa-solid fa-circle-check menu-item-icon"></i>
          <span class="menu-item-title">Aprobación</span>
        </a>
      </li>
      <li>
        <a href="rechazo_punto_cuenta.php">
          <i class="fa-solid fa-circle-xmark menu-item-icon"></i>
          <span class="menu-item-title">Rechazo</span>
        </a>
      </li>
    </ul>
  </aside>

  <div class="expand-arrow" id="expandArrow">→</div>

  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');
    const expandArrow = document.getElementById('expandArrow');
    const overlay = document.getElementById('overlay');

    function toggleSidebar() {
      const isCollapsed = sidebar.classList.contains('collapsed');
      if (isCollapsed) {
        sidebar.classList.remove('collapsed');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      } else {
        sidebar.classList.add('collapsed');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    expandArrow.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && !sidebar.classList.contains('collapsed')) {
        toggleSidebar();
      }
    });
  </script>

</body>
</html>