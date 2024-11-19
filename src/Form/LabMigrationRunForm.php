<?php
/**
 * @file
 * Contains \Drupal\lab_migration\Form\LabMigrationRunForm.
 */

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\DataCommand;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;


class LabMigrationRunForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_run_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $options_first = \Drupal::service("lab_migration_global")->_list_of_labs();
    // var_dump($options);
    $options_two = \Drupal::service("lab_migration_global")->_ajax_get_experiment_list();
    $select_two = !$form_state->getValue(['lab_experiment_list']) ? $form_state->getValue(['lab_experiment_list']) : key($options_two);
    // $url_lab_id = (int) arg(2);
    $route_match = \Drupal::routeMatch();

$url_lab_id = (int) $route_match->getParameter('url_lab_id');
    if (!$url_lab_id) {
      $selected = !$form_state->getValue(['lab']) ? $form_state->getValue(['lab']) : key($options_first);
    }
    elseif ($url_lab_id == '') {
      $selected = 0;
    }
    else {
      $selected = $url_lab_id;
      ;
    }
    $form = [];
    $form['lab'] = [
      '#type' => 'select',
      '#title' => t('Title of the lab'),
      '#options' => \Drupal::service("lab_migration_global")->_list_of_labs(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => '::ajax_experiment_list_callback'
        ],
    ];
    if (!$url_lab_id) {
      $form['selected_lab'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab"></div>',
      ];
      $form['selected_lab_r'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab_r"></div>',
      ];
      $form['selected_lab_pdf'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab_pdf"></div>',
      ];
      $form['lab_details'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_lab_details"></div>',
      ];
      $form['lab_experiment_list'] = [
        '#type' => 'select',
        '#title' => t('Titile of the experiment'),
        '#options' => \Drupal::service("lab_migration_global")->_ajax_get_experiment_list($selected),
        //'#default_value' => isset($form_state['values']['lab_experiment_list']) ? $form_state['values']['lab_experiment_list'] : '',
            '#ajax' => [
          'callback' => '::ajax_solution_list_callback'
          ],
        '#prefix' => '<div id="ajax_selected_experiment">',
        '#suffix' => '</div>',
        '#states' => [
          'invisible' => [
            ':input[name="lab"]' => [
              'value' => 0
              ]
            ]
          ],
      ];
      $form['download_experiment'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_download_experiments"></div>',
      ];
      $form['lab_solution_list'] = [
        '#type' => 'select',
        '#title' => t('Solution'),
        '#options' => \Drupal::service("lab_migration_global")->_ajax_get_solution_list($select_two),
        //'#default_value' => isset($form_state['values']['lab_solution_list']) ? 
        //$form_state['values']['lab_solution_list'] : '',
            '#ajax' => [
          'callback' => '::ajax_solution_files_callback'
          ],
        '#prefix' => '<div id="ajax_selected_solution">',
        '#suffix' => '</div>',
        '#states' => [
          'invisible' => [
            ':input[name="lab"]' => [
              'value' => 0
              ]
            ]
          ],
      ];
      $form['download_solution'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_download_experiment_solution"></div>',
      ];
      $form['edit_solution'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_edit_experiment_solution"></div>',
      ];
      $form['solution_files'] = [
        '#type' => 'item',
        //  '#title' => t('List of solution_files'),
            '#markup' => '<div id="ajax_solution_files"></div>',
        '#states' => [
          'invisible' => [
            ':input[name="lab"]' => [
              'value' => 0
              ]
            ]
          ],
      ];
    }
    else {
      $lab_default_value = $url_lab_id;
      $form['selected_lab'] = [
        '#type' => 'item',
        //'#markup' => '<div id="ajax_selected_lab">' . Link::fromTextAndUrl('Download Lab Solutions', 'lab-migration/download/lab/' . $lab_default_value) . '</div>',
      ];
      /* $form['selected_lab_pdf'] = array(
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab_pdf">'. Link::fromTextAndUrl('Download PDF of Lab Solutions', 'lab-migration/generate-lab/' . $lab_default_value . '/1') .'</div>',
        
        );*/
      /*if ($lab_default_value == '2')
          {
            $form['selected_lab_r'] = array(
                '#type' => 'item',
                '#markup' => '<div id="ajax_selected_lab_r">' . Link::fromTextAndUrl('Download Lab Solutions (r Version)', 'lab-migration-uploads/r_Version.zip') . '</div>'
            );
          }*/
      // $form['lab_details'] = [
      //   '#type' => 'item',
      //   '#markup' => '<div id="ajax_lab_details">' . _lab_details($lab_default_value) . '</div>',
      // ];
      $form['lab_experiment_list'] = [
        '#type' => 'select',
        '#title' => t('Titile of the experiment'),
        '#options' =>  \Drupal::service("lab_migration_global")->_ajax_get_experiment_list($selected),
        // '#default_value' => isset($form_state['values']['lab_experiment_list']) ? $form_state['values']['lab_experiment_list'] : '',
            '#ajax' => [
          'callback' => '::ajax_solution_list_callback'
          ],
        '#prefix' => '<div id="ajax_selected_experiment">',
        '#suffix' => '</div>',
        '#states' => [
          'invisible' => [
            ':input[name="lab"]' => [
              'value' => 0
              ]
            ]
          ],
      ];
      $form['download_experiment'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_download_experiments"></div>',
      ];
      $form['lab_solution_list'] = [
        '#type' => 'select',
        '#title' => t('Solution'),
        '#options' =>  \Drupal::service("lab_migration_global")->_ajax_get_solution_list($select_two),
        '#default_value' => !$form_state->getValue(['lab_solution_list']) ? $form_state->getValue(['lab_solution_list']) : '',
        '#ajax' => [
          'callback' => '::ajax_solution_files_callback'
          ],
        '#prefix' => '<div id="ajax_selected_solution">',
        '#suffix' => '</div>',
        '#states' => [
          'invisible' => [
            ':input[name="lab_experiment_list"]' => [
              'value' => 0
              ]
            ]
          ],
      ];
      $form['download_solution'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_download_experiment_solution"></div>',
      ];
      $form['edit_solution'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_edit_experiment_solution"></div>',
      ];
      $form['solution_files'] = [
        '#type' => 'item',
        //  '#title' => t('List of solution_files'),
            '#markup' => '<div id="ajax_solution_files"></div>',
        '#states' => [
          'invisible' => [
            ':input[name="lab_experiment_list"]' => [
              'value' => 0
              ]
            ]
          ],
      ];
    }
    /*
    $form['message'] = array(
    '#type' => 'textarea',
    '#title' => t('If Dis-Approved please specify reason for Dis-Approval'),
    '#prefix' => '<div id= "message_submit">',   
    '#states' => array('invisible' => array(':input[name="lab"]' => array('value' => 0,),),), 
    
    );
    
    $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),      
    '#suffix' => '</div>',
    '#states' => array('invisible' => array(':input[name="lab"]' => array('value' => 0,),),),
    
    );*/
    return $form;
  }
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  }

  /**
 * AJAX callback for bulk solution list.
 */
