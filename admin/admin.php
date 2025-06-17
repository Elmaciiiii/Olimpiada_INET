<?php
require_once 'config.php';

// Procesar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'obtener_producto':
            $producto = obtenerProductoPorId($pdo, $_POST['id']);
            echo json_encode(['success' => !!$producto, 'producto' => $producto]);
            exit;
        case 'actualizar_producto':
            $resultado = actualizarProducto($pdo, $_POST);
            echo json_encode(['success' => $resultado]);
            exit;
        case 'guardar_producto':
            $resultado = guardarProducto($pdo, $_POST);
            echo json_encode(['success' => $resultado]);
            exit;
            
        case 'eliminar_producto':
            $resultado = eliminarProducto($pdo, $_POST['id']);
            echo json_encode(['success' => $resultado]);
            exit;
            
        case 'confirmar_pedido':
            $resultado = actualizarEstadoReserva($pdo, $_POST['id'], 2); // Estado confirmado
            echo json_encode(['success' => $resultado]);
            exit;
            
        case 'anular_pedido':
            $resultado = actualizarEstadoReserva($pdo, $_POST['id'], 5); // Estado cancelado
            echo json_encode(['success' => $resultado]);
            exit;
    }
}

// Obtener datos para mostrar
$estadisticas = obtenerEstadisticas($pdo);
$productos = obtenerProductos($pdo);
$pedidos_pendientes = obtenerPedidosPendientes($pdo);
$categorias = obtenerCategorias($pdo);
$tipos_paquete = obtenerTiposPaquete($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Paquetes Turísticos</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* {
   margin: 0;
   padding: 0;
   box-sizing: border-box;
}

:root {
   --primary-green: #10b981;
   --primary-green-dark: #059669;
   --primary-green-light: #34d399;
   --secondary-green: #6ee7b7;
   --accent-green: #047857;
   --success-green: #22c55e;
   --emerald: #50c878;
   --mint: #98fb98;
   --forest: #228b22;
   --sage: #9caf88;
   
   --neutral-50: #f9fafb;
   --neutral-100: #f3f4f6;
   --neutral-200: #e5e7eb;
   --neutral-300: #d1d5db;
   --neutral-400: #9ca3af;
   --neutral-500: #6b7280;
   --neutral-600: #4b5563;
   --neutral-700: #374151;
   --neutral-800: #1f2937;
   --neutral-900: #111827;
   
   --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
   --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
   --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
   --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
   --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
   --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
}

body {
   font-family: 'Inter', sans-serif;
   background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 25%, #a7f3d0 50%, #6ee7b7 75%, #34d399 100%);
   min-height: 100vh;
   color: var(--neutral-700);
   overflow-x: hidden;
   position: relative;
   display: flex;
   font-weight: 400;
   line-height: 1.6;
}

body::before {
   content: '';
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%2310b981" fill-opacity="0.02"><circle cx="30" cy="30" r="2"/><circle cx="15" cy="15" r="1"/><circle cx="45" cy="45" r="1"/></g></g></svg>');
   pointer-events: none;
   z-index: -1;
}

/* Sidebar */
.sidebar {
   width: 300px;
   background: rgba(255, 255, 255, 0.98);
   backdrop-filter: blur(20px);
   border-radius: 0 30px 30px 0;
   box-shadow: var(--shadow-2xl);
   border: 1px solid rgba(16, 185, 129, 0.1);
   position: fixed;
   height: 100vh;
   left: 0;
   top: 0;
   overflow-y: auto;
   z-index: 1000;
   border-right: 3px solid var(--primary-green);
}

.sidebar-header {
   padding: 2.5rem 2rem;
   background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
   border-radius: 0 30px 0 0;
   margin-bottom: 1.5rem;
   position: relative;
   overflow: hidden;
}

.sidebar-header::before {
   content: '';
   position: absolute;
   top: -50%;
   left: -50%;
   width: 200%;
   height: 200%;
   background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
   animation: shimmer 4s ease-in-out infinite;
}

@keyframes shimmer {
   0%, 100% { transform: rotate(0deg) scale(1); }
   50% { transform: rotate(180deg) scale(1.1); }
}

.sidebar-header h2 {
   font-size: 1.5rem;
   font-weight: 700;
   color: white;
   text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
   position: relative;
   z-index: 1;
   letter-spacing: -0.02em;
}

.nav-menu {
   list-style: none;
   padding: 1.5rem;
}

.nav-item {
   margin-bottom: 0.75rem;
}

.nav-link {
   display: flex;
   align-items: center;
   padding: 1.25rem 1.75rem;
   color: var(--neutral-600);
   text-decoration: none;
   transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
   border: none;
   background: none;
   width: 100%;
   text-align: left;
   cursor: pointer;
   border-radius: 18px;
   font-weight: 500;
   position: relative;
   overflow: hidden;
   font-size: 0.95rem;
}

.nav-link::before {
   content: '';
   position: absolute;
   top: 0;
   left: -100%;
   width: 100%;
   height: 100%;
   background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.1), transparent);
   transition: left 0.6s ease;
}

