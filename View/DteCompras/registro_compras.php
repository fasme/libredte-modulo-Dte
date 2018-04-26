<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_compras" title="Volver a IEC">
            Volver a IEC
        </a>
    </li>
</ul>
<div class="page-header"><h1>Registro de compras del SII</small></h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'periodo',
    'label' => 'Período',
    'check' => 'notempty integer',
    'value' => date('Ym'),
    'placeholder' => date('Ym'),
]);
echo $f->input([
    'type' => 'select',
    'name' => 'estado',
    'label' => 'Estado',
    'options' => [
        'REGISTRO' => 'Registrados',
        'PENDIENTE' => 'Pendientes',
        'NO_INCLUIR' => 'No incluídos',
        'RECLAMADO' => 'Reclamados',
    ],
]);
echo $f->end('Ver resumen del período');