function ajax_bulk_solution_list_callback(array &$form, FormStateInterface $form_state) {
  $response = new AjaxResponse();

  $experiment_list_default_value = $form_state->getValue('lab_experiment_list');

  if ($experiment_list_default_value != 0) {
    // Update the form elements.
    $form['lab_experiment_actions']['#options'] = \Drupal::service("lab_migration_global")->_bulk_list_experiment_actions();
    $form['lab_solution_list']['#options'] = _ajax_bulk_get_solution_list($experiment_list_default_value);

    // Add commands to update the various parts of the form.
    $download_experiment_url = Url::fromUri('internal:/lab-migration/full-download/experiment/' . $experiment_list_default_value);
    $download_link = Link::fromTextAndUrl('Download Experiment', $download_experiment_url)->toString();

    $response->addCommand(new HtmlCommand('#ajax_download_experiment', $download_link));
    $response->addCommand(new DataCommand('#ajax_selected_experiment', 'form_state_value_select', $experiment_list_default_value));
    $response->addCommand(new HtmlCommand('#ajax_selected_experiment', \Drupal::service('renderer')->render($form['lab_experiment_list'])));
    $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_action', \Drupal::service('renderer')->render($form['lab_experiment_actions'])));
    $response->addCommand(new HtmlCommand('#ajax_selected_solution', \Drupal::service('renderer')->render($form['lab_solution_list'])));
    $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', ''));
    $response->addCommand(new HtmlCommand('#ajax_solution_files', ''));
    $response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', ''));
    $response->addCommand(new HtmlCommand('#ajax_edit_experiment_solution', ''));
  }
  else {
    // Reset all fields if no experiment is selected.
    $response->addCommand(new HtmlCommand('#ajax_download_experiment', ''));
    $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_action', ''));
    $response->addCommand(new HtmlCommand('#ajax_selected_solution', ''));
    $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', ''));
    $response->addCommand(new HtmlCommand('#ajax_solution_files', ''));
    $response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', ''));
    $response->addCommand(new HtmlCommand('#ajax_edit_experiment_solution', ''));
  }

  return $response;
}

