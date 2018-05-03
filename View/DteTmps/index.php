<script>
function buscar(q) {
    var dte_tmp = $('#dte_'+q.value.toUpperCase());
    q.value = '';
    if (dte_tmp.length==0) {
        alert('No se encontró el DTE temporal');
        q.focus();
    } else {
        dte_tmp.get(0).click();
    }
}
</script>
<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/documentos/emitir" title="Emitir documento temporal">
            <span class="fa fa-edit"></span> Emitir documento
        </a>
    </li>
    <li>
        <a href="<?=$_base?>/dte/dte_tmps/buscar" title="Búsqueda avanzada de documentos temporales">
            <span class="fa fa-search"></span> Buscar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Documentos temporales</h1></div>
<p>Aquí se listan los documentos temporales del emisor <?=$Emisor->razon_social?> que ya están normalizados pero que aun no han sido generados oficialmente (no poseen folio, ni timbre, ni firma).</p>
<form name="buscador" onsubmit="buscar(this.q); return false">
    <div class="form-group">
        <label class="control-label sr-only" for="qField">Buscar por folio</label>
        <div class="input-group input-group-sm">
            <input type="text" name="q" class="form-control" id="qField" placeholder="Buscar por folio..." autofocus="autofocus" />
            <span class="input-group-btn">
                <button class="btn btn-secondary" type="button" onclick="buscar(document.buscador.q); return false">
                    <span class="fa fa-search"></span>
                </button>
            </span>
        </div>
    </div>
</form>
<?php
$documentos = [['Receptor', 'RUT', 'Documento', 'Folio', 'Fecha', 'Total', 'Acciones']];
foreach ($dtes as &$dte) {
    $acciones = '<a href="dte_tmps/ver/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Ver el documento temporal" id="dte_'.$dte->getFolio().'"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="dte_tmps/eliminar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Eliminar DTE temporal"><span class="fa fa-times-circle btn btn-default" onclick="return eliminar(\'DteTmp\', \''.$dte->receptor.', '.$dte->dte.', '.$dte->codigo.'\')"></span></a>';
    $acciones .= ' <a href="documentos/generar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Generar DTE y enviar al SII" onclick="return Form.checkSend(\'¿Está seguro de querer generar el DTE?\')"><span class="far fa-paper-plane btn btn-default"></span></a>';
    $documentos[] = [
        $dte->getReceptor()->razon_social,
        $dte->getReceptor()->rut.'-'.$dte->getReceptor()->dv,
        $dte->getDte()->tipo,
        $dte->getFolio(),
        \sowerphp\general\Utility_Date::format($dte->fecha),
        num($dte->total),
        $acciones
    ];
}
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, 150]);
$t->setID('dte_tmps_'.$Emisor->rut);
$t->setExport(true);
echo $t->generate($documentos);
