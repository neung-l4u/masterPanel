-- Adds storage for the Push POS Customer Agreement URL.
--
-- Customers who buy POS receive two agreements: the existing marketing
-- agreement (dataContract) and the Push POS Customer Agreement (this column).
-- Kept as a separate column rather than changing dataContract's format, because
-- dataContract is read as a bare URL in pages/tableRendering/*.php.
--
-- Nullable and additive: existing rows and existing readers are unaffected.

ALTER TABLE `logssignup`
    ADD COLUMN `dataContractPushpos` longtext NULL DEFAULT NULL AFTER `dataContract`;
