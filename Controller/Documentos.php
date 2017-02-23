<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// namespace del controlador
namespace website\Dte;

/**
 * Clase para todas las acciones asociadas a documentos (incluyendo API)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-08-16
 */
class Controller_Documentos extends \Controller_App
{

    private $IndTraslado = [
        1 => 'Operación constituye venta',
        2 => 'Ventas por efectuar',
        3 => 'Consignaciones',
        4 => ' Entrega gratuita',
        5 => 'Traslados internos',
        6 => 'Otros traslados no venta',
        7 => 'Guía de devolución',
        8 => 'Traslado para exportación. (no venta)',
        9 => 'Venta para exportación',
    ]; ///< tipos de traslado

    private $IndServicio = [
        1 => 'Factura o boleta de servicios períodicos domiciliarios', // boleta es periodico no domiciliario (se ajusta)
        2 => 'Factura o boleta de otros servicios períodicos (no domiciliarios)',  // boleta es periodico domiciliario (se ajusta)
        3 => 'Factura de servicios o boleta de ventas y servicios',
        4 => 'Factura exportación de servicios de hotelería o boleta de espectáculos emitida por cuenta de terceros',
        5 => 'Factura exportación de servicios de transporte internacional',
    ]; ///< Tipos de indicadores de servicios

    private $monedas = [
        'DOLAR USA' => 'DOLAR USA',
        'EURO' => 'EURO',
    ]; // Tipo moneda para documentos de exportación

    /**
     * Método que corrije el tipo de documento en caso de ser factura o boleta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-30
     */
    private function getTipoDTE($tipo, $Detalle)
    {
        if (!in_array($tipo, [33, 34, 39,41]))
            return $tipo;
        // determinar tipo de documento
        $netos = 0;
        $exentos = 0;
        if (!isset($Detalle[0]))
            $Detalle = [$Detalle];
        foreach ($Detalle as $d) {
            if (!empty($d['IndExe']))
                $exentos++;
            else
                $netos++;
        }
        // el documento es factura
        if ($tipo == 33 or $tipo == 34) {
            if ($tipo == 33 and !$netos and $exentos)
                return 34;
            if ($tipo == 34 and !$exentos and $netos)
                return 33;
        }
        // es boleta
        else if ($tipo == 39 or $tipo == 41) {
            if ($tipo == 39 and !$netos and $exentos)
                return 41;
            if ($tipo == 41 and !$exentos and $netos)
                return 39;
        }
        // retornar tipo original ya que estaba bien
        return $tipo;
    }

