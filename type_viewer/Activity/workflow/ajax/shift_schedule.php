<?php

/**
 * Moves the schedule of an activity by some days, behind the tiny button in
 * the workflow status bar. Without a schedule yet, one is made from today.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

$obj  = $ajax->obj();
$days = max(1, $ajax->intParam('days', 7));
$data = $obj->data;

$schedule = is_array($data['schedule'] ?? null) ? $data['schedule'] : [];
$planned  = ! empty($schedule['start']) || ! empty($schedule['end']);

if( $planned )
{
  foreach( ['start', 'end'] as $key )
  {
    if( ! empty($schedule[$key]))
      $schedule[$key] = date('Y-m-d', strtotime("{$schedule[$key]} +$days days"));
  }
}
else
{
  $schedule = ['start' => date('Y-m-d'), 'end' => date('Y-m-d', strtotime("+$days days"))];
}

$data['schedule'] = $schedule;

$saved = $app->store->save($obj, $data, $obj->body, null);

return [
  'path'    => $saved->path,
  'card'    => $app->renderer($ajax->param('mode') === 'mobile' ? 'mobile' : 'list', $saved)->html(),
  'message' => $planned ? "'{$saved->title}' moved by $days days" : "'{$saved->title}' planned from today"
];
