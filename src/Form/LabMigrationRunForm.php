<?php

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RendererInterface;


class LabMigrationRunForm extends FormBase {

  /**
  * {@inheritdoc}
  */
  public function getFormId() {
  return 'lab_migration_run_form';
  }
  
  /**
  * {@inheritdoc}
  */
  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state)

  {
    $options_first =$this->_list_of_labs();
    $options_two = $this->_ajax_get_experiment_list();
    // $select_two = isset($form_state['values']['lab_experiment_list']) ? $form_state['values']['lab_experiment_list'] : key($options_two);
    $select_two = $form_state->getValue('lab_experiment_list') ?: key($options_two);
    // $url_lab_id = (int) arg(2);
    $route_match = \Drupal::routeMatch();
$url_lab_id = (int) $route_match->getParameter('url_lab_id');
    if (!$url_lab_id)
      {
        // $selected = isset($form_state['values']['lab']) ? $form_state['values']['lab'] : key($options_first);
     $selected = $form_state->getValue('lab') ?: key($options_first);
      }
    elseif ($url_lab_id == '')
      {
        $selected = 0;
      }
    else
      {
        $selected = $url_lab_id;
        ;
      }
    $form = array();
    $form['lab'] = array(
        '#type' => 'select',
        '#title' => t('Title of the lab'),
        '#options' => $this->_list_of_labs(),
        '#default_value' => $selected,
        '#ajax' => [
            'callback' => '::ajax_experiment_list_callback'
        ]
    );
    // var_dump($this->_list_of_labs());die;
    // if (!$url_lab_id)
    //   {
    //     $form['selected_lab'] = array(
    //         '#type' => 'item',
    //         '#markup' => '<div id="ajax_selected_lab"></div>'
    //     );
    //     $form['selected_lab_r'] = array(
    //         '#type' => 'item',
    //         '#markup' => '<div id="ajax_selected_lab_r"></div>'
    //     );
    //     $form['selected_lab_pdf'] = array(
    //         '#type' => 'item',
    //         '#markup' => '<div id="ajax_selected_lab_pdf"></div>'
    //     );
    //     $form['lab_details'] = array(
    //         '#type' => 'item',
    //         '#markup' => '<div id="ajax_lab_details"></div>'
    //     );
    //     $form['lab_experiment_list'] = array(
    //         '#type' => 'select',
    //         '#title' => t('Title of the experiment'),
    //         '#options' => $this->_ajax_get_experiment_list($selected),
    //         //'#default_value' => isset($form_state['values']['lab_experiment_list']) ? $form_state['values']['lab_experiment_list'] : '',
    //         '#ajax' => array(
    //             'callback' => '::ajax_solution_list_callback'
    //         ),
    //         '#prefix' => '<div id="ajax_selected_experiment">',
    //         '#suffix' => '</div>',
    //         '#states' => array(
    //             'invisible' => array(
    //                 ':input[name="lab"]' => array(
    //                     'value' => 0
    //                 )
    //             )
    //         )
    //     );
    //     // $form['download_experiment'] = array(
    //     //     '#type' => 'item',
    //     //     '#markup' => '<div id="ajax_download_experiments"></div>'
    //     // );
    //     $form['download_experiment'] = [
    //       '#type' => 'container',
    //       'ajax_download_experiments' => [
    //         '#type' => 'markup',
    //         '#markup' => '<div id="ajax_download_experiments">Download Experiment</div>',
    //       ],
    //     ];
        
      

    //     $form['lab_solution_list'] = array(
    //         '#type' => 'select',
    //         '#title' => t('Title of the Solution'),
    //         '#options' => $this->_ajax_get_solution_list($select_two),
    //         //'#default_value' => isset($form_state['values']['lab_solution_list']) ? 
    //         //$form_state['values']['lab_solution_list'] : '',
    //         '#ajax' => array(
    //             'callback' => '::ajax_solution_files_callback'
    //         ),
    //         '#prefix' => '<div id="ajax_selected_solution">',
    //         '#suffix' => '</div>',
    //         '#states' => array(
    //             'invisible' => array(
    //                 ':input[name="lab"]' => array(
    //                     'value' => 0
    //                 )
    //             )
    //         )
    //     );
       
    //   //   $form['download_solution'] = [
    //   //     '#type' => 'markup',
    //   //     '#markup' => '<div id="ajax_download_experiment_solution"></div>',
    //   // ];
    //   $form['download_solution'] = [
    //     '#type' => 'container',
    //     'ajax_download_solution' => [
    //       '#type' => 'markup',
    //       '#markup' => '<div id="ajax_download_solution"></div>',
    //     ],
    //   ];
      
        
        
    //     $form['edit_solution'] = array(
    //         '#type' => 'item',
    //         '#markup' => '<div id="ajax_edit_experiment_solution"></div>'
    //     );
    //     $form['solution_files'] = array(
    //         '#type' => 'item',
    //          '#title' => t('List of solution_files'),
    //         '#markup' => '<div id="ajax_solution_files">List of Solution Files</div>',
    //         '#states' => array(
    //             'invisible' => array(
    //                 ':input[name="lab"]' => array(
    //                     'value' => 0
    //                 )
    //             )
    //         )
    //     );
        
    //   }
    // else
    //   {
        $lab_default_value = $url_lab_id;
        $form['selected_lab'] = array(
            '#type' => 'item',
           
            $form['selected_lab'] = [
              '#type' => 'markup',
              '#markup' => Markup::create(
                '<div id="ajax_selected_lab">' . 
                Link::fromTextAndUrl(
                  $this->t('Download Lab Solutions'), 
                  Url::fromUri('internal:/lab_migration/download/lab/' . $lab_default_value)
                )->toString() . 
                '</div>'
              ),
            ]
            );
            
      
        /* $form['selected_lab_pdf'] = array(
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab_pdf">'. l('Download PDF of Lab Solutions', 'lab-migration/generate-lab/' . $lab_default_value . '/1') .'</div>',
        
        );*/
        /*if ($lab_default_value == '2')
          {
            $form['selected_lab_r'] = array(
                '#type' => 'item',
                '#markup' => '<div id="ajax_selected_lab_r">' . l('Download Lab Solutions (r Version)', 'lab-migration-uploads/r_Version.zip') . '</div>'
            );
          }*/
          //var_dump($this->_lab_details($lab_default_value));die;
        $form['lab_details'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_lab_details">' . $this->_lab_details($lab_default_value) . '</div>'
        );
        $form['lab_experiment_list'] = array(
            '#type' => 'select',
            '#title' => t('Title of the experiment'),
            '#options' => $this->_ajax_get_experiment_list($selected),
            // '#default_value' => isset($form_state['values']['lab_experiment_list']) ? $form_state['values']['lab_experiment_list'] : '',
            '#ajax' => [
                'callback' => '::ajax_solution_list_callback'
        ],
            '#prefix' => '<div id="ajax_selected_experiment">',
            '#suffix' => '</div>',
            '#states' => array(
                'invisible' => array(
                    ':input[name="lab"]' => array(
                        'value' => 0
                    )
                )
            )
        );
        $form['download_experiment'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_download_experiments"></div>'
        );
        $form['lab_solution_list'] = array(
            '#type' => 'select',
            '#title' => t('Solution'),
            '#options' => $this->_ajax_get_solution_list($select_two),
            // '#default_value' => isset($form_state['values']['lab_solution_list']) ? $form_state['values']['lab_solution_list'] : '',
            //'#default_value' => $form_state->getValue('lab_solution_list', ''),
            '#ajax' => [
                'callback' => '::ajax_solution_files_callback'
            ],
            '#prefix' => '<div id="ajax_selected_solution">',
            '#suffix' => '</div>',
            '#states' => array(
                'invisible' => array(
                    ':input[name="lab_experiment_list"]' => array(
                        'value' => 0
                    )
                )
            )
        );
        $form['download_solution'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_download_experiment_solution"></div>'
            
        );
        $form['edit_solution'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_edit_experiment_solution"></div>'
        );
        $form['solution_files'] = array(
            '#type' => 'item',
             '#title' => t('List of solution_files'),
            '#markup' => '<div id="ajax_solution_files"></div>',
            '#states' => array(
                'invisible' => array(
                    ':input[name="lab_experiment_list"]' => array(
                        'value' => 0
                    )
                )
            )
        );

    //  }
    return $form;
  }
  public function ajax_experiment_list_callback(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $commands = [];
    $lab_default_value = $form_state->getValue('lab');
    // var_dump($lab_default_value);die;
    if ($lab_default_value != 0) {
      // Set the lab details markup
      $form['lab_details']['#markup'] = $this->_lab_details($lab_default_value);
    
      // Get the lab details
      $lab_details = $this->_lab_information($lab_default_value);
      // var_dump($lab_details->solution_provider_uid);die;
      // var_dump($lab_details);die;
      $user_solution_provider = \Drupal\user\Entity\User::load($lab_details->solution_provider_uid);
      // If solution provider exists
      if ($lab_details->solution_provider_uid > 0) {
        // Download Lab Solutions link
        
        $response->addCommand(new HtmlCommand('#ajax_selected_lab', Link::fromTextAndUrl('Download Lab Solutions', Url::fromUri('internal:/lab-migration/download/lab/' . $lab_default_value))->toString()));
        $response->addCommand(new HtmlCommand('#ajax_lab_details', $this->_lab_details($lab_default_value)));
        
        // Additional conditions can be handled here as needed
        /*if ($lab_default_value == '2') {
          $commands[] = new HtmlCommand('#ajax_selected_lab_r', Link::fromTextAndUrl('Download Lab Solutions (r Version)', Url::fromUri('internal:/lab-migration_uploads/r_Version.zip'))->toString());
        }*/
      }
      else {
        // Clear the download links if no solution provider exists
        $commands[] = new HtmlCommand('#ajax_selected_lab', '');
        $commands[] = new HtmlCommand('#ajax_selected_lab_pdf', '');
        $commands[] = new HtmlCommand('#ajax_selected_lab_r', '');
      }
      
      // Set the lab details again
     
      
      // Update the experiment list options
      $form['lab_experiment_list']['#options'] = $this->_ajax_get_experiment_list($lab_default_value);
      
      // Replace the experiment list
      $response->addCommand(new ReplaceCommand('#ajax_selected_experiment', \Drupal::service('renderer')->render($form['lab_experiment_list'])));
      
      // Clear solution-related elements
      // $commands[] = new HtmlCommand('#ajax_selected_solution', '');
      // $commands[] = new HtmlCommand('#ajax_solution_files', '');
      // $commands[] = new HtmlCommand('#ajax_download_experiment_solution', '');
      // $commands[] = new HtmlCommand('#ajax_edit_experiment_solution', '');
      // $commands[] = new HtmlCommand('#ajax_download_experiments', '');
    }
    else {
      // Default options when no lab is selected
      $form['lab_experiment_list']['#options'] = $this->_ajax_get_experiment_list();
      
      // Replace experiment list with default options
      $commands[] = new ReplaceCommand('#ajax_selected_experiment', \Drupal::service('renderer')->render($form['lab_experiment_list']));
      
      // Clear all the other elements
      $commands[] = new HtmlCommand('#ajax_lab_details', '');
      $commands[] = new HtmlCommand('#ajax_selected_lab', '');
      $commands[] = new HtmlCommand('#ajax_selected_lab_r', '');
      $commands[] = new HtmlCommand('#ajax_selected_lab_pdf', '');
      $commands[] = new HtmlCommand('#ajax_selected_experiment', '');
      $commands[] = new HtmlCommand('#ajax_download_experiments', '');
      $commands[] = new HtmlCommand('#ajax_selected_solution', '');
      $commands[] = new HtmlCommand('#ajax_solution_files', '');
      $commands[] = new HtmlCommand('#ajax_download_experiment_solution', '');
      $commands[] = new HtmlCommand('#ajax_edit_experiment_solution', '');
    }
    
    // Return the response with commands
    
   // $response->addCommands($commands);
    return $response;
  }
  public function ajax_solution_list_callback(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $commands = [];
//var_dump("hi");die;
    $experiment_list_default_value = $form_state->getValue('lab_experiment_list');
    //var_dump($experiment_list_default_value);die;
    if ($experiment_list_default_value != 0) {
      // Update the solution list options
      $form['lab_solution_list']['#options'] = $this->_ajax_get_solution_list($experiment_list_default_value);
      
      // Add the commands to update the DOM
      $response->addCommand(new HtmlCommand('#ajax_download_experiments', Link::fromTextAndUrl('Download Experiment', Url::fromUri('internal:/lab-migration/download/experiment/' . $experiment_list_default_value))->toString()));
      $response->addCommand(new HtmlCommand('#ajax_selected_experiment', \Drupal::service('renderer')->render($form['lab_experiment_list'])));
      $response->addCommand(new HtmlCommand('#ajax_selected_solution', \Drupal::service('renderer')->render($form['lab_solution_list'])));
      // Uncomment if needed
      // $commands[] = new HtmlCommand('#ajax_solution_files', '');
      // $commands[] = new HtmlCommand('#ajax_download_experiment_solution', '');
      // $commands[] = new HtmlCommand('#ajax_edit_experiment_solution', '');
    }
    else {
      // Default options when no experiment is selected
      $form['lab_solution_list']['#options'] = $this->_ajax_get_solution_list();
      
      // Clear the DOM elements
          $commands = [];
          $response->addCommand(new HtmlCommand('#ajax_selected_solution', \Drupal::service('renderer')->render($form['lab_solution_list'])));
      $commands[] = new HtmlCommand('#ajax_download_experiments', '');
      $commands[] = new HtmlCommand('#ajax_selected_solution', '');
      $commands[] = new HtmlCommand('#ajax_solution_files', '');
      $commands[] = new HtmlCommand('#ajax_download_experiment_solution', '');
      $commands[] = new HtmlCommand('#ajax_edit_experiment_solution', '');
      // Uncomment if needed
      // $commands[] = new ReplaceCommand('#ajax_selected_experiment', \Drupal::service('renderer')->render($form['lab_experiment_list']));
    }
    
    // Return the response with commands
    // $response = new AjaxResponse();
    // $response->addCommand(new AppendCommand('#element-id', 'Updated content'));
    //return $response;
  

return $response;
  }
  
  public function ajax_solution_files_callback(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $response = new AjaxResponse();
   // $commands = [];
   var_dump("hi");die;
    $solution_list_default_value = $form_state->getValue('lab_solution_list');
  
    if ($solution_list_default_value != 0) {
      // Render experiment solution actions
      $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', \Drupal::service('renderer')->render($form['lab_experiment_solution_actions'])));
  
      // Query solution files
      $query = \Drupal::database()->select('lab_migration_solution_files', 's');
      $query->fields('s');
      $query->condition('solution_id', $solution_list_default_value);
      $solution_list_q = $query->execute();
  
      if ($solution_list_q) {
        $solution_files_rows = [];
        while ($solution_list_data = $solution_list_q->fetchObject()) {
          $solution_file_type = '';
          switch ($solution_list_data->filetype) {
            case 'S':
              $solution_file_type = 'Source or Main file';
              break;
            case 'R':
              $solution_file_type = 'Result file';
              break;
            case 'X':
              $solution_file_type = 'xcos file';
              break;
            default:
              $solution_file_type = 'Unknown';
              break;
          }
          // Create file download link
          $solution_files_rows[] = [
            Link::fromTextAndUrl($solution_list_data->filename, Url::fromUri('internal:/lab-migration/download/file/' . $solution_list_data->id))->toString(),
            $solution_file_type
          ];
        }
  
        // Query dependencies
        
        // Build the table of files
        $solution_files_header = ['Filename', 'Type'];
        $solution_files = \Drupal::service('renderer')->render([
          '#theme' => 'table',
          '#header' => $solution_files_header,
          '#rows' => $solution_files_rows
        ]);
      }
  
      // // Set the table markup for solution files
      // $form['solution_files']['#title'] = 'List of solution files';
      // $form['solution_files']['#markup'] = $solution_files;
    //   $form['solution_files'] = [
    //     '#type' => 'details',
    //     '#title' => $this->t('List of solution files'),
    //     '#open' => TRUE,
    //     'content' => [
    //         '#theme' => 'table',
    //         '#header' => ['File Name', 'Size', 'Actions'],
    //         '#rows' => $solution_files_rows, // An array of rows with file details.
    //     ],
    // ];
    
      // Add the download and edit links
     
$link = Link::fromTextAndUrl(
  $this->t('Download Solution'),
  Url::fromRoute('lab_migration.download_solution', ['solution' => $solution_list_default_value])
)->toString();
// Add the AJAX command to update the element with ID `#ajax_download_experiment_solution`.
$response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', $link));
      // Uncomment if needed
      // $commands[] = new HtmlCommand('#ajax_edit_experiment_solution', Link::fromTextAndUrl('Edit Solution', Url::fromUri('internal:/code_approval/editcode/' . $solution_list_default_value))->toString());
  
      // Add the solution files table to the page
     // $response->addCommand(new HtmlCommand('#ajax_solution_files', \Drupal::service('renderer')->render($form['solution_files'])));
    } else {
      // If no solution is selected, clear the areas
      $commands[] = new HtmlCommand('#ajax_selected_lab_experiment_solution_action', '');
      $commands[] = new HtmlCommand('#ajax_download_experiment_solution', '');
      $commands[] = new HtmlCommand('#ajax_edit_experiment_solution', '');
      $commands[] = new HtmlCommand('#ajax_solution_files', '');
    }
  
    // Return the AJAX response
    // $response->addCommands($commands);
    return $response;
  }
  
  public function bootstrap_table_format(array $headers, array $rows) {
    // Define the table header and rows.
    $table_header = [];
    foreach ($headers as $header) {
      $table_header[] = ['data' => $header, 'header' => TRUE];
    }
  
    // Define the table rows.
    $table_rows = [];
    foreach ($rows as $row) {
      $table_row = [];
      foreach ($row as $data) {
        $table_row[] = ['data' => $data];
      }
      $table_rows[] = $table_row;
    }
  
    // Create a table render array with Drupal's table theming.
    $table = [
      '#theme' => 'table',
      '#header' => $table_header,
      '#rows' => $table_rows,
      '#attributes' => ['class' => ['table', 'table-bordered', 'table-hover']],
    ];
  
    // Render the table using Drupal's renderer.
    $renderer = \Drupal::service('renderer');
    return $renderer->render($table);
  }
  
