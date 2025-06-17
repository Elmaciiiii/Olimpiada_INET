<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'sql300.infinityfree.com');
define('DB_USER', 'if0_39228166');
define('DB_PASS', '5YrrPCE4SlV');
define('DB_NAME', 'if0_39228166_olimpiada_inet');

function getDBConnection() {
    // Primero intentar conectar sin seleccionar base de datos
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        error_log("Error de conexión a MySQL: " . $conn->connect_error);
        die("Error de conexión a la base de datos. Por favor, inténtalo de nuevo más tarde.");
    }
    
    // Verificar si la base de datos existe
    $db_selected = $conn->select_db(DB_NAME);
    if (!$db_selected) {
        // Si la base de datos no existe, crearla
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if ($conn->query($sql) === TRUE) {
            $conn->select_db(DB_NAME);
            // Llamar a initializeDatabase para crear las tablas
            initializeDatabase($conn);
        } else {
            error_log("Error al crear la base de datos: " . $conn->error);
            die("Error al inicializar la base de datos. Por favor, contacta al administrador.");
        }
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Crear tablas si no existen
function initializeDatabase($conn) {
    if (!$conn) {
        error_log("Error: No se proporcionó una conexión válida a initializeDatabase");
        return false;
    }
    
    // Crear tabla de roles
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === FALSE) {
        error_log("Error al crear la tabla roles: " . $conn->error);
        return false;
    }
    
    // Insertar roles por defecto si no existen
    $result = $conn->query("SELECT COUNT(*) as count FROM roles");
    if ($result === FALSE) {
        error_log("Error al verificar roles: " . $conn->error);
        return false;
    }
    
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $insertRoles = $conn->query("INSERT INTO roles (nombre, descripcion) VALUES 
            ('user', 'Usuario normal'),
            ('ventas', 'Usuario de ventas')");
            
        if ($insertRoles === FALSE) {
            error_log("Error al insertar roles por defecto: " . $conn->error);
            return false;
        }
    }
    
    // Crear tabla de usuarios
    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        rol_id INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === FALSE) {
        error_log("Error al crear la tabla usuarios: " . $conn->error);
        return false;
    }
    
    // Crear tabla de categorías
    $sql = "CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT DEFAULT NULL,
        icono VARCHAR(50) DEFAULT NULL,
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === FALSE) {
        error_log("Error al crear la tabla categorias: " . $conn->error);
        return false;
    }
    
    // Insertar categorías por defecto si no existen
    $result = $conn->query("SELECT COUNT(*) as count FROM categorias");
    if ($result && $result->fetch_assoc()['count'] == 0) {
        $insertCategorias = $conn->query("INSERT INTO categorias (nombre, descripcion, icono) VALUES 
            ('Hoteles', 'Alojamientos y hospedajes', '🏨'),
            ('Paquetes Turísticos', 'Paquetes completos de viaje', '🎒'),
            ('Transporte', 'Servicios de transporte turístico', '🚌'),
            ('Actividades', 'Actividades y excursiones', '🏃‍♂️'),
            ('Gastronomía', 'Experiencias gastronómicas', '🍽️'),
            ('Aventura', 'Turismo de aventura y deportes', '🧗‍♂️')");
            
        if ($insertCategorias === FALSE) {
            error_log("Error al insertar categorías por defecto: " . $conn->error);
            return false;
        }
    }
    
    // Crear tabla de tipos de paquete
    $sql = "CREATE TABLE IF NOT EXISTS tipos_paquete (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === FALSE) {
        error_log("Error al crear la tabla tipos_paquete: " . $conn->error);
        return false;
    }
    
    // Insertar tipos de paquete por defecto si no existen
    $result = $conn->query("SELECT COUNT(*) as count FROM tipos_paquete");
    if ($result && $result->fetch_assoc()['count'] == 0) {
        $insertTipos = $conn->query("INSERT INTO tipos_paquete (nombre, descripcion) VALUES 
            ('Individual', 'Paquete para una persona'),
            ('Pareja', 'Paquete para dos personas'),
            ('Familiar', 'Paquete para familias con niños'),
            ('Grupo', 'Paquete para grupos grandes'),
            ('Corporativo', 'Paquete para empresas y eventos corporativos'),
            ('Luna de Miel', 'Paquete especial para parejas recién casadas')");
            
        if ($insertTipos === FALSE) {
            error_log("Error al insertar tipos de paquete por defecto: " . $conn->error);
            return false;
        }
    }
    
    // Crear tabla de productos
    $sql = "CREATE TABLE IF NOT EXISTS productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(200) NOT NULL,
        descripcion TEXT NOT NULL,
        descripcion_corta VARCHAR(300) DEFAULT NULL,
        precio DECIMAL(10,2) NOT NULL,
        categoria_id INT NOT NULL,
        tipo_paquete_id INT DEFAULT NULL,
        destino VARCHAR(100) NOT NULL,
        duracion_dias INT DEFAULT NULL,
        capacidad_min INT DEFAULT 1,
        capacidad_max INT DEFAULT NULL,
        incluye TEXT DEFAULT NULL,
        no_incluye TEXT DEFAULT NULL,
        imagen_principal VARCHAR(255) DEFAULT NULL,
        galeria_imagenes TEXT DEFAULT NULL,
        disponible TINYINT(1) DEFAULT 1,
        destacado TINYINT(1) DEFAULT 0,
        fecha_inicio DATE DEFAULT NULL,
        fecha_fin DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE,
        FOREIGN KEY (tipo_paquete_id) REFERENCES tipos_paquete(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === FALSE) {
        error_log("Error al crear la tabla productos: " . $conn->error);
        return false;
    }
    
    return true;
}

// Inicializar la base de datos (solo si se ejecuta directamente este archivo)
if (basename($_SERVER['PHP_SELF']) == 'database.php') {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    initializeDatabase($conn);
    $conn->close();
}
?>