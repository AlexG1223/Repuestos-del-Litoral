-- ============================================================
-- Repuestos del Litoral — Migración Fase 3 (Usuarios y Mayoristas)
-- ============================================================

ALTER TABLE users
  ADD COLUMN business_name VARCHAR(150) NULL AFTER phone;
