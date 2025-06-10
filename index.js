function showDescription() {
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; display: flex; align-items: center; justify-content: center;">
            <div style="background: linear-gradient(135deg, #ffffff, #f8f8f8); padding: 40px; border-radius: 20px; max-width: 500px; margin: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <h2 style="color: #333; margin-bottom: 20px; font-size: 1.8rem;">🏛️ Córdoba - Villa Carlos Paz</h2>
                <div style="color: #555; line-height: 1.6; margin-bottom: 30px;">
                    <p><strong>Pack Familiar incluye:</strong></p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li>🏨 Alojamiento para 4 personas</li>
                        <li>🍳 Desayuno incluido</li>
                        <li>🎠 Actividades para toda la familia</li>
                        <li>🎢 Acceso a parques temáticos</li>
                        <li>👨‍🏫 Guía turístico especializado</li>
                    </ul>
                    <p><em>¡Una experiencia inolvidable en el corazón de Argentina! 🇦🇷</em></p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" style="background: linear-gradient(135deg, #7db899, #6ba085); color: white; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-weight: 600;">Cerrar</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function addToCart() {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = '✅ Pack Familiar Córdoba agregado al carrito';
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 400);
    }, 3000);
}

function showFilter() {
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; display: flex; align-items: center; justify-content: center;">
            <div style="background: linear-gradient(135deg, #ffffff, #f8f8f8); padding: 40px; border-radius: 20px; max-width: 400px; margin: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <h2 style="color: #333; margin-bottom: 20px; font-size: 1.8rem;">🔍 Filtros Disponibles</h2>
                <div style="color: #555; line-height: 1.8;">
                    <p>💰 Por precio</p>
                    <p>📍 Por ubicación</p>
                    <p>📦 Por tipo de paquete</p>
                    <p>📅 Por fecha</p>
                    <p>⭐ Por calificación</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" style="background: linear-gradient(135deg, #7db899, #6ba085); color: white; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-weight: 600; margin-top: 20px;">Cerrar</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function showCart() {
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; display: flex; align-items: center; justify-content: center;">
            <div style="background: linear-gradient(135deg, #ffffff, #f8f8f8); padding: 40px; border-radius: 20px; max-width: 500px; margin: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <h2 style="color: #333; margin-bottom: 20px; font-size: 1.8rem;">🛒 Carrito de Compras</h2>
                <div style="color: #555; line-height: 1.6; margin-bottom: 20px;">
                    <p><strong>Productos agregados:</strong></p>
                    <div style="background: #f0f0f0; padding: 15px; border-radius: 10px; margin: 10px 0;">
                        <p>🏛️ Pack Familiar Córdoba - Villa Carlos Paz</p>
                        <p style="font-weight: bold; color: #7db899;">$89,999</p>
                    </div>
                    <p style="font-size: 1.2rem; font-weight: bold; color: #333;">Total: $89,999</p>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button onclick="alert('¡Compra realizada con éxito! 🎉'); this.parentElement.parentElement.parentElement.remove();" style="background: linear-gradient(135deg, #4CAF50, #45a049); color: white; border: none; padding: 12px 20px; border-radius: 25px; cursor: pointer; font-weight: 600; flex: 1;">Comprar</button>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" style="background: linear-gradient(135deg, #7db899, #6ba085); color: white; border: none; padding: 12px 20px; border-radius: 25px; cursor: pointer; font-weight: 600;">Cerrar</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}