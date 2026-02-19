const { registerBlockType } = wp.blocks;
const { PanelBody, SelectControl, DatePicker, TextControl } = wp.components;
const { InspectorControls } = wp.blockEditor;
const { Fragment } = wp.element;

registerBlockType("newestler/boletin", {
    edit({ attributes, setAttributes }) {

        return (
            <Fragment>

                <InspectorControls>
                    <PanelBody title="Configuración del boletín">

                        <TextControl
                            label="Categoría (slug o ID)"
                            value={attributes.categoria}
                            onChange={(val) => setAttributes({ categoria: val })}
                        />

                        <TextControl
                            label="Fecha inicio (YYYY-MM-DD)"
                            value={attributes.start_date}
                            onChange={(val) => setAttributes({ start_date: val })}
                        />

                        <TextControl
                            label="Fecha fin (YYYY-MM-DD)"
                            value={attributes.end_date}
                            onChange={(val) => setAttributes({ end_date: val })}
                        />

                        <TextControl
                            label="Límite de noticias"
                            type="number"
                            value={attributes.limit}
                            onChange={(val) => setAttributes({ limit: parseInt(val) })}
                        />

                    </PanelBody>
                </InspectorControls>

                <div style={{padding:"20px",border:"2px dashed #ccc"}}>
                    <h3>📰 Vista previa Newestler</h3>
                    <p>Este bloque generará el boletín automáticamente.</p>
                </div>

            </Fragment>
        );
    },

    save() {
        return null; // dinámico → usa PHP
    }
});
