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
 * @version 2018-11-07
 */
class Controller_DteBoletas extends \Controller_App
{

    /**
     * Acción principal que lista los períodos con boletas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-07
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'periodos' => $Emisor->getResumenBoletasPeriodos(),
        ]);
    }

    /**
     * Acción para descargar libro de boletas en XML
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-07-28
     */
    public function xml($periodo, $FolioNotificacion = 1)
    {
        $Emisor = $this->getContribuyente();
        $boletas = $Emisor->getBoletas($periodo);
        $Libro = new \sasco\LibreDTE\Sii\LibroBoleta();
        $Libro->setFirma($Emisor->getFirma());
        foreach ($boletas as $boleta) {
            $Libro->agregar([
                'TpoDoc' => $boleta['dte'],
                'FolioDoc' => $boleta['folio'],
                //'Anulado' => $boleta['anulada'] ? 'A' : false,
                'FchEmiDoc' => $boleta['fecha'],
                'RUTCliente' => $boleta['rut'],
                'MntExe' => $boleta['exento'] ? $boleta['exento'] : false,
                'MntTotal' => $boleta['total'],
            ]);
        }
        $Libro->setCaratula([
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'FchResol' => $Emisor->config_ambiente_en_certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' =>  $Emisor->config_ambiente_en_certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
            'FolioNotificacion' => $FolioNotificacion,
        ]);
        $xml = $Libro->generar();
        if (!$Libro->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el libro de boletas<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_boletas');
        }
        // entregar XML
        $file = 'boletas_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.xml';
        header('Content-Type: application/xml; charset=ISO-8859-1');
        header('Content-Length: '.strlen($xml));
        header('Content-Disposition: attachement; filename="'.$file.'"');
        print $xml;
        exit;
    }

    /**
     * Acción para descargar libro de boletas en CSV
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-07
     */
    public function csv($periodo)
    {
        $Emisor = $this->getContribuyente();
        $boletas = $Emisor->getBoletas($periodo);
        $Libro = new \sasco\LibreDTE\Sii\LibroBoleta();
        $Libro->setFirma($Emisor->getFirma());
        foreach ($boletas as $boleta) {
            $Libro->agregar([
                'TpoDoc' => $boleta['dte'],
                'FolioDoc' => $boleta['folio'],
                //'Anulado' => $boleta['anulada'] ? 'A' : false,
                'FchEmiDoc' => $boleta['fecha'],
                'RUTCliente' => $boleta['rut'],
                'MntExe' => $boleta['exento'] ? $boleta['exento'] : false,
                'MntTotal' => $boleta['total'],
                // oficialmente no son parte del libro estos campos, pero se entregan
                // ya que permiten tener mayor información de la boleta. igualmente
                // se podrían calcular a partir del monto exento y monto total pero se
                // dejan porque son útiles en el archivo para ser usados en el reporte
                // de consumo de folios
                'MntNeto' =>$boleta['neto'],
                'MntIVA' => $boleta['iva'],
                'TasaImp' => $boleta['tasa'],
            ]);
        }
        unset($boletas);
        $detalle = $Libro->getDetalle();
        // entregar XML
        $file = 'boletas_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo;
        if ($detalle) {
            array_unshift($detalle, array_keys($detalle[0]));
        }
        \sowerphp\general\Utility_Spreadsheet_CSV::generate($detalle, $file);
    }

}
