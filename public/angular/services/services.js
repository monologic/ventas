app.factory("tipoDocumento", function(){

    documentoTipos = [{
        codigo: '0',
        descripcion: 'DOC.TRIB.NO.DOM.SIN.RUC'
        }, {
        codigo: '1',
        descripcion: 'DOC. NACIONAL DE IDENTIDAD'
        }, {
        codigo: '4',
        descripcion: 'CARNET DE EXTRANJERIA'
        }, {
        codigo: '6',
        descripcion: 'REG. UNICO DE CONTRIBUYENTES (RUC)'
        }, {
        codigo: '7',
        descripcion: 'PASAPORTE'
        }, {
        codigo: 'A',
        descripcion: 'CED. DIPLOMATICA DE IDENTIDAD'
        }
    ];

    return documentoTipos;
});

app.factory("unidadesDeMedida", function(){

    unidadesDeMedida = [{
        codigo: 'NIU',
        descripcion: 'Unidad/es'
        }, {
        codigo: 'KGM',
        descripcion: 'Kilogramo/s'
        }
        , {
        codigo: 'LTR',
        descripcion: 'Litro/s'
        }
    ];

    return unidadesDeMedida;
});
app.factory("igvAfectaciones", function(){

    igvAfectaciones = [{
        codigo: '10',
        descripcion: 'Gravado - Operación Onerosa'
        }, {
        codigo: '11',
        descripcion: 'Gravado – Retiro por premio'
        }, {
        codigo: '12',
        descripcion: 'Gravado – Retiro por donación'
        }, {
        codigo: '13',
        descripcion: 'Gravado – Retiro'
        }, {
        codigo: '14',
        descripcion: 'Gravado – Retiro por publicidad'
        }, {
        codigo: '15',
        descripcion: 'Gravado – Bonificaciones'
        }, {
        codigo: '16',
        descripcion: 'Gravado – Retiro por entrega a trabajadores'
        }, {
        codigo: '20',
        descripcion: 'Exonerado - Operación Onerosa'
        }, {
        codigo: '30',
        descripcion: 'Inafecto - Operación Onerosa'
        }, {
        codigo: '31',
        descripcion: 'Inafecto – Retiro por Bonificación'
        }, {
        codigo: '32',
        descripcion: 'Inafecto – Retiro'
        }, {
        codigo: '33',
        descripcion: 'Inafecto – Retiro por Muestras Médicas'
        }, {
        codigo: '34',
        descripcion: 'Inafecto - Retiro por Convenio Colectivo'
        }, {
        codigo: '35',
        descripcion: 'Inafecto – Retiro por premio'
        }, {
        codigo: '36',
        descripcion: 'Inafecto - Retiro por publicidad'
        }, {
        codigo: '40',
        descripcion: 'Exportación'
        }
    ];

    return igvAfectaciones;
});