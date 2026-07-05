        </div><!-- /.auth-box -->

        <!-- Rodapé da página -->
        <footer class="page-footer" role="contentinfo">
            <nav class="footer-links" aria-label="Links legais">
                <a href="/privacidade">Política de Privacidade</a>
                <a href="/termos">Termos de Uso</a>
                <a href="/status" target="_blank" rel="noopener">Status da Plataforma</a>
            </nav>
            <div class="footer-version">
                <span class="status-dot" title="Todos os sistemas operacionais" aria-label="Sistema operacional"></span>
                VOXEL Copilot Enterprise 1.0
            </div>
        </footer>

    </main><!-- /.auth-panel -->

</div><!-- /.auth-layout -->

<script>
// ── TOGGLE SENHA ──
document.querySelectorAll('.btn-eye').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const wrap  = this.closest('.field-wrap');
        const input = wrap ? wrap.querySelector('input[type="password"], input[type="text"]') : null;
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        const icon = this.querySelector('i');
        if (icon) icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        this.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
    });
});

// ── SELETOR DE IDIOMA ──
document.querySelectorAll('.lang-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.lang-btn').forEach(function(b) {
            b.classList.remove('active');
            b.setAttribute('aria-pressed', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-pressed', 'true');
    });
});

// ── MÁSCARA CEP ──
document.querySelectorAll('[data-mask="cep"]').forEach(function(el) {
    el.addEventListener('input', function() {
        let v = this.value.replace(/\D/g,'').substring(0,8);
        if (v.length > 5) v = v.substring(0,5) + '-' + v.substring(5);
        this.value = v;
    });
});

// ── BUSCA CEP VIA VIACEP ──
document.querySelectorAll('[data-cep-trigger]').forEach(function(el) {
    el.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g,'');
        if (cep.length !== 8) return;
        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.erro) return;
                var f = function(id) { return document.getElementById(id); };
                if (f('logradouro')) f('logradouro').value = d.logradouro || '';
                if (f('bairro'))     f('bairro').value     = d.bairro     || '';
                if (f('cidade'))     f('cidade').value     = d.localidade || '';
                if (f('estado'))     f('estado').value     = d.uf         || '';
            })
            .catch(function() {});
    });
});
</script>
</body>
</html>
