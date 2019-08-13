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
 * Controlador de dte recibidos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-04
 */
class Controller_DteRecibidos extends \Controller_App
{

    /**
     * Acción que permite mostrar los documentos recibidos por el contribuyente
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
            $documentos_total = $Emisor->countDocumentosRecibidos($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = \sowerphp\core\Configure::read('app.registers_per_page');
                $filtros['offset'] = ($pagina-1)*$filtros['limit'];
                $paginas = ceil($documentos_total/$filtros['limit']);
                if ($pagina != 1 && $pagina > $paginas) {
                    $this->redirect('/dte/'.$this->request->params['controller'].'/listar'.$searchUrl);
                }
            }
            $documentos = $Emisor->getDocumentosRecibidos($filtros);
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
            'tipos_dte' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(true),
            'usuarios' => $Emisor->getListUsuarios(),
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * Acción que permite agregar un DTE recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-03
     */
    public function agregar()
    {
        $Emisor = $this->getContribuyente();
        // asignar variables para la vista
        $tipo_transacciones = \sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones;
        unset($tipo_transacciones[5], $tipo_transacciones[6]);
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/dte.js']],
            'Emisor' => $Emisor,
            'tipos_documentos' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(true),
            'iva_no_recuperables' => (new \website\Dte\Admin\Mantenedores\Model_IvaNoRecuperables())->getList(),
            'impuesto_adicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())->getList(),
            'iva_tasa' => \sasco\LibreDTE\Sii::getIVA(),
            'sucursales' => $Emisor->getSucursales(),
            'tipo_transacciones' => $tipo_transacciones,
        ]);
        // procesar formulario si se pasó
        if (isset($_POST['submit']))
            $this->save();
        $this->autoRender = false;
        $this->render('DteRecibidos/agregar_modificar');
    }

    /**
     * Acción que permite editar un DTE recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-02
     */
    public function modificar($emisor, $dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener dte recibido
        $DteRecibido = new Model_DteRecibido($emisor, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteRecibido->exists() or $DteRecibido->receptor!=$Emisor->rut) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE recibido solicitado no existe', 'error'
            );
            $this->redirect('/dte/dte_recibidos/listar');
        }
        // agregar variables para la vista
        $tipo_transacciones = \sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones;
        unset($tipo_transacciones[5], $tipo_transacciones[6]);
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/dte.js']],
            'Emisor' => $Emisor,
            'DteRecibido' => $DteRecibido,
            'tipos_documentos' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(true),
            'iva_no_recuperables' => (new \website\Dte\Admin\Mantenedores\Model_IvaNoRecuperables())->getList(),
            'impuesto_adicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())->getList(),
            'iva_tasa' => \sasco\LibreDTE\Sii::getIVA(),
            'sucursales' => $Emisor->getSucursales(),
            'tipo_transacciones' => $tipo_transacciones,
        ]);
        // procesar formulario si se pasó
        if (isset($_POST['submit']))
            $this->save();
        $this->autoRender = false;
        $this->render('DteRecibidos/agregar_modificar');
    }

    /**
     * Método que agrega o modifica un DTE recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-02
     */
    private function save()
    {
        $Emisor = $this->getContribuyente();
        // revisar datos minimos
        foreach(['emisor', 'dte', 'folio', 'fecha', 'tasa'] as $attr) {
            if (!isset($_POST[$attr][0])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe indicar '.$attr, 'error'
                );
                return;
            }
        }
        // crear dte recibido
        list($emisor, $dv) = explode('-', str_replace('.', '', $_POST['emisor']));
        $DteRecibido = new Model_DteRecibido($emisor, $_POST['dte'], (int)$_POST['folio'], (int)$Emisor->config_ambiente_en_certificacion);
        $DteRecibido->receptor = $Emisor->rut;
        $DteRecibido->tasa = !empty($_POST['neto']) ? (float)$_POST['tasa'] : 0;
        $DteRecibido->fecha = $_POST['fecha'];
        $DteRecibido->exento = !empty($_POST['exento']) ? $_POST['exento'] : null;
        $DteRecibido->neto = !empty($_POST['neto']) ? $_POST['neto'] : null;
        $DteRecibido->iva = !empty($_POST['iva']) ? $_POST['iva'] : round((int)$DteRecibido->neto * ($DteRecibido->tasa/100));
        $DteRecibido->usuario = $this->Auth->User->id;
        // tipo transaccion, iva uso común, no recuperable e impuesto adicional
        $DteRecibido->tipo_transaccion = !empty($_POST['tipo_transaccion']) ? $_POST['tipo_transaccion'] : null;
        $DteRecibido->iva_uso_comun = !empty($_POST['iva_uso_comun']) ? $_POST['iva_uso_comun'] : null;
        if ($DteRecibido->iva and !empty($_POST['iva_no_recuperable_codigo'])) {
            $DteRecibido->iva_no_recuperable = [];
            $n_codigos = count($_POST['iva_no_recuperable_codigo']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (!empty($_POST['iva_no_recuperable_codigo'][$i])) {
                    $DteRecibido->iva_no_recuperable[] = [
                        'codigo' => $_POST['iva_no_recuperable_codigo'][$i],
                        'monto' => !empty($_POST['iva_no_recuperable_monto'][$i]) ? $_POST['iva_no_recuperable_monto'][$i] : $DteRecibido->iva,
                    ];
                }
            }
            $DteRecibido->iva_no_recuperable = $DteRecibido->iva_no_recuperable ? json_encode($DteRecibido->iva_no_recuperable) : null;
        } else {
            $DteRecibido->iva_no_recuperable = null;
        }
        $impuesto_adicional_monto_total = 0;
        if (!empty($_POST['impuesto_adicional_codigo'])) {
            $DteRecibido->impuesto_adicional = [];
            $n_codigos = count($_POST['impuesto_adicional_codigo']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (!empty($_POST['impuesto_adicional_codigo'][$i])) {
                    $DteRecibido->impuesto_adicional[] = [
                        'codigo' => $_POST['impuesto_adicional_codigo'][$i],
                        'tasa' => $_POST['impuesto_adicional_tasa'][$i] ? $_POST['impuesto_adicional_tasa'][$i] : null,
                        'monto' => $_POST['impuesto_adicional_monto'][$i] ? $_POST['impuesto_adicional_monto'][$i] : null
                    ];
                    $impuesto_adicional_monto_total += (int)$_POST['impuesto_adicional_monto'][$i];
                }
            }
            $DteRecibido->impuesto_adicional = $DteRecibido->impuesto_adicional ? json_encode($DteRecibido->impuesto_adicional) : null;
        } else {
            $DteRecibido->impuesto_adicional = null;
        }
        $DteRecibido->impuesto_tipo = $_POST['impuesto_tipo'];
        $DteRecibido->anulado = isset($_POST['anulado']) ? 'A' : null;
        $DteRecibido->impuesto_sin_credito = !empty($_POST['impuesto_sin_credito']) ? $_POST['impuesto_sin_credito'] : null;
        $DteRecibido->monto_activo_fijo = !empty($_POST['monto_activo_fijo']) ? $_POST['monto_activo_fijo'] : null;
        $DteRecibido->monto_iva_activo_fijo = !empty($_POST['monto_iva_activo_fijo']) ? $_POST['monto_iva_activo_fijo'] : null;
        $DteRecibido->iva_no_retenido = !empty($_POST['iva_no_retenido']) ? $_POST['iva_no_retenido'] : null;
        $DteRecibido->periodo = !empty($_POST['periodo']) ? $_POST['periodo'] : null;
        $DteRecibido->impuesto_puros = !empty($_POST['impuesto_puros']) ? $_POST['impuesto_puros'] : null;
        $DteRecibido->impuesto_cigarrillos = !empty($_POST['impuesto_cigarrillos']) ? $_POST['impuesto_cigarrillos'] : null;
        $DteRecibido->impuesto_tabaco_elaborado = !empty($_POST['impuesto_tabaco_elaborado']) ? $_POST['impuesto_tabaco_elaborado'] : null;
        $DteRecibido->impuesto_vehiculos = !empty($_POST['impuesto_vehiculos']) ? $_POST['impuesto_vehiculos'] : null;
        $DteRecibido->numero_interno = !empty($_POST['numero_interno']) ? $_POST['numero_interno'] : null;
        $DteRecibido->emisor_nc_nd_fc = isset($_POST['emisor_nc_nd_fc']) ? 1 : null;
        $DteRecibido->sucursal_sii_receptor = !empty($_POST['sucursal_sii_receptor']) ? $_POST['sucursal_sii_receptor'] : null;
        $DteRecibido->total = !empty($_POST['total']) ? $_POST['total'] : (int)$DteRecibido->exento + (int)$DteRecibido->neto + (int)$DteRecibido->iva + $impuesto_adicional_monto_total + (int)$DteRecibido->impuesto_sin_credito + (int)$DteRecibido->impuesto_puros + (int)$DteRecibido->impuesto_cigarrillos + (int)$DteRecibido->impuesto_tabaco_elaborado + (int)$DteRecibido->impuesto_vehiculos;
        // si el DTE es de producción y es electrónico entonces se consultará su
        // estado antes de poder guardar, esto evitará agregar documentos que no
        // han sido recibidos en el SII o sus datos son incorrectos
        if (!$Emisor->config_ambiente_en_certificacion and $DteRecibido->getTipo()->electronico and !$Emisor->config_recepcion_omitir_verificacion_sii) {
            // obtener firma
            $Firma = $Emisor->getFirma($this->Auth->User->id);
            if (!$Firma) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
                );
                $this->redirect('/dte/admin/firma_electronicas');
            }
            // consultar estado dte
            $estado = $DteRecibido->getEstado($Firma);
            if ($estado===false) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No se pudo obtener el estado del DTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
                return;
            } else if (in_array($estado['ESTADO'], ['DNK', 'FAU', 'FNA', 'EMP'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Estado DTE: '.(is_array($estado)?implode('. ', $estado):$estado), 'error'
                );
                return;
            }
        }
        // todo ok con el dte así que se agrega a los dte recibidos
        try {
            $DteRecibido->save();
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE recibido guardado', 'ok'
            );
            $this->redirect('/dte/dte_recibidos/listar');
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible guardar el DTE: '.$e->getMessage(), 'error'
            );
        }
    }

    /**
     * Acción que permite eliminar un DTE recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-11
     */
    public function eliminar($emisor, $dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $DteRecibido = new Model_DteRecibido($emisor, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteRecibido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible eliminar, el DTE recibido solicitado no existe', 'warning'
            );
        } else {
            $DteRecibido->delete();
            \sowerphp\core\Model_Datasource_Session::message(
                'Se eliminó el DTE T'.$DteRecibido->dte.'F'.$DteRecibido->folio.' recibido de '.\sowerphp\app\Utility_Rut::addDV($DteRecibido->emisor), 'ok'
            );
        }
        $this->redirect('/dte/dte_recibidos/listar');
    }

    /**
     * Acción que permite descargar el PDF del documento recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-10-05
     */
    public function pdf($emisor, $dte, $folio)
    {
        $Receptor = $this->getContribuyente();
        $DteRecibido = new Model_DteRecibido($emisor, $dte, $folio, (int)$Receptor->config_ambiente_en_certificacion);
        if (!$DteRecibido->exists() or !$DteRecibido->intercambio) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible obtener el PDF, el DTE recibido solicitado no existe o bien no tiene intercambio asociado', 'warning'
            );
            $this->redirect('/dte/dte_recibidos/listar');
        }
        $this->redirect('/dte/dte_intercambios/pdf/'.$DteRecibido->intercambio.'/0/'.$emisor.'/'.$dte.'/'.$folio);
    }

    /**
     * Acción que permite buscar documentos recibidos en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-06
     */
    public function sii()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'hoy' => date('Y-m-d'),
        ]);
        if (isset($_POST['submit'])) {
            $r = $this->consume('/api/dte/dte_recibidos/sii/'.$_POST['desde'].'/'.$_POST['hasta'].'/'.$Emisor->rut.'?formato=json');
            if ($r['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible obtener los documentos desde el SII, intente nuevamente: '.$r['body'], 'error');
                return;
            }
            if (empty($r['body'])) {
                \sowerphp\core\Model_Datasource_Session::message('No se encontraron documentos, se recomienda reintentar la consulta ya que a veces no se obtiene la respuesta correcta desde el SII', 'warning');
                return;
            }
            $documentos = $r['body'];
            if ($_POST['excluir_en_libro']) {
                $n_documentos = count($documentos);
                for ($i=0; $i<$n_documentos; $i++) {
                    list($rut, $dv) = explode('-', $documentos[$i]['rut']);
                    try {
                        $DteRecibido = new Model_DteRecibido($rut, $documentos[$i]['dte'], $documentos[$i]['folio'], (int)$Emisor->config_ambiente_en_certificacion);
                        if ($DteRecibido->usuario) {
                            unset($documentos[$i]);
                        }
                    } catch (\Exception $e) {
                    }
                }
            }
            $this->set('documentos', $documentos);
        }
    }

    /**
     * Acción de la API que permite obtener la información de un documento recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-21
     */
    public function _api_info_GET($emisor, $dte, $folio, $receptor)
    {
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            $this->Api->send('Receptor no existe', 404);
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        if (strpos($emisor, '-')) {
            $emisor = \sowerphp\app\Utility_Rut::normalizar($emisor);
        }
        $DteRecibido = new Model_DteRecibido((int)$emisor, (int)$dte, (int)$folio, (int)$Receptor->config_ambiente_en_certificacion);
        if (!$DteRecibido->exists()) {
            $this->Api->send('No existe el documento recibido solicitado T'.$dte.'F'.$folio, 404);
        }
        if ($DteRecibido->receptor!=$Receptor->rut) {
            $this->Api->send('RUT del receptor no corresponde al DTE T'.$dte.'F'.$folio, 400);
        }
        extract($this->getQuery([
            'getXML' => false,
            'getDetalle' => false,
        ]));
        if ($getXML and $DteRecibido->intercambio) {
            $DteRecibido->getXML();
        }
        if ($getDetalle and $DteRecibido->intercambio) {
            $DteRecibido->getDetalle();
        }
        $this->Api->send($DteRecibido, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los DTE
     * recibidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-02
     */
    public function buscar()
    {
        $Receptor = $this->getContribuyente();
        $this->set([
            'tipos_dte' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(),
        ]);
        if (isset($_POST['submit'])) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/dte/dte_recibidos/buscar/'.$Receptor->rut, [
                'dte' => $_POST['dte'],
                'emisor' => $_POST['emisor'],
                'razon_social' => $_POST['razon_social'],
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
                    'Receptor' => $Receptor,
                    'documentos' => $response['body'],
                ]);
            }
        }
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * DTEs recibidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-02
     */
    public function _api_buscar_POST($receptor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar permisos del usuario autenticado sobre el receptor del DTE
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            $this->Api->send('Receptor no existe', 404);
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/buscar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // buscar documentos
        $this->Api->send($Receptor->getDocumentosRecibidos($this->Api->data, true), 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite buscar dentro de los documentos recibidos
     * @deprecated Este método se debe dejar de usar y será reemplazado con la versión por POST
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-11
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
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/listar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // buscar documentos
        $filtros = $this->getQuery([
            'fecha_desde' => date('Y-m-01'),
            'fecha_hasta' => date('Y-m-d'),
            'fecha' => null,
            'emisor' => null,
            'dte' => null,
            'total_desde' => null,
            'total_hasta' => null,
            'total' => null,
        ]);
        $documentos = (new Model_DteRecibidos())->setContribuyente($Receptor)->buscar($filtros);
        if (!$documentos) {
            $this->Api->send('No se encontraron documentos', 404);
        }
        $this->Api->send($documentos, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite buscar en el SII los documentos recibidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-13
     */
    public function _api_sii_GET($desde, $hasta, $receptor)
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
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/sii')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // armar datos con firma
        $Firma = $Receptor->getFirma($User->id);
        $data = [
            'firma' => [
                'cert-data' => $Firma->getCertificate(),
                'key-data' => $Firma->getPrivateKey(),
            ],
        ];
        // buscar documentos
        extract($this->getQuery([
            'formato' => 'csv',
        ]));
        $certificacion = (int)$Receptor->config_ambiente_en_certificacion;
        $response = libredte_consume(
            '/sii/dte_recibidos/'.$Receptor->getRUT().'/'.$desde.'/'.$hasta.'?formato='.$formato.'&certificacion='.$certificacion,
            $data
        );
        if ($response['status']['code']!=200) {
            $this->Api->send($response['body'], $response['status']['code']);
        }
        if ($formato=='json') {
            $this->Api->send($response['body'], 200, JSON_PRETTY_PRINT);
        } else {
            $this->Api->response()->type('text/csv');
            $this->Api->response()->header('Content-Disposition', 'attachment; filename=dte_recibidos_'.($certificacion?'certificacion':'produccion').'_'.$receptor.'_'.$desde.'_'.$hasta.'.csv');
            $this->Api->response()->header('Pragma', 'no-cache');
            $this->Api->response()->header('Expires', 0);
            $this->Api->send($response['body']);
        }
    }

}
