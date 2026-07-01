<?php

include 'nav/index.php';
include_once 'tema.php'; // Ej: include_once 'includes/tema.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gráfica de Ayudas</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    div.dt-container div.dt-layout-row {
      width: auto;
    }

    div.dt-container div.dt-layout-row div.dt-layout-cell {
      padding-left: 15px;
      padding-right: 15px;
    }

    #myChart {
      display: block;
      box-sizing: border-box;
      height: 654px;
      width: 700px;
      margin-left: 150px;
      margin-top: 50px;
    }

    footer {
      text-align: center;
      margin-top: 20px;
      color: #777;
    }

    @media (max-width: 600px) {
      #myChart {
        height: 300px;
      }
    }

    .form-inline {
      display: block;
    }

    .dataTables_wrapper .dataTables_filter {
      float: right;
      text-align: right;
      margin-bottom: 30px;
    }

    .form-inline {
      display: block;
    }

    .dataTables_wrapper .dataTables_filter {
      float: left;
      text-align: left;
      margin-bottom: 10px;
    }

    .table {
      width: 100%;
      margin: 20px 0;
      border: collapse;
    }

    .table th,
    .table td {
      padding: 12px;
      text-align: left;
      border: 1px solid #ddd;
    }

    .table th {
      background-color: #2e4ead;
      color: white;
    }

    .table tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    .table tr:hover {
      background-color: #ddd;
    }

    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
    }

    .btn-primary:hover {
      background-color: #0056b3;
      border-color: #0056b3;
    }

    .form-inline {
      display: block;
    }

    .dataTables_wrapper .dataTables_filter input {
      margin-left: 0.5em;
      border: 1px solid #007bff;
      border-radius: 4px;
      padding: 5px;
    }

    h2 {
      color: #343a40;
      margin-bottom: 20px;
    }

    form {
      margin-top: 20px;
    }


    .dataTables_wrapper .dataTables_filter input {
      width: 250px;
      padding: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
      margin-left: 150px;
    }

  </style>
</head>

<?php
include 'listadototal.php';
?>

</html>