.nav-link:hover::before {
   left: 100%;
}

.nav-link:hover {
   background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.05));
   color: var(--primary-green);
   transform: translateX(8px);
   box-shadow: var(--shadow-md);
   border-left: 3px solid var(--primary-green);
}

.nav-link.active {
   background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark));
   color: white;
   transform: translateX(8px);
   box-shadow: var(--shadow-lg);
   border-left: 3px solid var(--accent-green);
}

.nav-icon {
   width: 24px;
   margin-right: 1.25rem;
   font-size: 1.3rem;
   text-align: center;
}

/* Main Content */
.main-content {
   margin-left: 300px;
   flex: 1;
   padding: 2.5rem;
   position: relative;
   z-index: 1;
}

.top-bar {
   background: rgba(255, 255, 255, 0.98);
   backdrop-filter: blur(20px);
   padding: 2rem 2.5rem;
   margin-bottom: 2.5rem;
   border-radius: 25px;
   box-shadow: var(--shadow-xl);
   border: 1px solid rgba(16, 185, 129, 0.1);
   position: relative;
   overflow: hidden;
}

.top-bar::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   right: 0;
   bottom: 0;
   background: linear-gradient(45deg, transparent 30%, rgba(16, 185, 129, 0.05) 50%, transparent 70%);
   transform: translateX(-100%);
   transition: transform 1s ease;
}

.top-bar:hover::before {
   transform: translateX(100%);
}

.top-bar h1 {
   font-size: 2.25rem;
   font-weight: 700;
   background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   background-clip: text;
   position: relative;
   z-index: 1;
   letter-spacing: -0.03em;
}

/* Content Sections */
.content-section {
   background: rgba(255, 255, 255, 0.98);
   backdrop-filter: blur(20px);
   border-radius: 30px;
   padding: 3rem;
   box-shadow: var(--shadow-xl);
   border: 1px solid rgba(16, 185, 129, 0.1);
   display: none;
   position: relative;
   overflow: hidden;
}

.content-section.active {
   display: block;
   animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeInUp {
   from {
       opacity: 0;
       transform: translateY(30px);
   }
   to {
       opacity: 1;
       transform: translateY(0);
   }
}

.section-title {
   font-size: 2rem;
   font-weight: 700;
   background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   background-clip: text;
   margin-bottom: 2.5rem;
   padding-bottom: 1.25rem;
   border-bottom: 3px solid transparent;
   border-image: linear-gradient(135deg, var(--primary-green), var(--accent-green)) 1;
   position: relative;
   letter-spacing: -0.02em;
}

.section-title::after {
   content: '';
   position: absolute;
   bottom: -3px;
   left: 0;
   width: 60px;
   height: 4px;
   background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
   border-radius: 2px;
}

/* Dashboard Cards */
.dashboard-cards {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
   gap: 2.5rem;
   margin-bottom: 2.5rem;
}

.dashboard-card {
   background: rgba(255, 255, 255, 0.95);
   backdrop-filter: blur(15px);
   padding: 2.5rem;
   border-radius: 25px;
   box-shadow: var(--shadow-lg);
   border: 1px solid rgba(16, 185, 129, 0.1);
   transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
   position: relative;
   overflow: hidden;
}

.dashboard-card::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   width: 100%;
   height: 5px;
   background: linear-gradient(90deg, var(--primary-green), var(--secondary-green), var(--emerald));
   border-radius: 25px 25px 0 0;
}

.dashboard-card:hover {
   transform: translateY(-8px);
   box-shadow: var(--shadow-2xl);
   border-color: var(--primary-green);
}

.dashboard-card .card-value {
   font-size: 2.75rem;
   font-weight: 700;
   background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   background-clip: text;
   margin-bottom: 0.75rem;
   letter-spacing: -0.02em;
}

.dashboard-card .card-label {
   color: var(--neutral-500);
   font-size: 1.1rem;
   font-weight: 500;
}

/* Form Styles */
.form-group {
   margin-bottom: 2rem;
}

.form-label {
   display: block;
   margin-bottom: 1rem;
   font-weight: 600;
   color: var(--neutral-700);
   font-size: 1rem;
}

.form-control {
   width: 100%;
   padding: 1.25rem 1.5rem;
   border: 2px solid rgba(16, 185, 129, 0.2);
   border-radius: 18px;
   font-size: 1rem;
   background: rgba(255, 255, 255, 0.9);
   backdrop-filter: blur(10px);
   transition: all 0.3s ease;
   color: var(--neutral-700);
   font-family: inherit;
}

