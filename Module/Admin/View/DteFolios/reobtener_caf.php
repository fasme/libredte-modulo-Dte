<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Reobtener CAF del SII</h1></div>
<p>Aquí podrá reobtener un archivo de folios (CAF) previamente obtenido en el SII y que sea cargardo a LibreDTE.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Tipo de DTE',
    'options' => [''=>'Seleccione un tipo de DTE'] + $dte_tipos,
    'value' => $dte,
    'check' => 'notempty',
]);
echo $f->end('Buscar folios sin cargar');

if (isset($solicitudes)) {
    foreach ($solicitudes as &$s) {
        $s[] = '<a href="'.$_url.'/dte/admin/dte_folios/reobtener_caf_cargar/'.$dte.'/'.$s['inicial'].'/'.$s['final'].'/'.$s['fecha'].'" title="Reobtener el CAF y cargar en LibreDTE" class="btn btn-primary"><i class="fa fa-download fa-fw"></i></a>';
        $s['fecha'] = \sowerphp\general\Utility_Date::format($s['fecha']);
    }
    array_unshift($solicitudes, ['Desde', 'Hasta', 'Cantidad', 'Fecha autorización', 'Solicitante', 'Reobtener']);
    new \sowerphp\general\View_Helper_Table($solicitudes);
}
