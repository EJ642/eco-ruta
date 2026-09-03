function encabezadoPDF(doc, titulo, logoMEC, logoSanta, widthMapCustom, orientacion) {
    const ahora = new Date();
    const fecha = ahora.toLocaleDateString();
    const hora = ahora.toLocaleTimeString();

    const esHorizontal = (orientacion === 'landscape');
    const margenLateral = esHorizontal ? 35 : 40;
    const margenSuperior = 100;
    const margenInferior = 50;

    // Configuración de página
    doc.pageOrientation = esHorizontal ? 'landscape' : 'portrait';
    doc.pageSize = 'A4';
    doc.pageMargins = [margenLateral, margenSuperior, margenLateral, margenInferior];

    // Encabezado institucional
    doc.header = {
        margin: [margenLateral, 10, margenLateral, 0],
        columns: [
            logoMEC ? {
                image: logoMEC,
                width: 60,
                alignment: 'left'
            } : {
                text: '',
                width: 60
            },
            {
                width: '*',
                alignment: 'center',
                stack: [
                    {
                        text: 'DIRECCIÓN GENERAL DE EDUCACIÓN MEDIA',
                        bold: true,
                        fontSize: 9,
                        alignment: 'center',
                        color: '#1a1a1a'
                    },
                    {
                        text: 'INSTITUCIÓN EDUCATIVA DIOCESANA',
                        bold: true,
                        fontSize: 10,
                        alignment: 'center',
                        color: '#1a1a1a'
                    },
                    {
                        text: 'SANTA TERESITA',
                        bold: true,
                        fontSize: 12,
                        alignment: 'center',
                        color: '#1a1a1a'
                    },
                    {
                        text: 'Concepción - Paraguay',
                        fontSize: 8,
                        alignment: 'center',
                        color: '#4a4a4a'
                    },
                    {
                        text: titulo || '',
                        margin: [0, 4, 0, 0],
                        bold: true,
                        fontSize: 10,
                        alignment: 'center',
                        color: '#1a1a1a'
                    }
                ]
            },
            logoSanta ? {
                image: logoSanta,
                width: 60,
                alignment: 'right'
            } : {
                text: '',
                width: 60
            }
        ]
    };

    // Pie de página
    doc.footer = function(currentPage, pageCount) {
        return {
            margin: [margenLateral, 10, margenLateral, 10],
            columns: [
                {
                    text: 'Generado: ' + fecha + ' ' + hora,
                    alignment: 'left',
                    fontSize: 7,
                    color: '#6b7280'
                },
                {
                    text: 'Página ' + currentPage + ' de ' + pageCount,
                    alignment: 'right',
                    fontSize: 7,
                    color: '#6b7280'
                }
            ]
        };
    };

    // Estilos base
    doc.styles = {
        tableHeader: {
            bold: true,
            fontSize: 7,
            alignment: 'center',
            color: '#1a1a1a'
        },
        tableBody: {
            fontSize: 7,
            alignment: 'center',
            color: '#374151'
        }
    };

    doc.defaultStyle = {
        fontSize: 7,
        alignment: 'center',
        color: '#374151'
    };

    // Procesar el contenido de la tabla
    if (doc.content && doc.content.length) {
        for (var contentIndex = 0; contentIndex < doc.content.length; contentIndex++) {
            var item = doc.content[contentIndex];
            if (item && item.table) {
                var body = item.table.body;
                if (!body || !body.length) continue;

                var columnCount = body[0].length;
                var anchoPagina = esHorizontal ? 841.89 : 595.28;
                var anchoUtil = anchoPagina - (margenLateral * 2);

                // Si no hay widths definidos o están vacíos, calcular automáticamente
                if (!item.table.widths || item.table.widths.length === 0) {
                    var widths = [];

                    // Identificar columnas especiales
                    var esColumnaNombre = false;
                    var esColumnaAsistencia = false;
                    var esColumnaEstado = false;
                    var columnaEstadoIndex = -1;

                    if (body.length > 0) {
                        var primeraFila = body[0];
                        for (var i = 0; i < primeraFila.length; i++) {
                            var texto = '';
                            if (primeraFila[i] && typeof primeraFila[i] === 'object') {
                                texto = (primeraFila[i].text || '').toLowerCase();
                            } else {
                                texto = String(primeraFila[i] || '').toLowerCase();
                            }

                            if (texto.includes('nombre') || texto.includes('materia') || texto.includes('asignatura')) {
                                esColumnaNombre = true;
                            }
                            if (texto.includes('asistencia')) {
                                esColumnaAsistencia = true;
                            }
                            if (texto.includes('estado')) {
                                esColumnaEstado = true;
                                columnaEstadoIndex = i;
                            }
                        }
                    }

                    // Calcular anchos base
                    var anchoNombre = esColumnaNombre ? 75 : 65;
                    var anchoAsistencia = esColumnaAsistencia ? 22 : 18;
                    var anchoEstado = esColumnaEstado ? 28 : 22;

                    // Ajustar según cantidad de columnas
                    if (columnCount > 20) {
                        var factor = Math.min(1, Math.max(0.45, 14 / columnCount));
                        anchoNombre = Math.max(40, anchoNombre * factor);
                        anchoAsistencia = Math.max(12, anchoAsistencia * factor);
                        anchoEstado = Math.max(14, anchoEstado * factor);
                    }

                    // Contar columnas especiales
                    var columnasEspeciales = 0;
                    if (esColumnaNombre) columnasEspeciales++;
                    if (esColumnaAsistencia) columnasEspeciales++;
                    if (esColumnaEstado) columnasEspeciales++;

                    var columnasNormales = columnCount - columnasEspeciales;
                    var anchoEspeciales = 0;
                    if (esColumnaNombre) anchoEspeciales += anchoNombre;
                    if (esColumnaAsistencia) anchoEspeciales += anchoAsistencia;
                    if (esColumnaEstado) anchoEspeciales += anchoEstado;

                    var anchoRestante = anchoUtil - anchoEspeciales;
                    var anchoNormal = columnasNormales > 0 ? Math.floor(anchoRestante / columnasNormales) : 20;
                    var anchoMinimo = columnCount > 30 ? 8 : (columnCount > 20 ? 10 : 12);

                    if (anchoNormal < anchoMinimo) {
                        anchoNormal = anchoMinimo;
                    }

                    // Asignar widths según tipo de columna
                    for (var i = 0; i < columnCount; i++) {
                        var textoCol = '';
                        if (body[0][i] && typeof body[0][i] === 'object') {
                            textoCol = (body[0][i].text || '').toLowerCase();
                        } else {
                            textoCol = String(body[0][i] || '').toLowerCase();
                        }

                        if (textoCol.includes('nombre') || textoCol.includes('materia') || textoCol.includes('asignatura')) {
                            widths.push(anchoNombre);
                        } else if (textoCol.includes('asistencia')) {
                            widths.push(anchoAsistencia);
                        } else if (textoCol.includes('estado') || i === columnaEstadoIndex) {
                            widths.push(anchoEstado);
                        } else {
                            widths.push(anchoNormal);
                        }
                    }

                    // Verificar si cabe en la página y escalar si es necesario
                    var totalCalculado = 0;
                    for (var i = 0; i < widths.length; i++) {
                        totalCalculado += widths[i];
                    }

                    if (totalCalculado > anchoUtil) {
                        var factorEscala = anchoUtil / totalCalculado;
                        for (var i = 0; i < widths.length; i++) {
                            widths[i] = Math.floor(widths[i] * factorEscala);
                        }
                    }

                    item.table.widths = widths;
                }

                // Configurar layout profesional
                item.layout = {
                    hLineWidth: function(i, node) {
                        if (i === 0 || i === 1) return 0.8;
                        if (i === node.table.body.length) return 0.5;
                        return 0.3;
                    },
                    vLineWidth: function(i, node) {
                        return 0.3;
                    },
                    hLineColor: function(i, node) {
                        if (i === 0 || i === 1) return '#333333';
                        return '#999999';
                    },
                    vLineColor: function(i, node) {
                        return '#999999';
                    },
                    paddingLeft: function(i, node) {
                        if (columnCount > 25) return 1;
                        if (columnCount > 18) return 2;
                        return 3;
                    },
                    paddingRight: function(i, node) {
                        if (columnCount > 25) return 1;
                        if (columnCount > 18) return 2;
                        return 3;
                    },
                    paddingTop: function(i, node) {
                        if (columnCount > 25) return 1;
                        if (columnCount > 18) return 2;
                        return 3;
                    },
                    paddingBottom: function(i, node) {
                        if (columnCount > 25) return 1;
                        if (columnCount > 18) return 2;
                        return 3;
                    },
                    fillColor: function(i, node) {
                        if (i === 0 || i === 1) return null;
                        var row = node.table.body[i];
                        if (!row) return null;
                        return (i % 2 === 0) ? '#f9fafb' : null;
                    },

                    paddingRight: function(i, node) {
                        // Para la última columna, dar más padding
                        var row = node.table.body[i];
                        if (row && i === row.length - 1) {
                            return 8;  // Margen extra para la última columna
                        }
                        return columnCount > 25 ? 2 : 4;
                    }
                };

                // Configurar header rows
                if (!item.table.headerRows) {
                    item.table.headerRows = 2;
                }

                // Configurar para evitar saltos de fila
                if (!item.table.dontBreakRows) {
                    item.table.dontBreakRows = true;
                }

                // Procesar filas para ajustar fuente y padding si hay muchas columnas
                if (columnCount > 25) {
                    for (var i = 0; i < body.length; i++) {
                        var fila = body[i];
                        for (var j = 0; j < fila.length; j++) {
                            if (fila[j] && typeof fila[j] === 'object') {
                                if (!fila[j].fontSize) {
                                    fila[j].fontSize = 5.5;
                                }
                            }
                        }
                    }
                } else if (columnCount > 18) {
                    for (var i = 0; i < body.length; i++) {
                        var fila = body[i];
                        for (var j = 0; j < fila.length; j++) {
                            if (fila[j] && typeof fila[j] === 'object') {
                                if (!fila[j].fontSize) {
                                    fila[j].fontSize = 6;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

function encabezadoPrint(win, titulo, logoMEC, logoSanta){

    const fecha = new Date().toLocaleDateString();

    $(win.document.body).prepend(`

        <div style="margin-bottom:14px; font-size:10px;">

            <table class="tabla-encabezado" style="width:100%; border-collapse:collapse; font-size:10px; border:none;">

                <tr>

                    <td style="width:18%; text-align:left; vertical-align:middle; border:none;">
                        <img src="${logoMEC}" style="height:60px; max-width:100%;">
                    </td>

                    <td style="width:64%; text-align:center; vertical-align:middle; padding:0 10px; border:none;">

                        <div style="font-size:11pt; font-weight:bold; line-height:1.1;">
                            DIRECCIÓN GENERAL DE EDUCACIÓN MEDIA
                        </div>

                        <div style="font-size:12pt; font-weight:bold; line-height:1.1;">
                            INSTITUCIÓN EDUCATIVA DIOCESANA
                        </div>

                        <div style="font-size:13pt; font-weight:bold; line-height:1.1;">
                            SANTA TERESITA
                        </div>

                        <div style="font-size:9pt; line-height:1.1;">
                            Concepción - Paraguay
                        </div>

                        <div style="margin-top:8px; font-size:10pt; font-weight:bold;">
                            ${titulo}
                        </div>

                    </td>

                    <td style="width:18%; text-align:right; vertical-align:middle; border:none;">
                        <img src="${logoSanta}" style="height:60px; max-width:100%;">
                    </td>

                </tr>

            </table>

            <hr style="margin:10px 0; border-color:#444;">

            <div style="text-align:right; margin-bottom:8px; font-size:9pt;">
                Fecha de emisión: ${fecha}
            </div>

        </div>

    `);

    // quitar título automático de DataTables
    $(win.document.body).find('h1').remove();

    // estilos generales del body
    $(win.document.body).css({
        'font-family': 'Arial, sans-serif',
        'font-size': '10pt',
        'padding': '20px'
    });

    // La tabla del REPORTE será la última tabla del documento
    const $tablas = $(win.document.body).find('table');
    const $tablaReporte = $tablas.not('.tabla-encabezado').last();

    // estilos SOLO para la tabla del reporte
    $tablaReporte.css({
        'border-collapse': 'collapse',
        'width': '100%',
        'margin-top': '10px'
    });

    $tablaReporte.find('th, td').css({
        'border': '1px solid #444',
        'padding': '6px',
        'font-size': '9pt',
        'text-align': 'center'
    });

    $tablaReporte.find('thead th').css({
        'background-color': '#f1f1f1',
        'font-weight': 'bold'
    });

    // evitar bordes en la tabla del encabezado
    $(win.document.body).find('.tabla-encabezado td, .tabla-encabezado th').css({
        'border': 'none',
        'padding': '0'
    });
}