<div class="page-header"><h1>Modificar mantenedor de folios DTE <?=$DteFolio->dte?></h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'siguiente',
    'label' => 'Siguiente folio',
    'value' => $DteFolio->siguiente,
    'help' => 'Número de folio que es el siguiente que se debe asignar al documento que se emita',
    'check' => 'notempty integer',
]);
echo $f->input([
    'name' => 'alerta',
    'label' => 'Cantidad alerta',
    'value' => $DteFolio->alerta,
    'help' => 'Cuando los folios disponibles sean igual a esta cantidad se notificará al administrador de la empresa',
    'check' => 'notempty integer',
]);
echo $f->end('Modificar mantenedor de folios');
?>
<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base?>/dte/admin/dte_folios">Volver al mantenedor de folios</a>
</div>
