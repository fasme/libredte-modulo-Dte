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
     * @version 2017-10-20
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
        // respaldo normal, se descarga inmediatamente
        if (isset($_POST['tablas'])) {
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

    /**
     * Acción que permite exportar todos los datos de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-14
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
        if (!$config or !class_exists('\Kunnu\Dropbox\DropboxApp')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Respaldos en Dropbox no están disponibles', 'error'
            );
            $this->redirect('/dte/admin');
        }
        // si no existe configuración para dropbox se muestra enlace
        if (!$Emisor->config_respaldos_dropbox) {
            $app = new \Kunnu\Dropbox\DropboxApp($config['key'], $config['secret']);
            $dropbox = new \Kunnu\Dropbox\Dropbox($app);
            $authHelper = $dropbox->getAuthHelper();
            $callbackUrl = $this->request->url.$this->request->request;
            // procesar codigo y estado de Dropbox para obtener token
            if (isset($_GET['code']) and isset($_GET['state'])) {
                $code = $_GET['code'];
                $state = $_GET['state'];
                try {
                    $accessToken = $authHelper->getAccessToken($code, $state, $callbackUrl);
                    $token = $accessToken->getToken();
                    $app = new \Kunnu\Dropbox\DropboxApp($config['key'], $config['secret'], $token);
                    $dropbox = new \Kunnu\Dropbox\Dropbox($app);
                    $account = $dropbox->getCurrentAccount();
                    $Emisor->set([
                        'config_respaldos_dropbox' => (object)[
                            'uid'=> $account->getAccountId(),
                            'display_name' => $account->getDisplayName(),
                            'email' => $account->getEmail(),
                            'token' => $token,
                        ]
                    ]);
                    $Emisor->save();
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Dropbox se ha conectado correctamente', 'ok'
                    );
                } catch (\Exception $e) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No fue posible conectar a Dropbox: '.$e->getMessage(), 'error'
                    );
                }
                $this->redirect('/dte/admin/respaldos/dropbox');
            }
            // mostrar enlace para conectar a Dropbox
            else {
                $authUrl = $authHelper->getAuthUrl($callbackUrl);
                $this->set([
                    'Emisor' => $Emisor,
                    'authUrl' => $authUrl,
                ]);
            }
        }
        // si existe configuración de dropbox se obtiene info del usuario y se valida si se quiere desconectar
        else {
            $app = new \Kunnu\Dropbox\DropboxApp($config['key'], $config['secret'], $Emisor->config_respaldos_dropbox->token);
            $dropbox = new \Kunnu\Dropbox\Dropbox($app);
            // desconectar LibreDTE de Dropbox
            if ($desconectar) {
                $authHelper = $dropbox->getAuthHelper();
                $authHelper->revokeAccessToken();
                $Emisor->set(['config_respaldos_dropbox' => null]);
                $Emisor->save();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Dropbox se ha desconectado correctamente', 'ok'
                );
                $this->redirect('/dte/admin/respaldos/dropbox');
            }
            // info usuario
            else {
                $this->set([
                    'Emisor' => $Emisor,
                    'account' => $dropbox->getCurrentAccount(),
                    'accountSpace' => $dropbox->getSpaceUsage(),
                ]);
            }
        }
    }

}
