<ul class="nav nav-pills pull-right">
<?php if (!$Emisor->config_ambiente_en_certificacion) : ?>
    <li>
        <a href="https://palena.sii.cl/cgi_dte/consultaDTE/wsDTEConsRecContHtml.cgi" title="Consultar documentos recibidos directamente en la web del SII" target="_blank">
            <span class="fa fa-bank"></span>
            Consultar en web del SII
        </a>
    </li>
    <li>
        <a href="https://www4.sii.cl/registrorechazodteInternet" title="Ir al registro de aceptación o reclamos de un DTE en el SII" target="_blank">
            <span class="fa fa-bank"></span>
            Aceptar/rechazar en SII
        </a>
    </li>
<?php endif; ?>
    <li>
        <a href="<?=$_base?>/dte/dte_recibidos/listar">
            Volver
        </a>
    </li>
</ul>
<h1>Buscar documentos recibidos en el SII</h1>
<p>Aquí podrá consultar los documentos que el SII recibió para la empresa <?=$Emisor->razon_social?>.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => !empty($_POST['desde']) ? $_POST['desde'] : $hoy,
    'check' => 'notempty date',
    'help' => 'Desde qué fecha de recepción en el SII buscar',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => !empty($_POST['hasta']) ? $_POST['hasta'] : $hoy,
    'check' => 'notempty date',
    'help' => 'Hasta qué fecha de recepción en el SII buscar',
]);
echo $f->end('Buscar documentos');

if (isset($documentos)) {
    $DteTipos = new \website\Dte\Admin\Mantenedores\Model_DteTipos();
    foreach ($documentos as &$d) {
        $acciones = '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/verificar_datos/'.$Emisor->getRUT().'/'.$d['dte'].'/'.$d['folio'].'/'.$d['emision'].'/'.$d['total'].'/'.$d['rut'].'\', 750, 550)" title="Verificar datos del documento en la web del SII" class="btn btn-default"><span class="fa fa-search"></span></a>';
        $d[] = \sowerphp\general\Utility_Date::count(\sowerphp\general\Utility_Date::format($d['recepcion'], 'Y-m-d'));
        $d[] = $acciones;
        if (is_numeric($d['dte'])) {
            $d['documento'] = $DteTipos->get($d['dte'])->tipo;
        }
        $d['emision'] = \sowerphp\general\Utility_Date::format($d['emision']);
        $d['recepcion'] = \sowerphp\general\Utility_Date::format($d['recepcion'], 'd/m/Y H:i');
        $d['total'] = num($d['total']);
        unset($d['dte']);
    }
    array_unshift($documentos, ['RUT', 'Razón social', 'Documento', 'Folio', 'Fecha emisión', 'Total', 'Fecha recepción', 'Track ID', 'Días', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($documentos, 'dte_recibidos_sii_'.$Emisor->rut.'_'.$_POST['desde'].'_'.$_POST['hasta'], true);
?>
<link rel="stylesheet" type="text/css" href="<?=$_base?>/css/jquery.dataTables.css" />
<script type="text/javascript" src="<?=$_base?>/js/jquery.dataTables.js"></script>
<script type="text/javascript"> $(document).ready(function(){ dataTable("#dte_recibidos_sii_<?=$Emisor->rut?>_<?=$_POST['desde']?>_<?=$_POST['hasta']?>"); }); </script>
<?php
}
