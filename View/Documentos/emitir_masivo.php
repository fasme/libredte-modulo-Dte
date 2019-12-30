<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/buscar_masivo" title="Buscar documentos masivamente" class="nav-link">
            <i class="fa fa-search"></i>
            Buscar documentos masivos
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir" title="Emitir documentos de manera individual" class="nav-link">
            <i class="fa fa-file-invoice"></i>
            Emitir documento individual
        </a>
    </li>
</ul>
<div class="page-header"><h1>Emitir documentos masivos</h1></div>
<p>Aquí podrá solicitar la emisión masiva de DTE a partir de un archivo CSV (separado por punto y coma, codificado en UTF-8). El archivo debe tener el <a href="<?=$_base?>/dte/archivos/emision_masiva.csv" download="emision_masiva.csv">siguiente formato</a>:</p>
<ul>
    <li>Tipo DTE: código del tipo de documento (ej: 33 para factura o 39 para boletas) (obligatorio)</li>
    <li>Folio: número que identifica de manera única dentro del CSV al DTE. Este folio sólo se usará para la emisión si así está configurado (obligatorio)</li>
    <li>Fecha emisión: en formato AAAA-MM-DD (opcional)</li>
    <li>Fecha vencimiento: en formato AAAA-MM-DD (opcional)</li>
    <li>RUT receptor (obligatorio)</li>
    <li>Razón social receptor (opcional sólo en boletas)</li>
    <li>Giro del receptor (opcional sólo en boletas)</li>
    <li>Teléfono del receptor  (opcional)</li>
    <li>Email del receptor (obligatorio si se desea enviar a un correo específico y no usar los almacenados en la base de datos)</li>
    <li>Dirección del receptor (opcional sólo en boletas)</li>
    <li>Comuna del receptor (opcional sólo en boletas)</li>
    <li>Código del item (opcional)</li>
    <li>Exento: si el item es exento se debe indicar un 1 (uno) en este campo (opcional)</li>
    <li>Nombre del item (obligatorio)</li>
    <li>Descripción del item (opcional)</li>
    <li>Cantidad del item (obligatorio)</li>
    <li>Unidad del item (opcional)</li>
    <li>Precio del item: monto bruto si es boleta (con IVA), cualquier otro documento monto neto (sin IVA) (obligatorio)</li>
    <li>Descuento del item: puede ser 0.5 para indicar 50% de descuento un un monto mayor o igual a 1 para indicar descuento en cantidad (opcional)</li>
    <li>Observación del documento (opcional)</li>
    <li>Fecha período desde: en formato AAAA-MM-DD (opcional)</li>
    <li>Fecha período hasta: en formato AAAA-MM-DD (opcional)</li>
    <li>Patente vehículo despacho (opcional)</li>
    <li>RUT transportista despacho (opcional)</li>
    <li>RUT chofer vehículo despacho (obligatorio sólo si va el nombre del chofer)</li>
    <li>Nombre chofer vehículo despacho (obligatorio sólo si va el RUT del chofer)</li>
    <li>Dirección despacho (opcional)</li>
    <li>Comuna despacho (opcional)</li>
    <li>Tipo documento referencia (opcional)</li>
    <li>Folio documento referencia (obligatorio si hay referencia)</li>
    <li>Fecha referencia (obligatoria si hay referencia)</li>
    <li>Código de referencia (obligatorio en nota de crédito y nota de débito. Códigos: <?=implode(', ', $codigos_referencias)?></li>
    <li>Razón de referencia (obligatorio en nota de crédito y nota de débito, ej: motivo del DTE)</li>
    <li>Moneda para documentos de exportación: USD, EUR o CLP (opcional, por defecto: USD)</li>
</ul>
<p>Si el documento tiene más de un item, se agrega una nueva fila donde sólo van las columnas correspondientes al item y las demás vacías.</p>
<p>El archivo subido se procesará de manera asíncrona y se notificará vía correo electrónico a <?=$_Auth->User->email?> cuando el proceso esté completo. El correo incluirá el mismo archivo CSV que se subió a la plataforma con 2 columnas nuevas que incluirán el código del resultado de la operación para ese documento y la glosa asociada a dicho estado. El significado macro de cada código de estado es:</p>
<?php
new \sowerphp\general\View_Helper_Table([
    ['Código de resultado', 'Descripción macro del resultado'],
    [1, 'Error en el formato del archivo (faltan campos o tienen formato incorrecto)'],
    [2, 'No autorizado a emitir el tipo de DTE solicitado'],
    [3, 'Solicitó enviar por correo y falta el correo del receptor'],
    [4, 'No fue posible emitir el DTE temporal'],
    [5, 'No fue posible generar el DTE real a partir del DTE temporal emitido'],
    [6, 'No fue posible enviar por correo el DTE generado (ya sea temporal o real)'],
    ['', 'DTE generado (ya sea temporal o real) y enviado al receptor por correo (si así se solicitó)'],
]);
?>
<p>Podrá encontrar el detalle de cada estado en caso de error en la glosa descriptiva en el archivo CSV de resultados.</p>
<p><strong>Importante</strong>: si el código de resultado es 1, 2 o 3 (validaciones de formato) no se generará ningún documento, independientemente que otras filas pasen las validaciones de formato.</p>
<hr/>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Documentos',
    'check' => 'notempty',
    'help' => 'Archivo CSV (separado por punto y coma, codificado en UTF-8) con los documentos que se deben emitir masivamente. <a href="'.$_base.'/dte/archivos/emision_masiva.csv" download="emision_masiva.csv">Ejemplo formato</a>',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dte_emitido',
    'label' => '¿DTE real?',
    'options' => ['No, sólo generar DTE temporal (cotización)', 'Si, generar DTE real (documento emitido)'],
]);
echo $f->input([
    'type' => 'select',
    'name' => 'email',
    'label' => '¿Enviar email?',
    'options' => ['No enviar email al receptor', 'Si, enviar email al receptor con el documento'],
]);
echo $f->input([
    'type' => 'select',
    'name' => 'pdf',
    'label' => '¿Generar PDF?',
    'options' => ['No generar PDF', 'Si, generar todos los PDF y enviar enlace para descarga en correo de resultado'],
]);
echo $f->end('Emitir DTE masivamente');
