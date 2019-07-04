<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_recibidos/listar" title="Ir a los documentos emitidos" class="nav-link">
            <i class="fa fa-sign-in-alt"></i>
            Documentos recibidos
        </a>
    </li>
</ul>
<div class="page-header"><h1>Búsqueda avanzada de DTE recibidos</h1></div>
<p>Aquí podrá buscar entre sus documentos recibidos.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['onsubmit'=>'Form.check()']);
?>
<div class="row">
    <div class="form-group col-md-3"><?=$f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos los tipos de documentos'] + $tipos_dte])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'emisor', 'placeholder'=>'RUT emisor sin puntos ni DV', 'check'=>'integer'])?></div>
    <div class="form-group col-md-6"><?=$f->input(['name'=>'razon_social', 'placeholder'=>'Razón social del emisor'])?></div>
</div>
<div class="row">
    <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_desde', 'placeholder'=>'Fecha desde', 'check'=>'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_hasta', 'placeholder'=>'Fecha hasta', 'check'=>'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'total_desde', 'placeholder'=>'Total desde', 'check'=>'integer'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'total_hasta', 'placeholder'=>'Total hasta', 'check'=>'integer'])?></div>
</div>
<div class="text-center"><?=$f->input(['type'=>'submit', 'name'=>'submit', 'value'=>'Buscar documentos'])?></div>
<?php
echo $f->end(false);
// mostrar documentos
if (isset($documentos)) {
    // procesar documentos
    $total = 0;
    foreach ($documentos as &$d) {
        $filename = 'dte_'.$d['emisor'].'-'.$d['intercambio'].'_LibreDTE_T'.$d['dte'].'F'.$d['folio'].'.pdf';
        $total += $d['total'];
        $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$d['intercambio'].'" title="Ver detalles del intercambio" class="btn btn-primary'.(!$d['intercambio']?' disabled':'').'" role="button"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_recibidos/pdf/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Descargar PDF del documento" class="btn btn-primary'.(!$d['intercambio']?' disabled':'').'" '.($d['intercambio']?('download="'.$filename.'" data-click="pdf"'):'').' role="button"><i class="far fa-file-pdf fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_recibidos/modificar/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Modificar documento" class="btn btn-primary"><i class="fa fa-edit fa-fw"></i></a>';
        $d[] = $acciones;
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d['total'] = num($d['total']);
        unset($d['dte'], $d['intercambio'], $d['emisor']);
    }
    // agregar resumen
    echo '<div class="card mt-4 mb-4"><div class="card-body lead text-center">Se encontraron '.num(count($documentos)).' documentos por un total de $'.num($total).'.-</div></div>';
    // agregar tabla
    array_unshift($documentos, ['Documento', 'Folio', 'Emisor', 'Fecha', 'Total', 'Usuario', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, 160]);
    $t->setId('dte_recibidos_'.$Receptor->rut);
    $t->setExport(true);
    echo $t->generate($documentos);
}
