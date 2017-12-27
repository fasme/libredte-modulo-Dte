<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_recibidos/listar">
            Volver
        </a>
    </li>
</ul>
<h1>Buscar boletas de honorarios recibidas</h1>
<p>Aquí podrá consultar las boletas de honorarios electrónicas que el SII recibió para la empresa <?=$Emisor->razon_social?>.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'periodo',
    'label' => 'Período',
    'check' => 'notempty integer',
    'help' => 'Puede ser un año entero ('.date('Y').') o un mes en específico ('.date('Ym').')',
]);
echo $f->end('Buscar boletas');

if (isset($boletas)) {
    foreach ($boletas as &$b) {
        $b[] = '<a href="'.$_base.'/dte/dte_recibidos/bhe_pdf/'.$b['codigo'].'" title="Descargar PDF boleta honorarios electrónica" class="btn btn-default" target="_blank"><span class="far fa-file-pdf"></span></a>';
        if ($b['anulada']) {
            $b['anulada'] = \sowerphp\general\Utility_Date::format($b['anulada']);
        }
        $b['rut'] = num($b['rut']).'-'.$b['dv'];
        $b['fecha'] = \sowerphp\general\Utility_Date::format($b['fecha']);
        $b['total_honorarios'] = num($b['total_honorarios']);
        $b['total_retencion'] = num($b['total_retencion']);
        $b['total_liquido'] = num($b['total_liquido']);
        unset($b['dv'], $b['sociedad_profesional'], $b['comuna'], $b['estado'], $b['codigo']);
    }
    array_unshift($boletas, ['N°', 'RUT', 'Nombre', 'Fecha', 'Honor.', 'Retenc.', 'Líquido', 'Nula', 'PDF']);
    new \sowerphp\general\View_Helper_Table($boletas, 'bhe_'.$Emisor->rut.'_'.$_POST['periodo'], true);
?>
<link rel="stylesheet" type="text/css" href="<?=$_base?>/css/jquery.dataTables.css" />
<script type="text/javascript" src="<?=$_base?>/js/jquery.dataTables.js"></script>
<script type="text/javascript"> $(document).ready(function(){ dataTable("#bhe_<?=$Emisor->rut?>_<?=$_POST['periodo']?>"); }); </script>
<?php
}
