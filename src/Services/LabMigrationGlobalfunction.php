<?php
 
namespace Drupal\lab_migration\Services;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Database\Database;

class LabMigrationGlobalfunction{

   public function _list_of_labs()
  {
    $lab_titles = array(
        '0' => 'Please select...'
    );
    //$lab_titles_q = $injected_database->query("SELECT * FROM {lab_migration_proposal} WHERE solution_display = 1 ORDER BY lab_title ASC");
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
  function _lm_list_of_states()
  {
    $states = array(0 => '-Select-');
    $query = \Drupal::database()->select('list_states_of_india');
    $query->fields('list_states_of_india');
    //$query->orderBy('', '');
    $states_list = $query->execute();
    while ($states_list_data = $states_list->fetchObject())
      {
        $states[$states_list_data->state] = $states_list_data->state;
      }
    return $states;
  }
  function _lab_migration_list_of_states()
{
    $states = array(''=> '- Select -');
    $states_list = \Drupal::database()->query("SELECT state FROM all_india_pincode WHERE country = 'India' ORDER BY state ASC");
    while ($states_list_data = $states_list->fetchObject())
    {
        $states[$states_list_data->state] = $states_list_data->state;
    }
    return $states;
}

function _lm_list_of_cities()
  {
    $city = array(0 => '-Select-');
    $query = \Drupal::database()->select('list_cities_of_india');
    $query->fields('list_cities_of_india');
    $query->orderBy('city', 'ASC');
    $city_list = $query->execute();
    while ($city_list_data = $city_list->fetchObject())
      {
        $city[$city_list_data->city] = $city_list_data->city;
      }
    return $city;
  }

  function _lab_migration_list_of_city_pincode($city=Null, $state=NULL, $district=NULL)
{
    $pincode = array();
    if($city){
        $pincode_list = \Drupal::database()->query("SELECT pincode FROM all_india_pincode WHERE city = :city AND state = :state AND district = :district", array(':city' => $city,':state'=> $state, ':district' => $district));
        while ($pincode_list_data = $pincode_list->fetchObject())
        {
            $pincode[$pincode_list_data->pincode] = $pincode_list_data->pincode;
        }
    }
    else{
        $pincode[000000] = '000000';
    }
    return $pincode;
}

  function _lm_list_of_departments()
  {
    $department = array();
    $query = \Drupal::database()->select('list_of_departments');
    $query->fields('list_of_departments');
    $query->orderBy('id', 'DESC');
    $department_list = $query->execute();
    while ($department_list_data = $department_list->fetchObject())
      {
        $department[$department_list_data->department] = $department_list_data->department;
      }
    return $department;
  }

  function _lm_list_of_software_version()
  {
    $software_version = array();
    $query = \Drupal::database()->select('r_software_version');
    $query->fields('r_software_version');
    //$query->orderBy('id', 'DESC');
    $software_version_list = $query->execute();
    while ($software_version_list_data = $software_version_list->fetchObject())
      {
        $software_version[$software_version_list_data->r_version] = $software_version_list_data->r_version;
      }
    return $software_version;
  }

  function _lab_migration_list_of_district($state=Null)
{
    $district = array(''=> '- Select -');
    if($state){
        $district_list = \Drupal::database()->query("SELECT district FROM all_india_pincode WHERE state = :state ORDER BY district ASC", array(':state'=> $state));
        while ($district_list_data = $district_list->fetchObject())
        {
            $district[$district_list_data->district] = $district_list_data->district;
        }
    }
    return $district;
}
function _lm_dir_name($lab, $name, $university)
  {
    $lab_title = lm_ucname($lab);
    $proposar_name = lm_ucname($lab);
    $university_name = lm_ucname($university);
    $dir_name = $lab_title . " " . "by". " " . $proposar_name . ' ' . $university_name;
    $directory_name = str_replace("__", "_", str_replace(" ", "_", $dir_name));
    return $directory_name;
  }
function lm_ucname($string)
  {
    $string = ucwords(strtolower($string));
    foreach (array(
        '-',
        '\''
    ) as $delimiter)
      {
        if (strpos($string, $delimiter) !== false)
          {
            $string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
          }
      }
    return $string;
    // Example of using lm_ucname in another method of this service.
  
    $name = $this->lm_ucname('example-string');
    // Other logic here...
    return $name;
  
  }
  function lab_migration_path()
  {
    return $_SERVER['DOCUMENT_ROOT'] . base_path() . 'freecad_uploads/lab_migration_uploads/';
  }
  function _bulk_list_of_labs()
  {
    $lab_titles = array(
        '0' => 'Please select...'
    );
    //$lab_titles_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE solution_display = 1 ORDER BY lab_title ASC");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('solution_display', 1);
    $query->orderBy('lab_title', 'ASC');
    $lab_titles_q = $query->execute();
    while ($lab_titles_data = $lab_titles_q->fetchObject())
      {
        $lab_titles[$lab_titles_data->id] = $lab_titles_data->lab_title . ' (Proposed by ' . $lab_titles_data->name . ')';
      }
    return $lab_titles;
  }
  function _ajax_bulk_get_experiment_list($lab_default_value = '')
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
  function _ajax_get_experiment_list($lab_default_value = '')
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
  function _ajax_get_solution_list($lab_experiment_list = '')
  {
    $solutions = array(
        '0' => 'Please select...'
    );
    // $solutions_q = db_query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d ORDER BY
    //  CAST(SUBSTRING_INDEX(code_number, '.', 1) AS BINARY) ASC,
    //   CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number , '.', 2), '.', -1) AS UNSIGNED) ASC,
    //  CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number , '.', -1), '.', 1) AS UNSIGNED) ASC", $experiment_id);
    $query = \Drupal::database()->select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('experiment_id', $lab_experiment_list);
    //$query->orderBy("CAST(SUBSTRING_INDEX(code_number, '.', 1) AS BINARY", "ASC");
    // $query->orderBy("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number , '.', 2), '.', -1) AS UNSIGNED", "ASC");
    // $query->orderBy("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number , '.', -1), '.', 1) AS UNSIGNED", "ASC");
    $solutions_q = $query->execute();
    while ($solutions_data = $solutions_q->fetchObject())
      {
        $solutions[$solutions_data->id] = $solutions_data->code_number . ' (' . $solutions_data->caption . ')';
      }
    return $solutions;
  
  }

  function _latex_copy_script_file()
  {
    exec("cp ./" . drupal_get_path('module', 'lab_migration') . "/latex/* " . lab_migration_path() . "latex");
    exec("chmod u+x ./uploads/latex/*.sh");
  }
  
  function lab_migration_solution_proposal_pending()
  {
    /* get list of solution proposal where the solution_provider_uid is set to some userid except 0 and solution_status is also 1 */
    $pending_rows = array();
    //$pending_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE solution_provider_uid != 0 AND solution_status = 1 ORDER BY id DESC");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('solution_provider_uid', 0, '!=');
    $query->condition('solution_status', 1);
    $query->orderBy('id', 'DESC');
    $pending_q = $query->execute();
    while ($pending_data = $pending_q->fetchObject())
      {
        $pending_rows[$pending_data->id] = array(
            l($pending_data->name, 'user/' . $pending_data->uid),
            $pending_data->lab_title,
            l('Approve', 'lab-migration/manage-proposal/solution-proposal-approve/' . $pending_data->id)
        );
      }
    /* check if there are any pending proposals */
    if (!$pending_rows)
      {
        drupal_set_message(t('There are no pending solution proposals.'), 'status');
        return '';
      }
    $pending_header = array(
        'Proposer Name',
        'Title of the Lab',
        'Action'
    );
    $output = theme('table', array(
        'header' => $pending_header,
        'rows' => $pending_rows
    ));
    return $output;
  }
  public function lab_migration_list_experiments() {
    // $user = \Drupal::currentUser();
$user = $user->get('uid')->value;

    $proposal_data = \Drupal::service("lab_migration_global")->lab_migration_get_proposal();
    if (!$proposal_data) {
      RedirectResponse('');
      return;
    }

    $return_html = '<strong>Title of the Lab:</strong><br />' . $proposal_data->lab_title . '<br /><br />';
    $return_html .= '<strong>Proposer Name:</strong><br />' . $proposal_data->name_title . ' ' . $proposal_data->name . '<br /><br />';
    // $return_html .= Link::fromTextAndUrl('Upload Solution', 'lab-migration/code/upload') . '<br />';
    $return_html .= Link::fromTextAndUrl(
      'Upload Solution', 
      Url::fromUri('internal:/lab-migration/code/upload')
  )->toString() . '<br />';
    /* get experiment list */
    $experiment_rows = [];
    //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d ORDER BY number ASC", $proposal_data->id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_data->id);
    $query->orderBy('number', 'ASC');
    $experiment_q = $query->execute();

   

    while ($experiment_data = $experiment_q->fetchObject()) {


      $experiment_rows[] = [
        $experiment_data->number . ')&nbsp;&nbsp;&nbsp;&nbsp;' . $experiment_data->title,
        '',
        '',
        '',
      ];
      /* get solution list */
      //$solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d ORDER BY id ASC", $experiment_data->id);
      $query = \Drupal::database()->select('lab_migration_solution');
      $query->fields('lab_migration_solution');
      $query->condition('experiment_id', $experiment_data->id);
      $query->orderBy('id', 'ASC');
      $solution_q = $query->execute();
      if ($solution_q) {
        while ($solution_data = $solution_q->fetchObject()) {
          $solution_status = '';
          switch ($solution_data->approval_status) {
            case 0:
              $solution_status = "Pending";
              break;
            case 1:
              $solution_status = "Approved";
              break;
            default:
              $solution_status = "Unknown";
              break;
          }
          if ($solution_data->approval_status == 0) {
            $experiment_rows[] = [
              "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $solution_data->code_number . "   " . $solution_data->caption,
              '',
              $solution_status,
              Link::fromTextAndUrl('Delete', 'lab-migration/code/delete/' . $solution_data->id),
            ];
          }
          else {
            $experiment_rows[] = [
              "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $solution_data->code_number . "   " . $solution_data->caption,
              '',
              $solution_status,
              '',
            ];
          }
          /* get solution files */
          //$solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d ORDER BY id ASC", $solution_data->id);
          $query = \Drupal::database()->select('lab_migration_solution_files');
          $query->fields('lab_migration_solution_files');
          $query->condition('solution_id', $solution_data->id);
          $query->orderBy('id', 'ASC');
          $solution_files_q = $query->execute();

          if ($solution_files_q) {
            while ($solution_files_data = $solution_files_q->fetchObject()) {
              $code_file_type = '';
              switch ($solution_files_data->filetype) {
                case 'S':
                  $code_file_type = 'Source';
                  break;
                case 'R':
                  $code_file_type = 'Result';
                  break;
                case 'X':
                  $code_file_type = 'Xcox';
                  break;
                case 'U':
                  $code_file_type = 'Unknown';
                  break;
                default:
                  $code_file_type = 'Unknown';
                  break;
              }
              $experiment_rows[] = [
                "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . Link::fromTextAndUrl($solution_files_data->filename, 'lab-migration/download/file/' . $solution_files_data->id),
                $code_file_type,
                '',
                '',
              ];
            }
          }
          /* get dependencies files */
          //$dependency_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d ORDER BY id ASC", $solution_data->id);
          $query = \Drupal::database()->select('lab_migration_solution_dependency');
          $query->fields('lab_migration_solution_dependency');
          $query->condition('solution_id', $solution_data->id);
          $query->orderBy('id', 'ASC');
          $dependency_q = $query->execute();
          while ($dependency_data = $dependency_q->fetchObject()) {
            //$dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d", $dependency_data->dependency_id);
            $query = \Drupal::database()->select('lab_migration_dependency_files');
            $query->fields('lab_migration_dependency_files');
            $query->condition('id', $dependency_data->dependency_id);
            $dependency_files_q = $query->execute();
            $dependency_files_data = $dependency_files_q->fetchObject();
            $experiment_rows[] = [
              "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . Link::fromTextAndUrl($dependency_files_data->filename, 'lab-migration/download/dependency/' . $dependency_files_data->id),
              'Dependency',
              '',
              '',
            ];
          }
        }
      }
    }

    $experiment_header = [
      'No. Title of the Experiment',
      'Type',
      'Status',
      'Actions',
    ];
    // $return_html .= drupal_render()_table($experiment_header, $experiment_rows);

    // $return_html .= \Drupal::service("renderer")->render('table', [
    //   'header' => $experiment_header,
    //   'rows' => $experiment_rows,
    // ]);
    $return_html = '<strong>Title of the Lab:</strong><br /><br /><br />';
$return_html .= '<strong>Proposer Name:</strong><br /><br /><br />';
$return_html .= '<a href="/test_module_upgradtion/lab-migration/code/upload">Upload Solution</a><br />';
// Add your table or any other HTML content here

return new Response($return_html);
    $table = [
      '#type' => 'table',
      '#header' => $experiment_header,  // The headers for the table
      '#rows' => $experiment_rows,      // The rows for the table
      
    ];
  
    
    $return_html .= \Drupal::service('renderer')->render($table);
    return $return_html;
  }

  public function verify_lab_migration_certificates($qr_code = 0) {
    
    $route_match = \Drupal::routeMatch();

$qr_code = (int) $route_match->getParameter('qr_code');
    $page_content = "";
    if ($qr_code) {
      $page_content = verify_qrcode_lm_fromdb($qr_code);
    } //$qr_code
    else {
      $verify_certificates_form = \Drupal::formBuilder()->getForm("verify_lab_migration_certificates_form");
      $page_content = \Drupal::service("renderer")->render($verify_certificates_form);
    }
    return $page_content;
  }
  function _bulk_list_lab_actions()
  {
    $lab_actions = array(
        0 => 'Please select...'
    );
    $lab_actions[1] = 'Approve Entire Lab';
    $lab_actions[2] = 'Pending Review Entire Lab';
    $lab_actions[3] = 'Dis-Approve Entire Lab (This will delete all the solutions in the lab)';
    $lab_actions[4] = 'Delete Entire Lab Including Proposal';
    return $lab_actions;
  }
  function _bulk_list_experiment_actions()
  {
    $lab_experiment_actions = array(
        0 => 'Please select...'
    );
    $lab_experiment_actions[1] = 'Approve Entire Experiment';
    $lab_experiment_actions[2] = 'Pending Review Entire Experiment';
    $lab_experiment_actions[3] = 'Dis-Approve Entire Experiment (This will delete all the solutions in the experiment)';
    return $lab_experiment_actions;
  }
  function _ajax_bulk_get_solution_list($lab_experiment_list = '')
  {
    $solutions = array(
        0 => 'Please select...'
    );
    // $solutions_q = db_query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d ORDER BY
    //  CAST(SUBSTRING_INDEX(code_number, '.', 1) AS BINARY) ASC,
    //   CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number , '.', 2), '.', -1) AS UNSIGNED) ASC,
    //  CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number , '.', -1), '.', 1) AS UNSIGNED) ASC", $experiment_id);
    $query = \Drupal::database()->select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('experiment_id', $lab_experiment_list);
    //$query->orderBy("CAST(SUBSTRING_INDEX(code_number, '.', 1) AS BINARY", "ASC");
    // $query->orderBy("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number , '.', 2), '.', -1) AS UNSIGNED", "ASC");
    // $query->orderBy("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number , '.', -1), '.', 1) AS UNSIGNED", "ASC");
    $solutions_q = $query->execute();
    while ($solutions_data = $solutions_q->fetchObject())
      {
        $solutions[$solutions_data->id] = $solutions_data->code_number . ' (' . $solutions_data->caption . ')';
      }
    return $solutions;
  }
  function _bulk_list_solution_actions()
  {
    $lab_solution_actions = array(
        0 => 'Please select...'
    );
    $lab_solution_actions[1] = 'Approve Entire Solution';
    $lab_solution_actions[2] = 'Pending Review Entire Solution';
    $lab_solution_actions[3] = 'Dis-approve Solution (This will delete the solution)';
    return $lab_solution_actions;
  }
  public function lab_migration_delete_lab_pdf() {
    
    $route_match = \Drupal::routeMatch();

$lab_id = (int) $route_match->getParameter('lab_id');
\Drupal::service("lab_migration_global")->lab_migration_del_lab_pdf($lab_id);
    \Drupal::messenger()->addMessage(t('Lab schedule for regeneration.'), 'status');
    RedirectResponse('lab_migration/code_approval/bulk');
    return;
  }
  function lab_migration_get_proposal()
  {
    global $user;
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE solution_provider_uid = ".$user->uid." AND solution_status = 2 ORDER BY id DESC LIMIT 1");
    $query = Database::getConnection()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('solution_provider_uid',  $user->get('uid')->value);
    $query->condition('solution_status', 2);
    $query->orderBy('id', 'DESC');
    $query->range(0, 1);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    //var_dump($proposal_data);die;
//     if (!$proposal_data)
//       {
       
//         // Create the link URL object for the "available" link.
// $link_url = Url::fromRoute('lab_migration.proposal_open');
// $link = Link::fromTextAndUrl('available', $link_url)->toString();


// // Now you can use $link in your output or messages
// \Drupal::messenger()->addMessage("Check out the proposal: " . $link);
// }
    
    // switch ($proposal_data->approval_status)
    // {
    //     case 0:
    //         \Drupal::messenger()->addmessage(t('Proposal is awaiting approval.'), 'status');
    //         return FALSE;
    //     case 1:
    //         return $proposal_data;
    //     case 2:
    //       \Drupal::messenger()->addmessage(t('Proposal has been dis-approved.'), 'error');
    //         return FALSE;
    //     case 3:
    //       \Drupal::messenger()->addmessage(t('Proposal has been marked as completed.'), 'status');
    //         return FALSE;
    //     default:
    //     \Drupal::messenger()->addmessage(t('Invalid proposal state. Please contact site administrator for further information.'), 'error');
    //         return FALSE;
    // }
    return $proposal_data;
  }
  function lab_migration_upload_code_form($form,$form_state)
{
 
  global $user;

  $proposal_data = lab_migration_get_proposal();
  if (!$proposal_data) {
      drupal_goto('');
      return;
  }

  /* add javascript for dependency selection effects */
  $dep_selection_js = "(function ($) {
  //alert('ok');
    $('#edit-existing-depfile-dep-lab-title').change(function() {
      var dep_selected = '';   
 
      /* showing and hiding relevant files */
     $('.form-checkboxes .option').hide();
      $('.form-checkboxes .option').each(function(index) {
        var activeClass = $('#edit-existing-depfile-dep-lab-title').val();
        consloe.log(activeClass);
        if ($(this).children().hasClass(activeClass)) {
          $(this).show();
        }
        if ($(this).children().attr('checked') == true) {
          dep_selected += $(this).children().next().text() + '<br />';
        }
      });
      /* showing list of already existing dependencies */
      $('#existing_depfile_selected').html(dep_selected);
    });

    $('.form-checkboxes .option').change(function() {
      $('#edit-existing-depfile-dep-lab-title').trigger('change');
    });
    $('#edit-existing-depfile-dep-lab-title').trigger('change');
  }(jQuery));";
  drupal_add_js($dep_selection_js, 'inline', 'header');

