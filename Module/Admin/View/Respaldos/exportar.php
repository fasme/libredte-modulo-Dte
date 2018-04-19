<div class="page-header"><h1>Exportar datos del contribuyente <?=$Emisor->razon_social?></h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubit'=>'Form.check()']);
echo $f->input([
    'type' => 'tablecheck',
    'name' => 'tablas',
    'label' => 'Tablas',
    'titles' => ['Tabla'],
    'table' => $tablas,
    'mastercheck' => true,
]);
echo $f->end('Generar respaldo');
