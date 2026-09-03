function encabezadoPDF(doc, titulo, logoMEC, logoSanta) {

    const now = new Date();
    const fecha = now.toLocaleDateString();
    const hora = now.toLocaleTimeString();

    doc.pageMargins = [40, 120, 40, 60];

    doc.header = {
        margin: [20, 10, 20, 0],

        columns: [

            {
                image: logoMEC,
                width: 70,
                alignment: 'left'
            },

            {
                width: '*',
                alignment: 'center',

                stack: [

                    {
                        text: 'DIRECCIÓN GENERAL DE EDUCACIÓN MEDIA',
                        bold: true,
                        fontSize: 10,
                        alignment: 'center'
                    },

                    {
                        text: 'INSTITUCIÓN EDUCATIVA DIOSCESANA',
                        bold: true,
                        fontSize: 12,
                        alignment: 'center'
                    },

                    {
                        text: 'SANTA TERESITA',
                        bold: true,
                        fontSize: 14,
                        alignment: 'center'
                    },

                    {
                        text: 'Concepción - Paraguay',
                        fontSize: 9,
                        alignment: 'center'
                    },

                    {
                        text: titulo,
                        margin: [0, 5, 0, 0],
                        bold: true,
                        fontSize: 11,
                        alignment: 'center'
                    }

                ]
            },

            {
                image: logoSanta,
                width: 70,
                alignment: 'right'
            }

        ]
    };

    doc.footer = function(currentPage, pageCount){

        return {

            margin:[20,10],

            columns:[

                {
                    text:
                    'Generado: ' +
                    fecha +
                    ' ' +
                    hora,

                    alignment:'left',
                    fontSize:8
                },

                {
                    text:
                    'Página ' +
                    currentPage +
                    ' de ' +
                    pageCount,

                    alignment:'right',
                    fontSize:8
                }

            ]
        };

    };

    doc.pageSize = 'A4';
    doc.pageOrientation = 'portrait';

    doc.styles = {
        tableHeader: {
            bold: true,
            fontSize: 10,
            alignment: 'center'
        }
    };

    doc.defaultStyle = {
        fontSize: 9,
        alignment: 'center'
    };

    if (doc.content && doc.content.length) {
        for (var contentIndex = 0; contentIndex < doc.content.length; contentIndex++) {
            var item = doc.content[contentIndex];
            if (item && item.table) {
                var body = item.table.body;
                if (!body || !body.length) {
                    continue;
                }

                var columnCount = body[0].length;
                var widths = [];
                var widthMap = {
                    0: 'auto',
                    1: '*',
                    2: '*',
                    3: 'auto'
                };

                for (var i = 0; i < columnCount; i++) {
                    widths.push(widthMap[i] || '*');
                }

                item.table.widths = widths;
                item.table.layout = 'lightHorizontalLines';
                item.table.headerRows = 1;
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