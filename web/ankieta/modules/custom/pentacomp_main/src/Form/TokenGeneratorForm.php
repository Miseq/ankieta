<?php
namespace Drupal\pentacomp_main\Form;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Database;
use Drupal\pentacomp_main\Service\TokenQuestionnaireManager;

/**
 * Implements an Questionnaire Aidmn Form.
 */
class TokenGeneratorForm extends ConfigFormBase  {

  /**
   * @var string $themeKey
   */
  protected $themeKey = 'pentacomp_token_gen';

  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'pentacomp_main.settings';

  /**
   * @var AccountInterface $account
   */
  protected $account;

  /**
   * @var string $stage
   */
  protected $stage;

  /**
   * The renderer service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface  
   */
  protected $entity_type_manager;


  /**
   * The token manager.
   *
   * @var Drupal\pentacomp_main\Service\TokenQuestionnaireManager  
   */
  protected $token_manager;

  /**
   * Link from database.
   *
   * @var string
   */
  protected $link;

  /**
   * Constructs a new SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.* 
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The order number generator manager.
   * @param \Drupal\pentacomp_main\Service\TokenQuestionnaireManager $token_manager
   *   The order number generator manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, AccountInterface $account, TokenQuestionnaireManager $token_manager) {
    $this->config_factory = $config_factory;
    $this->entity_type_manager = $entity_type_manager;
    $this->account = $account;
    $this->token_manager = $token_manager;
    $this->stage = 'dashboard';
  }

  /**
   * {@inheritdoc}
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container for service.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('pentacomp_main.token.tools')
    );
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames() {  
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'questionnaire_token_gen_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $uid = $this->account->id();
    $form['#stage'] = $this->stage;
    $form['#theme'] = $this->themeKey;
		$form['#attributes']['enctype']  = 'multipart/form-data';
		$form['#attached']['library'][] = 'pentacomp_main/pentacomp_main.form.generator';
		$form['#prefix'] = '<div id="'.$this->themeKey.'" class="pentacomp-admin containers">';
		$form['#suffix'] = '</div>';    
    if ($this->account->hasPermission('token generator')){    
      switch ($this->stage) {
        case 'dashboard':
          $this->dashboardStep($form, $form_state);
          $form['#color'] = isset($this->storage['color']) ? $this->storage['color']: NULL;
          $form['#message'] = isset($this->storage['message']) ? $this->storage['message']: NULL;
          break;
        }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */  
  public function dashboardStep(array &$form, FormStateInterface $form_state) {
    $form['zid'] = [
      '#type' => 'number',
      '#title' => t('Numer zgłoszenia'),
      '#default_value' => isset($this->storage['zid']) ? $this->storage['zid'] : '', 
      '#attributes' => [
        'class' => ['d-flex align-items-center form-control mb-0'],
      ],
      '#prefix' => '<div class="gen-number-area">',
      '#suffix' => '</div>',
    ];
    $form['gen'] = [
      '#type' => 'submit',
      '#value' => 'Wygeneruj link',
    ];

    $form['link'] = [
      '#type' => 'markup',
      '#markup' => isset($this->storage['link']) ? $this->storage['link'] : NULL,
    ];  
    $form['token'] = [
      '#type' => 'markup',
      '#markup' => isset($this->storage['token']) ? $this->storage['token'] : NULL,
    ];
    $form['#attached']['drupalSettings']['copytoken'] = [
      'value' => (isset($this->storage['link']) ? $this->storage['link'] : NULL),
    ];      
    $form['gen'] = array_merge($this->buttonPartMain('gen_action', 100), $form['gen']);
  }

  /**
   * {@inheritdoc}
   */  
  protected function buttonPartMain(string $action, int $weight) { 
    $button = [];
    if ($action != '' ){
      $button = [
        '#attributes' => [
          'class' => ['btn btn-outline-primary append-arrow btn-gen-token'],
          'data-mode' => $action,
        ],
        '#ajax' => [
          'wrapper' => $this->themeKey,
          'callback' => '::ajaxSubmitForm',
        ],
        '#weight' => $weight,        
        '#prefix' => '',
        '#suffix' => '',    
      ];      
    }
    return $button;
  }

  /**
   * Implements ajax submit callback.
   */
  public function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $reload = false;
    $noreload = true;
    $button = $form_state->getTriggeringElement();
    if (isset($button['#attributes']['data-mode']))	{
      switch($button['#attributes']['data-mode']){
        case 'gen_action':
          $config = $this->config(static::SETTINGS);
          $zid = $form_state->getValue('zid');
          if ((int)$zid  > 0){
            $this->storage['zid'] = $zid;
            if (empty($config)) {
              $this->storage['color'] = 'red';
              $this->storage['message'] = t('W systemie nie ustawiono ankiety. Powiadom Administratora.');
              $this->storage['link'] = t('brak');
            } else {
              $dataGen = $this->token_manager->createToken($zid, $config);
              $this->storage['token'] = $dataGen['token'];
              $this->storage['link'] = $dataGen['link'];
              $this->storage['color'] = $dataGen['color'];
              $this->storage['message'] = $dataGen['error'];
            }
          } else {
            $this->storage['color'] = 'red';
            $this->storage['message'] = t('Wpisz numer zgłoszenia.');
          }
          $reload = TRUE;
          break;  
      }
    }
    if($reload) {
      $form_state->setRebuild(TRUE);
    } else {
      if($noreload) {			

      } else {
        parent::submitForm($form, $form_state);				
      }
    }
  }

}