  $form['#attributes'] = array('enctype' => "multipart/form-data");

  $form['lab_title'] = array(
    '#type' => 'item',
    '#markup' => $proposal_data->lab_title,
    '#title' => t('Title of the Lab'),
  );
  $form['name'] = array(
    '#type' => 'item',
    '#markup' => $proposal_data->name_title . ' ' . $proposal_data->name,
    '#title' => t('Proposer Name'),
  );

  /* get experiment list */
  $experiment_rows = array();
  //$experiment_q = db_query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d ORDER BY id ASC", $proposal_data->id);
  $query = db_select('lab_migration_experiment');
                $query->fields('lab_migration_experiment');
                $query->condition('proposal_id', $proposal_data->id);
                $query->orderBy('id', 'ASC');
                $experiment_q = $query->execute();
  while ($experiment_data = $experiment_q->fetchObject())
  {
    $experiment_rows[$experiment_data->id] = $experiment_data->number . '. ' . $experiment_data->title;
  }
  $form['experiment'] = array(
    '#type' => 'select',
    '#title' => t('Title of the Experiment'),
    '#options' => $experiment_rows,
    '#multiple' => FALSE,
    '#size' => 1,
    '#required' => TRUE,
  );

  $form['code_number'] = array(
    '#type' => 'textfield',
    '#title' => t('Code No'),
    '#size' => 5,
    '#maxlength' => 10,
    '#description' => t(""),
    '#required' => TRUE,
  );
  $form['code_caption'] = array(
    '#type' => 'textfield',
    '#title' => t('Caption'),
    '#size' => 40,
    '#maxlength' => 255,
    '#description' => t(''),
    '#required' => TRUE,
  );
  $form['os_used'] = array(
    '#type' => 'select',
    '#title' => t('Operating System used'),
    '#options' => array(
      'Linux' => 'Linux',
      'Windows' => 'Windows',
      'Mac' => 'Mac'
    ),
    '#required' => TRUE,
  );
  $form['r_version'] = array(
    '#type' => 'select',
    '#title' => t('R version used'),
    '#options' => _lm_list_of_software_version(),
    '#required' => TRUE,
  );
  $form['toolbox_used'] = array(
    '#type' => 'hidden',
    '#title' => t('Toolbox used (If any)'),
'#default_value'=>'none',
  );
  $form['code_warning'] = array(
    '#type' => 'item',
    '#title' => t('Upload all the r project files in .zip format'),
    '#prefix' => '<div style="color:red">',
    '#suffix' => '</div>',
  );
  $form['sourcefile'] = array(
    '#type' => 'fieldset',
    '#title' => t('Main or Source Files'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
  );
  $form['sourcefile']['sourcefile1'] = array(
      '#type' => 'file',
      '#title' => t('Upload main or source file'),
      '#size' => 48,
      '#description' => t('Only alphabets and numbers are allowed as a valid filename.') . '<br />' .
      t('Allowed file extensions: ') . variable_get('lab_migration_source_extensions', ''),
  );

 /* $form['dep_files'] = array(
    '#type' => 'item',
    '#title' => t('Dependency Files'),
  );*/
 }
}
 