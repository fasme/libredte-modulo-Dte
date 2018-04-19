<div class="page-header"><h1>Subir CAF</h1></div>
<p>Aquí podrá subir los códigos de autorización de folios (CAF) obtenidos desde el SII para la empresa <?=$Emisor->razon_social?>.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'caf',
    'label' => 'Archivo CAF',
    'check' => 'notempty',
    'help' => 'Archivo CAF en formato XML descargado desde SII',
    'attr' => 'accept="application/xml"',
]);
echo $f->end('Subir archivo CAF');
?>
<div style="float:right;margin-bottom:1em;font-size:0.8em">
    <a href="<?=$_base?>/dte/admin/dte_folios">Volver al mantenedor de folios</a>
</div>
