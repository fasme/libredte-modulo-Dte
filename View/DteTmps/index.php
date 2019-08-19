<script>
function buscar(q) {
    var dte_tmp = $('#dte_'+q.value.toUpperCase());
    q.value = '';
    if (dte_tmp.length==0) {
        Form.alert('No se encontró el DTE temporal', q);
        q.focus();
    } else {
        dte_tmp.get(0).click();
    }
}
</script>
<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir" title="Emitir documento temporal" class="nav-link">
            <i class="fa fa-file-invoice"></i> Emitir documento
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_tmps/buscar" title="Búsqueda avanzada de documentos temporales" class="nav-link">
            <i class="fa fa-search"></i> Buscar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Documentos temporales</h1></div>
<p>Aquí se listan los documentos temporales del emisor <?=$Emisor->razon_social?> que ya están normalizados pero que aun no han sido generados oficialmente (no poseen folio, ni timbre, ni firma).</p>
<form name="buscador" onsubmit="buscar(this.q); return false">
    <div class="form-group">
        <label class="control-label sr-only" for="qField">Buscar por folio</label>
        <div class="input-group input-group-sm mb-4">
            <input type="text" name="q" class="form-control" id="qField" placeholder="Buscar por folio..." autofocus="autofocus" />
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" onclick="buscar(document.buscador.q); return false">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</form>
<?php
$documentos = [['Receptor', 'RUT', 'Documento', 'Folio', 'Fecha', 'Total', 'Acciones']];
foreach ($dtes as &$dte) {
    $acciones = '<a href="dte_tmps/ver/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Ver el documento temporal" id="dte_'.$dte->getFolio().'" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="dte_tmps/eliminar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Eliminar DTE temporal" onclick="return eliminar(this, \'DteTmp\', \''.$dte->receptor.', '.$dte->dte.', '.$dte->codigo.'\')" class="btn btn-primary mb-2"><i class="fas fa-times fa-fw"></i></a>';
    $acciones .= ' <a href="documentos/generar/'.$dte->receptor.'/'.$dte->dte.'/'.$dte->codigo.'" title="Generar DTE y enviar al SII" onclick="return Form.confirm(this, \'¿Está seguro de querer generar el DTE?\')" class="btn btn-primary mb-2"><i class="far fa-paper-plane fa-fw"></i></a>';
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
$t->setColsWidth([null, null, null, null, null, null, 160]);
$t->setID('dte_tmps_'.$Emisor->rut);
$t->setExport(true);
echo $t->generate($documentos);
