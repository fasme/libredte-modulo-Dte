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
namespace website\Dte;

/**
 * Clase base para para el modelo singular de Libros
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-06-13
 */
abstract class Model_Base_Libro extends Model_Base_Envio
{

    /**
     * Método que guarda el estado del envío del libro al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-27
     */
    public function saveRevision($xml_data)
    {
        $xml = new \SimpleXMLElement($xml_data, LIBXML_COMPACT);
        if ($xml->Identificacion->TrackId!=$this->track_id)
            return 'Track ID no corresponde al envío del Libro';
        $this->revision_estado = (string)$xml->Identificacion->EstadoEnvio;
        if (isset($xml->ErrorEnvioLibro)) {
            if (is_string($xml->ErrorEnvioLibro->DetErrEnvio))
                $error = [$xml->ErrorEnvioLibro->DetErrEnvio];
            else
                $error = (array)$xml->ErrorEnvioLibro->DetErrEnvio;
            $this->revision_detalle = implode("\n\n", $error);
        } else {
            $this->revision_detalle = null;
        }
        try {
            $this->save();
            return true;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return $e->getMessage();
        }
    }

    /**
     * Método que entrega el estado (de 3 letras) del envío del libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-02
     */
    public function getEstado()
    {
        if (!$this->revision_estado)
            return null;
        return substr($this->revision_estado, 0, 3);
    }

    /**
     * Método que entrega el arreglo con los datos del libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-13
     */
    public function getDatos()
    {
        $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        $LibroCompraVenta->loadXML(base64_decode($this->xml));
        return $LibroCompraVenta->toArray();
    }

}
