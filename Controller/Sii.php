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
 * Controlador para acciones del SII
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-10-12
 */
class Controller_Sii extends \Controller_App
{

    /**
     * Acción que permite consultar el estado de un envío en el SII a partir del Track ID del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-08-06
     */
    public function estado_envio($track_id)
    {
        // si existe el proveedor libredte se consulta al servicio web de LibreDTE oficial
        if (\sowerphp\core\Configure::read('proveedores.api.libredte')) {
            $Emisor = $this->getContribuyente();
            $Firma = $Emisor->getFirma($this->Auth->User->id);
            $data = [
                'firma' => [
                    'cert-data' => $Firma->getCertificate(),
                    'key-data' => $Firma->getPrivateKey(),
                ],
            ];
            $certificacion = (int)$Emisor->config_ambiente_en_certificacion;
            $response = libredte_consume(
                '/sii/dte_emitido_estado_envio/'.$Emisor->getRUT().'/'.$track_id.'&certificacion='.$certificacion.'&formato=web',
                $data
            );
            echo $response['body'];
            exit;
        }
        // se crea enlace directo al SII
        else {
            $this->query('QEstadoEnvio2', ['TrackId' => $track_id, 'NPagina' => 1]);
        }
    }

    /**
     * Acción que permite verificar los datos de un DTE en el SII a partir de los datos generales del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function verificar_datos($receptor, $dte, $folio, $fecha, $total, $emisor = null)
    {
        list($receptor_rut, $receptor_dv) = explode('-', $receptor);
        list($emisor_rut, $emisor_dv) = $emisor ? explode('-', $emisor) : [null, null];
        $this->query('QEstadoDTE', [
            'rutReceiver' => str_replace('.', '', $receptor_rut),
            'dvReceiver' => $receptor_dv,
            'tipoDTE' => $dte,
            'folioDTE' => $folio,
            'fechaDTE' => \sowerphp\general\Utility_Date::format($fecha, 'dmY'),
            'montoDTE' => $total,
            'rutCompany' => $emisor_rut ? str_replace('.', '', $emisor_rut) : null,
            'dvCompany' => $emisor_dv,
        ]);
    }

    /**
     * Método que realiza la consulta al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    private function query($query, $params)
    {
        $Emisor = $this->getContribuyente();
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe firma asociada', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        list($rutQuery, $dvQuery) = explode('-', $Firma->getId());
        $servidor = \sasco\LibreDTE\Sii::getServidor();
        if (empty($params['rutCompany'])) {
            $params['rutCompany'] = $Emisor->rut;
            $params['dvCompany'] = $Emisor->dv;
        }
        $url = 'https://'.$servidor.'.sii.cl/cgi_dte/UPL/'.$query.'?rutQuery='.$rutQuery.'&amp;dvQuery='.$dvQuery;
        foreach ($params as $k => $v) {
            $url .= '&amp;'.$k.'='.$v;
        }
        // renderizar vista
        $this->set('url', $url);
        $this->layout = null;
        $this->autoRender = false;
        $this->render('Sii/query');
    }

}
