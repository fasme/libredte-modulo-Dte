<div class="page-header"><h1>Generar informe de estados en SII de los folios</h1></div>
<p>Aquí podrá solicitar vía correo electrónico a <?=$_Auth->User->email?> un informe con los estados que el SII tiene registrado para los folios (recibidos, anulados o pendientes).</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'tablecheck',
    'name' => 'documentos',
    'label' => 'Documentos',
    'titles' => ['Código', 'Documento'],
    'table' => $documentos,
]);
echo $f->input([
    'type' => 'tablecheck',
    'name' => 'estados',
    'label' => 'Estados',
    'titles' => ['Estado'],
    'table' => [
        ['recibidos', 'Recibidos en SII'],
        ['anulados', 'Anulados en SII'],
        ['pendientes', 'Pendientes (sin uso o disponibles)'],
    ],
    'mastercheck' => true,
    'display-key' => false,
]);
echo $f->end('Solicitar informe por correo');
?>
<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base?>/dte/admin/dte_folios">Volver al mantenedor de folios</a>
</div>
