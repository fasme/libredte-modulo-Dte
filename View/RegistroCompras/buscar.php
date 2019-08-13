<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/registro_compras/pendientes" title="Ver listado de documentos pendientes" class="nav-link">
            <i class="fas fa-paperclip"></i>
            Recibidos pendientes
        </a>
    </li>
</ul>
<div class="page-header"><h1>Buscar documentos pendientes en SII</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'emisor',
    'label' => 'Emisor',
    'check' => 'rut',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Documento',
    'options' => [''=>'Todos los documentos'] + $dte_tipos,
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha_desde',
    'label' => 'Fecha desde',
    'check' => 'date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha_hasta',
    'label' => 'Fecha hasta',
    'check' => 'date',
]);
echo $f->input([
    'name' => 'total_desde',
    'label' => 'Total desde',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'total_hasta',
    'label' => 'Total hasta',
    'check' => 'integer',
]);
echo $f->end('Buscar');

if (!empty($documentos)) {
    foreach ($documentos as &$d) {
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d['fecha_recepcion_sii'] = \sowerphp\general\Utility_Date::format($d['fecha_recepcion_sii'], 'd/m/Y H:i');
        $d['proveedor_razon_social'] .= '<span>'.num($d['proveedor_rut']).'-'.$d['proveedor_dv'].'</span>';
        $d['dte_glosa'] .= ' N° '.$d['folio'];
        foreach (['exento', 'neto', 'iva', 'total'] as $col) {
            if ($d[$col]) {
                $d[$col] = num($d[$col]);
            }
        }
        unset($d['estado'], $d['proveedor_rut'], $d['proveedor_dv'], $d['dte'], $d['folio'], $d['dettipotransaccion'], $d['desctipotransaccion']);
    }
    array_unshift($documentos, ['Proveedor','Documento', 'Fecha', 'Recepción SII', 'Exento', 'Neto', 'IVA', 'Total']);
    new \sowerphp\general\View_Helper_Table($documentos);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/registro_compras/csv?<?=http_build_query($filtros)?>" role="button">Descargar detalle de documentos</a>
<?php
}
