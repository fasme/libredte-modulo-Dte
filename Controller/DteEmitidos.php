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
 * Controlador de dte emitidos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-02-23
 */
class Controller_DteEmitidos extends \Controller_App
{

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-23
     */
    public function beforeFilter()
    {
        $this->Auth->allow('pdf', 'xml', 'consultar');
        parent::beforeFilter();
    }

    /**
     * Acción que permite mostrar los documentos emitidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-06
     */
    public function listar($pagina = 1)
    {
        if (!is_numeric($pagina)) {
            $this->redirect('/dte/'.$this->request->params['controller'].'/listar');
        }
        $Emisor = $this->getContribuyente();
        $filtros = [];
        if (isset($_GET['search'])) {
            foreach (explode(',', $_GET['search']) as $filtro) {
                list($var, $val) = explode(':', $filtro);
                $filtros[$var] = $val;
            }
        }
        $searchUrl = isset($_GET['search'])?('?search='.$_GET['search']):'';
        $paginas = 1;
        try {
            $documentos_total = $Emisor->countDocumentosEmitidos($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = \sowerphp\core\Configure::read('app.registers_per_page');
                $filtros['offset'] = ($pagina-1)*$filtros['limit'];
                $paginas = $documentos_total ? ceil($documentos_total/$filtros['limit']) : 0;
                if ($pagina != 1 && $pagina > $paginas) {
                    $this->redirect('/dte/'.$this->request->params['controller'].'/listar'.$searchUrl);
                }
            }
            $documentos = $Emisor->getDocumentosEmitidos($filtros);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Error al recuperar los documentos:<br/>'.$e->getMessage(), 'error'
            );
            $documentos_total = 0;
            $documentos = [];
        }
        $this->set([
            'Emisor' => $Emisor,
            'documentos' => $documentos,
            'documentos_total' => $documentos_total,
            'paginas' => $paginas,
            'pagina' => $pagina,
            'search' => $filtros,
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
            'sucursales' => $Emisor->getSucursales(),
            'sucursal' => -1, // TODO: sucursal por defecto
            'usuarios' => $Emisor->getListUsuarios(),
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * Acción que permite eliminar un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-03
     */
    public function eliminar($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_emitidos/eliminar/'.$dte.'/'.$folio.'/'.$Emisor->rut);
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
        }
        else if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
        }
        else {
            \sowerphp\core\Model_Datasource_Session::message('Se eliminó el DTE', 'ok');
        }
        $this->redirect('/dte/dte_emitidos/listar');
    }

    /**
     * Acción que muestra la página de un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-09
     */
    public function ver($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // asignar variables para la vista
        $this->set([
            'Emisor' => $Emisor,
            'DteEmitido' => $DteEmitido,
            'Receptor' => $DteEmitido->getReceptor(),
            'emails' => $DteEmitido->getEmails(),
            'referencias' => $DteEmitido->getReferencias(),
            'referencia' => $DteEmitido->getPropuestaReferencia(),
            'enviar_sii' => !(in_array($DteEmitido->dte, [39, 41])),
            'Cobro' => (\sowerphp\core\Module::loaded('Pagos') and $DteEmitido->getTipo()->operacion=='S') ? $DteEmitido->getCobro(false) : false,
        ]);
    }

