<?php

use Sys\ChildQuery;

/**
 * Workflow status bar: where the activity stands, how far the steps inside
 * it are and whether it is on time. A tiny button moves the schedule.
 */
class ActivityWorkflowStatusbarRenderer extends \Sys\StatusbarRenderer
{
  protected function parts() : array
  {
    $status   = (string) $this->obj->field('status', '');
    $schedule = $this->obj->field('schedule', []);
    $schedule = is_array($schedule) ? $schedule : [];

    return [
      '<span class="wf-state wf-' . $this->esc($status ?: 'none') . '">' . $this->esc($status ?: 'no status') . '</span>',
      $this->progress(),
      $this->due($schedule, $status),
      $status === 'done' ? '' : $this->shiftButton($schedule)
    ];
  }

  /**
   * Steps are the activities inside this one, linked ones included.
   */
  private function progress() : string
  {
    $steps = (new ChildQuery($this->app))->run($this->obj->path, ['types' => ['Activity']], 0, PHP_INT_MAX)['items'];

    if( ! $steps )
      return '';

    $total   = count($steps);
    $done    = count(array_filter($steps, fn( $step ) => $step->display()->field('status') === 'done'));
    $percent = round(100 * $done / $total);

    return "$done of $total steps done <span class=\"wf-progress\"><span style=\"width: $percent%\"></span></span>";
  }

  /**
   * The end date decides whether the activity is late, a done one never is.
   */
  private function due( array $schedule, string $status ) : string
  {
    $start = (string) ($schedule['start'] ?? '');
    $end   = (string) ($schedule['end'] ?? '');

    if( $end !== '' )
    {
      $late = $status !== 'done' && $end < date('Y-m-d');
      $text = ($late ? 'overdue since ' : 'due ') . $this->dateShort($end);

      return '<span class="wf-due' . ($late ? ' is-late' : '') . '">' . $this->icon('calendar') . $this->esc($text) . '</span>';
    }

    if( $start !== '' )
      return '<span class="wf-due">' . $this->icon('calendar') . $this->esc('starts ' . $this->dateShort($start)) . '</span>';

    return 'not scheduled';
  }

  private function shiftButton( array $schedule ) : string
  {
    $planned = ! empty($schedule['start']) || ! empty($schedule['end']);

    return $this->actionButton([
      'id'     => 'shift',
      'label'  => $planned ? '+1 week' : 'Plan a week',
      'title'  => $planned ? 'Move start and end by a week' : 'Schedule a week from today',
      'ajax'   => 'shift_schedule',
      'params' => ['days' => 7]
    ], 'btn-tiny');
  }
}
