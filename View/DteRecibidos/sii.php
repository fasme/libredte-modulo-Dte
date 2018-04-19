<ul class="nav nav-pills pull-right">
<?php if (!$Emisor->config_ambiente_en_certificacion) : ?>
    <li>
        <a href="https://palena.sii.cl/cgi_dte/consultaDTE/wsDTEConsRecContHtml.cgi" title="Consultar documentos recibidos directamente en la web del SII" target="_blank">
            <span class="fas fa-university"></span>
            Consultar en web del SII
        </a>
    </li>
    <li>
        <a href="https://www4.sii.cl/registrorechazodteInternet" title="Ir al registro de aceptación o reclamos de un DTE en el SII" target="_blank">
            <span class="fas fa-university"></span>
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
<div class="page-header"><h1>Buscar documentos recibidos en el SII</h1></div>
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
echo $f->input([
    'type' => 'select',
    'name' => 'excluir_en_libro',
    'label' => '¿Buscar todo?',
    'options' => ['Buscar todos los documentos recibidos en el SII', 'Buscar sólo los documentos que no están en el libro de compras'],
    'help' => '¿Excluir de la búsqueda lo que ya está ingresado al libro de compras?',
]);
echo $f->end('Buscar documentos');

if (isset($documentos)) {
    $DteTipos = new \website\Dte\Admin\Mantenedores\Model_DteTipos();
    foreach ($documentos as &$d) {
        $acciones = '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/verificar_datos/'.$Emisor->getRUT().'/'.$d['dte'].'/'.$d['folio'].'/'.$d['emision'].'/'.$d['total'].'/'.$d['rut'].'\', 750, 550)" title="Verificar datos del documento en la web del SII" class="btn btn-default"><span class="fa fa-search"></span></a>';
        $acciones .= ' <a href="#" onclick="__.popup(\''.$_base.'/dte/sii/dte_rcv/'.$d['rut'].'/'.$d['dte'].'/'.$d['folio'].'\', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII" class="btn btn-default"><span class="fa fa-eye"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/dte_rcv/'.$d['rut'].'/'.$d['dte'].'/'.$d['folio'].'" title="Ingresar acción del registro de compra/venta en el SII" class="btn btn-default"><span class="fa fa-check"></span></a>';
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
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setID('dte_recibidos_sii_'.$Emisor->rut.'_'.$_POST['desde'].'_'.$_POST['hasta']);
    $t->setExport(true);
    $t->setColsWidth([null, null, null, null, null, null, null, null, null, 120]);
    echo $t->generate($documentos);
?>
<link rel="stylesheet" type="text/css" href="<?=$_base?>/css/jquery.dataTables.css" />
<script type="text/javascript" src="<?=$_base?>/js/jquery.dataTables.js"></script>
<script type="text/javascript"> $(document).ready(function(){ dataTable("#dte_recibidos_sii_<?=$Emisor->rut?>_<?=$_POST['desde']?>_<?=$_POST['hasta']?>"); }); </script>
<?php
}
