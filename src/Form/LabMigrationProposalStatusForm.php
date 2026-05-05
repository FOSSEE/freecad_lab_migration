<?php
// Drupal 11 Migration - Verified and migrated 2026-04-24
/**
 * @file
 * Contains \Drupal\lab_migration\Form\LabMigrationProposalStatusForm.
 */

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\user\Entity\User;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Mail\MailManagerInterface;


class LabMigrationProposalStatusForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_proposal_status_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    // $proposal_id = (int) arg(3);
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
        //return new Trusted// DEPRECATED [OLD API]: RedirectResponse without new 
// MIGRATED [NEW API]: using return new RedirectResponse
return new \Symfony\Component\HttpFoundation\RedirectResponse('/lab-migration/manage-proposal');
        return;
      }
    }
    else {
      \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      // // DEPRECATED [OLD API]: RedirectResponse without new 
// MIGRATED [NEW API]: using return new RedirectResponse
return new \Symfony\Component\HttpFoundation\RedirectResponse('lab-migration/manage-proposal');
     // return new RedirectResponse('/lab-migration/manage-proposal');
      return;
    }

    $form['name'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl($proposal_data->name_title . ' ' . $proposal_data->name,Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid]))->toString(),
     '#title' => $this->t('Name'),
    ];
    $form['email_id'] = [
      '#type' => 'item',
      '#markup' => User::load($proposal_data->uid)->getEmail(),
      '#title' => $this->t('Email'),
    ];
    $form['contact_ph'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->contact_ph,
      '#title' => $this->t('Contact No.'),
    ];
    $form['department'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->department,
      '#title' => $this->t('Department/Branch'),
    ];
    $form['university'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->university,
      '#title' => $this->t('University/Institute'),
    ];
    $form['country'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->country,
      '#title' => $this->t('Country'),
    ];
    $form['all_state'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->state,
      '#title' => $this->t('State'),
    ];
    $form['city'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->city,
      '#title' => $this->t('City'),
    ];
    $form['pincode'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->pincode,
      '#title' => $this->t('Pincode/Postal code'),
    ];
    $form['operating_system'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->operating_system,
      '#title' => $this->t('Operating System'),
    ];
    $form['version'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->version,
      // '#markup' => Xss::filter($proposal_data->version),
      '#title' => $this->t('R Version'),
    ];
    $form['syllabus_link'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->syllabus_link,
      '#title' => $this->t('Syllabus Link'),
    ];
    $form['lab_title'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->lab_title,
      '#title' => $this->t('Title of the Lab'),
    ];
    // var_dump($form);die;
    /* get experiment details */
    $experiment_list = '<ul>';
    //$experiment_q = $injected_database->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d ORDER BY id ASC", $proposal_id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
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
    if ($proposal_data->solution_provider_uid == 0) {
      $solution_provider = "User will not provide solution, we will have to provide solution";
    }
    else {
      if ($proposal_data->solution_provider_uid == $proposal_data->uid) {
        $solution_provider = "Proposer will provide the solution of the lab";
      }
      else {
        $solution_provider_user_data = User::load($proposal_data->solution_provider_uid);
        if ($solution_provider_user_data) {
          //$solution_provider = "Solution will be provided by user " . Link::fromTextAndUrl($solution_provider_user_data->name, 'user/' . $proposal_data->solution_provider_uid);
        }
        else {
          $solution_provider = "User does not exists";
        }
      }
    }
    $form['solution_provider_uid'] = [
      '#type' => 'item',
      '#title' => $this->t('Who will provide the solution'),
      '#markup' => $solution_provider,
    ];
    /*$form['solution_display'] = array(
    '#type' => 'item',
    '#title' => $this->t('Display the solution on the www.r.fossee.in website'),
    '#markup' => ($proposal_data->solution_display == 1) ? "Yes" : "No",
    );*/
    $proposal_status = '';
    switch ($proposal_data->approval_status) {
      case 0:
        $proposal_status = $this->t('Pending');
        break;
      case 1:
        $proposal_status = $this->t('Approved');
        break;
      case 2:
        $proposal_status = $this->t('Dis-approved');
        break;
      case 3:
        $proposal_status = $this->t('Completed');
        break;
      default:
        $proposal_status = $this->t('Unkown');
        break;
    }
    $form['proposal_status'] = [
      '#type' => 'item',
      '#markup' => $proposal_status,
      '#title' => $this->t('Proposal Status'),
    ];
    if ($proposal_data->approval_status == 0) {
      if ($proposal_data->expected_completion_date == 0) {
        $form['completion_date'] = [
          '#type' => 'item',
          '#markup' => 'Expecting date of completion soon',
          '#title' => $this->t('Date of Completion'),
        ];
      }
      else {
        $form['completion_date'] = [
          '#type' => 'item',
          '#markup' => date('d-m-Y', $proposal_data->expected_completion_date),
          '#title' => $this->t('Expected date of completion'),
        ];
      }
    }
    if ($proposal_data->approval_status == 1) {
      if ($proposal_data->expected_completion_date == 0) {
        $form['completion_date'] = [
          '#type' => 'item',
          '#markup' => 'Expecting date of completion soon',
          '#title' => $this->t('Date of Completion'),
        ];
      }
      else {
        $form['completion_date'] = [
          '#type' => 'item',
          '#markup' => date('d-m-Y', $proposal_data->expected_completion_date),
          '#title' => $this->t('Date of Completion'),
        ];
      }
    }
    if ($proposal_data->approval_status == 0) {
      $form['approve'] = [
        '#type' => 'item',
        //'#markup' => Link::fromTextAndUrl('Click here', 'lab-migration/manage-proposal/approve/' . $proposal_id),
        '#title' => $this->t('Approve'),
      ];
    }
    if ($proposal_data->approval_status == 1) {
      $form['completed'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Completed'),
        '#description' => $this->t('Check if user has provided all experiment solutions.'),
      ];
    }
    if ($proposal_data->approval_status == 2) {
      $form['message'] = [
        '#type' => 'item',
        '#markup' => $proposal_data->message,
        '#title' => $this->t('Reason for disapproval'),
      ];
    }
    //DATE OF COMPLETION
    if ($proposal_data->approval_status == 3) {
      $form['completion_date'] = [
        '#type' => 'item',
        '#markup' => date('d-m-Y', $proposal_data->expected_completion_date),
        '#title' => $this->t('Date of Completion'),
      ];
    }
    if ($proposal_data->approval_status == 2) {
      $form['completion_date'] = [
        '#type' => 'item',
        '#markup' => "Proposal is disapproved",
        '#title' => $this->t('Date of Completion'),
      ];
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    $form['cancel'] = [
      '#type' => 'markup',
     // '#markup' => Link::fromTextAndUrl($this->t('Cancel'), 'lab-migration/manage-proposal/all'),
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    // $proposal_id = (int) arg(3);
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
        // DEPRECATED [OLD API]: send() on RedirectResponse 
// MIGRATED [NEW API]: returning RedirectResponse directly
return new \Symfony\Component\HttpFoundation\RedirectResponse('/lab-migration/manage-proposal/pending-solution');        return;
      }
    }
    else {
      \Drupal::messenger()->addError($this->t('Invalid proposal selected. Please try again.'));
      // // DEPRECATED [OLD API]: RedirectResponse without new 
// MIGRATED [NEW API]: using return new RedirectResponse
return new \Symfony\Component\HttpFoundation\RedirectResponse('lab-migration/manage-proposal');
      // DEPRECATED [OLD API]: send() on RedirectResponse 
// MIGRATED [NEW API]: returning RedirectResponse directly
return new \Symfony\Component\HttpFoundation\RedirectResponse('lab_migration.proposal_pending_solution');      return;
    }
    /* set the book status to completed */
    if ($form_state->getValue(['completed']) == 1) {
      $up_query = "UPDATE lab_migration_proposal SET approval_status = :approval_status , expected_completion_date = :expected_completion_date WHERE id = :proposal_id";
      $args = [
        ":approval_status" => '3',
        ":proposal_id" => $proposal_id,
        ":expected_completion_date" => time(),
      ];
       $result = \Drupal::database()->query($up_query, $args);
      
      // \Drupal::service("lab_migration_global")->CreateReadmeFileLabMigration($proposal_id);
      // if (!$result) {
      //   \Drupal::messenger()->addError('Error in update status');
      //   return;
      // }

/* Load user */
$user_data = User::load($proposal_data->uid);

if (!$user_data) {
  \Drupal::messenger()->addError('User not found.');
  return;
}

/* To email */
$email_to = $user_data->getEmail();

if (empty($email_to)) {
  \Drupal::messenger()->addError('User email is empty.');
  return;
}

/* Config */
$config = \Drupal::config('lab_migration.settings');

$from = $config->get('lab_migration_from_email');
$bcc  = $config->get('lab_migration_emails');
$cc   = $config->get('lab_migration_cc_emails');

/* Mandatory fallback */
if (empty($from)) {
  $from = \Drupal::config('system.site')->get('mail');
}

/* Build params */
$params = [
  'proposal_id' => $proposal_id,
  'user_id' => $proposal_data->uid,
  'headers' => [
    'Cc' => $cc,
    'Bcc' => $bcc,
  ],
];

/* Language */
$langcode = $user_data->getPreferredLangcode() ?: 'en';

/* Send mail */
$mailManager = \Drupal::service('plugin.manager.mail');

$result = $mailManager->mail(
  'lab_migration',
  'proposal_completed',
  $email_to,
  $langcode,
  $params,
  $from,
  TRUE
);

/* Debug */
\Drupal::logger('lab_migration')->notice('<pre>@data</pre>', [
  '@data' => print_r($result, TRUE),
]);

if (empty($result['result'])) {
  \Drupal::messenger()->addError('Mail sending failed.');
}
else {
  \Drupal::messenger()->addStatus('Email sent successfully.');
}



      // if (!drupal_mail('lab_migration', 'proposal_completed', $email_to, language_default(), $param, $from, TRUE)) {
      //   \Drupal::messenger()->addError('Error sending email message.');
      // }
      /*$email_to = $user->mail . ', ' . $config->get('lab_migration_emails', '');;
        if (!drupal_mail('lab_migration', 'proposal_completed', $email_to , language_default(), $param, $config->get('lab_migration_from_email', NULL), TRUE))
        \Drupal::messenger()->addError('Error sending email message.');*/
      \Drupal::messenger()->addStatus('Congratulations! Lab Migration proposal has been marked as completed. User has been notified of the completion.', 'status');
    }
    $response = new RedirectResponse(Url::fromRoute('lab_migration.proposal_all')->toString());
    $response->send();
    return;
  }
}
