--
-- Actualización al día 1 de octubre de 2016
--

BEGIN;

ALTER TABLE dte_emitido ADD anulado BOOLEAN NOT NULL DEFAULT false;
ALTER TABLE dte_intercambio_resultado_dte ALTER glosa DROP NOT NULL;

COMMIT;
