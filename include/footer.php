<?php
// Footer mínimo usado por las vistas cuando no hay un footer personalizado.
?>
    <footer class="bg-light text-center text-lg-start mt-4">
        <div class="text-center p-3">
            &copy; <?php echo date('Y'); ?> <?php echo defined('APP_NAME') ? APP_NAME : 'Mi Aplicación'; ?>. Todos los derechos reservados.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
