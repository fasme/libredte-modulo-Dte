<div class="page-header"><h1>Libro de boletas electrónicas</h1></div>
<?php
foreach ($periodos as &$p) {
    $p[] = ' <a href="dte_boletas/xml/'.$p['periodo'].'" title="Descargar XML del libro del período"><span class="far fa-file-code btn btn-default"></span></a>';
}
array_unshift($periodos, ['Período', 'Boletas emitidas', 'Descargar']);
new \sowerphp\general\View_Helper_Table($periodos);
