# Placeholder file for future updates to the database schema;

INSERT INTO `#__wbty_payments_gateways` (
`id` , `ordering` , `state` , `checked_out` , `checked_out_time` , `created_by` , `created_time` , `modified_by` , `modified_time` , `name` , `alias` , `default_gateway` , `type` )
VALUES (
'6' , '', '1', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'Stripe', 'stripe', '', ''
);


INSERT INTO `#__wbty_payments_gateway_fields` (`id`, `ordering`, `state`, `checked_out`, `checked_out_time`, `created_by`, `created_time`, `modified_by`, `modified_time`, `field_name`, `gateway_id`) VALUES
(20, 0, 1, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 'Test Secret Key', 6),
(21, 0, 1, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 'Test Publishable Key', 6),
(22, 0, 1, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 'Live Secret Key', 6),
(23, 0, 1, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 'Live Publishable Key', 6);