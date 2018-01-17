ALTER TABLE `ps_product_attribute` ENGINE=MyISAM;
ALTER TABLE  `ps_product_attribute` ENABLE KEYS;
ALTER TABLE  `ps_product_attribute` ADD FULLTEXT (`title`);
ALTER TABLE  `ps_product_attribute` ADD FULLTEXT (`reference`);