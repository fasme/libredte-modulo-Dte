<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_compras/ver/<?=$periodo?>" title="Volver a la IEC del período <?=$periodo?>">
            Volver al período
        </a>
    </li>
</ul>
<div class="page-header"><h1>Asignación tipo de transacciones del período <?=$periodo?></h1></div>
<p>Aquí puede hacer una búsqueda de los documentos emitidos y realizar la asignación del tipo de transacción.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'emisor',
    'label' => 'RUT emisor',
    'check' => 'rut',
]);
echo $f->end('Buscar documentos');

if (!empty($documentos)) {
    echo '<hr/>',"\n";
    foreach ($documentos as &$d) {
        $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$d['intercambio'].'" title="Ver detalles del intercambio" class="btn btn-default'.(!$d['intercambio']?' disabled':'').'" role="button"><span class="fa fa-search"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$d['intercambio'].'/0/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Descargar PDF del documento" class="btn btn-default'.(!$d['intercambio']?' disabled':'').'" role="button"><span class="far fa-file-pdf"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_recibidos/modificar/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Modificar documento" class="btn btn-default"><span class="fa fa-edit"></span></a>';
        $d[] = $acciones;
        $d['total'] = num($d['total']);
        unset($d['emisor'], $d['dte'], $d['intercambio']);
    }
    $f = new \sowerphp\general\View_Helper_Form(false);
    array_unshift($documentos, ['Documento', 'Folio', 'Emisor', 'Fecha', 'Total', 'Usuario', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, 150]);
    echo $t->generate($documentos);
}
