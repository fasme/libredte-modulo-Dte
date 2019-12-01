BEGIN;

--
-- Actualización al día 01 de enero de 2020
--

-- actualización tabla emitidos y recibidos
ALTER TABLE dte_emitido ADD fecha_hora_creacion TIMESTAMP WITHOUT TIME ZONE;
ALTER TABLE dte_recibido ADD fecha_hora_creacion TIMESTAMP WITHOUT TIME ZONE;

COMMIT;
