--
-- Actualización al día 15 de septiembre de 2016
--

BEGIN;

ALTER TABLE dte_emitido ADD anulado BOOLEAN NOT NULL DEFAULT false;

COMMIT;
