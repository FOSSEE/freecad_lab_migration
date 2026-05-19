<?php

namespace Drupal\lab_migration\Services;

use Drupal\Core\Form\FormStateInterface;

/**
 * Shared AJAX callbacks for lab_migration forms.
 *
 * All three forms (RunForm, BulkApprovalForm, ExportCSVForm) were doing the
 * exact same thing - returning a piece of the form when a select changes.
 * Instead of copying those one-liners into every class, they live here.
 *
 * In your form's #ajax definition just point to one of these static methods:
 *   'callback' => ['\Drupal\lab_migration\Services\LabMigrationAjaxHelper', 'getDownloadLabWrapper'],
 */
class LabMigrationAjaxHelper {

  /**
   * Returns the lab download section after the lab dropdown changes.
   *
   * Wraps the download link, lab actions select, and experiment list.
   */
  public static function getDownloadLabWrapper(array &$form, FormStateInterface $form_state): array {
    return $form['download_lab_wrapper'];
  }

  /**
   * Returns the experiment download section after the experiment dropdown changes.
   *
   * Wraps the experiment download link, experiment actions select, and solution list.
   */
  public static function getDownloadExperimentWrapper(array &$form, FormStateInterface $form_state): array {
    return $form['download_experiment_wrapper'];
  }

  /**
   * Returns the solution download section after the solution dropdown changes.
   *
   * Wraps the solution download link, solution actions select, and file list table.
   */
  public static function getDownloadSolutionWrapper(array &$form, FormStateInterface $form_state): array {
    return $form['download_solution_wrapper'];
  }

  /**
   * Returns the preview table in ExportCSVForm when a filter changes.
   *
   * Called when the status or year select changes — just refreshes
   * the results table below the filters, no full page reload needed.
   */
  public static function getPreviewTableWrapper(array &$form, FormStateInterface $form_state): array {
    return $form['preview_table_wrapper'];
  }

}
