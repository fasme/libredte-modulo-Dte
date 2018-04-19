<div class="page-header"><h1>Dte &raquo; Informes &raquo; Emitidos sin intercambio</h1></div>
<p>Aquí podrá revisar el listado de documentos emitidos que no han sido enviados al correo de intercambio registrado del receptor.</p>
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
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento"><span class="fa fa-search btn btn-default"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento"><span class="far fa-file-pdf btn btn-default"></span></a>';
        $d[] = $acciones;
        $d['total'] = num($d['total']);
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d['sucursal_sii'] = $Emisor->getSucursal($d['sucursal_sii'])->sucursal;
        unset($d['dte']);
    }
    array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Sucursal', 'Usuario', 'Correo intercambio', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setId('emitidos_sin_intercambio_'.$Emisor->rut);
    $t->setExport(true);
    $t->setColsWidth([null, null, null, null, null, null, null, null, null, 100]);
    echo $t->generate($documentos);
}
