<?php

/*
 * Unoffical najdi.si SMS API
 * Copyright 2011 Leon Pajk
  *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('COOKIES_FILE', 'cookies.txt');
define('DEBUG', 0); // Toggle to 1 for debug mode
define('NUM_OF_REDIRECTS', 10);
define('TIMEOUT', 30);
define('USER_AGENT', 'Mozilla/5.0 (Ubuntu; X11; Linux i686; rv:8.0) Gecko/20100101 Firefox/8.0');

$login_user = 'user';
$login_pass = 'password';

$message = 'foo bar';
$phone_number ='031 123 456';

login($login_user, $login_pass);
get_cookies();
send_message($phone_number, $message);

/*
 * Login to najdi.si
 */
function login($login_user, $login_pass) {
    $url = 'https://id.najdi.si/login/j_spring_security_check';
    
    # init cURL session with given url
    $ch = curl_init($url);
    
    # prepare username and password
    $post_data = array('j_username' => urlencode($login_user),
                       'j_password' => urlencode($login_pass));
    
    # set POST data
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    
    # enable POST method
    curl_setopt($ch, CURLOPT_POST, 1);
    
    # redirects (301 and 302) after POST
    curl_setopt($ch, CURLOPT_POSTREDIR, 3);
    
    # enable to follow location header
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
    
    # set maximum number of redirects
    curl_setopt($ch, CURLOPT_MAXREDIRS, NUM_OF_REDIRECTS);
    
    # maximum number of seconds to allow cUrl functions to execute
    curl_setopt ($ch, CURLOPT_TIMEOUT, TIMEOUT);
    
    # output verbose information
    curl_setopt($ch, CURLOPT_VERBOSE, (1 == DEBUG ? 1 : 0));
    
    # verify common name and hostname
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    # verify peer's certificate
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 1);
    
    # mark as new cookie session
    curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
    
    # name of a file to save all cookies
    curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIES_FILE);
    
    # return the transfer as a string of the return value of curl_exec()
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    
    # set Referer header value
    curl_setopt ($ch, CURLOPT_REFERER, 'https://id.najdi.si/login');
    
    # set User-Agent header value
    curl_setopt ($ch, CURLOPT_USERAGENT, USER_AGENT);
    
    # force the use of a new connection instead cached one
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    
    $result = array();
    
    # transfer cUrl return values to array
    $result['EXE'] = curl_exec($ch);
    $result['INF'] = curl_getinfo($ch);
    $result['ERR'] = curl_error($ch);
    
    # close session and free all resources
    curl_close ($ch);
}

/*
 * Get session cookies
 */
function get_cookies() {
    $url = 'http://lahki.najdi.si/auth/login.jsp?lg=0&target_url=http%3A%2F%2Flahki.najdi.si%2Findex.jsp%3Fsms_show';
    
    # init cURL session with given url
    $ch = curl_init($url);
    
    # enable to follow location header
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
    
    # set maximum number of redirects
    curl_setopt($ch, CURLOPT_MAXREDIRS, NUM_OF_REDIRECTS);
    
    # maximum number of seconds to allow cUrl functions to execute
    curl_setopt ($ch, CURLOPT_TIMEOUT, TIMEOUT);
    
    # output verbose information
    curl_setopt($ch, CURLOPT_VERBOSE, (1 == DEBUG ? 1 : 0));
    
    # verify common name and hostname
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    # verify peer's certificate
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 1);
    
    # name of a file to save all cookies
    curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIES_FILE);
    
    # name of the file containing the cookie data
    curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIES_FILE);
    
    # return the transfer as a string of the return value of curl_exec()
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    
    # set Referer header value
    curl_setopt ($ch, CURLOPT_REFERER,
                'http://lahki.najdi.si/index.jsp?sms_show');
    
    # set User-Agent header value
    curl_setopt ($ch, CURLOPT_USERAGENT, USER_AGENT);
    
    # force the use of a new connection instead cached one
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    
    $result = array();
    
    # transfer cUrl return values to array
    $result['EXE'] = curl_exec($ch);
    $result['INF'] = curl_getinfo($ch);
    $result['ERR'] = curl_error($ch);
    
    # close session and free all resources
    curl_close ($ch);
}

/*
 * Check if digits length is equal to 3
 */
function dlen3($str) {
  return preg_match('/^\d{3}$/', $str);
}

/*
 * Check mobile network code
 */
function dmnc($str) {
  return preg_match('/^(031|041|051|071|030|040|064|070)$/', $str);
}

/*
 * Send message to given phone number
 */
function send_message($phone_number, $message) {
    if(strlen($phone_number) != 11) {
        return false;
    }
    
    # split phone number
    $blocks = explode(' ', $phone_number);
    
    if(count($blocks) != 3) {
        return false;
    }
    
    # split phone number
    list($block_a, $block_b, $block_c) = $blocks;
    
    if(DEBUG){
        echo 'digits_a: ' . $block_a . "\n";
        echo 'digits_b: ' . $block_b . "\n";
        echo 'digits_c: ' . $block_c . "\n";
        echo 'mnc (mobile network code): ' . dmnc($block_a) . "\n";
    }
    
    if(!(dmnc($block_a) && dlen3($block_a) &&
        dlen3($block_b) && dlen3($block_c))) {
        return false;
    }
    
    # get UNIX time
    $unix_time = time();
    
    # encoded parameters
    $params = array(
        'sms_action'                           => 4,
        'sms_so_ac_'   . urlencode($unix_time) => substr($block_a, 1),
        'sms_so_l_'    . urlencode($unix_time) => $block_b . ' ' . $block_c,
        'myContacts'                           => '',
        'sms_message_' . urlencode($unix_time) => $message);
    
    $url = 'http://lahki.najdi.si/sms/smsController.jsp?';

    # init cURL session with given url
    $ch = curl_init($url . http_build_query($params));
    
    # disable to follow location header
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    
    # maximum number of seconds to allow cUrl functions to execute
    curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
    
    # output verbose information
    curl_setopt($ch, CURLOPT_VERBOSE, (1 == DEBUG ? 1 : 0));
    
    # name of a file to save all cookies
    curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIES_FILE);
    
    # name of the file containing the cookie data
    curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIES_FILE);
    
    # return the transfer as a string of the return value of curl_exec()
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    
    # set Referer header value
    curl_setopt($ch, CURLOPT_REFERER, 'http://lahki.najdi.si/');
    
    # set User-Agent header value
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    
    $result = array();
    
    # transfer cUrl return values to array
    $result['EXE'] = curl_exec($ch);
    $result['INF'] = curl_getinfo($ch);
    $result['ERR'] = curl_error($ch);
    
    # close session and free all resources
    curl_close($ch);
    
    #print_r($result);
    $json = json_decode($result['EXE']);
    if ($json != NULL) {
        print $json->{'odgovor'};
    }
    
    return true;
}

?>
