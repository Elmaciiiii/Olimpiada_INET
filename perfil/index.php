<?php
session_start();

// Redirigir a login si no hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();
$usuario_id = $_SESSION['user_id'];

// Obtener información del usuario
$usuario_query = "SELECT id, nombre, apellido, email, rol_id, created_at FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($usuario_query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Obtener reservas del usuario
$reservas_query = "SELECT 
                    r.id, 
                    r.numero_reserva, 
                    r.fecha_reserva, 
                    r.fecha_inicio, 
                    r.fecha_fin, 
                    r.total as monto_total, 
                    r.estado_id, 
                    r.numero_personas as cantidad_personas, 
                    r.observaciones,
                    er.nombre as estado_nombre, 
                    er.color as estado_color 
                FROM reservas r
                JOIN estados_reserva er ON r.estado_id = er.id
                WHERE r.usuario_id = ?
                ORDER BY r.fecha_reserva DESC";
$stmt = $conn->prepare($reservas_query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$reservas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Turismo INET</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/perfil.css">
</head>
<body>
    <!-- Header -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    
    <div class="main-container">
        <header>
            <div class="logo">
                <a href="../index.php" style="color: inherit; text-decoration: none;">Turismo INET</a>
            </div>
            <nav class="nav-buttons">
                <a href="../index.php" class="nav-btn">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="../product/productos.php" class="nav-btn">
                    <i class="fas fa-box"></i> Productos
                </a>
                <a href="../perfil/" class="nav-btn active">
                    <i class="fas fa-user"></i> Perfil
                </a>
                <a href="../carrito/ver.php" class="nav-btn cart-btn" id="cart-button">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
                <a href="../login_register/logout.php" class="nav-btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
        </header>

        <div class="perfil-container">
            <!-- Sección de información del usuario -->
            <div class="perfil-info">
                <h1>Mi Perfil</h1>
                <div class="info-card">
                    <h2>Información Personal</h2>
                    <p><strong>ID:</strong> #<?php echo htmlspecialchars($usuario['id']); ?></p>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                    <p><strong>Rol:</strong> 
                        <?php 
                        $rol = 'Usuario';
                        if ($usuario['rol_id'] == 1) {
                            $rol = 'Cliente';
                        } elseif ($usuario['rol_id'] == 2) {
                            $rol = 'Administrador';
                        }
                        echo $rol;
                        ?>
                    </p>
                    <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></p>
                    <button class="btn-editar" onclick="mostrarFormularioEdicion()">Editar Perfil</button>
                </div>
            </div>

            <!-- Sección de reservas -->
            <div class="reservas-container">
    <h2>Reservas Pendientes</h2>
    <div id="pendientes-grid" class="reservas-grid">
        <?php 
        $hayPendientes = false;
        foreach ($reservas as $reserva): 
            if (isset($reserva['estado_nombre']) && trim($reserva['estado_nombre']) === 'Pendiente'): 
                $hayPendientes = true;
        ?>
            <div class="reserva-card" id="reserva-<?php echo $reserva['id']; ?>">
                <div class="reserva-header">
                    <h3>Reserva #<?php echo $reserva['id']; ?></h3>
                    <span class="estado" style="background-color: <?php echo $reserva['estado_color']; ?>">
                        <?php echo htmlspecialchars($reserva['estado_nombre']); ?>
                    </span>
                </div>
                <div class="reserva-body">
                    <p><strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])); ?></p>
                    <p><strong>Fecha de viaje:</strong> 
                        <?php 
                        if (!empty($reserva['fecha_inicio'])) {
                            echo date('d/m/Y', strtotime($reserva['fecha_inicio']));
                            if (!empty($reserva['fecha_fin'])) {
                                echo ' al ' . date('d/m/Y', strtotime($reserva['fecha_fin']));
                            }
                        } else {
                            echo 'No especificada';
                        }
                        ?>
                    </p>
                    <p><strong>Total:</strong> $<?php echo number_format($reserva['monto_total'], 2, ',', '.'); ?></p>
                    <p><strong>Personas:</strong> <?php echo $reserva['cantidad_personas'] ?? 1; ?></p>
                    <?php if (!empty($reserva['observaciones'])): ?>
                        <p><strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($reserva['observaciones'])); ?></p>
                    <?php endif; ?>
                </div>
                <div class="reserva-acciones">
                    <div class="reserva-actions">
                        <button class="btn-cancelar" onclick="confirmarCancelar(<?php echo $reserva['id']; ?>)">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </div>
            </div>
        <?php 
            endif; 
        endforeach; 
        if (!$hayPendientes): ?>
            <p style="grid-column: 1/-1;">No tienes reservas pendientes.</p>
        <?php endif; ?>
    </div>

    <button class="btn-historial" onclick="abrirHistorialModal()">Ver historial de reservas</button>
    <!-- Modal de historial de reservas -->
    <div id="modal-historial" class="modal" style="display:none;">
      <div class="modal-content modal-historial-content">
        <span class="close" onclick="cerrarHistorialModal()">&times;</span>
        <h2>Historial de Reservas</h2>
        <div id="historial-grid" class="reservas-grid">
          <?php 
          $hayHistorial = false;
           foreach ($reservas as $reserva): 
            if ($reserva['estado_nombre'] !== 'Pendiente'):
              $hayHistorial = true;
          ?>
            <div class="reserva-card" id="reserva-<?php echo $reserva['id'] ?? '-'; ?>">
              <div class="reserva-header">
                <h3>Reserva #<?php echo $reserva['id'] ?? '-'; ?></h3>
                <span class="estado" style="background-color: <?php echo $reserva['estado_color'] ?? '#ccc'; ?>">
                  <?php echo htmlspecialchars($reserva['estado_nombre'] ?? ''); ?>
                </span>
              </div>
              <div class="reserva-body">
                <p><strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'] ?? '')); ?></p>
                <p><strong>Fecha de viaje:</strong> 
                  <?php 
                  if (!empty($reserva['fecha_inicio'])) {
                    echo date('d/m/Y', strtotime($reserva['fecha_inicio']));
                    if (!empty($reserva['fecha_fin'])) {
                      echo ' al ' . date('d/m/Y', strtotime($reserva['fecha_fin']));
                    }
                  } else {
                    echo 'No especificada';
                  }
                  ?>
                </p>
                <p><strong>Total:</strong> $<?php echo number_format($reserva['monto_total'] ?? 0, 2, ',', '.'); ?></p>
                <p><strong>Personas:</strong> <?php echo $reserva['cantidad_personas'] ?? 1; ?></p>
                <?php if (isset($reserva['observaciones']) && !empty($reserva['observaciones'])): ?>
                    <p><strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($reserva['observaciones'])); ?></p>
                <?php endif; ?>
            </div>
          <?php 
            endif;
          endforeach;
          if (!$hayHistorial): ?>
            <p style="grid-column: 1/-1;">No tienes historial de reservas.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <script>
      function abrirHistorialModal() {
        document.getElementById('modal-historial').style.display = 'block';
      }
      function cerrarHistorialModal() {
        document.getElementById('modal-historial').style.display = 'none';
      }
      // Cerrar modal al hacer click fuera del contenido
      window.onclick = function(event) {
        var modal = document.getElementById('modal-historial');
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      }
    </script>
                                
            </div>
        </div>
    </div>

    <!-- Modal para editar cantidad -->
    <div id="modal-editar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Modificar Cantidad</h2>
            <form id="form-editar-cantidad" method="POST" action="actualizar_pedido.php">
                <input type="hidden" name="pedido_id" id="pedido-id">
                <div class="form-group">
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" min="1" required>
                </div>
                <button type="submit" class="btn-guardar">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div id="modal-eliminar" class="modal">
        <div class="modal-content">
            <h2>¿Estás seguro?</h2>
            <p>¿Deseas eliminar esta reserva de tu lista?</p>
            <div class="modal-acciones">
                <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <form id="form-eliminar-reserva" method="POST" action="eliminar_reserva.php">
                    <input type="hidden" name="reserva_id" id="reserva-eliminar-id">
                    <button type="submit" class="btn-eliminar-confirmar">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Función para ver los detalles de una reserva
        function verDetalles(reservaId) {
            // Aquí podrías redirigir a una página de detalles o mostrar un modal
            alert('Detalles de la reserva #' + reservaId + ' (función en desarrollo)');
        }

        // Función para confirmar la cancelación de una reserva
        function confirmarCancelar(reservaId) {
            if (confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
                cancelarReserva(reservaId);
            }
        }

        // Función para cancelar una reserva
        function cancelarReserva(reservaId) {
            fetch('cancelar_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `reserva_id=${reservaId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reserva cancelada correctamente');
                    // Eliminar la tarjeta de pendientes al cancelar
                    const card = document.getElementById(`reserva-${reservaId}`);
                    if (card) {
                        card.remove();
                    }
                    // Opcional: podrías mostrar un mensaje o refrescar el historial vía AJAX si lo deseas.
                } else {
                    alert('Error al cancelar la reserva: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al conectar con el servidor');
            });
        }

        // Función para mostrar el formulario de edición de perfil
        function mostrarFormularioEdicion() {
            alert('Función de edición de perfil en desarrollo.');
        }

        // Funciones para los modales
        function cerrarModal() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        }

        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                cerrarModal();
            }
        };

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModal();
            }
        });

        // Manejar envío del formulario de eliminación con AJAX
        document.getElementById('form-eliminar-reserva').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('eliminar_reserva.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Eliminar el elemento del DOM con animación
                    const reservaElement = document.getElementById(`reserva-${data.reserva_id}`);
                    if (reservaElement) {
                        reservaElement.style.animation = 'fadeOut 0.3s forwards';
                        
                        // Esperar a que termine la animación antes de eliminar
                        setTimeout(() => {
                            reservaElement.remove();
                            
                            // Verificar si no quedan más reservas
                            if (document.querySelectorAll('.reserva-card').length === 0) {
                                location.reload(); // Recargar para mostrar el mensaje de "No hay reservas"
                            }
                        }, 300);
                    }
                } else {
                    alert('Error al eliminar la reserva: ' + data.message);
                }
                cerrarModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
    </script>
</body>
</html>
