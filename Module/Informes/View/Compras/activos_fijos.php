<h1>Dte &raquo; Informes &raquo; Compras de activos fijos</h1>
<p>Se listan los documentos de compras de activos fijos según fue informado en el libro de compras (IEC) del contribuyente <?=$Emisor->razon_social?>.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
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
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal',
    'label' => 'Sucursal',
    'options' => $sucursales,
    'check' => 'notempty',
]);
echo $f->end('Buscar compras');

// mostrar informe compras de activos fijos
if (isset($compras)) {
    foreach ($compras as &$c) {
        $c['fecha'] = \sowerphp\general\Utility_Date::format($c['fecha']);
        $c['neto'] = num($c['neto']);
        $c['monto_activo_fijo'] = num($c['monto_activo_fijo']);
        $c['items'] = implode('<br/>', $c['items']);
        $c['precios'] = implode('<br/>', array_map('num', $c['precios']));
        $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$c['intercambio'].'" title="Ver detalles del intercambio" class="btn btn-default'.(!$c['intercambio']?' disabled':'').'"><span class="fa fa-search"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$c['intercambio'].'/0/'.$c['emisor'].'/'.$c['dte'].'/'.$c['folio'].'" title="Descargar PDF del documento" class="btn btn-default'.(!$c['intercambio']?' disabled':'').'"><span class="far fa-file-pdf"></span></a>';
        $c[] = $acciones;
        unset($c['emisor'], $c['intercambio'], $c['dte']);
    }
    array_unshift($compras, ['Fecha', 'Sucursal', 'Emisor', 'Documento', 'Folio', 'Neto', 'Monto activo', 'Tipo', 'Items', 'Precios', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setID('activos_fijos_'.$Emisor->rut.'_'.$_POST['desde'].'_'.$_POST['hasta']);
    $t->setExport(true);
    $t->setColsWidth([null, null, null, null, null, null, null, null, null, null, 100]);
    echo $t->generate($compras);
}
