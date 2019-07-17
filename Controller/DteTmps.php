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
 * Controlador de dte temporales
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-05-03
 */
class Controller_DteTmps extends \Controller_App
{

    /**
     * Se permite descargar las cotizaciones sin estar logueado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-13
     */
    public function beforeFilter()
    {
        $this->Auth->allow('cotizacion');
        parent::beforeFilter();
    }

    /**
     * Método que muestra los documentos temporales disponibles
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-10-29
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $DteTmps = new Model_DteTmps();
        $DteTmps->setWhereStatement(['emisor = :rut'], [':rut'=>$Emisor->rut]);
        $DteTmps->setOrderByStatement('fecha DESC', 'receptor', 'codigo');
        $this->set([
            'Emisor' => $Emisor,
            'dtes' => $DteTmps->getObjects(),
        ]);
    }

    /**
     * Acción que muestra la página del documento temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-09
     */
    public function ver($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/dte.js']],
            'Emisor' => $Emisor,
            'Receptor' => $DteTmp->getReceptor(),
            'DteTmp' => $DteTmp,
            'datos' => $DteTmp->getDatos(),
            'emails' => $DteTmp->getEmails(),
            'email_html' => $Emisor->getEmailFromTemplate('dte'),
        ]);
    }

    /**
     * Método que genera la cotización en PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-04
     */
    public function cotizacion($receptor, $dte, $codigo, $emisor = null)
    {
        $Emisor = $emisor===null ? $this->getContribuyente() : new Model_Contribuyente($emisor);
        // datos por defecto
        extract($this->getQuery([
            'papelContinuo' => !empty($_POST['papelContinuo']) ? $_POST['papelContinuo']: $Emisor->config_pdf_dte_papel,
            'compress' => false,
        ]));
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($Emisor->getUsuario()->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_tmps/pdf/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$Emisor->rut.'?cotizacion=1&papelContinuo='.$papelContinuo.'&compress='.$compress);
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            $this->redirect('/dte/dte_tmps');
        }
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            $this->redirect('/dte/dte_tmps');
        }
        // si dió código 200 se entrega la respuesta del servicio web
        foreach (['Content-Length', 'Content-Type', 'Content-Disposition'] as $header) {
            if (!empty($response['header'][$header])) {
                header($header.': '.$response['header'][$header]);
            }
        }
        echo $response['body'];
        exit;
    }

    /**
     * Método que genera la previsualización del PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-04
     */
    public function pdf($receptor, $dte, $codigo, $disposition = 'attachment')
    {
        $Emisor = $this->getContribuyente();
        // datos por defecto
        extract($this->getQuery([
            'papelContinuo' => !empty($_POST['papelContinuo']) ? $_POST['papelContinuo']: $Emisor->config_pdf_dte_papel,
            'compress' => false,
        ]));
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_tmps/pdf/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$Emisor->rut.'?papelContinuo='.$papelContinuo.'&compress='.$compress);
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            $this->redirect('/dte/dte_tmps');
        }
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            $this->redirect('/dte/dte_tmps');
        }
        // si dió código 200 se entrega la respuesta del servicio web
        foreach (['Content-Length', 'Content-Type'] as $header) {
            if (isset($response['header'][$header]))
                header($header.': '.$response['header'][$header]);
        }
        header('Content-Disposition: '.($disposition=='inline'?'inline':(!empty($response['header']['Content-Disposition'])?$response['header']['Content-Disposition']:'inline')));
        echo $response['body'];
        exit;
    }

    /**
     * Acción que permite ver una vista previa del correo en HTML
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-04-29
     */
    public function email_html($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // tratar de obtener email
        $email_html = $Emisor->getEmailFromTemplate('dte', $DteTmp);
        if (!$email_html) {
            \sowerphp\core\Model_Datasource_Session::message('No existe correo en HTML para el envío del documento', 'error');
            $this->redirect(str_replace('email_html', 'ver', $this->request->request));
        }
        echo $email_html;
        exit;
    }

    /**
     * Acción que envía por email el PDF de la cotización del DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-04-29
     */
    public function enviar_email($receptor, $dte, $codigo)
    {
        if (isset($_POST['submit'])) {
            // armar emails a enviar
            $emails = [];
            if (!empty($_POST['emails'])) {
                $emails = $_POST['emails'];
            }
            if (!empty($_POST['para_extra'])) {
                $emails = array_merge($emails, explode(',', str_replace(' ', '', $_POST['para_extra'])));
            }
            // enviar correo
            $Emisor = $this->getContribuyente();
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post(
                $this->request->url.'/api/dte/dte_tmps/enviar_email/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$Emisor->rut,
                [
                    'emails' => $emails,
                    'asunto' => $_POST['asunto'],
                    'mensaje' => $_POST['mensaje'],
                    'cotizacion' => $_POST['cotizacion'],
                ]
            );
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            }
            else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se envió el PDF a: '.implode(', ', $emails), 'ok'
                );
            }
        }
        $this->redirect(str_replace('enviar_email', 'ver', $this->request->request).'#email');
    }

    /**
     * Acción de la API que permite enviar el DTE temporal por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-10-05
     */
    public function _api_enviar_email_POST($receptor, $dte, $codigo, $emisor)
    {
        // verificar permisos y crear DteEmitido
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/actualizar_estado')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists())
            $this->Api->send('No existe el documento temporal solicitado N° '.$DteTmp->getFolio(), 404);
        // parametros por defecto
        $data = array_merge([
            'emails' => $DteTmp->getReceptor()->email,
            'asunto' => null,
            'mensaje' => null,
            'cotizacion' => true,
        ], $this->Api->data);
        // enviar por correo
        try {
            $DteTmp->email($data['emails'], $data['asunto'], $data['mensaje'], $data['cotizacion']);
            return true;
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), 500);
        }
    }

    /**
     * Recurso de la API que genera la previsualización del PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-04-13
     */
    public function _api_pdf_GET($receptor, $dte, $codigo, $emisor)
    {
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
                $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/xml')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            $this->Api->send('No existe el DTE temporal solicitado', 404);
        }
        // datos por defecto
        extract($this->getQuery([
            'cotizacion' => 0,
            'papelContinuo' => $Emisor->config_pdf_dte_papel,
            'compress' => false,
        ]));
        // armar xml a partir de datos del dte temporal
        $xml = $DteTmp->getEnvioDte($cotizacion ? $DteTmp->getFolio() : 0)->generar();
        if (!$xml) {
            $this->Api->send(
                'No fue posible crear el PDF para previsualización:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 507
            );
        }
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $data = [
            'xml' => base64_encode($xml),
            'cedible' => false,
            'papelContinuo' => $papelContinuo,
            'compress' => $compress,
        ];
        // consultar servicio web de LibreDTE
        $ApiDtePdfClient = $Emisor->getApiClient('dte_pdf');
        if (!$ApiDtePdfClient) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($User->hash);
            $response = $rest->post($this->request->url.'/api/utilidades/documentos/generar_pdf', $data);
        }
        // consultar servicio web del contribuyente
        else {
            $response = $ApiDtePdfClient->post($ApiDtePdfClient->url, $data);
        }
        // procesar respuesta
        if ($response['status']['code']!=200) {
            $this->Api->send($response['body'], $response['status']['code']);
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
     * Método que genera la previsualización del XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-30
     */
    public function xml($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // armar xml a partir de datos del dte temporal
        $xml = $DteTmp->getEnvioDte()->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible crear el XML para previsualización:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // entregar xml
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$receptor.'_'.$dte.'_'.$codigo.'.xml"');
        print $xml;
        exit;
    }

    /**
     * Método que entrega el JSON del DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-07
     */
    public function json($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // entregar xml
        $json = json_encode(json_decode($DteTmp->datos), JSON_PRETTY_PRINT);
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Length: '.strlen($json));
        header('Content-Disposition: attachement; filename="'.$receptor.'_'.$dte.'_'.$codigo.'.json"');
        print $json;
        exit;
    }

    /**
     * Acción que descarga el código binario ESCPOS del DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-04
     */
    public function escpos($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // datos por defecto y recibidos por GET
        extract($this->getQuery([
            'cotizacion' => 0,
            'compress' => false,
        ]));
        // armar xml a partir de datos del dte temporal
        $xml = $DteTmp->getEnvioDte($cotizacion ? $DteTmp->getFolio() : 0)->generar();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible crear el código ESCPOS para previsualización:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_tmps/ver/'.$receptor.'/'.$dte.'/'.$codigo);
        }
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $data = [
            'xml' => base64_encode($xml),
            'cedible' => false,
            'compress' => $compress,
        ];
        // consultar servicio web de LibreDTE
        $ApiDteEscPosClient = $Emisor->getApiClient('dte_escpos');
        if (!$ApiDteEscPosClient) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/utilidades/documentos/generar_escpos', $data);
        }
        // consultar servicio web del contribuyente
        else {
            $response = $ApiDteEscPosClient->post($ApiDteEscPosClient->url, $data);
        }
        // procesar respuesta
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            $this->redirect('/dte/dte_tmps/ver/'.$receptor.'/'.$dte.'/'.$codigo);
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
     * Método que elimina un DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function eliminar($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // eliminar
        try {
            $DteTmp->delete();
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE temporal eliminado', 'ok'
            );
            $this->redirect('/dte/dte_tmps');
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible eliminar el DTE temporal: '.$e->getMessage()
            );
            $this->redirect('/dte/dte_tmps');
        }
    }

    /**
     * Servicio web que elimina un DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-28
     */
    public function _api_eliminar_GET($receptor, $dte, $codigo, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->usuario) {
            $this->Api->send('Contribuyente no está registrado en la aplicación', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_tmps')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            $this->Api->send('No existe el DTE temporal solicitado', 404);
        }
        // eliminar
        return $DteTmp->delete();
    }

    /**
     * Acción de la API que entrega el cobro asociado al documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-06-16
     */
    public function _api_cobro_GET($receptor, $dte, $codigo, $emisor)
    {
        // verificar permisos y crear DteEmitido
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->usuario) {
            $this->Api->send('Contribuyente no está registrado en la aplicación', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_tmps')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            $this->Api->send('No existe el DTE temporal solicitado', 404);
        }
        // entregar cobro (se agrega URL)
        $Cobro = $DteTmp->getCobro();
        $links = $DteTmp->getLinks();
        $Cobro->url = !empty($links['pagar']) ? $links['pagar'] : null;
        return $this->Api->send($Cobro, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Método que actualiza un DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-16
     */
    public function actualizar($receptor, $dte, $codigo, $fecha = null, $actualizar_precios = true)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // nueva fecha de actualización
        if (!$fecha) {
            $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        }
        if (isset($_POST['actualizar_precios'])) {
            $actualizar_precios = (bool)$_POST['actualizar_precios'];
        }
        if ($DteTmp->fecha==$fecha) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE temporal ya está con fecha '.$fecha, 'warning'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // actualizar fechas del DTE temporal
        $datos = json_decode($DteTmp->datos, true);
        $FchEmis = $datos['Encabezado']['IdDoc']['FchEmis'];
        $datos['Encabezado']['IdDoc']['FchEmis'] = $fecha;
        $datos['Encabezado']['IdDoc']['FchCancel'] = false;
        if ($datos['Encabezado']['IdDoc']['FchVenc']) {
            $dias = \sowerphp\general\Utility_Date::count($datos['Encabezado']['IdDoc']['FchVenc'], $FchEmis);
            $datos['Encabezado']['IdDoc']['FchVenc'] = date('Y-m-d', strtotime($fecha)+$dias*86400);
        }
        // actualizar precios de items (siempre que esten codificados)
        if ($actualizar_precios) {
            // actualizar precios de items si es que corresponde: existe código
            // del item, existe el item, existe un precio y es diferente al que
            // ya está asignado
            $fecha_calculo = !empty($datos['Encabezado']['IdDoc']['FchVenc']) ? $datos['Encabezado']['IdDoc']['FchVenc'] : $fecha;
            $precios_actualizados = false;
            foreach ($datos['Detalle'] as &$d) {
                if (empty($d['CdgItem']['VlrCodigo'])) {
                    continue;
                }
                $Item = (new \website\Dte\Admin\Model_Itemes())->get(
                    $Emisor->rut,
                    $d['CdgItem']['VlrCodigo'],
                    !empty($d['CdgItem']['TpoCodigo']) ? $d['CdgItem']['TpoCodigo'] : null
                );
                if ($Item->exists()) {
                    $precio = $Item->getPrecio($fecha_calculo);
                    if ($precio and $d['PrcItem']!=$precio) {
                        $precios_actualizados = true;
                        $d['PrcItem'] = $precio;
                        if ($d['DescuentoPct']) {
                            $d['DescuentoMonto'] = false;
                        }
                        if ($d['RecargoPct']) {
                            $d['RecargoMonto'] = false;
                        }
                        $d['MontoItem'] = false;
                    }
                }
            }
            // si se actualizó algún precio se deben recalcular los totales
            if ($precios_actualizados) {
                $datos['Encabezado']['Totales'] = [];
                $datos = (new \sasco\LibreDTE\Sii\Dte($datos))->getDatos();
            }
        }
        // guardar nuevo dte temporal
        $DteTmp->fecha = $fecha;
        $DteTmp->total = $datos['Encabezado']['Totales']['MntTotal'];
        $DteTmp->datos = json_encode($datos);
        $DteTmp->codigo = md5($DteTmp->datos);
        try {
            $DteTmp->save();
            \sowerphp\core\Model_Datasource_Session::message(
                'Se actualizó el DTE temporal al '.$fecha, 'ok'
            );
        } catch (\Exception $e) {
             \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible actualizar el DTE temporal al '.$fecha, 'error'
            );
        }
        $this->redirect('/dte/dte_tmps');
    }

    /**
     * Acción que permite generar un vale para imprimir con la identificación
     * del DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-10-22
     */
    public function vale($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // pasar datos a la vista
        $this->layout .= '.min';
        $this->set('DteTmp', $DteTmp);
    }

    /**
     * Acción que permite crear el cobro para el DTE y enviar al formulario de pago
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-10
     */
    public function pagar($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // si no permite cobro error
        if (!$DteTmp->getTipo()->permiteCobro()) {
            \sowerphp\core\Model_Datasource_Session::message('Documento no permite cobro', 'error');
            $this->redirect(str_replace('pagar', 'ver', $this->request->request));
        }
        // obtener cobro
        $Cobro = $DteTmp->getCobro();
        if ($Cobro->pagado) {
            \sowerphp\core\Model_Datasource_Session::message('Documento ya se encuentra pagado', 'ok');
            $this->redirect(str_replace('pagar', 'ver', $this->request->request));
        }
        $this->redirect('/pagos/cobros/pagar/'.$Cobro->codigo);
    }

    /**
     * Acción que permite editar el DTE temporal
     * @todo Programar funcionalidad
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-03-31
     */
    public function editar($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // editar
        \sowerphp\core\Model_Datasource_Session::message(
            'Edición del DTE temporal aun no está disponible', 'warning'
        );
        $this->redirect(str_replace('/editar/', '/ver/', $this->request->request));
    }

    /**
     * Acción que permite editar el JSON del DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-03-31
     */
    public function editar_json($receptor, $dte, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // sólo lo puede editar el equipo de soporte
        if (!$this->Auth->User->inGroup('soporte')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No está autorizado a editar el JSON del DTE temporal', 'error'
            );
            $this->redirect(str_replace('/editar_json/', '/ver/', $this->request->request));
        }
        // verificar que el JSON sea correcto tratando de leerlo
        $datos = json_decode($_POST['datos']);
        if (!$datos) {
            \sowerphp\core\Model_Datasource_Session::message(
                'JSON es inválido, no se editó', 'error'
            );
            $this->redirect(str_replace('/editar_json/', '/ver/', $this->request->request));
        }
        // guardar JSON
        $DteTmp->datos = json_encode($datos);
        if ($DteTmp->save()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'JSON guardado', 'ok'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible guardar el nuevo JSON', 'error'
            );
        }
        $this->redirect(str_replace('/editar_json/', '/ver/', $this->request->request));
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los DTE
     * temporales
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-03
     */
    public function buscar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
        ]);
        if (isset($_POST['submit'])) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/dte/dte_tmps/buscar/'.$Emisor->rut, [
                'dte' => $_POST['dte'],
                'receptor' => $_POST['receptor'],
                'fecha_desde' => $_POST['fecha_desde'],
                'fecha_hasta' => $_POST['fecha_hasta'],
                'total_desde' => $_POST['total_desde'],
                'total_hasta' => $_POST['total_hasta'],
            ]);
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            }
            else {
                $this->set([
                    'Emisor' => $Emisor,
                    'documentos' => $response['body'],
                ]);
            }
        }
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * DTEs temporales
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-03
     */
    public function _api_buscar_POST($emisor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_tmps/buscar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // buscar documentos
        $this->Api->send($Emisor->getDocumentosTemporales($this->Api->data, true), 200, JSON_PRETTY_PRINT);
    }

}
