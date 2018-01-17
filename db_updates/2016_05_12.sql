ALTER TABLE `ps_product` ADD `show_gift_label` BOOLEAN NOT NULL ;
ALTER TABLE `ps_product_attribute` CHANGE `reference` `reference` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `ps_product` CHANGE `reference` `reference` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