.form-control:focus {
   outline: none;
   border-color: var(--primary-green);
   background: rgba(255, 255, 255, 0.98);
   box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.15);
   transform: translateY(-2px);
}

.form-control::placeholder {
   color: var(--neutral-400);
}

/* Form Rows */
.form-row {
   display: grid;
   grid-template-columns: 1fr 1fr;
   gap: 2rem;
   margin-bottom: 2rem;
}

.form-row-3 {
   display: grid;
   grid-template-columns: 1fr 1fr 1fr;
   gap: 2rem;
   margin-bottom: 2rem;
}

/* Buttons */
.btn {
   padding: 1rem 2rem;
   border: none;
   border-radius: 15px;
   cursor: pointer;
   font-size: 1rem;
   font-weight: 600;
   text-decoration: none;
   display: inline-block;
   text-align: center;
   transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
   position: relative;
   overflow: hidden;
   margin: 0.5rem;
   font-family: inherit;
   letter-spacing: 0.01em;
}

.btn::before {
   content: '';
   position: absolute;
   top: 0;
   left: -100%;
   width: 100%;
   height: 100%;
   background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
   transition: left 0.6s ease;
}

.btn:hover::before {
   left: 100%;
}

.btn-primary {
   background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark));
   color: white;
   box-shadow: var(--shadow-md);
   border: 2px solid transparent;
}

.btn-primary:hover {
   transform: translateY(-3px);
   box-shadow: var(--shadow-xl);
   background: linear-gradient(135deg, var(--primary-green-dark), var(--accent-green));
}

.btn-success {
   background: linear-gradient(135deg, var(--success-green), var(--emerald));
   color: white;
   box-shadow: var(--shadow-md);
}

.btn-success:hover {
   transform: translateY(-3px);
   box-shadow: var(--shadow-xl);
   background: linear-gradient(135deg, var(--emerald), var(--forest));
}

.btn-danger {
   background: linear-gradient(135deg, #ef4444, #dc2626);
   color: white;
   box-shadow: var(--shadow-md);
}

.btn-danger:hover {
   transform: translateY(-3px);
   box-shadow: var(--shadow-xl);
   background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.btn-warning {
   background: linear-gradient(135deg, #f59e0b, #d97706);
   color: white;
   box-shadow: var(--shadow-md);
}

.btn-warning:hover {
   transform: translateY(-3px);
   box-shadow: var(--shadow-xl);
   background: linear-gradient(135deg, #d97706, #b45309);
}

.btn-outline {
   background: transparent;
   color: var(--primary-green);
   border: 2px solid var(--primary-green);
}

.btn-outline:hover {
   background: var(--primary-green);
   color: white;
   transform: translateY(-3px);
   box-shadow: var(--shadow-xl);
}

.btn-sm {
   padding: 0.75rem 1.5rem;
   font-size: 0.9rem;
   margin: 0.25rem;
}

.btn-lg {
   padding: 1.25rem 2.5rem;
   font-size: 1.1rem;
   margin: 0.75rem;
}

/* Button Groups */
.btn-group {
   display: flex;
   flex-wrap: wrap;
   gap: 1rem;
   margin: 1.5rem 0;
}

.btn-group .btn {
   margin: 0;
}

/* Table Styles */
.table-container {
   overflow-x: auto;
   margin-top: 2rem;
   border-radius: 25px;
   box-shadow: var(--shadow-xl);
   background: rgba(255, 255, 255, 0.95);
   backdrop-filter: blur(15px);
   border: 1px solid rgba(16, 185, 129, 0.1);
}

.table {
   width: 100%;
   border-collapse: collapse;
   border-radius: 25px;
   overflow: hidden;
}

.table th,
.table td {
   padding: 1.25rem 1.5rem;
   text-align: left;
   border-bottom: 1px solid rgba(16, 185, 129, 0.1);
}

.table th {
   background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark));
   color: white;
   font-weight: 600;
   font-size: 1rem;
   text-transform: uppercase;
   letter-spacing: 0.05em;
}

.table tbody tr {
   transition: all 0.3s ease;
}

.table tbody tr:hover {
   background: rgba(16, 185, 129, 0.05);
   transform: scale(1.01);
}

.table tbody tr:nth-child(even) {
   background: rgba(16, 185, 129, 0.02);
}

/* Status Badges */
.status-badge {
   padding: 0.6rem 1.2rem;
   border-radius: 25px;
   font-size: 0.9rem;
   font-weight: 600;
   text-transform: uppercase;
   letter-spacing: 0.05em;
   display: inline-block;
}

.status-pendiente {
   background: linear-gradient(135deg, #fef3c7, #fde68a);
   color: #92400e;
}

.status-confirmada {
   background: linear-gradient(135deg, var(--secondary-green), var(--mint));
   color: var(--forest);
}

.status-cancelada {
   background: linear-gradient(135deg, #fecaca, #fca5a5);
   color: #7f1d1d;
}

/* Alerts */
.alert {
   padding: 1.5rem 2rem;
   margin-bottom: 2rem;
   border-radius: 20px;
   font-weight: 500;
   border: none;
   backdrop-filter: blur(10px);
   animation: slideInDown 0.4s ease-out;
   border-left: 5px solid;
}

@keyframes slideInDown {
   from {
       opacity: 0;
       transform: translateY(-30px);
   }
   to {
       opacity: 1;
       transform: translateY(0);
   }
}

.alert-success {
   background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05));
   color: var(--forest);
   border-left-color: var(--success-green);
}

.alert-danger {
   background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
   color: #7f1d1d;
   border-left-color: #ef4444;
}

.alert-warning {
   background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05));
   color: #92400e;
   border-left-color: #f59e0b;
}

