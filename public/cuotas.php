<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';
$usuarioLogueado = isset($_SESSION['usuario']);
?>

<main class="container">
    <div style="text-align:center; margin-bottom:40px;">
        <h1>Elige tu Plan de Entrenamiento</h1>
        <p style="color:var(--muted)">Suscripciones automáticas para tu comodidad.</p>
    </div>
    
    <div class="pricing-grid">
        <div class="pricing-card">
            <h3>Mensual</h3>
            <div class="price">40€<span style="font-size:1rem; font-weight:normal;">/mes</span></div>
            <?php if($usuarioLogueado): ?>
                <div id="paypal-button-1"></div>
            <?php else: ?>
                <a href="login.php" class="btn-primary" style="width:100%; text-align:center;">Inicia sesión para pagar</a>
            <?php endif; ?>
        </div>

        <div class="pricing-card featured">
            <span class="ahorro">Ahorra 20€</span>
            <h3>Trimestral</h3>
            <div class="price">100€<span style="font-size:1rem; font-weight:normal;">/3 meses</span></div>
            <?php if($usuarioLogueado): ?>
                <div id="paypal-button-3"></div>
            <?php else: ?>
                <a href="login.php" class="btn-primary" style="width:100%; text-align:center;">Inicia sesión para pagar</a>
            <?php endif; ?>
        </div>

        <div class="pricing-card">
            <span class="ahorro">MEJOR PRECIO</span>
            <h3>Anual</h3>
            <div class="price">350€<span style="font-size:1rem; font-weight:normal;">/año</span></div>
            <?php if($usuarioLogueado): ?>
                <div id="paypal-button-12"></div>
            <?php else: ?>
                <a href="login.php" class="btn-primary" style="width:100%; text-align:center;">Inicia sesión para pagar</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if($usuarioLogueado): ?>
    <script src="https://www.paypal.com/sdk/js?client-id=AULgUNRMM4Es0qq5d31mGc3lwBavH_EwrYS87eQEGDCL6IkzQk3Jg8VBTea1vm-DvX0QRSCbfly0lw-4&currency=EUR"></script>
    <script>
        function renderBtn(id, precio, meses, nombre) {
            paypal.Buttons({
                style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'pay' },
                createOrder: (data, actions) => actions.order.create({
                    purchase_units: [{ amount: { value: precio }, description: 'Plan ' + nombre }]
                }),
                onApprove: (data, actions) => actions.order.capture().then(detalles => {
                    fetch('confirmar_suscripcion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ orderID: detalles.id, monto: precio, meses: meses, plan: nombre })
                    }).then(() => {
                        alert('¡Pago completado! Tu suscripción ' + nombre + ' está activa.');
                        window.location.href = '../dashboard/alumno.php';
                    });
                })
            }).render(id);
        }
        renderBtn('#paypal-button-1', '40.00', 1, 'Mensual');
        renderBtn('#paypal-button-3', '100.00', 3, 'Trimestral');
        renderBtn('#paypal-button-12', '350.00', 12, 'Anual');
    </script>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>