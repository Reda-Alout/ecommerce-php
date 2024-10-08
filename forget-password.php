<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_forget_password = $row['banner_forget_password'];
}
?>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if (isset($_POST['form1'])) {

    $valid = 1;
    $error_message = '';
    $success_message = '';
        
    if (empty($_POST['cust_email'])) {
        $valid = 0;
        $error_message .= LANG_VALUE_131."\\n";
    } else {
        if (filter_var($_POST['cust_email'], FILTER_VALIDATE_EMAIL) === false) {
            $valid = 0;
            $error_message .= LANG_VALUE_134."\\n";
        } else {
            $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
            $statement->execute(array($_POST['cust_email']));
            $total = $statement->rowCount();                        
            if (!$total) {
                $valid = 0;
                $error_message .= LANG_VALUE_135."\\n";
            }
        }
    }

    if ($valid == 1) {

        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $forget_password_message = $row['forget_password_message'];
        }

        $token = md5(rand());
        $now = time();

        $statement = $pdo->prepare("UPDATE tbl_customer SET cust_token=?,cust_timestamp=? WHERE cust_email=?");
        $statement->execute(array($token, $now, strip_tags($_POST['cust_email'])));
        
        //$message = '<p>'.LANG_VALUE_142.'<br> <a href="'.BASE_URL.'reset-password.php?email='.$_POST['cust_email'].'&token='.$token.'">Click here</a>';

        $message = '<p>'.LANG_VALUE_142.'<br> <a href=" ' . $_ENV['BASE_URL'] . 'reset-password.php?email='.$_POST['cust_email'].'&token='.$token.'">Click here</a>';

        $mail = new PHPMailer(true); // Create a new PHPMailer instance

        try {
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = $_ENV['SMTP_HOST']; // Set the SMTP server to send through
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = $_ENV['SMTP_USERNAME']; // SMTP username
            $mail->Password = $_ENV['SMTP_PASSWORD']; // SMTP password
            $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION']; // Enable implicit TLS encryption
            $mail->Port = $_ENV['SMTP_PORT']; // TCP port to connect to
            
            // Recipients
            $mail->setFrom($_ENV['SMTP_USERNAME']);            
            $mail->addAddress($_POST['cust_email']); // Add a recipient

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = LANG_VALUE_143;
            $mail->Body = $message;

            $mail->send();
            $success_message = $forget_password_message;
        } catch (Exception $e) {
            $error_message .= "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(assets/uploads/<?php echo $banner_forget_password; ?>);">
    <div class="inner">
        <h1><?php echo LANG_VALUE_97; ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <?php
                    if (!empty($error_message)) {
                        echo "<script>alert('".$error_message."')</script>";
                    }
                    if (!empty($success_message)) {
                        echo "<script>alert('".$success_message."')</script>";
                    }
                    ?>
                    <form action="" method="post">
                        <?php $csrf->echoInputField(); ?>
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for=""><?php echo LANG_VALUE_94; ?> *</label>
                                    <input type="email" class="form-control" name="cust_email">
                                </div>
                                <div class="form-group">
                                    <label for=""></label>
                                    <input type="submit" class="btn btn-primary" value="<?php echo LANG_VALUE_4; ?>" name="form1">
                                </div>
                                <a href="login.php" style="color:#e4144d;"><?php echo LANG_VALUE_12; ?></a>
                            </div>
                        </div>                        
                    </form>
                </div>                
            </div>
        </div>
    </div>
</div>

<!-- <?php require_once('footer.php'); ?> -->