.alert-info {
   background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
   color: var(--accent-green);
   border-left-color: var(--primary-green);
}

/* Checkboxes and Radio buttons */
input[type="checkbox"],
input[type="radio"] {
   width: 20px;
   height: 20px;
   margin-right: 0.75rem;
   accent-color: var(--primary-green);
   cursor: pointer;
}

/* Select dropdown */
select.form-control {
   cursor: pointer;
   background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
   background-position: right 0.75rem center;
   background-repeat: no-repeat;
   background-size: 1.5em 1.5em;
   padding-right: 3rem;
}

/* Floating Elements */
.floating-shapes {
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   pointer-events: none;
   z-index: -1;
}

.shape {
   position: absolute;
   opacity: 0.03;
}

.shape-1 {
   top: 15%;
   right: 10%;
   width: 120px;
   height: 120px;
   background: linear-gradient(45deg, var(--primary-green), var(--secondary-green));
   border-radius: 50%;
   animation: float1 10s ease-in-out infinite;
}

.shape-2 {
   bottom: 20%;
   left: 5%;
   width: 100px;
   height: 100px;
   background: linear-gradient(45deg, var(--emerald), var(--mint));
   transform: rotate(45deg);
   animation: float2 8s ease-in-out infinite reverse;
}

.shape-3 {
   top: 50%;
   right: 5%;
   width: 80px;
   height: 80px;
   background: linear-gradient(45deg, var(--accent-green), var(--forest));
   clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
   animation: float3 12s ease-in-out infinite;
}

@keyframes float1 {
   0%, 100% { transform: translateY(0px) rotate(0deg); }
   50% { transform: translateY(-40px) rotate(180deg); }
}

@keyframes float2 {
   0%, 100% { transform: translateY(0px) rotate(45deg); }
   50% { transform: translateY(-30px) rotate(225deg); }
}

@keyframes float3 {
   0%, 100% { transform: translateY(0px) rotate(0deg); }
   50% { transform: translateY(-35px) rotate(180deg); }
}

/* Utility Classes */
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left { text-align: left; }

.mt-1 { margin-top: 0.5rem; }
.mt-2 { margin-top: 1rem; }
.mt-3 { margin-top: 1.5rem; }
.mt-4 { margin-top: 2rem; }

.mb-1 { margin-bottom: 0.5rem; }
.mb-2 { margin-bottom: 1rem; }
.mb-3 { margin-bottom: 1.5rem; }
.mb-4 { margin-bottom: 2rem; }

.p-1 { padding: 0.5rem; }
.p-2 { padding: 1rem; }
.p-3 { padding: 1.5rem; }
.p-4 { padding: 2rem; }

.font-bold { font-weight: 700; }
.font-semibold { font-weight: 600; }
.font-medium { font-weight: 500; }

.text-sm { font-size: 0.9rem; }
.text-lg { font-size: 1.1rem; }
.text-xl { font-size: 1.25rem; }

/* Cards */
.card {
   background: rgba(255, 255, 255, 0.95);
   backdrop-filter: blur(15px);
   border-radius: 20px;
   padding: 2rem;
   box-shadow: var(--shadow-lg);
   border: 1px solid rgba(16, 185, 129, 0.1);
   margin-bottom: 2rem;
   transition: all 0.3s ease;
}

.card:hover {
   transform: translateY(-5px);
   box-shadow: var(--shadow-xl);
}

.card-header {
   border-bottom: 2px solid rgba(16, 185, 129, 0.1);
   padding-bottom: 1rem;
   margin-bottom: 1.5rem;
}

.card-title {
   font-size: 1.5rem;
   font-weight: 600;
   color: var(--primary-green);
   margin-bottom: 0.5rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
   .sidebar {
       width: 280px;
   }
   
   .main-content {
       margin-left: 280px;
       padding: 2rem;
   }
}

