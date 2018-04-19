<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/admin/item_clasificaciones/listar" title="Volver al mantenedor de clasificaciones de items">
            Volver a clasificaciones
        </a>
    </li>
</ul>
<div class="page-header"><h1>Importar clasificaciones de items desde archivo CSV</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.checkSend(\'¿Está seguro de importar el archivo seleccionado?\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo con clasificaciones',
    'help' => 'Archivo con clasificaciones de productos y/o servicios en formato CSV (separado por punto y coma, codificado en UTF-8). Puede consultar un <a href="'.$_base.'/dte/archivos/item_clasificacion.csv">ejemplo</a> para conocer el formato esperado.',
    'check' => 'notempty',
    'attr' => 'accept="csv"',
]);
echo $f->end('Importar clasificaciones');
