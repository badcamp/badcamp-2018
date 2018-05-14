<?php

namespace Drupal\badcamp_stripe_payment\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class DonateRedirectSubscriber.
 */
class DonateRedirectSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;



  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['kernel.response'] = ['routing_route_alter'];
    return $events;
  }

  /**
   * This method is called whenever the routing.route_alter event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function routing_route_alter(Event $event) {
    $user = \Drupal::currentUser();
    $uid = $user->id();
    if($uid > 0) {
      $donations = badcamp_stripe_payment_get_donations($uid);
      if (count($donations) < 1) {
        $url = URL::fromRoute('badcamp_stripe_payment.donation_page_controller');
        $link = Link::fromTextAndUrl('here', $url);
        //drupal_set_message(t('BADCamp can only exist with the help of individuals like yourself. Please take a moment to make a donation @here.', ['@here' => $link->toString()]));
      }
    }
  }
}