@media (max-width: 768px) {
   .sidebar {
       width: 100%;
       height: auto;
       position: static;
       border-radius: 0;
   }
   
   .main-content {
       margin-left: 0;
       padding: 1.5rem;
   }
   
   .form-row,
   .form-row-3 {
       grid-template-columns: 1fr;
       gap: 1.5rem;
   }
   
   .dashboard-cards {
       grid-template-columns: 1fr;
       gap: 1.5rem;
   }
   
   .top-bar {
       padding: 1.5rem;
   }
   
   .top-bar h1 {
       font-size: 1.75rem;
   }
   
   .content-section {
       padding: 2rem;
   }
   
   .table-container {
       font-size: 0.9rem;
   }
   
   .table th,
   .table td {
       padding: 1rem;
   }
   
   .btn-group {
       flex-direction: column;
   }
   
   .btn-group .btn {
       width: 100%;
   }
}

@media (max-width: 480px) {
   .sidebar-header {
       padding: 2rem 1.5rem;
   }
   
   .sidebar-header h2 {
       font-size: 1.3rem;
   }
   
   .nav-link {
       padding: 1rem 1.25rem;
       font-size: 0.9rem;
   }
   
   .content-section {
       padding: 1.5rem;
   }
   
   .btn {
       padding: 0.75rem 1.5rem;
       font-size: 0.95rem;
   }
   
   .form-control {
       padding: 1rem 1.25rem;
   }
   
   .dashboard-card {
       padding: 2rem;
   }
   
   .dashboard-card .card-value {
       font-size: 2.25rem;
   }
   
   .top-bar h1 {
       font-size: 1.5rem;
   }
   
   .section-title {
       font-size: 1.75rem;
   }
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <button class="nav-link active" data-section="dashboard">
                    <span class="nav-icon">🏠</span>
                    Dashboard
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-section="agregar-producto">
                    <span class="nav-icon">➕</span>
                    Agregar Paquete
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-section="lista-productos">
                    <span class="nav-icon">📦</span>
                    Lista de Productos
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-section="pedidos-pendientes">
                    <span class="nav-icon">📋</span>
                    Pedidos Pendientes
                </button>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Panel de Administración - Paquetes Turísticos</h1>
        </div>

        <!-- Dashboard -->
        <div class="content-section active" id="dashboard">
            <div class="section-title">Dashboard</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #007bff;">
                    <div style="font-size: 2rem; font-weight: bold; color: #007bff;"><?php echo $estadisticas['productos_activos']; ?></div>
                    <div style="color: #6c757d;">Productos Activos</div>
                </div>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #28a745;">
                    <div style="font-size: 2rem; font-weight: bold; color: #28a745;"><?php echo $estadisticas['pedidos_pendientes']; ?></div>
                    <div style="color: #6c757d;">Pedidos Pendientes</div>
                </div>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <div style="font-size: 2rem; font-weight: bold; color: #ffc107;"><?php echo $estadisticas['categorias']; ?></div>
                    <div style="color: #6c757d;">Categorías</div>
                </div>
            </div>
        </div>

        
        <!-- Agregar Producto -->
        <div class="content-section" id="agregar-producto">
            <div class="section-title">Agregar Nuevo Paquete Turístico</div>
            <form id="form-producto">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre del Paquete *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="descripcion">Descripción *</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label" for="precio">Precio *</label>
                        <input type="number" class="form-control" id="precio" name="precio" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="categoria">Categoría *</label>
                        <select class="form-control" id="categoria" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tipo_paquete">Tipo de Paquete *</label>
                        <select class="form-control" id="tipo_paquete" name="tipo_paquete_id" required>
                            <option value="">Seleccione tipo</option>
                            <?php foreach ($tipos_paquete as $tipo): ?>
                                <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="destino">Destino *</label>
                        <input type="text" class="form-control" id="destino" name="destino" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="duracion">Duración (días)</label>
                        <input type="number" class="form-control" id="duracion" name="duracion_dias" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="servicios_incluidos">Servicios Incluidos</label>
                    <textarea class="form-control" id="servicios_incluidos" name="servicios_incluidos" rows="3" placeholder="Ej: Transporte, Alojamiento, Desayuno, Guía..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="servicios_no_incluidos">Servicios NO Incluidos</label>
                    <textarea class="form-control" id="servicios_no_incluidos" name="servicios_no_incluidos" rows="3" placeholder="Ej: Almuerzo, Cena, Gastos personales..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="fecha_fin">Fecha de Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                    <label>
                        <input type="checkbox" id="disponible" name="disponible" checked> Disponible
                    </label>
                    <label>
                        <input type="checkbox" id="destacado" name="destacado"> Destacado
                    </label>
                </div>

                <button type="submit" class="btn btn-success">Guardar Paquete</button> <!-- El texto se actualizará dinámicamente en modo edición -->
            </form>
        </div>

        <!-- Lista de Productos -->
        <div class="content-section" id="lista-productos">
            <div class="section-title">Lista de Productos</div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Destino</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productos-list">
                        <?php foreach ($productos as $producto): ?>
                        <tr data-id="<?php echo $producto['id']; ?>">
                            <td><?php echo $producto['id']; ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['destino']); ?></td>
                            <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $producto['disponible'] ? 'status-confirmada' : 'status-cancelada'; ?>">
                                    <?php echo $producto['disponible'] ? 'Disponible' : 'No Disponible'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editarProducto(<?php echo $producto['id']; ?>)">Editar</button>
                                <button class="btn btn-danger btn-sm" onclick="eliminarProducto(<?php echo $producto['id']; ?>)">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>



      
