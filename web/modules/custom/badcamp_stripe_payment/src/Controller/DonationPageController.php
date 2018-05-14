<?php

namespace Drupal\badcamp_stripe_payment\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DonationPageController.
 */
class DonationPageController extends ControllerBase {

  /**
   * Displaycontent.
   *
   * @return string
   *   Return Hello string.
   */
  public function displayContent() {
    return [
      '#type' => 'markup',
      '#markup' => ''
    ];
  }

}
