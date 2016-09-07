<?php
/*-------------------------------------------------------+
| Calculate Event Fees                                   |
| Copyright (C) 2016 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

/**
 * Replicating CRM_Event_Form_Registration_Register::buildAmount
 */
function civicrm_api3_event_calculatefees($params) {
  $event_id = (int) $params['event_id'];
  $price_set_id = NULL;

  // first check if there is an active discount
  $discount_id = CRM_Core_BAO_Discount::findSet($event_id, 'civicrm_event');
  if ($discount_id) {
    // load discounts, extract price set
    $discounts = CRM_Core_BAO_Discount::getOptionGroup($event_id, 'civicrm_event');
    $price_set_id = $discounts[$discount_id];
  }

  // if no discount found: use the default
  if (empty($price_set_id)) {
    $price_set_id = CRM_Price_BAO_PriceSet::getFor('civicrm_event', $event_id);  
  }

  // verify that we have a price set
  if (empty($price_set_id)) {
    return civicrm_api3_create_error("No price set found for event [{$event_id}]");
  }

  // then load the valid price set
  $price_set = CRM_Price_BAO_PriceSet::getSetDetail($price_set_id);

  // verify that we have a price set
  if (empty($price_set)) {
    return civicrm_api3_create_error("Price set [{price_set_id}] doesn't exist.");
  }

  return civicrm_api3_create_success($price_set[$price_set_id]['fields']);
}

/**
 * API3 action specs
 */
function _civicrm_api3_event_calculatefees_spec(&$params) {
  $params['event_id']['api.required'] = 1;
}