/**
 * AJAX callback for bulk solution files.
 */
// function ajax_bulk_solution_files_callback(array &$form, FormStateInterface $form_state) {
//   $response = new AjaxResponse();

//   $solution_list_default_value = $form_state->getValue('lab_solution_list');

//   if ($solution_list_default_value != 0) {
//     // Update the solution actions dropdown.
//     $form['lab_experiment_solution_actions']['#options'] = _bulk_list_solution_actions();
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', \Drupal::service('renderer')->render($form['lab_experiment_solution_actions'])));

//     // Fetch solution files from the database.
//     $solution_files_rows = [];
//     $query = \Drupal::database()->select('lab_migration_solution_files', 'f')
//       ->fields('f')
//       ->condition('solution_id', $solution_list_default_value);
//     $solution_list_q = $query->execute();

//     foreach ($solution_list_q as $solution_list_data) {
//       // Determine the file type.
//       $solution_file_type = match ($solution_list_data->filetype) {
//         'S' => 'Source or Main file',
//         'R' => 'Result file',
//         'X' => 'xcos file',
//         default => 'Unknown',
//       };

//       $solution_files_rows[] = [
//         Link::fromTextAndUrl($solution_list_data->filename, Url::fromRoute('lab_migration.download_file', ['id' => $solution_list_data->id]))->toString(),
//         $solution_file_type,
//       ];

//       // Add PDF file link if applicable.
//       if (strlen($solution_list_data->pdfpath) >= 5) {
//         $pdfname = substr($solution_list_data->pdfpath, strrpos($solution_list_data->pdfpath, '/') + 1);
//         $solution_files_rows[] = [
//           Link::fromTextAndUrl($pdfname, Url::fromRoute('lab_migration.download_pdf', ['id' => $solution_list_data->id]))->toString(),
//           'PDF File',
//         ];
//       }
//     }

//     // Fetch dependency files.
//     $query = \Drupal::database()->select('lab_migration_solution_dependency', 'd')
//       ->fields('d')
//       ->condition('solution_id', $solution_list_default_value);
//     $dependency_q = $query->execute();

//     foreach ($dependency_q as $dependency_data) {
//       $dependency_query = \Drupal::database()->select('lab_migration_dependency_files', 'df')
//         ->fields('df')
//         ->condition('id', $dependency_data->dependency_id);
//       $dependency_file = $dependency_query->execute()->fetchObject();

//       if ($dependency_file) {
//         $solution_files_rows[] = [
//           Link::fromTextAndUrl($dependency_file->filename, Url::fromRoute('lab_migration.download_dependency', ['id' => $dependency_file->dependency_id]))->toString(),
//           'Dependency file',
//         ];
//       }
//     }

//     // Create the files table.
//     $solution_files_header = ['Filename', 'Type'];
//     $solution_files = [
//       '#theme' => 'table',
//       '#header' => $solution_files_header,
//       '#rows' => $solution_files_rows,
//     ];

//     $form['solution_files']['#title'] = 'List of solution files';
//     $form['solution_files']['#markup'] = \Drupal::service('renderer')->render($solution_files);

//     // Add commands to update relevant parts of the form.
//     $download_solution_url = Url::fromRoute('lab_migration.download_solution', ['id' => $solution_list_default_value]);
//     $edit_solution_url = Url::fromRoute('lab_migration.edit_code', ['id' => $solution_list_default_value]);