/*****************************************************/
public function _list_of_labs()
  {
    $lab_titles = array(
        '0' => 'Please select...'
    );
    //$lab_titles_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE solution_display = 1 ORDER BY lab_title ASC");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('solution_display', 1);
    $query->condition('approval_status', 3);
    $query->orderBy('lab_title', 'ASC');
    $lab_titles_q = $query->execute();
    while ($lab_titles_data = $lab_titles_q->fetchObject())
      {
        $lab_titles[$lab_titles_data->id] = $lab_titles_data->lab_title . ' (Proposed by ' . $lab_titles_data->name_title .' '.$lab_titles_data->name . ')';
      }
    return $lab_titles;
  }
public function _ajax_get_experiment_list($lab_default_value = '')
  {
    $experiments = array(
        '0' => 'Please select...'
    );
    //$experiments_q = db_query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d ORDER BY number ASC", $proposal_id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $lab_default_value);
    $query->orderBy('number', 'ASC');
    $experiments_q = $query->execute();
    while ($experiments_data = $experiments_q->fetchObject())
      {
        $experiments[$experiments_data->id] = $experiments_data->number . '. ' . $experiments_data->title;
      }
    return $experiments;
  }
  public function _ajax_get_solution_list($lab_experiment_list = '') {
    $solutions = [
      '0' => t('Please select...'),
    ];
  
    if (empty($lab_experiment_list)) {
      return $solutions;
    }
  
    // Query the database to get solutions for the given experiment.
    $connection = Database::getConnection();
    $query = $connection->select('lab_migration_solution', 'lms');
    $query->fields('lms', ['id', 'code_number', 'caption']);
    $query->condition('experiment_id', $lab_experiment_list);
    $results = $query->execute();
  
    // Process the query results and populate the solutions array.
    foreach ($results as $record) {
      $solutions[$record->id] = $record->code_number . ' (' . $record->caption . ')';
    }
  
    return $solutions;
  }
