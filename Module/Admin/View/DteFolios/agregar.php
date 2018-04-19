<div class="page-header"><h1>Crear mantenedor de folios</h1></div>
<p>Aquí podrá agregar un mantenedor de folios para un nuevo tipo de documento. En el paso siguiente se le pedirá que suba el primer archio CAF.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Tipo de DTE',
    'options' => [''=>'Seleccione un tipo de DTE'] + $dte_tipos,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'alerta',
    'label' => 'Cantidad alerta',
    'value' => 0,
    'help' => 'Cuando los folios disponibles sean igual a esta cantidad se notificará al administrador de la empresa',
    'check' => 'notempty integer',
]);
echo $f->end('Crear mantenedor de folios e ir al paso siguiente');
?>
<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base?>/dte/admin/dte_folios">Volver al mantenedor de folios</a>
</div>
