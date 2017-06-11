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
 * Clase exportar e importar datos de un contribuyente
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-03
 */
class Controller_Respaldos extends \Controller_App
{

    /**
     * Acción que permite exportar todos los datos de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-10
     */
    public function exportar($all = false)
    {
        $Emisor = $this->getContribuyente();
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Sólo el administrador de la empresa puede descargar un respaldo', 'error'
            );
            $this->redirect('/dte/admin');
        }
        $Respaldo = new Model_Respaldo();
        $tablas = $Respaldo->getTablas();
        $this->set([
            'Emisor' => $Emisor,
            'tablas' => $tablas,
        ]);
        if ($all) {
            $_POST['tablas'] = [];
            foreach ($tablas as $t) {
                $_POST['tablas'][] = $t[0];
            }
        }
        if (isset($_POST['tablas'])) {
            // si existe el comando para programar respaldo y avisar por email se usa
            if (class_exists('\website\Dte\Shell_Command_Respaldos_Email')) {
                if ($this->shell('Dte.Respaldos_Email '.$Emisor->rut)) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No fue posible programar el respaldo', 'error'
                    );
                } else {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'El respaldo está siendo generado, se notificará vía correo electrónico cuando esté listo', 'ok'
                    );
                }
                $this->redirect('/dte');
            }
            // respaldo normal, se descarga inmediatamente
            else {
                try {
                    $dir = $Respaldo->generar($Emisor->rut, $_POST['tablas']);
                    \sowerphp\general\Utility_File::compress(
                        $dir, ['format'=>'zip', 'delete'=>true]
                    );
                } catch (\Exception $e) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No fue posible exportar los datos: '.$e->getMessage(), 'error'
                    );
                }
            }
        }
    }

    /**
     * Acción que permite exportar todos los datos de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-10
     */
    public function dropbox($desconectar = false)
    {
        $Emisor = $this->getContribuyente();
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Sólo el administrador de la empresa puede configurar Dropbox', 'error'
            );
            $this->redirect('/dte/admin');
        }
        // verificar que exista soporta para usar Dropbox
        $config = \sowerphp\core\Configure::read('backup.dropbox');
        if (!$config or !class_exists('\Dropbox\AppInfo')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Respaldos en Dropbox no están disponibles', 'error'
            );
            $this->redirect('/dte/admin');
        }
        // conectar a API de dropbox
        $appInfo = new \Dropbox\AppInfo($config['key'], $config['secret']);
        $csrfTokenStore = new \Dropbox\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
        $webAuth = new \Dropbox\WebAuth($appInfo, 'LibreDTE/1.0', $this->request->url.$this->request->request, $csrfTokenStore);
        // procesar código si fue pasado
        if (!empty($_GET['state']) and !empty($_GET['code'])) {
            try {
                list($accessToken, $userId, $urlState) = $webAuth->finish($_GET);
                assert($urlState === null);
                $dbxClient = new \Dropbox\Client($accessToken, 'LibreDTE/1.0');
                $accountInfo = $dbxClient->getAccountInfo();
                $Emisor->set([
                    'config_respaldos_dropbox' => (object)[
                        'uid'=> $accountInfo['uid'],
                        'display_name' => $accountInfo['display_name'],
                        'email' => $accountInfo['email'],
                        'token'=>$accessToken,
                    ]
                ]);
                $Emisor->save();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Dropbox se ha conectado correctamente', 'ok'
                );
                $this->redirect('/dte/admin/respaldos/dropbox');
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $e->getMessage(), 'error'
                );
            }
        }
        // tratar de obtener datos del usuario si es que existe token
        if ($Emisor->config_respaldos_dropbox) {
            try {
                $dbxClient = new \Dropbox\Client($Emisor->config_respaldos_dropbox->token, 'LibreDTE/1.0');
                $accountInfo = $dbxClient->getAccountInfo();
            } catch (\Dropbox\Exception_InvalidAccessToken $e) {
                $desconectar = true;
            }
        }
        // desconectar LibreDTE de Dropbox
        if ($desconectar) {
            $Emisor->set(['config_respaldos_dropbox' => null]);
            $Emisor->save();
            \sowerphp\core\Model_Datasource_Session::message(
                'Dropbox se ha desconectado correctamente', 'ok'
            );
            $this->redirect('/dte/admin/respaldos/dropbox');
        }
        // asignar variables para la vista
        $this->set('Emisor', $Emisor);
        if (!$Emisor->config_respaldos_dropbox) {
            $this->set('authorizeUrl', $webAuth->start());
        } else {
            $this->set('accountInfo', $accountInfo);
        }
    }

}
