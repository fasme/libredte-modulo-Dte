<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/itemes/listar" title="Ir al mantenedor de items" class="nav-link">
            <i class="fa fa-cubes"></i> Items
        </a>
    </li>
</ul>
<div class="page-header"><h1>Importar productos y/o servicios</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.confirm(this, \'¿Está seguro de importar el archivo seleccionado?\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo con items',
    'help' => 'Archivo con productos y/o servicios en formato CSV (separado por punto y coma, codificado en UTF-8). Puede consultar un <a href="'.$_base.'/dte/archivos/item.csv" download="item.csv">ejemplo</a> para conocer el formato esperado.',
    'check' => 'notempty',
    'attr' => 'accept="csv"',
]);
echo $f->end('Importar productos y/o servicios');

// tabla con los resultados
if (!empty($resumen)) {
    new \sowerphp\general\View_Helper_Table($items, 'items_importados', true);
}
