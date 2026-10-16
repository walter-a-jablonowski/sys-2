<?php

/**
 * Workflow toolbar: the steps an activity can take from its current status.
 */
class ActivityWorkflowToolbarRenderer extends \Sys\ToolbarRenderer
{
  // Status -> the steps leading on from it, label => new status
  private const STEPS = [
    'open'    => ['Start' => 'running', 'Done' => 'done'],
    'running' => ['Done' => 'done', 'Pause' => 'open'],
    'done'    => ['Reopen' => 'open']
  ];

  protected function buttons() : array
  {
    $status  = (string) $this->obj->field('status', '');
    $buttons = [];

    // No status yet counts as open
    foreach( self::STEPS[$status] ?? self::STEPS['open'] as $label => $next )
    {
      $buttons[] = [
        'id'     => strtolower($label),
        'label'  => $label,
        'title'  => "Set the status to $next",
        'ajax'   => 'set_status',
        'params' => ['status' => $next]
      ];
    }

    return $buttons;
  }
}
