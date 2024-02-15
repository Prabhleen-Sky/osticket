#!/usr/bin/php -q
<?php
#
# Configuration: Enter the url and key. That is it.
#  url => URL to api/task/cron e.g #  http://yourdomain.com/support/api/tickets.json
#  key => API's Key (see admin panel on how to generate a key)
#

// If 1, display things to debug.
$debug = "0";

$config = array(
    'url' => 'http://local.osticket.com/api/http.php/tickets.json',
    'key' => 'AA2E3EC8CF331C707F05549CC664D1F9'
);

if ($config['url'] === 'http://your.domain.tld/api/tickets.json') {
    echo "<p style=\"color:red;\"><b>Error: No URL</b><br>You have not configured this script with your URL!</p>";
    echo "Please edit this file " . __FILE__ . " and add your URL at line 18.</p>";
    die();
}
if (IsNullOrEmptyString($config['key']) || ($config['key'] === 'AA2E3EC8CF331C707F05549CC664D1F9')) {
    echo "<p style=\"color:red;\"><b>Error: No API Key</b><br>You have not configured this script with an API Key!</p>";
    echo "<p>Please log into osticket as an admin and navigate to: Admin panel -> Manage -> Api Keys then add a new API Key.<br>";
    echo "Once you have your key edit this file " . __FILE__ . " and add the key at line 19.</p>";
    die();
}

# Fill in the data for the new ticket, this will likely come from $_POST.

$data = array(
    'name'      =>      'John Doe',
    'email'     =>      'mailbox@host.com',
    'subject'   =>      'Test API message',
    'message'   =>      'This is a test of the osTicket API',
    'ip'        =>      $_SERVER['REMOTE_ADDR'],
    'attachments' => array(),
);

if ($debug == '1') {
    print_r($data);
    die();
}

/* 
 * Add in attachments here if necessary

$data['attachments'][] =
array('filename.pdf' =>
        'data:image/png;base64,' .
            base64_encode(file_get_contents('/path/to/filename.pdf')));
 */

#pre-checks
function_exists('curl_version') or die('CURL support required');
function_exists('json_encode') or die('JSON support required');

#set timeout
set_time_limit(30);

#curl post
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $config['url']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_USERAGENT, 'osTicket API Client v1.7');
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:', 'X-API-Key: ' . $config['key']));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code != 201)
    die('Unable to create ticket: ' . $result);

$ticket_id = (int) $result;

# Continue onward here if necessary. $ticket_id has the ID number of the
# newly-created ticket
function IsNullOrEmptyString($question)
{
    return (!isset($question) || trim($question) === '');
}

?>