<?php
session_start();

// Verificar si el usuario no ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    // Redirigir a la página de inicio de sesión
    header('Location: /Olimpiada_INET/login_register/login.php');
    exit();
}

// Opcional: Verificar el rol del usuario si es necesario
// if ($_SESSION['user_role'] != 'admin') {
//     header('Location: /Olimpiada_INET/unauthorized.php');
//     exit();
// }
