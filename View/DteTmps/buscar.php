<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_tmps" title="Volver a los documentos temporales">
            Volver a documentos temporales
        </a>
    </li>
</ul>
<div class="page-header"><h1>Búsqueda avanzada de DTE temporales</h1></div>
<p>Aquí podrá buscar entre sus documentos temporales.</p>
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
<div class="center"><?=$f->input(['type'=>'submit', 'name'=>'submit', 'value'=>'Buscar documentos'])?></div>
<?php
echo $f->end(false);
// mostrar documentos
if (isset($documentos)) {
    echo '<hr/>';
    $aux = $documentos;
    $documentos = [['Receptor', 'RUT', 'Documento', 'Folio', 'Fecha', 'Total', 'Acciones']];
    foreach ($aux as &$d) {
        $acciones = '<a href="'.$_base.'/dte/dte_tmps/ver/'.$d['receptor'].'/'.$d['dte'].'/'.$d['codigo'].'" title="Ver documento"><span class="fa fa-search btn btn-default"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_tmps/pdf/'.$d['receptor'].'/'.$d['dte'].'/'.$d['codigo'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento"><span class="far fa-file-pdf btn btn-default"></span></a>';
        $documentos[] = [
            $d['razon_social'],
            $d['receptor'].'-'.\sowerphp\app\Utility_Rut::dv($d['receptor']),
            $d['tipo'],
            $d['folio'],
            \sowerphp\general\Utility_Date::format($d['fecha']),
            num($d['total']),
            $acciones
        ];
    }
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, 100]);
    $t->setId('dte_tmps_'.$Emisor->rut);
    $t->setExport(true);
    echo $t->generate($documentos);
}
