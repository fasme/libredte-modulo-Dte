<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_emitidos/listar" title="Ir a los documentos emitidos" class="nav-link">
            <i class="fa fa-sign-out-alt"></i>
            Documentos emitidos
        </a>
    </li>
</ul>
<div class="page-header"><h1>Búsqueda avanzada de DTE emitidos</h1></div>
<p>Aquí podrá buscar entre sus documentos emitidos.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['onsubmit'=>'Form.check()']);
?>
<div class="row">
    <div class="form-group col-md-3"><?=$f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos los tipos de documentos'] + $tipos_dte])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'receptor', 'placeholder'=>'RUT receptor sin puntos ni DV', 'check'=>'integer'])?></div>
    <div class="form-group col-md-6"><?=$f->input(['name'=>'razon_social', 'placeholder'=>'Razón social del receptor'])?></div>
</div>
<div class="row">
    <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_desde', 'placeholder'=>'Fecha desde', 'check'=>'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_hasta', 'placeholder'=>'Fecha hasta', 'check'=>'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'total_desde', 'placeholder'=>'Total desde', 'check'=>'integer'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'total_hasta', 'placeholder'=>'Total hasta', 'check'=>'integer'])?></div>
</div>
<?php
echo $f->input([
    'type' => 'js',
    'id' => 'xml',
    'titles' => ['Nodo', 'Valor'],
    'inputs' => [
        ['name'=>'xml_nodo', 'check'=>'notempty'],
        ['name'=>'xml_valor', 'check'=>'notempty'],
    ],
    'values' => [],
]);
?>
<p>Los nodos deben ser los del XML desde el tag Documento del DTE. Por ejemplo para buscar en los productos usar: Detalle/NmbItem</p>
<div class="text-center"><?=$f->input(['type'=>'submit', 'name'=>'submit', 'value'=>'Buscar documentos'])?></div>
<?php
echo $f->end(false);
// mostrar documentos
if (isset($documentos)) {
    echo '<hr/>';
    foreach ($documentos as &$d) {
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento" class="btn btn-primary"><i class="far fa-file-pdf fa-fw"></i></a>';
        $d[] = $acciones;
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d['total'] = num($d['total']);
        unset($d['dte'], $d['intercambio']);
    }
    array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Sucursal', 'Usuario', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, null, null, 110]);
    $t->setId('dte_emitidos_'.$Emisor->rut);
    $t->setExport(true);
    echo $t->generate($documentos);
}
