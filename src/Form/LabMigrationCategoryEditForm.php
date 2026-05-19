<?php
// Drupal 11 Migration - Verified and migrated 2026-04-24
/**
 * @file
 * Contains \Drupal\lab_migration\Form\LabMigrationCategoryEditForm.
 */

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Mail\MailManagerInterface;


class LabMigrationCategoryEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_category_edit_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    /* get current proposal */
    // $proposal_id = (int) arg(4);
    $route_match = \Drupal::routeMatch();

$proposal_id = (int) $route_match->getParameter('id');
    //$proposal_q = $injected_database->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query =\Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      }
      else {
        \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
        // // DEPRECATED [OLD API]: RedirectResponse without new 
// MIGRATED [NEW API]: using return new RedirectResponse
return new \Symfony\Component\HttpFoundation\RedirectResponse('lab-migration/manage-proposal');
        $response = new RedirectResponse(Url::fromRoute('lab_migration.category_edit_form')->toString());
  
// Send the redirect response
$response->send();


// return new RedirectResponse('/lab-migration/manage-proposal/category');
        return;
      }
    }
    else {
      \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      // // DEPRECATED [OLD API]: RedirectResponse without new 
// MIGRATED [NEW API]: using return new RedirectResponse
return new \Symfony\Component\HttpFoundation\RedirectResponse('lab-migration/manage-proposal');

$response = new RedirectResponse(Url::fromRoute('lab_migration.category_edit_form')->toString());
  
// Send the redirect response
$response->send();

      return;
    }
    $form['name'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl($proposal_data->name_title . ' ' . $proposal_data->name,Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid]))->toString(),
      '#attributes' => array('class' => array('form-control')),

      '#title' => $this->t('Name'),
    ];
    $form['email_id'] = [
      '#type' => 'item',
      '#markup' => User::load($proposal_data->uid)->getEmail(),
      '#attributes' => array('class' => array('form-control')),

      '#title' => $this->t('Email'),
    ];
    $form['contact_ph'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->contact_ph,
      '#attributes' => array('class' => array('form-control')),

      '#title' => $this->t('Contact No.'),
    ];
    $form['department'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->department,
      '#attributes' => array('class' => array('form-control')),

      '#title' => $this->t('Department/Branch'),
    ];
    $form['university'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->university,
      '#attributes' => array('class' => array('form-control')),

      '#title' => $this->t('University/Institute'),
    ];

    $form['lab_title'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->lab_title,
      '#attributes' => array('class' => array('form-control')),

      '#title' => $this->t('Title of the Lab'),
    ];
    $form['category'] = [
      '#type' => 'select',
      '#attributes' => array('class' => array('form-control')),

      '#title' => $this->t('Category'),
      '#options' => \Drupal::service("lab_migration_global")->_lm_list_of_departments(),
      '#required' => TRUE,
      '#default_value' => $proposal_data->category,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    $form['cancel'] = [
      '#type' => 'item',
      // '#markup' => Link::fromTextAndUrl($this->t('Cancel'), 'lab-migration/manage-proposal/category'),
      '#markup' => Link::fromTextAndUrl(
  $this->t('Cancel'),Url::fromRoute('lab_migration.category_all'))->toString(),
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    /* get current proposal */
    // $proposal_id = (int) arg(4);
    $route_match = \Drupal::routeMatch();

$proposal_id = (int) $route_match->getParameter('id');
    //$proposal_q = $injected_database->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      }
      else {
         \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
        // // DEPRECATED [OLD API]: RedirectResponse without new 
// MIGRATED [NEW API]: using return new RedirectResponse
return new \Symfony\Component\HttpFoundation\RedirectResponse('lab-migration/manage-proposal');
        $response = new RedirectResponse(Url::fromRoute('lab_migration.category_all')->toString());
  
        // Send the redirect response
        $response->send();
        
        return;
      }
    }
    else {
       \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      // // DEPRECATED [OLD API]: RedirectResponse without new 
// MIGRATED [NEW API]: using return new RedirectResponse
return new \Symfony\Component\HttpFoundation\RedirectResponse('lab-migration/manage-proposal');
      $response = new RedirectResponse(Url::fromRoute('lab_migration.category_all')->toString());
  
      // Send the redirect response
      $response->send();

      return;
    }
    $query = "UPDATE {lab_migration_proposal} SET category = :category WHERE id = :proposal_id";
    $args = [
      ":category" => $form_state->getValue(['category']),
      ":proposal_id" =>  $proposal_data->id,
    ];
    $result = \Drupal::database()->query($query, $args);
     \Drupal::messenger()->addStatus($this->t('Proposal Category Updated'), 'status');
    // // DEPRECATED [OLD API]: RedirectResponse without new 
// MIGRATED [NEW API]: using return new RedirectResponse
return new \Symfony\Component\HttpFoundation\RedirectResponse('lab-migration/manage-proposal/category');
    $response = new RedirectResponse(Url::fromRoute('lab_migration.category_all')->toString());
  
    // Send the redirect response
    $response->send();

  }

}

