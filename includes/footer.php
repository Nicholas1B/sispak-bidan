        </div><!-- /.page-content -->
    </main><!-- /.main-content -->
</div><!-- /.app-wrapper -->

<!-- Bootstrap 5 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= asset('js/main.js') ?>"></script>
<?php if (isset($extraJs)) echo $extraJs; ?>

<footer class="app-footer d-none">
    <span>&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; <?= APP_SUBTITLE ?></span>
</footer>

</body>
</html>
