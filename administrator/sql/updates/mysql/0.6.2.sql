# Update order item descriptions to better contain long descriptions with HTML;
ALTER TABLE `#__wbty_payments_coupons` ADD `discount_type` INT(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__wbty_payments_order_items` ADD `quantity` int(11) NOT NULL;
ALTER TABLE `#__wbty_payments_orders` ADD `billing_address` int(11) NOT NULL,
    `shipping_address` int(11) NOT NULL;

CREATE TABLE IF NOT EXISTS `#__wbty_payments_shipping` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shipping_price` float NOT NULL,
  `min_value` float NOT NULL,
  `max_value` float NOT NULL,
  `narrow_by_state` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__wbty_payments_taxes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `percent` float NOT NULL,
  `applicable_state` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
