<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$servername = "localhost";
$username = "gkblvzmy_student-portal";
$password = "Success100%";
$dbname = "gkblvzmy_student-portal";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

$sql="INSERT INTO student_registration_data (student_passport_photo, student_form, student_name, student_gender, student_address_line1, student_village, student_city, student_dob) VALUES (?,?,?,?,?,?,?,?)";
$stmt=$conn->prepare($sql);
$stmt->bind_param("ssssssss", $student_photo, $student_form, $student_name, $student_gender, $student_addr_line1, $student_village, $student_city, $student_dob);

//Collects form data from https://slss.edu.tt/registration
$student_photo= $_POST['fields']['student_passport']['value'];
$student_form= $_POST['fields']['student_class']['value'];
$student_name= $_POST['fields']['student_name']['value'];
$student_gender= $_POST['fields']['student_gender']['value'];
$student_addr_line1= $_POST['fields']['student_address_line1']['value'];
$student_village= $_POST['fields']['student_village']['value'];
$student_city= $_POST['fields']['student_city']['value'];
$student_dob= $_POST['fields']['student_dob']['value'];
$student_birth_certificate= $_POST['fields']['student_birth_certificate']['value'];
$student_birth_pin= $_POST['fields']['student_birth_pin']['value'];
$student_religion= $_POST['fields']['student_religion']['value'];
$student_country= $_POST['fields']['student_country_of_birth']['value'];
$student_nationality= $_POST['fields']['student_nationality']['value'];
$student_contact= $_POST['fields']['student_contact_no']['value'];
$student_email= $_POST['fields']['student_email']['value'];
this is a change and another change is here

$stmt->execute();
$stmt->close();
$conn->close();
?>