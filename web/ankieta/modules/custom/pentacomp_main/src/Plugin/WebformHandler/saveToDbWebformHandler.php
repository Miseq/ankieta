<?php
namespace Drupal\pentacomp_main\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Create a new node entity from a webform submission.
 *
 * @WebformHandler(
 *   id = "db execute questionnaire",
 *   label = @Translation("Save to db execute questionnaire"),
 *   category = @Translation("Save to db"),
 *   description = @Translation("Save to db execute questionnaire."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class saveToDbWebformHandler extends WebformHandlerBase {

  const SETTINGS = 'pentacomp_main.settings';

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $config = \Drupal::config(static::SETTINGS);
    $token_manager = \Drupal::service('pentacomp_main.token.tools');
    $config = $token_manager->getFullConfig($config);
    $table = $token_manager->getIdActivTable($config);
    $values = $webform_submission->getData();
    $db = \Drupal::database()->update($table);
    $db->fields(array('date' => time()))
      ->condition('token', $values['pentacomp_token'])
      ->condition('zid', $values['token_id'])
      ->execute();
  }
}