<?php
namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Response;

class ExportCSVForm extends FormBase
{

  public function getFormId()
  {
    return 'lab_migration_export_csv_form';
  }

  // Removed local ajaxTableCallback - replaced by LabMigrationAjaxHelper::getPreviewTableWrapper.

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => [
        '' => '- Any -',
        '0' => 'Pending',
        '1' => 'Approved',
        '2' => 'Dis-approved',
        '3' => 'Solved',
      ],
      '#ajax' => [
        'callback' => ['\Drupal\lab_migration\Services\LabMigrationAjaxHelper', 'getPreviewTableWrapper'],
        'wrapper' => 'export-preview-table-wrapper',
        'event' => 'change',
      ],
    ];

    // Build year options dynamically
    $current_year = date('Y');
    $year_options = ['' => '- Any -'];
    for ($y = $current_year; $y >= 2020; $y--) {
      $year_options[$y] = $y;
    }

    $form['year'] = [
      '#type' => 'select',
      '#title' => $this->t('Year of Submission'),
      '#options' => $year_options,
      '#ajax' => [
        'callback' => ['\Drupal\lab_migration\Services\LabMigrationAjaxHelper', 'getPreviewTableWrapper'],
        'wrapper' => 'export-preview-table-wrapper',
        'event' => 'change',
      ],
    ];

    $form['export'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export CSV'),
    ];

    // Retrieve selected filters from form state.
    $status = $form_state->getValue('status');
    $year = $form_state->getValue('year');

    // Query the database to build the preview table.
    $query = \Drupal::database()->select('lab_migration_proposal', 'p');
    $query->join('users_field_data', 'u', 'p.uid = u.uid');
    $query->fields('p', [
      'name_title', 'name', 'contact_ph', 'department', 'university',
      'city', 'state', 'country', 'pincode', 'lab_title', 'approval_status', 'creation_date'
    ]);
    $query->addField('u', 'mail', 'email');

    if ($status !== '' && $status !== NULL) {
      $query->condition('p.approval_status', $status);
    }
    if ($year !== '' && $year !== NULL) {
      $start_timestamp = strtotime("$year-01-01");
      $end_timestamp = strtotime("$year-12-31 23:59:59");
      $query->condition('p.creation_date', $start_timestamp, '>=');
      $query->condition('p.creation_date', $end_timestamp, '<=');
    }

    $results = $query->execute()->fetchAll();

    $form['preview_table_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'export-preview-table-wrapper'],
    ];

    $header = ['Name', 'Email', 'University', 'Lab Title', 'Status', 'Date'];
    $rows = [];

    foreach ($results as $row) {
      switch ($row->approval_status) {
        case 0: $status_label = 'Pending'; break;
        case 1: $status_label = 'Approved'; break;
        case 2: $status_label = 'Dis-approved'; break;
        case 3: $status_label = 'Solved'; break;
        default: $status_label = 'Unknown'; break;
      }

      $rows[] = [
        $row->name_title . ' ' . $row->name,
        $row->email,
        $row->university,
        $row->lab_title,
        $status_label,
        date('d-m-Y', $row->creation_date),
      ];
    }

    $form['preview_table_wrapper']['preview_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No proposals found for the selected filters.'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $status = $form_state->getValue('status');
    $year = $form_state->getValue('year');

    $query = \Drupal::database()->select('lab_migration_proposal', 'p');
    $query->join('users_field_data', 'u', 'p.uid = u.uid');
    $query->fields('p', [
      'name_title',
      'name',
      'contact_ph',
      'department',
      'university',
      'city',
      'state',
      'country',
      'pincode',
      'lab_title',
      'approval_status',
      'creation_date'
    ]);
    $query->addField('u', 'mail', 'email');

    // Filter by status if selected
    if ($status !== '') {
      $query->condition('p.approval_status', $status);
    }

    // Filter by year if selected
    if ($year !== '') {
      $start_timestamp = strtotime("$year-01-01");
      $end_timestamp = strtotime("$year-12-31 23:59:59");
      $query->condition('p.creation_date', $start_timestamp, '>=');
      $query->condition('p.creation_date', $end_timestamp, '<=');
    }

    $results = $query->execute()->fetchAll();

    // Build CSV content
    $csv_output = fopen('php://temp', 'w');
    // Write header row
    fputcsv($csv_output, [
      'Title',
      'Name',
      'Email',
      'Contact No.',
      'Department/Branch',
      'University/Institute',
      'City',
      'State',
      'Country',
      'Pincode',
      'Lab Title',
      'Status',
      'Date of Submission'
    ]);

    foreach ($results as $row) {
      // Map status code to label
      switch ($row->approval_status) {
        case 0:
          $status_label = 'Pending';
          break;
        case 1:
          $status_label = 'Approved';
          break;
        case 2:
          $status_label = 'Dis-approved';
          break;
        case 3:
          $status_label = 'Solved';
          break;
        default:
          $status_label = 'Unknown';
          break;
      }

      fputcsv($csv_output, [
        $row->name_title,
        $row->name,
        $row->email,
        $row->contact_ph,
        $row->department,
        $row->university,
        $row->city,
        $row->state,
        $row->country,
        $row->pincode,
        $row->lab_title,
        $status_label,
        date('d-m-Y', $row->creation_date),
      ]);
    }

    // Read the CSV content from the temp stream
    rewind($csv_output);
    $csv_content = stream_get_contents($csv_output);
    fclose($csv_output);

    // Send CSV as a downloadable response
    $response = new Response($csv_content);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="lab_migration_proposals.csv"');
    $form_state->setResponse($response);
  }

}