DELETE FROM `ps_zone_shop` WHERE id_zone IN (SELECT id_zone FROM ps_zone WHERE UCASE(name) LIKE '%PACKLINK%');
DELETE FROM `ps_zone` WHERE UCASE(name) LIKE '%PACKLINK%';

DROP TABLE `ps_country`;
RENAME TABLE `ps_country_no_pl` TO `ps_country`;

DELETE FROM `ps_range_price` WHERE id_carrier IN (SELECT id_carrier FROM ps_carrier WHERE UCASE(name) LIKE '%PACKLINK%');
DELETE FROM `ps_range_weight` WHERE id_carrier IN (SELECT id_carrier FROM ps_carrier WHERE UCASE(name) LIKE '%PACKLINK%');

DROP TABLE   `ps_product_carrier`;
RENAME TABLE `ps_product_carrier_no_pl` TO `prestashop`.`ps_product_carrier` ;

DELETE FROM `ps_carrier_lang` WHERE id_carrier IN (SELECT id_carrier FROM ps_carrier WHERE UCASE(name) LIKE '%PACKLINK%'); 
DELETE FROM `ps_carrier_shop` WHERE id_carrier IN (SELECT id_carrier FROM ps_carrier WHERE UCASE(name) LIKE '%PACKLINK%');
DELETE FROM `ps_carrier_zone` WHERE id_carrier IN (SELECT id_carrier FROM ps_carrier WHERE UCASE(name) LIKE '%PACKLINK%');
DELETE FROM `ps_carrier` WHERE UCASE(name) LIKE '%PACKLINK%';

DROP TABLE  `ps_packlink_config`;
DROP TABLE  `ps_packlink_orders`;