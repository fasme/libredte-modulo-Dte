<div class="page-header"><h1>Importar contribuyentes</h1></div>
<p>Aquí se podrán importar datos de contribuyentes. El orden de las columnas es:</p>
<ul>
    <li>RUT</li>
    <li>Razón social</li>
    <li>Giro</li>
    <li>Dirección</li>
    <li>Comuna</li>
    <li>Correo electrónico</li>
    <li>Teléfono</li>
    <li>Código de actividad económica</li>
</ul>
<p>Sólo el RUT y razón social son obligatorios, todo el resto es opcional. Sólo se actualizarán contribuyentes no registrados por un usuario y que no tengan el campo previamente asignado.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo',
    'check' => 'notempty',
]);
echo $f->end('Importar datos de contribuyentes');
