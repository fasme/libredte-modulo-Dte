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
 * Controlador para intercambio entre contribuyentes
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-06-15
 */
class Controller_DteIntercambios extends \Controller_App
{

    /**
     * Acción para mostrar la bandeja de intercambio de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-15
     */
    public function listar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'intercambios' => $Emisor->getIntercambios(false),
        ]);
    }

    /**
     * Acción para descargar los intercambios pendientes de procesar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-22
     */
    public function pendientes()
    {
        $Emisor = $this->getContribuyente();
        $pendientes = $Emisor->getIntercambios();
        if (!$pendientes) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay intercambios pendientes', 'warning'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        foreach ($pendientes as &$i) {
            if (!is_numeric($i['documentos'])) {
                $documentos = explode('|', $i['documentos']);
                foreach ($documentos as &$d) {
                    list($tipo, $folio) = explode(',', $d);
                    $d = 'T'.$tipo.'F'.$folio;
                }
                $i['documentos'] = implode("\n", $documentos);
            }
        }
        array_unshift($pendientes, array_keys($pendientes[0]));
        \sowerphp\general\Utility_Spreadsheet_CSV::generate($pendientes, $Emisor->rut.'_intercambios_pendientes_'.date('Ymd'));
        exit;
    }

    /**
     * Acción que muestra la página de un intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-11-24
     */
    public function ver($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE intercambiado
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // asignar variables para la vista
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/intercambio.js']],
            'Emisor' => $Emisor,
            'DteIntercambio' => $DteIntercambio,
            'EnvioDte' => $DteIntercambio->getEnvioDte(),
            'Documentos' => $DteIntercambio->getDocumentos(),
            'Firma' => $Firma,
        ]);
    }

    /**
     * Acción que permite eliminar un intercambio desde la bandeja
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-10
     */
    public function eliminar($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE intercambiado
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // verificar que el intercambio no esté en uso en los documentos recibidos
        if ($DteIntercambio->recibido()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'El intercambio solicitado fue recibido, no se puede eliminar', 'error'
            );
            $this->redirect('/dte/dte_intercambios/ver/'.$codigo);
        }
        // eliminar el intercambio y redireccionar
        $DteIntercambio->delete();
        \sowerphp\core\Model_Datasource_Session::message(
            'Intercambio '.$codigo.' eliminado', 'ok'
        );
        $this->redirect('/dte/dte_intercambios/listar');
    }

    /**
     * Acción que muestra el mensaje del email de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-08
     */
    public function html($codigo)
    {
        $Emisor = $this->getContribuyente();
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        $this->layout = null;
        $this->set([
            'html' => $DteIntercambio->mensaje_html ? base64_decode($DteIntercambio->mensaje_html) : 'No hay mensaje HTML',
        ]);
    }

    /**
     * Acción para actualizar la bandeja de intercambio. Guarda los DTEs
     * recibidos por intercambio y guarda los acuses de recibos de DTEs
     * enviados por otros contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-05
     */
    public function actualizar()
    {
        $Emisor = $this->getContribuyente();
        try {
            $resultado = $Emisor->actualizarBandejaIntercambio();
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                $e->getMessage(), ($e->getCode()==500 ? 'error' : 'info')
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        extract($resultado);
        if ($n_uids>1)
            $encontrados = 'Se encontraron '.num($n_uids).' correos';
        else
            $encontrados = 'Se encontró '.num($n_uids).' correo';
        \sowerphp\core\Model_Datasource_Session::message(
            $encontrados.': EnvioDTE='.num($n_EnvioDTE).',  EnvioRecibos='.num($n_EnvioRecibos).', RecepcionEnvio='.num($n_RecepcionEnvio).', ResultadoDTE='.num($n_ResultadoDTE).' y Omitidos='.num($omitidos), 'ok'
        );
        $this->redirect('/dte/dte_intercambios/listar');
    }

    /**
     * Acción para mostrar el PDF de un EnvioDTE de un intercambio de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-09
     */
    public function pdf($codigo, $cedible = false, $emisor = null, $dte = null, $folio = null)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE intercambiado
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // obtener XML que se debe usar
        if ($DteIntercambio->documentos>1 and $emisor and $dte and $folio) {
            $Documento = $DteIntercambio->getDocumento($emisor, $dte, $folio);
            if (!$Documento) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No existe el DTE T'.$dte.'F'.$folio.' del RUT '.$emisor.' en el intercambio N° '.$codigo, 'error'
                );
                $this->redirect('/dte/dte_intercambios/ver/'.$codigo);
            }
            $xml = base64_encode($Documento->saveXML());
        } else {
            $xml = $DteIntercambio->archivo_xml;
        }
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $data = [
            'xml' => $xml,
            'cedible' => $cedible,
        ];
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User ? $this->Auth->User->hash : $this->token);
        $response = $rest->post($this->request->url.'/api/utilidades/documentos/generar_pdf', $data);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            return;
        }
        // si dió código 200 se entrega la respuesta del servicio web
        foreach (['Content-Disposition', 'Content-Length', 'Content-Type'] as $header) {
            if (isset($response['header'][$header]))
                header($header.': '.$response['header'][$header]);
        }
        echo $response['body'];
        exit;
    }

    /**
     * Acción que descarga el XML del documento intercambiado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-27
     */
    public function xml($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // entregar XML
        $xml = base64_decode($DteIntercambio->archivo_xml);
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$DteIntercambio->archivo.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que procesa y responde al intercambio recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-10
     */
    public function responder($codigo)
    {
        $Emisor = $this->getContribuyente();
        // si no se viene por post error
        if (!isset($_POST['submit'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede acceder de forma directa a '.$this->request->request, 'error'
            );
            $this->redirect(str_replace('responder', 'ver', $this->request->request));
        }
        // obtener DTE emitido
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        //
        // construir RecepcionDTE
        //
        $RecepcionDTE = [];
        $n_dtes = count($_POST['TipoDTE']);
        for ($i=0; $i<$n_dtes; $i++) {
            $RecepcionDTE[] = [
                'TipoDTE' => $_POST['TipoDTE'][$i],
                'Folio' => $_POST['Folio'][$i],
                'FchEmis' => $_POST['FchEmis'][$i],
                'RUTEmisor' => $_POST['RUTEmisor'][$i],
                'RUTRecep' => $_POST['RUTRecep'][$i],
                'MntTotal' => $_POST['MntTotal'][$i],
                'EstadoRecepDTE' => $_POST['EstadoRecepDTE'][$i],
                'RecepDTEGlosa' => $_POST['RecepDTEGlosa'][$i],
            ];
        }
        // armar respuesta de envío
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(base64_decode($DteIntercambio->archivo_xml));
        $Caratula = $EnvioDte->getCaratula();
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        $RespuestaEnvio->agregarRespuestaEnvio([
            'NmbEnvio' => $DteIntercambio->archivo,
            'CodEnvio' => $DteIntercambio->codigo,
            'EnvioDTEID' => $EnvioDte->getID(),
            'Digest' => $EnvioDte->getDigest(),
            'RutEmisor' => $EnvioDte->getEmisor(),
            'RutReceptor' => $EnvioDte->getReceptor(),
            'EstadoRecepEnv' => $_POST['EstadoRecepEnv'],
            'RecepEnvGlosa' => $_POST['RecepEnvGlosa'],
            'NroDTE' => count($RecepcionDTE),
            'RecepcionDTE' => $RecepcionDTE,
        ]);
        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula([
            'RutResponde' => $Emisor->rut.'-'.$Emisor->dv,
            'RutRecibe' => $Caratula['RutEmisor'],
            'IdRespuesta' => $DteIntercambio->codigo,
            'NmbContacto' => $_POST['NmbContacto'],
            'MailContacto' => $_POST['MailContacto'],
        ]);
        $RespuestaEnvio->setFirma($Firma);
        // generar y validar XML
        $RecepcionDTE_xml = $RespuestaEnvio->generar();
        if (!$RespuestaEnvio->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar RecepcionDTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('responder', 'ver', $this->request->request));
        }
        //
        // generar EnvioRecibos
        //
        $EnvioRecibos = new \sasco\LibreDTE\Sii\EnvioRecibos();
        $EnvioRecibos->setCaratula([
            'RutResponde' => $Emisor->rut.'-'.$Emisor->dv,
            'RutRecibe' => $Caratula['RutEmisor'],
            'NmbContacto' => $_POST['NmbContacto'],
            'MailContacto' => $_POST['MailContacto'],
        ]);
        $EnvioRecibos->setFirma($Firma);
        // procesar cada DTE
        $EnvioRecibos_r = [];
        for ($i=0; $i<$n_dtes; $i++) {
            if ($_POST['acuse'][$i]) {
                $EnvioRecibos->agregar([
                    'TipoDoc' => $_POST['TipoDTE'][$i],
                    'Folio' => $_POST['Folio'][$i],
                    'FchEmis' => $_POST['FchEmis'][$i],
                    'RUTEmisor' => $_POST['RUTEmisor'][$i],
                    'RUTRecep' => $_POST['RUTRecep'][$i],
                    'MntTotal' => $_POST['MntTotal'][$i],
                    'Recinto' => $_POST['Recinto'],
                    'RutFirma' => $Firma->getID(),
                ]);
                $EnvioRecibos_r[] = 'T'.$_POST['TipoDTE'][$i].'F'.$_POST['Folio'][$i];
            }
        }
        // generar y validar XML
        if ($EnvioRecibos_r) {
            $EnvioRecibos_xml = $EnvioRecibos->generar();
            if (!$EnvioRecibos->schemaValidate()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible generar EnvioRecibos.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
                $this->redirect(str_replace('responder', 'ver', $this->request->request));
            }
        }
        //
        // generar ResultadoDTE
        //
        // objeto para la respuesta
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        // procesar cada DTE
        for ($i=0; $i<$n_dtes; $i++) {
            $estado = !$_POST['EstadoRecepDTE'][$i] ? 0 : 2;
            $RespuestaEnvio->agregarRespuestaDocumento([
                'TipoDTE' => $_POST['TipoDTE'][$i],
                'Folio' => $_POST['Folio'][$i],
                'FchEmis' => $_POST['FchEmis'][$i],
                'RUTEmisor' => $_POST['RUTEmisor'][$i],
                'RUTRecep' => $_POST['RUTRecep'][$i],
                'MntTotal' => $_POST['MntTotal'][$i],
                'CodEnvio' => $i+1,
                'EstadoDTE' => $estado,
                'EstadoDTEGlosa' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$estado],
            ]);
        }
        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula([
            'RutResponde' => $Emisor->rut.'-'.$Emisor->dv,
            'RutRecibe' => $Caratula['RutEmisor'],
            'IdRespuesta' => $DteIntercambio->codigo,
            'NmbContacto' => $_POST['NmbContacto'],
            'MailContacto' => $_POST['MailContacto'],
        ]);
        $RespuestaEnvio->setFirma($Firma);
        // generar y validar XML
        $ResultadoDTE_xml = $RespuestaEnvio->generar();
        if (!$RespuestaEnvio->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar ResultadoDTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect(str_replace('responder', 'ver', $this->request->request));
        }
        //
        // guardar estado del intercambio y usuario que lo procesó
        //
        $DteIntercambio->estado = (int)$_POST['EstadoRecepEnv'];
        $DteIntercambio->recepcion_xml = base64_encode($RecepcionDTE_xml);
        if (isset($EnvioRecibos_xml))
            $DteIntercambio->recibos_xml = base64_encode($EnvioRecibos_xml);
        $DteIntercambio->resultado_xml = base64_encode($ResultadoDTE_xml);
        $DteIntercambio->fecha_hora_respuesta = date('Y-m-d H:i:s');
        $DteIntercambio->usuario = $this->Auth->User->id;
        $DteIntercambio->save();
        //
        // guardar documentos que han sido aceptados con acuse de recibo
        //
        if (isset($EnvioRecibos_xml)) {
            // actualizar datos del emisor si no tine usuario asociado
            $EmisorIntercambio = $DteIntercambio->getEmisor();
            if (!$EmisorIntercambio->usuario) {
                $emisor = $DteIntercambio->getDocumentos()[0]->getDatos()['Encabezado']['Emisor'];
                $EmisorIntercambio->razon_social = $emisor['RznSoc'];
                if (!empty($emisor['GiroEmis']))
                    $EmisorIntercambio->giro = $emisor['GiroEmis'];
                if (!empty($emisor['CorreoEmisor']))
                    $EmisorIntercambio->email = $emisor['CorreoEmisor'];
                if (!empty($emisor['Acteco'])) {
                    $actividad_economica = $EmisorIntercambio->actividad_economica;
                    $EmisorIntercambio->actividad_economica = $emisor['Acteco'];
                    if (!$EmisorIntercambio->getActividadEconomica()->exists())
                        $EmisorIntercambio->actividad_economica = $actividad_economica;
                }
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($emisor['CmnaOrigen']);
                if ($comuna) {
                    $EmisorIntercambio->direccion = $emisor['DirOrigen'];
                    $EmisorIntercambio->comuna = $comuna;
                }
                $EmisorIntercambio->modificado = date('Y-m-d H:i:s');
                try {
                    $EmisorIntercambio->save();
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                }
            }
            // guardar documentos que tienen acuse de recibo como dte recibidos
            $Documentos = $DteIntercambio->getDocumentos();
            foreach ($Documentos as $Dte) {
                if (in_array($Dte->getID(true), $EnvioRecibos_r)) {
                    $resumen = $Dte->getResumen();
                    $DteRecibido = new Model_DteRecibido($DteIntercambio->getEmisor()->rut, $resumen['TpoDoc'], $resumen['NroDoc'], (int)$DteIntercambio->certificacion);
                    if (!$DteRecibido->exists()) {
                        $DteRecibido->receptor = $Emisor->rut;
                        $DteRecibido->tasa = (int)$resumen['TasaImp'];
                        $DteRecibido->fecha = $resumen['FchDoc'];
                        $DteRecibido->sucursal_sii = (int)$resumen['CdgSIISucur'];
                        if ($resumen['MntExe'])
                            $DteRecibido->exento = $resumen['MntExe'];
                        if ($resumen['MntNeto'])
                            $DteRecibido->neto = $resumen['MntNeto'];
                        $DteRecibido->iva = (int)$resumen['MntIVA'];
                        $DteRecibido->total = (int)$resumen['MntTotal'];
                        $DteRecibido->usuario = $this->Auth->User->id;
                        $DteRecibido->intercambio = $DteIntercambio->codigo;
                        $DteRecibido->impuesto_tipo = 1; // se asume siempre que es IVA
                        if (!empty($_POST['periodo'])) {
                            $DteRecibido->periodo = $_POST['periodo'];
                        }
                        if (!empty($_POST['sucursal'])) {
                            $DteRecibido->sucursal_sii_receptor = $_POST['sucursal'];
                        }
                        // si hay IVA y esta fuera de plazo se marca como no recuperable
                        if ($DteRecibido->iva and $DteRecibido->periodo) {
                            $periodo_dte = (int)substr(str_replace('-', '', $DteRecibido->fecha),0, 6);
                            $meses = \sowerphp\general\Utility_Date::countMonths($periodo_dte, $DteRecibido->periodo);
                            if ($meses > 2) {
                                $DteRecibido->iva_no_recuperable = json_encode([
                                    ['codigo'=>2, 'monto'=>$DteRecibido->iva]
                                ]);
                            }
                        }
                        // copiar impuestos adicionales
                        $datos = $Dte->getDatos();
                        if (!empty($datos['Encabezado']['Totales']['ImptoReten'])) {
                            if (!isset($datos['Encabezado']['Totales']['ImptoReten'][0])) {
                                $datos['Encabezado']['Totales']['ImptoReten'] = [$datos['Encabezado']['Totales']['ImptoReten']];
                            }
                            $DteRecibido->impuesto_adicional = [];
                            foreach ($datos['Encabezado']['Totales']['ImptoReten'] as $ia) {
                                $DteRecibido->impuesto_adicional[] = [
                                    'codigo' => $ia['TipoImp'],
                                    'tasa' => !empty($ia['TasaImp']) ? $ia['TasaImp'] : null,
                                    'monto' => $ia['MontoImp'],
                                ];
                            }
                            $DteRecibido->impuesto_adicional = json_encode($DteRecibido->impuesto_adicional);
                        }
                        // si es empresa exenta el IVA es no recuperable
                        if ($DteRecibido->iva and $Emisor->config_extra_exenta) {
                            $DteRecibido->iva_no_recuperable = json_encode([
                                ['codigo'=>1, 'monto'=>$DteRecibido->iva]
                            ]);
                        }
                        $DteRecibido->save();
                    } else if (!$DteRecibido->intercambio) {
                        $DteRecibido->intercambio = $DteIntercambio->codigo;
                        $DteRecibido->save();
                    }
                }
            }
        }
        //
        // enviar los 3 XML de respuesta por email
        //
        $email = $Emisor->getEmailSmtp();
        $email->to($_POST['responder_a']);
        $email->subject($Emisor->rut.'-'.$Emisor->dv.' - Respuesta intercambio N° '.$DteIntercambio->codigo);
        foreach (['RecepcionDTE', 'EnvioRecibos', 'ResultadoDTE'] as $xml) {
            if (isset(${$xml.'_xml'})) {
                $email->attach([
                    'data' => ${$xml.'_xml'},
                    'name' => $xml.'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$DteIntercambio->codigo.'.xml',
                    'type' => 'application/xml',
                ]);
            }
        }
        // enviar email
        $status = $email->send('Se adjuntan XMLs de respuesta a intercambio de DTE.');
        if ($status===true) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se procesaron DTEs de intercambio y se envió la respuesta a: '.$_POST['responder_a'], 'ok'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se procesaron DTEs de intercambio, pero no fue posible enviar el email, por favor intente nuevamente.<br /><em>'.$status['message'].'</em>', 'error'
            );
        }
        $this->redirect(str_replace('responder', 'ver', $this->request->request));
    }

    /**
     * Acción que entrega los XML del resultado de la revisión del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function resultados_xml($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // si no hay XML error
        if (!$DteIntercambio->recepcion_xml and !$DteIntercambio->recibos_xml and !$DteIntercambio->resultado_xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existen archivos de resultado generados, no se ha procesado aun el intercambio', 'error'
            );
            $this->redirect(str_replace('resultados_xml', 'ver', $this->request->request));
        }
        // agregar a archivo comprimido y entregar
        $dir = TMP.'/resultado_intercambio_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$DteIntercambio->codigo;
        if (is_dir($dir))
            \sowerphp\general\Utility_File::rmdir($dir);
        if (!mkdir($dir)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible crear el directorio temporal para los XML', 'error'
            );
            $this->redirect(str_replace('resultados_xml', 'ver', $this->request->request));
        }
        if ($DteIntercambio->recepcion_xml)
            file_put_contents($dir.'/RecepcionDTE.xml', base64_decode($DteIntercambio->recepcion_xml));
        if ($DteIntercambio->recibos_xml)
            file_put_contents($dir.'/EnvioRecibos.xml', base64_decode($DteIntercambio->recibos_xml));
        if ($DteIntercambio->resultado_xml)
            file_put_contents($dir.'/ResultadoDTE.xml', base64_decode($DteIntercambio->resultado_xml));
        \sowerphp\general\Utility_File::compress($dir, ['format'=>'zip', 'delete'=>true]);
        exit;
    }

}
