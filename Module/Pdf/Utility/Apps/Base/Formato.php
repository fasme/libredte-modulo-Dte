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

// namespace del modelo
namespace website\Dte\Pdf;

/**
 * Clase abstracta para las aplicaciones de formatos de PDF
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-08-02
 */
abstract class Utility_Apps_Base_Formato extends \sowerphp\app\Utility_Apps_Base_Apps
{

    /**
     * Método que entrega el código HTML de la página de configuración de la aplicación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-02
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form)
    {
        $buffer = '';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_disponible',
            'label' => '¿Disponible?',
            'options' => ['No', 'Si'],
            'value' => (int)(!empty($this->getConfig()->disponible)),
            'help' => '¿Está disponible este formato de PDF?',
        ]);
        return $buffer;
    }

    /**
     * Método que asigna la configuración de la aplicación procesando el formulario enviado por POST
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-02
     */
    public function setConfigPOST()
    {
        // asignar configuración
        if (!empty($_POST['dtepdf_'.$this->getCodigo().'_disponible'])) {
            $_POST['config_dtepdfs_'.$this->getCodigo()] = $this->createConfig('dtepdf_'.$this->getCodigo());
        }
        // eliminar configuración de la aplicación porque no está disponible
        // esto se puede hacer porque los PDF se pueden desactivar en el mapeo y no en su propia configuración
        else {
            $_POST['config_dtepdfs_'.$this->getCodigo()] = null;
        }
        // entregar configuración compartida
        return $_POST['config_dtepdfs_'.$this->getCodigo()];
    }

    /**
     * Método que crea la configuración de manera automágica :)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-02
     */
    private function createConfig(string $id): array
    {
        $config = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, $id.'_') === 0) {
                $name = str_replace($id.'_', '', $key);
                $parts = explode('_', $name, 4);
                switch (count($parts)) {
                    case 1:
                        $config[$parts[0]] = trim($value);
                        break;
                    case 2:
                        $config[$parts[0]][$parts[1]] = trim($value);
                        break;
                    case 3:
                        $config[$parts[0]][$parts[1]][$parts[2]] = trim($value);
                        break;
                    case 4:
                        $config[$parts[0]][$parts[1]][$parts[2]][$parts[3]] = trim($value);
                        break;
                }
                unset($_POST[$key]);
            }
        }
        return $config;
    }

    /**
     * Método que genera el PDF consumiendo el servicio web de la API de LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-04
     */
    public function generar($config)
    {
        // generar PDF localmente
        $class = '\sasco\LibreDTE\Extra\Sii\Dte\PDF\Formatos\\'.\sowerphp\core\Utility_Inflector::camelize($config['formato']);
        if (class_exists($class)) {
            try {
                $data = (new $class($config))->generar();
                return [
                    'status' => [
                        'code' => 200,
                    ],
                    'body' => $data,
                ];
            } catch (\Exception $e) {
                return [
                    'status' => [
                        'code' => $e->getCode() ? $e->getCode() : 500,
                    ],
                    'body' => $e->getMessage(),
                ];
            }
        }
        // generar PDF mediante funcionalidades extras
        return libredte_api_consume('/libredte/dte/documentos/pdf', $config);
    }

}
