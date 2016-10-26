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
 * Clase para las acciones asociadas a items
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-10-25
 */
class Controller_Itemes extends \Controller_Maintainer
{

    protected $namespace = __NAMESPACE__; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar'=>['codigo', 'item', 'precio', 'moneda', 'bruto', 'clasificacion', 'activo']
    ]; ///< Columnas que se deben mostrar en las vistas

    /**
     * Acción para listar los items del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-24
     */
    public function listar($page = 1, $orderby = null, $order = 'A')
    {
        $Contribuyente = $this->getContribuyente();
        $this->forceSearch(['contribuyente'=>$Contribuyente->rut]);
        parent::listar($page, $orderby, $order);
    }

    /**
     * Acción para crear un nuevo item
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-19
     */
    public function crear()
    {
        $Contribuyente = $this->getContribuyente();
        $_POST['contribuyente'] = $Contribuyente->rut;
        $this->set([
            'Contribuyente' => $Contribuyente,
            'clasificaciones' => (new Model_ItemClasificaciones())->getListByContribuyente($Contribuyente->rut),
            'impuesto_adicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())->getListContribuyente($Contribuyente->config_extra_impuestos_adicionales),
        ]);
        parent::crear();
    }

    /**
     * Acción para editar un item
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-19
     */
    public function editar($codigo, $tipo = 'INT1')
    {
        $Contribuyente = $this->getContribuyente();
        $_POST['contribuyente'] = $Contribuyente->rut;
        $this->set([
            'Contribuyente' => $Contribuyente,
            'clasificaciones' => (new Model_ItemClasificaciones())->getListByContribuyente($Contribuyente->rut),
            'impuesto_adicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())->getListContribuyente($Contribuyente->config_extra_impuestos_adicionales),
        ]);
        parent::editar($Contribuyente->rut, $tipo, $codigo);
    }

    /**
     * Acción para eliminar un item
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-24
     */
    public function eliminar($codigo, $tipo = 'INT1')
    {
        $Contribuyente = $this->getContribuyente();
        parent::eliminar($Contribuyente->rut, $tipo, $codigo);
    }

    /**
     * Recurso de la API que permite obtener los datos de un item a partir de su
     * código
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-25
     */
    public function _api_info_GET($empresa, $codigo)
    {
        extract($this->Api->getQuery(['fecha', 'tipo', 'bruto'=>false, 'moneda'=>'CLP']));
        // obtener usuario autenticado
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        // crear contribuyente y verificar que exista y tenga api configurada
        $Empresa = new \website\Dte\Model_Contribuyente($empresa);
        if (!$Empresa->exists())
            $this->Api->send('Empresa solicitada no existe', 404);
        // consultar item en servicio web del contribuyente
        if ($Empresa->config_api_url_items) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            if ($Empresa->config_api_auth_user) {
                if ($Empresa->config_api_auth_pass)
                    $rest->setAuth($Empresa->config_api_auth_user, $Empresa->config_api_auth_pass);
                else
                    $rest->setAuth($Empresa->config_api_auth_user);
            }
            $response = $rest->get($Empresa->config_api_url_items.$codigo);
            $this->Api->send($response['body'], $response['status']['code']);
        }
        // consultar item en base de datos local de LibreDTE
        else {
            $Item = (new Model_Itemes())->get($Empresa->rut, $codigo, $tipo);
            if (!$Item->exists() or !$Item->activo) {
                $this->Api->send('Item solicitado no existe o está inactivo', 404);
            }
            $this->Api->send([
                'TpoCodigo' => $Item->codigo_tipo,
                'VlrCodigo' => $Item->codigo,
                'NmbItem' => $Item->item,
                'DscItem' => $Item->descripcion,
                'IndExe' => $Item->exento,
                'UnmdItem' => $Item->unidad,
                'PrcItem' => $Item->getPrecio($fecha, $bruto, $moneda),
                'Moneda' => $moneda,
                'MntBruto' => (bool)$bruto,
                'ValorDR' => $Item->descuento,
                'TpoValor' => $Item->descuento_tipo,
                'CodImpAdic' => $Item->impuesto_adicional,
            ], 200, JSON_PRETTY_PRINT);
        }
    }

    /**
     * Acción que permite importar los items desde un archivo CSV
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-02
     */
    public function importar()
    {
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir el archivo con el libro
            if (!isset($_FILES['archivo']) or $_FILES['archivo']['error']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Ocurrió un error al subir el archivo con los items', 'error'
                );
                return;
            }
            // agregar cada item
            $Contribuyente = $this->getContribuyente();
            $items = \sowerphp\general\Utility_Spreadsheet::read($_FILES['archivo']);
            array_shift($items);
            $resumen = ['nuevos'=>[], 'editados'=>[], 'error'=>[]];
            $cols = ['codigo_tipo', 'codigo', 'item', 'descripcion', 'clasificacion', 'unidad', 'precio', 'moneda', 'exento', 'descuento', 'descuento_tipo', 'impuesto_adicional', 'activo'];
            $n_cols = count($cols);
            foreach ($items as $item) {
                // crear objeto
                $Item = new Model_Item();
                $Item->contribuyente = $Contribuyente->rut;
                for ($i=0; $i<$n_cols; $i++) {
                    $Item->{$cols[$i]} = $item[$i];
                }
                // corregir codigo
                $Item->codigo = str_replace('/', '_', $Item->codigo);
                // guardar
                try {
                    $existia = $Item->exists();
                    if ($Item->save()) {
                        if ($existia)
                            $resumen['editados'][] = $Item->codigo_tipo.'/'.$Item->codigo;
                        else
                            $resumen['nuevos'][] = $Item->codigo_tipo.'/'.$Item->codigo;
                    } else {
                        $resumen['error'][] = $Item->codigo_tipo.'/'.$Item->codigo;
                    }
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    $resumen['error'][] = $Item->codigo_tipo.'/'.$Item->codigo. '('.$e->getMessage().')';
                }
            }
            // mostrar errores o redireccionar
            if (!empty($resumen['error'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No se pudieron guardar todos los items:<br/>- nuevos: '.implode(', ', $resumen['nuevos']).
                        '<br/>- editados: '.implode(', ', $resumen['editados']).
                        '<br/>- con error: '.implode(', ', $resumen['error']),
                    ((empty($resumen['nuevos']) and empty($resumen['editados'])) ? 'error' : 'warning')
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se importó el listado de items', 'ok'
                );
                $this->redirect('/dte/admin/itemes/listar');
            }
        }
    }

}
