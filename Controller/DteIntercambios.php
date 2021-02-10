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
 * @version 2018-05-19
 */
class Controller_DteIntercambios extends \Controller_App
{

    /**
     * Acción para mostrar la bandeja de intercambio de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-04-05
     */
    public function listar($p = 1, $soloPendientes = false)
    {
        $Emisor = $this->getContribuyente();
        $filter = [
            'soloPendientes' => $soloPendientes,
            'p' => $p,
        ];
        if (!empty($_GET['search'])) {
            $search = explode(',', $_GET['search']);
            foreach ($search as $s) {
                list($var, $val) = explode(':', $s);
                $filter[$var] = $val;
            }
            $intercambios = $Emisor->getIntercambios($filter);
            $n_intercambios = count($intercambios);
        } else {
            $intercambios = $Emisor->getIntercambios($filter);
            $n_intercambios = (new Model_DteIntercambios())->setWhereStatement(['receptor = :receptor', 'certificacion = :certificacion'], [':receptor'=>$Emisor->rut, ':certificacion'=>$Emisor->enCertificacion()])->count();
        }
        $this->set([
            'Emisor' => $Emisor,
            'intercambios' => $intercambios,
            'n_intercambios' => $n_intercambios,
            'pages' => ceil($n_intercambios / \sowerphp\core\Configure::read('app.registers_per_page')),
            'p' => $p,
            'soloPendientes' => $soloPendientes,
        ]);
    }

    /**
     * Acción para descargar los intercambios pendientes de procesar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-18
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
            if (is_array($i['documentos'])) {
                $i['documentos'] = implode("\n", $i['documentos']);
            }
            if (is_array($i['totales'])) {
                $i['totales'] = implode("\n", $i['totales']);
            }
        }
        array_unshift($pendientes, array_keys($pendientes[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($pendientes);
        $this->response->sendContent($csv, $Emisor->rut.'_intercambios_pendientes_'.date('Ymd').'.csv');
    }

    /**
     * Acción que muestra la página de un intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-08-29
     */
    public function ver($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE intercambiado
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, $Emisor->enCertificacion());
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
     * @version 2018-05-21
     */
    public function eliminar($codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE intercambiado
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, $Emisor->enCertificacion());
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // verificar que el intercambio no esté en uso en los documentos recibidos
        if ($DteIntercambio->recibido()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'El intercambio tiene a lo menos un DTE recibido asociado, no se puede eliminar', 'error'
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
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, $Emisor->enCertificacion());
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
     * @version 2020-04-17
     */
    public function actualizar($dias = 7)
    {
        $Emisor = $this->getContribuyente();
        try {
            $resultado = $Emisor->actualizarBandejaIntercambio($dias);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                $e->getMessage(), ($e->getCode()==500 ? 'error' : 'info')
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        extract($resultado);
        if ($n_uids>1) {
            $encontrados = 'Se encontraron '.num($n_uids).' correos';
        } else {
            $encontrados = 'Se encontró '.num($n_uids).' correo';
        }
        \sowerphp\core\Model_Datasource_Session::message(
            $encontrados.': EnvioDTE='.num($n_EnvioDTE).',  EnvioRecibos='.num($n_EnvioRecibos).', RecepcionEnvio='.num($n_RecepcionEnvio).', ResultadoDTE='.num($n_ResultadoDTE).' y Omitidos='.num($omitidos), 'ok'
        );
        if (!empty($errores)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se encontraron algunos problemas al procesar ciertos correos:<br/>- '.implode('<br/>- ',$errores), 'warning'
            );
        }
        $this->redirect('/dte/dte_intercambios/listar');
    }

