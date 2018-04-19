<div class="page-header"><h1>Dte &raquo; Informes &raquo; Documentos usados</h1></div>
<p>Se listan los documentos usados por periodo por el contribuyente <?=$Emisor->razon_social?>.</p>
<?php
$emitidos = 0;
$recibidos = 0;
$total = 0;
$sobre_cuota = 0;
foreach ($documentos as &$d) {
    foreach (['emitidos', 'recibidos', 'total', 'sobre_cuota'] as $c) {
        if ($d[$c]) {
            $$c += $d[$c];
            $d[$c] = num($d[$c]);
        }
    }
}
?>
<div class="row">
    <div class="col-sm-3 col-xs-6 text-center well">
        <span class="text-info lead"><?=num($emitidos)?></span><br/>
        <small>emitidos</small>
    </div>
    <div class="col-sm-3 col-xs-6 text-center well">
        <span class="text-info lead"><?=num($recibidos)?></span><br/>
        <small>recibidos</small>
    </div>
    <div class="col-sm-3 col-xs-6 text-center well">
        <span class="text-info lead"><?=num($total)?></span><br/>
        <small>total</small>
    </div>
    <div class="col-sm-3 col-xs-6 text-center well">
        <span class="text-info lead"><?=num($sobre_cuota)?></span><br/>
        <small>sobre la cuota</small>
    </div>
</div>
<?php
array_unshift($documentos, ['Período', 'Emitidos', 'Recibidos', 'Total', 'Sobre la cuota']);
new \sowerphp\general\View_Helper_Table($documentos, 'documentos_usados_'.$Emisor->rut, true);
