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