//     $response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', Link::fromTextAndUrl('Download Solution', $download_solution_url)->toString()));
//     $response->addCommand(new HtmlCommand('#ajax_edit_experiment_solution', Link::fromTextAndUrl('Edit Solution', $edit_solution_url)->toString()));
//     $response->addCommand(new HtmlCommand('#ajax_solution_files', \Drupal::service('renderer')->render($form['solution_files'])));
//   }
//   else {
//     // Clear all related fields if no solution is selected.
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', ''));
//     $response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', ''));
//     $response->addCommand(new HtmlCommand('#ajax_edit_experiment_solution', ''));
//     $response->addCommand(new HtmlCommand('#ajax_solution_files', ''));
//   }

//   return $response;
// }

// /**
//  * AJAX callback for bulk experiment list.
//  */
// function ajax_bulk_experiment_list_callback(array &$form, FormStateInterface $form_state) {
//   $response = new AjaxResponse();

//   $lab_default_value = $form_state->getValue('lab');

//   if ($lab_default_value != 0) {
//     // Add commands for when a lab is selected.
//     $download_url = Url::fromUri('internal:/lab-migration/full-download/lab/' . $lab_default_value);
//     $download_link = Link::fromTextAndUrl(
//       'Download',
//       $download_url
//     )->toString() . ' ' . t('(Download all the approved and unapproved solutions of the entire lab)');
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab', $download_link));

//     // Update form elements dynamically.
//     $form['lab_actions']['#options'] = _bulk_list_lab_actions();
//     $form['lab_experiment_list']['#options'] = _ajax_bulk_get_experiment_list($lab_default_value);

//     // Replace updated form elements.
//     $response->addCommand(new DataCommand('#ajax_selected_lab', 'form_state_value_select', $form_state->getValue('lab_experiment_list')));
//     $response->addCommand(new ReplaceCommand('#ajax_selected_experiment', \Drupal::service('renderer')->render($form['lab_experiment_list'])));
//     $response->addCommand(new ReplaceCommand('#ajax_selected_lab_action', \Drupal::service('renderer')->render($form['lab_actions'])));
    
//     // Clear other fields.
//     $response->addCommand(new HtmlCommand('#ajax_selected_solution', ''));
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_action', ''));
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', ''));
//     $response->addCommand(new HtmlCommand('#ajax_solution_files', ''));
//     $response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', ''));
//     $response->addCommand(new HtmlCommand('#ajax_edit_experiment_solution', ''));
//   } 
//   else {
//     // Clear all related fields if no lab is selected.
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab', ''));
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab_pdf', ''));
//     $response->addCommand(new DataCommand('#ajax_selected_lab', 'form_state_value_select', $form_state->getValue('lab')));
//     $response->addCommand(new HtmlCommand('#ajax_selected_experiment', ''));
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab_action', ''));
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_action', ''));
//     $response->addCommand(new HtmlCommand('#ajax_download_experiment', ''));
//     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', ''));
//     $response->addCommand(new HtmlCommand('#ajax_solution_files', ''));
//     $response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', ''));
//     $response->addCommand(new HtmlCommand('#ajax_edit_experiment_solution', ''));
//   }

//   return $response;
// }



//   function bootstrap_table_format($headers, $rows)
//   {
//     $thead = "";
//     $tbody = "";
//     foreach ($headers as $header)
//       {
//         $thead .= "<th>{$header}</th>";
//       }
//     foreach ($rows as $row)
//       {
//         $tbody .= "<tr>";
//         foreach ($row as $data)
//           {
//             $tbody .= "<td>{$data}</td>";
//           }
//         $tbody .= "</tr>";
//       }
//     $table = "
//             <table class='table table-bordered table-hover' style='margin-left:-140px'>
//                 <thead>{$thead}</thead>
//                 <tbody>{$tbody}</tbody>
//             </table>
//         ";
//     return $table;
//   }
//   function _list_of_labs()
//   {
//     $lab_titles = array(
//         '0' => 'Please select...'
//     );
//     //$lab_titles_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE solution_display = 1 ORDER BY lab_title ASC");
//     $query = \Drupal::database()->select('lab_migration_proposal');
//     $query->fields('lab_migration_proposal');
//     $query->condition('solution_display', 1);
//     $query->condition('approval_status', 3);
//     $query->orderBy('lab_title', 'ASC');
//     $lab_titles_q = $query->execute();
//     while ($lab_titles_data = $lab_titles_q->fetchObject())
//       {
//         $lab_titles[$lab_titles_data->id] = $lab_titles_data->lab_title . ' (Proposed by ' . $lab_titles_data->name_title .' '.$lab_titles_data->name . ')';
//       }
//     return $lab_titles;
//   }

