<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de compras
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