    /**
     * Recurso para mostrar el PDF de un EnvioDTE de un intercambio de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-27
     */
    public function _api_pdf_GET($codigo, $contribuyente, $cedible = false, $emisor = null, $dte = null, $folio = null)
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear contribuyente
        $Receptor = new Model_Contribuyente($contribuyente);
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_intercambios/pdf')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener DTE intercambiado
        $DteIntercambio = new Model_DteIntercambio($Receptor->rut, $codigo, $Receptor->enCertificacion());
        if (!$DteIntercambio->exists()) {
            $this->Api->send('No existe el intercambio solicitado', 404);
        }
        // obtener XML que se debe usar
        if ($DteIntercambio->documentos>1 and $emisor and $dte and $folio) {
            $Documento = $DteIntercambio->getDocumento($emisor, $dte, $folio);
            if (!$Documento) {
                $this->Api->send('No existe el DTE T'.$dte.'F'.$folio.' del RUT '.$emisor.' en el intercambio N° '.$codigo, 404);
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
        // consultar servicio web de LibreDTE
        $ApiDtePdfClient = $DteIntercambio->getEmisor()->getApiClient('dte_pdf');
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
            return $this->Api->send($response['body'], $response['status']['code']);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        $this->Api->response()->type('application/pdf');
        foreach (['Content-Disposition', 'Content-Length'] as $header) {
            if (isset($response['header'][$header])) {
                $this->Api->response()->header($header, $response['header'][$header]);
            }
        }
        $this->Api->send($response['body']);
    }

    /**
     * Acción para mostrar el PDF de un EnvioDTE de un intercambio de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-29
     */
    public function pdf($codigo, $cedible = false, $emisor = null, $dte = null, $folio = null)
    {
        $Receptor = $this->getContribuyente();
        $response = $this->consume('/api/dte/dte_intercambios/pdf/'.$codigo.'/'.$Receptor->rut.'/'.(int)$cedible.'/'.(int)$emisor.'/'.(int)$dte.'/'.(int)$folio);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message(
                $response['body'], 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // si dió código 200 se entrega la respuesta del servicio web
        $this->response->type('application/pdf');
        if (isset($response['header']['Content-Disposition'])) {
            $disposition = $Receptor->config_pdf_disposition ? 'inline' : 'attachement';
            $response['header']['Content-Disposition'] = str_replace(['attachement', 'inline'], $disposition, $response['header']['Content-Disposition']);
        }
        foreach (['Content-Disposition', 'Content-Length'] as $header) {
            if (isset($response['header'][$header])) {
                $this->response->header($header, $response['header'][$header]);
            }
        }
        $this->response->send($response['body']);
    }

    /**
     * Recurso que descarga el XML del documento intercambiado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-17
     */
    public function _api_xml_GET($codigo, $contribuyente)
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear contribuyente
        $Receptor = new Model_Contribuyente($contribuyente);
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_intercambios/xml')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener DTE intercambio
        $DteIntercambio = new Model_DteIntercambio($Receptor->rut, $codigo, $Receptor->enCertificacion());
        if (!$DteIntercambio->exists()) {
            $this->Api->send('No existe el intercambio solicitado', 404);
        }
        // entregar XML
        $xml = base64_decode($DteIntercambio->archivo_xml);
        $this->Api->response()->type('application/xml', 'ISO-8859-1');
        $this->Api->response()->header('Content-Length', strlen($xml));
        $this->Api->response()->header('Content-Disposition', 'attachement; filename="'.$DteIntercambio->archivo.'"');
        $this->Api->send($xml);
    }

    /**
     * Acción que descarga el XML del documento intercambiado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-17
     */
    public function xml($codigo)
    {
        $Receptor = $this->getContribuyente();
        $response = $this->consume('/api/dte/dte_intercambios/xml/'.$codigo.'/'.$Receptor->rut);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message(
                $response['body'], 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // si dió código 200 se entrega la respuesta del servicio web
        $this->response->type('application/xml', 'ISO-8859-1');
        foreach (['Content-Disposition', 'Content-Length'] as $header) {
            if (isset($response['header'][$header])) {
                $this->response->header($header, $response['header'][$header]);
            }
        }
        $this->response->send($response['body']);
    }

    /**
     * Recurso que entrega los XML del resultado de la revisión del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-07
     */
    public function _api_resultados_xml_GET($codigo, $contribuyente)
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear contribuyente
        $Emisor = new Model_Contribuyente($contribuyente);
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_intercambios/resultados_xml')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener DTE intercambio
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, $Emisor->enCertificacion());
        if (!$DteIntercambio->exists()) {
            $this->Api->send('No existe el intercambio solicitado', 404);
        }
        // si no hay XML error
        if (!$DteIntercambio->recepcion_xml and !$DteIntercambio->recibos_xml and !$DteIntercambio->resultado_xml) {
            $this->Api->send('No existen archivos de resultado generados, no se ha procesado aun el intercambio', 400);
        }
        // agregar a archivo comprimido y entregar
        $dir = TMP.'/resultado_intercambio_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$DteIntercambio->codigo;
        if (is_dir($dir)) {
            \sowerphp\general\Utility_File::rmdir($dir);
        }
        if (!mkdir($dir)) {
            $this->Api->send('No fue posible crear el directorio temporal para los XML', 507);
        }
        if ($DteIntercambio->recepcion_xml) {
            file_put_contents($dir.'/RecepcionDTE.xml', base64_decode($DteIntercambio->recepcion_xml));
        }
        if ($DteIntercambio->recibos_xml) {
            file_put_contents($dir.'/EnvioRecibos.xml', base64_decode($DteIntercambio->recibos_xml));
        }
        if ($DteIntercambio->resultado_xml) {
            file_put_contents($dir.'/ResultadoDTE.xml', base64_decode($DteIntercambio->resultado_xml));
        }
        \sowerphp\general\Utility_File::compress($dir, ['format'=>'zip', 'delete'=>true]);
        exit; // TODO: enviar usando $this->Api->send() / File::compress()
    }

