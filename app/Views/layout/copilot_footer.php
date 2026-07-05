</main><!-- /.main-content -->
</div><!-- /.app-layout -->

<script>
(function() {
    var toggle  = document.getElementById('sidebar-toggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) { overlay.classList.add('show'); overlay.setAttribute('aria-hidden','false'); }
        if (toggle)  toggle.setAttribute('aria-expanded','true');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) { overlay.classList.remove('show'); overlay.setAttribute('aria-hidden','true'); }
        if (toggle)  toggle.setAttribute('aria-expanded','false');
        document.body.style.overflow = '';
    }
    function checkMobile() {
        if (toggle) toggle.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
    }

    if (toggle)  toggle.addEventListener('click', function() { sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
    if (overlay) overlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });
    checkMobile();
    window.addEventListener('resize', checkMobile);
})();

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
