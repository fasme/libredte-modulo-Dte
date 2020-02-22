BEGIN;

--
-- Actualización 20.04
--

-- actualización tabla emitidos y recibidos para soporte DTE de MIPYME
ALTER TABLE dte_emitido ALTER COLUMN xml DROP NOT NULL;
ALTER TABLE dte_emitido ADD mipyme BIGINT;
ALTER TABLE dte_recibido ADD mipyme BIGINT;

COMMIT;
