USE wms_estoque;

ALTER TABLE stock_movements ADD COLUMN email_requested ENUM('SIM','NAO') NOT NULL DEFAULT 'NAO' AFTER reason;
ALTER TABLE stock_movements ADD COLUMN email_sent ENUM('SIM','NAO') NOT NULL DEFAULT 'NAO' AFTER email_requested;

CREATE TABLE receipts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id BIGINT UNSIGNED NOT NULL,
  invoice_number VARCHAR(80) NOT NULL,
  invoice_total DECIMAL(12,2) NOT NULL,
  received_at DATE NOT NULL,
  supplier_name VARCHAR(160) NOT NULL,
  vehicle VARCHAR(120) NOT NULL,
  driver_name VARCHAR(160) NOT NULL,
  license_plate VARCHAR(16) NOT NULL,
  received_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_receipt_item FOREIGN KEY (item_id) REFERENCES items(id),
  CONSTRAINT fk_receipt_user FOREIGN KEY (received_by) REFERENCES users(id)
);

CREATE TABLE email_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  movement_id BIGINT UNSIGNED NULL,
  recipient VARCHAR(160) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  status ENUM('SIM','NAO') NOT NULL DEFAULT 'NAO',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notification_movement FOREIGN KEY (movement_id) REFERENCES stock_movements(id) ON DELETE CASCADE
);
