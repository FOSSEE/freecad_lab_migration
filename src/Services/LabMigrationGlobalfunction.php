<?php
 
namespace Drupal\lab_migration\Services;

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

  
 }

 