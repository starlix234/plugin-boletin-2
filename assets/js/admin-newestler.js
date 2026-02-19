(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var insertBtn = document.getElementById('nw_insert_shortcode');
        if (!insertBtn) return;

        var status = document.getElementById('nw_insert_status');
        var example = document.getElementById('nw_shortcode_example');

        function buildShortcode(cat, start, end, limit) {
            var parts = ['[newestler_boletin'];
            if (cat && parseInt(cat) > 0) parts.push(' categoria="' + cat + '"');
            if (start) parts.push(' start_date="' + start + '"');
            if (end) parts.push(' end_date="' + end + '"');
            if (limit && parseInt(limit) > 0) parts.push(' limit="' + limit + '"');
            parts.push(']');
            return parts.join('');
        }

        function updateExample() {
            var cat = document.getElementById('nw_metabox_cat').value;
            var start = document.getElementById('nw_metabox_start').value;
            var end = document.getElementById('nw_metabox_end').value;
            var limit = document.getElementById('nw_metabox_limit').value;
            var sc = buildShortcode(cat, start, end, limit);
            example.textContent = sc;
        }

        // actualizar ejemplo en cambios
        var inputs = ['nw_metabox_cat','nw_metabox_start','nw_metabox_end','nw_metabox_limit'];
        inputs.forEach(function(id){ var el = document.getElementById(id); if(el) el.addEventListener('change', updateExample); });

        insertBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var cat = document.getElementById('nw_metabox_cat').value;
            var start = document.getElementById('nw_metabox_start').value;
            var end = document.getElementById('nw_metabox_end').value;
            var limit = document.getElementById('nw_metabox_limit').value;
            var shortcode = buildShortcode(cat, start, end, limit);

            // Intenta insertar en Gutenberg
            if ( typeof wp !== 'undefined' && wp.data && wp.blocks && wp.data.dispatch ) {
                try {
                    var block = wp.blocks.createBlock( 'core/shortcode', { text: shortcode } );
                    wp.data.dispatch( 'core/block-editor' ).insertBlocks( block );
                    status.textContent = 'Insertado en el editor (Gutenberg).';
                    return;
                } catch (err) {
                    console.log('Gutenberg insert error', err);
                }
            }

            // Classic editor fallback
            if ( typeof window.send_to_editor === 'function' ) {
                try {
                    window.send_to_editor(shortcode);
                    status.textContent = 'Insertado en el editor clásico.';
                    return;
                } catch (err) {
                    console.log('send_to_editor error', err);
                }
            }

            // Fallback: copiar al portapapeles y mostrar
            if ( navigator.clipboard && navigator.clipboard.writeText ) {
                navigator.clipboard.writeText(shortcode).then(function () {
                    status.innerHTML = 'Shortcode copiado al portapapeles. Pega donde quieras.';
                }).catch(function () {
                    prompt('Copia el shortcode manualmente (CTRL+C):', shortcode);
                });
            } else {
                prompt('Copia el shortcode manualmente (CTRL+C):', shortcode);
            }
        });
    });
})();
