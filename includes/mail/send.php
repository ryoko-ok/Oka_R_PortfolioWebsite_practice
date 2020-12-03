<?php
//Debug only
// ini_set('display_errors', 1);

// TODO: Take care the form submission

// 4*. It returns proper info in JSON [checked]
// a. What is AJAX??
// b. What is JSON??
// c. How to build JSON??

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json; charset=UTF-8');
$results = [];
$visitor_firstname = '';
$visitor_lastname = '';
$visitor_email = '';
$visitor_message = '';
$topic_lookup = ["first" => "Question", "second" => "Order(Person)", "third" => "Order(Company)"];
// 1. Check the submission out - Validate the data
// $_POST['firstname']

if (isset($_POST['firstname'])) {
    $visitor_firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
    if (empty($_POST['firstname'])) {
        die(json_encode(["message" => "You must enter your firstname."]));
    }
}

if (isset($_POST['lastname'])) {
    $visitor_lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
    if (empty($_POST['lastname'])) {
        die(json_encode(["message" => "You must enter your lastname."]));
    }
}

if (isset($_POST['email'])) {
    $visitor_email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (empty($_POST['email'])) {
        die(json_encode(["message" => "You must enter your email."]));
    }
}

if (isset($_POST['topic'])) {
    if (empty($_POST['topic'])) {
        die(json_encode(["message" => "Choose your topic."]));
    }
    if (!array_key_exists($_POST["topic"], $topic_lookup)) {
        die(json_encode(["message" => "Invalid topic."]));
    }
}

if (isset($_POST['message'])) {
    $visitor_message = filter_var(htmlspecialchars($_POST['message']), FILTER_SANITIZE_STRING);
    if (empty($_POST['message'])) {
        die(json_encode(["message" => "You must enter your message."]));
    }
}

// reCAPTCHA
  
if (isset($_POST['g-recaptcha-response'])) {
    $secret = 'javascript:history.go(-1)';
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
    $responseData = filter_var(($_POST['g-recaptcha-response']));
    if (empty($_POST['g-recaptcha-response'])) {
        die(json_encode(["message" => 'Robot verification failed, please try again.']));
    }
}

$results['firstname'] = $visitor_firstname;
$results['lastname'] = $visitor_lastname;
$results['email'] = $visitor_email;
$results['topic'] = $topic_lookup;
$results['message'] = $visitor_message;


// 2. Prepare the email
$email_subject = $topic_lookup[$_POST['topic']];
$email_recipient = 'test@ryoko-o.com'; // Your Email, or AKA, "To" email
$email_message = sprintf('FirstName: %s, LastName: %s, Email: %s, Topic: %s, Message: %s', $visitor_firstname, $visitor_lastname, $visitor_email, $topic_lookup, $visitor_message);

// Make sure you run the code in PHP 7.4 +

// Otherwise, you would need to make $email_headers as string
$email_headers = array(
    // Best practice, but it may need you to have a mail set up in noreply@yourdomain.ca
    // 'From' => 'noreply@yryoko-o.com',
    // 'Reply-To' =>$visitor_email,

    // You can still use it, if above is too much work
    'From'=>$visitor_email
);

// 3. Send out the email
$email_result = mail($email_recipient, $email_subject, $email_message, $email_headers);
if ($email_result) {
    $results['message'] = sprintf('Thank you for contacting us, %s. You will get a reply within 24 hours.', $visitor_firstname);
} else {
    $results['message'] = sprintf('We are sorry but the email did not go through');
}

echo json_encode($results);
