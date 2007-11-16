<?php
/* $Id$
 *
 * Description:
 *  Web Service functionality to update, delete or add events.
 *
 * Input Parameters:
 *  id       - event id
 *  username - user login of user to add/edit
 *  action   - approve, reject, delete
 *
 * Result:
 *  On success:
 *    <result><success/></result>
 *  On failure/error:
 *    <result><error>Error message here...</error></result>
 *
 * Notes:
 *
 * Developer Notes:
 *  If you enable the WS_DEBUG option below,
 *  all data will be written to a debug file in /tmp also.
 *
 * Security:
 *  - The current user must have permission to modify the event
 *    in the way specified.
 */

$WS_DEBUG = false;

$error = '';

require_once 'ws.php';

// Initialize...
ws_init ();

// header ( 'Content-type: text/xml' );
header ( 'Content-type: text/plain' );

echo '<?xml version="1.0" encoding="UTF-8"?' . ">\n";

$out = '
<result>';

$eid = $WC->getId ();
$user = $WC->getUserLoginId ();

$action = $WC->getGET ( 'action' );
if ( strpos ( 'approvedeletereject', $action ) === false )
  // translate ( 'Unsupported action' )
  $error = str_replace ( 'XXX', ws_escape_xml ( $action ),
    translate ( 'Unsupported action XXX.' ) );

if ( empty ( $error ) && empty ( $eid ) )
  $error = translate ( 'No event id specified.' );

// Public user cannot do this...
if ( empty ( $error ) && $WC->login( '__public__' ) )
  $error = translate ( 'Not authorized' );

// Only admin users can modify events on the public calendar.
if ( empty ( $error ) && $PUBLIC_ACCESS == 'Y' && $user == '__public__' && !
    $WC->isAdmin() )
  // translate ( 'not admin' )
  $error = translate ( 'Not authorized (not admin).' );

if ( empty ( $error ) && ! $WC->isAdmin() && ! $WC->isLogin( $user ) ) {
  // Non-admin user has request to modify event on someone else's calendar.
  if ( ! access_user_calendar ( 'approve', $user ) )
    $error = translate ( 'Not authorized' );
}

if ( strpos ( ' approvedeletereject', $action ) )
  update_status ( ucfirst ( $action ), $user, $eid );

$out .= ( empty ( $error ) ? '
  <success/>' : '
  <error>' . ws_escape_xml ( $error ) . '</error>' ) . '
</result>
';

// If web service debugging is on...
if ( ! empty ( $WS_DEBUG ) && $WS_DEBUG )
  ws_log_message ( $out );

// Send output now...
echo $out;

?>