    /**
     * Acción que entrega los XML del resultado de la revisión del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-17
     */
    public function resultados_xml($codigo)
    {
        $Emisor = $this->getContribuyente();
        $response = $this->consume('/api/dte/dte_intercambios/resultados_xml/'.$codigo.'/'.$Emisor->rut);
        if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message(
                $response['body'], 'error'
            );
            if (in_array($response['status']['code'], [401, 403, 404])) {
                $this->redirect('/dte/dte_intercambios/listar');
            } else {
                $this->redirect(str_replace('resultados_xml', 'ver', $this->request->request));
            }
        }
        // si dió código 200 se entrega la respuesta del servicio web
        $this->response->type('application/zip');
        foreach (['Content-Disposition', 'Content-Length'] as $header) {
            if (isset($response['header'][$header])) {
                $this->response->header($header, $response['header'][$header]);
            }
        }
        $this->response->send($response['body']);
    }

    /**
     * Acción que procesa y responde al intercambio recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-20
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
        // obtener objeto de intercambio
        $DteIntercambio = new Model_DteIntercambio($Emisor->rut, $codigo, $Emisor->enCertificacion());
        if (!$DteIntercambio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el intercambio solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios/listar');
        }
        // armar documentos con sus respuestas
        $documentos = [];
        $n_dtes = count($_POST['TipoDTE']);
        for ($i=0; $i<$n_dtes; $i++) {
            $documentos[] = [
                'TipoDTE' => $_POST['TipoDTE'][$i],
                'Folio' => $_POST['Folio'][$i],
                'FchEmis' => $_POST['FchEmis'][$i],
                'RUTEmisor' => $_POST['RUTEmisor'][$i],
                'RUTRecep' => $_POST['RUTRecep'][$i],
                'MntTotal' => $_POST['MntTotal'][$i],
                'EstadoRecepDTE' => $_POST['rcv_accion_codigo'][$i],
                'RecepDTEGlosa' => $_POST['rcv_accion_glosa'][$i],
            ];
        }
        // armar configuración extra para la respuesta
        $config = [
            'user_id' => $this->Auth->User->id,
            'NmbContacto' => $_POST['NmbContacto'],
            'MailContacto' => $_POST['MailContacto'],
            'sucursal' => $_POST['sucursal'],
            'Recinto' => $_POST['Recinto'],
            'responder_a' => $_POST['responder_a'],
            'periodo' => $_POST['periodo'],
        ];
        // generar respuesta
        try {
            $resultado = $DteIntercambio->responder($documentos, $config);
            if ($resultado['email']===true) {
                $msg = 'Se procesaron DTEs de intercambio y se envió la respuesta a: '.$config['responder_a'];
                if ($resultado['rc']['estado']) {
                    $msg .= '<br/><br/>- '.implode('<br/> -', $resultado['rc']['estado']);
                }
                \sowerphp\core\Model_Datasource_Session::message($msg, 'ok');
            } else {
                $msg = 'Se procesaron DTEs de intercambio, pero no fue posible enviar el email, por favor intente nuevamente.<br /><em>'.$resultado['email']['message'].'</em>';
                if ($resultado['rc']['estado']) {
                    $msg .= '<br/><br/>- '.implode('<br/> -', $resultado['rc']['estado']);
                }
                \sowerphp\core\Model_Datasource_Session::message($msg, 'warning');
            }
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
        }
        // redireccionar
        $this->redirect(str_replace('responder', 'ver', $this->request->request));
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los
     * documentos de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-12-09
     */
    public function buscar()
    {
        $Receptor = $this->getContribuyente();
        $usuarios = array_keys($Receptor->getUsuarios());
        $this->set([
            'Receptor' => $Receptor,
            'tipos_dte' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(),
            'usuarios' => array_combine($usuarios, $usuarios),
            'values_xml' => [],
        ]);
        if (isset($_POST['submit'])) {
            $_POST['xml'] = [];
            $values_xml = [];
            if (!empty($_POST['xml_nodo'])) {
                $n_xml = count($_POST['xml_nodo']);
                for ($i=0; $i<$n_xml; $i++) {
                    if (!empty($_POST['xml_nodo'][$i]) and !empty($_POST['xml_valor'][$i])) {
                        $_POST['xml'][$_POST['xml_nodo'][$i]] = $_POST['xml_valor'][$i];
                        $values_xml[] = [
                            'xml_nodo' => $_POST['xml_nodo'][$i],
                            'xml_valor' => $_POST['xml_valor'][$i],
                        ];
                    }
                    unset($_POST['xml_nodo'][$i], $_POST['xml_valor'][$i]);
                }
            }
            $this->set([
                'values_xml' => $values_xml,
            ]);
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/dte/dte_intercambios/buscar/'.$Receptor->rut, $_POST);
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            }
            else {
                $this->set([
                    'intercambios' => $response['body'],
                ]);
            }
        }
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * documentos de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-18
     */
    public function _api_buscar_POST($receptor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists())
            $this->Api->send('Emisor no existe', 404);
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_intercambios/buscar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // buscar documentos
        $intercambios = (new Model_DteIntercambios())->setContribuyente($Receptor)->buscar((array)$this->Api->data);
        if (!$intercambios) {
            $this->Api->send('No se encontraron documentos de intercambio que coincidan con la búsqueda', 404);
        }
        $this->Api->send($intercambios, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite buscar dentro de la bandeja de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-03-07
     */
    public function _api_buscar_GET($receptor)
    {
        // crear receptor y verificar autorización
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            $this->Api->send('Receptor no existe', 404);
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_intercambios/listar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // buscar documentos
        $filtros = $this->getQuery([
            'soloPendientes' => true,
            'emisor' => null,
            'folio' => null,
            'recibido_desde' => date('Y-m-01'),
            'recibido_hasta' => date('Y-m-d'),
            'usuario' => null,
        ]);
        $intercambios = (new Model_DteIntercambios())->setContribuyente($Receptor)->buscar($filtros);
        if (!$intercambios) {
            $this->Api->send('No se encontraron documentos de intercambio', 404);
        }
        $this->Api->send($intercambios, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción que permite cargar la respuesta recibida de un intercambio
     * Esta acción principalmente sirve para procesar y validar una respuesta
     * que no ha sido procesada de manera automática por la actualización
     * de la bandeja de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-07-03
     */
    public function cargar_xml()
    {
        $Emisor = $this->getContribuyente();
        $this->set('Emisor', $Emisor);
        if (!empty($_FILES['archivo'])) {
            $n_archivos = count($_FILES['archivo']['name']);
            $archivos = [];
            for ($i = 0; $i<$n_archivos; $i++) {
                $file = [
                    'name' => $_FILES['archivo']['name'][$i],
                    'tmp_name' => $_FILES['archivo']['tmp_name'][$i],
                    'error' => $_FILES['archivo']['error'][$i],
                    'size' => $_FILES['archivo']['size'][$i],
                    'type' => $_FILES['archivo']['type'][$i],
                    'data' => file_get_contents($_FILES['archivo']['tmp_name'][$i]),
                ];
                if ($file['error'] or !$file['size'] or $file['type'] != 'text/xml') {
                    continue;
                }
                $archivo = [
                    'name' => $_FILES['archivo']['name'][$i],
                ];
                // tratar de procesar como EnvioDTE
                try {
                    $procesarEnvioDTE = (new Model_DteIntercambios())->setContribuyente($Emisor)->procesarEnvioDTE($file);
                    if ($procesarEnvioDTE!==null) {
                        $archivo['estado'] = 'EnvioDTE: procesado y guardado';
                        $archivos[] = $archivo;
                        continue;
                    }
                } catch (\Exception $e) {
                    $archivo['estado'] = 'EnvioDTE: '.$e->getMessage();
                    $archivos[] = $archivo;
                    continue;
                }
                // tratar de procesar como Recibo
                try {
                    $procesarRecibo = (new Model_DteIntercambioRecibo())->saveXML($this->getContribuyente(), $file['data']);
                    if ($procesarRecibo!==null) {
                        $archivo['estado'] = 'Recibo: procesado y guardado';
                        $archivos[] = $archivo;
                        continue;
                    }
                } catch (\Exception $e) {
                    $archivo['estado'] = 'Recibo: '.$e->getMessage();
                    $archivos[] = $archivo;
                    continue;
                }
                // tratar de procesar como Recepción
                try {
                    $procesarRecepcion = (new Model_DteIntercambioRecepcion())->saveXML($this->getContribuyente(), $file['data']);
                    if ($procesarRecepcion!==null) {
                        $archivo['estado'] = 'Recepción: procesado y guardado';
                        $archivos[] = $archivo;
                        continue;
                    }
                } catch (\Exception $e) {
                    $archivo['estado'] = 'Recepción: '.$e->getMessage();
                    $archivos[] = $archivo;
                    continue;
                }
                // tratar de procesar como Resultado
                try {
                    $procesarResultado = (new Model_DteIntercambioResultado())->saveXML($this->getContribuyente(), $file['data']);
                    if ($procesarResultado!==null) {
                        $archivo['estado'] = 'Resultado: procesado y guardado';
                        $archivos[] = $archivo;
                        continue;
                    }
                } catch (\Exception $e) {
                    $archivo['estado'] = 'Resultado: '.$e->getMessage();
                    $archivos[] = $archivo;
                    continue;
                }
                // no se procesó
                $archivo['estado'] = 'No procesado. Es probable que no sea del ambiente actual o bien no sea un XML de los 4 casos esperados: EnvioDTE, Recibo, Recepción o Resultado.';
                $archivos[] = $archivo;
            }
            $this->set('archivos', $archivos);
        }
    }

}
