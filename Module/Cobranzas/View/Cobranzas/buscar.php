<h1>Buscar pagos programados ventas a crédito</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => isset($_POST['desde']) ? $_POST['desde'] : date('Y-m-d'),
    'check' => 'date',
    'attr' => 'onchange="document.getElementById(\'hastaField\').value = this.value"',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => isset($_POST['hasta']) ? $_POST['hasta'] : date('Y-m-d'),
    'check'=>'date',
]);
echo $f->input([
    'name' => 'receptor',
    'label' => 'Receptor',
    'placeholder' => '55.666.777-8',
    'check'=>'rut',
]);
echo $f->end('Buscar');

if (isset($cobranza)) {
    foreach ($cobranza as &$c) {
        $c[] = '<a href="'.$_base.'/dte/cobranzas/cobranzas/ver/'.$c['dte'].'/'.$c['folio'].'/'.$c['fecha_pago'].'" title="Ver pago"><span class="fa fa-search btn btn-default"></span></a>';
        $c['rut'] = \sowerphp\app\Utility_Rut::addDV($c['rut']);
        $c['fecha_emision'] = \sowerphp\general\Utility_Date::format($c['fecha_emision']);
        $c['fecha_pago'] = \sowerphp\general\Utility_Date::format($c['fecha_pago']);
        $c['total'] = num($c['total']);
        $c['monto_pago'] = num($c['monto_pago']);
        if ($c['pagado']!==null) {
            $c['pagado'] = num($c['pagado']);
        }
        unset($c['dte']);
    }
    array_unshift($cobranza, ['Receptor', 'RUT receptor', 'Emisión', 'Documento', 'Folio', 'Total', 'Fecha pago', 'Monto pago', 'Glosa', 'Pago parcial', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($cobranza, 'cobranza_'.$_POST['desde'].'_'.$_POST['hasta'], true);
}
