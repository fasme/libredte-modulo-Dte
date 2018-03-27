<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_ventas" title="Volver a IEV">
            Volver a IEV
        </a>
    </li>
</ul>
<h1>Resumen libro de ventas</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'anio',
    'label' => 'Año',
    'value' => !empty($anio) ? $anio : null,
    'check' => 'notempty int',
]);
echo $f->end('Generar resumen');
if (isset($resumen)) {
    $total = [
        'TpoDoc' => '<strong>Total</strong>',
        'TotDoc' => 0,
        'TotAnulado' => 0,
        'TotOpExe' => 0,
        'TotMntExe' => 0,
        'TotMntNeto' => 0,
        'TotMntIVA' => 0,
        'TotIVAPropio' => 0,
        'TotIVATerceros' => 0,
        'TotLey18211' => 0,
        'TotMntTotal' => 0,
        'TotMntNoFact' => 0,
        'TotMntPeriodo' => 0,
    ];
    foreach ($resumen as &$r) {
        // sumar campos que se suman directamente
        foreach (['TotDoc', 'TotAnulado', 'TotOpExe'] as $c) {
            $total[$c] += $r[$c];
        }
        // sumar o restar campos segun operación
        foreach (['TotMntExe', 'TotMntNeto', 'TotMntIVA', 'TotIVAPropio', 'TotIVATerceros', 'TotLey18211', 'TotMntTotal', 'TotMntNoFact', 'TotMntPeriodo'] as $c) {
            if ($operaciones[$r['TpoDoc']]=='S') {
                $total[$c] += $r[$c];
            } else if ($operaciones[$r['TpoDoc']]=='R') {
                $total[$c] -= $r[$c];
            }
        }
        // dar formato de número
        foreach ($r as &$v) {
            if ($v) {
                $v = num($v);
            }
        }
    }
    foreach ($total as &$tot) {
        if (is_numeric($tot)) {
            $tot = $tot>0 ? num($tot) : null;
        }
    }
    $titulos = ['Tipo Doc.', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA', 'IVA propio', 'IVA terc.', 'Ley 18211', 'Monto total', 'No fact.', 'Total periodo'];
    array_unshift($resumen, $titulos);
    $resumen[] = $total;
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setShowEmptyCols(false);
    echo $t->generate($resumen);
}