<!-- =============================================== -->
<!-- 4. HTML PARA admin.php - SECCIÓN DE DISPONIBILIDAD -->
<!-- =============================================== -->

<div class="content-section" id="disponibilidad-productos">
    <div class="section-title">Gestión de Disponibilidad</div>
    <div class="alert alert-info">
        <strong>Información:</strong> Desde aquí puedes activar o desactivar la disponibilidad de tus productos de forma rápida.
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Producto</th>
                    <th>Destino</th>
                    <th>Precio</th>
                    <th>Categoría</th>
                    <th>Estado Actual</th>
                    <th>Cambiar Disponibilidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                <tr data-id="<?php echo $producto['id']; ?>">
                    <td><?php echo $producto['id']; ?></td>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($producto['destino']); ?></td>
                    <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $producto['disponible'] ? 'status-confirmada' : 'status-cancelada'; ?>" 
                              id="status-<?php echo $producto['id']; ?>">
                            <?php echo $producto['disponible'] ? 'Disponible' : 'No Disponible'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <?php if ($producto['disponible']): ?>
                                <button class="btn btn-warning btn-sm" 
                                        onclick="cambiarDisponibilidad(<?php echo $producto['id']; ?>, 0)">
                                    Desactivar
                                </button>
                            <?php else: ?>
                                <button class="btn btn-success btn-sm" 
                                        onclick="cambiarDisponibilidad(<?php echo $producto['id']; ?>, 1)">
                                    Activar
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Filtros rápidos -->
    <div class="card mt-4">
        <div class="card-header">
            <div class="card-title">Acciones Rápidas</div>
        </div>
        <div class="btn-group">
            <button class="btn btn-success" onclick="activarTodos()">
                Activar Todos los Productos
            </button>
            <button class="btn btn-warning" onclick="desactivarTodos()">
                Desactivar Todos los Productos
            </button>
            <button class="btn btn-outline" onclick="filtrarDisponibles()">
                Mostrar Solo Disponibles
            </button>
            <button class="btn btn-outline" onclick="filtrarNoDisponibles()">
                Mostrar Solo No Disponibles
            </button>
            <button class="btn btn-outline" onclick="mostrarTodos()">
                Mostrar Todos
            </button>
        </div>
    </div>
</div>

<!-- =============================================== -->
<!-- 5. JAVASCRIPT CORREGIDO -->
<!-- =============================================== -->

