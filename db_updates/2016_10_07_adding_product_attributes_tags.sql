ALTER TABLE `ps_tag`
    ADD `title_for_seo_h1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    ADD `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    ADD `meta_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    ADD `meta_description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    ADD `link_rewrite` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `ps_product_tag`
    ADD `id_product_attribute` INT(10) UNSIGNED NOT NULL AFTER `id_product`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id_product`, `id_tag`, `id_product_attribute`);
