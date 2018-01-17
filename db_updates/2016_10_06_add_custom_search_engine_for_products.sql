CREATE TABLE `ps_search_indexes` (
  `id_search_index` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) DEFAULT NULL,
  `id_product_attribute` int(11) DEFAULT NULL,
  `text` text,
  PRIMARY KEY (`id_search_index`),
  FULLTEXT KEY `search_text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ps_search_indexes`
(
`id_product`,
`id_product_attribute`,
`text`)
SELECT
    pa.id_product,
    pa.id_product_attribute,
    concat_ws(' ',
        replace(pa.reference, '-', ' '),
        replace(pa.title, '-', ' '),
        pl.name,
        m.name
    )
FROM
    ps_product_attribute pa
LEFT JOIN
    ps_product p ON p.id_product = pa.id_product
LEFT JOIN
    ps_product_lang pl ON pl.id_product = pa.id_product
LEFT JOIN
    ps_manufacturer m ON m.id_manufacturer = p.id_manufacturer
WHERE
    pa.hide = 0
    AND p.visibility IN ('both', 'search');


INSERT INTO `spinkat`.`ps_search_indexes`
(
`id_product`,
`id_product_attribute`,
`text`)
SELECT
    p.id_product,
    0,
    concat_ws(' ',
        replace(p.reference, '-', ' '),
        pl.name,
        m.name
    )
FROM
    ps_product p
LEFT JOIN
    ps_product_lang pl ON pl.id_product = p.id_product
LEFT JOIN
    ps_manufacturer m ON m.id_manufacturer = p.id_manufacturer
WHERE
    p.visibility IN ('both', 'search');
