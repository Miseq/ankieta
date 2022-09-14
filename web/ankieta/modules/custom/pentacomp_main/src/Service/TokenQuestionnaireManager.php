<?php
namespace Drupal\pentacomp_main\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;

/**
 * Service Training.
 *
 * Service for Token actions.
 */
class TokenQuestionnaireManager {

  protected $jump = 20;
  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */  
  private $entity_type_manager;

  /**
   * The renderer service.
   *
   * @var Drupal\Core\Session\AccountInterface $account
   */
  private $account;

  /**
   * The renderer service.
   *
   * @var Drupal\Core\Database\Connection $connection
   */
  private $connection;  
  
  /**
   * Constructs a TrainingService object.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param Drupal\Core\Database\Connection $connection
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $account, Connection $connection) {
    $this->account = $account;
    $this->entity_type_manager = $entity_type_manager;
    $this->connection = $connection;
  }

  /**
   * @inheritDoc
   */
  public function createTokenTable(string $name, string $label) { 
    $spec = [
      'description' => $label.' - '.date('m-d-Y H:m'),
      'fields' => [
        'id' => [
          'type' => 'serial',
          'not null' => TRUE,
        ],
        'zid' => [
          'type' => 'int',
          'not null' => TRUE,
          'default' => '0',
          'unsigned' => TRUE,
        ],
        'token' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => 40,
          'default' => '',
        ],
        'date' => [
          'type' => 'int',
          'not null' => TRUE,
        ],
      ],
      'primary key' => ['id'],
    ];
    if (!$this->connection->schema()->tableExists('pentacomp_token_'.$name)) {
      $schema = $this->connection->schema();
      $schema->createTable('pentacomp_token_'.$name, $spec);
    }
    return 'pentacomp_token_'.$name;
  }

  public function getFullConfig($config) {
    $listTable =  $config->get('list');    
    $listSetup =  $config->get('list_setup');
    return ['list' => $config->get('list'), 'setup' => $config->get('list_setup')];
  }

  /**
   * @inheritDoc
   */
  public function changeInList(string $name, $list) {
    $newList = $list;
    if (isset($list[$name])){
      $newList = [];
      $element = $list[$name];
      $newList[$name] = $element;
      if (!empty($list)){
        foreach ($list as $key => $row){
          if ($key != $name) $newList[$key] = $row;
        }
      }
    }
    return $newList;
  }

  /**
   * @inheritDoc
   */
  public function getIdActivTable(array $config){
    if ( !empty($config['list'])){
      $idElement = array_keys($config['list']);
      return isset($idElement[0]) ? $idElement[0] : NULL;
    }
    return NULL;
  } 

  /**
   * @inheritDoc
   */
  public function getActivTable(array $config, $idTable){
    if ( !empty($config['setup']))
      return isset($config['setup'][$idTable]) ? $config['setup'][$idTable] : NULL;
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function tokenGenerator(array $config) {
    if ($this->account->hasPermission('access questionnaire admin token')){
      $table = $this->getIdActivTable($config);
      $tokenConfig = $this->getActivTable($config, $table);
      $numberGen = $this->getNumberCreateToken($table, $this->account);
      $from = ((int)$tokenConfig['numberFrom'] + ($numberGen));
      $end = (int)$tokenConfig['numberTo'];
      $to = (($from + $this->$jump) > $end) ? (($end - $from) + (($end - $from > 0) ? 1 : 0 )) : $from + $this->$jump;
      $key = $tokenConfig['data']['key'];
      for ($a = $from; $a <= $to; $a++) {
        $this->setToken($table, $a, $key);
      }
    }
  } 

  /**
   * @inheritDoc
   */
  public function createToken($zid, $configBase) {
    $error= '';
    $config = $this->getFullConfig($configBase);
    $table = $this->getIdActivTable($config);
    $tokenConfig = $this->getActivTable($config, $table);
    $token = sha1(((string)$tokenConfig["key"].(string)$zid));
    $result = $this->findToken($configBase, $token)->fetchAssoc();
    $prefiks = isset($tokenConfig["link_url"]) ? $tokenConfig["link_url"] : '';
    $link = t('brak');
    if (empty($result)) {
      $this->setToken($table, $zid, $token);
      $color = 'green';
      $link = $prefiks.$token;
      $error = t('Token wygenerowany poprawnie.');      
    } else {
      $link = $prefiks.$token;
      $color = 'orange';
      $error = t('Token już istnieje, nie został jeszcze przypisany do żadnej ankiety.');
      if($result['date'] > 0 ) {
        $link = $token = 'Nie można użyć ';
        $color = 'red';
        $error = t('Token został już wykorzystany i ma przypisaną ankietę.');
      }
    }
    return [
      'token' => $token, 
      'link' => $link, 
      'error'=> $error,
      'color' => $color
    ];
  }

  /**
   * @inheritDoc
   */
  private function setToken($table, $zid, $key) {
      $query = $this->connection->insert($table)->fields(['zid', 'token', 'date']);
      $query->values([
        'zid' => $zid,
        'token' => $key, 
        'date' => 0,
      ]);  
      $query->execute();
  }

  /**
   * @inheritDoc
   */
  public function findToken($config, $token) {
    $config = $this->getFullConfig($config);
    $table = $this->getIdActivTable($config);
    $result = $this->findTokenSql($table, $token);
    return $result;
  }  

  /**
   * @inheritDoc
   */
  private function findTokenSql($table, $token) {
    $query = $this->connection->select($table, 't')
    ->condition('t.token', $token, '=')
    ->fields('t', ['zid', 'token', 'date'])
    ->range(0, 1);
    $result = $query->execute();  
    return $result;
  }

  /**
   * @inheritDoc
   */
  private function findSubmissionSql($token) {
    $query = $this->connection->select('webform_submission_data', 'wsd')
    ->condition('wsd.value', $token, '=')
    ->fields('wsd', ['sid', 'name', 'webform_id'])
    ->range(0, 1);
    $result = $query->execute();  
    return $result;
  }  

  /**
   * @inheritDoc
   */
  private function getValueSubmissionSql($id) {
    $query = $this->connection->select('webform_submission_data', 'wsd')
    ->condition('wsd.sid', $id, '=')
    ->fields('wsd', ['name', 'value']);
    $result = $query->execute();  
    return $result;
  }  


  /**
   * @inheritDoc
   */
  public function getZid($config, $token) {
    $results = $this->findToken($config, $token);
    if (!empty($results)) {
      foreach($results as $row){
        if ($row->date > 0) {
          $resultsWsd = $this->findSubmissionSql($token);
          if (!empty($resultsWsd)) {
            foreach($resultsWsd as $wsd){
              return [$wsd->sid, 'exist', $row->zid, 'data' => $this->getValueSubmissionSql($wsd->sid)];
            }
          }else {
            return [0, 'error', $row->zid];
          }
        } else {
          return [0, 'empty', $row->zid];
        }
      }
    }
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function useToken($account, AccountInterface $training) {
    
  }

  /**
   * @inheritDoc
   */
  public function getLastTokenUse($name, AccountInterface $account) {
  }

  /**
   * @inheritDoc
   */
  public function getRandomToken($number, $table, $mode) {
    if (!$mode){
      $query = $this->connection->select($table, 't')
      ->condition('t.date', 0, '=')
      ->fields('t', ['zid', 'token', 'date'])
      ->range(0, $number);
      $results = $query->execute();
    } else {

    }
    $list = [];
    if (!empty($results)){
      foreach($results as $row){
        $row = json_encode($row, true);
        $list[] = ['zid' => $row['zid'], 'token'  => $row['token'], 'date'];
      }    
    }
    return $list;
  }

  /**
   * @inheritDoc
   */
  public function removeToken($config) {
    if ($this->account->hasPermission('access questionnaire admin token')){
      $table = $this->getIdActivTable($config);
      $sql = "DELETE FROM ".$table;
      $this->connection->query($sql)->execute();
    }    
  }
  
  /**
   * @inheritDoc
   */
  public function getNumberCreateToken($name, AccountInterface $account) {
    $nameTable = $name;
    $name = $this->connection->getConnectionOptions()['database'];
    $sql = "SELECT COUNT(token) FROM ".$nameTable;
    $number = $this->connection->query($sql)->fetchCol();
    return  isset($number[0]) ? $number[0] : 0;
  }


  /**
   * @inheritDoc
   */
  public function getListTable($account) {
    $list = NULL;
    if ($this->account->hasPermission('access questionnaire admin token')){
      $nameTable = 'pentacomp_token_';
      $name = $this->connection->getConnectionOptions()['database'];
      $sql = "SELECT TABLE_NAME
      FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_SCHEMA = '".$name."' AND TABLE_NAME LIKE '".$nameTable."%'
      ORDER BY TABLE_NAME";
      $list = $this->connection->query($sql)->fetchCol();
    }
    return $list;
  }

  /**
   * @inheritDoc
   */  
  public function removeTable($table) {
    if ($this->account->hasPermission('access questionnaire admin token')){
      $sql = "DELETE FROM ".$table;
      $this->connection->query($sql)->execute();
      $this->connection->schema()->dropTable($table);
    } 
  }

}



