<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transporte Gutiérrez - EMBOL Coca-Cola</title>
    <style>
        /* Estilos para el header */
        .header {
            background-color: #F40009; /* Rojo Coca-Cola */
            color: white;
            padding: 0;
            margin: 0;
            width: 100%;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            padding-left: 20px;
        }
        
        .logo-container h1 {
            margin: 0;
            font-size: 1.5em;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .nav-menu {
            display: flex;
            height: 100%;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 0 25px;
            height: 100%;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background-color: #D10000; /* Rojo más oscuro al hover */
        }
        
        .nav-menu a.active {
            background-color: #A30000; /* Rojo más oscuro para activo */
            font-weight: bold;
        }
        
        .coca-cola-brand {
            background-color: white;
            color: #F40009;
            padding: 3px 10px;
            border-radius: 3px;
            margin-left: 10px;
            font-size: 0.8em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <h1>Transporte Gutiérrez <span class="coca-cola-brand">EMBOL Coca-Cola</span></h1>
        </div>
        
        <nav class="nav-menu">
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Inicio</a>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="tabla.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tabla.php' ? 'active' : ''; ?>">Resumen</a>
        </nav>
    </header>
    
    <!-- Espacio para evitar que el contenido quede detrás del header fijo -->
    <div style="height: 70px;"></div>