<h1>Dte &raquo; Informes &raquo; Estado proceso de intercambio</h1>
<p>Aquí podrá revisar el estado del proceso de intercambio de documentos emitidos y envíados al SII por el contribuyente <?=$Emisor->razon_social?>, como también acceder al detalle de los documentos por cada estado.</p>
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
        $d[] = '<a href="'.$_base.'/dte/informes/dte_emitidos/intercambio_detalle/'.$desde.'/'.$hasta.'/'.(int)$d['recibo'].'/'.($d['recepcion']!==null?$d['recepcion']:-1).'/'.($d['resultado']!==null?$d['resultado']:-1).'"><span class="fa fa-search btn btn-default"></span></a>';
        $d['recibo'] = $d['recibo'] ? 'Si' : 'No';
        if ($d['recepcion']!==null)
            $d['recepcion'] = isset(\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$d['recepcion']]) ? \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$d['recepcion']] : $d['recepcion'];
        if ($d['resultado']!==null)
            $d['resultado'] = isset(\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$d['resultado']]) ? \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$d['resultado']] : $d['resultado'];
        $d['total'] = num($d['total']);
    }
    array_unshift($documentos, ['Recibido', 'Recepción', 'Resultado', 'Documentos', 'Ver detalle']);
    new \sowerphp\general\View_Helper_Table($documentos, 'emitidos_intercambio_'.$Emisor->rut, true);
}
