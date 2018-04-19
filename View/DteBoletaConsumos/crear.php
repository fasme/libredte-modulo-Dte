<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_boleta_consumos/listar" title="Volver a libro de guías de despacho">
            Volver a RCOF
        </a>
    </li>
</ul>
<div class="page-header"><h1>Enviar reporte de consumo de folios</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'dia',
    'label' => 'Día',
    'value' => $dia,
    'check' => 'notempty date',
]);
echo $f->end('Enviar reporte de consumo de folios');
