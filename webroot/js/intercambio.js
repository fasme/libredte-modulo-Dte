function intercambio_aceptar() {
    document.getElementById('EstadoRecepEnvField').value = 0;
    document.getElementById("RecepEnvGlosaField").value = 'Envío Recibido Conforme';
    $('select[name="EstadoRecepDTE[]"]').each(function (i, e) {
        $('select[name="EstadoRecepDTE[]"]').get(i).value = 0;
        $('input[name="RecepDTEGlosa[]"]').get(i).value = 'DTE Recibido OK';
        $('select[name="acuse[]"]').get(i).value = 1;
    });
    $('#btnRespuesta').click();
}

function intercambio_rechazar() {
    document.getElementById('EstadoRecepEnvField').value = 99;
    var glosa = prompt('¿Cuál es el motivo del rechazo?', 'Envío no corresponde');
    if (glosa != null) {
        document.getElementById("RecepEnvGlosaField").value = glosa;
    } else {
        document.getElementById("RecepEnvGlosaField").value = '';
    }
    $('select[name="EstadoRecepDTE[]"]').each(function (i, e) {
        $('select[name="EstadoRecepDTE[]"]').get(i).value = 99;
        $('input[name="RecepDTEGlosa[]"]').get(i).value = glosa;
        $('select[name="acuse[]"]').get(i).value = 0;
    });
    $('#btnRespuesta').click();
}
