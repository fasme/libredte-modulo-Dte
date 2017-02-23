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
 * @version 2016-12-13
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
     * @version 2016-06-13
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $DteTmps = new Model_DteTmps();
        $DteTmps->setWhereStatement(['emisor = :rut'], [':rut'=>$Emisor->rut]);
        $DteTmps->setOrderByStatement('fecha DESC', 'receptor');
        $this->set([
            'Emisor' => $Emisor,
            'dtes' => $DteTmps->getObjects(),
        ]);
    }

    /**
     * Acción que muestra la página del documento temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-14
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
            'Emisor' => $Emisor,
            'Receptor' => $DteTmp->getReceptor(),
            'DteTmp' => $DteTmp,
            'emails' => $DteTmp->getEmails(),
        ]);
    }

    /**
     * Método que genera la cotización en PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-09
     */
    public function cotizacion($receptor, $dte, $codigo, $emisor = null)
    {
        $Emisor = $emisor===null ? $this->getContribuyente() : new Model_Contribuyente($emisor);
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        $datos = json_decode($DteTmp->datos, true);
        $datos['Encabezado']['IdDoc']['TipoDTE'] = 0;
        $datos['Encabezado']['IdDoc']['Folio'] = $DteTmp->getFolio();
        // generar PDF
        $pdf = new \sasco\LibreDTE\Sii\PDF\Dte();
        $pdf->setFooterText(\sowerphp\core\Configure::read('dte.pdf.footer'));
        $logo = DIR_STATIC.'/contribuyentes/'.$Emisor->rut.'/logo.png';
        if (is_readable($logo)) {
            $pdf->setLogo($logo);
        }
        $pdf->agregar($datos);
        $file = 'cotizacion_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$DteTmp->getFolio().'.pdf';
        $pdf->Output($file, 'D');
        exit;
    }

    /**
     * Método que genera la previsualización del PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-08
     */
    public function pdf($receptor, $dte, $codigo, $disposition = 'attachment')
    {
        $Emisor = $this->getContribuyente();
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_tmps/pdf/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$Emisor->rut);
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
        header('Content-Disposition: '.($disposition=='inline'?'inline':$response['header']['Content-Disposition']));
        echo $response['body'];
        exit;
    }

    /**
     * Acción que envía por email el PDF de la cotización del DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-14
     */
    public function enviar_email($receptor, $dte, $codigo)
    {
        if (isset($_POST['submit'])) {
            $Emisor = $this->getContribuyente();
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post(
                $this->request->url.'/api/dte/dte_tmps/enviar_email/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$Emisor->rut,
                [
                    'emails' => $_POST['emails'],
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
                    'Se envió el PDF a: '.implode(', ', $_POST['emails']), 'ok'
                );
            }
        }
        $this->redirect(str_replace('enviar_email', 'ver', $this->request->request).'#email');
    }

    /**
     * Acción de la API que permite enviar el DTE temporal por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-14
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
            $this->Api->send($e->getMessage(), 502);
        }
    }

    /**
     * Recurso de la API que genera la previsualización del PDF del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-09
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
        // armar xml a partir de datos del dte temporal
        $xml = $DteTmp->getEnvioDte()->generar();
        if (!$xml) {
            $this->Api->send(
                'No fue posible crear el PDF para previsualización:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 507
            );
        }
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $data = [
            'xml' => base64_encode($xml),
            'cedible' => false,
            'papelContinuo' => $Emisor->config_pdf_dte_papel,
            'compress' => false,
        ];
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($User->hash);
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
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE temporal solicitado', 'error'
            );
            $this->redirect('/dte/dte_tmps');
        }
        // eliminar
        return $DteTmp->delete();
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

}
