-- Add city column to tb_project for Elementor template placeholder $__city__!
-- Placed after `address` to keep location fields grouped.
ALTER TABLE `tb_project`
    ADD COLUMN `city` VARCHAR(100) NULL DEFAULT NULL AFTER `address`;
