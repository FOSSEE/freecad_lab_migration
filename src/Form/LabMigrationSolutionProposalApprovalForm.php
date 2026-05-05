<?php
// Drupal 11 Migration - Verified and migrated 2026-04-24
/**
 * @file
 * Contains \Drupal\lab_migration\Form\LabMigrationSolutionProposalApprovalForm.
 */

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;

class LabMigrationSolutionProposalApprovalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_solution_proposal_approval_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $route_match = \Drupal::routeMatch();
    $proposal_id = (int) $route_match->getParameter('proposal_id');

    // MIGRATED [NEW API]: using \Drupal::database()
    $query = \Drupal::database()->select('lab_migration_proposal', 'lmp');
    $query->fields('lmp');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      }
      else {
        // MIGRATED [NEW API]: using messenger()->addError()
        \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
        return new RedirectResponse(Url::fromUserInput('/lab-migration/manage-proposal/pending-solution-proposal')->toString());
      }
    }
    else {
      // MIGRATED [NEW API]: using messenger()->addError()
      \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      return new RedirectResponse(Url::fromUserInput('/lab-migration/manage-proposal/pending-solution-proposal')->toString());
    }

    $form['name'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl($proposal_data->solution_provider_name_title . ' ' . $proposal_data->solution_provider_name, Url::fromUri('internal:/user/' . $proposal_data->solution_provider_uid))->toString(),
      '#title' => $this->t('Solution Provider Name'),
    ];
    $form['email_id'] = [
      '#type' => 'item',
      // MIGRATED [NEW API]: using User::load() and getEmail()
      '#markup' => User::load($proposal_data->solution_provider_uid)->getEmail(),
      '#title' => $this->t('Solution Provider Email'),
    ];
    $form['contact_ph'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->solution_provider_contact_ph,
      '#title' => $this->t('Solution Provider Contact No.'),
    ];
    $form['department'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->solution_provider_department,
      '#title' => $this->t('Department/Branch'),
    ];
    $form['university'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->solution_provider_university,
      '#title' => $this->t('University/Institute'),
    ];

    $form['country'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->solution_provider_country,
      '#title' => $this->t('Country'),
    ];
    $form['all_state'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->solution_provider_state,
      '#title' => $this->t('State'),
    ];
    $form['city'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->solution_provider_city,
      '#title' => $this->t('City'),
    ];
    $form['pincode'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->solution_provider_pincode,
      '#title' => $this->t('Pincode/Postal code'),
    ];

    $form['esim_version'] = [
      '#type' => 'item',
      '#title' => $this->t('eSim version used'),
      '#markup' => $proposal_data->esim_version,
    ];

    $form['lab_title'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->lab_title,
      '#title' => $this->t('Title of the Lab'),
    ];

    /* get experiment details */
    $experiment_list = '<ul>';
    $query = \Drupal::database()->select('lab_migration_experiment', 'lme');
    $query->fields('lme');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('id', 'ASC');
    $experiment_q = $query->execute();
    while ($experiment_data = $experiment_q->fetchObject()) {
      $experiment_list .= '<li>' . $experiment_data->title . '</li>Description of Experiment : ' . $experiment_data->description . '<br>';
    }
    $experiment_list .= '</ul>';

    $form['experiment'] = [
      '#type' => 'item',
      '#markup' => $experiment_list,
      '#title' => $this->t('Experiments'),
    ];
    $form['solution_display'] = [
      '#type' => 'item',
      '#title' => $this->t('Display the solution on the www.esim.fossee.in website'),
      '#markup' => ($proposal_data->solution_display == 1) ? "Yes" : "No",
    ];

    // MIGRATED [NEW API]: fixed broken string concatenation due to inline comments and implemented new APIs
    $proposer = '<ul>' . 
      '<li><strong>Proposer:</strong> ' . Link::fromTextAndUrl($proposal_data->name, Url::fromUri('internal:/user/' . $proposal_data->uid))->toString() . '</li>' . 
      '<li><strong>Proposer Name:</strong> ' . $proposal_data->name_title . ' ' . $proposal_data->name . '</li>' . 
      '<li><strong>Contact No:</strong> ' . $proposal_data->contact_ph . '</li>' . 
      '<li><strong>Email:</strong> ' . User::load($proposal_data->uid)->getEmail() . '</li>' . 
      '<li><strong>Department:</strong> ' . $proposal_data->department . '</li>' . 
      '<li><strong>University:</strong> ' . $proposal_data->university . '</li>' . 
      '<li><strong>Country:</strong> ' . $proposal_data->country . '</li>' . 
      '<li><strong>State:</strong> ' . $proposal_data->state . '</li>' . 
      '<li><strong>City:</strong> ' . $proposal_data->city . '</li>' . 
      '<li><strong>Pincode:</strong> ' . $proposal_data->pincode . '</li>' . 
      '</ul>';

    $form['proposer_details'] = [
      '#type' => 'item',
      '#title' => $this->t('Proposer of Lab :'),
      '#markup' => $proposer,
    ];

    $form['approval'] = [
      '#type' => 'radios',
      '#title' => $this->t('Solution Provider'),
      '#options' => [
        '1' => 'Approve',
        '2' => 'Disapprove',
      ],
      '#required' => TRUE,
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reason for disapproval'),
      '#states' => [
        'visible' => [
          ':input[name="approval"]' => ['value' => '2'],
        ],
        'required' => [
          ':input[name="approval"]' => ['value' => '2'],
        ],
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    $form['cancel'] = [
      '#type' => 'markup',
      '#markup' => Link::fromTextAndUrl($this->t('Cancel'), Url::fromUserInput('/lab-migration/manage-proposal/pending-solution-proposal'))->toString(),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $route_match = \Drupal::routeMatch();
    $proposal_id = (int) $route_match->getParameter('proposal_id');

    // MIGRATED [NEW API]: using \Drupal::database()
    $query = \Drupal::database()->select('lab_migration_proposal', 'lmp');
    $query->fields('lmp');
    $query->condition('id', $proposal_id);
    $solution_provider_q = $query->execute();
    $solution_provider_data = $solution_provider_q->fetchObject();

    if ($solution_provider_data && isset($solution_provider_data->uid)) {
      $query = \Drupal::database()->select('lab_migration_proposal', 'lmp');
      $query->fields('lmp');
      $query->condition('solution_provider_uid', $solution_provider_data->uid);
      $query->condition('approval_status', [0, 1], 'IN');
      $query->condition('id', $proposal_id, '<>');
      $solution_provider_present_q = $query->execute();
      if ($solution_provider_present_q->fetchObject()) {
        $form_state->setErrorByName('', $this->t('Solution provider has already one proposal active'));
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $route_match = \Drupal::routeMatch();
    $proposal_id = (int) $route_match->getParameter('proposal_id');

    // MIGRATED [NEW API]: using \Drupal::database()
    $query = \Drupal::database()->select('lab_migration_proposal', 'lmp');
    $query->fields('lmp');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      }
      else {
        // MIGRATED [NEW API]: using messenger()->addError()
        \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
        $form_state->setRedirectUrl(Url::fromUserInput('/lab-migration/manage-proposal/pending-solution-proposal'));
        return;
      }
    }
    else {
      // MIGRATED [NEW API]: using messenger()->addError()
      \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      $form_state->setRedirectUrl(Url::fromUserInput('/lab-migration/manage-proposal/pending-solution-proposal'));
      return;
    }

    $user_data = User::load($proposal_data->solution_provider_uid);
    if (!$user_data) {
      \Drupal::messenger()->addError($this->t('Solution provider user not found.'));
      return;
    }

    $config = \Drupal::config('lab_migration.settings');

    if ($form_state->getValue(['approval']) == 1) {
      $query = "UPDATE {lab_migration_proposal} SET solution_status = 2 WHERE id = :proposal_id";
      $args = [":proposal_id" => $proposal_id];
      \Drupal::database()->query($query, $args);
      
      /* sending email */
      $email_to = $user_data->getEmail();
      $from = $config->get('lab_migration_from_email') ?: \Drupal::config('system.site')->get('mail');
      $bcc = $user->getEmail() . ', ' . ($config->get('lab_migration_emails') ?: '');
      $cc = $config->get('lab_migration_cc_emails') ?: '';
      
      $param['solution_proposal_approved']['proposal_id'] = $proposal_id;
      $param['solution_proposal_approved']['user_id'] = $proposal_data->solution_provider_uid;
      $param['solution_proposal_approved']['headers'] = [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ];
      
      // MIGRATED [NEW API]: using plugin.manager.mail service
      $mailManager = \Drupal::service('plugin.manager.mail');
      $result = $mailManager->mail('lab_migration', 'solution_proposal_approved', $email_to, \Drupal::languageManager()->getDefaultLanguage()->getId(), $param, $from, TRUE);
      
      if (!$result['result']) {
        \Drupal::messenger()->addError($this->t('Error sending email message.'));
      }
      
      \Drupal::messenger()->addStatus($this->t('Lab migration solution proposal approved. User has been notified of the approval.'));
      $form_state->setRedirectUrl(Url::fromUserInput('/lab-migration/manage-proposal/pending-solution-proposal'));
      return;
    }
    elseif ($form_state->getValue(['approval']) == 2) {
      $query = "UPDATE {lab_migration_proposal} SET solution_provider_uid = :solution_provider_uid, solution_status = :solution_status, solution_provider_name_title = '', solution_provider_name = '', solution_provider_contact_ph = '', solution_provider_department = '', solution_provider_university = '' WHERE id = :proposal_id";
      $args = [
        ":solution_provider_uid" => 0,
        ":solution_status" => 0,
        ":proposal_id" => $proposal_id,
      ];
      \Drupal::database()->query($query, $args);
      
      /* sending email */
      $email_to = $user_data->getEmail();
      $from = $config->get('lab_migration_from_email') ?: \Drupal::config('system.site')->get('mail');
      $bcc = $user->getEmail() . ', ' . ($config->get('lab_migration_emails') ?: '');
      $cc = $config->get('lab_migration_cc_emails') ?: '';
      
      $param['solution_proposal_disapproved']['proposal_id'] = $proposal_id;
      $param['solution_proposal_disapproved']['user_id'] = $proposal_data->solution_provider_uid;
      $param['solution_proposal_disapproved']['message'] = $form_state->getValue(['message']);
      $param['solution_proposal_disapproved']['headers'] = [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ];
      
      // MIGRATED [NEW API]: using plugin.manager.mail service
      $mailManager = \Drupal::service('plugin.manager.mail');
      $result = $mailManager->mail('lab_migration', 'solution_proposal_disapproved', $email_to, \Drupal::languageManager()->getDefaultLanguage()->getId(), $param, $from, TRUE);
      
      if (!$result['result']) {
        \Drupal::messenger()->addError($this->t('Error sending email message.'));
      }
      
      \Drupal::messenger()->addStatus($this->t('Lab migration solution proposal dis-approved. User has been notified of the dis-approval.'));
      $form_state->setRedirectUrl(Url::fromUserInput('/lab-migration/manage-proposal/pending-solution-proposal'));
      return;
    }
  }

}


