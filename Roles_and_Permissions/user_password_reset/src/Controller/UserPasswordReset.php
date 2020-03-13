<?php


namespace Drupal\user_password_reset\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class UserPasswordReset.
 */
class UserPasswordReset extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              RequestStack $requestStack,
                              LanguageManagerInterface $language_manager,
                              MessengerInterface $messenger,
                              LoggerInterface $logger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $requestStack;
    $this->languageManager = $language_manager;
    $this->messenger = $messenger;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('language_manager'),
      $container->get('messenger'),
      $container->get('logger.factory')->get('user')
    );
  }

  /**
   * Password Reset by link.
   */
  public function passReset() {
    $name = $this->requestStack->getCurrentRequest()->query->get('name');
    // TODO: Add destination.
    // $page_destination = $this->requestStack->getCurrentRequest()->query->get('destination');

    $langcode =  $this->languageManager->getCurrentLanguage()->getId();
    // Try to load by email.
    $users =  $this->entityTypeManager->getStorage('user')->loadByProperties(array('mail' => $name));
    if (empty($users)) {
      // No success, try to load by name.
      $users =  $this->entityTypeManager->getStorage('user')->loadByProperties(array('name' => $name));
    }
    $account = reset($users);
    // Mail one time login URL and instructions using current language.
    $mail = _user_mail_notify('password_reset', $account, $langcode);

    if (!empty($mail)) {
      $this->logger->notice('Password reset instructions mailed to %name at %email.', ['%name' => $account->getAccountName(), '%email' => $account->getEmail()]);
      $this->messenger->addStatus($this->t('Further instructions have been sent to your email address.'));
    }

    // TODO: Improve this part.
    $url = Url::fromUri('internal:/admin');
    return new RedirectResponse($url->toString());
  }

}
