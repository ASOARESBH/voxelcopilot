</main><!-- /.main-content -->
</div><!-- /.app-layout -->

<script>
// Sidebar toggle mobile
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.getElementById('sidebar');
if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });
    document.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
}

// Mostrar toggle em mobile
function checkMobile() {
    if (sidebarToggle) {
        sidebarToggle.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
    }
}
checkMobile();
window.addEventListener('resize', checkMobile);

// Auto-fechar alertas
document.querySelectorAll('.alert[data-dismiss]').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity .4s';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 400);
    }, parseInt(el.dataset.dismiss) || 4000);
});
</script>
<?php if (isset($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
