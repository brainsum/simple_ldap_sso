<?php

namespace Drupal\ldap_sso_auth\RequestPolicy\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\Session\SessionConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A policy allowing delivery of cached pages when there is no session open.
 *
 * Do not serve cached pages if LDAP SSO header variable contain name.
 *
 * Do not serve cached pages to authenticated users, or to anonymous users when
 * $_SESSION is non-empty. $_SESSION may contain status messages from a form
 * submission, the contents of a shopping cart, or other userspecific content
 * that should not be cached and displayed to other users.
 */
class LdapSsoAuthLoginName implements RequestPolicyInterface {

  /**
   * The session configuration.
   *
   * @var \Drupal\Core\Session\SessionConfigurationInterface
   */
  protected $sessionConfiguration;

  /**
   * The services container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Constructs a new page cache session policy.
   *
   * @param \Drupal\Core\Session\SessionConfigurationInterface $session_configuration
   *   The session configuration.
   */
  public function __construct(SessionConfigurationInterface $session_configuration, ContainerInterface $container) {
    $this->sessionConfiguration = $session_configuration;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if (!$this->sessionConfiguration->hasSession($request)) {
      // No session stored check header sso variable of user name.
      $sso_variable = $this->container->get('config.factory')
        ->get('ldap_sso_auth.settings')
        ->get('ssoVariable');
      // $request->server->set($sso_variable, 'riemann'); // For testing.
      if ($request->server->get($sso_variable) === NULL) {
        // Not contains name in sso variable allow to get page from cache.
        return static::ALLOW;
      }
    }
    // Session stored or sso variable contains name cached page disallow.
    return static::DENY;
  }

}
