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
 * Clase para las acciones asociadas al libro de boletas electrónicas
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-14
 */
class Controller_DteBoletaConsumos extends \Controller_Maintainer
{

    protected $namespace = __NAMESPACE__; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar'=>['dia', 'secuencia', 'glosa', 'track_id', 'revision_estado', 'revision_detalle']
    ]; ///< Columnas que se deben mostrar en las vistas
    protected $deleteRecord = false; ///< Indica si se permite o no borrar registros
    protected $actionsColsWidth = 190; ///< Ancho de columna para acciones en vista listar

    /**
     * Acción principal que lista los períodos con boletas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function listar($page = 1, $orderby = null, $order = 'A')
    {
        $Emisor = $this->getContribuyente();
        $this->forceSearch(['emisor'=>$Emisor->rut, 'certificacion'=>(int)$Emisor->config_ambiente_en_certificacion]);
        parent::listar($page, $orderby, $order);
    }

    /**
     * Acción que permite enviar el reporte de consumo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function crear()
    {
        $from_unix_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $day_before = strtotime('yesterday', $from_unix_time);
        $this->set('dia', date('Y-m-d', $day_before));
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/dte_boleta_consumos/enviar_sii/'.$_POST['dia'].'?listar='.$_GET['listar']);
        }
    }

    /**
     * Acción para prevenir comportamiento por defecto del mantenedor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function editar($pk)
    {
        \sowerphp\core\Model_Datasource_Session::message(
            'No se permite la edición de registros', 'error'
        );
        $this->redirect('/dte/dte_boleta_consumos/listar/1/dia/D');
    }

    /**
     * Acción para descargar reporte de consumo de folios en XML
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function xml($dia)
    {
        $Emisor = $this->getContribuyente();
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, (int)$Emisor->config_ambiente_en_certificacion);
        $xml = $DteBoletaConsumo->getXML();
        if (!$xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el reporte de consumo de folios<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar');
        }
        // entregar XML
        $file = 'consumo_folios_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$dia.'.xml';
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        print $xml;
        exit;
    }

    /**
     * Acción que permite enviar el consumo de folios al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function enviar_sii($dia)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        if ($dia>=date('Y-m-d')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Sólo se pueden enviar consumos de folios de días pasados', 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        $Emisor = $this->getContribuyente();
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, (int)$Emisor->config_ambiente_en_certificacion);
        $track_id = $DteBoletaConsumo->enviar();
        if (!$track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible enviar el reporte de consumo de folios al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'Reporte de consumo de folios del día '.$dia.' fue envíado al SII. Ahora debe consultar su estado con el Track ID '.$track_id, 'ok'
            );
        }
        $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
    }

    /**
     * Acción que actualiza el estado del envío del reporte de consumo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function actualizar_estado($dia)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        $Emisor = $this->getContribuyente();
        // obtener reporte enviado
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteBoletaConsumo->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el reporte de consumo de folios solicitado', 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        // si no tiene track id error
        if (!$DteBoletaConsumo->track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Reporte de consumo de folios no tiene Track ID, primero debe enviarlo al SII', 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        // buscar correo con respuesta
        $Imap = $Emisor->getEmailImap('sii');
        if (!$Imap) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible conectar mediante IMAP a '.$Emisor->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors()), 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        $asunto = 'TipoEnvio=Automatico TrackID='.$DteBoletaConsumo->track_id.' Rut='.$Emisor->rut.'-'.$Emisor->dv;
        $uids = $Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        if (!$uids) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No se encontró respuesta de envío del reporte de consumo de folios, espere unos segundos'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        // procesar emails recibidos
        foreach ($uids as $uid) {
            $estado = $detalle = null;
            $m = $Imap->getMessage($uid);
            if (!$m)
                continue;
            foreach ($m['attachments'] as $file) {
                if ($file['type']!='application/xml')
                    continue;
                $xml = new \SimpleXMLElement($file['data'], LIBXML_COMPACT);
                // obtener estado y detalle
                if (isset($xml->DocumentoResultadoConsumoFolios)) {
                    if ($xml->DocumentoResultadoConsumoFolios->Identificacion->Envio->TrackId==$DteBoletaConsumo->track_id) {
                        $estado = (string)$xml->DocumentoResultadoConsumoFolios->Resultado->Estado;
                        $detalle = str_replace('T', ' ', (string)$xml->DocumentoResultadoConsumoFolios->Identificacion->Envio->TmstRecepcion);
                    }
                }
            }
            if (isset($estado)) {
                $DteBoletaConsumo->revision_estado = $estado;
                $DteBoletaConsumo->revision_detalle = $detalle;
                try {
                    $DteBoletaConsumo->save();
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Se actualizó el estado del reporte de consumo de folios', 'ok'
                    );
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage(), 'error'
                    );
                }
                // marcar email como leído
                $Imap->setSeen($uid);
            }
        }
        // redireccionar
        $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
    }

    /**
     * Acción que actualiza el estado del envío del reporte de consumo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function solicitar_revision($dia)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        $Emisor = $this->getContribuyente();
        // obtener reporte enviado
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteBoletaConsumo->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el reporte de consumo de folios solicitado', 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        try {
            $DteBoletaConsumo->solicitarRevision($this->Auth->User->id);
            \sowerphp\core\Model_Datasource_Session::message(
                'Se solicitó revisión del consumo de folios', 'ok'
            );
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage, 'error');
        }
        // redireccionar
        $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
    }

}
