-- Indexes for the internal VMS Purchase Order, SPK and Kontrak lists.
-- Run on the VMS MySQL database (mysql7) once, preferably during low traffic.
-- Check existing indexes first; skip CREATE INDEX statements already present.
-- The matching Laravel migration also checks for existing equivalent indexes.

SHOW INDEX FROM `staging_po`;
SHOW INDEX FROM `staging_kontrak`;
SHOW INDEX FROM `staging_receipt_detail`;
SHOW INDEX FROM `orderheader`;
SHOW INDEX FROM `orderheaderkontrak`;
SHOW INDEX FROM `admin`;

CREATE INDEX `vms_po_listing` ON `staging_po` (`material_service`, `podate`, `id`, `cpny_id`);
CREATE INDEX `vms_po_vendor_listing` ON `staging_po` (`cpny_id`, `vendor_id`, `material_service`, `podate`, `id`);
CREATE INDEX `vms_kontrak_listing` ON `staging_kontrak` (`kontrakdate`, `id`, `cpny_id`);
CREATE INDEX `vms_kontrak_vendor_listing` ON `staging_kontrak` (`cpny_id`, `vendor_id`, `kontrakdate`, `id`);
CREATE INDEX `vms_po_receipt_qty` ON `staging_receipt_detail` (`cpny_id`, `ponbr`, `receiptqty`);
CREATE INDEX `vms_po_document_status` ON `orderheader` (`CpnyID`, `SecVendorID`, `OrderNo`, `id`, `Status`);
CREATE INDEX `vms_kontrak_document_status` ON `orderheaderkontrak` (`id_company`, `second_vendor_id`, `id_kontrak`, `id`, `status`);
CREATE INDEX `vms_po_purchaser` ON `admin` (`idacum`, `id`);
