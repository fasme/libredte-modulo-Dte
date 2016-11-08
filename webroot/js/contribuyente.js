var email_config = {
    'gmail.com': {
        'smtp': 'ssl://smtp.gmail.com:465',
        'imap': '{imap.gmail.com:993/imap/ssl}INBOX'
    }
};

function config_email_set(email, uso) {
    var dominio, status;
    status = Form.check_email(email);
    if (status !== true) {
        if (!__.empty(email.value))
            alert(status.replace('%s', 'Email'));
        return false;
    }
    dominio = email.value.substr(email.value.indexOf('@')+1);
    if (email_config[dominio] !== undefined ) {
        if (__.empty(document.getElementById('config_email_' + uso + '_smtpField').value))
            document.getElementById('config_email_' + uso + '_smtpField').value = email_config[dominio].smtp;
        if (__.empty(document.getElementById('config_email_' + uso + '_imapField').value))
            document.getElementById('config_email_' + uso + '_imapField').value = email_config[dominio].imap;
    }
}

function impuesto_adicional_sugerir_tasa(impuesto, impuestos_adicionales_tasa)
{
    var cols;
    if (impuestos_adicionales_tasa[impuesto.value] !== undefined) {
        cols = impuesto.parentNode.parentNode.parentNode.childNodes;
        cols[1].childNodes[0].childNodes[0].value = impuestos_adicionales_tasa[impuesto.value];
    }
}

function ambiente_set(en_certificacion) {
    if (en_certificacion==1) {
        $('#config_ambiente_produccion_fechaField').attr('disabled', 'disabled');
        $('#config_ambiente_produccion_numeroField').attr('disabled', 'disabled');
        $('#config_ambiente_certificacion_fechaField').removeAttr('disabled');
    } else {
        $('#config_ambiente_certificacion_fechaField').attr('disabled', 'disabled');
        $('#config_ambiente_produccion_fechaField').removeAttr('disabled');
        $('#config_ambiente_produccion_numeroField').removeAttr('disabled');
    }
}