    /**
     * Función de la API que permite emitir un DTE generando su documento
     * temporal. El documento generado no tiene folio, no está firmado y no es
     * enviado al SII. Luego se debe usar la función generar de la API para
     * generar el DTE final y enviarlo al SII.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-10
     */
    public function _api_emitir_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // definir formato de los datos que se están usando como entrada
        // y si es diferente a JSON se busca un parser para poder cargar los
        // datos a un arreglo de PHP (formato JSON)
        $formato = !empty($_GET['formato'])?$_GET['formato']:'json';
        if ($formato!='json') {
            try {
                $this->Api->data = \sasco\LibreDTE\Sii\Dte\Formatos::toArray(
                    $formato, base64_decode($this->Api->data)
                );
            } catch (\Exception $e) {
                $this->Api->send($e->getMessage(), 400);
            }
        }
        // verificar datos del DTE pasados
        if (!is_array($this->Api->data)) {
            $this->Api->send('Debe enviar el DTE como un objeto JSON', 400);
        }
        // buscar emisor del DTE y verificar que usuario tenga permisos para
        // trabajar con el emisor
        if (!isset($this->Api->data['Encabezado']['Emisor']['RUTEmisor'])) {
            $this->Api->send('Debe especificar RUTEmisor en el objeto JSON', 404);
        }
        $Emisor = new Model_Contribuyente($this->Api->data['Encabezado']['Emisor']['RUTEmisor']);
        if (!$Emisor->usuario) {
            $this->Api->send('Contribuyente no está registrado en la aplicación', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/documentos/emitir')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // guardar datos del receptor
        $Receptor = $this->guardarReceptor($this->Api->data['Encabezado']['Receptor']);
        if (!$Receptor) {
            $this->Api->send('No fue posible guardar los datos del receptor', 507);
        }
        // construir arreglo con datos del DTE
        $default = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 33,
                    'Folio' => 0,
                    'FchEmis' => date('Y-m-d'),
                ],
                'Emisor' => [
                    'RUTEmisor' => $Emisor->rut.'-'.$Emisor->dv,
                    'RznSoc' => $Emisor->razon_social,
                    'GiroEmis' => $Emisor->giro,
                    'Telefono' => $Emisor->telefono ? $Emisor->telefono : false,
                    'CorreoEmisor' => $Emisor->email ? $Emisor->email : false,
                    'Acteco' => $Emisor->actividad_economica,
                    'DirOrigen' => $Emisor->direccion,
                    'CmnaOrigen' => $Emisor->getComuna()->comuna,
                ],
            ]
        ];
        $dte = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default, $this->Api->data);
        // corregir dirección sucursal si se indicó
        if (!empty($dte['Encabezado']['Emisor']['CdgSIISucur'])) {
            $sucursal = $Emisor->getSucursal($dte['Encabezado']['Emisor']['CdgSIISucur']);
            $dte['Encabezado']['Emisor']['Sucursal'] = $sucursal->sucursal;
            $dte['Encabezado']['Emisor']['DirOrigen'] = $sucursal->direccion;
            $dte['Encabezado']['Emisor']['CmnaOrigen'] = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->get($sucursal->comuna)->comuna;
        }
        // verificar tipo de documento
        $dte['Encabezado']['IdDoc']['TipoDTE'] = $this->getTipoDTE(
            $dte['Encabezado']['IdDoc']['TipoDTE'], $dte['Detalle']
        );
        if (!$Emisor->documentoAutorizado($dte['Encabezado']['IdDoc']['TipoDTE'])) {
            $this->Api->send('No está autorizado a emitir el tipo de documento '.$dte['Encabezado']['IdDoc']['TipoDTE'], 403);
        }
        // crear objeto Dte y documento temporal
        $Dte = new \sasco\LibreDTE\Sii\Dte($dte, isset($_GET['normalizar'])?(bool)$_GET['normalizar']:true);
        $datos_dte = $Dte->getDatos();
        $datos_json = json_encode($datos_dte);
        if ($datos_dte === false or $datos_json === false) {
            $this->Api->send('No fue posible recuperar los datos del DTE para guardarlos como JSON en el DTE temporal. '.implode('. ', \sasco\LibreDTE\Log::readAll()), 507);
        }
        $resumen = $Dte->getResumen();
        $DteTmp = new Model_DteTmp();
        $DteTmp->datos = $datos_json;
        $DteTmp->emisor = $Emisor->rut;
        $DteTmp->receptor = $Receptor->rut;
        $DteTmp->dte = $resumen['TpoDoc'];
        $DteTmp->codigo = md5(md5($DteTmp->datos).date('U'));
        $DteTmp->fecha = $resumen['FchDoc'];
        if (!$Dte->esExportacion()) {
            $DteTmp->total = $resumen['MntTotal'];
        } else {
            $cambio = false;
            if (!empty($dte['Encabezado']['Totales']['TpoMoneda'])) {
                $moneda = $dte['Encabezado']['Totales']['TpoMoneda'];
                $fecha = $resumen['FchDoc'];
                $cambio = (new \sowerphp\app\Sistema\General\Model_MonedaCambio($moneda, 'CLP', $fecha))->valor;
            }
            $DteTmp->total = $cambio ? round($resumen['MntTotal'] * $cambio) : -1;
        }
        try {
            if ($DteTmp->save()) {
                if ($DteTmp->getTipo()->operacion=='S' and $Emisor->config_pagos_habilitado and $Emisor->config_cobros_temporal_automatico) {
                    $DteTmp->getCobro();
                }
                return [
                    'emisor' => $DteTmp->emisor,
                    'receptor' => $DteTmp->receptor,
                    'dte' => $DteTmp->dte,
                    'codigo' => $DteTmp->codigo,
                ];
            } else {
                $this->Api->send('No fue posible guardar el DTE temporal', 507);
            }
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->Api->send('No fue posible guardar el DTE temporal: '.$e->getMessage(), 507);
        }
    }

    /**
     * Acción para mostrar página de emisión de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function emitir($referencia_dte = null, $referencia_folio = null, $dte_defecto = null, $referencia_codigo = '', $referencia_razon = '')
    {
        $Emisor = $this->getContribuyente();
        if ($referencia_dte and $referencia_folio) {
            $DteEmitido = new Model_DteEmitido($Emisor->rut, $referencia_dte, $referencia_folio, (int)$Emisor->config_ambiente_en_certificacion);
            if (!$DteEmitido->exists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Documento T'.$referencia_dte.'F'.$referencia_folio.' no existe, no se puede referenciar', 'error'
                );
                $this->redirect('/dte/dte_emitidos');
            }
            $DteEmisor = $DteEmitido->getDatos()['Encabezado']['Emisor'];
            $DteReceptor = $DteEmitido->getDatos()['Encabezado']['Receptor'];
            $Comunas = new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas();
            $DteEmisor['CmnaOrigen'] = $Comunas->getComunaByName($DteEmisor['CmnaOrigen']);
            $DteReceptor['CmnaRecep'] = !empty($DteReceptor['CmnaRecep']) ? $Comunas->getComunaByName($DteReceptor['CmnaRecep']) : null;
            if (empty($DteReceptor['GiroRecep'])) {
                $DteReceptor['GiroRecep'] = $DteEmitido->getReceptor()->giro;
            }
            if (empty($DteReceptor['CorreoRecep'])) {
                $DteReceptor['CorreoRecep'] = $DteEmitido->getReceptor()->email;
            }
            $this->set([
                'DteEmitido' => $DteEmitido,
                'DteEmisor' => $DteEmisor,
                'DteReceptor' => $DteReceptor,
            ]);
        }
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/dte.js', '/js/typeahead.bundle.min.js', '/js/js.js'], 'css'=>['/dte/css/dte.css', '/css/typeahead.css']],
            'Emisor' => $Emisor,
            'sucursales_actividades' => $Emisor->getSucursalesActividades(),
            'actividades_economicas' => $Emisor->getListActividades(),
            'giros' => $Emisor->getListGiros(),
            'sucursales' => $Emisor->getSucursales(),
            'sucursal' => '', // TODO: sucursal por defecto
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
            'tasa' => \sasco\LibreDTE\Sii::getIVA(),
            'tipos_dte_autorizados' => $Emisor->getDocumentosAutorizados(),
            'tipos_dte_referencia' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getListReferencias(),
            'tipos_referencia' => (new \website\Dte\Admin\Mantenedores\Model_DteReferenciaTipos())->getList(),
            'IndTraslado' => $this->IndTraslado,
            'IndServicio' => $this->IndServicio,
            'monedas' => $this->monedas,
            'nacionalidades' => \sasco\LibreDTE\Sii\Aduana::getNacionalidades(),
            'codigos' => (new \website\Dte\Admin\Model_Itemes())->getCodigos($Emisor->rut),
            'impuesto_adicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())->getListContribuyente($Emisor->config_extra_impuestos_adicionales),
            'ImpuestoAdicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())->getObjectsContribuyente($Emisor->config_extra_impuestos_adicionales),
            'dte_defecto' => $dte_defecto ? $dte_defecto : $Emisor->config_emision_dte_defecto,
            'referencia_codigo' => (int)$referencia_codigo,
            'referencia_razon' => substr(urldecode($referencia_razon), 0, 90),
        ]);
    }

    /**
     * Acción para generar y mostrar previsualización de emisión de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-26
     */
    public function previsualizacion()
    {
        $Emisor = $this->getContribuyente();
        // si no se viene por POST redirigir
        if (!isset($_POST['submit'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede acceder de forma directa a la previsualización', 'error'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        // si no está autorizado a emitir el tipo de documento redirigir
        if (!$Emisor->documentoAutorizado($_POST['TpoDoc'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No está autorizado a emitir el tipo de documento '.$_POST['TpoDoc'], 'error'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        // obtener dirección y comuna emisor
        $sucursal = $Emisor->getSucursal($_POST['CdgSIISucur']);
        $_POST['DirOrigen'] = $sucursal->direccion;
        $_POST['CmnaOrigen'] = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->get($sucursal->comuna)->comuna;
        // si no se indicó el tipo de documento error
        if (empty($_POST['TpoDoc'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debe indicar el tipo de documento a emitir'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        // revisar datos mínimos
        $datos_minimos = ['FchEmis', 'GiroEmis', 'Acteco', 'DirOrigen', 'CmnaOrigen', 'RUTRecep', 'RznSocRecep', 'DirRecep', 'NmbItem'];
        if (!in_array($_POST['TpoDoc'], [110, 111, 112])) {
            $datos_minimos[] = 'GiroRecep';
            $datos_minimos[] = 'CmnaRecep';
        }
        foreach ($datos_minimos as $attr) {
            if (empty($_POST[$attr])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Error al recibir campos mínimos, falta: '.$attr
                );
                $this->redirect('/dte/documentos/emitir');
            }
        }
        // crear receptor
        list($rut, $dv) = explode('-', str_replace('.', '', $_POST['RUTRecep']));
        $Receptor = new Model_Contribuyente($rut);
        $Receptor->dv = $dv;
        $Receptor->razon_social = $_POST['RznSocRecep'];
        if (!empty($_POST['GiroRecep'])) {
            $Receptor->giro = substr($_POST['GiroRecep'], 0, 40);
        }
        $Receptor->telefono = $_POST['Contacto'];
        $Receptor->email = $_POST['CorreoRecep'];
        $Receptor->direccion = $_POST['DirRecep'];
        if (!empty($_POST['CmnaRecep'])) {
            $Receptor->comuna = $_POST['CmnaRecep'];
        }
        // guardar receptor si no tiene usuario asociado
        if (!$Receptor->usuario) {
            $Receptor->modificado = date('Y-m-d H:i:s');
            try {
                $Receptor->save();
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible guardar el receptor: '.$e->getMessage()
                );
                $this->redirect('/dte/documentos/emitir');
            }
        }
        // generar datos del encabezado para el dte
        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $_POST['TpoDoc'],
                    'Folio' => 0, // en previsualización no se asigna folio
                    'FchEmis' => $_POST['FchEmis'],
                    'FmaPago' => !empty($_POST['FmaPago']) ? $_POST['FmaPago'] : false,
                    'FchCancel' => $_POST['FchVenc'] < $_POST['FchEmis'] ? $_POST['FchVenc'] : false,
                    'PeriodoDesde' => !empty($_POST['PeriodoDesde']) ? $_POST['PeriodoDesde'] : false,
                    'PeriodoHasta' => !empty($_POST['PeriodoHasta']) ? $_POST['PeriodoHasta'] : false,
                    'TermPagoGlosa' => !empty($_POST['TermPagoGlosa']) ? $_POST['TermPagoGlosa'] : false,
                    'FchVenc' => $_POST['FchVenc'] > $_POST['FchEmis'] ? $_POST['FchVenc'] : false,
                ],
                'Emisor' => [
                    'RUTEmisor' => $Emisor->rut.'-'.$Emisor->dv,
                    'RznSoc' => $Emisor->razon_social,
                    'GiroEmis' => $_POST['GiroEmis'],
                    'Telefono' => $Emisor->telefono ? $Emisor->telefono : false,
                    'CorreoEmisor' => $Emisor->email ? $Emisor->email : false,
                    'Acteco' => $_POST['Acteco'],
                    'CdgSIISucur' => $_POST['CdgSIISucur'] ? $_POST['CdgSIISucur'] : false,
                    'DirOrigen' => $_POST['DirOrigen'],
                    'CmnaOrigen' => $_POST['CmnaOrigen'],
                    'CdgVendedor' => $_POST['CdgVendedor'] ? $_POST['CdgVendedor'] : false,
                ],
                'Receptor' => [
                    'RUTRecep' => $Receptor->rut.'-'.$Receptor->dv,
                    'RznSocRecep' => $Receptor->razon_social,
                    'GiroRecep' => !empty($_POST['GiroRecep']) ? $Receptor->giro : false,
                    'Contacto' => $Receptor->telefono ? $Receptor->telefono : false,
                    'CorreoRecep' => $Receptor->email ? $Receptor->email : false,
                    'DirRecep' => $Receptor->direccion,
                    'CmnaRecep' => !empty($_POST['CmnaRecep']) ? $Receptor->getComuna()->comuna : false,
                ],
                'RUTSolicita' => !empty($_POST['RUTSolicita']) ? str_replace('.', '', $_POST['RUTSolicita']) : false,
            ],
        ];
        // agregar pagos programados si es venta a crédito
        if ($_POST['FmaPago']==2) {
            // si no hay pagos explícitos se copia la fecha de vencimiento y el
            // monto total se determinará en el proceso de normalización
            if (empty($_POST['FchPago'])) {
                if ($_POST['FchVenc']>$_POST['FchEmis']) {
                    $dte['Encabezado']['IdDoc']['MntPagos'] = [
                        'FchPago' => $_POST['FchVenc'],
                        'GlosaPagos' => 'Fecha de pago igual al vencimiento',
                    ];
                }
            }
            // hay montos a pagar programados de forma explícita
            else {
                $dte['Encabezado']['IdDoc']['MntPagos'] = [];
                $n_pagos = count($_POST['FchPago']);
                for ($i=0; $i<$n_pagos; $i++) {
                    $dte['Encabezado']['IdDoc']['MntPagos'][] = [
                        'FchPago' => $_POST['FchPago'][$i],
                        'MntPago' => $_POST['MntPago'][$i],
                        'GlosaPagos' => !empty($_POST['GlosaPagos'][$i]) ? $_POST['GlosaPagos'][$i] : false,
                    ];
                }
            }
        }
        // agregar datos de traslado si es guía de despacho
        if ($dte['Encabezado']['IdDoc']['TipoDTE']==52) {
            $dte['Encabezado']['IdDoc']['IndTraslado'] = $_POST['IndTraslado'];
            if (!empty($_POST['Patente']) or !empty($_POST['RUTTrans']) or (!empty($_POST['RUTChofer']) and !empty($_POST['NombreChofer'])) or !empty($_POST['DirDest']) or !empty($_POST['CmnaDest'])) {
                $dte['Encabezado']['Transporte'] = [
                    'Patente' => !empty($_POST['Patente']) ? $_POST['Patente'] : false,
                    'RUTTrans' => !empty($_POST['RUTTrans']) ? str_replace('.', '', $_POST['RUTTrans']) : false,
                    'Chofer' => (!empty($_POST['RUTChofer']) and !empty($_POST['NombreChofer'])) ? [
                        'RUTChofer' => str_replace('.', '', $_POST['RUTChofer']),
                        'NombreChofer' => $_POST['NombreChofer'],
                    ] : false,
                    'DirDest' => !empty($_POST['DirDest']) ? $_POST['DirDest'] : false,
                    'CmnaDest' => !empty($_POST['CmnaDest']) ? (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comuna($_POST['CmnaDest']))->comuna : false,
                ];
            }
        }
        // si hay indicador de servicio se agrega
        if (!empty($_POST['IndServicio'])) {
            if (in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [39, 41])) {
                if ($_POST['IndServicio']==1)
                    $_POST['IndServicio'] = 2;
                else if ($_POST['IndServicio']==2)
                    $_POST['IndServicio'] = 1;
            }
            $dte['Encabezado']['IdDoc']['IndServicio'] = $_POST['IndServicio'];
        }
        // agregar datos de exportación
        if (in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [110, 111, 112])) {
            if (!empty($_POST['Nacionalidad'])) {
                $dte['Encabezado']['Receptor']['Extranjero']['Nacionalidad'] = $_POST['Nacionalidad'];
            }
            $dte['Encabezado']['Totales']['TpoMoneda'] = $_POST['TpoMoneda'];
        }
        // agregar detalle a los datos
        $n_detalles = count($_POST['NmbItem']);
        $dte['Detalle'] = [];
        $n_itemAfecto = 0;
        $n_itemExento = 0;
        for ($i=0; $i<$n_detalles; $i++) {
            $detalle = [];
            // código del item
            if (!empty($_POST['VlrCodigo'][$i])) {
                if (!empty($_POST['TpoCodigo'][$i])) {
                    $TpoCodigo = $_POST['TpoCodigo'][$i];
                } else {
                    $Item = (new \website\Dte\Admin\Model_Itemes())->get($Emisor->rut, $_POST['VlrCodigo'][$i]);
                    $TpoCodigo = $Item->codigo_tipo ? $Item->codigo_tipo : 'INT1';
                }
                $detalle['CdgItem'] = [
                    'TpoCodigo' => $TpoCodigo,
                    'VlrCodigo' => $_POST['VlrCodigo'][$i],
                ];
            }
            // otros datos
            $datos = ['IndExe', 'NmbItem', 'DscItem', 'QtyItem', 'UnmdItem', 'PrcItem', 'CodImpAdic'];
            foreach ($datos as $d) {
                if (isset($_POST[$d][$i])) {
                    $valor = trim($_POST[$d][$i]);
                    if (!empty($valor)) {
                        $detalle[$d] = $valor;
                    }
                }
            }
            // si es boleta se y el item no es exento se le agrega el IVA al precio y el impuesto adicional si existe
            if ($dte['Encabezado']['IdDoc']['TipoDTE']==39 and (!isset($detalle['IndExe']) or !$detalle['IndExe'])) {
                // IVA
                $iva = round($detalle['PrcItem'] * (\sasco\LibreDTE\Sii::getIVA()/100));
                // impuesto adicional TODO: no se permiten impuestos adicionales en boletas por el momento
                if (!empty($detalle['CodImpAdic'])) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No es posible generar una boleta que tenga impuestos adicionales', 'error'
                    );
                    $this->redirect('/dte/documentos/emitir');
                    //$tasa = $_POST['impuesto_adicional_tasa_'.$detalle['CodImpAdic']];
                    //$adicional = round($detalle['PrcItem'] * ($_POST['impuesto_adicional_tasa_'.$detalle['CodImpAdic']]/100));
                    //unset($detalle['CodImpAdic']);
                } else $adicional = 0;
                // agregar al precio
                $detalle['PrcItem'] += $iva + $adicional;
            }
            // descuento
            if (!empty($_POST['ValorDR'][$i]) and !empty($_POST['TpoValor'][$i])) {
                if ($_POST['TpoValor'][$i]=='%')
                    $detalle['DescuentoPct'] = $_POST['ValorDR'][$i];
                else
                    $detalle['DescuentoMonto'] = $_POST['ValorDR'][$i];
            }
            // agregar detalle al listado
            $dte['Detalle'][] = $detalle;
            // contabilizar item afecto o exento
            if (empty($detalle['IndExe'])) $n_itemAfecto++;
            else $n_itemExento++;
        }
        // si hay impuestos adicionales se copian los datos a totales para que se
        // calculen los montos
        $CodImpAdic = [];
        foreach ($dte['Detalle'] as $d) {
            if (!empty($d['CodImpAdic']) and !in_array($d['CodImpAdic'], $CodImpAdic)) {
                $CodImpAdic[] = $d['CodImpAdic'];
            }
        }
        $ImptoReten = [];
        foreach ($CodImpAdic as $codigo) {
            if (!empty($_POST['impuesto_adicional_tasa_'.$codigo])) {
                $ImptoReten[] = [
                    'TipoImp' => $codigo,
                    'TasaImp' => $_POST['impuesto_adicional_tasa_'.$codigo],
                ];
            }
        }
        if ($ImptoReten) {
            $dte['Encabezado']['Totales']['ImptoReten'] = $ImptoReten;
        }
        // si la empresa es constructora se marca para obtener el cŕedito del 65%
        if ($Emisor->config_extra_constructora and in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [33, 52, 56, 61]) and !empty($_POST['CredEC'])) {
            $dte['Encabezado']['Totales']['CredEC'] = true;
        }
        // agregar descuento globales
        if (!empty($_POST['ValorDR_global']) and !empty($_POST['TpoValor_global'])) {
            $dte['DscRcgGlobal'] = [];
            if ($n_itemAfecto) {
                $dte['DscRcgGlobal'][] = [
                    'TpoMov' => 'D',
                    'TpoValor' => $_POST['TpoValor_global'],
                    'ValorDR' => $_POST['ValorDR_global'],
                ];
            }
            if ($n_itemExento) {
                $dte['DscRcgGlobal'][] = [
                    'TpoMov' => 'D',
                    'TpoValor' => $_POST['TpoValor_global'],
                    'ValorDR' => $_POST['ValorDR_global'],
                    'IndExeDR' => 1,
                ];
            }
        }
        // agregar referencias
        if (isset($_POST['TpoDocRef'][0])) {
            $n_referencias = count($_POST['TpoDocRef']);
            $dte['Referencia'] = [];
            for ($i=0; $i<$n_referencias; $i++) {
                $dte['Referencia'][] = [
                    'TpoDocRef' => $_POST['TpoDocRef'][$i],
                    'IndGlobal' => is_numeric($_POST['FolioRef'][$i]) and $_POST['FolioRef'][$i] == 0 ? 1 : false,
                    'FolioRef' => $_POST['FolioRef'][$i],
                    'FchRef' => $_POST['FchRef'][$i],
                    'CodRef' => !empty($_POST['CodRef'][$i]) ? $_POST['CodRef'][$i] : false,
                    'RazonRef' => !empty($_POST['RazonRef'][$i]) ? $_POST['RazonRef'][$i] : false,
                ];
            }
        }
        // consumir servicio web para crear documento temporal
        $response = $this->consume('/api/dte/documentos/emitir', $dte);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message(
                $response['body'], 'error'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        if (empty($response['body']['emisor']) or empty($response['body']['receptor']) or empty($response['body']['dte']) or empty($response['body']['codigo'])) {
            $msg = is_string($response['body']) ? $response['body'] : json_encode($response['body']);
            \sowerphp\core\Model_Datasource_Session::message(
                'Hubo problemas al generar el documento temporal: '.$msg, 'error'
            );
            $this->redirect('/dte/documentos/emitir');
        }
        // enviar DTE automáticaente sin previsualizar
        if ($Emisor->config_sii_envio_automatico) {
            $this->redirect('/dte/documentos/generar/'.$response['body']['receptor'].'/'.$response['body']['dte'].'/'.$response['body']['codigo']);
        }
        // mostrar previsualización y botón para envío manual
        else {
            $DteTmp = new Model_DteTmp(
                (int)$response['body']['emisor'],
                (int)$response['body']['receptor'],
                (int)$response['body']['dte'],
                $response['body']['codigo']
            );
            $Dte = new \sasco\LibreDTE\Sii\Dte($dte);
            $this->set([
                'Emisor' => $Emisor,
                'resumen' => $Dte->getResumen(),
                'DteTmp' => $DteTmp,
                'Dte' => $Dte,
            ]);
        }
    }

    /**
     * Función de la API que permite emitir un DTE a partir de un documento
     * temporal, asignando folio, firmando y enviando al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-26
     */
    public function _api_generar_POST()
    {
        extract($this->Api->getQuery([
            'getXML' => false,
        ]));
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar datos del DTE pasados
        if (!is_array($this->Api->data)) {
            $this->Api->send('Debe enviar los datos del DTE temporal como objeto', 400);
        }
        // buscar datos mínimos
        foreach (['emisor', 'receptor', 'dte', 'codigo'] as $col) {
            if (!isset($this->Api->data[$col])) {
                $this->Api->send('Debe especificar: '.$col, 404);
            }
        }
        // crear emisor y verificar permisos
        $Emisor = new Model_Contribuyente($this->Api->data['emisor']);
        if (!$Emisor->usuario) {
            $this->Api->send('Contribuyente no está registrado en la aplicación', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/documentos/generar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        if (!$Emisor->documentoAutorizado($this->Api->data['dte'])) {
            $this->Api->send('No está autorizado a emitir el tipo de documento '.$this->Api->data['dte'], 403);
        }
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp(
            (int)$this->Api->data['emisor'],
            (int)$this->Api->data['receptor'],
            (int)$this->Api->data['dte'],
            $this->Api->data['codigo']
        );
        if (!$DteTmp->exists()) {
            $this->Api->send('No existe el DTE temporal solicitado', 404);
        }
        // generar DTE real
        try {
            $DteEmitido = $DteTmp->generar($User->id);
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), $e->getCode());
        }
        // entregar DTE emitido al cliente de la API
        if (!$getXML)
            $DteEmitido->xml = false;
        return $DteEmitido;
    }

    /**
     * Método que genera el XML del DTE temporal con Folio y Firma y lo envía
     * al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function generar($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        $response = $this->consume('/api/dte/documentos/generar', [
            'emisor' => $Emisor->rut,
            'receptor' => $receptor,
            'dte' => $dte,
            'codigo' => $codigo,
        ]);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message(
                $response['body'], 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        $DteEmitido = (new Model_DteEmitido())->set($response['body']);
        if (!in_array($DteEmitido->dte, [39, 41])) {
            if ($DteEmitido->track_id) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Documento emitido y envíado al SII, ahora debe verificar estado del envío. TrackID: '.$DteEmitido->track_id, 'ok'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Documento emitido, pero no pudo ser envíado al SII, debe reenviar.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'warning'
                );
            }
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento emitido', 'ok'
            );
        }
        $this->redirect('/dte/dte_emitidos/ver/'.$DteEmitido->dte.'/'.$DteEmitido->folio);
    }

    /**
     * Método que guarda los datos del Emisor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-04
     */
    private function guardarEmisor($datos)
    {
        list($emisor, $dv) = explode('-', $datos['RUTEmisor']);
        $Emisor = new Model_Contribuyente($emisor);
        if ($Emisor->usuario)
            return null;
        $Emisor->dv = $dv;
        $Emisor->razon_social = substr($datos['RznSoc'], 0, 100);
        if (!empty($datos['GiroEmis']))
            $Emisor->giro = substr($datos['GiroEmis'], 0, 80);
        if (!empty($datos['Telefono']))
            $Emisor->telefono = substr($datos['Telefono'], 0, 20);
        if (!empty($datos['CorreoEmisor']))
            $Emisor->email = substr($datos['CorreoEmisor'], 0, 80);
        $Emisor->actividad_economica = (int)$datos['Acteco'];
        if (!empty($datos['DirOrigen']))
            $Emisor->direccion = substr($datos['DirOrigen'], 0, 70);
        if (is_numeric($datos['CmnaOrigen'])) {
            $Emisor->comuna = $datos['CmnaOrigen'];
        } else {
            $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['CmnaOrigen']);
            if ($comuna) {
                $Emisor->comuna = $comuna;
            }
        }
        $Emisor->modificado = date('Y-m-d H:i:s');
        try {
            return $Emisor->save();
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
    }

    /**
     * Método que guarda un Receptor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-09
     */
    private function guardarReceptor($datos)
    {
        $aux = explode('-', $datos['RUTRecep']);
        if (!isset($aux[1]))
            return false;
        list($receptor, $dv) = $aux;
        $Receptor = new Model_Contribuyente($receptor);
        if ($Receptor->usuario)
            return $Receptor;
        $Receptor->dv = $dv;
        if (!empty($datos['RznSocRecep']))
            $Receptor->razon_social = substr($datos['RznSocRecep'], 0, 100);
        if (!empty($datos['GiroRecep']))
            $Receptor->giro = substr($datos['GiroRecep'], 0, 80);
        if (!empty($datos['Contacto']))
            $Receptor->telefono = substr($datos['Contacto'], 0, 20);
        if (!empty($datos['CorreoRecep']))
            $Receptor->email = substr($datos['CorreoRecep'], 0, 80);
        if (!empty($datos['DirRecep']))
            $Receptor->direccion = substr($datos['DirRecep'], 0, 70);
        if (!empty($datos['CmnaRecep'])) {
            if (is_numeric($datos['CmnaRecep'])) {
                $Receptor->comuna = $datos['CmnaRecep'];
            } else {
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['CmnaRecep']);
                if ($comuna) {
                    $Receptor->comuna = $comuna;
                }
            }
        }
        $Receptor->modificado = date('Y-m-d H:i:s');
        try {
            return $Receptor->save() ? $Receptor : false;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
    }

    /**
     * Recurso de la API que genera el PDF de los DTEs contenidos en un EnvioDTE
     * @deprecated API se movió a /api/utilidades/documentos/generar_pdf
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-23
     */
    public function _api_generar_pdf_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // realizar consulta al servicio web verdadero
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($User->hash);
        $response = $rest->post($this->request->url.'/api/utilidades/documentos/generar_pdf', $this->Api->data);
        if ($response===false) {
            $this->Api->send(implode("\n", $rest->getErrors()), 500);
        }
        if ($response['status']['code']!=200) {
            $this->Api->send($response['body'], 500);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        foreach (['Content-Disposition', 'Content-Length', 'Content-Type'] as $header) {
            if (isset($response['header'][$header]))
                header($header.': '.$response['header'][$header]);
        }
        echo $response['body'];
        exit;
    }

}