//   function _ajax_get_experiment_list($lab_default_value = '') {
//     $experiments = [
//       '0' => 'Please select...',
//     ];
  
//     // Fetch experiments from the database.
//     $query = \Drupal::database()->select('lab_migration_experiment', 'e')
//       ->fields('e', ['id', 'number', 'title'])
//       ->condition('proposal_id', $lab_default_value)
//       ->orderBy('number', 'ASC');
//     $experiments_q = $query->execute();
  
//     foreach ($experiments_q as $experiment) {
//       $experiments[$experiment->id] = $experiment->number . '. ' . $experiment->title;
//     }
  
//     return $experiments;
//   }
  
//   function _ajax_get_solution_list($lab_experiment_list = '') {
//     $solutions = [
//       '0' => 'Please select...',
//     ];
  
//     // Build and execute the query.
//     $query = \Drupal::database()->select('lab_migration_solution', 's')
//       ->fields('s', ['id', 'code_number', 'caption'])
//       ->condition('experiment_id', $lab_experiment_list);
  
//     // Execute the query and process results.
//     $solutions_q = $query->execute();
  
//     foreach ($solutions_q as $solution) {
//       $solutions[$solution->id] = $solution->code_number . ' (' . $solution->caption . ')';
//     }
  
//     return $solutions;
//   }
  
//   function _lab_information($proposal_id)
//   {
//     //$lab_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
//     $query = \Drupal::database()->select('lab_migration_proposal');
//     $query->fields('lab_migration_proposal');
//     $query->condition('id', $proposal_id);
//     $query->condition('approval_status', 3);
//     $lab_q = $query->execute();
//     $lab_data = $lab_q->fetchObject();
//     if($lab_data){
//      return $lab_data;
//       }
//     else 
//       {
//         return ;
//       }
   
//   }

//   function _lab_details($lab_default_value)
//   {
//     //$lab_default_value = $form_state['values']['lab'];
//     $lab_details = _lab_information($lab_default_value);
//     if ($lab_default_value != 0)
//       {
//         if ($lab_details){
//         if ($lab_details->solution_provider_uid > 0)
//           {
//             $user_solution_provider = user_load($lab_details->solution_provider_uid);
//             if ($user_solution_provider)
//               {
//                 $solution_provider = '<span style="color: rgb(128, 0, 0);"><strong>Solution Provider</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Solution Provider Name:</strong> ' . $lab_details->solution_provider_name_title . ' ' . $lab_details->solution_provider_name . '</li>' . '<li><strong>Department:</strong> ' . $lab_details->solution_provider_department . '</li>' . '<li><strong>University:</strong> ' . $lab_details->solution_provider_university . '</li>' . '</ul>';
//               }
//             else
//               {
//                 $solution_provider = '<span style="color: rgb(128, 0, 0);"><strong>Solution Provider</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Solution Provider: </strong> (Open) </li>' . '</ul>';
//               }
//           }
//         else
//           {
//             $solution_provider = '<span style="color: rgb(128, 0, 0);"><strong>Solution Provider</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Solution Provider: </strong> (Open) </li>' . '</ul>';
//           }}
//           else{
//           // drupal_goto('lab-migration/lab-migration-run');
//           $response = new RedirectResponse('/lab_migration/lab_migration-run');
//           $response->send();
          
//       }
//     $form['lab_details']['#markup'] = '<span style="color: rgb(128, 0, 0);"><strong>About the Lab</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Proposer Name:</strong> ' . $lab_details->name_title . ' ' . $lab_details->name . '</li>' . '<li><strong>Title of the Lab:</strong> ' . $lab_details->lab_title . '</li>' . '<li><strong>Department:</strong> ' . $lab_details->department . '</li>' . '<li><strong>University:</strong> ' . $lab_details->university . '</li>' . '<li><strong>R Version:</strong> ' . $lab_details->r_version . '</li>' . '<li><strong>Operating System:</strong> ' . $lab_details->operating_system . '</li>' . '</ul>' . $solution_provider;
//     $details = $form['lab_details']['#markup'];
//     return $details;
//     }
//   }

}
