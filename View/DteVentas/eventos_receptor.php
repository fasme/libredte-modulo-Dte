<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_ventas/ver/<?=$periodo?>" title="Volver al período del IEV">
            Volver al período
        </a>
    </li>
</ul>
<div class="page-header"><h1>Evento <?=$Evento->glosa?> <small>período <?=$periodo?></small></h1></div>
<p>Aquí podrá ver los eventos del tipo "<?=$Evento->glosa?>" registrados por el receptor para el período <?=$periodo?>.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento"><span class="far fa-file-pdf btn btn-default"></span></a>';
    $d[] = $acciones;
    $d['total'] = num($d['total']);
    unset($d['dte']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Intercambio', 'Sucursal', 'Usuario', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, null, null, 100]);
$t->setId('dte_emitidos_'.$Evento->codigo.'_'.$Emisor->rut);
$t->setExport(true);
echo $t->generate($documentos);