<script>
// Función para cambiar disponibilidad individual
function cambiarDisponibilidad(id, disponible) {
    const accion = disponible ? 'activar' : 'desactivar';
    
    if (confirm(`¿Estás seguro de que deseas ${accion} este producto?`)) {
        // Crear FormData para envío
        const formData = new FormData();
        formData.append('action', 'cambiar_disponibilidad');
        formData.append('id', id);
        formData.append('disponible', disponible);
        
        // Enviar petición AJAX
        fetch('admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarMensaje(`Producto ${accion}do exitosamente`, 'success');
                
                // Actualizar la interfaz
                const statusElement = document.getElementById(`status-${id}`);
                const row = document.querySelector(`tr[data-id="${id}"]`);
                const buttonCell = row.cells[6];
                
                if (disponible) {
                    statusElement.textContent = 'Disponible';
                    statusElement.className = 'status-badge status-confirmada';
                    buttonCell.innerHTML = `
                        <div class="btn-group">
                            <button class="btn btn-warning btn-sm" onclick="cambiarDisponibilidad(${id}, 0)">
                                Desactivar
                            </button>
                        </div>
                    `;
                } else {
                    statusElement.textContent = 'No Disponible';
                    statusElement.className = 'status-badge status-cancelada';
                    buttonCell.innerHTML = `
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm" onclick="cambiarDisponibilidad(${id}, 1)">
                                Activar
                            </button>
                        </div>
                    `;
                }
            } else {
                mostrarMensaje('Error al cambiar la disponibilidad: ' + (data.error || 'Error desconocido'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error de conexión', 'danger');
        });
    }
}

// Función para activar todos los productos
function activarTodos() {
    if (confirm('¿Activar todos los productos?')) {
        const filas = document.querySelectorAll('#disponibilidad-productos tbody tr');
        let promesas = [];
        
        filas.forEach(fila => {
            const id = fila.getAttribute('data-id');
            const statusElement = fila.querySelector('.status-badge');
            if (statusElement && statusElement.textContent.trim() === 'No Disponible') {
                const formData = new FormData();
                formData.append('action', 'cambiar_disponibilidad');
                formData.append('id', id);
                formData.append('disponible', 1);
                
                const promesa = fetch('admin.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json());
                
                promesas.push(promesa);
            }
        });
        
        Promise.all(promesas).then(() => {
            location.reload(); // Recargar página para actualizar todo
        });
    }
}

// Función para desactivar todos los productos
function desactivarTodos() {
    if (confirm('¿Desactivar todos los productos?')) {
        const filas = document.querySelectorAll('#disponibilidad-productos tbody tr');
        let promesas = [];
        
        filas.forEach(fila => {
            const id = fila.getAttribute('data-id');
            const statusElement = fila.querySelector('.status-badge');
            if (statusElement && statusElement.textContent.trim() === 'Disponible') {
                const formData = new FormData();
                formData.append('action', 'cambiar_disponibilidad');
                formData.append('id', id);
                formData.append('disponible', 0);
                
                const promesa = fetch('admin.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json());
                
                promesas.push(promesa);
            }
        });
        
        Promise.all(promesas).then(() => {
            location.reload(); // Recargar página para actualizar todo
        });
    }
}

// Funciones de filtrado
function filtrarDisponibles() {
    const filas = document.querySelectorAll('#disponibilidad-productos tbody tr');
    filas.forEach(fila => {
        const statusElement = fila.querySelector('.status-badge');
        if (statusElement && statusElement.textContent.trim() === 'Disponible') {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

function filtrarNoDisponibles() {
    const filas = document.querySelectorAll('#disponibilidad-productos tbody tr');
    filas.forEach(fila => {
        const statusElement = fila.querySelector('.status-badge');
        if (statusElement && statusElement.textContent.trim() === 'No Disponible') {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

function mostrarTodos() {
    const filas = document.querySelectorAll('#disponibilidad-productos tbody tr');
    filas.forEach(fila => {
        fila.style.display = '';
    });
}

// Función auxiliar para mostrar mensajes (si no existe)
function mostrarMensaje(mensaje, tipo) {
    // Si ya tienes esta función, déjala como está
    // Si no, puedes usar alert temporalmente:
    if (typeof mostrarMensaje === 'undefined') {
        alert(mensaje);
    }
}
</script>

<!-- =============================================== -->
<!-- 6. ELEMENTO PARA EL MENÚ LATERAL -->
<!-- =============================================== -->


<?php
// ===============================================
// 7. INSTRUCCIONES DE IMPLEMENTACIÓN
// ===============================================

/*
PASOS PARA IMPLEMENTAR:

1. AGREGA LA FUNCIÓN a config.php:
   - Copia la función cambiarDisponibilidadProducto()

2. EN admin.php:
   - Si YA TIENES un switch con cases, AGREGA el case 'cambiar_disponibilidad' dentro
   - Si NO TIENES switch, usa la alternativa con if

3. AGREGA EL HTML:
   - Copia la sección <div id="disponibilidad-productos">

4. AGREGA EL JAVASCRIPT:
   - Copia todo el script al final de tus scripts existentes

5. AGREGA EL MENÚ:
   - Copia el <li> del menú donde corresponda

6. VERIFICA:
   - Que config.php esté incluido en admin.php
   - Que la tabla productos tenga la columna 'disponible' (TINYINT)
   - Que la variable $productos esté definida con los datos necesarios

CONSULTA SQL PARA CREAR LA COLUMNA (si no existe):
ALTER TABLE productos ADD COLUMN disponible TINYINT(1) DEFAULT 1;
*/
?>
        <!-- Pedidos Pendientes -->
        <div class="content-section" id="pedidos-pendientes">
            <div class="section-title">Pedidos Pendientes</div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nº Reserva</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pedidos-list">
                        <?php if (empty($pedidos_pendientes)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #6c757d; padding: 2rem;">
                                No hay pedidos pendientes
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($pedidos_pendientes as $pedido): ?>
                            <tr data-id="<?php echo $pedido['id']; ?>">
                                <td><?php echo htmlspecialchars($pedido['numero_reserva']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellido_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['email_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['productos'] ?: 'Sin productos'); ?></td>
                                <td>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($pedido['fecha_reserva'])); ?></td>
                                <td><span class="status-badge status-pendiente"><?php echo htmlspecialchars($pedido['estado_nombre']); ?></span></td>
                                <td>
                                    <button class="btn btn-success btn-sm" onclick="confirmarPedido(<?php echo $pedido['id']; ?>)">Confirmar</button>
                                    <button class="btn btn-danger btn-sm" onclick="anularPedido(<?php echo $pedido['id']; ?>)">Anular</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Manejo de navegación
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const targetSection = this.getAttribute('data-section');
                
                // Remover clase active de todos los links
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Ocultar todas las secciones
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                // Mostrar sección seleccionada
                document.getElementById(targetSection).classList.add('active');
            });
        });

        // Función para realizar peticiones AJAX
        function enviarAjax(data) {
            return fetch('admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            }).then(response => response.json());
        }

        // Manejo del formulario de productos
        document.getElementById('form-producto').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            if (typeof editandoProductoId !== 'undefined' && editandoProductoId) {
                data.action = 'actualizar_producto';
                data.id = editandoProductoId;
            } else {
                data.action = 'guardar_producto';
            }
            data.disponible = document.getElementById('disponible').checked ? 1 : 0;
            data.destacado = document.getElementById('destacado').checked ? 1 : 0;
            
            enviarAjax(data).then(response => {
                if (response.success) {
                    mostrarMensaje('Producto guardado exitosamente', 'success');
                    this.reset();
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarMensaje('Error al guardar el producto', 'danger');
                }
            }).catch(error => {
                mostrarMensaje('Error de conexión','danger');
            });
        });

        // Función para mostrar mensajes
        function mostrarMensaje(mensaje, tipo = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo}`;
            alertDiv.textContent = mensaje;
            
            // Insertar al inicio del contenido activo
            const seccionActiva = document.querySelector('.content-section.active');
            seccionActiva.insertBefore(alertDiv, seccionActiva.firstChild);
            
            // Remover después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Función para eliminar producto
        function eliminarProducto(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                enviarAjax({
                    action: 'eliminar_producto',
                    id: id
                }).then(response => {
                    if (response.success) {
                        mostrarMensaje('Producto eliminado exitosamente', 'success');
                        // Remover la fila de la tabla
                        document.querySelector(`tr[data-id="${id}"]`).remove();
                    } else {
                        mostrarMensaje('Error al eliminar el producto', 'danger');
                    }
                }).catch(error => {
                    mostrarMensaje('Error de conexión', 'danger');
                });
            }
        }

        let editandoProductoId = null;
        // Función para editar producto
        function editarProducto(id) {
            enviarAjax({ action: 'obtener_producto', id: id }).then(response => {
                if (response.success && response.producto) {
                    const p = response.producto;
                    document.getElementById('nombre').value = p.nombre;
                    document.getElementById('descripcion').value = p.descripcion;
                    document.getElementById('precio').value = p.precio;
                    document.getElementById('categoria').value = p.categoria_id;
                    document.getElementById('tipo_paquete').value = p.tipo_paquete_id;
                    document.getElementById('destino').value = p.destino;
                    document.getElementById('duracion').value = p.duracion_dias;
                    document.getElementById('servicios_incluidos').value = p.servicios_incluidos || '';
                    document.getElementById('servicios_no_incluidos').value = p.servicios_no_incluidos || '';
                    document.getElementById('disponible').checked = p.disponible == 1;
                    document.getElementById('destacado').checked = p.destacado == 1;
                    document.getElementById('fecha_inicio').value = p.fecha_inicio || '';
                    document.getElementById('fecha_fin').value = p.fecha_fin || '';
                    editandoProductoId = p.id;
                    document.querySelector('#form-producto button[type="submit"]').textContent = 'Actualizar Paquete';
                    document.querySelector('[data-section="agregar-producto"]').click();
                } else {
                    mostrarMensaje('No se pudo cargar el paquete', 'danger');
                }
            });
        }

        // Función para confirmar pedido
        function confirmarPedido(id) {
            if (confirm('¿Confirmar este pedido?')) {
                enviarAjax({
                    action: 'confirmar_pedido',
                    id: id
                }).then(response => {
                    if (response.success) {
                        mostrarMensaje('Pedido confirmado exitosamente', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mostrarMensaje('Error al confirmar el pedido', 'danger');
                    }
                }).catch(error => {
                    mostrarMensaje('Error de conexión', 'danger');
                });
            }
        }

        // Función para anular pedido
        function anularPedido(id) {
            if (confirm('¿Anular este pedido?')) {
                enviarAjax({
                    action: 'anular_pedido',
                    id: id
                }).then(response => {
                    if (response.success) {
                        mostrarMensaje('Pedido anulado exitosamente', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mostrarMensaje('Error al anular el pedido', 'danger');
                    }
                }).catch(error => {
                    mostrarMensaje('Error de conexión', 'danger');
                });
            }
        }
    </script>
</body>
</html>