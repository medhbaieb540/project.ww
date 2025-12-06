<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/User.php';

// لو نزلت PHPMailer يدويًا في lib/PHPMailer
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ResetPasswordController
{
    // 1) إرسال طلب reset (توليد وتخزين التوكن)
    public function requestReset($email)
    {
        $db = config::getConnexion();

        // نتحقق من وجود المستخدم في جدول login
        $stmt = $db->prepare("SELECT * FROM `login` WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // لو المستخدم غير موجود، ما نكشف هذا الشيء (للأمان)
        if (!$user) {
            return true;
        }

        // نولّد token عشوائي + وقت انتهاء بعد ساعة
        $token   = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600);

        // نخزّن التوكن ووقت الانتهاء في نفس جدول login
        $stmt = $db->prepare("UPDATE `login` SET reset_token = :token, reset_expires = :exp 
            WHERE email = :email");
        $stmt->execute([
            ':token' => $token,
            ':exp'   => $expires,
            ':email' => $email
        ]);

        // نجهّز رابط إعادة تعيين كلمة السر
        $resetLink = "http://localhost/User_managment/View/FrontOffice/reset_password.php?email=$email&token=$token";

        // نرسل الإيميل عبر PHPMailer + Mailtrap
        $mail = new PHPMailer(true);

        try {
            // إعدادات SMTP (من Mailtrap)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';   // غيّرها من Mailtrap
            $mail->SMTPAuth   = true;
            $mail->Username   = 'aymanhamoda8@gmail.com';     // حط الـ Username الحقيقي
            $mail->Password   = 'ozwidwpjzegalpfv';     // حط الـ Password الحقيقي
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // بيانات الإرسال
            $mail->setFrom('aymanhamoda8@gmail.com', 'GameBridge');
            $mail->addAddress($email);

            // محتوى الرسالة
            $mail->Subject = 'Reset your GameBridge password';
            $mail->Body    = "Hello,\n\nClick this link to reset your password:\n$resetLink\n\nIf you did not request this, ignore this email.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        }
    }

    // 2) التحقق من صحة التوكن
    public function verifyToken($email, $token)
    {
        $db = config::getConnexion();

        $sql = "SELECT * FROM `login` 
            WHERE email = :email AND reset_token = :token AND reset_expires > NOW()";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':token' => $token
        ]);

        return $stmt->fetch(); // يرجع false لو الرابط غير صالح أو منتهي
    }

    // 3) تغيير كلمة السر
    public function resetPassword($email, $token, $newPassword)
    {
        $db = config::getConnexion();

        // نتحقّق من صحّة التوكن أولاً
        $user = $this->verifyToken($email, $token);
        if (!$user) {
           return false; // التوكن غير صالح أو منتهي
        }

        // نعمل Hash لكلمة السر الجديدة
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        // نحدّث كلمة السر ونصفّر التوكن ووقت انتهائه
        $sql = "UPDATE `login` SET password = :password,reset_token = NULL, reset_expires = NULL 
            WHERE email = :email";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':password' => $hashed,
            ':email'    => $email
        ]);

        return true;
    }
}

?>
