<div class="page-header"><h1>Dte &raquo; Informes &raquo; DTEs emitidos <small>estado: <?=$estado?></small></h1></div>
<p>Aquí podrá ver los documentos emitidos que tienen el estado "<?=$estado?>" de la empresa <?=$Emisor->razon_social?> que tienen fecha de emisión del DTE entre el <?=$desde?> y el <?=$hasta?>.</p>
<?php
foreach ($documentos as &$d) {
    $d['total'] = num($d['total']);
    $d[] = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'"><span class="fa fa-search btn btn-default"></span></a>';
    unset($d['dte']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Detalle estado', 'Intercambio', 'Sucursal', 'Usuario', 'Ver']);
new \sowerphp\general\View_Helper_Table($documentos, \sowerphp\core\Utility_String::normalize($estado).'_'.$Emisor->rut, true);
