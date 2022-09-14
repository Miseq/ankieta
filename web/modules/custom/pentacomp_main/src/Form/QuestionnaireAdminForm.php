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
class QuestionnaireAdminForm extends ConfigFormBase  {

  /**
   * @var string $themeKey
   */
  protected $themeKey = 'pentacomp_admin_token';

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
   * Local storage.
   *
   * @var array
   */
  protected $storage;
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
    return 'questionnaire_admin_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $uid = $this->account->id();
    $form['#stage'] = $this->stage;
    $form['#theme'] = $this->themeKey;
		$form['#attributes']['enctype']  = 'multipart/form-data';
		$form['#prefix'] = '<div id="'.$this->themeKey.'" class="pentacomp-admin containers">';
		$form['#suffix'] = '</div>';    
    if ($this->account->hasPermission('access questionnaire admin token')){    
      switch ($this->stage) {
        case 'dashboard':
          $message = 'Panel zarzadaznia systemu przypisywania Tokenów do ankiet.';
          $this->dashboardStep($form, $form_state);
          break;
        case 'poll_tools':
          $message = 'Wybierz która ankieta mam być używana.';
          $this->selectPollStep($form, $form_state);
          break;
        case 'token_tools':
          $message = 'Ustawienia dla tokenów.';
          $this->tokenShowStep($form, $form_state);
          break;

        case 'table_tools':
          $message = 'Ustawienia tabeli w bazie danych.';
          $this->tableShowStep($form, $form_state);
          break;
        case 'confirm_remove_token':
          $message = 'Czy napewno chcesz usunąć tokeny?.';
          $this->confirmRemoveTokenStep($form, $form_state);

          break;
        case 'confirm_remove_table':
          $message = 'Czy napewno chcesz usunąć tablicę?.';
          $this->confirmRemoveTableStep($form, $form_state);

          break;      
        }
    }
    $form['info'] = [
      '#type' => 'markup',
      '#markup' => $message,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */  
  public function dashboardStep(array &$form, FormStateInterface $form_state) {
    $form['button']['select-poll'] = [
      '#type' => 'submit',
      '#value' => 'Przypisanie ankiety',
    ];
    $form['button']['admin-token'] = [
      '#type' => 'submit',
      '#value' => 'Tokeny',
    ];
    $form['button']['admin-table'] = [
      '#type' => 'submit',
      '#value' => 'Tworzenie tablic',
    ];
    $form['button']['select-poll'] = array_merge($this->buttonPartMain('poll_action', 100), $form['button']['select-poll']);
    $form['button']['admin-token'] = array_merge($this->buttonPartMain('token_action', 100), $form['button']['admin-token']);
    $form['button']['admin-table'] = array_merge($this->buttonPartMain('table_action', 100), $form['button']['admin-table']);
  }

  /**
   * {@inheritdoc}
   */
  public function selectPollStep(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $activePollId =  $config->get('poll');
    $options = [];
    $webforms = $this->entity_type_manager->getStorage('webform')->loadByProperties([]);
    foreach ($webforms as $webform) {
      if ($webform->status() == 1 ){
        $options[$webform->id()] = $webform->label();
        if($webform->id() == $activePollId ) {
          $activePollName = $webform->label();
        }
      }
    }
    $form['top_info'] = [
      '#type' => 'markup',
      '#markup' => t('Obecnie wybrana ankieta: @name - id: @id', ['@name' =>  $activePollName, '@id' =>  $activePollId]),
    ];    
    $form['questionnaire'] = [
      '#type' => 'select',
      '#title' => t('Ankiety'),
      '#options' => $options,
      '#default_value' => $activePollId,
    ];
    $form['select_questionnaire'] = [
      '#type' => 'submit',
      '#value' => 'Wybierz i zapisz',
    ];
    
    $form['select_questionnaire_back'] = [
      '#type' => 'submit',
      '#value' => 'Powrót',
    ];
    $form['select_questionnaire'] = array_merge($this->buttonPartMain('poll_action_select', 2200), $form['select_questionnaire']);
    $form['select_questionnaire_back'] = array_merge($this->buttonPartMain('poll_action_back', 2200), $form['select_questionnaire_back']);
  }

  /**
   * {@inheritdoc}
   */
  public function tokenShowStep(array &$form, FormStateInterface $form_state) {
    $tokenConfig = $this->getTokenInfo();
    $this->renderTokenInfo($form, $form_state, $tokenConfig);
    $form['manual'] = [
      '#type' => 'checkbox',
      '#title' => t('Tokeny będą generowane pojedyńczo przez obsługę po przez podanie Id zgłoszenia'),
      '#default_value' => isset($tokenConfig['data']['manual']) ? $tokenConfig['data']['manual'] : '', 
    ];

    $form['number_begin'] = [
      '#type' => 'number',
      '#title' => t('Pierwszy numer identyfikatora'),
      '#default_value' => isset($tokenConfig['data']['numberFrom']) ? $tokenConfig['data']['numberFrom'] : 0, 
      '#states' => [
        'invisible' => [
          ':input[name="manual"]' => ['checked' => TRUE],
        ]
      ]
    ];
    $form['number_end'] = [
      '#type' => 'number',
      '#title' => t('Ostatni numer identyfikatora'),
      '#default_value' => isset($tokenConfig['data']['numberTo']) ? $tokenConfig['data']['numberTo'] : 0, 
      '#states' => [
        'invisible' => [
          ':input[name="manual"]' => ['checked' => TRUE],
        ]
      ]
    ];
    $form['key'] = [
      '#type' => 'textfield',
      '#title' => t('Klucz używany do generowania tokenów'),
      '#default_value' => isset($tokenConfig['data']['key']) ? $tokenConfig['data']['key'] : '', 
    ];
    $form['link_url'] = [
      '#type' => 'textfield',
      '#title' => t('Adres url użyty do generowania linku.'),
      '#description' => t('URL musi kończyć się parametrem GET "t" np: example.com?t='),
      '#default_value' => isset($tokenConfig['data']['link_url']) ? $tokenConfig['data']['link_url'] : '?t=', 
    ];    
    $form['select_questionnaire'] = [
      '#type' => 'submit',
      '#value' => 'Zapisz ustawienia',
    ];
    $form['select_questionnaire_back'] = [
      '#type' => 'submit',
      '#value' => 'Powrót',
    ];
    $form['gen_token'] = [
      '#type' => 'submit',
      '#value' => 'Generuj tokeny',
      '#states' => [
        'invisible' => [
          ':input[name="manual"]' => ['checked' => TRUE],
        ]
      ]
    ];
    $form['remove_token'] = [
      '#type' => 'submit',
      '#value' => 'Usuń istniejące tokeny',
      '#states' => [
        'invisible' => [
          ':input[name="manual"]' => ['checked' => TRUE],
        ]
      ]
    ];    
    $form['select_questionnaire'] = array_merge($this->buttonPartMain('token_action_select', 2201), $form['select_questionnaire']);
    $form['select_questionnaire_back'] = array_merge($this->buttonPartMain('token_action_back', 2202), $form['select_questionnaire_back']);
    $form['gen_token'] = array_merge($this->buttonPartMain('gen_token_select', 2203), $form['gen_token']);
    $form['remove_token'] = array_merge($this->buttonPartMain('remove_token_select', 2204), $form['remove_token']);

    $form['#exampleList'] = $this->token_manager->getRandomToken(10, $tokenConfig['table'], (isset($tokenConfig['data']['manual']) ? TRUE : FALSE));

  }  

  /**
   * {@inheritdoc}
   */
  public function tableShowStep(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $listTableInSql = $this->token_manager->getListTable($this->account);
    if (!isset($listTableInSql) || empty($listTableInSql)) {
      $listTableInSql = [];
    }
    $listTable =  $config->get('list');
    if (!isset($listTable) || empty($listTable)) {
      $listTable = [];
    }
    $activTable = [
      'name' => '',
      'table' => '',
      'date' => '',
      'uid' => '', 
      'autor' => '',
    ];
    if (!empty($listTable)) {
      $idElement = array_keys($listTable);
      $tabConfig = $listTable[$idElement[0]];
      $activeTableSelect = $activTable['name'] = $tabConfig['name'];
      $activTable['table'] = $tabConfig['table'];
      $activTable['date'] = $tabConfig['date'];
      $activTable['uid'] = $tabConfig['uid'];
      $activTable['autor'] = $this->readUserLabel((int)$tabConfig['uid']);
    }
    $form['content']['info'] = [
      '#table' => t('Obecnie używana tabela: @name', [
        '@name' => $activTable['name'], 
      ]),
      '#name' => t('Identyfikator tablicy: @table', [
        '@table' =>  $activTable['table'], 
      ]),
      '#data' => t('Data Utworzenia: @date', [
        '@date' =>  $activTable['date'], 
      ]),
      '#autor' => t('Autor tabeli: @autor', [
        '@autor' =>  $activTable['autor'].(($activTable['uid']!= '' ) ? (' ('.$activTable['uid'].')') : ''), 
      ]),
    ];
    $form['content']['#memory'] = t('Tabele zapisane w systemie');
    $form['content']['#memory_list'] = (!empty($listTable) ? $this->listTableRender($listTable) : 'Brak danych');
    $form['content']['#db'] = $this->t('Fizyczne tabele z bazy danych');
    $form['content']['#db_list'] = (!empty($listTableInSql) ? $this->listTableRender($listTableInSql) : 'Brak danych');
    $name = '';
    if(isset($activTable['name'])) {
      $name = explode('pentacomp_token_', $activTable['name']);
      $name = isset($name[1]) ? $name[1] : '';
    }
    $form['table']['label_table'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Opis tablicy'),
      '#default_value' => $activTable['table'],
      '#size' => 60,
      '#maxlength' => 60,
    ];
    $form['table']['name_table'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nazwa tablicy'),
      '#default_value' => $name,
      '#description' => 'nazwa zklada się z "pentacomp_token_" + <wprowadzona nazwa>. Nazwa powinna składać się ze liter A-z i cyfr 1-9 oraz znaku _',
      '#size' => 32,
      '#maxlength' => 32,
    ];
    $form['content']['table_action_button_select'] = [
      '#type' => 'submit',
      '#value' => 'Wybierz i zapisz',
    ];
    $form['content']['table_action_button_back'] = [
      '#type' => 'submit',
      '#value' => 'Powrót',
    ];
    $form['content']['table_action_button_create'] = [
      '#type' => 'submit',
      '#value' => 'Utwórz tablice',
    ];
    $form['content']['table_action_button_remove'] = [
      '#type' => 'submit',
      '#value' => 'Usuń Tablice',
    ];
    $form['content']['table_action_button_select'] = array_merge($this->buttonPartMain('table_action_select', 2200), $form['content']['table_action_button_select']);
    $form['content']['table_action_button_back'] = array_merge($this->buttonPartMain('table_action_back', 2000), $form['content']['table_action_button_back']);
    $form['content']['table_action_button_create'] = array_merge($this->buttonPartMain('table_action_create_table', 3000), $form['content']['table_action_button_create']);
    $form['content']['table_action_button_remove'] = array_merge($this->buttonPartMain('table_action_remove_table', 3000), $form['content']['table_action_button_remove']);
    $form['table_select'] = [
      '#type' => 'select',
      '#title' => t('Wybierz tablice tokenow'),
      '#options' => $this->listTableRender($listTable, TRUE) ,
      '#default_value' => $activeTableSelect,
    ];
  }  
  
  /**
   * {@inheritdoc}
   */
  public function confirmRemoveTokenStep(array &$form, FormStateInterface $form_state) {
    $tokenConfig = $this->getTokenInfo();
    $this->renderTokenInfo($form, $form_state, $tokenConfig);
    $form['confirm_remove'] = [
      '#type' => 'submit',
      '#value' => 'Tak usuń tokeny',
    ];
    $form['cancel_remove'] = [
      '#type' => 'submit',
      '#value' => 'Nie, anuluj operacje',
    ];
    $form['confirm_remove'] = array_merge($this->buttonPartMain('confirm_remove_select', 2200), $form['confirm_remove']);
    $form['cancel_remove'] = array_merge($this->buttonPartMain('cancel_remove_select', 2200), $form['cancel_remove']);

  }

  /**
   * {@inheritdoc}
   */
  public function confirmRemoveTableStep(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $listTable =  $config->get('list');
    $tabConfig = $listTable[$this->storage['tabRemove']];
    $activeTableSelect = $activTable['name'] = $tabConfig['name'];
    $activTable['table'] = $tabConfig['table'];
    $activTable['date'] = $tabConfig['date'];
    $activTable['uid'] = $tabConfig['uid'];
    $activTable['autor'] = $this->readUserLabel((int)$tabConfig['uid']);
    $form['content']['info'] = [
      '#table' => t('Obecnie używana tabela: @name', [
        '@name' => $activTable['name'], 
      ]),
      '#name' => t('Identyfikator tablicy: @table', [
        '@table' =>  $activTable['table'], 
      ]),
      '#data' => t('Data Utworzenia: @date', [
        '@date' =>  $activTable['date'], 
      ]),
      '#autor' => t('Autor tabeli: @autor', [
        '@autor' =>  $activTable['autor'].(($activTable['uid']!= '' ) ? (' ('.$activTable['uid'].')') : ''), 
      ]),
    ];
    $form['confirm_remove'] = [
      '#type' => 'submit',
      '#value' => 'Tak usuń tablicę',
    ];
    $form['cancel_remove'] = [
      '#type' => 'submit',
      '#value' => 'Nie, anuluj operacje',
    ];
    $form['confirm_remove'] = array_merge($this->buttonPartMain('confirm_remove_table_select', 2200), $form['confirm_remove']);
    $form['cancel_remove'] = array_merge($this->buttonPartMain('cancel_remove_table_select', 2200), $form['cancel_remove']);
  }
  
  /**
   * {@inheritdoc}
   */
  private function renderTokenInfo(array &$form, FormStateInterface $form_state, array $tokenConfig) {
    $form['#table'] = t('Aktualna ID tablicy tokenów:  <b>@name</b>', [
      '@name' => isset($tokenConfig['table']) ? $tokenConfig['table'] : 'brak',
    ]);
    $form['#top_info'] = t('Zakres identyfikatorów od @numberFrom do @numberTo przez @uid', [
        '@numberFrom' => isset($tokenConfig['data']['numberFrom']) ? $tokenConfig['data']['numberFrom'] : 'brak', 
        '@numberTo' =>  isset($tokenConfig['data']['numberTo']) ? $tokenConfig['data']['numberTo'] : 'brak', 
        '@uid' =>  isset($tokenConfig['data']['uid']) ? $this->readUserLabel((int)$tokenConfig['data']['uid']) : 'brak', 
    ]);
    $form['#date_created'] = t('Ostatnia operacja na tokenach: <b>@dateGen</b>', [
      '@dateGen' => isset($tokenConfig['data']['dateCreated']) ? date('m-d-Y H:m', $tokenConfig['data']['dateCreated']) : 'brak', 
    ]);
    $form['#last_info'] = t('wygenerowanych tokenów w bazie @numberGen z deklarowanej liczby: <b>@numberAll</b>', [
      '@numberGen' => $tokenConfig['data']['numberGen'], 
      '@numberAll' => $tokenConfig['data']['numberAll']
    ]);
    $form['#date_gen'] = t('Ostatnia operacja na tokenach: <b>@dateGen</b>', [
      '@dateGen' => isset($tokenConfig['data']['dateToken']) ? date('m-d-Y H:m', $tokenConfig['data']['dateToken']) : 'brak', 
    ]);
  }

  /**
   * {@inheritdoc}
   */  
  protected function buttonPartMain(string $action, int $weight) { 
    $button = [];
    if ($action != '' ){
      $button = [
        '#attributes' => [
          'class' => ['btn-primary'],
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
   * {@inheritdoc}
   */
  protected function listTableRender(array $listTableInSql, $mode = false) {
    $list = [];
    foreach($listTableInSql as $row) {
      if (!empty($row)) {
        if ($mode) {
          $list[$row['name']] = $row['name'].' - '.$row['table'];
        } else {
          if (is_array($row)) {
            $list[$row['name']] .= 'Opis tablicy: '.$row['table'].' <br/>';
            $list[$row['name']] .= 'Identyfikator: '.$row['name'].' utworzona '.date('m-d-Y H:m', $row['date']).'<br/>';
            $list[$row['name']] .= 'Autor: '.$row['uid'].' <br/>';
          }
          if (is_string($row)) {
            $list[].= "- ".$row." <br/>";
          }
        }
      }
    }
    return $list;
  }

  /**
   * Get user name.
   */  
  protected function readUserLabel(int $uid) { 
    if ($uid > 0 ){
      $author = $this->entity_type_manager->getStorage('user')->load($uid);
      return (!empty($author)) ? $author->label() : '';
    }
    return '';
  }

  /**
   * Get user name.
   */  
  protected function getFullConfig() {
    $config = $this->config(static::SETTINGS);
    $listTableInSql = $this->token_manager->getListTable($this->account);
    $listTable =  $config->get('list');    
    $listSetup =  $config->get('list_setup');
    return ['list' => $config->get('list'), 'setup' => $config->get('list_setup')];
  }

  /**
   * Get user name.
   */  
  protected function getTokenInfo() {
    $config = $this->getFullConfig();
    $idActiveTable = $error = '';
    $tokenConfig = [
      'numberFrom',
      'numberTo',
      'uid',
      'userName',
      'numberGen',
      'numberAll',
      'dateCreated',
      'dateGen',
      'dateToken',      
      'key',
    ];
    if (!empty($config['list'])) {
      $idActiveTable = $this->token_manager->getIdActivTable($config);
      if (!empty($config['setup'])) {
        $tokenConfig = $config['setup'][$idActiveTable];
        $userName = '';
        if ((int) $uidLastMod > 0) {
          $autor = $this->entity_type_manager->getStorage('user')
            ->load($uidLastMod);
          $tokenConfig['userName'] = !empty($autor) ? $autor->label() : '';
        }
        $tokenConfig['dateToken'] = $this->token_manager->getLastTokenUse($idActiveTable, $this->account);
        $tokenConfig['numberGen'] = $this->token_manager->getNumberCreateToken($idActiveTable, $this->account);
        $tokenConfig['numberAll'] = ((int)$tokenConfig['numberTo'] - ((int)$tokenConfig['numberFrom'] - 1));
      } else {
        $error = t('Brak ustawień tokenów dla bieżącej tablicy.');
      }
    } else {
      $error = t('Brak tablic, dodaj co najmniej jedną tablicę w sekcji TABLES.');
    }
    return [
      'error' => $error,
      'data' => $tokenConfig, 
      'table' => $idActiveTable
    ];
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
      $value = $form_state->getValues();
      switch($button['#attributes']['data-mode']){
        case 'poll_action':
          $reload = TRUE;		
          $this->stage = 'poll_tools';
          break;  

        case 'poll_action_back':
          $reload = TRUE;		
          $this->stage = 'dashboard';
          break;  

        case 'poll_action_select':
          $pollId = $form_state->getValue('questionnaire');
          $this->config(static::SETTINGS)
            ->set('poll', $pollId)
            ->save();                
          $this->stage = 'dashboard';
          $reload = TRUE;
          break;
        case 'token_action':
          $this->stage = 'token_tools';
          $reload = TRUE;		
          break;
        case 'token_action_back':
          $reload = TRUE;		
          $this->stage = 'dashboard';
          break;
        case 'token_action_select':
          $config = $this->config(static::SETTINGS);
          $setupTable =  $config->get('list_setup');
          $listTable =  $config->get('list');
          $idActiveTable = '';
          $numberBegin = $form_state->getValue('number_begin');
          $numberEnd = $form_state->getValue('number_end');
          $manual = $form_state->getValue('manual');          
          $key = $form_state->getValue('key');
          $link_url = $form_state->getValue('link_url');          
          $config = $this->getFullConfig();

          if (!empty($listTable)) {
            $idActiveTable = $this->token_manager->getIdActivTable($config);
            if ($idActiveTable != '' && isset($config['setup'][$idActiveTable])) {
              $setup = $config['setup'][$idActiveTable];
              $setup['manual'] = $manual;
              $setup['numberFrom'] = $numberBegin;
              $setup['numberTo'] = $numberEnd;
              $setup['numberAll'] = ($numberEnd - ($numberBegin + ($numberBegin > 0 ? - 1 : 0)));
              $setup['link_url'] = $link_url;
              if ($manual) {
                $setup['numberFrom'] = 0;
                $setup['numberTo'] = 0;
                $setup['numberAll'] = 0;
              }
              $setup['dateCreated'] = time();
              $setup['key'] = $key;
              $config['setup'][$idActiveTable] = $setup;
            } else {
              $config['setup'][$idActiveTable] = [
                'numberFrom' => $numberBegin,
                'numberTo' => $numberEnd,
                'numberGen' => 0,
                'numberAll' => ($numberEnd - ($numberBegin + ($numberBegin > 0 ? - 1 : 0))),
                'dateCreated' => time(),
                'dateGen' => NULL,
                'uid' => $this->account->id(),
                'key' => $key,
                'manual' => $manual,
                'link_url' => $link_url
              ];
              if ($manual) {
                $local = [
                  'numberFrom' => 0,
                  'numberTo' => 0,
                  'numberGen' => 0,
                  'numberAll' => 0,
                ];
                $config['setup'][$idActiveTable] = array_merge($config['setup'][$idActiveTable], $local);
              }
            }
            $this->config(static::SETTINGS)
            ->set('list_setup', $config['setup'])
            ->save();
          } else {
            dms('Nie ma aktywnej Tablicy. Ustaw najpierw tablice na potrzeby generowania tokenów.');
          }
          $reload = TRUE;
          break;

        case 'table_action':
          $this->stage = 'table_tools';
          $reload = TRUE;		
          break;

        case 'table_action_back':
          $this->stage = 'dashboard';
          $reload = TRUE;		
          break;

        case 'table_action_select':
          $nameTable = (string)$form_state->getValue('table_select');
          $list = $this->config(static::SETTINGS)->get('list');
          $orderList = $this->token_manager->changeInList($nameTable, $list);
          $this->config(static::SETTINGS)
            ->set('list', $orderList)
            ->save();   
          $reload = TRUE;
          break;

        case 'table_action_remove_table':
          $reload = TRUE;
          $this->storage['tabRemove'] =  (string)$form_state->getValue('table_select');
          $this->stage = 'confirm_remove_table';
          $reload = TRUE;
          break;

        case 'table_action_create_table':
          $nameTable = (string)$form_state->getValue('name_table');
          $label = (string)$form_state->getValue('label_table');
          $nameTableDB = $this->token_manager->createTokenTable($nameTable, $label);
          $list = $this->config(static::SETTINGS)->get('list');

          $list[$nameTableDB] = [
            'name' => $nameTableDB,
            'table' => $label,
            'date' => time(),
            'uid' => $this->account->id()
          ];

          $this->config(static::SETTINGS)
            ->set('list', $list)
            ->save();
          $reload = TRUE;
          break;


        case 'gen_token_select':
          $config = $this->getFullConfig();
          $this->token_manager->tokenGenerator($config);
          $reload = TRUE;
          break;

        case 'remove_token_select':
          $reload = TRUE;
          $this->stage = 'confirm_remove_token';
          break;
          
        case 'confirm_remove_select':
          $reload = TRUE;
          $config = $this->getFullConfig();
          $this->token_manager->removeToken($config);
          $this->stage = 'token_tools';
          break;

        case 'cancel_remove_select':
          $reload = TRUE;
          $this->stage = 'token_tools';
          break;


        case 'confirm_remove_table_select':
          $list = $this->config(static::SETTINGS)->get('list');
          $listSetup = $this->config(static::SETTINGS)->get('list_setup');          
          $list[$this->storage['tabRemove']] = NULL;
          $listSetup[$this->storage['tabRemove']] = NULL;
          $this->token_manager->removeTable($this->storage['tabRemove']);
          $this->config(static::SETTINGS)
            ->set('list', $list)
            ->set('list_setup', $listSetup)            
            ->save();
          $this->stage = 'table_tools';
          $reload = TRUE;
          $this->storage['tabRemove'] = NULL;
          break;

        case 'cancel_remove_table_select':
          $reload = TRUE;
          $this->stage = 'table_tools';
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

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }
  
}