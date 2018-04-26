<div class="page-header"><h1>Dte &raquo; Informes &raquo; Estado de DTEs emitidos</h1></div>
<p>Aquí podrá revisar los estados del envío al SII de documentos emitidos por el contribuyente <?=$Emisor->razon_social?>, como también acceder al detalle de los documentos por cada estado.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Formcheck()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => $desde,
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => $hasta,
    'check' => 'notempty date',
]);
echo $f->end('Buscar');

if ($documentos) {
    foreach ($documentos as &$d) {
        $d['total'] = num($d['total']);
        $d[] = '<a href="'.$_base.'/dte/informes/dte_emitidos/estados_detalle/'.$desde.'/'.$hasta.'/'.urlencode($d['estado']).'"><span class="fa fa-search btn btn-default"></span></a>';
    }
    array_unshift($documentos, ['Estado', 'Documentos', 'Ver detalle']);
    new \sowerphp\general\View_Helper_Table($documentos, 'emitidos_estados_'.$Emisor->rut, true);
}