    /**
     * Acción que envía el DTE al SII si este no ha sido envíado (no tiene
     * track_id) o bien si se solicita reenviar (tiene track id) y está
     * rechazado (no se permite reenviar documentos que estén aceptados o
     * aceptados con reparos (flag generar no tendrá efecto si no se cumple esto)
     * @param dte Tipo de DTE
     * @param folio Folio del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-05
     */
    public function enviar_sii($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_emitidos/enviar_sii/'.$dte.'/'.$folio.'/'.$Emisor->rut);
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
        }
        else if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
        }
        else {
            \sowerphp\core\Model_Datasource_Session::message('Se envió el DTE al SII', 'ok');
        }
        $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
    }

    /**
     * Acción que solicita se envíe una nueva revisión del DTE al email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-11
     */
    public function solicitar_revision($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // solicitar revision
        try {
            $estado = $DteEmitido->solicitarRevision($this->Auth->User->id);
            if ($estado===false) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible solicitar una nueva revisión del DTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
            } else if ((int)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:ESTADO')[0]) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible solicitar una nueva revisión del DTE: '.$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:GLOSA')[0], 'error'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se solicitó nueva revisión del DTE, verificar estado en unos segundos', 'ok'
                );
            }
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
        }
        // redireccionar
        $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
    }

    /**
     * Acción que actualiza el estado del envío del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-18
     */
    public function actualizar_estado($dte, $folio, $usarWebservice = null)
    {
        $Emisor = $this->getContribuyente();
        if ($usarWebservice===null)
            $usarWebservice = $Emisor->config_sii_estado_dte_webservice;
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_emitidos/actualizar_estado/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?usarWebservice='.(int)$usarWebservice);
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
        }
        else if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
        }
        else {
            \sowerphp\core\Model_Datasource_Session::message('Se actualizó el estado del DTE', 'ok');
        }
        $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
    }

    /**
     * Acción que descarga el PDF del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-23
     */
    public function pdf($dte, $folio, $cedible = false, $emisor = null, $fecha = null, $total = null)
    {
        // usar emisor de la sesión
        if (!$emisor) {
            $Emisor = $this->getContribuyente();
        }
        // usar emisor como parámetro
        else {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($emisor);
            if (!$Emisor->exists() or !$Emisor->usuario) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Emisor no está registrado en la aplicación', 'error'
                );
                $this->redirect('/dte/dte_emitidos/consultar');
            }
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si se está pidiendo con un emisor por parámetro se debe verificar
        // fecha de emisión y monto total del dte
        if ($emisor and ($DteEmitido->fecha!=$fecha or $DteEmitido->total!=$total)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE existe, pero fecha y/o monto no coinciden con los registrados', 'error'
            );
            $this->redirect('/dte/dte_emitidos/consultar');
        }
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $webVerificacion = $this->request->url.'/boletas';
        $data = [
            'xml' => $DteEmitido->xml,
            'cedible' => isset($_POST['copias_cedibles']) ? (int)(bool)$_POST['copias_cedibles'] : $cedible,
            'papelContinuo' => isset($_POST['papelContinuo']) ? $_POST['papelContinuo'] : $Emisor->config_pdf_dte_papel,
            'compress' => false,
            'webVerificacion' => in_array($DteEmitido->dte, [39,41]) ? $webVerificacion : false,
            'copias_tributarias' => isset($_POST['copias_tributarias']) ? (int)$_POST['copias_tributarias'] : 1,
            'copias_cedibles' => isset($_POST['copias_cedibles']) ? (int)$_POST['copias_cedibles'] : $cedible,
        ];
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User ? $this->Auth->User->hash : \sowerphp\core\Configure::read('api.default.token'));
        $response = $rest->post($this->request->url.'/api/utilidades/documentos/generar_pdf', $data);
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            $this->redirect('/dte/dte_emitidos/listar');
        }
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            $this->redirect('/dte/dte_emitidos/listar');
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
     * Acción que descarga el XML del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-23
     */
    public function xml($dte, $folio, $emisor = null, $fecha = null, $total = null)
    {
        // usar emisor de la sesión
        if (!$emisor) {
            $Emisor = $this->getContribuyente();
        }
        // usar emisor como parámetro
        else {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($emisor);
            if (!$Emisor->exists() or !$Emisor->usuario) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Emisor no está registrado en la aplicación', 'error'
                );
                $this->redirect('/dte/dte_emitidos/consultar');
            }
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si se está pidiendo con un emisor por parámetro se debe verificar
        // fecha de emisión y monto total del dte
        if ($emisor and ($DteEmitido->fecha!=$fecha or $DteEmitido->total!=$total)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE existe, pero fecha y/o monto no coinciden con los registrados', 'error'
            );
            $this->redirect('/dte/dte_emitidos/consultar');
        }
        // entregar XML
        $file = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml';
        $xml = base64_decode($DteEmitido->xml);
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que descarga el JSON del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-19
     */
    public function json($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // entregar JSON
        $file = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.json';
        $datos = $DteEmitido->getDatos();
        unset($datos['@attributes'], $datos['TED'], $datos['TmstFirma']);
        $json = json_encode($datos, JSON_PRETTY_PRINT);
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Length: '.strlen($json));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        echo $json;
        exit;
    }

    /**
     * Acción que envía por email el PDF y el XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-04-25
     */
    public function enviar_email($dte, $folio)
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
                $this->request->url.'/api/dte/dte_emitidos/enviar_email/'.$dte.'/'.$folio.'/'.$Emisor->rut,
                [
                    'emails' => $emails,
                    'asunto' => $_POST['asunto'],
                    'mensaje' => $_POST['mensaje'],
                    'pdf' => 1,
                    'cedible' => (int)isset($_POST['cedible']),
                    'papelContinuo' => $Emisor->config_pdf_dte_papel,
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
                    'Se envió el DTE a: '.implode(', ', $emails), 'ok'
                );
            }
        }
        $this->redirect(str_replace('enviar_email', 'ver', $this->request->request).'#email');
    }

    /**
     * Acción que permite ceder el documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-10
     */
    public function ceder($dte, $folio)
    {
        if (!isset($_POST['submit'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debe enviar el formulario para poder realizar la cesión', 'error'
            );
            $this->redirect(str_replace('ceder', 'ver', $this->request->request).'#cesion');
        }
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que sea documento cedible
        if (!$DteEmitido->getTipo()->cedible) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento no es cedible', 'error'
            );
            $this->redirect(str_replace('ceder', 'ver', $this->request->request));
        }
        // verificar que no esté cedido (enviado al SII)
        if ($DteEmitido->cesion_track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento ya fue enviado al SII para cesión', 'error'
            );
            $this->redirect(str_replace('ceder', 'ver', $this->request->request).'#cesion');
        }
        // objeto de firma electrónica
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        // armar el DTE cedido
        $DteCedido = new \sasco\LibreDTE\Sii\Factoring\DteCedido($DteEmitido->getDte());
        $DteCedido->firmar($Firma);
        // crear declaración de cesión
        $Cesion = new \sasco\LibreDTE\Sii\Factoring\Cesion($DteCedido);
        $Cesion->setCesionario([
            'RUT' => str_replace('.', '', $_POST['cesionario_rut']),
            'RazonSocial' => $_POST['cesionario_razon_social'],
            'Direccion' => $_POST['cesionario_direccion'],
            'eMail' => $_POST['cesionario_email'],
        ]);
        $Cesion->setCedente([
            'eMail' => $_POST['cedente_email'],
            'RUTAutorizado' => [
                'RUT' => $Firma->getID(),
                'Nombre' => $Firma->getName(),
            ],
        ]);
        $Cesion->firmar($Firma);
        // crear AEC
        $AEC = new \sasco\LibreDTE\Sii\Factoring\Aec();
        $AEC->setFirma($Firma);
        $AEC->agregarDteCedido($DteCedido);
        $AEC->agregarCesion($Cesion);
        // enviar el XML de la cesión al SII
        $xml = $AEC->generar();
        $track_id = $AEC->enviar();
        if ($track_id) {
            $DteEmitido->cesion_xml = base64_encode($xml);
            $DteEmitido->cesion_track_id = $track_id;
            $DteEmitido->save();
            \sowerphp\core\Model_Datasource_Session::message('Archivo de cesión enviado al SII con track id '.$track_id, 'ok');
        } else {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
        }
        $this->redirect(str_replace('ceder', 'ver', $this->request->request).'#cesion');
    }

    /**
     * Acción que descarga el XML de la cesión del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-10
     */
    public function cesion_xml($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que exista XML
        if (!$DteEmitido->cesion_xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE no tiene XML de AEC asociado', 'error'
            );
            $this->redirect(str_replace('cesion_xml', 'ver', $this->request->request).'#cesion');
        }
        // entregar XML
        $file = 'cesion_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml';
        $xml = base64_decode($DteEmitido->cesion_xml);
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que permite marcar el IVA como fuera de plazo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-01
     */
    public function avanzado_iva_fuera_plazo($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que sea documento que se puede marcar como fuera de plazo
        if ($DteEmitido->dte!=61) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Sólo es posible marcar IVA fuera de plazo en notas de crédito', 'error'
            );
            $this->redirect(str_replace('avanzado_iva_fuera_plazo', 'ver', $this->request->request));
        }
        // marcar IVA como fuera de plazo
        $DteEmitido->iva_fuera_plazo = (int)$_POST['iva_fuera_plazo'];
        $DteEmitido->save();
        $msg = $DteEmitido->iva_fuera_plazo ? 'IVA marcado como fuera de plazo (no recuperable)' : 'IVA marcado como recuperable';
        \sowerphp\core\Model_Datasource_Session::message($msg, 'ok');
        $this->redirect(str_replace('avanzado_iva_fuera_plazo', 'ver', $this->request->request).'#avanzado');
    }
    /**
     * Acción que permite anular un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-15
     */
    public function avanzado_anular($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que sea documento que se puede anular
        if ($DteEmitido->dte!=52) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Sólo es posible anular guias de despacho con la opción avanzada', 'error'
            );
            $this->redirect(str_replace('avanzado_anular', 'ver', $this->request->request));
        }
        // cambiar estado anulado del documento
        $DteEmitido->anulado = (int)$_POST['anulado'];
        $DteEmitido->save();
        $msg = $DteEmitido->anulado ? 'DTE anulado' : 'DTE ya no está anulado';
        \sowerphp\core\Model_Datasource_Session::message($msg, 'ok');
        $this->redirect(str_replace('avanzado_anular', 'ver', $this->request->request).'#avanzado');
    }

    /**
     * Acción que permite actualizar el tipo de cambio de un documento de exportación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-18
     */
    public function avanzado_tipo_cambio($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que sea de exportación
        if (!$DteEmitido->getDte()->esExportacion()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento no es de exportación', 'error'
            );
            $this->redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->request).'#avanzado');
        }
        // sólo administrador puede cambiar el tipo de cambio
        if ($Emisor->usuario != $this->Auth->User->id) {
            \sowerphp\core\Model_Datasource_Session::message('Sólo el administrador de la empresa puede cambiar el tipo de cambio', 'error');
            $this->redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->request));
        }
        // cambiar monto total
        $DteEmitido->exento = $DteEmitido->total = abs(round($DteEmitido->getDte()->getMontoTotal() * (float)$_POST['tipo_cambio']));
        $DteEmitido->save();
        \sowerphp\core\Model_Datasource_Session::message('Monto en pesos (CLP) del DTE actualizado', 'ok');
        $this->redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->request));
    }

    /**
     * Acción que permite actualizar el track_id del DteEmitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-16
     */
    public function avanzado_track_id($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // sólo administrador puede cambiar track id
        if ($DteEmitido->usuario != $this->Auth->User->id  and (!$Emisor->config_app_soporte or !$this->Auth->User->inGroup(['soporte']))) {
            \sowerphp\core\Model_Datasource_Session::message('Sólo el administrador de la empresa puede cambiar el Track ID', 'error');
            $this->redirect(str_replace('avanzado_track_id', 'ver', $this->request->request));
        }
        // cambiar track id
        $DteEmitido->track_id = (int)$_POST['track_id'];
        $DteEmitido->revision_estado = null;
        $DteEmitido->revision_detalle = null;
        $DteEmitido->save();
        \sowerphp\core\Model_Datasource_Session::message('Track ID actualizado', 'ok');
        $this->redirect(str_replace('avanzado_track_id', 'ver', $this->request->request).'#avanzado');
    }

    /**
     * Acción que permite crear el cobro para el DTE y enviar al formulario de pago
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-10
     */
    public function pagar($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si no permite cobro error
        if (!$DteEmitido->getTipo()->permiteCobro()) {
            \sowerphp\core\Model_Datasource_Session::message('Documento no permite cobro', 'error');
            $this->redirect(str_replace('pagar', 'ver', $this->request->request));
        }
        // obtener cobro
        $Cobro = $DteEmitido->getCobro();
        if ($Cobro->pagado) {
            \sowerphp\core\Model_Datasource_Session::message('Documento ya se encuentra pagado', 'ok');
            $this->redirect(str_replace('pagar', 'ver', $this->request->request));
        }
        $this->redirect('/pagos/cobros/pagar/'.$Cobro->codigo);
    }

    /**
     * Acción que permite cargar un archivo XML como DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-01
     */
    public function cargar_xml()
    {
        if (isset($_POST['submit']) and !$_FILES['xml']['error']) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post(
                $this->request->url.'/api/dte/dte_emitidos/cargar_xml?track_id='.(int)$_POST['track_id'],
                json_encode(base64_encode(file_get_contents($_FILES['xml']['tmp_name'])))
            );
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            }
            else {
                $dte = $response['body'];
                \sowerphp\core\Model_Datasource_Session::message('XML del DTE T'.$dte['dte'].'F'.$dte['folio'].' fue cargado correctamente', 'ok');
            }
        }
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los DTE
     * emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-28
     */
    public function buscar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
        ]);
        if (isset($_POST['submit'])) {
            $xml = [];
            if (!empty($_POST['xml_nodo'])) {
                $n_xml = count($_POST['xml_nodo']);
                for ($i=0; $i<$n_xml; $i++) {
                    if (!empty($_POST['xml_nodo'][$i]) and !empty($_POST['xml_valor'][$i])) {
                        $xml[$_POST['xml_nodo'][$i]] = $_POST['xml_valor'][$i];
                    }
                }
            }
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/dte/dte_emitidos/buscar/'.$Emisor->rut, json_encode([
                'dte' => $_POST['dte'],
                'receptor' => $_POST['receptor'],
                'fecha_desde' => $_POST['fecha_desde'],
                'fecha_hasta' => $_POST['fecha_hasta'],
                'total_desde' => $_POST['total_desde'],
                'total_hasta' => $_POST['total_hasta'],
                'xml' => $xml,
            ]));
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
     * Acción de la API que permite obtener la información de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-02
     */
    public function _api_info_GET($dte, $folio, $emisor)
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
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        $DteEmitido->xml = false;
        $this->Api->send($DteEmitido, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite obtener el PDF de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-11-20
     */
    public function _api_pdf_GET($dte, $folio, $emisor)
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
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        // datos por defecto
        extract($this->Api->getQuery([
            'cedible' => $Emisor->config_pdf_dte_cedible,
            'papelContinuo' => $Emisor->config_pdf_dte_papel,
            'compress' => false,
            'copias_tributarias' => 1,
            'copias_cedibles' => $Emisor->config_pdf_dte_cedible,
        ]));
        // armar datos con archivo XML y flag para indicar si es cedible o no
        $webVerificacion = $this->request->url.'/boletas';
        $data = [
            'xml' => $DteEmitido->xml,
            'cedible' => $cedible,
            'papelContinuo' => $papelContinuo,
            'compress' => $compress,
            'webVerificacion' => in_array($DteEmitido->dte, [39,41]) ? $webVerificacion : false,
            'copias_tributarias' => $copias_tributarias,
            'copias_cedibles' => $copias_cedibles,
        ];
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($User->hash);
        $response = $rest->post($this->request->url.'/api/utilidades/documentos/generar_pdf', $data);
        if ($response===false) {
            $this->Api->send(implode('<br/>', $rest->getErrors(), 500));
        }
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
     * Acción de la API que permite obtener el XML de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-02
     */
    public function _api_xml_GET($dte, $folio, $emisor)
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
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T.'.$dte.'F'.$folio, 404);
        }
        return $DteEmitido->xml;
    }

    /**
     * Acción de la API que permite obtener el timbre de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-02
     */
    public function _api_ted_GET($dte, $folio, $emisor)
    {
        extract($this->Api->getQuery(['formato'=>'png', 'ecl'=>5]));
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
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists())
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(base64_decode($DteEmitido->xml));
        $ted = $EnvioDte->getDocumentos()[0]->getTED();
        if ($formato == 'xml') {
            return base64_encode($ted);
        }
        else if ($formato == 'png') {
            $pdf417 = new \TCPDF2DBarcode($ted, 'PDF417,,'.$ecl);
            $pdf417->getBarcodePNG(4, 4, [0,0,0]);
        }
    }

    /**
     * Acción de la API que permite consultar el estado del envío del DTE al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-02
     */
    public function _api_estado_GET($dte, $folio, $emisor)
    {
        extract($this->Api->getQuery(['avanzado'=>false]));
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
        $Firma = $Emisor->getFirma($User->id);
        if (!$Firma) {
            $this->Api->send('No existe firma asociada', 506);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T.'.$dte.'F'.$folio, 404);
        }
        \sasco\LibreDTE\Sii::setAmbiente($Emisor->config_ambiente_en_certificacion);
        return $avanzado ? $DteEmitido->getDte()->getEstadoAvanzado($Firma) : $DteEmitido->getDte()->getEstado($Firma);
    }

    /**
     * Acción de la API que permite actualizar el estado de envio del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-18
     */
    public function _api_actualizar_estado_GET($dte, $folio, $emisor)
    {
        extract($this->Api->getQuery(['usarWebservice'=>true]));
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
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists())
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        // actualizar estado
        try {
            $this->Api->send($DteEmitido->actualizarEstado($User->id, $usarWebservice), 200, JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), 500);
        }
    }

    /**
     * Recurso de la API que envía el DTE al SII si este no ha sido envíado (no
     * tiene track_id) o bien si se solicita reenviar (tiene track id) y está
     * rechazado (no se permite reenviar documentos que estén aceptados o
     * aceptados con reparos (flag generar no tendrá efecto si no se cumple esto)
     * @param dte Tipo de DTE
     * @param folio Folio del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-05
     */
    public function _api_enviar_sii_GET($dte, $folio, $emisor)
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
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        // si es boleta no se puede enviar
        if (in_array($dte, [39, 41])) {
            $this->Api->send('Documento de tipo '.$dte.' no se envía al SII', 400);
        }
        // enviar DTE (si no se puede enviar se generará excepción)
        try {
            $DteEmitido->enviar($User->id);
            return $DteEmitido;
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), 502);
        }
    }

    /**
     * Acción de la API que permite enviar el DTE emitido por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function _api_enviar_email_POST($dte, $folio, $emisor)
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
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists())
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        // parametros por defecto
        $data = array_merge([
            'emails' => $DteEmitido->getReceptor()->config_email_intercambio_user,
            'asunto' => null,
            'mensaje' => null,
            'pdf' => false,
            'cedible' => false,
            'papelContinuo' => $Emisor->config_pdf_dte_papel,
        ], $this->Api->data);
        // enviar por correo
        try {
            $DteEmitido->email($data['emails'], $data['asunto'], $data['mensaje'], $data['pdf'], $data['cedible'], $data['papelContinuo']);
            return true;
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), 502);
        }
    }

    /**
     * Recurso de la API que permite eliminar un DTE rechazado o no enviado al
     * SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-03
     */
    public function _api_eliminar_GET($dte, $folio, $emisor)
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
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        // si el DTE no está rechazado no se puede eliminar
        if ($DteEmitido->track_id and $DteEmitido->track_id!=-1 and $DteEmitido->getEstado()!='R') {
            $this->Api->send('No es posible eliminar el DTE ya que no está rechazado', 400);
        }
        // eliminar DTE
        if (!$DteEmitido->delete()) {
            $this->Api->send('No fue posible eliminar el DTE', 500);
        }
        return true;
    }

    /**
     * Acción de la API que permite cargar el XML de un DTE como documento
     * emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-09
     */
    public function _api_cargar_xml_POST()
    {
        // verificar usuario autenticado
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        // cargar XML
        if (empty($this->Api->data)) {
            $this->Api->send('Debe enviar el XML del DTE emitido', 400);
        }
        if ($this->Api->data[0]!='"') {
            $this->Api->data = '"'.$this->Api->data.'"';
        }
        $xml = base64_decode(json_decode($this->Api->data));
        if (!$xml) {
            $this->Api->send('No fue posible recibir el XML enviado', 400);
        }
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        if (!$EnvioDte->loadXML($xml)) {
            $this->Api->send('No fue posible cargar el XML enviado', 400);
        }
        $Documentos = $EnvioDte->getDocumentos();
        $n_docs = count($Documentos);
        if ($n_docs!=1) {
            $this->Api->send('Sólo puede cargar XML que contengan un DTE, envío '.num($n_docs), 400);
        }
        $Caratula = $EnvioDte->getCaratula();
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Emisor = new Model_Contribuyente($Caratula['RutEmisor']);
        $certificacion = !(bool)$Caratula['NroResol'];
        if (!$Emisor->exists())
            $this->Api->send('Emisor no existe', 404);
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/cargar_xml')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // verificar RUT carátula con RUT documento
        $datos = $Documentos[0]->getDatos();
        if ($Caratula['RutReceptor']!=$datos['Encabezado']['Receptor']['RUTRecep']) {
            $this->Api->send('RUT del receptor en la carátula no coincide con el RUT del receptor del documento', 400);
        }
        // si el receptor no existe, se crea con los datos del XML
        $Receptor = new Model_Contribuyente($datos['Encabezado']['Receptor']['RUTRecep']);
        if (!$Receptor->exists()) {
            $Receptor->dv = explode('-', $datos['Encabezado']['Receptor']['RUTRecep'])[1];
            $Receptor->razon_social = $Receptor->getRUT();
            if (!empty($datos['Encabezado']['Receptor']['RznSocRecep'])) {
                $Receptor->razon_social = $datos['Encabezado']['Receptor']['RznSocRecep'];
            }
            if (!empty($datos['Encabezado']['Receptor']['GiroRecep'])) {
                $Receptor->giro = $datos['Encabezado']['Receptor']['GiroRecep'];
            }
            if (!empty($datos['Encabezado']['Receptor']['Contacto'])) {
                $Receptor->telefono = $datos['Encabezado']['Receptor']['Contacto'];
            }
            if (!empty($datos['Encabezado']['Receptor']['CorreoRecep'])) {
                $Receptor->email = $datos['Encabezado']['Receptor']['CorreoRecep'];
            }
            if (!empty($datos['Encabezado']['Receptor']['DirRecep'])) {
                $Receptor->direccion = $datos['Encabezado']['Receptor']['DirRecep'];
            }
            if (!empty($datos['Encabezado']['Receptor']['CmnaRecep'])) {
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['Encabezado']['Receptor']['CmnaRecep']);
                if ($comuna) {
                    $Receptor->comuna = $comuna;
                }
            }
            $Receptor->modificado = date('Y-m-d H:i:s');
            try {
                $Receptor->save();
            } catch (\Exception $e) {
                $this->Api->send('Receptor no pudo ser creado: '.$e->getMessage(), 507);
            }
        }
        // crear Objeto del DteEmitido y verificar si ya existe
        $Dte = $Documentos[0];
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $Dte->getTipo(), $Dte->getFolio(), (int)$certificacion);
        if ($DteEmitido->exists()) {
            $this->Api->send('XML enviado ya está registrado', 409);
        }
        // guardar DteEmitido
        $r = $Dte->getResumen();
        $cols = ['tasa'=>'TasaImp', 'fecha'=>'FchDoc', 'receptor'=>'RUTDoc', 'exento'=>'MntExe', 'neto'=>'MntNeto', 'iva'=>'MntIVA', 'total'=>'MntTotal'];
        foreach ($cols as $attr => $col) {
            if ($r[$col]!==false)
                $DteEmitido->$attr = $r[$col];
        }
        $DteEmitido->receptor = substr($DteEmitido->receptor, 0, -2);
        $DteEmitido->xml = base64_encode($xml);
        $DteEmitido->usuario = $User->id;
        $DteEmitido->track_id = !empty($_GET['track_id']) ? (int)$_GET['track_id'] : -1;
        $DteEmitido->save();
        if ($DteEmitido->track_id!=-1) {
            $DteEmitido->actualizarEstado();
        }
        $DteEmitido->xml = null;
        $this->Api->send($DteEmitido, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * DTEs emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-07
     */
    public function _api_buscar_POST($emisor)
    {
        // verificar usuario autenticado
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists())
            $this->Api->send('Emisor no existe', 404);
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/buscar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // buscar documentos
        $this->Api->send($Emisor->getDocumentosEmitidos(json_decode($this->Api->data, true)), 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción que permite buscar y consultar un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-29
     */
    public function consultar($dte = null)
    {
        // asignar variables para el formulario
        $this->set([
            'dtes' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(),
            'dte' => isset($_POST['dte']) ? $_POST['dte'] : $dte,
        ]);
        // si se solicitó un documento se busca
        if (isset($_POST['submit'])) {
            $r = $this->consume('/api/dte/dte_emitidos/consultar', $_POST);
            if ($r['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message(
                    str_replace("\n", '<br/>', $r['body']), 'error'
                );
                return;
            }
            // asignar DTE a la vista
            $this->set('DteEmitido', (new Model_DteEmitido())->set($r['body']));
        }
    }

    /**
     * Función de la API para consultar por un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-04-11
     */
    public function _api_consultar_POST()
    {
        extract($this->Api->getQuery([
            'getXML' => false,
        ]));
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar que se hayan pasado los índices básicos
        foreach (['emisor', 'dte', 'folio', 'fecha', 'total'] as $key) {
            if (!isset($this->Api->data[$key]))
                $this->Api->send('Falta índice/variable '.$key.' por POST', 400);
        }
        // verificar si el emisor existe
        $Emisor = new Model_Contribuyente($this->Api->data['emisor']);
        if (!$Emisor->exists() or !$Emisor->usuario) {
            $this->Api->send('Emisor no está registrado en la aplicación', 404);
        }
        // buscar si existe el DTE en el ambiente que el emisor esté usando
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $this->Api->data['dte'], $this->Api->data['folio'], (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send($Emisor->razon_social.' no tiene emitido el DTE solicitado en el ambiente de '.$Emisor->getAmbiente(), 404);
        }
        // verificar que coincida fecha de emisión y monto total del DTE
        if ($DteEmitido->fecha!=$this->Api->data['fecha'] or $DteEmitido->total!=$this->Api->data['total']) {
            $this->Api->send('DTE existe, pero fecha y/o monto no coinciden con los registrados', 409);
        }
        // quitar XML si no se pidió explícitamente
        if (!$getXML)
            $DteEmitido->xml = false;
        // enviar DteEmitido
        return $DteEmitido;
    }

}
