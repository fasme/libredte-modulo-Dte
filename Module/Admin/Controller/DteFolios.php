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
namespace website\Dte\Admin;

/**
 * Clase para el controlador asociado a la tabla dte_folio de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla dte_folio
 * @author SowerPHP Code Generator
 * @version 2015-09-22 10:44:45
 */
class Controller_DteFolios extends \Controller_App
{

    /**
     * Acción que muestra la página principal para mantener los folios de la
     * empresa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'folios' => $Emisor->getFolios(),
        ]);
    }

    /**
     * Acción que agrega mantenedor para un nuevo tipo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function agregar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'dte_tipos' => $Emisor->getDocumentosAutorizados(),
        ]);
        // procesar creación del mantenedor
        if (isset($_POST['submit'])) {
            // verificar que esté autorizado a cargar folios del tipo de dte
            if (!$Emisor->documentoAutorizado($_POST['dte'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $Emisor->razon_social.' no está autorizada a emitir documentos de tipo '.$_POST['dte'], 'error'
                );
                return;
            }
            // crear mantenedor del folio
            $DteFolio = new Model_DteFolio($Emisor->rut, $_POST['dte'], (int)$Emisor->config_ambiente_en_certificacion);
            if (!$DteFolio->exists()) {
                $DteFolio->siguiente = 0;
                $DteFolio->disponibles = 0;
                $DteFolio->alerta = $_POST['alerta'];
                try {
                    $DteFolio->save();
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No fue posible crear el mantenedor del folio: '.$e->getMessage(), 'error'
                    );
                    return;
                }
            }
            // si todo fue bien se redirecciona a la página de carga de CAF
            \sowerphp\core\Model_Datasource_Session::message(
                'Ahora debe subir un archivo CAF para el tipo de documento '.$_POST['dte']
            );
            $this->redirect('/dte/admin/dte_folios/subir_caf');
        }
    }

    /**
     * Acción que permite subir un caf para un tipo de folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-08-06
     */
    public function subir_caf()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
        ]);
        // procesar solo si se envió el formulario
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir CAF
            if (!isset($_FILES['caf']) or $_FILES['caf']['error']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Ocurrió un error al subir el CAF', 'error'
                );
                return;
            }
            if (\sowerphp\general\Utility_File::mimetype($_FILES['caf']['tmp_name'])!='application/xml') {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Formato del archivo '.$_FILES['caf']['name'].' es incorrecto', 'error'
                );
                return;
            }
            $caf = file_get_contents($_FILES['caf']['tmp_name']);
            $Folios = new \sasco\LibreDTE\Sii\Folios($caf);
            // buscar el mantenedor de folios del CAF
            $DteFolio = new Model_DteFolio($Emisor->rut, $Folios->getTipo(), (int)$Folios->getCertificacion());
            if (!$DteFolio->exists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Primero debe crear el mantenedor de los folios de tipo '.$Folios->getTipo(), 'error'
                );
                return;
            }
            // guardar el CAF
            try {
                $DteFolio->guardarFolios($caf);
                \sowerphp\core\Model_Datasource_Session::message(
                    'El CAF para el documento de tipo '.$Folios->getTipo().' que inicia en '.$Folios->getDesde().' fue cargado, el siguiente folio disponible es '.$DteFolio->siguiente, 'ok'
                );
                $this->redirect('/dte/admin/dte_folios');
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $e->getMessage(), 'error'
                );
                return;
            }
        }
    }

    /**
     * Acción que permite ver el mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-24
     */
    public function ver($dte)
    {
        $Emisor = $this->getContribuyente();
        $DteFolio = new Model_DteFolio($Emisor->rut, $dte, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteFolio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el mantenedor de folios solicitado', 'error'
            );
            $this->redirect('/dte/admin/dte_folios');
        }
        $this->set([
            'Emisor' => $Emisor,
            'DteFolio' => $DteFolio,
        ]);
    }

    /**
     * Acción que permite modificar un mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-29
     */
    public function modificar($dte)
    {
        $Emisor = $this->getContribuyente();
        $DteFolio = new Model_DteFolio($Emisor->rut, $dte, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteFolio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el mantenedor de folios solicitado', 'error'
            );
            $this->redirect('/dte/admin/dte_folios');
        }
        $this->set([
            'Emisor' => $Emisor,
            'DteFolio' => $DteFolio,
        ]);
        if (isset($_POST['submit'])) {
            // validar que campos existan y asignar
            foreach (['siguiente', 'alerta'] as $attr) {
                if (empty($_POST[$attr])) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Debe especificar el campo: '.$attr, 'error'
                    );
                    return;
                }
                $DteFolio->$attr = $_POST[$attr];
            }
            // guardar y redireccionar
            try {
                if (!$DteFolio->calcularDisponibles()) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No fue posible actualizar el mantenedor de folios', 'error'
                    );
                    return;
                }
                \sowerphp\core\Model_Datasource_Session::message(
                    'Mantenedor de folios para tipo '.$DteFolio->dte.' actualizado', 'ok'
                );
                $this->redirect('/dte/admin/dte_folios');
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible actualizar el mantenedor de folios: '.$e->getMessage(), 'error'
                );
                return;
            }
        }
    }

    /**
     * Acción que permite descargar el XML del archivo CAF
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-10
     */
    public function xml($dte, $desde)
    {
        $Emisor = $this->getContribuyente();
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Sólo el administrador de la empresa puede descargar los archivos CAF', 'error'
            );
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        $DteCaf = new Model_DteCaf($Emisor->rut, $dte, (int)$Emisor->config_ambiente_en_certificacion, $desde);
        if (!$DteCaf->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el archivo CAF solicitado', 'error'
            );
            $this->redirect('/dte/admin/dte_folios');
        }
        // entregar XML
        $file = 'caf_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$dte.'_'.$desde.'.xml';
        $xml = $DteCaf->getXML();
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que permite solicitar un archivo CAF al SII y cargarlo en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-05
     */
    public function solicitar_caf($dte = null)
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'dte_tipos' => $Emisor->getDocumentosAutorizados(),
            'dte' => $dte,
        ]);
        // procesar solicitud de folios
        if (isset($_POST['submit'])) {
            // buscar el mantenedor de folios del CAF
            $DteFolio = new Model_DteFolio($Emisor->rut, $_POST['dte'], (int)$Emisor->config_ambiente_en_certificacion);
            if (!$DteFolio->exists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Primero debe crear el mantenedor de los folios de tipo '.$_POST['dte'], 'error'
                );
                return;
            }
            // solicitar timbraje
            try {
                $Folios = $DteFolio->timbrar($_POST['cantidad']);
                \sowerphp\core\Model_Datasource_Session::message(
                    'El CAF para el documento de tipo '.$Folios->getTipo().' que inicia en '.$Folios->getDesde().' fue cargado, el siguiente folio disponible es '.$DteFolio->siguiente, 'ok'
                );
                $this->redirect('/dte/admin/dte_folios');
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $e->getMessage(), 'error'
                );
                return;
            }
        }
    }

    /**
     * Acción que muestra la página con el estado del folio en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-17
     */
    public function estado($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $r = $this->consume('/api/dte/admin/dte_folios/estado/'.$dte.'/'.$folio.'/'.$Emisor->rut);
        if ($r['status']['code']!=200) {
            die($r['body']);
        }
        $this->layout = null;
        $this->set([
            'Emisor' => $Emisor,
            'dte' => $dte,
            'folio' => $folio,
            'estado_web' => utf8_encode($r['body']),
        ]);
    }

    /**
     * Acción que permite anular un folio directamente en el sitio del SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-17
     */
    public function anular($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $r = $this->consume('/api/dte/admin/dte_folios/anular/'.$dte.'/'.$folio.'/'.$Emisor->rut);
        if ($r['status']['code']!=200) {
            die($r['body']);
        }
        echo utf8_encode($r['body']);
        exit;
    }

    /**
     * Acción que permite descargar del SII los folios según su estado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-18
     */
    public function descargar($dte, $folio, $estado = 'recibidos')
    {
        $Emisor = $this->getContribuyente();
        $DteCaf = new Model_DteCaf($Emisor->rut, $dte, (int)$Emisor->config_ambiente_en_certificacion, $folio);
        if (!$DteCaf->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el CAF solicitado', 'error'
            );
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        try {
            $detalle = $DteCaf->{'getFolios'.ucfirst($estado)}();
        } catch(\sowerphp\core\Exception_Object_Method_Missing $e) {
            \sowerphp\core\Model_Datasource_Session::message('No es posible descargar el estado de folios '.$estado, 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        } catch(\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        if (!$detalle) {
            \sowerphp\core\Model_Datasource_Session::message('No se encontraron folios con el estado \''.$estado.'\' en el SII para el CAF que inicia en '.$folio, 'warning');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        array_unshift($detalle, ['Folio inicial', 'Folio final', 'Cantidad de folios']);
        \sowerphp\general\Utility_Spreadsheet_CSV::generate($detalle, 'folios_'.$estado.'_'.$Emisor->rut.'_'.$dte.'_'.$folio.'_'.date('Y-m-d'));
    }

    /**
     * Acción que permite solicitar el informe de estado de los folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-19
     */
    public function informe_estados()
    {
        $Emisor = $this->getContribuyente();
        $aux = $Emisor->getDocumentosAutorizados();
        $documentos = [];
        foreach ($aux as $d) {
            if (!in_array($d['codigo'], [39, 41])) {
                $documentos[] = $d;
            }
        }
        $this->set([
            'documentos' => $documentos,
        ]);
        // procesar formulario
        if (isset($_POST['submit'])) {
            // si no hay documentos error
            if (empty($_POST['documentos'])) {
                \sowerphp\core\Model_Datasource_Session::message('Debe seleccionar al menos un tipo de documento', 'error');
                return;
            }
            if (empty($_POST['estados'])) {
                \sowerphp\core\Model_Datasource_Session::message('Debe seleccionar al menos un estado', 'error');
                return;
            }
            // lanzar comando
            $cmd = 'Dte.Admin.DteFolios_Estados '.escapeshellcmd((int)$Emisor->rut).' '.escapeshellcmd(implode(',',$_POST['documentos'])).' '.escapeshellcmd(implode(',',$_POST['estados'])).' '.escapeshellcmd((int)$this->Auth->User->id).' -v';
            if ($this->shell($cmd)) {
                \sowerphp\core\Model_Datasource_Session::message('Error al tratar de generar su informe, por favor reintentar', 'error');
            } else {
                \sowerphp\core\Model_Datasource_Session::message('Su informe está siendo generado, será enviado a su correo cuando esté listo', 'ok');
            }
            $this->redirect('/dte/admin/dte_folios');
        }
    }

    /**
     * Recurso que entrega el la información de cierto mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-26
     */
    public function _api_info_GET($dte, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/admin/dte_folios/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteFolio = new Model_DteFolio($Emisor->rut, $dte, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteFolio->exists()) {
            $this->Api->send('No existe el mantenedor de folios para el tipo de DTE '.$dte, 404);
        }
        extract($this->Api->getQuery(['sinUso'=>false]));
        if ($sinUso) {
            $DteFolio->sin_uso = $DteFolio->getSinUso();
        }
        return $DteFolio;
    }

    /**
     * Recurso que permite modificar el mantenedor de folios
     * Modifica: folio siguiente y/o alerta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-17
     */
    public function _api_modificar_POST($dte, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteFolio = new Model_DteFolio($Emisor->rut, $dte, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteFolio->exists()) {
            $this->Api->send('No existe el mantenedor de folios para el tipo de DTE '.$dte, 404);
        }
        // validar que campos existan y asignar
        foreach (['siguiente', 'alerta'] as $attr) {
            if (isset($this->Api->data[$attr])) {
                $DteFolio->$attr = $this->Api->data[$attr];
            }
        }
        // guardar e informar
        try {
            if (!$DteFolio->calcularDisponibles()) {
                $this->Api->send('No fue posible actualizar el mantenedor de folios', 500);
            }
            return $DteFolio;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->Api->send('No fue posible actualizar el mantenedor de folios: '.$e->getMessage(), 500);
        }
    }

    /**
     * Recurso que permite solicitar un CAF al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-06
     */
    public function _api_solicitar_caf_GET($dte, $cantidad, $emisor)
    {
        // crear usuario, emisor y verificar permisos
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/admin/dte_folios/subir_caf')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // verificar que exista un mantenedor de folios
        $DteFolio = new Model_DteFolio($Emisor->rut, $dte, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteFolio->exists()) {
            $this->Api->send('Primero debe crear el mantenedor de los folios de tipo '.$dte, 500);
        }
        if (!$DteFolio->siguiente) {
            $this->Api->send('Debe tener al menos un CAF cargado manualmente antes de solicitar timbraje vía LibreDTE', 500);
        }
        // recuperar firma electrónica
        $Firma = $Emisor->getFirma($User->id);
        if (!$Firma) {
            $this->Api->send('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de timbrar', 506);
        }
        // solicitar timbraje
        $data = [
            'firma' => [
                'cert-data' => $Firma->getCertificate(),
                'key-data' => $Firma->getPrivateKey(),
            ],
        ];
        $r = libredte_consume('/sii/caf_solicitar/'.$Emisor->getRUT().'/'.$dte.'/'.$cantidad.'?certificacion='.(int)$Emisor->config_ambiente_en_certificacion, $data);
        if ($r['status']['code']!=200) {
            $this->Api->send('No fue posible timbrar: '.$r['body'], 500);
        }
        return base64_encode($r['body']);
    }

    /**
     * Recurso que permite consultar el estado de un folio en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-17
     */
    public function _api_estado_GET($dte, $folio, $emisor)
    {
        // crear usuario, emisor y verificar permisos
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/admin/dte_folios/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // recuperar firma electrónica
        $Firma = $Emisor->getFirma($User->id);
        if (!$Firma) {
            $this->Api->send('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de consultar el estado de un folio', 506);
        }
        // consultar estado del folio
        $data = [
            'firma' => [
                'cert-data' => $Firma->getCertificate(),
                'key-data' => $Firma->getPrivateKey(),
            ],
        ];
        $r = libredte_consume('/sii/folio_estado/'.$Emisor->getRUT().'/'.$dte.'/'.$folio.'?certificacion='.(int)$Emisor->config_ambiente_en_certificacion, $data);
        if ($r['status']['code']!=200) {
            $this->Api->send('No fue posible consultar el estado del folio: '.$r['body'], 500);
        }
        echo $r['body'];
        exit;
    }

    /**
     * Recurso que permite anular un folio en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-17
     */
    public function _api_anular_GET($dte, $folio, $emisor)
    {
        // crear usuario, emisor y verificar permisos
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/admin/dte_folios/subir_caf')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // recuperar firma electrónica
        $Firma = $Emisor->getFirma($User->id);
        if (!$Firma) {
            $this->Api->send('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de anular un folio', 506);
        }
        // anular folio
        $data = [
            'firma' => [
                'cert-data' => $Firma->getCertificate(),
                'key-data' => $Firma->getPrivateKey(),
            ],
        ];
        $r = libredte_consume('/sii/folio_anular/'.$Emisor->getRUT().'/'.$dte.'/'.$folio.'?certificacion='.(int)$Emisor->config_ambiente_en_certificacion, $data);
        if ($r['status']['code']!=200) {
            $this->Api->send('No fue posible anular el folio: '.$r['body'], 500);
        }
        echo $r['body'];
        exit;
    }

}
