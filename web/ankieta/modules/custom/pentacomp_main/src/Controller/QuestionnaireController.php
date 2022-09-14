<?php

namespace Drupal\pentacomp_main\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\pentacomp_main\Service\TokenQuestionnaireManager;

/**
 * Returns responses for Node routes.
 */
class QuestionnaireController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The renderer service.
   *
   * @var Drupal\Core\Session\AccountProxyInterface  
   */
  protected $current_user;

  /**
   * The renderer service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface  
   */
  protected $entity_type_manager;

  const SETTINGS = 'pentacomp_main.settings';

  /**
   * Constructs a AdminToolsController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\pentacomp_main\Service\TokenQuestionnaireManager $token_manager
   *   The training service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   aaaaa
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, AccountProxyInterface  $current_user, ConfigFactoryInterface $config_factory,
  TokenQuestionnaireManager $token_manager) {
    $this->entity_type_manager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->current_user = $current_user;
    $this->config_factory = $config_factory;
    $this->token_manager = $token_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('pentacomp_main.token.tools')
    );
  }

  /**
   * Render page.
   */
  public function renderPage() {
    $build = [];
    $token = isset($_GET['t']) ? $_GET['t'] : NULL;
    $bug = FALSE;
    if (!isset($token)) {
      $bug = TRUE;
    } else {
      $config = $this->config_factory->get(static::SETTINGS);
      $dbData = $this->token_manager->getZid($config, $token);
    
      if ($dbData[2] == NULL) {
        $bug = TRUE;
      } else {
        if ($dbData[1] == 'exist') {
          $build['#title'] = t('Ta ankieta była już przeprowadzona.');
          $valueCacheableDependency = [false,  $token, 'exist', time()];
          $this->renderer->addCacheableDependency($build, $valueCacheableDependency);
          $config = $this->config_factory->get(static::SETTINGS);
          $activePollId =  $config->get('poll');
          $webform = $this->entity_type_manager->getStorage('webform')->load($activePollId);
          $build[$activePollId] = $webform->getSubmissionForm();

          foreach($dbData['data'] as $key => $value){
            $build[$activePollId]['elements'][$value->name]['#attributes']['disabled'] = 'disabled';
            if ($build[$activePollId]['elements'][$value->name]['#type'] == 'radios') {
              $build[$activePollId]['elements'][$value->name][$value->value]['#value'] = $value->value;
            } 
            else {
              $build[$activePollId]['elements'][$value->name]['#value'] = $value->value;
            }
          }

          unset($build[$activePollId]['elements']['actions']); 
        } else {
          $config = $this->config_factory->get(static::SETTINGS);
          $activePollId =  $config->get('poll');
          $webform = $this->entity_type_manager->getStorage('webform')->load($activePollId);
          $build['#title'] = $webform->label();
          $build[$activePollId] = [
            '#type' => 'webform',
            '#webform' => $activePollId,
            '#default_data' => [
              'token_id' => $dbData[2],
              'pentacomp_token' => $token,
            ],
          ];
          $valueCacheableDependency = [false,  $token, 'empty', time()];
          $this->renderer->addCacheableDependency($build, $valueCacheableDependency);
        }
      }
    }

    if ($bug){
      $build['#title'] = t('Brak identyfikatora zgłoszenia.');
      $valueCacheableDependency = [false,  $token, 'bug', time()];
      $this->renderer->addCacheableDependency($build, $valueCacheableDependency);
    }
    return $build;
  }


}
