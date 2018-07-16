<?php

/**
 * @file
 * Contains stripe_payment.page.inc.
 *
 * Page callback for Stripe payment entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Stripe payment templates.
 *
 * Default template: stripe_payment.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_stripe_payment(array &$variables) {
  // Fetch StripePayment Entity Object.
  $stripe_payment = $variables['elements']['#stripe_payment'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