public function _lab_information($proposal_id)
  {
      //var_dump($proposal_id);die;
    //$lab_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('lab_migration_proposal', 'l')
    ->fields('l') // Use the table alias here
    ->condition('l.id', $proposal_id)
    ->condition('l.approval_status', 3);

$lab_q = $query->execute();
$lab_data = $lab_q->fetchObject();
//var_dump($lab_data);die;

if ($lab_data) {
    return $lab_data;
} else {
    return;
}

    // $query = \Drupal::database()->select('lab_migration_proposal', 'l');
// $query->fields('l');  // This is correct; it selects all fields from the table.
// $query->condition('l.id', $proposal_id);
// $query->condition('l.approval_status', 3);
// $lab_q = $query->execute();
// $lab_data = $lab_q->fetchObject();

// if ($lab_data) {
//   return $lab_data;
// }
// else {
//   return NULL ;  // Return NULL explicitly
// }

   
  }
public function _lab_details($lab_default_value)
  {
    // $lab_default_value = $form_state['values']['lab'];
    $lab_details = $this->_lab_information($lab_default_value);
    //var_dump($lab_details->name_title);die;
    if ($lab_default_value != 0)
      {
        if ($lab_details){
        if ($lab_details->solution_provider_uid > 0)
          {
            $user_solution_provider = User::load($lab_details->solution_provider_uid);
            if ($user_solution_provider)
              {
                $solution_provider = '<span style="color: rgb(128, 0, 0);"><strong>Solution Provider</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Solution Provider Name:</strong> ' . $lab_details->solution_provider_name_title . ' ' . $lab_details->solution_provider_name . '</li>' . '<li><strong>Department:</strong> ' . $lab_details->solution_provider_department . '</li>' . '<li><strong>University:</strong> ' . $lab_details->solution_provider_university . '</li>' . '</ul>';
              }
            else
              {
                $solution_provider = '<span style="color: rgb(128, 0, 0);"><strong>Solution Provider</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Solution Provider: </strong> (Open) </li>' . '</ul>';
              }
          }
        else
          {
            $solution_provider = '<span style="color: rgb(128, 0, 0);"><strong>Solution Provider</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Solution Provider: </strong> (Open) </li>' . '</ul>';
          }}
          else{
          // drupal_goto('lab-migration/lab-migration-run');
          $url = Url::fromRoute('lab_migration.run_form');

// Create a RedirectResponse and send it.
$response = new RedirectResponse($url->toString());
$response->send();
          
      }
    $form['lab_details']['#markup'] = '<span style="color: rgb(128, 0, 0);"><strong>About the Lab</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Proposer Name:</strong> ' . $lab_details->name_title . ' ' . $lab_details->name . '</li>' . '<li><strong>Title of the Lab:</strong> ' . $lab_details->lab_title . '</li>' . '<li><strong>Department:</strong> ' . $lab_details->department . '</li>' . '<li><strong>University:</strong> ' . $lab_details->university . '</li>' . '<li><strong>Version:</strong> ' . $lab_details->version . '</li>' . '<li><strong>Operating System:</strong> ' . $lab_details->operating_system . '</li>' . '</ul>' . $solution_provider;
    $details = $form['lab_details']['#markup'];
    return $details;
    }
  }
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  }
}