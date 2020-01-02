<div class="page-header"><h1>Documentos usados por el contribuyente</h1></div>
<p>Se listan los documentos usados por periodo por el contribuyente <?=$Emisor->razon_social?>.</p>
<?php
$emitidos = 0;
$boletas = 0;
$recibidos = 0;
$intercambios = 0;
$total = 0;
foreach ($documentos as &$d) {
    foreach (['emitidos', 'boletas', 'recibidos', 'intercambios', 'total'] as $c) {
        if ($d[$c]) {
            $$c += $d[$c];
            $d[$c] = num($d[$c]);
        }
    }
}
?>
<div class="card-deck">
    <div class="card mb-4">
        <div class="card-body text-center">
            <span class="text-info lead"><?=num($emitidos)?></span><br/>
            <small>emitidos</small>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-center">
            <span class="text-info lead"><?=num($boletas)?></span><br/>
            <small>boletas emitidas</small>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-center">
            <span class="text-info lead"><?=num($recibidos)?></span><br/>
            <small>recibidos</small>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-center">
            <span class="text-info lead"><?=num($intercambios)?></span><br/>
            <small>intercambios</small>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-center">
            <span class="text-info lead"><?=num($total)?></span><br/>
            <small>total</small>
        </div>
    </div>
</div>
<?php
array_unshift($documentos, ['Período', 'Emitidos<sup>(1)</sup>', 'Boletas emitidas', 'Recibidos', 'Intercambios', 'Total<sup>(2)</sup>']);
new \sowerphp\general\View_Helper_Table($documentos, 'documentos_usados_'.$Emisor->rut, true);
?>
<p class="small mt-4">(1) No se incluyen las boletas emitidas</p>
<p class="small">(2) No se incluyen los intercambios</p>
