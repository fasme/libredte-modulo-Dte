BEGIN;

--
-- Actualización al día 18 de septiembre de 2019
--

-- actualización tabla para boletas de terceros electrónicas
ALTER TABLE boleta_tercero ADD sucursal_sii INTEGER;

COMMIT;
