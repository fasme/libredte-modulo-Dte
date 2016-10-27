<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_emitidos/listar" title="Volver a los documentos emitidos">
            Volver a documentos emitidos
        </a>
    </li>
</ul>
<h1>Búsqueda avanzada de DTE emitidos</h1>
<p>Aquí podrá buscar entre sus documentos emitidos.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['onsubmit'=>'Form.check()']);
?>
<div class="row">
    <div class="form-group col-md-8"><?=$f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Buscar en todos los tipos de documentos'] + $tipos_dte])?></div>
    <div class="form-group col-md-4"><?=$f->input(['name'=>'receptor', 'placeholder'=>'RUT receptor sin puntos ni DV', 'check'=>'integer'])?></div>
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
<div class="center"><?=$f->input(['type'=>'submit', 'name'=>'submit', 'value'=>'Buscar documentos'])?></div>
<?php
echo $f->end(false);
// mostrar documentos
if (isset($documentos)) {
    echo '<hr/>';
    foreach ($documentos as &$d) {
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento"><span class="fa fa-search btn btn-default"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
        $d[] = $acciones;
        $d['total'] = num($d['total']);
        unset($d['dte']);
    }
    array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Intercambio', 'Sucursal', 'Usuario', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, null, null, null, 100]);
    $t->setId('dte_emitidos_'.$Emisor->rut);
    $t->setExport(true);
    echo $t->generate($documentos);
}
