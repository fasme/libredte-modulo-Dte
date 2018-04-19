<div class="page-header"><h1>Dte &raquo; Admin &raquo; Mantenedores &raquo; Importar contribuyentes</h1></div>
<p>Aquí se podrán importar datos de contribuyentes. El orden de las columnas es: RUT, razón social, giro, dirección, comuna, email, teléfono y código actividad económica. Sólo el RUT y razón social son obligatorios, todo el resto es opcional. Sólo se actualizarán contribuyentes no registrados por un usuario y que no tengan el campo previamente asignado.</p>
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
