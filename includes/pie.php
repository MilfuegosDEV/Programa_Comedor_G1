<?php
/*
 * PIE COMÚN (se incluye al final de cada página)
 *
 * Variables que puede definir la página:
 *   $scripts    Lista de archivos JS propios de la página.
 *               Ej: ['assets/js/datos-ejemplo.js', 'assets/js/inventario.js']
 *   $usarTablas true para cargar jQuery + DataTables + assets/js/app.js
 */
$scripts    = $scripts ?? [];
$usarTablas = $usarTablas ?? false;
?>
    </main>

    <footer class="pie">
        <p>Comedores Estudiantiles · Grupo 1 · SC-502 Universidad Fidélitas</p>
    </footer>

    <?php if ($usarTablas): ?>
        <!-- Librerías guardadas en el proyecto (funcionan sin internet) -->
        <script src="assets/vendor/jquery/jquery.min.js"></script>
        <script src="assets/vendor/datatables/dataTables.min.js"></script>
        <script src="assets/vendor/datatables/dataTables.dataTables.min.js"></script>
        <script src="assets/js/app.js"></script>
    <?php endif; ?>

    <?php foreach ($scripts as $script): ?>
        <script src="<?= e($script) ?>"></script>
    <?php endforeach; ?>
</body>

</html>
