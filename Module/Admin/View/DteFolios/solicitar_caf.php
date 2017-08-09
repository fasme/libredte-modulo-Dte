<h1>Solicitar CAF al SII</h1>
<p>Aquí podrá solicitar un archivo de folios (CAF) al SII y cargarlo automáticamente a LibreDTE.</p>
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
echo $f->input([
    'name' => 'cantidad',
    'label' => 'Cantidad',
    'help' => 'Cantidad de folios máximo que se tratarán de solicitar (se podrían obtener menos si no hay suficientes autorizados por el SII)',
    'check' => 'notempty integer',
]);
echo $f->end('Solicitar folios al SII y cargar en LibreDTE');
?>
<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base?>/dte/admin/dte_folios">Volver al mantenedor de folios</a>
</div>
<p><strong>Importante</strong>: si no ha timbrado folios para este tipo de documento en el SII, o sea, es el primer CAF a generar, debe hacerlo en el sitio del SII, no acá. Timbrajes futuros se pueden realizar acá o de manera automática.</p>
