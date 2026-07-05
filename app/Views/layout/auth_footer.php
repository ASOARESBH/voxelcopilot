        </div><!-- /.auth-box -->
    </div><!-- /.auth-panel -->

</div><!-- /.auth-layout -->

<script>
// Máscara CEP
document.querySelectorAll('[data-mask="cep"]').forEach(function(el) {
    el.addEventListener('input', function() {
        let v = this.value.replace(/\D/g,'').substring(0,8);
        if (v.length > 5) v = v.substring(0,5) + '-' + v.substring(5);
        this.value = v;
    });
});

// Busca CEP via ViaCEP
document.querySelectorAll('[data-cep-trigger]').forEach(function(el) {
    el.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g,'');
        if (cep.length !== 8) return;
        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(r => r.json())
            .then(function(d) {
                if (d.erro) return;
                const f = function(id) { return document.getElementById(id); };
                if (f('logradouro')) f('logradouro').value = d.logradouro || '';
                if (f('bairro'))     f('bairro').value     = d.bairro     || '';
                if (f('cidade'))     f('cidade').value     = d.localidade || '';
                if (f('estado'))     f('estado').value     = d.uf         || '';
            })
            .catch(function() {});
    });
});

// Toggle senha
document.querySelectorAll('.btn-eye').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.querySelector('i').className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    });
});
</script>
</body>
</html>
